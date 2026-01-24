-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-01-2026 a las 19:54:01
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_subastas_inversas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_puja`
--

CREATE TABLE `configuracion_puja` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `duracion_minutos` enum('5','10','15') NOT NULL,
  `hora_inicio` datetime NOT NULL,
  `zona_horaria` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `convalidaciones`
--

CREATE TABLE `convalidaciones` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `detalle_texto` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `convalidaciones`
--

INSERT INTO `convalidaciones` (`id`, `producto_id`, `usuario_id`, `detalle_texto`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'Prueba', '2026-01-24 06:33:59', '2026-01-24 06:33:59'),
(2, 1, 10, 'Aqui entrego mi convalidacion usuario 2', '2026-01-24 07:19:28', '2026-01-24 07:19:28'),
(3, 17, 31, 'Esta es una prueba nueva de convalidacion de errores', '2026-01-24 09:18:00', '2026-01-24 09:18:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `convalidacion_archivos`
--

CREATE TABLE `convalidacion_archivos` (
  `id` int(11) NOT NULL,
  `convalidacion_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_carga` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `convalidacion_archivos`
--

INSERT INTO `convalidacion_archivos` (`id`, `convalidacion_id`, `nombre_archivo`, `ruta_archivo`, `fecha_carga`) VALUES
(1, 1, 'PagoVisaHJEnero2026.pdf', 'uploads/Convalidacion_files/conv_6974ae27ba7129.74249542_1769254439.pdf', '2026-01-24 06:33:59'),
(2, 2, 'blank (1).pdf', 'uploads/Convalidacion_files/conv_6974b8d04c8a76.49237588_1769257168.pdf', '2026-01-24 07:19:28'),
(3, 3, 'blank (1).pdf', 'uploads/Convalidacion_files/conv_6974d498a26468.47036952_1769264280.pdf', '2026-01-24 09:18:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cpc`
--

