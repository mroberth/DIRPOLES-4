/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: dirpoles_business
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
-- Table structure for table `asignaciones_rutas`
--

DROP TABLE IF EXISTS `asignaciones_rutas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asignaciones_rutas` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_ruta` int(11) NOT NULL,
  `id_vehiculo` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `fecha_asignacion` date NOT NULL,
  `estatus` enum('Activa','Inactiva') DEFAULT 'Activa',
  PRIMARY KEY (`id_asignacion`),
  KEY `id_ruta` (`id_ruta`),
  KEY `id_vehiculo` (`id_vehiculo`),
  CONSTRAINT `asignaciones_rutas_ibfk_1` FOREIGN KEY (`id_ruta`) REFERENCES `rutas` (`id_ruta`),
  CONSTRAINT `asignaciones_rutas_ibfk_2` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaciones_rutas`
--

LOCK TABLES `asignaciones_rutas` WRITE;
/*!40000 ALTER TABLE `asignaciones_rutas` DISABLE KEYS */;
INSERT INTO `asignaciones_rutas` VALUES
(1,1,2,4,'2026-10-02','Activa');
/*!40000 ALTER TABLE `asignaciones_rutas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `becas`
--

DROP TABLE IF EXISTS `becas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `becas` (
  `id_becas` int(11) NOT NULL AUTO_INCREMENT,
  `id_solicitud_serv` int(11) DEFAULT NULL,
  `cta_bcv` varchar(100) DEFAULT NULL,
  `direccion_pdf` varchar(100) DEFAULT NULL,
  `tipo_banco` varchar(4) NOT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_becas`),
  KEY `id_solicitud_serv` (`id_solicitud_serv`),
  CONSTRAINT `becas_ibfk_1` FOREIGN KEY (`id_solicitud_serv`) REFERENCES `solicitud_de_servicio` (`id_solicitud_serv`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `becas`
--

LOCK TABLES `becas` WRITE;
/*!40000 ALTER TABLE `becas` DISABLE KEYS */;
INSERT INTO `becas` VALUES
(1,6,'0102000999121200','uploads/trabajo_social/becas/planilla_20260928_013602_6ab9c482ed22a.pdf','0108','2026-09-27');
/*!40000 ALTER TABLE `becas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beneficiario`
--

DROP TABLE IF EXISTS `beneficiario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `beneficiario` (
  `id_beneficiario` int(11) NOT NULL AUTO_INCREMENT,
  `id_pnf` int(11) DEFAULT NULL,
  `seccion` varchar(20) DEFAULT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `tipo_cedula` varchar(10) NOT NULL,
  `cedula` varchar(12) DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `genero` char(10) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `estatus` int(10) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_beneficiario`),
  UNIQUE KEY `idx_ben_cedula` (`tipo_cedula`,`cedula`),
  KEY `id_pnf` (`id_pnf`),
  KEY `idx_ben_estatus_pnf` (`estatus`,`id_pnf`),
  KEY `idx_ben_fecha` (`fecha_creacion`),
  CONSTRAINT `beneficiario_ibfk_1` FOREIGN KEY (`id_pnf`) REFERENCES `pnf` (`id_pnf`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beneficiario`
--

LOCK TABLES `beneficiario` WRITE;
/*!40000 ALTER TABLE `beneficiario` DISABLE KEYS */;
INSERT INTO `beneficiario` VALUES
(1,5,'3102','Jesus','Matos','V','30995937','2005-11-13','04245304944','matosjesus464@gmail.com','M','Carrera 13 con calle 54',1,'2026-09-20 19:14:38');
/*!40000 ALTER TABLE `beneficiario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cita`
--

DROP TABLE IF EXISTS `cita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cita` (
  `id_cita` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `id_beneficiario` int(11) DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL,
  `estatus` int(1) DEFAULT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_cita`),
  KEY `id_beneficiario` (`id_beneficiario`),
  KEY `estatus` (`estatus`),
  KEY `idx_cita_emp_fecha_hora` (`id_empleado`,`fecha`,`hora`),
  KEY `idx_cita_estatus` (`estatus`,`fecha`),
  CONSTRAINT `cita_ibfk_1` FOREIGN KEY (`id_beneficiario`) REFERENCES `beneficiario` (`id_beneficiario`),
  CONSTRAINT `cita_ibfk_2` FOREIGN KEY (`estatus`) REFERENCES `estado_cita` (`id_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cita`
--

LOCK TABLES `cita` WRITE;
/*!40000 ALTER TABLE `cita` DISABLE KEYS */;
INSERT INTO `cita` VALUES
(1,'2026-09-21','12:00:00',1,3,5,'2026-09-17');
/*!40000 ALTER TABLE `cita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consulta_medica`
--

DROP TABLE IF EXISTS `consulta_medica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `consulta_medica` (
  `id_consulta_med` int(11) NOT NULL AUTO_INCREMENT,
  `id_detalle_patologia` int(11) NOT NULL,
  `id_solicitud_serv` int(11) NOT NULL,
  `estatura` decimal(4,2) NOT NULL,
  `peso` decimal(4,2) NOT NULL,
  `tipo_sangre` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `motivo_visita` varchar(255) NOT NULL,
  `diagnostico` varchar(255) NOT NULL,
  `tratamiento` varchar(255) NOT NULL,
  `observaciones` varchar(255) NOT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_consulta_med`),
  KEY `id_solicitud_serv` (`id_solicitud_serv`),
  KEY `id_detalle_patologia` (`id_detalle_patologia`),
  CONSTRAINT `consulta_medica_ibfk_1` FOREIGN KEY (`id_solicitud_serv`) REFERENCES `solicitud_de_servicio` (`id_solicitud_serv`),
  CONSTRAINT `consulta_medica_ibfk_2` FOREIGN KEY (`id_detalle_patologia`) REFERENCES `detalle_patologia` (`id_detalle_patologia`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consulta_medica`
--

LOCK TABLES `consulta_medica` WRITE;
/*!40000 ALTER TABLE `consulta_medica` DISABLE KEYS */;
INSERT INTO `consulta_medica` VALUES
(1,3,3,1.70,59.00,'A+','Motivo','Diagnostico','Tratamiento','Observaciones','2026-09-24'),
(2,5,9,1.70,60.00,'A+','Nada que agregar','Nada que agregar','Nada que agregar','Nada que agregar','2026-09-29');
/*!40000 ALTER TABLE `consulta_medica` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consulta_psicologica`
--

DROP TABLE IF EXISTS `consulta_psicologica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `consulta_psicologica` (
  `id_psicologia` int(11) NOT NULL AUTO_INCREMENT,
  `id_solicitud_serv` int(11) NOT NULL,
  `id_detalle_patologia` int(11) DEFAULT NULL,
  `tipo_consulta` enum('Diagnóstico','Retiro temporal','Cambio de carrera','') NOT NULL,
  `diagnostico` text DEFAULT NULL,
  `tratamiento_gen` text DEFAULT NULL,
  `motivo_retiro` text DEFAULT NULL,
  `duracion_retiro` varchar(50) DEFAULT NULL,
  `motivo_cambio` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_psicologia`),
  KEY `id_solicitud_serv` (`id_solicitud_serv`),
  KEY `id_detalle_patologia` (`id_detalle_patologia`),
  CONSTRAINT `consulta_psicologica_ibfk_1` FOREIGN KEY (`id_detalle_patologia`) REFERENCES `detalle_patologia` (`id_detalle_patologia`),
  CONSTRAINT `consulta_psicologica_ibfk_2` FOREIGN KEY (`id_solicitud_serv`) REFERENCES `solicitud_de_servicio` (`id_solicitud_serv`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consulta_psicologica`
--

LOCK TABLES `consulta_psicologica` WRITE;
/*!40000 ALTER TABLE `consulta_psicologica` DISABLE KEYS */;
INSERT INTO `consulta_psicologica` VALUES
(2,2,2,'Retiro temporal','No aplica','No aplica','Motivos personales','Dos semanas','No aplica','Nada','2026-09-20 19:33:30'),
(3,8,4,'Diagnóstico','Prueba del diagnostico','nada','No aplica','No aplica','No aplica','nada','2026-09-28 19:36:33');
/*!40000 ALTER TABLE `consulta_psicologica` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_desactivar_beneficiario_retiro` AFTER INSERT ON `consulta_psicologica` FOR EACH ROW BEGIN
    DECLARE v_id_beneficiario INT;
    
    IF NEW.tipo_consulta = 'Retiro temporal' THEN

        SELECT id_beneficiario 
        INTO v_id_beneficiario
        FROM solicitud_de_servicio 
        WHERE id_solicitud_serv = NEW.id_solicitud_serv;
        

        IF v_id_beneficiario IS NOT NULL THEN
            UPDATE beneficiario 
            SET estatus = 0 
            WHERE id_beneficiario = v_id_beneficiario;
        END IF;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `detalle_ficha_equipo`
--

DROP TABLE IF EXISTS `detalle_ficha_equipo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_ficha_equipo` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_ficha` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_ficha` (`id_ficha`),
  KEY `id_equipo` (`id_equipo`),
  CONSTRAINT `detalle_ficha_equipo_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`),
  CONSTRAINT `detalle_ficha_equipo_ibfk_2` FOREIGN KEY (`id_ficha`) REFERENCES `fichas_tecnicas` (`id_ficha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ficha_equipo`
--

LOCK TABLES `detalle_ficha_equipo` WRITE;
/*!40000 ALTER TABLE `detalle_ficha_equipo` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_ficha_equipo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_ficha_mobiliario`
--

DROP TABLE IF EXISTS `detalle_ficha_mobiliario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_ficha_mobiliario` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_ficha` int(11) NOT NULL,
  `id_mobiliario` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_ficha` (`id_ficha`),
  KEY `id_mobiliario` (`id_mobiliario`),
  CONSTRAINT `detalle_ficha_mobiliario_ibfk_1` FOREIGN KEY (`id_ficha`) REFERENCES `fichas_tecnicas` (`id_ficha`),
  CONSTRAINT `detalle_ficha_mobiliario_ibfk_2` FOREIGN KEY (`id_mobiliario`) REFERENCES `mobiliario` (`id_mobiliario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ficha_mobiliario`
--

LOCK TABLES `detalle_ficha_mobiliario` WRITE;
/*!40000 ALTER TABLE `detalle_ficha_mobiliario` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_ficha_mobiliario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_insumo`
--

DROP TABLE IF EXISTS `detalle_insumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_insumo` (
  `id_detalle_insumo` int(11) NOT NULL AUTO_INCREMENT,
  `id_consulta_med` int(11) NOT NULL,
  `id_insumo` int(11) NOT NULL,
  `cantidad_usada` varchar(100) NOT NULL,
  PRIMARY KEY (`id_detalle_insumo`),
  KEY `id_consulta_med` (`id_consulta_med`),
  KEY `id_insumo` (`id_insumo`),
  CONSTRAINT `detalle_insumo_ibfk_1` FOREIGN KEY (`id_consulta_med`) REFERENCES `consulta_medica` (`id_consulta_med`),
  CONSTRAINT `detalle_insumo_ibfk_2` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_insumo`
--

LOCK TABLES `detalle_insumo` WRITE;
/*!40000 ALTER TABLE `detalle_insumo` DISABLE KEYS */;
INSERT INTO `detalle_insumo` VALUES
(1,2,1,'2');
/*!40000 ALTER TABLE `detalle_insumo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_insumo_jornadas`
--

DROP TABLE IF EXISTS `detalle_insumo_jornadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_insumo_jornadas` (
  `id_detalle_insumo_jornadas` int(11) NOT NULL AUTO_INCREMENT,
  `id_jornadas` int(11) NOT NULL,
  `id_insumo` int(11) NOT NULL,
  `cantidad_usada` varchar(100) NOT NULL,
  PRIMARY KEY (`id_detalle_insumo_jornadas`),
  KEY `id_jornadas` (`id_jornadas`),
  KEY `id_insumo` (`id_insumo`),
  CONSTRAINT `detalle_insumo_jornadas_ibfk_1` FOREIGN KEY (`id_jornadas`) REFERENCES `jornadas_medicas` (`id_jornada`),
  CONSTRAINT `detalle_insumo_jornadas_ibfk_2` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_insumo_jornadas`
--

LOCK TABLES `detalle_insumo_jornadas` WRITE;
/*!40000 ALTER TABLE `detalle_insumo_jornadas` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_insumo_jornadas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_patologia`
--

DROP TABLE IF EXISTS `detalle_patologia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_patologia` (
  `id_detalle_patologia` int(11) NOT NULL AUTO_INCREMENT,
  `id_patologia` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_detalle_patologia`),
  KEY `id_patologia` (`id_patologia`),
  CONSTRAINT `detalle_patologia_ibfk_1` FOREIGN KEY (`id_patologia`) REFERENCES `patologia` (`id_patologia`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_patologia`
--

LOCK TABLES `detalle_patologia` WRITE;
/*!40000 ALTER TABLE `detalle_patologia` DISABLE KEYS */;
INSERT INTO `detalle_patologia` VALUES
(2,3),
(3,5),
(5,5),
(4,6);
/*!40000 ALTER TABLE `detalle_patologia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discapacidad`
--

DROP TABLE IF EXISTS `discapacidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `discapacidad` (
  `id_discapacidad` int(11) NOT NULL AUTO_INCREMENT,
  `id_solicitud_serv` int(11) NOT NULL,
  `tipo_discapacidad` enum('Física','Sensorial','Intelectual','Múltiple','Otro') NOT NULL,
  `disc_especifica` varchar(200) DEFAULT NULL,
  `diagnostico` varchar(255) NOT NULL,
  `grado` enum('Leve','Moderado','Grave') NOT NULL,
  `medicamentos` varchar(255) DEFAULT NULL,
  `habilidades_funcionales` varchar(255) NOT NULL,
  `requiere_asistencia` varchar(2) DEFAULT NULL,
  `dispositivo_asistencia` varchar(255) DEFAULT NULL,
  `observaciones` text NOT NULL,
  `recomendaciones` text DEFAULT NULL,
  `carnet_discapacidad` varchar(20) DEFAULT NULL,
  `fecha_creacion` date DEFAULT NULL,
  PRIMARY KEY (`id_discapacidad`),
  KEY `id_solicitud_serv` (`id_solicitud_serv`),
  CONSTRAINT `discapacidad_ibfk_1` FOREIGN KEY (`id_solicitud_serv`) REFERENCES `solicitud_de_servicio` (`id_solicitud_serv`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discapacidad`
--

LOCK TABLES `discapacidad` WRITE;
/*!40000 ALTER TABLE `discapacidad` DISABLE KEYS */;
INSERT INTO `discapacidad` VALUES
(1,5,'Física','Lentitud','Nada que agregar','Leve','Nada','Todas','No','Nada','Nada que agregar','Nada que agregar','123444','2026-09-27');
/*!40000 ALTER TABLE `discapacidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipos`
--

DROP TABLE IF EXISTS `equipos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipos` (
  `id_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo_equipo` int(11) NOT NULL,
  `id_servicios` int(11) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `serial` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `estado` enum('Nuevo','Bueno','Regular','Malo','En reparación') DEFAULT 'Bueno',
  `fecha_adquisicion` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_equipo`),
  KEY `id_tipo_equipo` (`id_tipo_equipo`),
  KEY `id_servicios` (`id_servicios`),
  CONSTRAINT `equipos_ibfk_1` FOREIGN KEY (`id_servicios`) REFERENCES `servicio` (`id_servicios`),
  CONSTRAINT `equipos_ibfk_2` FOREIGN KEY (`id_tipo_equipo`) REFERENCES `tipo_equipo` (`id_tipo_equipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipos`
--

LOCK TABLES `equipos` WRITE;
/*!40000 ALTER TABLE `equipos` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estado_cita`
--

DROP TABLE IF EXISTS `estado_cita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `estado_cita` (
  `id_estado` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `es_activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estado_cita`
--

LOCK TABLES `estado_cita` WRITE;
/*!40000 ALTER TABLE `estado_cita` DISABLE KEYS */;
INSERT INTO `estado_cita` VALUES
(1,'Pendiente','Cita agendada y pendiente de atención',1,'2025-12-14 16:02:38'),
(2,'Confirmada','Cita confirmada por el beneficiario',1,'2025-12-14 16:02:38'),
(3,'Atendida','Cita completada exitosamente',1,'2025-12-14 16:02:38'),
(4,'Cancelada','Cita cancelada',1,'2025-12-14 16:02:38'),
(5,'No asistió','Beneficiario no se presentó',1,'2025-12-14 16:02:38');
/*!40000 ALTER TABLE `estado_cita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos_calendario_personal`
--

DROP TABLE IF EXISTS `eventos_calendario_personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos_calendario_personal` (
  `id_evento` int(11) NOT NULL AUTO_INCREMENT,
  `id_empleado` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_evento`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos_calendario_personal`
--

LOCK TABLES `eventos_calendario_personal` WRITE;
/*!40000 ALTER TABLE `eventos_calendario_personal` DISABLE KEYS */;
INSERT INTO `eventos_calendario_personal` VALUES
(1,3,'Prueba','Hola','2026-09-30 15:32:00','2026-09-30');
/*!40000 ALTER TABLE `eventos_calendario_personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exoneracion`
--

DROP TABLE IF EXISTS `exoneracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `exoneracion` (
  `id_exoneracion` int(11) NOT NULL AUTO_INCREMENT,
  `id_solicitud_serv` int(11) DEFAULT NULL,
  `motivo` varchar(100) DEFAULT NULL,
  `otro_motivo` varchar(100) DEFAULT NULL,
  `direccion_carta` varchar(100) DEFAULT NULL,
  `direccion_estudiose` varchar(100) DEFAULT NULL,
  `carnet_discapacidad` varchar(100) NOT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_exoneracion`),
  KEY `id_solicitud_serv` (`id_solicitud_serv`),
  CONSTRAINT `exoneracion_ibfk_1` FOREIGN KEY (`id_solicitud_serv`) REFERENCES `solicitud_de_servicio` (`id_solicitud_serv`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exoneracion`
--

LOCK TABLES `exoneracion` WRITE;
/*!40000 ALTER TABLE `exoneracion` DISABLE KEYS */;
INSERT INTO `exoneracion` VALUES
(1,7,'Inscripción','No aplica','uploads/trabajo_social/exoneracion/carta_20260928_020025_6ab9ca390e40b.pdf','uploads/trabajo_social/exoneracion/estudiose/6aba7ff47b45f_estudioSE.pdf','123444','2026-09-27');
/*!40000 ALTER TABLE `exoneracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fames`
--

DROP TABLE IF EXISTS `fames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fames` (
  `id_fames` int(11) NOT NULL AUTO_INCREMENT,
  `id_solicitud_serv` int(11) DEFAULT NULL,
  `id_detalle_patologia` int(11) NOT NULL,
  `tipo_ayuda` varchar(100) NOT NULL,
  `otro_tipo` varchar(100) DEFAULT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_fames`),
  KEY `id_solicitud_serv` (`id_solicitud_serv`),
  KEY `id_detalle_patologia` (`id_detalle_patologia`),
  CONSTRAINT `fames_ibfk_1` FOREIGN KEY (`id_detalle_patologia`) REFERENCES `detalle_patologia` (`id_detalle_patologia`),
  CONSTRAINT `fames_ibfk_2` FOREIGN KEY (`id_solicitud_serv`) REFERENCES `solicitud_de_servicio` (`id_solicitud_serv`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fames`
--

LOCK TABLES `fames` WRITE;
/*!40000 ALTER TABLE `fames` DISABLE KEYS */;
/*!40000 ALTER TABLE `fames` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fichas_tecnicas`
--

DROP TABLE IF EXISTS `fichas_tecnicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fichas_tecnicas` (
  `id_ficha` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_ficha` varchar(100) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `id_empleado_responsable` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` date NOT NULL,
  `estatus` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_ficha`),
  KEY `id_servicio` (`id_servicio`),
  CONSTRAINT `fichas_tecnicas_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicios`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fichas_tecnicas`
--

LOCK TABLES `fichas_tecnicas` WRITE;
/*!40000 ALTER TABLE `fichas_tecnicas` DISABLE KEYS */;
/*!40000 ALTER TABLE `fichas_tecnicas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gestion_emb`
--

DROP TABLE IF EXISTS `gestion_emb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gestion_emb` (
  `id_gestion` int(11) NOT NULL AUTO_INCREMENT,
  `id_solicitud_serv` int(11) NOT NULL,
  `id_detalle_patologia` int(11) NOT NULL,
  `semanas_gest` int(11) NOT NULL,
  `codigo_patria` int(11) DEFAULT NULL,
  `serial_patria` int(11) DEFAULT NULL,
  `estado` varchar(20) NOT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_gestion`),
  KEY `id_solicitud_serv` (`id_solicitud_serv`),
  KEY `id_detalle_patologia` (`id_detalle_patologia`),
  CONSTRAINT `gestion_emb_ibfk_1` FOREIGN KEY (`id_detalle_patologia`) REFERENCES `detalle_patologia` (`id_detalle_patologia`),
  CONSTRAINT `gestion_emb_ibfk_2` FOREIGN KEY (`id_solicitud_serv`) REFERENCES `solicitud_de_servicio` (`id_solicitud_serv`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gestion_emb`
--

LOCK TABLES `gestion_emb` WRITE;
/*!40000 ALTER TABLE `gestion_emb` DISABLE KEYS */;
/*!40000 ALTER TABLE `gestion_emb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_inventario`
--

DROP TABLE IF EXISTS `historial_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_inventario` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `id_empleado` int(11) NOT NULL,
  `tipo_item` enum('mobiliario','equipo') NOT NULL,
  `id_item` int(11) NOT NULL,
  `tipo_movimiento` enum('asignacion','reubicacion','baja','modificacion') NOT NULL,
  `id_ficha` int(11) DEFAULT NULL,
  `id_servicio_anterior` int(11) DEFAULT NULL,
  `id_servicio_nuevo` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_historial`),
  KEY `id_ficha` (`id_ficha`),
  KEY `id_servicio_anterior` (`id_servicio_anterior`),
  KEY `id_servicio_nuevo` (`id_servicio_nuevo`),
  CONSTRAINT `historial_inventario_ibfk_1` FOREIGN KEY (`id_ficha`) REFERENCES `fichas_tecnicas` (`id_ficha`),
  CONSTRAINT `historial_inventario_ibfk_2` FOREIGN KEY (`id_servicio_anterior`) REFERENCES `servicio` (`id_servicios`),
  CONSTRAINT `historial_inventario_ibfk_3` FOREIGN KEY (`id_servicio_nuevo`) REFERENCES `servicio` (`id_servicios`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_inventario`
--

LOCK TABLES `historial_inventario` WRITE;
/*!40000 ALTER TABLE `historial_inventario` DISABLE KEYS */;
INSERT INTO `historial_inventario` VALUES
(1,1,'mobiliario',1,'asignacion',NULL,NULL,5,'Alta de mobiliario: Escritorio de madera','2026-09-29 14:13:02');
/*!40000 ALTER TABLE `historial_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario`
--

DROP TABLE IF EXISTS `horario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `horario` (
  `id_horario` int(11) NOT NULL AUTO_INCREMENT,
  `id_empleado` int(11) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  PRIMARY KEY (`id_horario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario`
--

LOCK TABLES `horario` WRITE;
/*!40000 ALTER TABLE `horario` DISABLE KEYS */;
INSERT INTO `horario` VALUES
(1,3,'Lunes','08:00:00','13:00:00'),
(2,3,'Martes','08:00:00','14:00:00');
/*!40000 ALTER TABLE `horario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `insumos`
--

DROP TABLE IF EXISTS `insumos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `insumos` (
  `id_insumo` int(11) NOT NULL AUTO_INCREMENT,
  `id_presentacion` int(11) NOT NULL,
  `nombre_insumo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `tipo_insumo` varchar(50) DEFAULT NULL,
  `fecha_vencimiento` date NOT NULL,
  `fecha_creacion` date NOT NULL,
  `cantidad` int(255) NOT NULL,
  `estatus` varchar(20) NOT NULL,
  PRIMARY KEY (`id_insumo`),
  KEY `id_presentacion` (`id_presentacion`),
  KEY `idx_insumos_estatus_cant` (`estatus`,`cantidad`),
  KEY `idx_insumos_vencimiento` (`fecha_vencimiento`),
  CONSTRAINT `insumos_ibfk_1` FOREIGN KEY (`id_presentacion`) REFERENCES `presentacion_insumo` (`id_presentacion`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insumos`
--

LOCK TABLES `insumos` WRITE;
/*!40000 ALTER TABLE `insumos` DISABLE KEYS */;
INSERT INTO `insumos` VALUES
(1,1,'Acetaminofén 500MG','Pastillas de acetaminofen','Medicamento','2027-02-19','2026-09-28',6,'Disponible');
/*!40000 ALTER TABLE `insumos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario_medico`
--

DROP TABLE IF EXISTS `inventario_medico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario_medico` (
  `id_inv_med` int(11) NOT NULL AUTO_INCREMENT,
  `id_insumo` int(11) DEFAULT NULL,
  `id_empleado` int(11) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tipo_movimiento` varchar(100) NOT NULL,
  `cantidad` int(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  PRIMARY KEY (`id_inv_med`),
  KEY `id_insumo` (`id_insumo`),
  CONSTRAINT `inventario_medico_ibfk_1` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario_medico`
--

LOCK TABLES `inventario_medico` WRITE;
/*!40000 ALTER TABLE `inventario_medico` DISABLE KEYS */;
INSERT INTO `inventario_medico` VALUES
(1,1,1,'2026-09-29 02:19:46','Registro',0,'Nuevo registro'),
(2,1,1,'2026-09-29 02:33:33','Entrada',10,'Compra de acetaminofen'),
(3,1,1,'2026-09-29 02:44:42','Salida',1,'Pérdida - Prueba'),
(4,1,1,'2026-09-29 16:18:53','Salida',2,'Salida por consulta médica #2'),
(5,1,1,'2026-09-29 19:42:07','Salida',1,'Salida por jornada médica (diagnóstico #1)');
/*!40000 ALTER TABLE `inventario_medico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario_mob`
--

DROP TABLE IF EXISTS `inventario_mob`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario_mob` (
  `id_inventario_mob` int(11) NOT NULL AUTO_INCREMENT,
  `id_mobiliario` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `fecha_movimiento` date NOT NULL,
  `tipo_movimiento` varchar(100) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  PRIMARY KEY (`id_inventario_mob`),
  KEY `id_mobiliario` (`id_mobiliario`),
  CONSTRAINT `inventario_mob_ibfk_1` FOREIGN KEY (`id_mobiliario`) REFERENCES `mobiliario` (`id_mobiliario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario_mob`
--

LOCK TABLES `inventario_mob` WRITE;
/*!40000 ALTER TABLE `inventario_mob` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventario_mob` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario_repuestos`
--

DROP TABLE IF EXISTS `inventario_repuestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario_repuestos` (
  `id_inventario` int(11) NOT NULL AUTO_INCREMENT,
  `id_repuesto` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `cantidad` varchar(100) NOT NULL,
  `tipo_movimiento` varchar(50) NOT NULL,
  `razon_movimiento` varchar(255) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_inventario`),
  KEY `id_repuesto` (`id_repuesto`),
  CONSTRAINT `inventario_repuestos_ibfk_1` FOREIGN KEY (`id_repuesto`) REFERENCES `repuestos_vehiculos` (`id_repuesto`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario_repuestos`
--

LOCK TABLES `inventario_repuestos` WRITE;
/*!40000 ALTER TABLE `inventario_repuestos` DISABLE KEYS */;
INSERT INTO `inventario_repuestos` VALUES
(1,1,1,'0','Registro','Alta inicial de repuesto','2026-09-29 22:47:12'),
(2,1,1,'5','Entrada','Compra','2026-09-29 22:47:34'),
(3,1,1,'1','Salida','Consumo en mantenimiento ID 1','2026-09-30 14:22:39');
/*!40000 ALTER TABLE `inventario_repuestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jornada_beneficiarios`
--

DROP TABLE IF EXISTS `jornada_beneficiarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jornada_beneficiarios` (
  `id_jornada_beneficiario` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_cedula` varchar(2) NOT NULL,
  `cedula` varchar(15) NOT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('Femenino','Masculino') DEFAULT NULL,
  `tipo_paciente` enum('Estudiante','Personal Obrero','Personal Docente','Personal Administrativo','Comunidad') DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `id_jornada` int(11) NOT NULL,
  `fecha_atencion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estatus` enum('Atendido','Cancelado') DEFAULT 'Atendido',
  PRIMARY KEY (`id_jornada_beneficiario`),
  KEY `id_jornada` (`id_jornada`),
  CONSTRAINT `jornada_beneficiarios_ibfk_1` FOREIGN KEY (`id_jornada`) REFERENCES `jornadas_medicas` (`id_jornada`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jornada_beneficiarios`
--

LOCK TABLES `jornada_beneficiarios` WRITE;
/*!40000 ALTER TABLE `jornada_beneficiarios` DISABLE KEYS */;
INSERT INTO `jornada_beneficiarios` VALUES
(1,'V','28281433','Roberth','Matos','2002-04-05','Masculino','Estudiante','04129298008','admin@gmail.com','Calle 53 y 54 con carrera 14',1,'2026-09-29 19:39:53','Atendido'),
(2,'V','30995937','Jesus','Matos','2005-11-13','Masculino','Personal Docente','04245304944','matosjesus464@gmail.com','Carrera 13 con calle 54',1,'2026-09-29 19:41:28','Atendido');
/*!40000 ALTER TABLE `jornada_beneficiarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jornada_diagnosticos`
--

DROP TABLE IF EXISTS `jornada_diagnosticos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jornada_diagnosticos` (
  `id_jornada_diagnostico` int(11) NOT NULL AUTO_INCREMENT,
  `id_jornada_beneficiario` int(11) NOT NULL,
  `id_empleado_medico` int(11) NOT NULL,
  `diagnostico` text NOT NULL,
  `tratamiento` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_diagnostico` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_jornada_diagnostico`),
  KEY `id_jornada_beneficiario` (`id_jornada_beneficiario`),
  CONSTRAINT `jornada_diagnosticos_ibfk_1` FOREIGN KEY (`id_jornada_beneficiario`) REFERENCES `jornada_beneficiarios` (`id_jornada_beneficiario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jornada_diagnosticos`
--

LOCK TABLES `jornada_diagnosticos` WRITE;
/*!40000 ALTER TABLE `jornada_diagnosticos` DISABLE KEYS */;
INSERT INTO `jornada_diagnosticos` VALUES
(1,2,1,'No tiene nada','Nada tiene','Nada que agregar','2026-09-29 19:42:07');
/*!40000 ALTER TABLE `jornada_diagnosticos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jornada_insumos`
--

DROP TABLE IF EXISTS `jornada_insumos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jornada_insumos` (
  `id_jornada_insumo` int(11) NOT NULL AUTO_INCREMENT,
  `id_jornada_diagnostico` int(11) NOT NULL,
  `id_insumo` int(11) NOT NULL,
  `cantidad_usada` int(11) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_jornada_insumo`),
  KEY `id_jornada_diagnostico` (`id_jornada_diagnostico`),
  KEY `id_insumo` (`id_insumo`),
  CONSTRAINT `jornada_insumos_ibfk_1` FOREIGN KEY (`id_jornada_diagnostico`) REFERENCES `jornada_diagnosticos` (`id_jornada_diagnostico`),
  CONSTRAINT `jornada_insumos_ibfk_2` FOREIGN KEY (`id_insumo`) REFERENCES `insumos` (`id_insumo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jornada_insumos`
--

LOCK TABLES `jornada_insumos` WRITE;
/*!40000 ALTER TABLE `jornada_insumos` DISABLE KEYS */;
INSERT INTO `jornada_insumos` VALUES
(1,1,1,1,'Insumo utilizado en jornada médica');
/*!40000 ALTER TABLE `jornada_insumos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jornadas_medicas`
--

DROP TABLE IF EXISTS `jornadas_medicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jornadas_medicas` (
  `id_jornada` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_jornada` varchar(100) NOT NULL,
  `tipo_jornada` varchar(50) NOT NULL,
  `aforo_maximo` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estatus` enum('Activa','Cancelada','Finalizada') DEFAULT 'Activa',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_jornada`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jornadas_medicas`
--

LOCK TABLES `jornadas_medicas` WRITE;
/*!40000 ALTER TABLE `jornadas_medicas` DISABLE KEYS */;
INSERT INTO `jornadas_medicas` VALUES
(1,'Jornada medica comunitaria','Médica Integral',2,'2026-09-30 15:38:00','2026-10-01 15:38:00','Uptaeb','Nada','Activa','2026-09-29 15:38:49');
/*!40000 ALTER TABLE `jornadas_medicas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_referencias`
--

DROP TABLE IF EXISTS `log_referencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_referencias` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_referencia` int(11) NOT NULL,
  `estado_anterior` enum('Pendiente','Aceptada','Rechazada') DEFAULT NULL,
  `estado_nuevo` enum('Pendiente','Aceptada','Rechazada') NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `fecha_accion` datetime DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id_log`),
  KEY `id_referencia` (`id_referencia`),
  CONSTRAINT `log_referencias_ibfk_1` FOREIGN KEY (`id_referencia`) REFERENCES `referencias` (`id_referencia`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_referencias`
--

LOCK TABLES `log_referencias` WRITE;
/*!40000 ALTER TABLE `log_referencias` DISABLE KEYS */;
INSERT INTO `log_referencias` VALUES
(1,1,'Pendiente','Aceptada',2,'2026-09-29 12:03:36','Referencia aceptada'),
(2,2,'Pendiente','Rechazada',1,'2026-10-01 09:10:45','No estoy de acuerdo');
/*!40000 ALTER TABLE `log_referencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mantenimiento_vehiculos`
--

DROP TABLE IF EXISTS `mantenimiento_vehiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mantenimiento_vehiculos` (
  `id_mantenimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_vehiculo` int(11) NOT NULL,
  `tipo` enum('Preventivo','Correctivo') NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_mantenimiento`),
  KEY `id_vehiculo` (`id_vehiculo`),
  CONSTRAINT `mantenimiento_vehiculos_ibfk_1` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mantenimiento_vehiculos`
--

LOCK TABLES `mantenimiento_vehiculos` WRITE;
/*!40000 ALTER TABLE `mantenimiento_vehiculos` DISABLE KEYS */;
INSERT INTO `mantenimiento_vehiculos` VALUES
(1,1,'Preventivo','2026-09-30','Probando');
/*!40000 ALTER TABLE `mantenimiento_vehiculos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobiliario`
--

DROP TABLE IF EXISTS `mobiliario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mobiliario` (
  `id_mobiliario` int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo_mobiliario` int(11) DEFAULT NULL,
  `id_servicios` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `estado` enum('Nuevo','Bueno','Regular','Malo','En reparación') DEFAULT 'Bueno',
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `fecha_adquisicion` date DEFAULT NULL,
  `descripcion_adicional` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id_mobiliario`),
  KEY `id_tipo_mobiliario` (`id_tipo_mobiliario`),
  KEY `id_servicios` (`id_servicios`),
  CONSTRAINT `mobiliario_ibfk_1` FOREIGN KEY (`id_servicios`) REFERENCES `servicio` (`id_servicios`),
  CONSTRAINT `mobiliario_ibfk_2` FOREIGN KEY (`id_tipo_mobiliario`) REFERENCES `tipo_mobiliario` (`id_tipo_mobiliario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobiliario`
--

LOCK TABLES `mobiliario` WRITE;
/*!40000 ALTER TABLE `mobiliario` DISABLE KEYS */;
INSERT INTO `mobiliario` VALUES
(1,1,5,10,'Bueno','Activo','2026-09-29 18:13:02','Ergo','T-200','Negro','2026-08-12','Escritorio','asdasasdasa');
/*!40000 ALTER TABLE `mobiliario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orientacion`
--

DROP TABLE IF EXISTS `orientacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orientacion` (
  `id_orientacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_solicitud_serv` int(11) DEFAULT NULL,
  `motivo_orientacion` mediumtext DEFAULT NULL,
  `descripcion_orientacion` mediumtext DEFAULT NULL,
  `obs_adic_orientacion` mediumtext DEFAULT NULL,
  `indicaciones_orientacion` mediumtext DEFAULT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_orientacion`),
  KEY `id_solicitud_serv` (`id_solicitud_serv`),
  CONSTRAINT `orientacion_ibfk_1` FOREIGN KEY (`id_solicitud_serv`) REFERENCES `solicitud_de_servicio` (`id_solicitud_serv`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orientacion`
--

LOCK TABLES `orientacion` WRITE;
/*!40000 ALTER TABLE `orientacion` DISABLE KEYS */;
INSERT INTO `orientacion` VALUES
(1,4,'Motivo','Descripcion','Observaciones','indicaciones','2026-09-27');
/*!40000 ALTER TABLE `orientacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patologia`
--

DROP TABLE IF EXISTS `patologia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `patologia` (
  `id_patologia` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_patologia` varchar(100) DEFAULT NULL,
  `tipo_patologia` varchar(100) NOT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_patologia`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patologia`
--

LOCK TABLES `patologia` WRITE;
/*!40000 ALTER TABLE `patologia` DISABLE KEYS */;
INSERT INTO `patologia` VALUES
(1,'Sin patología médica','Médica','2025-11-12'),
(2,'Sin patología psicológica','Psicológica','2025-11-12'),
(3,'Sin patología general','General','2025-11-12'),
(4,'Leucemia','General','2026-01-20'),
(5,'Gripe','General','2026-02-02'),
(6,'Ansiedad','General','2026-02-02');
/*!40000 ALTER TABLE `patologia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pnf`
--

DROP TABLE IF EXISTS `pnf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pnf` (
  `id_pnf` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_pnf` varchar(100) DEFAULT NULL,
  `estatus` tinyint(1) DEFAULT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_pnf`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pnf`
--

LOCK TABLES `pnf` WRITE;
/*!40000 ALTER TABLE `pnf` DISABLE KEYS */;
INSERT INTO `pnf` VALUES
(1,'PNF Administración',1,'2024-11-12'),
(2,'PNF Contaduría Pública',1,'2024-11-12'),
(3,'PNF Informática',1,'2024-11-12'),
(4,'PNF Higiene y Seguridad Laboral',1,'2024-11-12'),
(5,'PNF Deporte',1,'2024-11-12'),
(6,'PNF Turismo',1,'2024-11-12'),
(7,'PNF Ciencias de la Información',1,'2024-11-12'),
(8,'PNF Sistemas de Calidad y Ambiente',1,'2024-11-12'),
(9,'PNF Agroalimentación',1,'2024-11-12'),
(10,'PNF Distribución y Logística',1,'2024-11-12'),
(11,'PNF Materiales Industriales',1,'2024-11-26'),
(12,'PNF Procesos Químicos',1,'2024-11-26'),
(13,'PNF Sistemas informáticos',0,'2026-02-02');
/*!40000 ALTER TABLE `pnf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presentacion_insumo`
--

DROP TABLE IF EXISTS `presentacion_insumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `presentacion_insumo` (
  `id_presentacion` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_presentacion` varchar(100) DEFAULT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_presentacion`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presentacion_insumo`
--

LOCK TABLES `presentacion_insumo` WRITE;
/*!40000 ALTER TABLE `presentacion_insumo` DISABLE KEYS */;
INSERT INTO `presentacion_insumo` VALUES
(1,'Pastillas','2025-11-12'),
(2,'Capsulas','2025-11-12'),
(3,'Polvo','2025-11-12'),
(4,'Líquida','2025-11-12'),
(5,'Otro tipo','2025-11-12'),
(6,'Gaseosas','2026-02-02');
/*!40000 ALTER TABLE `presentacion_insumo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_documento` enum('V','E','J','G') NOT NULL,
  `num_documento` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(25) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `estatus` varchar(10) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_proveedor`),
  UNIQUE KEY `idx_proveedores_documento_unique` (`num_documento`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES
(1,'V','282814331','Repuestos Barquisimeto','04129990909','proveedor@gmail.es','Av. Venezuela con calle 15','Activo','2026-09-29 22:38:59');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referencias`
--

DROP TABLE IF EXISTS `referencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `referencias` (
  `id_referencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_beneficiario` int(11) NOT NULL,
  `id_empleado_origen` int(11) NOT NULL,
  `id_servicio_origen` int(11) NOT NULL,
  `id_empleado_destino` int(11) DEFAULT NULL,
  `id_servicio_destino` int(11) NOT NULL,
  `fecha_referencia` timestamp NOT NULL DEFAULT current_timestamp(),
  `motivo` varchar(255) DEFAULT NULL,
  `estado` enum('Pendiente','Aceptada','Rechazada') DEFAULT 'Pendiente',
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id_referencia`),
  KEY `id_beneficiario` (`id_beneficiario`),
  KEY `id_servicio_origen` (`id_servicio_origen`),
  KEY `id_servicio_destino` (`id_servicio_destino`),
  CONSTRAINT `referencias_ibfk_1` FOREIGN KEY (`id_beneficiario`) REFERENCES `beneficiario` (`id_beneficiario`),
  CONSTRAINT `referencias_ibfk_2` FOREIGN KEY (`id_servicio_destino`) REFERENCES `servicio` (`id_servicios`),
  CONSTRAINT `referencias_ibfk_3` FOREIGN KEY (`id_servicio_origen`) REFERENCES `servicio` (`id_servicios`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referencias`
--

LOCK TABLES `referencias` WRITE;
/*!40000 ALTER TABLE `referencias` DISABLE KEYS */;
INSERT INTO `referencias` VALUES
(1,1,3,1,2,3,'2026-09-29 16:02:34','Prueba de referencia','Aceptada','Detalle alguno'),
(2,1,3,1,1,8,'2026-10-01 13:08:16','Prueba de referencia','Rechazada','Nada que agregar');
/*!40000 ALTER TABLE `referencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repuestos_mantenimiento`
--

DROP TABLE IF EXISTS `repuestos_mantenimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `repuestos_mantenimiento` (
  `id_repuestos_inv` int(11) NOT NULL AUTO_INCREMENT,
  `id_mantenimiento` int(11) NOT NULL,
  `id_repuesto` int(11) NOT NULL,
  `cantidad` varchar(255) NOT NULL,
  PRIMARY KEY (`id_repuestos_inv`),
  KEY `id_mantenimiento` (`id_mantenimiento`),
  KEY `id_repuesto` (`id_repuesto`),
  CONSTRAINT `repuestos_mantenimiento_ibfk_1` FOREIGN KEY (`id_mantenimiento`) REFERENCES `mantenimiento_vehiculos` (`id_mantenimiento`),
  CONSTRAINT `repuestos_mantenimiento_ibfk_2` FOREIGN KEY (`id_repuesto`) REFERENCES `repuestos_vehiculos` (`id_repuesto`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repuestos_mantenimiento`
--

LOCK TABLES `repuestos_mantenimiento` WRITE;
/*!40000 ALTER TABLE `repuestos_mantenimiento` DISABLE KEYS */;
INSERT INTO `repuestos_mantenimiento` VALUES
(1,1,1,'1');
/*!40000 ALTER TABLE `repuestos_mantenimiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repuestos_vehiculos`
--

DROP TABLE IF EXISTS `repuestos_vehiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `repuestos_vehiculos` (
  `id_repuesto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `cantidad` int(11) DEFAULT 0,
  `id_proveedor` int(10) DEFAULT NULL,
  `fecha_creacion` date DEFAULT NULL,
  `estatus` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id_repuesto`),
  KEY `id_proveedor` (`id_proveedor`),
  CONSTRAINT `repuestos_vehiculos_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repuestos_vehiculos`
--

LOCK TABLES `repuestos_vehiculos` WRITE;
/*!40000 ALTER TABLE `repuestos_vehiculos` DISABLE KEYS */;
INSERT INTO `repuestos_vehiculos` VALUES
(1,'Filtro de aceite Yutong','Nada que agregar',4,1,'2026-09-29','Disponible');
/*!40000 ALTER TABLE `repuestos_vehiculos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rutas`
--

DROP TABLE IF EXISTS `rutas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rutas` (
  `id_ruta` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_ruta` varchar(100) NOT NULL,
  `trayectoria` text DEFAULT NULL,
  `tipo_ruta` varchar(100) NOT NULL,
  `horario_salida` time DEFAULT NULL,
  `horario_llegada` time DEFAULT NULL,
  `punto_partida` varchar(255) DEFAULT NULL,
  `punto_destino` varchar(255) DEFAULT NULL,
  `estatus` enum('Activa','Inactiva') DEFAULT 'Activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_ruta`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rutas`
--

LOCK TABLES `rutas` WRITE;
/*!40000 ALTER TABLE `rutas` DISABLE KEYS */;
INSERT INTO `rutas` VALUES
(1,'Ruta 1 - UPTAEB','Metropolis','Urbana','08:00:00','08:30:00','UPTAEB','Terminal de Barquisimeto','Activa','2026-09-30 14:36:53');
/*!40000 ALTER TABLE `rutas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servicio`
--

DROP TABLE IF EXISTS `servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicio` (
  `id_servicios` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_serv` varchar(50) DEFAULT NULL,
  `estatus` tinyint(1) DEFAULT NULL,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_servicios`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicio`
--

LOCK TABLES `servicio` WRITE;
/*!40000 ALTER TABLE `servicio` DISABLE KEYS */;
INSERT INTO `servicio` VALUES
(1,'Psicologia',1,'2024-11-12'),
(2,'Medicina',1,'2024-11-12'),
(3,'Orientacion',1,'2024-11-12'),
(4,'Trabajo Social',1,'2024-11-12'),
(5,'Discapacidad',1,'2024-11-12'),
(6,'General',1,'2024-11-20'),
(7,'Comedor',1,'2024-11-21'),
(8,'Gerente',1,'2025-04-17'),
(9,'Transporte',1,'2025-04-19'),
(10,'Mantenimiento',0,'2026-02-02');
/*!40000 ALTER TABLE `servicio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitud_de_servicio`
--

DROP TABLE IF EXISTS `solicitud_de_servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitud_de_servicio` (
  `id_solicitud_serv` int(11) NOT NULL AUTO_INCREMENT,
  `id_servicios` int(11) NOT NULL,
  `id_beneficiario` int(11) DEFAULT NULL,
  `id_empleado` int(11) NOT NULL,
  PRIMARY KEY (`id_solicitud_serv`),
  KEY `id_beneficiario` (`id_beneficiario`),
  KEY `id_servicios` (`id_servicios`),
  CONSTRAINT `solicitud_de_servicio_ibfk_1` FOREIGN KEY (`id_beneficiario`) REFERENCES `beneficiario` (`id_beneficiario`),
  CONSTRAINT `solicitud_de_servicio_ibfk_2` FOREIGN KEY (`id_servicios`) REFERENCES `servicio` (`id_servicios`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_de_servicio`
--

LOCK TABLES `solicitud_de_servicio` WRITE;
/*!40000 ALTER TABLE `solicitud_de_servicio` DISABLE KEYS */;
INSERT INTO `solicitud_de_servicio` VALUES
(2,1,1,1),
(3,2,1,1),
(4,3,1,1),
(5,5,1,1),
(6,4,1,1),
(7,4,1,1),
(8,1,1,3),
(9,2,1,1);
/*!40000 ALTER TABLE `solicitud_de_servicio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_equipo`
--

DROP TABLE IF EXISTS `tipo_equipo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_equipo` (
  `id_tipo_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estatus` tinyint(1) DEFAULT 1,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_tipo_equipo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_equipo`
--

LOCK TABLES `tipo_equipo` WRITE;
/*!40000 ALTER TABLE `tipo_equipo` DISABLE KEYS */;
INSERT INTO `tipo_equipo` VALUES
(1,'Monitor LCD','Monitor ACER',1,'2025-11-12'),
(2,'Video Beam HP','Compatible con HDMi',1,'2026-02-02');
/*!40000 ALTER TABLE `tipo_equipo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_mobiliario`
--

DROP TABLE IF EXISTS `tipo_mobiliario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_mobiliario` (
  `id_tipo_mobiliario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estatus` tinyint(1) DEFAULT 1,
  `fecha_creacion` date NOT NULL,
  PRIMARY KEY (`id_tipo_mobiliario`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_mobiliario`
--

LOCK TABLES `tipo_mobiliario` WRITE;
/*!40000 ALTER TABLE `tipo_mobiliario` DISABLE KEYS */;
INSERT INTO `tipo_mobiliario` VALUES
(1,'Escritorio de madera','Escritorio de madera compacto',1,'2025-11-12'),
(2,'Silla giratoria','Silla que gira 360 grado',1,'2026-02-02');
/*!40000 ALTER TABLE `tipo_mobiliario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehiculos`
--

DROP TABLE IF EXISTS `vehiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehiculos` (
  `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT,
  `placa` varchar(20) NOT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `tipo` enum('Autobús','Camioneta','Automóvil') NOT NULL,
  `fecha_adquisicion` date DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Mantenimiento') DEFAULT 'Activo',
  PRIMARY KEY (`id_vehiculo`),
  UNIQUE KEY `idx_vehiculos_placa_unique` (`placa`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehiculos`
--

LOCK TABLES `vehiculos` WRITE;
/*!40000 ALTER TABLE `vehiculos` DISABLE KEYS */;
INSERT INTO `vehiculos` VALUES
(1,'ABC1234','Yutong AZZC2','Autobús','2026-09-15','Mantenimiento'),
(2,'ABC1233','Ford Fiesta','Automóvil','2026-09-22','Activo');
/*!40000 ALTER TABLE `vehiculos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `vw_beneficiarios_completos`
--

DROP TABLE IF EXISTS `vw_beneficiarios_completos`;
/*!50001 DROP VIEW IF EXISTS `vw_beneficiarios_completos`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `vw_beneficiarios_completos` AS SELECT
 1 AS `id_beneficiario`,
  1 AS `nombres`,
  1 AS `apellidos`,
  1 AS `nombre_completo`,
  1 AS `tipo_cedula`,
  1 AS `cedula`,
  1 AS `cedula_completa`,
  1 AS `identificacion_formateada`,
  1 AS `fecha_nac`,
  1 AS `telefono`,
  1 AS `correo`,
  1 AS `genero`,
  1 AS `genero_texto`,
  1 AS `direccion`,
  1 AS `seccion`,
  1 AS `estatus`,
  1 AS `fecha_creacion`,
  1 AS `id_pnf`,
  1 AS `nombre_pnf` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `vw_beneficiarios_completos`
--

/*!50001 DROP VIEW IF EXISTS `vw_beneficiarios_completos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_beneficiarios_completos` AS select `b`.`id_beneficiario` AS `id_beneficiario`,`b`.`nombres` AS `nombres`,`b`.`apellidos` AS `apellidos`,concat(`b`.`nombres`,' ',coalesce(`b`.`apellidos`,'')) AS `nombre_completo`,`b`.`tipo_cedula` AS `tipo_cedula`,`b`.`cedula` AS `cedula`,concat(`b`.`tipo_cedula`,'-',`b`.`cedula`) AS `cedula_completa`,concat(`b`.`nombres`,' ',`b`.`apellidos`,' (',`b`.`tipo_cedula`,' - ',`b`.`cedula`,')') AS `identificacion_formateada`,`b`.`fecha_nac` AS `fecha_nac`,`b`.`telefono` AS `telefono`,`b`.`correo` AS `correo`,`b`.`genero` AS `genero`,if(`b`.`genero` = 'M','Masculino','Femenino') AS `genero_texto`,`b`.`direccion` AS `direccion`,`b`.`seccion` AS `seccion`,`b`.`estatus` AS `estatus`,`b`.`fecha_creacion` AS `fecha_creacion`,`p`.`id_pnf` AS `id_pnf`,`p`.`nombre_pnf` AS `nombre_pnf` from (`beneficiario` `b` left join `pnf` `p` on(`b`.`id_pnf` = `p`.`id_pnf`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-01 12:26:59
