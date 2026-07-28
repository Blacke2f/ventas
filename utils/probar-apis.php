<?php
/**
 * Probar APIs del Sistema
 * Prueba todos los endpoints para verificar funcionamiento
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$baseURL = 'http://localhost/Sistema%20de%20venta';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Probar APIs - AbasPOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
        }
        .test-card {
            margin-bottom: 15px;
            border-left: 4px solid #6c757d;
        }
        .test-card.success {
            border-left-color: #28a745;
        }
        .test-card.error {
            border-left-color: #dc3545;
        }
        .test-card.loading {
            border-left-color: #ffc107;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            max-height: 200px;
            overflow: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4"><i class="fas fa-vial"></i> Prueba de APIs - AbasPOS</h2>
        
        <button class="btn btn-primary mb-4" onclick="probarTodo()">
            <i class="fas fa-play"></i> Probar Todas las APIs
        </button>

        <div id="resultados"></div>
    </div>

    <script>
        const baseURL = '<?php echo $baseURL; ?>';
        
        const tests = [
            {
                name: 'Tasa de Cambio BCV',
                url: '/api-tasa-cambio.php',
                method: 'GET'
            },
            {
                name: 'Listar Productos',
                url: '/api/productos?action=list',
                method: 'GET'
            },
            {
                name: 'Listar Categorías',
                url: '/api/productos?action=categorias',
                method: 'GET'
            },
            {
                name: 'Buscar Producto (harina)',
                url: '/api/productos?action=search&q=harina',
                method: 'GET'
            },
            {
                name: 'Productos con Stock Bajo',
                url: '/api/productos?action=stock-bajo',
                method: 'GET'
            },
            {
                name: 'Top Productos Vendidos',
                url: '/api/productos?action=top-vendidos&limit=5',
                method: 'GET'
            },
            {
                name: 'Listar Clientes',
                url: '/api/clientes?action=list',
                method: 'GET'
            },
            {
                name: 'Buscar Cliente (carlos)',
                url: '/api/clientes?action=search&q=carlos',
                method: 'GET'
            },
            {
                name: 'Clientes con Deuda',
                url: '/api/clientes?action=con-deuda',
                method: 'GET'
            },
            {
                name: 'Ventas del Día',
                url: '/api/ventas?action=hoy',
                method: 'GET'
            },
            {
                name: 'Resumen Diario de Ventas',
                url: '/api/ventas?action=resumen-diario',
                method: 'GET'
            },
            {
                name: 'Créditos Vencidos',
                url: '/api/creditos?action=vencidos',
                method: 'GET'
            }
        ];

        async function probarAPI(test) {
            const card = document.getElementById(`test-${tests.indexOf(test)}`);
            card.className = 'card test-card loading';
            
            const startTime = Date.now();
            
            try {
                const response = await fetch(baseURL + test.url, {
                    method: test.method,
                    credentials: 'same-origin'
                });
                
                const endTime = Date.now();
                const duration = endTime - startTime;
                
                const data = await response.json();
                
                if (response.ok) {
                    card.className = 'card test-card success';
                    card.innerHTML = `
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-check-circle text-success"></i> 
                                ${test.name}
                            </h6>
                            <small class="text-muted">
                                ${test.method} ${test.url}
                                <span class="badge bg-success">${response.status}</span>
                                <span class="badge bg-info">${duration}ms</span>
                            </small>
                            <details class="mt-2">
                                <summary class="btn btn-sm btn-outline-secondary">Ver Respuesta</summary>
                                <pre class="mt-2">${JSON.stringify(data, null, 2)}</pre>
                            </details>
                        </div>
                    `;
                } else {
                    card.className = 'card test-card error';
                    card.innerHTML = `
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-exclamation-circle text-danger"></i> 
                                ${test.name}
                            </h6>
                            <small class="text-muted">
                                ${test.method} ${test.url}
                                <span class="badge bg-danger">${response.status}</span>
                                <span class="badge bg-info">${duration}ms</span>
                            </small>
                            <div class="alert alert-danger mt-2 mb-0">
                                ${data.error || data.message || 'Error desconocido'}
                            </div>
                            <details class="mt-2">
                                <summary class="btn btn-sm btn-outline-secondary">Ver Respuesta</summary>
                                <pre class="mt-2">${JSON.stringify(data, null, 2)}</pre>
                            </details>
                        </div>
                    `;
                }
            } catch (error) {
                const endTime = Date.now();
                const duration = endTime - startTime;
                
                card.className = 'card test-card error';
                card.innerHTML = `
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-times-circle text-danger"></i> 
                            ${test.name}
                        </h6>
                        <small class="text-muted">
                            ${test.method} ${test.url}
                            <span class="badge bg-danger">ERROR</span>
                            <span class="badge bg-info">${duration}ms</span>
                        </small>
                        <div class="alert alert-danger mt-2 mb-0">
                            ${error.message}
                        </div>
                    </div>
                `;
            }
        }

        async function probarTodo() {
            const resultados = document.getElementById('resultados');
            resultados.innerHTML = '';
            
            // Crear cards de carga
            tests.forEach((test, index) => {
                const card = document.createElement('div');
                card.id = `test-${index}`;
                card.className = 'card test-card loading';
                card.innerHTML = `
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-spinner fa-spin"></i> 
                            ${test.name}
                        </h6>
                        <small class="text-muted">Probando...</small>
                    </div>
                `;
                resultados.appendChild(card);
            });

            // Probar cada API
            for (const test of tests) {
                await probarAPI(test);
                await new Promise(resolve => setTimeout(resolve, 200)); // Esperar 200ms entre pruebas
            }

            // Resumen
            const successCards = document.querySelectorAll('.test-card.success').length;
            const errorCards = document.querySelectorAll('.test-card.error').length;
            
            const summary = document.createElement('div');
            summary.className = 'alert ' + (errorCards === 0 ? 'alert-success' : 'alert-warning');
            summary.innerHTML = `
                <h5><i class="fas fa-chart-pie"></i> Resumen</h5>
                <p class="mb-0">
                    <strong>${successCards}</strong> exitosas &nbsp;|&nbsp;
                    <strong>${errorCards}</strong> fallidas &nbsp;|&nbsp;
                    <strong>${tests.length}</strong> total
                </p>
            `;
            resultados.insertBefore(summary, resultados.firstChild);
        }

        // Auto-ejecutar al cargar
        window.addEventListener('DOMContentLoaded', probarTodo);
    </script>
</body>
</html>
