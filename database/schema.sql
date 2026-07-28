-- =====================================================
-- AbasPOS - Sistema de Punto de Venta para Abastos
-- Base de Datos Completa
-- =====================================================

CREATE DATABASE IF NOT EXISTS abastospos;
USE abastospos;

-- =====================================================
-- 1. TABLA: USUARIOS (Cajeros, Administradores)
-- =====================================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    rol ENUM('admin', 'cajero') NOT NULL DEFAULT 'cajero',
    nombre_completo VARCHAR(150) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. TABLA: CLIENTES
-- =====================================================
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(150) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    documento_identidad VARCHAR(50) UNIQUE,
    limite_monto_fiado DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Límite de monto que puede fiar',
    limite_tiempo_dias INT DEFAULT 30 COMMENT 'Días para pagar créditos',
    saldo_fiado DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Total adeudado actualmente',
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. TABLA: CATEGORIAS DE PRODUCTOS
-- =====================================================
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    icono VARCHAR(50),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TABLA: PRODUCTOS
-- =====================================================
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_producto VARCHAR(150) NOT NULL,
    descripcion TEXT,
    id_categoria INT NOT NULL,
    precio_costo DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Precio de costo unitario',
    porcentaje_ganancia DECIMAL(5, 2) DEFAULT 0.00 COMMENT 'Porcentaje de ganancia aplicado',
    precio_venta DECIMAL(10, 2) NOT NULL COMMENT 'Precio de venta al público',
    precio_mayoreo DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Precio de compra por bulto/paquete',
    unidades_por_bulto INT DEFAULT 1 COMMENT 'Cantidad de unidades en un bulto',
    stock_actual INT DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    imagen_url VARCHAR(255),
    codigo_barras VARCHAR(100) UNIQUE,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. TABLA: VENTAS (Encabezado)
