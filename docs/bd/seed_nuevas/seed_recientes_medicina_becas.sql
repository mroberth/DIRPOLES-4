-- =====================================================================
-- SEED DE REGISTROS RECIENTES: MEDICINA Y BECAS (NOVIEMBRE 2026)
-- Ubicación: docs/bd/seed_nuevas/seed_recientes_medicina_becas.sql
--
-- Cobertura Temporal: 18/11/2026 al 26/11/2026 (HOY) hasta las 13:40 PM.
-- Diseñado específicamente para que las estadísticas y el microservicio de IA
-- reflejen datos clínicos y socioeconómicos en tiempo real para la presentación.
--
-- Registros incluidos:
--   1. Consultas Médicas (12 registros): 18/11 al 26/11 (máx 13:40 PM)
--   2. Becas Estudiantiles (6 registros): 18/11 al 26/11 (máx 11:30 AM)
--
-- Médicos responsables (dirpoles_security.empleado):
--   * Carlos Eduardo Sánchez Bracamonte (Cédula: 19632847)
--   * Yuri Carolina Boscán Méndez       (Cédula: 16754209)
--
-- Trabajadores Sociales responsables (dirpoles_security.empleado):
--   * Luis Armando Gutiérrez Nieves    (Cédula: 13529764)
--   * Keila Andrea Montiel Aponte      (Cédula: 20481637)
--
-- 100% IDEMPOTENTE y con intercalación utf8mb4_spanish2_ci.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_spanish2_ci;

DELIMITER ;;

-- =====================================================================
-- 1. PROCEDIMIENTO: CONSULTAS MÉDICAS RECIENTES
-- =====================================================================
DROP PROCEDURE IF EXISTS registrar_consulta_medica_reciente_seed;;
CREATE PROCEDURE registrar_consulta_medica_reciente_seed(
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
    IN p_fecha DATE,
    IN p_nombre_insumo VARCHAR(100),
    IN p_cant_insumo VARCHAR(100)
)
BEGIN
    DECLARE v_id_med INT;
    DECLARE v_id_ben INT;
    DECLARE v_id_pat INT;
    DECLARE v_id_ins INT;
    DECLARE v_id_det_pat INT;
    DECLARE v_id_sol INT;
    DECLARE v_id_con INT;

    SELECT id_empleado INTO v_id_med FROM dirpoles_security.empleado WHERE cedula = p_cedula_med COLLATE utf8mb4_spanish2_ci LIMIT 1;
    SELECT id_beneficiario INTO v_id_ben FROM beneficiario WHERE cedula = p_cedula_ben COLLATE utf8mb4_spanish2_ci LIMIT 1;
    SELECT id_patologia INTO v_id_pat FROM patologia WHERE nombre_patologia = p_nombre_patologia COLLATE utf8mb4_spanish2_ci LIMIT 1;

    IF v_id_pat IS NULL THEN
        SELECT id_patologia INTO v_id_pat FROM patologia WHERE nombre_patologia = 'Sin patología médica' LIMIT 1;
    END IF;

    IF v_id_med IS NOT NULL AND v_id_ben IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM consulta_medica cm
            JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cm.id_solicitud_serv
            WHERE ss.id_beneficiario = v_id_ben AND cm.fecha_creacion = p_fecha
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
                p_motivo_visita, p_diagnostico, p_tratamiento, p_observaciones, p_fecha
            );
            SET v_id_con = LAST_INSERT_ID();

            -- D) detalle_insumo si aplica
            IF p_nombre_insumo IS NOT NULL AND p_cant_insumo IS NOT NULL THEN
                SELECT id_insumo INTO v_id_ins
                FROM insumos
                WHERE nombre_insumo LIKE CONCAT('%', p_nombre_insumo COLLATE utf8mb4_spanish2_ci, '%') LIMIT 1;

                IF v_id_ins IS NOT NULL THEN
                    INSERT INTO detalle_insumo (id_consulta_med, id_insumo, cantidad_usada)
                    VALUES (v_id_con, v_id_ins, p_cant_insumo);
                END IF;
            END IF;
        END IF;
    END IF;
END;;

