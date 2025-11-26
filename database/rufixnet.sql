-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol ENUM('ventas', 'compras', 'admin') NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_empresa VARCHAR(255) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion INT,
    FOREIGN KEY (usuario_creacion) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de proveedores
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_proveedor VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    fuente TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion INT,
    FOREIGN KEY (usuario_creacion) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255),
    enlace_compra VARCHAR(500),
    precio_unitario DECIMAL(10,2),
    cantidad_disponible INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    proveedor_id INT,
    usuario_creacion INT,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_creacion) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de ventas
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    gastos_extra DECIMAL(10,2) DEFAULT 0,
    metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta_debito', 'tarjeta_credito') NOT NULL,
    comprobante_pago VARCHAR(255),
    precio_final DECIMAL(10,2) NOT NULL,
    observaciones TEXT,
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion INT,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_creacion) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de detalles de venta
CREATE TABLE detalle_ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de compras
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT NOT NULL,
    observaciones TEXT,
    precio_final DECIMAL(10,2) NOT NULL,
    fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion INT,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_creacion) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de detalles de compra
CREATE TABLE detalle_compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compra_id INT NOT NULL,
    nombre_producto VARCHAR(255) NOT NULL,
    imagen_producto VARCHAR(255),
    enlace_compra VARCHAR(500),
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE
);

-- Insertar usuarios de ejemplo (contraseña: password123)
INSERT INTO usuarios (username, password, nombre, rol) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'admin'),
('ventas1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez - Ventas', 'ventas'),
('compras1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María García - Compras', 'compras');

-- Insertar algunos datos de ejemplo
INSERT INTO clientes (nombre_empresa, direccion, telefono, email, observaciones, usuario_creacion) VALUES
('Empresa ABC', 'Av. Principal 123, CDMX', '555-123-4567', 'contacto@empresaabc.com', 'Cliente preferente', 1),
('Tienda XYZ', 'Calle Secundaria 456, Guadalajara', '333-987-6543', 'info@xyz.com', 'Paga siempre a tiempo', 1);

INSERT INTO proveedores (nombre_proveedor, telefono, email, fuente, usuario_creacion) VALUES
('Proveedor Tech', '555-111-2233', 'ventas@tech.com', 'https://www.proveedortech.com', 1),
('Suministros Global', '555-444-5566', 'contacto@suministros.com', 'Av. Industrial 789, Monterrey', 1);

INSERT INTO productos (nombre, descripcion, precio_unitario, cantidad_disponible, proveedor_id, usuario_creacion) VALUES
('Laptop HP Pavilion', 'Laptop 15.6 pulgadas, 8GB RAM, 256GB SSD', 15000.00, 10, 1, 1),
('Mouse Inalámbrico', 'Mouse ergonómico inalámbrico', 450.00, 25, 2, 1),
('Teclado Mecánico', 'Teclado mecánico RGB', 1200.00, 15, 1, 1);