<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . '/login');
    exit;
}
$pageTitle = 'Reportes';
ob_start();
?>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Desde</label>
                <input type="date" class="form-control form-control-sm" id="filtroInicio"
                       value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Hasta</label>
                <input type="date" class="form-control form-control-sm" id="filtroFin"
                       value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Tipo de pago</label>
                <select class="form-select form-select-sm" id="filtroTipo">
                    <option value="">Todos</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="fiado">Fiado</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-primary btn-sm w-100" onclick="cargarTodos()">
                    <i class="fas fa-chart-bar me-1"></i>Generar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#rVentas"><i class="fas fa-receipt text-primary me-1"></i>Ventas</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rGanancias"><i class="fas fa-coins text-warning me-1"></i>Ganancias</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rProductos"><i class="fas fa-box text-success me-1"></i>Productos</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rInventario"><i class="fas fa-warehouse text-info me-1"></i>Inventario</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rClientes"><i class="fas fa-users text-secondary me-1"></i>Deudas</button></li>
</ul>

<div class="tab-content">

    <!-- ── Ventas ─────────────────────────────────────────────────── -->
    <div class="tab-pane fade show active" id="rVentas">
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2"><h3 id="rvTotal" class="text-primary mb-0">—</h3><small class="text-muted">Transacciones</small></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2"><h3 id="rvUSD" class="text-success mb-0">—</h3><small class="text-muted">Total USD</small></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2"><h3 id="rvEfectivo" class="text-secondary mb-0">—</h3><small class="text-muted">Efectivo</small></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2"><h3 id="rvFiado" class="text-warning mb-0">—</h3><small class="text-muted">Fiado</small></div></div></div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between">
                <span class="fw-semibold">Detalle de Ventas</span>
                <button class="btn btn-sm btn-outline-success" onclick="exportarVentas()">
                    <i class="fas fa-file-csv me-1"></i>CSV
                </button>
            </div>
            <div class="card-body p-0"><div id="rvTabla"><div class="text-center p-4"><div class="spinner-border text-primary"></div></div></div></div>
        </div>
    </div>

    <!-- ── Ganancias ──────────────────────────────────────────────── -->
    <div class="tab-pane fade" id="rGanancias">
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2"><h3 id="ganIngresos" class="text-success mb-0">—</h3><small class="text-muted">Ingresos USD</small></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2"><h3 id="ganCostos" class="text-danger mb-0">—</h3><small class="text-muted">Costos USD</small></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2"><h3 id="ganGanancia" class="text-primary mb-0">—</h3><small class="text-muted">Ganancia Bruta</small></div></div></div>
            <div class="col-6 col-md-3"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2"><h3 id="ganMargen" class="text-info mb-0">—</h3><small class="text-muted">Margen %</small></div></div></div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0"><div id="ganTabla"><div class="text-center p-4"><div class="spinner-border text-warning"></div></div></div></div>
        </div>
    </div>

    <!-- ── Productos más vendidos ─────────────────────────────────── -->
    <div class="tab-pane fade" id="rProductos">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0"><div id="rpTabla"><div class="text-center p-4"><div class="spinner-border text-success"></div></div></div></div>
        </div>
    </div>

    <!-- ── Inventario valorado ────────────────────────────────────── -->
    <div class="tab-pane fade" id="rInventario">
        <div class="row g-2 mb-3">
            <div class="col-6"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2"><h3 id="invCosto" class="text-danger mb-0">—</h3><small class="text-muted">Valor a costo</small></div></div></div>
            <div class="col-6"><div class="card border-0 shadow-sm text-center"><div class="card-body py-2"><h3 id="invVenta" class="text-success mb-0">—</h3><small class="text-muted">Valor a precio venta</small></div></div></div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between">
                <span class="fw-semibold">Inventario Valorado</span>
                <button class="btn btn-sm btn-outline-success" onclick="exportarInventario()">
                    <i class="fas fa-file-csv me-1"></i>CSV
                </button>
            </div>
            <div class="card-body p-0"><div id="invTabla"><div class="text-center p-4"><div class="spinner-border text-info"></div></div></div></div>
        </div>
    </div>

    <!-- ── Deudas clientes ────────────────────────────────────────── -->
    <div class="tab-pane fade" id="rClientes">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0"><div id="rcTabla"><div class="text-center p-4"><div class="spinner-border text-secondary"></div></div></div></div>
        </div>
    </div>

</div>

<script>
let ventasReporte = [], inventarioData = [];
const tasa = () => (typeof tasaCambio !== 'undefined' && tasaCambio) ? tasaCambio.tasa : 567.68;

async function cargarTodos() {
    const inicio = document.getElementById('filtroInicio').value;
    const fin    = document.getElementById('filtroFin').value;
    const tipo   = document.getElementById('filtroTipo').value || null;
    await Promise.all([
        cargarVentas(inicio, fin, tipo),
        cargarGanancias(inicio, fin),
        cargarProductos(),
        cargarInventario(),
        cargarClientes(),
    ]);
}

