-- Crear tabla de vacaciones
CREATE TABLE IF NOT EXISTS vacaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_aprobacion DATETIME NULL,
    estado_solicitud ENUM('Pendiente', 'Aprobado', 'Rechazado') DEFAULT 'Pendiente',
    comentarios TEXT,
    aprobado_por INT NULL,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(ID),
    FOREIGN KEY (aprobado_por) REFERENCES usuarios(ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 