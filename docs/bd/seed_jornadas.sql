-- =====================================================================
-- LLENADO MASIVO: JORNADAS MÉDICAS (id_modulo 11)
-- Tablas involucradas: jornadas_medicas, jornada_beneficiarios,
--                      jornada_diagnosticos, jornada_insumos, inventario_medico
-- =====================================================================

SET NAMES utf8mb4;

-- 0. Limpieza preventiva en orden correcto de FK
DELETE FROM jornada_insumos;
DELETE FROM jornada_diagnosticos;
DELETE FROM jornada_beneficiarios;
DELETE FROM jornadas_medicas;

-- 1. Resolver IDs de Médicos / Responsables
SET @med_carlos = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '19632847' LIMIT 1), 1);
SET @med_yuri   = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '16754209' LIMIT 1), @med_carlos);

-- 2. Resolver Insumos Médicos para consumo en Jornadas
SET @ins_aceta   = COALESCE((SELECT id_insumo FROM insumos WHERE nombre_insumo LIKE '%Acetaminofén%' LIMIT 1), 1);
SET @ins_ibu     = COALESCE((SELECT id_insumo FROM insumos WHERE nombre_insumo LIKE '%Ibuprofeno%' LIMIT 1), 1);
SET @ins_alcohol = COALESCE((SELECT id_insumo FROM insumos WHERE nombre_insumo LIKE '%Alcohol%' LIMIT 1), 1);
SET @ins_gasas   = COALESCE((SELECT id_insumo FROM insumos WHERE nombre_insumo LIKE '%Gasas%' LIMIT 1), 1);
SET @ins_lora    = COALESCE((SELECT id_insumo FROM insumos WHERE nombre_insumo LIKE '%Loratadina%' LIMIT 1), 1);
SET @ins_jeringa = COALESCE((SELECT id_insumo FROM insumos WHERE nombre_insumo LIKE '%Jeringa%' LIMIT 1), 1);
SET @ins_sol     = COALESCE((SELECT id_insumo FROM insumos WHERE nombre_insumo LIKE '%Solución%' LIMIT 1), 1);
SET @ins_omep    = COALESCE((SELECT id_insumo FROM insumos WHERE nombre_insumo LIKE '%Omeprazol%' LIMIT 1), 1);

-- =====================================================================
-- JORNADA 1: Jornada de Atención Médica Integral y Desparasitación (Feb 2025)
-- =====================================================================
INSERT INTO jornadas_medicas 
(nombre_jornada, tipo_jornada, aforo_maximo, fecha_inicio, fecha_fin, ubicacion, descripcion, estatus, fecha_creacion)
VALUES 
('Jornada de Atención Médica Integral y Desparasitación', 'Médica Integral', 50, '2025-02-12 08:00:00', '2025-02-12 15:00:00', 'Sede Central - Plaza Bolivariana UPTAEB', 'Jornada médica preventiva dirigida a estudiantes de nuevo ingreso y comunidad universitaria.', 'Finalizada', '2025-02-01 09:00:00');
SET @jornada_1 = LAST_INSERT_ID();

-- Asistentes Jornada 1
INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '30995937', 'Roberth', 'Matos', '2002-04-05', 'Masculino', 'Estudiante', '04129298008', 'roberth@gmail.com', 'Calle 53 y 54 con carrera 14', @jornada_1, '2025-02-12 08:30:00', 'Atendido');
SET @jb_1_1 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '21381247', 'Marielys', 'Yépez', '1995-03-12', 'Femenino', 'Estudiante', '04145218844', 'marielys.yepez@gmail.com', 'Urb. Obelisco Bloque 12', @jornada_1, '2025-02-12 09:10:00', 'Atendido');
SET @jb_1_2 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '18390420', 'Carlos Eduardo', 'Pérez', '1988-07-22', 'Masculino', 'Personal Docente', '04268390112', 'carlos.perez@uptaeb.edu.ve', 'Carrera 19 entre 24 y 25', @jornada_1, '2025-02-12 09:50:00', 'Atendido');
SET @jb_1_3 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '26399593', 'Ana Gabriela', 'Rivas', '1998-11-04', 'Femenino', 'Estudiante', '04169402231', 'ana.rivas@gmail.com', 'Barrio Unión Calle 4', @jornada_1, '2025-02-12 10:20:00', 'Atendido');
SET @jb_1_4 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '16427112', 'José Gregorio', 'Mendoza', '1983-02-15', 'Masculino', 'Personal Obrero', '04123405567', 'jose.mendoza@uptaeb.edu.ve', 'El Ujano Sector 2', @jornada_1, '2025-02-12 11:00:00', 'Atendido');
SET @jb_1_5 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '14445458', 'Carmen Elena', 'Morales', '1979-09-30', 'Femenino', 'Comunidad', '04245119988', 'carmen.morales@gmail.com', 'Sector Pueblo Nuevo Calle 3', @jornada_1, '2025-02-12 11:45:00', 'Atendido');
SET @jb_1_6 = LAST_INSERT_ID();

