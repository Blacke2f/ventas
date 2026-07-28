<?php
/**
 * Instalación Completa de AbasPOS
 * Este script ejecuta toda la instalación de forma correcta
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación Completa - AbasPOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .install-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .install-body {
            padding: 30px;
        }
        .step {
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .step.success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .step.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .step.info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1><i class="fas fa-store"></i> AbasPOS</h1>
            <p>Instalación Completa del Sistema</p>
        </div>
        <div class="install-body">
            <?php
            // Leer credenciales del entorno (Docker) o usar valores por defecto (Laragon)
            $host   = getenv('DB_HOST')     ?: 'localhost';
            $port   = (int)(getenv('DB_PORT') ?: 3306);
            $dbname = getenv('DB_NAME')     ?: 'abastospos';
            $user   = getenv('DB_USER')     ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';
            
            try {
                // Paso 1: Conectar al servidor MySQL
                echo "<div class='step info'>";
                echo "<h5><i class='fas fa-plug'></i> Paso 1: Conectando a MySQL...</h5>";
                $pdo = new PDO("mysql:host=$host;port=$port", $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]);
                echo "<p class='text-success mb-0'>✅ Conexión exitosa</p>";
                echo "</div>";
                
                // Paso 2: Eliminar base de datos anterior si existe
                echo "<div class='step info'>";
                echo "<h5><i class='fas fa-trash'></i> Paso 2: Limpiando base de datos anterior...</h5>";
                $pdo->exec("DROP DATABASE IF EXISTS $dbname");
                echo "<p class='text-success mb-0'>✅ Base de datos anterior eliminada</p>";
                echo "</div>";
                
                // Paso 3: Crear nueva base de datos
                echo "<div class='step info'>";
                echo "<h5><i class='fas fa-database'></i> Paso 3: Creando base de datos...</h5>";
                $pdo->exec("CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE $dbname");
                echo "<p class='text-success mb-0'>✅ Base de datos '$dbname' creada</p>";
                echo "</div>";
                
                // Paso 4: Leer y ejecutar el SQL
                echo "<div class='step info'>";
                echo "<h5><i class='fas fa-file-code'></i> Paso 4: Ejecutando schema SQL...</h5>";
                
                $sqlFile = __DIR__ . '/database/schema.sql';
                if (!file_exists($sqlFile)) {
                    throw new Exception("No se encuentra el archivo schema.sql");
                }
                
                $sql = file_get_contents($sqlFile);
                
                // Dividir por punto y coma y ejecutar cada statement
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($s) { return !empty($s) && !preg_match('/^--/', $s); }
                );
                
                $executed = 0;
                foreach ($statements as $statement) {
                    if (stripos($statement, 'CREATE DATABASE') !== false || 
                        stripos($statement, 'USE ') === 0) {
                        continue; // Ya lo hicimos arriba
                    }
                    
                    try {
                        $pdo->exec($statement);
                        $executed++;
                    } catch (PDOException $e) {
                        echo "<small class='text-warning d-block'>⚠️ " . substr($statement, 0, 50) . "...</small>";
                    }
                }
                
                echo "<p class='text-success mb-0'>✅ $executed sentencias SQL ejecutadas</p>";
                echo "</div>";
                
                // Paso 5: Verificar tablas creadas
                echo "<div class='step success'>";
                echo "<h5><i class='fas fa-check-circle'></i> Paso 5: Verificando tablas...</h5>";
                
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                echo "<p>📋 Tablas creadas: <strong>" . count($tables) . "</strong></p>";
                echo "<div class='row'>";
                foreach ($tables as $table) {
                    echo "<div class='col-md-4'><small>✓ $table</small></div>";
                }
                echo "</div>";
                echo "</div>";
                
                // Paso 6: Contar registros
                echo "<div class='step success'>";
                echo "<h5><i class='fas fa-database'></i> Paso 6: Verificando datos...</h5>";
                
                $usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
                $productos = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
                $categorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
                $clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
                
                echo "<div class='row text-center'>";
                echo "<div class='col-md-3'><h3>$usuarios</h3><small>Usuarios</small></div>";
                echo "<div class='col-md-3'><h3>$categorias</h3><small>Categorías</small></div>";
                echo "<div class='col-md-3'><h3>$productos</h3><small>Productos</small></div>";
                echo "<div class='col-md-3'><h3>$clientes</h3><small>Clientes</small></div>";
                echo "</div>";
                echo "</div>";
                
                // Paso 7: Resetear contraseñas
                echo "<div class='step info'>";
                echo "<h5><i class='fas fa-key'></i> Paso 7: Configurando usuarios...</h5>";
                
                $passwordHash = password_hash('password', PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("UPDATE usuarios SET contraseña = ? WHERE nombre_usuario IN ('admin', 'cajero1')");
                $stmt->execute([$passwordHash]);
                
                echo "<p class='mb-1'>✅ Contraseñas actualizadas</p>";
                echo "<small>Hash: <code>" . substr($passwordHash, 0, 40) . "...</code></small>";
                echo "</div>";
                
                // Resultado final
                echo "<div class='step success'>";
                echo "<h3 class='text-success'><i class='fas fa-check-circle'></i> ¡Instalación Completada!</h3>";
                echo "<hr>";
                echo "<h5>🔐 Credenciales de Acceso:</h5>";
                echo "<div class='row'>";
                echo "<div class='col-md-6'>";
                echo "<strong>Usuario Admin:</strong><br>";
                echo "Usuario: <code>admin</code><br>";
                echo "Contraseña: <code>password</code>";
                echo "</div>";
                echo "<div class='col-md-6'>";
                echo "<strong>Usuario Cajero:</strong><br>";
                echo "Usuario: <code>cajero1</code><br>";
                echo "Contraseña: <code>password</code>";
                echo "</div>";
                echo "</div>";
                echo "<hr>";
                echo "<div class='d-grid gap-2'>";
                echo "<a href='/Sistema%20de%20venta/' class='btn btn-primary btn-lg'>";
                echo "<i class='fas fa-sign-in-alt'></i> Ir al Login";
                echo "</a>";
                echo "<a href='/Sistema%20de%20venta/utils/diagnostico.php' class='btn btn-outline-secondary'>";
                echo "<i class='fas fa-stethoscope'></i> Ver Diagnóstico";
                echo "</a>";
                echo "</div>";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='step error'>";
                echo "<h5><i class='fas fa-exclamation-circle'></i> Error</h5>";
                echo "<p class='text-danger'>" . $e->getMessage() . "</p>";
                echo "<pre>" . $e->getTraceAsString() . "</pre>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