-- =====================================================================
-- 2. PROCEDIMIENTO: BECAS ESTUDIANTILES RECIENTES
-- =====================================================================
DROP PROCEDURE IF EXISTS registrar_beca_reciente_seed;;
CREATE PROCEDURE registrar_beca_reciente_seed(
    IN p_cedula_ts VARCHAR(15),
    IN p_cedula_ben VARCHAR(15),
    IN p_cta_bcv VARCHAR(100),
    IN p_pdf VARCHAR(100),
    IN p_banco VARCHAR(4),
    IN p_fecha DATE
)
BEGIN
    DECLARE v_id_ts INT;
    DECLARE v_id_ben INT;
    DECLARE v_id_sol INT;

    SELECT id_empleado INTO v_id_ts FROM dirpoles_security.empleado WHERE cedula = p_cedula_ts COLLATE utf8mb4_spanish2_ci LIMIT 1;
    SELECT id_beneficiario INTO v_id_ben FROM beneficiario WHERE cedula = p_cedula_ben COLLATE utf8mb4_spanish2_ci LIMIT 1;

    IF v_id_ts IS NOT NULL AND v_id_ben IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM becas b
            JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = b.id_solicitud_serv
            WHERE ss.id_beneficiario = v_id_ben AND b.fecha_creacion = p_fecha
        ) THEN
            -- Solicitud de servicio (id_servicios = 4 para Trabajo Social)
            INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
            VALUES (4, v_id_ben, v_id_ts);
            SET v_id_sol = LAST_INSERT_ID();

            -- Beca
            INSERT INTO becas (id_solicitud_serv, cta_bcv, direccion_pdf, tipo_banco, fecha_creacion)
            VALUES (v_id_sol, p_cta_bcv, p_pdf, p_banco, p_fecha);
        END IF;
    END IF;
END;;

DELIMITER ;

-- =====================================================================
-- 3. INSERCIÓN DE CONSULTAS MÉDICAS (18/11/2026 al 26/11/2026 13:40 PM)
-- =====================================================================