// ── Ventas ────────────────────────────────────────────────────────────
async function cargarVentas(inicio, fin, tipo) {
    let url = `/api/ventas?action=rango&inicio=${inicio}&fin=${fin}`;
    if (tipo) url += `&tipo_pago=${tipo}`;
    const r = await api.get(url);
    if (!r.ok) return;
    ventasReporte = r.data.ventas || [];
    const t = tasa();

    let totalUSD = 0, ef = 0, fiado = 0;
    ventasReporte.forEach(v => {
        const usd = parseFloat(v.total)||0;
        totalUSD += usd;
        if (v.tipo_pago==='efectivo') ef += usd;
        if (v.tipo_pago==='fiado')    fiado += usd;
    });

    document.getElementById('rvTotal').textContent    = ventasReporte.length;
    document.getElementById('rvUSD').textContent      = '$'+totalUSD.toFixed(2);
    document.getElementById('rvEfectivo').textContent = '$'+ef.toFixed(2);
    document.getElementById('rvFiado').textContent    = '$'+fiado.toFixed(2);

    if (!ventasReporte.length) {
        document.getElementById('rvTabla').innerHTML = '<div class="text-center p-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i><h6>Sin ventas</h6></div>';
        return;
    }
    let rows = '';
    ventasReporte.forEach(v => {
        const usd = parseFloat(v.total)||0;
        const c   = {efectivo:'success',tarjeta:'info',fiado:'warning'}[v.tipo_pago]||'secondary';
        rows += `<tr>
            <td><small>${new Date(v.fecha_venta).toLocaleDateString('es-VE')}</small></td>
            <td><small>${v.numero_venta}</small></td>
            <td><small>${v.nombre_cliente||'General'}</small></td>
            <td><span class="badge bg-${c}">${v.tipo_pago}</span></td>
            <td><strong>$${usd.toFixed(2)}</strong></td>
            <td><small class="text-primary">Bs ${(usd*t).toLocaleString('es-VE',{maximumFractionDigits:0})}</small></td>
        </tr>`;
    });
    document.getElementById('rvTabla').innerHTML = `<div class="table-responsive"><table class="table table-hover table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>Fecha</th><th>N° Venta</th><th>Cliente</th><th>Pago</th><th>USD</th><th>Bs</th></tr></thead>
        <tbody>${rows}</tbody></table></div>`;
}

// ── Ganancias ─────────────────────────────────────────────────────────
async function cargarGanancias(inicio, fin) {
    const r = await api.get(`/api/ventas?action=ganancias&inicio=${inicio}&fin=${fin}`);
    if (!r.ok) return;
    const d = r.data;
    document.getElementById('ganIngresos').textContent = '$'+parseFloat(d.total_ingresos||0).toFixed(2);
    document.getElementById('ganCostos').textContent   = '$'+parseFloat(d.total_costos||0).toFixed(2);
    document.getElementById('ganGanancia').textContent = '$'+parseFloat(d.total_ganancia||0).toFixed(2);
    document.getElementById('ganMargen').textContent   = (d.margen_pct||0)+'%';

    const det = d.detalle || [];
    if (!det.length) {
        document.getElementById('ganTabla').innerHTML = '<div class="text-center p-4 text-muted">Sin datos de ganancias. Verifica que los productos tengan precio de costo.</div>';
        return;
    }
    let rows = '';
    det.forEach(x => {
        rows += `<tr>
            <td>${x.fecha}</td>
            <td>${x.ventas}</td>
            <td>$${parseFloat(x.ingresos||0).toFixed(2)}</td>
            <td>$${parseFloat(x.costos||0).toFixed(2)}</td>
            <td class="text-success fw-semibold">$${parseFloat(x.ganancia_bruta||0).toFixed(2)}</td>
        </tr>`;
    });
    document.getElementById('ganTabla').innerHTML = `<div class="table-responsive"><table class="table table-hover table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>Fecha</th><th>Ventas</th><th>Ingresos</th><th>Costos</th><th>Ganancia</th></tr></thead>
        <tbody>${rows}</tbody></table></div>`;
}

// ── Productos más vendidos ────────────────────────────────────────────
async function cargarProductos() {
    const r = await productosAPI.getTopVendidos(30);
    if (!r.ok) return;
    const p = r.data.productos || [];
    if (!p.length) {
        document.getElementById('rpTabla').innerHTML = '<div class="text-center p-5 text-muted"><i class="fas fa-box fa-3x mb-3 d-block opacity-25"></i><h6>Sin ventas registradas</h6></div>';
        return;
    }
    let rows = '';
    p.forEach((prod, i) => {
        rows += `<tr>
            <td><span class="badge bg-secondary">${i+1}</span></td>
            <td><strong>${prod.nombre_producto}</strong></td>
            <td class="text-center">${prod.total_vendido||0}</td>
            <td class="text-center">${prod.cantidad_total||0}</td>
            <td>$${parseFloat(prod.ingresos_total||0).toFixed(2)}</td>
        </tr>`;
    });
    document.getElementById('rpTabla').innerHTML = `<div class="table-responsive"><table class="table table-hover table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>#</th><th>Producto</th><th class="text-center">Transacciones</th><th class="text-center">Unidades</th><th>Ingresos</th></tr></thead>
        <tbody>${rows}</tbody></table></div>`;
}

