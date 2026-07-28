<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . '/login');
    exit;
}
$pageTitle = 'Clientes';
ob_start();
?>

<div class="row g-2 mb-3">
    <div class="col-md-8">
        <input type="text" class="form-control" id="searchCliente"
               placeholder="🔍 Buscar cliente por nombre o documento...">
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-primary" onclick="nuevoCliente()">
            <i class="fas fa-user-plus me-1"></i> Nuevo Cliente
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div id="clientesBox">
            <div class="text-center p-5"><div class="spinner-border text-primary"></div></div>
        </div>
    </div>
</div>

<!-- ── Modal Nuevo / Editar ─────────────────────────────────────────── -->
<div class="modal fade" id="clienteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#6366f1,#7c3aed);">
                <h5 class="modal-title text-white" id="clienteModalTitle">Nuevo Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="clienteForm">
                <div class="modal-body">
                    <input type="hidden" id="clienteId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" class="form-control" id="cNombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cédula / Documento</label>
                            <input type="text" class="form-control" id="cDocumento" placeholder="V-12345678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="cTelefono" placeholder="0412-1234567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="cEmail">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="cDireccion">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Límite de Fiado (USD)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="cLimiteMonto"
                                       value="0" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Plazo de pago (días)</label>
                            <input type="number" class="form-control" id="cLimiteTiempo" value="30" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal Historial de compras ─────────────────────────────────── -->
<div class="modal fade" id="historialModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#6366f1,#7c3aed);">
                <h5 class="modal-title text-white" id="historialNombre">Historial</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="historialContent" class="p-3">
                    <div class="text-center p-4"><div class="spinner-border text-primary"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal Cobrar deuda ──────────────────────────────────────────── -->
<div class="modal fade" id="cobrarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                <h5 class="modal-title text-white"><i class="fas fa-coins me-2"></i>Registrar Abono</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="cobrarForm">
                <div class="modal-body">
                    <input type="hidden" id="cobrarCreditoId">
                    <div class="alert alert-warning py-2 mb-3">
                        <strong id="cobrarClienteNombre"></strong><br>
                        <small>Deuda pendiente: <strong id="cobrarPendiente" class="text-danger"></strong></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto a abonar (USD) *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="cobrarMonto"
                                   step="0.01" min="0.01" required>
                        </div>
                        <div class="form-text text-success fw-semibold" id="cobrarEnBs"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Método de pago *</label>
                        <select class="form-select" id="cobrarMetodo" required>
                            <option value="">Seleccionar...</option>
                            <option value="efectivo">💵 Efectivo</option>
                            <option value="tarjeta">💳 Tarjeta</option>
                            <option value="transferencia">🏦 Transferencia</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="fas fa-check me-1"></i>Registrar Abono
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const tasaActual = () => (typeof tasaCambio !== 'undefined' && tasaCambio) ? tasaCambio.tasa : 567.68;

