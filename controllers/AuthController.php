<?php
/**
 * ===================================
 * AuthController - Control de autenticación
 * ===================================
 * Maneja login, logout y validación de sesiones
 */


class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Manejo de rutas de autenticación
     */
    public static function route() {
        $controller = new self();
        $action = $_GET['action'] ?? 'index';

        switch ($action) {
            case 'login':
                $controller->login();
                break;
            case 'logout':
                $controller->logout();
                break;
            case 'profile':
                $controller->profile();
                break;
            case 'change-password':
                $controller->changePassword();
                break;
            case 'register':
                $controller->register();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Acción no encontrada']);
                break;
        }
    }

    /**
     * Login - Autenticación de usuario
     */
    private function login() {
        // Solo POST permitido
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        // Obtener datos JSON
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar campos requeridos
        if (!isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Usuario y contraseña requeridos']);
            return;
        }

        $username = trim($data['username']);
        $password = $data['password'];

        // Validar que no estén vacíos
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Usuario y contraseña no pueden estar vacíos']);
            return;
        }

        // Buscar usuario
        $usuario = $this->usuarioModel->findByUsername($username);

        if (!$usuario) {
            // No registrar intento de login fallido por seguridad
            http_response_code(401);
            echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
            return;
        }

        // Verificar contraseña
        if (!$this->usuarioModel->verifyPassword($password, $usuario['contraseña'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
            return;
        }

        // Crear sesión
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
        $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        // Respuesta exitosa
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'usuario' => [
                'id' => $usuario['id_usuario'],
                'nombre_usuario' => $usuario['nombre_usuario'],
                'nombre_completo' => $usuario['nombre_completo'],
                'rol' => $usuario['rol'],
                'email' => $usuario['email']
            ]
        ]);
    }

    /**
     * Logout - Cerrar sesión
     */
    private function logout() {
        // Limpiar variables de sesión
        $usuarioId = $_SESSION['usuario_id'] ?? null;

        $_SESSION = [];

        // Destruir cookie de sesión
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destruir sesión
        session_destroy();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }

    /**
     * Obtener perfil del usuario actual
     */
    private function profile() {
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $usuario = $this->usuarioModel->findById($_SESSION['usuario_id']);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }

        // Obtener estadísticas
        $stats = $this->usuarioModel->getStats($usuario['id_usuario']);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'usuario' => [
                'id' => $usuario['id_usuario'],
                'nombre_usuario' => $usuario['nombre_usuario'],
                'nombre_completo' => $usuario['nombre_completo'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol'],
                'activo' => $usuario['activo'],
                'fecha_creacion' => $usuario['fecha_creacion']
            ],
            'estadisticas' => $stats
        ]);
    }

    /**
     * Cambiar contraseña
     */
    private function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Validar campos
        if (!isset($data['password_actual']) || !isset($data['password_nueva']) || !isset($data['password_confirmar'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan campos requeridos']);
            return;
        }

        // Obtener usuario actual
        $usuario = $this->usuarioModel->findById($_SESSION['usuario_id']);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }

        // Verificar contraseña actual
        if (!$this->usuarioModel->verifyPassword($data['password_actual'], $usuario['contraseña'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Contraseña actual incorrecta']);
            return;
        }

        // Validar nueva contraseña
        if (strlen($data['password_nueva']) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'La nueva contraseña debe tener al menos 6 caracteres']);
            return;
        }

        if ($data['password_nueva'] !== $data['password_confirmar']) {
            http_response_code(400);
            echo json_encode(['error' => 'Las contraseñas no coinciden']);
            return;
        }

        // Actualizar contraseña
        $result = $this->usuarioModel->updateUser($_SESSION['usuario_id'], [
            'contraseña' => $data['password_nueva']
        ]);

        if ($result['success']) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada exitosamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $result['message']]);
        }
    }

    /**
     * Validar sesión activa
     */
    public static function checkSession() {
        if (!isset($_SESSION['usuario_id'])) {
            return false;
        }

        // Verificar timeout de sesión
        $timeout = SESSION_LIFETIME * 60;
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
            session_destroy();
            return false;
        }

        // Verificar cambio de IP (seguridad adicional)
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            // En desarrollo, permitir. En producción, rechazar.
            if (!DEBUG_MODE) {
                session_destroy();
                return false;
            }
        }

        // Renovar tiempo de sesión
        $_SESSION['login_time'] = time();

        return true;
    }

    /**
     * Verificar rol de usuario
     */
    public static function hasRole($role) {
        if (!isset($_SESSION['rol'])) {
            return false;
        }

        if (is_array($role)) {
            return in_array($_SESSION['rol'], $role);
        }

        return $_SESSION['rol'] === $role;
    }

    /**
     * Registrar nuevo usuario (solo admin autenticado)
     */
    private function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); echo json_encode(['error'=>'Método no permitido']); return;
        }
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
            http_response_code(403); echo json_encode(['error'=>'No autorizado']); return;
        }
        $data = json_decode(file_get_contents('php://input'), true);

        // Aceptar contraseña enviada como 'password', 'contrasena' o 'contraseña'
        $pass = $data['contraseña'] ?? $data['contrasena'] ?? $data['password'] ?? null;

        if (!$data || empty($data['nombre_usuario']) || !$pass || empty($data['email']) || empty($data['rol'])) {
            http_response_code(400); echo json_encode(['error'=>'Faltan campos requeridos (nombre_usuario, password, email, rol)']); return;
        }
        if (!in_array($data['rol'], ['admin','cajero'])) {
            http_response_code(400); echo json_encode(['error'=>'Rol inválido. Use: admin o cajero']); return;
        }

        // Normalizar al campo que el modelo espera
        $data['password'] = $pass;

        $r = $this->usuarioModel->create($data);
        if ($r['success']) {
            http_response_code(201);
            echo json_encode(['success'=>true, 'message'=>$r['message'], 'data'=>['id'=>$r['id']]]);
        } else {
            http_response_code(409);
            echo json_encode(['success'=>false, 'error'=>$r['message']]);
        }
    }
}
?>
