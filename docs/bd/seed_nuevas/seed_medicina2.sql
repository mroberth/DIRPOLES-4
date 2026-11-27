-- =====================================================================
-- SEED DE CONSULTAS MÉDICAS (TANDA 2) — 60 registros sintéticos
-- Ubicación: docs/bd/seed_nuevas/seed_medicina2.sql
-- Módulo: Consultas de Medicina General (id_modulo 5, id_servicios = 2)
--
-- Cobertura Temporal: 23/01/2024 al 17/11/2026 (Ideal para microservicio IA)
-- Tablas involucradas: patologia, detalle_patologia, solicitud_de_servicio,
--                      consulta_medica, detalle_insumo
--
-- Distribución temporal:
--   * Cohorte 2024: 20 consultas médicas (enero a noviembre 2024)
--   * Cohorte 2025: 20 consultas médicas (enero a noviembre 2025)
--   * Cohorte 2026: 20 consultas médicas (enero al 17 de noviembre 2026)
--
-- Especialistas (Médicos en dirpoles_security.empleado):
--   * Dr. Carlos Eduardo Sánchez Bracamonte (Cédula: 19632847) - 30 consultas
--   * Dra. Yuri Alexandra Boscán Anzola     (Cédula: 16754209) - 30 consultas
--
-- Patologías Médicas tratadas:
--   * Cefalea tensional (Fatiga visual, estrés académico, astenopia)
--   * Gripe / Rinofaringitis viral aguda
--   * Gastritis aguda / Reflujo gastroesofágico
--   * Faringoamigdalitis (Bacteriana supurativa y viral congestiva)
--   * Esguince de tobillo leve (Grado I en canchas y talleres)
--   * Lumbalgia mecánica / Contractura paravertebral
--   * Asma bronquial / Broncoespasmo reactivo
--   * Gastroenteritis aguda viral / Diarrea no complicada
--   * Rinitis alérgica estacional
--   * Dermatitis por contacto irritativa / química
--   * Infección urinaria baja no complicada
--   * Hipertensión arterial leve en estudio
--   * Sin patología médica (Chequeos preventivos, certificaciones, pasantías)
--
-- Consumo de Insumos Médicos (detalle_insumo):
--   * ~55% de consultas dispensan medicamentos o material médico:
--     Acetaminofén 500mg, Ibuprofeno 400mg, Omeprazol 20mg, Amoxicilina 500mg,
--     Vendas Elásticas 10cm, Solución Fisiológica 0.9%, Gasas Estériles 7.5x7.5cm.
--
-- 100% IDEMPOTENTE: omite registros si ya existen para el beneficiario y fecha.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_spanish2_ci;

-- =====================================================================
-- 1. ASEGURAR PATOLOGÍAS MÉDICAS COMUNES EN EL CATÁLOGO
-- =====================================================================
INSERT INTO patologia (nombre_patologia, tipo_patologia, fecha_creacion)
SELECT v.nombre, v.tipo, v.fecha
FROM (
    SELECT 'Hipertensión arterial leve' AS nombre, 'Médica' AS tipo, '2024-01-10' AS fecha
    UNION ALL SELECT 'Lumbalgia mecánica', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Dermatitis por contacto', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Infección urinaria no complicada', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Rinitis alérgica', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Esguince de tobillo leve', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Gastroenteritis aguda viral', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Cefalea tensional', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Gastritis aguda', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Faringoamigdalitis', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Asma bronquial', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Sin patología médica', 'Médica', '2024-01-10'
    UNION ALL SELECT 'Gripe', 'General', '2024-01-10'
) v
WHERE NOT EXISTS (
    SELECT 1 FROM patologia p WHERE p.nombre_patologia = v.nombre
);

