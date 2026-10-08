-- =====================================================================
-- LLENADO MASIVO: MOBILIARIO Y EQUIPOS (id_modulo 12)
-- Tablas involucradas: tipo_mobiliario, tipo_equipo, mobiliario, equipos,
--                      fichas_tecnicas, detalle_ficha_mobiliario,
--                      detalle_ficha_equipo, historial_inventario
-- =====================================================================

SET NAMES utf8mb4;

-- 0. Limpieza preventiva en orden seguro por Foreign Keys
DELETE FROM historial_inventario;
DELETE FROM detalle_ficha_equipo;
DELETE FROM detalle_ficha_mobiliario;
DELETE FROM fichas_tecnicas;
DELETE FROM equipos;
DELETE FROM mobiliario;

-- 1. Asegurar Catálogo de Tipos de Mobiliario
INSERT INTO tipo_mobiliario (nombre, descripcion, estatus, fecha_creacion)
SELECT v.nombre, v.descripcion, 1, '2025-01-10'
FROM (
    SELECT 'Escritorio de Madera' AS nombre, 'Escritorio ejecutivo o de oficina de madera' AS descripcion
    UNION ALL SELECT 'Escritorio Ergonómico', 'Escritorio modulado de oficina'
    UNION ALL SELECT 'Silla Giratoria Ergonómica', 'Silla de oficina con ruedas y regulador de altura'
    UNION ALL SELECT 'Silla de Visitante', 'Silla fija para atención al público/pacientes'
    UNION ALL SELECT 'Archivador Metálico', 'Archivador de metal de 4 gavetas'
    UNION ALL SELECT 'Camilla de Examen Médico', 'Camilla clínica tapizada para evaluación médica'
    UNION ALL SELECT 'Mesa de Conferencias', 'Mesa ejecutiva ovalada para reuniones'
    UNION ALL SELECT 'Silla de Ruedas Institucional', 'Silla de ruedas plegable para asistencia'
) v
WHERE NOT EXISTS (SELECT 1 FROM tipo_mobiliario WHERE nombre = v.nombre);

-- 2. Asegurar Catálogo de Tipos de Equipo
INSERT INTO tipo_equipo (nombre, descripcion, estatus, fecha_creacion)
SELECT v.nombre, v.descripcion, 1, '2025-01-10'
FROM (
    SELECT 'Computadora de Escritorio (CPU)' AS nombre, 'Unidad central de procesamiento de escritorio' AS descripcion
    UNION ALL SELECT 'Monitor LCD / LED', 'Pantalla de visualización'
    UNION ALL SELECT 'Impresora Multifuncional', 'Impresora fotocopiadora y escáner'
    UNION ALL SELECT 'Video Beam', 'Proyector multimedia'
    UNION ALL SELECT 'Equipo de Computación Portátil (Laptop)', 'Computadora portátil de oficina'
    UNION ALL SELECT 'Tensiómetro de Pared', 'Equipo médico para toma de presión arterial'
    UNION ALL SELECT 'Sistema de Audio Amplificado', 'Altavoz amplificado para sala de conferencias'
) v
WHERE NOT EXISTS (SELECT 1 FROM tipo_equipo WHERE nombre = v.nombre);

