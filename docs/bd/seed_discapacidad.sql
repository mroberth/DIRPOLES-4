-- =====================================================================
-- LLENADO MASIVO: DIAGNÓSTICOS DE DISCAPACIDAD (id_servicios = 5)
-- Tablas involucradas: solicitud_de_servicio, discapacidad
-- =====================================================================

SET NAMES utf8mb4;

-- 0. Limpieza preventiva en orden seguro
DELETE d, ss 
FROM discapacidad d
JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = d.id_solicitud_serv
WHERE d.fecha_creacion >= '2024-11-01';

-- 1. Resolver IDs de Especialistas en Discapacidad
SET @disc_yhon     = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '15928374' LIMIT 1), 1);
SET @disc_marielva = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '24671583' LIMIT 1), @disc_yhon);

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
-- ESPECIALISTA 1: Yhon Guerra
-- =====================================================================

-- Diagnóstico 1
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_1, @disc_yhon);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Física', 'Hemiparesia izquierda residual de etiología congénita.', 'Dificultad moderada para la marcha prolongada y bipedestación sostenida.', 'Moderado', 'Sin medicación fija indicada actualmente.', 'Autonomía en desplazamiento a ritmo pausado, adecuada movilidad superior.', 'No', 'Bastón ortopédico monopodal para trayectos largos.', 'Estudiante cursa trayecto académico regular con apoyo de ubicación.', 'Garantizar asignación de aulas en planta baja o con rampa habilitada.', 'CONAPDIS-194820', '2024-11-25');

-- Diagnóstico 2
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_2, @disc_yhon);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Sensorial', 'Hipoacusia neurosensorial bilateral moderada.', 'Disminución de agudeza auditiva bilateral con comprensión mediante lectura labial.', 'Moderado', 'Sin medicación requerida.', 'Excelente lectura labiofacial, lectoescritura fluida y manejo de lengua de señas.', 'Si', 'Auxiliares auditivos retroauriculares bilaterales.', 'Requiere ubicación en los primeros pupitres de la sección asignada.', 'Facilitar material educativo digitalizado e impreso de apoyo.', 'CONAPDIS-839201', '2024-12-18');

-- Diagnóstico 3
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_3, @disc_yhon);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Sensorial', 'Baja visión por miopía magna no corregible totalmente.', 'Restricción significativa del campo visual y baja agudeza visual focal.', 'Grave', 'Lágrimas artificiales en gotas c/8h.', 'Manejo autónomo de lectores de pantalla sintéticos y macrocristales.', 'No', 'Lentes de alta graduación y software lector de pantalla NVDA.', 'Requiere evaluaciones impresas con contraste de fuente ampliada (18pt+).', 'Proporcionar material evaluativo en formato digital accesible.', 'CONAPDIS-492018', '2025-01-28');

-- Diagnóstico 4
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_4, @disc_yhon);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Intelectual', 'Compromiso cognitivo leve en procesamiento abstracto.', 'Dificultad leve en la abstracción de conceptos matemáticos complejos.', 'Leve', 'No indica medicación.', 'Adecuada interacción social, cumplimiento de tareas con guía estructurada.', 'No', 'Guías visuales estructuradas y esquemas de trabajo.', 'Responde de forma positiva a la adaptación del ritmo evaluativo.', 'Desglosar las instrucciones escritas en pasos secuenciales breves.', NULL, '2025-02-20');

-- Diagnóstico 5
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_5, @disc_yhon);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Múltiple', 'Parálisis cerebral infantil de tipo diplejía espástica.', 'Compromiso motor severo en miembros inferiores y disartria leve.', 'Grave', 'Miorrelajantes según prescripción neuropediátrica.', 'Desplazamiento autónomo en silla de ruedas, excelente comprensión.', 'Si', 'Silla de ruedas manual ergonómica.', 'Requiere adecuación de mesas en laboratorios de informática.', 'Asegurar rampa de acceso y mesas de altura adaptada en laboratorios.', 'CONAPDIS-721094', '2025-03-24');

-- Diagnóstico 6
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_6, @disc_yhon);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Física', 'Amputación traumática parcial de antebrazo izquierdo.', 'Ausencia funcional del segmento distal del miembro superior izquierdo.', 'Moderado', 'No requiere medicamentos.', 'Desplazamiento normal, compensación motora eficaz con miembro derecho.', 'No', 'Prótesis mecánica funcional de antebrazo.', 'Estudiante adaptado al uso de teclado con una sola mano.', 'Permitir tiempo adicional en evaluaciones escritas o de tipeo.', 'CONAPDIS-305912', '2025-04-18');

-- Diagnóstico 7
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_7, @disc_yhon);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Sensorial', 'Acúfenos crónicos e hipoacusia leve unilateral.', 'Disminución discreta de la audición en oído derecho por trauma acústico.', 'Leve', 'No requiere medicación gótica.', 'Comunicación verbal normal en ambientes con bajo ruido de fondo.', 'No', 'Filtros auditivos de protección.', 'Monitoreo preventivo en áreas ruidosas.', 'Evitar exposición continuada a altoparlantes o ruido elevado.', NULL, '2025-05-29');

