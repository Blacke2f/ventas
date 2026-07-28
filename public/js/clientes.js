/**
 * ===================================
 * clientes.js - Funciones de Clientes
 * ===================================
 */

class ClientesAPI {
    constructor() {
        this.baseUrl = '/api/clientes';
    }

    /**
     * Obtener todos los clientes
     */
    async list() {
        return await api.get(this.baseUrl + '?action=list');
    }

    /**
     * Obtener un cliente
     */
    async getOne(id) {
        return await api.get(this.baseUrl + '?action=get&id=' + id);
    }

    /**
     * Buscar clientes
     */
    async search(query) {
        return await api.get(this.baseUrl + '?action=search&q=' + encodeURIComponent(query));
    }

    /**
     * Obtener detalles completos
     */
    async getDetalles(id) {
        return await api.get(this.baseUrl + '?action=detalles&id=' + id);
    }

    /**
     * Obtener estadísticas del cliente
     */
    async getEstadisticas(id) {
        return await api.get(this.baseUrl + '?action=estadisticas&id=' + id);
    }

    /**
     * Obtener clientes con deuda
     */
    async getClientesConDeuda() {
        return await api.get(this.baseUrl + '?action=con-deuda');
    }

    /**
     * Obtener top clientes por gasto
     */
    async getTopClientes(limit = 10) {
        return await api.get(this.baseUrl + '?action=top&limit=' + limit);
    }

    /**
     * Crear cliente
     */
    async create(data) {
        return await api.post(this.baseUrl + '?action=create', data);
    }

    /**
     * Actualizar cliente
     */
    async update(id, data) {
        return await api.put(this.baseUrl + '?action=update&id=' + id, data);
    }

    /**
     * Eliminar/desactivar cliente (admin only)
     */
    async delete(id) {
        return await api.delete(this.baseUrl + '?action=delete&id=' + id);
    }
}

// Instancia global
const clientesAPI = new ClientesAPI();