CREATE TABLE `cpc` (
  `id` int(11) NOT NULL,
  `codigo` varchar(5) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cpc`
--

INSERT INTO `cpc` (`id`, `codigo`, `descripcion`) VALUES
(1, '00134', 'bienes'),
(6, '65432', 'cpc de prueba 2'),
(7, '78978', 'dfgdsfgsdg'),
(8, '74185', '777777777777'),
(17, '85269', 'cpc sept'),
(18, '97979', 'CPC 2025'),
(31, '33333', 'sdfsdfsdf'),
(33, '95162', 'Prueba de cpc');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_producto`
--

CREATE TABLE `documentos_producto` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_carga` timestamp NOT NULL DEFAULT current_timestamp(),
  `procesado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentos_producto`
--

INSERT INTO `documentos_producto` (`id`, `producto_id`, `usuario_id`, `nombre_archivo`, `ruta_archivo`, `fecha_carga`, `procesado`) VALUES
(9, 1, 3, 'Diploma_acreditativo-JESUS_VICENTE_OROZCO_VACA.pdf', 'uploads/offers/6915f19d69214_1763045789.pdf', '2025-11-13 14:56:29', 1),
(10, 15, 3, 'Diploma_acreditativo-JESUS_VICENTE_OROZCO_VACA.pdf', 'uploads/offers/6915f946eacdb_1763047750.pdf', '2025-11-13 15:29:10', 1),
(12, 10, 3, 'RCR1762980524677572.pdf', 'uploads/offers/69160606ba3d0_1763051014.pdf', '2025-11-13 16:23:34', 1),
(14, 11, 3, '0 - DECLARACION VAE.pdf', 'uploads/offers/691e15e8a6b77_1763579368.pdf', '2025-11-19 19:09:28', 1),
(15, 1, 10, 'blank (1).pdf', 'uploads/offers/6974b8948a91a_1769257108.pdf', '2026-01-24 12:18:28', 1),
(16, 1, 10, 'blank (1).pdf', 'uploads/offers/6974b8948f472_1769257108.pdf', '2026-01-24 12:18:28', 1),
(23, 17, 10, 'blank (1).pdf', 'uploads/offers/6974d01b88e8c_1769263131.pdf', '2026-01-24 13:58:51', 0),
(24, 17, 10, 'blank (1).pdf', 'uploads/offers/6974d01b9202d_1769263131.pdf', '2026-01-24 13:58:51', 0),
(28, 17, 3, 'blank (1).pdf', 'uploads/offers/6974d080d9f9f_1769263232.pdf', '2026-01-24 14:00:32', 1),
(35, 17, 31, 'blank (1).pdf', 'uploads/offers/6974d412489de_1769264146.pdf', '2026-01-24 14:15:46', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_producto`
--

CREATE TABLE `estados_producto` (
  `id` int(11) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `orden` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_producto`
--

INSERT INTO `estados_producto` (`id`, `codigo`, `descripcion`, `orden`, `activo`, `fecha_creacion`) VALUES
(1, 'pyr', 'Preguntas y Respuestas', 1, 1, '2025-10-23 18:18:33'),
(2, 'eof', 'Entrega de Ofertas', 2, 1, '2025-10-23 18:18:33'),
(3, 'conv', 'Convalidación de errores', 3, 1, '2025-10-23 18:18:33'),
(4, 'calif', 'Calificación', 4, 1, '2025-10-23 18:18:33'),
(5, 'ofini', 'Oferta Inicial', 5, 1, '2025-10-23 18:18:33'),
(6, 'puja', 'Puja', 6, 1, '2025-10-23 18:18:33'),
(7, 'por_adj', 'Por adjudicar', 7, 1, '2025-10-23 18:18:33'),
(8, 'adj_reg', 'Adjudicado – Registro de Contrato', 8, 1, '2025-10-23 18:18:33'),
(9, 'ejec', 'En ejecución', 9, 1, '2025-10-23 18:18:33'),
(10, 'recepc', 'En recepción', 10, 1, '2025-10-23 18:18:33'),
(11, 'fin', 'Finalizado', 11, 1, '2025-10-23 18:18:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas_calificaciones`
--

CREATE TABLE `ofertas_calificaciones` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `calificacion` enum('Cumple','No Cumple') NOT NULL,
  `comentario` varchar(300) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ofertas_calificaciones`
--

INSERT INTO `ofertas_calificaciones` (`id`, `producto_id`, `usuario_id`, `calificacion`, `comentario`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'Cumple', 'Cumple prueba de respuesta', '2026-01-24 13:22:12', '2026-01-24 13:22:12'),
(2, 1, 10, 'No Cumple', 'No cumple prueba de incumplimiento', '2026-01-24 13:22:15', '2026-01-24 13:22:15'),
(3, 17, 31, 'Cumple', 'Cumples brooooo Admin says', '2026-01-24 15:34:31', '2026-01-24 16:20:29'),
(7, 17, 3, 'Cumple', 'Cumples', '2026-01-24 18:37:57', '2026-01-24 18:37:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas_detalle`
--

CREATE TABLE `ofertas_detalle` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tiempo_entrega` varchar(100) NOT NULL,
  `plazo_oferta` varchar(100) NOT NULL,
  `oferta_inicial_user` decimal(15,2) NOT NULL DEFAULT 0.00,
  `descripcion` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ofertas_detalle`
--

INSERT INTO `ofertas_detalle` (`id`, `producto_id`, `usuario_id`, `tiempo_entrega`, `plazo_oferta`, `oferta_inicial_user`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 15, 3, '90', '230', 0.00, 'Se cumple con todo lo requerido.', '2025-11-13 17:04:38', '2025-11-13 17:04:38'),
(2, 10, 3, '60', '30', 0.00, 'Cumplo con todoooooo', '2025-11-13 17:23:49', '2025-11-13 17:23:49'),
(3, 1, 3, '120', '5', 0.00, 'OKOK', '2025-11-13 17:31:13', '2025-11-13 17:31:13'),
(4, 11, 3, '20', '20', 56000.00, 'Prueba de envio de monto de oferta inicial en formulario', '2025-11-19 20:11:38', '2025-11-19 20:11:38'),
(5, 1, 10, '120', '120', 15000.00, 'Se cumple con todo', '2026-01-24 13:18:50', '2026-01-24 13:18:50'),
(6, 17, 3, '120', '120', 20000.00, 'Se cumple con lo requerido', '2026-01-24 15:01:13', '2026-01-24 15:01:13'),
(7, 17, 31, '120', '120', 12221.99, 'Se cumple con todo', '2026-01-24 15:16:50', '2026-01-24 15:16:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas_iniciales`
--

CREATE TABLE `ofertas_iniciales` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `codigo` varchar(32) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ofertas_iniciales`
--

INSERT INTO `ofertas_iniciales` (`id`, `producto_id`, `usuario_id`, `codigo`, `created_at`, `updated_at`) VALUES
(1, 17, 31, '1qjqux74lq0eesmzcllz8su2xupuizkz', '2026-01-24 13:47:45', '2026-01-24 13:47:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participantes_producto`
--

CREATE TABLE `participantes_producto` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `estado` enum('Cumple','No Cumple') DEFAULT NULL,
  `oferta_inicial` decimal(10,2) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_respuestas`
--

CREATE TABLE `preguntas_respuestas` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `pregunta` text NOT NULL,
  `respuesta` text DEFAULT NULL,
  `fecha_pregunta` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_respuesta` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas_respuestas`
--

INSERT INTO `preguntas_respuestas` (`id`, `producto_id`, `usuario_id`, `pregunta`, `respuesta`, `fecha_pregunta`, `fecha_respuesta`) VALUES
(1, 1, 3, 'Pregunta de prueba', 'Resp13 MODsssss', '2025-10-23 20:29:23', '2025-11-12 19:14:03'),
(2, 1, 3, 'Test pregunta desde script', 'Resp moderador 1', '2025-10-23 20:42:53', '2025-11-12 19:14:03'),
(3, 1, 3, 'Pregunta de prueba', 'Prueba de pdf', '2025-10-23 20:56:02', '2025-11-12 19:14:03'),
(4, 1, 3, 'Test question from direct AJAX', 'Simonnnnn', '2025-10-23 21:18:43', '2025-11-12 19:14:03'),
(5, 1, 3, 'Test question from direct test', NULL, '2025-10-23 22:01:59', NULL),
(6, 1, 3, 'Test question from direct test', NULL, '2025-10-23 22:02:14', NULL),
(7, 1, 3, 'Test question from PYR-TEST', NULL, '2025-10-23 22:50:58', NULL),
(8, 1, 3, 'Hola Prueba nuevecita', NULL, '2025-10-23 22:51:05', NULL),
(9, 1, 3, 'Otra prueba massss', NULL, '2025-10-23 23:10:41', NULL),
(10, 13, 3, 'Pregunta de prueba', 'Prueba pdf', '2025-10-23 23:33:15', '2025-11-12 19:14:23'),
(11, 1, 3, 'NUEVA CON ESTILOS MIGRADOS', NULL, '2025-10-23 23:44:59', NULL),
(12, 1, 3, 'HOla', NULL, '2025-10-23 23:48:07', NULL),
(13, 15, 3, 'Preguntaaaaarrrrrrrr', 'Pruebita pdf', '2025-10-23 23:50:32', '2025-11-12 19:14:30'),
(14, 11, 3, 'Probando preguntasssss', 'Prueba de pdf', '2025-10-23 23:50:48', '2025-11-12 19:14:10'),
(15, 10, 3, 'Todos los procesos con preguntassssssss', 'Pregunta respondida y prueba de generar el pdf...', '2025-10-23 23:51:11', '2025-11-12 19:14:17'),
(16, 17, 10, 'Esta es una pregunta de prueba desde otro usuario partdos', 'Pregunta respondida Señor\nPrueba de edicion', '2026-01-24 13:52:20', '2026-01-24 13:52:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `entidad` varchar(100) NOT NULL,
  `objeto_proceso` text NOT NULL,
  `cpc_id` int(11) DEFAULT NULL,
  `codigo` varchar(50) NOT NULL,
  `tipo_compra` enum('SIE','MC','CCD','LIC','FI') NOT NULL,
  `presupuesto_referencial` decimal(10,2) NOT NULL,
  `tipo_contratacion` enum('Total','Parcial') DEFAULT 'Total',
  `forma_pago` enum('0','25','50','70') NOT NULL,
  `plazo_entrega` int(11) NOT NULL,
  `vigencia_oferta` int(11) NOT NULL,
  `funcionario_encargado` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `variacion_minima` decimal(5,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `entidad`, `objeto_proceso`, `cpc_id`, `codigo`, `tipo_compra`, `presupuesto_referencial`, `tipo_contratacion`, `forma_pago`, `plazo_entrega`, `vigencia_oferta`, `funcionario_encargado`, `descripcion`, `variacion_minima`, `fecha_creacion`, `estado_id`) VALUES
(1, 'Chavo del ocho jaaaadddd', 'producto de prueba numero UNO22', 18, 'SIE-CDO-2024-001', 'SIE', 20150.00, 'Total', '25', 365, 58, 'Jesus Vicente Orozco Vaca', 'Esta es la descripción222', 2.00, '2024-09-01 21:54:36', 4),
(10, 'Chavo del ocho', 'tfhysfdhgsdfhfdghfdghdfghfdghdfghdfghdfghdfgh', 6, 'SIE-CDO-2024-002', 'SIE', 7777770.00, 'Total', '0', 99, 99, 'Carolina Zúñiga', 'Prueba final', 3.00, '2024-09-02 14:04:12', 2),
(11, 'Chavo del ocho8888', '99999999999999999', 8, 'SIE-CDO-2024-003', 'SIE', 88888.00, 'Total', '0', 88, 88, 'Jesus Vicente Orozco Vaca', 'hgjdgfhjghj', 8.00, '2024-09-02 14:41:57', 2),
(13, 'Power Ranger', 'edicion octubre', 8, 'SIE-PR-2024-004', 'SIE', 44000.00, 'Total', '50', 90, 90, 'Jesus Vicente Orozco Vaca', 'pruebaaaaaa sept', 3.00, '2024-09-10 07:59:29', 1),
(15, 'jesusoctubre', 'octubres', 1, 'SIE-J-2025-005', 'SIE', 50000.00, 'Total', '50', 120, 120, 'Jesus Octubre', 'OKTOBER FEST', 1.50, '2025-10-14 17:21:53', 2),
(17, 'Hj consulting management', 'Prueba para paginacion', 1, 'SIE-HCM-2026-006', 'SIE', 12222.00, 'Total', '25', 120, 120, 'Jesus Vicente Orozco Vaca', 'asdfasdfasdfasdf', 2.50, '2026-01-24 13:43:39', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pujas`
--

CREATE TABLE `pujas` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `fecha_puja` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_puja_ms` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `correo_electronico` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contrasena` varchar(255) NOT NULL,
  `nivel_acceso` tinyint(4) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `cedula`, `nombre_completo`, `correo_electronico`, `telefono`, `contrasena`, `nivel_acceso`, `estado`, `fecha_creacion`) VALUES
(1, '12345678998', 'Admin Prueba888', 'admin@example.com', '0991234567', '$2a$12$FtNAVEtzIHWXP.mCs3gmGe4IR8..W3C9xVqnz6FkIFRk5fr2hodT.', 1, 'activo', '2024-09-01 20:39:41'),
(2, '23456789018', 'Moderador Prueba', 'moderador@example.com', '0992345678', '$2a$12$FtNAVEtzIHWXP.mCs3gmGe4IR8..W3C9xVqnz6FkIFRk5fr2hodT.', 2, 'activo', '2024-09-01 20:39:41'),
(3, '3456789012', 'Jesus Orozco V.', 'participante@example.com', '0993456789', '$2a$12$FtNAVEtzIHWXP.mCs3gmGe4IR8..W3C9xVqnz6FkIFRk5fr2hodT.', 3, 'activo', '2024-09-01 20:39:41'),
(10, '987654321852', 'participante dos dos', 'partdos@example.com', '852647391', '$2y$10$2UNv8N7DyScUvzDQ6kOEv.ILw89PbeybPfWoSwjty2PK8PEDJd6f.', 3, 'activo', '2026-01-24 12:16:27'),
(31, '654654654', 'Jesus Tres', 'partres@example.com', '6546546546', '$2y$10$zSmsLkLvQfBfSmbxWls3KO.0H2rx5SNz3AamiJ88VsiWPHjXooW.K', 3, 'activo', '2026-01-24 14:06:55'),
(32, '0927031856', 'Jesus Orozco Vaca', 'jesusorozcovaca@gmail.com', '0613462332', '$2y$10$RXTt7LVBHr65H166d2k93uMh42vM1WFpH.goaTocDN997W4KuUALW', 3, 'activo', '2026-01-24 17:12:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_cpc`
--

CREATE TABLE `usuarios_cpc` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `cpc_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_cpc`
--

INSERT INTO `usuarios_cpc` (`id`, `usuario_id`, `cpc_id`) VALUES
(118, 3, 7),
(119, 3, 17),
(120, 3, 31),
(121, 3, 18),
(122, 3, 6),
(123, 3, 8),
(124, 3, 1),
(125, 10, 1),
(126, 10, 6),
(127, 10, 7),
(128, 10, 8),
(129, 10, 17),
(130, 10, 18),
(131, 10, 31),
(132, 31, 1),
(133, 31, 6),
(134, 31, 7),
(135, 31, 8),
(136, 31, 17),
(137, 31, 18),
(138, 31, 31),
(139, 31, 33),
(144, 32, 1),
(145, 32, 6);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuracion_puja`
--
ALTER TABLE `configuracion_puja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `convalidaciones`
--
ALTER TABLE `convalidaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_convalidacion` (`producto_id`,`usuario_id`);

--
-- Indices de la tabla `convalidacion_archivos`
--
ALTER TABLE `convalidacion_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_convalidacion_archivos` (`convalidacion_id`);

--
-- Indices de la tabla `cpc`
--
ALTER TABLE `cpc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `documentos_producto`
--
ALTER TABLE `documentos_producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `estados_producto`
--
ALTER TABLE `estados_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `ofertas_calificaciones`
--
ALTER TABLE `ofertas_calificaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_oferta_calificacion` (`producto_id`,`usuario_id`),
  ADD KEY `idx_oferta_calificacion_producto` (`producto_id`),
  ADD KEY `idx_oferta_calificacion_usuario` (`usuario_id`);

--
-- Indices de la tabla `ofertas_detalle`
--
ALTER TABLE `ofertas_detalle`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ofertas_detalle_producto_usuario` (`producto_id`,`usuario_id`),
  ADD KEY `fk_ofertas_detalle_usuario` (`usuario_id`);

--
-- Indices de la tabla `ofertas_iniciales`
--
ALTER TABLE `ofertas_iniciales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_oferta_inicial` (`producto_id`,`usuario_id`),
  ADD KEY `idx_oferta_inicial_producto` (`producto_id`),
  ADD KEY `idx_oferta_inicial_usuario` (`usuario_id`);

--
-- Indices de la tabla `participantes_producto`
--
ALTER TABLE `participantes_producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `preguntas_respuestas`
--
ALTER TABLE `preguntas_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `cpc_id` (`cpc_id`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indices de la tabla `pujas`
--
ALTER TABLE `pujas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `correo_electronico` (`correo_electronico`);

--
-- Indices de la tabla `usuarios_cpc`
--
ALTER TABLE `usuarios_cpc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `cpc_id` (`cpc_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `configuracion_puja`
--
ALTER TABLE `configuracion_puja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `convalidaciones`
--
ALTER TABLE `convalidaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `convalidacion_archivos`
--
ALTER TABLE `convalidacion_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cpc`
--
ALTER TABLE `cpc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `documentos_producto`
--
ALTER TABLE `documentos_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `estados_producto`
--
ALTER TABLE `estados_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `ofertas_calificaciones`
--
ALTER TABLE `ofertas_calificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ofertas_detalle`
--
ALTER TABLE `ofertas_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ofertas_iniciales`
--
ALTER TABLE `ofertas_iniciales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `participantes_producto`
--
ALTER TABLE `participantes_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas_respuestas`
--
ALTER TABLE `preguntas_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `pujas`
--
ALTER TABLE `pujas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `usuarios_cpc`
--
ALTER TABLE `usuarios_cpc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `configuracion_puja`
--
ALTER TABLE `configuracion_puja`
  ADD CONSTRAINT `configuracion_puja_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `convalidacion_archivos`
--
ALTER TABLE `convalidacion_archivos`
  ADD CONSTRAINT `fk_convalidacion_archivos_convalidacion` FOREIGN KEY (`convalidacion_id`) REFERENCES `convalidaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `documentos_producto`
--
ALTER TABLE `documentos_producto`
  ADD CONSTRAINT `documentos_producto_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `documentos_producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `ofertas_detalle`
--
ALTER TABLE `ofertas_detalle`
  ADD CONSTRAINT `fk_ofertas_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ofertas_detalle_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `participantes_producto`
--
ALTER TABLE `participantes_producto`
  ADD CONSTRAINT `participantes_producto_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `participantes_producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `preguntas_respuestas`
--
ALTER TABLE `preguntas_respuestas`
  ADD CONSTRAINT `preguntas_respuestas_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `preguntas_respuestas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`cpc_id`) REFERENCES `cpc` (`id`),
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `estados_producto` (`id`);

--
-- Filtros para la tabla `pujas`
--
ALTER TABLE `pujas`
  ADD CONSTRAINT `pujas_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `pujas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `usuarios_cpc`
--
ALTER TABLE `usuarios_cpc`
  ADD CONSTRAINT `usuarios_cpc_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `usuarios_cpc_ibfk_2` FOREIGN KEY (`cpc_id`) REFERENCES `cpc` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
