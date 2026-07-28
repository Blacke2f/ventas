/**
 * ===================================
 * Tasa de Cambio - Gestión USD/Bs
 * ===================================
 */

class TasaCambio {
    constructor() {
        this.tasa = 567.68; // Se actualiza automáticamente desde la API
        this.lastUpdate = null;
        this.loadTasa();
    }

    /**
     * Cargar tasa de cambio
     */
    async loadTasa() {
        try {
            const response = await fetch(`${APP_URL}/api-tasa-cambio.php`);
            const data = await response.json();
            
            if (data.success) {
                this.tasa = parseFloat(data.tasa);
                this.lastUpdate = data.ultima_actualizacion;
                this.updateUI();
                
                // Guardar en localStorage
                localStorage.setItem('tasa_cambio', JSON.stringify({
                    tasa: this.tasa,
                    timestamp: Date.now()
                }));
            }
        } catch (error) {
            console.error('Error cargando tasa:', error);
            this.loadFromCache();
        }
    }

    /**
     * Cargar desde caché
     */
    loadFromCache() {
        const cached = localStorage.getItem('tasa_cambio');
        if (cached) {
            const data = JSON.parse(cached);
            // Si el caché tiene menos de 1 hora
            if (Date.now() - data.timestamp < 3600000) {
                this.tasa = data.tasa;
            }
        }
    }

    /**
     * Convertir USD a Bs
     */
    usdToBs(usd) {
        return usd * this.tasa;
    }

    /**
     * Convertir Bs a USD
     */
    bsToUsd(bs) {
        return bs / this.tasa;
    }

    /**
     * Formatear USD
     */
    formatUSD(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    /**
     * Formatear Bs
     */
    formatBs(amount) {
        return new Intl.NumberFormat('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount) + ' Bs';
    }

    /**
     * Actualizar UI
     */
    updateUI() {
        // Actualizar widgets de tasa
        const rateWidgets = document.querySelectorAll('.rate-value');
        rateWidgets.forEach(widget => {
            widget.textContent = this.formatBs(this.tasa);
        });

        // Actualizar timestamp
        const updateWidgets = document.querySelectorAll('.rate-update');
        updateWidgets.forEach(widget => {
            widget.textContent = this.lastUpdate || 'Hace un momento';
        });

        // Actualizar todos los precios duales
        this.updateAllPrices();
    }

    /**
     * Actualizar todos los precios en la página
     */
    updateAllPrices() {
        const priceElements = document.querySelectorAll('[data-price-usd]');
        priceElements.forEach(element => {
            const usd = parseFloat(element.dataset.priceUsd);
            const bs = this.usdToBs(usd);
            
            // Actualizar precio en Bs
            const bsElement = element.querySelector('.price-bs');
            if (bsElement) {
                bsElement.textContent = this.formatBs(bs);
            }
        });
    }

    /**
     * Forzar actualización
     */
    async forceUpdate() {
        const btn = document.getElementById('btn-refresh-rate');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        }

        try {
            const response = await fetch(`${APP_URL}/api-tasa-cambio.php?force=1`);
            const data = await response.json();
            
            if (data.success) {
                this.tasa = parseFloat(data.tasa);
                this.lastUpdate = data.ultima_actualizacion;
                this.updateUI();
                
                Utils.showToast('Tasa actualizada: Bs ' + this.tasa.toLocaleString('es-VE', {minimumFractionDigits:2}), 'success');
            }
        } catch (error) {
            console.error('Error actualizando tasa:', error);
            Utils.showToast('Error al actualizar la tasa BCV', 'warning');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt"></i>';
            }
        }
    }
}

// Instancia global
let tasaCambio;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    tasaCambio = new TasaCambio();
    
    // Configurar botón de actualización
    const btnRefresh = document.getElementById('btn-refresh-rate');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', () => {
            tasaCambio.forceUpdate();
        });
    }
    
    // Actualizar cada 30 minutos
    setInterval(() => {
        tasaCambio.loadTasa();
    }, 1800000);
});

/**
 * Función helper para mostrar precios duales
 */
function showDualPrice(usd) {
    const bs = tasaCambio ? tasaCambio.usdToBs(usd) : usd * 567.68;
    return {
        usd: tasaCambio ? tasaCambio.formatUSD(usd) : `$${usd.toFixed(2)}`,
        bs: tasaCambio ? tasaCambio.formatBs(bs) : `Bs ${bs.toFixed(2)}`
    };
}
