<?php
/**
 * Generar Datos de Prueba - AbasPOS
 * Crea ventas, abonos y deudas realistas para probar el sistema
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';

header('Content-Type: text/html; charset=utf-8');

$pdo = Database::getInstance()->getConnection();
$errores = [];
$ok = [];

try {
    // ── 1. Obtener datos existentes ─────────────────────────────────────────
    $usuarios  = $pdo->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE activo=1")->fetchAll(PDO::FETCH_ASSOC);
    $clientes  = $pdo->query("SELECT id_cliente, nombre_cliente, limite_monto_fiado FROM clientes WHERE activo=1")->fetchAll(PDO::FETCH_ASSOC);
    $productos = $pdo->query("SELECT id_producto, nombre_producto, precio_venta, stock_actual FROM productos WHERE activo=1 AND stock_actual>0 LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($usuarios) || empty($clientes) || empty($productos)) {
        die('<div style="color:red;font-family:sans-serif;padding:20px">Faltan datos base. Ejecute primero install-complete.php</div>');
    }

    $adminId = $usuarios[0]['id_usuario'];

    // ── 2. Limpiar ventas anteriores de prueba ──────────────────────────────
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("DELETE FROM abonos");
    $pdo->exec("DELETE FROM creditos");
    $pdo->exec("DELETE FROM detalle_ventas");
    $pdo->exec("DELETE FROM ventas");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    $ok[] = "Datos anteriores limpiados";

    // ── Helper: generar número de venta ─────────────────────────────────────
    $ventaNum = 1;
    function numVenta($fecha, &$n) {
        return $fecha . str_pad($n++, 6, '0', STR_PAD_LEFT);
    }

    // ── 3. Generar ventas de los últimos 30 días ────────────────────────────
    $totalVentas = 0;
    $fechaBase = strtotime('-30 days');

    // 25 ventas distribuidas en el último mes
    $ventasConfig = [
        // [días_atrás, tipo_pago, id_cliente(null=general), num_items]
        [28, 'efectivo', null,      2],
        [27, 'efectivo', 1,         3],
        [26, 'tarjeta',  2,         1],
        [25, 'fiado',    1,         2],
        [24, 'efectivo', null,      4],
        [23, 'efectivo', 3,         2],
        [22, 'tarjeta',  null,      1],
        [21, 'fiado',    2,         3],
        [20, 'efectivo', 1,         2],
        [19, 'efectivo', null,      3],
        [18, 'efectivo', 3,         1],
        [17, 'tarjeta',  2,         2],
        [16, 'efectivo', null,      3],
        [15, 'fiado',    1,         2],
        [14, 'efectivo', null,      4],
        [13, 'efectivo', 2,         2],
        [12, 'tarjeta',  3,         1],
        [11, 'efectivo', null,      3],
        [10, 'fiado',    1,         2],
        [9,  'efectivo', 2,         3],
        [7,  'efectivo', null,      4],
        [5,  'efectivo', 3,         2],
        [3,  'tarjeta',  null,      2],
        [2,  'fiado',    2,         3],
        [1,  'efectivo', 1,         2],
        [0,  'efectivo', null,      3],
        [0,  'tarjeta',  3,         1],
        [0,  'efectivo', 2,         2],
    ];

    $ventasCreadas = [];

    foreach ($ventasConfig as $cfg) {
        [$diasAtras, $tipoPago, $idCliente, $numItems] = $cfg;
        $fechaVenta = date('Y-m-d H:i:s', strtotime("-{$diasAtras} days") + rand(28800, 64800));
        $fechaStr   = date('Ymd', strtotime("-{$diasAtras} days"));
        $numero     = numVenta($fechaStr, $ventaNum);

        // Seleccionar productos aleatorios
        $prodsSel = array_slice(array_keys($productos), rand(0, max(0, count($productos)-$numItems)), $numItems);
        
        $subtotal = 0;
        $items = [];
        foreach ($prodsSel as $pi) {
            $prod     = $productos[$pi];
            $cantidad = rand(1, 3);
            $precio   = (float)$prod['precio_venta'];
            $sub      = $cantidad * $precio;
            $subtotal += $sub;
            $items[]  = ['id' => $prod['id_producto'], 'qty' => $cantidad, 'precio' => $precio, 'sub' => $sub];
        }

        $descuento = ($tipoPago === 'efectivo' && rand(0,4) === 0) ? round($subtotal * 0.05, 2) : 0;
        $total     = round($subtotal - $descuento, 2);
        $estado    = 'pagada';

        // Insertar venta
        $stmt = $pdo->prepare("INSERT INTO ventas (numero_venta, id_cliente, id_usuario, tipo_pago, subtotal, descuento, total, estado_venta, fecha_venta) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$numero, $idCliente, $adminId, $tipoPago, $subtotal, $descuento, $total, $estado, $fechaVenta]);
        $ventaId = $pdo->lastInsertId();

        // Insertar detalles
        $stmtDet = $pdo->prepare("INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?,?,?,?,?)");
        foreach ($items as $item) {
            $stmtDet->execute([$ventaId, $item['id'], $item['qty'], $item['precio'], $item['sub']]);
            // Reducir stock
            $pdo->prepare("UPDATE productos SET stock_actual = GREATEST(0, stock_actual - ?) WHERE id_producto = ?")->execute([$item['qty'], $item['id']]);
        }

        // Si es fiado, crear crédito
        if ($tipoPago === 'fiado' && $idCliente) {
            $diasVenc = rand(15, 45);
            $fechaVenc = date('Y-m-d', strtotime($fechaVenta . " +{$diasVenc} days"));
            $stmt2 = $pdo->prepare("INSERT INTO creditos (id_venta, id_cliente, monto_original, monto_abonado, monto_pendiente, fecha_vencimiento, estado_credito) VALUES (?,?,?,0,?,?,?)");
            $estadoCred = (strtotime($fechaVenc) < time()) ? 'vencido' : 'activo';
            $stmt2->execute([$ventaId, $idCliente, $total, $total, $fechaVenc, $estadoCred]);
            $creditoId = $pdo->lastInsertId();

            // Actualizar saldo del cliente
            $pdo->prepare("UPDATE clientes SET saldo_fiado = saldo_fiado + ? WHERE id_cliente = ?")->execute([$total, $idCliente]);

            $ventasCreadas[] = ['venta_id' => $ventaId, 'credito_id' => $creditoId, 'total' => $total, 'cliente' => $idCliente];
        }

        $totalVentas++;
    }

    $ok[] = "{$totalVentas} ventas creadas";

    // ── 4. Crear abonos en algunos créditos ─────────────────────────────────
    $creditos = $pdo->query("SELECT c.*, cl.saldo_fiado FROM creditos c JOIN clientes cl ON c.id_cliente = cl.id_cliente WHERE c.estado_credito != 'pagado' LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    $abonosCreados = 0;
    foreach ($creditos as $i => $cred) {
        if ($i % 2 === 0) { // Abonar en la mitad
            $montoAbono = round($cred['monto_pendiente'] * rand(30, 60) / 100, 2);
            $stmtAb = $pdo->prepare("INSERT INTO abonos (id_credito, monto_abono, metodo_pago, id_usuario, fecha_abono) VALUES (?,?,?,?,?)");
            $stmtAb->execute([$cred['id_credito'], $montoAbono, 'efectivo', $adminId, date('Y-m-d H:i:s', strtotime('-' . rand(1, 5) . ' days'))]);

            // Actualizar crédito
            $nuevoPend  = round($cred['monto_pendiente'] - $montoAbono, 2);
            $nuevoAbon  = round(($cred['monto_original'] - $cred['monto_pendiente']) + $montoAbono, 2);
            $nuevoEst   = $nuevoPend <= 0 ? 'pagado' : 'parcial';
            $pdo->prepare("UPDATE creditos SET monto_abonado=?, monto_pendiente=?, estado_credito=? WHERE id_credito=?")->execute([$nuevoAbon, max(0, $nuevoPend), $nuevoEst, $cred['id_credito']]);

            // Actualizar saldo cliente
            $pdo->prepare("UPDATE clientes SET saldo_fiado = GREATEST(0, saldo_fiado - ?) WHERE id_cliente=?")->execute([$montoAbono, $cred['id_cliente']]);
            $abonosCreados++;
        }
    }
    $ok[] = "{$abonosCreados} abonos creados";

    // ── 5. Resumen final ─────────────────────────────────────────────────────
    $resVentas  = $pdo->query("SELECT COUNT(*) as t, SUM(total) as s FROM ventas")->fetch(PDO::FETCH_ASSOC);
    $resCreds   = $pdo->query("SELECT COUNT(*) as t, SUM(monto_pendiente) as s FROM creditos WHERE estado_credito != 'pagado'")->fetch(PDO::FETCH_ASSOC);
    $resAbonos  = $pdo->query("SELECT COUNT(*) as t, SUM(monto_abono) as s FROM abonos")->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $errores[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos de Prueba - AbasPOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>body{background:linear-gradient(135deg,#667eea,#764ba2);min-height:100vh;padding:20px;}</style>
</head>
<body>
<div class="container" style="max-width:700px;">
    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
        <div class="card-header text-white text-center py-4" style="background:linear-gradient(135deg,#667eea,#764ba2);">
            <h3 class="mb-0"><i class="fas fa-flask"></i> Generador de Datos de Prueba</h3>
            <small>AbasPOS</small>
        </div>
        <div class="card-body p-4">

            <?php foreach ($errores as $e): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>

            <?php foreach ($ok as $msg): ?>
                <div class="alert alert-success py-2 mb-2"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?></div>
            <?php endforeach; ?>

            <?php if (empty($errores)): ?>
            <hr>
            <h5 class="mb-3">📊 Resumen generado</h5>
            <div class="row g-3 text-center mb-4">
                <div class="col-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body py-3">
                            <h2 class="text-primary mb-0"><?php echo $resVentas['t']; ?></h2>
                            <small class="text-muted">Ventas</small><br>
                            <strong class="text-success">$<?php echo number_format($resVentas['s'],2); ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body py-3">
                            <h2 class="text-warning mb-0"><?php echo $resCreds['t']; ?></h2>
                            <small class="text-muted">Créditos activos</small><br>
                            <strong class="text-danger">$<?php echo number_format($resCreds['s'],2); ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body py-3">
                            <h2 class="text-info mb-0"><?php echo $resAbonos['t']; ?></h2>
                            <small class="text-muted">Abonos</small><br>
                            <strong>$<?php echo number_format($resAbonos['s'],2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <h6 class="mb-2">✅ Datos creados:</h6>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item"><i class="fas fa-receipt text-primary me-2"></i>28 ventas en los últimos 30 días</li>
                <li class="list-group-item"><i class="fas fa-money-bill text-success me-2"></i>Ventas en efectivo, tarjeta y fiado</li>
                <li class="list-group-item"><i class="fas fa-users text-info me-2"></i>Ventas asignadas a clientes reales</li>
                <li class="list-group-item"><i class="fas fa-hourglass text-warning me-2"></i>Créditos activos y vencidos</li>
                <li class="list-group-item"><i class="fas fa-coins text-secondary me-2"></i>Abonos parciales a créditos</li>
                <li class="list-group-item"><i class="fas fa-boxes text-danger me-2"></i>Stock actualizado automáticamente</li>
            </ul>

            <div class="d-grid gap-2">
                <a href="<?php echo APP_URL; ?>/dashboard" class="btn btn-primary btn-lg">
                    <i class="fas fa-tachometer-alt"></i> Ver Dashboard
                </a>
                <a href="<?php echo APP_URL; ?>/ventas" class="btn btn-outline-secondary">
                    <i class="fas fa-list"></i> Ver Ventas
                </a>
                <a href="<?php echo APP_URL; ?>/creditos" class="btn btn-outline-warning">
                    <i class="fas fa-receipt"></i> Ver Créditos
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