-- Diagnóstico 8
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_8, @disc_yhon);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Otro', 'Condición metabólica con neuropatía periférica motora.', 'Disminución de sensibilidad de miembros inferiores y fatigabilidad.', 'Moderado', 'Tratamiento regulador neurológico prescrito.', 'Marcha lenta, independencia plena en actividades académicas de aula.', 'No', 'Plantillas ortopédicas de descarga.', 'Revisión semestral de la condición de movilidad.', 'Se aconseja descansos breves de 5 minutos en clases largas.', 'CONAPDIS-612048', '2025-06-22');


-- =====================================================================
-- ESPECIALISTA 2: Marielva Hernández
-- =====================================================================

-- Diagnóstico 9
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_9, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Física', 'Secuela de poliomielitis en miembro inferior derecho.', 'Marcha claudicante con disminución de fuerza muscular en pierna derecha.', 'Moderado', 'No indica medicamentos.', 'Independencia funcional con apoyo de órtesis pasiva.', 'No', 'Órtesis corta de tobillo y pie (AFO).', 'Se desplaza con autonomía por el recinto universitario.', 'Evitar traslados entre edificios distantes en receso corto.', 'CONAPDIS-519203', '2025-07-19');

-- Diagnóstico 10
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_10, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Sensorial', 'Restos visuales conservados / Visión tubular.', 'Disminución marcada de la visión periférica bilateral.', 'Grave', 'Gotas hipotensoras oculares cada 12 horas.', 'Desplazamiento con bastón guía, orientación espacial adecuada.', 'Si', 'Bastón blanco rígido de orientación.', 'Acompañamiento en áreas de alta concurrencia o escaleras.', 'Mantener pasillos libres de obstáculos físicos.', 'CONAPDIS-940215', '2025-08-30');

-- Diagnóstico 11
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_11, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Física', 'Malformación congénita de mano derecha (Oligodactilia).', 'Limitación fina de agarre en mano derecha sin afectación del brazo.', 'Leve', 'No requiere medicamentos.', 'Plena capacidad de lectoescritura con mano izquierda dominante.', 'No', 'Adaptador ergonómico para bolígrafo.', 'Estudiante con alto rendimiento en desarrollo de software.', 'Permitir uso de computadoras portátiles para toma de apuntes.', NULL, '2025-09-22');

-- Diagnóstico 12
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_12, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Intelectual', 'Dificultad de aprendizaje procedimental leve.', 'Retraso leve en la consolidación de secuencias lógicas automatizadas.', 'Leve', 'No indica fármacos.', 'Expresión verbal fluida, buena sociabilidad y comprensión general.', 'No', 'Calculadora y esquemas visuales de apoyo.', 'Acompañamiento periódico por parte del departamento de orientación.', 'Flexibilización en el tiempo de entrega de evaluaciones escritas.', 'CONAPDIS-204918', '2025-10-16');

-- Diagnóstico 13
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_13, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Sensorial', 'Anacusia unilateral derecha.', 'Pérdida completa de la audición en oído derecho por parotiditis infantil.', 'Leve', 'No requiere medicamentos.', 'Comprensión auditiva conservada mediante oído izquierdo sano.', 'No', 'Sin dispositivo específico.', 'Ubicación estratégica en el costado izquierdo del aula.', 'Garantizar que el docente hable desde el campo auditivo izquierdo.', NULL, '2025-11-24');

-- Diagnóstico 14
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_14, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Múltiple', 'Síndrome motor-sensorial post-traumatismo vertebromedular.', 'Monoparesia de miembro inferior izquierdo e hipoacusia leve.', 'Grave', 'Analgésicos neuropáticos según necesidad.', 'Movilidad con apoyo postural, uso de lenguaje de señas básico.', 'Si', 'Silla de ruedas motorizada plegable.', 'Se tramita carnet de estacionamiento preferencial.', 'Ubicación en planta baja y asistencia en laboratorios.', 'CONAPDIS-730192', '2025-12-19');

-- Diagnóstico 15
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_15, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Física', 'Artritis juvenil idiopática en fase remisa.', 'Rigidez articular matutina de carácter leve en articulaciones intergalángicas.', 'Leve', 'Antiinflamatorios de rescate ocasionales.', 'Movilidad manual conservada sin deformidad articular.', 'No', 'Férulas nocturnas de reposo.', 'Monitoreo en épocas de bajas temperaturas.', 'Evitar mantener las manos en posturas estáticas prolongadas.', 'CONAPDIS-118293', '2026-01-22');

