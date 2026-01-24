CREATE TABLE IF NOT EXISTS ofertas_calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    calificacion ENUM('Cumple', 'No Cumple') NOT NULL,
    comentario VARCHAR(300) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uniq_oferta_calificacion (producto_id, usuario_id),
    INDEX idx_oferta_calificacion_producto (producto_id),
    INDEX idx_oferta_calificacion_usuario (usuario_id)
);

