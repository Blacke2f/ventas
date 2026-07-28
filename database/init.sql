DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  rol ENUM('admin', 'cajero') NOT NULL DEFAULT 'cajero',
  nombre_completo VARCHAR(150) NOT NULL,
  activo TINYINT(1) DEFAULT 1,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO usuarios (nombre_usuario, password, email, rol, nombre_completo) VALUES
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/TVm', 'admin@abastos.com', 'admin', 'Administrador Principal');

CREATE TABLE IF NOT EXISTS clientes (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  nombre_cliente VARCHAR(150) NOT NULL,
  email VARCHAR(100),
  telefono VARCHAR(20),
  documento_identidad VARCHAR(50) UNIQUE,
  saldo_fiado DECIMAL(10, 2) DEFAULT 0.00,
  activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categorias (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
  descripcion TEXT,
  activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS productos (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  nombre_producto VARCHAR(150) NOT NULL,
  id_categoria INT,
  precio_venta DECIMAL(10, 2) NOT NULL,
  stock_actual INT DEFAULT 0,
  activo TINYINT(1) DEFAULT 1,
  FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ventas (
  id_venta INT AUTO_INCREMENT PRIMARY KEY,
  numero_venta VARCHAR(50) NOT NULL UNIQUE,
  id_usuario INT NOT NULL,
  id_cliente INT,
  total DECIMAL(10, 2) NOT NULL,
  fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
  FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS detalle_ventas (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_venta INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
  FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS creditos (
  id_credito INT AUTO_INCREMENT PRIMARY KEY,
  id_venta INT NOT NULL UNIQUE,
  id_cliente INT NOT NULL,
  monto_pendiente DECIMAL(10, 2) NOT NULL,
  estado_credito ENUM('activo', 'pagado') DEFAULT 'activo',
  FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
  FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS abonos (
  id_abono INT AUTO_INCREMENT PRIMARY KEY,
  id_credito INT NOT NULL,
  monto_abono DECIMAL(10, 2) NOT NULL,
  fecha_abono TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_credito) REFERENCES creditos(id_credito)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