-- =====================================================================
-- 2. PROCEDIMIENTO TEMPORAL DE CARGA TRANSACCIONAL E IDEMPOTENTE
-- =====================================================================
DELIMITER ;;
DROP PROCEDURE IF EXISTS registrar_consulta_medica_seed;;
CREATE PROCEDURE registrar_consulta_medica_seed(
    IN p_cedula_med VARCHAR(15),
    IN p_cedula_ben VARCHAR(15),
    IN p_nombre_patologia VARCHAR(100),
    IN p_estatura DECIMAL(4,2),
    IN p_peso DECIMAL(4,2),
    IN p_tipo_sangre ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-'),
    IN p_motivo_visita VARCHAR(255),
    IN p_diagnostico VARCHAR(255),
    IN p_tratamiento VARCHAR(255),
    IN p_observaciones VARCHAR(255),
    IN p_fecha_creacion DATE,
    IN p_nombre_insumo1 VARCHAR(100),
    IN p_cant_insumo1 VARCHAR(100),
    IN p_nombre_insumo2 VARCHAR(100),
    IN p_cant_insumo2 VARCHAR(100)
)
BEGIN
    DECLARE v_id_med INT;
    DECLARE v_id_ben INT;
    DECLARE v_id_pat INT;
    DECLARE v_id_ins1 INT;
    DECLARE v_id_ins2 INT;
    DECLARE v_id_det_pat INT;
    DECLARE v_id_sol INT;
    DECLARE v_id_con INT;

    -- 1. Resolver médico
    SELECT id_empleado INTO v_id_med
    FROM dirpoles_security.empleado
    WHERE cedula = p_cedula_med COLLATE utf8mb4_spanish2_ci LIMIT 1;

    -- 2. Resolver beneficiario
    SELECT id_beneficiario INTO v_id_ben
    FROM beneficiario
    WHERE cedula = p_cedula_ben COLLATE utf8mb4_spanish2_ci LIMIT 1;

    -- 3. Resolver patología
    SELECT id_patologia INTO v_id_pat
    FROM patologia
    WHERE nombre_patologia = p_nombre_patologia COLLATE utf8mb4_spanish2_ci LIMIT 1;

    IF v_id_pat IS NULL THEN
        SELECT id_patologia INTO v_id_pat FROM patologia WHERE nombre_patologia = 'Sin patología médica' LIMIT 1;
    END IF;

    -- Idempotencia: Verificar que médico y beneficiario existan, y que no exista consulta en esa fecha
    IF v_id_med IS NOT NULL AND v_id_ben IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM consulta_medica cm
            JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cm.id_solicitud_serv
            WHERE ss.id_beneficiario = v_id_ben
              AND cm.fecha_creacion = p_fecha_creacion
        ) THEN
            -- A) detalle_patologia
            INSERT INTO detalle_patologia (id_patologia) VALUES (v_id_pat);
            SET v_id_det_pat = LAST_INSERT_ID();

            -- B) solicitud_de_servicio (id_servicios = 2 para Medicina)
            INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
            VALUES (2, v_id_ben, v_id_med);
            SET v_id_sol = LAST_INSERT_ID();

            -- C) consulta_medica
            INSERT INTO consulta_medica (
                id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre,
                motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion
            ) VALUES (
                v_id_det_pat, v_id_sol, p_estatura, p_peso, p_tipo_sangre,
                p_motivo_visita, p_diagnostico, p_tratamiento, p_observaciones, p_fecha_creacion
            );
            SET v_id_con = LAST_INSERT_ID();

            -- D) detalle_insumo 1 (opcional)
            IF p_nombre_insumo1 IS NOT NULL AND p_cant_insumo1 IS NOT NULL THEN
                SELECT id_insumo INTO v_id_ins1
                FROM insumos
                WHERE nombre_insumo LIKE CONCAT('%', p_nombre_insumo1 COLLATE utf8mb4_spanish2_ci, '%') LIMIT 1;

                IF v_id_ins1 IS NOT NULL THEN
                    INSERT INTO detalle_insumo (id_consulta_med, id_insumo, cantidad_usada)
                    VALUES (v_id_con, v_id_ins1, p_cant_insumo1);
                END IF;
            END IF;

            -- E) detalle_insumo 2 (opcional)
            IF p_nombre_insumo2 IS NOT NULL AND p_cant_insumo2 IS NOT NULL THEN
                SELECT id_insumo INTO v_id_ins2
                FROM insumos
                WHERE nombre_insumo LIKE CONCAT('%', p_nombre_insumo2 COLLATE utf8mb4_spanish2_ci, '%') LIMIT 1;

                IF v_id_ins2 IS NOT NULL THEN
                    INSERT INTO detalle_insumo (id_consulta_med, id_insumo, cantidad_usada)
                    VALUES (v_id_con, v_id_ins2, p_cant_insumo2);
                END IF;
            END IF;
        END IF;
    END IF;
END;;
DELIMITER ;

-- =====================================================================
-- 3. INSERTAR 60 CONSULTAS MÉDICAS SINTÉTICAS
-- =====================================================================

-- ---------------------------------------------------------------------
-- COHORTE 2024 (20 Consultas: Enero a Noviembre 2024)
-- ---------------------------------------------------------------------

-- Consulta 1: Cefalea tensional (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '28114201', 'Cefalea tensional', 1.63, 56.40, 'O+',
    'Cefalea frontal pulsátil de 48 horas de evolución tras semana de estudio continuo.',
    'Cefalea tensional por fatiga visual y estrés académico.',
    'Acetaminofén 500mg V.O. c/8h por 3 días, hidratación y reposo visual.',
    'TA: 115/75 mmHg, FC: 72 bpm. Se recomienda reducir exposición a pantallas.',
    '2024-01-23', 'Acetaminofén 500mg', '2', NULL, NULL
);

-- Consulta 2: Gripe (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '29215302', 'Gripe', 1.76, 71.00, 'A+',
    'Rinorrea hialina, odinofagia leve, estornudos frecuentes y febrícula no cuantificada.',
    'Síndrome viral respiratorio agudo / Rinofaringitis.',
    'Acetaminofén 500mg V.O. c/8h por 3 días, abundantes líquidos y reposo por 48h.',
    'Faringe congestiva sin placas exudativas. Constancia médica emitida por 48 horas.',
    '2024-02-07', 'Acetaminofén 500mg', '2', NULL, NULL
);

-- Consulta 3: Gastritis aguda (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '30316403', 'Gastritis aguda', 1.58, 51.20, 'B+',
    'Epigastralgia urente matutina agudizada tras ingesta de café sin desayuno previo.',
    'Gastritis aguda irritativa vinculada a hábitos dietéticos irregulares.',
    'Omeprazol 20mg V.O. en ayunas por 14 días. Dieta blanda sin condimentos ni café.',
    'Abdomen blando, doloroso a la palpación en epigastrio sin signos de peritonismo.',
    '2024-02-20', 'Omeprazol 20mg', '1', NULL, NULL
);

-- Consulta 4: Sin patología médica (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '31417504', 'Sin patología médica', 1.81, 79.50, 'O+',
    'Evaluación médica preventiva y certificación física para ingreso a talleres técnicos.',
    'Paciente clínicamente sano. Examen cardiopulmonar dentro de límites normales.',
    'No amerita medicación. Se refuerzan hábitos de actividad física y nutrición.',
    'TA: 120/80 mmHg, FC: 68 bpm, IMC: 24.2. Certificado médico emitido satisfactoriamente.',
    '2024-03-05', NULL, NULL, NULL, NULL
);

-- Consulta 5: Esguince de tobillo leve (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '27518605', 'Esguince de tobillo leve', 1.65, 58.00, 'O+',
    'Dolor e inflamación en tobillo derecho tras torsión involuntaria en cancha deportiva.',
    'Esguince de tobillo derecho Grado I (ligamento peroneoastragalino anterior).',
    'Inmovilización con vendaje elástico, reposo relativo, crioterapia local y analgésico.',
    'Sin deformidad ósea. Maniobras de bostezo articular negativas. Vendaje compresivo colocado.',
    '2024-03-19', 'Vendas Elásticas 10cm', '1', 'Ibuprofeno 400mg', '1'
);

