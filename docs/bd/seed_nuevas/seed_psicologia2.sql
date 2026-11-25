-- =====================================================================
-- SEED DE CONSULTAS PSICOLÓGICAS (TANDA 2) — 60 registros sintéticos
-- Ubicación: docs/bd/seed_nuevas/seed_psicologia2.sql
-- Módulo: Diagnósticos de Psicología (id_modulo 4, id_servicios = 1)
--
-- Cobertura Temporal: 22/01/2024 al 17/11/2026 (Ideal para microservicio IA)
-- Tablas involucradas: patologia, detalle_patologia, solicitud_de_servicio,
--                      consulta_psicologica
--
-- Distribución por Tipo de Consulta (tipo_consulta):
--   * 'Diagnóstico'       : 40 consultas (~67% - casos clínicos reales)
--   * 'Cambio de carrera' : 11 consultas (~18% - reorientación vocacional)
--   * 'Retiro temporal'   : 9 consultas  (~15% - justificación de suspensión)
--     (Dispara automáticamente el trigger trg_desactivar_beneficiario_retiro)
--
-- Especialistas (Psicólogas activas en dirpoles_security.empleado):
--   * Iris Alvarez (Cédula: 12023052)
--   * Yaselis Contreras (Cédula: 14872391)
--   * María Alejandra Ríos (Cédula: 17543826)
--
-- 100% IDEMPOTENTE: omite registros si ya existen para el beneficiario y fecha.
-- =====================================================================

SET NAMES utf8mb4;

-- =====================================================================
-- 1. ASEGURAR PATOLOGÍAS PSICOLÓGICAS COMUNES EN EL CATÁLOGO
-- =====================================================================
INSERT INTO patologia (nombre_patologia, tipo_patologia, fecha_creacion)
SELECT v.nombre, v.tipo, v.fecha
FROM (
    SELECT 'Estrés académico' AS nombre, 'Psicológica' AS tipo, '2024-01-10' AS fecha
    UNION ALL SELECT 'Depresión leve', 'Psicológica', '2024-01-10'
    UNION ALL SELECT 'Trastorno de adaptación', 'Psicológica', '2024-01-10'
    UNION ALL SELECT 'Fobia social / Ansiedad social', 'Psicológica', '2024-01-10'
    UNION ALL SELECT 'Duelo no complicado', 'Psicológica', '2024-01-10'
    UNION ALL SELECT 'Agotamiento emocional / Burnout', 'Psicológica', '2024-01-10'
) v
WHERE NOT EXISTS (
    SELECT 1 FROM patologia p WHERE p.nombre_patologia = v.nombre
);

