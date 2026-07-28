<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . '/login');
    exit;
}
$pageTitle = 'Créditos y Fiados';
ob_start();
?>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tTodos">
            <i class="fas fa-list text-primary"></i> Todos los créditos
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tVencidos">
            <i class="fas fa-exclamation-circle text-danger"></i> Vencidos
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tResumen">
            <i class="fas fa-chart-pie text-success"></i> Resumen
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- ── Todos los créditos ──────────────────────────────── -->
    <div class="tab-pane fade show active" id="tTodos">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div id="todosBox">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Vencidos ────────────────────────────────────────── -->
    <div class="tab-pane fade" id="tVencidos">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div id="vencidosBox">
                    <div class="text-center p-5">
                        <div class="spinner-border text-danger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Resumen ─────────────────────────────────────────── -->
    <div class="tab-pane fade" id="tResumen">
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-3">
                        <h3 id="rTotal" class="mb-0">—</h3>
                        <small class="text-muted">Total créditos</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-3">
                        <h3 id="rActivos" class="text-primary mb-0">—</h3>
                        <small class="text-muted">Activos/Parciales</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-3">
                        <h3 id="rPendiente" class="text-warning mb-0">—</h3>
                        <small class="text-muted">Pendiente USD</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body py-3">
                        <h3 id="rPagados" class="text-success mb-0">—</h3>
                        <small class="text-muted">Pagados</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ── Modal Abono ──────────────────────────────────────────── -->
<div class="modal fade" id="abonoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#6366f1,#7c3aed);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-coins me-2"></i>Registrar Abono
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>
            <form id="abonoForm">
                <div class="modal-body">
                    <input type="hidden" id="abonoIdCredito">

                    <div class="alert alert-info py-2 mb-3">
                        <i class="fas fa-user me-1"></i>
                        <strong id="abonoClienteNombre"></strong>
                        &nbsp;|&nbsp; Pendiente: <strong id="abonoMaxLabel" class="text-danger"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Monto a abonar (USD) *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="abonoMonto"
                                   step="0.01" min="0.01" required>
                        </div>
                        <div class="form-text" id="abonoEnBs"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Método de Pago *</label>
                        <select class="form-select" id="abonoMetodo" required>
                            <option value="">Seleccionar...</option>
                            <option value="efectivo">💵 Efectivo</option>
                            <option value="tarjeta">💳 Tarjeta</option>
                            <option value="transferencia">🏦 Transferencia</option>
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Notas (opcional)</label>
                        <textarea class="form-control" id="abonoNotas" rows="2"
                                  placeholder="Observaciones del abono..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="fas fa-check me-1"></i> Registrar Abono
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const tasaActual = () => (typeof tasaCambio !== 'undefined' && tasaCambio) ? tasaCambio.tasa : 567.68;

// ── Cargar todos los créditos (activos + vencidos + parciales) ───
async function cargarTodos() {
    const box = document.getElementById('todosBox');
    // Usamos la cartera que agrupa por estado
    const [carteraR, vencR] = await Promise.all([
        creditosAPI.getCartera(),
        creditosAPI.getVencidos()
    ]);

    // Obtener créditos activos de cada cliente con deuda
    const clientesR = await api.get('/api/clientes?action=con-deuda');
    const clientes  = (clientesR.ok && clientesR.data) ? (clientesR.data.clientes || []) : [];

    if (clientes.length === 0) {
        box.innerHTML = `<div class="text-center p-5 text-muted">
            <i class="fas fa-check-circle fa-3x mb-3 d-block text-success opacity-50"></i>
            <h6 class="text-success">No hay clientes con deuda activa</h6></div>`;
        return;
    }

    // Para cada cliente con deuda, obtener sus créditos
    let rows = '';
    for (const c of clientes) {
        const cr = await api.get(`/api/creditos?action=cliente&id=${c.id_cliente}`);
        const credList = (cr.ok && cr.data) ? (cr.data.creditos || []) : [];
        for (const cred of credList) {
            if (cred.estado_credito === 'pagado') continue;
            const pend   = parseFloat(cred.monto_pendiente) || 0;
            const tasa   = tasaActual();
            const pendBs = pend * tasa;
            const venc   = new Date(cred.fecha_vencimiento);
            const hoy    = new Date();
            const esVenc = venc < hoy;
            const dias   = Math.abs(Math.round((venc - hoy) / 86400000));
            const estadoBadge = {
                activo:  `<span class="badge bg-primary">Activo</span>`,
                parcial: `<span class="badge bg-info text-white">Parcial</span>`,
                vencido: `<span class="badge bg-danger">Vencido</span>`,
            }[cred.estado_credito] || `<span class="badge bg-secondary">${cred.estado_credito}</span>`;

            rows += `<tr>
                <td>
                    <strong>${c.nombre_cliente}</strong>
                    <div class="d-md-none"><small class="text-muted">${estadoBadge}</small></div>
                </td>
                <td class="hide-mobile">$${parseFloat(cred.monto_original).toFixed(2)}</td>
                <td class="text-danger fw-semibold">$${pend.toFixed(2)}</td>
                <td class="hide-mobile"><small class="text-primary">Bs ${pendBs.toLocaleString('es-VE',{maximumFractionDigits:0})}</small></td>
                <td class="hide-mobile">
                    <small class="${esVenc ? 'text-danger' : 'text-muted'}">
                        ${venc.toLocaleDateString('es-VE')}
                        ${esVenc
                            ? `<br><span class="text-danger fw-semibold">${dias}d vencido</span>`
                            : `<br><span class="text-muted">${dias}d restantes</span>`}
                    </small>
                </td>
                <td class="hide-mobile">${estadoBadge}</td>
                <td>
                    <button class="btn btn-sm btn-success"
                            onclick="abrirAbono(${cred.id_credito},'${c.nombre_cliente.replace(/'/g,"\\'")}',${pend})">
                        <i class="fas fa-plus"></i> <span class="d-none d-md-inline">Abonar</span>
                    </button>
                </td>
            </tr>`;
        }
    }

    if (!rows) {
        box.innerHTML = `<div class="text-center p-5 text-muted">
            <i class="fas fa-check-circle fa-3x mb-3 d-block text-success opacity-50"></i>
            <h6 class="text-success">Sin créditos pendientes</h6></div>`;
        return;
    }

    box.innerHTML = `<div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th>Cliente</th>
                <th class="hide-mobile">Original</th>
                <th>Pendiente USD</th>
                <th class="hide-mobile">Pendiente Bs</th>
                <th class="hide-mobile">Vencimiento</th>
                <th class="hide-mobile">Estado</th>
                <th></th>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table></div>`;
}