-- Consulta 1: Miércoles 18/11/2026 08:30:00 (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_reciente_seed(
    '19632847', '28962019', 'Faringoamigdalitis',
    1.63, 54.20, 'O+',
    'Dolor de garganta agudo de 48 horas de evolución, odinofagia y febrícula.',
    'Faringitis eritematosa aguda de probable etiología viral.',
    'Ibuprofeno 400mg cada 8 horas por 3 días, gárgaras de agua salina e hidratación constante.',
    'Paciente orientada; faringe congestiva sin placas purulentas. Se emite reposo por 24 horas.',
    '2026-11-18', 'Ibuprofeno 400mg', '1'
);

-- Consulta 2: Miércoles 18/11/2026 11:15:00 (Dra. Yuri Boscán)
CALL registrar_consulta_medica_reciente_seed(
    '16754209', '27063120', 'Cefalea tensional',
    1.74, 72.80, 'A+',
    'Cefalea occipital opresiva bilateral y contractura muscular en cuello.',
    'Cefalea tensional secundaria a estrés por preparación de proyecto de grado.',
    'Acetaminofén 500mg cada 8 horas en caso de dolor, reposo en ambiente tranquilo y pausas activas.',
    'Tensión arterial 120/80 mmHg. Sin signos neurológicos de focalización.',
    '2026-11-18', 'Acetaminofén 500mg', '2'
);

-- Consulta 3: Jueves 19/11/2026 09:45:00 (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_reciente_seed(
    '19632847', '26164231', 'Gastritis aguda',
    1.58, 51.00, 'B+',
    'Epigastralgia urente, pirosis y náuseas matutinas tras ingesta de café.',
    'Gastritis aguda irritativa por transgresión dietética.',
    'Omeprazol 20mg en ayunas por 14 días y dieta gástrica blanda fraccionada.',
    'Abdomen blando, depresible, doloroso a la palpación en epigastrio sin defensa muscular.',
    '2026-11-19', 'Omeprazol 20mg', '1'
);

-- Consulta 4: Viernes 20/11/2026 10:20:00 (Dra. Yuri Boscán)
CALL registrar_consulta_medica_reciente_seed(
    '16754209', '25265342', 'Esguince de tobillo leve',
    1.77, 76.50, 'O+',
    'Traumatismo indirecto por torsión de tobillo derecho al bajar escaleras en campus.',
    'Esguince de tobillo derecho Grado I (ligamento peroneoastragalino anterior).',
    'Vendaje compresivo elástico, crioterapia local 15 minutos c/4h y reposo relativo por 48 horas.',
    'Edema bimaleolar leve sin equimosis severa. Marcha claudicante antiálgica.',
    '2026-11-20', 'Venda Elástica 10cm', '1'
);

-- Consulta 5: Lunes 23/11/2026 08:45:00 (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_reciente_seed(
    '19632847', '29366453', 'Sin patología médica',
    1.66, 60.50, 'A-',
    'Evaluación médica institucional para participación en juegos inter-facultades.',
    'Estudiante clínicamente sana, apta para actividades físicas de alta exigencia.',
    'Sin tratamiento farmacológico. Se dan recomendaciones de calentamiento previo y nutrición.',
    'Aparato cardiopulmonar auscultado normal, frecuencia cardíaca 68 lpm, saturación O2 99%.',
    '2026-11-23', NULL, NULL
);

-- Consulta 6: Lunes 23/11/2026 13:10:00 (Dra. Yuri Boscán)
CALL registrar_consulta_medica_reciente_seed(
    '16754209', '30467564', 'Gripe',
    1.71, 68.00, 'O+',
    'Rinorrea hialina profusa, prurito nasal, estornudos frecuentes y astenia.',
    'Rinofaringitis aguda viral (cuadro gripal estacional).',
    'Tratamiento sintomático con analgésico oral, reposo relativo e ingesta de abundantes líquidos cítricos.',
    'Temperatura 36.7 °C, orofaringe levemente congestiva. Se indican medidas higiénicas.',
    '2026-11-23', 'Acetaminofén 500mg', '1'
);

-- Consulta 7: Martes 24/11/2026 09:15:00 (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_reciente_seed(
    '19632847', '31568675', 'Infección urinaria no complicada',
    1.60, 55.20, 'AB+',
    'Disuria moderada, polaquiuria y sensación de vaciamiento vesical incompleto.',
    'Infección urinaria baja aguda no complicada (Cistitis bacteriana).',
    'Amoxicilina 500mg cada 8 horas por 7 días, antiespasmódico e hidratación abundante (>2.5L diarios).',
    'Puño percusión lumbar bilateral negativa. Se entrega orden para urocultivo de control.',
    '2026-11-24', 'Amoxicilina 500mg', '1'
);

-- Consulta 8: Martes 24/11/2026 11:40:00 (Dra. Yuri Boscán)
CALL registrar_consulta_medica_reciente_seed(
    '16754209', '28669786', 'Lumbalgia mecánica',
    1.75, 79.10, 'A+',
    'Dolor lumbosacro de inicio insidioso tras levantamiento de cajas de material en laboratorio.',
    'Lumbalgia mecánica aguda por esfuerzo físico indebido.',
    'Ibuprofeno 400mg cada 8 horas con las comidas por 4 días, calor local seco y pautas ergonómicas.',
    'Contractura de músculos paravertebrales lumbares. Maniobra de Lasègue negativa bilateral.',
    '2026-11-24', 'Ibuprofeno 400mg', '1'
);

-- Consulta 9: Miércoles 25/11/2026 10:00:00 (Dr. Carlos Sánchez)
CALL registrar_consulta_medica_reciente_seed(
    '19632847', '27770897', 'Rinitis alérgica',
    1.62, 58.00, 'O-',
    'Congestión nasal matutina intensa, estornudos en salva y lagrimeo durante trabajo de campo.',
    'Rinitis alérgica estacional exacerbada por alérgenos ambientales.',
    'Lavados nasales con solución fisiológica tibia dos veces al día y medidas de barrera física.',
    'Mucosa nasal pálida con hipertrofia de cornetes inferiores. Tórax sin ruidos agregados.',
    '2026-11-25', 'Solución Fisiológica 0.9%', '1'
);

-- Consulta 10: Miércoles 25/11/2026 12:30:00 (Dra. Yuri Boscán)
CALL registrar_consulta_medica_reciente_seed(
    '16754209', '26871908', 'Dermatitis por contacto',
    1.82, 84.30, 'B-',
    'Lesiones micropapulares eritematosas y prurito intenso en antebrazo derecho tras contacto con reactivo.',
    'Dermatitis de contacto irritativa en extremidad superior derecha.',
    'Lavado suave con jabón neutro, aplicación de crema humectante hipoalergénica y protección dérmica.',
    'Área eritematosa circunscrita sin signos de celulitis ni calor local patológico.',
    '2026-11-25', 'Gasas Estériles 7.5x7.5', '1'
);

-- Consulta 11: Jueves 26/11/2026 08:30:00 (HOY a primera hora - Dr. Carlos Sánchez)
CALL registrar_consulta_medica_reciente_seed(
    '19632847', '25972019', 'Cefalea tensional',
    1.65, 57.40, 'O+',
    'Cefalea opresiva en banda que inició al levantarse hoy en la mañana, asociada a trasnocho.',
    'Cefalea tensional por privación de sueño y sobrecarga académica de cierre de lapso.',
    'Acetaminofén 500mg vía oral, hidratación y 40 minutos de reposo en camilla clínica del dispensario.',
    'Atendida en la primera consulta de hoy (08:30 AM). Reporta alivio de sintomatología y regresa a clases.',
    '2026-11-26', 'Acetaminofén 500mg', '2'
);

-- Consulta 12: Jueves 26/11/2026 13:40:00 (HOY antes de las 14:00 - Dra. Yuri Boscán)
CALL registrar_consulta_medica_reciente_seed(
    '16754209', '29073120', 'Gastritis aguda',
    1.78, 74.20, 'A+',
    'Dolor punzante epigástrico súbito posterior a almuerzo apresurado y consumo de alimentos irritantes.',
    'Dispepsia funcional aguda con hiperacidez gástrica.',
    'Omeprazol 20mg cápsula oral, suspensión de comidas condimentadas y recomendaciones de ingesta pausada.',
    'Atendido al mediodía de hoy (13:40 PM). Ruidos hidroaéreos normales, dolor a la palpación epigástrica.',
    '2026-11-26', 'Omeprazol 20mg', '1'
);

-- =====================================================================
-- 4. INSERCIÓN DE BECAS RECIENTES (18/11/2026 al 26/11/2026 11:30 AM)
-- =====================================================================

-- Beca 1: Miércoles 18/11/2026 (Lic. Luis Gutiérrez)
CALL registrar_beca_reciente_seed(
    '13529764', '30174231', '01020491827364510293',
    'uploads/trabajo_social/planilla_beca_reciente_1.pdf', '0102',
    '2026-11-18'
);

-- Beca 2: Viernes 20/11/2026 (Lic. Keila Montiel)
CALL registrar_beca_reciente_seed(
    '20481637', '31275342', '01340918273645102938',
    'uploads/trabajo_social/planilla_beca_reciente_2.pdf', '0134',
    '2026-11-20'
);

-- Beca 3: Lunes 23/11/2026 (Lic. Luis Gutiérrez)
CALL registrar_beca_reciente_seed(
    '13529764', '28376453', '01080192837465102938',
    'uploads/trabajo_social/planilla_beca_reciente_3.pdf', '0108',
    '2026-11-23'
);

-- Beca 4: Martes 24/11/2026 (Lic. Keila Montiel)
CALL registrar_beca_reciente_seed(
    '20481637', '27477564', '01050918273645102938',
    'uploads/trabajo_social/planilla_beca_reciente_4.pdf', '0105',
    '2026-11-24'
);

-- Beca 5: Miércoles 25/11/2026 (Lic. Luis Gutiérrez)
CALL registrar_beca_reciente_seed(
    '13529764', '26578675', '01910192837465102938',
    'uploads/trabajo_social/planilla_beca_reciente_5.pdf', '0191',
    '2026-11-25'
);

-- Beca 6: Jueves 26/11/2026 11:30:00 (HOY en la mañana - Lic. Keila Montiel)
CALL registrar_beca_reciente_seed(
    '20481637', '25679786', '01020987654321098765',
    'uploads/trabajo_social/planilla_beca_reciente_6.pdf', '0102',
    '2026-11-26'
);

-- =====================================================================
-- 5. LIMPIEZA DE PROCEDIMIENTOS TEMPORALES
-- =====================================================================
DROP PROCEDURE IF EXISTS registrar_consulta_medica_reciente_seed;
DROP PROCEDURE IF EXISTS registrar_beca_reciente_seed;
