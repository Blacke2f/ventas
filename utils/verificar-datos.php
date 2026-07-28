<?php
/**
 * Verificar Datos en Base de Datos
 * Comprueba que todos los datos estén cargados correctamente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Datos - AbasPOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .table-card {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        .table-card-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .table-card-body {
            padding: 15px;
        }
        .status-ok { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h2><i class="fas fa-database"></i> Verificación de Datos - AbasPOS</h2>
            <p class="text-muted">Comprobando contenido de la base de datos</p>
        </div>

        <?php
        try {
            $db = Database::getInstance()->getConnection();
            
            echo '<div class="alert alert-success">';
            echo '<i class="fas fa-check-circle"></i> <strong>Conexión exitosa a la base de datos</strong><br>';
            echo 'Base de datos: <code>' . DB_NAME . '</code>';
            echo '</div>';

            // Verificar cada tabla
            $tablas = [
                'usuarios' => 'Usuarios del sistema',
                'clientes' => 'Clientes registrados',
                'categorias' => 'Categorías de productos',
                'productos' => 'Productos en inventario',
                'ventas' => 'Ventas registradas',
                'detalle_ventas' => 'Detalles de ventas',
                'creditos' => 'Créditos/Fiados',
                'abonos' => 'Abonos a créditos',
                'auditoria' => 'Registro de auditoría'
            ];

            foreach ($tablas as $tabla => $descripcion) {
                echo '<div class="table-card">';
                echo '<div class="table-card-header">';
                echo '<h5 class="mb-0"><i class="fas fa-table"></i> ' . ucfirst($tabla) . '</h5>';
                echo '<small class="text-muted">' . $descripcion . '</small>';
                echo '</div>';
                echo '<div class="table-card-body">';

                // Contar registros
                $stmt = $db->query("SELECT COUNT(*) as total FROM $tabla");
                $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                if ($total > 0) {
                    echo '<div class="status-ok">';
                    echo '<i class="fas fa-check-circle"></i> ';
                    echo '<strong>' . $total . ' registro(s)</strong> encontrado(s)';
                    echo '</div>';

                    // Mostrar primeros registros
                    $stmt = $db->query("SELECT * FROM $tabla LIMIT 3");
                    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($registros) > 0) {
                        echo '<div class="mt-3">';
                        echo '<small class="text-muted">Primeros registros:</small>';
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-sm table-striped">';
                        echo '<thead><tr>';
                        
                        // Encabezados
                        foreach (array_keys($registros[0]) as $columna) {
                            echo '<th>' . $columna . '</th>';
                        }
                        echo '</tr></thead>';
                        echo '<tbody>';

                        // Datos
                        foreach ($registros as $registro) {
                            echo '<tr>';
                            foreach ($registro as $valor) {
                                // Limitar longitud del valor
                                $valorMostrar = is_string($valor) ? substr($valor, 0, 50) : $valor;
                                if (strlen($valor ?? '') > 50) {
                                    $valorMostrar .= '...';
                                }
                                echo '<td>' . htmlspecialchars($valorMostrar ?? 'NULL') . '</td>';
                            }
                            echo '</tr>';
                        }

                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="status-warning">';
                    echo '<i class="fas fa-exclamation-triangle"></i> ';
                    echo '<strong>0 registros</strong> - Tabla vacía';
                    echo '</div>';
                }

                echo '</div>';
                echo '</div>';
            }

            // Resumen general
            echo '<div class="alert alert-info mt-4">';
            echo '<h5><i class="fas fa-chart-pie"></i> Resumen General</h5>';
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
            $usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
            $productos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM categorias WHERE activo = 1");
            $categorias = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
            $clientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM ventas");
            $ventas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            echo '<div class="row text-center">';
            echo '<div class="col-md-2"><h3>' . $usuarios . '</h3><small>Usuarios</small></div>';
            echo '<div class="col-md-2"><h3>' . $categorias . '</h3><small>Categorías</small></div>';
            echo '<div class="col-md-3"><h3>' . $productos . '</h3><small>Productos</small></div>';
            echo '<div class="col-md-2"><h3>' . $clientes . '</h3><small>Clientes</small></div>';
            echo '<div class="col-md-3"><h3>' . $ventas . '</h3><small>Ventas</small></div>';
            echo '</div>';
            echo '</div>';

            // Verificar productos por categoría
            echo '<div class="alert alert-secondary mt-4">';
            echo '<h5><i class="fas fa-boxes"></i> Productos por Categoría</h5>';
            $stmt = $db->query("
                SELECT c.nombre_categoria, COUNT(p.id_producto) as total_productos
                FROM categorias c
                LEFT JOIN productos p ON c.id_categoria = p.id_categoria AND p.activo = 1
                GROUP BY c.id_categoria, c.nombre_categoria
                ORDER BY total_productos DESC
            ");
            $categorias_productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<div class="table-responsive">';
            echo '<table class="table table-sm">';
            echo '<thead><tr><th>Categoría</th><th>Productos</th></tr></thead>';
            echo '<tbody>';
            foreach ($categorias_productos as $cat) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($cat['nombre_categoria']) . '</td>';
                echo '<td><span class="badge bg-primary">' . $cat['total_productos'] . '</span></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';

            // Diagnóstico final
            echo '<div class="alert alert-success mt-4">';
            echo '<h5><i class="fas fa-check-double"></i> Diagnóstico</h5>';
            
            if ($usuarios > 0 && $productos > 0 && $categorias > 0) {
                echo '<p class="mb-0"><strong>✅ El sistema tiene datos básicos cargados correctamente</strong></p>';
                echo '<p class="mb-0">Puede iniciar sesión y usar el sistema.</p>';
                
                if ($ventas == 0) {
                    echo '<p class="mb-0 text-warning"><i class="fas fa-info-circle"></i> No hay ventas registradas todavía. Cree una venta desde el POS.</p>';
                }
            } else {
                echo '<p class="mb-0 text-danger"><strong>⚠️ PROBLEMA: Faltan datos básicos</strong></p>';
                
                if ($usuarios == 0) {
                    echo '<p class="mb-0">❌ No hay usuarios - Ejecute el instalador</p>';
                }
                if ($categorias == 0) {
                    echo '<p class="mb-0">❌ No hay categorías - Ejecute el instalador</p>';
                }
                if ($productos == 0) {
                    echo '<p class="mb-0">❌ No hay productos - Ejecute el instalador</p>';
                }
            }
            echo '</div>';

            echo '<div class="d-grid gap-2 mt-4">';
            echo '<a href="../install-complete.php" class="btn btn-primary btn-lg">';
            echo '<i class="fas fa-redo"></i> Reinstalar Base de Datos';
            echo '</a>';
            echo '<a href="../" class="btn btn-success btn-lg">';
            echo '<i class="fas fa-arrow-left"></i> Ir al Sistema';
            echo '</a>';
            echo '</div>';

        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">';
            echo '<h5><i class="fas fa-exclamation-circle"></i> Error de Conexión</h5>';
            echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p class="mb-0">Verifique la configuración de la base de datos en <code>config/config.php</code></p>';
            echo '</div>';

            echo '<div class="card mt-3">';
            echo '<div class="card-header">Configuración Actual</div>';
            echo '<div class="card-body">';
            echo '<code>';
            echo 'DB_HOST: ' . DB_HOST . '<br>';
            echo 'DB_PORT: ' . DB_PORT . '<br>';
            echo 'DB_NAME: ' . DB_NAME . '<br>';
            echo 'DB_USER: ' . DB_USER . '<br>';
            echo 'DB_PASSWORD: ' . (DB_PASSWORD ? '***' : '(vacío)') . '<br>';
            echo '</code>';
            echo '</div>';
            echo '</div>';

            echo '<div class="d-grid gap-2 mt-4">';
            echo '<a href="../install-complete.php" class="btn btn-primary btn-lg">';
            echo '<i class="fas fa-database"></i> Ejecutar Instalador';
            echo '</a>';
            echo '</div>';
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