-- =====================================================================
-- 2. PROCEDIMIENTO TEMPORAL DE CARGA TRANSACCIONAL E IDEMPOTENTE
-- =====================================================================
DELIMITER ;;
DROP PROCEDURE IF EXISTS registrar_consulta_psicologica_seed;;
CREATE PROCEDURE registrar_consulta_psicologica_seed(
    IN p_cedula_psic VARCHAR(15),
    IN p_cedula_ben VARCHAR(15),
    IN p_nombre_patologia VARCHAR(100),
    IN p_tipo_consulta VARCHAR(50),
    IN p_diagnostico TEXT,
    IN p_tratamiento TEXT,
    IN p_motivo_retiro TEXT,
    IN p_duracion_retiro VARCHAR(50),
    IN p_motivo_cambio VARCHAR(100),
    IN p_observaciones TEXT,
    IN p_fecha DATETIME
)
BEGIN
    DECLARE v_id_psic INT;
    DECLARE v_id_ben INT;
    DECLARE v_id_pat INT;
    DECLARE v_id_det INT;
    DECLARE v_id_sol INT;

    SELECT id_empleado INTO v_id_psic FROM dirpoles_security.empleado WHERE cedula = p_cedula_psic LIMIT 1;
    SELECT id_beneficiario INTO v_id_ben FROM beneficiario WHERE cedula = p_cedula_ben LIMIT 1;
    SELECT id_patologia INTO v_id_pat FROM patologia WHERE nombre_patologia = p_nombre_patologia LIMIT 1;

    IF v_id_pat IS NULL THEN
        SELECT id_patologia INTO v_id_pat FROM patologia WHERE tipo_patologia = 'Psicológica' LIMIT 1;
    END IF;

    -- Solo insertar si no existe ya una consulta para ese beneficiario y fecha exacta
    IF v_id_psic IS NOT NULL AND v_id_ben IS NOT NULL AND NOT EXISTS (
        SELECT 1 FROM consulta_psicologica cp
        JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cp.id_solicitud_serv
        WHERE ss.id_beneficiario = v_id_ben AND cp.fecha_creacion = p_fecha
    ) THEN
        INSERT INTO detalle_patologia (id_patologia) VALUES (v_id_pat);
        SET v_id_det = LAST_INSERT_ID();

        INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
        VALUES (1, v_id_ben, v_id_psic);
        SET v_id_sol = LAST_INSERT_ID();

        INSERT INTO consulta_psicologica (
            id_solicitud_serv, id_detalle_patologia, tipo_consulta,
            diagnostico, tratamiento_gen, motivo_retiro, duracion_retiro,
            motivo_cambio, observaciones, fecha_creacion
        ) VALUES (
            v_id_sol, v_id_det, p_tipo_consulta,
            p_diagnostico, p_tratamiento, p_motivo_retiro, p_duracion_retiro,
            p_motivo_cambio, p_observaciones, p_fecha
        );
    END IF;
END;;
DELIMITER ;

-- =====================================================================
-- 3. INVOCACIÓN DE LAS 60 CONSULTAS PSICOLÓGICAS
-- =====================================================================

