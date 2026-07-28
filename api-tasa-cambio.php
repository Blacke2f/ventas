<?php
/**
 * API de Tasa de Cambio
 * Retorna la tasa USD a Bs actual
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/TasaCambio.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $tasaCambioModel = new TasaCambio();
    
    // Verificar si se solicita actualización forzada
    $force = isset($_GET['force']) && $_GET['force'] == '1';
    
    if ($force) {
        $tasa = $tasaCambioModel->forceUpdate();
    } else {
        $tasa = $tasaCambioModel->getTasa();
    }
    
    $lastUpdate = $tasaCambioModel->getLastUpdate();
    
    echo json_encode([
        'success' => true,
        'tasa' => $tasa,
        'tasa_formateada' => $tasaCambioModel->formatBs($tasa),
        'ultima_actualizacion' => $lastUpdate['hace'] ?? 'Hace un momento',
        'timestamp' => $lastUpdate['timestamp'] ?? time(),
        'fecha' => $lastUpdate['fecha'] ?? date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener la tasa de cambio',
        'message' => DEBUG_MODE ? $e->getMessage() : null
    ], JSON_UNESCAPED_UNICODE);
}
?>