-- Resolver IDs del Catálogo
SET @tm_esc_mad   = COALESCE((SELECT id_tipo_mobiliario FROM tipo_mobiliario WHERE nombre = 'Escritorio de Madera' LIMIT 1), 1);
SET @tm_esc_erg   = COALESCE((SELECT id_tipo_mobiliario FROM tipo_mobiliario WHERE nombre = 'Escritorio Ergonómico' LIMIT 1), 1);
SET @tm_sil_gir   = COALESCE((SELECT id_tipo_mobiliario FROM tipo_mobiliario WHERE nombre LIKE '%Giratoria%' LIMIT 1), 2);
SET @tm_sil_vis   = COALESCE((SELECT id_tipo_mobiliario FROM tipo_mobiliario WHERE nombre LIKE '%Visitante%' LIMIT 1), 2);
SET @tm_arch      = COALESCE((SELECT id_tipo_mobiliario FROM tipo_mobiliario WHERE nombre LIKE '%Archivador%' LIMIT 1), 1);
SET @tm_camilla   = COALESCE((SELECT id_tipo_mobiliario FROM tipo_mobiliario WHERE nombre LIKE '%Camilla%' LIMIT 1), 1);
SET @tm_mesa_conf = COALESCE((SELECT id_tipo_mobiliario FROM tipo_mobiliario WHERE nombre LIKE '%Mesa%' LIMIT 1), 1);
SET @tm_silla_rue = COALESCE((SELECT id_tipo_mobiliario FROM tipo_mobiliario WHERE nombre LIKE '%Ruedas%' LIMIT 1), 2);

SET @te_cpu   = COALESCE((SELECT id_tipo_equipo FROM tipo_equipo WHERE nombre LIKE '%CPU%' OR nombre LIKE '%Computadora%' LIMIT 1), 1);
SET @te_mon   = COALESCE((SELECT id_tipo_equipo FROM tipo_equipo WHERE nombre LIKE '%Monitor%' LIMIT 1), 1);
SET @te_imp   = COALESCE((SELECT id_tipo_equipo FROM tipo_equipo WHERE nombre LIKE '%Impresora%' LIMIT 1), 1);
SET @te_vbeam = COALESCE((SELECT id_tipo_equipo FROM tipo_equipo WHERE nombre LIKE '%Video Beam%' LIMIT 1), 2);
SET @te_lap   = COALESCE((SELECT id_tipo_equipo FROM tipo_equipo WHERE nombre LIKE '%Laptop%' LIMIT 1), 1);
SET @te_ten   = COALESCE((SELECT id_tipo_equipo FROM tipo_equipo WHERE nombre LIKE '%Tensiómetro%' LIMIT 1), 1);
SET @te_aud   = COALESCE((SELECT id_tipo_equipo FROM tipo_equipo WHERE nombre LIKE '%Audio%' LIMIT 1), 2);

-- Resolver IDs de Empleados Responsables
SET @emp_iris     = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '12023052' LIMIT 1), 3);
SET @emp_yaselis  = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '14872391' LIMIT 1), @emp_iris);
SET @emp_carlos   = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '19632847' LIMIT 1), 1);
SET @emp_yuri     = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '16754209' LIMIT 1), @emp_carlos);
SET @emp_jhan     = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '13652666' LIMIT 1), 2);
SET @emp_edward   = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '18364725' LIMIT 1), @emp_jhan);
SET @emp_nohelia  = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '22159408' LIMIT 1), @emp_jhan);
SET @emp_luis     = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '13529764' LIMIT 1), 1);
SET @emp_keila    = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '20481637' LIMIT 1), @emp_luis);
SET @emp_yhon     = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '15928374' LIMIT 1), 1);
SET @emp_marielva = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '24671583' LIMIT 1), @emp_yhon);

-- Identificar Servicios
SET @s_psic = 1;
SET @s_med  = 2;
SET @s_ori  = 3;
SET @s_ts   = 4;
SET @s_disc = 5;
SET @s_gen  = 6;
SET @s_dir  = 8;

-- =====================================================================
-- 3. MOBILIARIO INSTITUCIONAL (Piezas de Mobiliario por Oficina)
-- =====================================================================

-- Recepción / Control (General)
INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_esc_mad, @s_gen, 1, 'Bueno', 'Activo', '2025-01-15 08:30:00', 'ErgoOffice', 'EO-120', 'Marrón/Negro', '2025-01-10', 'Escritorio receptor principal', 'En perfecto estado');
SET @mob_1 = LAST_INSERT_ID();
INSERT INTO historial_inventario (id_empleado, tipo_item, id_item, tipo_movimiento, id_servicio_nuevo, descripcion, fecha_movimiento)
VALUES (@emp_nohelia, 'mobiliario', @mob_1, 'asignacion', @s_gen, 'Alta e inventariado de Escritorio receptor', '2025-01-15 08:30:00');

INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_sil_gir, @s_gen, 1, 'Bueno', 'Activo', '2025-01-15 08:35:00', 'FlexFit', 'FX-300', 'Negro', '2025-01-10', 'Silla giratoria recepción', 'Funcionamiento correcto');
SET @mob_2 = LAST_INSERT_ID();

INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_sil_vis, @s_gen, 3, 'Bueno', 'Activo', '2025-01-15 08:40:00', 'SitWell', 'SW-10', 'Azul', '2025-01-10', 'Sillas de espera en recepción', 'Uso continuo');
SET @mob_3 = LAST_INSERT_ID();

-- Psicología
INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_esc_erg, @s_psic, 2, 'Bueno', 'Activo', '2025-01-20 09:00:00', 'ErgoOffice', 'EO-140', 'Marrón Claro', '2025-01-12', 'Escritorios cubículos Psicología', 'Equipados con gavetas');
SET @mob_4 = LAST_INSERT_ID();

INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_sil_gir, @s_psic, 2, 'Bueno', 'Activo', '2025-01-20 09:10:00', 'FlexFit', 'FX-500', 'Negro', '2025-01-12', 'Sillas ergonómicas Psicología', 'Respaldo reclinable');
SET @mob_5 = LAST_INSERT_ID();

INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_sil_vis, @s_psic, 4, 'Bueno', 'Activo', '2025-01-20 09:15:00', 'SitWell', 'SW-20', 'Gris', '2025-01-12', 'Sillas confidenciales consulta psicológica', 'Tapizado de tela');
SET @mob_6 = LAST_INSERT_ID();

-- Medicina
INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_esc_mad, @s_med, 2, 'Bueno', 'Activo', '2025-01-25 10:00:00', 'MueblesLara', 'ML-MED', 'Madera Caoba', '2025-01-15', 'Escritorios médicos', 'Superficie lavable');
SET @mob_7 = LAST_INSERT_ID();

INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_camilla, @s_med, 2, 'Bueno', 'Activo', '2025-01-25 10:15:00', 'MedEquip', 'ME-CAM', 'Azul Clínico', '2025-01-15', 'Camillas de evaluación clínica', 'Con cabezal reclinable');
SET @mob_8 = LAST_INSERT_ID();

INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_arch, @s_med, 2, 'Bueno', 'Activo', '2025-01-25 10:20:00', 'MetalOffice', 'MO-4G', 'Gris Metalizado', '2025-01-15', 'Archivadores de historias médicas', 'Con cerradura de seguridad');
SET @mob_9 = LAST_INSERT_ID();

-- Orientación
INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_esc_erg, @s_ori, 2, 'Bueno', 'Activo', '2025-02-01 08:30:00', 'ErgoOffice', 'EO-100', 'Negro/Gris', '2025-01-20', 'Escritorios Orientación', 'Buen estado');
SET @mob_10 = LAST_INSERT_ID();

INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_sil_gir, @s_ori, 2, 'Bueno', 'Activo', '2025-02-01 08:35:00', 'FlexFit', 'FX-200', 'Negro', '2025-01-20', 'Sillas giratorias Orientación', 'Uso administrativo');
SET @mob_11 = LAST_INSERT_ID();

-- Trabajo Social
INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_esc_mad, @s_ts, 2, 'Bueno', 'Activo', '2025-02-05 09:00:00', 'MueblesLara', 'ML-TS', 'Madera Roble', '2025-01-22', 'Escritorios Trabajo Social', 'Con soporte para CPU');
SET @mob_12 = LAST_INSERT_ID();

INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_arch, @s_ts, 2, 'Bueno', 'Activo', '2025-02-05 09:10:00', 'MetalOffice', 'MO-4G', 'Gris Metalizado', '2025-01-22', 'Archivadores de expedientes socioeconómicos', 'Cerradura reforzada');
SET @mob_13 = LAST_INSERT_ID();

-- Discapacidad
INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_esc_erg, @s_disc, 1, 'Bueno', 'Activo', '2025-02-10 10:00:00', 'ErgoOffice', 'EO-ACCES', 'Beige', '2025-01-25', 'Escritorio accesible para personas con discapacidad', 'Altura regulable');
SET @mob_14 = LAST_INSERT_ID();

INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_silla_rue, @s_disc, 1, 'Bueno', 'Activo', '2025-02-10 10:10:00', 'DriveMedical', 'DM-200', 'Cromo/Negro', '2025-01-25', 'Silla de ruedas institucional de traslado', 'Para apoyo de movilidad');
SET @mob_15 = LAST_INSERT_ID();

-- Dirección / Coordinación
INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_esc_mad, @s_dir, 1, 'Bueno', 'Activo', '2025-02-15 11:00:00', 'MueblesLara', 'ML-EXEC', 'Caoba Oscuro', '2025-02-01', 'Escritorio Ejecutivo Dirección', 'Poblado con ala lateral');
SET @mob_16 = LAST_INSERT_ID();

INSERT INTO mobiliario (id_tipo_mobiliario, id_servicios, cantidad, estado, estatus, fecha_registro, marca, modelo, color, fecha_adquisicion, descripcion_adicional, observaciones)
VALUES (@tm_mesa_conf, @s_gen, 1, 'Bueno', 'Activo', '2025-02-15 11:15:00', 'MueblesLara', 'ML-CONF', 'Caoba Oscuro', '2025-02-01', 'Mesa de reuniones Sala de Conferencia', 'Capacidad 10 puestos');
SET @mob_17 = LAST_INSERT_ID();


-- =====================================================================
-- 4. EQUIPOS INFORMÁTICOS Y TECNOLÓGICOS (Con Serial Único)
-- =====================================================================

-- Recepción
INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_cpu, @s_gen, 'HP', 'ProDesk 400 G6', 'HP-CPU-2025-001', 'Negro', 'Bueno', '2025-01-10', 'Intel Core i5, 8GB RAM, 256GB SSD', 'Estación de recepción', 'Activo', '2025-01-15 08:30:00');
SET @eq_1 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_mon, @s_gen, 'HP', 'P22h G4', 'HP-MON-2025-001', 'Negro', 'Bueno', '2025-01-10', 'Monitor LED 21.5 Pulgadas', 'Conexión HDMI/VGA', 'Activo', '2025-01-15 08:30:00');
SET @eq_2 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_imp, @s_gen, 'HP', 'LaserJet Pro M404dn', 'HP-IMP-2025-001', 'Blanco/Gris', 'Bueno', '2025-01-10', 'Impresora láser red', 'Impresora compartida', 'Activo', '2025-01-15 08:35:00');
SET @eq_3 = LAST_INSERT_ID();

-- Psicología (Cubículos 1 y 2)
INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_cpu, @s_psic, 'Lenovo', 'ThinkCentre M720s', 'LEN-CPU-2025-002', 'Negro', 'Bueno', '2025-01-12', 'Intel Core i5, 16GB RAM, 512GB SSD', 'CPU Psicología 1', 'Activo', '2025-01-20 09:00:00');
SET @eq_4 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_mon, @s_psic, 'Lenovo', 'ThinkVision T24i', 'LEN-MON-2025-002', 'Negro', 'Bueno', '2025-01-12', 'Monitor LED 23.8 Pulgadas', 'Monitor Psicología 1', 'Activo', '2025-01-20 09:00:00');
SET @eq_5 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_cpu, @s_psic, 'Dell', 'OptiPlex 3080', 'DEL-CPU-2025-003', 'Negro', 'Bueno', '2025-01-12', 'Intel Core i3, 8GB RAM, 256GB SSD', 'CPU Psicología 2', 'Activo', '2025-01-20 09:05:00');
SET @eq_6 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_mon, @s_psic, 'Dell', 'E2220H', 'DEL-MON-2025-003', 'Negro', 'Bueno', '2025-01-12', 'Monitor LED 21.5 Pulgadas', 'Monitor Psicología 2', 'Activo', '2025-01-20 09:05:00');
SET @eq_7 = LAST_INSERT_ID();

