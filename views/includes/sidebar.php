<?php
$uri = isset($_SERVER['REQUEST_URI']) ? urldecode($_SERVER['REQUEST_URI']) : '';

function sbActive($seg, $uri) {
    return (strpos($uri, '/' . $seg) !== false) ? ' active' : '';
}

// Leer nombre del sistema desde la BD (con fallback)
$appNombre    = APP_NAME;
$appSubtitulo = 'Punto de Venta';
$appIcono     = 'fa-store';

try {
    $pdo = Database::getInstance()->getConnection();
    $tablaExiste = $pdo->query("SHOW TABLES LIKE 'configuracion'")->fetchAll();
    if (!empty($tablaExiste)) {
        $cfg = $pdo->query("SELECT clave, valor FROM configuracion WHERE clave IN ('app_nombre','app_subtitulo','app_logo_icono')")
                   ->fetchAll(PDO::FETCH_KEY_PAIR);
        if (!empty($cfg['app_nombre']))     $appNombre    = $cfg['app_nombre'];
        if (!empty($cfg['app_subtitulo']))  $appSubtitulo = $cfg['app_subtitulo'];
        if (!empty($cfg['app_logo_icono'])) $appIcono     = $cfg['app_logo_icono'];
    }
} catch (Exception $ignore) {}
?>

<!-- Logo / Marca -->
<div class="sb-logo">
    <a href="<?php echo APP_URL; ?>/dashboard" class="brand">
        <div class="brand-icon"><i class="fas <?php echo htmlspecialchars($appIcono); ?>"></i></div>
        <div>
            <div class="brand-name"><?php echo htmlspecialchars($appNombre); ?></div>
            <div class="brand-sub"><?php echo htmlspecialchars($appSubtitulo); ?></div>
        </div>
    </a>
</div>

<!-- Menú scrollable -->
<div class="sb-scroll">

    <div class="sb-section">Principal</div>

    <a href="<?php echo APP_URL; ?>/dashboard"
       class="sb-link<?php echo sbActive('dashboard', $uri); ?>">
        <i class="fas fa-house"></i> Dashboard
    </a>
    <a href="<?php echo APP_URL; ?>/pos"
       class="sb-link<?php echo (strpos($uri, '/pos') !== false) ? ' active' : ''; ?>">
        <i class="fas fa-cash-register"></i> Punto de Venta
    </a>
    <a href="<?php echo APP_URL; ?>/ventas"
       class="sb-link<?php echo sbActive('ventas', $uri); ?>">
        <i class="fas fa-receipt"></i> Historial Ventas
    </a>

    <div class="sb-section">Gestión</div>

    <a href="<?php echo APP_URL; ?>/clientes"
       class="sb-link<?php echo sbActive('clientes', $uri); ?>">
        <i class="fas fa-users"></i> Clientes
    </a>
    <a href="<?php echo APP_URL; ?>/creditos"
       class="sb-link<?php echo sbActive('creditos', $uri); ?>">
        <i class="fas fa-hand-holding-dollar"></i> Créditos / Fiados
    </a>
    <a href="<?php echo APP_URL; ?>/reportes"
       class="sb-link<?php echo sbActive('reportes', $uri); ?>">
        <i class="fas fa-chart-bar"></i> Reportes
    </a>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
    <div class="sb-section">Administración</div>

    <a href="<?php echo APP_URL; ?>/productos"
       class="sb-link<?php echo sbActive('productos', $uri); ?>">
        <i class="fas fa-box"></i> Productos
    </a>
    <a href="<?php echo APP_URL; ?>/configuracion"
       class="sb-link<?php echo sbActive('configuracion', $uri); ?>">
        <i class="fas fa-gear"></i> Configuración
    </a>
    <?php endif; ?>

    <div style="height:16px;"></div>

</div>

<!-- Widget tasa BCV (fijo abajo) -->
<div class="sb-rate">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="rate-lbl">Tasa BCV</div>
            <div class="rate-val rate-value">Bs 567,68</div>
            <div class="rate-sub rate-update">Actualizando...</div>
        </div>
        <button id="btn-refresh-rate" class="rate-btn" title="Actualizar tasa">
            <i class="fas fa-rotate-right"></i>
        </button>
    </div>
</div>
