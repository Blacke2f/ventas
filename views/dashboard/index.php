<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . '/login');
    exit;
}

require_once MODELS_PATH . 'Venta.php';
require_once MODELS_PATH . 'Cliente.php';
require_once MODELS_PATH . 'Credito.php';
require_once MODELS_PATH . 'Producto.php';
require_once MODELS_PATH . 'Utils.php';

$ventaModel    = new Venta();
$clienteModel  = new Cliente();
$creditoModel  = new Credito();
$productoModel = new Producto();

try {
    $resumenHoy       = $ventaModel->getResumenDiario() ?: ['total_vendido'=>0,'total_transacciones'=>0,'efectivo'=>0,'tarjeta'=>0,'fiado'=>0];
    $ventasHoy        = (int)($ventaModel->countHoy() ?? 0);
    $creditosVencidos = count($creditoModel->getVencidos() ?? []);
    $clientesDeuda    = count($clienteModel->getClientesConDeuda() ?? []);
    $stockBajo        = $productoModel->getStockBajo() ?? [];
    $totalStockBajo   = count($stockBajo);
    // Comparar con ayer
    $resumenAyer = $ventaModel->getResumenDiario(date('Y-m-d', strtotime('-1 day'))) ?: ['total_vendido'=>0,'total_transacciones'=>0];
} catch (Exception $e) {
    $resumenHoy       = ['total_vendido'=>0,'total_transacciones'=>0,'efectivo'=>0,'tarjeta'=>0,'fiado'=>0];
    $ventasHoy        = 0;
    $creditosVencidos = 0;
    $clientesDeuda    = 0;
    $stockBajo        = [];
    $totalStockBajo   = 0;
    $resumenAyer      = ['total_vendido'=>0,'total_transacciones'=>0];
}

$totalVendidoHoy = (float)($resumenHoy['total_vendido'] ?? 0);

$pageTitle = 'Dashboard';
ob_start();
?>

