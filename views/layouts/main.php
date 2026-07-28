<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' · ' : ''; ?>AbasPOS</title>
    <?php $APP_URL_ENCODED = str_replace(' ', '%20', APP_URL); ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="<?php echo $APP_URL_ENCODED; ?>/public/logo.svg">
    <link href="<?php echo $APP_URL_ENCODED; ?>/public/css/styles.css" rel="stylesheet">
    <link href="<?php echo $APP_URL_ENCODED; ?>/public/css/responsive.css" rel="stylesheet">

    <?php if (isset($customCSS)): ?>
        <link href="<?php echo $customCSS; ?>" rel="stylesheet">
    <?php endif; ?>

    <style>
    /* ═══ Variables ══════════════════════════════════════════════ */
    :root {
        --sb-width: 220px;
        --sb-bg1:   #1e1b4b;
        --sb-bg2:   #312e81;
        --sb-text:  rgba(255,255,255,.75);
        --sb-hover: rgba(255,255,255,.1);
        --sb-active:#fff;
        --top-h:    56px;
        --body-bg:  #f1f5f9;
        --card-r:   12px;
        --pri:      #6366f1;
        --pri2:     #4f46e5;
    }

    /* ═══ Reset base ═════════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', system-ui, sans-serif;
        background: var(--body-bg);
        color: #1e293b;
        overflow-x: hidden;
    }

    /* ═══ Sidebar ════════════════════════════════════════════════ */
    #sidebar {
        position: fixed;
        top: 0; left: 0;
        width: var(--sb-width);
        height: 100vh;
        background: linear-gradient(180deg, var(--sb-bg1) 0%, var(--sb-bg2) 100%);
        display: flex;
        flex-direction: column;
        z-index: 200;
        box-shadow: 4px 0 20px rgba(0,0,0,.15);
        transition: transform .25s ease;
    }

    /* Logo */
    .sb-logo {
        padding: 18px 20px 14px;
        border-bottom: 1px solid rgba(255,255,255,.1);
        flex-shrink: 0;
    }
    .sb-logo .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }
    .sb-logo .brand-icon {
        width: 38px; height: 38px;
        background: linear-gradient(135deg,#6366f1,#a855f7);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 1.1rem;
        flex-shrink: 0;
    }
    .sb-logo .brand-name {
        color: #fff;
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -.3px;
        line-height: 1.1;
    }
    .sb-logo .brand-sub {
        color: rgba(255,255,255,.5);
        font-size: .65rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Scroll area del menú */
    .sb-scroll {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 10px 0 10px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.2) transparent;
    }
    .sb-scroll::-webkit-scrollbar { width: 4px; }
    .sb-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }

    /* Sección label */
    .sb-section {
        padding: 14px 18px 4px;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: rgba(255,255,255,.35);
    }

    /* Links */
    .sb-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 18px;
        color: var(--sb-text);
        text-decoration: none;
        font-size: .875rem;
        font-weight: 500;
        border-radius: 0;
        border-left: 3px solid transparent;
        transition: background .15s, color .15s, border-color .15s;
        white-space: nowrap;
    }
    .sb-link i { width: 18px; text-align: center; font-size: .85rem; flex-shrink: 0; }
    .sb-link:hover {
        background: var(--sb-hover);
        color: var(--sb-active);
    }
    .sb-link.active {
        background: rgba(255,255,255,.12);
        color: var(--sb-active);
        border-left-color: #a5b4fc;
        font-weight: 600;
    }

    /* Widget tasa */
    .sb-rate {
        padding: 12px 16px;
        margin: 8px 12px 12px;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 10px;
        flex-shrink: 0;
    }
    .sb-rate .rate-lbl  { font-size:.65rem; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.8px; }
    .sb-rate .rate-val  { font-size:1.05rem; font-weight:700; color:#fbbf24; line-height:1.2; }
    .sb-rate .rate-sub  { font-size:.65rem; color:rgba(255,255,255,.4); }
    .sb-rate .rate-btn  {
        background: none; border: 1px solid rgba(255,255,255,.2);
        color: rgba(255,255,255,.6); border-radius:6px;
        padding:2px 8px; font-size:.75rem; cursor:pointer;
        transition: background .15s;
    }
    .sb-rate .rate-btn:hover { background: rgba(255,255,255,.1); color:#fff; }

    /* ═══ Topbar ══════════════════════════════════════════════════ */
    #topbar {
        position: fixed;
        top: 0;
        left: var(--sb-width);
        right: 0;
        height: var(--top-h);
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        padding: 0 20px;
        justify-content: space-between;
        z-index: 100;
        box-shadow: 0 1px 8px rgba(0,0,0,.06);
    }
    #topbar .page-name {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }
    #topbar .top-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    #topbar .top-time {
        font-size: .8rem;
        color: #64748b;
    }
    #topbar .user-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 5px 12px 5px 6px;
        border-radius: 50px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: background .15s;
    }
    #topbar .user-pill:hover { background: #f8fafc; }
    #topbar .user-avatar {
        width: 30px; height: 30px;
        background: linear-gradient(135deg,#6366f1,#a855f7);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: .8rem; font-weight: 700;
    }
    #topbar .user-name  { font-size: .82rem; font-weight: 600; color: #1e293b; }
    #topbar .user-role  { font-size: .7rem; color: #64748b; }

    /* ═══ Contenido principal ════════════════════════════════════ */
    #main {
        margin-left: var(--sb-width);
        padding-top: calc(var(--top-h) + 20px);
        padding-left: 20px;
        padding-right: 20px;
        padding-bottom: 30px;
        min-height: 100vh;
    }

    /* ═══ Cards ══════════════════════════════════════════════════ */
    .card {
        border: 1px solid #e2e8f0;
        border-radius: var(--card-r);
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
        margin-bottom: 0;
    }
    /* card-header sin clase bg-* → gradiente */
    .card-header:not([class*="bg-"]) {
        background: linear-gradient(135deg, var(--pri), #7c3aed);
        color: #fff;
        border: none;
        padding: 12px 18px;
        font-weight: 600;
        font-size: .9rem;
        border-radius: var(--card-r) var(--card-r) 0 0;
    }
    .card-header.bg-white {
        background: #fff !important;
        color: #1e293b !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 12px 18px;
        font-weight: 600;
    }
    .card-body { padding: 16px 18px; color: #1e293b; }

    /* ═══ Botones ════════════════════════════════════════════════ */
    .btn-primary {
        background: linear-gradient(135deg, var(--pri), var(--pri2));
        border: none;
    }
    .btn-primary:hover, .btn-primary:focus {
        background: linear-gradient(135deg, var(--pri2), #4338ca);
        box-shadow: 0 4px 14px rgba(99,102,241,.35);
    }

    /* ═══ Tablas ══════════════════════════════════════════════════ */
    .table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        border-color: #e2e8f0;
        padding: 10px 14px;
    }
    .table tbody td { padding: 10px 14px; vertical-align: middle; color: #1e293b; }
    .table-hover tbody tr:hover { background: #f8fafc; }

    /* ═══ Formularios ════════════════════════════════════════════ */
    .form-control, .form-select {
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        color: #1e293b;
        background: #fff;
        font-size: .875rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--pri);
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
        color: #1e293b;
    }
    .form-control::placeholder { color: #94a3b8; }
    .form-label { font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 5px; }
    .input-group-text { border: 1.5px solid #e2e8f0; background: #f8fafc; color: #475569; }

    /* ═══ Alerts ══════════════════════════════════════════════════ */
    .alert { border: none; border-left: 4px solid; border-radius: 8px; font-size: .875rem; }
    .alert-success { background:#f0fdf4; border-left-color:#22c55e; color:#15803d; }
    .alert-danger  { background:#fef2f2; border-left-color:#ef4444; color:#991b1b; }
    .alert-warning { background:#fffbeb; border-left-color:#f59e0b; color:#92400e; }
    .alert-info    { background:#eff6ff; border-left-color:#3b82f6; color:#1d4ed8; }

    /* ═══ Badges ══════════════════════════════════════════════════ */
    .badge { font-weight: 600; letter-spacing: .3px; }

    /* ═══ Nav tabs ═══════════════════════════════════════════════ */
    .nav-tabs { border-bottom: 2px solid #e2e8f0; }
    .nav-tabs .nav-link { color: #64748b; border: none; border-bottom: 2px solid transparent; font-size: .875rem; font-weight: 500; margin-bottom: -2px; padding: 8px 14px; }
    .nav-tabs .nav-link.active { color: var(--pri); border-bottom-color: var(--pri); background: none; font-weight: 700; }

    /* ═══ Modal ══════════════════════════════════════════════════ */
    .modal-content { border: none; border-radius: var(--card-r); box-shadow: 0 20px 60px rgba(0,0,0,.2); }
    .modal-header:not([style*="background"]) { background: linear-gradient(135deg,var(--pri),#7c3aed); color:#fff; border:none; }
    .modal-header .btn-close { filter: brightness(10); }

    /* ═══ Page title ══════════════════════════════════════════════ */
    .page-title { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
    .page-title::before { content: ''; display: block; width: 4px; height: 22px; background: linear-gradient(180deg,var(--pri),#a855f7); border-radius: 4px; flex-shrink: 0; }

    /* ═══ Toasts ══════════════════════════════════════════════════ */
    #toastContainer { position:fixed; top:70px; right:20px; z-index:9999; max-width:360px; }
    #toastContainer .alert { margin-bottom: 8px; box-shadow: 0 4px 16px rgba(0,0,0,.1); animation: slideIn .25s ease; }
    @keyframes slideIn { from { opacity:0; transform:translateX(30px); } to { opacity:1; transform:translateX(0); } }

    /* ═══ Responsive ══════════════════════════════════════════════ */
    @media (max-width: 768px) {
        #sidebar {
            transform: translateX(-100%);
            transition: transform .25s ease;
            z-index: 1050;
        }
        #sidebar.open {
            transform: translateX(0);
            box-shadow: 4px 0 30px rgba(0,0,0,.4);
        }
        #topbar {
            left: 0;
        }
        #main {
            margin-left: 0;
            padding-left: 12px;
            padding-right: 12px;
        }
        #sb-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 1049;
        }
        #sb-overlay.show { display: block; }
        .sb-toggle-btn { display: flex !important; }
    }
    @media (min-width: 769px) {
        .sb-toggle-btn { display: none !important; }
        #sb-overlay     { display: none !important; }
    }
    </style>
</head>
<body>

<!-- ══ OVERLAY (móvil) ════════════════════════════════════════════ -->
<div id="sb-overlay" onclick="cerrarSidebar()"></div>

<!-- ══ SIDEBAR ════════════════════════════════════════════════════ -->
<nav id="sidebar">
    <?php include VIEWS_PATH . 'includes/sidebar.php'; ?>
</nav>

<!-- ══ TOPBAR ═════════════════════════════════════════════════════ -->
<header id="topbar">
    <div class="d-flex align-items-center gap-3">
        <!-- Botón hamburguesa (solo móvil) -->
        <button class="btn sb-toggle-btn p-1 border-0"
                style="background:none;"
                onclick="toggleSidebar()"
                aria-label="Menú">
            <i class="fas fa-bars fa-lg" style="color:#64748b;"></i>
        </button>
        <span class="page-name">
            <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'AbasPOS'; ?>
        </span>
    </div>
    <?php include VIEWS_PATH . 'includes/topbar.php'; ?>
</header>

<!-- ══ CONTENIDO ══════════════════════════════════════════════════ -->
<main id="main">
    <?php echo $content ?? ''; ?>
</main>

<!-- ══ BOTTOM NAV (solo móvil) ═══════════════════════════════════ -->
<?php
$cp = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
function mbAct($seg, $cp) { return strpos($cp, $seg) !== false ? ' active' : ''; }
?>
<nav id="mobile-nav">
    <a href="<?php echo APP_URL; ?>/dashboard"<?php echo mbAct('dashboard',$cp); ?>>
        <i class="fas fa-house"></i>
        <div class="nav-dot"></div>
        Inicio
    </a>
    <a href="<?php echo APP_URL; ?>/pos"<?php echo mbAct('/pos',$cp); ?>>
        <i class="fas fa-cash-register"></i>
        <div class="nav-dot"></div>
        Vender
    </a>
    <a href="<?php echo APP_URL; ?>/clientes"<?php echo mbAct('clientes',$cp); ?>>
        <i class="fas fa-users"></i>
        <div class="nav-dot"></div>
        Clientes
    </a>
    <a href="<?php echo APP_URL; ?>/creditos"<?php echo mbAct('creditos',$cp); ?>>
        <i class="fas fa-handshake"></i>
        <div class="nav-dot"></div>
        Fiados
    </a>
    <a href="<?php echo APP_URL; ?>/reportes"<?php echo mbAct('reportes',$cp); ?>>
        <i class="fas fa-chart-bar"></i>
        <div class="nav-dot"></div>
        Reportes
    </a>
</nav>

<!-- ══ FAB "Nueva Venta" fuera del POS ═══════════════════════════ -->
<?php if (strpos($cp, '/pos') === false): ?>
<a href="<?php echo APP_URL; ?>/pos" class="fab-pos" title="Nueva Venta">
    <i class="fas fa-plus"></i>
</a>
<?php endif; ?>

<!-- Toast container -->
<div id="toastContainer"></div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
const APP_URL = '<?php echo APP_URL; ?>';</script>
<script src="<?php echo $APP_URL_ENCODED; ?>/public/js/api-client.js"></script>
<script src="<?php echo $APP_URL_ENCODED; ?>/public/js/utils.js"></script>
<script src="<?php echo $APP_URL_ENCODED; ?>/public/js/tasa-cambio.js"></script>
<script src="<?php echo $APP_URL_ENCODED; ?>/public/js/app.js"></script>
<script src="<?php echo $APP_URL_ENCODED; ?>/public/js/productos.js"></script>
<script src="<?php echo $APP_URL_ENCODED; ?>/public/js/clientes.js"></script>
<script src="<?php echo $APP_URL_ENCODED; ?>/public/js/ventas.js"></script>
<script src="<?php echo $APP_URL_ENCODED; ?>/public/js/creditos.js"></script>
<?php if (isset($customJS)): ?>
<script src="<?php echo $customJS; ?>"></script>
<?php endif; ?>

<!-- Menú móvil -->
<script>
function toggleSidebar() {
    var sb  = document.getElementById('sidebar');
    var ov  = document.getElementById('sb-overlay');
    var open = sb.classList.toggle('open');
    ov.classList.toggle('show', open);
    document.body.style.overflow = open ? 'hidden' : '';
}
function cerrarSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sb-overlay').classList.remove('show');
    document.body.style.overflow = '';
}
// Cerrar con tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarSidebar();
});
// Cerrar sidebar al navegar en móvil
document.querySelectorAll('#sidebar .sb-link').forEach(function(link) {
    link.addEventListener('click', function() {
        if (window.innerWidth < 769) cerrarSidebar();
    });
});
</script>

</body>
</html>
