<?php
/**
 * API para calcular precio de venta
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/BaseModel.php';
require_once __DIR__ . '/models/Producto.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $productoModel = new Producto();
    
    // Obtener datos
    $modo = $_GET['modo'] ?? 'bulto';
    
    if ($modo === 'bulto') {
        // Calcular desde bulto/paquete
        $precioMayoreo = floatval($_GET['precio_mayoreo'] ?? 0);
        $unidades = intval($_GET['unidades'] ?? 1);
        $porcentaje = floatval($_GET['porcentaje'] ?? 0);
        
        if ($precioMayoreo <= 0 || $unidades <= 0) {
            throw new Exception('Datos inválidos');
        }
        
        $resultado = $productoModel->calcularPrecioDesdeBuilto($precioMayoreo, $unidades, $porcentaje);
        
    } else {
        // Calcular desde costo directo
        $costo = floatval($_GET['costo'] ?? 0);
        $porcentaje = floatval($_GET['porcentaje'] ?? 0);
        
        if ($costo <= 0) {
            throw new Exception('Costo inválido');
        }
        
        $resultado = $productoModel->calcularPrecioDesdeCosto($costo, $porcentaje);
    }
    
    echo json_encode([
        'success' => true,
        'resultado' => $resultado
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
