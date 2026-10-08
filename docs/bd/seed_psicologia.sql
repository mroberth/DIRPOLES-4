-- =====================================================================
-- LLENADO MASIVO: CONSULTAS PSICOLÓGICAS (id_servicios = 1)
-- Tablas involucradas: detalle_patologia, solicitud_de_servicio, consulta_psicologica
-- =====================================================================

SET NAMES utf8mb4;

-- Limpieza preventiva de intentos fallidos parciales previa
DELETE cp, ss, dp 
FROM consulta_psicologica cp
JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cp.id_solicitud_serv
LEFT JOIN detalle_patologia dp ON dp.id_detalle_patologia = cp.id_detalle_patologia
WHERE cp.fecha_creacion >= '2024-11-01';

-- 1. Identificar IDs de Psicólogos registrados (según cédulas de seed_empleados y dump)
SET @psic_iris    = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '12023052' LIMIT 1), 3);
SET @psic_yaselis = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '14872391' LIMIT 1), @psic_iris);
SET @psic_maria   = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '17543826' LIMIT 1), @psic_iris);

-- 2. Identificar IDs de Beneficiarios (por cédulas conocidas)
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
SET @ben_21 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '18564707' LIMIT 1);
SET @ben_22 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '29583053' LIMIT 1);
SET @ben_23 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '16592226' LIMIT 1);
SET @ben_24 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '21601399' LIMIT 1);

-- =====================================================================
-- PSICÓLOGA 1: Iris Álvarez
-- =====================================================================

-- Consulta 1: Diagnóstico General (Ansiedad)
INSERT INTO detalle_patologia (id_patologia) VALUES (6);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_1, @psic_iris);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Cuadro de ansiedad académica caracterizado por insomnio de conciliación y tensión muscular previa a periodos de evaluación parcial.', 'Psicoterapia cognitivo-conductual (4 sesiones), entrenamiento en respiración diafragmática y técnicas de organización del tiempo de estudio.', 'No aplica', 'No aplica', 'No aplica', 'Estudiante muestra excelente disposición al tratamiento. Se sugiere seguimiento al finalizar el trayecto.', '2024-11-12 10:15:00');

-- Consulta 2: Diagnóstico General (Estrés adaptativo)
INSERT INTO detalle_patologia (id_patologia) VALUES (2);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_2, @psic_iris);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Estrés adaptativo leve asociado al ingreso al nuevo trayecto universitario y sobrecarga de proyectos comunitarios.', 'Taller de manejo del estrés, psicoeducación en higiene del sueño y pautas de autocuidado.', 'No aplica', 'No aplica', 'No aplica', 'Sin hallazgos clínicos severos. Se otorgan herramientas de afrontamiento.', '2024-12-05 14:30:00');

-- Consulta 3: Diagnóstico General (Ansiedad moderada)
INSERT INTO detalle_patologia (id_patologia) VALUES (6);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_3, @psic_iris);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Síntomas ansiosos episódicos manifestados por angustia antes de exposiciones orales y labilidad afectiva.', 'Desensibilización sistemática, práctica de oratoria supervisada y restructuración de pensamientos catastróficos.', 'No aplica', 'No aplica', 'No aplica', 'Evolución favorable en 2da sesión. Reporta disminución de la fobia social.', '2025-01-20 09:00:00');

-- Consulta 4: Cambio de Carrera
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_4, @psic_iris);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Cambio de carrera', 'No aplica', 'No aplica', 'No aplica', 'No aplica', 'Reorientación vocacional hacia PNF Informática por interés en programación.', 'Se aplicó la batería vocacional de Holland con resultado compatible en perfil analítico.', '2025-02-14 11:30:00');

-- Consulta 5: Diagnóstico General (Evaluación de control)
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_5, @psic_iris);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Evaluación psicológica de rutina. Estudiante orientado en tiempo, espacio y persona con discurso coherente.', 'No requiere tratamiento psicológico continuo. Recomendaciones de rendimiento académico.', 'No aplica', 'No aplica', 'No aplica', 'Cita solicitada por iniciativa propia para valoración de desempeño.', '2025-03-26 15:10:00');

-- Consulta 6: Retiro Temporal
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_6, @psic_iris);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Retiro temporal', 'No aplica', 'No aplica', 'Situación de salud familiar severa en su municipio de origen que requiere cuidado continuo.', '1 Semestre', 'No aplica', 'Se entrega informe institucional para avalar la suspensión temporal de la matrícula.', '2025-04-18 08:45:00');

