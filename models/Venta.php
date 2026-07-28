<?php
require_once 'BaseModel.php';

class Venta extends BaseModel {
    protected $table = 'ventas';

    /**
     * Crear venta completa con todos sus ítems en una sola transacción atómica.
     * Si cualquier ítem falla (stock, etc.) se hace rollback completo.
     *
     * @param array $data  ['id_usuario', 'total', 'tipo_pago', 'subtotal', 'descuento', 'id_cliente']
     * @param array $items [['id_producto', 'cantidad', 'precio_unitario'], ...]
     */
    public function createVentaCompleta(array $data, array $items) {
        if (!isset($data['id_usuario'], $data['total'], $data['tipo_pago'])) {
            return ['success' => false, 'message' => 'Faltan campos requeridos'];
        }
        if (empty($items)) {
            return ['success' => false, 'message' => 'La venta no tiene ítems'];
        }

        // Validar tipo de pago
        if (!in_array($data['tipo_pago'], ['efectivo', 'tarjeta', 'fiado'])) {
            return ['success' => false, 'message' => 'Tipo de pago inválido'];
        }

        // Validar total > 0
        if ((float)$data['total'] <= 0) {
            return ['success' => false, 'message' => 'El total debe ser mayor a 0'];
        }

        try {
            $this->db->beginTransaction();

            // Verificar stock de todos los ítems ANTES de insertar nada
            foreach ($items as $item) {
                $prod = $this->db->prepare(
                    "SELECT stock_actual, nombre_producto FROM productos WHERE id_producto = ? AND activo = 1 FOR UPDATE"
                );
                $prod->execute([$item['id_producto']]);
                $producto = $prod->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => "Producto #{$item['id_producto']} no encontrado"];
                }
                if ($producto['stock_actual'] < $item['cantidad']) {
                    $this->db->rollBack();
                    return [
                        'success' => false,
                        'message' => "Stock insuficiente para '{$producto['nombre_producto']}'. Disponible: {$producto['stock_actual']}, solicitado: {$item['cantidad']}"
                    ];
                }
            }

