CREATE TABLE IF NOT EXISTS convalidaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    detalle_texto TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uniq_convalidacion (producto_id, usuario_id)
);

CREATE TABLE IF NOT EXISTS convalidacion_archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    convalidacion_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    fecha_carga DATETIME NOT NULL,
    INDEX idx_convalidacion_archivos (convalidacion_id),
    CONSTRAINT fk_convalidacion_archivos_convalidacion
        FOREIGN KEY (convalidacion_id) REFERENCES convalidaciones(id)
        ON DELETE CASCADE
);

