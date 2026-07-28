<?php
/**
 * ===================================
 * Modelo Producto
 * ===================================
 */

require_once 'BaseModel.php';

class Producto extends BaseModel {
    protected $table = 'productos';

    /**
     * Obtener todos los productos activos
     */
    public function getAllActivos() {
        $query = "SELECT p.*, c.nombre_categoria 
                  FROM {$this->table} p
                  LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                  WHERE p.activo = 1
                  ORDER BY p.nombre_producto ASC";
        return $this->findAll($query, []);
    }

    /**
     * Obtener productos por categoría
     */
    public function getByCategoria($idCategoria) {
        $query = "SELECT p.*, c.nombre_categoria 
                  FROM {$this->table} p
                  LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                  WHERE p.id_categoria = ? AND p.activo = 1
                  ORDER BY p.nombre_producto ASC";
        return $this->findAll($query, [$idCategoria]);
    }

    /**
     * Buscar producto por nombre (búsqueda parcial)
     */
    public function searchByName($nombre) {
        $query = "SELECT p.*, c.nombre_categoria 
                  FROM {$this->table} p
                  LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                  WHERE p.nombre_producto LIKE ? AND p.activo = 1
                  ORDER BY p.nombre_producto ASC";
        return $this->findAll($query, ['%' . $nombre . '%']);
    }

    /**
     * Buscar producto por código de barras
     */
    public function findByBarcode($codigo) {
        $query = "SELECT p.*, c.nombre_categoria 
                  FROM {$this->table} p
                  LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                  WHERE p.codigo_barras = ? AND p.activo = 1";
        return $this->findOne($query, [$codigo]);
    }

    /**
     * Calcular precio de venta desde bulto/paquete
     * @param float $precioMayoreo Precio del bulto/paquete
     * @param int $unidades Cantidad de unidades en el bulto
     * @param float $porcentajeGanancia Porcentaje de ganancia a aplicar
     * @return array ['costo_unitario', 'ganancia', 'precio_venta']
     */
    public function calcularPrecioDesdeBuilto($precioMayoreo, $unidades, $porcentajeGanancia) {
        if ($unidades <= 0) {
            return ['error' => 'El número de unidades debe ser mayor a 0'];
        }

        $costoUnitario = $precioMayoreo / $unidades;
        $ganancia = $costoUnitario * ($porcentajeGanancia / 100);
        $precioVenta = $costoUnitario + $ganancia;

        return [
            'costo_unitario' => round($costoUnitario, 2),
            'ganancia' => round($ganancia, 2),
            'precio_venta' => round($precioVenta, 2),
            'porcentaje_aplicado' => $porcentajeGanancia
        ];
    }

    /**
     * Calcular precio de venta desde costo unitario
     * @param float $costo Costo unitario
     * @param float $porcentajeGanancia Porcentaje de ganancia
     * @return array ['ganancia', 'precio_venta']
     */
    public function calcularPrecioDesdeosto($costo, $porcentajeGanancia) {
        $ganancia = $costo * ($porcentajeGanancia / 100);
        $precioVenta = $costo + $ganancia;

        return [
            'ganancia' => round($ganancia, 2),
            'precio_venta' => round($precioVenta, 2),
            'porcentaje_aplicado' => $porcentajeGanancia
        ];
    }



