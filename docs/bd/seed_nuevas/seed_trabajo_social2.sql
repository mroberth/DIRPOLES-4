-- =====================================================================
-- SEED DE TRABAJO SOCIAL (TANDA 2) — 72 registros sintéticos
-- Ubicación: docs/bd/seed_nuevas/seed_trabajo_social2.sql
-- Módulo: Trabajo Social / Bienestar Estudiantil (id_modulo 7, id_servicios = 4)
--
-- Cobertura Temporal: 26/01/2024 al 17/11/2026 (Ideal para microservicio IA)
-- Sub-módulos integrados:
--   1. Becas Estudiantiles         (18 registros) -> tabla: becas
--   2. Exoneraciones de Aranceles  (18 registros) -> tabla: exoneracion
--   3. Apoyos Médicos FAMES        (18 registros) -> tabla: fames, detalle_patologia
--   4. Atención a Gestantes        (18 registros) -> tabla: gestion_emb, detalle_patologia
--
-- Distribución temporal:
--   * Cohorte 2024: 24 registros (6 de cada sub-módulo)
--   * Cohorte 2025: 24 registros (6 de cada sub-módulo)
--   * Cohorte 2026: 24 registros (6 de cada sub-módulo, hasta el 17/11/2026)
--
-- Especialistas (Trabajadores Sociales en dirpoles_security.empleado):
--   * Luis Armando Gutiérrez Nieves (Cédula: 13529764) - 36 casos
--   * Keila Andrea Montiel Aponte   (Cédula: 20481637) - 36 casos
--
-- Reglas de negocio (TrabajoSocialModel):
--   * Todas las operaciones cuelgan de solicitud_de_servicio con id_servicios = 4.
--   * En gestión_emb las beneficiarias son estrictamente de GÉNERO FEMENINO.
--   * Exoneraciones cuenta con registros completados (estudio_se generado) y
--     pendientes de estudio socioeconómico (direccion_estudiose IS NULL).
--   * Códigos bancarios venezolanos de 4 dígitos (0102, 0134, 0108, 0105, 0191, 0114).
--   * Códigos Patria numéricos seguros dentro del rango de INT(11) firmado.
--
-- 100% IDEMPOTENTE: omite registros si ya existen para el beneficiario y fecha.
-- =====================================================================

SET NAMES utf8mb4;

-- =====================================================================
-- 1. PROCEDIMIENTOS TEMPORALES TRANSACCIONALES E IDEMPOTENTES
-- =====================================================================
DELIMITER ;;

-- Procedimiento 1: Becas
DROP PROCEDURE IF EXISTS registrar_beca_seed;;
CREATE PROCEDURE registrar_beca_seed(
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

    SELECT id_empleado INTO v_id_ts FROM dirpoles_security.empleado WHERE cedula = p_cedula_ts LIMIT 1;
    SELECT id_beneficiario INTO v_id_ben FROM beneficiario WHERE cedula = p_cedula_ben LIMIT 1;

    IF v_id_ts IS NOT NULL AND v_id_ben IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM becas b
            JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = b.id_solicitud_serv
            WHERE ss.id_beneficiario = v_id_ben AND b.fecha_creacion = p_fecha
        ) THEN
            INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
            VALUES (4, v_id_ben, v_id_ts);
            SET v_id_sol = LAST_INSERT_ID();

            INSERT INTO becas (id_solicitud_serv, cta_bcv, direccion_pdf, tipo_banco, fecha_creacion)
            VALUES (v_id_sol, p_cta_bcv, p_pdf, p_banco, p_fecha);
        END IF;
    END IF;
END;;

