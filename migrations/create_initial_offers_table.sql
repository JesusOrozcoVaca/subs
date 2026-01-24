CREATE TABLE IF NOT EXISTS ofertas_iniciales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    codigo VARCHAR(32) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uniq_oferta_inicial (producto_id, usuario_id),
    INDEX idx_oferta_inicial_producto (producto_id),
    INDEX idx_oferta_inicial_usuario (usuario_id)
);
