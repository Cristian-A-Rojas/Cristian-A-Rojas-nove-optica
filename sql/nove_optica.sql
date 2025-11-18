
CREATE DATABASE IF NOT EXISTS nove_optica CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE nove_optica;

-- ==========================================================
--  TABLA USUARIOS
-- ==========================================================
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(77) NOT NULL,
  correo VARCHAR(77) NOT NULL UNIQUE,
  clave VARCHAR(255) NOT NULL,
  telefono VARCHAR(33),
  verificado TINYINT(1) DEFAULT 0,
  tipo ENUM('cliente','admin') DEFAULT 'cliente',
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO usuarios (nombre, correo, clave, telefono, verificado, tipo)
VALUES ('Administrador', 'c.alejandro57175@gmail.com',
        '$2y$10$Lxk4rhPe8OOVC1DeDkMJ3utZqTFiM.3PMpH0s8rktDP0dbzH5P22y',
        '600123456', 1, 'admin');

-- ==========================================================
--  TABLA VERIFICACIONES (Zero Trust)
-- ==========================================================
CREATE TABLE IF NOT EXISTS verificaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  correo VARCHAR(100) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expiracion DATETIME NOT NULL
);

-- ==========================================================
--  TABLA CATEGORIAS
-- ==========================================================
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(77) NOT NULL,
  descripcion VARCHAR(255)
);

INSERT INTO categorias (nombre, descripcion) VALUES
('Gafas de sol', 'Protección UV y estilo moderno.'),
('Gafas graduadas', 'Monturas ligeras y resistentes.'),
('Lentes de contacto', 'Confort y corrección visual.'),
('Accesorios', 'Fundas, paños y productos de limpieza.');

-- ==========================================================
--  TABLA PRODUCTOS
-- ==========================================================
CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(77) NOT NULL,
  codigo VARCHAR(17) UNIQUE,
  descripcion TEXT,
  precio DECIMAL(7,2) NOT NULL,
  stock INT DEFAULT 0,
  id_categoria INT,
  destacado TINYINT(1) DEFAULT 0,
  activo TINYINT(1) DEFAULT 1,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE SET NULL
);

INSERT INTO productos (nombre, codigo, descripcion, precio, stock, id_categoria, destacado) VALUES
('Ray-Ban Clubmaster', 'RB3016', 'Montura clásica con protección UV400.', 189.00, 10, 1, 1),
('Oakley Pitchman', 'OK2001', 'Diseño ultraligero para uso diario.', 159.00, 8, 1, 1),
('Vogue VO5326', 'VG5326', 'Montura moderna con estilo urbano.', 129.00, 6, 2, 1),
('Persol PO3019S', 'PS3019S', 'Montura italiana de acetato premium.', 210.00, 5, 1, 1),
('Prada PR17RV', 'PR17RV', 'Diseño sofisticado con detalles metálicos.', 235.00, 5, 2, 1),
('Tommy TH1785', 'TH1785', 'Gafas deportivas con acabado mate.', 145.00, 7, 1, 0),
('Ray-Ban Erika', 'RB4171', 'Montura redonda con lentes degradadas.', 169.00, 10, 1, 1),
('Oakley Holbrook', 'OK9406', 'Diseño clásico reinventado con tecnología Prizm.', 179.00, 9, 1, 1),
('Vogue VO4195S', 'VG4195S', 'Montura metálica ligera y moderna.', 155.00, 5, 2, 0),
('Persol PO0649', 'PS0649', 'Diseño icónico con máxima comodidad.', 219.00, 8, 1, 1);

-- ==========================================================
--  TABLA IMAGENES
-- ==========================================================
CREATE TABLE IF NOT EXISTS imagenes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT,
  ruta VARCHAR(255) NOT NULL,
  principal TINYINT(1) DEFAULT 1,
  FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);

-- ==========================================================
--  TABLAS PEDIDOS Y DETALLES
-- ==========================================================
CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(10,2) NOT NULL,
  estado ENUM('pendiente','pagado','enviado','entregado','cancelado') DEFAULT 'pendiente',
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pedido_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_pedido INT,
  id_producto INT,
  cantidad INT DEFAULT 1,
  precio_unitario DECIMAL(7,2),
  FOREIGN KEY (id_pedido) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);

-- ==========================================================
--  TABLAS FUNCIONALES ADICIONALES
-- ==========================================================
CREATE TABLE IF NOT EXISTS login (
  id INT AUTO_INCREMENT PRIMARY KEY,
  correo VARCHAR(100),
  ip VARCHAR(45),
  exito TINYINT(1),
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  correo VARCHAR(100) NOT NULL,
  telefono VARCHAR(30),
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS stonks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  cambio INT NOT NULL,
  motivo VARCHAR(100),
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS scout (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT,
  ip VARCHAR(45),
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS comentarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT,
  id_usuario INT,
  comentario TEXT,
  puntuacion INT CHECK (puntuacion BETWEEN 1 AND 5),
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS log_admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_admin INT,
  accion VARCHAR(100),
  descripcion TEXT,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_admin) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS citas_opticas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT,
  fecha DATE,
  hora TIME,
  motivo VARCHAR(255),
  estado ENUM('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS metodos_pago (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50),
  activo TINYINT(1) DEFAULT 1
);

INSERT INTO metodos_pago (nombre) VALUES ('PayPal'), ('Tarjeta'), ('Transferencia');

-- ==========================================================
--  INDICES
-- ==========================================================
CREATE INDEX idx_producto_nombre ON productos(nombre);
CREATE INDEX idx_categoria_nombre ON categorias(nombre);
CREATE INDEX idx_pedido_usuario ON pedidos(id_usuario);
CREATE INDEX idx_pedidos_fecha ON pedidos(fecha);

-- ==========================================================
--  TRIGGER CONTROL DE STOCK (corregido)
-- ==========================================================
DELIMITER $$

CREATE TRIGGER trg_stonks AFTER UPDATE ON productos
FOR EACH ROW
BEGIN
  DECLARE diferencia INT;
  SET diferencia = NEW.stock - OLD.stock;

  IF diferencia <> 0 THEN
    INSERT INTO stonks (id_producto, cambio, motivo)
    VALUES (NEW.id, diferencia, 'Actualización automática de stock');
  END IF;
END$$

DELIMITER ;

-- ==========================================================
--  VISTAS DE COMPATIBILIDAD (stats_data.php)
-- ==========================================================
DROP VIEW IF EXISTS ventas;
CREATE VIEW ventas AS
SELECT 
  p.id AS id,
  p.id_usuario,
  p.fecha,
  p.total,
  p.estado
FROM pedidos p;

DROP VIEW IF EXISTS venta_detalle;
CREATE VIEW venta_detalle AS
SELECT 
  i.id AS id,
  i.id_pedido AS venta_id,
  i.id_producto AS producto_id,
  i.cantidad,
  i.precio_unitario
FROM pedido_items i;

-- ==========================================================
--  USUARIO SEGURO PARA ACCESO APP
-- ==========================================================
DROP USER IF EXISTS 'nove_supremo'@'localhost';
CREATE USER 'nove_supremo'@'localhost' IDENTIFIED BY 'tr3c3.1120+4';
GRANT SELECT, INSERT, UPDATE, DELETE ON nove_optica.* TO 'nove_supremo'@'localhost';

FLUSH PRIVILEGES;
