-- =====================================================================
-- SEED DE CITAS DE PSICOLOGÍA (TANDA 2) — 72 registros sintéticos
-- Ubicación: docs/bd/seed_nuevas/seed_citas2.sql
-- Módulo: Citas (id_modulo 3)
--
-- Cobertura Temporal: 22/01/2024 al 17/11/2026 (Ideal para microservicio IA)
-- Distribución anual:
--   * Cohorte 2024: 24 citas (Enero a Noviembre 2024)
--   * Cohorte 2025: 24 citas (Enero a Diciembre 2025)
--   * Cohorte 2026: 24 citas (Enero al 17 de Noviembre 2026)
--
-- Estados de Cita (estado_cita):
--   * 3 = 'Atendida'    (~70% - citas pasadas cumplidas con éxito)
--   * 4 = 'Cancelada'   (~10% - citas canceladas con anticipación)
--   * 5 = 'No asistió'  (~10% - ausentismo para reportes de morbilidad)
--   * 2 = 'Confirmada'  (~5%  - citas confirmadas próximas a atenderse)
--   * 1 = 'Pendiente'   (~5%  - agenda activa para Noviembre 2026)
--
-- Especialistas (Psicólogos de dirpoles_security.empleado):
--   * Iris Alvarez (Cédula: 12023052)
--   * Yaselis Contreras (Cédula: 14872391)
--   * María Alejandra Ríos (Cédula: 17543826)
--
-- Reglas de concurrencia y validación (CitaModel):
--   * Cero solapamiento de horario para el mismo especialista.
--   * Bloques horarios válidos en punto o y media (:00 o :30).
--   * Días hábiles universitarios (Lunes a Viernes).
--   * Idempotente: omite inserciones de slots (psicologo, fecha, hora) ya ocupados.
-- =====================================================================

SET NAMES utf8mb4;

-- =====================================================================
-- 1. ASEGURAR HORARIOS LABORALES DE ATENCIÓN (Lunes a Viernes)
-- =====================================================================
INSERT INTO horario (id_empleado, dia_semana, hora_inicio, hora_fin)
SELECT emp.id_empleado, h.dia_semana, h.hora_inicio, h.hora_fin
FROM (
    -- Iris Alvarez (12023052)
    SELECT '12023052' AS cedula, 'Lunes' AS dia_semana, '08:00:00' AS hora_inicio, '14:00:00' AS hora_fin
    UNION ALL SELECT '12023052', 'Martes', '08:00:00', '14:00:00'
    UNION ALL SELECT '12023052', 'Miércoles', '08:00:00', '14:00:00'
    UNION ALL SELECT '12023052', 'Jueves', '08:00:00', '14:00:00'
    UNION ALL SELECT '12023052', 'Viernes', '08:00:00', '13:00:00'
    -- Yaselis Contreras (14872391)
    UNION ALL SELECT '14872391', 'Lunes', '08:00:00', '14:00:00'
    UNION ALL SELECT '14872391', 'Martes', '08:00:00', '15:00:00'
    UNION ALL SELECT '14872391', 'Miércoles', '08:00:00', '15:00:00'
    UNION ALL SELECT '14872391', 'Jueves', '08:00:00', '14:00:00'
    UNION ALL SELECT '14872391', 'Viernes', '08:00:00', '13:00:00'
    -- María Alejandra Ríos (17543826)
    UNION ALL SELECT '17543826', 'Lunes', '08:00:00', '14:00:00'
    UNION ALL SELECT '17543826', 'Martes', '08:00:00', '14:00:00'
    UNION ALL SELECT '17543826', 'Miércoles', '08:00:00', '15:00:00'
    UNION ALL SELECT '17543826', 'Jueves', '08:00:00', '15:00:00'
    UNION ALL SELECT '17543826', 'Viernes', '08:00:00', '14:00:00'
) h
JOIN dirpoles_security.empleado emp ON emp.cedula = h.cedula
WHERE NOT EXISTS (
    SELECT 1 FROM horario ex
    WHERE ex.id_empleado = emp.id_empleado
      AND ex.dia_semana = h.dia_semana
);

