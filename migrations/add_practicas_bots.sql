-- Bots rivales para Prácticas de Puja
-- Ejecutar en producción tras backup.
-- Si alguna columna ya existe, comentar ese ALTER y continuar.

SET NAMES utf8mb4;

ALTER TABLE `practicas_salas`
  ADD COLUMN `bots_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `estado_sala`,
  ADD COLUMN `bots_count` tinyint(3) unsigned NOT NULL DEFAULT 2 AFTER `bots_enabled`,
  ADD COLUMN `bots_profile` enum('pasivo','equilibrado','agresivo') NOT NULL DEFAULT 'equilibrado' AFTER `bots_count`;

ALTER TABLE `practicas_rondas`
  ADD COLUMN `bots_last_tick_ms` bigint(20) DEFAULT NULL AFTER `updated_at`;

ALTER TABLE `usuarios`
  ADD COLUMN `es_bot` tinyint(1) NOT NULL DEFAULT 0 AFTER `nivel_acceso`;

INSERT INTO `usuarios`
  (`cedula`, `nombre_completo`, `correo_electronico`, `telefono`, `contrasena`, `nivel_acceso`, `es_bot`, `estado`)
SELECT 'BOT0000001', 'Rival Simulado 01', 'bot01.practica@local.invalid', '0000000001',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, 'activo'
WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `cedula` = 'BOT0000001' OR `correo_electronico` = 'bot01.practica@local.invalid');

INSERT INTO `usuarios`
  (`cedula`, `nombre_completo`, `correo_electronico`, `telefono`, `contrasena`, `nivel_acceso`, `es_bot`, `estado`)
SELECT 'BOT0000002', 'Rival Simulado 02', 'bot02.practica@local.invalid', '0000000002',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, 'activo'
WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `cedula` = 'BOT0000002' OR `correo_electronico` = 'bot02.practica@local.invalid');

INSERT INTO `usuarios`
  (`cedula`, `nombre_completo`, `correo_electronico`, `telefono`, `contrasena`, `nivel_acceso`, `es_bot`, `estado`)
SELECT 'BOT0000003', 'Rival Simulado 03', 'bot03.practica@local.invalid', '0000000003',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, 'activo'
WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `cedula` = 'BOT0000003' OR `correo_electronico` = 'bot03.practica@local.invalid');

INSERT INTO `usuarios`
  (`cedula`, `nombre_completo`, `correo_electronico`, `telefono`, `contrasena`, `nivel_acceso`, `es_bot`, `estado`)
SELECT 'BOT0000004', 'Rival Simulado 04', 'bot04.practica@local.invalid', '0000000004',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, 'activo'
WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `cedula` = 'BOT0000004' OR `correo_electronico` = 'bot04.practica@local.invalid');

INSERT INTO `usuarios`
  (`cedula`, `nombre_completo`, `correo_electronico`, `telefono`, `contrasena`, `nivel_acceso`, `es_bot`, `estado`)
SELECT 'BOT0000005', 'Rival Simulado 05', 'bot05.practica@local.invalid', '0000000005',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, 'activo'
WHERE NOT EXISTS (SELECT 1 FROM `usuarios` WHERE `cedula` = 'BOT0000005' OR `correo_electronico` = 'bot05.practica@local.invalid');