-- Diagnósticos e Insumos Jornada 1
INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_1_1, @med_carlos, 'Síndrome febril agudo y cefalea leve.', 'Reposo relativo por 48h y rehidratación oral.', 'Se entrega analgésico oral.', '2025-02-12 08:35:00');
SET @jd_1_1 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_1_1, @ins_aceta, 2, 'Insumo utilizado en jornada médica');
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_aceta, @med_carlos, '2025-02-12 08:35:00', 'Salida', 2, 'Salida por jornada médica (diagnóstico #1)');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_1_2, @med_yuri, 'Parasitosis intestinal sospechada y rinitis alérgica.', 'Tratamiento sintomático y antialérgico oral.', 'Recomendaciones de higiene alimentaria.', '2025-02-12 09:15:00');
SET @jd_1_2 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_1_2, @ins_lora, 1, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_1_3, @med_carlos, 'Faringitis aguda eritematosa.', 'Antiséptico bucofaríngeo e hidratación.', 'Paciente refiere odinofagia de 3 días.', '2025-02-12 09:55:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_1_4, @med_yuri, 'Dermatitis de contacto en antebrazo.', 'Crema hidratante y limpieza local.', 'Alergia a detergente industrial.', '2025-02-12 10:25:00');
SET @jd_1_4 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_1_4, @ins_alcohol, 1, 'Insumo utilizado en jornada médica'), (@jd_1_4, @ins_gasas, 2, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_1_5, @med_carlos, 'Lumbago mecánico agudo por esfuerzo físico.', 'Analgésico antiinflamatorio y compresa caliente.', 'Levantamiento de carga en talleres UPTAEB.', '2025-02-12 11:05:00');
SET @jd_1_5 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_1_5, @ins_ibu, 2, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_1_6, @med_yuri, 'Control médico preventivo. PA: 120/80 mmHg.', 'Estilo de vida saludable y dieta baja en sodio.', 'Vecina del sector Pueblo Nuevo.', '2025-02-12 11:50:00');


-- =====================================================================
-- JORNADA 2: Despistaje de HTA y Diabetes Mellitus (May 2025)
-- =====================================================================
INSERT INTO jornadas_medicas 
(nombre_jornada, tipo_jornada, aforo_maximo, fecha_inicio, fecha_fin, ubicacion, descripcion, estatus, fecha_creacion)
VALUES 
('Despistaje de Hipertensión Arterial y Diabetes Mellitus', 'Despistaje HTA y Diabetes', 40, '2025-05-20 08:30:00', '2025-05-20 14:00:00', 'Edificio Argelia Laya - Planta Baja', 'Jornada epidemiológica de pesquisaje cardiovascular y control de glicemia en ayunas.', 'Finalizada', '2025-05-10 10:00:00');
SET @jornada_2 = LAST_INSERT_ID();