-- Consulta 6: Faringoamigdalitis (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '26619706', 'Faringoamigdalitis', 1.72, 67.80, 'A-',
    'Odinofagia intensa de 3 días de evolución, dolor al tragar y fiebre termometrada en 38.5°C.',
    'Faringoamigdalitis bacteriana aguda con exudado amigdalino bilateral.',
    'Amoxicilina 500mg V.O. c/8h por 7 días, Ibuprofeno 400mg c/8h e hidratación.',
    'Amígdalas hipertróficas grado II eritematosas con focos purulentos. Reposo de 72 horas.',
    '2024-04-10', 'Amoxicilina 500mg', '1', 'Ibuprofeno 400mg', '1'
);

-- Consulta 7: Lumbalgia mecánica (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '25720807', 'Lumbalgia mecánica', 1.60, 54.00, 'B-',
    'Dolor punzante en región lumbar baja tras cargar cajas pesadas en proyecto comunitario.',
    'Lumbalgia mecánica postural y contractura muscular paravertebral.',
    'Ibuprofeno 400mg V.O. c/8h por 4 días, calor local seco y evitar cargas pesadas.',
    'Maniobra de Lasègue negativa. Contractura bilateral paravertebral L3-L5 evidente.',
    '2024-04-24', 'Ibuprofeno 400mg', '1', NULL, NULL
);

-- Consulta 8: Asma bronquial (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '24821908', 'Asma bronquial', 1.74, 73.20, 'O+',
    'Dificultad respiratoria leve, tos seca nocturna y sibilancias desencadenadas por frío.',
    'Broncoespasmo reactivo leve / Asma bronquial intermitente.',
    'Salbutamol inhalador 2 disparos c/8h SOS, reposo relativo en ambiente ventilado.',
    'Murmullo vesicular audible con sibilancias espiratorias bilaterales dispersas. SatO2: 97%.',
    '2024-05-08', NULL, NULL, NULL, NULL
);

-- Consulta 9: Gastroenteritis aguda viral (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '23922019', 'Gastroenteritis aguda viral', 1.62, 53.50, 'A+',
    'Cuadro de náuseas, dolor cólico abdominal difuso y 3 deposiciones líquidas sin moco ni sangre.',
    'Síndrome diarreico agudo de probable etiología viral / Gastroenteritis leve.',
    'Sales de rehidratación oral a libre demanda, dieta astringente y reposo por 48h.',
    'Abdomen blando, depresible, ruidos hidroaéreos aumentados. Sin signos de deshidratación severa.',
    '2024-05-22', 'Solución Fisiológica 0.9%', '1', NULL, NULL
);

-- Consulta 10: Dermatitis por contacto (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '29023120', 'Dermatitis por contacto', 1.77, 74.00, 'O+',
    'Prurito intenso y eritema micropapular en dorso de ambas manos tras uso de solventes de limpieza.',
    'Dermatitis por contacto de tipo irritativo en miembros superiores.',
    'Lavado suave con solución jabonosa neutra, crema emoliente hidratante y evitar contacto con solventes.',
    'Lesiones eritematosas no exudativas delimitadas a zona de contacto. Se indica uso de guantes de nitrilo.',
    '2024-06-12', 'Alcohol Antiséptico 70%', '1', 'Gasas Estériles 7.5x7.5cm', '1'
);

-- Consulta 11: Cefalea tensional (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '30124231', 'Cefalea tensional', 1.67, 59.80, 'AB+',
    'Cefalea opresiva en banda biparietal posterior a evaluaciones finales de trimestre.',
    'Cefalea tensional moderada por sobrecarga académica y desvelo.',
    'Acetaminofén 500mg V.O. c/8h según dolor, higiene del sueño y pausas activas.',
    'Exploración neurológica normal. Tensión arterial 110/70 mmHg. Remitida a charla de manejo de estrés.',
    '2024-06-25', 'Acetaminofén 500mg', '1', NULL, NULL
);

-- Consulta 12: Infección urinaria no complicada (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '31225342', 'Infección urinaria no complicada', 1.70, 65.50, 'O-',
    'Disuria terminal, polaquiuria y leve molestia suprapúbica de 48 horas de evolución.',
    'Infección de vías urinarias bajas no complicada (Cistitis aguda).',
    'Amoxicilina 500mg V.O. c/8h por 5 días, ingesta abundante de agua (2 a 3 litros/día).',
    'Puñopercusión lumbar negativa bilateral. Se solicita urocultivo de control post-tratamiento.',
    '2024-07-10', 'Amoxicilina 500mg', '1', NULL, NULL
);

-- Consulta 13: Sin patología médica (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '28326453', 'Sin patología médica', 1.64, 55.00, 'A+',
    'Chequeo médico general preventivo y actualización de ficha clínica estudiantil.',
    'Beneficiaria asintomática con signos vitales en rango fisiológico.',
    'Hábitos saludables de hidratación y alimentación equilibrada.',
    'TA: 118/76 mmHg, FC: 70 bpm, FR: 16 rpm. Ficha médica actualizada en sistema.',
    '2024-07-23', NULL, NULL, NULL, NULL
);

-- Consulta 14: Gripe (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '27427564', 'Gripe', 1.78, 76.50, 'B+',
    'Congestión nasal, carraspera faríngea, estornudos matutinos y malestar corporal difuso.',
    'Rinofaringitis catarral aguda de etiología viral.',
    'Acetaminofén 500mg c/8h, infusiones tibias, consumo de cítricos y reposo relativo.',
    'Coriza serosa, mucosa nasal eritematosa. Paciente en condiciones generales estables.',
    '2024-08-06', 'Acetaminofén 500mg', '2', NULL, NULL
);

-- Consulta 15: Gastritis aguda (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '26528675', 'Gastritis aguda', 1.61, 52.80, 'O+',
    'Ardor retroesternal y pirosis tras ingesta copiosa de alimentos grasos y refrescos.',
    'Reflujo gastroesofágico leve / Gastritis irritativa postprandial.',
    'Omeprazol 20mg V.O. c/24h en ayunas por 10 días, evitar acostarse tras comer.',
    'Epigastrio ligeramente sensible. Recomendaciones de fraccionamiento de comidas.',
    '2024-08-20', 'Omeprazol 20mg', '1', NULL, NULL
);