-- Diagnóstico 16
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_16, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Sensorial', 'Estrabismo divergente severo con ambliopía binocular.', 'Dificultad en la percepción de profundidad y visión binocular reducida.', 'Moderado', 'Tratamiento ortóptico periódico.', 'Lectura a distancia corta adecuada, desplazamiento autónomo.', 'No', 'Lentes con prismas correctores.', 'Se sugiere descansos visuales de 10 minutos cada hora.', 'Ubicación en filas centrales del aula a distancia media.', NULL, '2026-02-26');

-- Diagnóstico 17
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_17, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Física', 'Espina bífida oculta con debilidad muscular distal.', 'Disminución leve del tono muscular en pies y tobillos.', 'Moderado', 'Suplementos vitamínicos del complejo B.', 'Marcha independiente, dificultad moderada al subir gradas.', 'No', 'Plantillas de soporte de arco plantar.', 'Uso continuo de elevador en sedes de múltiples niveles.', 'Asignar aulas accesibles mediante rampa o ascensor.', 'CONAPDIS-883019', '2026-03-20');

-- Diagnóstico 18
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_18, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Otro', 'Acondroplasia / Talla baja armónica.', 'Restricción del crecimiento óseo longitudinal sin afectación cognitiva.', 'Moderado', 'No indica medicamentos.', 'Independencia plena, destreza motora fina excelente.', 'No', 'Escalones portátiles y adaptaciones posturales.', 'Se requiere adaptación de mobiliario en pupitres.', 'Facilitar apoya-pies y pupitre adaptado a su estatura.', 'CONAPDIS-449102', '2026-04-25');

-- Diagnóstico 19
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_19, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Sensorial', 'Hipoacusia neurosensorial profunda bilateral.', 'Ausencia funcional de la audición bilateral.', 'Grave', 'No indica fármacos.', 'Excelente dominio de Lengua de Señas Venezolana (LSV) y lectura labial.', 'Si', 'Implante coclear unilateral en oído izquierdo.', 'Cuenta con acompañamiento de intérprete en asignaturas complejas.', 'Permitir el ingreso de intérprete de lengua de señas al aula.', 'CONAPDIS-992014', '2026-05-18');

-- Diagnóstico 20
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_20, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Física', 'Secuela postural de Escoliosis idiopática severa.', 'Desviación lateral de columna vertebral que genera fatiga al estar sentado.', 'Moderado', 'Analgésicos de uso eventual.', 'Marcha y postura en bipedestación normales.', 'No', 'Corsé de soporte dorsolumbar.', 'Requiere silla ergonómica con soporte lumbar adecuado.', 'Permitir que el estudiante se ponga de pie breves minutos en clase.', NULL, '2026-06-30');

-- Diagnóstico 21
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_21, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Intelectual', 'Trastorno del espectro autista de alto funcionamiento (Nivel 1).', 'Dificultad leve en la flexibilidad cognitiva e interacción social no verbal.', 'Leve', 'Sin medicación psicotrópica.', 'Alta capacidad de concentración en temas de su interés (programación).', 'No', 'Audífonos de cancelación de ruido para ambientes saturados.', 'Estudiante muy organizado con alto rendimiento en lógica.', 'Mantener ambiente predictivo y notificar cambios de evaluación con tiempo.', 'CONAPDIS-339102', '2026-07-22');

-- Diagnóstico 22
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_22, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Física', 'Distrofia muscular progresiva en fase inicial.', 'Debilidad moderada en musculatura proximal de miembros inferiores.', 'Grave', 'Tratamiento fisioterapéutico continuo.', 'Desplazamiento autónomo a corta distancia con apoyo de andadera.', 'Si', 'Andadera de aluminio con ruedas frontales.', 'Se evalúa necesidad de transporte institucional preferencial.', 'Asignar aulas en planta baja cerca de la entrada principal.', 'CONAPDIS-664019', '2026-08-28');

-- Diagnóstico 23
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_23, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Sensorial', 'Ceguera total de ojo derecho por trauma ocular.', 'Pérdida de la visión monocular derecha con conservación del ojo izquierdo.', 'Moderado', 'Gotas protectoras en ojo izquierdo.', 'Visión monocular conservada, orientación espacial buena.', 'No', 'Lentes de policarbonato con protección UV.', 'Ubicación en el sector derecho del aula.', 'Proteger el ojo funcional durante actividades de laboratorio.', NULL, '2026-09-20');

-- Diagnóstico 24 (Del mes actual: Octubre 2026)
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (5, @ben_24, @disc_marielva);
INSERT INTO discapacidad (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado, medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia, observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Física', 'Monoparesia leve residual de miembro superior derecho.', 'Disminución discreta de la fuerza de agarre en mano derecha.', 'Leve', 'Ejercicios de rehabilitación de fuerza fina.', 'Independencia funcional total con compensación.', 'No', 'Pelota de fisioterapia para articulación.', 'Diagnóstico reciente registrado en la unidad este mes.', 'Permitir tiempo adicional en pruebas escritas prolongadas.', 'CONAPDIS-551029', '2026-10-07');

-- =====================================================================
-- FIN DEL SCRIPT DE DISCAPACIDAD
-- =====================================================================