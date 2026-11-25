-- =====================================================================
-- SEED DE BENEFICIARIOS (TANDA 2) — 72 registros sintéticos realistas
-- Ubicación: docs/bd/seed_nuevas/seed_beneficiarios2.sql
-- Módulo: Beneficiarios (id_modulo 2)
--
-- Cobertura Temporal: 15/01/2024 al 17/11/2026 (Ideal para microservicio IA)
-- Distribución: 6 beneficiarios por cada uno de los 12 PNF (72 total)
-- Género: Equilibrado (36 Femenino / 36 Masculino)
-- Estatus: ~92% Activos (1) / ~8% Inactivos (0)
--
-- Cumplimiento estricto de validaciones (BeneficiarioModel + validaciones.js):
--   * tipo_cedula en ('V','E') y cedula de 8 dígitos sin colisión global.
--   * Unicidad GLOBAL de cédula/correo/teléfono contra:
--       - dirpoles_security.empleado
--       - dirpoles_business.beneficiario
--       - dirpoles_business.proveedores
--   * nombres/apellidos: solo letras, acentos y espacios (<= 20 caracteres).
--   * correo válido (<= 50 caracteres).
--   * telefono 11 dígitos con prefijos válidos: 0412|0414|0416|0422|0424|0426.
--   * seccion formato NNNN-S (trayecto 1-4, sedes M, C, J, U, B).
--   * id_pnf resuelto dinámicamente por nombre contra tabla pnf (estatus = 1).
--   * fecha_nac válida y no futura; direccion real en Lara (<= 255 chars).
--
-- ES 100% IDEMPOTENTE: solo inserta las filas que no existan previamente.
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO beneficiario
    (id_pnf, seccion, nombres, apellidos, tipo_cedula, cedula,
     fecha_nac, telefono, correo, genero, direccion, estatus, fecha_creacion)
SELECT p.id_pnf, v.seccion, v.nombres, v.apellidos, v.tipo_cedula, v.cedula,
       v.fecha_nac, v.telefono, v.correo, v.genero, v.direccion, v.estatus, v.fecha_creacion
