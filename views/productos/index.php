<?php
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . APP_URL . '/dashboard');
    exit;
}

$pageTitle = 'Gestión de Productos';
ob_start();
?>

<!-- Barra de búsqueda y filtros -->
<div class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text" class="form-control" id="searchProducto" placeholder="🔍 Buscar producto...">
    </div>
    <div class="col-md-4">
        <select class="form-select" id="filterCategoria">
            <option value="">Todas las categorías</option>
        </select>
    </div>
    <div class="col-md-3 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productoModal" onclick="nuevoProducto()">
            <i class="fas fa-plus"></i> Nuevo Producto
        </button>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-activos">
            <i class="fas fa-check-circle text-success"></i> Activos
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-stock-bajo">
            <i class="fas fa-exclamation-triangle text-warning"></i> Stock Bajo
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Activos -->
    <div class="tab-pane fade show active" id="tab-activos">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div id="productosTableContainer">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="text-muted mt-2">Cargando productos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Stock Bajo -->
    <div class="tab-pane fade" id="tab-stock-bajo">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div id="stockBajoContainer">
                    <div class="text-center p-5">
                        <div class="spinner-border text-warning"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL NUEVO / EDITAR PRODUCTO
═══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="productoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productoModalTitle">Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productoForm">
                <div class="modal-body">
                    <input type="hidden" id="productoId">

                    <!-- Nombre y categoría -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nombre del Producto *</label>
                            <input type="text" class="form-control" id="productoNombre" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Categoría *</label>
                            <select class="form-select" id="productoCategoria" required>
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea class="form-control" id="productoDescripcion" rows="2"></textarea>
                    </div>

                    <!-- ── Calculadora de precio ───────────────────── -->
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-calculator text-primary"></i>
                                Calcular Precio de Venta
                            </h6>

                            <!-- Tabs de cálculo -->
                            <ul class="nav nav-pills mb-3" id="calcTabs">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#calcBulto" type="button">
                                        📦 Desde Bulto
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#calcCosto" type="button">
                                        💰 Desde Costo
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Desde Bulto -->
                                <div class="tab-pane fade show active" id="calcBulto">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small">Precio del Bulto (USD)</label>
                                            <input type="number" class="form-control form-control-sm" id="calcPrecioBulto"
                                                   step="0.01" min="0" placeholder="Ej: 17.00">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Unidades por Bulto</label>
                                            <input type="number" class="form-control form-control-sm" id="calcUnidades"
                                                   min="1" placeholder="Ej: 20">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">% Ganancia</label>
                                            <input type="number" class="form-control form-control-sm" id="calcGananciaBulto"
                                                   step="0.1" min="0" value="30" placeholder="Ej: 30">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary mt-2" onclick="calcularDesdeBulto()">
                                        <i class="fas fa-calculator"></i> Calcular
                                    </button>
                                </div>

                                <!-- Desde Costo -->
                                <div class="tab-pane fade" id="calcCosto">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Precio de Costo (USD)</label>
                                            <input type="number" class="form-control form-control-sm" id="calcPrecioCosto"
                                                   step="0.01" min="0" placeholder="Ej: 1.20">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">% Ganancia</label>
                                            <input type="number" class="form-control form-control-sm" id="calcGananciaCosto"
                                                   step="0.1" min="0" value="30" placeholder="Ej: 30">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary mt-2" onclick="calcularDesdeCosto()">
                                        <i class="fas fa-calculator"></i> Calcular
                                    </button>
                                </div>
                            </div>

                            <!-- Resultado del cálculo -->
                            <div id="calcResultado" class="mt-3" style="display:none;">
                                <div class="alert alert-success mb-0 py-2">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <small class="text-muted d-block">Costo Unitario</small>
                                            <strong id="resCosto">-</strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">Ganancia</small>
                                            <strong id="resGanancia" class="text-success">-</strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">Precio de Venta</small>
                                            <strong id="resPrecio" class="text-primary fs-5">-</strong>
                                        </div>
                                    </div>
                                    <div class="text-center mt-1">
                                        <small class="text-muted" id="resBs"></small>
                                    </div>
                                    <div class="text-center mt-2">
                                        <button type="button" class="btn btn-sm btn-success" onclick="aplicarPrecioCalculado()">
                                            <i class="fas fa-check"></i> Usar este precio
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ── Fin calculadora ────────────────────────── -->

                    <!-- Precios y stock -->
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Precio de Venta (USD) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="productoPrecio" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Costo Unitario (USD)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="productoCosto" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Stock Actual</label>
                            <input type="number" class="form-control" id="productoStock" value="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Stock Mínimo</label>
                            <input type="number" class="form-control" id="productoStockMin" value="5" min="1">
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Código de Barras</label>
                            <input type="text" class="form-control" id="productoBarcode">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">% Ganancia Aplicada</label>
                            <input type="number" class="form-control" id="productoPorcentaje" step="0.01" min="0" value="0" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajustar Stock -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajustar Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockForm">
                <div class="modal-body">
                    <input type="hidden" id="stockProductoId">
                    <p id="stockProductoNombre" class="fw-bold"></p>
                    <div class="mb-3">
                        <label class="form-label">Operación</label>
                        <select class="form-select" id="stockOperacion" required>
                            <option value="sumar">➕ Agregar Stock</option>
                            <option value="restar">➖ Restar Stock</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="stockCantidad" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let precioCalculado = 0;