<div class="container-fluid">

    <!-- Tasa de cambio -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="p-3 rounded-3 text-white d-flex align-items-center justify-content-between"
                 style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <div>
                    <div style="font-size:.72rem;opacity:.75;margin-bottom:2px;">
                        <i class="fas fa-dollar-sign"></i> Tasa BCV Oficial
                    </div>
                    <div class="d-flex align-items-baseline gap-2 flex-wrap">
                        <span class="opacity-75 small">1 USD =</span>
                        <strong class="rate-value fw-bold" style="font-size:1.5rem;">Bs 567,68</strong>
                    </div>
                    <small class="opacity-75 rate-update" style="font-size:.68rem;">Actualizando...</small>
                </div>
                <button id="btn-refresh-rate-dash" class="btn btn-light btn-sm flex-shrink-0">
                    <i class="fas fa-sync-alt"></i>
                    <span class="d-none d-sm-inline"> Actualizar</span>
                </button>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 text-white"
                 style="background:linear-gradient(135deg,#6366f1,#7c3aed);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 small opacity-75 text-uppercase fw-semibold">Ventas Hoy</p>
                        <h2 class="mb-0 fw-bold"><?php echo $ventasHoy; ?></h2>
                        <?php
                        $ayerTrans = (int)($resumenAyer['total_transacciones'] ?? 0);
                        if ($ayerTrans > 0) {
                            $difTrans = $ventasHoy - $ayerTrans;
                            $signo = $difTrans >= 0 ? '+' : '';
                            echo "<small class='opacity-75'>{$signo}{$difTrans} vs ayer</small>";
                        } else {
                            echo "<small class='opacity-75'>transacciones</small>";
                        }
                        ?>
                    </div>
                    <div class="p-3 rounded-circle" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-cash-register fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 text-white"
                 style="background:linear-gradient(135deg,#10b981,#059669);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 small opacity-75 text-uppercase fw-semibold">Total Vendido</p>
                        <h2 class="mb-0 fw-bold"><?php echo Utils::formatCurrency($totalVendidoHoy); ?></h2>
                        <small class="opacity-75" id="total-bs">calculando...</small>
                    </div>
                    <div class="p-3 rounded-circle" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-coins fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 text-white"
                 style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 small opacity-75 text-uppercase fw-semibold">Créditos Vencidos</p>
                        <h2 class="mb-0 fw-bold"><?php echo $creditosVencidos; ?></h2>
                        <small class="opacity-75">
                            <?php echo $creditosVencidos > 0 ? '<a href="'.APP_URL.'/creditos" class="text-white opacity-75"><u>Ver créditos</u></a>' : 'Sin vencidos'; ?>
                        </small>
                    </div>
                    <div class="p-3 rounded-circle" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 text-white"
                 style="background:linear-gradient(135deg,#ef4444,#dc2626);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 small opacity-75 text-uppercase fw-semibold">Clientes con Deuda</p>
                        <h2 class="mb-0 fw-bold"><?php echo $clientesDeuda; ?></h2>
                        <small class="opacity-75">con saldo pendiente</small>
                    </div>
                    <div class="p-3 rounded-circle" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-user-clock fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta stock bajo (si hay productos) -->
    <?php if ($totalStockBajo > 0): ?>
    <div class="alert border-0 mb-4 d-flex align-items-center gap-3"
         style="background:#fef3c7;border-left:4px solid #f59e0b !important;border-left:0;">
        <i class="fas fa-box-open fa-xl text-warning flex-shrink-0"></i>
        <div class="flex-grow-1">
            <strong><?php echo $totalStockBajo; ?> producto<?php echo $totalStockBajo > 1 ? 's' : ''; ?> con stock bajo</strong>
            <div class="d-flex flex-wrap gap-1 mt-1">
                <?php foreach (array_slice($stockBajo, 0, 5) as $prod): ?>
                <span class="badge bg-warning text-dark">
                    <?php echo htmlspecialchars($prod['nombre_producto']); ?>
                    (<?php echo $prod['stock_actual']; ?>)
                </span>
                <?php endforeach; ?>
                <?php if ($totalStockBajo > 5): ?>
                <span class="badge bg-secondary">+<?php echo $totalStockBajo - 5; ?> más</span>
                <?php endif; ?>
            </div>
        </div>
        <a href="<?php echo APP_URL; ?>/productos" class="btn btn-sm btn-warning fw-semibold flex-shrink-0">
            <i class="fas fa-arrow-right me-1"></i>Ver productos
        </a>
    </div>
    <?php endif; ?>

    <!-- Acciones y resumen -->
    <div class="row g-3 mb-4">
    <!-- Acciones rápidas — ocultas en móvil (usar bottom nav) -->
    <div class="d-none d-md-block col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-bolt text-warning"></i> Acciones Rápidas</h5>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="<?php echo APP_URL; ?>/pos" class="btn btn-lg btn-primary">
                    <i class="fas fa-cash-register me-2"></i> Nueva Venta
                </a>
                <a href="<?php echo APP_URL; ?>/clientes" class="btn btn-lg btn-outline-info">
                    <i class="fas fa-user-plus me-2"></i> Clientes
                </a>
                <a href="<?php echo APP_URL; ?>/creditos" class="btn btn-lg btn-outline-warning">
                    <i class="fas fa-receipt me-2"></i> Créditos / Fiados
                </a>
                <a href="<?php echo APP_URL; ?>/productos" class="btn btn-lg btn-outline-success">
                    <i class="fas fa-box me-2"></i> Productos
                </a>
            </div>
        </div>
    </div>

        <!-- Resumen numérico — full width en móvil -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Resumen de Ventas Hoy</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-primary mb-0"><?php echo (int)($resumenHoy['total_transacciones'] ?? 0); ?></h4>
                                <small class="text-muted">Transacciones</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-success mb-0"><?php echo Utils::formatCurrency($resumenHoy['efectivo'] ?? 0); ?></h4>
                                <small class="text-muted">Efectivo</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-info mb-0"><?php echo Utils::formatCurrency($resumenHoy['tarjeta'] ?? 0); ?></h4>
                                <small class="text-muted">Tarjeta</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-warning mb-0"><?php echo Utils::formatCurrency($resumenHoy['fiado'] ?? 0); ?></h4>
                                <small class="text-muted">Fiado</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas ventas del día -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list text-secondary"></i> Últimas Ventas del Día</h5>
            <a href="<?php echo APP_URL; ?>/ventas" class="btn btn-sm btn-outline-secondary">
                Ver historial <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div id="ventasTableContainer">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="text-muted mt-2 mb-0">Cargando...</p>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
const TOTAL_USD_HOY = <?php echo $totalVendidoHoy; ?>;

