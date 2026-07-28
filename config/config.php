<?php
/**
 * AbasPOS — Configuración Global
 * Compatible con: Laragon, red local, Docker, hosting
 */

// ── Cargar variables de entorno ───────────────────────────────────
require_once __DIR__ . '/env.php';

// ── Zona horaria ──────────────────────────────────────────────────
date_default_timezone_set('America/Caracas');

// ── Debug ─────────────────────────────────────────────────────────
define('DEBUG_MODE', (bool)(getenv('APP_DEBUG') ?: true));

// ── Rutas del servidor ────────────────────────────────────────────
define('BASE_PATH',        dirname(__DIR__));
define('CONTROLLERS_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR);
define('MODELS_PATH',      BASE_PATH . DIRECTORY_SEPARATOR . 'models'      . DIRECTORY_SEPARATOR);
define('VIEWS_PATH',       BASE_PATH . DIRECTORY_SEPARATOR . 'views'       . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH',      BASE_PATH . DIRECTORY_SEPARATOR . 'public'      . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH',     PUBLIC_PATH . 'uploads' . DIRECTORY_SEPARATOR);

// ── Base de Datos ─────────────────────────────────────────────────
// Prioridad: variable de entorno Docker → valor por defecto Laragon
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_PORT',     (int)(getenv('DB_PORT') ?: 3306));
define('DB_NAME',     getenv('DB_NAME')     ?: 'abastospos');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_CHARSET',  'utf8mb4');

// ── APP_URL dinámica ──────────────────────────────────────────────
// Se calcula en tiempo real según el host que hace la petición.
// Funciona automáticamente en: localhost, 192.168.x.x, Docker, dominios.
if (!defined('APP_URL')) {

    // Solo se puede calcular si hay una request HTTP activa
    if (!empty($_SERVER['HTTP_HOST'])) {

        // Protocolo
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['SERVER_PORT'] ?? 80) == 443)
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $scheme = $isHttps ? 'https' : 'http';

        // Host con puerto (incluye el puerto si no es el estándar)
        $host = $_SERVER['HTTP_HOST'];

        // Calcular la subcarpeta del proyecto relativa al document root
        $docRoot  = !empty($_SERVER['DOCUMENT_ROOT'])
                    ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/')
                    : '';
        $basePath = rtrim(str_replace('\\', '/', BASE_PATH), '/');

        if ($docRoot && strpos($basePath, $docRoot) === 0) {
            $subPath = substr($basePath, strlen($docRoot));
        } else {
            // Docker o entorno sin DOCUMENT_ROOT: sin subcarpeta
            $subPath = '';
        }

        define('APP_URL', $scheme . '://' . $host . rtrim($subPath, '/'));

    } else {
        // Contexto CLI o sin request: usar valor estático
        define('APP_URL', 'http://localhost/Sistema de venta');
    }
}

// ── Aplicación ────────────────────────────────────────────────────
define('APP_NAME',        getenv('APP_NAME') ?: 'AbasPOS');
define('APP_VERSION',     '1.0.0');
define('APP_DESCRIPTION', 'Sistema de Punto de Venta para Abastos');
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE',   'USD');

// ── Sesión ────────────────────────────────────────────────────────
define('SESSION_LIFETIME', (int)(getenv('SESSION_LIFETIME') ?: 1440));
define('COOKIE_PATH',      '/');

// ── Seguridad ─────────────────────────────────────────────────────
define('BCRYPT_ROUNDS',  (int)(getenv('BCRYPT_ROUNDS') ?: 10));
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: 'abaspos_secret_2026');

// ── Mensajes ──────────────────────────────────────────────────────
define('MSG_SUCCESS',      'Operación exitosa');
define('MSG_ERROR',        'Ocurrió un error');
define('MSG_UNAUTHORIZED', 'No autorizado');
define('MSG_NOT_FOUND',    'No encontrado');

// ── Upload ────────────────────────────────────────────────────────
define('MAX_UPLOAD_SIZE',     5242880);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
?>