-- Consulta 7: Diagnóstico General (Ansiedad)
INSERT INTO detalle_patologia (id_patologia) VALUES (6);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_7, @psic_iris);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Ansiedad por desempeño durante las actividades de laboratorios prácticos y evaluaciones de campo.', 'Entrenamiento en técnicas de afrontamiento cognitivo y autoinstrucciones positivas.', 'No aplica', 'No aplica', 'No aplica', 'Se recomienda coordinación de tutoría para reforzar conceptos prácticos.', '2025-05-30 13:20:00');

-- Consulta 8: Cambio de Carrera
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_8, @psic_iris);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Cambio de carrera', 'No aplica', 'No aplica', 'No aplica', 'No aplica', 'Baja afinidad con PNF Deporte e interés en PNF Administración.', 'Resultados de test de intereses ocupacionales respaldan la solicitud del cambio de especialidad.', '2025-06-22 10:00:00');


-- =====================================================================
-- PSICÓLOGA 2: Yaselis Contreras
-- =====================================================================

-- Consulta 9: Diagnóstico General (Ansiedad)
INSERT INTO detalle_patologia (id_patologia) VALUES (6);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_9, @psic_yaselis);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Ansiedad generalizada reactiva a presiones financieras socioeconómicas familiares que impactan la concentración.', 'Intervención breve enfocada en soluciones, psicoeducación afectiva y canalización con Trabajo Social.', 'No aplica', 'No aplica', 'No aplica', 'Se coordina remisión a la unidad socioeconómica institucional para evaluación de beneficio.', '2025-07-11 16:00:00');

-- Consulta 10: Retiro Temporal
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_10, @psic_yaselis);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Retiro temporal', 'No aplica', 'No aplica', 'Dificultades socioeconómicas severas y desplazamiento laboral temporal a otra región.', '2 Semestres', 'No aplica', 'Estudiante acude a consulta para formalizar la recomendación de retiro temporal por motivos económicos.', '2025-08-25 09:15:00');

-- Consulta 11: Diagnóstico General (Sin patología)
INSERT INTO detalle_patologia (id_patologia) VALUES (2);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_11, @psic_yaselis);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Consulta preventiva de manejo de tiempo. El beneficiario no presenta patología psiquiátrica ni alteraciones emocionales.', 'Pautas de organización académica, mapa de metas personales a mediano plazo.', 'No aplica', 'No aplica', 'No aplica', 'Estudiante de excelente rendimiento que busca optimizar su ritmo de estudio.', '2025-09-14 14:50:00');

-- Consulta 12: Cambio de Carrera
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_12, @psic_yaselis);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Cambio de carrera', 'No aplica', 'No aplica', 'No aplica', 'No aplica', 'Afinidad a PNF Contaduría Pública por habilidades numéricas.', 'Entrevista vocacional favorable. Muestra comprensión clara de las implicaciones de la carrera.', '2025-10-03 11:00:00');

-- Consulta 13: Diagnóstico General (Ansiedad)
INSERT INTO detalle_patologia (id_patologia) VALUES (6);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_13, @psic_yaselis);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Cuadro de ansiedad reactiva por duelo no complicado tras la pérdida de un familiar cercano.', 'Acompañamiento psicológico en proceso de duelo, validación emocional y 3 sesiones de apoyo.', 'No aplica', 'No aplica', 'No aplica', 'Muestra avance progresivo en la elaboración del duelo. Asistirá a control en un mes.', '2025-11-19 15:30:00');

-- Consulta 14: Diagnóstico General (Estrés laboral/estudiantil)
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_14, @psic_yaselis);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Fatiga física y mental vinculada al cumplimiento simultáneo de actividades laborales nocturnas y clases académicas.', 'Estrategias de higiene del sueño, priorización de tareas y descansos programados.', 'No aplica', 'No aplica', 'No aplica', 'Se aconseja evaluación de la carga horaria académica para evitar el síndrome de agotamiento.', '2025-12-10 08:30:00');

-- Consulta 15: Cambio de Carrera
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_15, @psic_yaselis);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Cambio de carrera', 'No aplica', 'No aplica', 'No aplica', 'No aplica', 'Traslado a PNF Turismo por inclinación a gestión patrimonial.', 'Aplica prueba de perfil vocacional. Informe favorable para la comisión de admisión.', '2026-01-15 10:45:00');

