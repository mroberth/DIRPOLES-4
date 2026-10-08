-- =====================================================================
-- LLENADO MASIVO: DIAGNÓSTICOS DE ORIENTACIÓN (id_servicios = 3)
-- Tablas involucradas: solicitud_de_servicio, orientacion
-- =====================================================================

SET NAMES utf8mb4;

-- 0. Limpieza preventiva en orden seguro
DELETE o, ss 
FROM orientacion o
JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = o.id_solicitud_serv
WHERE o.fecha_creacion >= '2024-11-01';

-- 1. Resolver IDs de Orientadores
SET @ori_jhan    = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '13652666' LIMIT 1), 2);
SET @ori_edward  = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '18364725' LIMIT 1), @ori_jhan);
SET @ori_nohelia = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '22159408' LIMIT 1), @ori_jhan);

-- 2. Resolver IDs de Beneficiarios
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
-- ORIENTADOR 1: Jhan Guevara
-- =====================================================================

-- Orientación 1
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_1, @ori_jhan);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación sobre hábitos de estudio y rendimiento académico.', 'El estudiante manifiesta vacíos metodológicos en la organización de su tiempo libre y estudio independiente.', 'Crear una rutina de estudio fija de 2 horas diarias y emplear la técnica Pomodoro.', 'Muestra excelente disposición para adoptar nuevas técnicas de lectura.', '2024-11-20');

-- Orientación 2
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_2, @ori_jhan);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Asesoría vocacional y clarificación de metas profesionales.', 'Consulta sobre opciones de desempeño profesional de su especialidad y áreas de inserción laboral.', 'Revisar el diseño curricular del PNF e investigar el perfil de egreso en el área regional.', 'Se acuerda una segunda sesión para evaluar áreas de preferencia laboral.', '2024-12-15');

-- Orientación 3
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_3, @ori_jhan);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación para el abordaje del proyecto sociointegrador.', 'Dificultad en la selección de la comunidad y definición del diagnóstico participativo.', 'Coordinar reunión con el tutor institucional y solicitar formatos de vinculación comunitaria.', 'El estudiante asistirá a la asamblea comunitaria la próxima semana.', '2025-01-25');

-- Orientación 4
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_4, @ori_jhan);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Acompañamiento en la adaptación al entorno académico universitario.', 'Estudiante de primer trayecto reporta desorientación sobre trámites académicos institucionales.', 'Asistir a la jornada de inducción del PNF e inscribirse en las tutorías entre pares.', 'Se hace entrega de la guía de procedimientos al estudiante.', '2025-02-18');

-- Orientación 5
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_5, @ori_jhan);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Asesoría en preparación de síntesis curricular y entrevistas.', 'Búsqueda de orientación para optar a pasantías universitarias institucionales.', 'Estructurar el currículum resaltando proyectos académicos y competencias técnicas desarrolladas.', 'Revisión final de la síntesis curricular programada para el próximo lunes.', '2025-03-22');

-- Orientación 6
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_6, @ori_jhan);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación ante el bajo rendimiento en evaluaciones continuas.', 'Acude remitido por la coordinación tras reprobar evaluaciones parciales del trayecto.', 'Diseñar cuadro comparativo de asignaturas críticas e ingresar a círculos de estudio grupal.', 'Compromiso firmado de asistencia regular a asesorías docentes.', '2025-04-15');

-- Orientación 7
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_7, @ori_jhan);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación en la toma de decisiones sobre carga horaria.', 'Evaluación de cantidad de unidades crediticias inscritas frente a compromisos personales.', 'Solicitar ajuste de horario para evitar solapamientos con su jornada laboral.', 'Trámite canalizado satisfactoriamente con la unidad de control de estudios.', '2025-05-30');

-- Orientación 8
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_8, @ori_jhan);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Seguimiento pedagógico y planificación académica personal.', 'Sesión de control periódica para evaluar el cumplimiento del plan de estudio individual.', 'Mantener el ritmo de entregas académicas y priorizar la redacción del informe final.', 'Resultados académicos positivos respecto a la consulta inicial.', '2025-06-25');


-- =====================================================================
-- ORIENTADOR 2: Edward Urdaneta
-- =====================================================================

-- Orientación 9
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_9, @ori_edward);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación para la reincorporación al trayecto académico.', 'Estudiante reincorporado solicita guía sobre normativa de evaluación y asignación de secciones.', 'Visitar los departamentos académicos correspondientes para solicitar plan de contingencia.', 'Estudiante orientado satisfactoriamente sobre el reglamento de evaluación.', '2025-07-18');

-- Orientación 10
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_10, @ori_edward);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Asesoría pedagógica en metodologías de investigación aplicada.', 'Dudas conceptuales en la delimitación del problema y alcance de su proyecto universitario.', 'Consultar bibliografía recomendada en el centro de documentación y coordinar asesoría técnica.', 'Muestra comprensión adecuada de la ruta metodológica a seguir.', '2025-08-28');

-- Orientación 11
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_11, @ori_edward);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación sobre becas y apoyos socioacadémicos.', 'Consulta de requisitos institucionales para aplicar a programas de apoyo estudiantil.', 'Consignar recaudos socioeconómicos actualizados en la unidad de Trabajo Social.', 'Remisión realizada para trámite socioeconómico formal.', '2025-09-20');