// ── Vencidos ─────────────────────────────────────────────────────
async function cargarVencidos() {
    const box  = document.getElementById('vencidosBox');
    const tasa = tasaActual();
    const r    = await creditosAPI.getVencidos();
    if (!r.ok) { box.innerHTML = `<div class="alert alert-danger m-3">${r.error}</div>`; return; }

    const list = r.data.creditos || [];
    if (list.length === 0) {
        box.innerHTML = `<div class="text-center p-5 text-muted">
            <i class="fas fa-check-circle fa-3x mb-3 d-block text-success opacity-50"></i>
            <h6 class="text-success">No hay créditos vencidos</h6></div>`;
        return;
    }

    let rows = '';
    list.forEach(c => {
        const pend = parseFloat(c.monto_pendiente) || 0;
        const bs   = pend * tasa;
        rows += `<tr>
            <td>
                <strong>${c.nombre_cliente}</strong>
                <div class="d-md-none">
                    <span class="badge bg-danger mt-1" style="font-size:.65rem;">${c.dias_vencido}d vencido</span>
                </div>
            </td>
            <td class="text-danger fw-semibold">$${pend.toFixed(2)}</td>
            <td class="hide-mobile"><small class="text-primary">Bs ${bs.toLocaleString('es-VE',{maximumFractionDigits:0})}</small></td>
            <td class="hide-mobile"><span class="badge bg-danger">${c.dias_vencido} días</span></td>
            <td>
                <button class="btn btn-sm btn-success"
                        onclick="abrirAbono(${c.id_credito},'${c.nombre_cliente.replace(/'/g,"\\'")}',${pend})">
                    <i class="fas fa-plus"></i>
                    <span class="d-none d-md-inline"> Abonar</span>
                </button>
            </td>
        </tr>`;
    });

    box.innerHTML = `<div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th>Cliente</th>
                <th>Pendiente USD</th>
                <th class="hide-mobile">Pendiente Bs</th>
                <th class="hide-mobile">Días vencido</th>
                <th></th>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table></div>`;
}

// ── Resumen ───────────────────────────────────────────────────────
async function cargarResumen() {
    const r = await creditosAPI.getEstadisticas();
    if (!r.ok || !r.data) return;
    const s = r.data;
    document.getElementById('rTotal').textContent    = s.total_creditos || 0;
    document.getElementById('rActivos').textContent  = (parseInt(s.activos||0) + parseInt(s.parciales||0));
    document.getElementById('rPendiente').textContent = '$' + parseFloat(s.monto_pendiente||0).toFixed(2);
    document.getElementById('rPagados').textContent  = s.pagados || 0;
}

// ── Abrir modal de abono ──────────────────────────────────────────
function abrirAbono(id, nombre, pendiente) {
    document.getElementById('abonoIdCredito').value        = id;
    document.getElementById('abonoClienteNombre').textContent = nombre;
    document.getElementById('abonoMaxLabel').textContent   = '$' + parseFloat(pendiente).toFixed(2);
    document.getElementById('abonoMonto').value            = parseFloat(pendiente).toFixed(2);
    document.getElementById('abonoMonto').max              = pendiente;
    document.getElementById('abonoMetodo').value           = '';
    document.getElementById('abonoNotas').value            = '';
    actualizarAbonoBs();
    new bootstrap.Modal(document.getElementById('abonoModal')).show();
}

function actualizarAbonoBs() {
    const monto = parseFloat(document.getElementById('abonoMonto').value) || 0;
    const bs    = monto * tasaActual();
    document.getElementById('abonoEnBs').textContent =
        monto > 0 ? `≈ Bs ${bs.toLocaleString('es-VE',{minimumFractionDigits:2,maximumFractionDigits:2})}` : '';
}

document.getElementById('abonoMonto').addEventListener('input', actualizarAbonoBs);

// ── Guardar abono ─────────────────────────────────────────────────
document.getElementById('abonoForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id     = document.getElementById('abonoIdCredito').value;
    const monto  = parseFloat(document.getElementById('abonoMonto').value);
    const metodo = document.getElementById('abonoMetodo').value;
    const notas  = document.getElementById('abonoNotas').value;

    if (!metodo) { Utils.showToast('Selecciona el método de pago', 'warning'); return; }

    const r = await creditosAPI.addAbono(id, monto, metodo, notas);
    if (r.ok) {
        Utils.showToast(`Abono de $${monto.toFixed(2)} registrado`, 'success');
        bootstrap.Modal.getInstance(document.getElementById('abonoModal')).hide();
        cargarTodos();
        cargarVencidos();
        cargarResumen();
    } else {
        Utils.showToast(r.error || 'Error al registrar abono', 'danger');
    }
});

// ── Iniciar ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    cargarTodos();
    cargarVencidos();
    cargarResumen();
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . 'layouts/main.php';
?>
