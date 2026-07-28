<?php
/**
 * ===================================
 * CreditosController - API REST
 * ===================================
 */


class CreditosController {
    private $creditoModel;
    private $clienteModel;

    public function __construct() {
        $this->creditoModel = new Credito();
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
                    case 'get':
                        $controller->getOne();
                        break;
                    case 'cliente':
                        $controller->getByCliente();
                        break;
                    case 'vencidos':
                        $controller->getVencidos();
                        break;
                    case 'resumen':
                        $controller->getResumen();
                        break;
                    case 'cartera':
                        $controller->getCartera();
                        break;
                    case 'estadisticas':
                        $controller->getEstadisticas();
                        break;
                    case 'validar-cliente':
                        $controller->validarCliente();
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
                    case 'abono':
                        $controller->addAbono();
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
     * Obtener un crédito
     */
    private function getOne() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->error('ID requerido', 400);
            return;
        }

        try {
            $credito = $this->creditoModel->getDetalles($id);

            if (!$credito) {
                $this->error('Crédito no encontrado', 404);
                return;
            }

            $this->success('Crédito obtenido', $credito);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Créditos del cliente
     */
    private function getByCliente() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->error('ID cliente requerido', 400);
            return;
        }

        try {
            $creditos = $this->creditoModel->getActivesByCliente($id);
            
            $this->success('Créditos del cliente obtenidos', [
                'creditos' => $creditos,
                'total' => count($creditos)
            ]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Créditos vencidos
     */
    private function getVencidos() {
        try {
            $creditos = $this->creditoModel->getVencidos();
            
            $this->success('Créditos vencidos obtenidos', [
                'creditos' => $creditos,
                'total' => count($creditos)
            ]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Resumen de pendientes
     */
    private function getResumen() {
        try {
            $resumen = $this->creditoModel->getResumenPendientes();
            
            $this->success('Resumen de créditos obtenido', $resumen);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cartera de créditos
     */
    private function getCartera() {
        try {
            // Actualizar estados de vencimiento
            $this->creditoModel->actualizarEstadosVencimiento();
            
            $cartera = $this->creditoModel->getCartera();
            
            $this->success('Cartera obtenida', [
                'cartera' => $cartera,
                'total' => count($cartera)
            ]);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Estadísticas de créditos
     */
    private function getEstadisticas() {
        try {
            $stats = $this->creditoModel->getEstadisticas();
            
            $this->success('Estadísticas obtenidas', $stats);
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validar si cliente puede fiar
     */
    private function validarCliente() {
        $idCliente = $_GET['id_cliente'] ?? null;
        $monto = $_GET['monto'] ?? null;

        if (!$idCliente || !$monto) {
            $this->error('ID cliente y monto requeridos', 400);
            return;
        }

        try {
            $validacion = $this->creditoModel->validarClienteFiar($idCliente, $monto);
            
            if ($validacion['valido']) {
                $this->success('Cliente puede fiar', $validacion);
            } else {
                $this->error($validacion['razon'], 400);
            }
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear crédito
     */
    private function create() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id_venta']) || !isset($data['id_cliente']) || !isset($data['monto_original'])) {
            $this->error('Datos incompletos', 400);
            return;
        }

        try {
            // Validar que cliente puede fiar
            $validacion = $this->creditoModel->validarClienteFiar(
                $data['id_cliente'],
                $data['monto_original']
            );

            if (!$validacion['valido']) {
                $this->error($validacion['razon'], 400);
                return;
            }

            // Crear crédito
            $resultado = $this->creditoModel->createCredito($data);

            if ($resultado['success']) {
                // Actualizar saldo del cliente
                $this->clienteModel->actualizarSaldoFiado(
                    $data['id_cliente'],
                    $data['monto_original'],
                    'sumar'
                );

                $this->success($resultado['message'], $resultado, 201);
            } else {
                $this->error($resultado['message'], 400);
            }
        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Agregar abono
     */
    private function addAbono() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id_credito']) || !isset($data['monto_abono']) || !isset($data['metodo_pago'])) {
            $this->error('Datos incompletos', 400);
            return;
        }

        try {
            $resultado = $this->creditoModel->addAbono(
                $data['id_credito'],
                $data['monto_abono'],
                $data['metodo_pago'],
                $_SESSION['usuario_id'],
                $data['notas'] ?? null
            );

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