            // Validar fiado antes de insertar venta
            if ($data['tipo_pago'] === 'fiado') {
                if (empty($data['id_cliente'])) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => 'Se requiere un cliente para ventas de fiado'];
                }
                require_once 'Credito.php';
                $creditoModel = new Credito();
                $validacion = $creditoModel->validarClienteFiar($data['id_cliente'], $data['total']);
                if (!$validacion['valido']) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => 'No se puede fiar: ' . $validacion['razon']];
                }
            }

            // Insertar venta
            $numeroVenta = $this->generarNumeroVenta();
            $data['numero_venta'] = $numeroVenta;
            $data['estado_venta'] = 'pagada';
            $data['subtotal']     = $data['subtotal'] ?? $data['total'];
            $data['descuento']    = $data['descuento'] ?? 0;

            $ventaId = $this->insert($data);
            if (!$ventaId) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Error al crear la venta'];
            }

            // Insertar ítems y descontar stock
            $stmtItem = $this->db->prepare(
                "INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?,?,?,?,?)"
            );
            $stmtStock = $this->db->prepare(
                "UPDATE productos SET stock_actual = stock_actual - ? WHERE id_producto = ?"
            );

            foreach ($items as $item) {
                $sub = $item['cantidad'] * $item['precio_unitario'];
                $stmtItem->execute([$ventaId, $item['id_producto'], $item['cantidad'], $item['precio_unitario'], $sub]);
                $stmtStock->execute([$item['cantidad'], $item['id_producto']]);
            }

            // Crear registro de crédito si es fiado
            if ($data['tipo_pago'] === 'fiado') {
                $fechaVenc = new DateTime();
                $fechaVenc->modify('+30 days');
                $creditoData = [
                    'id_venta' => $ventaId,
                    'id_cliente' => $data['id_cliente'],
                    'monto_original' => $data['total'],
                    'fecha_vencimiento' => $fechaVenc->format('Y-m-d')
                ];
                $creditoResult = $creditoModel->createCredito($creditoData);
                if (!$creditoResult['success']) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => 'Error al registrar el crédito: ' . $creditoResult['message']];
                }
                
                require_once 'Cliente.php';
                $clienteModel = new Cliente();
                $clienteModel->actualizarSaldoFiado($data['id_cliente'], $data['total'], 'sumar');
            }

            $this->db->commit();
            $this->log('CREATE', 'ventas', $ventaId, null, ['items' => count($items)]);

            return [
                'success'      => true,
                'message'      => 'Venta creada exitosamente',
                'id'           => $ventaId,
                'numero_venta' => $numeroVenta,
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    /** @deprecated Usar createVentaCompleta() */
    public function createVenta(array $data) {
        if (!isset($data['id_usuario'], $data['total'], $data['tipo_pago'])) {
            return ['success' => false, 'message' => 'Faltan campos requeridos'];
        }
        $numeroVenta = $this->generarNumeroVenta();
        $data['numero_venta'] = $numeroVenta;
        $data['estado_venta'] = $data['estado_venta'] ?? 'pagada';
        $data['subtotal']     = $data['subtotal'] ?? $data['total'];
        $data['descuento']    = $data['descuento'] ?? 0;
        $id = $this->insert($data);
        if ($id) {
            $this->log('CREATE', 'ventas', $id, null, $data);
            return ['success' => true, 'message' => 'Venta creada', 'id' => $id, 'numero_venta' => $numeroVenta];
        }
        return ['success' => false, 'message' => 'Error al crear la venta'];
    }

    private function generarNumeroVenta(): string {
        $fecha = date('Ymd');
        $stmt  = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE numero_venta LIKE ?");
        $stmt->execute([$fecha . '%']);
        $n = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        return $fecha . str_pad($n + 1, 6, '0', STR_PAD_LEFT);
    }

    public function addItem($idVenta, $idProducto, $cantidad, $precioUnitario) {
        $sub  = $cantidad * $precioUnitario;
        $stmt = $this->db->prepare(
            "INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?,?,?,?,?)"
        );
        return $stmt->execute([$idVenta, $idProducto, $cantidad, $precioUnitario, $sub])
            ? ['success' => true,  'message' => 'Item agregado']
            : ['success' => false, 'message' => 'Error al agregar item'];
    }

    public function getDetalles($id) {
        $venta = $this->findOne(
            "SELECT v.*, u.nombre_completo as cajero_nombre, c.nombre_cliente
             FROM {$this->table} v
             LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
             LEFT JOIN clientes c ON v.id_cliente  = c.id_cliente
             WHERE v.id_venta = ?",
            [$id]
        );
        if (!$venta) return null;

        $stmt = $this->db->prepare(
            "SELECT dv.*, p.nombre_producto, p.codigo_barras, p.precio_costo,
                    cat.nombre_categoria
             FROM detalle_ventas dv
             JOIN productos p   ON dv.id_producto   = p.id_producto
             LEFT JOIN categorias cat ON p.id_categoria = cat.id_categoria
             WHERE dv.id_venta = ? ORDER BY dv.id_detalle"
        );
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcular ganancia bruta de la venta
        $ganancia = 0;
        foreach ($items as $i) {
            $ganancia += ($i['precio_unitario'] - $i['precio_costo']) * $i['cantidad'];
        }

        return ['venta' => $venta, 'items' => $items, 'total_items' => count($items), 'ganancia_bruta' => round($ganancia, 2)];
    }

    public function getByDateRange($fechaInicio, $fechaFin, $tipoPago = null, $idUsuario = null) {
        $sql    = "SELECT v.*, u.nombre_completo as cajero_nombre, c.nombre_cliente,
                   COUNT(dv.id_detalle) as total_items
                   FROM {$this->table} v
                   LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                   LEFT JOIN clientes c ON v.id_cliente  = c.id_cliente
                   LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
                   WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
                   AND v.estado_venta = 'pagada'";
        $params = [$fechaInicio, $fechaFin];
        if ($tipoPago)  { $sql .= " AND v.tipo_pago    = ?"; $params[] = $tipoPago; }
        if ($idUsuario) { $sql .= " AND v.id_usuario   = ?"; $params[] = $idUsuario; }
        $sql .= " GROUP BY v.id_venta ORDER BY v.fecha_venta DESC";
        return $this->findAll($sql, $params);
    }

    public function getHoy() {
        return $this->getByDateRange(date('Y-m-d'), date('Y-m-d'));
    }

    /** Cancelar venta y RESTAURAR stock */
    public function cancelar($id, $motivo = null) {
        $venta = $this->findById($id);
        if (!$venta) return ['success' => false, 'message' => 'Venta no encontrada'];
        if ($venta['estado_venta'] === 'cancelada') return ['success' => false, 'message' => 'Ya está cancelada'];

        try {
            $this->db->beginTransaction();

            // Restaurar stock de cada ítem
            $items = $this->db->prepare("SELECT id_producto, cantidad FROM detalle_ventas WHERE id_venta = ?");
            $items->execute([$id]);
            $stmtStock = $this->db->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id_producto = ?");
            foreach ($items->fetchAll(PDO::FETCH_ASSOC) as $it) {
                $stmtStock->execute([$it['cantidad'], $it['id_producto']]);
            }

            // Cancelar la venta
            $this->update($id, ['estado_venta' => 'cancelada', 'notas' => $motivo]);

            // Si era fiado, limpiar crédito y saldo cliente
            if ($venta['tipo_pago'] === 'fiado' && $venta['id_cliente']) {
                $this->db->prepare("UPDATE creditos SET estado_credito='cancelado' WHERE id_venta=?")->execute([$id]);
                $this->db->prepare("UPDATE clientes SET saldo_fiado = GREATEST(0, saldo_fiado - ?) WHERE id_cliente=?")
                         ->execute([$venta['total'], $venta['id_cliente']]);
            }

            $this->db->commit();
            $this->log('CANCEL', 'ventas', $id);
            return ['success' => true, 'message' => 'Venta cancelada y stock restaurado'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al cancelar: ' . $e->getMessage()];
        }
    }

    public function getResumenDiario($fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        return $this->findOne(
            "SELECT COUNT(*) as total_transacciones,
                    SUM(total) as total_vendido, AVG(total) as promedio_venta,
                    SUM(CASE WHEN tipo_pago='efectivo' THEN total ELSE 0 END) as efectivo,
                    SUM(CASE WHEN tipo_pago='tarjeta'  THEN total ELSE 0 END) as tarjeta,
                    SUM(CASE WHEN tipo_pago='fiado'    THEN total ELSE 0 END) as fiado
             FROM {$this->table} WHERE DATE(fecha_venta)=? AND estado_venta='pagada'",
            [$fecha]
        );
    }

    public function getResumenPeriodo($fechaInicio, $fechaFin) {
        return $this->findAll(
            "SELECT DATE(fecha_venta) as fecha, COUNT(*) as transacciones,
                    SUM(total) as total, AVG(total) as promedio
             FROM {$this->table}
             WHERE DATE(fecha_venta) BETWEEN ? AND ? AND estado_venta='pagada'
             GROUP BY DATE(fecha_venta) ORDER BY fecha DESC",
            [$fechaInicio, $fechaFin]
        );
    }

    public function getByUsuario($idUsuario, $limite = 100) {
        $limite = (int)$limite;
        $stmt = $this->db->prepare(
            "SELECT v.*, c.nombre_cliente, COUNT(dv.id_detalle) as total_items
             FROM {$this->table} v
             LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
             LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
             WHERE v.id_usuario = ? AND v.estado_venta='pagada'
             GROUP BY v.id_venta ORDER BY v.fecha_venta DESC LIMIT $limite"
        );
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCliente($idCliente) {
        return $this->findAll(
            "SELECT v.*, u.nombre_completo as cajero_nombre, COUNT(dv.id_detalle) as total_items
             FROM {$this->table} v
             LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
             LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
             WHERE v.id_cliente = ? AND v.estado_venta='pagada'
             GROUP BY v.id_venta ORDER BY v.fecha_venta DESC",
            [$idCliente]
        );
    }

    public function getVentasCredito() {
        return $this->findAll(
            "SELECT v.*, c.nombre_cliente, cr.monto_pendiente, cr.fecha_vencimiento,
                    DATEDIFF(cr.fecha_vencimiento, NOW()) as dias_restantes
             FROM {$this->table} v
             JOIN clientes c  ON v.id_cliente = c.id_cliente
             JOIN creditos cr ON v.id_venta   = cr.id_venta
             WHERE v.tipo_pago='fiado' AND v.estado_venta='pagada'
             AND cr.estado_credito IN ('activo','parcial','vencido')
             ORDER BY cr.fecha_vencimiento",
            []
        );
    }

    public function countHoy() {
        $r = $this->findOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE DATE(fecha_venta)=? AND estado_venta='pagada'",
            [date('Y-m-d')]
        );
        return $r['total'] ?? 0;
    }

    /** Reporte de ganancias brutas por período */
    public function getGananciaPeriodo($fechaInicio, $fechaFin) {
        return $this->findAll(
            "SELECT DATE(v.fecha_venta) as fecha,
                    COUNT(DISTINCT v.id_venta) as ventas,
                    SUM(dv.subtotal) as ingresos,
                    SUM(dv.cantidad * p.precio_costo) as costos,
                    SUM(dv.subtotal - dv.cantidad * p.precio_costo) as ganancia_bruta
             FROM ventas v
             JOIN detalle_ventas dv ON v.id_venta   = dv.id_venta
             JOIN productos p       ON dv.id_producto = p.id_producto
             WHERE DATE(v.fecha_venta) BETWEEN ? AND ? AND v.estado_venta='pagada'
             GROUP BY DATE(v.fecha_venta) ORDER BY fecha DESC",
            [$fechaInicio, $fechaFin]
        );
    }

    /** Inventario valorado */
    public function getInventarioValorado() {
        return $this->db->query(
            "SELECT p.nombre_producto, c.nombre_categoria,
                    p.stock_actual, p.precio_costo, p.precio_venta,
                    (p.stock_actual * p.precio_costo)  as valor_costo,
                    (p.stock_actual * p.precio_venta)  as valor_venta
             FROM productos p
             LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
             WHERE p.activo=1 ORDER BY c.nombre_categoria, p.nombre_producto"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeItem($idVenta, $idDetalle) {
        $venta = $this->findById($idVenta);
        if (!$venta || $venta['estado_venta'] === 'pagada') {
            return ['success' => false, 'message' => 'No se puede editar una venta pagada'];
        }
        $stmt = $this->db->prepare("DELETE FROM detalle_ventas WHERE id_detalle=? AND id_venta=?");
        return $stmt->execute([$idDetalle, $idVenta])
            ? ['success' => true,  'message' => 'Item eliminado']
            : ['success' => false, 'message' => 'Error al eliminar'];
    }
}
?>
