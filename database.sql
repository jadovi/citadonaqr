-- Base de datos para sistema de acreditación de visitantes
CREATE DATABASE IF NOT EXISTS evento_acreditacion;
USE evento_acreditacion;

-- Tabla de eventos
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    empresa VARCHAR(255) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    descripcion TEXT,
    link_codigo VARCHAR(255) UNIQUE NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de visitantes
CREATE TABLE visitantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    empresa VARCHAR(255),
    rut VARCHAR(20),
    cargo VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rut (rut),
    INDEX idx_nombre (nombre, apellido),
    INDEX idx_empresa (empresa)
);

-- Tabla de inscripciones (relación entre visitantes y eventos)
CREATE TABLE inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    visitante_id INT NOT NULL,
    codigo_qr VARCHAR(255) UNIQUE NOT NULL,
    estado ENUM('pendiente', 'confirmado', 'cancelado') DEFAULT 'pendiente',
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmado_email BOOLEAN DEFAULT FALSE,
    recordatorio_enviado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (visitante_id) REFERENCES visitantes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_inscripcion (evento_id, visitante_id)
);

-- Tabla de accesos (registro de entrada al evento)
CREATE TABLE accesos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id INT NOT NULL,
    fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id) ON DELETE CASCADE
);

-- Tabla de usuarios admin
CREATE TABLE usuarios_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar usuario admin por defecto
INSERT INTO usuarios_admin (username, password, email, nombre) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@evento.com', 'Administrador');
-- Password por defecto: password

-- Tabla de configuración del sistema
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion TEXT
);

-- Configuraciones por defecto
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('smtp_host', 'localhost', 'Servidor SMTP'),
('smtp_port', '587', 'Puerto SMTP'),
('smtp_username', '', 'Usuario SMTP'),
('smtp_password', '', 'Contraseña SMTP'),
('smtp_from_email', 'noreply@evento.com', 'Email remitente'),
('smtp_from_name', 'Sistema de Eventos', 'Nombre remitente'),
('site_url', 'http://localhost/claudeson4-qr', 'URL base del sitio');