    /**
     * Obtener producto con información completa
     */
    public function getDetalles($id) {
        $query = "SELECT p.*, c.nombre_categoria, c.id_categoria 
                  FROM {$this->table} p
                  LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                  WHERE p.id_producto = ? AND p.activo = 1";
        $producto = $this->findOne($query, [$id]);

        if (!$producto) {
            return null;
        }

        // Obtener últimas ventas del producto
        $ventasQuery = "SELECT v.fecha_venta, dv.cantidad, dv.precio_unitario, 
                       v.total, c.nombre_cliente
                       FROM detalle_ventas dv
                       JOIN ventas v ON dv.id_venta = v.id_venta
                       LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                       WHERE dv.id_producto = ?
                       ORDER BY v.fecha_venta DESC
                       LIMIT 20";
        $stmt = $this->db->prepare($ventasQuery);
        $stmt->execute([$id]);
        $ultimas_ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcular estadísticas
        $estadisticasQuery = "SELECT 
                            COUNT(*) as total_ventas,
                            SUM(dv.cantidad) as cantidad_vendida,
                            AVG(dv.cantidad) as promedio_cantidad,
                            MIN(dv.precio_unitario) as precio_minimo,
                            MAX(dv.precio_unitario) as precio_maximo
                            FROM detalle_ventas dv
                            WHERE dv.id_producto = ?";
        $stmt = $this->db->prepare($estadisticasQuery);
        $stmt->execute([$id]);
        $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'producto' => $producto,
            'ultimas_ventas' => $ultimas_ventas,
            'estadisticas' => $estadisticas
        ];
    }

    /**
     * Crear producto
     */
    public function create($data) {
        // Validar campos requeridos
        if (empty($data['nombre_producto']) || empty($data['id_categoria']) || !isset($data['precio_venta'])) {
            return ['success' => false, 'message' => 'Faltan campos requeridos'];
        }

        // Validar que el código de barras sea único
        if (!empty($data['codigo_barras'])) {
            if ($this->findByBarcode($data['codigo_barras'])) {
                return ['success' => false, 'message' => 'El código de barras ya existe'];
            }
        }

        // Establecer valores por defecto
        $data['stock_actual'] = $data['stock_actual'] ?? 0;
        $data['stock_minimo'] = $data['stock_minimo'] ?? 5;
        $data['activo'] = $data['activo'] ?? 1;

        $id = $this->insert($data);
        
        if ($id) {
            $this->log('CREATE', 'productos', $id, null, $data);
            return ['success' => true, 'message' => 'Producto creado exitosamente', 'id' => $id];
        }

        return ['success' => false, 'message' => 'Error al crear el producto'];
    }

    /**
     * Actualizar producto
     */
    public function updateProducto($id, $data) {
        $oldProduct = $this->findById($id);
        
        if (!$oldProduct) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        // Validar código de barras si cambia
        if (isset($data['codigo_barras']) && $data['codigo_barras'] !== $oldProduct['codigo_barras']) {
            if ($this->findByBarcode($data['codigo_barras'])) {
                return ['success' => false, 'message' => 'El código de barras ya existe'];
            }
        }

        $result = $this->update($id, $data);
        
        if ($result) {
            $this->log('UPDATE', 'productos', $id, $oldProduct, $data);
            return ['success' => true, 'message' => 'Producto actualizado exitosamente'];
        }

        return ['success' => false, 'message' => 'Error al actualizar el producto'];
    }

    /**
     * Actualizar stock
     */
    public function updateStock($id, $cantidad, $operacion = 'restar') {
        $producto = $this->findById($id);
        
        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        $nuevoStock = $operacion === 'restar' 
            ? $producto['stock_actual'] - $cantidad 
            : $producto['stock_actual'] + $cantidad;

        // Validar que no quede en negativo
        if ($nuevoStock < 0) {
            return ['success' => false, 'message' => 'Stock insuficiente'];
        }

        $result = $this->update($id, ['stock_actual' => $nuevoStock]);
        
        if ($result) {
            $this->log('UPDATE_STOCK', 'productos', $id, 
                      ['stock' => $producto['stock_actual']], 
                      ['stock' => $nuevoStock]);
            return ['success' => true, 'message' => 'Stock actualizado'];
        }

        return ['success' => false, 'message' => 'Error al actualizar stock'];
    }

    /**
     * Obtener productos con stock bajo
     */
    public function getStockBajo() {
        $query = "SELECT p.*, c.nombre_categoria,
                 (p.stock_minimo - p.stock_actual) as faltante
                 FROM {$this->table} p
                 LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                 WHERE p.stock_actual <= p.stock_minimo AND p.activo = 1
                 ORDER BY p.stock_actual ASC";
        return $this->findAll($query, []);
    }

    /**
     * Obtener productos más vendidos
     */
    public function getTopVendidos($limit = 10) {
        $limit = (int)$limit;
        $query = "SELECT p.id_producto, p.nombre_producto, p.precio_venta,
                 COUNT(dv.id_detalle) as total_vendido,
                 SUM(dv.cantidad) as cantidad_total,
                 SUM(dv.subtotal) as ingresos_total
                 FROM {$this->table} p
                 LEFT JOIN detalle_ventas dv ON p.id_producto = dv.id_producto
                 WHERE p.activo = 1
                 GROUP BY p.id_producto
                 ORDER BY cantidad_total DESC
                 LIMIT $limit";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Desactivar producto
     */
    public function deactivate($id) {
        $result = $this->update($id, ['activo' => 0]);
        
        if ($result) {
            $this->log('DEACTIVATE', 'productos', $id);
            return ['success' => true, 'message' => 'Producto desactivado'];
        }

        return ['success' => false, 'message' => 'Error al desactivar el producto'];
    }

    /**
     * Obtener todas las categorías
     */
    public function getCategorias() {
        $query = "SELECT id_categoria, nombre_categoria, descripcion, icono, activo 
                  FROM categorias WHERE activo = 1 ORDER BY nombre_categoria ASC";
        return $this->findAll($query, []);
    }

    /**
     * Crear categoría
     */
    public function createCategoria($data) {
        if (empty($data['nombre_categoria'])) {
            return ['success' => false, 'message' => 'El nombre de categoría es requerido'];
        }

        $query = "INSERT INTO categorias (nombre_categoria, descripcion, icono, activo) 
                  VALUES (?, ?, ?, 1)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $data['nombre_categoria'],
            $data['descripcion'] ?? null,
            $data['icono'] ?? null
        ]);

        if ($result) {
            return ['success' => true, 'message' => 'Categoría creada exitosamente'];
        }

        return ['success' => false, 'message' => 'Error al crear la categoría'];
    }

    /**
     * Obtener cuadrículas de productos para el POS
     * Muestra TODOS los activos, con o sin stock
     */
    public function getProductosGrid($pagina = 1, $porPagina = 24) {
        $pagina    = (int)$pagina;
        $porPagina = (int)$porPagina;
        $offset    = ($pagina - 1) * $porPagina;
        
        $query = "SELECT p.id_producto, p.nombre_producto, p.precio_venta, 
                 p.stock_actual, p.imagen_url, c.nombre_categoria
                 FROM {$this->table} p
                 LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                 WHERE p.activo = 1
                 ORDER BY p.nombre_producto ASC
                 LIMIT $porPagina OFFSET $offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar productos activos
     */
    public function countActivos() {
        return $this->count('activo = 1');
    }
}
?>
