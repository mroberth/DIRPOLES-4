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
-- Current Database: `dirpoles_business`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `dirpoles_business` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci */;

USE `dirpoles_business`;

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaciones_rutas`
--

LOCK TABLES `asignaciones_rutas` WRITE;
/*!40000 ALTER TABLE `asignaciones_rutas` DISABLE KEYS */;
INSERT INTO `asignaciones_rutas` VALUES
(2,2,3,4,'2025-05-10','Inactiva'),
(3,3,4,20,'2025-05-12','Inactiva'),
(4,2,3,4,'2026-10-01','Activa'),
(5,3,4,20,'2026-10-02','Activa'),
(6,4,6,4,'2026-10-05','Activa');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `becas`
--

LOCK TABLES `becas` WRITE;
/*!40000 ALTER TABLE `becas` DISABLE KEYS */;
INSERT INTO `becas` VALUES
(8,137,'0102012345678901','uploads/trabajo_social/planilla_beca_1.pdf','0102','2024-11-28'),
(9,138,'0134098765432109','uploads/trabajo_social/planilla_beca_2.pdf','0134','2025-01-15'),
(10,139,'0108112233445566','uploads/trabajo_social/planilla_beca_3.pdf','0108','2025-03-10'),
(11,140,'0105665544332211','uploads/trabajo_social/planilla_beca_4.pdf','0105','2025-05-20'),
(12,141,'0191998877665544','uploads/trabajo_social/planilla_beca_5.pdf','0191','2025-09-12'),
(13,142,'0102443322110099','uploads/trabajo_social/planilla_beca_6.pdf','0102','2026-02-14');
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
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beneficiario`
--