-- Consulta 16: Rinitis alérgica (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '25629786', 'Rinitis alérgica', 1.83, 82.00, 'A-',
    'Prurito nasal constante, lagrimeo ocular y crisis de estornudos al estar en archivos de biblioteca.',
    'Rinitis alérgica perenne exacerbada por aeroalérgenos (polvo y ácaros).',
    'Lavados nasales con solución fisiológica tibia, antihistamínico y uso de mascarilla en depósitos.',
    'Mucosa nasal pálida y edematosa. Cornetes hipertróficos. Mejoría inmediata tras aseo nasal.',
    '2024-09-11', 'Solución Fisiológica 0.9%', '1', NULL, NULL
);

-- Consulta 17: Sin patología médica (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '24730897', 'Sin patología médica', 1.69, 61.20, 'O+',
    'Certificación médica requerida para participación en juegos deportivos interfacultades.',
    'Aptitud física y cardiovascular óptima para práctica deportiva de alto rendimiento.',
    'Mantener entrenamiento dosificado y pautas de hidratación isotónica.',
    'FC basal: 60 bpm, TA: 110/70 mmHg. Auscultación cardíaca sin soplos. Certificado avalado.',
    '2024-09-24', NULL, NULL, NULL, NULL
);

-- Consulta 18: Faringoamigdalitis (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '29831908', 'Faringoamigdalitis', 1.75, 70.00, 'B+',
    'Dolor faríngeo intenso que irradia a ambos oídos, cefalea y decaimiento general.',
    'Faringoamigdalitis eritematopultácea aguda.',
    'Amoxicilina 500mg c/8h por 7 días, gárgaras con agua tibia y sal, e Ibuprofeno 400mg c/8h.',
    'Cadenas ganglionares cervicales anteriores dolorosas a la palpación. Control en 5 días.',
    '2024-10-15', 'Amoxicilina 500mg', '1', 'Ibuprofeno 400mg', '1'
);

-- Consulta 19: Cefalea tensional (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '30932019', 'Cefalea tensional', 1.59, 50.50, 'O+',
    'Dolor en región occipital que asciende al vértex, acompañado de contractura de cuello y hombros.',
    'Cefalea miotensiva de origen postural cervical.',
    'Ibuprofeno 400mg V.O. c/8h por 3 días, compresas húmedo-calientes en trapecio y estiramientos.',
    'Punto gatillo activo en músculo trapecio superior derecho. Se indican pautas ergonómicas de silla.',
    '2024-11-06', 'Ibuprofeno 400mg', '1', NULL, NULL
);

-- Consulta 20: Hipertensión arterial leve (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '31033120', 'Hipertensión arterial leve', 1.73, 77.00, 'A+',
    'Mareo leve, visión borrosa episódica y pesadez de nuca tras discusión acalorada.',
    'Cifras tensionales elevadas reactivas / Descarte de Hipertensión Arterial Grado I.',
    'Reposo clínico en decúbito supino por 30 minutos, restricción de sodio y control ambulatorio de TA.',
    'TA inicial: 145/95 mmHg; TA post-reposo: 130/85 mmHg. Se indica diario de tensión por 7 días.',
    '2024-11-27', NULL, NULL, NULL, NULL
);

-- ---------------------------------------------------------------------
-- COHORTE 2025 (20 Consultas: Enero a Noviembre 2025)
-- ---------------------------------------------------------------------

-- Consulta 21: Gastritis aguda (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '28134231', 'Gastritis aguda', 1.63, 55.40, 'O+',
    'Sensación de quemazón epigástrica y plenitud postprandial precoz desde hace 1 semana.',
    'Gastritis aguda leve secundaria a estrés y consumo recurrente de analgésicos.',
    'Omeprazol 20mg en ayunas por 14 días. Dieta fraccionada y suspender AINEs.',
    'Sin náuseas ni vómitos. Abdomen depresible. Se indica acudir a control nutricional.',
    '2025-01-15', 'Omeprazol 20mg', '1', NULL, NULL
);

-- Consulta 22: Gripe (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '27235342', 'Gripe', 1.79, 75.00, 'B+',
    'Malestar osteomuscular, rinorrea mucosa, tos productiva incipiente y escalofríos.',
    'Cuadro gripal estacional no complicado.',
    'Acetaminofén 500mg c/8h por 3 días, reposo médico por 48 horas e hidratación con sueros orales.',
    'Faringe congestiva difusa. Auscultación respiratoria limpia. Reposo consignado.',
    '2025-01-29', 'Acetaminofén 500mg', '2', NULL, NULL
);

-- Consulta 23: Sin patología médica (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '26336453', 'Sin patología médica', 1.66, 58.00, 'A+',
    'Chequeo médico anual de rutina y control de laboratorio básico preventivo.',
    'Examen clínico general sin hallazgos patológicos. Parámetros somatométricos normales.',
    'Continuar con dieta balanceada rica en frutas y verduras, e ingesta hídrica óptima.',
    'TA: 115/75 mmHg, Peso estable, FC: 72 bpm. Certificado de control anual emitido.',
    '2025-02-12', NULL, NULL, NULL, NULL
);

-- Consulta 24: Lumbalgia mecánica (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '25437564', 'Lumbalgia mecánica', 1.82, 84.50, 'O-',
    'Dolor agudo lumbar tras maniobras de carga en laboratorios de química industrial.',
    'Lumbalgia aguda mecánica por sobreesfuerzo físico osteomuscular.',
    'Ibuprofeno 400mg V.O. c/8h por 5 días, reposo relativo y aplicación de frío/calor local alternado.',
    'Rango de flexión anterior del tronco limitado por contractura paravertebral.',
    '2025-02-26', 'Ibuprofeno 400mg', '1', NULL, NULL
);

-- Consulta 25: Cefalea tensional (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '29538675', 'Cefalea tensional', 1.62, 53.00, 'O+',
    'Cefalea holocraneal opresiva diaria de intensidad 6/10 agravada en las tardes.',
    'Cefalea de origen tensional y fatiga postural visual prolongada.',
    'Acetaminofén 500mg c/8h según requerimiento, pausas de descanso ocular c/45 min.',
    'TA: 110/70 mmHg. Fondo de ojo preliminar normal. Se remite a evaluación optométrica.',
    '2025-03-12', 'Acetaminofén 500mg', '1', NULL, NULL
);

