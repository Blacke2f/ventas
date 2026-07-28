<?php
/**
 * Limpiar Datos de Prueba
 * Elimina: ventas, detalles, créditos, abonos, auditoria, clientes, productos
 * Conserva: usuarios, categorías
 * Requiere sesión admin activa
 */

session_start();

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';

header('Content-Type: application/json; charset=utf-8');

// Solo admin autenticado
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$body       = json_decode(file_get_contents('php://input'), true);
$confirmar  = $body['confirmar'] ?? false;

if (!$confirmar) {
    echo json_encode(['success' => false, 'error' => 'Confirmación requerida']);
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();

    // Desactivar chequeo de llaves foráneas temporalmente
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $resumen = [];

    // 1. Auditoria
    $stmt = $pdo->query("SELECT COUNT(*) FROM auditoria");
    $resumen['auditoria'] = (int)$stmt->fetchColumn();
    $pdo->exec("TRUNCATE TABLE auditoria");

    // 2. Abonos
    $stmt = $pdo->query("SELECT COUNT(*) FROM abonos");
    $resumen['abonos'] = (int)$stmt->fetchColumn();
    $pdo->exec("TRUNCATE TABLE abonos");

    // 3. Créditos
    $stmt = $pdo->query("SELECT COUNT(*) FROM creditos");
    $resumen['creditos'] = (int)$stmt->fetchColumn();
    $pdo->exec("TRUNCATE TABLE creditos");

    // 4. Detalle de ventas
    $stmt = $pdo->query("SELECT COUNT(*) FROM detalle_ventas");
    $resumen['detalle_ventas'] = (int)$stmt->fetchColumn();
    $pdo->exec("TRUNCATE TABLE detalle_ventas");

    // 5. Ventas
    $stmt = $pdo->query("SELECT COUNT(*) FROM ventas");
    $resumen['ventas'] = (int)$stmt->fetchColumn();
    $pdo->exec("TRUNCATE TABLE ventas");

    // 6. Clientes
    $stmt = $pdo->query("SELECT COUNT(*) FROM clientes");
    $resumen['clientes'] = (int)$stmt->fetchColumn();
    $pdo->exec("TRUNCATE TABLE clientes");

    // 7. Productos — eliminar todos y restablecer auto_increment
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos");
    $resumen['productos'] = (int)$stmt->fetchColumn();
    $pdo->exec("TRUNCATE TABLE productos");

    // Reactivar chequeo de llaves foráneas
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Registrar acción en auditoria (después de re-activar FK)
    $pdo->prepare("INSERT INTO auditoria (id_usuario, accion, tabla_afectada, ip_address) VALUES (?,?,?,?)")
        ->execute([$_SESSION['usuario_id'], 'LIMPIEZA_DATOS_PRUEBA', 'TODAS', $_SERVER['REMOTE_ADDR'] ?? '']);

    echo json_encode([
        'success'  => true,
        'message'  => 'Base de datos limpiada correctamente',
        'resumen'  => $resumen,
        'conserva' => ['usuarios' => 'intactos', 'categorias' => 'intactas'],
    ]);

} catch (Exception $e) {
    // Asegurarse de re-activar FK aunque haya error
    try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 1"); } catch (Exception $ignore) {}

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error al limpiar: ' . $e->getMessage(),
    ]);
}
?>