-- Medicina (Consultorios 1 y 2)
INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_cpu, @s_med, 'HP', 'Compaq Pro 6300', 'HP-CPU-2025-005', 'Negro/Gris', 'Bueno', '2025-01-15', 'Intel Core i3, 8GB RAM, 500GB HDD', 'CPU Medicina 1', 'Activo', '2025-01-25 10:00:00');
SET @eq_8 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_mon, @s_med, 'HP', 'V20 HD', 'HP-MON-2025-005', 'Negro', 'Bueno', '2025-01-15', 'Monitor LED 19.5 Pulgadas', 'Monitor Medicina 1', 'Activo', '2025-01-25 10:00:00');
SET @eq_9 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_ten, @s_med, 'WelchAllyn', '767 Wall Aneroid', 'WEL-TEN-2025-005', 'Blanco', 'Bueno', '2025-01-15', 'Tensiómetro aneroide de pared con brazalete', 'Fijo en consultorio médico', 'Activo', '2025-01-25 10:10:00');
SET @eq_10 = LAST_INSERT_ID();

-- Orientación
INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_cpu, @s_ori, 'HP', 'ProDesk 400 G5', 'HP-CPU-2025-007', 'Negro', 'Bueno', '2025-01-20', 'Intel Core i5, 8GB RAM, 256GB SSD', 'CPU Orientación 1', 'Activo', '2025-02-01 08:30:00');
SET @eq_11 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_mon, @s_ori, 'Acer', 'V226HQL', 'ACE-MON-2025-007', 'Negro', 'Bueno', '2025-01-20', 'Monitor LED 21.5 Pulgadas', 'Monitor Orientación 1', 'Activo', '2025-02-01 08:30:00');
SET @eq_12 = LAST_INSERT_ID();

-- Trabajo Social
INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_cpu, @s_ts, 'HP', 'ProDesk 600 G4', 'HP-CPU-2025-009', 'Negro', 'Bueno', '2025-01-22', 'Intel Core i5, 16GB RAM, 512GB SSD', 'CPU Trabajo Social 1', 'Activo', '2025-02-05 09:00:00');
SET @eq_13 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_mon, @s_ts, 'HP', 'E243 23.8"', 'HP-MON-2025-009', 'Plata/Negro', 'Bueno', '2025-01-22', 'Monitor IPS Full HD 23.8"', 'Monitor Trabajo Social 1', 'Activo', '2025-02-05 09:00:00');
SET @eq_14 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_imp, @s_ts, 'Epson', 'EcoTank L3250', 'EPS-IMP-2025-009', 'Negro', 'Bueno', '2025-01-22', 'Multifuncional de tinta continua', 'Para impresión de becas y estudios', 'Activo', '2025-02-05 09:05:00');
SET @eq_15 = LAST_INSERT_ID();

-- Discapacidad
INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_cpu, @s_disc, 'Lenovo', 'ThinkCentre Neo 50s', 'LEN-CPU-2025-011', 'Negro', 'Bueno', '2025-01-25', 'Intel Core i5, 8GB RAM, 512GB SSD', 'CPU Unidad de Discapacidad', 'Activo', '2025-02-10 10:00:00');
SET @eq_16 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_mon, @s_disc, 'Lenovo', 'ThinkVision S24e', 'LEN-MON-2025-011', 'Negro', 'Bueno', '2025-01-25', 'Monitor LED 23.8 Pulgadas', 'Monitor Unidad de Discapacidad', 'Activo', '2025-02-10 10:00:00');
SET @eq_17 = LAST_INSERT_ID();