-- Consulta 26: Esguince de tobillo leve (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '30639786', 'Esguince de tobillo leve', 1.75, 70.50, 'A+',
    'Torcedura de tobillo izquierdo al bajar escaleras en módulo universitario, con edema leve.',
    'Esguince de tobillo izquierdo Grado I sin compromiso óseo ni ligamentario mayor.',
    'Colocación de vendaje funcional elástico, crioterapia 15 min c/4h y analgésicos.',
    'Apoyo podálico doloroso pero posible. Se coloca vendaje elástico en espiga en el dispensario.',
    '2025-03-25', 'Vendas Elásticas 10cm', '1', 'Ibuprofeno 400mg', '1'
);

-- Consulta 27: Faringoamigdalitis (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '31740897', 'Faringoamigdalitis', 1.57, 49.80, 'B-',
    'Fiebre de 38°C, dolor de garganta intenso que dificulta la deglución y adenopatías submandibulares.',
    'Faringoamigdalitis aguda supurativa.',
    'Amoxicilina 500mg c/8h por 7 días, gárgaras tibias y Acetaminofén 500mg c/8h por 3 días.',
    'Exudado purulento pultáceo visible en ambas amígdalas. Reposo médico de 72 horas.',
    '2025-04-09', 'Amoxicilina 500mg', '1', 'Acetaminofén 500mg', '1'
);

-- Consulta 28: Asma bronquial (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '28841908', 'Asma bronquial', 1.80, 78.00, 'O+',
    'Opresión torácica, respiración entrecortada y tos seca tras exposición al polvo en taller.',
    'Crisis asmática leve desencadenada por irritantes inhalados.',
    'Inhaloterapia de rescate con broncodilatador, hidratación y reposo en unidad médica 45 min.',
    'Sibilancias espiratorias bilaterales que ceden tras inhalación. SatO2 recuperada a 98%.',
    '2025-04-23', NULL, NULL, NULL, NULL
);

-- Consulta 29: Sin patología médica (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '27942019', 'Sin patología médica', 1.68, 62.00, 'AB+',
    'Chequeo médico deportivo precompetitivo para torneo universitario de atletismo.',
    'Beneficiaria atlética sin contraindicaciones médicas para deporte de alta exigencia.',
    'Continuar plan de entrenamiento regular y nutrición deportiva.',
    'FC: 58 bpm, TA: 112/72 mmHg. Excelente acondicionamiento físico. Aval deportivo emitido.',
    '2025-05-14', NULL, NULL, NULL, NULL
);

-- Consulta 30: Gastroenteritis aguda viral (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '26043120', 'Gastroenteritis aguda viral', 1.74, 72.80, 'A-',
    'Náuseas matutinas, dolor tipo retortijón periumbilical y diarrea líquida de 24h de evolución.',
    'Gastroenteritis aguda de origen infeccioso viral sin deshidratación severa.',
    'Solución de hidratación oral fraccionada, dieta astringente y reposo en casa por 48h.',
    'Mucosas orales húmedas, abdomen doloroso difuso a la palpación profunda. Constancia médica.',
    '2025-05-27', 'Solución Fisiológica 0.9%', '1', NULL, NULL
);

-- Consulta 31: Dermatitis por contacto (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '25144231', 'Dermatitis por contacto', 1.60, 54.20, 'O+',
    'Brote pruriginoso con pápulas eritematosas en cuello tras uso de accesorio metálico de fantasía.',
    'Dermatitis por contacto alérgica (sensibilidad probable a níquel).',
    'Retirar causante, aplicar compresas frías con gasa estéril y crema tópica emoliente.',
    'Placas eritematosas sin sobreinfección bacteriana. Lesiones no exudativas.',
    '2025-06-11', 'Gasas Estériles 7.5x7.5cm', '1', NULL, NULL
);

-- Consulta 32: Rinitis alérgica (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '29245342', 'Rinitis alérgica', 1.73, 68.00, 'B+',
    'Estornudos en salva matutinos, rinorrea hialina profusa y comezón ocular persistente.',
    'Rinitis alérgica estacional reactiva a polen y cambios de temperatura.',
    'Lavados con solución salina c/12h, antihistamínico oral nocturno e hidratación.',
    'Ojos con inyección conjuntival leve bilateral. Mucosa nasal pálida.',
    '2025-06-24', 'Solución Fisiológica 0.9%', '1', NULL, NULL
);

-- Consulta 33: Infección urinaria no complicada (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '30346453', 'Infección urinaria no complicada', 1.64, 56.50, 'O+',
    'Ardor miccional acentuado, tenesmo vesical y aumento en la frecuencia miccional.',
    'Cistitis bacteriana aguda / Infección urinaria no complicada.',
    'Amoxicilina 500mg V.O. c/8h por 7 días, abundantes líquidos y normas higiénicas.',
    'Sin fiebre ni puñopercusión lumbar. Se orienta no demorar la evacuación vesical.',
    '2025-07-09', 'Amoxicilina 500mg', '1', NULL, NULL
);

-- Consulta 34: Gripe (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '31447564', 'Gripe', 1.81, 80.00, 'A+',
    'Escalofríos, tos no productiva, congestión nasal y cefalea leve de 2 días de evolución.',
    'Infección respiratoria alta de origen viral / Síndrome gripal.',
    'Acetaminofén 500mg c/8h, reposo médico por 48 horas y abundantes líquidos cítricos.',
    'TA: 120/80 mmHg, FC: 76 bpm. Paciente orientado, colaborador. Reposo emitido.',
    '2025-07-22', 'Acetaminofén 500mg', '2', NULL, NULL
);

-- Consulta 35: Cefalea tensional (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '28548675', 'Cefalea tensional', 1.65, 57.20, 'O-',
    'Dolor de cabeza en zona frontal y orbitaria tras largas jornadas de lectura en pantalla.',
    'Cefalea tensional por astenopia y estrés académico acumulado.',
    'Acetaminofén 500mg c/8h SOS dolor, pausas visuales regulares y ejercicios cervicales.',
    'Tensión arterial normal (114/74 mmHg). Se sugiere evaluación por oftalmología.',
    '2025-08-13', 'Acetaminofén 500mg', '1', NULL, NULL
);

