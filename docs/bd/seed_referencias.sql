-- =====================================================================
-- LLENADO MASIVO: REFERENCIAS ENTRE ÁREAS (id_modulo 10)
-- Tablas involucradas: referencias, log_referencias
-- =====================================================================

SET NAMES utf8mb4;

-- 0. Limpieza preventiva en orden seguro (log_referencias primero por la FK)
DELETE l FROM log_referencias l JOIN referencias r ON r.id_referencia = l.id_referencia WHERE r.fecha_referencia >= '2024-01-01';
DELETE FROM referencias WHERE fecha_referencia >= '2024-01-01';

-- 1. Identificar Servicios
SET @serv_psic = 1;
SET @serv_med  = 2;
SET @serv_ori  = 3;
SET @serv_ts   = 4;
SET @serv_disc = 5;

-- 2. Identificar Empleados por Área (con fallback robusto a IDs numéricos)
SET @emp_psic_iris    = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '12023052' LIMIT 1), 3);
SET @emp_psic_yaselis = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '14872391' LIMIT 1), @emp_psic_iris, 3);

SET @emp_med_carlos   = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '19632847' LIMIT 1), 1);
SET @emp_med_yuri     = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '16754209' LIMIT 1), @emp_med_carlos, 1);

SET @emp_ori_jhan     = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '13652666' LIMIT 1), 2);
SET @emp_ori_edward   = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '18364725' LIMIT 1), @emp_ori_jhan, 2);
SET @emp_ori_nohelia  = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '22159408' LIMIT 1), @emp_ori_jhan, 2);

SET @emp_ts_luis      = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '13529764' LIMIT 1), 1);
SET @emp_ts_keila     = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '20481637' LIMIT 1), @emp_ts_luis, 1);

SET @emp_disc_yhon    = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '15928374' LIMIT 1), 1);
SET @emp_disc_marielva = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '24671583' LIMIT 1), @emp_disc_yhon, 1);

-- 3. Identificar Beneficiarios
SET @ben_1  = COALESCE((SELECT id_beneficiario FROM beneficiario WHERE cedula = '30995937' LIMIT 1), 1);
SET @ben_2  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '21381247' LIMIT 1);
SET @ben_3  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '18390420' LIMIT 1);
SET @ben_4  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '26399593' LIMIT 1);
SET @ben_5  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '23408766' LIMIT 1);
SET @ben_6  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '29417939' LIMIT 1);
SET @ben_7  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '16427112' LIMIT 1);
SET @ben_8  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '24436285' LIMIT 1);
SET @ben_9  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '14445458' LIMIT 1);
SET @ben_10 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '27454631' LIMIT 1);
SET @ben_11 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '30463804' LIMIT 1);
SET @ben_12 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '19472977' LIMIT 1);
SET @ben_13 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '28482150' LIMIT 1);
SET @ben_14 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '22491323' LIMIT 1);
SET @ben_15 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '25509669' LIMIT 1);
SET @ben_16 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '31518842' LIMIT 1);
SET @ben_17 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '15528015' LIMIT 1);
SET @ben_18 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '20537188' LIMIT 1);
SET @ben_19 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '26546361' LIMIT 1);
SET @ben_20 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '13555534' LIMIT 1);


-- =====================================================================
-- BLOQUE 1: REFERENCIAS ACEPTADAS (10 registros)
-- =====================================================================

-- Ref 1: Psicología -> Medicina
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_1, @emp_psic_iris, @serv_psic, @emp_med_carlos, @serv_med, '2025-01-15 09:30:00', 'Evaluación médica por somatización gastrointestinal.', 'Aceptada', 'Estudiante presenta gastritis reactiva vinculada a ansiedad académica.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Aceptada', @emp_med_carlos, '2025-01-16 10:15:00', 'Referencia aceptada. Paciente atendido e iniciado tratamiento digestivo.');

-- Ref 2: Medicina -> Psicología
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_2, @emp_med_carlos, @serv_med, @emp_psic_yaselis, @serv_psic, '2025-02-10 11:00:00', 'Apoyo psicológico por diagnóstico de cefalea tensional recurrente.', 'Aceptada', 'Se descarta causa orgánica severa; requiere manejo de estrés.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Aceptada', @emp_psic_yaselis, '2025-02-11 14:20:00', 'Referencia aceptada. Se agenda primera sesión de terapia cognitivo-conductual.');

-- Ref 3: Orientación -> Trabajo Social
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_3, @emp_ori_jhan, @serv_ori, @emp_ts_luis, @serv_ts, '2025-03-20 14:00:00', 'Evaluación de vulnerabilidad socioeconómica para beca.', 'Aceptada', 'Estudiante con alto rendimiento en riesgo de deserción por motivos económicos.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Aceptada', @emp_ts_luis, '2025-03-21 09:30:00', 'Referencia aceptada. Iniciado trámite de estudio socioeconómico.');

