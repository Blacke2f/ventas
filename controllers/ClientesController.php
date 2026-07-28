<?php
/**
 * ===================================
 * ClientesController - API REST
 * ===================================
 */


class ClientesController {
    private $clienteModel;

    public function __construct() {
        $this->clienteModel = new Cliente();
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
                    case 'detalles':
                        $controller->getDetalles();
                        break;
                    case 'estadisticas':
                        $controller->getEstadisticas();
                        break;
                    case 'con-deuda':
                        $controller->getClientesConDeuda();
                        break;
                    case 'top':
                        $controller->getTopClientes();
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
                    default:
                        $controller->notFound();
                }
                break;

            case 'PUT':
                switch ($action) {
                    case 'update':
                        $controller->update();
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
     * Obtener todos los clientes
     */
    private function list() {
        try {
            $clientes = $this->clienteModel->getAllActivos();
            
            $this->success('Clientes obtenidos', [
                'clientes' => $clientes,
                'total' => count($clientes)
            ]);
        } catch (Exception $e) {
            $this->error('Error al obtener clientes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener un cliente
     */
    private function getOne() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->error('ID requerido', 400);
            return;
        }

        try {
            $cliente = $this->clienteModel->findById($id);

            if (!$cliente) {
                $this->error('Cliente no encontrado', 404);
                return;
            }

            $this->success('Cliente obtenido', ['cliente' => $cliente]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Buscar clientes
     */
    private function search() {
        $q = $_GET['q'] ?? '';

        if (strlen($q) < 2) {
            $this->error('Búsqueda debe tener al menos 2 caracteres', 400);
            return;
        }

        try {
            $clientes = $this->clienteModel->searchByName($q);
            
            $this->success('Búsqueda completada', [
                'clientes' => $clientes,
                'total' => count($clientes)
            ]);
        } catch (Exception $e) {
            $this->error('Error en búsqueda: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener detalles completos
     */
    private function getDetalles() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->error('ID requerido', 400);
            return;
        }

        try {
            $detalles = $this->clienteModel->getDetalles($id);

            if (!$detalles) {
                $this->error('Cliente no encontrado', 404);
                return;
            }

            $this->success('Detalles obtenidos', $detalles);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener estadísticas
     */
    private function getEstadisticas() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->error('ID requerido', 400);
            return;
        }

        try {
            $stats = $this->clienteModel->getEstadisticas($id);

            if (!$stats) {
                $this->error('Cliente no encontrado', 404);
                return;
            }

            $this->success('Estadísticas obtenidas', $stats);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Clientes con deuda
     */
    private function getClientesConDeuda() {
        try {
            $clientes = $this->clienteModel->getClientesConDeuda();
            
            $this->success('Clientes con deuda obtenidos', [
                'clientes' => $clientes,
                'total' => count($clientes)
            ]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Top clientes
     */
    private function getTopClientes() {
        $limit = $_GET['limit'] ?? 10;

        try {
            $clientes = $this->clienteModel->getTopClientes($limit);
            
            $this->success('Top clientes obtenidos', [
                'clientes' => $clientes,
                'total' => count($clientes)
            ]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear cliente
     */
    private function create() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $this->error('Datos inválidos', 400);
            return;
        }

        try {
            $resultado = $this->clienteModel->create($data);

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
     * Actualizar cliente
     */
    private function update() {
        $id = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$id || !$data) {
            $this->error('ID y datos requeridos', 400);
            return;
        }

        try {
            $resultado = $this->clienteModel->updateCliente($id, $data);

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
     * Eliminar (desactivar) cliente
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
            $resultado = $this->clienteModel->deactivate($id);

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
