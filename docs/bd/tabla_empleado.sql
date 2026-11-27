/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.3.2-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: dirpoles_security
-- ------------------------------------------------------
-- Server version	12.3.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleado`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `empleado` WRITE;
/*!40000 ALTER TABLE `empleado` DISABLE KEYS */;
INSERT INTO `empleado` VALUES
(1,'Roberth','Matos','V','28281433','admin@gmail.com','04129298008',6,'2002-04-05','Calle 53 y 54 con carrera 14','$2y$12$XtOhu8QO2qeqxaSUkO3oaeDFfPAPvuEB/jiUra78vLPSAwiM2G7Nq',1,'2025-04-17'),
(2,'Jhan','Guevara','V','13652666','jhanguevara@gmail.com','04129991212',4,'1994-01-12','Av. Fuerzas armadas con 54','$2y$10$TJUNBZT3ortgGw8PovyFe.i3W2eIa4kwTjaevG7six7e0BFXcTyeS',1,'2026-09-15'),
(3,'Iris','Alvarez','V','12023052','irisalva19@gmail.com','04245289178',1,'1974-09-17','Carrera 13 con calle 54','$2y$10$cvFRuU76DCQ.wk5H6H7Ql.s8VWADXKLp.g78VeMQGyCglULyUyJdu',1,'2026-09-15'),
(4,'Juan','Melendez','V','12023051','juanmelendez@gmail.com','04145004988',8,'1994-01-30','Calle 1 de barrio unión','$2y$10$QrCkeLcPHKk0N9sfA8sHUO.HYBpIg1rumUd73uFEygMP92c/i8mn6',1,'2026-09-30'),
(5,'Yaselis Gabriela','Contreras Salas','V','14872391','yaselis.contreras21@gmail.com','04147318264',1,'1991-03-12','Av. 5 de Julio con calle 14, Urb. Don Rómulo, Barquisimeto',NULL,1,'2024-11-18'),
(6,'María Alejandra','Ríos Rodríguez','V','17543826','maria.alejandra.rios28@gmail.com','04247645193',1,'1989-07-24','Carrera 16 con calle 51, Barrio Belén, Barquisimeto',NULL,1,'2025-01-13'),
(7,'Carlos Eduardo','Sánchez Bracamonte','V','19632847','carlos.sanchez47@hotmail.com','04167029458',2,'1985-11-05','Urb. Buen Pastor, calle 12 con Av. Las Américas, Barquisimeto',NULL,1,'2024-08-05'),
(8,'Yuri Alexandra','Boscán Anzola','V','16754209','yuri.boscan18@gmail.com','04127483621',2,'1993-04-18','Calle 8 con carrera 17, Sector Centro, Barquisimeto',NULL,1,'2025-03-10'),
(9,'Luis Armando','Gutiérrez Nieves','V','13529764','luis.gutierrez29@gmail.com','04267195842',3,'1988-01-30','Av. Libertador con calle 6, Urb. Sucre, Barquisimeto',NULL,1,'2024-09-16'),
(10,'Keila Andrea','Montiel Aponte','V','20481637','keila.montiel34@gmail.com','04227562938',3,'1994-09-14','Sector El Tránsito, calle 30 con carrera 9, Barquisimeto',NULL,1,'2026-09-08'),
(11,'Edward José','Urdaneta Briceño','V','18364725','edward.urdaneta52@gmail.com','04147036185',4,'1987-06-02','Urb. El Tostao, calle 14 con Av. 5 de Julio, Barquisimeto',NULL,1,'2024-12-02'),
(12,'Nohelia Alexandra','Quintero Márquez','V','22159408','nohelia.quintero45@gmail.com','04247419627',4,'1992-10-27','Carrera 11 con calle 29, Barrio El Maní, Barquisimeto',NULL,1,'2025-08-18'),
(13,'Yhon Alexander','Guerra Marcano','V','15928374','yhon.guerra37@gmail.com','04127854036',5,'1990-02-16','Av. Carabobo con calle 15, Urb. Daniel Camacho, Barquisimeto',NULL,1,'2025-02-03'),
(14,'Marielva Yulieth','Hernández Briceño','V','24671583','marielva.hernandez61@gmail.com','04167268491',5,'1996-08-08','Calle 28 con carrera 6, Sector Pío Tamayo, Barquisimeto',NULL,1,'2026-10-02');
/*!40000 ALTER TABLE `empleado` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-11-27  0:59:07
