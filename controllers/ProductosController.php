<?php
/**
 * ===================================
 * ProductosController - API REST
 * ===================================
 */


class ProductosController {
    private $productoModel;

    public function __construct() {
        $this->productoModel = new Producto();
    }

    /**
     * Manejo de rutas
     */
    public static function route() {
        $controller = new self();
        
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? 'list';

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        $controller->list();
                        break;
                    case 'get':
                        $controller->getOne();
                        break;
                    case 'search':
                        $controller->search();
                        break;
                    case 'categoria':
                        $controller->getByCategoria();
                        break;
                    case 'categorias':
                        $controller->getCategorias();
                        break;
                    case 'barcode':
                        $controller->findByBarcode();
                        break;
                    case 'stock-bajo':
                        $controller->getStockBajo();
                        break;
                    case 'top-vendidos':
                        $controller->getTopVendidos();
                        break;
                    case 'grid':
                        $controller->getProductosGrid();
                        break;
                    default:
                        $controller->notFound();
                }
                break;

            case 'POST':
                switch ($action) {
                    case 'create':
                        $controller->create();
                        break;
                    case 'categoria-create':
                        $controller->createCategoria();
                        break;
                    default:
                        $controller->notFound();
                }
                break;

            case 'PUT':
                switch ($action) {
                    case 'update':
                        $controller->update();
                        break;
                    case 'stock':
                        $controller->updateStock();
                        break;
                    default:
                        $controller->notFound();
                }
                break;

            case 'DELETE':
                switch ($action) {
                    case 'delete':
                        $controller->delete();
                        break;
                    default:
                        $controller->notFound();
                }
                break;

            default:
                http_response_code(405);
                $this->json(['error' => 'Método no permitido']);
        }
    }

    /**
     * Obtener todos los productos
     */
    private function list() {
        try {
            $productos = $this->productoModel->getAllActivos();
            
            $this->success('Productos obtenidos', [
                'productos' => $productos,
                'total' => count($productos)
            ]);
        } catch (Exception $e) {
            $this->error('Error al obtener productos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener un producto
     */
    private function getOne() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->error('ID requerido', 400);
            return;
        }

        try {
            $producto = $this->productoModel->getDetalles($id);

            if (!$producto) {
                $this->error('Producto no encontrado', 404);
                return;
            }

            $this->success('Producto obtenido', $producto);
        } catch (Exception $e) {
            $this->error('Error al obtener producto: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Buscar productos
     */
    private function search() {
        $q = $_GET['q'] ?? '';

        if (strlen($q) < 2) {
            $this->error('Búsqueda debe tener al menos 2 caracteres', 400);
            return;
        }

        try {
            $productos = $this->productoModel->searchByName($q);
            
            $this->success('Búsqueda completada', [
                'productos' => $productos,
                'total' => count($productos)
            ]);
        } catch (Exception $e) {
            $this->error('Error en búsqueda: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener por categoría
     */
    private function getByCategoria() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->error('ID de categoría requerido', 400);
            return;
        }

        try {
            $productos = $this->productoModel->getByCategoria($id);
            
            $this->success('Productos por categoría obtenidos', [
                'productos' => $productos,
                'total' => count($productos)
            ]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener categorías
     */
    private function getCategorias() {
        try {
            $categorias = $this->productoModel->getCategorias();
            
            $this->success('Categorías obtenidas', [
                'categorias' => $categorias,
                'total' => count($categorias)
            ]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Buscar por código de barras
     */
    private function findByBarcode() {
        $codigo = $_GET['codigo'] ?? null;

        if (!$codigo) {
            $this->error('Código de barras requerido', 400);
            return;
        }

        try {
            $producto = $this->productoModel->findByBarcode($codigo);

            if (!$producto) {
                $this->error('Producto no encontrado', 404);
                return;
            }

            $this->success('Producto encontrado', ['producto' => $producto]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Stock bajo
     */
    private function getStockBajo() {
        try {
            $productos = $this->productoModel->getStockBajo();
            
            $this->success('Productos con stock bajo', [
                'productos' => $productos,
                'total' => count($productos)
            ]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Top vendidos
     */
    private function getTopVendidos() {
        $limit = $_GET['limit'] ?? 10;

        try {
            $productos = $this->productoModel->getTopVendidos($limit);
            
            $this->success('Top vendidos obtenido', [
                'productos' => $productos,
                'total' => count($productos)
            ]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Grid de productos para POS
     */
    private function getProductosGrid() {
        $pagina = $_GET['pagina'] ?? 1;
        $porPagina = $_GET['por_pagina'] ?? 12;

        try {
            $productos = $this->productoModel->getProductosGrid($pagina, $porPagina);
            
            $this->success('Grid de productos obtenido', [
                'productos' => $productos,
                'total' => count($productos),
                'pagina' => $pagina,
                'por_pagina' => $porPagina
            ]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear producto
     */
    private function create() {
        if (!AuthController::hasRole('admin')) {
            $this->error('No autorizado', 403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $this->error('Datos inválidos', 400);
            return;
        }

        try {
            $resultado = $this->productoModel->create($data);

            if ($resultado['success']) {
                $this->success($resultado['message'], $resultado, 201);
            } else {
                $this->error($resultado['message'], 400);
            }
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear categoría
     */
    private function createCategoria() {
        if (!AuthController::hasRole('admin')) {
            $this->error('No autorizado', 403);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $this->error('Datos inválidos', 400);
            return;
        }

        try {
            $resultado = $this->productoModel->createCategoria($data);

            if ($resultado['success']) {
                $this->success($resultado['message'], $resultado, 201);
            } else {
                $this->error($resultado['message'], 400);
            }
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar producto
     */
    private function update() {
        if (!AuthController::hasRole('admin')) {
            $this->error('No autorizado', 403);
            return;
        }

        $id = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$id || !$data) {
            $this->error('ID y datos requeridos', 400);
            return;
        }

        try {
            $resultado = $this->productoModel->updateProducto($id, $data);

            if ($resultado['success']) {
                $this->success($resultado['message'], $resultado);
            } else {
                $this->error($resultado['message'], 400);
            }
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar stock
     */
    private function updateStock() {
        if (!AuthController::hasRole('admin')) {
            $this->error('No autorizado', 403);
            return;
        }

        $id = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$id || !isset($data['cantidad']) || !isset($data['operacion'])) {
            $this->error('Datos incompletos', 400);
            return;
        }

        try {
            $resultado = $this->productoModel->updateStock(
                $id,
                $data['cantidad'],
                $data['operacion']
            );

            if ($resultado['success']) {
                $this->success($resultado['message'], $resultado);
            } else {
                $this->error($resultado['message'], 400);
            }
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar (desactivar) producto
     */
    private function delete() {
        if (!AuthController::hasRole('admin')) {
            $this->error('No autorizado', 403);
            return;
        }

        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->error('ID requerido', 400);
            return;
        }

        try {
            $resultado = $this->productoModel->deactivate($id);

            if ($resultado['success']) {
                $this->success($resultado['message'], $resultado);
            } else {
                $this->error($resultado['message'], 400);
            }
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Respuesta exitosa
     */
    private function success($message, $data = null, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Respuesta de error
     */
    private function error($message, $code = 400) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }

    /**
     * No encontrado
     */
    private function notFound() {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Acción no encontrada'
        ]);
    }

    /**
     * JSON genérico
     */
    private function json($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data);
    }
}


?>
