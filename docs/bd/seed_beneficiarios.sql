-- =====================================================================
-- SEED DE BENEFICIARIOS — 60 registros sintéticos realistas
-- Módulo: Beneficiarios (id_modulo 2)
--
-- Los datos (nombres, cédulas, teléfonos, correos, direcciones) son
-- SINTÉTICOS: NO corresponden a personas reales.
--
-- Respeta las validaciones del módulo (backend BeneficiarioModel +
-- frontend dist/js/modulos/beneficiario/validaciones.js):
--   * tipo_cedula en (V,E,J,P,G) y cedula de 6 a 10 dígitos.
--   * Unicidad GLOBAL de cédula/correo/teléfono contra
--     dirpoles_security.empleado, beneficiario y proveedores.
--   * nombres/apellidos solo letras, acentos y espacios.
--   * telefono 11 dígitos: 0412|0414|0416|0422|0424|0426 + 7 dígitos.
--   * seccion formato NNNN-S (NNNN = 4 dígitos que inician en 1-4;
--     sede en M,C,J,U,B).
--   * id_pnf resuelto POR NOMBRE contra la tabla pnf (estatus = 1).
--   * fecha_nac válida y no futura; estatus 0/1; direccion <= 255.
--
-- ES IDEMPOTENTE: solo inserta las filas cuya cédula/correo/teléfono
-- todavía no existen en ninguna de las tres tablas.
--
-- Ejecutar (desde la terminal, contra la BD de negocio):
--   mysql -u <usuario> -p dirpoles_business < docs/bd/seed_beneficiarios.sql
--
-- Verificación (debe devolver 60 la primera vez, 0 en re-ejecuciones):
--   SELECT COUNT(*) FROM beneficiario
--    WHERE cedula IN ('21381247','18390420', ...);  -- o bien:
--   SELECT COUNT(*) FROM beneficiario;   -- total esperado = 1 + 60 = 61
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO beneficiario
    (id_pnf, seccion, nombres, apellidos, tipo_cedula, cedula,
     fecha_nac, telefono, correo, genero, direccion, estatus, fecha_creacion)
SELECT p.id_pnf, v.seccion, v.nombres, v.apellidos, v.tipo_cedula, v.cedula,
       v.fecha_nac, v.telefono, v.correo, v.genero, v.direccion, v.estatus, v.fecha_creacion