-- ---------------------------------------------------------------------
-- COHORTE 2024 (20 Consultas: Enero a Diciembre 2024)
-- ---------------------------------------------------------------------
CALL registrar_consulta_psicologica_seed(
    '12023052', '28114201', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Reorientación vocacional hacia PNF Administración por afinidad con finanzas y gestión de costos.',
    'Batería vocacional de Holland aplicada. Muestra perfil emprendedor compatible con el perfil de egreso.',
    '2024-01-22 09:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '29215302', 'Ansiedad', 'Diagnóstico',
    'Cuadro de ansiedad reactiva caracterizado por rumiaciones obsesivas e insomnio previo a defensas de proyectos.',
    'Terapia cognitivo-conductual (4 sesiones), reestructuración cognitiva y entrenamiento en respiración diafragmática.',
    'No aplica', 'No aplica', 'No aplica',
    'Estudiante receptivo con buen pronóstico de resolución de síntomas.',
    '2024-02-06 10:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '30316403', 'Estrés académico', 'Diagnóstico',
    'Estrés adaptativo moderado asociado a la transición de secundaria a la educación universitaria.',
    'Psicoeducación en higiene del sueño, gestión del tiempo y taller de resolución de problemas.',
    'No aplica', 'No aplica', 'No aplica',
    'Se otorgan herramientas prácticas de organización del estudio.',
    '2024-02-22 14:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '31417504', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Solicitud de cambio vocacional a PNF Informática por marcado interés en programación web.',
    'Test de aptitudes analíticas arroja percentil 85 en razonamiento lógico formal.',
    '2024-03-04 11:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '27518605', 'Fobia social / Ansiedad social', 'Diagnóstico',
    'Temor fóbico e hiperventilación en exposiciones orales ante comités evaluadores.',
    'Desensibilización sistemática in vivo, ensayos conductuales de oratoria y modulación de volumen.',
    'No aplica', 'No aplica', 'No aplica',
    'Evolución positiva tras segunda sesión de modelado conductual.',
    '2024-03-21 09:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '26619706', 'Trastorno de adaptación', 'Retiro temporal',
    'No aplica', 'No aplica',
    'Situación de salud familiar crítica en su municipio foráneo que requiere su atención domiciliaria continua.',
    '1 Semestre', 'No aplica',
    'Se emite informe favorable para avalar la suspensión temporal de matrícula.',
    '2024-04-09 10:45:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '25720807', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Reorientación a PNF Turismo por afinidad a la gestión del patrimonio y relaciones públicas.',
    'Entrevista vocacional favorable. Estudiante comprende la estructura curricular de la especialidad.',
    '2024-04-22 15:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '24821908', 'Agotamiento emocional / Burnout', 'Diagnóstico',
    'Fatiga física y mental severa derivada del cumplimiento simultáneo de empleo fabril nocturno y clases diurnas.',
    'Estrategias de higiene del descanso, jerarquización de prioridades y negociación de descansos breves.',
    'No aplica', 'No aplica', 'No aplica',
    'Se recomienda adecuar la carga crediticia para evitar colapso tensional.',
    '2024-05-06 08:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '23922019', 'Ansiedad', 'Diagnóstico',
    'Sintomatología ansiosa somática (cefaleas tensionales y opresión torácica) previa a exámenes de lapso.',
    'Técnicas de relajación muscular progresiva de Jacobson y pautas de automonitoreo emocional.',
    'No aplica', 'No aplica', 'No aplica',
    'Remitida al dispensario médico para descartar compromiso orgánico. Cita psicológica de seguimiento programada.',
    '2024-05-27 11:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '29023120', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Cambio vocacional a PNF Contaduría Pública por excelente desempeño en cátedras numéricas.',
    'Informe psicotécnico positivo remitido a la comisión de validación de admisiones.',
    '2024-06-11 09:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '30124231', 'Trastorno de adaptación', 'Retiro temporal',
    'No aplica', 'No aplica',
    'Dificultades socioeconómicas severas y necesidad de incorporación a jornada laboral a tiempo completo.',
    '2 Semestres', 'No aplica',
    'Estudiante solicita retiro temporal mientras estabiliza su situación de manutención personal.',
    '2024-06-25 14:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '31225342', 'Sin patología psicológica', 'Diagnóstico',
    'Consulta preventiva de evaluación de rendimiento y técnicas de estudio. Sin patología mental diagnosticada.',
    'Diseño de cronograma académico personalizado y mapa conceptual de metas personales.',
    'No aplica', 'No aplica', 'No aplica',
    'Beneficiario con excelente perfil socioacadémico que busca optimizar su ritmo de estudio.',
    '2024-07-09 10:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '28326453', 'Duelo no complicado', 'Diagnóstico',
    'Reacción de duelo adaptativo tras el fallecimiento de su progenitora con labilidad emocional transitoria.',
    'Psicoterapia breve de apoyo en proceso de duelo, validación de afectos y contención psicosocial.',
    'No aplica', 'No aplica', 'No aplica',
    'Evolución favorable en proceso de elaboración de la pérdida. Mantiene su ritmo de estudio.',
    '2024-07-24 11:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '27427564', 'Estrés académico', 'Diagnóstico',
    'Tensión muscular cervical y preocupación recurrente ante la entrega del tomo final de proyecto.',
    'Técnicas de reencuadre cognitivo, autoinstrucciones de seguridad y pausas activas durante el redactado.',
    'No aplica', 'No aplica', 'No aplica',
    'Notable reducción de los niveles de tensión autoreportados.',
    '2024-08-13 08:45:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '26528675', 'Ansiedad', 'Diagnóstico',
    'Episodio ansioso agudo reactivo a sobrecarga de compromisos evaluativos simultáneos.',
    'Intervención breve focalizada en crisis, respiración diafragmática y planificación semanal de tareas.',
    'No aplica', 'No aplica', 'No aplica',
    'Paciente se retira tranquila y compensada. Se agenda control quincenal voluntario.',
    '2024-08-28 13:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '25629786', 'Depresión leve', 'Diagnóstico',
    'Estado de ánimo decaído, anhedonia leve y desmotivación en actividades académicas habituales.',
    'Activación conductual, reestructuración cognitiva y fortalecimiento de redes de apoyo entre pares.',
    'No aplica', 'No aplica', 'No aplica',
    'Responde satisfactoriamente al plan de tareas graduales.',
    '2024-09-10 10:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '24730897', 'Sin patología psicológica', 'Diagnóstico',
    'Asesoría de control de desempeño para el inicio de prácticas profesionales institucionales.',
    'Recomendaciones de comunicación asertiva, resolución de conflictos y presentación personal.',
    'No aplica', 'No aplica', 'No aplica',
    'Estudiante apto para el desarrollo de actividades en el centro de pasantías asignado.',
    '2024-09-27 09:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '29831908', 'Estrés académico', 'Diagnóstico',
    'Síndrome ansioso leve desencadenado por la presentación de exámenes recuperativos de mitad de trayecto.',
    'Técnicas de visualización positiva, ensayo mental guiado y pausas activas de respiración profunda.',
    'No aplica', 'No aplica', 'No aplica',
    'Disminución del nivel de activación autonómica reportada por el estudiante.',
    '2024-10-15 11:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '27235342', 'Trastorno de adaptación', 'Retiro temporal',
    'No aplica', 'No aplica',
    'Traslado forzoso de residencia familiar a otro estado del país por motivos económicos imprevistos.',
    '1 Año Académico', 'No aplica',
    'Se entrega constancia institucional para tramitar la reserva de cupo reglamentaria.',
    '2024-11-12 14:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '26336453', 'Ansiedad', 'Diagnóstico',
    'Ansiedad de desempeño y temor anticipatorio ante la evaluación oral final de proyecto de grado.',
    'Simulación supervisada de defensa, técnicas de grounding sensorial y manejo de preguntas difíciles.',
    'No aplica', 'No aplica', 'No aplica',
    'Demuestra dominio temático y mayor control expresivo.',
    '2024-12-05 10:30:00'
);