-- Ref 4: Trabajo Social -> Discapacidad
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_4, @emp_ts_luis, @serv_ts, @emp_disc_yhon, @serv_disc, '2025-04-14 10:30:00', 'Valoración de condición motora para adecuación física.', 'Aceptada', 'Beneficiario requiere evaluación de accesibilidad en aulas de su PNF.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Aceptada', @emp_disc_yhon, '2025-04-15 11:45:00', 'Referencia aceptada. Realizada inspección y registro de discapacidad.');

-- Ref 5: Discapacidad -> Psicología
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_5, @emp_disc_yhon, @serv_disc, @emp_psic_iris, @serv_psic, '2025-05-18 15:15:00', 'Acompañamiento psicológico por proceso adaptativo a prótesis.', 'Aceptada', 'Estudiante en fase de adaptación motora con labilidad emocional ocasional.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Aceptada', @emp_psic_iris, '2025-05-19 16:00:00', 'Referencia aceptada. Incorporado a programa de apoyo emocional.');

-- Ref 6: Medicina -> Trabajo Social (FAMES)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_6, @emp_med_yuri, @serv_med, @emp_ts_keila, @serv_ts, '2025-06-22 08:45:00', 'Solicitud de cobertura de exámenes de laboratorio por FAMES.', 'Aceptada', 'Paciente requiere exámenes especializados de alto costo.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Aceptada', @emp_ts_keila, '2025-06-23 10:00:00', 'Referencia aceptada. Recaudos de FAMES procesados con éxito.');

-- Ref 7: Psicología -> Orientación
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_7, @emp_psic_yaselis, @serv_psic, @emp_ori_edward, @serv_ori, '2025-07-30 13:20:00', 'Orientación vocacional por duda de prosecución en PNF.', 'Aceptada', 'Tras superar crisis de ansiedad, se sugiere clarificación vocacional.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Aceptada', @emp_ori_edward, '2025-07-31 09:15:00', 'Referencia aceptada. Aplicada batería vocacional de seguimiento.');

-- Ref 8: Orientación -> Medicina
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_8, @emp_ori_edward, @serv_ori, @emp_med_carlos, @serv_med, '2025-08-14 10:00:00', 'Valoración auditiva por reporte de dificultades en clase.', 'Aceptada', 'Estudiante indica no escuchar con claridad al docente desde el fondo del aula.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Aceptada', @emp_med_carlos, '2025-08-15 11:30:00', 'Referencia aceptada. Otoscopia realizada y remitido a audiometría.');

-- Ref 9: Trabajo Social -> Psicología
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_9, @emp_ts_keila, @serv_ts, @emp_psic_iris, @serv_psic, '2025-09-25 14:30:00', 'Atención psicológica a estudiante en estado de gestación.', 'Aceptada', 'Beneficiaria manifiesta angustia por manejo del tiempo entre embarazo y estudios.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Aceptada', @emp_psic_iris, '2025-09-26 15:45:00', 'Referencia aceptada. Iniciado acompañamiento perinatal.');

-- Ref 10: Discapacidad -> Trabajo Social (Beca/Exoneración)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_10, @emp_disc_marielva, @serv_disc, @emp_ts_luis, @serv_ts, '2026-02-18 09:15:00', 'Tramitación de exoneración de arancel por carnet CONAPDIS.', 'Aceptada', 'Beneficiario con discapacidad sensorial auditiva requiere exoneración.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Aceptada', @emp_ts_luis, '2026-02-19 10:30:00', 'Referencia aceptada. Registrada solicitud de exoneración institucional.');


-- =====================================================================
-- BLOQUE 2: REFERENCIAS RECHAZADAS (6 registros)
-- =====================================================================

-- Ref 11: Medicina -> Orientación (Rechazada)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_11, @emp_med_carlos, @serv_med, @emp_ori_jhan, @serv_ori, '2025-01-20 15:00:00', 'Solicitud de tutoría académica por inasistencias médicas.', 'Rechazada', 'Paciente solicita justificación de faltas que no compete a orientación.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Rechazada', @emp_ori_jhan, '2025-01-21 09:00:00', 'El trámite de justificación de inasistencias se realiza directamente en Secretaría Afecta.');

-- Ref 12: Psicología -> Discapacidad (Rechazada)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_12, @emp_psic_iris, @serv_psic, @emp_disc_yhon, @serv_disc, '2025-03-28 11:30:00', 'Evaluación de déficit de atención para carnet de discapacidad.', 'Rechazada', 'Solicita registro de condición sin informe neuropediátrico avalado.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Rechazada', @emp_disc_yhon, '2025-03-29 10:20:00', 'Se requiere informe médico neurológico previo antes de proceder con el registro.');

-- Ref 13: Orientación -> Medicina (Rechazada)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_13, @emp_ori_edward, @serv_ori, @emp_med_yuri, @serv_med, '2025-05-10 14:15:00', 'Evaluación física preventiva general.', 'Rechazada', 'Beneficiario ya cuenta con chequeo médico vigente de este mismo mes.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Rechazada', @emp_med_yuri, '2025-05-11 08:30:00', 'Beneficiario atendido el 15/04 en consulta regular sin requerir nueva evaluación.');