-- Consulta 16: Diagnóstico General (Ansiedad)
INSERT INTO detalle_patologia (id_patologia) VALUES (6);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_16, @psic_yaselis);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Manifestación somática de ansiedad por estrés de entregas de proyectos sociointegradores finales.', 'Técnicas de regulación emocional, autoinstrucciones positivas y ejercicios de relajación progresiva de Jacobson.', 'No aplica', 'No aplica', 'No aplica', 'Buena respuesta al tratamiento psicoeducativo.', '2026-02-20 13:15:00');


-- =====================================================================
-- PSICÓLOGA 3: María Alejandra Ríos
-- =====================================================================

-- Consulta 17: Diagnóstico General (Ansiedad)
INSERT INTO detalle_patologia (id_patologia) VALUES (6);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_17, @psic_maria);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Trastorno de ansiedad social leve que interfiere en la presentación de defensas públicas de proyectos.', 'Entrenamiento en habilidades sociales, simulación de presentaciones y modulación de la voz.', 'No aplica', 'No aplica', 'No aplica', 'Participa activamente en la sesión de ensayo grupal. Muestra avances notables.', '2026-03-12 09:30:00');

-- Consulta 18: Cambio de Carrera
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_18, @psic_maria);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Cambio de carrera', 'No aplica', 'No aplica', 'No aplica', 'No aplica', 'Reorientación a PNF Higiene y Seguridad por prevención de riesgos.', 'Estudiante cuenta con habilidades organizativas acordes a la especialidad elegida.', '2026-04-24 15:00:00');

-- Consulta 19: Retiro Temporal
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_19, @psic_maria);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Retiro temporal', 'No aplica', 'No aplica', 'Proceso médico postquirúrgico y rehabilitación que requiere reposo absoluto domiciliario.', '1 Semestre', 'No aplica', 'Consigna constancia emitida por el servicio médico institucional.', '2026-05-18 11:20:00');

-- Consulta 20: Diagnóstico General (Sin patología)
INSERT INTO detalle_patologia (id_patologia) VALUES (2);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_20, @psic_maria);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Consulta por orientación vocacional sin patología clínica identificada. Funcionamiento cognitivo y afectivo conservado.', 'Asesoría vocacional personalizada e información de perfiles de egreso.', 'No aplica', 'No aplica', 'No aplica', 'Estudiante decide continuar en su especialidad actual tras aclarar dudas de salida profesional.', '2026-06-29 14:00:00');

-- Consulta 21: Diagnóstico General (Ansiedad)
INSERT INTO detalle_patologia (id_patologia) VALUES (6);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_21, @psic_maria);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Sintomatología ansiosa asociada a la toma de decisiones profesionales de fin de trayecto.', 'Reestructuración cognitiva, resolución de problemas y focalización en metas inmediatas.', 'No aplica', 'No aplica', 'No aplica', 'Evolución satisfactoria. Se otorga alta psicológica de la fase aguda.', '2026-07-21 10:10:00');

-- Consulta 22: Cambio de Carrera
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_22, @psic_maria);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Cambio de carrera', 'No aplica', 'No aplica', 'No aplica', 'No aplica', 'Cambio vocacional a PNF Agroalimentación por producción sostenible.', 'Batería psicométrica confirma perfil aptitudinal hacia áreas agrícolas y ambientales.', '2026-08-16 16:30:00');

-- Consulta 23: Diagnóstico General (Estrés adaptativo)
INSERT INTO detalle_patologia (id_patologia) VALUES (3);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_23, @psic_maria);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Tensión emocional y sobrecarga percibida ante el inicio de prácticas profesionales institucionales.', 'Técnicas de gestión del tiempo, comunicación asertiva y contención emocional.', 'No aplica', 'No aplica', 'No aplica', 'Se mantiene en seguimiento mensual voluntario.', '2026-09-08 09:00:00');

-- Consulta 24: Diagnóstico General (Ansiedad)
INSERT INTO detalle_patologia (id_patologia) VALUES (6);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (1, @ben_24, @psic_maria);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_psicologica (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones, fecha_creacion)
VALUES (@sol, @det, 'Diagnóstico', 'Episodio ansioso agudo generado por sobrecarga de compromisos académicos de fin de trayecto.', 'Psicoterapia breve de apoyo, ejercicios de grounding y planificación semanal.', 'No aplica', 'No aplica', 'No aplica', 'Paciente se retira tranquilo y fortalecido tras la sesión.', '2026-10-04 11:45:00');

-- =====================================================================
-- FIN DEL SCRIPT CORREGIDO DE PSICOLOGÍA
-- =====================================================================