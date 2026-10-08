-- =====================================================================
-- LLENADO MASIVO: CONSULTAS MÉDICAS (id_servicios = 2)
-- Tablas involucradas: patologia, detalle_patologia, solicitud_de_servicio, 
--                      consulta_medica, detalle_insumo
-- =====================================================================

SET NAMES utf8mb4;

-- 0. Limpieza preventiva en orden correcto (primero detalle_insumo)
DELETE di 
FROM detalle_insumo di
JOIN consulta_medica cm ON cm.id_consulta_med = di.id_consulta_med
WHERE cm.fecha_creacion >= '2024-11-01';

DELETE cm, ss, dp 
FROM consulta_medica cm
JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cm.id_solicitud_serv
LEFT JOIN detalle_patologia dp ON dp.id_detalle_patologia = cm.id_detalle_patologia
WHERE cm.fecha_creacion >= '2024-11-01';

-- 1. Insertar patologías médicas comunes si aún no existen
INSERT INTO patologia (nombre_patologia, tipo_patologia, fecha_creacion)
SELECT v.nombre, v.tipo, v.fecha
FROM (
    SELECT 'Cefalea tensional' AS nombre, 'Médica' AS tipo, '2025-01-10' AS fecha
    UNION ALL SELECT 'Gastritis aguda', 'Médica', '2025-01-10'
    UNION ALL SELECT 'Faringoamigdalitis', 'Médica', '2025-01-10'
    UNION ALL SELECT 'Asma bronquial', 'Médica', '2025-01-10'
) v
WHERE NOT EXISTS (SELECT 1 FROM patologia WHERE nombre_patologia = v.nombre);

-- 2. Resolver IDs de Médicos
SET @med_carlos = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '19632847' LIMIT 1), 1);
SET @med_yuri   = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '16754209' LIMIT 1), @med_carlos);

-- 3. Resolver IDs de Patologías Médicas
SET @pat_sin        = (SELECT id_patologia FROM patologia WHERE nombre_patologia = 'Sin patología médica' LIMIT 1);
SET @pat_gripe      = (SELECT id_patologia FROM patologia WHERE nombre_patologia = 'Gripe' LIMIT 1);
SET @pat_cefalea    = (SELECT id_patologia FROM patologia WHERE nombre_patologia = 'Cefalea tensional' LIMIT 1);
SET @pat_gastritis  = (SELECT id_patologia FROM patologia WHERE nombre_patologia = 'Gastritis aguda' LIMIT 1);
SET @pat_faringitis = (SELECT id_patologia FROM patologia WHERE nombre_patologia = 'Faringoamigdalitis' LIMIT 1);
SET @pat_asma       = (SELECT id_patologia FROM patologia WHERE nombre_patologia = 'Asma bronquial' LIMIT 1);

-- 4. Resolver IDs de Insumos
SET @ins_aceta = COALESCE((SELECT id_insumo FROM insumos WHERE nombre_insumo LIKE '%Acetaminofén%' LIMIT 1), 1);

-- 5. Resolver IDs de Beneficiarios
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
-- MÉDICO 1: Dr. Carlos Sánchez
-- =====================================================================

-- Consulta 1
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_cefalea);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_1, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.72, 68.50, 'O+', 'Cefalea pulsátil de 24 horas y fotofobia leve.', 'Cefalea tensional secundaria a estrés académico.', 'Acetaminofén 500mg V.O. c/8h por 3 días y reposo.', 'Se recomienda control de tensión arterial y descanso de pantallas.', '2024-11-18');
INSERT INTO detalle_insumo (id_consulta_med, id_insumo, cantidad_usada) VALUES (LAST_INSERT_ID(), @ins_aceta, '2');

-- Consulta 2
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gripe);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_2, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.65, 58.00, 'A+', 'Congestión nasal, odinofagia y febrícula no cuantificada.', 'Síndrome viral respiratorio agudo.', 'Abundante hidratación oral, vitamina C y reposo relativo.', 'Evolución clínica estable, indicar uso de tapabocas.', '2024-12-10');

