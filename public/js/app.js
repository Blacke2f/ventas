/**
 * ===================================
 * App.js - Script Principal
 * ===================================
 * Maneja la lógica global de la aplicación
 */

class AbasPOS {
    constructor() {
        this.currentUser = null;
        this.initialized = false;
        this.init();
    }

    /**
     * Inicializar la aplicación
     */
    async init() {
        try {
            // Configurar escuchadores globales
            this.setupEventListeners();
            
            // Cargar datos del usuario (opcional, no bloquea)
            this.loadUserProfile().catch(err => {
                console.log('Perfil de usuario no cargado (normal en algunas vistas)');
            });
            
            // Marcar como inicializado
            this.initialized = true;
            
            console.log('AbasPOS iniciado correctamente');
        } catch (error) {
            console.error('Error al inicializar AbasPOS:', error);
            // No llamar handleInitError para no mostrar error al usuario
        }
    }

    /**
     * Cargar perfil del usuario actual
     */
    async loadUserProfile() {
        // Solo intentar cargar si no estamos en la página de login
        if (window.location.pathname.includes('/login')) {
            return false;
        }

        try {
            const response = await api.get('/api/auth/profile');
            
            if (response.ok && response.data && response.data.usuario) {
                this.currentUser = response.data.usuario;
                console.log('Usuario cargado:', this.currentUser.nombre_completo || this.currentUser.nombre_usuario);
                return true;
            }
        } catch (error) {
            console.log('No se pudo cargar el perfil del usuario');
        }
        
        return false;
    }

    /**
     * Configurar escuchadores globales
     */
    setupEventListeners() {
        // Logout
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }

        // Verificar conexión periódicamente (cada 5 minutos)
        setInterval(() => this.checkConnection(), 300000);
    }

    /**
     * Logout - Cerrar sesión
     */
    async logout() {
        if (confirm('¿Deseas cerrar sesión?')) {
            try {
                await api.post('/api/auth/logout', {});
            } catch (error) {
                console.log('Error en logout:', error);
            }
            
            // Siempre redirigir al login
            Utils.showToast('Sesión cerrada correctamente', 'success', 1000);
            setTimeout(() => {
                window.location.href = APP_URL + '/login';
            }, 500);
        }
    }

    /**
     * Verificar conexión a internet
     */
    async checkConnection() {
        const hasConnection = await Utils.checkConnection();
        
        if (!hasConnection) {
            console.warn('Sin conexión detectada');
        }
    }

    /**
     * Obtener usuario actual
     */
    getCurrentUser() {
        return this.currentUser;
    }

    /**
     * Verificar permisos
     */
    hasPermission(permission) {
        if (!this.currentUser) return false;
        
        // Admin tiene todos los permisos
        if (this.currentUser.rol === 'admin') {
            return true;
        }
        
        // Lógica específica de permisos por rol
        const permissions = {
            'cajero': ['sales', 'customers', 'credits'],
            'admin': ['sales', 'customers', 'credits', 'products', 'users', 'reports', 'settings']
        };
        
        return permissions[this.currentUser.rol]?.includes(permission) || false;
    }
}

// Inicializar cuando el documento esté listo
let abaspos;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        abaspos = new AbasPOS();
    });
} else {
    abaspos = new AbasPOS();
}

// Exportar a global
window.abaspos = abaspos;
// Mantener compatibilidad con nombre anterior
window.gastropos = abaspos;
