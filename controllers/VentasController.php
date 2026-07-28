<?php
class VentasController {
    private $ventaModel;

    public function __construct() {
        $this->ventaModel = new Venta();
    }

    public static function route() {
        $c      = new self();
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? 'list';

        if ($method === 'GET') {
            switch ($action) {
                case 'list':           $c->list();           break;
                case 'get':            $c->getOne();         break;
                case 'hoy':            $c->getHoy();         break;
                case 'rango':          $c->getByDateRange(); break;
                case 'usuario':        $c->getByUsuario();   break;
                case 'cliente':        $c->getByCliente();   break;
                case 'credito':        $c->getVentasCredito(); break;
                case 'resumen-diario': $c->getResumenDiario(); break;
                case 'resumen-periodo':$c->getResumenPeriodo(); break;
                case 'ganancias':      $c->getGanancias();   break;
                case 'inventario':     $c->getInventario();  break;
                default:               $c->notFound();
            }
        } elseif ($method === 'POST') {
            match ($action) {
                'create' => $c->create(),
                default  => $c->notFound(),
            };
        } elseif ($method === 'PUT') {
            match ($action) {
                'cancel' => $c->cancel(),
                default  => $c->notFound(),
            };
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
        }
    }

    // ── Listado ──────────────────────────────────────────────────────
    private function list() {
        try {
            $ventas = $this->ventaModel->getByUsuario($_SESSION['usuario_id'], 100);
            $this->ok('Ventas obtenidas', ['ventas' => $ventas, 'total' => count($ventas)]);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    private function getOne() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->err('ID requerido', 400); return; }
        try {
            $venta = $this->ventaModel->getDetalles($id);
            if (!$venta) { $this->err('Venta no encontrada', 404); return; }
            $this->ok('Venta obtenida', $venta);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    private function getHoy() {
        try {
            $ventas  = $this->ventaModel->getHoy();
            $resumen = $this->ventaModel->getResumenDiario();
            $this->ok('Ventas del día', ['ventas' => $ventas, 'resumen' => $resumen, 'total' => count($ventas)]);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    private function getByDateRange() {
        $inicio    = $_GET['inicio']    ?? null;
        $fin       = $_GET['fin']       ?? null;
        $tipoPago  = $_GET['tipo_pago'] ?? null;
        $idUsuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : null;

        if (!$inicio || !$fin) { $this->err('Fechas requeridas', 400); return; }
        try {
            $ventas = $this->ventaModel->getByDateRange($inicio, $fin, $tipoPago, $idUsuario);
            $this->ok('Ventas obtenidas', ['ventas' => $ventas, 'total' => count($ventas)]);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    private function getByUsuario() {
        $id = (int)($_GET['id'] ?? $_SESSION['usuario_id']);
        try {
            $ventas = $this->ventaModel->getByUsuario($id, 200);
            $this->ok('Ventas del usuario', ['ventas' => $ventas, 'total' => count($ventas)]);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    private function getByCliente() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->err('ID cliente requerido', 400); return; }
        try {
            $ventas = $this->ventaModel->getByCliente($id);
            $this->ok('Ventas del cliente', ['ventas' => $ventas, 'total' => count($ventas)]);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    private function getVentasCredito() {
        try {
            $ventas = $this->ventaModel->getVentasCredito();
            $this->ok('Ventas a crédito', ['ventas' => $ventas, 'total' => count($ventas)]);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    private function getResumenDiario() {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        try {
            $this->ok('Resumen diario', $this->ventaModel->getResumenDiario($fecha));
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    private function getResumenPeriodo() {
        $inicio = $_GET['inicio'] ?? null;
        $fin    = $_GET['fin']    ?? null;
        if (!$inicio || !$fin) { $this->err('Fechas requeridas', 400); return; }
        try {
            $r = $this->ventaModel->getResumenPeriodo($inicio, $fin);
            $this->ok('Resumen período', ['resumen' => $r, 'total_dias' => count($r)]);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    private function getGanancias() {
        $inicio = $_GET['inicio'] ?? date('Y-m-01');
        $fin    = $_GET['fin']    ?? date('Y-m-d');
        try {
            $r = $this->ventaModel->getGananciaPeriodo($inicio, $fin);
            $totalIngresos = array_sum(array_column($r, 'ingresos'));
            $totalCostos   = array_sum(array_column($r, 'costos'));
            $totalGanancia = array_sum(array_column($r, 'ganancia_bruta'));
            $this->ok('Ganancias', [
                'detalle'        => $r,
                'total_ingresos' => round($totalIngresos, 2),
                'total_costos'   => round($totalCostos, 2),
                'total_ganancia' => round($totalGanancia, 2),
                'margen_pct'     => $totalIngresos > 0 ? round($totalGanancia / $totalIngresos * 100, 1) : 0,
            ]);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    private function getInventario() {
        try {
            $items       = $this->ventaModel->getInventarioValorado();
            $totalCosto  = array_sum(array_column($items, 'valor_costo'));
            $totalVenta  = array_sum(array_column($items, 'valor_venta'));
            $this->ok('Inventario valorado', [
                'items'       => $items,
                'total_costo' => round($totalCosto, 2),
                'total_venta' => round($totalVenta, 2),
            ]);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    // ── Crear venta (atómica) ────────────────────────────────────────
    private function create() {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) { $this->err('Datos inválidos', 400); return; }

        // Soporta dos formatos:
        // A) {venta: {...}, items: [...]}   — nuevo formato atómico
        // B) {tipo_pago, total, items, ...} — compatibilidad con el POS
        if (isset($body['items'])) {
            $ventaData = $body['venta'] ?? $body;
            $items     = $body['items'];
            if (isset($ventaData['items'])) unset($ventaData['items']);
        } else {
            $this->err('Se requiere el campo items', 400);
            return;
        }

        $ventaData['id_usuario'] = $_SESSION['usuario_id'];

        try {
            $r = $this->ventaModel->createVentaCompleta($ventaData, $items);
            if ($r['success']) {
                $this->ok($r['message'], $r, 201);
            } else {
                $this->err($r['message'], 422);
            }
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    // ── Cancelar venta (restaura stock) ─────────────────────────────
    private function cancel() {
        $id    = (int)($_GET['id'] ?? 0);
        $body  = json_decode(file_get_contents('php://input'), true);
        $motivo = $body['motivo'] ?? null;
        if (!$id) { $this->err('ID requerido', 400); return; }
        try {
            $r = $this->ventaModel->cancelar($id, $motivo);
            if ($r['success']) $this->ok($r['message'], $r);
            else $this->err($r['message'], 422);
        } catch (Exception $e) { $this->err($e->getMessage()); }
    }

    // ── Helpers ──────────────────────────────────────────────────────
    private function ok($msg, $data = null, $code = 200) {
        http_response_code($code);
        echo json_encode(['success' => true,  'message' => $msg, 'data' => $data]);
    }
    private function err($msg, $code = 500) {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $msg]);
    }
    private function notFound() {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Acción no encontrada']);
    }
}
?>
