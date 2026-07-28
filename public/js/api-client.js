/**
 * ===================================
 * API Client - Cliente HTTP con Fetch
 * ===================================
 * Centraliza todas las llamadas AJAX a la API
 */

class APIClient {
    constructor(baseURL = '') {
        this.baseURL = baseURL || (typeof APP_URL !== 'undefined' ? APP_URL : window.location.pathname.replace(/\/$/, ''));
        this.timeout = 30000; // 30 segundos
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
    }

    /**
     * Realizar solicitud HTTP genérica
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        
        const fetchOptions = {
            method: options.method || 'GET',
            headers: { ...this.defaultHeaders, ...options.headers },
            credentials: 'same-origin' // Incluir cookies de sesión
        };

        // Agregar body si existe
        if (options.body) {
            fetchOptions.body = typeof options.body === 'string' 
                ? options.body 
                : JSON.stringify(options.body);
        }

        try {
            // Crear promesa con timeout
            const fetchPromise = fetch(url, fetchOptions);
            const timeoutPromise = new Promise((_, reject) =>
                setTimeout(() => reject(new Error('Request timeout')), this.timeout)
            );

            const response = await Promise.race([fetchPromise, timeoutPromise]);

            // Verificar autenticación
            if (response.status === 401) {
                this.handleUnauthorized();
                throw new Error('No autorizado');
            }

            // Obtener respuesta JSON
            const raw = await response.json().catch(() => ({}));

            // Los controladores devuelven {success, message, data: {...}}
            // aplanamos para que r.data sea directamente el payload
            const payload = (raw && raw.success !== undefined && raw.data !== undefined)
                ? raw.data
                : raw;

            // Retornar respuesta completa
            return {
                status: response.status,
                ok:     response.ok,
                data:   payload,
                error:  !response.ok ? (raw.error || raw.message || 'Error en la solicitud') : null
            };
        } catch (error) {
            console.error('API Error:', error);
            return {
                status: 0,
                ok: false,
                data: null,
                error: error.message || 'Error de conexión'
            };
        }
    }

    /**
     * GET - Obtener datos
     */
    async get(endpoint, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'GET'
        });
    }

    /**
     * POST - Crear datos
     */
    async post(endpoint, body, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'POST',
            body
        });
    }

    /**
     * PUT - Actualizar datos
     */
    async put(endpoint, body, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'PUT',
            body
        });
    }

    /**
     * DELETE - Eliminar datos
     */
    async delete(endpoint, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'DELETE'
        });
    }

    /**
     * Manejar errores de autenticación
     */
    handleUnauthorized() {
        console.warn('Sesión expirada. Redirigiendo al login...');
        window.location.href = `${this.baseURL}/login`;
    }

    /**
     * Construir URL con parámetros
     */
    buildQueryString(params) {
        const query = new URLSearchParams();
        for (const [key, value] of Object.entries(params)) {
            if (value !== null && value !== undefined) {
                query.append(key, value);
            }
        }
        return query.toString() ? `?${query.toString()}` : '';
    }
}

// Crear instancia global
const api = new APIClient();