-- Consulta 36: Gastritis aguda (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '27649786', 'Gastritis aguda', 1.77, 73.00, 'B+',
    'Dolor ardoroso en la boca del estómago, náuseas y sabor agrio en garganta al despertar.',
    'Dispepsia ulcerosa / Gastritis aguda inducida por desorden horario en comidas.',
    'Omeprazol 20mg diario en ayunas durante 14 días. Suspender café, picantes y frituras.',
    'Dolor localizado en epigastrio sin irradiación a flancos. Pautas nutricionales entregadas.',
    '2025-09-03', 'Omeprazol 20mg', '1', NULL, NULL
);

-- Consulta 37: Sin patología médica (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '26750897', 'Sin patología médica', 1.63, 55.80, 'A+',
    'Examen médico preventivo y expedición de constancia de salud para pasantías profesionales.',
    'Estudiante en perfecto estado físico y cardiopulmonar.',
    'Mantener hábitos saludables y medidas ergonómicas en el entorno laboral de pasantía.',
    'Signos vitales: TA 118/76 mmHg, FC 70 bpm, Peso adecuado. Constancia aprobada.',
    '2025-09-23', NULL, NULL, NULL, NULL
);

-- Consulta 38: Lumbalgia mecánica (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '25851908', 'Lumbalgia mecánica', 1.76, 77.50, 'O+',
    'Rigidez lumbar matutina y dolor al inclinarse hacia adelante tras trabajo prolongado en mesa.',
    'Lumbalgia postural crónica reactivada por sedentarismo.',
    'Ibuprofeno 400mg c/8h por 3 días, ejercicios de fortalecimiento lumbopélvico y caminatas.',
    'Sin focalización neurológica. Se brindan folletos de ejercicios de higiene postural.',
    '2025-10-14', 'Ibuprofeno 400mg', '1', NULL, NULL
);

-- Consulta 39: Faringoamigdalitis (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '29952019', 'Faringoamigdalitis', 1.58, 51.00, 'AB-',
    'Odinofagia moderada al despertar, carraspera y sensación de cuerpo extraño en garganta.',
    'Faringitis aguda congestiva no exudativa.',
    'Analgésicos orales, colutorios antisépticos e hidratación continua con té tibio.',
    'Orofaringe eritematosa brillante, amígdalas de tamaño normal sin exudado purulento.',
    '2025-11-04', 'Ibuprofeno 400mg', '1', NULL, NULL
);

-- Consulta 40: Hipertensión arterial leve (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '30053120', 'Hipertensión arterial leve', 1.78, 83.50, 'O+',
    'Cefalea occipital palpitante vespertina y sensación de rubor facial al final de la tarde.',
    'Hipertensión arterial estadio 1 en estudio / Sobrepeso moderado (IMC: 26.3).',
    'Dieta hiposódica estricta, incremento de actividad aeróbica y registro tensional seriado.',
    'TA en consulta: 142/92 mmHg, FC: 80 bpm. Se entrega planilla de registro de presión arterial.',
    '2025-11-25', NULL, NULL, NULL, NULL
);

-- ---------------------------------------------------------------------
-- COHORTE 2026 (20 Consultas: Enero al 17 de Noviembre 2026)
-- ---------------------------------------------------------------------

-- Consulta 41: Esguince de tobillo leve (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '31154231', 'Esguince de tobillo leve', 1.67, 59.00, 'O+',
    'Inversión forzada de tobillo derecho durante entrenamiento en la cancha deportiva de la universidad.',
    'Esguince lateral de tobillo derecho Grado I (edema leve, sin equimosis).',
    'Protocolo RICE (reposo, hielo local, vendaje comprensivo y elevación) más analgésico por 4 días.',
    'Se coloca vendaje elástico en unidad médica. Marcha asistida posible sin claudicación severa.',
    '2026-01-14', 'Vendas Elásticas 10cm', '1', 'Ibuprofeno 400mg', '1'
);

-- Consulta 42: Gripe (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '28255342', 'Gripe', 1.75, 72.00, 'A+',
    'Malestar osteoarticular, rinorrea hialina, estornudos y febrícula de inicio súbito.',
    'Síndrome viral respiratorio agudo tipo influenza leve.',
    'Acetaminofén 500mg V.O. c/8h por 3 días, descanso en habitación ventilada y abundantes líquidos.',
    'Paciente lúcido, afebril al momento de la consulta (36.8°C). Permiso de 48h tramitado.',
    '2026-01-28', 'Acetaminofén 500mg', '2', NULL, NULL
);

-- Consulta 43: Cefalea tensional (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '27356453', 'Cefalea tensional', 1.61, 53.40, 'B+',
    'Dolor de cabeza sordo y continuo en frente y sienes tras semanas de trabajo de grado.',
    'Cefalea de tensión asociada a ansiedad y deprivación de sueño.',
    'Acetaminofén 500mg c/8h por 3 días, técnicas de respiración profunda y reposo nocturno de 8 horas.',
    'Sin signos de alarma meníngea. Tensión arterial 116/74 mmHg. Se sugiere apoyo de Psicología.',
    '2026-02-11', 'Acetaminofén 500mg', '1', NULL, NULL
);

-- Consulta 44: Gastritis aguda (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '26457564', 'Gastritis aguda', 1.74, 74.00, 'O+',
    'Dolor quemante en epigastrio tras ingesta de comida rápida condimentada fuera de horas de clase.',
    'Gastritis aguda irritativa y dispepsia ácida secundaria.',
    'Omeprazol 20mg diario en ayunas por 14 días. Dieta blanda y evitar frituras y salsas.',
    'Palpación profunda dolorosa en mesogastrio y epigastrio. Se refuerzan horarios de comidas.',
    '2026-02-25', 'Omeprazol 20mg', '1', NULL, NULL
);