-- Procedimiento 2: Exoneraciones
DROP PROCEDURE IF EXISTS registrar_exoneracion_seed;;
CREATE PROCEDURE registrar_exoneracion_seed(
    IN p_cedula_ts VARCHAR(15),
    IN p_cedula_ben VARCHAR(15),
    IN p_motivo VARCHAR(100),
    IN p_otro VARCHAR(255),
    IN p_carta VARCHAR(100),
    IN p_estudio VARCHAR(100),
    IN p_carnet VARCHAR(20),
    IN p_fecha DATE
)
BEGIN
    DECLARE v_id_ts INT;
    DECLARE v_id_ben INT;
    DECLARE v_id_sol INT;

    SELECT id_empleado INTO v_id_ts FROM dirpoles_security.empleado WHERE cedula = p_cedula_ts LIMIT 1;
    SELECT id_beneficiario INTO v_id_ben FROM beneficiario WHERE cedula = p_cedula_ben LIMIT 1;

    IF v_id_ts IS NOT NULL AND v_id_ben IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM exoneracion e
            JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = e.id_solicitud_serv
            WHERE ss.id_beneficiario = v_id_ben AND e.fecha_creacion = p_fecha
        ) THEN
            INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
            VALUES (4, v_id_ben, v_id_ts);
            SET v_id_sol = LAST_INSERT_ID();

            INSERT INTO exoneracion (id_solicitud_serv, motivo, otro_motivo, direccion_carta, direccion_estudiose, carnet_discapacidad, fecha_creacion)
            VALUES (v_id_sol, p_motivo, p_otro, p_carta, p_estudio, p_carnet, p_fecha);
        END IF;
    END IF;
END;;

-- Procedimiento 3: FAMES
DROP PROCEDURE IF EXISTS registrar_fames_seed;;
CREATE PROCEDURE registrar_fames_seed(
    IN p_cedula_ts VARCHAR(15),
    IN p_cedula_ben VARCHAR(15),
    IN p_nombre_patologia VARCHAR(100),
    IN p_tipo_ayuda VARCHAR(100),
    IN p_otro VARCHAR(100),
    IN p_fecha DATE
)
BEGIN
    DECLARE v_id_ts INT;
    DECLARE v_id_ben INT;
    DECLARE v_id_pat INT;
    DECLARE v_id_det INT;
    DECLARE v_id_sol INT;

    SELECT id_empleado INTO v_id_ts FROM dirpoles_security.empleado WHERE cedula = p_cedula_ts LIMIT 1;
    SELECT id_beneficiario INTO v_id_ben FROM beneficiario WHERE cedula = p_cedula_ben LIMIT 1;
    SELECT id_patologia INTO v_id_pat FROM patologia WHERE nombre_patologia = p_nombre_patologia LIMIT 1;

    IF v_id_pat IS NULL THEN
        SELECT id_patologia INTO v_id_pat FROM patologia WHERE nombre_patologia = 'Gripe' LIMIT 1;
    END IF;

    IF v_id_ts IS NOT NULL AND v_id_ben IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM fames f
            JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = f.id_solicitud_serv
            WHERE ss.id_beneficiario = v_id_ben AND f.fecha_creacion = p_fecha
        ) THEN
            INSERT INTO detalle_patologia (id_patologia) VALUES (v_id_pat);
            SET v_id_det = LAST_INSERT_ID();

            INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
            VALUES (4, v_id_ben, v_id_ts);
            SET v_id_sol = LAST_INSERT_ID();

            INSERT INTO fames (id_solicitud_serv, id_detalle_patologia, tipo_ayuda, otro_tipo, fecha_creacion)
            VALUES (v_id_sol, v_id_det, p_tipo_ayuda, p_otro, p_fecha);
        END IF;
    END IF;
END;;

-- Procedimiento 4: Gestantes / Embarazadas
DROP PROCEDURE IF EXISTS registrar_embarazada_seed;;
CREATE PROCEDURE registrar_embarazada_seed(
    IN p_cedula_ts VARCHAR(15),
    IN p_cedula_ben VARCHAR(15),
    IN p_nombre_patologia VARCHAR(100),
    IN p_semanas INT,
    IN p_cod_patria INT,
    IN p_serial_patria INT,
    IN p_estado VARCHAR(20),
    IN p_fecha DATE
)
BEGIN
    DECLARE v_id_ts INT;
    DECLARE v_id_ben INT;
    DECLARE v_id_pat INT;
    DECLARE v_id_det INT;
    DECLARE v_id_sol INT;

    SELECT id_empleado INTO v_id_ts FROM dirpoles_security.empleado WHERE cedula = p_cedula_ts LIMIT 1;
    SELECT id_beneficiario INTO v_id_ben FROM beneficiario WHERE cedula = p_cedula_ben LIMIT 1;
    SELECT id_patologia INTO v_id_pat FROM patologia WHERE nombre_patologia = p_nombre_patologia LIMIT 1;

    IF v_id_pat IS NULL THEN
        SELECT id_patologia INTO v_id_pat FROM patologia WHERE nombre_patologia = 'Sin patología médica' LIMIT 1;
    END IF;

    IF v_id_ts IS NOT NULL AND v_id_ben IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM gestion_emb g
            JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = g.id_solicitud_serv
            WHERE ss.id_beneficiario = v_id_ben AND g.fecha_creacion = p_fecha
        ) THEN
            INSERT INTO detalle_patologia (id_patologia) VALUES (v_id_pat);
            SET v_id_det = LAST_INSERT_ID();

            INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
            VALUES (4, v_id_ben, v_id_ts);
            SET v_id_sol = LAST_INSERT_ID();

            INSERT INTO gestion_emb (id_solicitud_serv, id_detalle_patologia, semanas_gest, codigo_patria, serial_patria, estado, fecha_creacion)
            VALUES (v_id_sol, v_id_det, p_semanas, p_cod_patria, p_serial_patria, p_estado, p_fecha);
        END IF;
    END IF;
