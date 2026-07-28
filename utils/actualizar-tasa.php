<?php
/**
 * Actualizar Tasa de Cambio Manualmente
 * Útil cuando la API no está disponible o se necesita configurar una tasa específica
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración
$cacheFile = dirname(__DIR__) . '/cache/tasa_cambio.json';
$nuevaTasa = null;

// Verificar si se envió una tasa nueva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tasa'])) {
    $nuevaTasa = floatval($_POST['tasa']);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Tasa de Cambio - AbasPOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .current-rate {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .rate-display {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-dollar-sign text-warning"></i> Actualizar Tasa de Cambio</h2>
            <p class="text-muted">Configure la tasa BCV manualmente</p>
        </div>

        <?php
        // Procesar actualización
        if ($nuevaTasa !== null && $nuevaTasa > 0) {
            $data = [
                'tasa' => $nuevaTasa,
                'timestamp' => time(),
                'fecha' => date('Y-m-d H:i:s'),
                'manual' => true
            ];
            
            // Crear directorio si no existe
            if (!file_exists(dirname($cacheFile))) {
                mkdir(dirname($cacheFile), 0755, true);
            }
            
            // Guardar
            if (file_put_contents($cacheFile, json_encode($data))) {
                echo '<div class="alert alert-success">';
                echo '<i class="fas fa-check-circle"></i> <strong>¡Tasa actualizada exitosamente!</strong><br>';
                echo 'Nueva tasa: <strong>Bs ' . number_format($nuevaTasa, 2, ',', '.') . '</strong>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-danger">';
                echo '<i class="fas fa-exclamation-circle"></i> Error al guardar la tasa';
                echo '</div>';
            }
        }

        // Leer tasa actual
        $tasaActual = 563.29; // Por defecto
        $fechaActual = 'No configurada';
        $esManual = false;

        if (file_exists($cacheFile)) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if ($cacheData) {
                $tasaActual = $cacheData['tasa'];
                $fechaActual = $cacheData['fecha'] ?? 'Desconocida';
                $esManual = $cacheData['manual'] ?? false;
                
                $segundos = time() - ($cacheData['timestamp'] ?? 0);
                if ($segundos < 60) {
                    $hace = 'Hace ' . $segundos . ' segundos';
                } elseif ($segundos < 3600) {
                    $hace = 'Hace ' . floor($segundos / 60) . ' minutos';
                } else {
                    $hace = 'Hace ' . floor($segundos / 3600) . ' horas';
                }
            }
        }
        ?>

        <!-- Tasa Actual -->
        <div class="current-rate">
            <small class="text-muted">Tasa Actual</small>
            <div class="rate-display">Bs <?php echo number_format($tasaActual, 2, ',', '.'); ?></div>
            <small class="text-muted">
                <?php echo $hace ?? 'Primera configuración'; ?>
                <?php if ($esManual): ?>
                    <span class="badge bg-info">Manual</span>
                <?php endif; ?>
            </small>
        </div>

        <!-- Formulario -->
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nueva Tasa (1 USD = ? Bs)</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text">Bs</span>
                    <input type="number" 
                           class="form-control" 
                           name="tasa" 
                           step="0.01" 
                           min="0.01"
                           value="<?php echo $tasaActual; ?>"
                           required>
                </div>
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Ingrese el valor actual del dólar en bolívares según el BCV
                </small>
            </div>

            <div class="alert alert-info">
                <strong><i class="fas fa-lightbulb"></i> Sugerencia:</strong><br>
                Consulte la tasa oficial en:<br>
                • <a href="https://www.bcv.org.ve/" target="_blank">BCV Oficial</a><br>
                • <a href="https://alcambio.app/" target="_blank">AlCambio.app</a><br>
                • <a href="https://www.dolartoday.com/" target="_blank">DolarToday</a>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Actualizar Tasa
                </button>
                <a href="../" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Sistema
                </a>
            </div>
        </form>

        <!-- Información adicional -->
        <hr class="my-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle text-info"></i> Información</h6>
                <ul class="small mb-0">
                    <li>La tasa se almacena en: <code>/cache/tasa_cambio.json</code></li>
                    <li>El sistema intenta obtener la tasa automáticamente cada hora</li>
                    <li>Esta actualización manual tiene prioridad sobre la automática</li>
                    <li>Todos los precios se almacenan en USD y se convierten a Bs en tiempo real</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