-- Dirección / Conferencia
INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_lap, @s_dir, 'HP', 'ProBook 440 G8', 'HP-LAP-2025-012', 'Plata', 'Bueno', '2025-02-01', 'Intel Core i7, 16GB RAM, 512GB SSD', 'Laptop Institucional Dirección', 'Activo', '2025-02-15 11:00:00');
SET @eq_18 = LAST_INSERT_ID();

INSERT INTO equipos (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado, fecha_adquisicion, descripcion, observaciones, estatus, fecha_registro)
VALUES (@te_vbeam, @s_gen, 'Epson', 'PowerLite E20', 'EPS-VIB-2025-013', 'Blanco', 'Bueno', '2025-02-01', 'Proyector 3400 lúmenes HDMI', 'Video Beam Sala de Conferencias', 'Activo', '2025-02-15 11:15:00');
SET @eq_19 = LAST_INSERT_ID();


-- =====================================================================
-- 5. FICHAS TÉCNICAS (Una Ficha Activa por Empleado Responsable)
-- =====================================================================

-- Ficha 1: Recepción (Nohelia / Roberth)
INSERT INTO fichas_tecnicas (nombre_ficha, id_servicio, id_empleado_responsable, descripcion, fecha_creacion, estatus)
VALUES ('Ficha Técnica - Recepción y Control de Acceso', @s_gen, @emp_nohelia, 'Mobiliario y equipos asignados al área de recepción general.', '2025-01-15', 1);
SET @ficha_1 = LAST_INSERT_ID();
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_1, @mob_1, 1, 'Escritorio de recepción');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_1, @mob_2, 1, 'Silla receptor');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_1, @eq_1, 'CPU Recepción');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_1, @eq_2, 'Monitor Recepción');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_1, @eq_3, 'Impresora compartida');

-- Ficha 2: Cubículo Psicología 1 (Iris)
INSERT INTO fichas_tecnicas (nombre_ficha, id_servicio, id_empleado_responsable, descripcion, fecha_creacion, estatus)
VALUES ('Ficha Técnica - Cubículo Psicología 1', @s_psic, @emp_iris, 'Asignación de equipos y mobiliario para atención psicológica.', '2025-01-20', 1);
SET @ficha_2 = LAST_INSERT_ID();
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_2, @mob_4, 1, 'Escritorio ergonómico');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_2, @mob_5, 1, 'Silla ergonómica');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_2, @mob_6, 2, 'Sillas paciente');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_2, @eq_4, 'CPU Lenovo');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_2, @eq_5, 'Monitor ThinkVision');

-- Ficha 3: Cubículo Psicología 2 (Yaselis)
INSERT INTO fichas_tecnicas (nombre_ficha, id_servicio, id_empleado_responsable, descripcion, fecha_creacion, estatus)
VALUES ('Ficha Técnica - Cubículo Psicología 2', @s_psic, @emp_yaselis, 'Asignación para consulta de orientación y psicología.', '2025-01-20', 1);
SET @ficha_3 = LAST_INSERT_ID();
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_3, @mob_4, 1, 'Escritorio ergonómico');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_3, @mob_5, 1, 'Silla ergonómica');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_3, @eq_6, 'CPU Dell');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_3, @eq_7, 'Monitor Dell');

-- Ficha 4: Consultorio Médico 1 (Carlos Mendoza)
INSERT INTO fichas_tecnicas (nombre_ficha, id_servicio, id_empleado_responsable, descripcion, fecha_creacion, estatus)
VALUES ('Ficha Técnica - Consultorio Médico 1', @s_med, @emp_carlos, 'Mobiliario clínico y estación médica.', '2025-01-25', 1);
SET @ficha_4 = LAST_INSERT_ID();
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_4, @mob_7, 1, 'Escritorio médico');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_4, @mob_8, 1, 'Camilla clínica');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_4, @mob_9, 1, 'Archivador historias');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_4, @eq_8, 'CPU HP');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_4, @eq_9, 'Monitor HP');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_4, @eq_10, 'Tensiómetro de pared');