-- Ref 14: Trabajo Social -> Orientación (Rechazada)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_14, @emp_ts_luis, @serv_ts, @emp_ori_nohelia, @serv_ori, '2025-08-18 09:45:00', 'Reubicación de sección por motivos laborales.', 'Rechazada', 'Solicitud de cambio de sección fuera del periodo normativo del calendario.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Rechazada', @emp_ori_nohelia, '2025-08-19 11:00:00', 'El periodo de ajuste de secciones culminó la semana pasada.');

-- Ref 15: Discapacidad -> Medicina (Rechazada)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_15, @emp_disc_marielva, @serv_disc, @emp_med_carlos, @serv_med, '2025-11-22 13:00:00', 'Prescripción de medicamentos especializados de alto costo.', 'Rechazada', 'El servicio médico institucional no dispone del medicamento solicitado en stock.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Rechazada', @emp_med_carlos, '2025-11-23 09:15:00', 'Se sugiere canalizar la solicitud a través del programa FAMES de Trabajo Social.');

-- Ref 16: Psicología -> Trabajo Social (Rechazada)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_16, @emp_psic_yaselis, @serv_psic, @emp_ts_keila, @serv_ts, '2026-03-12 10:20:00', 'Solicitud de asignación de vivienda estudiantil.', 'Rechazada', 'La institución no dispone de programa de residencia o vivienda estudiantil.');
SET @ref = LAST_INSERT_ID();
INSERT INTO log_referencias (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
VALUES (@ref, 'Pendiente', 'Rechazada', @emp_ts_keila, '2026-03-13 14:00:00', 'Se orienta al estudiante hacia los programas de becas socioeconómicas disponibles.');


-- =====================================================================
-- BLOQUE 3: REFERENCIAS PENDIENTES (8 registros - Incluye Octubre 2026)
-- =====================================================================

-- Ref 17: Psicología -> Medicina (Pendiente)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_17, @emp_psic_iris, @serv_psic, @emp_med_yuri, @serv_med, '2025-12-05 11:15:00', 'Valoración médica por mareos y cefalea constante.', 'Pendiente', 'Paciente en terapia que reporta síntomas físicos persistentes en el aula.');

-- Ref 18: Medicina -> Discapacidad (Pendiente)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_18, @emp_med_carlos, @serv_med, @emp_disc_yhon, @serv_disc, '2026-01-14 09:30:00', 'Evaluación de marcha claudicante por secuela traumática.', 'Pendiente', 'Se remite para valoración formal e incorporación al registro de accesibilidad.');

-- Ref 19: Orientación -> Trabajo Social (Pendiente)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_19, @emp_ori_jhan, @serv_ori, @emp_ts_luis, @serv_ts, '2026-04-18 14:20:00', 'Evaluación caso familiar complejo para asignación de ayuda económica.', 'Pendiente', 'Estudiante de excelente promedio con severa restricción de ingresos familiares.');

-- Ref 20: Trabajo Social -> Orientación (Pendiente)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_20, @emp_ts_keila, @serv_ts, @emp_ori_nohelia, @serv_ori, '2026-06-22 10:45:00', 'Acompañamiento vocacional para reingreso académico.', 'Pendiente', 'Beneficiaria que retoma sus estudios tras suspensión temporal de matrícula.');

-- Ref 21: Discapacidad -> Psicología (Pendiente)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_1, @emp_disc_marielva, @serv_disc, @emp_psic_yaselis, @serv_psic, '2026-07-28 15:30:00', 'Apoyo psicológico por diagnóstico reciente de baja visión.', 'Pendiente', 'Estudiante requiere contención emocional tras pérdida progresiva de agudeza visual.');

-- Ref 22: Medicina -> Psicología (Pendiente - Octubre 2026)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_2, @emp_med_yuri, @serv_med, @emp_psic_iris, @serv_psic, '2026-10-02 09:00:00', 'Evaluación por somatización de estrés en sistema respiratorio.', 'Pendiente', 'Crisis asmáticas desencadenadas principalmente en semanas de exámenes.');

-- Ref 23: Orientación -> Medicina (Pendiente - Octubre 2026)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_3, @emp_ori_edward, @serv_ori, @emp_med_carlos, @serv_med, '2026-10-04 11:30:00', 'Revisión por fatiga crónica reportada durante entrevistas de seguimiento.', 'Pendiente', 'Estudiante manifiesta somnolencia excesiva diurna que afecta su rendimiento.');

-- Ref 24: Trabajo Social -> Medicina (Pendiente - Octubre 2026)
INSERT INTO referencias (id_beneficiario, id_empleado_origen, id_servicio_origen, id_empleado_destino, id_servicio_destino, fecha_referencia, motivo, estado, observaciones)
VALUES (@ben_4, @emp_ts_luis, @serv_ts, @emp_med_yuri, @serv_med, '2026-10-06 14:00:00', 'Valoración médica de control para beneficiario de programa FAMES.', 'Pendiente', 'Verificación de evolución clínica previa a la entrega de reembolso quirúrgico.');

-- =====================================================================
-- FIN DEL SCRIPT DE REFERENCIAS CORREGIDO
-- =====================================================================