-- Asistentes Jornada 2
INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '23408766', 'Luis Alberto', 'Torres', '1994-06-18', 'Masculino', 'Personal Administrativo', '04145401122', 'luis.torres@uptaeb.edu.ve', 'Urb. Patarata Calle 3', @jornada_2, '2025-05-20 08:45:00', 'Atendido');
SET @jb_2_1 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '29417939', 'Sofia Victoria', 'Gómez', '2001-10-14', 'Femenino', 'Estudiante', '04269503344', 'sofia.gomez@gmail.com', 'Barrio la Ferretera', @jornada_2, '2025-05-20 09:15:00', 'Atendido');
SET @jb_2_2 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '24436285', 'Daniel Alejandro', 'Silva', '1992-04-08', 'Masculino', 'Personal Docente', '04125607788', 'daniel.silva@uptaeb.edu.ve', 'Av. Lara Res. Las Trinitarias', @jornada_2, '2025-05-20 09:50:00', 'Atendido');
SET @jb_2_3 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '27454631', 'Patricia Andreína', 'Colmenárez', '1999-01-25', 'Femenino', 'Personal Obrero', '04163209900', 'patricia.colmenarez@uptaeb.edu.ve', 'Carrera 15 con calle 50', @jornada_2, '2025-05-20 10:30:00', 'Atendido');
SET @jb_2_4 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '30463804', 'Gabriel Enrique', 'Suárez', '2003-08-19', 'Masculino', 'Estudiante', '04245701133', 'gabriel.suarez@gmail.com', 'Urb. La Concordia Calle 2', @jornada_2, '2025-05-20 11:15:00', 'Atendido');
SET @jb_2_5 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '19472977', 'Elena María', 'Castillo', '1989-12-03', 'Femenino', 'Comunidad', '04145802244', 'elena.castillo@gmail.com', 'Comunidad Ruíz Pineda', @jornada_2, '2025-05-20 12:00:00', 'Atendido');
SET @jb_2_6 = LAST_INSERT_ID();

-- Diagnósticos e Insumos Jornada 2
INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_2_1, @med_carlos, 'Hipertensión arterial Estadio 1 (PA 145/90 mmHg).', 'Cita por Medicina Interna para inicio de tratamiento.', 'Remitido a consulta regular.', '2025-05-20 08:50:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_2_2, @med_yuri, 'Normotensa. Glicemia capilar en ayunas 88 mg/dL.', 'Mantener hábitos alimenticios saludables.', 'Sin alteraciones detectadas.', '2025-05-20 09:20:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_2_3, @med_carlos, 'Glicemia alterada en ayunas (112 mg/dL).', 'Dieta hipoglucídica y reevaluación en 15 días.', 'Antecedente familiar de Diabetes Tipo 2.', '2025-05-20 09:55:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_2_4, @med_yuri, 'Prehipertensión arterial (PA 135/85 mmHg).', 'Disminución de ingesta de sal y actividad física regular.', 'Revisión en 1 mes.', '2025-05-20 10:35:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_2_5, @med_carlos, 'Evaluación cardiovascular dentro de límites normales (PA 115/75 mmHg).', 'Continuar práctica deportiva.', 'Atleta de baloncesto UPTAEB.', '2025-05-20 11:20:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_2_6, @med_yuri, 'Hipertensión arterial descompensada (PA 160/100 mmHg).', 'Sublingual administrado y reposo en camilla.', 'Remitida de urgencia al CDI más cercano.', '2025-05-20 12:05:00');
SET @jd_2_6 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_2_6, @ins_gasas, 1, 'Insumo utilizado en jornada médica');


-- =====================================================================
-- JORNADA 3: Salud Visual, Optometría y Evaluación Médica (Aug 2025)
-- =====================================================================
INSERT INTO jornadas_medicas 
(nombre_jornada, tipo_jornada, aforo_maximo, fecha_inicio, fecha_fin, ubicacion, descripcion, estatus, fecha_creacion)
VALUES 
('Jornada de Salud Visual y Evaluación Optométrica', 'Salud Visual y Optometría', 60, '2025-08-14 08:00:00', '2025-08-14 16:00:00', 'Auditorio Libertador UPTAEB', 'Jornada de agudeza visual, refracción y evaluación médica oftalmológica básica.', 'Finalizada', '2025-08-01 08:30:00');
SET @jornada_3 = LAST_INSERT_ID();

