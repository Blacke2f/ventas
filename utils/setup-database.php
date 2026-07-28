<?php
/**
 * Script de configuración de base de datos
 * Ejecuta este archivo para crear/verificar la base de datos
 */

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// Usar variables de entorno o defaults
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: 3306;
$dbname = getenv('DB_NAME') ?: 'gastropos';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

echo "<!DOCTYPE html>";
echo "<html lang='es'><head>";
echo "<meta charset='UTF-8'>";
echo "<title>Setup Database - GastroPOS</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h1 { color: #667eea; }
.success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
.error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
.info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0; }
.btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px 0 0; }
.btn:hover { background: #5568d3; }
pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style>";
echo "</head><body><div class='container'>";

echo "<h1>🔧 Configuración de Base de Datos - AbasPOS</h1>";

try {
    // Conectar sin seleccionar base de datos
    echo "<div class='info'>📡 Conectando al servidor MySQL en $host:$port...</div>";
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    echo "<div class='success'>✅ Conexión exitosa al servidor MySQL</div>";

    // Verificar si existe la base de datos
    $result = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    $exists = $result->rowCount() > 0;

    if ($exists) {
        echo "<div class='info'>📊 La base de datos '$dbname' ya existe</div>";
        
        // Conectar a la base de datos
        $pdo->exec("USE $dbname");
        
        // Verificar tablas
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "<div class='info'>📋 Tablas encontradas: " . count($tables) . "</div>";
        
        if (count($tables) > 0) {
            echo "<pre>";
            foreach ($tables as $table) {
                echo "- $table\n";
            }
            echo "</pre>";
        }
        
        // Verificar usuarios
        $usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        echo "<div class='info'>👥 Usuarios registrados: $usuarios</div>";
        
        if ($usuarios > 0) {
            $users = $pdo->query("SELECT nombre_usuario, rol, nombre_completo FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>";
            foreach ($users as $u) {
                echo "- {$u['nombre_usuario']} ({$u['rol']}) - {$u['nombre_completo']}\n";
            }
            echo "</pre>";
        }
        
    } else {
        echo "<div class='info'>🆕 La base de datos '$dbname' no existe. Creando...</div>";
        
        // Leer y ejecutar el SQL
        $sqlFile = __DIR__ . '/../database/schema.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("No se encuentra el archivo schema.sql en /database/");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Ejecutar el script SQL completo
        $pdo->exec($sql);
        
        echo "<div class='success'>✅ Base de datos creada exitosamente</div>";
        echo "<div class='info'>🏪 Se han agregado <strong>150+ productos de abarrotes</strong> venezolanos</div>";
        echo "<div class='info'>📂 Se han creado <strong>15 categorías</strong>: Abarrotes, Enlatados, Lácteos, Botanas, Confitería, Harinas, Frutas/Verduras, Bebidas, Licores, Carnes, Automedicación, Higiene, Limpieza, Helados, Jarcería</div>";
        echo "<div class='info'>👤 Usuario admin creado con contraseña: <strong>password</strong></div>";
        echo "<div class='info'>👤 Usuario cajero1 creado con contraseña: <strong>password</strong></div>";
        echo "<div class='info'>💵 Los precios están en <strong>dólares (USD)</strong> y se convierten automáticamente a <strong>bolívares (Bs)</strong> con la tasa del BCV</div>";
    }
    
    // Verificar datos de prueba
    $pdo->exec("USE $dbname");
    $productos = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
    $clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    $categorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    
    echo "<div class='success'>✅ Configuración completa</div>";
    echo "<div class='info'>📦 Productos: $productos | 👥 Clientes: $clientes | 📂 Categorías: $categorias</div>";
    
    echo "<h2>🔑 Credenciales de Acceso</h2>";
    echo "<pre>";
    echo "Usuario Admin:\n";
    echo "  Usuario: admin\n";
    echo "  Contraseña: password\n\n";
    echo "Usuario Cajero:\n";
    echo "  Usuario: cajero1\n";
    echo "  Contraseña: password\n";
    echo "</pre>";
    
    echo "<h2>🚀 Siguiente Paso</h2>";
    echo "<a href='/Sistema%20de%20venta/' class='btn'>Ir a la Aplicación</a>";
    echo "<a href='/Sistema%20de%20venta/diagnostico.php' class='btn'>Ver Diagnóstico</a>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    echo "<div class='info'>Verifica que MySQL esté corriendo (host: $host:$port)</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