-- Consulta 45: Sin patología médica (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '25558675', 'Sin patología médica', 1.65, 57.00, 'A-',
    'Chequeo médico institucional y certificación física para servicio comunitario universitario.',
    'Beneficiaria clínicamente sana, apta para actividades de campo y vinculación comunitaria.',
    'Medidas preventivas de hidratación bajo el sol y uso de protector solar.',
    'TA: 110/70 mmHg, FC: 68 bpm. Ficha y certificado médico de aptitud entregados.',
    '2026-03-11', NULL, NULL, NULL, NULL
);

-- Consulta 46: Asma bronquial (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '29659786', 'Asma bronquial', 1.82, 81.50, 'O-',
    'Dificultad para respirar al subir escaleras en edificio administrativo y tos seca persistente.',
    'Asma bronquial moderada persistente / Broncoespasmo agudizado por cambio de clima.',
    'Terapia broncodilatadora inhalada pautada, control ambiental en casa y reposo por 24h.',
    'Tórax simétrico, sibilancias espiratorias audibles a distancia. SatO2: 96%.',
    '2026-03-24', NULL, NULL, NULL, NULL
);

-- Consulta 47: Faringoamigdalitis (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '30760897', 'Faringoamigdalitis', 1.59, 50.80, 'B+',
    'Dolor faríngeo agudo de 48h de evolución, disfagia a alimentos sólidos y fiebre de 38.3°C.',
    'Faringitis estreptocócica aguda con amígdalas hiperémicas y exudado blanquecino.',
    'Amoxicilina 500mg V.O. c/8h por 7 días, Ibuprofeno 400mg c/8h por 3 días y gárgaras antisépticas.',
    'Adenopatía yugulodigástrica dolorosa derecha palpable. Constancia médica por 3 días.',
    '2026-04-08', 'Amoxicilina 500mg', '1', 'Ibuprofeno 400mg', '1'
);

-- Consulta 48: Lumbalgia mecánica (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '31861908', 'Lumbalgia mecánica', 1.77, 76.00, 'O+',
    'Dolor punzante paravertebral lumbar derecho tras levantar material de laboratorio pesado.',
    'Contractura lumbar aguda de origen mecánico traumático leve.',
    'Ibuprofeno 400mg c/8h por 4 días, calor seco local por 20 minutos c/8h y reposo de cargas.',
    'Marcha conservada con ligera flexión compensatoria del tronco. Signos neurológicos negativos.',
    '2026-04-22', 'Ibuprofeno 400mg', '1', NULL, NULL
);

-- Consulta 49: Rinitis alérgica (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '28962019', 'Rinitis alérgica', 1.63, 55.00, 'A+',
    'Crisis de estornudos matutinos, prurito palatino y secreción nasal transparente continua.',
    'Rinitis alérgica intermitente agravada por polvo en aulas de clase.',
    'Lavado de fosas nasales con solución fisiológica al despertar, antihistamínico oral nocturno.',
    'Cornetes inferiores edematosos y sonrosados. Mejoría notable tras aseo salino en consultorio.',
    '2026-05-13', 'Solución Fisiológica 0.9%', '1', NULL, NULL
);

-- Consulta 50: Gastroenteritis aguda viral (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '27063120', 'Gastroenteritis aguda viral', 1.80, 79.20, 'O+',
    'Cuadro diarreico agudo (4 deposiciones líquidas), cólicos abdominales y debilidad general.',
    'Gastroenteritis aguda de probable etiología viral sin compromiso hidroelectrolítico severo.',
    'Hidratación oral con suero fisiológico y sales orales, dieta blanda astringente por 3 días.',
    'Signo del pliegue cutáneo negativo, lengua húmeda. Reposo por 48 horas emitido.',
    '2026-05-26', 'Solución Fisiológica 0.9%', '1', NULL, NULL
);

-- Consulta 51: Dermatitis por contacto (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '26164231', 'Dermatitis por contacto', 1.64, 56.00, 'B-',
    'Erupción papular eritematosa en antebrazos tras práctica de campo con reactivos químicos.',
    'Dermatitis de contacto eccematosa irritativa por agentes químicos de laboratorio.',
    'Limpieza exhaustiva con solución salina y gasa estéril, crema protectora y uso obligatorio de bata y guantes.',
    'Lesiones eritematosas no infectadas. Se remite notificación a la jefatura de laboratorio.',
    '2026-06-10', 'Gasas Estériles 7.5x7.5cm', '1', 'Solución Fisiológica 0.9%', '1'
);

-- Consulta 52: Infección urinaria no complicada (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '25265342', 'Infección urinaria no complicada', 1.76, 73.50, 'O+',
    'Disuria progresiva, orinas turbias de olor fuerte y polaquiuria desde hace 3 días.',
    'Infección bacteriana de tracto urinario inferior no complicada.',
    'Amoxicilina 500mg V.O. c/8h por 7 días, incrementar ingesta de líquidos a más de 2 litros diarios.',
    'Fosas lumbares indoloras. Puñopercusión negativa. Se indica evitar retención urinaria prolongada.',
    '2026-06-23', 'Amoxicilina 500mg', '1', NULL, NULL
);

-- Consulta 53: Sin patología médica (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '29366453', 'Sin patología médica', 1.68, 61.50, 'A+',
    'Valoración médica general de egreso académico y control de salud preventiva.',
    'Paciente sana, buen estado nutricional y parámetros hemodinámicos normales.',
    'Se promueve continuación de hábitos alimenticios adecuados y chequeo preventivo semestral.',
    'TA: 114/72 mmHg, FC: 70 bpm, IMC: 21.8. Expedición de constancia médica satisfactoria.',
    '2026-07-08', NULL, NULL, NULL, NULL
);

-- Consulta 54: Cefalea tensional (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '30467564', 'Cefalea tensional', 1.72, 68.40, 'AB+',
    'Dolor de cabeza bilateral continuo de intensidad moderada, con pesadez en hombros y cuello.',
    'Cefalea tensional crónica reagudizada por cierre de evaluaciones académicas.',
    'Acetaminofén 500mg c/8h por 3 días, higiene del descanso y pausas ergonómicas.',
    'Fondo ocular normal. Pupilas isocóricas fotorreactivas. TA: 118/76 mmHg.',
    '2026-07-21', 'Acetaminofén 500mg', '1', NULL, NULL
);