-- Asistentes Jornada 3
INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '28482150', 'Ricardo José', 'Ramírez', '2000-05-14', 'Masculino', 'Estudiante', '04126903322', 'ricardo.ramirez@gmail.com', 'Barrio Cerritos Blancos', @jornada_3, '2025-08-14 08:30:00', 'Atendido');
SET @jb_3_1 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '22491323', 'Vanessa Carolina', 'Gutiérrez', '1993-09-28', 'Femenino', 'Personal Docente', '04145904455', 'vanessa.gutierrez@uptaeb.edu.ve', 'Urb. Santa Elena Calle 5', @jornada_3, '2025-08-14 09:20:00', 'Atendido');
SET @jb_3_2 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '25509669', 'Fernando Antonio', 'Medina', '1996-12-11', 'Masculino', 'Estudiante', '04269105566', 'fernando.medina@gmail.com', 'Av. Vargas con carrera 21', @jornada_3, '2025-08-14 10:10:00', 'Atendido');
SET @jb_3_3 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '31518842', 'Camila Isabela', 'Herrera', '2004-03-07', 'Femenino', 'Estudiante', '04169206677', 'camila.herrera@gmail.com', 'El Manzano Sector El Parral', @jornada_3, '2025-08-14 11:00:00', 'Atendido');
SET @jb_3_4 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '15528015', 'Héctor Manuel', 'Vargas', '1981-11-17', 'Masculino', 'Comunidad', '04129307788', 'hector.vargas@gmail.com', 'Comunidad El Garabatal', @jornada_3, '2025-08-14 11:45:00', 'Atendido');
SET @jb_3_5 = LAST_INSERT_ID();

-- Diagnósticos Jornada 3
INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_3_1, @med_carlos, 'Miopía compuesta con astigmatismo en Ojo Izquierdo.', 'Fórmula corregida OD -1.25, OI -1.75. Uso permanente de lentes.', 'Refiere fatiga visual al leer.', '2025-08-14 08:35:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_3_2, @med_yuri, 'Presbicia corregida. Fatiga ocular por exposición a pantallas.', 'Lágrimas artificiales cada 8h y pausas activas.', 'Docente del PNF Informática.', '2025-08-14 09:25:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_3_3, @med_carlos, 'Conjuntivitis alérgica bilateral.', 'Gotas antialérgicas oftálmicas por 7 días.', 'Evitar frotarse los ojos.', '2025-08-14 10:15:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_3_4, @med_yuri, 'Agudeza visual 20/20 ambos ojos.', 'Sin observaciones patológicas.', 'Estudiante de Fisioterapia.', '2025-08-14 11:05:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_3_5, @med_carlos, 'Pterigión Grado I en ojo derecho.', 'Lentes de protección solar y lubricante ocular.', 'Paciente de la comunidad externa.', '2025-08-14 11:50:00');


-- =====================================================================
-- JORNADA 4: Odontología Preventiva y Salud Bucal (Nov 2025)
-- =====================================================================
INSERT INTO jornadas_medicas 
(nombre_jornada, tipo_jornada, aforo_maximo, fecha_inicio, fecha_fin, ubicacion, descripcion, estatus, fecha_creacion)
VALUES 
('Jornada de Odontología Preventiva y Salud Bucal', 'Odontología Preventiva', 35, '2025-11-05 08:00:00', '2025-11-05 15:00:00', 'Clínica Odontológica UPTAEB - Módulo 3', 'Evaluación odontológica, profilaxis dental y aplicación de flúor a beneficiarios.', 'Finalizada', '2025-10-25 11:00:00');
SET @jornada_4 = LAST_INSERT_ID();

-- Asistentes Jornada 4
INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '20537188', 'Diego Armando', 'Paredes', '1991-01-30', 'Masculino', 'Estudiante', '04245908899', 'diego.paredes@gmail.com', 'Barrio la Paz Calle 8', @jornada_4, '2025-11-05 08:40:00', 'Atendido');
SET @jb_4_1 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '26546361', 'Stephanie Beatriz', 'Lugo', '1997-08-14', 'Femenino', 'Estudiante', '04145309900', 'stephanie.lugo@gmail.com', 'Urb. la Rosaleda Calle 1', @jornada_4, '2025-11-05 09:30:00', 'Atendido');
SET @jb_4_2 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '13555534', 'Roberto Antonio', 'Fernández', '1977-04-02', 'Masculino', 'Personal Obrero', '04125801122', 'roberto.fernandez@uptaeb.edu.ve', 'Carrera 12 con calle 42', @jornada_4, '2025-11-05 10:15:00', 'Atendido');
SET @jb_4_3 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '18564707', 'Laura Cristina', 'Delgado', '1987-10-21', 'Femenino', 'Personal Administrativo', '04163402233', 'laura.delgado@uptaeb.edu.ve', 'Urb. Fundalara Calle 4', @jornada_4, '2025-11-05 11:00:00', 'Atendido');
SET @jb_4_4 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '29583053', 'Marcos Vinicio', 'Briceño', '2002-02-18', 'Masculino', 'Estudiante', '04269603344', 'marcos.briceno@gmail.com', 'Comunidad Tamaca Sector El Cuji', @jornada_4, '2025-11-05 11:45:00', 'Atendido');
SET @jb_4_5 = LAST_INSERT_ID();