-- Orientación 12
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_12, @ori_edward);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Estrategias de afrontamiento del pánico escénico en defensas.', 'Temor infundado al presentar informes orales ante comités evaluadores.', 'Realizar ensayos grupales de exposición con retroalimentación entre compañeros.', 'Mejoría notable de la confianza personal durante la simulación.', '2025-10-15');

-- Orientación 13
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_13, @ori_edward);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación en la resolución de conflictos comunitarios de proyecto.', 'Desacuerdos entre los integrantes del equipo de proyecto sociointegrador.', 'Establecer acta de acuerdos internos de trabajo y dividir responsabilidades por módulo.', 'El equipo de trabajo logró definir compromisos claros.', '2025-11-22');

-- Orientación 14
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_14, @ori_edward);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Asesoría vocacional para egresados de TSU hacia Ingeniería/Licenciatura.', 'Incertidumbre sobre la prosecución de estudios al nivel de grado.', 'Revisar las líneas de investigación disponibles en el tramo de prosecución del PNF.', 'Estudiante decide continuar sus estudios de prosecución académica.', '2025-12-18');

-- Orientación 15
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_15, @ori_edward);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación pedagógica sobre comprensión lectora y análisis técnico.', 'Dificultad en la interpretación de manuales y bibliografía técnica especializada.', 'Utilizar técnicas de subrayado jerárquico y elaboración de esquemas conceptuales.', 'Se entregan guías prácticas de análisis documental.', '2026-01-20');

-- Orientación 16
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_16, @ori_edward);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Acompañamiento en el cumplimiento del servicio comunitario.', 'Dudas sobre las 120 horas lectivas requeridas y llenado de bitácoras de campo.', 'Registrar actividades semanalmente en la bitácora física y solicitar firma del vocero.', 'Proceso de vinculación comunitaria en marcha sin contratiempos.', '2026-02-25');


-- =====================================================================
-- ORIENTADOR 3: Nohelia Quintero
-- =====================================================================

-- Orientación 17
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_17, @ori_nohelia);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación para la organización del tiempo entre estudio y trabajo.', 'El beneficiario realiza jornadas laborales vespertinas que afectan sus lecturas nocturnas.', 'Rediseñar horario semanal otorgando bloques prioritarios los fines de semana.', 'Se logra una distribución horaria más equilibrada y realista.', '2026-03-18');

-- Orientación 18
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_18, @ori_nohelia);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Asesoría para la solicitud de traslados de sección o sede.', 'Inconvenientes de transporte público desde su lugar de residencia hasta la sede.', 'Consignar carta justificativa de residencia ante la coordinación del PNF.', 'Tramitación administrativa en curso.', '2026-04-22');

-- Orientación 19
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_19, @ori_nohelia);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación en técnicas de resolución de exámenes analíticos.', 'Reporta bloqueo mental al enfrentarse a pruebas escritas con límite de tiempo.', 'Aplicar lecturas iniciales globales y responder primero las preguntas de mayor dominio.', 'El estudiante pondrá en práctica las recomendaciones en el próximo examen.', '2026-05-15');

-- Orientación 20
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_20, @ori_nohelia);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Acompañamiento pedagógico post-recuperación de salud.', 'Reincorporación tras reposo médico prolongado con acumulación de asignaciones.', 'Coordinar con cada docente un cronograma de entregas extemporáneas justificadas.', 'Docentes notificados y receptivos al plan de entrega.', '2026-06-28');

-- Orientación 21
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_21, @ori_nohelia);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación sobre canales institucionales de atención al estudiante.', 'Desconocimiento del catálogo de servicios transversales disponibles en la UPTAEB.', 'Informar sobre las unidades de Psicología, Medicina, Trabajo Social y Biblioteca.', 'Se entrega tríptico informativo de los servicios de la institución.', '2026-07-20');

-- Orientación 22
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_22, @ori_nohelia);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Asesoría para la formulación de objetivos de investigación.', 'Redacción ambigua en los objetivos específicos del proyecto comunitario.', 'Utilizar la taxonomía de Bloom para seleccionar verbos en infinitivo medibles.', 'Objetivos corregidos y alineados con la metodología.', '2026-08-25');

-- Orientación 23
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_23, @ori_nohelia);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Orientación preventiva sobre deserción estudiantil.', 'Dudas sobre continuar la carrera debido a desmotivación temporal.', 'Reevaluar los logros alcanzados y definir metas a corto plazo.', 'Estudiante decide continuar matriculado con acompañamiento quincenal.', '2026-09-18');

-- Orientación 24
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (3, @ben_24, @ori_nohelia);
INSERT INTO orientacion (id_solicitud_serv, motivo_orientacion, descripcion_orientacion, indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Evaluación de cierre de acompañamiento pedagógico.', 'Sesión final para verificar el logro de las metas académicas del trayecto.', 'Mantener las metodologías de estudio consolidadas durante las sesiones.', 'Cierre de caso por cumplimiento satisfactorio de los objetivos planteados.', '2026-10-06');

-- =====================================================================
-- FIN DEL SCRIPT DE ORIENTACIÓN
-- =====================================================================