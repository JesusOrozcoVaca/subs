-- Migración: Agregar columna oferta_inicial_user a la tabla ofertas_detalle
-- Fecha: 2025-11-XX
-- Descripción: Agrega el campo oferta_inicial_user para almacenar el valor de la oferta inicial registrada por el usuario

ALTER TABLE ofertas_detalle 
ADD COLUMN oferta_inicial_user DECIMAL(15,2) NOT NULL DEFAULT 0.00 
AFTER plazo_oferta;

-- Comentario en la columna
ALTER TABLE ofertas_detalle 
MODIFY COLUMN oferta_inicial_user DECIMAL(15,2) NOT NULL 
COMMENT 'Valor de la oferta inicial registrada por el usuario';

