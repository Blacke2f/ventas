<?php
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . APP_URL . '/dashboard');
    exit;
}

// Cargar usuarios directamente desde la BD
require_once MODELS_PATH . 'Usuario.php';
$usuarioModel = new Usuario();
$usuarios = $usuarioModel->getAllActivos() ?? [];

// Contar productos y categorías
require_once MODELS_PATH . 'Producto.php';
$productoModel = new Producto();
$totalProductos = $productoModel->countActivos();
$categorias = $productoModel->getCategorias() ?? [];

$pageTitle = 'Configuración del Sistema';
ob_start();
?>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-usuarios">
            <i class="fas fa-users"></i> Usuarios
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-apariencia">
            <i class="fas fa-palette"></i> Apariencia
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-categorias">
            <i class="fas fa-folder"></i> Categorías
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sistema">
            <i class="fas fa-cog"></i> Sistema
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- ── APARIENCIA ────────────────────────────────────────────── -->
    <div class="tab-pane fade" id="tab-apariencia">
        <div class="row g-3">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-palette text-primary"></i>
                            Personalización del Sistema
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="formApariencia">
                            <div class="mb-3">
                                <label class="form-label">Nombre del sistema *</label>
                                <input type="text" class="form-control" id="cfgNombre"
                                       placeholder="Ej: Mi Abasto, Bodegón El Rey..."
                                       maxlength="40">
                                <div class="form-text">Aparece en el logo del menú lateral y en el título del navegador.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subtítulo</label>
                                <input type="text" class="form-control" id="cfgSubtitulo"
                                       placeholder="Ej: Punto de Venta"
                                       maxlength="30">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Icono del logo</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="iconPreview">
                                        <i class="fas fa-store"></i>
                                    </span>
                                    <input type="text" class="form-control" id="cfgIcono"
                                           placeholder="fa-store"
                                           oninput="previewIcono(this.value)">
                                </div>
                                <div class="form-text">
                                    Clase de <a href="https://fontawesome.com/icons" target="_blank">Font Awesome 6</a>.
                                    Ejemplos:
                                    <code class="cursor-pointer" onclick="setIcono('fa-store')">fa-store</code>,
                                    <code class="cursor-pointer" onclick="setIcono('fa-cart-shopping')">fa-cart-shopping</code>,
                                    <code class="cursor-pointer" onclick="setIcono('fa-shop')">fa-shop</code>,
                                    <code class="cursor-pointer" onclick="setIcono('fa-basket-shopping')">fa-basket-shopping</code>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar cambios
                            </button>
                            <button type="button" class="btn btn-outline-secondary ms-2"
                                    onclick="recargarApariencia()">
                                <i class="fas fa-undo me-1"></i> Recargar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0"><i class="fas fa-eye text-success"></i> Vista previa</h6>
                    </div>
                    <div class="card-body p-3">
                        <!-- Mini sidebar preview -->
                        <div style="background:linear-gradient(180deg,#1e1b4b,#312e81);border-radius:10px;padding:14px;color:#fff;">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2"
                                 style="border-bottom:1px solid rgba(255,255,255,.15);">
                                <div style="width:36px;height:36px;background:linear-gradient(135deg,#6366f1,#a855f7);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                    <i id="previewIcon" class="fas fa-store"></i>
                                </div>
                                <div>
                                    <div id="previewNombre" class="fw-bold" style="font-size:.9rem;line-height:1.1;">AbasPOS</div>
                                    <div id="previewSubtitulo" style="font-size:.62rem;opacity:.5;text-transform:uppercase;letter-spacing:.8px;">Punto de Venta</div>
                                </div>
                            </div>
                            <div style="font-size:.72rem;opacity:.5;text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;">Principal</div>
                            <div style="padding:6px 8px;border-radius:6px;background:rgba(255,255,255,.12);font-size:.8rem;margin-bottom:4px;">
                                <i class="fas fa-gauge-high me-2"></i>Dashboard
                            </div>
                            <div style="padding:6px 8px;font-size:.8rem;opacity:.6;margin-bottom:2px;">
                                <i class="fas fa-cash-register me-2"></i>Punto de Venta
                            </div>
                            <div style="padding:6px 8px;font-size:.8rem;opacity:.6;">
                                <i class="fas fa-receipt me-2"></i>Historial Ventas
                            </div>
                        </div>
                        <small class="text-muted d-block text-center mt-2">
                            Así se verá el menú lateral
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── USUARIOS ─────────────────────────────────────────────── -->
    <div class="tab-pane fade show active" id="tab-usuarios">
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </button>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Creado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($u['nombre_completo']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($u['nombre_usuario']); ?></code></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php if ($u['rol'] === 'admin'): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Cajero</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-success">Activo</span></td>
                                <td><small><?php echo date('d/m/Y', strtotime($u['fecha_creacion'])); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($usuarios)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No hay usuarios</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── CATEGORÍAS ────────────────────────────────────────────── -->
    <div class="tab-pane fade" id="tab-categorias">
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoriaModal">
                <i class="fas fa-folder-plus"></i> Nueva Categoría
            </button>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Icono</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td><?php echo $cat['id_categoria']; ?></td>
                                <td><strong><?php echo htmlspecialchars($cat['nombre_categoria']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cat['descripcion'] ?? '-'); ?></td>
                                <td><i class="fas <?php echo htmlspecialchars($cat['icono'] ?? 'fa-box'); ?>"></i></td>
                                <td><span class="badge bg-success">Activa</span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($categorias)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No hay categorías</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── SISTEMA ────────────────────────────────────────────────── -->
    <div class="tab-pane fade" id="tab-sistema">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0"><i class="fas fa-info-circle text-primary"></i> Información del Sistema</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr><td><strong>Aplicación</strong></td><td>AbasPOS</td></tr>
                            <tr><td><strong>Versión</strong></td><td>1.0.0</td></tr>
                            <tr><td><strong>PHP</strong></td><td><?php echo phpversion(); ?></td></tr>
                            <tr><td><strong>Zona Horaria</strong></td><td>America/Caracas</td></tr>
                            <tr><td><strong>Fecha del Servidor</strong></td><td><?php echo date('d/m/Y H:i:s'); ?></td></tr>
                            <tr><td><strong>Total Productos</strong></td><td><?php echo $totalProductos; ?></td></tr>
                            <tr><td><strong>Total Categorías</strong></td><td><?php echo count($categorias); ?></td></tr>
                            <tr><td><strong>Total Usuarios</strong></td><td><?php echo count($usuarios); ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0"><i class="fas fa-database text-success"></i> Base de Datos</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr><td><strong>Host</strong></td><td><?php echo DB_HOST; ?></td></tr>
                            <tr><td><strong>Puerto</strong></td><td><?php echo DB_PORT; ?></td></tr>
                            <tr><td><strong>Base de Datos</strong></td><td><?php echo DB_NAME; ?></td></tr>
                            <tr><td><strong>Usuario</strong></td><td><?php echo DB_USER; ?></td></tr>
                            <tr><td><strong>Charset</strong></td><td><?php echo DB_CHARSET; ?></td></tr>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0"><i class="fas fa-tools text-warning"></i> Herramientas</h6>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="<?php echo APP_URL; ?>/utils/verificar-datos.php" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-search"></i> Verificar Datos en BD
                        </a>
                        <a href="<?php echo APP_URL; ?>/utils/actualizar-tasa.php" target="_blank" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-dollar-sign"></i> Actualizar Tasa de Cambio
                        </a>
                        <a href="<?php echo APP_URL; ?>/reset-password.php" target="_blank" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-key"></i> Resetear Contraseñas
                        </a>
                    </div>
                </div>
            </div>

            <!-- ── LIMPIEZA DE DATOS ───────────────────────────── -->
            <div class="col-12">
                <div class="card border-0 shadow-sm border-danger" style="border:2px solid #fecaca !important;">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 text-danger">
                            <i class="fas fa-trash-can me-2"></i>Zona Peligrosa — Limpiar Base de Datos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning border-0 mb-3">
                            <i class="fas fa-triangle-exclamation me-2"></i>
                            <strong>Esta acción elimina permanentemente:</strong> ventas, detalles de ventas, créditos, abonos, clientes y productos.<br>
                            <strong>Se conservan:</strong> <span class="badge bg-success">Usuarios</span> <span class="badge bg-success">Categorías</span>
                        </div>

                        <p class="text-muted small mb-3">
                            Usa esta opción solo si deseas borrar todos los datos de prueba y empezar desde cero con datos reales. Esta acción <strong>no se puede deshacer</strong>.
                        </p>

                        <!-- Resumen de lo que se va a borrar (PHP) -->
                        <?php
                        try {
                            $pdo = Database::getInstance()->getConnection();
                            $cuentas = [];
                            foreach (['ventas','detalle_ventas','creditos','abonos','clientes','productos'] as $t) {
                                $cuentas[$t] = (int)$pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
                            }
                        } catch (Exception $e) {
                            $cuentas = [];
                        }
                        ?>
                        <?php if (!empty($cuentas)): ?>
                        <div class="row g-2 mb-3">
                            <?php
                            $labels = ['ventas'=>'Ventas','detalle_ventas'=>'Detalles','creditos'=>'Créditos','abonos'=>'Abonos','clientes'=>'Clientes','productos'=>'Productos'];
                            foreach ($cuentas as $tabla => $total):
                            ?>
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="text-center p-2 rounded" style="background:#fef2f2;border:1px solid #fecaca;">
                                    <div class="fw-bold text-danger" style="font-size:1.3rem;"><?php echo $total; ?></div>
                                    <small class="text-muted"><?php echo $labels[$tabla]; ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <button type="button" class="btn btn-danger fw-bold"
                                id="btnLimpiarDatos"
                                onclick="confirmarLimpieza()">
                            <i class="fas fa-trash-can me-2"></i>
                            Limpiar Todos los Datos de Prueba
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- /row g-3 -->
    </div><!-- /tab-pane sistema -->

</div><!-- /tab-content -->
<div class="modal fade" id="usuarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="usuarioForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" id="uNombreCompleto" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de Usuario *</label>
                        <input type="text" class="form-control" id="uUsername" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" id="uEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select class="form-select" id="uRol" required>
                            <option value="">Seleccionar...</option>
                            <option value="cajero">Cajero</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="uPassword" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nueva Categoría -->
<div class="modal fade" id="categoriaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoriaForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="catNombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="catDescripcion" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icono (FontAwesome)</label>
                        <input type="text" class="form-control" id="catIcono" placeholder="fa-box">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('categoriaForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
        nombre_categoria: document.getElementById('catNombre').value,
        descripcion: document.getElementById('catDescripcion').value,
        icono: document.getElementById('catIcono').value
    };
    const r = await productosAPI.createCategoria(data);
    if (r.ok) {
        Utils.showToast('Categoría creada. Recarga la página para verla.', 'success');
        bootstrap.Modal.getInstance(document.getElementById('categoriaModal')).hide();
        document.getElementById('categoriaForm').reset();
    } else {
        Utils.showToast(r.error || 'Error', 'danger');
    }
});

