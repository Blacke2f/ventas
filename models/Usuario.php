<?php
/**
 * ===================================
 * Modelo Usuario
 * ===================================
 */

require_once 'BaseModel.php';

class Usuario extends BaseModel {
    protected $table = 'usuarios';

    /**
     * Buscar usuario por nombre de usuario
     */
    public function findByUsername($username) {
        $query = "SELECT * FROM {$this->table} WHERE nombre_usuario = ? AND activo = 1";
        return $this->findOne($query, [$username]);
    }

    /**
     * Buscar usuario por email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = ? AND activo = 1";
        return $this->findOne($query, [$email]);
    }

    /**
     * Buscar usuario por ID
     */
    public function findById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id_usuario = ? AND activo = 1";
        return $this->findOne($query, [$id]);
    }

    public function create($data) {
        // Validar username único
        if ($this->findByUsername($data['nombre_usuario'])) {
            return ['success' => false, 'message' => 'El nombre de usuario ya existe'];
        }
        // Validar email único
        if ($this->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }

        // Normalizar la clave de contraseña al nombre correcto de la columna
        // Acepta: 'contraseña', 'contrasena', 'password'
        $pass = $data['contraseña'] ?? $data['contrasena'] ?? $data['password'] ?? null;
        if (!$pass) {
            return ['success' => false, 'message' => 'La contraseña es requerida'];
        }

        // Construir el array limpio para insertar
        $insert = [
            'nombre_usuario'  => $data['nombre_usuario'],
            'contraseña'      => password_hash($pass, PASSWORD_BCRYPT, ['cost' => BCRYPT_ROUNDS]),
            'email'           => $data['email'],
            'rol'             => $data['rol'] ?? 'cajero',
            'nombre_completo' => $data['nombre_completo'] ?? $data['nombre_usuario'],
            'activo'          => 1,
        ];

        $id = $this->insert($insert);
        if ($id) {
            $this->log('CREATE', 'usuarios', $id, null, ['nombre_usuario' => $insert['nombre_usuario']]);
            return ['success' => true, 'message' => 'Usuario creado exitosamente', 'id' => $id];
        }

        return ['success' => false, 'message' => 'Error al crear el usuario'];
    }

    public function updateUser($id, $data) {
        $oldUser = $this->findById($id);
        if (!$oldUser) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        // Validar username si cambia
        if (isset($data['nombre_usuario']) && $data['nombre_usuario'] !== $oldUser['nombre_usuario']) {
            if ($this->findByUsername($data['nombre_usuario'])) {
                return ['success' => false, 'message' => 'El nombre de usuario ya existe'];
            }
        }

        // Validar email si cambia
        if (isset($data['email']) && $data['email'] !== $oldUser['email']) {
            if ($this->findByEmail($data['email'])) {
                return ['success' => false, 'message' => 'El email ya está registrado'];
            }
        }

        // Construir array limpio para actualizar
        $update = [];

        if (!empty($data['nombre_usuario']))  $update['nombre_usuario']  = $data['nombre_usuario'];
        if (!empty($data['email']))           $update['email']           = $data['email'];
        if (!empty($data['rol']))             $update['rol']             = $data['rol'];
        if (!empty($data['nombre_completo'])) $update['nombre_completo'] = $data['nombre_completo'];
        if (isset($data['activo']))           $update['activo']          = (int)$data['activo'];

        // Contraseña — aceptar cualquier variante del campo
        $newPass = $data['contraseña'] ?? $data['contrasena'] ?? $data['password'] ?? null;
        if ($newPass && strlen($newPass) >= 6) {
            $update['contraseña'] = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => BCRYPT_ROUNDS]);
        }

        if (empty($update)) {
            return ['success' => false, 'message' => 'No hay datos para actualizar'];
        }

        $result = $this->update($id, $update);
        if ($result) {
            $this->log('UPDATE', 'usuarios', $id, $oldUser, array_keys($update));
            return ['success' => true, 'message' => 'Usuario actualizado exitosamente'];
        }

        return ['success' => false, 'message' => 'Error al actualizar el usuario'];
    }

    /**
     * Verificar contraseña - busca en ambas columnas
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Obtener todos los usuarios activos
     */
    public function getAllActivos() {
        return $this->getAll('activo = 1');
    }

    /**
     * Desactivar usuario
     */
    public function deactivate($id) {
        $result = $this->update($id, ['activo' => 0]);
        
        if ($result) {
            $this->log('DEACTIVATE', 'usuarios', $id);
            return ['success' => true, 'message' => 'Usuario desactivado'];
        }

        return ['success' => false, 'message' => 'Error al desactivar el usuario'];
    }

    /**
     * Activar usuario
     */
    public function activate($id) {
        $result = $this->update($id, ['activo' => 1]);
        
        if ($result) {
            $this->log('ACTIVATE', 'usuarios', $id);
            return ['success' => true, 'message' => 'Usuario activado'];
        }

        return ['success' => false, 'message' => 'Error al activar el usuario'];
    }

    /**
     * Obtener estadísticas de usuario
     */
    public function getStats($userId) {
        // Ventas totales del usuario
        $ventasQuery = "SELECT COUNT(*) as total_ventas, SUM(total) as monto_total 
                        FROM ventas WHERE id_usuario = ? AND estado_venta = 'pagada'";
        $stmt = $this->db->prepare($ventasQuery);
        $stmt->execute([$userId]);
        $ventas = $stmt->fetch(PDO::FETCH_ASSOC);

        // Última venta
        $lastSaleQuery = "SELECT * FROM ventas WHERE id_usuario = ? ORDER BY fecha_venta DESC LIMIT 1";
        $stmt = $this->db->prepare($lastSaleQuery);
        $stmt->execute([$userId]);
        $lastSale = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_ventas' => $ventas['total_ventas'] ?? 0,
            'monto_total' => $ventas['monto_total'] ?? 0,
            'ultima_venta' => $lastSale['fecha_venta'] ?? null
        ];
    }
}
?>
