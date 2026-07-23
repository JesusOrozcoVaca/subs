-- Módulo Prácticas de Puja (entrenamiento aislado del proceso completo)
-- Ejecutar en producción tras backup.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `practicas_salas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(40) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `presupuesto_referencial` decimal(15,2) NOT NULL,
  `variacion_minima` decimal(5,2) NOT NULL DEFAULT 1.00,
  `duracion_minutos` int(11) NOT NULL DEFAULT 10,
  `zona_horaria` varchar(64) NOT NULL DEFAULT 'America/Guayaquil',
  `estado_sala` enum('borrador','activa','archivada') NOT NULL DEFAULT 'borrador',
  `bots_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `bots_count` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `bots_profile` enum('pasivo','equilibrado','agresivo') NOT NULL DEFAULT 'equilibrado',
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_practicas_salas_codigo` (`codigo`),
  KEY `idx_practicas_salas_estado` (`estado_sala`),
  KEY `idx_practicas_salas_created_by` (`created_by`),
  CONSTRAINT `fk_practicas_salas_usuario` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `practicas_rondas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sala_id` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `hora_inicio` datetime NOT NULL COMMENT 'UTC',
  `duracion_minutos` int(11) NOT NULL,
  `zona_horaria` varchar(64) NOT NULL DEFAULT 'America/Guayaquil',
  `estado` enum('programada','en_curso','finalizada','cancelada') NOT NULL DEFAULT 'programada',
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `ganador_usuario_id` int(11) DEFAULT NULL,
  `ganador_valor` decimal(15,2) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `bots_last_tick_ms` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_practicas_rondas_sala_numero` (`sala_id`,`numero`),
  KEY `idx_practicas_rondas_estado` (`estado`),
  KEY `idx_practicas_rondas_sala` (`sala_id`),
  KEY `idx_practicas_rondas_ganador` (`ganador_usuario_id`),
  CONSTRAINT `fk_practicas_rondas_sala` FOREIGN KEY (`sala_id`) REFERENCES `practicas_salas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_practicas_rondas_created_by` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_practicas_rondas_ganador` FOREIGN KEY (`ganador_usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `practicas_inscripciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ronda_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `oferta_inicial` decimal(15,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `joined_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_practicas_inscripciones_ronda_usuario` (`ronda_id`,`usuario_id`),
  KEY `idx_practicas_inscripciones_usuario` (`usuario_id`),
  CONSTRAINT `fk_practicas_inscripciones_ronda` FOREIGN KEY (`ronda_id`) REFERENCES `practicas_rondas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_practicas_inscripciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `practicas_pujas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ronda_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `fecha_puja_ms` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_practicas_pujas_ronda_valor` (`ronda_id`,`valor`),
  KEY `idx_practicas_pujas_ronda_usuario_ms` (`ronda_id`,`usuario_id`,`fecha_puja_ms`),
  CONSTRAINT `fk_practicas_pujas_ronda` FOREIGN KEY (`ronda_id`) REFERENCES `practicas_rondas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_practicas_pujas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
