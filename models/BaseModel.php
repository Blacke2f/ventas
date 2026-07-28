<?php
/**
 * ===================================
 * BaseModel - Clase base para modelos
 * ===================================
 * Proporciona métodos comunes para todos los modelos
 */

class BaseModel {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los registros
     */
    public function getAll($where = '', $params = []) {
        $query = "SELECT * FROM {$this->table}";
        if ($where) {
            // Si contiene ORDER BY, LIMIT etc. los agrega directo sin WHERE
            if (stripos($where, 'ORDER BY') === 0 || stripos($where, 'LIMIT') === 0) {
                $query .= " $where";
            } else {
                $query .= " WHERE $where";
            }
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un registro por ID
     */
    public function getById($id) {
        return $this->findById($id);
    }

    /**
     * Obtener un registro por ID (alias universal)
     * Detecta automáticamente el nombre de la columna PK
     */
    public function findById($id) {
        // Buscar la PK real de la tabla
        $pkMap = [
            'productos'     => 'id_producto',
            'clientes'      => 'id_cliente',
            'ventas'        => 'id_venta',
            'creditos'      => 'id_credito',
            'usuarios'      => 'id_usuario',
            'categorias'    => 'id_categoria',
            'detalle_ventas'=> 'id_detalle',
            'abonos'        => 'id_abono',
            'auditoria'     => 'id_auditoria',
        ];

        $pk = $pkMap[$this->table] ?? 'id_' . rtrim($this->table, 's');

        $query = "SELECT * FROM {$this->table} WHERE {$pk} = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un registro con consulta personalizada
     */
    public function findOne($query, $params = []) {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener múltiples registros con consulta personalizada
     */
    public function findAll($query, $params = []) {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insertar registro
     */
    public function insert($data) {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $query = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute(array_values($data));
        
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Actualizar registro
     */
    public function update($id, $data) {
        $pkMap = [
            'productos'     => 'id_producto',
            'clientes'      => 'id_cliente',
            'ventas'        => 'id_venta',
            'creditos'      => 'id_credito',
            'usuarios'      => 'id_usuario',
            'categorias'    => 'id_categoria',
            'detalle_ventas'=> 'id_detalle',
            'abonos'        => 'id_abono',
            'auditoria'     => 'id_auditoria',
        ];
        $pk = $pkMap[$this->table] ?? 'id_' . rtrim($this->table, 's');

        $setClause = implode(',', array_map(fn($k) => "$k=?", array_keys($data)));
        $query = "UPDATE {$this->table} SET $setClause WHERE {$pk} = ?";

        $params = array_merge(array_values($data), [$id]);
        $stmt = $this->db->prepare($query);

        return $stmt->execute($params);
    }

    /**
     * Eliminar registro
     */
    public function delete($id) {
        $pkMap = [
            'productos'     => 'id_producto',
            'clientes'      => 'id_cliente',
            'ventas'        => 'id_venta',
            'creditos'      => 'id_credito',
            'usuarios'      => 'id_usuario',
            'categorias'    => 'id_categoria',
            'detalle_ventas'=> 'id_detalle',
            'abonos'        => 'id_abono',
            'auditoria'     => 'id_auditoria',
        ];
        $pk = $pkMap[$this->table] ?? 'id_' . rtrim($this->table, 's');

        $query = "DELETE FROM {$this->table} WHERE {$pk} = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Contar registros
     */
    public function count($where = '', $params = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where) {
            $query .= " WHERE $where";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Ejecutar consulta personalizada
     */
    public function execute($query, $params = []) {
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Registrar auditoría
     */
    protected function log($action, $tabla, $id, $oldValues = null, $newValues = null) {
        $userId = $_SESSION['usuario_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        $query = "INSERT INTO auditoria (id_usuario, accion, tabla_afectada, registro_id, valores_antiguos, valores_nuevos, ip_address) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $userId,
            $action,
            $tabla,
            $id,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ip
        ]);
    }
}
?>