FROM (
    -- ================================================================
    -- COHORTE 2024 (24 Registros: Enero 2024 a Diciembre 2024)
    -- ================================================================
    SELECT 'PNF Informática' AS pnf_nombre, '1101-M' AS seccion, 'Valeria Sofía' AS nombres, 'Colmenárez Gil' AS apellidos,
           'V' AS tipo_cedula, '28114201' AS cedula, '2004-03-12' AS fecha_nac, '04125101122' AS telefono,
           'valeria.colmenarez01@gmail.com' AS correo, 'F' AS genero,
           'Carrera 19 con calle 25, Sector Centro, Barquisimeto' AS direccion, 1 AS estatus, '2024-01-15' AS fecha_creacion
    UNION ALL SELECT 'PNF Administración','2201-C','Diego Alejandro','Paredes Riera','V','29215302','2003-07-24','04145112233','diego.paredes02@gmail.com','M','Av. Libertador con calle 33, Urb. Sucre, Barquisimeto',1,'2024-01-28'
    UNION ALL SELECT 'PNF Contaduría Pública','3102-M','Camila Victoria','Castillo Alvarado','V','30316403','2005-02-18','04245123344','camila.castillo03@gmail.com','F','Urb. El Tostao, calle 12 con Av. 5 de Julio, Barquisimeto',1,'2024-02-10'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','1401-J','Gabriel José','Montilla Barreto','V','31417504','2006-05-30','04165134455','gabriel.montilla04@gmail.com','M','Calle 8 con carrera 15, Barrio Unión, Barquisimeto',1,'2024-02-22'
    UNION ALL SELECT 'PNF Deporte','2501-U','Daniela Beatriz','Medina Carrillo','V','27518605','2002-11-09','04265145566','daniela.medina05@gmail.com','F','Urb. Don Rómulo, Av. 5 de Julio con calle 18, Barquisimeto',1,'2024-03-08'
    UNION ALL SELECT 'PNF Turismo','3302-M','Mateo Jesús','Rojas Guédez','V','26619706','2001-09-15','04225156677','mateo.rojas06@yahoo.es','M','Barrio Belén, carrera 17 con calle 52, Barquisimeto',1,'2024-03-25'
    UNION ALL SELECT 'PNF Ciencias de la Información','1601-C','Paula Andrea','Hernández Silva','V','25720807','2000-08-20','04125167788','paula.hernandez07@gmail.com','F','Carrera 22 con calle 30, Sector Obrero, Barquisimeto',1,'2024-04-12'
    UNION ALL SELECT 'PNF Sistemas de Calidad y Ambiente','2102-B','Samuel David','Pérez Escalona','V','24821908','1999-04-14','04145178899','samuel.perez08@gmail.com','M','Av. Las Américas, Urb. Buen Pastor, calle 7, Barquisimeto',1,'2024-04-29'
    UNION ALL SELECT 'PNF Agroalimentación','2701-U','Lucía Valentina','Gómez Morillo','V','23922019','1998-12-03','04245189900','lucia.gomez09@gmail.com','F','Sector Alto de la Cruz, calle 38 con carrera 9, Barquisimeto',1,'2024-05-14'
    UNION ALL SELECT 'PNF Distribución y Logística','3401-C','Sebastián José','Torres Sequera','V','29023120','2003-01-25','04165201122','sebastian.torres10@gmail.com','M','Urb. Daniel Camacho, Av. Carabobo con calle 16, Barquisimeto',1,'2024-05-30'
    UNION ALL SELECT 'PNF Materiales Industriales','2602-B','Isabella María','Navas Valera','V','30124231','2004-10-17','04265212233','isabella.navas11@gmail.com','F','Barrio Tamarindo, carrera 4 con calle 42, Barquisimeto',0,'2024-06-11'
    UNION ALL SELECT 'PNF Procesos Químicos','3501-C','Alejandro José','Soto Meléndez','V','31225342','2005-06-08','04225223344','alejandro.soto12@yahoo.es','M','Av. Venezuela con calle 21, Urb. 15 de Enero, Barquisimeto',1,'2024-06-26'
    UNION ALL SELECT 'PNF Informática','1202-B','Victoria Elena','Chávez Oropeza','V','28326453','2002-03-30','04125234455','victoria.chavez13@gmail.com','F','Calle 54 con carrera 13, Barquisimeto',1,'2024-07-09'
    UNION ALL SELECT 'PNF Administración','2301-B','Leonardo Daniel','Briceño Falcón','V','27427564','2001-08-11','04145245566','leonardo.briceno14@gmail.com','M','Urb. Santa Rosa, Av. Paramacay con calle 8, Barquisimeto',1,'2024-07-24'
    UNION ALL SELECT 'PNF Contaduría Pública','1302-M','Mariana Coromoto','Marrero Peraza','V','26528675','2000-05-19','04245256677','mariana.marrero15@gmail.com','F','Barrio San José, calle 28 con carrera 14, Barquisimeto',1,'2024-08-13'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','2101-M','Ricardo Javier','Vargas Cordero','V','25629786','1999-09-07','04165267788','ricardo.vargas16@hotmail.com','M','Sector Pío Tamayo, calle 23 con carrera 8, Barquisimeto',1,'2024-08-28'
    UNION ALL SELECT 'PNF Deporte','1901-U','Natalia Carolina','Salas Aranguren','V','24730897','1998-01-16','04265278899','natalia.salas17@gmail.com','F','Urb. La Carreta, calle 11 con Av. Santiago Rodríguez, Barquisimeto',1,'2024-09-10'
    UNION ALL SELECT 'PNF Turismo','2301-M','Mauricio Enrique','Reyes Camacaro','V','29831908','2004-12-05','04225289900','mauricio.reyes18@gmail.com','M','Barrio La Sequita, carrera 20 con calle 31, Barquisimeto',1,'2024-09-27'
    UNION ALL SELECT 'PNF Ciencias de la Información','3301-J','Fabiana del Valle','Quero Linárez','V','30932019','2005-04-22','04125301122','fabiana.quero19@gmail.com','F','Carrera 16 con calle 40, Barrio El Recreo, Barquisimeto',1,'2024-10-15'
    UNION ALL SELECT 'PNF Sistemas de Calidad y Ambiente','3201-B','Santiago Andrés','Bermúdez Yépez','V','31033120','2006-02-14','04145312233','santiago.bermudez20@gmail.com','M','Urb. 6 de Marzo, calle 9 con carrera 12, Barquisimeto',1,'2024-10-31'
    UNION ALL SELECT 'PNF Agroalimentación','1502-M','Bárbara Teresa','Alvarado Parra','V','28134231','2003-09-03','04245323344','barbara.alvarado21@gmail.com','F','Barrio El Maní, carrera 18 con calle 27, Barquisimeto',1,'2024-11-12'
    UNION ALL SELECT 'PNF Distribución y Logística','2201-U','Fernando José','Suárez Mendoza','V','27235342','2001-10-29','04165334455','fernando.suarez22@gmail.com','M','Urb. Javier Este, Av. Los Leones con calle 5, Barquisimeto',0,'2024-11-26'
    UNION ALL SELECT 'PNF Materiales Industriales','3101-C','Adriana Mercedes','Pineda Lameda','V','26336453','2000-07-06','04265345566','adriana.pineda23@gmail.com','F','Calle 14 con carrera 26, Urb. Sucre, Barquisimeto',1,'2024-12-05'
    UNION ALL SELECT 'PNF Procesos Químicos','2402-U','Guillermo Antonio','Godoy Bastidas','V','25437564','1999-03-18','04225356677','guillermo.godoy24@gmail.com','M','Carrera 7 con calle 34, Sector El Tránsito, Barquisimeto',1,'2024-12-18'

    -- ================================================================
    -- COHORTE 2025 (24 Registros: Enero 2025 a Diciembre 2025)
    -- ================================================================
    UNION ALL SELECT 'PNF Informática','2103-M','Verónica Isabel','Lovera Mogollón','V','29538675','2004-06-25','04125367788','veronica.lovera25@gmail.com','F','Av. 20 con calle 45, Sector Centro, Barquisimeto',1,'2025-01-14'
    UNION ALL SELECT 'PNF Administración','3202-C','Andrés Eduardo','Ochoa Terán','V','30639786','2005-08-14','04145378899','andres.ochoa26@gmail.com','M','Calle 32 con carrera 10, Barrio Unión, Barquisimeto',1,'2025-01-27'
    UNION ALL SELECT 'PNF Contaduría Pública','2105-M','Diana Patricia','Urbina Zambrano','V','31740897','2006-01-31','04245389900','diana.urbina27@gmail.com','F','Urb. El Tostao, calle 9 con carrera 3, Barquisimeto',1,'2025-02-11'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','3101-J','Javier Ignacio','Lucena Garrido','V','28841908','2003-11-12','04165401122','javier.lucena28@gmail.com','M','Barrio Belén, carrera 15 con calle 48, Barquisimeto',1,'2025-02-24'
    UNION ALL SELECT 'PNF Deporte','3103-U','Claudia Virginia','Páez Santeliz','V','27942019','2002-04-04','04265412233','claudia.paez29@gmail.com','F','Av. Libertador con calle 24, Urb. Sucre, Barquisimeto',1,'2025-03-10'
    UNION ALL SELECT 'PNF Turismo','1701-U','Héctor Alfonso','Giménez Crespo','V','26043120','2000-12-28','04225423344','hector.gimenez30@yahoo.es','M','Sector Obrero, carrera 21 con calle 14, Barquisimeto',1,'2025-03-26'
    UNION ALL SELECT 'PNF Ciencias de la Información','2201-B','Silvia Raquel','Sánchez Oviedo','V','25144231','1999-07-15','04125434455','silvia.sanchez31@gmail.com','F','Urb. Don Rómulo, carrera 11 con calle 16, Barquisimeto',1,'2025-04-08'
    UNION ALL SELECT 'PNF Sistemas de Calidad y Ambiente','1201-B','Ignacio Ramón','Daza Colmenárez','V','29245342','2004-02-09','04145445566','ignacio.daza32@gmail.com','M','Calle 18 con carrera 5, Barrio La Loma, Barquisimeto',1,'2025-04-22'
    UNION ALL SELECT 'PNF Agroalimentación','3101-J','Rocío Milagros','Leal Chirinos','V','30346453','2005-09-21','04245456677','rocio.leal33@gmail.com','F','Av. 5 de Julio, Urb. 6 de Marzo, calle 12, Barquisimeto',1,'2025-05-13'
    UNION ALL SELECT 'PNF Distribución y Logística','1401-C','César Augusto','Aguilar Colina','V','31447564','2006-03-10','04165467788','cesar.aguilar34@gmail.com','M','Sector Alto de la Cruz, calle 44 con carrera 10, Barquisimeto',1,'2025-05-28'
    UNION ALL SELECT 'PNF Materiales Industriales','1801-B','Elena Cristina','Yépez Carucí','V','28548675','2003-05-17','04265478899','elena.yepez35@gmail.com','F','Urb. Daniel Camacho, carrera 17 con calle 19, Barquisimeto',1,'2025-06-10'
    UNION ALL SELECT 'PNF Procesos Químicos','1701-U','Álvaro Luis','Barrios Valero','V','27649786','2001-11-23','04225489900','alvaro.barrios36@gmail.com','M','Barrio Tamarindo, carrera 2 con calle 39, Barquisimeto',1,'2025-06-25'
    UNION ALL SELECT 'PNF Informática','3104-B','Irene Coromoto','Carreño Hurtado','V','26750897','2000-10-11','04125501122','irene.carreno37@gmail.com','F','Urb. Santa Rosa, calle 15 con Av. Paramacay, Barquisimeto',1,'2025-07-15'
    UNION ALL SELECT 'PNF Administración','1102-M','Rafael Vicente','Morales Rivero','V','25851908','1999-08-01','04145512233','rafael.morales38@gmail.com','M','Carrera 14 con calle 50, Barrio Belén, Barquisimeto',1,'2025-07-29'
    UNION ALL SELECT 'PNF Contaduría Pública','3201-M','Aura Marina','Campos Tovar','V','29952019','2004-11-04','04245523344','aura.campos39@gmail.com','F','Av. Las Américas, Urb. Buen Pastor, calle 14, Barquisimeto',1,'2025-08-12'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','2401-J','Marcos Antonio','Peña Villegas','V','30053120','2005-03-27','04165534455','marcos.pena40@gmail.com','M','Barrio San José, carrera 18 con calle 30, Barquisimeto',0,'2025-08-26'
    UNION ALL SELECT 'PNF Deporte','2101-U','Carolina Esther','Mora Antequera','V','31154231','2006-07-19','04265545566','carolina.mora41@gmail.com','F','Sector Pío Tamayo, calle 27 con carrera 6, Barquisimeto',1,'2025-09-09'
    UNION ALL SELECT 'PNF Turismo','3401-M','Víctor Manuel','Infante Palencia','V','28255342','2002-12-14','04225556677','victor.infante42@yahoo.es','M','Urb. La Carreta, Av. 14 con calle 8, Barquisimeto',1,'2025-09-23'
    UNION ALL SELECT 'PNF Ciencias de la Información','1702-C','Norma Cecilia','Guanipa Sira','V','27356453','2001-06-29','04125567788','norma.guanipa43@gmail.com','F','Calle 24 con carrera 8, Sector Centro, Barquisimeto',1,'2025-10-14'
    UNION ALL SELECT 'PNF Sistemas de Calidad y Ambiente','3101-B','Nelson Enrique','Brito Cañizalez','V','26457564','2000-02-16','04145578899','nelson.brito44@gmail.com','M','Barrio La Sequita, carrera 24 con calle 28, Barquisimeto',1,'2025-10-28'
    UNION ALL SELECT 'PNF Agroalimentación','2201-U','Mónica Alejandra','Pernía Sivira','V','25558675','1999-10-05','04245589900','monica.pernia45@gmail.com','F','Urb. El Tostao, Av. 9 con calle 14, Barquisimeto',1,'2025-11-11'
    UNION ALL SELECT 'PNF Distribución y Logística','2501-C','Gustavo Adolfo','Cuicas Manzano','V','29659786','2004-04-18','04165601122','gustavo.cuicas46@gmail.com','M','Carrera 15 con calle 38, Barrio El Maní, Barquisimeto',1,'2025-11-25'
    UNION ALL SELECT 'PNF Materiales Industriales','2202-B','Gladys Josefina','Guillén Piña','V','30760897','2005-01-26','04265612233','gladys.guillen47@gmail.com','F','Urb. Javier Este, calle 4 con Av. Los Leones, Barquisimeto',1,'2025-12-09'
    UNION ALL SELECT 'PNF Procesos Químicos','3101-U','Julio César','Corao Vielma','V','31861908','2006-08-30','04225623344','julio.corao48@gmail.com','M','Av. Libertador con calle 16, Urb. Sucre, Barquisimeto',1,'2025-12-16'

    -- ================================================================
    -- COHORTE 2026 (24 Registros: Enero 2026 al 17 de Noviembre 2026)
    -- ================================================================
    UNION ALL SELECT 'PNF Informática','4101-M','Ligia Margarita','Heredia Mogollón','V','28962019','2003-03-08','04125634455','ligia.heredia49@gmail.com','F','Carrera 17 con calle 46, Barrio Belén, Barquisimeto',1,'2026-01-13'
    UNION ALL SELECT 'PNF Administración','4201-B','Pedro Vicente','Blanco Mujica','V','27063120','2001-09-22','04145645566','pedro.blanco50@gmail.com','M','Sector Obrero, carrera 23 con calle 11, Barquisimeto',1,'2026-01-27'
    UNION ALL SELECT 'PNF Contaduría Pública','4101-C','Lourdes Teresa','Fontaines Prado','V','26164231','2000-06-12','04245656677','lourdes.fontaines51@gmail.com','F','Urb. Don Rómulo, calle 21 con Av. 5 de Julio, Barquisimeto',1,'2026-02-10'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','4401-J','Óscar Enrique','Gil Castrillo','V','25265342','1999-11-25','04165667788','oscar.gil52@gmail.com','M','Calle 10 con carrera 19, Barrio El Maní, Barquisimeto',1,'2026-02-24'
    UNION ALL SELECT 'PNF Deporte','4101-U','Rosalba María','Orellana Ramos','V','29366453','2004-07-14','04265678899','rosalba.orellana53@gmail.com','F','Urb. El Tostao, calle 16 con carrera 5, Barquisimeto',1,'2026-03-09'
    UNION ALL SELECT 'PNF Turismo','4301-M','Rubén Darío','Coronado Pire','V','30467564','2005-04-02','04225689900','ruben.coronado54@yahoo.es','M','Barrio Tamarindo, carrera 6 con calle 36, Barquisimeto',1,'2026-03-23'
    UNION ALL SELECT 'PNF Ciencias de la Información','4201-C','Carmen Elena','Carcache Gómez','V','31568675','2006-12-01','04125701122','carmen.carcache55@gmail.com','F','Sector Alto de la Cruz, calle 40 con carrera 7, Barquisimeto',1,'2026-04-14'
    UNION ALL SELECT 'PNF Sistemas de Calidad y Ambiente','4102-B','Felipe José','Pacheco Nelo','V','28669786','2003-02-17','04145712233','felipe.pacheco56@gmail.com','M','Av. Venezuela con calle 12, Urb. 15 de Enero, Barquisimeto',1,'2026-04-28'
    UNION ALL SELECT 'PNF Agroalimentación','4101-U','Beatriz Eugenia','Timaure Vargas','V','27770897','2002-08-30','04245723344','beatriz.timaure57@gmail.com','F','Barrio San José, carrera 21 con calle 32, Barquisimeto',1,'2026-05-12'
    UNION ALL SELECT 'PNF Distribución y Logística','4401-C','Manuel Salvador','Méndez Sigala','V','26871908','2001-05-15','04165734455','manuel.mendez58@gmail.com','M','Urb. Daniel Camacho, Av. Carabobo con calle 24, Barquisimeto',1,'2026-05-26'
    UNION ALL SELECT 'PNF Materiales Industriales','4101-B','Raquel Yolanda','Argüelles Otero','V','25972019','2000-03-24','04265745566','raquel.arguelles59@gmail.com','F','Urb. Santa Rosa, calle 10 con Av. Paramacay, Barquisimeto',0,'2026-06-09'
    UNION ALL SELECT 'PNF Procesos Químicos','4501-U','Tomás Alberto','Ceballos Prieto','V','29073120','2004-10-09','04225756677','tomas.ceballos60@gmail.com','M','Sector Pío Tamayo, calle 29 con carrera 5, Barquisimeto',1,'2026-06-23'
    UNION ALL SELECT 'PNF Informática','1106-M','Elba Coromoto','Riera Aldana','V','30174231','2005-08-04','04125767788','elba.riera61@gmail.com','F','Calle 13 con carrera 51, Barrio Belén, Barquisimeto',1,'2026-07-14'
    UNION ALL SELECT 'PNF Administración','1205-C','José Gregorio','Montes Delgado','V','31275342','2006-04-12','04145778899','jose.montes62@gmail.com','M','Av. Libertador con calle 38, Urb. Sucre, Barquisimeto',1,'2026-07-28'
    UNION ALL SELECT 'PNF Contaduría Pública','1304-M','Miriam Esther','Zamora Marcano','V','28376453','2003-01-18','04245789900','miriam.zamora63@gmail.com','F','Urb. Don Rómulo, carrera 14 con calle 19, Barquisimeto',1,'2026-08-11'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','1405-J','Edgar David','Duno Villalobos','V','27477564','2001-09-05','04165801122','edgar.duno64@gmail.com','M','Barrio La Sequita, carrera 22 con calle 26, Barquisimeto',1,'2026-08-25'
    UNION ALL SELECT 'PNF Deporte','1904-U','Yolanda Beatriz','Sosa Cordero','V','26578675','2000-06-21','04265812233','yolanda.sosa65@gmail.com','F','Sector Obrero, calle 25 con carrera 17, Barquisimeto',1,'2026-09-08'
    UNION ALL SELECT 'PNF Turismo','2305-M','Félix Eduardo','Cisneros Parra','V','25679786','1999-12-10','04225823344','felix.cisneros66@yahoo.es','M','Urb. La Carreta, carrera 18 con calle 11, Barquisimeto',1,'2026-09-22'
    UNION ALL SELECT 'PNF Ciencias de la Información','2403-J','Xiomara del Valle','Mendoza Querales','V','29780897','2004-05-28','04125834455','xiomara.mendoza67@gmail.com','F','Barrio El Maní, calle 29 con carrera 12, Barquisimeto',1,'2026-10-13'
    UNION ALL SELECT 'PNF Sistemas de Calidad y Ambiente','1205-B','Hugo Alexander','Carballo Meza','V','30881908','2005-02-15','04145845566','hugo.carballo68@gmail.com','M','Av. Las Américas, Urb. Buen Pastor, calle 18, Barquisimeto',1,'2026-10-27'
    UNION ALL SELECT 'PNF Agroalimentación','1504-M','Alicia Josefina','Lozada Barrios','V','31982019','2006-10-03','04245856677','alicia.lozada69@gmail.com','F','Urb. 6 de Marzo, calle 14 con carrera 10, Barquisimeto',1,'2026-11-05'
    UNION ALL SELECT 'PNF Distribución y Logística','1208-C','Emilio José','Vásquez Nieves','V','28083120','2002-07-20','04165867788','emilio.vasquez70@gmail.com','M','Barrio Tamarindo, carrera 5 con calle 41, Barquisimeto',1,'2026-11-12'
    UNION ALL SELECT 'PNF Materiales Industriales','3104-C','Milena Coromoto','Nava Montilla','V','27184231','2001-03-16','04265878899','milena.nava71@gmail.com','F','Urb. Daniel Camacho, calle 20 con Av. Carabobo, Barquisimeto',1,'2026-11-16'
    UNION ALL SELECT 'PNF Procesos Químicos','3404-M','Alonso Rafael','Freitez Castillo','V','26285342','2000-11-28','04225889900','alonso.freitez72@gmail.com','M','Calle 35 con carrera 16, Barrio San José, Barquisimeto',1,'2026-11-17'
) v
INNER JOIN pnf p
    ON p.nombre_pnf = v.pnf_nombre
   AND p.estatus = 1