-- Ficha 5: Consultorio Médico 2 (Yuraima Silva)
INSERT INTO fichas_tecnicas (nombre_ficha, id_servicio, id_empleado_responsable, descripcion, fecha_creacion, estatus)
VALUES ('Ficha Técnica - Consultorio Médico 2', @s_med, @emp_yuri, 'Estación clínica médica 2.', '2025-01-25', 1);
SET @ficha_5 = LAST_INSERT_ID();
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_5, @mob_7, 1, 'Escritorio médico');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_5, @mob_8, 1, 'Camilla clínica');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_5, @mob_9, 1, 'Archivador historias');

-- Ficha 6: Oficina Orientación (Jhan / Edward)
INSERT INTO fichas_tecnicas (nombre_ficha, id_servicio, id_empleado_responsable, descripcion, fecha_creacion, estatus)
VALUES ('Ficha Técnica - Oficina de Orientación Vocacional', @s_ori, @emp_jhan, 'Equipamiento de la unidad de Orientación.', '2025-02-01', 1);
SET @ficha_6 = LAST_INSERT_ID();
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_6, @mob_10, 1, 'Escritorio orientación');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_6, @mob_11, 1, 'Silla giratoria');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_6, @eq_11, 'CPU HP');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_6, @eq_12, 'Monitor Acer');

-- Ficha 7: Oficina Trabajo Social (Luis)
INSERT INTO fichas_tecnicas (nombre_ficha, id_servicio, id_empleado_responsable, descripcion, fecha_creacion, estatus)
VALUES ('Ficha Técnica - Trabajo Social (Becas y Exoneraciones)', @s_ts, @emp_luis, 'Unidad de estudios socioeconómicos y atención de becas.', '2025-02-05', 1);
SET @ficha_7 = LAST_INSERT_ID();
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_7, @mob_12, 1, 'Escritorio Trabajo Social');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_7, @mob_13, 1, 'Archivador expedientes');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_7, @eq_13, 'CPU HP');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_7, @eq_14, 'Monitor HP 24"');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_7, @eq_15, 'Impresora Epson EcoTank');

-- Ficha 8: Unidad de Discapacidad (Yhon)
INSERT INTO fichas_tecnicas (nombre_ficha, id_servicio, id_empleado_responsable, descripcion, fecha_creacion, estatus)
VALUES ('Ficha Técnica - Unidad de Atención a la Discapacidad', @s_disc, @emp_yhon, 'Mobiliario accesible y equipamiento adaptado.', '2025-02-10', 1);
SET @ficha_8 = LAST_INSERT_ID();
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_8, @mob_14, 1, 'Escritorio accesible');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_8, @mob_15, 1, 'Silla de ruedas traslados');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_8, @eq_16, 'CPU Lenovo');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_8, @eq_17, 'Monitor Lenovo');

-- Ficha 9: Dirección General
INSERT INTO fichas_tecnicas (nombre_ficha, id_servicio, id_empleado_responsable, descripcion, fecha_creacion, estatus)
VALUES ('Ficha Técnica - Dirección General DIRPOLES', @s_dir, @emp_carlos, 'Oficina de Coordinación y Dirección.', '2025-02-15', 1);
SET @ficha_9 = LAST_INSERT_ID();
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_9, @mob_16, 1, 'Escritorio Ejecutivo');
INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad, observaciones) VALUES (@ficha_9, @mob_17, 1, 'Mesa de conferencias');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_9, @eq_18, 'Laptop ProBook');
INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo, observaciones) VALUES (@ficha_9, @eq_19, 'Video Beam Epson');