-- ---------------------------------------------------------------------
-- COHORTE 2025 (20 Consultas: Enero a Diciembre 2025)
-- ---------------------------------------------------------------------
CALL registrar_consulta_psicologica_seed(
    '17543826', '29538675', 'Estrés académico', 'Diagnóstico',
    'Tensión adaptativa moderada asociada a la exigencia académica del segundo trayecto formativo.',
    'Taller individual de gestión del tiempo, mapas de metas y técnicas de autocuidado.',
    'No aplica', 'No aplica', 'No aplica',
    'Estudiante motivada y comprometida con el plan de estudio individualizado.',
    '2025-01-20 09:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '30639786', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Reorientación a PNF Higiene y Seguridad Laboral por marcado interés en seguridad industrial.',
    'Batería de intereses profesionales respalda plenamente la afinidad con la nueva especialidad.',
    '2025-02-05 11:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '31740897', 'Fobia social / Ansiedad social', 'Diagnóstico',
    'Inhibición conductual y ansiedad al participar en debates grupales y preguntas en clase.',
    'Entrenamiento en habilidades asertivas, exposición gradual y desensibilización sistemática.',
    'No aplica', 'No aplica', 'No aplica',
    'Reporta avances notables en la interacción espontánea con su grupo de estudio.',
    '2025-02-18 14:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '28841908', 'Ansiedad', 'Diagnóstico',
    'Preocupación constante respecto al rendimiento académico y temor infundado a reprobar.',
    'Reestructuración de creencias disfuncionales y técnicas de respiración diafragmática.',
    'No aplica', 'No aplica', 'No aplica',
    'Paciente logra identificar y sustituir pensamientos catastrofistas con solvencia.',
    '2025-03-11 08:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '27942019', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Cambio vocacional a PNF Administración por interés en la gestión de organizaciones deportivas.',
    'Entrevista vocacional favorable. Informe de reorientación curricular consignado.',
    '2025-03-24 10:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '26043120', 'Trastorno de adaptación', 'Retiro temporal',
    'No aplica', 'No aplica',
    'Proceso de recuperación física y rehabilitación prolongada derivado de traumatismo vehicular.',
    '1 Semestre', 'No aplica',
    'Consigna informes médicos oficiales para validar el retiro temporal por salud.',
    '2025-04-08 13:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '25144231', 'Trastorno de adaptación', 'Retiro temporal',
    'No aplica', 'No aplica',
    'Situación de calamidad doméstica en su núcleo familiar que le impide la asistencia presencial regular.',
    '1 Semestre', 'No aplica',
    'Se elabora acta institucional de suspensión justificada de matrícula.',
    '2025-04-23 09:45:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '29245342', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Reorientación vocacional hacia PNF Agroalimentación por vocación familiar en el sector agrícola.',
    'Pruebas psicotécnicas ratifican perfil técnico y vocacional acorde a las ciencias agrícolas.',
    '2025-05-13 11:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '30346453', 'Depresión leve', 'Diagnóstico',
    'Desánimo moderado y aislamiento social temporal tras fracaso en entrega académica parcial.',
    'Activación conductual programada, búsqueda de soluciones y refuerzo de autoeficacia.',
    'No aplica', 'No aplica', 'No aplica',
    'Notable mejoría en el estado anímico y reactivación de compromisos de evaluación.',
    '2025-05-28 14:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '31447564', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Reorientación hacia PNF Distribución y Logística por interés en el comercio exterior.',
    'Perfil analítico compatible con la administración de cadenas de suministro y distribución.',
    '2025-06-16 08:45:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '28548675', 'Ansiedad', 'Diagnóstico',
    'Manifestaciones ansiosas somáticas en semanas de exámenes finales de lapso.',
    'Entrenamiento en relajación guiada, hábitos de alimentación saludable y descansos programados.',
    'No aplica', 'No aplica', 'No aplica',
    'Se reporta alivio de sintomatología somática tras 3 sesiones de seguimiento.',
    '2025-06-25 10:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '27649786', 'Agotamiento emocional / Burnout', 'Diagnóstico',
    'Agotamiento psíquico por sobreexigencia académica y desbalances en la rutina de sueño.',
    'Técnicas de regulación emocional, priorización de metas y descompresión tensional.',
    'No aplica', 'No aplica', 'No aplica',
    'Estudiante logra reorganizar su semana reservando espacios obligatorios de descanso.',
    '2025-07-15 13:45:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '26750897', 'Estrés académico', 'Diagnóstico',
    'Sobrecarga emocional percibida ante la exigencia simultánea de proyecto y pasantías.',
    'Estrategias de resolución de problemas, delegación de tareas y comunicación asertiva con tutores.',
    'No aplica', 'No aplica', 'No aplica',
    'El beneficiario canaliza adecuadamente sus entregas con un cronograma realista.',
    '2025-07-29 09:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '25851908', 'Duelo no complicado', 'Diagnóstico',
    'Proceso adaptativo de duelo por ruptura afectiva significativa que interfiere transitoriamente en su concentración.',
    'Contención emocional, psicoeducación afectiva y fomento de actividades recreativas saludables.',
    'No aplica', 'No aplica', 'No aplica',
    'Buena capacidad de introspección y compromiso en la superación de la fase aguda.',
    '2025-08-12 11:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '30053120', 'Trastorno de adaptación', 'Retiro temporal',
    'No aplica', 'No aplica',
    'Asignación de contrato laboral a tiempo completo en sector industrial que colisiona con el horario presencial.',
    '1 Semestre', 'No aplica',
    'Solicitud avalada por el departamento para permitir la prosecución posterior.',
    '2025-08-26 14:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '31154231', 'Fobia social / Ansiedad social', 'Diagnóstico',
    'Ansiedad y tartamudez episódica al dirigirse a audiencias de más de 10 personas.',
    'Prácticas de oratoria guiada, control de respiración diafragmática y modelado de conducta asertiva.',
    'No aplica', 'No aplica', 'No aplica',
    'Muestra avances significativos en soltura verbal y contacto visual durante la sesión.',
    '2025-09-09 10:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '28255342', 'Sin patología psicológica', 'Diagnóstico',
    'Evaluación psicológica preventiva de rutina por solicitud de beca de honor académico.',
    'Recomendaciones de alto rendimiento mental y equilibrio en la rutina de estudio.',
    'No aplica', 'No aplica', 'No aplica',
    'Estudiante con sobresaliente estabilidad psíquica y emocional.',
    '2025-09-23 08:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '27356453', 'Ansiedad', 'Diagnóstico',
    'Tensión ansiosa moderada vinculada a la incertidumbre sobre la inserción en el campo laboral.',
    'Terapia breve centrada en soluciones, mapa de fortalezas y preparación de perfil profesional.',
    'No aplica', 'No aplica', 'No aplica',
    'Estudiante recupera la perspectiva y la confianza en sus capacidades técnicas.',
    '2025-10-14 11:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '26457564', 'Estrés académico', 'Diagnóstico',
    'Preocupación constante y cefalea tensional por entregas del proyecto sociointegrador comunitario.',
    'Pautas de ergonomía cognitiva, técnicas de respiración profunda y organización metodológica.',
    'No aplica', 'No aplica', 'No aplica',
    'Se coordina asesoría con el tutor metodológico para clarificar el alcance del trabajo.',
    '2025-11-11 13:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '25558675', 'Sin patología psicológica', 'Diagnóstico',
    'Cierre y evaluación final de acompañamiento psicológico del periodo formativo.',
    'Revisión de logros y consolidación de herramientas psicoeducativas incorporadas.',
    'No aplica', 'No aplica', 'No aplica',
    'Se otorga el alta psicológica formal con excelente pronóstico de autonomía.',
    '2025-12-09 10:00:00'
);

