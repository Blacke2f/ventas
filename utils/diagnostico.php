<?php
/**
 * ===================================
 * GastroPOS - Diagnóstico del Sistema
 * ===================================
 */

// Cargar configuración
require_once 'config/config.php';
require_once 'config/Database.php';

session_start();

// Headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GastroPOS - Diagnóstico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .diagnostic-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .diagnostic-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .diagnostic-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .diagnostic-body {
            padding: 20px;
        }
        .check-item {
            padding: 12px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .status-success {
            color: #28a745;
            font-weight: 600;
        }
        .status-error {
            color: #dc3545;
            font-weight: 600;
        }
        .status-warning {
            color: #ffc107;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="diagnostic-container">
        <!-- Header -->
        <div class="diagnostic-card">
            <div class="diagnostic-header">
                <h1>🔧 Configuración de Base de Datos - AbasPOS</h1>
                <p style="margin: 0; opacity: 0.9;">Sistema de verificación del entorno</p>
            </div>
        </div>

        <!-- PHP Version -->
        <div class="diagnostic-card">
            <div class="diagnostic-body">
                <h5 class="mb-3">Información del Servidor</h5>
                <div class="check-item">
                    <span>PHP Version</span>
                    <span class="<?php echo version_compare(PHP_VERSION, '8.0.0', '>=') ? 'status-success' : 'status-error'; ?>">
                        <?php echo PHP_VERSION; ?>
                        <?php echo version_compare(PHP_VERSION, '8.0.0', '>=') ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>'; ?>
                    </span>
                </div>
                <div class="check-item">
                    <span>Sistema Operativo</span>
                    <span><?php echo PHP_OS; ?></span>
                </div>
                <div class="check-item">
                    <span>Servidor Web</span>
                    <span><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?></span>
                </div>
            </div>
        </div>

        <!-- Extensions -->
        <div class="diagnostic-card">
            <div class="diagnostic-body">
                <h5 class="mb-3">Extensiones PHP Requeridas</h5>
                <?php
                $extensions = [
                    'PDO' => extension_loaded('pdo'),
                    'PDO MySQL' => extension_loaded('pdo_mysql'),
                    'cURL' => extension_loaded('curl'),
                    'JSON' => extension_loaded('json'),
                    'OpenSSL' => extension_loaded('openssl'),
                    'Mbstring' => extension_loaded('mbstring'),
                ];

                foreach ($extensions as $name => $loaded) {
                    ?>
                    <div class="check-item">
                        <span><?php echo $name; ?></span>
                        <span class="<?php echo $loaded ? 'status-success' : 'status-error'; ?>">
                            <?php echo $loaded ? '<i class="fas fa-check-circle"></i> Cargado' : '<i class="fas fa-times-circle"></i> No Cargado'; ?>
                        </span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- Database Connection -->
        <div class="diagnostic-card">
            <div class="diagnostic-body">
                <h5 class="mb-3">Conexión a Base de Datos</h5>
                <?php
                try {
                    $db = Database::getInstance();
                    $query = $db->prepare("SELECT VERSION()");
                    $query->execute();
                    $version = $query->fetchColumn();
                    
                    $dbStatus = true;
                    $dbMessage = "Conectado";
                    $dbVersion = $version;
                } catch (Exception $e) {
                    $dbStatus = false;
                    $dbMessage = $e->getMessage();
                    $dbVersion = null;
                }
                ?>
                <div class="check-item">
                    <span>Host de Base de Datos</span>
                    <span><?php echo DB_HOST . ':' . DB_PORT; ?></span>
                </div>
                <div class="check-item">
                    <span>Base de Datos</span>
                    <span><?php echo DB_NAME; ?></span>
                </div>
                <div class="check-item">
                    <span>Usuario</span>
                    <span><?php echo DB_USER; ?></span>
                </div>
                <div class="check-item">
                    <span>Estado de Conexión</span>
                    <span class="<?php echo $dbStatus ? 'status-success' : 'status-error'; ?>">
                        <?php echo $dbStatus ? '<i class="fas fa-check-circle"></i> OK' : '<i class="fas fa-times-circle"></i> Error'; ?>
                    </span>
                </div>
                <?php if ($dbVersion) { ?>
                    <div class="check-item">
                        <span>Versión MySQL</span>
                        <span><?php echo $dbVersion; ?></span>
                    </div>
                <?php } else { ?>
                    <div class="check-item">
                        <span>Error</span>
                        <span class="status-error"><?php echo $dbMessage; ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- File Permissions -->
        <div class="diagnostic-card">
            <div class="diagnostic-body">
                <h5 class="mb-3">Permisos de Directorios</h5>
                <?php
                $directories = [
                    'controllers/' => CONTROLLERS_PATH,
                    'models/' => MODELS_PATH,
                    'views/' => VIEWS_PATH,
                    'public/' => PUBLIC_PATH,
                ];

                foreach ($directories as $name => $path) {
                    $exists = is_dir($path);
                    $readable = is_readable($path);
                    ?>
                    <div class="check-item">
                        <span><?php echo $name; ?></span>
                        <span class="<?php echo ($exists && $readable) ? 'status-success' : 'status-error'; ?>">
                            <?php 
                            if (!$exists) {
                                echo '<i class="fas fa-times-circle"></i> No existe';
                            } else if (!$readable) {
                                echo '<i class="fas fa-exclamation-circle"></i> No legible';
                            } else {
                                echo '<i class="fas fa-check-circle"></i> OK';
                            }
                            ?>
                        </span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- Configuration -->
        <div class="diagnostic-card">
            <div class="diagnostic-body">
                <h5 class="mb-3">Configuración de la Aplicación</h5>
                <div class="check-item">
                    <span>Nombre</span>
                    <span><?php echo APP_NAME; ?></span>
                </div>
                <div class="check-item">
                    <span>Versión</span>
                    <span><?php echo APP_VERSION; ?></span>
                </div>
                <div class="check-item">
                    <span>URL Base</span>
                    <span><?php echo APP_URL; ?></span>
                </div>
                <div class="check-item">
                    <span>Moneda</span>
                    <span><?php echo CURRENCY_SYMBOL . ' (' . CURRENCY_CODE . ')'; ?></span>
                </div>
                <div class="check-item">
                    <span>Modo Debug</span>
                    <span class="<?php echo DEBUG_MODE ? 'status-warning' : 'status-success'; ?>">
                        <?php echo DEBUG_MODE ? '<i class="fas fa-exclamation-circle"></i> ACTIVO' : '<i class="fas fa-check-circle"></i> Desactivado'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="diagnostic-card">
            <div class="diagnostic-body text-center">
                <a href="/Sistema de venta/inicio.html" class="btn btn-primary" style="margin: 5px;">
                    <i class="fas fa-home"></i> Ir al Inicio
                </a>
                <a href="/Sistema de venta/login" class="btn btn-success" style="margin: 5px;">
                    <i class="fas fa-sign-in-alt"></i> Ir al Login
                </a>
                <a href="/Sistema de venta/diagnostico.php" class="btn btn-info" style="margin: 5px;">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; color: white; margin-top: 30px;">
            <p>GastroPOS v<?php echo APP_VERSION; ?> - Diagnóstico del Sistema</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