-- Consulta 3
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gastritis);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_3, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.78, 74.20, 'B+', 'Epigastralgia de tipo urente tras ingesta de irritantes.', 'Gastritis aguda irritativa.', 'Dieta blanda sin irritantes, antiácidos según indicación.', 'Evitar periodos prolongados de ayuno.', '2025-01-22');

-- Consulta 4
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_faringitis);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_4, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.60, 52.00, 'O-', 'Odinofagia moderada y sensación febril de 48 horas.', 'Faringoamigdalitis eritematosa agudizada.', 'Gárgaras antisépticas, analgésicos e hidratación.', 'Sin secreción purulenta visible al examen físico.', '2025-02-15');
INSERT INTO detalle_insumo (id_consulta_med, id_insumo, cantidad_usada) VALUES (LAST_INSERT_ID(), @ins_aceta, '1');

-- Consulta 5
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_sin);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_5, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.82, 81.00, 'AB+', 'Chequeo médico preventivo institucional.', 'Examen físico dentro de parámetros normales.', 'No requiere tratamiento farmacológico.', 'Signos vitales normales: TA 120/80 mmHg, FC 72 bpm.', '2025-03-20');

-- Consulta 6
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_asma);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_6, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.68, 62.50, 'A-', 'Disnea leve y sibilancias desencadenadas por cambios de clima.', 'Broncoespasmo leve / Asma bronquial compensada.', 'Uso de inhalador broncodilatador según esquema previo.', 'Se indica evitar exposición a alérgenos conocidos.', '2025-04-12');

-- Consulta 7
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_cefalea);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_7, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.75, 70.00, 'O+', 'Dolor de cabeza opresivo constante en región frontal.', 'Cefalea por fatiga visual y cansancio.', 'Analgésicos simples e hidratación adecuada.', 'Se recomienda evaluación por optometría.', '2025-05-28');

-- Consulta 8
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gripe);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_8, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.62, 55.40, 'B-', 'Mialgias, malestar general y estornudos frecuentes.', 'Rinitis viral aguda / Gripe.', 'Antihistamínicos, reposo en casa y líquidos orales.', 'Permiso de asistencia por 48 horas consignado.', '2025-06-19');
INSERT INTO detalle_insumo (id_consulta_med, id_insumo, cantidad_usada) VALUES (LAST_INSERT_ID(), @ins_aceta, '2');

-- Consulta 9
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gastritis);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_9, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.70, 66.80, 'A+', 'Acidez estomacal intensa posterior a ayuno prolongado.', 'Gastritis medicamentosa / irritativa.', 'Protector gástrico, dieta libre de grasas y condimentos.', 'Reevaluación en 5 días de persistir la sintomatología.', '2025-07-25');

-- Consulta 10
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_faringitis);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_10, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.66, 60.00, 'O+', 'Dolor al tragar alimentos sólidos y ardor faríngeo.', 'Faringitis aguda de etiología probable viral.', 'Analgésicos, antiinflamatorios y abundante agua.', 'Paciente asiste a control con franca mejoría.', '2025-08-30');

-- Consulta 11
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_sin);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_11, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.74, 72.00, 'A+', 'Valoración médica para práctica deportiva institucional.', 'Apto para actividades físicas y deportivas.', 'No requiere prescripción médica.', 'Certificado médico emitido satisfactoriamente.', '2025-09-18');

-- Consulta 12
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_cefalea);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_12, @med_carlos);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.69, 63.00, 'B+', 'Dolor pulsátil retroocular posterior a jornada intensiva.', 'Cefalea tensional severa.', 'Analgésicos orales, reposo en ambiente oscuro.', 'Mejoría notable tras 2 horas de reposo clínico.', '2025-10-14');


-- =====================================================================
-- MÉDICO 2: Dra. Yuri Boscán
-- =====================================================================

-- Consulta 13
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gripe);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_13, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.76, 71.50, 'O+', 'Tos seca episódica, estornudos y secreción hialina.', 'Infección respiratoria alta no complicada.', 'Tratamiento sintomático, reposo e hidratación constante.', 'Se sugiere control ambiental para evitar polvo.', '2025-11-20');
INSERT INTO detalle_insumo (id_consulta_med, id_insumo, cantidad_usada) VALUES (LAST_INSERT_ID(), @ins_aceta, '2');

