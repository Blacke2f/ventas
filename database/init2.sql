DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  rol ENUM('admin', 'cajero') NOT NULL DEFAULT 'cajero',
  nombre_completo VARCHAR(150) NOT NULL,
  activo TINYINT(1) DEFAULT 1,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO usuarios (nombre_usuario, contrasena, email, rol, nombre_completo) VALUES
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/TVm', 'admin@abastos.com', 'admin', 'Administrador Principal');
