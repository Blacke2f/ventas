/**
 * ===================================
 * Cálculo Automático de Precios
 * ===================================
 * Calcula precio de venta basado en costo y porcentaje de ganancia
 */

class CalculadoraPrecios {
    constructor() {
        this.setupEventListeners();
    }

    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Detectar cambios en campos de costo
        document.addEventListener('input', (e) => {
            if (e.target.matches('#precio_mayoreo, #unidades_por_bulto, #porcentaje_ganancia')) {
                this.calcularPrecioVenta();
            }
        });

        // Detectar cambios en precio de costo directo
        document.addEventListener('input', (e) => {
            if (e.target.matches('#precio_costo, #porcentaje_ganancia_directo')) {
                this.calcularPrecioVentaDirecto();
            }
        });
    }

    /**
     * Calcular precio de venta desde bulto/paquete
     */
    calcularPrecioVenta() {
        const precioMayoreo = parseFloat(document.getElementById('precio_mayoreo')?.value || 0);
        const unidadesBulto = parseFloat(document.getElementById('unidades_por_bulto')?.value || 1);
        const porcentajeGanancia = parseFloat(document.getElementById('porcentaje_ganancia')?.value || 0);

        if (precioMayoreo > 0 && unidadesBulto > 0) {
            // Calcular costo unitario
            const precioUnitario = precioMayoreo / unidadesBulto;
            
            // Aplicar porcentaje de ganancia
            const ganancia = precioUnitario * (porcentajeGanancia / 100);
            const precioVenta = precioUnitario + ganancia;

            // Mostrar resultados
            this.mostrarResultados({
                costo: precioUnitario,
                ganancia: ganancia,
                precioVenta: precioVenta,
                porcentaje: porcentajeGanancia
            });

            // Actualizar campo de precio de venta
            const inputPrecioVenta = document.getElementById('precio_venta');
            if (inputPrecioVenta) {
                inputPrecioVenta.value = precioVenta.toFixed(2);
            }

            // Actualizar campo de precio costo
            const inputPrecioCosto = document.getElementById('precio_costo');
            if (inputPrecioCosto) {
                inputPrecioCosto.value = precioUnitario.toFixed(2);
            }
        }
    }

    /**
     * Calcular precio de venta desde costo directo
     */
    calcularPrecioVentaDirecto() {
        const precioCosto = parseFloat(document.getElementById('precio_costo')?.value || 0);
        const porcentaje = parseFloat(document.getElementById('porcentaje_ganancia_directo')?.value || 0);

        if (precioCosto > 0) {
            const ganancia = precioCosto * (porcentaje / 100);
            const precioVenta = precioCosto + ganancia;

            // Actualizar campo de precio de venta
            const inputPrecioVenta = document.getElementById('precio_venta');
            if (inputPrecioVenta) {
                inputPrecioVenta.value = precioVenta.toFixed(2);
            }

            // Mostrar en preview
            this.mostrarResultadosDirecto({
                costo: precioCosto,
                ganancia: ganancia,
                precioVenta: precioVenta,
                porcentaje: porcentaje
            });
        }
    }

    /**
     * Mostrar resultados del cálculo (desde bulto)
     */
    mostrarResultados(datos) {
        const container = document.getElementById('calculo-preview');
        if (!container) return;

        const preciosBs = tasaCambio ? {
            costo: tasaCambio.usdToBs(datos.costo),
            ganancia: tasaCambio.usdToBs(datos.ganancia),
            precioVenta: tasaCambio.usdToBs(datos.precioVenta)
        } : null;

        container.innerHTML = `
            <div class="alert alert-info">
                <h6 class="mb-3"><i class="fas fa-calculator"></i> Cálculo de Precio</h6>
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Costo Unitario</small>
                        <div class="fw-bold">$${datos.costo.toFixed(2)}</div>
                        ${preciosBs ? `<small class="text-muted">Bs ${preciosBs.costo.toFixed(2)}</small>` : ''}
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Ganancia (${datos.porcentaje}%)</small>
                        <div class="fw-bold text-success">+$${datos.ganancia.toFixed(2)}</div>
                        ${preciosBs ? `<small class="text-muted">Bs ${preciosBs.ganancia.toFixed(2)}</small>` : ''}
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Precio de Venta</small>
                        <div class="fw-bold text-primary" style="font-size: 1.2rem;">$${datos.precioVenta.toFixed(2)}</div>
                        ${preciosBs ? `<small class="text-muted">Bs ${preciosBs.precioVenta.toFixed(2)}</small>` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Mostrar resultados del cálculo (directo)
     */
    mostrarResultadosDirecto(datos) {
        const container = document.getElementById('calculo-preview-directo');
        if (!container) return;

        const preciosBs = tasaCambio ? {
            costo: tasaCambio.usdToBs(datos.costo),
            ganancia: tasaCambio.usdToBs(datos.ganancia),
            precioVenta: tasaCambio.usdToBs(datos.precioVenta)
        } : null;

        container.innerHTML = `
            <div class="alert alert-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Precio Final</small>
                        <div class="fw-bold" style="font-size: 1.3rem;">$${datos.precioVenta.toFixed(2)}</div>
                        ${preciosBs ? `<small>Bs ${preciosBs.precioVenta.toFixed(2)}</small>` : ''}
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Ganancia</small>
                        <div class="text-success fw-bold">+$${datos.ganancia.toFixed(2)}</div>
                        <small class="text-muted">(${datos.porcentaje}%)</small>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Calcular precio sugerido basado en mercado
     */
    calcularPrecioSugerido(categoria) {
        // Porcentajes sugeridos por categoría
        const porcentajesPorCategoria = {
            'Abarrotes': 25,
            'Enlatados': 30,
            'Lácteos': 20,
            'Botanas': 35,
            'Confitería': 40,
            'Harinas y Pan': 25,
            'Frutas y Verduras': 50,
            'Bebidas': 30,
            'Bebidas Alcohólicas': 25,
            'Carnes y Embutidos': 20,
            'Automedicación': 30,
            'Higiene Personal': 35,
            'Uso Doméstico': 30,
            'Helados': 40,
            'Jarcería': 35
        };

        return porcentajesPorCategoria[categoria] || 30;
    }

    /**
     * Mostrar sugerencia de porcentaje
     */
    mostrarSugerencia(categoria) {
        const porcentajeSugerido = this.calcularPrecioSugerido(categoria);
        const container = document.getElementById('sugerencia-porcentaje');
        
        if (container) {
            container.innerHTML = `
                <small class="text-info">
                    <i class="fas fa-lightbulb"></i> 
                    Sugerencia para ${categoria}: ${porcentajeSugerido}%
                    <a href="#" onclick="aplicarPorcentajeSugerido(${porcentajeSugerido}); return false;" class="ms-2">
                        Aplicar
                    </a>
                </small>
            `;
        }
    }
}

/**
 * Aplicar porcentaje sugerido
 */
function aplicarPorcentajeSugerido(porcentaje) {
    const inputPorcentaje = document.getElementById('porcentaje_ganancia');
    if (inputPorcentaje) {
        inputPorcentaje.value = porcentaje;
        inputPorcentaje.dispatchEvent(new Event('input'));
    }
}

/**
 * Toggle entre modos de cálculo
 */
function toggleModoPrecio(modo) {
    const modoBulto = document.getElementById('modo-bulto');
    const modoDirecto = document.getElementById('modo-directo');

    if (modo === 'bulto') {
        modoBulto?.classList.remove('d-none');
        modoDirecto?.classList.add('d-none');
    } else {
        modoBulto?.classList.add('d-none');
        modoDirecto?.classList.remove('d-none');
    }
}

// Instancia global
let calculadoraPrecios;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    calculadoraPrecios = new CalculadoraPrecios();
});