let costoCalculado = 0;
let porcentajeCalculado = 0;

// ── Cargar datos al iniciar ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    cargarCategorias();
    cargarProductos();
    cargarStockBajo();
});

// ── Categorías ────────────────────────────────────────────────────────────────
async function cargarCategorias() {
    const r = await productosAPI.getCategorias();
    if (!r.ok) return;
    const cats = r.data.categorias || [];
    const s1 = document.getElementById('productoCategoria');
    const s2 = document.getElementById('filterCategoria');
    cats.forEach(c => {
        s1.innerHTML += `<option value="${c.id_categoria}">${c.nombre_categoria}</option>`;
        s2.innerHTML += `<option value="${c.id_categoria}">${c.nombre_categoria}</option>`;
    });
}

// ── Productos ─────────────────────────────────────────────────────────────────
async function cargarProductos(categoriaId = '', busqueda = '') {
    const box = document.getElementById('productosTableContainer');
    box.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';

    let r;
    if (busqueda.length >= 2) {
        r = await productosAPI.search(busqueda);
    } else if (categoriaId) {
        r = await productosAPI.getByCategoria(categoriaId);
    } else {
        r = await productosAPI.list();
    }

    if (!r.ok) {
        box.innerHTML = `<div class="alert alert-danger m-3">Error: ${r.error}</div>`;
        return;
    }

    const prods = r.data.productos || [];
    if (prods.length === 0) {
        box.innerHTML = '<div class="alert alert-info m-3"><i class="fas fa-info-circle"></i> No hay productos</div>';
        return;
    }

    let html = `<div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th class="hide-mobile">Categoría</th>
                    <th>Precio USD</th>
                    <th class="hide-mobile">Precio Bs</th>
                    <th>Stock</th>
                    <th class="hide-mobile">Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead><tbody>`;

    prods.forEach(p => {
        const stockBadge = p.stock_actual <= p.stock_minimo
            ? `<span class="badge bg-warning text-dark">${p.stock_actual} ⚠️</span>`
            : `<span class="badge bg-success">${p.stock_actual}</span>`;
        const bs = tasaCambio ? tasaCambio.usdToBs(p.precio_venta) : p.precio_venta * 567.68;
        const bsStr = tasaCambio ? tasaCambio.formatBs(bs) : 'Bs ' + bs.toFixed(2);

        html += `<tr>
            <td>
                <div class="fw-semibold">${p.nombre_producto}</div>
                <small class="text-muted d-md-none">${p.nombre_categoria || ''}</small>
            </td>
            <td class="hide-mobile"><small class="text-muted">${p.nombre_categoria || '-'}</small></td>
            <td><strong>$${parseFloat(p.precio_venta).toFixed(2)}</strong></td>
            <td class="hide-mobile"><small class="text-primary">${bsStr}</small></td>
            <td>${stockBadge}</td>
            <td class="hide-mobile"><span class="badge bg-success">Activo</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editarProducto(${p.id_producto})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="abrirStockModal(${p.id_producto}, '${p.nombre_producto.replace(/'/g, "\\'")}')" title="Ajustar stock">
                    <i class="fas fa-warehouse"></i>
                </button>
            </td>
        </tr>`;
    });

    html += '</tbody></table></div>';
    box.innerHTML = html;
}