document.getElementById('usuarioForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const pass = document.getElementById('uPassword').value;
    if (pass.length < 6) { Utils.showToast('La contraseña debe tener al menos 6 caracteres', 'warning'); return; }

    const data = {
        nombre_completo: document.getElementById('uNombreCompleto').value.trim(),
        nombre_usuario:  document.getElementById('uUsername').value.trim(),
        email:           document.getElementById('uEmail').value.trim(),
        rol:             document.getElementById('uRol').value,
        password:        pass,   // campo estándar sin tilde — el modelo lo acepta
        activo: 1
    };

    if (!data.nombre_usuario || !data.email || !data.rol) {
        Utils.showToast('Completa todos los campos obligatorios', 'warning'); return;
    }

    const btn = e.submitter;
    if (btn) { btn.disabled = true; btn.textContent = 'Creando...'; }

    const r = await api.post('/api/auth/register', data);

    if (btn) { btn.disabled = false; btn.textContent = 'Crear Usuario'; }

    if (r && r.ok) {
        Utils.showToast('✅ Usuario "' + data.nombre_usuario + '" creado correctamente. Recarga para verlo.', 'success', 4000);
        bootstrap.Modal.getInstance(document.getElementById('usuarioModal')).hide();
        document.getElementById('usuarioForm').reset();
        setTimeout(() => location.reload(), 2000);
    } else {
        Utils.showToast('❌ ' + ((r && r.error) ? r.error : 'Error al crear usuario'), 'danger');
    }
});