-- ---------------------------------------------------------------------
-- COHORTE 2026 (20 Consultas: Enero al 17 de Noviembre 2026)
-- ---------------------------------------------------------------------
CALL registrar_consulta_psicologica_seed(
    '14872391', '28962019', 'Estrés académico', 'Diagnóstico',
    'Tensión adaptativa moderada asociada al ingreso al trayecto final de la carrera.',
    'Técnicas de planificación estratégica, gestión del tiempo y autoeficacia.',
    'No aplica', 'No aplica', 'No aplica',
    'Muestra determinación para cumplir el plan de actividades propuesto.',
    '2026-01-19 09:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '27063120', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Solicitud de cambio a PNF Contaduría Pública por afinidad comprobada con auditoría financiera.',
    'Evaluación psicotécnica respalda plenamente las competencias analíticas del estudiante.',
    '2026-02-09 11:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '26164231', 'Ansiedad', 'Diagnóstico',
    'Cuadro de ansiedad reactiva por presiones económicas familiares que perturban la concentración académica.',
    'Psicoterapia breve de apoyo, técnicas de grounding y canalización con el área de Trabajo Social.',
    'No aplica', 'No aplica', 'No aplica',
    'Se remite a Trabajo Social para evaluación de beneficio socioeconómico institucional.',
    '2026-02-24 14:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '25265342', 'Trastorno de adaptación', 'Retiro temporal',
    'No aplica', 'No aplica',
    'Enfermedad crónica de progenitor en fase de tratamiento activo que requiere acompañamiento diario.',
    '1 Semestre', 'No aplica',
    'Se consigna constancia médica para formalizar la reserva temporal de cupo.',
    '2026-03-09 08:45:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '29366453', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Reorientación a PNF Deporte por vocación comprobada en entrenamiento atlético institucional.',
    'Batería vocacional confirma alta motivación intrínseca hacia las ciencias del ejercicio.',
    '2026-03-23 10:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '30467564', 'Agotamiento emocional / Burnout', 'Diagnóstico',
    'Fatiga extrema por sobrecarga de actividades laborales y preparación de defensas de grado.',
    'Estrategias de higiene del descanso, delegación de responsabilidades y reestructuración de metas.',
    'No aplica', 'No aplica', 'No aplica',
    'El beneficiario reduce la jornada externa y reporta franca recuperación del rendimiento.',
    '2026-04-14 13:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '31568675', 'Trastorno de adaptación', 'Retiro temporal',
    'No aplica', 'No aplica',
    'Embarazo de alto riesgo que demanda reposo médico absoluto en su segundo trimestre.',
    '2 Semestres', 'No aplica',
    'Informe obstétrico validado. Se emite acta de retiro temporal por maternidad.',
    '2026-04-28 09:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '28669786', 'Ansiedad', 'Diagnóstico',
    'Ansiedad anticipatoria recurrente ante la inminencia de las primeras evaluaciones parciales.',
    'Terapia cognitivo-conductual, desensibilización sistemática y autorregistros de pensamiento.',
    'No aplica', 'No aplica', 'No aplica',
    'Evolución clínica satisfactoria. Disminución de los picos de angustia en el aula.',
    '2026-05-12 11:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '27770897', 'Fobia social / Ansiedad social', 'Diagnóstico',
    'Dificultad marcada para formular preguntas en clase y participar en dinámicas grupales.',
    'Entrenamiento en habilidades sociales asertivas, ensayo conductual y contacto visual progresivo.',
    'No aplica', 'No aplica', 'No aplica',
    'Se observan progresos palpables en la soltura comunicacional del estudiante.',
    '2026-05-26 14:45:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '26871908', 'Sin patología psicológica', 'Cambio de carrera',
    'No aplica', 'No aplica', 'No aplica', 'No aplica',
    'Reorientación vocacional a PNF Procesos Químicos por afinidad hacia análisis fisicoquímicos.',
    'Prueba vocacional de Kuder ratifica interés prioritario en el área científica y de laboratorio.',
    '2026-06-09 08:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '25972019', 'Trastorno de adaptación', 'Retiro temporal',
    'No aplica', 'No aplica',
    'Intervención quirúrgica programada de columna y rehabilitación post-operatoria que exige reposo domiciliario.',
    '1 Semestre', 'No aplica',
    'Se avala la suspensión temporal justificada de la escolaridad regular.',
    '2026-06-23 10:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '29073120', 'Estrés académico', 'Diagnóstico',
    'Tensión muscular e insomnio leve por acumulación de entregas metodológicas del proyecto comunitario.',
    'Entrenamiento en relajación diafragmática profunda y pausas ergonómicas regulares.',
    'No aplica', 'No aplica', 'No aplica',
    'Notable mejoría en el patrón de descanso nocturno reportado por el beneficiario.',
    '2026-07-14 13:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '30174231', 'Duelo no complicado', 'Diagnóstico',
    'Proceso adaptativo de duelo por la pérdida de un familiar cercano con labilidad afectiva transitoria.',
    'Acompañamiento psicológico respetuoso, validación emocional y canalización de afectos.',
    'No aplica', 'No aplica', 'No aplica',
    'Avance progresivo en la reorganización emocional. Estudiante mantiene su asistencia a clases.',
    '2026-07-28 09:45:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '31275342', 'Sin patología psicológica', 'Diagnóstico',
    'Consulta preventiva de valoración vocacional para elección de mención de grado.',
    'Asesoría técnica sobre los perfiles de egreso y campos de inserción ocupacional.',
    'No aplica', 'No aplica', 'No aplica',
    'Estudiante aclara su vocación y decide continuar con firmeza en su especialidad.',
    '2026-08-11 11:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '28376453', 'Ansiedad', 'Diagnóstico',
    'Sintomatología ansiosa moderada derivada de conflictos de convivencia en equipo de proyecto.',
    'Intervención breve orientada a la resolución asertiva de conflictos y comunicación no violenta.',
    'No aplica', 'No aplica', 'No aplica',
    'Logro de acuerdos de convivencia en el equipo de proyecto con mediación metodológica.',
    '2026-08-25 14:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '27477564', 'Depresión leve', 'Diagnóstico',
    'Apatía académica transitoria y desmotivación en asignaturas teóricas del trayecto.',
    'Activación conductual gradual, mapa de metas a corto plazo y refuerzo de logros pasados.',
    'No aplica', 'No aplica', 'No aplica',
    'Reactivación positiva de la participación en las actividades de aula.',
    '2026-09-08 08:45:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '26578675', 'Agotamiento emocional / Burnout', 'Diagnóstico',
    'Fatiga física y mental severa vinculada a la preparación simultánea de pasantías y carga académica.',
    'Reestructuración de horarios, eliminación de actividades no esenciales e higiene del sueño.',
    'No aplica', 'No aplica', 'No aplica',
    'Estudiante reporta mayor vitalidad tras dos semanas de implementación de las pautas.',
    '2026-09-22 10:30:00'
);