WHERE NOT EXISTS (
    SELECT 1 FROM (
        SELECT e.tipo_cedula AS tipo, e.cedula AS doc
          FROM dirpoles_security.empleado e
         WHERE e.tipo_cedula IS NOT NULL AND e.cedula IS NOT NULL
        UNION ALL
        SELECT b.tipo_cedula, b.cedula
          FROM beneficiario b
         WHERE b.cedula IS NOT NULL
        UNION ALL
        SELECT pr.tipo_documento, pr.num_documento
          FROM proveedores pr
    ) x
    WHERE x.tipo = v.tipo_cedula AND x.doc = v.cedula
)
AND NOT EXISTS (
    SELECT 1 FROM (
        SELECT e.correo
          FROM dirpoles_security.empleado e
         WHERE e.correo IS NOT NULL
        UNION ALL
        SELECT b.correo
          FROM beneficiario b
         WHERE b.correo IS NOT NULL
        UNION ALL
        SELECT pr.correo
          FROM proveedores pr
    ) y
    WHERE y.correo = v.correo
)
AND NOT EXISTS (
    SELECT 1 FROM (
        SELECT e.telefono
          FROM dirpoles_security.empleado e
         WHERE e.telefono IS NOT NULL
        UNION ALL
        SELECT b.telefono
          FROM beneficiario b
         WHERE b.telefono IS NOT NULL
        UNION ALL
        SELECT pr.telefono
          FROM proveedores pr
    ) z
    WHERE z.telefono = v.telefono
);

-- =====================================================================
-- CONSULTAS DE VERIFICACIÓN (Ejecutar en la terminal)
-- =====================================================================
-- 1) Contar total de beneficiarios (debería incrementarse en +72):
--    SELECT COUNT(*) AS total_beneficiarios FROM beneficiario;
--
-- 2) Comprobar distribución por PNF de los nuevos registros:
--    SELECT p.nombre_pnf, COUNT(*) AS total
--      FROM beneficiario b
--      JOIN pnf p ON p.id_pnf = b.id_pnf
--     WHERE b.cedula IN ('28114201','29215302','30316403','26285342') -- muestra
--     GROUP BY p.nombre_pnf;
--
-- 3) Verificar que no existan duplicados internos ni colisiones globales:
--    SELECT b.cedula, COUNT(*) FROM beneficiario b GROUP BY b.cedula HAVING COUNT(*) > 1;
-- =====================================================================
