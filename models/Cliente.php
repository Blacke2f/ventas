<?php
/**
 * ===================================
 * Modelo Cliente
 * ===================================
 */

require_once 'BaseModel.php';

class Cliente extends BaseModel {
    protected $table = 'clientes';

    /**
     * Obtener todos los clientes activos
     */
    public function getAllActivos() {
        return $this->getAll('activo = 1 ORDER BY nombre_cliente ASC');
    }

    /**
     * Buscar cliente por nombre (búsqueda parcial)
     */
    public function searchByName($nombre) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE nombre_cliente LIKE ? AND activo = 1 
                  ORDER BY nombre_cliente ASC";
        return $this->findAll($query, ['%' . $nombre . '%']);
    }

    /**
     * Buscar cliente por documento de identidad
     */
    public function findByDocumento($documento) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE documento_identidad = ? AND activo = 1";
        return $this->findOne($query, [$documento]);
    }

    /**
     * Crear nuevo cliente
     */
    public function create($data) {
        // Validar campos requeridos
        if (empty($data['nombre_cliente'])) {
            return ['success' => false, 'message' => 'El nombre es requerido'];
        }

        // Validar que no exista documento duplicado
        if (!empty($data['documento_identidad'])) {
            if ($this->findByDocumento($data['documento_identidad'])) {
                return ['success' => false, 'message' => 'El documento ya está registrado'];
            }
        }

        // Establecer valores por defecto
        $data['saldo_fiado'] = $data['saldo_fiado'] ?? 0;
        $data['activo'] = $data['activo'] ?? 1;

        $id = $this->insert($data);
        
        if ($id) {
            $this->log('CREATE', 'clientes', $id, null, $data);
            return ['success' => true, 'message' => 'Cliente creado exitosamente', 'id' => $id];
        }

        return ['success' => false, 'message' => 'Error al crear el cliente'];
    }

    /**
     * Actualizar cliente
     */
    public function updateCliente($id, $data) {
        $oldClient = $this->findById($id);
        
        if (!$oldClient) {
            return ['success' => false, 'message' => 'Cliente no encontrado'];
        }

        // Validar documento si cambia
        if (isset($data['documento_identidad']) && 
            $data['documento_identidad'] !== $oldClient['documento_identidad']) {
            if ($this->findByDocumento($data['documento_identidad'])) {
                return ['success' => false, 'message' => 'El documento ya está registrado'];
            }
        }

        $result = $this->update($id, $data);
        
        if ($result) {
            $this->log('UPDATE', 'clientes', $id, $oldClient, $data);
            return ['success' => true, 'message' => 'Cliente actualizado exitosamente'];
        }

        return ['success' => false, 'message' => 'Error al actualizar el cliente'];
    }

    /**
     * Obtener detalles completos del cliente
     */
    public function getDetalles($id) {
        $cliente = $this->findById($id);
        
        if (!$cliente) {
            return null;
        }

        // Obtener créditos activos
        $creditosQuery = "SELECT c.*, 
                         (c.monto_original - c.monto_abonado) as pendiente,
                         DATEDIFF(c.fecha_vencimiento, NOW()) as dias_restantes
                         FROM creditos c 
                         WHERE c.id_cliente = ? AND c.estado_credito IN ('activo', 'parcial', 'vencido')
                         ORDER BY c.fecha_vencimiento ASC";
        $stmt = $this->db->prepare($creditosQuery);
        $stmt->execute([$id]);
        $creditos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener últimas compras
        $ventasQuery = "SELECT v.id_venta, v.numero_venta, v.total, v.tipo_pago, 
                       v.fecha_venta, COUNT(dv.id_detalle) as items
                       FROM ventas v
                       LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
                       WHERE v.id_cliente = ? AND v.estado_venta = 'pagada'
                       GROUP BY v.id_venta
                       ORDER BY v.fecha_venta DESC
                       LIMIT 10";
        $stmt = $this->db->prepare($ventasQuery);
        $stmt->execute([$id]);
        $ultimas_compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'cliente' => $cliente,
            'creditos_activos' => $creditos,
            'total_creditos_activos' => count($creditos),
            'ultimas_compras' => $ultimas_compras,
            'puede_fiar' => $this->puedeFiar($cliente),
            'limite_disponible' => $cliente['limite_monto_fiado'] - $cliente['saldo_fiado']
        ];
    }

    /**
     * Verificar si el cliente puede fiar
     */
    public function puedeFiar($cliente) {
        // Verificar límite de monto
        if ($cliente['saldo_fiado'] >= $cliente['limite_monto_fiado']) {
            return [
                'puede' => false,
                'razon' => 'Límite de monto alcanzado',
                'tipo' => 'monto'
            ];
        }

        // Verificar créditos vencidos
        $vencidosQuery = "SELECT COUNT(*) as total FROM creditos 
                         WHERE id_cliente = ? AND estado_credito = 'vencido'";
        $stmt = $this->db->prepare($vencidosQuery);
        $stmt->execute([$cliente['id_cliente']]);
        $vencidos = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vencidos['total'] > 0) {
            return [
                'puede' => false,
                'razon' => 'Tiene créditos vencidos',
                'tipo' => 'vencido'
            ];
        }

        return [
            'puede' => true,
            'razon' => 'Cliente puede fiar',
            'tipo' => 'ok'
        ];
    }

    /**
     * Actualizar saldo de fiado del cliente
     */
    public function actualizarSaldoFiado($idCliente, $monto, $operacion = 'sumar') {
        $cliente = $this->findById($idCliente);
        
        if (!$cliente) {
            return false;
        }

        $nuevoSaldo = $operacion === 'sumar' 
            ? $cliente['saldo_fiado'] + $monto 
            : $cliente['saldo_fiado'] - $monto;

        return $this->update($idCliente, ['saldo_fiado' => $nuevoSaldo]);
    }

    /**
     * Desactivar cliente
     */
    public function deactivate($id) {
        $result = $this->update($id, ['activo' => 0]);
        
        if ($result) {
            $this->log('DEACTIVATE', 'clientes', $id);
            return ['success' => true, 'message' => 'Cliente desactivado'];
        }

        return ['success' => false, 'message' => 'Error al desactivar el cliente'];
    }

    /**
     * Obtener estadísticas del cliente
     */
    public function getEstadisticas($id) {
        $cliente = $this->findById($id);
        
        if (!$cliente) {
            return null;
        }

        // Total gastado
        $gastadoQuery = "SELECT SUM(total) as total_gastado FROM ventas 
                        WHERE id_cliente = ? AND estado_venta = 'pagada'";
        $stmt = $this->db->prepare($gastadoQuery);
        $stmt->execute([$id]);
        $gastado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Total de transacciones
        $transaccionesQuery = "SELECT COUNT(*) as total FROM ventas 
                             WHERE id_cliente = ? AND estado_venta = 'pagada'";
        $stmt = $this->db->prepare($transaccionesQuery);
        $stmt->execute([$id]);
        $transacciones = $stmt->fetch(PDO::FETCH_ASSOC);

        // Promedio de compra
        $promedio = $transacciones['total'] > 0 
            ? ($gastado['total_gastado'] ?? 0) / $transacciones['total'] 
            : 0;

        return [
            'total_gastado' => $gastado['total_gastado'] ?? 0,
            'total_transacciones' => $transacciones['total'] ?? 0,
            'promedio_compra' => round($promedio, 2),
            'saldo_actual' => $cliente['saldo_fiado'],
            'limite_monto' => $cliente['limite_monto_fiado'],
            'dias_credito' => $cliente['limite_tiempo_dias']
        ];
    }

    /**
     * Obtener clientes con deuda
     */
    public function getClientesConDeuda() {
        $query = "SELECT id_cliente, nombre_cliente, saldo_fiado, 
                 limite_monto_fiado, 
                 (limite_monto_fiado - saldo_fiado) as limite_disponible,
                 (saldo_fiado / limite_monto_fiado * 100) as porcentaje_utilizacion
                 FROM {$this->table}
                 WHERE saldo_fiado > 0 AND activo = 1
                 ORDER BY saldo_fiado DESC";
        return $this->findAll($query, []);
    }

    /**
     * Obtener top clientes por gasto
     */
    public function getTopClientes($limit = 10) {
        $limit = (int)$limit;
        $query = "SELECT c.id_cliente, c.nombre_cliente, 
                 SUM(v.total) as total_gastado, COUNT(v.id_venta) as transacciones
                 FROM {$this->table} c
                 LEFT JOIN ventas v ON c.id_cliente = v.id_cliente AND v.estado_venta = 'pagada'
                 WHERE c.activo = 1
                 GROUP BY c.id_cliente
                 ORDER BY total_gastado DESC
                 LIMIT $limit";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