// ── Apariencia ────────────────────────────────────────────────────
async function recargarApariencia() {
    const r = await api.get('/api-configuracion.php?action=get');
    if (!r.ok) return;
    const cfg = r.data || {};
    document.getElementById('cfgNombre').value   = cfg.app_nombre    || 'AbasPOS';
    document.getElementById('cfgSubtitulo').value = cfg.app_subtitulo || 'Punto de Venta';
    document.getElementById('cfgIcono').value    = cfg.app_logo_icono || 'fa-store';
    previewIcono(cfg.app_logo_icono || 'fa-store');
    document.getElementById('previewNombre').textContent    = cfg.app_nombre    || 'AbasPOS';
    document.getElementById('previewSubtitulo').textContent = cfg.app_subtitulo || 'Punto de Venta';
}

function previewIcono(icono) {
    const clase = (icono || 'fa-store').trim();
    document.getElementById('iconPreview').innerHTML = `<i class="fas ${clase}"></i>`;
    document.getElementById('previewIcon').className = `fas ${clase}`;
}

function setIcono(icono) {
    document.getElementById('cfgIcono').value = icono;
    previewIcono(icono);
}

document.getElementById('formApariencia').addEventListener('submit', async (e) => {
    e.preventDefault();
    const nombre   = document.getElementById('cfgNombre').value.trim();
    const subtitulo = document.getElementById('cfgSubtitulo').value.trim();
    const icono    = document.getElementById('cfgIcono').value.trim() || 'fa-store';

    if (!nombre) { Utils.showToast('El nombre no puede estar vacío', 'warning'); return; }

    const r = await api.post('/api-configuracion.php?action=save', {
        app_nombre:     nombre,
        app_subtitulo:  subtitulo,
        app_logo_icono: icono,
    });

    if (r.ok) {
        // Actualizar sidebar y título en tiempo real
        const brandName = document.querySelector('.brand-name');
        const brandSub  = document.querySelector('.brand-sub');
        const brandIcon = document.querySelector('.brand-icon i');
        const pageTitle = document.querySelector('title');

        if (brandName) brandName.textContent = nombre;
        if (brandSub)  brandSub.textContent  = subtitulo;
        if (brandIcon) brandIcon.className   = `fas ${icono}`;
        if (pageTitle) pageTitle.textContent = pageTitle.textContent.replace(/^.+·/, nombre + ' ·');

        Utils.showToast('Apariencia guardada correctamente', 'success');
    } else {
        Utils.showToast(r.error || 'Error al guardar', 'danger');
    }
});