// ── Cargar lista ──────────────────────────────────────────────────────
async function cargarClientes(busqueda='') {
    const box  = document.getElementById('clientesBox');
    box.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';
    const r = busqueda.length >= 2 ? await clientesAPI.search(busqueda) : await clientesAPI.list();
    if (!r.ok) { box.innerHTML = `<div class="alert alert-danger m-3">${r.error}</div>`; return; }

    const clientes = r.data.clientes || [];
    if (!clientes.length) {
        box.innerHTML = `<div class="text-center p-5 text-muted">
            <i class="fas fa-users fa-3x mb-3 d-block opacity-25"></i>
            <h6>${busqueda ? 'Sin resultados' : 'No hay clientes registrados'}</h6></div>`;
        return;
    }

    const tasa = tasaActual();
    let html = `<div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr>
            <th>Cliente</th>
            <th class="hide-mobile">Teléfono</th>
            <th>Deuda USD</th>
            <th class="hide-mobile">Deuda Bs</th>
            <th class="hide-mobile">Límite</th>
            <th class="hide-mobile">Estado</th>
            <th class="text-end">Acciones</th>
        </tr></thead><tbody>`;

    clientes.forEach(c => {
        const saldo  = parseFloat(c.saldo_fiado) || 0;
        const limite = parseFloat(c.limite_monto_fiado) || 0;
        const pct    = limite > 0 ? Math.min(100, Math.round(saldo/limite*100)) : 0;
        const color  = pct >= 80 ? 'danger' : pct >= 50 ? 'warning' : 'success';
        const bs     = saldo * tasa;

        html += `<tr>
            <td>
                <div class="fw-semibold">${c.nombre_cliente}</div>
                <small class="text-muted">${c.documento_identidad || ''}</small>
                ${saldo > 0 ? `<div class="d-md-none mt-1"><span class="badge bg-warning text-dark" style="font-size:.65rem;">Debe $${saldo.toFixed(2)}</span></div>` : ''}
            </td>
            <td class="hide-mobile"><small>${c.telefono || '—'}</small></td>
            <td class="${saldo>0 ? 'text-danger fw-semibold' : ''}">$${saldo.toFixed(2)}</td>
            <td class="hide-mobile"><small class="text-primary">Bs ${bs.toLocaleString('es-VE',{maximumFractionDigits:0})}</small></td>
            <td class="hide-mobile">
                <div class="d-flex align-items-center gap-1">
                    <div class="progress flex-grow-1" style="height:6px;min-width:50px;">
                        <div class="progress-bar bg-${color}" style="width:${pct}%"></div>
                    </div>
                    <small class="text-muted">${pct}%</small>
                </div>
                <small class="text-muted">Límite: $${limite.toFixed(2)}</small>
            </td>
            <td class="hide-mobile">${saldo > 0
                ? `<span class="badge bg-warning text-dark">Con deuda</span>`
                : `<span class="badge bg-success">Sin deuda</span>`}</td>
            </td>
            <td class="text-end">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="editarCliente(${c.id_cliente})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-info" onclick="verHistorial(${c.id_cliente},'${c.nombre_cliente.replace(/'/g,"\\'")}')" title="Historial de compras">
                        <i class="fas fa-history"></i>
                    </button>
                    ${saldo > 0 ? `<button class="btn btn-outline-warning" onclick="abrirCobro(${c.id_cliente},'${c.nombre_cliente.replace(/'/g,"\\'")}',${saldo})" title="Cobrar deuda">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </button>` : ''}
                </div>
            </td>
        </tr>`;
    });

    html += '</tbody></table></div>';
    box.innerHTML = html;
}

// ── Historial de compras ──────────────────────────────────────────────
async function verHistorial(id, nombre) {
    document.getElementById('historialNombre').textContent = '📋 Historial: ' + nombre;
    document.getElementById('historialContent').innerHTML =
        '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';
    new bootstrap.Modal(document.getElementById('historialModal')).show();

    const r = await ventasAPI.getByCliente(id);
    const ventas = (r.ok && r.data) ? (r.data.ventas || []) : [];

    if (!ventas.length) {
        document.getElementById('historialContent').innerHTML =
            '<div class="alert alert-info m-3">Este cliente no tiene compras registradas.</div>';
        return;
    }

    const tasa = tasaActual();
    let totalUSD = 0;
    let rows = '';
    ventas.forEach(v => {
        const usd  = parseFloat(v.total) || 0;
        totalUSD  += usd;
        const bs   = usd * tasa;
        const color = {efectivo:'success',tarjeta:'info',fiado:'warning'}[v.tipo_pago] || 'secondary';
        rows += `<tr>
            <td><small>${new Date(v.fecha_venta).toLocaleDateString('es-VE')}</small></td>
            <td><small class="fw-semibold">${v.numero_venta}</small></td>
            <td><span class="badge bg-${color}">${v.tipo_pago.toUpperCase()}</span></td>
            <td><strong>$${usd.toFixed(2)}</strong></td>
            <td><small class="text-primary">Bs ${bs.toLocaleString('es-VE',{maximumFractionDigits:0})}</small></td>
        </tr>`;
    });

    document.getElementById('historialContent').innerHTML = `
        <div class="d-flex gap-3 px-3 py-2 bg-light border-bottom">
            <span class="small text-muted">${ventas.length} compras</span>
            <span class="small fw-semibold">Total: $${totalUSD.toFixed(2)}</span>
            <span class="small text-primary">Bs ${(totalUSD*tasa).toLocaleString('es-VE',{maximumFractionDigits:0})}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light"><tr>
                    <th>Fecha</th><th>N° Venta</th><th>Tipo Pago</th><th>Total USD</th><th>Total Bs</th>
                </tr></thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`;
}

// ── Cobrar deuda ─────────────────────────────────────────────────────
async function abrirCobro(idCliente, nombre, deuda) {
    // Buscar el crédito activo del cliente
    const r = await api.get('/api/creditos?action=cliente&id=' + idCliente);
    const creditos = (r.ok && r.data) ? (r.data.creditos || []) : [];
    const activo   = creditos.find(c => c.estado_credito !== 'pagado');

    if (!activo) {
        Utils.showToast('No se encontró un crédito activo para este cliente', 'warning');
        return;
    }

    document.getElementById('cobrarCreditoId').value        = activo.id_credito;
    document.getElementById('cobrarClienteNombre').textContent = nombre;
    document.getElementById('cobrarPendiente').textContent  = '$' + parseFloat(activo.monto_pendiente).toFixed(2);
    document.getElementById('cobrarMonto').value            = parseFloat(activo.monto_pendiente).toFixed(2);
    document.getElementById('cobrarMonto').max              = activo.monto_pendiente;
    document.getElementById('cobrarMetodo').value           = '';
    actualizarCobrarBs();
    new bootstrap.Modal(document.getElementById('cobrarModal')).show();
}

function actualizarCobrarBs() {
    const m  = parseFloat(document.getElementById('cobrarMonto').value) || 0;
    const bs = m * tasaActual();
    document.getElementById('cobrarEnBs').textContent = m > 0
        ? `≈ Bs ${bs.toLocaleString('es-VE',{minimumFractionDigits:2,maximumFractionDigits:2})}`
        : '';
}
document.getElementById('cobrarMonto').addEventListener('input', actualizarCobrarBs);

document.getElementById('cobrarForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id    = document.getElementById('cobrarCreditoId').value;
    const monto = parseFloat(document.getElementById('cobrarMonto').value);
    const met   = document.getElementById('cobrarMetodo').value;
    if (!met) { Utils.showToast('Selecciona el método de pago', 'warning'); return; }
    const r = await creditosAPI.addAbono(id, monto, met);
    if (r.ok) {
        Utils.showToast(`Abono de $${monto.toFixed(2)} registrado`, 'success');
        bootstrap.Modal.getInstance(document.getElementById('cobrarModal')).hide();
        cargarClientes();
    } else {
        Utils.showToast(r.error || 'Error al registrar', 'danger');
    }
});

// ── CRUD ──────────────────────────────────────────────────────────────
function nuevoCliente() {
    document.getElementById('clienteForm').reset();
    document.getElementById('clienteId').value = '';
    document.getElementById('clienteModalTitle').textContent = 'Nuevo Cliente';
    new bootstrap.Modal(document.getElementById('clienteModal')).show();
}

async function editarCliente(id) {
    const r = await clientesAPI.getOne(id);
    if (!r.ok) { Utils.showToast('Error al cargar cliente', 'danger'); return; }
    const c = r.data.cliente;
    document.getElementById('clienteId').value         = id;
    document.getElementById('cNombre').value           = c.nombre_cliente;
    document.getElementById('cDocumento').value        = c.documento_identidad || '';
    document.getElementById('cTelefono').value         = c.telefono || '';
    document.getElementById('cEmail').value            = c.email || '';
    document.getElementById('cDireccion').value        = c.direccion || '';
    document.getElementById('cLimiteMonto').value      = c.limite_monto_fiado;
    document.getElementById('cLimiteTiempo').value     = c.limite_tiempo_dias;
    document.getElementById('clienteModalTitle').textContent = 'Editar Cliente';
    new bootstrap.Modal(document.getElementById('clienteModal')).show();
}

document.getElementById('clienteForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id   = document.getElementById('clienteId').value;
    const data = {
        nombre_cliente:      document.getElementById('cNombre').value,
        documento_identidad: document.getElementById('cDocumento').value,
        telefono:            document.getElementById('cTelefono').value,
        email:               document.getElementById('cEmail').value,
        direccion:           document.getElementById('cDireccion').value,
        limite_monto_fiado:  parseFloat(document.getElementById('cLimiteMonto').value) || 0,
        limite_tiempo_dias:  parseInt(document.getElementById('cLimiteTiempo').value) || 30,
    };
    const r = id ? await clientesAPI.update(id, data) : await clientesAPI.create(data);
    if (r.ok) {
        Utils.showToast(r.data.message || 'Cliente guardado', 'success');
        bootstrap.Modal.getInstance(document.getElementById('clienteModal')).hide();
        cargarClientes();
    } else {
        Utils.showToast(r.error || 'Error', 'danger');
    }
});

// ── Búsqueda ──────────────────────────────────────────────────────────
let searchTimer;
document.getElementById('searchCliente').addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => cargarClientes(e.target.value), 300);
});

document.addEventListener('DOMContentLoaded', () => cargarClientes());
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . 'layouts/main.php';
?>
