<?php
/**
 * Verificar conexión a la base de datos (útil para Docker)
 * Acceder: http://IP:8080/utils/wait-for-db.php
 */
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/config/config.php';

$intentos  = 0;
$maxIntentos = 10;
$conectado = false;

while ($intentos < $maxIntentos && !$conectado) {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4',
            DB_USER,
            DB_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
        );
        $conectado = true;
    } catch (PDOException $e) {
        $intentos++;
        sleep(2);
    }
}

if ($conectado) {
    // Verificar si la BD existe
    $dbs = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'")->fetchAll();
    $bdExiste = !empty($dbs);

    echo json_encode([
        'success'        => true,
        'db_host'        => DB_HOST,
        'db_name'        => DB_NAME,
        'db_conectado'   => true,
        'bd_existe'      => $bdExiste,
        'app_url'        => APP_URL,
        'mensaje'        => $bdExiste
            ? 'Base de datos lista'
            : 'Conectado a MySQL pero la BD no existe. Ejecuta /install-complete.php',
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(503);
    echo json_encode([
        'success'      => false,
        'db_host'      => DB_HOST,
        'db_name'      => DB_NAME,
        'db_conectado' => false,
        'mensaje'      => 'No se pudo conectar a MySQL después de ' . ($intentos * 2) . ' segundos',
    ], JSON_PRETTY_PRINT);
}
?>