-- Diagnósticos e Insumos Jornada 4
INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_4_1, @med_yuri, 'Profilaxis realizada. Caries oclusal en pieza 36.', 'Restauración estética programada en odontología.', 'Buena higiene bucal.', '2025-11-05 08:45:00');
SET @jd_4_1 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_4_1, @ins_gasas, 2, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_4_2, @med_carlos, 'Gingivitis marginal crónica por acumulación de placa.', 'Tartrectomía ultrasónica y enjuague con Clorhexidina.', 'Técnica de cepillado explicada.', '2025-11-05 09:35:00');
SET @jd_4_2 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_4_2, @ins_gasas, 1, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_4_3, @med_yuri, 'Ausencias dentarias parciales en maxilar inferior.', 'Remisión al área de prótesis bucal.', 'Mantenimiento e higiene.', '2025-11-05 10:20:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_4_4, @med_carlos, 'Salud bucodental óptima. Profilaxis superficial.', 'Control semestral.', 'Sin hallazgos patológicos.', '2025-11-05 11:05:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_4_5, @med_yuri, 'Sensibilidad dentinaria leve en incisivos superiores.', 'Crema dental desensibilizante y fluorización.', 'Uso de cepillo suave.', '2025-11-05 11:50:00');


-- =====================================================================
-- JORNADA 5: Jornada Especial de Vacunación e Inmunización (Feb 2026)
-- =====================================================================
INSERT INTO jornadas_medicas 
(nombre_jornada, tipo_jornada, aforo_maximo, fecha_inicio, fecha_fin, ubicacion, descripcion, estatus, fecha_creacion)
VALUES 
('Jornada Especial de Vacunación e Inmunización', 'Vacunación e Inmunización', 80, '2026-02-18 08:00:00', '2026-02-18 14:00:00', 'Comedor Universitario UPTAEB', 'Jornada de inmunización con esquema completo de Toxoide Tetánico, Hepatitis B y Fiebre Amarilla.', 'Finalizada', '2026-02-05 09:30:00');
SET @jornada_5 = LAST_INSERT_ID();

-- Asistentes Jornada 5
INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '16592226', 'Yolanda Josefina', 'Rondón', '1984-06-09', 'Femenino', 'Personal Obrero', '04145703344', 'yolanda.rondon@uptaeb.edu.ve', 'Comunidad el Cuji', @jornada_5, '2026-02-18 08:15:00', 'Atendido');
SET @jb_5_1 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '30995937', 'Roberth', 'Matos', '2002-04-05', 'Masculino', 'Estudiante', '04129298008', 'roberth@gmail.com', 'Calle 53 y 54 con carrera 14', @jornada_5, '2026-02-18 09:00:00', 'Atendido');
SET @jb_5_2 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '21381247', 'Marielys', 'Yépez', '1995-03-12', 'Femenino', 'Estudiante', '04145218844', 'marielys.yepez@gmail.com', 'Urb. Obelisco Bloque 12', @jornada_5, '2026-02-18 09:40:00', 'Atendido');
SET @jb_5_3 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '18390420', 'Carlos Eduardo', 'Pérez', '1988-07-22', 'Masculino', 'Personal Docente', '04268390112', 'carlos.perez@uptaeb.edu.ve', 'Carrera 19 entre 24 y 25', @jornada_5, '2026-02-18 10:20:00', 'Atendido');
SET @jb_5_4 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '26399593', 'Ana Gabriela', 'Rivas', '1998-11-04', 'Femenino', 'Estudiante', '04169402231', 'ana.rivas@gmail.com', 'Barrio Unión Calle 4', @jornada_5, '2026-02-18 11:00:00', 'Atendido');
SET @jb_5_5 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '23408766', 'Luis Alberto', 'Torres', '1994-06-18', 'Masculino', 'Personal Administrativo', '04145401122', 'luis.torres@uptaeb.edu.ve', 'Urb. Patarata Calle 3', @jornada_5, '2026-02-18 11:40:00', 'Atendido');
SET @jb_5_6 = LAST_INSERT_ID();

