/**
 * pos.js — Punto de Venta AbasPOS
 * Usa createVentaCompleta (transacción atómica en el backend)
 */

class POS {
    constructor() {
        this.carrito        = [];
        this.productoActual = null;
        this.procesando     = false;   // guard anti-doble-click
    }

    async init() {
        this.setupListeners();
        await Promise.all([
            this.cargarCategorias(),
            this.cargarProductos(),
            this.cargarClientes(),
        ]);
    }

    setupListeners() {
        document.getElementById('searchProducto').addEventListener('input', (e) => {
            clearTimeout(this._searchTimer);
            this._searchTimer = setTimeout(() => this.buscarProductos(e.target.value), 280);
        });
        document.getElementById('descuento').addEventListener('input', () => this.actualizarTotales());
    }

    // ── Cargar datos ──────────────────────────────────────────────────
    async cargarCategorias() {
        const r = await productosAPI.getCategorias();
        if (!r.ok) return;
        const container = document.getElementById('categoriasContainer');
        (r.data.categorias || []).forEach(cat => {
            const btn = document.createElement('button');
            btn.className   = 'btn btn-sm btn-outline-secondary';
            btn.textContent = cat.nombre_categoria;
            btn.onclick     = () => this.filtrarProductos(cat.id_categoria, btn);
            container.appendChild(btn);
        });
    }

    async cargarProductos() {
        const grid = document.getElementById('productosGrid');
        grid.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border text-primary"></div></div>';
        const r = await productosAPI.getProductosGrid(1, 48);
        if (!r.ok) {
            grid.innerHTML = `<div class="col-12"><div class="alert alert-danger m-2">${r.error || 'Error al cargar'}</div></div>`;
            return;
        }
        this.mostrarProductos(r.data.productos || []);
    }

    async cargarClientes() {
        const r = await clientesAPI.list();
        if (!r.ok) return;
        const sel = document.getElementById('clienteSelect');
        (r.data.clientes || []).forEach(c => {
            const opt = document.createElement('option');
            opt.value       = c.id_cliente;
            opt.textContent = c.nombre_cliente + (parseFloat(c.saldo_fiado) > 0 ? ` ($${parseFloat(c.saldo_fiado).toFixed(2)} deuda)` : '');
            sel.appendChild(opt);
        });
    }

