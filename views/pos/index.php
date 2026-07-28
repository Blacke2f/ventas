<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . '/login');
    exit;
}
$pageTitle = 'Punto de Venta';
$customJS  = str_replace(' ', '%20', APP_URL) . '/public/js/pos.js';
ob_start();
?>
<style>
.prod-card{border:2px solid #e9ecef;border-radius:12px;background:#fff;cursor:pointer;transition:border-color .15s,transform .15s,box-shadow .15s;height:100%;}
.prod-card:hover{border-color:#6366f1;transform:translateY(-3px);box-shadow:0 6px 20px rgba(99,102,241,.18);}
.prod-card.agotado{opacity:.5;cursor:not-allowed;}
.prod-icon{height:60px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;margin-bottom:7px;}
.prod-name{font-size:.75rem;font-weight:600;color:#1e293b;line-height:1.2;min-height:2.4em;}
.prod-usd{font-size:.92rem;font-weight:700;color:#1e293b;}
.prod-bs{font-size:.72rem;color:#6366f1;font-weight:600;}
.prod-stock{font-size:.68rem;color:#64748b;}
#categoriasContainer .btn{font-size:.72rem;padding:.2rem .55rem;border-radius:20px;}
#categoriasContainer .btn.activo{background:linear-gradient(135deg,#6366f1,#7c3aed);color:#fff;border-color:transparent;}
.carrito-item{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;margin-bottom:6px;}
.item-name{font-size:.8rem;font-weight:600;color:#1e293b;}
.item-price{font-size:.72rem;color:#64748b;}
.item-total{font-size:.85rem;font-weight:700;color:#6366f1;}
</style>

<div class="container-fluid px-1">
<div class="row g-3">

    <!-- PRODUCTOS -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between gap-2">
                <h6 class="mb-0 fw-bold"><i class="fas fa-th text-primary"></i> Catálogo</h6>
                <input type="text" class="form-control form-control-sm" id="searchProducto"
                       placeholder="🔍 Buscar producto..." style="max-width:260px;">
            </div>
            <div class="card-body pt-2">
                <div class="mb-2 d-flex flex-wrap gap-1" id="categoriasContainer">
                    <button class="btn btn-sm activo" id="btnTodos" onclick="pos.filtrarProductos(0,this)">Todos</button>
                </div>
                <div class="row g-2" id="productosGrid">
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="text-muted mt-2 small">Cargando productos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARRITO -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="position:sticky;top:68px;">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-shopping-cart text-success"></i> Carrito
                    <span class="badge bg-success ms-1" id="carritoCount" style="display:none;">0</span>
                </h6>
                <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="pos.limpiarCarrito()" title="Vaciar">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>

            <!-- Items -->
            <div id="carritoItems" style="min-height:100px;max-height:300px;overflow-y:auto;padding:10px;">
                <div class="text-center text-muted py-3">
                    <i class="fas fa-inbox fa-2x d-block mb-1 opacity-25"></i>
                    <small>Carrito vacío</small>
                </div>
            </div>

            <!-- Totales + pago -->
            <div class="border-top p-3">
                <!-- Resumen -->
                <div class="bg-light rounded p-2 mb-2" style="font-size:.82rem;">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold" id="subtotal">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Descuento</span>
                        <div class="input-group input-group-sm" style="width:100px;">
                            <input type="number" id="descuento" class="form-control form-control-sm text-center"
                                   value="0" min="0" max="100" step="1">
                            <span class="input-group-text px-1">%</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Monto desc.</span>
                        <span class="text-danger fw-semibold" id="montoDescuento">-$0.00</span>
                    </div>
                    <hr class="my-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>TOTAL USD</strong>
                        <strong class="fs-5 text-primary" id="total">$0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Total Bs</span>
                        <span class="text-success fw-bold small" id="totalBs">Bs 0,00</span>
                    </div>
                </div>

                <!-- Cliente -->
                <div class="mb-2">
                    <label class="form-label mb-1" style="font-size:.78rem;font-weight:600;color:#374151;">
                        <i class="fas fa-user text-secondary"></i> Cliente
                    </label>
                    <select class="form-select form-select-sm" id="clienteSelect">
                        <option value="">— Público General —</option>
                    </select>
                </div>

                <!-- Botones -->
                <button id="btnEfectivo" class="btn btn-success w-100 fw-bold mb-1"
                        onclick="pos.procesarPago('efectivo')">
                    <i class="fas fa-money-bill-wave me-1"></i> Cobrar en Efectivo
                </button>
                <div class="row g-1">
                    <div class="col-6">
                        <button id="btnTarjeta" class="btn btn-info w-100 text-white"
                                onclick="pos.procesarPago('tarjeta')"
                                style="font-size:.85rem;">
                            <i class="fas fa-credit-card me-1"></i> Tarjeta
                        </button>
                    </div>
                    <div class="col-6">
                        <button id="btnFiado" class="btn btn-warning w-100"
                                onclick="pos.procesarPago('fiado')"
                                style="font-size:.85rem;color:#1e293b;">
                            <i class="fas fa-handshake me-1"></i> Fiado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

<!-- Modal producto -->
<div class="modal fade" id="productoModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2" style="background:linear-gradient(135deg,#6366f1,#7c3aed);">
                <h6 class="modal-title text-white" id="productoNombreModal">Producto</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-3">
                <strong id="productoPrecioModal" class="d-block fs-4 fw-bold" style="color:#6366f1;"></strong>
                <span id="productoBsModal" class="d-block fw-semibold text-success"></span>
                <small class="text-muted d-block mb-3">Disponible: <strong id="productoStockModal"></strong> uds.</small>
                <label class="form-label fw-semibold text-dark">Cantidad</label>
                <input type="number" class="form-control form-control-lg text-center fw-bold"
                       id="productoCantidad" value="1" min="1" style="color:#1e293b;font-size:1.4rem;">
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="pos.agregarAlCarrito()">
                    <i class="fas fa-cart-plus me-1"></i>Agregar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tries = 0, max = 40;
    var t = setInterval(function () {
        tries++;
        if (typeof POS !== 'undefined') {
            clearInterval(t);
            window.pos = new POS();
            pos.init();
        } else if (tries >= max) {
            clearInterval(t);
            document.getElementById('productosGrid').innerHTML =
                '<div class="col-12"><div class="alert alert-danger">Error al inicializar el POS. Recarga la página.</div></div>';
        }
    }, 50);
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . 'layouts/main.php';
?>