FROM (
    SELECT 'PNF Informática' AS pnf_nombre, '1104-M' AS seccion, 'Andrea Carolina' AS nombres, 'Rodríguez Silva' AS apellidos,
           'V' AS tipo_cedula, '21381247' AS cedula, '2003-04-18' AS fecha_nac, '04126183473' AS telefono,
           'andrea.rodriguez23@gmail.com' AS correo, 'F' AS genero,
           'Av. 5 de Julio con calle 20, Urb. Don Rómulo' AS direccion, 1 AS estatus, '2026-03-14' AS fecha_creacion
    UNION ALL SELECT 'PNF Administración','2207-C','Luis Enrique','Mendoza Torres','V','18390420','1999-09-02','04146197264','luis.mendoza41@gmail.com','M','Carrera 19 con calle 44, Barrio Belén',1,'2026-01-22'
    UNION ALL SELECT 'PNF Contaduría Pública','3105-M','Gabriela Patricia','Suárez Rivas','V','26399593','2001-12-11','04166211055','gabriela.suarez58@hotmail.com','F','Urb. Buen Pastor, calle 9 con Av. Las Américas',1,'2025-11-30'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','1403-J','José Gregorio','Hernández Blanco','V','23408766','1996-06-25','04226224846','jose.hernandez67@gmail.com','M','Calle 4 con carrera 15, Sector Centro',1,'2026-02-09'
    UNION ALL SELECT 'PNF Informática','1208-B','Daniela Fernanda','Márquez Rojas','V','29417939','2004-02-07','04246238637','daniela.marquez34@gmail.com','F','Urb. Altamora, Av. Libertador con calle 12',1,'2026-04-03'
    UNION ALL SELECT 'PNF Deporte','2506-U','Carlos Andrés','Salazar Mora','V','16427112','2000-11-19','04266252428','carlos.salazar89@gmail.com','M','Sector El Tránsito, calle 32 con carrera 7',1,'2026-05-16'
    UNION ALL SELECT 'PNF Turismo','3301-M','Yorgelis Andreína','Pérez Cordero','V','24436285','2002-08-14','04126266219','yorgelis.perez12@yahoo.es','F','Urb. La Carreta, Av. Santiago Rodríguez con calle 6',1,'2026-06-21'
    UNION ALL SELECT 'PNF Ciencias de la Información','1602-C','Jesús Alexander','Páez Iriarte','V','14445458','1998-03-30','04146280010','jesus.paez45@gmail.com','M','Barrio La Sequita, carrera 21 con calle 29',1,'2026-07-08'
    UNION ALL SELECT 'PNF Sistemas de Calidad y Ambiente','2104-B','Luisana Carolina','Espinoza Fuentes','V','27454631','2003-10-05','04166293801','luisana.espinoza71@gmail.com','F','Av. Venezuela con calle 15, Urb. 15 de Enero',1,'2026-08-12'
    UNION ALL SELECT 'PNF Informática','1105-M','Cristian David','Bermúdez Aponte','V','30463804','2005-01-23','04226307592','cristian.bermudez19@gmail.com','M','Calle 2 con carrera 24, Sector Obrero',1,'2026-10-04'
    UNION ALL SELECT 'PNF Agroalimentación','2703-U','Karla Natalie','Jiménez Urdaneta','V','19472977','1997-07-16','04246321383','karla.jimenez36@gmail.com','F','Urb. Daniel Camacho, calle 18 con Av. Carabobo',1,'2026-02-27'
    UNION ALL SELECT 'PNF Distribución y Logística','3402-C','Brayan Alexander','Rincón Segovia','V','28482150','2004-05-09','04266335174','brayan.rincon88@yahoo.es','M','Barrio Tamarindo, carrera 3 con calle 40',1,'2026-03-31'
    UNION ALL SELECT 'PNF Contaduría Pública','1306-M','María Gabriela','Contreras Perdomo','V','22491323','2001-03-21','04126348965','maria.contreras52@gmail.com','F','Av. 9 con calle 10, Urb. El Tostao',1,'2026-04-18'
    UNION ALL SELECT 'PNF Materiales Industriales','2601-B','Andrés Feliciano','Quintana Velásquez','V','17500496','1995-12-04','04146362756','andres.quintana27@gmail.com','M','Calle 33 con carrera 12, Barrio San José',0,'2026-01-09'
    UNION ALL SELECT 'PNF Informática','1507-J','Franyeli Jackeline','Torres Orellana','V','25509669','2002-09-28','04166376547','franyeli.torres43@gmail.com','F','Urb. Santa Rosa, Av. Paramacay con calle 5',1,'2026-05-05'
    UNION ALL SELECT 'PNF Deporte','2304-U','Kelvin Jhonatan','Guzmán Barrios','V','31518842','2006-02-12','04226390338','kelvin.guzman64@gmail.com','M','Sector Pío Tamayo, calle 25 con carrera 9',1,'2026-06-14'
    UNION ALL SELECT 'PNF Administración','1803-M','Yaneth del Carmen','Piñate Gutiérrez','V','15528015','1994-08-07','04246404129','yaneth.pinate31@gmail.com','F','Barrio El Maní, carrera 14 con calle 26',1,'2026-07-25'
    UNION ALL SELECT 'PNF Procesos Químicos','3502-C','Enrique Jesús','Alvarado Núñez','V','20537188','1999-04-26','04266417920','enrique.alvarado77@gmail.com','M','Av. Los Leones con calle 3, Urb. Javier Este',1,'2026-08-30'
    UNION ALL SELECT 'PNF Ciencias de la Información','1204-B','Stefany Paola','Rangel Medina','V','26546361','2003-06-13','04126431711','stefany.rangel55@gmail.com','F','Urb. 6 de Marzo, calle 6 con carrera 11',1,'2026-09-17'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','2405-M','Javier Alejandro','Montiel Cárdenas','V','13555534','2000-10-31','04146445502','javier.montiel92@gmail.com','M','Calle 12 con carrera 22, Sector Obrero',1,'2026-04-29'
    UNION ALL SELECT 'PNF Turismo','1708-U','Eliana Margarita','Rojas Farías','V','18564707','1993-01-17','04166459293','eliana.rojas14@yahoo.es','F','Urb. Cristo Rey, Av. Carabobo con calle 19',1,'2026-02-16'
    UNION ALL SELECT 'PNF Distribución y Logística','3601-C','Omar Daniel','Fuentes Bastidas','V','24573880','1997-11-08','04226473084','omar.fuentes61@gmail.com','M','Barrio La Loma, carrera 5 con calle 30',0,'2026-03-06'
    UNION ALL SELECT 'PNF Informática','1406-M','Rosangely Yuraima','Hernández Pino','V','29583053','2004-12-02','04246486875','rosangely.hernandez48@gmail.com','F','Calle 20 con carrera 16, Urb. Sucre',1,'2026-05-23'
    UNION ALL SELECT 'PNF Sistemas de Calidad y Ambiente','2209-B','Luis David','Castillo Aponte','V','16592226','2002-07-19','04266500666','luis.castillo25@gmail.com','M','Av. 15 con calle 7, Urb. La Carreta',1,'2026-06-02'
    UNION ALL SELECT 'PNF Agroalimentación','3102-J','Yuraima Cristina','Velásquez Márquez','V','21601399','2001-05-24','04126514457','yuraima.velasquez39@gmail.com','F','Sector Alto de la Cruz, calle 41 con carrera 8',1,'2026-07-13'
    UNION ALL SELECT 'PNF Deporte','1905-U','Yohander Enrique','Sánchez Ríos','V','30610572','2005-08-21','04146528248','yohander.sanchez83@gmail.com','M','Urb. El Tostao, calle 11 con Av. 5 de Julio',1,'2026-08-06'
    UNION ALL SELECT 'PNF Contaduría Pública','1604-M','Marielena Josefina','Colmenares Omaña','V','14619745','1996-02-28','04166542039','marielena.colmenares57@gmail.com','F','Calle 27 con carrera 13, Barrio Belén',1,'2026-09-22'
    UNION ALL SELECT 'PNF Materiales Industriales','2503-C','Deivis Alexander','Blanco Márquez','V','27628918','1998-09-10','04226555830','deivis.blanco46@gmail.com','M','Carrera 2 con calle 35, Sector Centro',1,'2026-01-15'
    UNION ALL SELECT 'PNF Informática','1307-B','Yuleimy Gabriela','Salas Ibarra','V','23638091','2003-11-06','04246569621','yuleimy.salas21@gmail.com','F','Urb. 15 de Enero, calle 14 con carrera 20',1,'2026-02-23'
    UNION ALL SELECT 'PNF Administración','2801-U','José Antonio','Méndez Caldera','V','17647264','1992-06-15','04266583412','jose.mendez95@gmail.com','M','Av. Carabobo con calle 22, Urb. Daniel Camacho',0,'2025-12-14'
    UNION ALL SELECT 'PNF Procesos Químicos','3406-M','Endrina Patricia','Quintero León','V','19656437','2000-04-03','04126597203','endrina.quintero33@gmail.com','F','Calle 8 con carrera 6, Barrio El Recreo',1,'2026-04-11'
    UNION ALL SELECT 'PNF Distribución y Logística','1206-C','Yonathan David','Aponte Gil','V','31665610','2006-01-29','04146610994','yonathan.aponte74@gmail.com','M','Carrera 17 con calle 28, Barrio La Sequita',1,'2026-05-30'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','2102-M','Ingrid Carolina','Marcano Terán','V','22674783','1999-10-14','04166624785','ingrid.marcano18@gmail.com','F','Av. Libertador con calle 17, Urb. Sucre',1,'2026-06-27'
    UNION ALL SELECT 'PNF Ciencias de la Información','1705-J','Jesús Daniel','Rodríguez Aponte','V','26683956','2004-07-07','04226638576','jesus.rodriguez53@gmail.com','M','Calle 30 con carrera 4, Sector Obrero',1,'2026-07-19'
    UNION ALL SELECT 'PNF Turismo','2602-U','María Elena','Linares Bracho','V','13693129','1991-03-12','04246652367','maria.linares29@gmail.com','F','Urb. El Maní, calle 26 con carrera 15',1,'2026-08-24'
    UNION ALL SELECT 'PNF Sistemas de Calidad y Ambiente','3204-B','Oscar Alejandro','Paredes Inojosa','V','18702302','1995-12-19','04266666158','oscar.paredes47@gmail.com','M','Av. Las Américas con calle 1, Urb. Buen Pastor',1,'2026-10-06'
    UNION ALL SELECT 'PNF Agroalimentación','1503-M','Yusbely Paola','Urdaneta Rangel','V','25711475','2002-05-27','04126679949','yusbely.urdaneta66@gmail.com','F','Calle 16 con carrera 10, Barrio Tamarindo',1,'2026-01-28'
    UNION ALL SELECT 'PNF Contaduría Pública','2306-C','Manuel Enrique','Villarroel Ponce','V','15720648','1990-08-03','04146693740','manuel.villarroel81@gmail.com','M','Carrera 9 con calle 21, Urb. Don Rómulo',0,'2026-02-05'
    UNION ALL SELECT 'PNF Informática','1804-U','Lisbeth Carolina','Nava Cárdenas','V','28729821','2005-09-17','04166707531','lisbeth.nava24@gmail.com','F','Av. 9 de Febrero con calle 5, Urb. Javier Este',1,'2026-03-19'
    UNION ALL SELECT 'PNF Materiales Industriales','2901-B','Franklin Jesús','Salcedo Márquez','V','20738994','1996-01-05','04226721322','franklin.salcedo90@gmail.com','M','Calle 24 con carrera 19, Barrio San José',1,'2026-04-25'
    UNION ALL SELECT 'PNF Deporte','1408-M','Naireth Alexandra','García Uzcátegui','V','24748167','2003-12-08','04246735113','naireth.garcia37@gmail.com','F','Urb. Santa Rosa, calle 13 con Av. Paramacay',1,'2026-05-12'
    UNION ALL SELECT 'PNF Distribución y Logística','3602-C','Eduard Jhosmel','Pérez Iriarte','V','29757340','2006-06-22','04266748904','eduard.perez11@gmail.com','M','Carrera 23 con calle 7, Barrio El Recreo',1,'2026-06-08'
    UNION ALL SELECT 'PNF Administración','2705-M','Yelitza Maribel','Sequera Ojeda','V','16766513','1998-02-16','04126762695','yelitza.sequera62@gmail.com','F','Av. 5 de Julio con calle 23, Urb. 6 de Marzo',1,'2026-07-31'
    UNION ALL SELECT 'PNF Procesos Químicos','2407-U','Luis Alfredo','Carrasco Mendoza','V','23775686','1993-10-29','04146776486','luis.carrasco44@gmail.com','M','Calle 19 con carrera 25, Sector Pío Tamayo',0,'2026-08-18'
    UNION ALL SELECT 'PNF Turismo','1903-B','Aurimar del Carmen','Páez Berroterán','V','17784859','1997-07-11','04166790277','aurimar.paez76@gmail.com','F','Urb. Cristo Rey, calle 12 con Av. Carabobo',1,'2026-09-29'
    UNION ALL SELECT 'PNF Informática','1607-M','Kleiver Alexander','Ríos Páez','V','30794032','2004-11-24','04226804068','kleiver.rios85@gmail.com','M','Carrera 6 con calle 31, Barrio La Loma',1,'2026-01-06'
    UNION ALL SELECT 'PNF Ciencias de la Información','3302-J','Marianella Alexandra','Gil Ponce','V','22803205','2001-08-30','04246817859','marianella.gil16@gmail.com','F','Calle 10 con carrera 18, Urb. El Maní',1,'2026-02-19'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','2803-C','Yohan David','Montoya Salas','V','26812378','2005-04-09','04266831650','yohan.montoya59@gmail.com','M','Av. 15 con calle 4, Barrio Belén',1,'2026-03-08'
    UNION ALL SELECT 'PNF Contaduría Pública','2106-M','Ana Gabriela','Betancourt Silva','V','19821551','1994-05-20','04126845441','ana.betancourt79@gmail.com','F','Calle 17 con carrera 11, Urb. Sucre',1,'2026-04-07'
    UNION ALL SELECT 'PNF Deporte','1304-U','Daren Jhonatan','Gutiérrez Peña','V','31830724','2006-09-03','04146859232','daren.gutierrez42@gmail.com','M','Sector Alto de la Cruz, carrera 2 con calle 42',1,'2026-05-19'
    UNION ALL SELECT 'PNF Agroalimentación','2605-C','Marielys Jackeline','Farías Mora','V','18839897','2002-01-31','04166873023','marielys.farias28@gmail.com','F','Av. Venezuela con calle 8, Urb. 15 de Enero',1,'2026-06-16'
    UNION ALL SELECT 'PNF Sistemas de Calidad y Ambiente','3405-M','Ángel Rafael','Ochoa Núñez','V','15849070','1999-12-13','04226886814','angel.ochoa54@gmail.com','M','Carrera 16 con calle 27, Sector Obrero',1,'2026-07-27'
    UNION ALL SELECT 'PNF Distribución y Logística','2208-U','Yarimar Alejandra','Nieto Briñez','V','27858243','2000-06-26','04246900605','yarimar.nieto35@gmail.com','F','Urb. El Tostao, calle 7 con Av. 9',1,'2026-08-14'
    UNION ALL SELECT 'PNF Informática','1509-B','Kevin Josué','Márquez Rincón','V','16867416','2003-03-15','04266914396','kevin.marquez68@gmail.com','M','Calle 5 con carrera 29, Barrio El Maní',1,'2026-10-05'
    UNION ALL SELECT 'PNF Administración','2904-M','Greisbel Andrea','Molina Cárdenas','V','21876589','1996-10-02','04126928187','greisbel.molina51@gmail.com','F','Av. Carabobo con calle 30, Urb. Daniel Camacho',0,'2026-01-19'
    UNION ALL SELECT 'PNF Materiales Industriales','3107-C','Yeferson Alexander','Urbina Lara','V','30885762','2004-08-25','04146941978','yeferson.urbina87@gmail.com','M','Carrera 4 con calle 36, Barrio San José',1,'2026-02-11'
    UNION ALL SELECT 'PNF Procesos Químicos','1706-U','Milagros Carolina','Senmache Ríos','E','1025487965','2001-11-09','04166955769','milagros.senmache26@gmail.com','F','Calle 11 con carrera 33, Urb. La Carreta',1,'2026-03-27'
    UNION ALL SELECT 'PNF Turismo','2309-M','Erick Darío','Silva Nieves','V','19904108','1998-05-18','04226969560','erick.silva63@gmail.com','M','Av. 9 con calle 14, Barrio Tamarindo',1,'2026-04-15'
    UNION ALL SELECT 'PNF Higiene y Seguridad Laboral','2408-J','Erika Jackeline','Vera Cárdenas','V','23913281','1999-02-27','04246983351','erika.vera73@gmail.com','F','Calle 22 con carrera 3, Sector Centro',1,'2026-05-26'
    UNION ALL SELECT 'PNF Informática','1908-B','Alexander Jhonatan','Becerra Márquez','V','26922454','2002-10-06','04266997142','alexander.becerra96@gmail.com','M','Urb. Buen Pastor, calle 13 con Av. Las Américas',1,'2026-10-02'
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
-- VERIFICACIÓN (ejecutar a mano después del seed)
-- =====================================================================
-- 1) Total de beneficiarios (esperado: 1 existente + 60 nuevos = 61):
--    SELECT COUNT(*) AS total FROM beneficiario;
--
-- 2) Repetidos de cédula dentro de la tabla (esperado: 0 filas):
--    SELECT tipo_cedula, cedula, COUNT(*) n FROM beneficiario
--     GROUP BY tipo_cedula, cedula HAVING n > 1;
--
-- 3) Chocan con empleados o proveedores (esperado: 0 filas):
--    SELECT b.tipo_cedula, b.cedula, b.correo, b.telefono
--      FROM beneficiario b
--      JOIN dirpoles_security.empleado e
--        ON (e.tipo_cedula = b.tipo_cedula AND e.cedula = b.cedula)
--        OR e.correo = b.correo OR e.telefono = b.telefono
--     UNION
--    SELECT b.tipo_cedula, b.cedula, b.correo, b.telefono
--      FROM beneficiario b
--      JOIN proveedores pr
--        ON pr.num_documento = b.cedula OR pr.correo = b.correo
--        OR pr.telefono = b.telefono;
--
-- 4) Si el total NO es 61, puede ser por: (a) cédula/correo/teléfono ya
--    existentes (los NOT EXISTS omiten la fila) o (b) un nombre de PNF que
--    no existe en tu BD (el INNER JOIN la omite). Compara los PNF:
--    SELECT nombre_pnf FROM pnf WHERE estatus = 1 ORDER BY id_pnf;
--    Los 12 PNF usados en este seed son: PNF Administración,
--    PNF Contaduría Pública, PNF Informática, PNF Higiene y Seguridad
--    Laboral, PNF Deporte, PNF Turismo, PNF Ciencias de la Información,
--    PNF Sistemas de Calidad y Ambiente, PNF Agroalimentación,
--    PNF Distribución y Logística, PNF Materiales Industriales,
--    PNF Procesos Químicos.
--
-- 5) Muestra de los nuevos registros (esperado: 60 filas):
--    SELECT b.id_beneficiario, b.cedula, b.nombres, b.apellidos,
--           p.nombre_pnf, b.seccion, b.genero, b.estatus, b.fecha_creacion
--      FROM beneficiario b
--      LEFT JOIN pnf p ON p.id_pnf = b.id_pnf
--     ORDER BY b.id_beneficiario DESC LIMIT 60;
-- =====================================================================