LOCK TABLES `beneficiario` WRITE;
/*!40000 ALTER TABLE `beneficiario` DISABLE KEYS */;
INSERT INTO `beneficiario` VALUES
(1,5,'3103-B','Jesus','Matos','V','30995937','2005-11-13','04245304944','matosjesus464@gmail.com','M','Carrera 13 con calle 54.',1,'2026-10-01 23:56:23'),
(2,3,'2112-B','Fernando','Melendez','V','30995938','2002-10-01','04129293008','Fernando@gmail.com','M','Calle 10 de pueblo nuevo',1,'2026-10-07 04:00:00'),
(3,3,'1104-M','Andrea Carolina','Rodríguez Silva','V','21381247','2003-04-18','04126183473','andrea.rodriguez23@gmail.com','F','Av. 5 de Julio con calle 20, Urb. Don Rómulo',1,'2026-03-14 04:00:00'),
(4,1,'2207-C','Luis Enrique','Mendoza Torres','V','18390420','1999-09-02','04146197264','luis.mendoza41@gmail.com','M','Carrera 19 con calle 44, Barrio Belén',1,'2026-01-22 04:00:00'),
(5,2,'3105-M','Gabriela Patricia','Suárez Rivas','V','26399593','2001-12-11','04166211055','gabriela.suarez58@hotmail.com','F','Urb. Buen Pastor, calle 9 con Av. Las Américas',1,'2025-11-30 04:00:00'),
(6,4,'1403-J','José Gregorio','Hernández Blanco','V','23408766','1996-06-25','04226224846','jose.hernandez67@gmail.com','M','Calle 4 con carrera 15, Sector Centro',1,'2026-02-09 04:00:00'),
(7,3,'1208-B','Daniela Fernanda','Márquez Rojas','V','29417939','2004-02-07','04246238637','daniela.marquez34@gmail.com','F','Urb. Altamora, Av. Libertador con calle 12',0,'2026-10-08 16:32:29'),
(8,5,'2506-U','Carlos Andrés','Salazar Mora','V','16427112','2000-11-19','04266252428','carlos.salazar89@gmail.com','M','Sector El Tránsito, calle 32 con carrera 7',1,'2026-05-16 04:00:00'),
(9,6,'3301-M','Yorgelis Andreína','Pérez Cordero','V','24436285','2002-08-14','04126266219','yorgelis.perez12@yahoo.es','F','Urb. La Carreta, Av. Santiago Rodríguez con calle 6',1,'2026-06-21 04:00:00'),
(10,7,'1602-C','Jesús Alexander','Páez Iriarte','V','14445458','1998-03-30','04146280010','jesus.paez45@gmail.com','M','Barrio La Sequita, carrera 21 con calle 29',1,'2026-07-08 04:00:00'),
(11,8,'2104-B','Luisana Carolina','Espinoza Fuentes','V','27454631','2003-10-05','04166293801','luisana.espinoza71@gmail.com','F','Av. Venezuela con calle 15, Urb. 15 de Enero',0,'2026-10-08 16:32:29'),
(12,3,'1105-M','Cristian David','Bermúdez Aponte','V','30463804','2005-01-23','04226307592','cristian.bermudez19@gmail.com','M','Calle 2 con carrera 24, Sector Obrero',1,'2026-10-04 04:00:00'),
(13,9,'2703-U','Karla Natalie','Jiménez Urdaneta','V','19472977','1997-07-16','04246321383','karla.jimenez36@gmail.com','F','Urb. Daniel Camacho, calle 18 con Av. Carabobo',1,'2026-02-27 04:00:00'),
(14,10,'3402-C','Brayan Alexander','Rincón Segovia','V','28482150','2004-05-09','04266335174','brayan.rincon88@yahoo.es','M','Barrio Tamarindo, carrera 3 con calle 40',1,'2026-03-31 04:00:00'),
(15,2,'1306-M','María Gabriela','Contreras Perdomo','V','22491323','2001-03-21','04126348965','maria.contreras52@gmail.com','F','Av. 9 con calle 10, Urb. El Tostao',1,'2026-04-18 04:00:00'),
(16,11,'2601-B','Andrés Feliciano','Quintana Velásquez','V','17500496','1995-12-04','04146362756','andres.quintana27@gmail.com','M','Calle 33 con carrera 12, Barrio San José',0,'2026-01-09 04:00:00'),
(17,3,'1507-J','Franyeli Jackeline','Torres Orellana','V','25509669','2002-09-28','04166376547','franyeli.torres43@gmail.com','F','Urb. Santa Rosa, Av. Paramacay con calle 5',1,'2026-05-05 04:00:00'),
(18,5,'2304-U','Kelvin Jhonatan','Guzmán Barrios','V','31518842','2006-02-12','04226390338','kelvin.guzman64@gmail.com','M','Sector Pío Tamayo, calle 25 con carrera 9',1,'2026-06-14 04:00:00'),
(19,1,'1803-M','Yaneth del Carmen','Piñate Gutiérrez','V','15528015','1994-08-07','04246404129','yaneth.pinate31@gmail.com','F','Barrio El Maní, carrera 14 con calle 26',1,'2026-07-25 04:00:00'),
(20,12,'3502-C','Enrique Jesús','Alvarado Núñez','V','20537188','1999-04-26','04266417920','enrique.alvarado77@gmail.com','M','Av. Los Leones con calle 3, Urb. Javier Este',1,'2026-08-30 04:00:00'),
(21,7,'1204-B','Stefany Paola','Rangel Medina','V','26546361','2003-06-13','04126431711','stefany.rangel55@gmail.com','F','Urb. 6 de Marzo, calle 6 con carrera 11',0,'2026-10-08 16:33:58'),
(22,4,'2405-M','Javier Alejandro','Montiel Cárdenas','V','13555534','2000-10-31','04146445502','javier.montiel92@gmail.com','M','Calle 12 con carrera 22, Sector Obrero',1,'2026-04-29 04:00:00'),
(23,6,'1708-U','Eliana Margarita','Rojas Farías','V','18564707','1993-01-17','04166459293','eliana.rojas14@yahoo.es','F','Urb. Cristo Rey, Av. Carabobo con calle 19',1,'2026-02-16 04:00:00'),
(24,10,'3601-C','Omar Daniel','Fuentes Bastidas','V','24573880','1997-11-08','04226473084','omar.fuentes61@gmail.com','M','Barrio La Loma, carrera 5 con calle 30',0,'2026-03-06 04:00:00'),
(25,3,'1406-M','Rosangely Yuraima','Hernández Pino','V','29583053','2004-12-02','04246486875','rosangely.hernandez48@gmail.com','F','Calle 20 con carrera 16, Urb. Sucre',1,'2026-05-23 04:00:00'),
(26,8,'2209-B','Luis David','Castillo Aponte','V','16592226','2002-07-19','04266500666','luis.castillo25@gmail.com','M','Av. 15 con calle 7, Urb. La Carreta',1,'2026-06-02 04:00:00'),
(27,9,'3102-J','Yuraima Cristina','Velásquez Márquez','V','21601399','2001-05-24','04126514457','yuraima.velasquez39@gmail.com','F','Sector Alto de la Cruz, calle 41 con carrera 8',1,'2026-07-13 04:00:00'),
(28,5,'1905-U','Yohander Enrique','Sánchez Ríos','V','30610572','2005-08-21','04146528248','yohander.sanchez83@gmail.com','M','Urb. El Tostao, calle 11 con Av. 5 de Julio',1,'2026-08-06 04:00:00'),
(29,2,'1604-M','Marielena Josefina','Colmenares Omaña','V','14619745','1996-02-28','04166542039','marielena.colmenares57@gmail.com','F','Calle 27 con carrera 13, Barrio Belén',1,'2026-09-22 04:00:00'),
(30,11,'2503-C','Deivis Alexander','Blanco Márquez','V','27628918','1998-09-10','04226555830','deivis.blanco46@gmail.com','M','Carrera 2 con calle 35, Sector Centro',1,'2026-01-15 04:00:00'),
(31,3,'1307-B','Yuleimy Gabriela','Salas Ibarra','V','23638091','2003-11-06','04246569621','yuleimy.salas21@gmail.com','F','Urb. 15 de Enero, calle 14 con carrera 20',1,'2026-02-23 04:00:00'),
(32,1,'2801-U','José Antonio','Méndez Caldera','V','17647264','1992-06-15','04266583412','jose.mendez95@gmail.com','M','Av. Carabobo con calle 22, Urb. Daniel Camacho',0,'2025-12-14 04:00:00'),
(33,12,'3406-M','Endrina Patricia','Quintero León','V','19656437','2000-04-03','04126597203','endrina.quintero33@gmail.com','F','Calle 8 con carrera 6, Barrio El Recreo',1,'2026-04-11 04:00:00'),
(34,10,'1206-C','Yonathan David','Aponte Gil','V','31665610','2006-01-29','04146610994','yonathan.aponte74@gmail.com','M','Carrera 17 con calle 28, Barrio La Sequita',1,'2026-05-30 04:00:00'),
(35,4,'2102-M','Ingrid Carolina','Marcano Terán','V','22674783','1999-10-14','04166624785','ingrid.marcano18@gmail.com','F','Av. Libertador con calle 17, Urb. Sucre',1,'2026-06-27 04:00:00'),
(36,7,'1705-J','Jesús Daniel','Rodríguez Aponte','V','26683956','2004-07-07','04226638576','jesus.rodriguez53@gmail.com','M','Calle 30 con carrera 4, Sector Obrero',1,'2026-07-19 04:00:00'),
(37,6,'2602-U','María Elena','Linares Bracho','V','13693129','1991-03-12','04246652367','maria.linares29@gmail.com','F','Urb. El Maní, calle 26 con carrera 15',1,'2026-08-24 04:00:00'),
(38,8,'3204-B','Oscar Alejandro','Paredes Inojosa','V','18702302','1995-12-19','04266666158','oscar.paredes47@gmail.com','M','Av. Las Américas con calle 1, Urb. Buen Pastor',1,'2026-10-06 04:00:00'),
(39,9,'1503-M','Yusbely Paola','Urdaneta Rangel','V','25711475','2002-05-27','04126679949','yusbely.urdaneta66@gmail.com','F','Calle 16 con carrera 10, Barrio Tamarindo',1,'2026-01-28 04:00:00'),
(40,2,'2306-C','Manuel Enrique','Villarroel Ponce','V','15720648','1990-08-03','04146693740','manuel.villarroel81@gmail.com','M','Carrera 9 con calle 21, Urb. Don Rómulo',0,'2026-02-05 04:00:00'),
(41,3,'1804-U','Lisbeth Carolina','Nava Cárdenas','V','28729821','2005-09-17','04166707531','lisbeth.nava24@gmail.com','F','Av. 9 de Febrero con calle 5, Urb. Javier Este',1,'2026-03-19 04:00:00'),
(42,11,'2901-B','Franklin Jesús','Salcedo Márquez','V','20738994','1996-01-05','04226721322','franklin.salcedo90@gmail.com','M','Calle 24 con carrera 19, Barrio San José',1,'2026-04-25 04:00:00'),
(43,5,'1408-M','Naireth Alexandra','García Uzcátegui','V','24748167','2003-12-08','04246735113','naireth.garcia37@gmail.com','F','Urb. Santa Rosa, calle 13 con Av. Paramacay',1,'2026-05-12 04:00:00'),
(44,10,'3602-C','Eduard Jhosmel','Pérez Iriarte','V','29757340','2006-06-22','04266748904','eduard.perez11@gmail.com','M','Carrera 23 con calle 7, Barrio El Recreo',1,'2026-06-08 04:00:00'),
(45,1,'2705-M','Yelitza Maribel','Sequera Ojeda','V','16766513','1998-02-16','04126762695','yelitza.sequera62@gmail.com','F','Av. 5 de Julio con calle 23, Urb. 6 de Marzo',1,'2026-07-31 04:00:00'),
(46,12,'2407-U','Luis Alfredo','Carrasco Mendoza','V','23775686','1993-10-29','04146776486','luis.carrasco44@gmail.com','M','Calle 19 con carrera 25, Sector Pío Tamayo',0,'2026-08-18 04:00:00'),
(48,3,'1607-M','Kleiver Alexander','Ríos Páez','V','30794032','2004-11-24','04226804068','kleiver.rios85@gmail.com','M','Carrera 6 con calle 31, Barrio La Loma',1,'2026-01-06 04:00:00'),
(49,7,'3302-J','Marianella Alexandra','Gil Ponce','V','22803205','2001-08-30','04246817859','marianella.gil16@gmail.com','F','Calle 10 con carrera 18, Urb. El Maní',1,'2026-02-19 04:00:00'),
(50,4,'2803-C','Yohan David','Montoya Salas','V','26812378','2005-04-09','04266831650','yohan.montoya59@gmail.com','M','Av. 15 con calle 4, Barrio Belén',1,'2026-03-08 04:00:00'),
(51,2,'2106-M','Ana Gabriela','Betancourt Silva','V','19821551','1994-05-20','04126845441','ana.betancourt79@gmail.com','F','Calle 17 con carrera 11, Urb. Sucre',1,'2026-04-07 04:00:00'),
(52,5,'1304-U','Daren Jhonatan','Gutiérrez Peña','V','31830724','2006-09-03','04146859232','daren.gutierrez42@gmail.com','M','Sector Alto de la Cruz, carrera 2 con calle 42',1,'2026-05-19 04:00:00'),
(53,9,'2605-C','Marielys Jackeline','Farías Mora','V','18839897','2002-01-31','04166873023','marielys.farias28@gmail.com','F','Av. Venezuela con calle 8, Urb. 15 de Enero',1,'2026-06-16 04:00:00'),
(54,8,'3405-M','Ángel Rafael','Ochoa Núñez','V','15849070','1999-12-13','04226886814','angel.ochoa54@gmail.com','M','Carrera 16 con calle 27, Sector Obrero',1,'2026-07-27 04:00:00'),
(55,10,'2208-U','Yarimar Alejandra','Nieto Briñez','V','27858243','2000-06-26','04246900605','yarimar.nieto35@gmail.com','F','Urb. El Tostao, calle 7 con Av. 9',1,'2026-08-14 04:00:00'),
(56,3,'1509-B','Kevin Josué','Márquez Rincón','V','16867416','2003-03-15','04266914396','kevin.marquez68@gmail.com','M','Calle 5 con carrera 29, Barrio El Maní',1,'2026-10-05 04:00:00'),
(57,1,'2904-M','Greisbel Andrea','Molina Cárdenas','V','21876589','1996-10-02','04126928187','greisbel.molina51@gmail.com','F','Av. Carabobo con calle 30, Urb. Daniel Camacho',0,'2026-01-19 04:00:00'),
(58,11,'3107-C','Yeferson Alexander','Urbina Lara','V','30885762','2004-08-25','04146941978','yeferson.urbina87@gmail.com','M','Carrera 4 con calle 36, Barrio San José',1,'2026-02-11 04:00:00'),
(59,12,'1706-U','Milagros Carolina','Senmache Ríos','E','1025487965','2001-11-09','04166955769','milagros.senmache26@gmail.com','F','Calle 11 con carrera 33, Urb. La Carreta',1,'2026-03-27 04:00:00'),
(60,6,'2309-M','Erick Darío','Silva Nieves','V','19904108','1998-05-18','04226969560','erick.silva63@gmail.com','M','Av. 9 con calle 14, Barrio Tamarindo',1,'2026-04-15 04:00:00'),
(61,4,'2408-J','Erika Jackeline','Vera Cárdenas','V','23913281','1999-02-27','04246983351','erika.vera73@gmail.com','F','Calle 22 con carrera 3, Sector Centro',1,'2026-05-26 04:00:00'),
(63,3,'2103-B','Ligia','Duran','V','14335760','1988-10-09','04245952471','Duranligia.pnfi@gmail.com','F','Pueblo nuevo',1,'2026-10-09 04:00:00'),
(66,1,'1101','Otro','Beneficiario','V','30000001','2003-01-01','04144959176','correo.distinto.benef@test.com','F','Lara',1,'2026-10-18 04:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cita`
--

LOCK TABLES `cita` WRITE;
/*!40000 ALTER TABLE `cita` DISABLE KEYS */;
INSERT INTO `cita` VALUES
(1,'2026-09-21','12:00:00',1,3,3,'2026-09-17'),
(2,'2026-10-12','09:30:00',2,3,1,'2026-10-07'),
(3,'2026-10-12','11:00:00',63,3,1,'2026-10-09');
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consulta_medica`
--

LOCK TABLES `consulta_medica` WRITE;
/*!40000 ALTER TABLE `consulta_medica` DISABLE KEYS */;
INSERT INTO `consulta_medica` VALUES
(3,42,46,1.72,68.50,'O+','Cefalea pulsátil de 24 horas y fotofobia leve.','Cefalea tensional secundaria a estrés académico.','Acetaminofén 500mg V.O. c/8h por 3 días y reposo.','Se recomienda control de tensión arterial y descanso de pantallas.','2024-11-18'),
(4,43,47,1.65,58.00,'A+','Congestión nasal, odinofagia y febrícula no cuantificada.','Síndrome viral respiratorio agudo.','Abundante hidratación oral, vitamina C y reposo relativo.','Evolución clínica estable, indicar uso de tapabocas.','2024-12-10'),
(5,44,48,1.78,74.20,'B+','Epigastralgia de tipo urente tras ingesta de irritantes.','Gastritis aguda irritativa.','Dieta blanda sin irritantes, antiácidos según indicación.','Evitar periodos prolongados de ayuno.','2025-01-22'),
(6,45,49,1.60,52.00,'O-','Odinofagia moderada y sensación febril de 48 horas.','Faringoamigdalitis eritematosa agudizada.','Gárgaras antisépticas, analgésicos e hidratación.','Sin secreción purulenta visible al examen físico.','2025-02-15'),
(7,46,50,1.82,81.00,'AB+','Chequeo médico preventivo institucional.','Examen físico dentro de parámetros normales.','No requiere tratamiento farmacológico.','Signos vitales normales: TA 120/80 mmHg, FC 72 bpm.','2025-03-20'),
(8,47,51,1.68,62.50,'A-','Disnea leve y sibilancias desencadenadas por cambios de clima.','Broncoespasmo leve / Asma bronquial compensada.','Uso de inhalador broncodilatador según esquema previo.','Se indica evitar exposición a alérgenos conocidos.','2025-04-12'),
(9,48,52,1.75,70.00,'O+','Dolor de cabeza opresivo constante en región frontal.','Cefalea por fatiga visual y cansancio.','Analgésicos simples e hidratación adecuada.','Se recomienda evaluación por optometría.','2025-05-28'),
(10,49,53,1.62,55.40,'B-','Mialgias, malestar general y estornudos frecuentes.','Rinitis viral aguda / Gripe.','Antihistamínicos, reposo en casa y líquidos orales.','Permiso de asistencia por 48 horas consignado.','2025-06-19'),
(11,50,54,1.70,66.80,'A+','Acidez estomacal intensa posterior a ayuno prolongado.','Gastritis medicamentosa / irritativa.','Protector gástrico, dieta libre de grasas y condimentos.','Reevaluación en 5 días de persistir la sintomatología.','2025-07-25'),
(12,51,55,1.66,60.00,'O+','Dolor al tragar alimentos sólidos y ardor faríngeo.','Faringitis aguda de etiología probable viral.','Analgésicos, antiinflamatorios y abundante agua.','Paciente asiste a control con franca mejoría.','2025-08-30'),
(13,52,56,1.74,72.00,'A+','Valoración médica para práctica deportiva institucional.','Apto para actividades físicas y deportivas.','No requiere prescripción médica.','Certificado médico emitido satisfactoriamente.','2025-09-18'),
(14,53,57,1.69,63.00,'B+','Dolor pulsátil retroocular posterior a jornada intensiva.','Cefalea tensional severa.','Analgésicos orales, reposo en ambiente oscuro.','Mejoría notable tras 2 horas de reposo clínico.','2025-10-14'),
(15,54,58,1.76,71.50,'O+','Tos seca episódica, estornudos y secreción hialina.','Infección respiratoria alta no complicada.','Tratamiento sintomático, reposo e hidratación constante.','Se sugiere control ambiental para evitar polvo.','2025-11-20'),
(16,55,59,1.64,56.00,'A+','Dolor en epigastrio y náuseas matutinas leves.','Gastritis aguda por estrés y mala alimentación.','Dieta gástrica estricta, antiácidos orales.','Se recalca la importancia del desayuno regular.','2025-12-15'),
(17,56,60,1.71,67.00,'AB+','Sensación de cuerpo extraño en garganta y tos.','Faringitis eritematosa leve.','Enjuagues bucofaríngeos y analgésicos.','Responde favorablemente al examen clínico.','2026-01-18'),
(18,57,61,1.80,83.00,'O-','Opresión torácica leve posterior a ejercicio físico.','Crisis asmática leve / Broncoespasmo reactivo.','Nebulización de rescate en unidad, reposo por 1 hora.','Recuperación completa del patrón respiratorio.','2026-02-22'),
(19,58,62,1.58,51.20,'B+','Cefalea occipital vinculada a posturas prolongadas.','Cefalea miotensiva de origen postural.','Compresas tibias cervicales, estiramientos y analgésicos.','Se dan pautas ergonómicas de estudio.','2026-03-15'),
(20,59,63,1.73,69.00,'O+','Malestar generalizado, escalofríos y rinorrea.','Cuadro gripal agudo.','Reposo médico por 48 horas, analgésicos e hidratación.','Constancia médica entregada al beneficiario.','2026-04-20'),
(21,60,64,1.67,59.80,'A-','Evaluación médica general de seguimiento.','Estudiante asintomático. Parámetros normales.','Sin tratamiento indicado.','Continuar con estilo de vida saludable.','2026-05-12'),
(22,61,65,1.79,77.00,'A+','Pirosis constante y distensión abdominal postprandial.','Dispepsia funcional / Gastritis.','Antiácidos, modificaciones dietéticas.','Se solicita control en caso de continuar molestia.','2026-06-25'),
(23,62,66,1.63,54.50,'O+','Odinofagia leve al despertar e irritación faríngea.','Faringitis aguda incipiente.','Gárgaras antisépticas e hidratación.','Sintomatología leve sin compromiso sistémico.','2026-07-18'),
(24,63,67,1.77,75.00,'B-','Dolor de cabeza hemicraneal opresivo episódico.','Cefalea tensional.','Acetaminofén, ambiente tranquilo.','Paciente indica mejoría tras toma de analgésico.','2026-08-22'),
(25,64,68,1.70,65.00,'O+','Estornudos, obstrucción nasal y malestar corporal.','Rinitis viral agudizada.','Líquidos calientes, reposo relativo.','Indicaciones generales otorgadas.','2026-09-15'),
(26,65,69,1.61,53.00,'A+','Dolor en epigastrio secundario a ingesta de café sin alimento.','Gastritis aguda estresora.','Tratamiento sintomático gástrico.','Recomendación de suspender irritantes por 7 días.','2026-10-05');
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consulta_psicologica`
--

LOCK TABLES `consulta_psicologica` WRITE;
/*!40000 ALTER TABLE `consulta_psicologica` DISABLE KEYS */;
INSERT INTO `consulta_psicologica` VALUES
(15,22,18,'Diagnóstico','Cuadro de ansiedad académica caracterizado por insomnio de conciliación y tensión muscular previa a periodos de evaluación parcial.','Psicoterapia cognitivo-conductual (4 sesiones), entrenamiento en respiración diafragmática y técnicas de organización del tiempo de estudio.','No aplica','No aplica','No aplica','Estudiante muestra excelente disposición al tratamiento. Se sugiere seguimiento al finalizar el trayecto.','2024-11-12 14:15:00'),
(16,23,19,'Diagnóstico','Estrés adaptativo leve asociado al ingreso al nuevo trayecto universitario y sobrecarga de proyectos comunitarios.','Taller de manejo del estrés, psicoeducación en higiene del sueño y pautas de autocuidado.','No aplica','No aplica','No aplica','Sin hallazgos clínicos severos. Se otorgan herramientas de afrontamiento.','2024-12-05 18:30:00'),
(17,24,20,'Diagnóstico','Síntomas ansiosos episódicos manifestados por angustia antes de exposiciones orales y labilidad afectiva.','Desensibilización sistemática, práctica de oratoria supervisada y restructuración de pensamientos catastróficos.','No aplica','No aplica','No aplica','Evolución favorable en 2da sesión. Reporta disminución de la fobia social.','2025-01-20 13:00:00'),
(18,25,21,'Cambio de carrera','No aplica','No aplica','No aplica','No aplica','Reorientación vocacional hacia PNF Informática por interés en programación.','Se aplicó la batería vocacional de Holland con resultado compatible en perfil analítico.','2025-02-14 15:30:00'),
(19,26,22,'Diagnóstico','Evaluación psicológica de rutina. Estudiante orientado en tiempo, espacio y persona con discurso coherente.','No requiere tratamiento psicológico continuo. Recomendaciones de rendimiento académico.','No aplica','No aplica','No aplica','Cita solicitada por iniciativa propia para valoración de desempeño.','2025-03-26 19:10:00'),
(20,27,23,'Retiro temporal','No aplica','No aplica','Situación de salud familiar severa en su municipio de origen que requiere cuidado continuo.','1 Semestre','No aplica','Se entrega informe institucional para avalar la suspensión temporal de la matrícula.','2025-04-18 12:45:00'),
(21,28,24,'Diagnóstico','Ansiedad por desempeño durante las actividades de laboratorios prácticos y evaluaciones de campo.','Entrenamiento en técnicas de afrontamiento cognitivo y autoinstrucciones positivas.','No aplica','No aplica','No aplica','Se recomienda coordinación de tutoría para reforzar conceptos prácticos.','2025-05-30 17:20:00'),
(22,29,25,'Cambio de carrera','No aplica','No aplica','No aplica','No aplica','Baja afinidad con PNF Deporte e interés en PNF Administración.','Resultados de test de intereses ocupacionales respaldan la solicitud del cambio de especialidad.','2025-06-22 14:00:00'),
(23,30,26,'Diagnóstico','Ansiedad generalizada reactiva a presiones financieras socioeconómicas familiares que impactan la concentración.','Intervención breve enfocada en soluciones, psicoeducación afectiva y canalización con Trabajo Social.','No aplica','No aplica','No aplica','Se coordina remisión a la unidad socioeconómica institucional para evaluación de beneficio.','2025-07-11 20:00:00'),
(24,31,27,'Retiro temporal','No aplica','No aplica','Dificultades socioeconómicas severas y desplazamiento laboral temporal a otra región.','2 Semestres','No aplica','Estudiante acude a consulta para formalizar la recomendación de retiro temporal por motivos económicos.','2025-08-25 13:15:00'),
(25,32,28,'Diagnóstico','Consulta preventiva de manejo de tiempo. El beneficiario no presenta patología psiquiátrica ni alteraciones emocionales.','Pautas de organización académica, mapa de metas personales a mediano plazo.','No aplica','No aplica','No aplica','Estudiante de excelente rendimiento que busca optimizar su ritmo de estudio.','2025-09-14 18:50:00'),
(26,33,29,'Cambio de carrera','No aplica','No aplica','No aplica','No aplica','Afinidad a PNF Contaduría Pública por habilidades numéricas.','Entrevista vocacional favorable. Muestra comprensión clara de las implicaciones de la carrera.','2025-10-03 15:00:00'),
(27,34,30,'Diagnóstico','Cuadro de ansiedad reactiva por duelo no complicado tras la pérdida de un familiar cercano.','Acompañamiento psicológico en proceso de duelo, validación emocional y 3 sesiones de apoyo.','No aplica','No aplica','No aplica','Muestra avance progresivo en la elaboración del duelo. Asistirá a control en un mes.','2025-11-19 19:30:00'),
(28,35,31,'Diagnóstico','Fatiga física y mental vinculada al cumplimiento simultáneo de actividades laborales nocturnas y clases académicas.','Estrategias de higiene del sueño, priorización de tareas y descansos programados.','No aplica','No aplica','No aplica','Se aconseja evaluación de la carga horaria académica para evitar el síndrome de agotamiento.','2025-12-10 12:30:00'),
(29,36,32,'Cambio de carrera','No aplica','No aplica','No aplica','No aplica','Traslado a PNF Turismo por inclinación a gestión patrimonial.','Aplica prueba de perfil vocacional. Informe favorable para la comisión de admisión.','2026-01-15 14:45:00'),
(30,37,33,'Diagnóstico','Manifestación somática de ansiedad por estrés de entregas de proyectos sociointegradores finales.','Técnicas de regulación emocional, autoinstrucciones positivas y ejercicios de relajación progresiva de Jacobson.','No aplica','No aplica','No aplica','Buena respuesta al tratamiento psicoeducativo.','2026-02-20 17:15:00'),
(31,38,34,'Diagnóstico','Trastorno de ansiedad social leve que interfiere en la presentación de defensas públicas de proyectos.','Entrenamiento en habilidades sociales, simulación de presentaciones y modulación de la voz.','No aplica','No aplica','No aplica','Participa activamente en la sesión de ensayo grupal. Muestra avances notables.','2026-03-12 13:30:00'),
(32,39,35,'Cambio de carrera','No aplica','No aplica','No aplica','No aplica','Reorientación a PNF Higiene y Seguridad por prevención de riesgos.','Estudiante cuenta con habilidades organizativas acordes a la especialidad elegida.','2026-04-24 19:00:00'),
(33,40,36,'Retiro temporal','No aplica','No aplica','Proceso médico postquirúrgico y rehabilitación que requiere reposo absoluto domiciliario.','1 Semestre','No aplica','Consigna constancia emitida por el servicio médico institucional.','2026-05-18 15:20:00'),
(34,41,37,'Diagnóstico','Consulta por orientación vocacional sin patología clínica identificada. Funcionamiento cognitivo y afectivo conservado.','Asesoría vocacional personalizada e información de perfiles de egreso.','No aplica','No aplica','No aplica','Estudiante decide continuar en su especialidad actual tras aclarar dudas de salida profesional.','2026-06-29 18:00:00'),
(35,42,38,'Diagnóstico','Sintomatología ansiosa asociada a la toma de decisiones profesionales de fin de trayecto.','Reestructuración cognitiva, resolución de problemas y focalización en metas inmediatas.','No aplica','No aplica','No aplica','Evolución satisfactoria. Se otorga alta psicológica de la fase aguda.','2026-07-21 14:10:00'),
(36,43,39,'Cambio de carrera','No aplica','No aplica','No aplica','No aplica','Cambio vocacional a PNF Agroalimentación por producción sostenible.','Batería psicométrica confirma perfil aptitudinal hacia áreas agrícolas y ambientales.','2026-08-16 20:30:00'),
(37,44,40,'Diagnóstico','Tensión emocional y sobrecarga percibida ante el inicio de prácticas profesionales institucionales.','Técnicas de gestión del tiempo, comunicación asertiva y contención emocional.','No aplica','No aplica','No aplica','Se mantiene en seguimiento mensual voluntario.','2026-09-08 13:00:00'),
(38,45,41,'Diagnóstico','Episodio ansioso agudo generado por sobrecarga de compromisos académicos de fin de trayecto.','Psicoterapia breve de apoyo, ejercicios de grounding y planificación semanal.','No aplica','No aplica','No aplica','Paciente se retira tranquilo y fortalecido tras la sesión.','2026-10-04 15:45:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ficha_equipo`
--

LOCK TABLES `detalle_ficha_equipo` WRITE;
/*!40000 ALTER TABLE `detalle_ficha_equipo` DISABLE KEYS */;
INSERT INTO `detalle_ficha_equipo` VALUES
(1,1,1,'CPU Recepción'),
(2,1,2,'Monitor Recepción'),
(3,1,3,'Impresora compartida'),
(4,2,4,'CPU Lenovo'),
(5,2,5,'Monitor ThinkVision'),
(6,3,6,'CPU Dell'),
(7,3,7,'Monitor Dell'),
(8,4,8,'CPU HP'),
(9,4,9,'Monitor HP'),
(10,4,10,'Tensiómetro de pared'),
(11,6,11,'CPU HP'),
(12,6,12,'Monitor Acer'),
(13,7,13,'CPU HP'),
(14,7,14,'Monitor HP 24\"'),
(15,7,15,'Impresora Epson EcoTank'),
(16,8,16,'CPU Lenovo'),
(17,8,17,'Monitor Lenovo'),
(18,9,18,'Laptop ProBook'),
(19,9,19,'Video Beam Epson');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ficha_mobiliario`
--

LOCK TABLES `detalle_ficha_mobiliario` WRITE;
/*!40000 ALTER TABLE `detalle_ficha_mobiliario` DISABLE KEYS */;
INSERT INTO `detalle_ficha_mobiliario` VALUES
(1,1,2,1,'Escritorio de recepción'),
(2,1,3,1,'Silla receptor'),
(3,2,5,1,'Escritorio ergonómico'),
(4,2,6,1,'Silla ergonómica'),
(5,2,7,2,'Sillas paciente'),
(6,3,5,1,'Escritorio ergonómico'),
(7,3,6,1,'Silla ergonómica'),
(8,4,8,1,'Escritorio médico'),
(9,4,9,1,'Camilla clínica'),
(10,4,10,1,'Archivador historias'),
(11,5,8,1,'Escritorio médico'),
(12,5,9,1,'Camilla clínica'),
(13,5,10,1,'Archivador historias'),
(14,6,11,1,'Escritorio orientación'),
(15,6,12,1,'Silla giratoria'),
(16,7,13,1,'Escritorio Trabajo Social'),
(17,7,14,1,'Archivador expedientes'),
(18,8,15,1,'Escritorio accesible'),
(19,8,16,1,'Silla de ruedas traslados'),
(20,9,17,1,'Escritorio Ejecutivo'),
(21,9,18,1,'Mesa de conferencias');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_insumo`
--

LOCK TABLES `detalle_insumo` WRITE;
/*!40000 ALTER TABLE `detalle_insumo` DISABLE KEYS */;
INSERT INTO `detalle_insumo` VALUES
(2,3,1,'2'),
(3,6,1,'1'),
(4,10,1,'2'),
(5,15,1,'2'),
(6,20,1,'1'),
(7,23,1,'2');
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
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_patologia`
--

LOCK TABLES `detalle_patologia` WRITE;
/*!40000 ALTER TABLE `detalle_patologia` DISABLE KEYS */;
INSERT INTO `detalle_patologia` VALUES
(46,1),
(52,1),
(60,1),
(19,2),
(28,2),
(37,2),
(17,3),
(21,3),
(22,3),
(23,3),
(25,3),
(27,3),
(29,3),
(31,3),
(32,3),
(35,3),
(36,3),
(39,3),
(40,3),
(43,5),
(49,5),
(54,5),
(59,5),
(64,5),
(72,5),
(73,5),
(79,5),
(82,5),
(18,6),
(20,6),
(24,6),
(26,6),
(30,6),
(33,6),
(34,6),
(38,6),
(41,6),
(42,10),
(48,10),
(53,10),
(58,10),
(63,10),
(44,11),
(50,11),
(55,11),
(61,11),
(65,11),
(74,11),
(77,11),
(80,11),
(83,11),
(45,12),
(51,12),
(56,12),
(62,12),
(76,12),
(81,12),
(47,13),
(57,13),
(75,13),
(78,13),
(84,13);
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discapacidad`
--

LOCK TABLES `discapacidad` WRITE;
/*!40000 ALTER TABLE `discapacidad` DISABLE KEYS */;
INSERT INTO `discapacidad` VALUES
(2,94,'Física','Hemiparesia izquierda residual de etiología congénita.','Dificultad moderada para la marcha prolongada y bipedestación sostenida.','Moderado','Sin medicación fija indicada actualmente.','Autonomía en desplazamiento a ritmo pausado, adecuada movilidad superior.','No','Bastón ortopédico monopodal para trayectos largos.','Estudiante cursa trayecto académico regular con apoyo de ubicación.','Garantizar asignación de aulas en planta baja o con rampa habilitada.','CONAPDIS-194820','2024-11-25'),
(3,95,'Sensorial','Hipoacusia neurosensorial bilateral moderada.','Disminución de agudeza auditiva bilateral con comprensión mediante lectura labial.','Moderado','Sin medicación requerida.','Excelente lectura labiofacial, lectoescritura fluida y manejo de lengua de señas.','Si','Auxiliares auditivos retroauriculares bilaterales.','Requiere ubicación en los primeros pupitres de la sección asignada.','Facilitar material educativo digitalizado e impreso de apoyo.','CONAPDIS-839201','2024-12-18'),
(4,96,'Sensorial','Baja visión por miopía magna no corregible totalmente.','Restricción significativa del campo visual y baja agudeza visual focal.','Grave','Lágrimas artificiales en gotas c/8h.','Manejo autónomo de lectores de pantalla sintéticos y macrocristales.','No','Lentes de alta graduación y software lector de pantalla NVDA.','Requiere evaluaciones impresas con contraste de fuente ampliada (18pt+).','Proporcionar material evaluativo en formato digital accesible.','CONAPDIS-492018','2025-01-28'),
(5,97,'Intelectual','Compromiso cognitivo leve en procesamiento abstracto.','Dificultad leve en la abstracción de conceptos matemáticos complejos.','Leve','No indica medicación.','Adecuada interacción social, cumplimiento de tareas con guía estructurada.','No','Guías visuales estructuradas y esquemas de trabajo.','Responde de forma positiva a la adaptación del ritmo evaluativo.','Desglosar las instrucciones escritas en pasos secuenciales breves.',NULL,'2025-02-20'),
(6,98,'Múltiple','Parálisis cerebral infantil de tipo diplejía espástica.','Compromiso motor severo en miembros inferiores y disartria leve.','Grave','Miorrelajantes según prescripción neuropediátrica.','Desplazamiento autónomo en silla de ruedas, excelente comprensión.','Si','Silla de ruedas manual ergonómica.','Requiere adecuación de mesas en laboratorios de informática.','Asegurar rampa de acceso y mesas de altura adaptada en laboratorios.','CONAPDIS-721094','2025-03-24'),
(7,99,'Física','Amputación traumática parcial de antebrazo izquierdo.','Ausencia funcional del segmento distal del miembro superior izquierdo.','Moderado','No requiere medicamentos.','Desplazamiento normal, compensación motora eficaz con miembro derecho.','No','Prótesis mecánica funcional de antebrazo.','Estudiante adaptado al uso de teclado con una sola mano.','Permitir tiempo adicional en evaluaciones escritas o de tipeo.','CONAPDIS-305912','2025-04-18'),
(8,100,'Sensorial','Acúfenos crónicos e hipoacusia leve unilateral.','Disminución discreta de la audición en oído derecho por trauma acústico.','Leve','No requiere medicación gótica.','Comunicación verbal normal en ambientes con bajo ruido de fondo.','No','Filtros auditivos de protección.','Monitoreo preventivo en áreas ruidosas.','Evitar exposición continuada a altoparlantes o ruido elevado.',NULL,'2025-05-29'),
(9,101,'Otro','Condición metabólica con neuropatía periférica motora.','Disminución de sensibilidad de miembros inferiores y fatigabilidad.','Moderado','Tratamiento regulador neurológico prescrito.','Marcha lenta, independencia plena en actividades académicas de aula.','No','Plantillas ortopédicas de descarga.','Revisión semestral de la condición de movilidad.','Se aconseja descansos breves de 5 minutos en clases largas.','CONAPDIS-612048','2025-06-22'),
(10,102,'Física','Secuela de poliomielitis en miembro inferior derecho.','Marcha claudicante con disminución de fuerza muscular en pierna derecha.','Moderado','No indica medicamentos.','Independencia funcional con apoyo de órtesis pasiva.','No','Órtesis corta de tobillo y pie (AFO).','Se desplaza con autonomía por el recinto universitario.','Evitar traslados entre edificios distantes en receso corto.','CONAPDIS-519203','2025-07-19'),
(11,103,'Sensorial','Restos visuales conservados / Visión tubular.','Disminución marcada de la visión periférica bilateral.','Grave','Gotas hipotensoras oculares cada 12 horas.','Desplazamiento con bastón guía, orientación espacial adecuada.','Si','Bastón blanco rígido de orientación.','Acompañamiento en áreas de alta concurrencia o escaleras.','Mantener pasillos libres de obstáculos físicos.','CONAPDIS-940215','2025-08-30'),
(12,104,'Física','Malformación congénita de mano derecha (Oligodactilia).','Limitación fina de agarre en mano derecha sin afectación del brazo.','Leve','No requiere medicamentos.','Plena capacidad de lectoescritura con mano izquierda dominante.','No','Adaptador ergonómico para bolígrafo.','Estudiante con alto rendimiento en desarrollo de software.','Permitir uso de computadoras portátiles para toma de apuntes.',NULL,'2025-09-22'),
(13,105,'Intelectual','Dificultad de aprendizaje procedimental leve.','Retraso leve en la consolidación de secuencias lógicas automatizadas.','Leve','No indica fármacos.','Expresión verbal fluida, buena sociabilidad y comprensión general.','No','Calculadora y esquemas visuales de apoyo.','Acompañamiento periódico por parte del departamento de orientación.','Flexibilización en el tiempo de entrega de evaluaciones escritas.','CONAPDIS-204918','2025-10-16'),
(14,106,'Sensorial','Anacusia unilateral derecha.','Pérdida completa de la audición en oído derecho por parotiditis infantil.','Leve','No requiere medicamentos.','Comprensión auditiva conservada mediante oído izquierdo sano.','No','Sin dispositivo específico.','Ubicación estratégica en el costado izquierdo del aula.','Garantizar que el docente hable desde el campo auditivo izquierdo.',NULL,'2025-11-24'),
(15,107,'Múltiple','Síndrome motor-sensorial post-traumatismo vertebromedular.','Monoparesia de miembro inferior izquierdo e hipoacusia leve.','Grave','Analgésicos neuropáticos según necesidad.','Movilidad con apoyo postural, uso de lenguaje de señas básico.','Si','Silla de ruedas motorizada plegable.','Se tramita carnet de estacionamiento preferencial.','Ubicación en planta baja y asistencia en laboratorios.','CONAPDIS-730192','2025-12-19'),
(16,108,'Física','Artritis juvenil idiopática en fase remisa.','Rigidez articular matutina de carácter leve en articulaciones intergalángicas.','Leve','Antiinflamatorios de rescate ocasionales.','Movilidad manual conservada sin deformidad articular.','No','Férulas nocturnas de reposo.','Monitoreo en épocas de bajas temperaturas.','Evitar mantener las manos en posturas estáticas prolongadas.','CONAPDIS-118293','2026-01-22'),
(17,109,'Sensorial','Estrabismo divergente severo con ambliopía binocular.','Dificultad en la percepción de profundidad y visión binocular reducida.','Moderado','Tratamiento ortóptico periódico.','Lectura a distancia corta adecuada, desplazamiento autónomo.','No','Lentes con prismas correctores.','Se sugiere descansos visuales de 10 minutos cada hora.','Ubicación en filas centrales del aula a distancia media.',NULL,'2026-02-26'),
(18,110,'Física','Espina bífida oculta con debilidad muscular distal.','Disminución leve del tono muscular en pies y tobillos.','Moderado','Suplementos vitamínicos del complejo B.','Marcha independiente, dificultad moderada al subir gradas.','No','Plantillas de soporte de arco plantar.','Uso continuo de elevador en sedes de múltiples niveles.','Asignar aulas accesibles mediante rampa o ascensor.','CONAPDIS-883019','2026-03-20'),
(19,111,'Otro','Acondroplasia / Talla baja armónica.','Restricción del crecimiento óseo longitudinal sin afectación cognitiva.','Moderado','No indica medicamentos.','Independencia plena, destreza motora fina excelente.','No','Escalones portátiles y adaptaciones posturales.','Se requiere adaptación de mobiliario en pupitres.','Facilitar apoya-pies y pupitre adaptado a su estatura.','CONAPDIS-449102','2026-04-25'),
(20,112,'Sensorial','Hipoacusia neurosensorial profunda bilateral.','Ausencia funcional de la audición bilateral.','Grave','No indica fármacos.','Excelente dominio de Lengua de Señas Venezolana (LSV) y lectura labial.','Si','Implante coclear unilateral en oído izquierdo.','Cuenta con acompañamiento de intérprete en asignaturas complejas.','Permitir el ingreso de intérprete de lengua de señas al aula.','CONAPDIS-992014','2026-05-18'),
(21,113,'Física','Secuela postural de Escoliosis idiopática severa.','Desviación lateral de columna vertebral que genera fatiga al estar sentado.','Moderado','Analgésicos de uso eventual.','Marcha y postura en bipedestación normales.','No','Corsé de soporte dorsolumbar.','Requiere silla ergonómica con soporte lumbar adecuado.','Permitir que el estudiante se ponga de pie breves minutos en clase.',NULL,'2026-06-30'),
(22,114,'Intelectual','Trastorno del espectro autista de alto funcionamiento (Nivel 1).','Dificultad leve en la flexibilidad cognitiva e interacción social no verbal.','Leve','Sin medicación psicotrópica.','Alta capacidad de concentración en temas de su interés (programación).','No','Audífonos de cancelación de ruido para ambientes saturados.','Estudiante muy organizado con alto rendimiento en lógica.','Mantener ambiente predictivo y notificar cambios de evaluación con tiempo.','CONAPDIS-339102','2026-07-22'),
(23,115,'Física','Distrofia muscular progresiva en fase inicial.','Debilidad moderada en musculatura proximal de miembros inferiores.','Grave','Tratamiento fisioterapéutico continuo.','Desplazamiento autónomo a corta distancia con apoyo de andadera.','Si','Andadera de aluminio con ruedas frontales.','Se evalúa necesidad de transporte institucional preferencial.','Asignar aulas en planta baja cerca de la entrada principal.','CONAPDIS-664019','2026-08-28'),
(24,116,'Sensorial','Ceguera total de ojo derecho por trauma ocular.','Pérdida de la visión monocular derecha con conservación del ojo izquierdo.','Moderado','Gotas protectoras en ojo izquierdo.','Visión monocular conservada, orientación espacial buena.','No','Lentes de policarbonato con protección UV.','Ubicación en el sector derecho del aula.','Proteger el ojo funcional durante actividades de laboratorio.',NULL,'2026-09-20'),
(25,117,'Física','Monoparesia leve residual de miembro superior derecho.','Disminución discreta de la fuerza de agarre en mano derecha.','Leve','Ejercicios de rehabilitación de fuerza fina.','Independencia funcional total con compensación.','No','Pelota de fisioterapia para articulación.','Diagnóstico reciente registrado en la unidad este mes.','Permitir tiempo adicional en pruebas escritas prolongadas.','CONAPDIS-551029','2026-10-07');
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipos`
--

LOCK TABLES `equipos` WRITE;
/*!40000 ALTER TABLE `equipos` DISABLE KEYS */;
INSERT INTO `equipos` VALUES
(1,5,6,'HP','ProDesk 400 G6','HP-CPU-2025-001','Negro','Bueno','2025-01-10','Intel Core i5, 8GB RAM, 256GB SSD','Estación de recepción','Activo','2025-01-15 12:30:00'),
(2,1,6,'HP','P22h G4','HP-MON-2025-001','Negro','Bueno','2025-01-10','Monitor LED 21.5 Pulgadas','Conexión HDMI/VGA','Activo','2025-01-15 12:30:00'),
(3,7,6,'HP','LaserJet Pro M404dn','HP-IMP-2025-001','Blanco/Gris','Bueno','2025-01-10','Impresora láser red','Impresora compartida','Activo','2025-01-15 12:35:00'),
(4,5,1,'Lenovo','ThinkCentre M720s','LEN-CPU-2025-002','Negro','Bueno','2025-01-12','Intel Core i5, 16GB RAM, 512GB SSD','CPU Psicología 1','Activo','2025-01-20 13:00:00'),
(5,1,1,'Lenovo','ThinkVision T24i','LEN-MON-2025-002','Negro','Bueno','2025-01-12','Monitor LED 23.8 Pulgadas','Monitor Psicología 1','Activo','2025-01-20 13:00:00'),
(6,5,1,'Dell','OptiPlex 3080','DEL-CPU-2025-003','Negro','Bueno','2025-01-12','Intel Core i3, 8GB RAM, 256GB SSD','CPU Psicología 2','Activo','2025-01-20 13:05:00'),
(7,1,1,'Dell','E2220H','DEL-MON-2025-003','Negro','Bueno','2025-01-12','Monitor LED 21.5 Pulgadas','Monitor Psicología 2','Activo','2025-01-20 13:05:00'),
(8,5,2,'HP','Compaq Pro 6300','HP-CPU-2025-005','Negro/Gris','Bueno','2025-01-15','Intel Core i3, 8GB RAM, 500GB HDD','CPU Medicina 1','Activo','2025-01-25 14:00:00'),
(9,1,2,'HP','V20 HD','HP-MON-2025-005','Negro','Bueno','2025-01-15','Monitor LED 19.5 Pulgadas','Monitor Medicina 1','Activo','2025-01-25 14:00:00'),
(10,10,2,'WelchAllyn','767 Wall Aneroid','WEL-TEN-2025-005','Blanco','Bueno','2025-01-15','Tensiómetro aneroide de pared con brazalete','Fijo en consultorio médico','Activo','2025-01-25 14:10:00'),
(11,5,3,'HP','ProDesk 400 G5','HP-CPU-2025-007','Negro','Bueno','2025-01-20','Intel Core i5, 8GB RAM, 256GB SSD','CPU Orientación 1','Activo','2025-02-01 12:30:00'),
(12,1,3,'Acer','V226HQL','ACE-MON-2025-007','Negro','Bueno','2025-01-20','Monitor LED 21.5 Pulgadas','Monitor Orientación 1','Activo','2025-02-01 12:30:00'),
(13,5,4,'HP','ProDesk 600 G4','HP-CPU-2025-009','Negro','Bueno','2025-01-22','Intel Core i5, 16GB RAM, 512GB SSD','CPU Trabajo Social 1','Activo','2025-02-05 13:00:00'),
(14,1,4,'HP','E243 23.8\"','HP-MON-2025-009','Plata/Negro','Bueno','2025-01-22','Monitor IPS Full HD 23.8\"','Monitor Trabajo Social 1','Activo','2025-02-05 13:00:00'),
(15,7,4,'Epson','EcoTank L3250','EPS-IMP-2025-009','Negro','Bueno','2025-01-22','Multifuncional de tinta continua','Para impresión de becas y estudios','Activo','2025-02-05 13:05:00'),
(16,5,5,'Lenovo','ThinkCentre Neo 50s','LEN-CPU-2025-011','Negro','Bueno','2025-01-25','Intel Core i5, 8GB RAM, 512GB SSD','CPU Unidad de Discapacidad','Activo','2025-02-10 14:00:00'),
(17,1,5,'Lenovo','ThinkVision S24e','LEN-MON-2025-011','Negro','Bueno','2025-01-25','Monitor LED 23.8 Pulgadas','Monitor Unidad de Discapacidad','Activo','2025-02-10 14:00:00'),
(18,9,8,'HP','ProBook 440 G8','HP-LAP-2025-012','Plata','Bueno','2025-02-01','Intel Core i7, 16GB RAM, 512GB SSD','Laptop Institucional Dirección','Activo','2025-02-15 15:00:00'),
(19,2,6,'Epson','PowerLite E20','EPS-VIB-2025-013','Blanco','Bueno','2025-02-01','Proyector 3400 lúmenes HDMI','Video Beam Sala de Conferencias','Activo','2025-02-15 15:15:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exoneracion`
--

LOCK TABLES `exoneracion` WRITE;
/*!40000 ALTER TABLE `exoneracion` DISABLE KEYS */;
INSERT INTO `exoneracion` VALUES
(8,143,'Inscripción','No aplica','uploads/trabajo_social/carta_exo_1.pdf','uploads/trabajo_social/estudio_se_1.pdf','No posee','2024-12-02'),
(9,144,'Paquete de Grado','No aplica','uploads/trabajo_social/carta_exo_2.pdf',NULL,'CONAPDIS-091823','2025-02-18'),
(10,145,'Otro','Exoneración arancelaria especial','uploads/trabajo_social/carta_exo_3.pdf','uploads/trabajo_social/estudio_se_3.pdf','No posee','2025-04-22'),
(11,146,'Inscripción','No aplica','uploads/trabajo_social/carta_exo_4.pdf',NULL,'No posee','2025-06-14'),
(12,147,'Paquete de Grado','No aplica','uploads/trabajo_social/carta_exo_5.pdf','uploads/trabajo_social/estudio_se_5.pdf','CONAPDIS-772109','2025-10-08'),
(13,148,'Inscripción','No aplica','uploads/trabajo_social/carta_exo_6.pdf',NULL,'No posee','2026-10-04');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fames`
--

LOCK TABLES `fames` WRITE;
/*!40000 ALTER TABLE `fames` DISABLE KEYS */;
INSERT INTO `fames` VALUES
(7,73,73,'Económica','No aplica','2024-12-10'),
(8,74,74,'Exámenes','No aplica','2025-03-15'),
(9,75,75,'Operaciones','No aplica','2025-05-28'),
(10,76,76,'Otros','Insumos y material quirúrgico','2025-08-14'),
(11,77,77,'Económica','No aplica','2025-11-20'),
(12,78,78,'Exámenes','No aplica','2026-03-08');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fichas_tecnicas`
--

LOCK TABLES `fichas_tecnicas` WRITE;
/*!40000 ALTER TABLE `fichas_tecnicas` DISABLE KEYS */;
INSERT INTO `fichas_tecnicas` VALUES
(1,'Ficha Técnica - Recepción y Control de Acceso',6,12,'Mobiliario y equipos asignados al área de recepción general.','2025-01-15',1),
(2,'Ficha Técnica - Cubículo Psicología 1',1,3,'Asignación de equipos y mobiliario para atención psicológica.','2025-01-20',1),
(3,'Ficha Técnica - Cubículo Psicología 2',1,5,'Asignación para consulta de orientación y psicología.','2025-01-20',1),
(4,'Ficha Técnica - Consultorio Médico 1',2,7,'Mobiliario clínico y estación médica.','2025-01-25',1),
(5,'Ficha Técnica - Consultorio Médico 2',2,8,'Estación clínica médica 2.','2025-01-25',1),
(6,'Ficha Técnica - Oficina de Orientación Vocacional',3,2,'Equipamiento de la unidad de Orientación.','2025-02-01',1),
(7,'Ficha Técnica - Trabajo Social (Becas y Exoneraciones)',4,9,'Unidad de estudios socioeconómicos y atención de becas.','2025-02-05',1),
(8,'Ficha Técnica - Unidad de Atención a la Discapacidad',5,13,'Mobiliario accesible y equipamiento adaptado.','2025-02-10',1),
(9,'Ficha Técnica - Dirección General DIRPOLES',8,7,'Oficina de Coordinación y Dirección.','2025-02-15',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gestion_emb`
--

LOCK TABLES `gestion_emb` WRITE;
/*!40000 ALTER TABLE `gestion_emb` DISABLE KEYS */;
INSERT INTO `gestion_emb` VALUES
(1,79,79,12,1029384756,1987654321,'Aprobado','2024-12-18'),
(2,80,80,24,1082736451,1981273645,'Aprobado','2025-02-22'),
(3,81,81,16,NULL,NULL,'Rechazado','2025-06-10'),
(4,82,82,32,1049281736,1983029182,'Aprobado','2025-09-28'),
(5,83,83,8,1073829104,1981920384,'En Proceso','2026-04-12'),
(6,84,84,18,1092830192,1984019283,'En Proceso','2026-10-06');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_inventario`
--

LOCK TABLES `historial_inventario` WRITE;
/*!40000 ALTER TABLE `historial_inventario` DISABLE KEYS */;
INSERT INTO `historial_inventario` VALUES
(2,12,'mobiliario',2,'asignacion',NULL,NULL,6,'Alta e inventariado de Escritorio receptor','2025-01-15 08:30:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insumos`
--

LOCK TABLES `insumos` WRITE;
/*!40000 ALTER TABLE `insumos` DISABLE KEYS */;
INSERT INTO `insumos` VALUES
(1,1,'Acetaminofén 500mg','Pastillas de acetaminofen','Medicamento','2027-08-20','2026-09-28',15,'Disponible'),
(3,2,'Ibuprofeno 400mg','Cápsulas analgésicas y antiinflamatorias.','Medicamento','2027-11-15','2025-01-10',8,'Disponible'),
(4,4,'Alcohol Antiséptico 70%','Frasco de 500ml para desinfección de superficies y piel.','Desinfectante','2028-04-30','2025-01-15',12,'Disponible'),
(5,5,'Gasas Estériles 7.5x7.5cm','Sobres individuales de gasa estéril para curaciones básicas.','Material Médico','2027-12-01','2025-02-01',25,'Disponible'),
(6,5,'Jeringas Desechables 5ml','Jeringas descartables con aguja 21G para aplicación parenteral.','Material Médico','2027-09-15','2025-02-10',5,'Disponible'),
(7,4,'Solución Fisiológica 0.9%','Frasco de 500ml para hidratación y lavado de heridas.','Insumo de Emergencia','2027-06-30','2025-03-05',4,'Disponible'),
(8,2,'Omeprazol 20mg','Cápsulas protectoras gástricas para tratamiento de gastritis','Medicamento','2027-05-10','2025-03-15',0,'Agotado'),
(9,5,'Vendas Elásticas 10cm','Rollos de venda elástica para inmovilización de esguinces.','Material Médico','2028-10-10','2025-04-01',6,'Disponible'),
(10,2,'Amoxicilina 500mg','Cápsulas antibióticas de amplio espectro.','Medicamento','2026-10-28','2025-05-10',10,'Disponible'),
(11,4,'Diclofenac Sódico Ampollas','Ampollas inyectables de 75mg/3ml para analgesia profunda.','Medicamento','2026-05-15','2025-01-05',0,'Vencido'),
(12,1,'Tachiforte 500mg','Nada','Medicamento','2027-01-13','2026-10-12',0,'Agotado');
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
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
(5,1,1,'2026-09-29 19:42:07','Salida',1,'Salida por jornada médica (diagnóstico #1)'),
(7,3,7,'2025-01-10 13:00:00','Registro',0,'Nuevo registro de insumo'),
(8,3,7,'2025-01-10 13:15:00','Entrada',8,'Stock inicial dispensario'),
(9,4,8,'2025-01-15 14:00:00','Registro',0,'Nuevo registro de insumo'),
(10,4,8,'2025-01-15 14:10:00','Entrada',12,'Dotación de material desinfectante'),
(11,5,7,'2025-02-01 12:30:00','Registro',0,'Nuevo registro de insumo'),
(12,5,7,'2025-02-01 12:45:00','Entrada',25,'Caja de material de curación'),
(13,6,8,'2025-02-10 15:00:00','Registro',0,'Nuevo registro de insumo'),
(14,6,8,'2025-02-10 15:15:00','Entrada',5,'Lote de jeringas parenterales'),
(15,7,7,'2025-03-05 18:00:00','Registro',0,'Nuevo registro de insumo'),
(16,7,7,'2025-03-05 18:20:00','Entrada',4,'Dotación de soluciones parenterales'),
(17,8,8,'2025-03-15 13:30:00','Registro',0,'Nuevo registro de insumo'),
(18,9,7,'2025-04-01 14:00:00','Registro',0,'Nuevo registro de insumo'),
(19,9,7,'2025-04-01 14:15:00','Entrada',6,'Adquisición de vendas elásticas'),
(20,10,8,'2025-05-10 19:00:00','Registro',0,'Nuevo registro de insumo'),
(21,10,8,'2025-05-10 19:20:00','Entrada',10,'Donación institucional de antibióticos'),
(22,11,7,'2025-01-05 15:00:00','Registro',0,'Nuevo registro de insumo'),
(23,1,7,'2025-02-12 12:35:00','Salida',2,'Salida por jornada médica (diagnóstico #1)'),
(24,6,7,'2026-02-18 12:20:00','Salida',1,'Salida por jornada médica (diagnóstico de vacunación)'),
(25,12,1,'2026-10-12 21:25:35','Registro',0,'Nuevo registro');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario_repuestos`
--

LOCK TABLES `inventario_repuestos` WRITE;
/*!40000 ALTER TABLE `inventario_repuestos` DISABLE KEYS */;
INSERT INTO `inventario_repuestos` VALUES
(4,2,4,'0','Registro','Alta inicial de repuesto','2025-01-20 13:00:00'),
(5,2,4,'8','Entrada','Compra a proveedor','2025-01-20 13:15:00'),
(6,3,4,'0','Registro','Alta inicial de repuesto','2025-02-01 14:00:00'),
(7,3,4,'3','Entrada','Compra a proveedor','2025-02-01 14:15:00'),
(8,4,4,'0','Registro','Alta inicial de repuesto','2025-02-10 15:00:00'),
(9,4,4,'8','Entrada','Compra a proveedor','2025-02-10 15:15:00'),
(10,5,4,'0','Registro','Alta inicial de repuesto','2025-02-15 12:30:00'),
(11,5,4,'3','Entrada','Compra a proveedor','2025-02-15 12:45:00'),
(12,6,4,'0','Registro','Alta inicial de repuesto','2025-03-01 13:00:00'),
(13,2,4,'2','Salida','Uso en mantenimiento rutinario','2026-10-02 14:00:00'),
(14,5,4,'1','Salida','Uso en mantenimiento rutinario','2026-10-02 14:05:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jornada_beneficiarios`
--

LOCK TABLES `jornada_beneficiarios` WRITE;
/*!40000 ALTER TABLE `jornada_beneficiarios` DISABLE KEYS */;
INSERT INTO `jornada_beneficiarios` VALUES
(3,'V','30995937','Roberth','Matos','2002-04-05','Masculino','Estudiante','04129298008','roberth@gmail.com','Calle 53 y 54 con carrera 14',2,'2025-02-12 12:30:00','Atendido'),
(4,'V','21381247','Marielys','Yépez','1995-03-12','Femenino','Estudiante','04145218844','marielys.yepez@gmail.com','Urb. Obelisco Bloque 12',2,'2025-02-12 13:10:00','Atendido'),
(5,'V','18390420','Carlos Eduardo','Pérez','1988-07-22','Masculino','Personal Docente','04268390112','carlos.perez@uptaeb.edu.ve','Carrera 19 entre 24 y 25',2,'2025-02-12 13:50:00','Atendido'),
(6,'V','26399593','Ana Gabriela','Rivas','1998-11-04','Femenino','Estudiante','04169402231','ana.rivas@gmail.com','Barrio Unión Calle 4',2,'2025-02-12 14:20:00','Atendido'),
(7,'V','16427112','José Gregorio','Mendoza','1983-02-15','Masculino','Personal Obrero','04123405567','jose.mendoza@uptaeb.edu.ve','El Ujano Sector 2',2,'2025-02-12 15:00:00','Atendido'),
(8,'V','14445458','Carmen Elena','Morales','1979-09-30','Femenino','Comunidad','04245119988','carmen.morales@gmail.com','Sector Pueblo Nuevo Calle 3',2,'2025-02-12 15:45:00','Atendido'),
(9,'V','23408766','Luis Alberto','Torres','1994-06-18','Masculino','Personal Administrativo','04145401122','luis.torres@uptaeb.edu.ve','Urb. Patarata Calle 3',3,'2025-05-20 12:45:00','Atendido'),
(10,'V','29417939','Sofia Victoria','Gómez','2001-10-14','Femenino','Estudiante','04269503344','sofia.gomez@gmail.com','Barrio la Ferretera',3,'2025-05-20 13:15:00','Atendido'),
(11,'V','24436285','Daniel Alejandro','Silva','1992-04-08','Masculino','Personal Docente','04125607788','daniel.silva@uptaeb.edu.ve','Av. Lara Res. Las Trinitarias',3,'2025-05-20 13:50:00','Atendido'),
(12,'V','27454631','Patricia Andreína','Colmenárez','1999-01-25','Femenino','Personal Obrero','04163209900','patricia.colmenarez@uptaeb.edu.ve','Carrera 15 con calle 50',3,'2025-05-20 14:30:00','Atendido'),
(13,'V','30463804','Gabriel Enrique','Suárez','2003-08-19','Masculino','Estudiante','04245701133','gabriel.suarez@gmail.com','Urb. La Concordia Calle 2',3,'2025-05-20 15:15:00','Atendido'),
(14,'V','19472977','Elena María','Castillo','1989-12-03','Femenino','Comunidad','04145802244','elena.castillo@gmail.com','Comunidad Ruíz Pineda',3,'2025-05-20 16:00:00','Atendido'),
(15,'V','28482150','Ricardo José','Ramírez','2000-05-14','Masculino','Estudiante','04126903322','ricardo.ramirez@gmail.com','Barrio Cerritos Blancos',4,'2025-08-14 12:30:00','Atendido'),
(16,'V','22491323','Vanessa Carolina','Gutiérrez','1993-09-28','Femenino','Personal Docente','04145904455','vanessa.gutierrez@uptaeb.edu.ve','Urb. Santa Elena Calle 5',4,'2025-08-14 13:20:00','Atendido'),
(17,'V','25509669','Fernando Antonio','Medina','1996-12-11','Masculino','Estudiante','04269105566','fernando.medina@gmail.com','Av. Vargas con carrera 21',4,'2025-08-14 14:10:00','Atendido'),
(18,'V','31518842','Camila Isabela','Herrera','2004-03-07','Femenino','Estudiante','04169206677','camila.herrera@gmail.com','El Manzano Sector El Parral',4,'2025-08-14 15:00:00','Atendido'),
(19,'V','15528015','Héctor Manuel','Vargas','1981-11-17','Masculino','Comunidad','04129307788','hector.vargas@gmail.com','Comunidad El Garabatal',4,'2025-08-14 15:45:00','Atendido'),
(20,'V','20537188','Diego Armando','Paredes','1991-01-30','Masculino','Estudiante','04245908899','diego.paredes@gmail.com','Barrio la Paz Calle 8',5,'2025-11-05 12:40:00','Atendido'),
(21,'V','26546361','Stephanie Beatriz','Lugo','1997-08-14','Femenino','Estudiante','04145309900','stephanie.lugo@gmail.com','Urb. la Rosaleda Calle 1',5,'2025-11-05 13:30:00','Atendido'),
(22,'V','13555534','Roberto Antonio','Fernández','1977-04-02','Masculino','Personal Obrero','04125801122','roberto.fernandez@uptaeb.edu.ve','Carrera 12 con calle 42',5,'2025-11-05 14:15:00','Atendido'),
(23,'V','18564707','Laura Cristina','Delgado','1987-10-21','Femenino','Personal Administrativo','04163402233','laura.delgado@uptaeb.edu.ve','Urb. Fundalara Calle 4',5,'2025-11-05 15:00:00','Atendido'),
(24,'V','29583053','Marcos Vinicio','Briceño','2002-02-18','Masculino','Estudiante','04269603344','marcos.briceno@gmail.com','Comunidad Tamaca Sector El Cuji',5,'2025-11-05 15:45:00','Atendido'),
(25,'V','16592226','Yolanda Josefina','Rondón','1984-06-09','Femenino','Personal Obrero','04145703344','yolanda.rondon@uptaeb.edu.ve','Comunidad el Cuji',6,'2026-02-18 12:15:00','Atendido'),
(26,'V','30995937','Roberth','Matos','2002-04-05','Masculino','Estudiante','04129298008','roberth@gmail.com','Calle 53 y 54 con carrera 14',6,'2026-02-18 13:00:00','Atendido'),
(27,'V','21381247','Marielys','Yépez','1995-03-12','Femenino','Estudiante','04145218844','marielys.yepez@gmail.com','Urb. Obelisco Bloque 12',6,'2026-02-18 13:40:00','Atendido'),
(28,'V','18390420','Carlos Eduardo','Pérez','1988-07-22','Masculino','Personal Docente','04268390112','carlos.perez@uptaeb.edu.ve','Carrera 19 entre 24 y 25',6,'2026-02-18 14:20:00','Atendido'),
(29,'V','26399593','Ana Gabriela','Rivas','1998-11-04','Femenino','Estudiante','04169402231','ana.rivas@gmail.com','Barrio Unión Calle 4',6,'2026-02-18 15:00:00','Atendido'),
(30,'V','23408766','Luis Alberto','Torres','1994-06-18','Masculino','Personal Administrativo','04145401122','luis.torres@uptaeb.edu.ve','Urb. Patarata Calle 3',6,'2026-02-18 15:40:00','Atendido'),
(31,'V','29417939','Sofia Victoria','Gómez','2001-10-14','Femenino','Estudiante','04269503344','sofia.gomez@gmail.com','Barrio la Ferretera',7,'2026-05-27 13:00:00','Atendido'),
(32,'V','24436285','Daniel Alejandro','Silva','1992-04-08','Masculino','Personal Docente','04125607788','daniel.silva@uptaeb.edu.ve','Av. Lara Res. Las Trinitarias',7,'2026-05-27 13:45:00','Atendido'),
(33,'V','27454631','Patricia Andreína','Colmenárez','1999-01-25','Femenino','Personal Obrero','04163209900','patricia.colmenarez@uptaeb.edu.ve','Carrera 15 con calle 50',7,'2026-05-27 14:30:00','Atendido'),
(34,'V','30463804','Gabriel Enrique','Suárez','2003-08-19','Masculino','Estudiante','04245701133','gabriel.suarez@gmail.com','Urb. La Concordia Calle 2',7,'2026-05-27 15:15:00','Atendido'),
(35,'V','19472977','Elena María','Castillo','1989-12-03','Femenino','Comunidad','04145802244','elena.castillo@gmail.com','Comunidad Ruíz Pineda',7,'2026-05-27 16:00:00','Atendido'),
(36,'V','28482150','Ricardo José','Ramírez','2000-05-14','Masculino','Estudiante','04126903322','ricardo.ramirez@gmail.com','Barrio Cerritos Blancos',7,'2026-05-27 17:00:00','Atendido'),
(37,'V','22491323','Vanessa Carolina','Gutiérrez','1993-09-28','Femenino','Personal Docente','04145904455','vanessa.gutierrez@uptaeb.edu.ve','Urb. Santa Elena Calle 5',8,'2026-10-06 12:30:00','Atendido'),
(38,'V','25509669','Fernando Antonio','Medina','1996-12-11','Masculino','Estudiante','04269105566','fernando.medina@gmail.com','Av. Vargas con carrera 21',8,'2026-10-06 13:15:00','Atendido'),
(39,'V','31518842','Camila Isabela','Herrera','2004-03-07','Femenino','Estudiante','04169206677','camila.herrera@gmail.com','El Manzano Sector El Parral',8,'2026-10-06 14:00:00','Atendido'),
(40,'V','15528015','Héctor Manuel','Vargas','1981-11-17','Masculino','Comunidad','04129307788','hector.vargas@gmail.com','Comunidad El Garabatal',8,'2026-10-06 14:45:00','Atendido'),
(41,'V','20537188','Diego Armando','Paredes','1991-01-30','Masculino','Estudiante','04245908899','diego.paredes@gmail.com','Barrio la Paz Calle 8',8,'2026-10-06 15:30:00','Atendido'),
(42,'V','26546361','Stephanie Beatriz','Lugo','1997-08-14','Femenino','Estudiante','04145309900','stephanie.lugo@gmail.com','Urb. la Rosaleda Calle 1',9,'2026-10-07 13:00:00','Atendido');
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
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jornada_diagnosticos`
--

LOCK TABLES `jornada_diagnosticos` WRITE;
/*!40000 ALTER TABLE `jornada_diagnosticos` DISABLE KEYS */;
INSERT INTO `jornada_diagnosticos` VALUES
(2,3,7,'Síndrome febril agudo y cefalea leve.','Reposo relativo por 48h y rehidratación oral.','Se entrega analgésico oral.','2025-02-12 12:35:00'),
(3,4,8,'Parasitosis intestinal sospechada y rinitis alérgica.','Tratamiento sintomático y antialérgico oral.','Recomendaciones de higiene alimentaria.','2025-02-12 13:15:00'),
(4,5,7,'Faringitis aguda eritematosa.','Antiséptico bucofaríngeo e hidratación.','Paciente refiere odinofagia de 3 días.','2025-02-12 13:55:00'),
(5,6,8,'Dermatitis de contacto en antebrazo.','Crema hidratante y limpieza local.','Alergia a detergente industrial.','2025-02-12 14:25:00'),
(6,7,7,'Lumbago mecánico agudo por esfuerzo físico.','Analgésico antiinflamatorio y compresa caliente.','Levantamiento de carga en talleres UPTAEB.','2025-02-12 15:05:00'),
(7,8,8,'Control médico preventivo. PA: 120/80 mmHg.','Estilo de vida saludable y dieta baja en sodio.','Vecina del sector Pueblo Nuevo.','2025-02-12 15:50:00'),
(8,9,7,'Hipertensión arterial Estadio 1 (PA 145/90 mmHg).','Cita por Medicina Interna para inicio de tratamiento.','Remitido a consulta regular.','2025-05-20 12:50:00'),
(9,10,8,'Normotensa. Glicemia capilar en ayunas 88 mg/dL.','Mantener hábitos alimenticios saludables.','Sin alteraciones detectadas.','2025-05-20 13:20:00'),
(10,11,7,'Glicemia alterada en ayunas (112 mg/dL).','Dieta hipoglucídica y reevaluación en 15 días.','Antecedente familiar de Diabetes Tipo 2.','2025-05-20 13:55:00'),
(11,12,8,'Prehipertensión arterial (PA 135/85 mmHg).','Disminución de ingesta de sal y actividad física regular.','Revisión en 1 mes.','2025-05-20 14:35:00'),
(12,13,7,'Evaluación cardiovascular dentro de límites normales (PA 115/75 mmHg).','Continuar práctica deportiva.','Atleta de baloncesto UPTAEB.','2025-05-20 15:20:00'),
(13,14,8,'Hipertensión arterial descompensada (PA 160/100 mmHg).','Sublingual administrado y reposo en camilla.','Remitida de urgencia al CDI más cercano.','2025-05-20 16:05:00'),
(14,15,7,'Miopía compuesta con astigmatismo en Ojo Izquierdo.','Fórmula corregida OD -1.25, OI -1.75. Uso permanente de lentes.','Refiere fatiga visual al leer.','2025-08-14 12:35:00'),
(15,16,8,'Presbicia corregida. Fatiga ocular por exposición a pantallas.','Lágrimas artificiales cada 8h y pausas activas.','Docente del PNF Informática.','2025-08-14 13:25:00'),
(16,17,7,'Conjuntivitis alérgica bilateral.','Gotas antialérgicas oftálmicas por 7 días.','Evitar frotarse los ojos.','2025-08-14 14:15:00'),
(17,18,8,'Agudeza visual 20/20 ambos ojos.','Sin observaciones patológicas.','Estudiante de Fisioterapia.','2025-08-14 15:05:00'),
(18,19,7,'Pterigión Grado I en ojo derecho.','Lentes de protección solar y lubricante ocular.','Paciente de la comunidad externa.','2025-08-14 15:50:00'),
(19,20,8,'Profilaxis realizada. Caries oclusal en pieza 36.','Restauración estética programada en odontología.','Buena higiene bucal.','2025-11-05 12:45:00'),
(20,21,7,'Gingivitis marginal crónica por acumulación de placa.','Tartrectomía ultrasónica y enjuague con Clorhexidina.','Técnica de cepillado explicada.','2025-11-05 13:35:00'),
(21,22,8,'Ausencias dentarias parciales en maxilar inferior.','Remisión al área de prótesis bucal.','Mantenimiento e higiene.','2025-11-05 14:20:00'),
(22,23,7,'Salud bucodental óptima. Profilaxis superficial.','Control semestral.','Sin hallazgos patológicos.','2025-11-05 15:05:00'),
(23,24,8,'Sensibilidad dentinaria leve en incisivos superiores.','Crema dental desensibilizante y fluorización.','Uso de cepillo suave.','2025-11-05 15:50:00'),
(24,25,7,'Inmunización con Toxoide Tetánico (2da Dosis) y Anti-Hepatitis B.','Compresa fría local en caso de dolor.','Personal de mantenimiento de áreas verdes.','2026-02-18 12:20:00'),
(25,26,8,'Refuerzo de Toxoide Tetánico administrado sin complicaciones.','Analgesia simple si presenta febrícula.','Estudiante universitario.','2026-02-18 13:05:00'),
(26,27,7,'Vacunación Anti-Hepatitis B (1ra Dosis).','Segunda dosis programada en 30 días.','Carnet de vacunación entregado.','2026-02-18 13:45:00'),
(27,28,8,'Vacunación contra Fiebre Amarilla aplicada.','Inmunización registrada.','Sin reacciones adversas inmediatas.','2026-02-18 14:25:00'),
(28,29,7,'Refuerzo de Toxoide Diftérico y Tetánico.','Reposo relativo del brazo inoculado.','Inmunización al día.','2026-02-18 15:05:00'),
(29,30,8,'Inmunización Anti-Hepatitis B (3ra Dosis - Esquema Completo).','Esquema de vacunación finalizado con éxito.','Constancia física emitida.','2026-02-18 15:45:00'),
(30,31,7,'Cefalea tensional secundario a estrés académico.','Analgésico oral cada 8h por 3 días e hidratación.','Evaluación en víspera de exámenes.','2026-05-27 13:05:00'),
(31,32,8,'Síndrome bronquial obstructivo leve / Rinitis alérgica.','Antihistamínico oral y broncodilatador inhalado si requiere.','Refiere tos seca nocturna.','2026-05-27 13:50:00'),
(32,33,7,'Gastritis aguda por omisión de comidas.','Protector gástrico y dieta blanda.','Indicaciones nutricionales.','2026-05-27 14:35:00'),
(33,34,8,'Esguince de tobillo derecho Grado 1.','Inmovilización suave con vendaje elástico y crioterapia local.','Trauma durante práctica deportiva en la cancha.','2026-05-27 15:20:00'),
(34,35,7,'Lumbalgia aguda y contractura muscular paravertebral.','Analgésico antiinflamatorio muscular.','Reposo físico por 5 días.','2026-05-27 16:05:00'),
(35,36,8,'Examen físico general satisfactorio.','Continuar con hábitos de vida saludable.','Chequeo médico de rutina.','2026-05-27 17:05:00'),
(36,37,7,'Evaluación postural y control de peso normal. IMC 22.4.','Mantener rutina de ejercicios.','Control preventivo semestral.','2026-10-06 12:35:00'),
(37,38,8,'Déficit ponderal leve (IMC 18.2).','Multivitamínico oral y orientación nutricional.','Remitido a Trabajo Social para evaluación de beca comedor.','2026-10-06 13:20:00'),
(38,39,7,'Examen físico dentro de límites normales.','Recomendaciones de actividad física.','Estudiante sana.','2026-10-06 14:05:00'),
(39,40,8,'Hipertensión arterial controlada (PA 130/80 mmHg).','Mantener medicación antihipertensiva habitual.','Paciente de 58 años de la comunidad.','2026-10-06 14:50:00'),
(40,41,7,'Cefalea por deshidratación leve.','Rehidratación oral con solución salina/glucosada.','Exposición prolongada al sol.','2026-10-06 15:35:00'),
(41,42,8,'Screening de estrés académico moderado.','Talleres de técnicas de relajación e higiene del sueño.','Remitida a consulta de Psicología UPTAEB.','2026-10-07 13:10:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jornada_insumos`
--

LOCK TABLES `jornada_insumos` WRITE;
/*!40000 ALTER TABLE `jornada_insumos` DISABLE KEYS */;
INSERT INTO `jornada_insumos` VALUES
(2,2,1,2,'Insumo utilizado en jornada médica'),
(3,3,1,1,'Insumo utilizado en jornada médica'),
(4,5,4,1,'Insumo utilizado en jornada médica'),
(5,5,5,2,'Insumo utilizado en jornada médica'),
(6,6,3,2,'Insumo utilizado en jornada médica'),
(7,13,5,1,'Insumo utilizado en jornada médica'),
(8,19,5,2,'Insumo utilizado en jornada médica'),
(9,20,5,1,'Insumo utilizado en jornada médica'),
(10,24,6,1,'Insumo utilizado en jornada médica'),
(11,24,4,1,'Insumo utilizado en jornada médica'),
(12,25,6,1,'Insumo utilizado en jornada médica'),
(13,26,6,1,'Insumo utilizado en jornada médica'),
(14,27,6,1,'Insumo utilizado en jornada médica'),
(15,30,1,2,'Insumo utilizado en jornada médica'),
(16,31,1,1,'Insumo utilizado en jornada médica'),
(17,32,8,1,'Insumo utilizado en jornada médica'),
(18,33,5,2,'Insumo utilizado en jornada médica'),
(19,33,4,1,'Insumo utilizado en jornada médica'),
(20,34,3,2,'Insumo utilizado en jornada médica'),
(21,40,7,1,'Insumo utilizado en jornada médica');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jornadas_medicas`
--

LOCK TABLES `jornadas_medicas` WRITE;
/*!40000 ALTER TABLE `jornadas_medicas` DISABLE KEYS */;
INSERT INTO `jornadas_medicas` VALUES
(2,'Jornada de Atención Médica Integral y Desparasitación','Médica Integral',50,'2025-02-12 08:00:00','2025-02-12 15:00:00','Sede Central - Plaza Bolivariana UPTAEB','Jornada médica preventiva dirigida a estudiantes de nuevo ingreso y comunidad universitaria.','Finalizada','2025-02-01 09:00:00'),
(3,'Despistaje de Hipertensión Arterial y Diabetes Mellitus','Despistaje HTA y Diabetes',40,'2025-05-20 08:30:00','2025-05-20 14:00:00','Edificio Argelia Laya - Planta Baja','Jornada epidemiológica de pesquisaje cardiovascular y control de glicemia en ayunas.','Finalizada','2025-05-10 10:00:00'),
(4,'Jornada de Salud Visual y Evaluación Optométrica','Salud Visual y Optometría',60,'2025-08-14 08:00:00','2025-08-14 16:00:00','Auditorio Libertador UPTAEB','Jornada de agudeza visual, refracción y evaluación médica oftalmológica básica.','Finalizada','2025-08-01 08:30:00'),
(5,'Jornada de Odontología Preventiva y Salud Bucal','Odontología Preventiva',35,'2025-11-05 08:00:00','2025-11-05 15:00:00','Clínica Odontológica UPTAEB - Módulo 3','Evaluación odontológica, profilaxis dental y aplicación de flúor a beneficiarios.','Finalizada','2025-10-25 11:00:00'),
(6,'Jornada Especial de Vacunación e Inmunización','Vacunación e Inmunización',80,'2026-02-18 08:00:00','2026-02-18 14:00:00','Comedor Universitario UPTAEB','Jornada de inmunización con esquema completo de Toxoide Tetánico, Hepatitis B y Fiebre Amarilla.','Finalizada','2026-02-05 09:30:00'),
(7,'Jornada de Salud Integral y Atención Médica General','Médica Integral',50,'2026-05-27 08:30:00','2026-05-27 16:00:00','Cancha Techada UPTAEB','Jornada médico-asistencial con despistaje de afecciones respiratorias, musculares y gastrointestinales.','Finalizada','2026-05-15 10:00:00'),
(8,'Jornada de Pesquisaje Médico y Evaluación Nutricional','Médica Integral',45,'2026-10-06 08:00:00','2026-10-06 14:30:00','Edificio de Salud y Desarrollo Estudiantil','Jornada activa de inicio de semestre para control antropométrico y valoración médica integral.','Activa','2026-10-01 08:00:00'),
(9,'Jornada de Salud Mental, Manejo del Estrés y Screening Médico','Salud Mental y Bienestar',30,'2026-10-28 08:00:00','2026-10-28 15:00:00','Sala de Usos Múltiples - Biblioteca Central','Jornada de promoción de salud mental, detección precoz de ansiedad/depresión y triage médico.','Activa','2026-10-05 11:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_referencias`
--

LOCK TABLES `log_referencias` WRITE;
/*!40000 ALTER TABLE `log_referencias` DISABLE KEYS */;
INSERT INTO `log_referencias` VALUES
(16,17,'Pendiente','Aceptada',7,'2025-01-16 10:15:00','Referencia aceptada. Paciente atendido e iniciado tratamiento digestivo.'),
(17,18,'Pendiente','Aceptada',5,'2025-02-11 14:20:00','Referencia aceptada. Se agenda primera sesión de terapia cognitivo-conductual.'),
(18,19,'Pendiente','Aceptada',9,'2025-03-21 09:30:00','Referencia aceptada. Iniciado trámite de estudio socioeconómico.'),
(19,20,'Pendiente','Aceptada',13,'2025-04-15 11:45:00','Referencia aceptada. Realizada inspección y registro de discapacidad.'),
(20,21,'Pendiente','Aceptada',3,'2025-05-19 16:00:00','Referencia aceptada. Incorporado a programa de apoyo emocional.'),
(21,22,'Pendiente','Aceptada',10,'2025-06-23 10:00:00','Referencia aceptada. Recaudos de FAMES procesados con éxito.'),
(22,23,'Pendiente','Aceptada',11,'2025-07-31 09:15:00','Referencia aceptada. Aplicada batería vocacional de seguimiento.'),
(23,24,'Pendiente','Aceptada',7,'2025-08-15 11:30:00','Referencia aceptada. Otoscopia realizada y remitido a audiometría.'),
(24,25,'Pendiente','Aceptada',3,'2025-09-26 15:45:00','Referencia aceptada. Iniciado acompañamiento perinatal.'),
(25,26,'Pendiente','Aceptada',9,'2026-02-19 10:30:00','Referencia aceptada. Registrada solicitud de exoneración institucional.'),
(26,27,'Pendiente','Rechazada',2,'2025-01-21 09:00:00','El trámite de justificación de inasistencias se realiza directamente en Secretaría Afecta.'),
(27,28,'Pendiente','Rechazada',13,'2025-03-29 10:20:00','Se requiere informe médico neurológico previo antes de proceder con el registro.'),
(28,29,'Pendiente','Rechazada',8,'2025-05-11 08:30:00','Beneficiario atendido el 15/04 en consulta regular sin requerir nueva evaluación.'),
(29,30,'Pendiente','Rechazada',12,'2025-08-19 11:00:00','El periodo de ajuste de secciones culminó la semana pasada.'),
(30,31,'Pendiente','Rechazada',7,'2025-11-23 09:15:00','Se sugiere canalizar la solicitud a través del programa FAMES de Trabajo Social.'),
(31,32,'Pendiente','Rechazada',10,'2026-03-13 14:00:00','Se orienta al estudiante hacia los programas de becas socioeconómicas disponibles.');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mantenimiento_vehiculos`
--

LOCK TABLES `mantenimiento_vehiculos` WRITE;
/*!40000 ALTER TABLE `mantenimiento_vehiculos` DISABLE KEYS */;
INSERT INTO `mantenimiento_vehiculos` VALUES
(2,5,'Correctivo','2026-10-02','Reemplazo de filtro de aceite por saturación y sustitución de correa de alternador agrietada.');
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobiliario`
--

LOCK TABLES `mobiliario` WRITE;
/*!40000 ALTER TABLE `mobiliario` DISABLE KEYS */;
INSERT INTO `mobiliario` VALUES
(2,1,6,1,'Bueno','Activo','2025-01-15 12:30:00','ErgoOffice','EO-120','Marrón/Negro','2025-01-10','Escritorio receptor principal','En perfecto estado'),
(3,2,6,1,'Bueno','Activo','2025-01-15 12:35:00','FlexFit','FX-300','Negro','2025-01-10','Silla giratoria recepción','Funcionamiento correcto'),
(4,7,6,3,'Bueno','Activo','2025-01-15 12:40:00','SitWell','SW-10','Azul','2025-01-10','Sillas de espera en recepción','Uso continuo'),
(5,5,1,2,'Bueno','Activo','2025-01-20 13:00:00','ErgoOffice','EO-140','Marrón Claro','2025-01-12','Escritorios cubículos Psicología','Equipados con gavetas'),
(6,2,1,2,'Bueno','Activo','2025-01-20 13:10:00','FlexFit','FX-500','Negro','2025-01-12','Sillas ergonómicas Psicología','Respaldo reclinable'),
(7,7,1,4,'Bueno','Activo','2025-01-20 13:15:00','SitWell','SW-20','Gris','2025-01-12','Sillas confidenciales consulta psicológica','Tapizado de tela'),
(8,1,2,2,'Bueno','Activo','2025-01-25 14:00:00','MueblesLara','ML-MED','Madera Caoba','2025-01-15','Escritorios médicos','Superficie lavable'),
(9,9,2,2,'Bueno','Activo','2025-01-25 14:15:00','MedEquip','ME-CAM','Azul Clínico','2025-01-15','Camillas de evaluación clínica','Con cabezal reclinable'),
(10,8,2,2,'Bueno','Activo','2025-01-25 14:20:00','MetalOffice','MO-4G','Gris Metalizado','2025-01-15','Archivadores de historias médicas','Con cerradura de seguridad'),
(11,5,3,2,'Bueno','Activo','2025-02-01 12:30:00','ErgoOffice','EO-100','Negro/Gris','2025-01-20','Escritorios Orientación','Buen estado'),
(12,2,3,2,'Bueno','Activo','2025-02-01 12:35:00','FlexFit','FX-200','Negro','2025-01-20','Sillas giratorias Orientación','Uso administrativo'),
(13,1,4,2,'Bueno','Activo','2025-02-05 13:00:00','MueblesLara','ML-TS','Madera Roble','2025-01-22','Escritorios Trabajo Social','Con soporte para CPU'),
(14,8,4,2,'Bueno','Activo','2025-02-05 13:10:00','MetalOffice','MO-4G','Gris Metalizado','2025-01-22','Archivadores de expedientes socioeconómicos','Cerradura reforzada'),
(15,5,5,1,'Bueno','Activo','2025-02-10 14:00:00','ErgoOffice','EO-ACCES','Beige','2025-01-25','Escritorio accesible para personas con discapacidad','Altura regulable'),
(16,11,5,1,'Bueno','Activo','2025-02-10 14:10:00','DriveMedical','DM-200','Cromo/Negro','2025-01-25','Silla de ruedas institucional de traslado','Para apoyo de movilidad'),
(17,1,8,1,'Bueno','Activo','2025-02-15 15:00:00','MueblesLara','ML-EXEC','Caoba Oscuro','2025-02-01','Escritorio Ejecutivo Dirección','Poblado con ala lateral'),
(18,10,6,1,'Bueno','Activo','2025-02-15 15:15:00','MueblesLara','ML-CONF','Caoba Oscuro','2025-02-01','Mesa de reuniones Sala de Conferencia','Capacidad 10 puestos');
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orientacion`
--

LOCK TABLES `orientacion` WRITE;
/*!40000 ALTER TABLE `orientacion` DISABLE KEYS */;
INSERT INTO `orientacion` VALUES
(2,70,'Orientación sobre hábitos de estudio y rendimiento académico.','El estudiante manifiesta vacíos metodológicos en la organización de su tiempo libre y estudio independiente.','Muestra excelente disposición para adoptar nuevas técnicas de lectura.','Crear una rutina de estudio fija de 2 horas diarias y emplear la técnica Pomodoro.','2024-11-20'),
(3,71,'Asesoría vocacional y clarificación de metas profesionales.','Consulta sobre opciones de desempeño profesional de su especialidad y áreas de inserción laboral.','Se acuerda una segunda sesión para evaluar áreas de preferencia laboral.','Revisar el diseño curricular del PNF e investigar el perfil de egreso en el área regional.','2024-12-15'),
(4,72,'Orientación para el abordaje del proyecto sociointegrador.','Dificultad en la selección de la comunidad y definición del diagnóstico participativo.','El estudiante asistirá a la asamblea comunitaria la próxima semana.','Coordinar reunión con el tutor institucional y solicitar formatos de vinculación comunitaria.','2025-01-25'),
(5,73,'Acompañamiento en la adaptación al entorno académico universitario.','Estudiante de primer trayecto reporta desorientación sobre trámites académicos institucionales.','Se hace entrega de la guía de procedimientos al estudiante.','Asistir a la jornada de inducción del PNF e inscribirse en las tutorías entre pares.','2025-02-18'),
(6,74,'Asesoría en preparación de síntesis curricular y entrevistas.','Búsqueda de orientación para optar a pasantías universitarias institucionales.','Revisión final de la síntesis curricular programada para el próximo lunes.','Estructurar el currículum resaltando proyectos académicos y competencias técnicas desarrolladas.','2025-03-22'),
(7,75,'Orientación ante el bajo rendimiento en evaluaciones continuas.','Acude remitido por la coordinación tras reprobar evaluaciones parciales del trayecto.','Compromiso firmado de asistencia regular a asesorías docentes.','Diseñar cuadro comparativo de asignaturas críticas e ingresar a círculos de estudio grupal.','2025-04-15'),
(8,76,'Orientación en la toma de decisiones sobre carga horaria.','Evaluación de cantidad de unidades crediticias inscritas frente a compromisos personales.','Trámite canalizado satisfactoriamente con la unidad de control de estudios.','Solicitar ajuste de horario para evitar solapamientos con su jornada laboral.','2025-05-30'),
(9,77,'Seguimiento pedagógico y planificación académica personal.','Sesión de control periódica para evaluar el cumplimiento del plan de estudio individual.','Resultados académicos positivos respecto a la consulta inicial.','Mantener el ritmo de entregas académicas y priorizar la redacción del informe final.','2025-06-25'),
(10,78,'Orientación para la reincorporación al trayecto académico.','Estudiante reincorporado solicita guía sobre normativa de evaluación y asignación de secciones.','Estudiante orientado satisfactoriamente sobre el reglamento de evaluación.','Visitar los departamentos académicos correspondientes para solicitar plan de contingencia.','2025-07-18'),
(11,79,'Asesoría pedagógica en metodologías de investigación aplicada.','Dudas conceptuales en la delimitación del problema y alcance de su proyecto universitario.','Muestra comprensión adecuada de la ruta metodológica a seguir.','Consultar bibliografía recomendada en el centro de documentación y coordinar asesoría técnica.','2025-08-28'),
(12,80,'Orientación sobre becas y apoyos socioacadémicos.','Consulta de requisitos institucionales para aplicar a programas de apoyo estudiantil.','Remisión realizada para trámite socioeconómico formal.','Consignar recaudos socioeconómicos actualizados en la unidad de Trabajo Social.','2025-09-20'),
(13,81,'Estrategias de afrontamiento del pánico escénico en defensas.','Temor infundado al presentar informes orales ante comités evaluadores.','Mejoría notable de la confianza personal durante la simulación.','Realizar ensayos grupales de exposición con retroalimentación entre compañeros.','2025-10-15'),
(14,82,'Orientación en la resolución de conflictos comunitarios de proyecto.','Desacuerdos entre los integrantes del equipo de proyecto sociointegrador.','El equipo de trabajo logró definir compromisos claros.','Establecer acta de acuerdos internos de trabajo y dividir responsabilidades por módulo.','2025-11-22'),
(15,83,'Asesoría vocacional para egresados de TSU hacia Ingeniería/Licenciatura.','Incertidumbre sobre la prosecución de estudios al nivel de grado.','Estudiante decide continuar sus estudios de prosecución académica.','Revisar las líneas de investigación disponibles en el tramo de prosecución del PNF.','2025-12-18'),
(16,84,'Orientación pedagógica sobre comprensión lectora y análisis técnico.','Dificultad en la interpretación de manuales y bibliografía técnica especializada.','Se entregan guías prácticas de análisis documental.','Utilizar técnicas de subrayado jerárquico y elaboración de esquemas conceptuales.','2026-01-20'),
(17,85,'Acompañamiento en el cumplimiento del servicio comunitario.','Dudas sobre las 120 horas lectivas requeridas y llenado de bitácoras de campo.','Proceso de vinculación comunitaria en marcha sin contratiempos.','Registrar actividades semanalmente en la bitácora física y solicitar firma del vocero.','2026-02-25'),
(18,86,'Orientación para la organización del tiempo entre estudio y trabajo.','El beneficiario realiza jornadas laborales vespertinas que afectan sus lecturas nocturnas.','Se logra una distribución horaria más equilibrada y realista.','Rediseñar horario semanal otorgando bloques prioritarios los fines de semana.','2026-03-18'),
(19,87,'Asesoría para la solicitud de traslados de sección o sede.','Inconvenientes de transporte público desde su lugar de residencia hasta la sede.','Tramitación administrativa en curso.','Consignar carta justificativa de residencia ante la coordinación del PNF.','2026-04-22'),
(20,88,'Orientación en técnicas de resolución de exámenes analíticos.','Reporta bloqueo mental al enfrentarse a pruebas escritas con límite de tiempo.','El estudiante pondrá en práctica las recomendaciones en el próximo examen.','Aplicar lecturas iniciales globales y responder primero las preguntas de mayor dominio.','2026-05-15'),
(21,89,'Acompañamiento pedagógico post-recuperación de salud.','Reincorporación tras reposo médico prolongado con acumulación de asignaciones.','Docentes notificados y receptivos al plan de entrega.','Coordinar con cada docente un cronograma de entregas extemporáneas justificadas.','2026-06-28'),
(22,90,'Orientación sobre canales institucionales de atención al estudiante.','Desconocimiento del catálogo de servicios transversales disponibles en la UPTAEB.','Se entrega tríptico informativo de los servicios de la institución.','Informar sobre las unidades de Psicología, Medicina, Trabajo Social y Biblioteca.','2026-07-20'),
(23,91,'Asesoría para la formulación de objetivos de investigación.','Redacción ambigua en los objetivos específicos del proyecto comunitario.','Objetivos corregidos y alineados con la metodología.','Utilizar la taxonomía de Bloom para seleccionar verbos en infinitivo medibles.','2026-08-25'),
(24,92,'Orientación preventiva sobre deserción estudiantil.','Dudas sobre continuar la carrera debido a desmotivación temporal.','Estudiante decide continuar matriculado con acompañamiento quincenal.','Reevaluar los logros alcanzados y definir metas a corto plazo.','2026-09-18'),
(25,93,'Evaluación de cierre de acompañamiento pedagógico.','Sesión final para verificar el logro de las metas académicas del trayecto.','Cierre de caso por cumplimiento satisfactorio de los objetivos planteados.','Mantener las metodologías de estudio consolidadas durante las sesiones.','2026-10-06');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
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
(6,'Ansiedad','General','2026-02-02'),
(10,'Cefalea tensional','Médica','2025-01-10'),
(11,'Gastritis aguda','Médica','2025-01-10'),
(12,'Faringoamigdalitis','Médica','2025-01-10'),
(13,'Asma bronquial','Médica','2025-01-10');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES
(1,'V','282814331','Repuestos Barquisimeto','04129990909','proveedor@gmail.es','Av. Venezuela con calle 15','Activo','2026-09-29 22:38:59'),
(2,'J','J-30495821-0','Distribuidora Yutong Lara, C.A.','0251-4458899','ventas@yutonglara.com.ve','Av. Las Industrias Calle 5 Barquisimeto','Activo','2025-01-10 13:00:00'),
(3,'J','J-40123987-5','Autopartes y Mantenimiento Barquisimeto, F.P.','0251-2334455','contacto@autobarquisimeto.com','Av. Venezuela con Calle 29','Activo','2025-01-15 14:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referencias`
--

LOCK TABLES `referencias` WRITE;
/*!40000 ALTER TABLE `referencias` DISABLE KEYS */;
INSERT INTO `referencias` VALUES
(17,1,3,1,7,2,'2025-01-15 13:30:00','Evaluación médica por somatización gastrointestinal.','Aceptada','Estudiante presenta gastritis reactiva vinculada a ansiedad académica.'),
(18,3,7,2,5,1,'2025-02-10 15:00:00','Apoyo psicológico por diagnóstico de cefalea tensional recurrente.','Aceptada','Se descarta causa orgánica severa; requiere manejo de estrés.'),
(19,4,2,3,9,4,'2025-03-20 18:00:00','Evaluación de vulnerabilidad socioeconómica para beca.','Aceptada','Estudiante con alto rendimiento en riesgo de deserción por motivos económicos.'),
(20,5,9,4,13,5,'2025-04-14 14:30:00','Valoración de condición motora para adecuación física.','Aceptada','Beneficiario requiere evaluación de accesibilidad en aulas de su PNF.'),
(21,6,13,5,3,1,'2025-05-18 19:15:00','Acompañamiento psicológico por proceso adaptativo a prótesis.','Aceptada','Estudiante en fase de adaptación motora con labilidad emocional ocasional.'),
(22,7,8,2,10,4,'2025-06-22 12:45:00','Solicitud de cobertura de exámenes de laboratorio por FAMES.','Aceptada','Paciente requiere exámenes especializados de alto costo.'),
(23,8,5,1,11,3,'2025-07-30 17:20:00','Orientación vocacional por duda de prosecución en PNF.','Aceptada','Tras superar crisis de ansiedad, se sugiere clarificación vocacional.'),
(24,9,11,3,7,2,'2025-08-14 14:00:00','Valoración auditiva por reporte de dificultades en clase.','Aceptada','Estudiante indica no escuchar con claridad al docente desde el fondo del aula.'),
(25,10,10,4,3,1,'2025-09-25 18:30:00','Atención psicológica a estudiante en estado de gestación.','Aceptada','Beneficiaria manifiesta angustia por manejo del tiempo entre embarazo y estudios.'),
(26,11,14,5,9,4,'2026-02-18 13:15:00','Tramitación de exoneración de arancel por carnet CONAPDIS.','Aceptada','Beneficiario con discapacidad sensorial auditiva requiere exoneración.'),
(27,12,7,2,2,3,'2025-01-20 19:00:00','Solicitud de tutoría académica por inasistencias médicas.','Rechazada','Paciente solicita justificación de faltas que no compete a orientación.'),
(28,13,3,1,13,5,'2025-03-28 15:30:00','Evaluación de déficit de atención para carnet de discapacidad.','Rechazada','Solicita registro de condición sin informe neuropediátrico avalado.'),
(29,14,11,3,8,2,'2025-05-10 18:15:00','Evaluación física preventiva general.','Rechazada','Beneficiario ya cuenta con chequeo médico vigente de este mismo mes.'),
(30,15,9,4,12,3,'2025-08-18 13:45:00','Reubicación de sección por motivos laborales.','Rechazada','Solicitud de cambio de sección fuera del periodo normativo del calendario.'),
(31,17,14,5,7,2,'2025-11-22 17:00:00','Prescripción de medicamentos especializados de alto costo.','Rechazada','El servicio médico institucional no dispone del medicamento solicitado en stock.'),
(32,18,5,1,10,4,'2026-03-12 14:20:00','Solicitud de asignación de vivienda estudiantil.','Rechazada','La institución no dispone de programa de residencia o vivienda estudiantil.'),
(33,19,3,1,8,2,'2025-12-05 15:15:00','Valoración médica por mareos y cefalea constante.','Pendiente','Paciente en terapia que reporta síntomas físicos persistentes en el aula.'),
(34,20,7,2,13,5,'2026-01-14 13:30:00','Evaluación de marcha claudicante por secuela traumática.','Pendiente','Se remite para valoración formal e incorporación al registro de accesibilidad.'),
(35,21,2,3,9,4,'2026-04-18 18:20:00','Evaluación caso familiar complejo para asignación de ayuda económica.','Pendiente','Estudiante de excelente promedio con severa restricción de ingresos familiares.'),
(36,22,10,4,12,3,'2026-06-22 14:45:00','Acompañamiento vocacional para reingreso académico.','Pendiente','Beneficiaria que retoma sus estudios tras suspensión temporal de matrícula.'),
(37,1,14,5,5,1,'2026-07-28 19:30:00','Apoyo psicológico por diagnóstico reciente de baja visión.','Pendiente','Estudiante requiere contención emocional tras pérdida progresiva de agudeza visual.'),
(38,3,8,2,3,1,'2026-10-02 13:00:00','Evaluación por somatización de estrés en sistema respiratorio.','Pendiente','Crisis asmáticas desencadenadas principalmente en semanas de exámenes.'),
(39,4,11,3,7,2,'2026-10-04 15:30:00','Revisión por fatiga crónica reportada durante entrevistas de seguimiento.','Pendiente','Estudiante manifiesta somnolencia excesiva diurna que afecta su rendimiento.'),
(40,5,9,4,8,2,'2026-10-06 18:00:00','Valoración médica de control para beneficiario de programa FAMES.','Pendiente','Verificación de evolución clínica previa a la entrega de reembolso quirúrgico.');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repuestos_mantenimiento`
--

LOCK TABLES `repuestos_mantenimiento` WRITE;
/*!40000 ALTER TABLE `repuestos_mantenimiento` DISABLE KEYS */;
INSERT INTO `repuestos_mantenimiento` VALUES
(2,2,2,'2'),
(3,2,5,'1');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repuestos_vehiculos`
--

LOCK TABLES `repuestos_vehiculos` WRITE;
/*!40000 ALTER TABLE `repuestos_vehiculos` DISABLE KEYS */;
INSERT INTO `repuestos_vehiculos` VALUES
(2,'Filtro de Aceite Diésel Yutong','Filtro para motor Yutong ZK6118H',6,2,'2025-01-20','Disponible'),
(3,'Filtro de Combustible Yutong','Filtro primario de trampa de agua',3,2,'2025-02-01','Disponible'),
(4,'Liga de Freno DOT4 1L','Envase de 1 litro de fluido de frenos',8,3,'2025-02-10','Disponible'),
(5,'Correa de Alternador Encava','Correa acanalada para motor ISUZU/Encava',2,3,'2025-02-15','Disponible'),
(6,'Batería 800Ah 12V Heavy Duty','Batería de alto amperaje para autobús',0,3,'2025-03-01','Agotado');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rutas`
--

LOCK TABLES `rutas` WRITE;
/*!40000 ALTER TABLE `rutas` DISABLE KEYS */;
INSERT INTO `rutas` VALUES
(2,'Ruta 1 - Tamaca / El Cují','UPTAEB - Av. Intercomunal Barquisimeto-Duaca - El Cují - Tamaca','Urbana','06:30:00','08:00:00','Sede UPTAEB','Sector Tamaca Centro','Activa','2025-01-15 12:00:00'),
(3,'Ruta 2 - Cabudare / Palavecino','UPTAEB - Av. Ribereña - Distribuidor Tarabana - Plaza Bolívar Cabudare','Suburbana','06:45:00','08:15:00','Sede UPTAEB','Plaza Bolívar de Cabudare','Activa','2025-01-15 12:30:00'),
(4,'Ruta 3 - Oeste / La Carucieña','UPTAEB - Av. Florencio Jiménez - Pueblo Nuevo - La Carucieña','Urbana','07:00:00','08:30:00','Sede UPTAEB','Redoma de La Carucieña','Activa','2025-01-15 13:00:00'),
(5,'Ruta 4 - Quíbor / Traslado Especial','UPTAEB - Carretera Lara-Zulia - Entrada Quíbor','Extraurbana','06:00:00','08:00:00','Sede UPTAEB','Plaza Bolívar de Quíbor','Inactiva','2025-02-01 14:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_de_servicio`
--

LOCK TABLES `solicitud_de_servicio` WRITE;
/*!40000 ALTER TABLE `solicitud_de_servicio` DISABLE KEYS */;
INSERT INTO `solicitud_de_servicio` VALUES
(21,1,13,5),
(22,1,1,3),
(23,1,3,3),
(24,1,4,3),
(25,1,5,3),
(26,1,6,3),
(27,1,7,3),
(28,1,8,3),
(29,1,9,3),
(30,1,10,5),
(31,1,11,5),
(32,1,12,5),
(33,1,13,5),
(34,1,14,5),
(35,1,15,5),
(36,1,17,5),
(37,1,18,5),
(38,1,19,6),
(39,1,20,6),
(40,1,21,6),
(41,1,22,6),
(42,1,23,6),
(43,1,25,6),
(44,1,26,6),
(45,1,27,6),
(46,2,1,7),
(47,2,3,7),
(48,2,4,7),
(49,2,5,7),
(50,2,6,7),
(51,2,7,7),
(52,2,8,7),
(53,2,9,7),
(54,2,10,7),
(55,2,11,7),
(56,2,12,7),
(57,2,13,7),
(58,2,14,8),
(59,2,15,8),
(60,2,17,8),
(61,2,18,8),
(62,2,19,8),
(63,2,20,8),
(64,2,21,8),
(65,2,22,8),
(66,2,23,8),
(67,2,25,8),
(68,2,26,8),
(69,2,27,8),
(70,3,1,2),
(71,3,3,2),
(72,3,4,2),
(73,3,5,2),
(74,3,6,2),
(75,3,7,2),
(76,3,8,2),
(77,3,9,2),
(78,3,10,11),
(79,3,11,11),
(80,3,12,11),
(81,3,13,11),
(82,3,14,11),
(83,3,15,11),
(84,3,17,11),
(85,3,18,11),
(86,3,19,12),
(87,3,20,12),
(88,3,21,12),
(89,3,22,12),
(90,3,23,12),
(91,3,25,12),
(92,3,26,12),
(93,3,27,12),
(94,5,1,13),
(95,5,3,13),
(96,5,4,13),
(97,5,5,13),
(98,5,6,13),
(99,5,7,13),
(100,5,8,13),
(101,5,9,13),
(102,5,10,14),
(103,5,11,14),
(104,5,12,14),
(105,5,13,14),
(106,5,14,14),
(107,5,15,14),
(108,5,17,14),
(109,5,18,14),
(110,5,19,14),
(111,5,20,14),
(112,5,21,14),
(113,5,22,14),
(114,5,23,14),
(115,5,25,14),
(116,5,26,14),
(117,5,27,14),
(137,4,1,9),
(138,4,4,9),
(139,4,6,9),
(140,4,8,10),
(141,4,10,10),
(142,4,3,10),
(143,4,1,9),
(144,4,4,9),
(145,4,6,9),
(146,4,8,10),
(147,4,10,10),
(148,4,5,10),
(149,4,1,9),
(150,4,4,9),
(151,4,6,9),
(152,4,8,10),
(153,4,10,10),
(154,4,7,10),
(155,4,3,9),
(156,4,5,9),
(157,4,7,9),
(158,4,9,10),
(159,4,11,10),
(160,4,13,10);
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_equipo`
--

LOCK TABLES `tipo_equipo` WRITE;
/*!40000 ALTER TABLE `tipo_equipo` DISABLE KEYS */;
INSERT INTO `tipo_equipo` VALUES
(1,'Monitor LCD','Monitor ACER',1,'2025-11-12'),
(2,'Video Beam HP','Compatible con HDMi',1,'2026-02-02'),
(5,'Computadora de Escritorio (CPU)','Unidad central de procesamiento de escritorio',1,'2025-01-10'),
(6,'Monitor LCD / LED','Pantalla de visualización',1,'2025-01-10'),
(7,'Impresora Multifuncional','Impresora fotocopiadora y escáner',1,'2025-01-10'),
(8,'Video Beam','Proyector multimedia',1,'2025-01-10'),
(9,'Equipo de Computación Portátil (Laptop)','Computadora portátil de oficina',1,'2025-01-10'),
(10,'Tensiómetro de Pared','Equipo médico para toma de presión arterial',1,'2025-01-10'),
(11,'Sistema de Audio Amplificado','Altavoz amplificado para sala de conferencias',1,'2025-01-10');
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_mobiliario`
--

LOCK TABLES `tipo_mobiliario` WRITE;
/*!40000 ALTER TABLE `tipo_mobiliario` DISABLE KEYS */;
INSERT INTO `tipo_mobiliario` VALUES
(1,'Escritorio de madera','Escritorio de madera compacto',1,'2025-11-12'),
(2,'Silla giratoria','Silla que gira 360 grado',1,'2026-02-02'),
(5,'Escritorio Ergonómico','Escritorio modulado de oficina',1,'2025-01-10'),
(6,'Silla Giratoria Ergonómica','Silla de oficina con ruedas y regulador de altura',1,'2025-01-10'),
(7,'Silla de Visitante','Silla fija para atención al público/pacientes',1,'2025-01-10'),
(8,'Archivador Metálico','Archivador de metal de 4 gavetas',1,'2025-01-10'),
(9,'Camilla de Examen Médico','Camilla clínica tapizada para evaluación médica',1,'2025-01-10'),
(10,'Mesa de Conferencias','Mesa ejecutiva ovalada para reuniones',1,'2025-01-10'),
(11,'Silla de Ruedas Institucional','Silla de ruedas plegable para asistencia',1,'2025-01-10');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehiculos`
--

LOCK TABLES `vehiculos` WRITE;
/*!40000 ALTER TABLE `vehiculos` DISABLE KEYS */;
INSERT INTO `vehiculos` VALUES
(3,'A01AB2C','Yutong ZK6118H','Autobús','2024-03-15','Activo'),
(4,'A02CD3E','Encava ENT-610','Autobús','2024-06-20','Activo'),
(5,'A03EF4G','Yutong ZK6852','Autobús','2024-09-10','Mantenimiento'),
(6,'A04GH5I','Chevrolet LUV D-Max','Camioneta','2025-01-15','Activo'),
(7,'A05IJ6K','Toyota Corolla','Automóvil','2025-02-01','Inactivo');
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
-- Current Database: `dirpoles_business`
--

USE `dirpoles_business`;

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

-- Dump completed on 2026-10-20 15:16:37