document.addEventListener('DOMContentLoaded', () => {
    // Mostrar total en Bs cuando la tasa esté lista — polling en lugar de timeout fijo
    let intentos = 0;
    const maxIntentos = 30;
    const esperar = setInterval(() => {
        intentos++;
        const el = document.getElementById('total-bs');
        if (el && typeof tasaCambio !== 'undefined' && tasaCambio && tasaCambio.tasa > 1) {
            clearInterval(esperar);
            el.textContent = tasaCambio.formatBs(tasaCambio.usdToBs(TOTAL_USD_HOY));
        } else if (el && intentos >= maxIntentos) {
            clearInterval(esperar);
            const bs = TOTAL_USD_HOY * 567.68;
            el.textContent = 'Bs ' + bs.toLocaleString('es-VE',{minimumFractionDigits:2});
        }
    }, 100);

    // Botón actualizar tasa
    document.getElementById('btn-refresh-rate-dash')?.addEventListener('click', () => {
        if (typeof tasaCambio !== 'undefined' && tasaCambio) tasaCambio.forceUpdate();
    });

    // Cargar ventas del día
    cargarVentasDashboard();
});

async function cargarVentasDashboard() {
    const box  = document.getElementById('ventasTableContainer');
    const isMobile = window.innerWidth < 768;

    try {
        const r     = await ventasAPI.getHoy();
        const ventas = (r.ok && r.data && r.data.ventas) ? r.data.ventas : [];

        if (ventas.length === 0) {
            box.innerHTML = `
                <div class="text-center p-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                    <h6 class="mb-1">Sin ventas hoy</h6>
                    <a href="${APP_URL}/pos" class="btn btn-primary btn-sm mt-2">
                        <i class="fas fa-plus me-1"></i>Nueva Venta
                    </a>
                </div>`;
            return;
        }

        const tasa = (typeof tasaCambio !== 'undefined' && tasaCambio) ? tasaCambio.tasa : 567.68;

        if (isMobile) {
            // ── Vista mobile: cards ────────────────────────────────
            let html = '<div class="p-2">';
            ventas.forEach(v => {
                const hora  = (v.fecha_venta || '').split(' ')[1]?.substring(0,5) || '';
                const usd   = parseFloat(v.total) || 0;
                const bs    = usd * tasa;
                const colors = {efectivo:'#10b981', tarjeta:'#3b82f6', fiado:'#f59e0b'};
                const color  = colors[v.tipo_pago] || '#94a3b8';

                html += `
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                         style="width:38px;height:38px;background:${color}20;">
                        <i class="fas fa-receipt" style="color:${color};font-size:.85rem;"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold" style="font-size:.82rem;">${v.numero_venta}</span>
                            <strong style="font-size:.88rem;color:#1e293b;">$${usd.toFixed(2)}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="font-size:.72rem;color:#94a3b8;">${v.nombre_cliente || 'General'} · ${hora}</span>
                            <span style="font-size:.7rem;color:#6366f1;">Bs ${bs.toLocaleString('es-VE',{maximumFractionDigits:0})}</span>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
            box.innerHTML = html;

        } else {
            // ── Vista desktop: tabla ───────────────────────────────
            let html = `<div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th>Hora</th><th>N° Venta</th><th>Cliente</th>
                        <th>Total USD</th><th>Total Bs</th><th>Pago</th>
                    </tr></thead><tbody>`;

            ventas.forEach(v => {
                const hora  = (v.fecha_venta || '').split(' ')[1] || '';
                const usd   = parseFloat(v.total) || 0;
                const bs    = usd * tasa;
                const bsStr = 'Bs ' + bs.toLocaleString('es-VE',{minimumFractionDigits:2,maximumFractionDigits:2});
                const cm    = {efectivo:'success',tarjeta:'info',fiado:'warning'};
                const color = cm[v.tipo_pago] || 'secondary';

                html += `<tr>
                    <td><small class="text-muted">${hora}</small></td>
                    <td><strong>${v.numero_venta}</strong></td>
                    <td>${v.nombre_cliente || 'General'}</td>
                    <td><strong>$${usd.toFixed(2)}</strong></td>
                    <td><small class="text-primary">${bsStr}</small></td>
                    <td><span class="badge bg-${color}">${v.tipo_pago.toUpperCase()}</span></td>
                </tr>`;
            });

            html += '</tbody></table></div>';
            box.innerHTML = html;
        }

    } catch(e) {
        console.error(e);
        box.innerHTML = `<div class="alert alert-warning m-3 mb-0">Error al cargar ventas.</div>`;
    }
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . 'layouts/main.php';
?>
