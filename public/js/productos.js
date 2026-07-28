/**
 * ===================================
 * productos.js - Funciones de Productos
 * ===================================
 */

class ProductosAPI {
    constructor() {
        this.baseUrl = '/api/productos';
    }

    /**
     * Obtener todos los productos
     */
    async list() {
        return await api.get(this.baseUrl + '?action=list');
    }

    /**
     * Obtener un producto
     */
    async getOne(id) {
        return await api.get(this.baseUrl + '?action=get&id=' + id);
    }

    /**
     * Buscar productos
     */
    async search(query) {
        return await api.get(this.baseUrl + '?action=search&q=' + encodeURIComponent(query));
    }

    /**
     * Obtener por categoría
     */
    async getByCategoria(idCategoria) {
        return await api.get(this.baseUrl + '?action=categoria&id=' + idCategoria);
    }

    /**
     * Obtener categorías
     */
    async getCategorias() {
        return await api.get(this.baseUrl + '?action=categorias');
    }

    /**
     * Buscar por código de barras
     */
    async findByBarcode(codigo) {
        return await api.get(this.baseUrl + '?action=barcode&codigo=' + encodeURIComponent(codigo));
    }

    /**
     * Obtener productos con stock bajo
     */
    async getStockBajo() {
        return await api.get(this.baseUrl + '?action=stock-bajo');
    }

    /**
     * Obtener top vendidos
     */
    async getTopVendidos(limit = 10) {
        return await api.get(this.baseUrl + '?action=top-vendidos&limit=' + limit);
    }

    /**
     * Obtener grid de productos (para POS)
     */
    async getProductosGrid(pagina = 1, porPagina = 12) {
        return await api.get(this.baseUrl + '?action=grid&pagina=' + pagina + '&por_pagina=' + porPagina);
    }

    /**
     * Crear producto (admin only)
     */
    async create(data) {
        return await api.post(this.baseUrl + '?action=create', data);
    }

    /**
     * Actualizar producto (admin only)
     */
    async update(id, data) {
        return await api.put(this.baseUrl + '?action=update&id=' + id, data);
    }

    /**
     * Actualizar stock (admin only)
     */
    async updateStock(id, cantidad, operacion = 'restar') {
        return await api.put(this.baseUrl + '?action=stock&id=' + id, {
            cantidad: cantidad,
            operacion: operacion
        });
    }

    /**
     * Eliminar/desactivar producto (admin only)
     */
    async delete(id) {
        return await api.delete(this.baseUrl + '?action=delete&id=' + id);
    }

    /**
     * Crear categoría (admin only)
     */
    async createCategoria(data) {
        return await api.post(this.baseUrl + '?action=categoria-create', data);
    }
}

// Instancia global
const productosAPI = new ProductosAPI();