-- =====================================================================
-- 2. INSERTAR 72 CITAS DE PSICOLOGÍA
-- =====================================================================
INSERT INTO cita (fecha, hora, id_beneficiario, id_empleado, estatus, fecha_creacion)
SELECT v.fecha, v.hora, b.id_beneficiario, e.id_empleado, v.estatus, v.fecha_creacion
FROM (
    -- -----------------------------------------------------------------
    -- Cohorte 2024 (24 Citas: Enero a Noviembre 2024)
    -- -----------------------------------------------------------------
    SELECT '2024-01-22' AS fecha, '09:00:00' AS hora, '28114201' AS cedula_ben, '12023052' AS cedula_psic, 3 AS estatus, '2024-01-18' AS fecha_creacion
    UNION ALL SELECT '2024-01-29', '10:30:00', '29215302', '14872391', 3, '2024-01-25'
    UNION ALL SELECT '2024-02-06', '08:30:00', '30316403', '17543826', 3, '2024-02-01'
    UNION ALL SELECT '2024-02-14', '11:00:00', '31417504', '12023052', 4, '2024-02-10'
    UNION ALL SELECT '2024-02-22', '09:30:00', '27518605', '14872391', 3, '2024-02-18'
    UNION ALL SELECT '2024-03-04', '10:00:00', '26619706', '17543826', 3, '2024-02-28'
    UNION ALL SELECT '2024-03-13', '08:30:00', '25720807', '12023052', 5, '2024-03-08'
    UNION ALL SELECT '2024-03-21', '11:30:00', '24821908', '14872391', 3, '2024-03-16'
    UNION ALL SELECT '2024-04-09', '09:00:00', '23922019', '17543826', 3, '2024-04-04'
    UNION ALL SELECT '2024-04-18', '10:30:00', '29023120', '12023052', 3, '2024-04-14'
    UNION ALL SELECT '2024-05-06', '08:30:00', '30124231', '14872391', 3, '2024-05-01'
    UNION ALL SELECT '2024-05-15', '11:00:00', '31225342', '17543826', 4, '2024-05-10'
    UNION ALL SELECT '2024-05-27', '09:30:00', '28326453', '12023052', 3, '2024-05-22'
    UNION ALL SELECT '2024-06-05', '10:00:00', '27427564', '14872391', 3, '2024-05-31'
    UNION ALL SELECT '2024-06-18', '08:30:00', '26528675', '17543826', 3, '2024-06-13'
    UNION ALL SELECT '2024-07-02', '11:30:00', '25629786', '12023052', 5, '2024-06-27'
    UNION ALL SELECT '2024-07-17', '09:00:00', '24730897', '14872391', 3, '2024-07-12'
    UNION ALL SELECT '2024-08-08', '10:30:00', '29831908', '17543826', 3, '2024-08-03'
    UNION ALL SELECT '2024-08-20', '08:30:00', '30932019', '12023052', 3, '2024-08-15'
    UNION ALL SELECT '2024-09-09', '11:00:00', '31033120', '14872391', 3, '2024-09-04'
    UNION ALL SELECT '2024-09-23', '09:30:00', '28134231', '17543826', 3, '2024-09-18'
    UNION ALL SELECT '2024-10-08', '10:00:00', '27235342', '12023052', 4, '2024-10-03'
    UNION ALL SELECT '2024-10-22', '08:30:00', '26336453', '14872391', 3, '2024-10-17'
    UNION ALL SELECT '2024-11-06', '11:30:00', '25437564', '17543826', 3, '2024-11-01'

    -- -----------------------------------------------------------------
    -- Cohorte 2025 (24 Citas: Enero a Diciembre 2025)
    -- -----------------------------------------------------------------
    UNION ALL SELECT '2025-01-20', '09:00:00', '29538675', '12023052', 3, '2025-01-15'
    UNION ALL SELECT '2025-01-28', '10:30:00', '30639786', '14872391', 3, '2025-01-23'
    UNION ALL SELECT '2025-02-05', '08:30:00', '31740897', '17543826', 3, '2025-01-31'
    UNION ALL SELECT '2025-02-17', '11:00:00', '28841908', '12023052', 4, '2025-02-12'
    UNION ALL SELECT '2025-02-27', '09:30:00', '27942019', '14872391', 3, '2025-02-22'
    UNION ALL SELECT '2025-03-11', '10:00:00', '26043120', '17543826', 3, '2025-03-06'
    UNION ALL SELECT '2025-03-24', '08:30:00', '25144231', '12023052', 5, '2025-03-19'
    UNION ALL SELECT '2025-04-07', '11:30:00', '29245342', '14872391', 3, '2025-04-02'
    UNION ALL SELECT '2025-04-23', '09:00:00', '30346453', '17543826', 3, '2025-04-18'
    UNION ALL SELECT '2025-05-08', '10:30:00', '31447564', '12023052', 3, '2025-05-03'
    UNION ALL SELECT '2025-05-19', '08:30:00', '28548675', '14872391', 3, '2025-05-14'
    UNION ALL SELECT '2025-06-04', '11:00:00', '27649786', '17543826', 4, '2025-05-30'
    UNION ALL SELECT '2025-06-16', '09:30:00', '26750897', '12023052', 3, '2025-06-11'
    UNION ALL SELECT '2025-07-08', '10:00:00', '25851908', '14872391', 3, '2025-07-03'
    UNION ALL SELECT '2025-07-22', '08:30:00', '29952019', '17543826', 3, '2025-07-17'
    UNION ALL SELECT '2025-08-07', '11:30:00', '30053120', '12023052', 5, '2025-08-02'
    UNION ALL SELECT '2025-08-19', '09:00:00', '31154231', '14872391', 3, '2025-08-14'
    UNION ALL SELECT '2025-09-08', '10:30:00', '28255342', '17543826', 3, '2025-09-03'
    UNION ALL SELECT '2025-09-24', '08:30:00', '27356453', '12023052', 3, '2025-09-19'
    UNION ALL SELECT '2025-10-07', '11:00:00', '26457564', '14872391', 3, '2025-10-02'
    UNION ALL SELECT '2025-10-21', '09:30:00', '25558675', '17543826', 3, '2025-10-16'
    UNION ALL SELECT '2025-11-05', '10:00:00', '29659786', '12023052', 4, '2025-10-31'
    UNION ALL SELECT '2025-11-18', '08:30:00', '30760897', '14872391', 3, '2025-11-13'
    UNION ALL SELECT '2025-12-02', '11:30:00', '31861908', '17543826', 3, '2025-11-27'

    -- -----------------------------------------------------------------
    -- Cohorte 2026 (24 Citas: Enero al 17 de Noviembre 2026)
    -- -----------------------------------------------------------------
    UNION ALL SELECT '2026-01-19', '09:00:00', '28962019', '12023052', 3, '2026-01-14'
    UNION ALL SELECT '2026-01-28', '10:30:00', '27063120', '14872391', 3, '2026-01-23'
    UNION ALL SELECT '2026-02-09', '08:30:00', '26164231', '17543826', 3, '2026-02-04'
    UNION ALL SELECT '2026-02-23', '11:00:00', '25265342', '12023052', 4, '2026-02-18'
    UNION ALL SELECT '2026-03-05', '09:30:00', '29366453', '14872391', 3, '2026-02-28'
    UNION ALL SELECT '2026-03-18', '10:00:00', '30467564', '17543826', 3, '2026-03-13'
    UNION ALL SELECT '2026-04-08', '08:30:00', '31568675', '12023052', 5, '2026-04-03'
    UNION ALL SELECT '2026-04-20', '11:30:00', '28669786', '14872391', 3, '2026-04-15'
    UNION ALL SELECT '2026-05-05', '09:00:00', '27770897', '17543826', 3, '2026-04-30'
    UNION ALL SELECT '2026-05-19', '10:30:00', '26871908', '12023052', 3, '2026-05-14'
    UNION ALL SELECT '2026-06-03', '08:30:00', '25972019', '14872391', 3, '2026-05-29'
    UNION ALL SELECT '2026-06-17', '11:00:00', '29073120', '17543826', 4, '2026-06-12'
    UNION ALL SELECT '2026-07-07', '09:30:00', '30174231', '12023052', 3, '2026-07-02'
    UNION ALL SELECT '2026-07-21', '10:00:00', '31275342', '14872391', 3, '2026-07-16'
    UNION ALL SELECT '2026-08-06', '08:30:00', '28376453', '17543826', 3, '2026-08-01'
    UNION ALL SELECT '2026-08-18', '11:30:00', '27477564', '12023052', 5, '2026-08-13'
    UNION ALL SELECT '2026-09-07', '09:00:00', '26578675', '14872391', 3, '2026-09-02'
    UNION ALL SELECT '2026-09-22', '10:30:00', '25679786', '17543826', 3, '2026-09-17'
    UNION ALL SELECT '2026-10-06', '08:30:00', '29780897', '12023052', 3, '2026-10-01'
    UNION ALL SELECT '2026-10-20', '11:00:00', '30881908', '14872391', 3, '2026-10-15'
    UNION ALL SELECT '2026-11-03', '09:30:00', '31982019', '17543826', 2, '2026-10-29'
    UNION ALL SELECT '2026-11-09', '10:00:00', '28083120', '12023052', 2, '2026-11-04'
    UNION ALL SELECT '2026-11-16', '08:30:00', '27184231', '14872391', 1, '2026-11-10'
    UNION ALL SELECT '2026-11-17', '11:30:00', '26285342', '17543826', 1, '2026-11-12'
) v
JOIN beneficiario b
    ON b.cedula = v.cedula_ben
JOIN dirpoles_security.empleado e
    ON e.cedula = v.cedula_psic
WHERE NOT EXISTS (
    SELECT 1 FROM cita c
    WHERE c.id_empleado = e.id_empleado
      AND c.fecha = v.fecha
      AND c.hora = v.hora
);

-- =====================================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================================
-- 1) Total de citas registradas:
--    SELECT COUNT(*) AS total_citas FROM cita;
--
-- 2) Distribución por estado de cita:
--    SELECT ec.nombre AS estado, COUNT(*) AS total
--      FROM cita c
--      JOIN estado_cita ec ON ec.id_estado = c.estatus
--     GROUP BY ec.nombre;
--
-- 3) Citas por psicólogo:
--    SELECT CONCAT(e.nombre, ' ', e.apellido) AS psicologo, COUNT(*) AS total_citas
--      FROM cita c
--      JOIN dirpoles_security.empleado e ON e.id_empleado = c.id_empleado
--     GROUP BY psicologo;
-- =====================================================================
