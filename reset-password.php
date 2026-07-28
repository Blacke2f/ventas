<?php
/**
 * Script para resetear contraseñas
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html lang='es'><head>";
echo "<meta charset='UTF-8'>";
echo "<title>Reset Password - AbasPOS</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.container { max-width: 600px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); }
h1 { color: #667eea; margin-bottom: 20px; }
.success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
.error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
.info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
.btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style>";
echo "</head><body><div class='container'>";

echo "<h1>🔑 Resetear Contraseñas</h1>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<div class='info'>📡 Conectado a la base de datos...</div>";
    
    // Verificar si existe la tabla usuarios
    $tables = $conn->query("SHOW TABLES LIKE 'usuarios'")->fetchAll();
    
    if (empty($tables)) {
        echo "<div class='error'>❌ La tabla 'usuarios' no existe.</div>";
        echo "<div class='info'>Ejecuta primero: <a href='utils/setup-database.php'>setup-database.php</a></div>";
        exit;
    }
    
    // Generar nuevas contraseñas hasheadas
    $passwordHash = password_hash('password', PASSWORD_DEFAULT);
    
    echo "<div class='info'>🔐 Generando nuevas contraseñas...</div>";
    
    // Actualizar usuario admin
    $stmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE nombre_usuario = 'admin'");
    $stmt->execute([$passwordHash]);
    
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ Contraseña de 'admin' actualizada</div>";
    } else {
        // Si no existe, crear usuario admin
        $stmt = $conn->prepare("
            INSERT INTO usuarios (nombre_usuario, contraseña, email, rol, nombre_completo, activo) 
            VALUES ('admin', ?, 'admin@abaspos.com', 'admin', 'Administrador Principal', 1)
        ");
        $stmt->execute([$passwordHash]);
        echo "<div class='success'>✅ Usuario 'admin' creado</div>";
    }
    
    // Actualizar usuario cajero1
    $stmt = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE nombre_usuario = 'cajero1'");
    $stmt->execute([$passwordHash]);
    
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ Contraseña de 'cajero1' actualizada</div>";
    } else {
        // Si no existe, crear usuario cajero
        $stmt = $conn->prepare("
            INSERT INTO usuarios (nombre_usuario, contraseña, email, rol, nombre_completo, activo) 
            VALUES ('cajero1', ?, 'cajero1@abaspos.com', 'cajero', 'Juan Pérez', 1)
        ");
        $stmt->execute([$passwordHash]);
        echo "<div class='success'>✅ Usuario 'cajero1' creado</div>";
    }
    
    echo "<h2>✅ Contraseñas Actualizadas</h2>";
    echo "<div class='info'>";
    echo "<strong>Credenciales de acceso:</strong><br><br>";
    echo "<strong>Usuario Admin:</strong><br>";
    echo "• Usuario: <code>admin</code><br>";
    echo "• Contraseña: <code>password</code><br><br>";
    echo "<strong>Usuario Cajero:</strong><br>";
    echo "• Usuario: <code>cajero1</code><br>";
    echo "• Contraseña: <code>password</code><br>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "Hash generado (para verificación):<br>";
    echo "<pre>$passwordHash</pre>";
    echo "</div>";
    
    // Probar la contraseña
    if (password_verify('password', $passwordHash)) {
        echo "<div class='success'>✅ Verificación exitosa: La contraseña 'password' funciona correctamente</div>";
    } else {
        echo "<div class='error'>❌ Error en la verificación</div>";
    }
    
    echo "<a href='/Sistema%20de%20venta/views/auth/login.php' class='btn'>Ir al Login</a>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div></body></html>";
?>
