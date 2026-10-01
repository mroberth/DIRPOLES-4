/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: dirpoles_security
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL AUTO_INCREMENT,
  `id_empleado` int(11) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `accion` enum('Registro','Lectura','Actualización','Eliminación','Inicio de sesión','Cierre de sesión','Respaldo') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_bitacora`),
  KEY `id_empleado` (`id_empleado`),
  KEY `idx_bitacora_emp_fecha` (`id_empleado`,`fecha`),
  CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES
(1,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-15 16:57:45'),
(2,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-15 16:58:03'),
(3,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-16 00:07:27'),
(4,1,'Empleados','Registro','Creó al empleado \"Jhan Guevara\"','2026-09-16 00:31:03'),
(5,1,'Beneficiarios','Registro','Creó al beneficiario \"Jesus Matos\"','2026-09-16 00:57:53'),
(6,1,'Empleados','Registro','Creó al empleado \"Iris Alvarez\"','2026-09-16 00:58:50'),
(7,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-16 01:13:12'),
(8,1,'Horarios','Registro','Registró un horario para un psicólogo.','2026-09-16 01:22:40'),
(9,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-16 03:20:45'),
(10,1,'Horarios','Registro','Registró un horario para un psicólogo.','2026-09-16 03:21:39'),
(11,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-16 03:22:51'),
(12,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-16 13:34:40'),
(13,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-16 14:09:44'),
(14,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-17 13:34:22'),
(15,1,'Citas','Registro','Registró una cita para un beneficiario.','2026-09-17 13:35:30'),
(16,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-17 13:36:00'),
(17,3,'Login','Inicio de sesión','El empleado Iris ha iniciado sesión.','2026-09-17 13:36:09'),
(18,3,'Citas','Actualización','Actualizó el estado de una cita.','2026-09-17 13:39:47'),
(19,3,'Login','Cierre de sesión','El empleado Iris ha cerrado sesión.','2026-09-17 13:40:01'),
(20,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-17 13:40:10'),
(21,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-17 15:45:58'),
(22,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-17 16:15:49'),
(23,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-17 16:15:58'),
(24,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-17 16:27:53'),
(25,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-17 16:28:01'),
(26,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-17 16:28:09'),
(27,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-17 16:28:15'),
(28,1,'Psicologia','Registro','Registró una consulta psicológica de tipo Diagnóstico.','2026-09-17 16:56:08'),
(29,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-17 17:08:32'),
(30,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-20 17:25:07'),
(31,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-20 18:11:13'),
(32,1,'Psicologia','Registro','Registró una consulta psicológica de tipo Retiro temporal.','2026-09-20 18:54:09'),
(33,1,'Beneficiarios','Actualización','Actualizó al beneficiario \"Jesus Matos\"','2026-09-20 19:08:12'),
(34,1,'Beneficiarios','Actualización','Actualizó al beneficiario \"Jesus Matoss\"','2026-09-20 19:08:33'),
(35,1,'Beneficiarios','Actualización','Actualizó al beneficiario \"Jesus Matos\"','2026-09-20 19:08:48'),
(36,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-20 19:14:23'),
(37,1,'Beneficiarios','Actualización','Actualizó al beneficiario \"Jesus Matos\"','2026-09-20 19:14:38'),
(38,1,'Beneficiarios','Actualización','Actualizó al beneficiario \"Jesus Matos\"','2026-09-20 19:14:38'),
(39,1,'Psicologia','Actualización','Actualizó una consulta psicológica.','2026-09-20 19:33:30'),
(40,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-20 19:58:49'),
(41,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-24 19:45:23'),
(42,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-24 19:45:33'),
(43,3,'Login','Inicio de sesión','El empleado Iris ha iniciado sesión.','2026-09-24 19:45:43'),
(44,3,'Login','Cierre de sesión','El empleado Iris ha cerrado sesión.','2026-09-24 19:46:04'),
(45,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-24 19:51:44'),
(46,1,'Psicologia','Eliminación','Eliminó una consulta psicológica.','2026-09-24 19:56:55'),
(47,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-24 21:00:16'),
(48,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-24 22:58:16'),
(49,1,'Medicina','Registro','Registró una consulta médica.','2026-09-24 23:28:29'),
(50,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-25 14:31:02'),
(51,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-25 14:33:04'),
(52,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-25 14:33:16'),
(53,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-27 18:01:05'),
(54,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-27 18:18:32'),
(55,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-27 19:20:39'),
(56,1,'Orientacion','Registro','Registró una orientación.','2026-09-27 19:21:20'),
(57,1,'Discapacidad','Registro','Registró un diagnóstico de discapacidad.','2026-09-27 19:51:47'),
(58,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-27 20:14:20'),
(59,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-27 20:14:28'),
(60,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-28 01:28:12'),
(61,1,'Trabajador Social','Registro','Registró una beca de trabajo social.','2026-09-28 01:36:02'),
(62,1,'Trabajador Social','Registro','Registró una exoneración de trabajo social.','2026-09-28 02:00:25'),
(63,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-28 02:54:38'),
(64,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-28 03:48:18'),
(65,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-28 12:47:20'),
(66,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-28 13:17:05'),
(67,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-28 14:51:27'),
(68,1,'Trabajador Social','Registro','Generó el estudio socioeconómico de Jesus Matos.','2026-09-28 14:55:48'),
(69,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-28 19:00:41'),
(70,1,'Trabajador Social','Registro','Generó la constancia de atención de la beca de Jesus Matos.','2026-09-28 19:02:41'),
(71,1,'Trabajador Social','Registro','Generó la referencia al área Psicologia de la beca de Jesus Matos.','2026-09-28 19:03:23'),
(72,1,'Trabajador Social','Registro','Generó la constancia de atención de la exoneración de Jesus Matos.','2026-09-28 19:03:44'),
(73,1,'Trabajador Social','Registro','Generó la referencia al área Medicina de la exoneración de Jesus Matos.','2026-09-28 19:03:53'),
(74,1,'Psicologia','Registro','Generó la constancia de atención de Jesus Matos.','2026-09-28 19:04:07'),
(75,1,'Medicina','Registro','Generó el recipe médico de Jesus Matos.','2026-09-28 19:26:49'),
(76,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-28 19:27:10'),
(77,1,'Medicina','Registro','Generó la constancia de atención de Jesus Matos.','2026-09-28 19:27:33'),
(78,1,'Medicina','Registro','Generó el recipe médico de Jesus Matos.','2026-09-28 19:27:37'),
(79,1,'Medicina','Registro','Generó el recipe médico de Jesus Matos.','2026-09-28 19:28:06'),
(80,1,'Medicina','Registro','Generó el recipe médico de Jesus Matos.','2026-09-28 19:31:20'),
(81,1,'Medicina','Registro','Generó el recipe médico de Jesus Matos.','2026-09-28 19:31:23'),
(82,1,'Medicina','Registro','Generó el recipe médico de Jesus Matos.','2026-09-28 19:32:09'),
(83,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-28 19:35:55'),
(84,3,'Login','Inicio de sesión','El empleado Iris ha iniciado sesión.','2026-09-28 19:36:04'),
(85,3,'Psicologia','Registro','Registró una consulta psicológica de tipo Diagnóstico.','2026-09-28 19:36:33'),
(86,3,'Psicologia','Registro','Generó la constancia de atención de Jesus Matos.','2026-09-28 19:36:41'),
(87,3,'Psicologia','Registro','Generó la referencia al área Psicologia de Jesus Matos.','2026-09-28 19:36:53'),
(88,3,'Login','Cierre de sesión','El empleado Iris ha cerrado sesión.','2026-09-28 19:37:01'),
(89,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-29 02:18:46'),
(90,1,'Inventario Medico','Registro','Registró el insumo \"Acetaminofén 500MG\" en el inventario médico.','2026-09-29 02:19:46'),
(91,1,'Inventario Medico','Registro','Registró la entrada del insumo Acetaminofén 500MG (+10) en el inventario médico.','2026-09-29 02:33:33'),
(92,1,'Inventario Medico','Registro','Registró salida (Pérdida) del insumo Acetaminofén 500MG (-1) en el inventario médico.','2026-09-29 02:44:42'),
(93,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-29 02:48:12'),
(94,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-29 16:01:28'),
(95,1,'Referencias','Registro','Creó la referencia #1 del beneficiario \"Jesus Matos\" hacia Jhan Guevara.','2026-09-29 16:02:34'),
(96,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-29 16:03:11'),
(97,2,'Login','Inicio de sesión','El empleado Jhan ha iniciado sesión.','2026-09-29 16:03:20'),
(98,2,'Referencias','Actualización','Aceptó la referencia #1.','2026-09-29 16:03:36'),
(99,2,'Login','Cierre de sesión','El empleado Jhan ha cerrado sesión.','2026-09-29 16:03:46'),
(100,3,'Login','Inicio de sesión','El empleado Iris ha iniciado sesión.','2026-09-29 16:03:55'),
(101,3,'Login','Cierre de sesión','El empleado Iris ha cerrado sesión.','2026-09-29 16:04:16'),
(102,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-29 16:04:37'),
(103,1,'Medicina','Registro','Registró una consulta médica.','2026-09-29 16:18:53'),
(104,1,'Medicina','Registro','Generó el recipe médico de Jesus Matos.','2026-09-29 16:19:05'),
(105,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-29 16:55:31'),
(106,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-29 18:10:39'),
(107,1,'Mobiliario','Registro','Registró mobiliario: Escritorio de madera x10 en Discapacidad.','2026-09-29 18:13:02'),
(108,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-29 19:37:35'),
(109,1,'Jornadas','Registro','Creó la jornada \"Jornada medica comunitaria\" (aforo 2).','2026-09-29 19:38:49'),
(110,1,'Jornadas','Registro','Registró a \"Roberth Matos\" (CI: V-28281433) en la jornada \"Jornada medica comunitaria\".','2026-09-29 19:39:53'),
(111,1,'Jornadas','Registro','Registró a \"Jesus Matos\" (CI: V-30995937) en la jornada \"Jornada medica comunitaria\".','2026-09-29 19:41:28'),
(112,1,'Jornadas','Registro','Registró un diagnóstico para \"Jesus Matos\" en la jornada \"Jornada medica comunitaria\": No tiene nada. Insumos usados: Acetaminofén 500MG ×1.','2026-09-29 19:42:07'),
(113,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-29 19:56:24'),
(114,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-29 19:56:31'),
(115,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-29 20:58:29'),
(116,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-29 22:36:51'),
(117,1,'Transporte','Registro','Creó el vehículo placa \'ABC1234\' (Autobús)','2026-09-29 22:37:50'),
(118,1,'Transporte','Registro','Creó el proveedor \'Repuestos Barquisimeto\' (V-282814331)','2026-09-29 22:38:59'),
(119,1,'Transporte','Registro','Registró el repuesto \'Filtro de aceite Yutong\'','2026-09-29 22:47:12'),
(120,1,'Transporte','Actualización','Entrada de 5 unidades del repuesto \'Filtro de aceite Yutong\'','2026-09-29 22:47:34'),
(121,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-29 23:02:20'),
(122,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-30 02:19:52'),
(123,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-30 03:44:40'),
(124,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-30 03:45:29'),
(125,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-30 12:47:42'),
(126,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-30 14:21:51'),
(127,1,'Transporte','Registro','Registró mantenimiento Preventivo al vehículo placa \'ABC1234\'','2026-09-30 14:22:39'),
(128,1,'Transporte','Registro','Creó la ruta \'Ruta 1 - UPTAEB\'','2026-09-30 14:36:53'),
(129,1,'Transporte','Registro','Creó el vehículo placa \'ABC1233\' (Automóvil)','2026-09-30 14:40:56'),
(130,1,'Empleados','Registro','Creó al empleado \"Juan Melendez\"','2026-09-30 14:57:36'),
(131,1,'Transporte','Registro','Asignó vehículo ABC1233 a ruta \'Ruta 1 - UPTAEB\'','2026-09-30 14:57:57'),
(132,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-30 17:52:15'),
(133,1,'Reportes','Lectura','Generó el reporte estadístico general.','2026-09-30 17:57:18'),
(134,1,'Reportes','Lectura','Generó el reporte estadístico general.','2026-09-30 17:57:21'),
(135,1,'Reportes','Lectura','Generó el reporte estadístico general.','2026-09-30 17:59:21'),
(136,1,'Reportes','Lectura','Generó el reporte de Referencias.','2026-09-30 18:00:11'),
(137,1,'Reportes','Lectura','Generó el reporte de Referencias.','2026-09-30 18:00:13'),
(138,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 18:00:47'),
(139,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 18:00:47'),
(140,1,'Reportes','Lectura','Generó el reporte de Mobiliario y Equipos.','2026-09-30 18:01:26'),
(141,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 18:01:46'),
(142,1,'Reportes','Lectura','Generó el reporte de Discapacidad.','2026-09-30 18:02:01'),
(143,1,'Reportes','Lectura','Generó el reporte de Trabajo Social.','2026-09-30 18:02:15'),
(144,1,'Reportes','Lectura','Generó el reporte de Trabajo Social.','2026-09-30 18:02:16'),
(145,1,'Reportes','Lectura','Generó el reporte de Orientación.','2026-09-30 18:02:31'),
(146,1,'Reportes','Lectura','Generó el reporte de Medicina.','2026-09-30 18:02:46'),
(147,1,'Reportes','Lectura','Generó el reporte estadístico general.','2026-09-30 18:02:56'),
(148,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 18:14:06'),
(149,1,'Reportes','Lectura','Generó el reporte estadístico general.','2026-09-30 18:14:06'),
(150,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 18:14:09'),
(151,1,'Reportes','Lectura','Generó el reporte estadístico general.','2026-09-30 18:14:09'),
(152,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 18:14:17'),
(153,1,'Reportes','Lectura','Generó el reporte de Medicina.','2026-09-30 18:14:17'),
(154,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 18:14:23'),
(155,1,'Reportes','Lectura','Generó el reporte de Referencias.','2026-09-30 18:14:23'),
(156,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-30 19:05:44'),
(157,1,'Reportes','Lectura','Generó el reporte estadístico general.','2026-09-30 19:05:58'),
(158,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:05:58'),
(159,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:06:37'),
(160,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:07:11'),
(161,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:08:18'),
(162,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 19:08:25'),
(163,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:08:25'),
(164,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:10:17'),
(165,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:12:02'),
(166,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:12:36'),
(167,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 19:14:26'),
(168,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:14:26'),
(169,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 19:14:46'),
(170,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:14:46'),
(171,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 19:15:17'),
(172,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:15:17'),
(173,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 19:15:19'),
(174,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:15:19'),
(175,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 19:16:52'),
(176,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:16:52'),
(177,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:17:11'),
(178,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 19:17:11'),
(179,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-30 19:17:27'),
(180,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-30 19:17:33'),
(181,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-30 19:17:56'),
(182,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-30 19:18:06'),
(183,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-30 19:18:13'),
(184,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-30 19:18:20'),
(185,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:18:30'),
(186,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:18:31'),
(187,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:19:15'),
(188,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:19:35'),
(189,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:19:58'),
(190,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:20:14'),
(191,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:20:21'),
(192,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:20:26'),
(193,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 19:20:33'),
(194,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:20:33'),
(195,1,'Reportes','Lectura','Generó el reporte de Medicina.','2026-09-30 19:22:07'),
(196,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:22:07'),
(197,1,'Reportes','Lectura','Generó el reporte de Psicología.','2026-09-30 19:22:13'),
(198,1,'Reportes','Lectura','Generó el reporte de Psicología.','2026-09-30 19:22:20'),
(199,1,'Reportes','Lectura','Generó el reporte de Orientación.','2026-09-30 19:22:28'),
(200,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:22:28'),
(201,1,'Reportes','Lectura','Generó el reporte de Trabajo Social.','2026-09-30 19:22:33'),
(202,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:22:33'),
(203,1,'Reportes','Lectura','Generó el reporte de Discapacidad.','2026-09-30 19:22:38'),
(204,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:22:38'),
(205,1,'Reportes','Lectura','Generó el reporte de Referencias.','2026-09-30 19:22:42'),
(206,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:22:42'),
(207,1,'Reportes','Lectura','Generó el reporte de Jornadas Médicas.','2026-09-30 19:22:47'),
(208,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:22:47'),
(209,1,'Reportes','Lectura','Generó el reporte de Mobiliario y Equipos.','2026-09-30 19:22:52'),
(210,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:22:52'),
(211,1,'Reportes','Lectura','Generó el reporte de Mobiliario y Equipos.','2026-09-30 19:22:56'),
(212,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:22:56'),
(213,1,'Reportes','Lectura','Generó el reporte de Mobiliario y Equipos.','2026-09-30 19:22:56'),
(214,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:22:56'),
(215,1,'Reportes','Lectura','Generó el reporte de Mobiliario y Equipos.','2026-09-30 19:23:01'),
(216,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:23:01'),
(217,1,'Reportes','Lectura','Generó el reporte de Mobiliario y Equipos.','2026-09-30 19:23:06'),
(218,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:23:06'),
(219,1,'Reportes','Lectura','Generó el reporte de Mobiliario y Equipos.','2026-09-30 19:23:08'),
(220,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:23:08'),
(221,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 19:23:22'),
(222,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:23:22'),
(223,1,'Reportes','Lectura','Generó el reporte de Transporte.','2026-09-30 19:23:26'),
(224,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:23:26'),
(225,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-30 19:32:30'),
(226,3,'Login','Inicio de sesión','El empleado Iris ha iniciado sesión.','2026-09-30 19:32:41'),
(227,3,'Calendario','Registro','Creó un evento personal (id 1).','2026-09-30 19:32:55'),
(228,3,'Login','Cierre de sesión','El empleado Iris ha cerrado sesión.','2026-09-30 19:33:23'),
(229,2,'Login','Inicio de sesión','El empleado Jhan ha iniciado sesión.','2026-09-30 19:33:34'),
(230,2,'Login','Cierre de sesión','El empleado Jhan ha cerrado sesión.','2026-09-30 19:33:41'),
(231,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-09-30 19:41:07'),
(232,1,'Reportes','Lectura','Generó el reporte estadístico general.','2026-09-30 19:41:18'),
(233,1,'Reportes','Lectura','Consultó las estadísticas de un reporte.','2026-09-30 19:41:18'),
(234,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-09-30 19:42:19'),
(235,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-10-01 12:37:55'),
(236,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-10-01 13:06:35'),
(237,3,'Login','Inicio de sesión','El empleado Iris ha iniciado sesión.','2026-10-01 13:06:47'),
(238,3,'Login','Cierre de sesión','El empleado Iris ha cerrado sesión.','2026-10-01 13:07:04'),
(239,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-10-01 13:07:13'),
(240,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-10-01 13:07:32'),
(241,3,'Login','Inicio de sesión','El empleado Iris ha iniciado sesión.','2026-10-01 13:07:44'),
(242,3,'Referencias','Registro','Creó la referencia #2 del beneficiario \"Jesus Matos\" hacia Roberth Matos.','2026-10-01 13:08:16'),
(243,3,'Login','Cierre de sesión','El empleado Iris ha cerrado sesión.','2026-10-01 13:08:22'),
(244,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-10-01 13:08:30'),
(245,1,'Referencias','Actualización','Rechazó la referencia #2.','2026-10-01 13:10:45'),
(246,1,'Login','Cierre de sesión','El empleado Roberth ha cerrado sesión.','2026-10-01 13:11:14'),
(247,3,'Login','Inicio de sesión','El empleado Iris ha iniciado sesión.','2026-10-01 13:12:02'),
(248,1,'Login','Inicio de sesión','El empleado Roberth ha iniciado sesión.','2026-10-01 15:43:21');
/*!40000 ALTER TABLE `bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleado`
--

DROP TABLE IF EXISTS `empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleado` (
  `id_empleado` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `tipo_cedula` varchar(1) DEFAULT NULL,
  `cedula` varchar(10) DEFAULT NULL,
  `correo` varchar(50) DEFAULT NULL,
  `telefono` varchar(12) DEFAULT NULL,
  `id_tipo_empleado` int(1) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `direccion` mediumtext DEFAULT NULL,
  `clave` varchar(65) DEFAULT NULL,
  `estatus` tinyint(1) DEFAULT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_empleado`),
  UNIQUE KEY `idx_emp_cedula` (`tipo_cedula`,`cedula`),
  UNIQUE KEY `idx_emp_correo` (`correo`),
  KEY `id_tipo_empleado` (`id_tipo_empleado`),
  KEY `idx_emp_tipo` (`id_tipo_empleado`,`estatus`),
  CONSTRAINT `empleado_ibfk_1` FOREIGN KEY (`id_tipo_empleado`) REFERENCES `tipo_empleado` (`id_tipo_emp`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleado`
--

LOCK TABLES `empleado` WRITE;
/*!40000 ALTER TABLE `empleado` DISABLE KEYS */;
INSERT INTO `empleado` VALUES
(1,'Roberth','Matos','V','28281433','admin@gmail.com','04129298008',6,'2002-04-05','Calle 53 y 54 con carrera 14','$2y$10$I7kpYQ.Cju5KWqNkz.VdQ.Pg3PXIKmG./kuCiqMv78c516KPUd7Lq',1,'2025-04-17'),
(2,'Jhan','Guevara','V','13652666','jhanguevara@gmail.com','04129991212',4,'1994-01-12','Av. Fuerzas armadas con 54','$2y$10$TJUNBZT3ortgGw8PovyFe.i3W2eIa4kwTjaevG7six7e0BFXcTyeS',1,'2026-09-15'),
(3,'Iris','Alvarez','V','12023052','irisalva19@gmail.com','04245289178',1,'1974-09-17','Carrera 13 con calle 54','$2y$10$cvFRuU76DCQ.wk5H6H7Ql.s8VWADXKLp.g78VeMQGyCglULyUyJdu',1,'2026-09-15'),
(4,'Juan','Melendez','V','12023051','juanmelendez@gmail.com','04145004988',8,'1994-01-30','Calle 1 de barrio unión','$2y$10$QrCkeLcPHKk0N9sfA8sHUO.HYBpIg1rumUd73uFEygMP92c/i8mn6',1,'2026-09-30');
/*!40000 ALTER TABLE `empleado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_intentos`
--

DROP TABLE IF EXISTS `login_intentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_intentos` (
  `correo` varchar(100) NOT NULL,
  `intentos_fallidos` int(11) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`correo`),
  KEY `idx_login_intentos_fecha` (`ultimo_intento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_intentos`
--

LOCK TABLES `login_intentos` WRITE;
/*!40000 ALTER TABLE `login_intentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_intentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modulo`
--

DROP TABLE IF EXISTS `modulo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modulo` (
  `id_modulo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  PRIMARY KEY (`id_modulo`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modulo`
--

LOCK TABLES `modulo` WRITE;
/*!40000 ALTER TABLE `modulo` DISABLE KEYS */;
INSERT INTO `modulo` VALUES
(1,'Empleados','Gestionar Empleados'),
(2,'Beneficiarios','Gestionar Beneficiarios'),
(3,'Citas','Gestionar Citas'),
(4,'Psicologia','Diagnosticos de Psicologia'),
(5,'Medicina','Diagnosticos de Medicina'),
(6,'Orientacion','Diagnosticos de Orientacion'),
(7,'Trabajador Social','Diagnosticos de Trabajador Social'),
(8,'Discapacidad','Diagnosticos de Discapacidad'),
(9,'Inventario Medico','Gestionar Inventario Medico'),
(10,'Referencias','Gestionar Referencias'),
(11,'Jornadas','Gestionar Jornadas'),
(12,'Mobiliario','Gestionar Mobiliario'),
(13,'Transporte','Gestionar Transporte'),
(14,'Configuracion','Gestionar Configuracion'),
(15,'Reportes','Gestionar Reportes'),
(16,'Bitacora','Gestionar Bitacora'),
(17,'Permisos','Gestionar Permisos de Usuario'),
(18,'Horarios','Gestionar Horarios para empleados de Psicología'),
(19,'Notificaciones','Bandeja de notificaciones del usuario'),
(20,'Perfil','Gestionar perfil de Empleado');
/*!40000 ALTER TABLE `modulo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id_notificaciones` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notificaciones`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES
(1,'Nueva cita asignada','citas/consultar','info','2026-09-17 13:35:30'),
(2,'Nueva Referencia','referencias/consultar','referencia','2026-09-29 16:02:34'),
(3,'Referencia aceptada','referencias/consultar','referencia','2026-09-29 16:03:36'),
(4,'Nueva Referencia','referencias/consultar','referencia','2026-10-01 13:08:16'),
(5,'Referencia rechazada','referencias/consultar','referencia','2026-10-01 13:10:45');
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones_empleados`
--

DROP TABLE IF EXISTS `notificaciones_empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones_empleados` (
  `id_notificaciones_empleados` int(11) NOT NULL AUTO_INCREMENT,
  `id_notificaciones` int(11) NOT NULL,
  `id_emisor` int(11) NOT NULL,
  `id_receptor` int(11) NOT NULL,
  `leido` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_notificaciones_empleados`),
  KEY `id_notificaciones` (`id_notificaciones`),
  KEY `id_emisor` (`id_emisor`),
  KEY `id_receptor` (`id_receptor`),
  KEY `idx_notif_emp_receptor` (`id_receptor`,`leido`),
  CONSTRAINT `notificaciones_empleados_ibfk_1` FOREIGN KEY (`id_notificaciones`) REFERENCES `notificaciones` (`id_notificaciones`),
  CONSTRAINT `notificaciones_empleados_ibfk_2` FOREIGN KEY (`id_emisor`) REFERENCES `empleado` (`id_empleado`),
  CONSTRAINT `notificaciones_empleados_ibfk_3` FOREIGN KEY (`id_receptor`) REFERENCES `empleado` (`id_empleado`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones_empleados`
--

LOCK TABLES `notificaciones_empleados` WRITE;
/*!40000 ALTER TABLE `notificaciones_empleados` DISABLE KEYS */;
INSERT INTO `notificaciones_empleados` VALUES
(1,1,1,3,1),
(2,2,1,2,1),
(3,3,2,3,1),
(4,4,3,1,1),
(5,5,1,3,0);
/*!40000 ALTER TABLE `notificaciones_empleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permiso`
--

DROP TABLE IF EXISTS `permiso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permiso` (
  `id_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(20) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id_permiso`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permiso`
--

LOCK TABLES `permiso` WRITE;
/*!40000 ALTER TABLE `permiso` DISABLE KEYS */;
INSERT INTO `permiso` VALUES
(1,'Crear','Crear registros'),
(2,'Leer','Leer registros'),
(3,'Editar','Editar registros'),
(4,'Eliminar','Eliminar registros');
/*!40000 ALTER TABLE `permiso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_limits` (
  `ip_address` varchar(45) NOT NULL,
  `endpoint` varchar(100) NOT NULL,
  `tokens_actuales` decimal(10,4) NOT NULL,
  `ultima_peticion` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ip_address`,`endpoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_limits`
--

LOCK TABLES `rate_limits` WRITE;
/*!40000 ALTER TABLE `rate_limits` DISABLE KEYS */;
INSERT INTO `rate_limits` VALUES
('127.0.0.1','api/beneficiarios/listar',29.0000,1789491481),
('127.0.0.1','api/beneficiarios/pnfs',29.0000,1789491481),
('127.0.0.1','api/beneficiarios/stats',29.0000,1789491481),
('127.0.0.1','api/bitacora/filtros',29.0000,1789491471),
('127.0.0.1','api/bitacora/listar',29.0000,1789491471),
('127.0.0.1','api/bitacora/stats',29.0000,1789491471),
('127.0.0.1','api/calendario/eventos',29.0000,1789491468),
('127.0.0.1','api/dashboard/stats',29.0000,1789491468),
('127.0.0.1','api/empleados/listar',29.0000,1789491476),
('127.0.0.1','api/empleados/stats',29.0000,1789491475),
('127.0.0.1','api/empleados/tipos',29.0000,1789491476),
('127.0.0.1','api/notificaciones/listar',29.0000,1789491482),
('127.0.0.1','beneficiarios/consultar',29.0000,1789491480),
('127.0.0.1','bitacora/consultar',29.0000,1789491470),
('127.0.0.1','empleados/consultar',29.0000,1789491475),
('127.0.0.1','iniciar_sesion',4.0000,1789491465),
('127.0.0.1','inicio',29.0000,1789491467),
('127.0.0.1','login',29.0000,1789491483),
('127.0.0.1','logout',29.0000,1789491483),
('127.0.0.1','sse/notificaciones',29.0000,1789491481),
('::1','api/beneficiarios/actualizar',13.0000,1789931678),
('::1','api/beneficiarios/crear',14.0000,1789520273),
('::1','api/beneficiarios/listar',29.0000,1790283650),
('::1','api/beneficiarios/obtener/{id}',29.0000,1789931688),
('::1','api/beneficiarios/pnfs',29.0000,1790649921),
('::1','api/beneficiarios/stats',29.0000,1790649921),
('::1','api/beneficiarios/validar_cedula',79.0000,1790285494),
('::1','api/beneficiarios/validar_correo',79.0000,1790282925),
('::1','api/beneficiarios/validar_telefono',79.0000,1790282916),
('::1','api/bitacora/filtros',29.0000,1790860253),
('::1','api/bitacora/listar',29.0000,1790860265),
('::1','api/bitacora/stats',29.0000,1790860253),
('::1','api/calendario/eventos',29.0000,1790869404),
('::1','api/calendario/guardar',14.0000,1790796775),
('::1','api/citas/actualizar_estado',14.0000,1789652387),
('::1','api/citas/beneficiarios',29.0000,1790283634),
('::1','api/citas/crear',14.0000,1789652130),
('::1','api/citas/disponibilidad',14.0000,1789652122),
('::1','api/citas/estados',29.0000,1789652392),
('::1','api/citas/horario',27.0000,1790279152),
('::1','api/citas/listar',29.0000,1790346924),
('::1','api/citas/psicologos',29.0000,1790283634),
('::1','api/citas/stats',29.0000,1790346924),
('::1','api/dashboard/stats',29.0000,1790869404),
('::1','api/discapacidad/catalogos',29.0000,1790649932),
('::1','api/discapacidad/crear',14.0000,1790538707),
('::1','api/discapacidad/listar',29.0000,1790538713),
('::1','api/discapacidad/stats',29.0000,1790649932),
('::1','api/empleados/crear',14.0000,1790780256),
('::1','api/empleados/listar',29.0000,1790697783),
('::1','api/empleados/obtener/{id}',29.0000,1790346837),
('::1','api/empleados/stats',29.0000,1790780256),
('::1','api/empleados/tipos',29.0000,1790780213),
('::1','api/empleados/validar_cedula',79.0000,1790780256),
('::1','api/empleados/validar_correo',79.0000,1790780256),
('::1','api/empleados/validar_telefono',79.0000,1790780256),
('::1','api/horarios/crear',14.0000,1789652086),
('::1','api/horarios/listar',29.0000,1789528903),
('::1','api/horarios/psicologos',29.0000,1789659964),
('::1','api/horarios/stats',29.0000,1789659964),
('::1','api/inventario/crear',14.0000,1790648386),
('::1','api/inventario/entrada',14.0000,1790649213),
('::1','api/inventario/listar',29.0000,1790698766),
('::1','api/inventario/movimientos',29.0000,1790698769),
('::1','api/inventario/obtener/{id}',29.0000,1790648421),
('::1','api/inventario/presentaciones',29.0000,1790698766),
('::1','api/inventario/salida',14.0000,1790649882),
('::1','api/inventario/stats',29.0000,1790698766),
('::1','api/inventario/validar_insumo',79.0000,1790648386),
('::1','api/jornadas/actualizar',14.0000,1790710950),
('::1','api/jornadas/agregar_asistente',14.0000,1790710888),
('::1','api/jornadas/agregar_diagnostico',14.0000,1790710927),
('::1','api/jornadas/asistentes/{id}',29.0000,1790711699),
('::1','api/jornadas/buscar_persona',29.0000,1790710879),
('::1','api/jornadas/catalogos',29.0000,1790721956),
('::1','api/jornadas/crear',14.0000,1790710729),
('::1','api/jornadas/diagnosticos/{id}',29.0000,1790710927),
('::1','api/jornadas/insumos_disponibles',29.0000,1790710927),
('::1','api/jornadas/listar',29.0000,1790710987),
('::1','api/jornadas/obtener/{id}',29.0000,1790711699),
('::1','api/jornadas/stats',29.0000,1790721956),
('::1','api/medicina/catalogos',29.0000,1790698697),
('::1','api/medicina/crear',14.0000,1790698733),
('::1','api/medicina/listar',29.0000,1790698738),
('::1','api/medicina/stats',28.0000,1790698739),
('::1','api/mobiliario/catalogos',29.0000,1790706675),
('::1','api/mobiliario/crear_mobiliario',14.0000,1790705582),
('::1','api/mobiliario/items_disponibles',29.0000,1790706675),
('::1','api/mobiliario/listar_equipos',29.0000,1790705466),
('::1','api/mobiliario/listar_fichas',29.0000,1790705467),
('::1','api/mobiliario/listar_mobiliario',29.0000,1790706518),
('::1','api/mobiliario/obtener/mobiliario/{id}',29.0000,1790705710),
('::1','api/mobiliario/stats',29.0000,1790706675),
('::1','api/notificaciones',29.0000,1790796479),
('::1','api/notificaciones/listar',28.5000,1790869405),
('::1','api/notificaciones/marcar_leida',14.0000,1790860141),
('::1','api/orientacion/catalogos',29.0000,1790649934),
('::1','api/orientacion/crear',14.0000,1790536880),
('::1','api/orientacion/listar',29.0000,1790537849),
('::1','api/orientacion/stats',29.0000,1790649934),
('::1','api/perfil/obtener',29.0000,1790609445),
('::1','api/perfil/validar_correo',79.0000,1789518998),
('::1','api/perfil/validar_telefono',79.0000,1789519013),
('::1','api/permisos/matriz',29.0000,1790797300),
('::1','api/permisos/stats',29.0000,1790797300),
('::1','api/psicologia/actualizar',14.0000,1789932810),
('::1','api/psicologia/beneficiarios',29.0000,1789929198),
('::1','api/psicologia/catalogos',29.0000,1790649927),
('::1','api/psicologia/crear',14.0000,1790624193),
('::1','api/psicologia/eliminar',14.0000,1790279815),
('::1','api/psicologia/listar',29.0000,1790624197),
('::1','api/psicologia/patologias',29.0000,1789929198),
('::1','api/psicologia/stats',29.0000,1790649927),
('::1','api/referencias/aceptar',14.0000,1790697816),
('::1','api/referencias/beneficiarios',29.0000,1790860073),
('::1','api/referencias/crear',14.0000,1790860096),
('::1','api/referencias/empleados',28.0000,1790860086),
('::1','api/referencias/listar',29.0000,1790860245),
('::1','api/referencias/obtener/{id}',29.0000,1790860219),
('::1','api/referencias/rechazar',14.0000,1790860245),
('::1','api/referencias/servicios',29.0000,1790860073),
('::1','api/referencias/stats',29.0000,1790860245),
('::1','api/reportes/catalogos',29.0000,1790797277),
('::1','api/reportes/discapacidad',29.0000,1790796158),
('::1','api/reportes/general',29.0000,1790797278),
('::1','api/reportes/jornadas',29.0000,1790796167),
('::1','api/reportes/medicina',29.0000,1790796127),
('::1','api/reportes/mobiliario',29.0000,1790796188),
('::1','api/reportes/orientacion',29.0000,1790796148),
('::1','api/reportes/psicologia',29.0000,1790796140),
('::1','api/reportes/referencias',29.0000,1790796162),
('::1','api/reportes/stats',29.0000,1790797278),
('::1','api/reportes/trabajo-social',29.0000,1790796153),
('::1','api/reportes/transporte',29.0000,1790796205),
('::1','api/trabajo-social/becas/crear',14.0000,1790559362),
('::1','api/trabajo-social/catalogos',29.0000,1790649931),
('::1','api/trabajo-social/estudio/generar',14.0000,1790607348),
('::1','api/trabajo-social/exoneraciones/crear',14.0000,1790560825),
('::1','api/trabajo-social/exoneraciones/pendientes',29.0000,1790609360),
('::1','api/trabajo-social/listar',28.0000,1790622218),
('::1','api/trabajo-social/stats',29.0000,1790649931),
('::1','api/transporte/asignaciones/crear',14.0000,1790780277),
('::1','api/transporte/asignaciones/listar',29.0000,1790780281),
('::1','api/transporte/asignaciones/opciones',29.0000,1790780262),
('::1','api/transporte/mantenimientos/crear',14.0000,1790778159),
('::1','api/transporte/mantenimientos/listar',29.0000,1790780281),
('::1','api/transporte/mantenimientos/obtener/{id}',29.0000,1790778532),
('::1','api/transporte/proveedores/crear',14.0000,1790721539),
('::1','api/transporte/proveedores/listar',29.0000,1790780281),
('::1','api/transporte/proveedores/validar_correo',79.0000,1790721991),
('::1','api/transporte/proveedores/validar_documento',79.0000,1790721539),
('::1','api/transporte/proveedores/validar_telefono',79.0000,1790721539),
('::1','api/transporte/repuestos/crear',14.0000,1790722032),
('::1','api/transporte/repuestos/entrada',14.0000,1790722054),
('::1','api/transporte/repuestos/historial',29.0000,1790778525),
('::1','api/transporte/repuestos/listar',29.0000,1790780281),
('::1','api/transporte/repuestos/obtener/{id}',29.0000,1790722059),
('::1','api/transporte/rutas/crear',14.0000,1790779013),
('::1','api/transporte/rutas/listar',29.0000,1790780281),
('::1','api/transporte/stats',29.0000,1790780281),
('::1','api/transporte/vehiculos/crear',14.0000,1790779256),
('::1','api/transporte/vehiculos/listar',29.0000,1790780281),
('::1','api/transporte/vehiculos/obtener/{id}',29.0000,1790721496),
('::1','api/transporte/vehiculos/validar_placa',79.0000,1790779256),
('::1','asignaciones_calendario_json',29.0000,1790711929),
('::1','asignaciones_rutas_data_json',29.0000,1790711946),
('::1','beneficiarios/consultar',29.0000,1790283650),
('::1','beneficiarios/crear',29.0000,1790649921),
('::1','beneficiarios_activos_data_json',29.0000,1790540075),
('::1','bitacora/consultar',29.0000,1790860253),
('::1','citas/consultar',29.0000,1790346924),
('::1','citas/crear',29.0000,1790283634),
('::1','citas_calendario_json',29.0000,1790795889),
('::1','consultar_empleados',29.0000,1789661412),
('::1','diagnostico_discapacidad',29.0000,1790540073),
('::1','diagnostico_psicologia',29.0000,1789661762),
('::1','diagnostico_trabajo_social',29.0000,1790540074),
('::1','discapacidad/consultar',29.0000,1790538713),
('::1','discapacidad/crear',29.0000,1790649932),
('::1','dist/js/modulos/discapacidad/crear.js',29.0000,1790537855),
('::1','dist/js/modulos/discapacidad/stats.js',29.0000,1790537855),
('::1','dist/js/modulos/discapacidad/tour.js',29.0000,1790537855),
('::1','dist/js/modulos/discapacidad/validaciones.js',29.0000,1790537855),
('::1','dist/js/modulos/orientacion/crear.js',29.0000,1790533100),
('::1','dist/js/modulos/orientacion/stats.js',29.0000,1790533100),
('::1','dist/js/modulos/orientacion/tour.js',29.0000,1790533100),
('::1','dist/js/modulos/orientacion/validaciones.js',29.0000,1790533100),
('::1','empleados/consultar',29.0000,1790697783),
('::1','empleados/crear',29.0000,1790780212),
('::1','horarios/consultar',29.0000,1789528903),
('::1','horarios/crear',29.0000,1789659964),
('::1','iniciar_sesion',4.0000,1790869401),
('::1','inicio',29.0000,1790869403),
('::1','inventario/consultar',29.0000,1790698765),
('::1','inventario/crear',29.0000,1790698685),
('::1','jornadas/consultar',29.0000,1790710987),
('::1','jornadas/crear',29.0000,1790721955),
('::1','jornadas/detalle/{id}',29.0000,1790711698),
('::1','login',29.0000,1790869397),
('::1','logout',29.0000,1790860274),
('::1','mantenimientos_data_json',29.0000,1790711952),
('::1','medicina/constancia/{id}',29.0000,1790623653),
('::1','medicina/consultar',29.0000,1790698738),
('::1','medicina/crear',29.0000,1790698696),
('::1','medicina/recipe/{id}',29.0000,1790698745),
('::1','mobiliario/consultar',29.0000,1790706518),
('::1','mobiliario/crear',29.0000,1790706674),
('::1','obtenerNotificaciones',14.0000,1790795890),
('::1','obtener_choferes_activos',29.0000,1790711957),
('::1','obtener_eventos_calendario',29.0000,1790795889),
('::1','obtener_rutas_activas',29.0000,1790711957),
('::1','obtener_vehiculos_activos',29.0000,1790711957),
('::1','orientacion/consultar',29.0000,1790537849),
('::1','orientacion/crear',29.0000,1790649933),
('::1','perfil/ver',29.0000,1790609444),
('::1','permisos/gestionar',29.0000,1790797299),
('::1','plugins/DataTables/js/pdfmake.min.js.map',29.0000,1790797300),
('::1','plugins/jspdf/jspdf.umd.min.js.map',29.0000,1790797300),
('::1','proveedores_data_json',29.0000,1790711945),
('::1','psicologia/constancia/{id}',29.0000,1790624201),
('::1','psicologia/consultar',29.0000,1790624197),
('::1','psicologia/crear',29.0000,1790649927),
('::1','psicologia/referencia/{id}',29.0000,1790624213),
('::1','psicologia_stats_json',29.0000,1790795889),
('::1','referencias/consultar',29.0000,1790860141),
('::1','referencias/crear',29.0000,1790860073),
('::1','referencias_stats_admin',29.0000,1790795890),
('::1','refresh_token',14.0000,1790864549),
('::1','reportes/discapacidad',29.0000,1790796157),
('::1','reportes/general',29.0000,1790797276),
('::1','reportes/jornadas',29.0000,1790796165),
('::1','reportes/medicina',29.0000,1790796125),
('::1','reportes/mobiliario',29.0000,1790796170),
('::1','reportes/orientacion',29.0000,1790796147),
('::1','reportes/psicologia',29.0000,1790796132),
('::1','reportes/referencias',29.0000,1790796161),
('::1','reportes/trabajo-social',29.0000,1790796151),
('::1','reportes/transporte',29.0000,1790796197),
('::1','reportes_transporte',29.0000,1790795858),
('::1','reportes_transporte_data',29.0000,1790795859),
('::1','repuestos_data_json',29.0000,1790711951),
('::1','rutas_data_json',29.0000,1790711944),
('::1','sse-notificaciones',29.0000,1790795889),
('::1','sse/notificaciones',29.0000,1790869404),
('::1','stats_discapacidad_admin',29.0000,1790795889),
('::1','stats_medicina_admin',29.0000,1790795889),
('::1','stats_orientacion_admin',29.0000,1790795889),
('::1','stats_ts_admin',29.0000,1790795889),
('::1','trabajo-social/constancia/becas/{id}',29.0000,1790622161),
('::1','trabajo-social/constancia/exoneraciones/{id}',29.0000,1790622224),
('::1','trabajo-social/consultar',29.0000,1790622052),
('::1','trabajo-social/crear',29.0000,1790649930),
('::1','trabajo-social/referencia/becas/{id}',29.0000,1790622203),
('::1','trabajo-social/referencia/exoneraciones/{id}',29.0000,1790622233),
('::1','transporte/consultar',29.0000,1790780280),
('::1','transporte/crear',29.0000,1790780261),
('::1','transporte_consulta',29.0000,1790711928),
('::1','transporte_estadisticas',29.0000,1790711929),
('::1','vehiculos_data_json',29.0000,1790711953);
/*!40000 ALTER TABLE `rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refresh_tokens`
--

DROP TABLE IF EXISTS `refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `refresh_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empleado` int(11) NOT NULL,
  `token` varchar(512) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `revoked` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`(191)),
  KEY `idx_empleado` (`id_empleado`),
  KEY `idx_expires` (`expires_at`),
  KEY `idx_revoked` (`revoked`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refresh_tokens`
--

LOCK TABLES `refresh_tokens` WRITE;
/*!40000 ALTER TABLE `refresh_tokens` DISABLE KEYS */;
INSERT INTO `refresh_tokens` VALUES
(1,1,'ad9c6bb6a680fb747bb4291dc2b26652c1bc8c60b28bee8147db48c1f0b7715aad9902c41f6ef8ab083eabf96164b76f8ac8bc68c5738dad703f03129b2d7e89','2026-09-30 16:57:45','2026-09-15 12:57:45',1),
(2,1,'b11a248d64a28f9031fb6ec212810921a96f9802f709922c81c888c07974b5ebbc8d46da138ade271d58859a4ae9575746e41d18d326bcfd114e0d38facf5ff9','2026-10-01 00:07:27','2026-09-15 20:07:27',0),
(3,1,'10bdb7eeeec201f52bfe3ac3bd80cd2827513bf9a290bbd80407099cbb12336362d9a8354765adc86474a9f3bb3de74d05489cdcbacf50e70754f7d14e284ecf','2026-10-01 01:13:12','2026-09-15 21:13:12',0),
(4,1,'1ca969e96767094c97666e7723fde508c82c87cefef56981d2a4b68d71b734434ae7cb463fce63a5c40f8ff1708ad61491dbec93aa55e31365fb8c61174f1984','2026-10-01 03:20:45','2026-09-15 23:20:45',1),
(5,1,'44392d8b196ac657f362d7200a01cb4634c234b9a5544c7def582de981308c25a461534a7733b87be199a482a42feb8272dc81ebeae3271dbbabe432a0e02819','2026-10-01 13:34:40','2026-09-16 09:34:40',1),
(6,1,'f470d49bdf0bb573b2b2b2c3b9d6c5e61211e3fbb3e38a458767b66a79a04328ae00c811409a3606ea154e59f49a5f454cb69bb216d473adce55cc80999e2f6e','2026-10-02 13:34:22','2026-09-17 09:34:22',1),
(7,3,'70e0fe3d2079898fc38501d5192a35c4a909e2815dd54a7c8d6ad3d85c66a90a12b11b77916e843125bded37dde84906ca04160bef2217a5a6658e98376a2da6','2026-10-02 13:36:09','2026-09-17 09:36:09',1),
(8,1,'12428e162597435e5c33af334ada4cb9fb5f37503adfa4385f30f21844634d6a5a2d27c0d91f53031a87dd2288df701d9f683b1ff32ab089cb722cf6f5fab075','2026-10-02 13:40:10','2026-09-17 09:40:10',0),
(9,1,'2b6fcff0a6386df0c1073fd518c7ca90f3e43ba153c78706d0bd321a2bb765ac5b611683ae1b0ece4973f7e50b5f9547424b40f4cf2ee67c23732af560b9b1e1','2026-10-02 15:45:58','2026-09-17 11:45:58',1),
(10,1,'913bf0b8313e2fd09d89c317041cb93b4a082ac1c71209798017c87f40e48302c0f4f17c12d5acb5114012d6c6af611bfc2fee30a9ff10163adf8b2569bd81a4','2026-10-17 16:15:58','2026-09-17 12:15:58',1),
(11,1,'c617b03ef5920a0ce3132066295cd951cca17c3f66c2ec1466b3ce41f6f6e220c3f16490a856944a6e4eab8b366834777286cde48b5e0e5f388dccb3c05972ce','2026-10-17 16:28:01','2026-09-17 12:28:01',1),
(12,1,'a1e0260f8f52b3ef46cdad75cce821e7abf19171b7a731be97957e3a8a324f46ebdb42ba3247c0e85a5fc7c79866d1085f8198c5ec522bdfacd4a28813419ced','2026-10-02 16:28:15','2026-09-17 12:28:15',1),
(13,1,'0cdb4115877031fcd36fd0e0f3fee13ede1a82eec00e6fefed6bbd2b7a4324a4661f09b3e31721b0d171d3b77c28956a2f699a31d498aaac3b2177793a3da5e8','2026-10-05 17:25:07','2026-09-20 13:25:07',0),
(14,1,'9f713ec25df23c7fff09d2c1279f3b39be754122e0deaf9020f95a69653e0417a7cc402a238bbc47069deb4da84f1a0f66166738c816449620af82a171f2618c','2026-10-05 18:11:13','2026-09-20 14:11:13',0),
(15,1,'b3edd0f7081e2e0fd2b78b75130d36bb04f480d45303ca42f0b5cc136e39affd8773f1b5201d1fb2a016e657bc94183cfd73fde4cba1b432eaf5ffe716146074','2026-10-05 19:14:23','2026-09-20 15:14:23',1),
(16,1,'56868275cf3535c2f52a225a4129b9d1a3d1b37a598c38936f18dbdbf81628ca6fc958b5f8fb70d90328d62999a11fc14a0a2472881a8f5b33b8195c4f518e4b','2026-10-09 19:45:23','2026-09-24 15:45:23',1),
(17,3,'7a026dd527e9aa9231a5b1c32d14049c959a10e45350eb12ef7d8497c955fdeee4184b1922bfdca6aadee337d96182b36bda81b4f4e228c7a8f7a0b5652be9a7','2026-10-09 19:45:43','2026-09-24 15:45:43',1),
(18,1,'54ee0428ff2ff9b7169f6560b02100e0b4b0c2978ed6c85f25cc69bb4107d8bd583b22a0d0dd7a9a4a389b272bf0ed6dd417594ab8a9bf5835ef1770d461062b','2026-10-09 19:51:44','2026-09-24 15:51:44',0),
(19,1,'9e625a368a797f9598912d2dca7016ffab1d908984cbc7e93184a9a96cf6956f663ed9464d0228a1fef6b5b5ecb7e9c32d7bdcc8c00c2b19a19dd8e809517d04','2026-10-09 21:00:16','2026-09-24 17:00:16',0),
(20,1,'b8410a26d326814f1f4c6b7fe338201567261116f1f9daf92a9f05a8c1c7737071db53002240b832cf0d7ac245b1d623cf76bef423da8810a7fce945b0538425','2026-10-09 22:58:16','2026-09-24 18:58:16',0),
(21,1,'78c4119962ad3e4a08ad64d20d9a08009cee6b4f7d34787be4e5aee2aebaa63f63e20eb8613a45e657bcb136e07c84c2e71ec5b5d06f65e22c3125142c366dc6','2026-10-10 14:31:02','2026-09-25 10:31:02',1),
(22,1,'b35383540085ea0669b1528e4ad4757cefeb8c6f3a7b0b2cf2730a33c4628347b360b7510884f9387761005bd9c4917c0043f12430a29b8826e58c21fad5b4e5','2026-10-10 14:33:16','2026-09-25 10:33:16',0),
(23,1,'919e7fcf18db87fcbfa042b48e6d3201a4371f72e2661aa16f92dfb99bb8d45547faad68f03837d36a89c661fb98dfcc38b478b587872a4b3e2a00fcd807a783','2026-10-12 18:01:05','2026-09-27 14:01:05',1),
(24,1,'74b323dcd3d89d1a098265f18c56d780980e9e7b76c3b21127371c35aec63e84c0908730b94edc9906a837a098f853905f99b9ad5a7700ff8aab092d249390af','2026-10-12 19:20:39','2026-09-27 15:20:39',1),
(25,1,'6543046b2a07ab11a8f5becec9d5e6dfae80bc54427458151cb118b4f493a0f267b9137ced8667d4c5fc1a8eb540d1108e2498c733f6e06bca71e966ff3938dd','2026-10-27 20:14:28','2026-09-27 16:14:28',0),
(26,1,'84fbc5070dcffe175a95f762bd113c56dac8f83625684859d3fa3155bd3392d38f905a343509280c81b94d87024036cb69ffec6e6681a4a1f619eb3cb0b73825','2026-10-13 01:28:12','2026-09-27 21:28:12',0),
(27,1,'799e38232e5ddc7e0c95ca054c33193a5f9722d92fb71ee30f9f136ce57c89d970fc0e9d28cf4c80d8866be599256e79745ecf9394a16bce96a35952782c96f7','2026-10-13 02:54:38','2026-09-27 22:54:38',1),
(28,1,'d3cf220b5954034b3bf7a9659c7c2b360482036fbc691d1d423df6cd9d87204f48d925d657ba34c0d2f81cb50a41d3ba4f7aab765092362277746bc7763812fb','2026-10-13 12:47:20','2026-09-28 08:47:20',1),
(29,1,'4af75ed74672680eee99f5663d5e1fc8356bf4d8cee9034dab185058c8c5ac41d33de33a5e3c85f688ab6fa0f2935815b0fa0246609cd9b44dce30a63f0db722','2026-10-13 14:51:27','2026-09-28 10:51:27',0),
(30,1,'833b22e84609e233d8dc76fa5ce9fcdd0868353628a8de4b06fc64b460f5024026b329c3f48483db66824f491329436fc0371d17b52d0fce3a7c0f04fe750cb6','2026-10-13 19:00:41','2026-09-28 15:00:41',0),
(31,1,'e96d99f4884efc386c010d00739cce44b9449cbb46135aa748f7bd371cc0384d78c4245f34fcd43e51392ade3be6ef0a472562c4c29d8a0733540b35c609c8c5','2026-10-13 19:27:10','2026-09-28 15:27:10',1),
(32,3,'7b4c1b7d3c8b0880f8c0a2247947feac27a3d86b2dd064c4173c6d2c9cb41b9b845805fecc171e8a3e60b8e2e59f5b6a922219633fbcabde83117ce3608c6c6f','2026-10-13 19:36:04','2026-09-28 15:36:04',1),
(33,1,'43414e6339b67cfbf39832a08d6b17453dfd4769ed6d971489270b66dcde39f1136a3bfc454aa0b291c28a13d268163f5b8a5275a2b7f806c6b7a986edd02a79','2026-10-14 02:18:46','2026-09-28 22:18:46',1),
(34,1,'83d305a72f75d4baf115b978570d759c4f0246d8d04e54bdde569248a7ccfde28d2f9fd88c33b03c5f2186414e7a7bef13de8ff2213f893c8c335b05a1a7dcee','2026-10-14 16:01:28','2026-09-29 12:01:28',1),
(35,2,'31de3b7e43662710d8220ab36303fe9119fc6f60ea292f1d7a0cc224d11e9e101f241b18b9491ba1e1410902cb8471791f19f7edb674ca85c676329342de9c31','2026-10-14 16:03:20','2026-09-29 12:03:20',1),
(36,3,'3d2c85e23da3212b2b993364411204e8fa52741dcc63a96a1b47a866e2addc201ef6bcd7e232344a949d95701669caf263c6fa0e95452e8abfa5bdab0e85620e','2026-10-14 16:03:55','2026-09-29 12:03:55',1),
(37,1,'2ca2ed7bc01fd0ca9da6f9e5ed0e50ac741307784146662528e9b3e70751c6c2c4bcdc15a5253fbd67fae0232706936c5e518e098213bf5e0e9f43c2ac737f7d','2026-10-14 16:04:37','2026-09-29 12:04:37',1),
(38,1,'586be9e42cfe1d8b375d4749be62ba741691b8ab00d2d0748b21fa3bcfb002ee09ec49680793a0451adba1f2a1ac791decd5fca95325c8ee1c76121ffa344c66','2026-10-14 18:10:39','2026-09-29 14:10:39',0),
(39,1,'c7196402101b1166597c7b6665a4c34899f819d814fc7a4f0c96ced39c7a8bb886db51bfd9157e91c8e77494c79b571b46a20fb9e3f5f2e6adc5bc6ed089f438','2026-10-14 19:37:35','2026-09-29 15:37:35',1),
(40,1,'71338174287205e8f088defd4ee5633d875ae37c0eb1f7e01042f0270a4c6b45edbdd5f0a96b368598f9a595769645eaf6266f6804d865f245cfa00938bf10c4','2026-10-29 19:56:31','2026-09-29 15:56:31',0),
(41,1,'307944851b5f8e2f1f1efdb86212189c2c8ca2e057d7544c222ad0e6e41107d292996c65020e16339b5f33bda8862c91d97ef22c8e25051125c8b5e10a994fc3','2026-10-14 20:58:29','2026-09-29 16:58:29',0),
(42,1,'11738e928e3bf3042adbd52d9232c0d53ac83d2e5e8efc7de10b1c6c531856aa48b50423ea1a2dbe82d361fc71380b6562990443b6bd4515dcd64eb251852c36','2026-10-14 22:36:51','2026-09-29 18:36:51',1),
(43,1,'3ff229711c82e4a77da63da613a5b2c6e39cd5f8147ce474dae828dc180e3c7643ca07f842d8a01ae738dd6b2389691e3b743eb1a15051c0f1890c9025726088','2026-10-15 02:19:52','2026-09-29 22:19:52',0),
(44,1,'d872dfbb67bdf8607281e0112bc88f541f6db7c54d67c3c8f9144e1d36dd90e59964dd848ff208cac97e4634bbbc9afef379f0c998230e519a25ef4c881930f8','2026-10-15 03:44:40','2026-09-29 23:44:40',1),
(45,1,'c267e9f2cce8b038595feb1078c93d7b7857070e20c65fd45ed853e93cc22749edfbb69657f56108e14c0bd04319f2e57da291ea12e25e29aea2d0561df05fc5','2026-10-15 12:47:42','2026-09-30 08:47:42',0),
(46,1,'c5bbe9f306c3dfdbb5bb75822e4a8d8864adde6ed60118bbb75f8abe935d0fb603fe9b02fa6ad0a57981ebceef1547ef7567b9a9823fce008823ebf450964510','2026-10-15 14:21:51','2026-09-30 10:21:51',0),
(47,1,'a9fd430b7a655f2bf85f3861dd098c2eda40ec18d52762b7ce245e30c29e8c68dac167d4ed0d4b1f5e5eae1f142f3fe2c1f6db9b5203e4e165c1686237a05659','2026-10-15 17:52:15','2026-09-30 13:52:15',1),
(48,1,'863137b31f2ec03aae48479adccc399a6f9da0bc9b11f16298c7d5f128325dbac2e14072a9878ef5bbe82bfb185716cd94e8e4f98adbc53121b59e3ffe64ab4b','2026-10-15 19:05:44','2026-09-30 15:05:44',1),
(49,1,'35525eeafdb5aaba99d8566db7bcd8d69e2dd819e57f7d8290f7d5ae1ae763ecf7f0b388bbebacb60d04a23873a09443f3f59ba5915290da3dd12d92e690f780','2026-10-30 19:17:33','2026-09-30 15:17:33',1),
(50,1,'b3002512cf8425278acef00836f54f470457a5fd7720970855c378c9c58e7370ab012a8b3752edfaef3f554a58888239644a32c493a9bf96dcf079fcf4400cf4','2026-10-30 19:18:06','2026-09-30 15:18:06',1),
(51,1,'e1e55e0fe63816d340794a75c667455136c2a45d7a177e0aa138e59b02b3f63001bae87bdaf1a3b9df091b8ac14291a257dfb8e221e522b1579019f084f58141','2026-10-15 19:18:20','2026-09-30 15:18:20',1),
(52,3,'3a2884c0c4795057c6655a355c3d582febac94d7f94c17a78caf1d9f62d25326a1aad6b88b50e73b7c79aa2e4fc26e9cc74b11dfbb47cb17d87778a93ab14014','2026-10-15 19:32:41','2026-09-30 15:32:41',1),
(53,2,'9d7e9769b41b181cf671e637413b6e185cfb75c869666934ce78738283226d2f280f1d4989d3d6544c6dc06cd7ff188a2109411251303a20df209661dcec1f3e','2026-10-15 19:33:34','2026-09-30 15:33:34',1),
(54,1,'73fd920a53207a30ed0c3c332b92593feb498d02586eb6d00d1f54aa9b503886220203b14d9e8ae084018cdeaed038b498775b1f441f583bef0b8b64cb473231','2026-10-15 19:41:07','2026-09-30 15:41:07',1),
(55,1,'1d49d973bc3e4ad126ad8a5dd7c39cc312354045fc241ae1a683ab3b57c29206acda5c10c09151ab96ae4b5b6a404ec62abca3b6802592e30dfce2fe005b2fa4','2026-10-16 12:37:55','2026-10-01 08:37:55',1),
(56,3,'479ae9fc0f7c333262b697db48a9723b2028638c1f851173ac2d8c48011c8c2727ea678efb36b9866c8cce8e399ccfc632d81e76dd48328adeeca3df74c6215f','2026-10-16 13:06:47','2026-10-01 09:06:47',1),
(57,1,'9108b90782eefbf39703d67d35611f8085b4dff39519b2da479903f40e49e972aa030d96b2e3f86d833ed55ab0af1e2e986f4579467c2b6f5e2718342422c4dd','2026-10-16 13:07:13','2026-10-01 09:07:13',1),
(58,3,'b3a1e235d9ce9f5b7c84c786e361e8e27408f946cd4d7ebee2f87b2d19c14822d4b24acb1338bbdcbd05ae29a03d69678e9d1cf7058d3cfe7e85102fae647f0b','2026-10-16 13:07:44','2026-10-01 09:07:44',1),
(59,1,'0b6c984c51bba9098b637ead690e0a5f5a234aaed78c4aa1a3f431ed348d578ca899ce24391825915ba172f2b5c6c4c4630ecf016eead35c6db9d6df6a0d9542','2026-10-16 13:08:30','2026-10-01 09:08:30',1),
(60,3,'20076abac386de4a57ad745951d45e9cbad5605d2d43b9d9a7e956c2347e8aca906266704d146fc737d01726eb5a8d53c485510051b064f3bca36ee8a045be9a','2026-10-16 13:12:02','2026-10-01 09:12:02',0),
(61,1,'582658e75a81a98a48458a27eac57979c0f00feca1553a236aedf749cb042096','2026-10-16 15:43:21','2026-10-01 11:43:21',0);
/*!40000 ALTER TABLE `refresh_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_modulo_permiso`
--

DROP TABLE IF EXISTS `rol_modulo_permiso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol_modulo_permiso` (
  `id_rmp` int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo_emp` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL,
  PRIMARY KEY (`id_rmp`),
  KEY `id_tipo_emp` (`id_tipo_emp`),
  KEY `id_modulo` (`id_modulo`),
  KEY `id_permiso` (`id_permiso`),
  KEY `idx_rmp_rol_mod_perm` (`id_tipo_emp`,`id_modulo`,`id_permiso`),
  CONSTRAINT `rol_modulo_permiso_ibfk_1` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id_modulo`),
  CONSTRAINT `rol_modulo_permiso_ibfk_2` FOREIGN KEY (`id_permiso`) REFERENCES `permiso` (`id_permiso`),
  CONSTRAINT `rol_modulo_permiso_ibfk_3` FOREIGN KEY (`id_tipo_emp`) REFERENCES `tipo_empleado` (`id_tipo_emp`)
) ENGINE=InnoDB AUTO_INCREMENT=329 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_modulo_permiso`
--

LOCK TABLES `rol_modulo_permiso` WRITE;
/*!40000 ALTER TABLE `rol_modulo_permiso` DISABLE KEYS */;
INSERT INTO `rol_modulo_permiso` VALUES
(137,1,2,1),
(138,1,2,2),
(139,1,2,3),
(140,1,2,4),
(141,1,3,1),
(142,1,3,2),
(143,1,3,3),
(144,1,3,4),
(145,1,4,1),
(146,1,4,2),
(147,1,4,3),
(148,1,4,4),
(149,1,10,1),
(150,1,10,2),
(151,1,10,3),
(152,1,10,4),
(153,1,15,1),
(154,1,15,2),
(155,1,15,3),
(156,1,15,4),
(263,1,19,1),
(264,1,19,2),
(265,1,19,3),
(266,1,19,4),
(157,2,2,1),
(158,2,2,2),
(159,2,2,3),
(160,2,2,4),
(161,2,5,1),
(162,2,5,2),
(163,2,5,3),
(164,2,5,4),
(165,2,9,1),
(166,2,9,2),
(167,2,9,3),
(168,2,9,4),
(169,2,10,1),
(170,2,10,2),
(171,2,10,3),
(172,2,10,4),
(259,2,11,1),
(260,2,11,2),
(261,2,11,3),
(262,2,11,4),
(177,2,15,1),
(178,2,15,2),
(179,2,15,3),
(180,2,15,4),
(267,2,19,1),
(268,2,19,2),
(269,2,19,3),
(270,2,19,4),
(181,3,2,1),
(182,3,2,2),
(183,3,2,3),
(184,3,2,4),
(185,3,7,1),
(186,3,7,2),
(187,3,7,3),
(188,3,7,4),
(189,3,10,1),
(190,3,10,2),
(191,3,10,3),
(192,3,10,4),
(193,3,15,1),
(194,3,15,2),
(195,3,15,3),
(196,3,15,4),
(271,3,19,1),
(272,3,19,2),
(273,3,19,3),
(274,3,19,4),
(197,4,2,1),
(198,4,2,2),
(199,4,2,3),
(200,4,2,4),
(201,4,6,1),
(202,4,6,2),
(203,4,6,3),
(204,4,6,4),
(205,4,10,1),
(206,4,10,2),
(207,4,10,3),
(208,4,10,4),
(209,4,15,1),
(210,4,15,2),
(211,4,15,3),
(212,4,15,4),
(317,4,19,1),
(318,4,19,2),
(319,4,19,3),
(320,4,19,4),
(213,5,2,1),
(214,5,2,2),
(215,5,2,3),
(216,5,2,4),
(217,5,8,1),
(218,5,8,2),
(219,5,8,3),
(220,5,8,4),
(221,5,10,1),
(222,5,10,2),
(223,5,10,3),
(224,5,10,4),
(225,5,15,1),
(226,5,15,2),
(227,5,15,3),
(228,5,15,4),
(279,5,19,1),
(280,5,19,2),
(281,5,19,3),
(282,5,19,4),
(1,6,1,1),
(2,6,1,2),
(3,6,1,3),
(4,6,1,4),
(5,6,2,1),
(6,6,2,2),
(7,6,2,3),
(8,6,2,4),
(9,6,3,1),
(10,6,3,2),
(11,6,3,3),
(12,6,3,4),
(13,6,4,1),
(14,6,4,2),
(15,6,4,3),
(16,6,4,4),
(17,6,5,1),
(18,6,5,2),
(19,6,5,3),
(20,6,5,4),
(21,6,6,1),
(22,6,6,2),
(23,6,6,3),
(24,6,6,4),
(25,6,7,1),
(26,6,7,2),
(27,6,7,3),
(28,6,7,4),
(29,6,8,1),
(30,6,8,2),
(31,6,8,3),
(32,6,8,4),
(33,6,9,1),
(34,6,9,2),
(35,6,9,3),
(36,6,9,4),
(37,6,10,1),
(38,6,10,2),
(39,6,10,3),
(40,6,10,4),
(41,6,11,1),
(42,6,11,2),
(43,6,11,3),
(44,6,11,4),
(45,6,12,1),
(46,6,12,2),
(47,6,12,3),
(48,6,12,4),
(49,6,13,1),
(50,6,13,2),
(51,6,13,3),
(52,6,13,4),
(245,6,14,1),
(246,6,14,2),
(247,6,14,3),
(248,6,14,4),
(57,6,15,1),
(58,6,15,2),
(59,6,15,3),
(60,6,15,4),
(61,6,16,1),
(62,6,16,2),
(63,6,16,3),
(64,6,16,4),
(65,6,17,1),
(66,6,17,2),
(67,6,17,3),
(68,6,17,4),
(241,6,18,1),
(242,6,18,2),
(243,6,18,3),
(244,6,18,4),
(283,6,19,1),
(284,6,19,2),
(285,6,19,3),
(286,6,19,4),
(325,6,20,1),
(326,6,20,2),
(327,6,20,3),
(328,6,20,4),
(321,7,15,1),
(322,7,15,2),
(323,7,15,3),
(324,7,15,4),
(287,7,19,1),
(288,7,19,2),
(289,7,19,3),
(290,7,19,4),
(291,8,19,1),
(292,8,19,2),
(293,8,19,3),
(294,8,19,4),
(295,9,19,1),
(296,9,19,2),
(297,9,19,3),
(298,9,19,4),
(69,10,1,1),
(70,10,1,2),
(71,10,1,3),
(72,10,1,4),
(73,10,2,1),
(74,10,2,2),
(75,10,2,3),
(76,10,2,4),
(77,10,3,1),
(78,10,3,2),
(79,10,3,3),
(80,10,3,4),
(81,10,4,1),
(82,10,4,2),
(83,10,4,3),
(84,10,4,4),
(85,10,5,1),
(86,10,5,2),
(87,10,5,3),
(88,10,5,4),
(89,10,6,1),
(90,10,6,2),
(91,10,6,3),
(92,10,6,4),
(93,10,7,1),
(94,10,7,2),
(95,10,7,3),
(96,10,7,4),
(97,10,8,1),
(98,10,8,2),
(99,10,8,3),
(100,10,8,4),
(101,10,9,1),
(102,10,9,2),
(103,10,9,3),
(104,10,9,4),
(105,10,10,1),
(106,10,10,2),
(107,10,10,3),
(108,10,10,4),
(109,10,11,1),
(110,10,11,2),
(111,10,11,3),
(112,10,11,4),
(113,10,12,1),
(114,10,12,2),
(115,10,12,3),
(116,10,12,4),
(117,10,13,1),
(118,10,13,2),
(119,10,13,3),
(120,10,13,4),
(121,10,14,1),
(122,10,14,2),
(123,10,14,3),
(124,10,14,4),
(125,10,15,1),
(126,10,15,2),
(127,10,15,3),
(128,10,15,4),
(129,10,16,1),
(130,10,16,2),
(131,10,16,3),
(132,10,16,4),
(133,10,17,1),
(134,10,17,2),
(135,10,17,3),
(136,10,17,4),
(299,10,19,1),
(300,10,19,2),
(301,10,19,3),
(302,10,19,4),
(229,11,15,1),
(230,11,15,2),
(231,11,15,3),
(232,11,15,4),
(303,11,19,1),
(304,11,19,2),
(305,11,19,3),
(306,11,19,4),
(307,12,19,1),
(308,12,19,2),
(309,12,19,3),
(310,12,19,4),
(311,13,19,1),
(312,13,19,2),
(313,13,19,3),
(314,13,19,4);
/*!40000 ALTER TABLE `rol_modulo_permiso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_empleado`
--

DROP TABLE IF EXISTS `tipo_empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_empleado` (
  `id_tipo_emp` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) DEFAULT NULL,
  `id_servicios` int(11) NOT NULL,
  `estatus` tinyint(1) DEFAULT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_tipo_emp`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_empleado`
--

LOCK TABLES `tipo_empleado` WRITE;
/*!40000 ALTER TABLE `tipo_empleado` DISABLE KEYS */;
INSERT INTO `tipo_empleado` VALUES
(1,'Psicologo',1,1,'2024-11-12'),
(2,'Medico',2,1,'2024-11-12'),
(3,'Trabajador Social',4,1,'2024-11-12'),
(4,'Orientador',3,1,'2024-11-12'),
(5,'Discapacidad',5,1,'2024-11-12'),
(6,'Administrador',8,1,'2024-11-12'),
(7,'Secretaria',6,1,'2024-11-14'),
(8,'Chofer',9,1,'2025-04-19'),
(9,'Mecánico',9,1,'2025-04-19'),
(10,'Superusuario',8,1,'2025-05-30'),
(11,'Administrativo',6,1,'2025-06-10'),
(12,'Enfermero',2,1,'2026-02-02'),
(13,'Obreros',6,1,'2026-02-03');
/*!40000 ALTER TABLE `tipo_empleado` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-01 12:26:51