-- Diagnósticos e Insumos Jornada 5
INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_5_1, @med_carlos, 'Inmunización con Toxoide Tetánico (2da Dosis) y Anti-Hepatitis B.', 'Compresa fría local en caso de dolor.', 'Personal de mantenimiento de áreas verdes.', '2026-02-18 08:20:00');
SET @jd_5_1 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_5_1, @ins_jeringa, 1, 'Insumo utilizado en jornada médica'), (@jd_5_1, @ins_alcohol, 1, 'Insumo utilizado en jornada médica');
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_jeringa, @med_carlos, '2026-02-18 08:20:00', 'Salida', 1, 'Salida por jornada médica (diagnóstico de vacunación)');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_5_2, @med_yuri, 'Refuerzo de Toxoide Tetánico administrado sin complicaciones.', 'Analgesia simple si presenta febrícula.', 'Estudiante universitario.', '2026-02-18 09:05:00');
SET @jd_5_2 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_5_2, @ins_jeringa, 1, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_5_3, @med_carlos, 'Vacunación Anti-Hepatitis B (1ra Dosis).', 'Segunda dosis programada en 30 días.', 'Carnet de vacunación entregado.', '2026-02-18 09:45:00');
SET @jd_5_3 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_5_3, @ins_jeringa, 1, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_5_4, @med_yuri, 'Vacunación contra Fiebre Amarilla aplicada.', 'Inmunización registrada.', 'Sin reacciones adversas inmediatas.', '2026-02-18 10:25:00');
SET @jd_5_4 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_5_4, @ins_jeringa, 1, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_5_5, @med_carlos, 'Refuerzo de Toxoide Diftérico y Tetánico.', 'Reposo relativo del brazo inoculado.', 'Inmunización al día.', '2026-02-18 11:05:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_5_6, @med_yuri, 'Inmunización Anti-Hepatitis B (3ra Dosis - Esquema Completo).', 'Esquema de vacunación finalizado con éxito.', 'Constancia física emitida.', '2026-02-18 11:45:00');


-- =====================================================================
-- JORNADA 6: Jornada de Salud Integral y Primeros Auxilios (May 2026)
-- =====================================================================
INSERT INTO jornadas_medicas 
(nombre_jornada, tipo_jornada, aforo_maximo, fecha_inicio, fecha_fin, ubicacion, descripcion, estatus, fecha_creacion)
VALUES 
('Jornada de Salud Integral y Atención Médica General', 'Médica Integral', 50, '2026-05-27 08:30:00', '2026-05-27 16:00:00', 'Cancha Techada UPTAEB', 'Jornada médico-asistencial con despistaje de afecciones respiratorias, musculares y gastrointestinales.', 'Finalizada', '2026-05-15 10:00:00');
SET @jornada_6 = LAST_INSERT_ID();

-- Asistentes Jornada 6
INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '29417939', 'Sofia Victoria', 'Gómez', '2001-10-14', 'Femenino', 'Estudiante', '04269503344', 'sofia.gomez@gmail.com', 'Barrio la Ferretera', @jornada_6, '2026-05-27 09:00:00', 'Atendido');
SET @jb_6_1 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '24436285', 'Daniel Alejandro', 'Silva', '1992-04-08', 'Masculino', 'Personal Docente', '04125607788', 'daniel.silva@uptaeb.edu.ve', 'Av. Lara Res. Las Trinitarias', @jornada_6, '2026-05-27 09:45:00', 'Atendido');
SET @jb_6_2 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '27454631', 'Patricia Andreína', 'Colmenárez', '1999-01-25', 'Femenino', 'Personal Obrero', '04163209900', 'patricia.colmenarez@uptaeb.edu.ve', 'Carrera 15 con calle 50', @jornada_6, '2026-05-27 10:30:00', 'Atendido');
SET @jb_6_3 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '30463804', 'Gabriel Enrique', 'Suárez', '2003-08-19', 'Masculino', 'Estudiante', '04245701133', 'gabriel.suarez@gmail.com', 'Urb. La Concordia Calle 2', @jornada_6, '2026-05-27 11:15:00', 'Atendido');
SET @jb_6_4 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '19472977', 'Elena María', 'Castillo', '1989-12-03', 'Femenino', 'Comunidad', '04145802244', 'elena.castillo@gmail.com', 'Comunidad Ruíz Pineda', @jornada_6, '2026-05-27 12:00:00', 'Atendido');
SET @jb_6_5 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '28482150', 'Ricardo José', 'Ramírez', '2000-05-14', 'Masculino', 'Estudiante', '04126903322', 'ricardo.ramirez@gmail.com', 'Barrio Cerritos Blancos', @jornada_6, '2026-05-27 13:00:00', 'Atendido');
SET @jb_6_6 = LAST_INSERT_ID();