-- =====================================================
CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    numero_venta VARCHAR(50) NOT NULL UNIQUE,
    id_cliente INT,
    id_usuario INT NOT NULL,
    tipo_pago ENUM('efectivo', 'tarjeta', 'fiado') NOT NULL DEFAULT 'efectivo',
    subtotal DECIMAL(10, 2) NOT NULL,
    descuento DECIMAL(10, 2) DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL,
    estado_venta ENUM('pendiente', 'pagada', 'cancelada') NOT NULL DEFAULT 'pagada',
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notas TEXT,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    INDEX idx_fecha_venta (fecha_venta),
    INDEX idx_tipo_pago (tipo_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. TABLA: DETALLE DE VENTAS (Ítems)
-- =====================================================
CREATE TABLE detalle_ventas (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. TABLA: CREDITOS (Fiados - Registro detallado)
-- =====================================================
CREATE TABLE creditos (
    id_credito INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL UNIQUE,
    id_cliente INT NOT NULL,
    monto_original DECIMAL(10, 2) NOT NULL,
    monto_abonado DECIMAL(10, 2) DEFAULT 0.00,
    monto_pendiente DECIMAL(10, 2) NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    estado_credito ENUM('activo', 'pagado', 'vencido', 'parcial') NOT NULL DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE RESTRICT,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT,
    INDEX idx_estado_credito (estado_credito),
    INDEX idx_fecha_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. TABLA: ABONOS (Pagos parciales a créditos)
-- =====================================================
CREATE TABLE abonos (
    id_abono INT AUTO_INCREMENT PRIMARY KEY,
    id_credito INT NOT NULL,
    monto_abono DECIMAL(10, 2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL DEFAULT 'efectivo',
    id_usuario INT NOT NULL,
    fecha_abono TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notas TEXT,
    FOREIGN KEY (id_credito) REFERENCES creditos(id_credito) ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. TABLA: AUDITORIA (Log de operaciones)
-- =====================================================
CREATE TABLE auditoria (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    accion VARCHAR(255) NOT NULL,
    tabla_afectada VARCHAR(100),
    registro_id INT,
    valores_antiguos JSON,
    valores_nuevos JSON,
    ip_address VARCHAR(45),
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ÍNDICES DE RENDIMIENTO
-- =====================================================
CREATE INDEX idx_clientes_activos ON clientes(activo, fecha_creacion);
CREATE INDEX idx_productos_categoria ON productos(id_categoria, activo);
CREATE INDEX idx_creditos_cliente ON creditos(id_cliente, estado_credito);
CREATE INDEX idx_abonos_credito ON abonos(id_credito, fecha_abono);
CREATE INDEX idx_ventas_usuario ON ventas(id_usuario, fecha_venta);

-- =====================================================
-- DATOS INICIALES (Demo)
-- =====================================================

-- Usuarios de prueba
INSERT INTO usuarios (nombre_usuario, contraseña, email, rol, nombre_completo) VALUES
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/TVm', 'admin@gastropos.com', 'admin', 'Administrador Principal'),
('cajero1', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/TVm', 'cajero1@gastropos.com', 'cajero', 'Juan Pérez');

-- Categorías de productos de abarrotes
INSERT INTO categorias (nombre_categoria, descripcion, icono) VALUES
('Abarrotes', 'Productos básicos de despensa', 'fa-box'),
('Enlatados', 'Conservas y productos en lata', 'fa-jar'),
('Lácteos', 'Leche y derivados', 'fa-cow'),
('Botanas', 'Snacks y frituras', 'fa-cookie-bite'),
('Confitería', 'Dulces y chocolates', 'fa-candy-cane'),
('Harinas y Pan', 'Productos de panadería', 'fa-bread-slice'),
('Frutas y Verduras', 'Productos frescos', 'fa-carrot'),
('Bebidas', 'Refrescos y bebidas', 'fa-bottle-water'),
('Bebidas Alcohólicas', 'Licores y cervezas', 'fa-wine-bottle'),
('Carnes y Embutidos', 'Productos cárnicos', 'fa-bacon'),
('Automedicación', 'Productos de primeros auxilios', 'fa-kit-medical'),
('Higiene Personal', 'Cuidado personal', 'fa-pump-soap'),
('Uso Doméstico', 'Productos de limpieza', 'fa-spray-can-sparkles'),
('Helados', 'Helados y paletas', 'fa-ice-cream'),
('Jarcería', 'Productos de limpieza y desechables', 'fa-broom');

-- Productos de Abarrotes (precios en USD)
INSERT INTO productos (nombre_producto, descripcion, id_categoria, precio_venta, stock_actual, stock_minimo, codigo_barras) VALUES
-- ABARROTES
('Aceite Vegetal 1L', 'Aceite comestible', 1, 2.50, 50, 10, 'AB001'),
('Mayonesa 400g', 'Aderezo mayonesa', 1, 2.00, 40, 10, 'AB002'),
('Consomé Cubo 12u', 'Cubitos de caldo', 1, 1.20, 60, 15, 'AB003'),
('Crema de Cacahuate 350g', 'Mantequilla de maní', 1, 3.50, 30, 8, 'AB004'),
('Puré de Tomate 200g', 'Pasta de tomate', 1, 0.90, 45, 12, 'AB005'),
('Avena 500g', 'Hojuelas de avena', 1, 1.80, 35, 10, 'AB006'),
('Azúcar 1kg', 'Azúcar blanca refinada', 1, 1.20, 80, 20, 'AB007'),
('Café Molido 250g', 'Café tostado y molido', 1, 4.50, 25, 8, 'AB008'),
('Cereal Corn Flakes 500g', 'Cereal de maíz', 1, 3.20, 30, 10, 'AB009'),
('Especias Mix 100g', 'Mezcla de especias', 1, 2.00, 20, 5, 'AB010'),
('Gelatina Fresa 100g', 'Gelatina en polvo', 1, 0.80, 50, 15, 'AB011'),
('Harina de Trigo 1kg', 'Harina todo uso', 1, 1.50, 60, 15, 'AB012'),
('Harina de Maíz 1kg', 'Harina P.A.N.', 1, 1.60, 55, 15, 'AB013'),
('Sal 1kg', 'Sal de mesa', 1, 0.60, 100, 25, 'AB014'),
('Salsa de Soya 250ml', 'Salsa soya', 1, 1.80, 30, 8, 'AB015'),
('Pasta Corta 500g', 'Pasta tipo caracol', 1, 0.90, 70, 20, 'AB016'),
('Arroz 1kg', 'Arroz blanco', 1, 1.30, 90, 25, 'AB017'),
('Catsup 400g', 'Salsa catsup', 1, 1.70, 35, 10, 'AB018'),
('Mermelada Fresa 300g', 'Mermelada de fresa', 1, 2.50, 25, 8, 'AB019'),
('Miel de Abeja 250g', 'Miel natural', 1, 4.00, 20, 5, 'AB020'),
('Té Negro 25 sobres', 'Té en bolsitas', 1, 2.20, 40, 10, 'AB021'),
('Vinagre 500ml', 'Vinagre blanco', 1, 1.10, 45, 12, 'AB022'),
('Huevos 12u', 'Huevos frescos', 1, 3.50, 60, 20, 'AB023'),

-- ENLATADOS
('Aceitunas 350g', 'Aceitunas verdes', 2, 2.80, 30, 8, 'EN001'),
('Champiñones 400g', 'Champiñones enteros', 2, 2.50, 25, 8, 'EN002'),
('Chícharos 400g', 'Chícharos enlatados', 2, 1.50, 40, 12, 'EN003'),
('Frijoles Negros 400g', 'Frijoles enlatados', 2, 1.80, 50, 15, 'EN004'),
('Duraznos en Almíbar 800g', 'Frutas en conserva', 2, 3.20, 20, 6, 'EN005'),
('Sardinas 425g', 'Sardinas en tomate', 2, 2.00, 45, 12, 'EN006'),
('Atún en Agua 140g', 'Atún enlatado', 2, 1.50, 80, 20, 'EN007'),
('Chiles Jalapeños 380g', 'Chiles enlatados', 2, 2.30, 30, 8, 'EN008'),
('Elote en Grano 400g', 'Maíz dulce', 2, 1.60, 35, 10, 'EN009'),
('Sopa de Verduras 400g', 'Sopa en lata', 2, 2.20, 25, 8, 'EN010'),

-- LÁCTEOS
('Leche Condensada 397g', 'Leche azucarada', 3, 2.50, 40, 10, 'LA001'),
('Leche Deslactosada 1L', 'Leche sin lactosa', 3, 2.00, 30, 10, 'LA002'),
('Leche en Polvo 400g', 'Leche en polvo', 3, 5.50, 20, 6, 'LA003'),
('Leche Evaporada 410g', 'Leche evaporada', 3, 1.80, 35, 10, 'LA004'),
('Leche Pasteurizada 1L', 'Leche fresca', 3, 1.50, 50, 15, 'LA005'),
('Crema 200ml', 'Crema de leche', 3, 1.80, 30, 8, 'LA006'),
('Yogurt Natural 1L', 'Yogurt sin azúcar', 3, 2.50, 25, 8, 'LA007'),
('Mantequilla 250g', 'Mantequilla con sal', 3, 3.50, 20, 6, 'LA008'),
('Margarina 500g', 'Margarina vegetal', 3, 2.80, 25, 8, 'LA009'),
('Queso Blanco 500g', 'Queso fresco', 3, 4.50, 20, 5, 'LA010'),

-- BOTANAS
('Papas Fritas 150g', 'Papas chips natural', 4, 1.80, 50, 15, 'BO001'),
('Palomitas de Maíz 100g', 'Palomitas para microondas', 4, 1.20, 40, 12, 'BO002'),
('Cheetos 90g', 'Frituras de maíz', 4, 1.50, 60, 18, 'BO003'),
('Cacahuates 200g', 'Maní salado', 4, 2.00, 35, 10, 'BO004'),
('Mix de Nueces 150g', 'Mezcla de nueces', 4, 3.50, 20, 6, 'BO005'),

-- CONFITERÍA
('Caramelos 100g', 'Caramelos surtidos', 5, 1.00, 50, 15, 'CO001'),
('Dulce Enchilado 100g', 'Dulce picante', 5, 0.80, 45, 12, 'CO002'),
('Chocolate Tableta 100g', 'Chocolate con leche', 5, 2.00, 40, 12, 'CO003'),
('Gomas de Mascar 20u', 'Chicles sabores', 5, 1.50, 60, 18, 'CO004'),
('Mazapán 20g', 'Dulce de cacahuate', 5, 0.50, 80, 25, 'CO005'),
('Malvaviscos 200g', 'Bombones esponjosos', 5, 1.80, 30, 10, 'CO006'),
('Pulpa Tamarindo 100g', 'Tamarindo natural', 5, 1.20, 35, 10, 'CO007'),

-- HARINAS Y PAN
('Tortillas de Maíz 1kg', 'Tortillas frescas', 6, 1.50, 40, 12, 'HA001'),
('Galletas Marías 200g', 'Galletas dulces', 6, 1.20, 50, 15, 'HA002'),
('Galletas Saladas 250g', 'Galletas de soda', 6, 1.50, 45, 12, 'HA003'),
('Pan de Caja Blanco 680g', 'Pan de molde', 6, 2.00, 30, 10, 'HA004'),
('Pan Dulce 6u', 'Conchas surtidas', 6, 2.50, 25, 8, 'HA005'),

-- FRUTAS Y VERDURAS
('Aguacate Hass u', 'Aguacate fresco', 7, 1.50, 30, 10, 'FV001'),
('Cebolla 1kg', 'Cebolla blanca', 7, 0.80, 50, 15, 'FV002'),
('Chile Serrano 100g', 'Chile fresco', 7, 0.60, 40, 12, 'FV003'),
('Cilantro Manojo', 'Cilantro fresco', 7, 0.40, 35, 10, 'FV004'),
('Jitomate 1kg', 'Tomate rojo', 7, 1.20, 60, 20, 'FV005'),
('Papas 1kg', 'Papa blanca', 7, 1.00, 70, 20, 'FV006'),
('Limones 1kg', 'Limón verde', 7, 1.50, 50, 15, 'FV007'),
('Manzanas 1kg', 'Manzana roja', 7, 2.50, 40, 12, 'FV008'),
('Naranjas 1kg', 'Naranja dulce', 7, 1.80, 45, 15, 'FV009'),
('Plátanos 1kg', 'Plátano maduro', 7, 1.20, 55, 18, 'FV010'),

-- BEBIDAS
('Agua Natural 1.5L', 'Agua purificada', 8, 0.80, 100, 30, 'BE001'),
('Agua Mineral 600ml', 'Agua con gas', 8, 1.00, 80, 25, 'BE002'),
('Jugo de Naranja 1L', 'Jugo natural', 8, 2.50, 40, 12, 'BE003'),
('Coca Cola 2L', 'Refresco de cola', 8, 2.50, 60, 20, 'BE004'),
('Pepsi 2L', 'Refresco de cola', 8, 2.30, 55, 18, 'BE005'),
('Sprite 2L', 'Refresco de lima-limón', 8, 2.40, 50, 15, 'BE006'),
('Energizante Red Bull 250ml', 'Bebida energética', 8, 2.50, 35, 10, 'BE007'),

-- BEBIDAS ALCOHÓLICAS
('Cerveza Lata 355ml', 'Cerveza nacional', 9, 1.50, 100, 30, 'AL001'),
('Cerveza Botella 355ml', 'Cerveza en vidrio', 9, 1.80, 80, 25, 'AL002'),
('Ron 750ml', 'Ron añejo', 9, 15.00, 15, 5, 'AL003'),
('Tequila 750ml', 'Tequila blanco', 9, 18.00, 12, 4, 'AL004'),
('Whiskey 750ml', 'Whiskey escocés', 9, 25.00, 10, 3, 'AL005'),
('Vodka 750ml', 'Vodka premium', 9, 20.00, 12, 4, 'AL006'),

-- CARNES Y EMBUTIDOS
('Salchicha 500g', 'Salchichas de pavo', 10, 3.50, 30, 10, 'CA001'),
('Mortadela 500g', 'Mortadela especial', 10, 2.80, 25, 8, 'CA002'),
('Tocino 250g', 'Tocino ahumado', 10, 4.00, 20, 6, 'CA003'),
('Jamón 200g', 'Jamón de pavo', 10, 3.50, 25, 8, 'CA004'),
('Chorizo 500g', 'Chorizo mexicano', 10, 3.80, 20, 6, 'CA005'),
('Pollo Entero 1kg', 'Pollo fresco', 10, 3.50, 40, 12, 'CA006'),

-- AUTOMEDICACIÓN
('Suero Oral 625ml', 'Suero rehidratante', 11, 1.50, 30, 10, 'AU001'),
('Agua Oxigenada 120ml', 'Desinfectante', 11, 0.80, 40, 12, 'AU002'),
('Preservativos 3u', 'Condones', 11, 2.50, 25, 8, 'AU003'),
('Alcohol 250ml', 'Alcohol antiséptico', 11, 1.20, 50, 15, 'AU004'),
('Gasas 10u', 'Gasas estériles', 11, 1.50, 35, 10, 'AU005'),
('Paracetamol 500mg 10tab', 'Analgésico', 11, 2.00, 40, 12, 'AU006'),

-- HIGIENE PERSONAL
('Toallas Húmedas 50u', 'Toallitas limpiadoras', 12, 2.50, 30, 10, 'HI001'),
('Toallas Femeninas 10u', 'Toallas sanitarias', 12, 2.00, 35, 10, 'HI002'),
('Algodón 50g', 'Algodón hidrófilo', 12, 1.20, 40, 12, 'HI003'),
('Cepillo Dental u', 'Cepillo de dientes', 12, 1.50, 50, 15, 'HI004'),
('Shampoo 400ml', 'Shampoo anticaspa', 12, 3.50, 30, 10, 'HI005'),
('Crema Dental 150ml', 'Pasta de dientes', 12, 2.50, 40, 12, 'HI006'),
('Papel Higiénico 4 Rollos', 'Papel suave', 12, 2.80, 60, 20, 'HI007'),
('Desodorante 150ml', 'Desodorante spray', 12, 3.00, 30, 10, 'HI008'),
('Jabón Tocador 125g', 'Jabón antibacterial', 12, 1.50, 50, 15, 'HI009'),

-- USO DOMÉSTICO
('Detergente en Polvo 1kg', 'Detergente para ropa', 13, 3.50, 30, 10, 'UD001'),
('Cloro 1L', 'Blanqueador', 13, 1.50, 40, 12, 'UD002'),
('Jabón de Lavar 500g', 'Jabón en barra', 13, 1.20, 50, 15, 'UD003'),
('Lavavajillas 500ml', 'Líquido para trastes', 13, 2.00, 35, 10, 'UD004'),
('Suavizante 1L', 'Suavizante de telas', 13, 2.50, 30, 10, 'UD005'),
('Servilletas 100u', 'Servilletas de papel', 13, 1.00, 60, 20, 'UD006'),
('Aluminio 30m', 'Papel aluminio', 13, 2.50, 25, 8, 'UD007'),
('Pilas AA 4u', 'Pilas alcalinas', 13, 3.00, 40, 12, 'UD008'),

-- HELADOS
('Paletas de Hielo 6u', 'Paletas sabores', 14, 2.50, 50, 15, 'HE001'),
('Helado 1L', 'Helado de vainilla', 14, 5.00, 20, 6, 'HE002'),

-- JARCERÍA
('Veladoras 3u', 'Velas aromáticas', 15, 2.00, 30, 10, 'JA001'),
('Vasos Desechables 50u', 'Vasos de plástico', 15, 1.50, 40, 12, 'JA002'),
('Platos Desechables 20u', 'Platos de cartón', 15, 2.00, 35, 10, 'JA003'),
('Escoba u', 'Escoba de plástico', 15, 3.50, 15, 5, 'JA004'),
('Trapeador u', 'Trapeador de pabilo', 15, 4.00, 12, 4, 'JA005'),
('Focos 60W 2u', 'Focos ahorradores', 15, 3.00, 25, 8, 'JA006');

-- Clientes de ejemplo
INSERT INTO clientes (nombre_cliente, email, telefono, documento_identidad, limite_monto_fiado, limite_tiempo_dias, saldo_fiado) VALUES
('Carlos González', 'carlos@email.com', '555-1234', '12345678', 500.00, 30, 0.00),
('María López', 'maria@email.com', '555-5678', '87654321', 300.00, 15, 0.00),
('Pedro Sánchez', 'pedro@email.com', '555-9999', '11223344', 1000.00, 60, 0.00);

-- =====================================================
-- VISTAS ÚTILES PARA REPORTES
-- =====================================================

CREATE VIEW v_resumen_ventas_diarias AS
SELECT 
    DATE(fecha_venta) as fecha,
    COUNT(*) as total_transacciones,
    SUM(total) as total_vendido,
    AVG(total) as promedio_venta,
    COUNT(DISTINCT id_cliente) as clientes_unicos
FROM ventas
WHERE estado_venta = 'pagada'
GROUP BY DATE(fecha_venta)
ORDER BY fecha DESC;

CREATE VIEW v_creditos_vencidos AS
SELECT 
    c.id_credito,
    c.id_cliente,
    cl.nombre_cliente,
    c.monto_pendiente,
    c.fecha_vencimiento,
    DATEDIFF(NOW(), c.fecha_vencimiento) as dias_vencido
FROM creditos c
JOIN clientes cl ON c.id_cliente = cl.id_cliente
WHERE c.estado_credito IN ('activo', 'vencido', 'parcial')
AND c.fecha_vencimiento < NOW()
ORDER BY c.fecha_vencimiento ASC;

CREATE VIEW v_clientes_con_deuda AS
SELECT 
    id_cliente,
    nombre_cliente,
    saldo_fiado,
    limite_monto_fiado,
    (limite_monto_fiado - saldo_fiado) as limite_disponible,
    (saldo_fiado / limite_monto_fiado * 100) as porcentaje_utilizacion
FROM clientes
WHERE saldo_fiado > 0
ORDER BY saldo_fiado DESC;

-- =====================================================
-- FIN DEL SCRIPT SQL
-- =====================================================
