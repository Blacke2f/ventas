<?php
/**
 * ===================================
 * Modelo Crédito (Fiados)
 * ===================================
 */

require_once 'BaseModel.php';

class Credito extends BaseModel {
    protected $table = 'creditos';

    /**
     * Crear nuevo crédito
     */
    public function createCredito($data) {
        // Validar campos requeridos
        if (!isset($data['id_venta']) || !isset($data['id_cliente']) || !isset($data['monto_original'])) {
            return ['success' => false, 'message' => 'Faltan campos requeridos'];
        }

        // Establecer valores por defecto
        $data['monto_abonado'] = $data['monto_abonado'] ?? 0;
        $data['monto_pendiente'] = $data['monto_original'] - $data['monto_abonado'];
        $data['estado_credito'] = $data['estado_credito'] ?? 'activo';

        $id = $this->insert($data);
        
        if ($id) {
            $this->log('CREATE', 'creditos', $id, null, $data);
            return ['success' => true, 'message' => 'Crédito creado exitosamente', 'id' => $id];
        }

        return ['success' => false, 'message' => 'Error al crear el crédito'];
    }

    /**
     * Obtener detalles del crédito
     */
    public function getDetalles($id) {
        $credito = $this->findById($id);
        
        if (!$credito) {
            return null;
        }

        // Obtener abonos
        $abonosQuery = "SELECT a.*, u.nombre_completo as usuario_nombre
                       FROM abonos a
                       LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
                       WHERE a.id_credito = ?
                       ORDER BY a.fecha_abono DESC";
        
        $stmt = $this->db->prepare($abonosQuery);
        $stmt->execute([$id]);
        $abonos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener información de la venta
        $ventaQuery = "SELECT v.numero_venta, v.fecha_venta, v.total, 
                      c.nombre_cliente, c.id_cliente
                      FROM ventas v
                      JOIN clientes c ON v.id_cliente = c.id_cliente
                      WHERE v.id_venta = ?";
        
        $stmt = $this->db->prepare($ventaQuery);
        $stmt->execute([$credito['id_venta']]);
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'credito' => $credito,
            'venta' => $venta,
            'abonos' => $abonos,
            'total_abonos' => count($abonos),
            'dias_restantes' => max(0, intval((strtotime($credito['fecha_vencimiento']) - time()) / 86400))
        ];
    }

    /**
     * Obtener créditos activos de un cliente
     */
    public function getActivesByCliente($idCliente) {
        $query = "SELECT c.*, v.numero_venta, v.fecha_venta,
                 DATEDIFF(c.fecha_vencimiento, NOW()) as dias_restantes,
                 CASE 
                    WHEN c.estado_credito = 'pagado' THEN 'Pagado'
                    WHEN c.estado_credito = 'vencido' THEN 'Vencido'
                    WHEN DATEDIFF(c.fecha_vencimiento, NOW()) < 0 THEN 'Vencido'
                    WHEN c.monto_abonado > 0 THEN 'Parcial'
                    ELSE 'Activo'
                 END as estado_actual
                 FROM {$this->table} c
                 JOIN ventas v ON c.id_venta = v.id_venta
                 WHERE c.id_cliente = ? AND c.estado_credito != 'pagado'
                 ORDER BY c.fecha_vencimiento ASC";
        
        return $this->findAll($query, [$idCliente]);
    }

    /**
     * Obtener créditos vencidos
     */
    public function getVencidos() {
        $query = "SELECT c.*, v.numero_venta, 
                 cl.nombre_cliente, DATEDIFF(NOW(), c.fecha_vencimiento) as dias_vencido
                 FROM {$this->table} c
                 JOIN ventas v ON c.id_venta = v.id_venta
                 JOIN clientes cl ON c.id_cliente = cl.id_cliente
                 WHERE c.estado_credito != 'pagado' 
                 AND c.fecha_vencimiento < NOW()
                 ORDER BY c.fecha_vencimiento ASC";
        
        return $this->findAll($query, []);
    }

    /**
     * Agregar abono a crédito
     */
    public function addAbono($idCredito, $monto, $metodoPago, $idUsuario, $notas = null) {
        $credito = $this->findById($idCredito);
        
        if (!$credito) {
            return ['success' => false, 'message' => 'Crédito no encontrado'];
        }

        if ($monto <= 0) {
            return ['success' => false, 'message' => 'El monto debe ser mayor a 0'];
        }

        if ($monto > $credito['monto_pendiente']) {
            return ['success' => false, 'message' => 'El monto excede lo adeudado'];
        }

        // Iniciar transacción
        try {
            $this->db->beginTransaction();

            // Insertar abono
            $abonoQuery = "INSERT INTO abonos (id_credito, monto_abono, metodo_pago, id_usuario, notas) 
                          VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($abonoQuery);
            $stmt->execute([$idCredito, $monto, $metodoPago, $idUsuario, $notas]);

            // Actualizar crédito
            $nuevoAbonadoTotal = $credito['monto_abonado'] + $monto;
            $nuevoPendiente = $credito['monto_original'] - $nuevoAbonadoTotal;
            
            // Determinar nuevo estado
            $nuevoEstado = $nuevoPendiente <= 0 ? 'pagado' : 'parcial';

            $this->update($idCredito, [
                'monto_abonado' => $nuevoAbonadoTotal,
                'monto_pendiente' => max(0, $nuevoPendiente),
                'estado_credito' => $nuevoEstado
            ]);

            // Actualizar saldo del cliente
            require_once 'Cliente.php';
            $clienteModel = new Cliente();
            $clienteModel->actualizarSaldoFiado($credito['id_cliente'], $monto, 'restar');

            $this->db->commit();

            $this->log('ADD_ABONO', 'creditos', $idCredito, null, 
                      ['monto' => $monto, 'metodo' => $metodoPago]);

            return [
                'success' => true, 
                'message' => 'Abono registrado exitosamente',
                'nuevo_estado' => $nuevoEstado,
                'monto_pendiente' => max(0, $nuevoPendiente)
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al registrar el abono: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener resumen de créditos pendientes
     */
    public function getResumenPendientes() {
        $query = "SELECT 
                 COUNT(*) as total_creditos,
                 SUM(monto_original) as monto_total,
                 SUM(monto_pendiente) as pendiente_total,
                 SUM(CASE WHEN fecha_vencimiento < NOW() THEN monto_pendiente ELSE 0 END) as vencido_total,
                 COUNT(CASE WHEN estado_credito = 'parcial' THEN 1 END) as creditos_parciales,
                 COUNT(CASE WHEN fecha_vencimiento < NOW() AND estado_credito != 'pagado' THEN 1 END) as creditos_vencidos
                 FROM {$this->table}
                 WHERE estado_credito != 'pagado'";
        
        return $this->findOne($query, []);
    }

    /**
     * Obtener cartera de créditos (por vencer, parcial, vencida)
     */
    public function getCartera() {
        $query = "SELECT 
                 'Por vencer' as categoria,
                 COUNT(*) as cantidad,
                 SUM(monto_pendiente) as total
                 FROM {$this->table}
                 WHERE estado_credito = 'activo' AND fecha_vencimiento > NOW()
                 UNION ALL
                 SELECT 
                 'Parcial' as categoria,
                 COUNT(*) as cantidad,
                 SUM(monto_pendiente) as total
                 FROM {$this->table}
                 WHERE estado_credito = 'parcial'
                 UNION ALL
                 SELECT 
                 'Vencido' as categoria,
                 COUNT(*) as cantidad,
                 SUM(monto_pendiente) as total
                 FROM {$this->table}
                 WHERE estado_credito = 'vencido' OR (estado_credito IN ('activo', 'parcial') AND fecha_vencimiento < NOW())";
        
        return $this->findAll($query, []);
    }

    /**
     * Validar si cliente puede fiar
     */
    public function validarClienteFiar($idCliente, $montoAFiar) {
        require_once 'Cliente.php';
        $clienteModel = new Cliente();
        $cliente = $clienteModel->findById($idCliente);

        if (!$cliente) {
            return ['valido' => false, 'razon' => 'Cliente no encontrado'];
        }

        // Verificar límite de monto
        $disponible = $cliente['limite_monto_fiado'] - $cliente['saldo_fiado'];
        if ($montoAFiar > $disponible) {
            return [
                'valido' => false,
                'razon' => 'Monto supera el límite disponible',
                'disponible' => $disponible,
                'solicitado' => $montoAFiar
            ];
        }

        // Verificar créditos vencidos
        $vencidosQuery = "SELECT COUNT(*) as total FROM {$this->table} 
                         WHERE id_cliente = ? AND estado_credito IN ('vencido', 'activo', 'parcial')
                         AND fecha_vencimiento < NOW()";
        $stmt = $this->db->prepare($vencidosQuery);
        $stmt->execute([$idCliente]);
        $vencidos = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vencidos['total'] > 0) {
            return [
                'valido' => false,
                'razon' => 'Cliente tiene créditos vencidos',
                'creditos_vencidos' => $vencidos['total']
            ];
        }


        return [
            'valido' => true,
            'razon' => 'Cliente puede fiar',
            'disponible' => $disponible
        ];
    }

    /**
     * Obtener estadísticas de créditos
     */
    public function getEstadisticas() {
        $query = "SELECT 
                 COUNT(*) as total_creditos,
                 COUNT(CASE WHEN estado_credito = 'pagado' THEN 1 END) as pagados,
                 COUNT(CASE WHEN estado_credito = 'activo' THEN 1 END) as activos,
                 COUNT(CASE WHEN estado_credito = 'parcial' THEN 1 END) as parciales,
                 COUNT(CASE WHEN estado_credito = 'vencido' THEN 1 END) as vencidos,
                 SUM(monto_original) as monto_total_fiado,
                 SUM(monto_pendiente) as monto_pendiente,
                 SUM(monto_abonado) as monto_abonado
                 FROM {$this->table}";
        
        return $this->findOne($query, []);
    }

    /**
     * Marcar crédito como vencido si procede
     */
    public function actualizarEstadosVencimiento() {
        $query = "UPDATE {$this->table} 
                 SET estado_credito = 'vencido'
                 WHERE estado_credito IN ('activo', 'parcial') 
                 AND fecha_vencimiento < NOW()";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute();
    }
}
?>