// Actualizar vista previa en tiempo real
document.getElementById('cfgNombre').addEventListener('input', (e) => {
    document.getElementById('previewNombre').textContent = e.target.value || 'AbasPOS';
});
document.getElementById('cfgSubtitulo').addEventListener('input', (e) => {
    document.getElementById('previewSubtitulo').textContent = e.target.value || 'Punto de Venta';
});
document.getElementById('cfgIcono').addEventListener('input', (e) => {
    previewIcono(e.target.value);
});

// ── Limpiar datos de prueba ────────────────────────────────────────
async function confirmarLimpieza() {
    if (!confirm('⚠️ ATENCIÓN\n\nEsto eliminará PERMANENTEMENTE:\n• Todas las ventas\n• Todos los clientes\n• Todos los productos\n• Créditos y abonos\n\nSe conservarán: usuarios y categorías.\n\n¿Estás seguro?')) return;
    if (!confirm('Segunda confirmación: ¿Realmente deseas borrar TODOS los datos de prueba?\n\nEsta acción NO se puede deshacer.')) return;

    const btn = document.getElementById('btnLimpiarDatos');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Limpiando...';

    const r = await api.post('/utils/limpiar-datos.php', { confirmar: true });

    if (r.ok) {
        const res = r.data && r.data.resumen ? r.data.resumen : {};
        const detalles = Object.entries(res).map(([t,n]) => `${t}: ${n}`).join(', ');
        Utils.showToast(`Base de datos limpiada. ${detalles}`, 'success', 5000);
        setTimeout(() => location.reload(), 2000);
    } else {
        Utils.showToast(r.error || 'Error al limpiar', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-can me-2"></i>Limpiar Todos los Datos de Prueba';
    }
}

// Cargar configuración al abrir la pestaña de apariencia
document.querySelector('[data-bs-target="#tab-apariencia"]').addEventListener('shown.bs.tab', recargarApariencia);
document.addEventListener('DOMContentLoaded', recargarApariencia);
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . 'layouts/main.php';
?>