CALL registrar_consulta_psicologica_seed(
    '12023052', '29780897', 'Estrés académico', 'Diagnóstico',
    'Preocupación constante ante el inicio de las defensas públicas de los proyectos comunitarios.',
    'Ensayo conductual guiado, técnicas de autoinstrucción positiva y modulación vocal.',
    'No aplica', 'No aplica', 'No aplica',
    'Beneficiaria adquiere seguridad y dominio de su discurso de presentación.',
    '2026-10-13 13:15:00'
);

CALL registrar_consulta_psicologica_seed(
    '14872391', '30881908', 'Ansiedad', 'Diagnóstico',
    'Episodio ansioso agudo generado por sobrecarga de compromisos académicos de fin de trayecto.',
    'Psicoterapia breve de apoyo, ejercicios de grounding y planificación semanal.',
    'No aplica', 'No aplica', 'No aplica',
    'Paciente se retira tranquilo y fortalecido tras la sesión de contención.',
    '2026-11-05 09:00:00'
);

CALL registrar_consulta_psicologica_seed(
    '17543826', '31982019', 'Sin patología psicológica', 'Diagnóstico',
    'Consulta de cierre y evaluación final de orientación psicológica y desempeño.',
    'Revisión final de metas consolidadas y pautas de afrontamiento para el nuevo trayecto.',
    'No aplica', 'No aplica', 'No aplica',
    'Cierre de caso formal por cumplimiento exitoso de todos los objetivos trazados.',
    '2026-11-17 10:45:00'
);

-- =====================================================================
-- 4. LIMPIEZA DEL PROCEDIMIENTO TEMPORAL
-- =====================================================================
DROP PROCEDURE IF EXISTS registrar_consulta_psicologica_seed;

-- =====================================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================================
-- 1) Total de consultas psicológicas registradas:
--    SELECT COUNT(*) AS total_consultas_psicologia FROM consulta_psicologica;
--
-- 2) Distribución por tipo de consulta:
--    SELECT tipo_consulta, COUNT(*) AS total
--      FROM consulta_psicologica
--     GROUP BY tipo_consulta;
--
-- 3) Consultas por especialista en psicología:
--    SELECT CONCAT(e.nombre, ' ', e.apellido) AS psicologo, COUNT(*) AS total
--      FROM consulta_psicologica cp
--      JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cp.id_solicitud_serv
--      JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
--     GROUP BY psicologo;
--
-- 4) Distribución cronológica por año:
--    SELECT YEAR(fecha_creacion) AS anio, COUNT(*) AS total
--      FROM consulta_psicologica
--     GROUP BY anio ORDER BY anio;
-- =====================================================================