-- Diagnósticos e Insumos Jornada 6
INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_6_1, @med_carlos, 'Cefalea tensional secundario a estrés académico.', 'Analgésico oral cada 8h por 3 días e hidratación.', 'Evaluación en víspera de exámenes.', '2026-05-27 09:05:00');
SET @jd_6_1 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_6_1, @ins_aceta, 2, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_6_2, @med_yuri, 'Síndrome bronquial obstructivo leve / Rinitis alérgica.', 'Antihistamínico oral y broncodilatador inhalado si requiere.', 'Refiere tos seca nocturna.', '2026-05-27 09:50:00');
SET @jd_6_3 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_6_3, @ins_lora, 1, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_6_3, @med_carlos, 'Gastritis aguda por omisión de comidas.', 'Protector gástrico y dieta blanda.', 'Indicaciones nutricionales.', '2026-05-27 10:35:00');
SET @jd_6_3b = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_6_3b, @ins_omep, 1, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_6_4, @med_yuri, 'Esguince de tobillo derecho Grado 1.', 'Inmovilización suave con vendaje elástico y crioterapia local.', 'Trauma durante práctica deportiva en la cancha.', '2026-05-27 11:20:00');
SET @jd_6_4 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_6_4, @ins_gasas, 2, 'Insumo utilizado en jornada médica'), (@jd_6_4, @ins_alcohol, 1, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_6_5, @med_carlos, 'Lumbalgia aguda y contractura muscular paravertebral.', 'Analgésico antiinflamatorio muscular.', 'Reposo físico por 5 días.', '2026-05-27 12:05:00');
SET @jd_6_5 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_6_5, @ins_ibu, 2, 'Insumo utilizado en jornada médica');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_6_6, @med_yuri, 'Examen físico general satisfactorio.', 'Continuar con hábitos de vida saludable.', 'Chequeo médico de rutina.', '2026-05-27 13:05:00');


-- =====================================================================
-- JORNADA 7: Jornada de Despistaje y Nutrición (MES ACTUAL: Octubre 2026)
-- =====================================================================
INSERT INTO jornadas_medicas 
(nombre_jornada, tipo_jornada, aforo_maximo, fecha_inicio, fecha_fin, ubicacion, descripcion, estatus, fecha_creacion)
VALUES 
('Jornada de Pesquisaje Médico y Evaluación Nutricional', 'Médica Integral', 45, '2026-10-06 08:00:00', '2026-10-06 14:30:00', 'Edificio de Salud y Desarrollo Estudiantil', 'Jornada activa de inicio de semestre para control antropométrico y valoración médica integral.', 'Activa', '2026-10-01 08:00:00');
SET @jornada_7 = LAST_INSERT_ID();

