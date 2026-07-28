<?php
/**
 * AbasPOS - Punto de Entrada Principal
 */

// Configuración y base de datos
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

// ── Cargar TODOS los modelos al inicio ──────────────────────────────────────
require_once MODELS_PATH . 'BaseModel.php';
require_once MODELS_PATH . 'Utils.php';
require_once MODELS_PATH . 'Usuario.php';
require_once MODELS_PATH . 'Producto.php';
require_once MODELS_PATH . 'Cliente.php';
require_once MODELS_PATH . 'Venta.php';
require_once MODELS_PATH . 'Credito.php';
require_once MODELS_PATH . 'TasaCambio.php';

// ── Cargar TODOS los controladores al inicio ─────────────────────────────────
require_once CONTROLLERS_PATH . 'AuthController.php';
require_once CONTROLLERS_PATH . 'ProductosController.php';
require_once CONTROLLERS_PATH . 'ClientesController.php';
require_once CONTROLLERS_PATH . 'VentasController.php';
require_once CONTROLLERS_PATH . 'CreditosController.php';

// ── Sesión ───────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// ── Routing ──────────────────────────────────────────────────────────────────
$requestUri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Detectar la ruta base: en Docker es '/', en Laragon es '/Sistema de venta'
if (strpos($requestUri, '/Sistema') === 0 || strpos($requestUri, 'Sistema%20de%20venta') !== false) {
    $basePath = '/Sistema de venta';
} else {
    $basePath = '/';
}

$route      = trim(str_replace($basePath, '', $requestUri), '/');

$routeParts = explode('/', $route);
$action     = $routeParts[0] ?? '';

// Detectar si es una ruta de API
$isApiRoute = (strpos($route, 'api/') === 0);

// Cabeceras para APIs
if ($isApiRoute) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
}

// Rutas públicas (sin autenticación)
$isPublic = ($action === 'login') || strpos($route, 'api/auth') === 0;

// Verificar autenticación
if (!$isPublic && !AuthController::checkSession()) {
    if ($isApiRoute) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => MSG_UNAUTHORIZED]);
        exit;
    }
    header('Location: ' . APP_URL . '/login');
    exit;
}

// Raíz → redirigir
if ($route === '') {
    header('Location: ' . APP_URL . (AuthController::checkSession() ? '/dashboard' : '/login'));
    exit;
}

// ── Switch de rutas ───────────────────────────────────────────────────────────
switch (true) {

    // Login
    case $action === 'login':
        if (AuthController::checkSession()) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        require VIEWS_PATH . 'auth/login.php';
        break;

    // API Auth
    case strpos($route, 'api/auth') === 0:
        $_GET['action'] = $routeParts[2] ?? 'index';
        AuthController::route();
        break;

    // API Productos
    case strpos($route, 'api/productos') === 0:
        ProductosController::route();
        break;

    // API Clientes
    case strpos($route, 'api/clientes') === 0:
        ClientesController::route();
        break;

    // API Ventas
    case strpos($route, 'api/ventas') === 0:
        VentasController::route();
        break;

    // API Créditos
    case strpos($route, 'api/creditos') === 0:
        CreditosController::route();
        break;

    // Dashboard
    case $action === 'dashboard':
        require VIEWS_PATH . 'dashboard/index.php';
        break;

    // POS
    case $action === 'pos':
        require VIEWS_PATH . 'pos/index.php';
        break;

    // Clientes
    case $action === 'clientes':
        require VIEWS_PATH . 'clientes/index.php';
        break;

    // Créditos
    case $action === 'creditos':
        require VIEWS_PATH . 'creditos/index.php';
        break;

    // Ventas
    case $action === 'ventas':
        require VIEWS_PATH . 'ventas/index.php';
        break;

    // Reportes
    case $action === 'reportes':
        require VIEWS_PATH . 'reportes/index.php';
        break;

    // Productos (solo admin)
    case $action === 'productos':
        if (($_SESSION['rol'] ?? '') !== 'admin') {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        require VIEWS_PATH . 'productos/index.php';
        break;

    // Configuración (solo admin)
    case $action === 'configuracion':
        if (($_SESSION['rol'] ?? '') !== 'admin') {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        require VIEWS_PATH . 'configuracion/index.php';
        break;

    // Manual de uso
    case $action === 'manual':
        require VIEWS_PATH . 'manual/index.php';
        break;

    // 404
    default:
        if ($isApiRoute) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => MSG_NOT_FOUND]);
        } else {
            http_response_code(404);
            echo '<div style="font-family:sans-serif;text-align:center;padding:50px"><h1>404</h1><p>Página no encontrada</p><a href="' . APP_URL . '">Ir al inicio</a></div>';
        }
        break;
}
?>
