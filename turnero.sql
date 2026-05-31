-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 31-05-2026 a las 16:01:44
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `turnero`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloqueos_medicos`
--

DROP TABLE IF EXISTS `bloqueos_medicos`;
CREATE TABLE IF NOT EXISTS `bloqueos_medicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medico_id` int NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medico_id` (`medico_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

DROP TABLE IF EXISTS `especialidades`;
CREATE TABLE IF NOT EXISTS `especialidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `estado` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id`, `nombre`, `descripcion`, `estado`, `created_at`) VALUES
(1, 'Clínica Médicas', NULL, 1, '2026-04-28 11:55:43'),
(2, 'Pediatría', NULL, 1, '2026-04-28 11:55:43'),
(3, 'Traumatología', NULL, 1, '2026-04-28 11:55:43'),
(4, 'Ginecología', NULL, 1, '2026-04-28 11:55:43'),
(7, 'Odontologías', NULL, 1, '2026-04-28 12:13:43'),
(8, 'Obstetricia', NULL, 1, '2026-04-28 14:38:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_medicos`
--

DROP TABLE IF EXISTS `horarios_medicos`;
CREATE TABLE IF NOT EXISTS `horarios_medicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medico_id` int NOT NULL,
  `dia_semana` tinyint NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `duracion_turno` int DEFAULT '20',
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `medico_id` (`medico_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `horarios_medicos`
--

INSERT INTO `horarios_medicos` (`id`, `medico_id`, `dia_semana`, `hora_inicio`, `hora_fin`, `duracion_turno`, `activo`) VALUES
(23, 3, 1, '16:30:00', '18:30:00', 20, 1),
(24, 3, 3, '13:30:00', '16:30:00', 20, 1),
(25, 5, 1, '13:30:00', '17:30:00', 20, 1),
(34, 6, 1, '09:30:00', '12:30:00', 20, 1),
(35, 6, 2, '09:30:00', '12:30:00', 20, 1),
(36, 6, 3, '09:30:00', '12:30:00', 20, 1),
(37, 6, 4, '09:30:00', '12:30:00', 20, 1),
(38, 4, 1, '16:30:00', '18:30:00', 20, 1),
(39, 4, 2, '12:30:00', '18:30:00', 20, 1),
(40, 4, 3, '15:30:00', '18:30:00', 20, 1),
(41, 4, 4, '15:30:00', '18:30:00', 20, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicos`
--

DROP TABLE IF EXISTS `medicos`;
CREATE TABLE IF NOT EXISTS `medicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `consultorio` varchar(50) DEFAULT NULL,
  `dni` varchar(20) NOT NULL,
  `matricula` varchar(50) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `especialidad_id` int NOT NULL,
  `valor_consulta` decimal(10,2) DEFAULT '0.00',
  `estado` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricula` (`matricula`),
  UNIQUE KEY `dni` (`dni`),
  UNIQUE KEY `matricula_2` (`matricula`),
  KEY `fk_medico_especialidad` (`especialidad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `medicos`
--

INSERT INTO `medicos` (`id`, `nombre`, `apellido`, `consultorio`, `dni`, `matricula`, `telefono`, `email`, `password`, `especialidad_id`, `valor_consulta`, `estado`, `created_at`) VALUES
(3, 'Antonio', 'Fernandez', '2', '28880533', '3245', '498499', 'xs@gmail.com', '$2y$10$B00nFDMK5EfSwTkckjcKh.DZf/TTzLT2FmJ/vkruVUGFxqWDLjhOa', 7, 15000.00, 1, '2026-04-28 13:07:34'),
(4, 'Jorge', 'Wiener', '2', '28880532', 'MP: 3246', '0280467896', 'jor@gmail.com', '$2y$10$B00nFDMK5EfSwTkckjcKh.DZf/TTzLT2FmJ/vkruVUGFxqWDLjhOa', 4, 15000.00, 1, '2026-04-28 14:42:17'),
(5, 'Dimittri', 'Stefanov', '3', '25789632', 'MP: 12365', '28046532178', 'dimi@gmail.com', '$2y$10$tSxzbYSE0QFzuTr1iH3mCOjvMC3BZO8wFAtzuDt9T0EwimF8UE3Ci', 1, 15000.00, 1, '2026-04-30 15:24:00'),
(6, 'Julian', 'Wiener', '4', '56234899', 'MP: 12345', '280484177289', 'ju@gmail.com', '$2y$10$OB1TZHXDpkPHCSIaU8Wkueiw8vu.7rxwvfR3vwXixTDh/98DLrLMS', 2, 15000.00, 1, '2026-04-30 15:37:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medico_obras_sociales`
--

DROP TABLE IF EXISTS `medico_obras_sociales`;
CREATE TABLE IF NOT EXISTS `medico_obras_sociales` (
  `medico_id` int NOT NULL,
  `obra_social_id` int NOT NULL,
  PRIMARY KEY (`medico_id`,`obra_social_id`),
  KEY `obra_social_id` (`obra_social_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `medico_obras_sociales`
--

INSERT INTO `medico_obras_sociales` (`medico_id`, `obra_social_id`) VALUES
(3, 1),
(3, 2),
(3, 4),
(4, 1),
(4, 3),
(5, 4),
(6, 2),
(6, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obras_sociales`
--

DROP TABLE IF EXISTS `obras_sociales`;
CREATE TABLE IF NOT EXISTS `obras_sociales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `coseguro_estandar` decimal(10,2) DEFAULT '0.00',
  `estado` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `obras_sociales`
--

INSERT INTO `obras_sociales` (`id`, `nombre`, `coseguro_estandar`, `estado`, `created_at`) VALUES
(1, 'SEROS', 4500.00, 1, '2026-04-28 15:53:31'),
(2, 'OSDE', 8500.00, 1, '2026-04-28 15:53:43'),
(3, 'JERARQUICOS', 7000.00, 1, '2026-04-28 15:53:56'),
(4, 'OSECAC', 5800.00, 1, '2026-04-28 16:35:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

DROP TABLE IF EXISTS `pacientes`;
CREATE TABLE IF NOT EXISTS `pacientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dni` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text,
  `obrasocial` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `obra_social_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`),
  KEY `fk_paciente_os` (`obra_social_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id`, `dni`, `nombre`, `apellido`, `fecha_nacimiento`, `telefono`, `email`, `direccion`, `obrasocial`, `estado`, `created_at`, `obra_social_id`) VALUES
(3, '56432669', 'Victoria', 'Wiener', '2017-09-18', '02804671799', 'ju@gmail.com', NULL, '', 1, '2026-04-28 15:03:07', 1),
(4, '28880532', 'Jorge', 'Wiener', '1981-06-20', '0280467896', 'jor@gmail.com', NULL, NULL, 1, '2026-04-28 17:19:35', 1),
(5, '30864442', 'Noelia Elisabet', 'Santana', '1984-01-16', '02804671799', 'jor@gmail.com', NULL, NULL, 1, '2026-04-28 17:29:43', 2),
(6, '28880533', 'Florencia', 'Rogel', NULL, '896532', NULL, NULL, NULL, 1, '2026-04-29 11:10:52', NULL),
(7, '56432668', 'Corina Alzamendia', 'Alzamendia', NULL, '896532', NULL, NULL, NULL, 1, '2026-04-29 13:55:36', 3),
(8, '56432667', 'Jorge', 'Wiener Fernández', NULL, '02804671799', NULL, NULL, NULL, 1, '2026-04-29 14:16:51', 3),
(9, '56432664', 'Irina', 'Lopez', NULL, '8520742963', NULL, NULL, NULL, 1, '2026-04-29 14:27:58', 4),
(10, '56432666', 'Desideria Itatí', 'Fernández', NULL, '3782456789', NULL, NULL, NULL, 1, '2026-04-29 15:53:20', NULL),
(11, '58497962', 'Jose ', 'Iriarte', NULL, '852496', NULL, NULL, NULL, 1, '2026-04-29 16:15:39', NULL),
(12, '54321897', 'Olga', 'Vazquez', NULL, '9634963', NULL, NULL, NULL, 1, '2026-04-29 17:14:08', 4),
(13, '56478923', 'Lucas', 'Fernandez', NULL, '89496', NULL, NULL, NULL, 1, '2026-04-29 17:17:49', 1),
(14, '4589765', 'Hernán', 'Lopez', '2026-05-04', '464169', 'her@gmail.com', NULL, NULL, 1, '2026-04-29 17:18:49', NULL),
(18, '56789321', 'Roberto', 'Sandoval', NULL, '2804567896', NULL, NULL, NULL, 1, '2026-05-04 12:02:54', 3),
(19, '28880534', 'Jose Luis', 'Saravia', NULL, '2804659822', NULL, NULL, NULL, 1, '2026-05-04 14:29:03', 2),
(20, '5892316', 'Alfredo', 'Fernanse', NULL, '852741963', NULL, NULL, NULL, 1, '2026-05-04 16:15:12', NULL),
(21, '30864443', 'Ruperto', 'Fernandez', NULL, '2846650618', NULL, NULL, NULL, 1, '2026-05-05 14:22:22', 1),
(22, '53562318', 'Hernan', 'Lopez', NULL, '25879363', NULL, NULL, NULL, 1, '2026-05-05 14:27:37', NULL),
(23, '53432669', 'Juan', ' Perez', NULL, '2804567896', NULL, NULL, NULL, 1, '2026-05-06 17:00:52', 4),
(24, '58564236', 'Olga', 'Subiria', NULL, '2804652358', NULL, NULL, NULL, 1, '2026-05-19 12:40:48', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

DROP TABLE IF EXISTS `turnos`;
CREATE TABLE IF NOT EXISTS `turnos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medico_id` int NOT NULL,
  `paciente_id` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `obra_social_id` int DEFAULT NULL,
  `estado` enum('Pendiente','Confirmado','Atendido','Cancelado','Ausente','Espera','Llamando','Atendiendo') DEFAULT 'Pendiente',
  `tipo_pago` varchar(50) DEFAULT 'Particular',
  `monto_cobrado` decimal(10,2) DEFAULT '0.00',
  `nro_operacion` varchar(100) DEFAULT NULL,
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medico` (`medico_id`),
  KEY `idx_paciente` (`paciente_id`),
  KEY `fk_turno_os` (`obra_social_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id`, `medico_id`, `paciente_id`, `fecha`, `hora`, `obra_social_id`, `estado`, `tipo_pago`, `monto_cobrado`, `nro_operacion`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 3, 3, '2026-04-29', '13:30:00', 1, 'Espera', 'Obra Social', 4500.00, NULL, '', '2026-04-29 16:38:10', '2026-05-04 14:55:33'),
(2, 4, 10, '2026-04-29', '15:30:00', NULL, 'Espera', 'Particular', 10000.00, NULL, 'Trae estudios', '2026-04-29 16:53:33', '2026-05-04 14:55:33'),
(3, 4, 7, '2026-04-29', '17:50:00', 3, 'Espera', 'Obra Social', 7000.00, NULL, '', '2026-04-29 16:54:38', '2026-05-04 14:55:33'),
(4, 4, 8, '2026-04-29', '15:50:00', 3, 'Espera', 'Particular', 7000.00, NULL, '', '2026-04-29 16:56:52', '2026-05-04 14:55:33'),
(5, 4, 7, '2026-04-30', '15:30:00', 3, 'Espera', 'Particular', 10000.00, NULL, '', '2026-04-29 17:07:16', '2026-05-04 14:55:33'),
(6, 3, 3, '2026-04-29', '15:50:00', 1, 'Espera', 'Obra Social', 4500.00, NULL, '', '2026-04-29 17:10:22', '2026-05-04 14:55:33'),
(7, 3, 12, '2026-04-29', '16:10:00', 4, 'Espera', 'Particular', 15000.00, NULL, '', '2026-04-29 17:14:08', '2026-05-04 14:55:33'),
(8, 4, 13, '2026-04-29', '17:10:00', 1, 'Espera', 'Particular', 10000.00, NULL, '', '2026-04-29 17:17:49', '2026-05-04 14:55:33'),
(9, 4, 14, '2026-04-29', '16:10:00', NULL, 'Espera', 'Particular', 10000.00, NULL, '', '2026-04-29 17:18:49', '2026-05-04 14:55:33'),
(10, 4, 3, '2026-04-30', '15:50:00', 1, 'Espera', 'Particular', 10000.00, NULL, '', '2026-04-29 17:25:03', '2026-05-04 14:55:33'),
(11, 4, 3, '2026-04-30', '16:10:00', 1, 'Espera', 'OBRA_SOCIAL - Debito_OS', 4500.00, '1111', '', '2026-04-29 18:56:41', '2026-05-04 14:55:33'),
(12, 4, 3, '2026-04-29', '18:10:00', 1, 'Espera', 'PARTICULAR - Efectivo', 10000.00, '', '', '2026-04-29 18:57:51', '2026-05-04 14:55:33'),
(13, 3, 3, '2026-04-29', '15:10:00', 1, 'Espera', 'PARTICULAR - Billetera_Virtual', 15000.00, '1112', '', '2026-04-29 18:58:41', '2026-05-04 14:55:33'),
(15, 4, 3, '2026-05-04', '16:30:00', 1, 'Atendido', 'OBRA_SOCIAL - Efectivo', 4500.00, '', '', '2026-05-04 17:58:02', '2026-05-04 19:22:27'),
(16, 4, 3, '2026-05-04', '16:50:00', 1, 'Atendido', 'OBRA_SOCIAL - Transferencia', 4500.00, '8498', '', '2026-05-04 19:24:09', '2026-05-04 19:35:15'),
(17, 3, 3, '2026-05-04', '16:30:00', 1, 'Atendido', 'OBRA_SOCIAL - Efectivo', 4500.00, '', '', '2026-05-04 19:26:00', '2026-05-04 19:27:02'),
(18, 4, 18, '1969-12-31', '16:30:00', 3, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-04 12:02:54', '2026-05-04 12:02:54'),
(19, 4, 3, '2026-05-04', '16:30:00', 1, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-04 12:08:52', '2026-05-04 12:08:52'),
(20, 3, 19, '2026-05-04', '16:50:00', 2, 'Atendido', 'OBRA_SOCIAL - Efectivo', 8500.00, '', '', '2026-05-04 16:56:03', '2026-05-04 17:00:16'),
(21, 5, 4, '2026-05-04', '13:30:00', 4, 'Espera', 'OBRA_SOCIAL - Efectivo', 5800.00, '', '', '2026-05-04 17:01:33', '2026-05-04 17:01:33'),
(22, 3, 19, '2026-05-04', '17:10:00', 1, 'Atendido', 'OBRA_SOCIAL - Efectivo', 4500.00, '', '', '2026-05-04 17:04:14', '2026-05-04 17:20:05'),
(23, 3, 3, '2026-05-06', '13:30:00', 1, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-04 14:38:17', '2026-05-04 14:38:17'),
(24, 3, 3, '2026-05-04', '17:30:00', NULL, 'Atendido', 'PARTICULAR - Efectivo', 15000.00, '', '', '2026-05-04 17:16:10', '2026-05-04 17:21:34'),
(25, 5, 20, '2026-05-04', '13:50:00', NULL, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-04 16:15:12', '2026-05-04 16:15:12'),
(26, 4, 3, '2026-05-04', '17:10:00', 3, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-04 16:21:00', '2026-05-04 16:21:00'),
(27, 3, 3, '2026-05-04', '17:50:00', 2, 'Atendido', 'OBRA_SOCIAL - Efectivo', 8500.00, '', '', '2026-05-04 17:19:10', '2026-05-04 17:37:32'),
(28, 3, 3, '2026-05-04', '18:10:00', 1, 'Atendido', 'OBRA_SOCIAL - Efectivo', 4500.00, '', '', '2026-05-04 17:38:19', '2026-05-04 17:38:56'),
(29, 4, 20, '2026-05-05', '15:30:00', NULL, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 11:10:30', '2026-05-05 11:10:30'),
(30, 6, 6, '2026-05-05', '09:30:00', 3, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 11:41:38', '2026-05-05 11:41:38'),
(31, 6, 6, '2026-05-06', '09:30:00', NULL, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 11:45:01', '2026-05-05 11:45:01'),
(32, 3, 6, '2026-05-06', '13:50:00', NULL, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 13:55:25', '2026-05-05 13:55:25'),
(33, 3, 3, '2026-05-06', '14:10:00', NULL, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 13:56:58', '2026-05-05 13:56:58'),
(34, 3, 6, '2026-05-06', '14:30:00', NULL, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 14:21:09', '2026-05-05 14:21:09'),
(35, 3, 21, '2026-05-06', '14:50:00', 1, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 14:22:22', '2026-05-05 14:22:22'),
(36, 3, 6, '2026-05-06', '15:10:00', 1, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 14:23:28', '2026-05-05 14:23:28'),
(37, 3, 6, '2026-05-06', '15:30:00', 4, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 14:24:50', '2026-05-05 14:24:50'),
(38, 3, 6, '2026-05-06', '15:50:00', NULL, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 14:26:31', '2026-05-05 14:26:31'),
(39, 3, 22, '2026-05-06', '16:10:00', NULL, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 14:27:37', '2026-05-05 14:27:37'),
(40, 5, 6, '2026-05-11', '13:30:00', NULL, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 14:29:31', '2026-05-05 14:29:31'),
(41, 5, 6, '2026-05-11', '13:50:00', 4, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 14:30:15', '2026-05-05 14:30:15'),
(42, 5, 6, '2026-05-11', '14:10:00', NULL, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 14:30:37', '2026-05-05 14:30:37'),
(43, 5, 6, '2026-05-11', '14:30:00', 4, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 14:41:12', '2026-05-05 14:41:12'),
(44, 4, 6, '2026-05-05', '13:30:00', 1, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 16:21:32', '2026-05-05 16:21:32'),
(45, 4, 6, '2026-05-06', '15:30:00', 1, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-05 16:32:00', '2026-05-05 16:32:00'),
(46, 3, 23, '2026-05-11', '16:30:00', 4, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-06 17:00:52', '2026-05-06 17:00:52'),
(47, 6, 3, '2026-05-07', '10:10:00', 2, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-06 17:01:38', '2026-05-06 17:01:38'),
(48, 5, 10, '2026-05-18', '16:30:00', 4, 'Pendiente', 'Particular', 0.00, NULL, NULL, '2026-05-18 17:05:43', '2026-05-18 17:05:43'),
(49, 4, 3, '2026-05-19', '13:30:00', 1, 'Espera', 'OBRA_SOCIAL - Efectivo', 4500.00, '', '', '2026-05-19 12:24:59', '2026-05-19 12:24:59'),
(50, 4, 24, '2026-05-19', '12:30:00', 1, 'Atendido', 'PARTICULAR - Transferencia', 15000.00, '123', '', '2026-05-19 12:42:14', '2026-05-19 12:57:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `rol` enum('admin','recepcion') DEFAULT 'admin',
  `estado` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `rol`, `estado`, `created_at`) VALUES
(1, 'admin', '$2y$10$v/gK5jKBsn3OitwBiV3EnOpcG7zzZzQ.Tvgl2Nwoa6DltwNpUL4r2', 'Administrador', 'admin', 1, '2026-04-28 13:37:34'),
(2, 'victorias', '$2y$10$v/gK5jKBsn3OitwBiV3EnOpcG7zzZzQ.Tvgl2Nwoa6DltwNpUL4r2', 'Victoria', 'recepcion', 0, '2026-05-05 17:21:36'),
(3, 'juanjosé', '$2y$10$/e6u1dDtJTC6QVJQr1U09.EQ0TywveHI8mLyR.jfoiPnau4UcAOyi', 'Juan José', 'recepcion', 1, '2026-05-06 15:31:31'),
(4, 'enrique', '$2y$10$bwFrTcdtsFoFHklhaAwpFugLqvmAnYl3YUahJ79SfbLPStpeEbbMy', 'Enrique Martinez', 'recepcion', 1, '2026-05-06 15:46:45'),
(5, 'Perez', '$2y$10$5IHrkKiCNst.wbIHgeQu8eXsjmfD1JDpEUXmOgcaq3CI6sTzKdoTm', 'Adrian José', 'admin', 1, '2026-05-20 12:36:50'),
(6, 'hernandez', '$2y$10$w3E8RivjASt6wFG.ormLM.7MkrmbYcmHgUKs/stcXmIij62o86SJ6', 'Adrian Eduardo', 'admin', 1, '2026-05-20 12:47:15');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bloqueos_medicos`
--
ALTER TABLE `bloqueos_medicos`
  ADD CONSTRAINT `bloqueos_medicos_ibfk_1` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `horarios_medicos`
--
ALTER TABLE `horarios_medicos`
  ADD CONSTRAINT `horarios_medicos_ibfk_1` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD CONSTRAINT `fk_medico_especialidad` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD CONSTRAINT `fk_turno_medico` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_turno_os` FOREIGN KEY (`obra_social_id`) REFERENCES `obras_sociales` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_turno_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