END;;

DELIMITER ;

-- =====================================================================
-- 2. INSERTAR 18 REGISTROS DE BECAS ESTUDIANTILES
-- =====================================================================

-- Cohorte 2024
CALL registrar_beca_seed('13529764', '28114201', '01020381920192837465', 'uploads/trabajo_social/planilla_beca_2024_1.pdf', '0102', '2024-01-26');
CALL registrar_beca_seed('20481637', '29215302', '01340192837465019283', 'uploads/trabajo_social/planilla_beca_2024_2.pdf', '0134', '2024-03-15');
CALL registrar_beca_seed('13529764', '30316403', '01080491827364501928', 'uploads/trabajo_social/planilla_beca_2024_3.pdf', '0108', '2024-05-17');
CALL registrar_beca_seed('20481637', '31417504', '01050819273645019283', 'uploads/trabajo_social/planilla_beca_2024_4.pdf', '0105', '2024-07-19');
CALL registrar_beca_seed('13529764', '27518605', '01910582910293847561', 'uploads/trabajo_social/planilla_beca_2024_5.pdf', '0191', '2024-09-20');
CALL registrar_beca_seed('20481637', '26619706', '01140928172635481920', 'uploads/trabajo_social/planilla_beca_2024_6.pdf', '0114', '2024-11-15');

-- Cohorte 2025
CALL registrar_beca_seed('13529764', '25720807', '01020718293049182736', 'uploads/trabajo_social/planilla_beca_2025_1.pdf', '0102', '2025-01-24');
CALL registrar_beca_seed('20481637', '24821908', '01340829102938475610', 'uploads/trabajo_social/planilla_beca_2025_2.pdf', '0134', '2025-03-21');
CALL registrar_beca_seed('13529764', '23922019', '01080391827364501928', 'uploads/trabajo_social/planilla_beca_2025_3.pdf', '0108', '2025-05-16');
CALL registrar_beca_seed('20481637', '29023120', '01050192837465019283', 'uploads/trabajo_social/planilla_beca_2025_4.pdf', '0105', '2025-07-18');
CALL registrar_beca_seed('13529764', '30124231', '01910619283746501928', 'uploads/trabajo_social/planilla_beca_2025_5.pdf', '0191', '2025-09-19');
CALL registrar_beca_seed('20481637', '31225342', '01020948172635481920', 'uploads/trabajo_social/planilla_beca_2025_6.pdf', '0102', '2025-11-21');

-- Cohorte 2026
CALL registrar_beca_seed('13529764', '28326453', '01020819203948571625', 'uploads/trabajo_social/planilla_beca_2026_1.pdf', '0102', '2026-01-23');
CALL registrar_beca_seed('20481637', '27427564', '01340718293049182736', 'uploads/trabajo_social/planilla_beca_2026_2.pdf', '0134', '2026-03-20');
CALL registrar_beca_seed('13529764', '26528675', '01080619283746501928', 'uploads/trabajo_social/planilla_beca_2026_3.pdf', '0108', '2026-05-15');
CALL registrar_beca_seed('20481637', '25629786', '01050518293049182736', 'uploads/trabajo_social/planilla_beca_2026_4.pdf', '0105', '2026-07-17');
CALL registrar_beca_seed('13529764', '24730897', '01910419283746501928', 'uploads/trabajo_social/planilla_beca_2026_5.pdf', '0191', '2026-09-18');
CALL registrar_beca_seed('20481637', '29831908', '01020319283746501928', 'uploads/trabajo_social/planilla_beca_2026_6.pdf', '0102', '2026-11-13');

