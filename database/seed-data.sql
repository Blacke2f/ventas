-- Insertar categorías
INSERT INTO categorias (nombre_categoria, descripcion) VALUES
('Abarrotes', 'Productos básicos de despensa'),
('Lácteos', 'Leche y derivados'),
('Bebidas', 'Refrescos y bebidas'),
('Carnes', 'Productos cárnicos'),
('Verduras', 'Frutas y verduras frescas');

-- Insertar productos de ejemplo
INSERT INTO productos (nombre_producto, id_categoria, precio_venta, stock_actual, activo) VALUES
('Arroz 1kg', 1, 1.30, 50, 1),
('Aceite 1L', 1, 2.50, 30, 1),
('Harina 1kg', 1, 1.50, 40, 1),
('Leche 1L', 2, 1.50, 60, 1),
('Queso Blanco 500g', 2, 4.50, 25, 1),
('Coca Cola 2L', 3, 2.50, 45, 1),
('Agua 1.5L', 3, 0.80, 100, 1),
('Pollo 1kg', 4, 3.50, 35, 1),
('Salchicha 500g', 4, 3.50, 20, 1),
('Tomate 1kg', 5, 1.20, 50, 1),
('Cebolla 1kg', 5, 0.80, 60, 1),
('Papas 1kg', 5, 1.00, 70, 1);

-- Insertar clientes de ejemplo
INSERT INTO clientes (nombre_cliente, email, telefono, documento_identidad, saldo_fiado, activo) VALUES
('Juan Pérez', 'juan@email.com', '555-1234', '12345678', 0.00, 1),
('María García', 'maria@email.com', '555-5678', '87654321', 0.00, 1),
('Carlos López', 'carlos@email.com', '555-9999', '11223344', 0.00, 1);
