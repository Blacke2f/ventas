/**
 * ===================================
 * ventas.js - Funciones de Ventas
 * ===================================
 */

class VentasAPI {
    constructor() {
        this.baseUrl = '/api/ventas';
    }

    /**
     * Obtener ventas
     */
    async list() {
        return await api.get(this.baseUrl + '?action=list');
    }

    /**
     * Obtener una venta
     */
    async getOne(id) {
        return await api.get(this.baseUrl + '?action=get&id=' + id);
    }

    /**
     * Obtener ventas del día
     */
    async getHoy() {
        return await api.get(this.baseUrl + '?action=hoy');
    }

    /**
     * Obtener ventas por rango de fechas
     */
    async getByDateRange(inicio, fin, tipoPago = null) {
        let url = this.baseUrl + '?action=rango&inicio=' + inicio + '&fin=' + fin;
        if (tipoPago) {
            url += '&tipo_pago=' + tipoPago;
        }
        return await api.get(url);
    }

    /**
     * Obtener ventas del usuario
     */
    async getByUsuario(id) {
        return await api.get(this.baseUrl + '?action=usuario&id=' + id);
    }

    /**
     * Obtener ventas del cliente
     */
    async getByCliente(id) {
        return await api.get(this.baseUrl + '?action=cliente&id=' + id);
    }

    /**
     * Obtener ventas a crédito
     */
    async getVentasCredito() {
        return await api.get(this.baseUrl + '?action=credito');
    }

    /**
     * Obtener resumen diario
     */
    async getResumenDiario(fecha = null) {
        let url = this.baseUrl + '?action=resumen-diario';
        if (fecha) {
            url += '&fecha=' + fecha;
        }
        return await api.get(url);
    }

    /**
     * Obtener resumen por período
     */
    async getResumenPeriodo(inicio, fin) {
        return await api.get(this.baseUrl + '?action=resumen-periodo&inicio=' + inicio + '&fin=' + fin);
    }

    /**
     * Crear venta — payload atómico {tipo_pago, total, ..., items:[...]}
     */
    async create(data) {
        // Envuelve el payload en el formato que espera el controller
        const body = data.items ? data : { ...data };
        return await api.post(this.baseUrl + '?action=create', body);
    }

    /**
     * Agregar item a venta
     */
    async addItem(idVenta, idProducto, cantidad, precioUnitario) {
        return await api.post(this.baseUrl + '?action=add-item', {
            id_venta: idVenta,
            id_producto: idProducto,
            cantidad: cantidad,
            precio_unitario: precioUnitario
        });
    }

    /**
     * Cancelar venta
     */
    async cancel(id, motivo = null) {
        return await api.put(this.baseUrl + '?action=cancel&id=' + id, {
            motivo: motivo
        });
    }
}

// Instancia global
const ventasAPI = new VentasAPI();