-- =====================================================================
-- 3. INSERTAR 18 REGISTROS DE EXONERACIONES
-- =====================================================================

-- Cohorte 2024
CALL registrar_exoneracion_seed('13529764', '30932019', 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_2024_1.pdf', 'uploads/trabajo_social/estudio_se_2024_1.pdf', 'No posee', '2024-02-09');
CALL registrar_exoneracion_seed('20481637', '31033120', 'Paquete de Grado', 'No aplica', 'uploads/trabajo_social/carta_exo_2024_2.pdf', NULL, 'CONAPDIS-883019', '2024-04-12');
CALL registrar_exoneracion_seed('13529764', '28134231', 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_2024_3.pdf', 'uploads/trabajo_social/estudio_se_2024_3.pdf', 'No posee', '2024-06-14');
CALL registrar_exoneracion_seed('20481637', '27235342', 'Otro', 'Exoneración arancelaria de constancias académicas', 'uploads/trabajo_social/carta_exo_2024_4.pdf', NULL, 'No posee', '2024-08-09');
CALL registrar_exoneracion_seed('13529764', '26336453', 'Paquete de Grado', 'No aplica', 'uploads/trabajo_social/carta_exo_2024_5.pdf', 'uploads/trabajo_social/estudio_se_2024_5.pdf', 'CONAPDIS-528190', '2024-10-18');
CALL registrar_exoneracion_seed('20481637', '25437564', 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_2024_6.pdf', NULL, 'No posee', '2024-11-29');

-- Cohorte 2025
CALL registrar_exoneracion_seed('13529764', '29538675', 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_2025_1.pdf', 'uploads/trabajo_social/estudio_se_2025_1.pdf', 'CONAPDIS-772819', '2025-02-14');
CALL registrar_exoneracion_seed('20481637', '30639786', 'Paquete de Grado', 'No aplica', 'uploads/trabajo_social/carta_exo_2025_2.pdf', NULL, 'No posee', '2025-04-11');
CALL registrar_exoneracion_seed('13529764', '31740897', 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_2025_3.pdf', 'uploads/trabajo_social/estudio_se_2025_3.pdf', 'CONAPDIS-829103', '2025-06-13');
CALL registrar_exoneracion_seed('20481637', '28841908', 'Otro', 'Exoneración de aranceles de carnetización', 'uploads/trabajo_social/carta_exo_2025_4.pdf', NULL, 'No posee', '2025-08-08');
CALL registrar_exoneracion_seed('13529764', '27942019', 'Paquete de Grado', 'No aplica', 'uploads/trabajo_social/carta_exo_2025_5.pdf', 'uploads/trabajo_social/estudio_se_2025_5.pdf', 'CONAPDIS-619284', '2025-10-17');
CALL registrar_exoneracion_seed('20481637', '26043120', 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_2025_6.pdf', NULL, 'CONAPDIS-903812', '2025-11-28');

-- Cohorte 2026
CALL registrar_exoneracion_seed('13529764', '25144231', 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_2026_1.pdf', 'uploads/trabajo_social/estudio_se_2026_1.pdf', 'CONAPDIS-348192', '2026-02-13');
CALL registrar_exoneracion_seed('20481637', '29245342', 'Paquete de Grado', 'No aplica', 'uploads/trabajo_social/carta_exo_2026_2.pdf', NULL, 'No posee', '2026-04-10');
CALL registrar_exoneracion_seed('13529764', '30346453', 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_2026_3.pdf', 'uploads/trabajo_social/estudio_se_2026_3.pdf', 'CONAPDIS-528190', '2026-06-12');
CALL registrar_exoneracion_seed('20481637', '31447564', 'Otro', 'Exoneración de certificado de calificaciones', 'uploads/trabajo_social/carta_exo_2026_4.pdf', NULL, 'CONAPDIS-649102', '2026-08-14');
CALL registrar_exoneracion_seed('13529764', '28548675', 'Paquete de Grado', 'No aplica', 'uploads/trabajo_social/carta_exo_2026_5.pdf', 'uploads/trabajo_social/estudio_se_2026_5.pdf', 'No posee', '2026-10-16');
CALL registrar_exoneracion_seed('20481637', '27649786', 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_2026_6.pdf', NULL, 'No posee', '2026-11-16');

-- =====================================================================
-- 4. INSERTAR 18 REGISTROS DE APOYOS MÉDICOS FAMES
-- =====================================================================

-- Cohorte 2024
CALL registrar_fames_seed('13529764', '26750897', 'Faringoamigdalitis', 'Económica', 'No aplica', '2024-02-23');
CALL registrar_fames_seed('20481637', '25851908', 'Gastritis aguda', 'Exámenes', 'No aplica', '2024-04-26');
CALL registrar_fames_seed('13529764', '29952019', 'Asma bronquial', 'Operaciones', 'Cirugía ambulatoria de cornetes', '2024-06-28');
CALL registrar_fames_seed('20481637', '30053120', 'Esguince de tobillo leve', 'Otros', 'Apoyo en inmovilización y resonancia magnética', '2024-08-23');
CALL registrar_fames_seed('13529764', '31154231', 'Cefalea tensional', 'Económica', 'No aplica', '2024-10-25');
CALL registrar_fames_seed('20481637', '28255342', 'Gripe', 'Exámenes', 'Perfil 20 y hematología completa', '2024-11-22');

-- Cohorte 2025
CALL registrar_fames_seed('13529764', '27356453', 'Infección urinaria no complicada', 'Económica', 'No aplica', '2025-02-28');
CALL registrar_fames_seed('20481637', '26457564', 'Gastritis aguda', 'Exámenes', 'Endoscopia digestiva superior', '2025-04-25');
CALL registrar_fames_seed('13529764', '25558675', 'Lumbalgia mecánica', 'Otros', 'Tratamiento fisioterapéutico especializado', '2025-06-27');
CALL registrar_fames_seed('20481637', '29659786', 'Asma bronquial', 'Operaciones', 'Corrección de tabique nasal', '2025-08-22');
CALL registrar_fames_seed('13529764', '30760897', 'Faringoamigdalitis', 'Económica', 'No aplica', '2025-10-24');
CALL registrar_fames_seed('20481637', '31861908', 'Gastroenteritis aguda viral', 'Exámenes', 'Coproanálisis y electrolitos séricos', '2025-11-21');

-- Cohorte 2026
CALL registrar_fames_seed('13529764', '28962019', 'Cefalea tensional', 'Exámenes', 'Tomografía axial computarizada de cráneo', '2026-02-27');
CALL registrar_fames_seed('20481637', '27063120', 'Gripe', 'Económica', 'No aplica', '2026-04-24');
CALL registrar_fames_seed('13529764', '26164231', 'Dermatitis por contacto', 'Exámenes', 'Pruebas de parche y panel de alergias', '2026-06-26');
CALL registrar_fames_seed('20481637', '25265342', 'Esguince de tobillo leve', 'Operaciones', 'Artroscopia diagnóstica de tobillo', '2026-08-21');
CALL registrar_fames_seed('13529764', '29366453', 'Gastritis aguda', 'Económica', 'No aplica', '2026-10-23');
CALL registrar_fames_seed('20481637', '30467564', 'Asma bronquial', 'Otros', 'Dotación de espaciador y broncodilatadores', '2026-11-13');

-- =====================================================================
-- 5. INSERTAR 18 REGISTROS DE GESTANTES / EMBARAZADAS (MUJERES)
-- =====================================================================

-- Cohorte 2024
CALL registrar_embarazada_seed('13529764', '28114201', 'Sin patología médica', 10, 1029384711, 1987654311, 'Aprobado', '2024-03-01');
CALL registrar_embarazada_seed('20481637', '30316403', 'Gripe', 16, 1039485822, 1988765422, 'Aprobado', '2024-05-03');
CALL registrar_embarazada_seed('13529764', '27518605', 'Gastritis aguda', 24, 1049586933, 1989876533, 'Aprobado', '2024-07-05');
CALL registrar_embarazada_seed('20481637', '25720807', 'Sin patología médica', 14, NULL, NULL, 'Rechazado', '2024-08-16');
CALL registrar_embarazada_seed('13529764', '23922019', 'Sin patología médica', 30, 1059687044, 1980987644, 'Aprobado', '2024-10-04');
CALL registrar_embarazada_seed('20481637', '30124231', 'Cefalea tensional', 12, 1069788155, 1981098755, 'En Proceso', '2024-11-22');

-- Cohorte 2025
CALL registrar_embarazada_seed('13529764', '28326453', 'Sin patología médica', 18, 1079889266, 1982109866, 'Aprobado', '2025-02-07');
CALL registrar_embarazada_seed('20481637', '26528675', 'Gastritis aguda', 22, 1089990377, 1983210977, 'Aprobado', '2025-04-04');
CALL registrar_embarazada_seed('13529764', '24730897', 'Gripe', 15, NULL, NULL, 'Rechazado', '2025-06-06');
CALL registrar_embarazada_seed('20481637', '30932019', 'Sin patología médica', 28, 1090101488, 1984321088, 'Aprobado', '2025-08-01');
CALL registrar_embarazada_seed('13529764', '28134231', 'Sin patología médica', 34, 1011212599, 1985432199, 'Aprobado', '2025-10-03');
CALL registrar_embarazada_seed('20481637', '26336453', 'Cefalea tensional', 14, 1022323600, 1986543200, 'En Proceso', '2025-11-14');

-- Cohorte 2026
CALL registrar_embarazada_seed('13529764', '29538675', 'Sin patología médica', 12, 1033434711, 1987654311, 'Aprobado', '2026-02-06');
CALL registrar_embarazada_seed('20481637', '31740897', 'Gripe', 20, 1044545822, 1988765422, 'Aprobado', '2026-04-17');
CALL registrar_embarazada_seed('13529764', '27942019', 'Gastritis aguda', 16, NULL, NULL, 'Rechazado', '2026-06-19');
CALL registrar_embarazada_seed('20481637', '25144231', 'Sin patología médica', 26, 1055656933, 1989876533, 'Aprobado', '2026-08-07');
CALL registrar_embarazada_seed('13529764', '30346453', 'Sin patología médica', 18, 1066767044, 1980987644, 'En Proceso', '2026-10-02');
CALL registrar_embarazada_seed('20481637', '28548675', 'Sin patología médica', 14, 1077878155, 1981098755, 'En Proceso', '2026-11-17');

-- =====================================================================
-- 6. LIMPIEZA DE LOS PROCEDIMIENTOS TEMPORALES
-- =====================================================================
DROP PROCEDURE IF EXISTS registrar_beca_seed;
DROP PROCEDURE IF EXISTS registrar_exoneracion_seed;
DROP PROCEDURE IF EXISTS registrar_fames_seed;
DROP PROCEDURE IF EXISTS registrar_embarazada_seed;

-- =====================================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================================
-- 1) Total por sub-módulo de Trabajo Social:
--    SELECT 'Becas' AS modulo, COUNT(*) AS total FROM becas
--    UNION ALL
--    SELECT 'Exoneraciones', COUNT(*) FROM exoneracion
--    UNION ALL
--    SELECT 'FAMES', COUNT(*) FROM fames
--    UNION ALL
--    SELECT 'Embarazadas', COUNT(*) FROM gestion_emb;
--
-- 2) Casos por trabajador social:
--    SELECT CONCAT(e.nombre, ' ', e.apellido) AS trabajador_social, COUNT(*) AS total
--      FROM solicitud_de_servicio ss
--      JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
--     WHERE ss.id_servicios = 4
--     GROUP BY trabajador_social;
--
-- 3) Distribución por banco en Becas:
--    SELECT tipo_banco, COUNT(*) AS total FROM becas GROUP BY tipo_banco;
--
-- 4) Estatus de Estudios Socioeconómicos en Exoneraciones:
--    SELECT CASE WHEN direccion_estudiose IS NOT NULL THEN 'Completada (Estudio generado)' ELSE 'Pendiente de estudio' END AS estatus_estudio,
--           COUNT(*) AS total
--      FROM exoneracion
--     GROUP BY estatus_estudio;
--
-- 5) Tipos de ayuda solicitadas en FAMES:
--    SELECT tipo_ayuda, COUNT(*) AS total FROM fames GROUP BY tipo_ayuda;
--
-- 6) Estatus del beneficio en Gestantes / Embarazadas:
--    SELECT estado, COUNT(*) AS total FROM gestion_emb GROUP BY estado;
--
-- 7) Distribución anual global de Trabajo Social:
--    SELECT YEAR(ss.fecha_creacion) AS anio, COUNT(*) AS total
--      FROM solicitud_de_servicio ss
--     WHERE ss.id_servicios = 4
--     GROUP BY anio ORDER BY anio;
-- =====================================================================