-- Consulta 55: Gastritis aguda (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '31568675', 'Gastritis aguda', 1.62, 52.50, 'O+',
    'Dolor epigástrico ardoroso nocturno que despierta a la paciente, acidez y náuseas.',
    'Gastritis aguda inducida por estrés y ayunos prolongados.',
    'Omeprazol 20mg en ayunas por 14 días. Dieta fraccionada cada 3-4 horas sin irritantes.',
    'Abdomen plano, blando, dolor epigástrico leve a la descompresión. Se entrega plan dietético.',
    '2026-08-12', 'Omeprazol 20mg', '1', NULL, NULL
);

-- Consulta 56: Gripe (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '28669786', 'Gripe', 1.78, 75.00, 'B+',
    'Rinorrea serosa profusa, estornudos, tos irritativa y sensación febril de 24 horas.',
    'Cuadro catarral agudo / Infección respiratoria viral no complicada.',
    'Acetaminofén 500mg c/8h por 3 días, abundante líquido tibio y reposo médico por 48 horas.',
    'Faringe moderadamente enrojecida, sin placas. Constancia médica emitida.',
    '2026-09-02', 'Acetaminofén 500mg', '2', NULL, NULL
);

-- Consulta 57: Esguince de tobillo leve (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '27770897', 'Esguince de tobillo leve', 1.66, 58.20, 'A+',
    'Dolor y edema moderado en tobillo izquierdo tras caída de sus propios pies en el campus.',
    'Esguince de tobillo izquierdo Grado I (lesión de partes blandas sin inestabilidad).',
    'Vendaje compresivo elástico en espiga, hielo local 15 minutos c/6h e Ibuprofeno 400mg c/8h por 4 días.',
    'Se coloca vendaje elástico en el dispensario. Apoyo plantar parcialmente tolerado.',
    '2026-09-22', 'Vendas Elásticas 10cm', '1', 'Ibuprofeno 400mg', '1'
);

-- Consulta 58: Faringoamigdalitis (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '26871908', 'Faringoamigdalitis', 1.75, 71.00, 'O+',
    'Odinofagia intensa, dolor al tragar saliva, escalofríos y decaimiento de 2 días de evolución.',
    'Faringoamigdalitis bacteriana aguda con hiperemia amigdalina y exudado.',
    'Amoxicilina 500mg V.O. c/8h por 7 días, Ibuprofeno 400mg c/8h por 3 días e hidratación.',
    'Amígdalas aumentadas de tamaño con secreción purulenta. Reposo médico de 72 horas otorgado.',
    '2026-10-14', 'Amoxicilina 500mg', '1', 'Ibuprofeno 400mg', '1'
);

-- Consulta 59: Lumbalgia mecánica (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_seed(
    '19632847', '25972019', 'Lumbalgia mecánica', 1.63, 54.50, 'O-',
    'Dolor en región lumbosacra que empeora al estar de pie por tiempo prolongado en talleres de dibujo.',
    'Lumbalgia mecánica postural y sobrecarga muscular bilateral.',
    'Ibuprofeno 400mg c/8h por 3 días, calor local seco en la noche y ejercicios de estiramiento.',
    'Contractura muscular en masa común lumbosacra. Reflejos osteotendinosos simétricos normales.',
    '2026-11-04', 'Ibuprofeno 400mg', '1', NULL, NULL
);

-- Consulta 60: Sin patología médica (Dra. Yuri Boscán)
CALL registrar_consulta_medica_seed(
    '16754209', '29173120', 'Sin patología médica', 1.79, 78.50, 'A+',
    'Evaluación médica preventiva integral y control de signos vitales para inicio de nuevo trayecto académico.',
    'Estudiante en excelentes condiciones clínicas generales. Examen físico cardiovascular y pulmonar normal.',
    'No requiere tratamiento farmacológico. Se promueve continuidad de hábitos de vida activos y saludables.',
    'TA: 118/78 mmHg, FC: 72 bpm, Peso: 78.50 kg, Talla: 1.79 m, IMC: 24.5. Certificado médico emitido con fecha 17/11/2026.',
    '2026-11-17', NULL, NULL, NULL, NULL
);

-- =====================================================================
-- 4. LIMPIEZA DEL PROCEDIMIENTO TEMPORAL
-- =====================================================================
DROP PROCEDURE IF EXISTS registrar_consulta_medica_seed;

-- =====================================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================================
-- 1) Total de consultas médicas registradas:
--    SELECT COUNT(*) AS total_consultas_medicas FROM consulta_medica;
--
-- 2) Consultas por médico:
--    SELECT CONCAT(e.nombre, ' ', e.apellido) AS medico, COUNT(*) AS total
--      FROM consulta_medica cm
--      JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cm.id_solicitud_serv
--      JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
--     GROUP BY medico;
--
-- 3) Consultas por patología médica:
--    SELECT p.nombre_patologia, COUNT(*) AS total
--      FROM consulta_medica cm
--      JOIN detalle_patologia dp ON dp.id_detalle_patologia = cm.id_detalle_patologia
--      JOIN patologia p ON p.id_patologia = dp.id_patologia
--     GROUP BY p.nombre_patologia
--     ORDER BY total DESC;
--
-- 4) Distribución anual (2024, 2025, 2026):
--    SELECT YEAR(fecha_creacion) AS anio, COUNT(*) AS total
--      FROM consulta_medica
--     GROUP BY anio ORDER BY anio;
--
-- 5) Detalle de insumos dispensados:
--    SELECT i.nombre_insumo, COUNT(*) AS veces_usado, SUM(CAST(di.cantidad_usada AS UNSIGNED)) AS unidades_totales
--      FROM detalle_insumo di
--      JOIN insumos i ON i.id_insumo = di.id_insumo
--     GROUP BY i.nombre_insumo
--     ORDER BY unidades_totales DESC;
-- =====================================================================
