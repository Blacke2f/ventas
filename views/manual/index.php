<?php
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . '/login');
    exit;
}
$pageTitle = 'Manual de Uso';
ob_start();
?>

<style>
.manual-section   { scroll-margin-top: 80px; }
.manual-nav a     { font-size: .82rem; color: #64748b; text-decoration: none; display: block; padding: 5px 12px; border-radius: 6px; }
.manual-nav a:hover, .manual-nav a.active { background: #eff6ff; color: #6366f1; font-weight: 600; }
.step-badge       { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg,#6366f1,#7c3aed); color: #fff; font-size: .8rem; font-weight: 700; flex-shrink: 0; }
.tip-box          { background: #eff6ff; border-left: 4px solid #6366f1; border-radius: 0 8px 8px 0; padding: 12px 16px; margin: 12px 0; font-size: .875rem; }
.warn-box         { background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 0 8px 8px 0; padding: 12px 16px; margin: 12px 0; font-size: .875rem; }
.key-box          { display: inline-block; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 5px; padding: 1px 7px; font-family: monospace; font-size: .82rem; color: #1e293b; }
.section-title    { font-size: 1.1rem; font-weight: 800; color: #1e293b; padding-bottom: 8px; border-bottom: 2px solid #e2e8f0; margin-bottom: 16px; }
.screen-mock      { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin: 12px 0; font-size: .82rem; }
</style>

<div class="row g-3">

    <!-- Índice lateral (sticky) -->
    <div class="col-lg-3 d-none d-lg-block">
        <div class="card border-0 shadow-sm" style="position:sticky;top:74px;">
            <div class="card-header" style="font-size:.82rem;font-weight:700;">
                <i class="fas fa-book-open me-2"></i>Contenido
            </div>
            <div class="card-body p-2 manual-nav" id="manualNav">
                <a href="#inicio">🏠 Inicio y Login</a>
                <a href="#dashboard">📊 Dashboard</a>
                <a href="#pos">🛒 Punto de Venta</a>
                <a href="#productos">📦 Productos</a>
                <a href="#clientes">👥 Clientes</a>
                <a href="#creditos">🤝 Créditos / Fiados</a>
                <a href="#ventas">🧾 Historial de Ventas</a>
                <a href="#reportes">📈 Reportes</a>
                <a href="#configuracion">⚙️ Configuración</a>
                <a href="#tasa">💱 Tasa de Cambio</a>
                <a href="#tips">💡 Consejos</a>
            </div>
        </div>
    </div>

    <!-- Contenido del manual -->
    <div class="col-lg-9">

        <!-- Encabezado -->
        <div class="card border-0 shadow-sm mb-4"
             style="background:linear-gradient(135deg,#6366f1,#7c3aed);color:#fff;border-radius:12px;">
            <div class="card-body py-4 px-4">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-store fa-3x opacity-75"></i>
                    <div>
                        <h2 class="mb-1 fw-bold">Manual de Uso — AbasPOS</h2>
                        <p class="mb-0 opacity-75">Sistema de Punto de Venta para Abastos y Tiendas de Abarrotes</p>
                        <small class="opacity-50">Versión 1.0 · Venezuela 🇻🇪</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN 1: LOGIN ══════════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="inicio">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-sign-in-alt text-primary me-2"></i>1. Inicio y Login
                </div>

                <p>Al abrir el sistema por primera vez verás la pantalla de inicio de sesión.</p>

                <div class="screen-mock">
                    <strong>Pantalla de login:</strong><br>
                    <span class="text-muted">📧 Usuario:</span> <span class="key-box">admin</span> o <span class="key-box">cajero1</span><br>
                    <span class="text-muted">🔒 Contraseña:</span> <span class="key-box">password</span>
                </div>

                <div class="d-flex flex-column gap-2">
                    <div class="d-flex gap-2">
                        <span class="step-badge">1</span>
                        <div>Escribe tu nombre de usuario en el campo <strong>Usuario</strong>.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="step-badge">2</span>
                        <div>Escribe tu contraseña. Puedes hacer clic en el ojo <i class="fas fa-eye"></i> para verla.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="step-badge">3</span>
                        <div>Haz clic en <strong>Iniciar Sesión</strong>. Serás llevado al Dashboard.</div>
                    </div>
                </div>

                <div class="tip-box mt-3">
                    <i class="fas fa-lightbulb text-primary me-2"></i>
                    <strong>Tip:</strong> Marca "Recuerda mis datos" para no escribir tu contraseña cada vez.
                </div>

                <h6 class="mt-3 fw-bold">Tipos de usuario</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>Tipo</th><th>Puede hacer</th><th>No puede hacer</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-danger">Admin</span></td>
                                <td>Todo: ventas, productos, clientes, reportes, configuración</td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-info">Cajero</span></td>
                                <td>Ventas, clientes, créditos, reportes</td>
                                <td>Gestionar productos, configuración</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN 2: DASHBOARD ═════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="dashboard">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-chart-bar text-success me-2"></i>2. Dashboard (Pantalla Principal)
                </div>

                <p>El Dashboard es lo primero que ves después de iniciar sesión. Te muestra un resumen del día.</p>

                <h6 class="fw-bold">¿Qué significan los números?</h6>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#eff6ff;">
                            <div class="fw-bold text-primary">Ventas Hoy</div>
                            <div class="small text-muted">Cantidad de ventas realizadas hoy. Si tiene "+2 vs ayer", significa que hoy vendiste 2 más que ayer.</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#f0fdf4;">
                            <div class="fw-bold text-success">Total Vendido</div>
                            <div class="small text-muted">Suma de dinero vendido hoy en dólares y bolívares.</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#fffbeb;">
                            <div class="fw-bold text-warning">Créditos Vencidos</div>
                            <div class="small text-muted">Clientes que no pagaron su deuda a tiempo. Haz clic en "Ver créditos" para cobrarles.</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#fef2f2;">
                            <div class="fw-bold text-danger">Clientes con Deuda</div>
                            <div class="small text-muted">Cantidad de clientes que deben dinero actualmente.</div>
                        </div>
                    </div>
                </div>

                <div class="warn-box">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    <strong>Alerta de Stock Bajo:</strong> Si aparece una barra amarilla con nombres de productos, significa que esos productos se están agotando. Ve a <strong>Productos</strong> para agregar más stock.
                </div>

                <h6 class="fw-bold mt-3">Tasa de Cambio BCV</h6>
                <p class="small">En la parte superior del Dashboard verás el valor del dólar en bolívares (Bs), obtenido automáticamente del BCV. Haz clic en <strong>Actualizar</strong> para obtener la tasa más reciente.</p>
            </div>
        </div>

        <!-- ══ SECCIÓN 3: POS ════════════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="pos">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-cash-register text-warning me-2"></i>3. Punto de Venta (POS)
                </div>

                <p>El Punto de Venta es donde registras las ventas. Se accede desde el menú lateral o el botón <strong>"Nueva Venta"</strong> del Dashboard.</p>

                <h6 class="fw-bold">Cómo hacer una venta</h6>
                <div class="d-flex flex-column gap-3 mb-3">
                    <div class="d-flex gap-3 align-items-start">
                        <span class="step-badge">1</span>
                        <div>
                            <strong>Busca el producto</strong><br>
                            <span class="small text-muted">Usa el buscador arriba a la derecha, o filtra por categoría (Abarrotes, Bebidas, etc.). También puedes escanear un código de barras.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <span class="step-badge">2</span>
                        <div>
                            <strong>Haz clic en el producto</strong><br>
                            <span class="small text-muted">Aparece un modal con el precio en USD y Bs. Indica la cantidad y haz clic en <strong>"Agregar"</strong>.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <span class="step-badge">3</span>
                        <div>
                            <strong>Revisa el carrito</strong><br>
                            <span class="small text-muted">En el panel derecho verás los productos agregados. Puedes usar los botones <strong>+</strong> y <strong>−</strong> para cambiar cantidades, o la <strong>X</strong> para eliminar.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <span class="step-badge">4</span>
                        <div>
                            <strong>Aplica descuento (opcional)</strong><br>
                            <span class="small text-muted">En el campo <strong>Descuento</strong>, escribe el porcentaje de descuento (ejemplo: <span class="key-box">10</span> para 10%). El monto se calcula automáticamente.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <span class="step-badge">5</span>
                        <div>
                            <strong>Selecciona el cliente (opcional)</strong><br>
                            <span class="small text-muted">Si es un cliente registrado, selecciónalo. Para ventas al público en general, déjalo como "Público General".</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <span class="step-badge">6</span>
                        <div>
                            <strong>Elige la forma de pago y cobra</strong><br>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <span class="badge bg-success">Efectivo</span>
                                <span class="badge bg-info">Tarjeta</span>
                                <span class="badge bg-warning text-dark">Fiado (crédito)</span>
                            </div>
                            <span class="small text-muted d-block mt-1">Si es <strong>Fiado</strong>, debes seleccionar un cliente primero.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <span class="step-badge">7</span>
                        <div>
                            <strong>Ticket de venta</strong><br>
                            <span class="small text-muted">Aparecerá un ticket con el resumen. Puedes imprimirlo con el botón <strong>Imprimir</strong>.</span>
                        </div>
                    </div>
                </div>

                <div class="tip-box">
                    <i class="fas fa-lightbulb text-primary me-2"></i>
                    El sistema actualiza el stock automáticamente al procesar la venta. Si el stock es insuficiente, te avisará antes de cobrar.
                </div>

                <div class="warn-box">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    <strong>No hagas clic dos veces</strong> en el botón de cobrar. El sistema desactiva los botones mientras procesa para evitar ventas duplicadas.
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN 4: PRODUCTOS ══════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="productos">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-box text-info me-2"></i>4. Gestión de Productos <span class="badge bg-danger ms-1" style="font-size:.65rem;">Solo Admin</span>
                </div>

                <h6 class="fw-bold">Agregar un producto nuevo</h6>
                <div class="d-flex flex-column gap-2 mb-3">
                    <div class="d-flex gap-2"><span class="step-badge">1</span><div>Haz clic en <strong>"Nuevo Producto"</strong>.</div></div>
                    <div class="d-flex gap-2"><span class="step-badge">2</span><div>Escribe el nombre y selecciona la categoría.</div></div>
                    <div class="d-flex gap-2">
                        <span class="step-badge">3</span>
                        <div>
                            <strong>Usa la calculadora de precio</strong> para calcular cuánto cobrar:<br>
                            <div class="screen-mock mt-1">
                                <strong>Ejemplo desde Bulto:</strong><br>
                                Un cartón de leche (12 unidades) te costó <span class="key-box">$18.00</span><br>
                                Le quieres ganar un <span class="key-box">30%</span><br>
                                → Precio unitario sugerido: <strong>$1.95</strong><br>
                                → Haz clic en <strong>"Usar este precio"</strong>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2"><span class="step-badge">4</span><div>Ingresa el stock inicial y el stock mínimo (para alertas).</div></div>
                    <div class="d-flex gap-2"><span class="step-badge">5</span><div>Haz clic en <strong>"Guardar Producto"</strong>.</div></div>
                </div>

                <h6 class="fw-bold">Ajustar el stock</h6>
                <p class="small">Cuando recibas mercancía nueva, usa el botón <i class="fas fa-warehouse text-warning"></i> en la tabla de productos para agregar o restar unidades al stock.</p>

                <h6 class="fw-bold">Pestaña "Stock Bajo"</h6>
                <p class="small">Muestra los productos que tienen pocas unidades. La regla: si el stock actual es igual o menor al stock mínimo, aparece aquí.</p>
            </div>
        </div>

        <!-- ══ SECCIÓN 5: CLIENTES ══════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="clientes">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-users text-secondary me-2"></i>5. Clientes
                </div>

                <h6 class="fw-bold">Registrar un cliente nuevo</h6>
                <p class="small">Haz clic en <strong>"Nuevo Cliente"</strong> y llena el formulario. Los campos más importantes son:</p>
                <ul class="small">
                    <li><strong>Nombre completo</strong> — requerido.</li>
                    <li><strong>Teléfono</strong> — para contactarlo cuando tenga deudas.</li>
                    <li><strong>Límite de Fiado</strong> — cuánto crédito máximo puedes darle (en USD). Si lo dejas en 0, no podrá comprar a crédito.</li>
                    <li><strong>Plazo de pago</strong> — cuántos días tiene para pagar su deuda.</li>
                </ul>

                <h6 class="fw-bold">Ver historial de compras</h6>
                <p class="small">Haz clic en el ícono <i class="fas fa-history text-info"></i> junto al cliente para ver todas sus compras anteriores con montos y fechas.</p>

                <h6 class="fw-bold">Cobrar una deuda directamente</h6>
                <p class="small">Si el cliente debe dinero, aparecerá el ícono <i class="fas fa-hand-holding-dollar text-warning"></i>. Haz clic para registrar un abono sin salir de la lista de clientes.</p>

                <div class="tip-box">
                    <i class="fas fa-lightbulb text-primary me-2"></i>
                    La barra de progreso muestra qué porcentaje del límite ha usado el cliente. Si está en rojo, está cerca de su límite.
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN 6: CRÉDITOS ══════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="creditos">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-hand-holding-dollar text-warning me-2"></i>6. Créditos y Fiados
                </div>

                <p>Aquí manejas todas las deudas de los clientes.</p>

                <h6 class="fw-bold">Pestañas disponibles</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>Pestaña</th><th>Qué muestra</th></tr></thead>
                        <tbody>
                            <tr><td><span class="badge bg-primary">Todos los créditos</span></td><td>Todos los clientes con deuda activa, parcial o vencida</td></tr>
                            <tr><td><span class="badge bg-danger">Vencidos</span></td><td>Clientes que no pagaron en el plazo acordado</td></tr>
                            <tr><td><span class="badge bg-success">Resumen</span></td><td>Estadísticas generales: total de deuda, pagados, vencidos</td></tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold mt-3">Registrar un abono</h6>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex gap-2"><span class="step-badge">1</span><div>Busca el cliente en la tabla.</div></div>
                    <div class="d-flex gap-2"><span class="step-badge">2</span><div>Haz clic en el botón verde <strong>"Abonar"</strong>.</div></div>
                    <div class="d-flex gap-2"><span class="step-badge">3</span><div>Indica el monto en dólares (puedes cambiar el monto; no tienes que pagar todo de una vez).</div></div>
                    <div class="d-flex gap-2"><span class="step-badge">4</span><div>Selecciona el método de pago: Efectivo, Tarjeta o Transferencia.</div></div>
                    <div class="d-flex gap-2"><span class="step-badge">5</span><div>Haz clic en <strong>"Registrar Abono"</strong>. El saldo se actualiza automáticamente.</div></div>
                </div>

                <div class="tip-box mt-3">
                    <i class="fas fa-lightbulb text-primary me-2"></i>
                    Puedes abonar a cualquier crédito, aunque no esté vencido. No tienes que esperar a que se venza el plazo.
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN 7: VENTAS ════════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="ventas">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-receipt text-primary me-2"></i>7. Historial de Ventas
                </div>

                <p>Consulta todas las ventas realizadas con filtros por fecha, tipo de pago y cajero.</p>

                <h6 class="fw-bold">Filtros disponibles</h6>
                <ul class="small">
                    <li><strong>Desde / Hasta:</strong> rango de fechas a consultar.</li>
                    <li><strong>Tipo de pago:</strong> Efectivo, Tarjeta o Fiado.</li>
                    <li><strong>Cajero</strong> (solo admin): ver ventas de un cajero específico.</li>
                </ul>

                <h6 class="fw-bold">Ver el detalle de una venta</h6>
                <p class="small">Haz clic en el ícono <i class="fas fa-eye text-primary"></i> para ver todos los productos de esa venta, el total en USD y Bs, y el cajero que la procesó.</p>

                <h6 class="fw-bold">Cancelar una venta</h6>
                <p class="small">Solo los administradores pueden cancelar ventas. Al cancelar, el stock de los productos se restaura automáticamente. Haz clic en <strong>"Ver detalle"</strong> y luego en el botón rojo <strong>"Cancelar Venta"</strong>.</p>

                <h6 class="fw-bold">Exportar a Excel</h6>
                <p class="small">Haz clic en el botón <strong>"CSV"</strong> para descargar las ventas filtradas en un archivo que puedes abrir con Excel.</p>
            </div>
        </div>

        <!-- ══ SECCIÓN 8: REPORTES ══════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="reportes">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-chart-bar text-success me-2"></i>8. Reportes
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr><th>Pestaña</th><th>Qué muestra</th><th>Para qué sirve</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="fas fa-receipt text-primary me-1"></i>Ventas</td>
                                <td>Lista de ventas del período con totales por tipo de pago</td>
                                <td>Saber cuánto vendiste y en qué forma te pagaron</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-coins text-warning me-1"></i>Ganancias</td>
                                <td>Ingresos, costos y ganancia bruta por día</td>
                                <td>Ver cuánto estás ganando realmente</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-box text-success me-1"></i>Productos</td>
                                <td>Los 30 productos más vendidos</td>
                                <td>Saber qué productos tienen más salida</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-warehouse text-info me-1"></i>Inventario</td>
                                <td>Valor de todo tu stock (a costo y a precio de venta)</td>
                                <td>Saber cuánto vale tu mercancía</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-users text-secondary me-1"></i>Deudas</td>
                                <td>Clientes con deuda y porcentaje de su límite usado</td>
                                <td>Control de clientes morosos</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="tip-box">
                    <i class="fas fa-lightbulb text-primary me-2"></i>
                    Las pestañas <strong>Ganancias</strong> e <strong>Inventario</strong> muestran resultados correctos solo si los productos tienen <strong>Precio de Costo</strong> configurado.
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN 9: CONFIGURACIÓN ═════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="configuracion">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-gear text-secondary me-2"></i>9. Configuración <span class="badge bg-danger ms-1" style="font-size:.65rem;">Solo Admin</span>
                </div>

                <h6 class="fw-bold">Usuarios</h6>
                <p class="small">Crea nuevos usuarios para tus cajeros. Haz clic en <strong>"Nuevo Usuario"</strong>, llena el formulario y elige el rol:</p>
                <ul class="small">
                    <li><span class="badge bg-info">Cajero</span> — puede hacer ventas y ver reportes.</li>
                    <li><span class="badge bg-danger">Admin</span> — tiene acceso a todo el sistema.</li>
                </ul>

                <h6 class="fw-bold">Categorías</h6>
                <p class="small">Agrega nuevas categorías de productos si las necesitas.</p>

                <h6 class="fw-bold">Herramientas del sistema</h6>
                <ul class="small">
                    <li><strong>Verificar datos en BD</strong> — comprueba que la base de datos tiene todos los registros.</li>
                    <li><strong>Actualizar Tasa de Cambio</strong> — cambia manualmente la tasa BCV si la automática no está disponible.</li>
                    <li><strong>Resetear Contraseñas</strong> — restablece contraseñas si alguien perdió el acceso.</li>
                </ul>
            </div>
        </div>

        <!-- ══ SECCIÓN 10: TASA DE CAMBIO ═══════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="tasa">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-dollar-sign text-warning me-2"></i>10. Tasa de Cambio BCV
                </div>

                <p>El sistema obtiene la tasa del BCV automáticamente y la guarda por 1 hora. Todos los precios se almacenan en <strong>dólares (USD)</strong> y se convierten a bolívares en pantalla según la tasa vigente.</p>

                <div class="screen-mock">
                    <strong>Ejemplo:</strong><br>
                    Producto: Harina P.A.N. = <strong>$1.60</strong><br>
                    Tasa BCV = <strong>Bs 567,68</strong><br>
                    Precio en Bs = 1.60 × 567.68 = <strong>Bs 901,26</strong>
                </div>

                <h6 class="fw-bold mt-3">Actualizar la tasa manualmente</h6>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex gap-2"><span class="step-badge">1</span><div>Ve a <strong>Configuración → Herramientas → Actualizar Tasa</strong>.</div></div>
                    <div class="d-flex gap-2"><span class="step-badge">2</span><div>Consulta el valor actual en <a href="https://www.bcv.org.ve/" target="_blank">bcv.org.ve</a> o <a href="https://alcambio.app/" target="_blank">alcambio.app</a>.</div></div>
                    <div class="d-flex gap-2"><span class="step-badge">3</span><div>Ingresa la tasa y guarda.</div></div>
                </div>

                <div class="warn-box mt-3">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Si la tasa automática falla, el sistema usará la última tasa guardada. Te recomendamos actualizarla al inicio de cada jornada.
                </div>
            </div>
        </div>

        <!-- ══ SECCIÓN 11: CONSEJOS ══════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4 manual-section" id="tips">
            <div class="card-body">
                <div class="section-title">
                    <i class="fas fa-star text-warning me-2"></i>11. Consejos y Buenas Prácticas
                </div>

                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3">
                        <i class="fas fa-check-circle text-success fa-lg flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Al inicio del día:</strong> revisa el Dashboard para ver el stock bajo y actualiza la tasa de cambio.
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="fas fa-check-circle text-success fa-lg flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Al recibir mercancía:</strong> ve a Productos y usa <i class="fas fa-warehouse"></i> para agregar el stock recibido.
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="fas fa-check-circle text-success fa-lg flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Para nuevos productos:</strong> usa siempre la calculadora de precio por bulto para garantizar que estás ganando lo correcto.
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="fas fa-check-circle text-success fa-lg flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Con los fiados:</strong> cobra abonos frecuentes. No dejes que la deuda crezca más allá del límite del cliente.
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="fas fa-check-circle text-success fa-lg flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Al cierre del día:</strong> revisa los reportes de ventas para saber cuánto vendiste y en qué forma de pago.
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="fas fa-check-circle text-success fa-lg flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Seguridad:</strong> cambia tu contraseña desde <strong>Configuración</strong> periódicamente. No compartas tu usuario con otras personas.
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="fas fa-check-circle text-success fa-lg flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Exporta reportes</strong> a CSV mensualmente para llevar un respaldo en tu computadora o teléfono.
                        </div>
                    </div>
                </div>

                <div class="tip-box mt-4">
                    <i class="fas fa-headset text-primary me-2"></i>
                    <strong>¿Tienes dudas?</strong> Cualquier problema técnico puede solucionarse desde <strong>Configuración → Herramientas</strong>. En caso de que el sistema no inicie correctamente, ejecuta el instalador desde <span class="key-box">/install-complete.php</span>.
                </div>
            </div>
        </div>

    </div><!-- /col-9 -->
</div><!-- /row -->

<script>
// Resaltar sección activa en el índice al hacer scroll
document.addEventListener('DOMContentLoaded', function () {
    const sections = document.querySelectorAll('.manual-section');
    const links    = document.querySelectorAll('.manual-nav a');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                links.forEach(l => l.classList.remove('active'));
                const active = document.querySelector('.manual-nav a[href="#' + e.target.id + '"]');
                if (active) active.classList.add('active');
            }
        });
    }, { threshold: 0.35 });

    sections.forEach(s => observer.observe(s));
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . 'layouts/main.php';
?>