-- Asistentes Jornada 7
INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '22491323', 'Vanessa Carolina', 'Gutiérrez', '1993-09-28', 'Femenino', 'Personal Docente', '04145904455', 'vanessa.gutierrez@uptaeb.edu.ve', 'Urb. Santa Elena Calle 5', @jornada_7, '2026-10-06 08:30:00', 'Atendido');
SET @jb_7_1 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '25509669', 'Fernando Antonio', 'Medina', '1996-12-11', 'Masculino', 'Estudiante', '04269105566', 'fernando.medina@gmail.com', 'Av. Vargas con carrera 21', @jornada_7, '2026-10-06 09:15:00', 'Atendido');
SET @jb_7_2 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '31518842', 'Camila Isabela', 'Herrera', '2004-03-07', 'Femenino', 'Estudiante', '04169206677', 'camila.herrera@gmail.com', 'El Manzano Sector El Parral', @jornada_7, '2026-10-06 10:00:00', 'Atendido');
SET @jb_7_3 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '15528015', 'Héctor Manuel', 'Vargas', '1981-11-17', 'Masculino', 'Comunidad', '04129307788', 'hector.vargas@gmail.com', 'Comunidad El Garabatal', @jornada_7, '2026-10-06 10:45:00', 'Atendido');
SET @jb_7_4 = LAST_INSERT_ID();

INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '20537188', 'Diego Armando', 'Paredes', '1991-01-30', 'Masculino', 'Estudiante', '04245908899', 'diego.paredes@gmail.com', 'Barrio la Paz Calle 8', @jornada_7, '2026-10-06 11:30:00', 'Atendido');
SET @jb_7_5 = LAST_INSERT_ID();

-- Diagnósticos e Insumos Jornada 7
INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_7_1, @med_carlos, 'Evaluación postural y control de peso normal. IMC 22.4.', 'Mantener rutina de ejercicios.', 'Control preventivo semestral.', '2026-10-06 08:35:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_7_2, @med_yuri, 'Déficit ponderal leve (IMC 18.2).', 'Multivitamínico oral y orientación nutricional.', 'Remitido a Trabajo Social para evaluación de beca comedor.', '2026-10-06 09:20:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_7_3, @med_carlos, 'Examen físico dentro de límites normales.', 'Recomendaciones de actividad física.', 'Estudiante sana.', '2026-10-06 10:05:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_7_4, @med_yuri, 'Hipertensión arterial controlada (PA 130/80 mmHg).', 'Mantener medicación antihipertensiva habitual.', 'Paciente de 58 años de la comunidad.', '2026-10-06 10:50:00');

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_7_5, @med_carlos, 'Cefalea por deshidratación leve.', 'Rehidratación oral con solución salina/glucosada.', 'Exposición prolongada al sol.', '2026-10-06 11:35:00');
SET @jd_7_5 = LAST_INSERT_ID();
INSERT INTO jornada_insumos (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
VALUES (@jd_7_5, @ins_sol, 1, 'Insumo utilizado en jornada médica');


-- =====================================================================
-- JORNADA 8: Jornada de Salud Mental y Bienestar (Octubre 2026)
-- =====================================================================
INSERT INTO jornadas_medicas 
(nombre_jornada, tipo_jornada, aforo_maximo, fecha_inicio, fecha_fin, ubicacion, descripcion, estatus, fecha_creacion)
VALUES 
('Jornada de Salud Mental, Manejo del Estrés y Screening Médico', 'Salud Mental y Bienestar', 30, '2026-10-28 08:00:00', '2026-10-28 15:00:00', 'Sala de Usos Múltiples - Biblioteca Central', 'Jornada de promoción de salud mental, detección precoz de ansiedad/depresión y triage médico.', 'Activa', '2026-10-05 11:00:00');
SET @jornada_8 = LAST_INSERT_ID();

-- Asistentes Jornada 8 (1 asistente registrado hasta ahora)
INSERT INTO jornada_beneficiarios (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero, tipo_paciente, telefono, correo, direccion, id_jornada, fecha_atencion, estatus)
VALUES ('V', '26546361', 'Stephanie Beatriz', 'Lugo', '1997-08-14', 'Femenino', 'Estudiante', '04145309900', 'stephanie.lugo@gmail.com', 'Urb. la Rosaleda Calle 1', @jornada_8, '2026-10-07 09:00:00', 'Atendido');
SET @jb_8_1 = LAST_INSERT_ID();

INSERT INTO jornada_diagnosticos (id_jornada_beneficiario, id_empleado_medico, diagnostico, tratamiento, observaciones, fecha_diagnostico)
VALUES (@jb_8_1, @med_yuri, 'Screening de estrés académico moderado.', 'Talleres de técnicas de relajación e higiene del sueño.', 'Remitida a consulta de Psicología UPTAEB.', '2026-10-07 09:10:00');