// ── Stock Bajo ────────────────────────────────────────────────────────────────
async function cargarStockBajo() {
    const box = document.getElementById('stockBajoContainer');
    const r = await productosAPI.getStockBajo();
    if (!r.ok) return;
    const prods = r.data.productos || [];
    if (prods.length === 0) {
        box.innerHTML = '<div class="alert alert-success m-3"><i class="fas fa-check-circle"></i> Todos los productos tienen stock adecuado</div>';
        return;
    }
    let html = `<div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Nombre</th><th>Stock Actual</th><th>Stock Mínimo</th><th>Faltante</th></tr></thead><tbody>`;
    prods.forEach(p => {
        html += `<tr>
            <td><strong>${p.nombre_producto}</strong></td>
            <td class="text-danger fw-bold">${p.stock_actual}</td>
            <td>${p.stock_minimo}</td>
            <td><span class="badge bg-danger">${p.faltante}</span></td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    box.innerHTML = html;
}

// ── Calculadora de precios ────────────────────────────────────────────────────
function calcularDesdeBulto() {
    const precioBulto = parseFloat(document.getElementById('calcPrecioBulto').value) || 0;
    const unidades    = parseInt(document.getElementById('calcUnidades').value) || 0;
    const ganancia    = parseFloat(document.getElementById('calcGananciaBulto').value) || 0;

    if (precioBulto <= 0 || unidades <= 0) {
        Utils.showToast('Ingresa precio del bulto y unidades', 'warning'); return;
    }

    const costoUnit = precioBulto / unidades;
    const precioVenta = costoUnit * (1 + ganancia / 100);

    precioCalculado    = Math.round(precioVenta * 100) / 100;
    costoCalculado     = Math.round(costoUnit * 100) / 100;
    porcentajeCalculado = ganancia;

    mostrarResultadoCalc(costoUnit, precioVenta - costoUnit, precioVenta, ganancia);
}

function calcularDesdeCosto() {
    const costo   = parseFloat(document.getElementById('calcPrecioCosto').value) || 0;
    const ganancia = parseFloat(document.getElementById('calcGananciaCosto').value) || 0;

    if (costo <= 0) {
        Utils.showToast('Ingresa el precio de costo', 'warning'); return;
    }

    const precioVenta = costo * (1 + ganancia / 100);

    precioCalculado    = Math.round(precioVenta * 100) / 100;
    costoCalculado     = costo;
    porcentajeCalculado = ganancia;

    mostrarResultadoCalc(costo, precioVenta - costo, precioVenta, ganancia);
}

function mostrarResultadoCalc(costo, ganancia, precio, pct) {
    const bs = tasaCambio ? tasaCambio.usdToBs(precio) : precio * 567.68;
    const bsStr = tasaCambio ? tasaCambio.formatBs(bs) : 'Bs ' + bs.toFixed(2);

    document.getElementById('resCosto').textContent   = '$' + costo.toFixed(2);
    document.getElementById('resGanancia').textContent = '$' + ganancia.toFixed(2) + ' (' + pct + '%)';
    document.getElementById('resPrecio').textContent   = '$' + precio.toFixed(2);
    document.getElementById('resBs').textContent       = '≈ ' + bsStr;
    document.getElementById('calcResultado').style.display = 'block';
}

function aplicarPrecioCalculado() {
    document.getElementById('productoPrecio').value     = precioCalculado;
    document.getElementById('productoCosto').value      = costoCalculado;
    document.getElementById('productoPorcentaje').value = porcentajeCalculado;
    Utils.showToast('Precio aplicado al formulario', 'success', 1500);
}

// ── Guardar producto ──────────────────────────────────────────────────────────
document.getElementById('productoForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('productoId').value;
    const data = {
        nombre_producto:    document.getElementById('productoNombre').value,
        id_categoria:       parseInt(document.getElementById('productoCategoria').value),
        descripcion:        document.getElementById('productoDescripcion').value,
        precio_venta:       parseFloat(document.getElementById('productoPrecio').value),
        precio_costo:       parseFloat(document.getElementById('productoCosto').value) || 0,
        porcentaje_ganancia:parseFloat(document.getElementById('productoPorcentaje').value) || 0,
        stock_actual:       parseInt(document.getElementById('productoStock').value) || 0,
        stock_minimo:       parseInt(document.getElementById('productoStockMin').value) || 5,
        codigo_barras:      document.getElementById('productoBarcode').value || null
    };

    const r = id ? await productosAPI.update(id, data) : await productosAPI.create(data);
    if (r.ok) {
        Utils.showToast(r.data.message || 'Guardado', 'success');
        bootstrap.Modal.getInstance(document.getElementById('productoModal')).hide();
        document.getElementById('productoForm').reset();
        document.getElementById('productoId').value = '';
        document.getElementById('calcResultado').style.display = 'none';
        cargarProductos();
        cargarStockBajo();
    } else {
        Utils.showToast(r.error || 'Error al guardar', 'danger');
    }
});

// ── Editar producto ───────────────────────────────────────────────────────────
async function editarProducto(id) {
    const r = await productosAPI.getOne(id);
    if (!r.ok) { Utils.showToast('Error al cargar producto', 'danger'); return; }

    const p = r.data.producto || r.data;
    document.getElementById('productoId').value           = id;
    document.getElementById('productoNombre').value       = p.nombre_producto;
    document.getElementById('productoCategoria').value    = p.id_categoria;
    document.getElementById('productoDescripcion').value  = p.descripcion || '';
    document.getElementById('productoPrecio').value       = p.precio_venta;
    document.getElementById('productoCosto').value        = p.precio_costo || 0;
    document.getElementById('productoPorcentaje').value   = p.porcentaje_ganancia || 0;
    document.getElementById('productoStock').value        = p.stock_actual;
    document.getElementById('productoStockMin').value     = p.stock_minimo;
    document.getElementById('productoBarcode').value      = p.codigo_barras || '';
    document.getElementById('calcResultado').style.display = 'none';
    document.getElementById('productoModalTitle').textContent = 'Editar Producto';
    new bootstrap.Modal(document.getElementById('productoModal')).show();
}

function nuevoProducto() {
    document.getElementById('productoForm').reset();
    document.getElementById('productoId').value = '';
    document.getElementById('calcResultado').style.display = 'none';
    document.getElementById('productoModalTitle').textContent = 'Nuevo Producto';
}

// ── Ajustar stock ─────────────────────────────────────────────────────────────
function abrirStockModal(id, nombre) {
    document.getElementById('stockProductoId').value = id;
    document.getElementById('stockProductoNombre').textContent = nombre;
    document.getElementById('stockCantidad').value = '';
    new bootstrap.Modal(document.getElementById('stockModal')).show();
}

document.getElementById('stockForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id  = document.getElementById('stockProductoId').value;
    const qty = parseInt(document.getElementById('stockCantidad').value);
    const op  = document.getElementById('stockOperacion').value;
    const r   = await productosAPI.updateStock(id, qty, op);
    if (r.ok) {
        Utils.showToast('Stock actualizado', 'success');
        bootstrap.Modal.getInstance(document.getElementById('stockModal')).hide();
        cargarProductos();
        cargarStockBajo();
    } else {
        Utils.showToast(r.error || 'Error', 'danger');
    }
});

// ── Filtros en tiempo real ────────────────────────────────────────────────────
let searchTimer;
document.getElementById('searchProducto').addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        cargarProductos(document.getElementById('filterCategoria').value, e.target.value);
    }, 300);
});

document.getElementById('filterCategoria').addEventListener('change', (e) => {
    cargarProductos(e.target.value, document.getElementById('searchProducto').value);
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . 'layouts/main.php';
?>
