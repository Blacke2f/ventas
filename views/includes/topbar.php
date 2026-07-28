<?php
$nombre = $_SESSION['nombre_completo'] ?? ($_SESSION['nombre_usuario'] ?? 'Usuario');
$rol    = $_SESSION['rol'] ?? 'cajero';
$letra  = strtoupper(substr($nombre, 0, 1));
?>
<div class="top-right">
    <span class="top-time d-none d-md-block" id="currentDateTime"></span>

    <div class="dropdown">
        <div class="user-pill" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="user-avatar"><?php echo $letra; ?></div>
            <div class="d-none d-sm-block">
                <div class="user-name"><?php echo htmlspecialchars($nombre); ?></div>
                <div class="user-role"><?php echo ucfirst($rol); ?></div>
            </div>
            <i class="fas fa-chevron-down" style="font-size:.65rem;color:#94a3b8;"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:180px;">
            <li>
                <span class="dropdown-item-text" style="font-size:.75rem;color:#94a3b8;padding:6px 14px;">
                    <?php echo htmlspecialchars($nombre); ?>
                </span>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
                <a class="dropdown-item" href="<?php echo APP_URL; ?>/manual"
                   style="font-size:.875rem;">
                    <i class="fas fa-book-open me-2 text-primary"></i>Manual de Uso
                </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
                <a class="dropdown-item" href="#" id="logoutBtn"
                   style="color:#ef4444;font-size:.875rem;">
                    <i class="fas fa-right-from-bracket me-2"></i>Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
(function(){
    function tick(){
        var now = new Date();
        var el = document.getElementById('currentDateTime');
        if(el) el.textContent = now.toLocaleDateString('es-VE',{weekday:'short',day:'numeric',month:'short'})
                              + ' · ' + now.toLocaleTimeString('es-VE',{hour:'2-digit',minute:'2-digit'});
    }
    tick();
    setInterval(tick, 30000);
})();
</script>
