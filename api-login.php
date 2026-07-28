<?php
/**
 * API de Login
 * Funciona en localhost, red local (192.168.x.x) y Docker
 */
ob_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/BaseModel.php';
require_once __DIR__ . '/models/Usuario.php';

// ── Sesión compatible con cualquier host ─────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',       // vacío = dominio actual (funciona en IP y hostnames)
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// ── Headers ───────────────────────────────────────────────────────
// Permitir el origen exacto que hace la petición (necesario con credentials)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); ob_end_clean(); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE || !$data) {
        ob_end_clean(); http_response_code(400);
        echo json_encode(['error' => 'JSON inválido']); exit;
    }

    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (!$username || !$password) {
        ob_end_clean(); http_response_code(400);
        echo json_encode(['error' => 'Usuario y contraseña requeridos']); exit;
    }

    $usuarioModel = new Usuario();
    $usuario      = $usuarioModel->findByUsername($username);

    if (!$usuario) {
        ob_end_clean(); http_response_code(401);
        echo json_encode(['error' => 'Usuario o contraseña incorrectos']); exit;
    }

    // Buscar contraseña en ambas columnas (password o contrasena)
    $passwordHash = $usuario['password'] ?? $usuario['contraseña'] ?? $usuario['contrasena'] ?? null;
    
    if (!$passwordHash || !$usuarioModel->verifyPassword($password, $passwordHash)) {
        ob_end_clean(); http_response_code(401);
        echo json_encode(['error' => 'Usuario o contraseña incorrectos']); exit;
    }

    // Guardar sesión
    $_SESSION['usuario_id']     = $usuario['id_usuario'];
    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
    $_SESSION['nombre_completo']= $usuario['nombre_completo'];
    $_SESSION['rol']            = $usuario['rol'];
    $_SESSION['email']          = $usuario['email'];
    $_SESSION['login_time']     = time();
    $_SESSION['ip_address']     = $_SERVER['REMOTE_ADDR'] ?? '';
    $_SESSION['user_agent']     = $_SERVER['HTTP_USER_AGENT'] ?? '';

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login exitoso',
        'usuario' => [
            'id'              => $usuario['id_usuario'],
            'nombre_usuario'  => $usuario['nombre_usuario'],
            'nombre_completo' => $usuario['nombre_completo'],
            'rol'             => $usuario['rol'],
            'email'           => $usuario['email'],
        ]
    ]);

} catch (RuntimeException $e) {
    // Error de conexión a la base de datos (Docker: MySQL aún no está listo)
    ob_end_clean();
    http_response_code(503);
    echo json_encode([
        'error'   => 'Base de datos no disponible',
        'message' => 'El servidor de base de datos no está listo. Espera unos segundos y reintenta.',
        'detalle' => defined('DEBUG_MODE') && DEBUG_MODE ? $e->getMessage() : null,
    ]);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'error'   => 'Error interno del servidor',
        'message' => defined('DEBUG_MODE') && DEBUG_MODE ? $e->getMessage() : 'Error en el servidor',
    ]);
}
?>
