/**
 * ===================================
 * creditos.js - Funciones de Créditos/Fiados
 * ===================================
 */

class CreditosAPI {
    constructor() {
        this.baseUrl = '/api/creditos';
    }

    /**
     * Obtener un crédito
     */
    async getOne(id) {
        return await api.get(this.baseUrl + '?action=get&id=' + id);
    }

    /**
     * Obtener créditos del cliente
     */
    async getByCliente(id) {
        return await api.get(this.baseUrl + '?action=cliente&id=' + id);
    }

    /**
     * Obtener créditos vencidos
     */
    async getVencidos() {
        return await api.get(this.baseUrl + '?action=vencidos');
    }

    /**
     * Obtener resumen de pendientes
     */
    async getResumen() {
        return await api.get(this.baseUrl + '?action=resumen');
    }

    /**
     * Obtener cartera de créditos
     */
    async getCartera() {
        return await api.get(this.baseUrl + '?action=cartera');
    }

    /**
     * Obtener estadísticas de créditos
     */
    async getEstadisticas() {
        return await api.get(this.baseUrl + '?action=estadisticas');
    }

    /**
     * Validar si cliente puede fiar
     */
    async validarCliente(idCliente, monto) {
        return await api.get(this.baseUrl + '?action=validar-cliente&id_cliente=' + idCliente + '&monto=' + monto);
    }

    /**
     * Crear crédito
     */
    async create(data) {
        return await api.post(this.baseUrl + '?action=create', data);
    }

    /**
     * Agregar abono
     */
    async addAbono(idCredito, montoAbono, metodoPago, notas = null) {
        return await api.post(this.baseUrl + '?action=abono', {
            id_credito: idCredito,
            monto_abono: montoAbono,
            metodo_pago: metodoPago,
            notas: notas
        });
    }
}

// Instancia global
const creditosAPI = new CreditosAPI();
