<?php
/**
 * API Configuración del Sistema
 * GET  ?action=get              → Leer todas las claves
 * POST ?action=save             → Guardar clave/valor
 */
ob_start();
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

header('Content-Type: application/json; charset=utf-8');

// Solo admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'get';

try {
    $pdo = Database::getInstance()->getConnection();

    // Verificar que la tabla existe
    $tablas = $pdo->query("SHOW TABLES LIKE 'configuracion'")->fetchAll();
    if (empty($tablas)) {
        // Crear la tabla si no existe
        $pdo->exec("CREATE TABLE IF NOT EXISTS configuracion (
            clave VARCHAR(100) PRIMARY KEY,
            valor TEXT,
            descripcion VARCHAR(255)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Valores por defecto
        $defaults = [
            ['app_nombre',     'AbasPOS',         'Nombre del sistema'],
            ['app_subtitulo',  'Punto de Venta',  'Subtítulo del sistema'],
            ['app_logo_icono', 'fa-store',         'Icono FontAwesome'],
        ];
        $s = $pdo->prepare("INSERT IGNORE INTO configuracion (clave,valor,descripcion) VALUES(?,?,?)");
        foreach ($defaults as $d) $s->execute($d);
    }

    if ($method === 'GET' && $action === 'get') {
        $rows = $pdo->query("SELECT clave, valor FROM configuracion")->fetchAll(PDO::FETCH_KEY_PAIR);
        ob_end_clean();
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    if ($method === 'POST' && $action === 'save') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Datos inválidos']); exit; }

        $stmt = $pdo->prepare("INSERT INTO configuracion (clave, valor) VALUES (?,?) ON DUPLICATE KEY UPDATE valor=VALUES(valor)");
        $saved = 0;
        foreach ($body as $clave => $valor) {
            // Solo claves permitidas
            $allowed = ['app_nombre','app_subtitulo','app_logo_icono','tasa_default'];
            if (!in_array($clave, $allowed)) continue;
            $stmt->execute([$clave, trim($valor)]);
            $saved++;
        }

        ob_end_clean();
        echo json_encode(['success' => true, 'message' => "$saved valor(es) guardado(s)"]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Acción no válida']);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