// ── Inventario valorado ───────────────────────────────────────────────
async function cargarInventario() {
    const r = await api.get('/api/ventas?action=inventario');
    if (!r.ok) return;
    inventarioData = r.data.items || [];
    const t = tasa();
    document.getElementById('invCosto').textContent = '$'+parseFloat(r.data.total_costo||0).toFixed(2);
    document.getElementById('invVenta').textContent = '$'+parseFloat(r.data.total_venta||0).toFixed(2);

    if (!inventarioData.length) { document.getElementById('invTabla').innerHTML = '<div class="p-4 text-muted text-center">Sin datos</div>'; return; }
    let rows = '';
    inventarioData.forEach(i => {
        const vc = parseFloat(i.valor_costo)||0;
        const vv = parseFloat(i.valor_venta)||0;
        rows += `<tr>
            <td>${i.nombre_producto}</td>
            <td><small class="text-muted">${i.nombre_categoria}</small></td>
            <td class="text-center">${i.stock_actual}</td>
            <td>$${parseFloat(i.precio_costo||0).toFixed(2)}</td>
            <td>$${parseFloat(i.precio_venta||0).toFixed(2)}</td>
            <td>$${vc.toFixed(2)}<br><small class="text-primary">Bs ${(vc*t).toLocaleString('es-VE',{maximumFractionDigits:0})}</small></td>
            <td>$${vv.toFixed(2)}<br><small class="text-success">Bs ${(vv*t).toLocaleString('es-VE',{maximumFractionDigits:0})}</small></td>
        </tr>`;
    });
    document.getElementById('invTabla').innerHTML = `<div class="table-responsive"><table class="table table-hover table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>Producto</th><th>Categoría</th><th class="text-center">Stock</th><th>P. Costo</th><th>P. Venta</th><th>Valor Costo</th><th>Valor Venta</th></tr></thead>
        <tbody>${rows}</tbody></table></div>`;
}

// ── Clientes con deuda ────────────────────────────────────────────────
async function cargarClientes() {
    const r = await clientesAPI.getClientesConDeuda();
    if (!r.ok) return;
    const c = r.data.clientes || [];
    const t = tasa();
    if (!c.length) {
        document.getElementById('rcTabla').innerHTML = '<div class="text-center p-5 text-muted"><i class="fas fa-check-circle fa-3x mb-3 d-block text-success opacity-50"></i><h6 class="text-success">No hay clientes con deuda</h6></div>';
        return;
    }
    let rows = '';
    c.forEach(x => {
        const s = parseFloat(x.saldo_fiado)||0;
        const l = parseFloat(x.limite_monto_fiado)||1;
        const pct = Math.min(100,Math.round(s/l*100));
        const col = pct>=80?'danger':pct>=50?'warning':'success';
        rows += `<tr>
            <td><strong>${x.nombre_cliente}</strong></td>
            <td class="text-danger fw-semibold">$${s.toFixed(2)}</td>
            <td><small class="text-primary">Bs ${(s*t).toLocaleString('es-VE',{maximumFractionDigits:0})}</small></td>
            <td>$${l.toFixed(2)}</td>
            <td><div class="d-flex align-items-center gap-1">
                <div class="progress flex-grow-1" style="height:6px;">
                    <div class="progress-bar bg-${col}" style="width:${pct}%"></div>
                </div><small>${pct}%</small>
            </div></td>
        </tr>`;
    });
    document.getElementById('rcTabla').innerHTML = `<div class="table-responsive"><table class="table table-hover table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>Cliente</th><th>Deuda USD</th><th>Deuda Bs</th><th>Límite</th><th>Uso</th></tr></thead>
        <tbody>${rows}</tbody></table></div>`;
}

// ── Exportar CSV ──────────────────────────────────────────────────────
function exportarVentas() {
    if (!ventasReporte.length) { Utils.showToast('Sin datos para exportar','warning'); return; }
    let csv = 'Fecha,N°Venta,Cliente,Cajero,Tipo,Total USD\n';
    ventasReporte.forEach(v => {
        csv += `"${v.fecha_venta}","${v.numero_venta}","${v.nombre_cliente||'General'}","${v.cajero_nombre||''}","${v.tipo_pago}","${parseFloat(v.total).toFixed(2)}"\n`;
    });
    descargar(csv, 'ventas.csv');
}

function exportarInventario() {
    if (!inventarioData.length) { Utils.showToast('Sin datos','warning'); return; }
    let csv = 'Producto,Categoría,Stock,P.Costo,P.Venta,Valor Costo,Valor Venta\n';
    inventarioData.forEach(i => {
        csv += `"${i.nombre_producto}","${i.nombre_categoria}","${i.stock_actual}","${i.precio_costo}","${i.precio_venta}","${i.valor_costo}","${i.valor_venta}"\n`;
    });
    descargar(csv, 'inventario.csv');
}

function descargar(csv, nombre) {
    const a = document.createElement('a');
    a.href     = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = nombre;
    a.click();
}

document.addEventListener('DOMContentLoaded', cargarTodos);
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . 'layouts/main.php';
?>
