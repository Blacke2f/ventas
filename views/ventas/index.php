<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . '/login');
    exit;
}

// Lista de cajeros para filtro (solo admin)
require_once MODELS_PATH . 'Usuario.php';
$usuarioModel = new Usuario();
$cajeros = ($_SESSION['rol'] === 'admin') ? ($usuarioModel->getAllActivos() ?? []) : [];

$pageTitle = 'Historial de Ventas';
ob_start();
?>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Desde</label>
                <input type="date" class="form-control form-control-sm" id="fechaInicio"
                       value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Hasta</label>
                <input type="date" class="form-control form-control-sm" id="fechaFin"
                       value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Tipo de pago</label>
                <select class="form-select form-select-sm" id="filterTipo">
                    <option value="">Todos</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="fiado">Fiado</option>
                </select>
            </div>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Cajero</label>
                <select class="form-select form-select-sm" id="filterCajero">
                    <option value="">Todos los cajeros</option>
                    <?php foreach ($cajeros as $u): ?>
                    <option value="<?php echo $u['id_usuario']; ?>">
                        <?php echo htmlspecialchars($u['nombre_completo']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-6 col-md-2">
                <button class="btn btn-primary btn-sm w-100" onclick="cargarVentas()">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Resumen -->
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-2">
                <h3 class="text-primary mb-0" id="resTotal">0</h3>
                <small class="text-muted">Ventas</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-2">
                <h3 class="text-success mb-0" id="resUSD">$0</h3>
                <small class="text-muted">Total USD</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-2">
                <h3 class="text-primary mb-0" id="resBs">Bs 0</h3>
                <small class="text-muted">Total Bs</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-2">
                <h3 class="text-info mb-0" id="resPromedio">$0</h3>
                <small class="text-muted">Promedio</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Listado de Ventas</span>
        <button class="btn btn-sm btn-outline-success" onclick="exportarCSV()">
            <i class="fas fa-file-csv me-1"></i>Exportar CSV
        </button>
    </div>
    <div class="card-body p-0">
        <div id="ventasBox">
            <div class="text-center p-5"><div class="spinner-border text-primary"></div></div>
        </div>
    </div>
</div>

<!-- Modal Detalle de Venta -->
<div class="modal fade" id="detalleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#6366f1,#7c3aed);">
                <h5 class="modal-title text-white"><i class="fas fa-receipt me-2"></i>Detalle de Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="detalleContent">
                <div class="text-center p-4"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="btnCancelarVenta" class="btn btn-danger btn-sm" style="display:none;">
                    <i class="fas fa-ban me-1"></i>Cancelar Venta
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let ventasData     = [];
let ventaDetalleId = null;
const esAdmin      = <?php echo ($_SESSION['rol'] === 'admin') ? 'true' : 'false'; ?>;
const tasa = () => (typeof tasaCambio !== 'undefined' && tasaCambio) ? tasaCambio.tasa : 567.68;

async function cargarVentas() {
    const box     = document.getElementById('ventasBox');
    box.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';

    const inicio   = document.getElementById('fechaInicio').value;
    const fin      = document.getElementById('fechaFin').value;
    const tipo     = document.getElementById('filterTipo').value || null;
    const cajeroEl = document.getElementById('filterCajero');
    const cajero   = cajeroEl ? cajeroEl.value || null : null;

    let url = `/api/ventas?action=rango&inicio=${inicio}&fin=${fin}`;
    if (tipo)   url += `&tipo_pago=${tipo}`;
    if (cajero) url += `&id_usuario=${cajero}`;

    const r = await api.get(url);
    if (!r.ok) { box.innerHTML = `<div class="alert alert-danger m-3">${r.error}</div>`; return; }

    ventasData = r.data.ventas || [];

    // Resumen
    const totalUSD = ventasData.reduce((s,v) => s + parseFloat(v.total||0), 0);
    const t = tasa();
    document.getElementById('resTotal').textContent   = ventasData.length;
    document.getElementById('resUSD').textContent     = '$' + totalUSD.toFixed(2);
    document.getElementById('resBs').textContent      = 'Bs ' + (totalUSD*t).toLocaleString('es-VE',{maximumFractionDigits:0});
    document.getElementById('resPromedio').textContent = '$' + (ventasData.length ? (totalUSD/ventasData.length).toFixed(2) : '0.00');

    if (!ventasData.length) {
        box.innerHTML = '<div class="text-center p-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i><h6>Sin ventas en el período</h6></div>';
        return;
    }

    let rows = '';
    ventasData.forEach(v => {
        const usd   = parseFloat(v.total) || 0;
        const bs    = usd * t;
        const fecha = new Date(v.fecha_venta);
        const color = {efectivo:'success',tarjeta:'info',fiado:'warning'}[v.tipo_pago] || 'secondary';
        rows += `<tr>
            <td>
                <div class="small fw-semibold">${fecha.toLocaleDateString('es-VE')}</div>
                <div class="text-muted" style="font-size:.7rem;">${fecha.toLocaleTimeString('es-VE',{hour:'2-digit',minute:'2-digit'})}</div>
            </td>
            <td>
                <small class="fw-semibold">${v.numero_venta}</small>
                <div class="d-md-none"><span class="badge bg-${color} mt-1" style="font-size:.62rem;">${v.tipo_pago.toUpperCase()}</span></div>
            </td>
            <td><small>${v.nombre_cliente || '<span class="text-muted">General</span>'}</small></td>
            <td class="hide-mobile"><small>${v.cajero_nombre || '—'}</small></td>
            <td class="hide-mobile"><span class="badge bg-${color}">${v.tipo_pago.toUpperCase()}</span></td>
            <td><strong>$${usd.toFixed(2)}</strong></td>
            <td class="hide-mobile"><small class="text-primary">Bs ${bs.toLocaleString('es-VE',{maximumFractionDigits:0})}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="verDetalle(${v.id_venta})">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>`;
    });

    box.innerHTML = `<div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr>
                <th>Fecha</th><th>N° Venta</th><th>Cliente</th>
                <th class="hide-mobile">Cajero</th>
                <th class="hide-mobile">Pago</th>
                <th>Total USD</th>
                <th class="hide-mobile">Total Bs</th>
                <th></th>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table></div>`;
}

async function verDetalle(id) {
    ventaDetalleId = id;
    const cnt = document.getElementById('detalleContent');
    cnt.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';
    document.getElementById('btnCancelarVenta').style.display = 'none';
    new bootstrap.Modal(document.getElementById('detalleModal')).show();

    const r = await ventasAPI.getOne(id);
    if (!r.ok) { cnt.innerHTML = `<div class="alert alert-danger m-3">${r.error}</div>`; return; }

    const venta = r.data.venta;
    const items = r.data.items || [];
    const t     = tasa();
    const bs    = parseFloat(venta.total) * t;

    // Mostrar botón cancelar solo si es admin y está pagada
    if (esAdmin && venta.estado_venta === 'pagada') {
        document.getElementById('btnCancelarVenta').style.display = '';
    }

    let filas = '';
    items.forEach(i => {
        filas += `<tr>
            <td>${i.nombre_producto}</td>
            <td class="text-center">${i.cantidad}</td>
            <td class="text-end">$${parseFloat(i.precio_unitario).toFixed(2)}</td>
            <td class="text-end">$${parseFloat(i.subtotal).toFixed(2)}</td>
        </tr>`;
    });

    cnt.innerHTML = `
    <div class="p-3 border-bottom bg-light d-flex flex-wrap gap-3">
        <div><small class="text-muted">N° Venta</small><div class="fw-bold">${venta.numero_venta}</div></div>
        <div><small class="text-muted">Fecha</small><div class="fw-bold">${new Date(venta.fecha_venta).toLocaleString('es-VE')}</div></div>
        <div><small class="text-muted">Cajero</small><div>${venta.cajero_nombre || '—'}</div></div>
        <div><small class="text-muted">Cliente</small><div>${venta.nombre_cliente || 'General'}</div></div>
        <div><small class="text-muted">Tipo Pago</small><div>${venta.tipo_pago}</div></div>
        <div><small class="text-muted">Estado</small><div class="badge ${venta.estado_venta==='pagada'?'bg-success':'bg-danger'}">${venta.estado_venta}</div></div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Producto</th><th class="text-center">Qty</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>${filas}</tbody>
            <tfoot>
                <tr class="table-light"><th colspan="3" class="text-end">TOTAL USD</th><th class="text-end">$${parseFloat(venta.total).toFixed(2)}</th></tr>
                <tr><th colspan="3" class="text-end text-muted small">Total Bs</th><th class="text-end text-primary small">Bs ${bs.toLocaleString('es-VE',{minimumFractionDigits:2})}</th></tr>
            </tfoot>
        </table>
    </div>`;
}

// Cancelar venta
document.getElementById('btnCancelarVenta').addEventListener('click', async () => {
    if (!confirm('¿Cancelar esta venta? El stock se restaurará automáticamente.')) return;
    const motivo = prompt('Motivo de cancelación (opcional):') || 'Cancelada por admin';
    const r = await api.put(`/api/ventas?action=cancel&id=${ventaDetalleId}`, {motivo});
    if (r.ok) {
        Utils.showToast('Venta cancelada y stock restaurado', 'success');
        bootstrap.Modal.getInstance(document.getElementById('detalleModal')).hide();
        cargarVentas();
    } else {
        Utils.showToast(r.error || 'Error al cancelar', 'danger');
    }
});

// Exportar CSV
function exportarCSV() {
    if (!ventasData.length) { Utils.showToast('No hay ventas para exportar', 'warning'); return; }
    let csv = 'Fecha,N° Venta,Cliente,Cajero,Tipo Pago,Total USD\n';
    ventasData.forEach(v => {
        csv += `"${v.fecha_venta}","${v.numero_venta}","${v.nombre_cliente||'General'}","${v.cajero_nombre||''}","${v.tipo_pago}","${parseFloat(v.total).toFixed(2)}"\n`;
    });
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'ventas_' + document.getElementById('fechaInicio').value + '_a_' + document.getElementById('fechaFin').value + '.csv';
    a.click();
}

document.addEventListener('DOMContentLoaded', cargarVentas);
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . 'layouts/main.php';
?>