-- Consulta 14
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gastritis);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_14, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.64, 56.00, 'A+', 'Dolor en epigastrio y náuseas matutinas leves.', 'Gastritis aguda por estrés y mala alimentación.', 'Dieta gástrica estricta, antiácidos orales.', 'Se recalca la importancia del desayuno regular.', '2025-12-15');

-- Consulta 15
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_faringitis);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_15, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.71, 67.00, 'AB+', 'Sensación de cuerpo extraño en garganta y tos.', 'Faringitis eritematosa leve.', 'Enjuagues bucofaríngeos y analgésicos.', 'Responde favorablemente al examen clínico.', '2026-01-18');

-- Consulta 16
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_asma);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_16, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.80, 83.00, 'O-', 'Opresión torácica leve posterior a ejercicio físico.', 'Crisis asmática leve / Broncoespasmo reactivo.', 'Nebulización de rescate en unidad, reposo por 1 hora.', 'Recuperación completa del patrón respiratorio.', '2026-02-22');

-- Consulta 17
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_cefalea);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_17, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.58, 51.20, 'B+', 'Cefalea occipital vinculada a posturas prolongadas.', 'Cefalea miotensiva de origen postural.', 'Compresas tibias cervicales, estiramientos y analgésicos.', 'Se dan pautas ergonómicas de estudio.', '2026-03-15');

-- Consulta 18
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gripe);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_18, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.73, 69.00, 'O+', 'Malestar generalizado, escalofríos y rinorrea.', 'Cuadro gripal agudo.', 'Reposo médico por 48 horas, analgésicos e hidratación.', 'Constancia médica entregada al beneficiario.', '2026-04-20');
INSERT INTO detalle_insumo (id_consulta_med, id_insumo, cantidad_usada) VALUES (LAST_INSERT_ID(), @ins_aceta, '1');

-- Consulta 19
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_sin);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_19, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.67, 59.80, 'A-', 'Evaluación médica general de seguimiento.', 'Estudiante asintomático. Parámetros normales.', 'Sin tratamiento indicado.', 'Continuar con estilo de vida saludable.', '2026-05-12');

-- Consulta 20
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gastritis);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_20, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.79, 77.00, 'A+', 'Pirosis constante y distensión abdominal postprandial.', 'Dispepsia funcional / Gastritis.', 'Antiácidos, modificaciones dietéticas.', 'Se solicita control en caso de continuar molestia.', '2026-06-25');

-- Consulta 21
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_faringitis);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_21, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.63, 54.50, 'O+', 'Odinofagia leve al despertar e irritación faríngea.', 'Faringitis aguda incipiente.', 'Gárgaras antisépticas e hidratación.', 'Sintomatología leve sin compromiso sistémico.', '2026-07-18');
INSERT INTO detalle_insumo (id_consulta_med, id_insumo, cantidad_usada) VALUES (LAST_INSERT_ID(), @ins_aceta, '2');

-- Consulta 22
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_cefalea);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_22, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.77, 75.00, 'B-', 'Dolor de cabeza hemicraneal opresivo episódico.', 'Cefalea tensional.', 'Acetaminofén, ambiente tranquilo.', 'Paciente indica mejoría tras toma de analgésico.', '2026-08-22');

-- Consulta 23
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gripe);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_23, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.70, 65.00, 'O+', 'Estornudos, obstrucción nasal y malestar corporal.', 'Rinitis viral agudizada.', 'Líquidos calientes, reposo relativo.', 'Indicaciones generales otorgadas.', '2026-09-15');

-- Consulta 24
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gastritis);
SET @det = LAST_INSERT_ID();
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (2, @ben_24, @med_yuri);
SET @sol = LAST_INSERT_ID();
INSERT INTO consulta_medica (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre, motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
VALUES (@det, @sol, 1.61, 53.00, 'A+', 'Dolor en epigastrio secundario a ingesta de café sin alimento.', 'Gastritis aguda estresora.', 'Tratamiento sintomático gástrico.', 'Recomendación de suspender irritantes por 7 días.', '2026-10-05');

-- =====================================================================
-- FIN DEL SCRIPT DE MEDICINA CORREGIDO
-- =====================================================================