    // ── Mostrar productos ─────────────────────────────────────────────
    mostrarProductos(productos) {
        const grid = document.getElementById('productosGrid');
        if (!productos.length) {
            grid.innerHTML = '<div class="col-12"><div class="alert alert-info border-0 m-2">No hay productos.</div></div>';
            return;
        }
        const tasa = (typeof tasaCambio !== 'undefined' && tasaCambio) ? tasaCambio.tasa : 567.68;
        let html = '';
        productos.forEach(p => {
            const usd     = parseFloat(p.precio_venta) || 0;
            const bs      = usd * tasa;
            const bsStr   = 'Bs ' + bs.toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2});
            const conStock = p.stock_actual > 0;
            const nombre  = p.nombre_producto.replace(/\\/g,'\\\\').replace(/'/g,"\\'");
            html += `
            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <div class="prod-card p-2 text-center${conStock ? '' : ' agotado'}"
                     ${conStock ? `onclick="pos.seleccionarProducto(${p.id_producto},'${nombre}',${usd},${p.stock_actual})"` : ''}>
                    <div class="prod-icon"><i class="fas fa-box"></i></div>
                    <div class="prod-name">${p.nombre_producto}</div>
                    <div class="prod-usd">$${usd.toFixed(2)}</div>
                    <div class="prod-bs">${bsStr}</div>
                    <div class="prod-stock" id="pstock-${p.id_producto}">
                        ${conStock
                            ? `<i class="fas fa-check-circle text-success" style="font-size:.7rem;"></i> ${p.stock_actual}`
                            : `<span class="badge bg-danger" style="font-size:.65rem;">AGOTADO</span>`}
                    </div>
                </div>
            </div>`;
        });
        grid.innerHTML = html;
    }

    // ── Seleccionar → modal ───────────────────────────────────────────
    seleccionarProducto(id, nombre, precio, stock) {
        if (stock <= 0) { Utils.showToast('Sin stock', 'warning'); return; }
        this.productoActual = {id, nombre, precio, stock};

        const tasa  = (typeof tasaCambio !== 'undefined' && tasaCambio) ? tasaCambio.tasa : 567.68;
        const bs    = precio * tasa;
        document.getElementById('productoNombreModal').textContent  = nombre;
        document.getElementById('productoPrecioModal').textContent  = '$' + parseFloat(precio).toFixed(2);
        document.getElementById('productoBsModal').textContent      = 'Bs ' + bs.toLocaleString('es-VE',{minimumFractionDigits:2,maximumFractionDigits:2});
        document.getElementById('productoStockModal').textContent   = stock;
        document.getElementById('productoCantidad').value           = 1;
        document.getElementById('productoCantidad').max             = stock;
        new bootstrap.Modal(document.getElementById('productoModal')).show();
    }

    // ── Carrito ───────────────────────────────────────────────────────
    agregarAlCarrito() {
        const cantidad = parseInt(document.getElementById('productoCantidad').value) || 1;
        if (cantidad <= 0 || cantidad > this.productoActual.stock) {
            Utils.showToast('Cantidad inválida', 'warning'); return;
        }
        const existente = this.carrito.find(i => i.id === this.productoActual.id);
        if (existente) {
            if (existente.cantidad + cantidad > existente.stock) {
                Utils.showToast('Excede el stock disponible', 'warning'); return;
            }
            existente.cantidad += cantidad;
        } else {
            this.carrito.push({...this.productoActual, cantidad});
        }
        this.actualizarCarrito();
        bootstrap.Modal.getInstance(document.getElementById('productoModal')).hide();
        Utils.showToast(`${this.productoActual.nombre} agregado`, 'success', 1500);
    }

    actualizarCarrito() {
        const box      = document.getElementById('carritoItems');
        const countEl  = document.getElementById('carritoCount');
        const totalItems = this.carrito.reduce((s,i) => s + i.cantidad, 0);
        if (countEl) { countEl.textContent = totalItems; countEl.style.display = totalItems ? '' : 'none'; }

        if (!this.carrito.length) {
            box.innerHTML = `<div class="text-center text-muted py-4" id="carritoVacio">
                <i class="fas fa-inbox fa-2x d-block mb-2 opacity-25"></i>
                <small>Agrega productos al carrito</small></div>`;
            this.actualizarTotales(); return;
        }

        const tasa = (typeof tasaCambio !== 'undefined' && tasaCambio) ? tasaCambio.tasa : 567.68;
        let html = '';
        this.carrito.forEach((item, idx) => {
            const sub   = item.precio * item.cantidad;
            const subBs = sub * tasa;
            html += `
            <div class="carrito-item">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="flex-grow-1 me-1">
                        <div class="item-name">${item.nombre}</div>
                        <div class="item-price">$${parseFloat(item.precio).toFixed(2)} × ${item.cantidad}</div>
                    </div>
                    <div class="text-end">
                        <div class="item-total">$${sub.toFixed(2)}</div>
                        <small style="color:#10b981;font-size:.7rem;">Bs ${subBs.toLocaleString('es-VE',{maximumFractionDigits:0})}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <div class="input-group input-group-sm" style="width:96px;">
                        <button class="btn btn-outline-secondary py-0 px-2" onclick="pos.cambiarCantidad(${idx},-1)">−</button>
                        <input type="text" class="form-control text-center py-0 fw-bold" style="color:#1f2937;" value="${item.cantidad}" readonly>
                        <button class="btn btn-outline-secondary py-0 px-2" onclick="pos.cambiarCantidad(${idx},1)">+</button>
                    </div>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2 ms-auto" onclick="pos.removerDelCarrito(${idx})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>`;
        });
        box.innerHTML = html;
        this.actualizarTotales();
    }

    cambiarCantidad(idx, delta) {
        const item = this.carrito[idx];
        const nueva = item.cantidad + delta;
        if (nueva <= 0) { this.removerDelCarrito(idx); return; }
        if (nueva > item.stock) { Utils.showToast('Stock insuficiente', 'warning'); return; }
        item.cantidad = nueva;
        this.actualizarCarrito();
    }

    removerDelCarrito(idx) {
        this.carrito.splice(idx, 1);
        this.actualizarCarrito();
    }

    limpiarCarrito() {
        if (this.carrito.length && !confirm('¿Vaciar el carrito?')) return;
        this.carrito = [];
        document.getElementById('descuento').value = 0;
        this.actualizarCarrito();
    }

    // ── Totales (descuento en %) ──────────────────────────────────────
    actualizarTotales() {
        const subtotal  = this.carrito.reduce((s,i) => s + i.precio * i.cantidad, 0);
        const pct       = parseFloat(document.getElementById('descuento').value) || 0;
        const montoDesc = Math.round(subtotal * (pct / 100) * 100) / 100;
        const total     = Math.max(0, Math.round((subtotal - montoDesc) * 100) / 100);
        const tasa      = (typeof tasaCambio !== 'undefined' && tasaCambio) ? tasaCambio.tasa : 567.68;
        const totalBs   = total * tasa;

        document.getElementById('subtotal').textContent       = '$' + subtotal.toFixed(2);
        const md = document.getElementById('montoDescuento');
        if (md) md.textContent = '-$' + montoDesc.toFixed(2);
        document.getElementById('total').textContent          = '$' + total.toFixed(2);
        const elBs = document.getElementById('totalBs');
        if (elBs) elBs.textContent = 'Bs ' + totalBs.toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2});
    }

    // ── Buscar / filtrar ──────────────────────────────────────────────
    async buscarProductos(termino) {
        if (!termino) { await this.cargarProductos(); return; }
        if (termino.length < 2) return;
        const r = await productosAPI.search(termino);
        if (r.ok) this.mostrarProductos(r.data.productos || []);
    }

    async filtrarProductos(idCategoria, btnEl) {
        // Marcar botón activo
        document.querySelectorAll('#categoriasContainer .btn').forEach(b => b.classList.remove('activo'));
        if (btnEl) btnEl.classList.add('activo');
        else document.getElementById('btnTodos')?.classList.add('activo');

        const grid = document.getElementById('productosGrid');
        grid.innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border text-primary"></div></div>';
        let prods = [];
        if (idCategoria === 0) {
            const r = await productosAPI.getProductosGrid(1, 48);
            if (r.ok) prods = r.data.productos || [];
        } else {
            const r = await productosAPI.getByCategoria(idCategoria);
            if (r.ok) prods = r.data.productos || [];
        }
        this.mostrarProductos(prods);
    }

    // ── Procesar pago ─────────────────────────────────────────────────
    async procesarPago(tipoPago) {
        if (this.carrito.length === 0) { Utils.showToast('El carrito está vacío', 'warning'); return; }
        if (this.procesando) return;

        const idCliente = document.getElementById('clienteSelect').value || null;
        if (tipoPago === 'fiado' && !idCliente) {
            Utils.showToast('Selecciona un cliente para vender a crédito', 'warning'); return;
        }

        // Deshabilitar botones
        this.procesando = true;
        this.setBotonesEstado(false, 'Procesando...');

        const subtotal  = this.carrito.reduce((s,i) => s + i.precio * i.cantidad, 0);
        const pct       = parseFloat(document.getElementById('descuento').value) || 0;
        const montoDesc = Math.round(subtotal * (pct / 100) * 100) / 100;
        const total     = Math.max(0, Math.round((subtotal - montoDesc) * 100) / 100);

        // Construir payload atómico
        const payload = {
            tipo_pago:  tipoPago,
            id_cliente: idCliente ? parseInt(idCliente) : null,
            subtotal:   subtotal,
            descuento:  montoDesc,
            total:      total,
            items: this.carrito.map(i => ({
                id_producto:     i.id,
                cantidad:        i.cantidad,
                precio_unitario: i.precio,
            }))
        };

        try {
            const r = await ventasAPI.create(payload);

            if (!r.ok) {
                Utils.showToast(r.error || 'Error al procesar la venta', 'danger');
                this.setBotonesEstado(true);
                this.procesando = false;
                return;
            }

            const ventaId    = r.data.id;
            const numVenta   = r.data.numero_venta;
            const carritoSnap = [...this.carrito]; // copia para el ticket



            // Limpiar y recargar grid (stock actualizado)
            this.limpiarSilencioso();
            await this.cargarProductos();

            // Mostrar ticket en modal
            this.mostrarTicket(numVenta, carritoSnap, subtotal, montoDesc, total, tipoPago);

        } catch (e) {
            console.error(e);
            Utils.showToast('Error de conexión al procesar la venta', 'danger');
        } finally {
            this.setBotonesEstado(true);
            this.procesando = false;
        }
    }

    setBotonesEstado(habilitado, texto) {
        ['btnEfectivo','btnTarjeta','btnFiado'].forEach(id => {
            const btn = document.getElementById(id);
            if (!btn) return;
            btn.disabled = !habilitado;
            if (!habilitado && texto) btn.dataset.original = btn.innerHTML;
            if (habilitado && btn.dataset.original) btn.innerHTML = btn.dataset.original;
            if (!habilitado && texto && id === 'btnEfectivo')
                btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>${texto}`;
        });
    }

    limpiarSilencioso() {
        this.carrito = [];
        document.getElementById('descuento').value = 0;
        this.actualizarCarrito();
    }

    // ── Ticket ────────────────────────────────────────────────────────
    mostrarTicket(numVenta, items, subtotal, descuento, total, tipoPago) {
        const tasa   = (typeof tasaCambio !== 'undefined' && tasaCambio) ? tasaCambio.tasa : 567.68;
        const totalBs = total * tasa;
        const fecha  = new Date().toLocaleString('es-VE');

        const pagoLabel = {efectivo:'Efectivo 💵', tarjeta:'Tarjeta 💳', fiado:'Fiado 🤝'}[tipoPago] || tipoPago;

        let filas = '';
        items.forEach(i => {
            const sub = i.precio * i.cantidad;
            filas += `<tr>
                <td>${i.nombre}</td>
                <td class="text-center">${i.cantidad}</td>
                <td class="text-end">$${parseFloat(i.precio).toFixed(2)}</td>
                <td class="text-end">$${sub.toFixed(2)}</td>
            </tr>`;
        });

        const html = `
        <div class="text-center mb-3">
            <div style="background:linear-gradient(135deg,#6366f1,#7c3aed);color:#fff;padding:14px;border-radius:8px;margin-bottom:10px;">
                <i class="fas fa-check-circle fa-2x mb-1 d-block"></i>
                <strong>¡Venta Registrada!</strong>
            </div>
            <div class="text-muted small">N° ${numVenta} &nbsp;|&nbsp; ${fecha}</div>
        </div>
        <table class="table table-sm">
            <thead class="table-light"><tr><th>Producto</th><th class="text-center">Qty</th><th class="text-end">Precio</th><th class="text-end">Total</th></tr></thead>
            <tbody>${filas}</tbody>
            <tfoot>
                ${descuento > 0 ? `<tr><td colspan="3" class="text-end text-muted small">Subtotal</td><td class="text-end text-muted small">$${subtotal.toFixed(2)}</td></tr>
                <tr><td colspan="3" class="text-end text-muted small">Descuento</td><td class="text-end text-danger small">-$${descuento.toFixed(2)}</td></tr>` : ''}
                <tr class="table-light">
                    <td colspan="3" class="text-end fw-bold">TOTAL USD</td>
                    <td class="text-end fw-bold text-primary">$${total.toFixed(2)}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end text-muted small">Total Bs</td>
                    <td class="text-end fw-bold text-success small">Bs ${totalBs.toLocaleString('es-VE',{minimumFractionDigits:2,maximumFractionDigits:2})}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end text-muted small">Método de Pago</td>
                    <td class="text-end small">${pagoLabel}</td>
                </tr>
            </tfoot>
        </table>`;

        // Inyectar en modal de ticket (crearlo si no existe)
        let modal = document.getElementById('ticketModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id        = 'ticketModal';
            modal.className = 'modal fade';
            modal.tabIndex  = -1;
            modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header py-2" style="background:linear-gradient(135deg,#6366f1,#7c3aed);">
                        <h6 class="modal-title text-white"><i class="fas fa-receipt me-2"></i>Ticket de Venta</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="ticketBody"></div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Imprimir
                        </button>
                    </div>
                </div>
            </div>`;
            document.body.appendChild(modal);
        }
        document.getElementById('ticketBody').innerHTML = html;
        new bootstrap.Modal(modal).show();
    }
}

// NO instanciar aquí — lo hace la vista vía DOMContentLoaded
