-- =====================================================================
-- SEED DE REFERENCIAS ENTRE ÁREAS (TANDA 2) — 72 registros sintéticos
-- Ubicación: docs/bd/seed_nuevas/seed_referencias2.sql
-- Módulo: Referencias Inter-departamentales (id_modulo 10)
--
-- Cobertura Temporal: 22/01/2024 al 17/11/2026 (Ideal para microservicio IA)
-- Tablas involucradas:
--   1. dirpoles_business.referencias     (72 registros principales)
--   2. dirpoles_business.log_referencias (58 registros de trazabilidad y auditoría de estado)
--
-- Distribución temporal:
--   * Cohorte 2024: 24 referencias (14 Aceptadas, 6 Rechazadas, 4 Pendientes)
--   * Cohorte 2025: 24 referencias (15 Aceptadas, 6 Rechazadas, 3 Pendientes)
--   * Cohorte 2026: 24 referencias (12 Aceptadas, 5 Rechazadas, 7 Pendientes, hasta 17/11/2026)
--
-- Distribución de estados:
--   * Aceptadas:  41 registros (56.9%) -> Genera trazabilidad completa en log_referencias
--   * Rechazadas: 17 registros (23.6%) -> Genera motivo normativo en log_referencias
--   * Pendientes: 14 registros (19.4%) -> Flujo vivo en curso, sin log de transición
--
-- Servicios integrados (Flujo bidireccional entre todas las áreas):
--   * 1 = Psicología
--   * 2 = Medicina
--   * 3 = Orientación
--   * 4 = Trabajador Social
--   * 5 = Discapacidad
--
-- Reglas de negocio (ReferenciaModel):
--   * id_servicio_destino != id_servicio_origen (obligatorio cruce entre áreas).
--   * id_empleado_destino != id_empleado_origen.
--   * Motivo (<= 255) y observaciones (<= 2000) obligatorios al crear.
--   * Aceptar/Rechazar registra en log_referencias el empleado destinatario y fecha_accion.
--   * El rechazo registra el motivo en log_referencias.observaciones sin sobreescribir la referencia padre.
--   * Beneficiarios vinculados 1:1 con la cohorte de seed_beneficiarios2.sql.
--
-- 100% IDEMPOTENTE: omite la inserción si ya existe la referencia en la misma fecha.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_spanish2_ci;

-- =====================================================================
-- 1. PROCEDIMIENTO TEMPORAL DE CARGA TRANSACCIONAL E IDEMPOTENTE
-- =====================================================================
DELIMITER ;;

DROP PROCEDURE IF EXISTS registrar_referencia_seed;;
CREATE PROCEDURE registrar_referencia_seed(
    IN p_cedula_ben VARCHAR(15),
    IN p_cedula_origen VARCHAR(15),
    IN p_id_serv_origen INT,
    IN p_cedula_destino VARCHAR(15),
    IN p_id_serv_destino INT,
    IN p_fecha_ref DATETIME,
    IN p_motivo VARCHAR(255),
    IN p_estado VARCHAR(20),
    IN p_observaciones TEXT,
    IN p_fecha_log DATETIME,
    IN p_obs_log TEXT
)
BEGIN
    DECLARE v_id_ben INT;
    DECLARE v_id_emp_orig INT;
    DECLARE v_id_emp_dest INT;
    DECLARE v_id_ref INT;

    -- Resolución dinámica de IDs
    SELECT id_beneficiario INTO v_id_ben FROM beneficiario WHERE cedula = p_cedula_ben COLLATE utf8mb4_spanish2_ci LIMIT 1;
    SELECT id_empleado INTO v_id_emp_orig FROM dirpoles_security.empleado WHERE cedula = p_cedula_origen COLLATE utf8mb4_spanish2_ci LIMIT 1;
    SELECT id_empleado INTO v_id_emp_dest FROM dirpoles_security.empleado WHERE cedula = p_cedula_destino COLLATE utf8mb4_spanish2_ci LIMIT 1;

    IF v_id_ben IS NOT NULL AND v_id_emp_orig IS NOT NULL AND v_id_emp_dest IS NOT NULL THEN
        -- Comprobación de idempotencia: evitar duplicados exactos en misma fecha
        IF NOT EXISTS (
            SELECT 1 FROM referencias
            WHERE id_beneficiario = v_id_ben
              AND DATE(fecha_referencia) = DATE(p_fecha_ref)
              AND id_servicio_origen = p_id_serv_origen
              AND id_servicio_destino = p_id_serv_destino
        ) THEN
            -- Inserción de la cabecera de la referencia
            INSERT INTO referencias (
                id_beneficiario, id_empleado_origen, id_servicio_origen,
                id_empleado_destino, id_servicio_destino,
                fecha_referencia, motivo, estado, observaciones
            ) VALUES (
                v_id_ben, v_id_emp_orig, p_id_serv_origen,
                v_id_emp_dest, p_id_serv_destino,
                p_fecha_ref, p_motivo, p_estado, p_observaciones
            );
            SET v_id_ref = LAST_INSERT_ID();

            -- Si ya fue respondida (Aceptada o Rechazada), registrar la trazabilidad en log_referencias
            IF p_estado IN ('Aceptada', 'Rechazada') THEN
                INSERT INTO log_referencias (
                    id_referencia, estado_anterior, estado_nuevo,
                    id_empleado, fecha_accion, observaciones
                ) VALUES (
                    v_id_ref, 'Pendiente', p_estado,
                    v_id_emp_dest,
                    COALESCE(p_fecha_log, DATE_ADD(p_fecha_ref, INTERVAL 1 DAY)),
                    p_obs_log
                );
            END IF;
        END IF;
    END IF;
END;;

DELIMITER ;

-- =====================================================================
-- 2. CARGA DE REFERENCIAS: COHORTE 2024 (24 Registros)
-- =====================================================================

-- Ref 01: Psicología -> Medicina (Aceptada)
CALL registrar_referencia_seed(
    '28114201', '12023052', 1, '19632847', 2,
    '2024-01-22 09:30:00', 'Evaluación médica por somatización gastrointestinal severa.', 'Aceptada',
    'Estudiante presenta epigastralgia recurrente vinculada a episodios de ansiedad académica.',
    '2024-01-23 10:15:00', 'Referencia aceptada. Paciente evaluado; iniciado tratamiento con protector gástrico y pautas dietéticas.'
);

-- Ref 02: Medicina -> Psicología (Aceptada)
CALL registrar_referencia_seed(
    '29215302', '19632847', 2, '14872391', 1,
    '2024-02-05 10:45:00', 'Apoyo psicológico por cefalea tensional refractaria a analgésicos.', 'Aceptada',
    'Examen neurológico básico sin alteraciones orgánicas; dolor muscular peri-craneal vinculado a estrés académico.',
    '2024-02-06 14:00:00', 'Referencia aceptada. Primera sesión programada para técnicas de relajación progresiva y respiración diafragmática.'
);

-- Ref 03: Orientación -> Trabajo Social (Aceptada)
CALL registrar_referencia_seed(
    '30316403', '13652666', 3, '13529764', 4,
    '2024-02-19 14:15:00', 'Estudio socioeconómico por riesgo de abandono de carrera por pasajes.', 'Aceptada',
    'Estudiante reside en Quíbor y manifiesta incapacidad económica para cubrir los traslados diarios a Barquisimeto.',
    '2024-02-20 09:30:00', 'Referencia aceptada. Solicitada planilla de recaudos y preseleccionado para postulación de beca estudiantil.'
);

-- Ref 04: Trabajo Social -> Discapacidad (Aceptada)
CALL registrar_referencia_seed(
    '31417504', '13529764', 4, '15928374', 5,
    '2024-03-04 11:00:00', 'Evaluación de accesibilidad física para estudiante con muletas.', 'Aceptada',
    'Beneficiario con fractura tibial en recuperación prolongada solicita reubicación de aula a planta baja.',
    '2024-03-05 11:30:00', 'Referencia aceptada. Notificación formal remitida a Coordinación de PNF para asignación en aula 101.'
);

-- Ref 05: Discapacidad -> Psicología (Aceptada)
CALL registrar_referencia_seed(
    '27518605', '24671583', 5, '17543826', 1,
    '2024-03-18 15:30:00', 'Acompañamiento psicológico por baja visión progresiva.', 'Aceptada',
    'Estudiante diagnosticada con retinopatía con impacto en su estado de ánimo e integración social.',
    '2024-03-19 16:00:00', 'Referencia aceptada. Iniciado plan de contención emocional y desarrollo de resiliencia ante pérdida visual.'
);

-- Ref 06: Medicina -> Trabajo Social (Aceptada)
CALL registrar_referencia_seed(
    '26619706', '16754209', 2, '20481637', 4,
    '2024-04-08 09:00:00', 'Solicitud de cobertura de exámenes especializados FAMES.', 'Aceptada',
    'Paciente requiere ecografía abdominal y perfil lipídico completo no realizables en el dispensario.',
    '2024-04-09 10:45:00', 'Referencia aceptada. Expediente FAMES consignado para trámite de ayuda económica ante la fundación.'
);

-- Ref 07: Psicología -> Orientación (Aceptada)
CALL registrar_referencia_seed(
    '25720807', '14872391', 1, '18364725', 3,
    '2024-04-22 13:45:00', 'Reorientación vocacional tras crisis de desmotivación en PNF.', 'Aceptada',
    'Estudiante culminó acompañamiento por depresión leve; requiere clarificar intereses y aptitudes académicas.',
    '2024-04-23 09:15:00', 'Referencia aceptada. Agendada aplicación de batería de exploración vocacional Chaside.'
);

-- Ref 08: Orientación -> Medicina (Aceptada)
CALL registrar_referencia_seed(
    '24821908', '18364725', 3, '19632847', 2,
    '2024-05-06 10:30:00', 'Valoración médica por hipoacusia percibida en entrevistas.', 'Aceptada',
    'El estudiante reporta dificultades para comprender las instrucciones del docente en clases magistrales.',
    '2024-05-07 11:45:00', 'Referencia aceptada. Otoscopia realizada con presencia de tapón de cerumen bilateral; lavado completado.'
);

-- Ref 09: Trabajo Social -> Psicología (Aceptada)
CALL registrar_referencia_seed(
    '23922019', '20481637', 4, '12023052', 1,
    '2024-05-20 14:00:00', 'Atención psicológica para estudiante gestante de primer trimestre.', 'Aceptada',
    'Beneficiaria registrada en programa de gestantes manifiesta crisis de pánico por compatibilidad con estudios.',
    '2024-05-21 15:30:00', 'Referencia aceptada. Sesiones semanales de afrontamiento positivo y manejo del estrés perinatal.'
);

-- Ref 10: Discapacidad -> Trabajo Social (Aceptada)
CALL registrar_referencia_seed(
    '29023120', '15928374', 5, '13529764', 4,
    '2024-06-03 08:45:00', 'Tramitación de exoneración arancelaria por diversidad funcional.', 'Aceptada',
    'Beneficiario con carnet CONAPDIS presenta recaudos para exoneración de aranceles de grado.',
    '2024-06-04 10:00:00', 'Referencia aceptada. Registrada exoneración institucional con validación de certificación CONAPDIS.'
);

-- Ref 11: Orientación -> Psicología (Aceptada)
CALL registrar_referencia_seed(
    '30124231', '22159408', 3, '17543826', 1,
    '2024-06-17 11:15:00', 'Crisis de pánico recurrente durante exposiciones orales.', 'Aceptada',
    'Estudiante experimenta temblores y bloqueo cognitivo severo al momento de realizar defensas públicas.',
    '2024-06-18 14:20:00', 'Referencia aceptada. Iniciado entrenamiento cognitivo-conductual en técnicas de exposición gradual.'
);

-- Ref 12: Medicina -> Discapacidad (Aceptada)
CALL registrar_referencia_seed(
    '31225342', '19632847', 2, '24671583', 5,
    '2024-07-08 09:30:00', 'Valoración médica de secuela motora post-quirúrgica.', 'Aceptada',
    'Paciente operado de ligamento cruzado con marcha claudicante residual que requiere adaptaciones físicas temporales.',
    '2024-07-09 11:00:00', 'Referencia aceptada. Expediente temporal abierto para exención en actividades físicas y uso de ascensor.'
);

-- Ref 13: Discapacidad -> Orientación (Aceptada)
CALL registrar_referencia_seed(
    '28326453', '24671583', 5, '13652666', 3,
    '2024-07-22 14:00:00', 'Adecuación pedagógica y tiempo adicional en evaluaciones escritas.', 'Aceptada',
    'Estudiante con hipoacusia moderada requiere apoyo en adaptación de materiales audiovisuales en aula.',
    '2024-07-23 09:45:00', 'Referencia aceptada. Informe curricular emitido a los docentes del trayecto con recomendaciones.'
);

-- Ref 14: Trabajo Social -> Medicina (Aceptada)
CALL registrar_referencia_seed(
    '27427564', '13529764', 4, '16754209', 2,
    '2024-08-12 10:15:00', 'Certificación médica para postulación a beca deportiva institucional.', 'Aceptada',
    'Se requiere valoración cardiovascular para certificar aptitud física del beneficiario para selección atlética.',
    '2024-08-13 11:30:00', 'Referencia aceptada. Electrocardiograma normal y constancia médica de aptitud emitida sin observaciones.'
);

-- Ref 15: Medicina -> Orientación (Rechazada)
CALL registrar_referencia_seed(
    '26528675', '19632847', 2, '13652666', 3,
    '2024-02-12 15:00:00', 'Solicitud de justificación médica de inasistencias acumuladas.', 'Rechazada',
    'Estudiante solicita que el área de orientación justifique reposos médicos emitidos hace 2 meses.',
    '2024-02-13 10:00:00', 'El trámite de justificación extemporánea de inasistencias compete a la Dirección de Control de Estudios con aval de Secretaría.'
);

-- Ref 16: Psicología -> Discapacidad (Rechazada)
CALL registrar_referencia_seed(
    '25629786', '12023052', 1, '15928374', 5,
    '2024-03-25 11:30:00', 'Registro de condición de déficit atencional sin informe especializado.', 'Rechazada',
    'Estudiante solicita carnet y adecuación sin informe neuropediátrico o psiquiátrico formal que avale diagnóstico.',
    '2024-03-26 12:15:00', 'Normativa exige informe médico neuropediátrico o psiquiátrico emitido por centro asistencial público antes del ingreso al registro.'
);

-- Ref 17: Orientación -> Medicina (Rechazada)
CALL registrar_referencia_seed(
    '24730897', '18364725', 3, '16754209', 2,
    '2024-05-13 14:30:00', 'Chequeo médico de rutina sin sintomatología aguda.', 'Rechazada',
    'Estudiante solicita chequeo general habiendo sido evaluado hace menos de 15 días en jornada integral de salud.',
    '2024-05-14 09:30:00', 'Beneficiario con chequeo físico completo y vigente del 28/04/2024; no procede nueva evaluación salvo urgencia.'
);

-- Ref 18: Trabajo Social -> Orientación (Rechazada)
CALL registrar_referencia_seed(
    '29831908', '13529764', 4, '22159408', 3,
    '2024-06-24 09:15:00', 'Solicitud de cambio de turno por motivos de empleo informal.', 'Rechazada',
    'Beneficiario solicita cambio a turno nocturno fuera de las fechas de modificación de matrícula.',
    '2024-06-25 11:00:00', 'El lapso oficial para modificaciones y permutas de turno concluyó según calendario académico el 15/06.'
);

-- Ref 19: Discapacidad -> Medicina (Rechazada)
CALL registrar_referencia_seed(
    '30932019', '24671583', 5, '19632847', 2,
    '2024-09-16 13:00:00', 'Prescripción de medicamentos de alto costo no disponibles.', 'Rechazada',
    'Solicitud de fármacos neuromoduladores especializados no contemplados en el petitorio oficial dispensario.',
    '2024-09-17 10:30:00', 'El dispensario médico universitario no maneja psicofármacos controlados. Se orienta tramitar ayuda económica vía FAMES.'
);

-- Ref 20: Psicología -> Trabajo Social (Rechazada)
CALL registrar_referencia_seed(
    '31033120', '17543826', 1, '20481637', 4,
    '2024-10-14 10:45:00', 'Solicitud de cupo en residencia universitaria estudiantil.', 'Rechazada',
    'Paciente foráneo solicita hospedaje institucional por conflictos de vivienda en Barquisimeto.',
    '2024-10-15 14:00:00', 'La UPTAEB no dispone de residencias universitarias activas. Se remite al estudiante a postulación de beca de sustento.'
);

-- Ref 21: Psicología -> Medicina (Pendiente)
CALL registrar_referencia_seed(
    '28134231', '12023052', 1, '19632847', 2,
    '2024-11-04 11:00:00', 'Evaluación médica por temblores distales y pérdida ponderal.', 'Pendiente',
    'Estudiante bajo estrés que presenta sintomatología somática con temblores en manos y pérdida de peso sin causa aparente.',
    NULL, NULL
);

-- Ref 22: Medicina -> Discapacidad (Pendiente)
CALL registrar_referencia_seed(
    '27235342', '16754209', 2, '15928374', 5,
    '2024-11-18 14:30:00', 'Valoración de limitación articular en hombro derecho.', 'Pendiente',
    'Paciente con luxación recidivante remitido para verificación de adaptaciones ergonómicas en talleres prácticos.',
    NULL, NULL
);

-- Ref 23: Orientación -> Trabajo Social (Pendiente)
CALL registrar_referencia_seed(
    '26336453', '13652666', 3, '13529764', 4,
    '2024-12-02 09:15:00', 'Evaluación sociofamiliar compleja por vulnerabilidad severa.', 'Pendiente',
    'Estudiante de excelencia académica con riesgo de suspensión por desempleo prolongado de sus progenitores.',
    NULL, NULL
);

-- Ref 24: Trabajo Social -> Orientación (Pendiente)
CALL registrar_referencia_seed(
    '25437564', '20481637', 4, '22159408', 3,
    '2024-12-16 10:30:00', 'Acompañamiento vocacional para reingreso académico.', 'Pendiente',
    'Beneficiaria que retoma sus estudios universitarios luego de dos periodos inactiva por maternidad.',
    NULL, NULL
);

-- =====================================================================
-- 3. CARGA DE REFERENCIAS: COHORTE 2025 (24 Registros)
-- =====================================================================

-- Ref 25: Psicología -> Medicina (Aceptada)
CALL registrar_referencia_seed(
    '29538675', '12023052', 1, '19632847', 2,
    '2025-01-14 09:30:00', 'Evaluación médica por somatización gastrointestinal y gastritis reactiva.', 'Aceptada',
    'Estudiante presenta gastritis reactiva vinculada a ansiedad académica y malos hábitos nutricionales.',
    '2025-01-15 10:15:00', 'Referencia aceptada. Paciente atendido e indicado tratamiento con antiácidos y dieta blanda fraccionada.'
);

-- Ref 26: Medicina -> Psicología (Aceptada)
CALL registrar_referencia_seed(
    '30639786', '19632847', 2, '14872391', 1,
    '2025-01-27 11:00:00', 'Apoyo psicológico por diagnóstico de cefalea tensional recurrente.', 'Aceptada',
    'Se descarta causa orgánica severa; requiere manejo integral de estrés y técnica de relajación.',
    '2025-01-28 14:20:00', 'Referencia aceptada. Se agenda primera sesión de terapia cognitivo-conductual con enfoque en mindfulness.'
);

-- Ref 27: Orientación -> Trabajo Social (Aceptada)
CALL registrar_referencia_seed(
    '31740897', '13652666', 3, '13529764', 4,
    '2025-02-11 14:00:00', 'Evaluación de vulnerabilidad socioeconómica para postulación a beca.', 'Aceptada',
    'Estudiante con alto rendimiento en riesgo de deserción por motivos estrictamente económicos.',
    '2025-02-12 09:30:00', 'Referencia aceptada. Iniciado trámite de estudio socioeconómico integral y consignación de recibos de servicios.'
);

-- Ref 28: Trabajo Social -> Discapacidad (Aceptada)
CALL registrar_referencia_seed(
    '28841908', '13529764', 4, '15928374', 5,
    '2025-02-24 10:30:00', 'Valoración de condición motora para adecuación física de pupitre.', 'Aceptada',
    'Beneficiario requiere evaluación de accesibilidad en aulas y laboratorios de su PNF.',
    '2025-02-25 11:45:00', 'Referencia aceptada. Realizada inspección y asignado mobiliario accesible en planta baja de Edificio Central.'
);

-- Ref 29: Discapacidad -> Psicología (Aceptada)
CALL registrar_referencia_seed(
    '27942019', '15928374', 5, '12023052', 1,
    '2025-03-10 15:15:00', 'Acompañamiento psicológico por proceso de adaptación a prótesis.', 'Aceptada',
    'Estudiante en fase de adaptación motora con labilidad emocional ocasional ante miradas de pares.',
    '2025-03-11 16:00:00', 'Referencia aceptada. Incorporado a programa grupal de apoyo emocional y fortalecimiento de autoimagen.'
);

-- Ref 30: Medicina -> Trabajo Social (Aceptada)
CALL registrar_referencia_seed(
    '26043120', '16754209', 2, '20481637', 4,
    '2025-03-26 08:45:00', 'Solicitud de cobertura de exámenes de laboratorio por FAMES.', 'Aceptada',
    'Paciente requiere exámenes especializados de perfil tiroideo y resonancia magnética no cubiertos en centro público.',
    '2025-03-27 10:00:00', 'Referencia aceptada. Recaudos de FAMES procesados y remitidos para aprobación de presupuesto.'
);

-- Ref 31: Psicología -> Orientación (Aceptada)
CALL registrar_referencia_seed(
    '25144231', '14872391', 1, '18364725', 3,
    '2025-04-08 13:20:00', 'Orientación vocacional por duda de prosecución en PNF Administración.', 'Aceptada',
    'Tras superar crisis de ansiedad generalizada, se sugiere clarificación vocacional para afianzar prosecución.',
    '2025-04-09 09:15:00', 'Referencia aceptada. Aplicada batería vocacional de seguimiento con plan de metas semestrales.'
);

-- Ref 32: Orientación -> Medicina (Aceptada)
CALL registrar_referencia_seed(
    '29245342', '18364725', 3, '19632847', 2,
    '2025-04-22 10:00:00', 'Valoración auditiva por reporte de dificultades de comprensión en clase.', 'Aceptada',
    'Estudiante indica no escuchar con claridad al docente desde la zona posterior del aula de conferencias.',
    '2025-04-23 11:30:00', 'Referencia aceptada. Otoscopia realizada con tapón ceruminoso; lavado completado y remitido a audiometría preventiva.'
);

-- Ref 33: Trabajo Social -> Psicología (Aceptada)
CALL registrar_referencia_seed(
    '30346453', '20481637', 4, '17543826', 1,
    '2025-05-13 14:30:00', 'Atención psicológica a estudiante en estado de gestación.', 'Aceptada',
    'Beneficiaria manifiesta angustia y fatiga por manejo del tiempo entre el embarazo y las entregas del trayecto.',
    '2025-05-14 15:45:00', 'Referencia aceptada. Iniciado acompañamiento perinatal y pautas de distribución horaria.'
);

-- Ref 34: Discapacidad -> Trabajo Social (Aceptada)
CALL registrar_referencia_seed(
    '31447564', '24671583', 5, '13529764', 4,
    '2025-05-28 09:15:00', 'Tramitación de exoneración de arancel por carnet CONAPDIS.', 'Aceptada',
    'Beneficiario con discapacidad sensorial auditiva requiere exoneración de matrícula y derechos de constancia.',
    '2025-05-29 10:30:00', 'Referencia aceptada. Registrada solicitud de exoneración institucional en el sistema.'
);

-- Ref 35: Orientación -> Discapacidad (Aceptada)
CALL registrar_referencia_seed(
    '28548675', '22159408', 3, '24671583', 5,
    '2025-06-10 11:30:00', 'Evaluación de adecuación de evaluaciones por dificultad visual.', 'Aceptada',
    'Estudiante con debilidad visual corregida parcialmente que requiere apoyo en fuentes tipográficas ampliadas.',
    '2025-06-11 13:00:00', 'Referencia aceptada. Recomendada formalmente la ampliación de instrumentos a fuente 16pt a la coordinación.'
);

-- Ref 36: Medicina -> Discapacidad (Aceptada)
CALL registrar_referencia_seed(
    '27649786', '19632847', 2, '15928374', 5,
    '2025-06-25 15:00:00', 'Valoración funcional por secuela de traumatismo lumbar.', 'Aceptada',
    'Paciente con lumbalgia crónica y limitación de flexión de tronco para uso de rampa y ubicación preferencial.',
    '2025-06-26 16:15:00', 'Referencia aceptada. Carnet interno de acceso prioritario emitido para el beneficiario.'
);

-- Ref 37: Discapacidad -> Orientación (Aceptada)
CALL registrar_referencia_seed(
    '26750897', '15928374', 5, '13652666', 3,
    '2025-07-15 08:30:00', 'Seguimiento pedagógico para estudiante con diversidad funcional motora.', 'Aceptada',
    'Se requiere monitoreo del ritmo de trabajo en laboratorios de informática de tercer piso.',
    '2025-07-16 10:00:00', 'Referencia aceptada. Coordinación con jefe de laboratorio para asignación en estación de planta baja.'
);

-- Ref 38: Trabajo Social -> Medicina (Aceptada)
CALL registrar_referencia_seed(
    '25851908', '13529764', 4, '19632847', 2,
    '2025-07-29 14:15:00', 'Valoración médica para solicitud de ayuda técnica FAMES.', 'Aceptada',
    'Estudiante requiere informe médico actualizado para solicitar faja lumbar ortopédica vía FAMES.',
    '2025-07-30 09:30:00', 'Referencia aceptada. Examen físico completado y récipe con especificaciones técnicas emitido.'
);

-- Ref 39: Psicología -> Trabajo Social (Aceptada)
CALL registrar_referencia_seed(
    '29952019', '17543826', 1, '20481637', 4,
    '2025-08-12 10:00:00', 'Evaluación de beca trabajo por situación de desamparo familiar.', 'Aceptada',
    'Paciente en terapia que experimenta desalojo familiar y subsiste mediante empleos temporales precarios.',
    '2025-08-13 11:45:00', 'Referencia aceptada. Preseleccionada con carácter de urgencia social para modalidad de Beca Trabajo.'
);

-- Ref 40: Medicina -> Orientación (Rechazada)
CALL registrar_referencia_seed(
    '30053120', '19632847', 2, '13652666', 3,
    '2025-01-20 15:00:00', 'Solicitud de tutoría académica por inasistencias médicas.', 'Rechazada',
    'Paciente solicita justificación de faltas que no compete al departamento de orientación.',
    '2025-01-21 09:00:00', 'El trámite de justificación de inasistencias se realiza directamente en Secretaría Afecta con aval médico.'
);

-- Ref 41: Psicología -> Discapacidad (Rechazada)
CALL registrar_referencia_seed(
    '31154231', '12023052', 1, '15928374', 5,
    '2025-03-28 11:30:00', 'Evaluación de déficit de atención para carnet de discapacidad.', 'Rechazada',
    'Solicita registro de condición sin informe neuropediátrico avalado por institución de salud pública.',
    '2025-03-29 10:20:00', 'Se requiere informe médico neurológico previo antes de proceder con el registro en la unidad.'
);

-- Ref 42: Orientación -> Medicina (Rechazada)
CALL registrar_referencia_seed(
    '28255342', '18364725', 3, '16754209', 2,
    '2025-05-10 14:15:00', 'Evaluación física preventiva general fuera de periodo.', 'Rechazada',
    'Beneficiario ya cuenta con chequeo médico vigente de este mismo mes sin sintomatología aguda.',
    '2025-05-11 08:30:00', 'Beneficiario atendido el 15/04 en consulta regular sin requerir nueva evaluación en el mismo lapso.'
);

-- Ref 43: Trabajo Social -> Orientación (Rechazada)
CALL registrar_referencia_seed(
    '27356453', '13529764', 4, '22159408', 3,
    '2025-08-18 09:45:00', 'Reubicación de sección por motivos laborales.', 'Rechazada',
    'Solicitud de cambio de sección fuera del periodo normativo del calendario académico.',
    '2025-08-19 11:00:00', 'El periodo de ajuste de secciones culminó la semana pasada según circular de Control de Estudios.'
);

-- Ref 44: Discapacidad -> Medicina (Rechazada)
CALL registrar_referencia_seed(
    '26457564', '24671583', 5, '19632847', 2,
    '2025-10-14 13:00:00', 'Prescripción de medicamentos especializados de alto costo.', 'Rechazada',
    'El servicio médico institucional no dispone del medicamento solicitado en stock dispensario.',
    '2025-10-15 09:15:00', 'Se sugiere canalizar la solicitud a través del programa FAMES de Trabajo Social para adquisición externa.'
);

-- Ref 45: Psicología -> Trabajo Social (Rechazada)
CALL registrar_referencia_seed(
    '25558675', '14872391', 1, '20481637', 4,
    '2025-11-25 10:20:00', 'Solicitud de asignación de vivienda estudiantil.', 'Rechazada',
    'La institución no dispone de programa de residencia o vivienda estudiantil en sus sedes.',
    '2025-11-26 14:00:00', 'Se orienta al estudiante hacia los programas de becas socioeconómicas disponibles para apoyo en residencia privada.'
);

-- Ref 46: Psicología -> Medicina (Pendiente)
CALL registrar_referencia_seed(
    '29659786', '12023052', 1, '16754209', 2,
    '2025-11-10 11:15:00', 'Valoración médica por mareos y cefalea constante.', 'Pendiente',
    'Paciente en terapia psicológica que reporta síntomas físicos persistentes en el aula con visión borrosa transitoria.',
    NULL, NULL
);

-- Ref 47: Medicina -> Discapacidad (Pendiente)
CALL registrar_referencia_seed(
    '30760897', '19632847', 2, '15928374', 5,
    '2025-12-01 09:30:00', 'Evaluación de marcha claudicante por secuela traumática.', 'Pendiente',
    'Se remite para valoración formal e incorporación al registro institucional de accesibilidad.',
    NULL, NULL
);

-- Ref 48: Orientación -> Trabajo Social (Pendiente)
CALL registrar_referencia_seed(
    '31861908', '13652666', 3, '13529764', 4,
    '2025-12-15 14:20:00', 'Evaluación caso familiar complejo para asignación de ayuda económica.', 'Pendiente',
    'Estudiante de excelente promedio con severa restricción de ingresos familiares en situación de riesgo social.',
    NULL, NULL
);

-- =====================================================================
-- 4. CARGA DE REFERENCIAS: COHORTE 2026 (24 Registros hasta 17/11/2026)
-- =====================================================================

-- Ref 49: Trabajo Social -> Orientación (Aceptada)
CALL registrar_referencia_seed(
    '28962019', '20481637', 4, '22159408', 3,
    '2026-01-19 10:45:00', 'Acompañamiento vocacional para reingreso académico.', 'Aceptada',
    'Beneficiaria que retoma sus estudios universitarios tras suspensión temporal de matrícula.',
    '2026-01-20 11:30:00', 'Referencia aceptada. Plan de reincorporación y tutoría académica estructurado con éxito.'
);

-- Ref 50: Discapacidad -> Psicología (Aceptada)
CALL registrar_referencia_seed(
    '27063120', '24671583', 5, '14872391', 1,
    '2026-02-02 15:30:00', 'Apoyo psicológico por diagnóstico reciente de baja visión severa.', 'Aceptada',
    'Estudiante requiere contención emocional tras pérdida progresiva de agudeza visual confirmada por oftalmología.',
    '2026-02-03 16:45:00', 'Referencia aceptada. Sesiones de contención y adaptación emocional iniciadas con frecuencia quincenal.'
);

-- Ref 51: Medicina -> Psicología (Aceptada)
CALL registrar_referencia_seed(
    '26164231', '16754209', 2, '12023052', 1,
    '2026-02-16 09:00:00', 'Evaluación por somatización de estrés en sistema respiratorio.', 'Aceptada',
    'Crisis asmáticas desencadenadas principalmente en semanas de exámenes parciales.',
    '2026-02-17 10:15:00', 'Referencia aceptada. Manejo de crisis asmáticas reactivas asociadas a evaluación académica.'
);

-- Ref 52: Orientación -> Medicina (Aceptada)
CALL registrar_referencia_seed(
    '25265342', '18364725', 3, '19632847', 2,
    '2026-03-02 11:30:00', 'Revisión por fatiga crónica reportada durante entrevistas de seguimiento.', 'Aceptada',
    'Estudiante manifiesta somnolencia excesiva diurna que afecta notablemente su rendimiento académico.',
    '2026-03-03 14:00:00', 'Referencia aceptada. Descartada anemia clínica; indicadas pautas de higiene de sueño y control de cafeína.'
);

-- Ref 53: Trabajo Social -> Medicina (Aceptada)
CALL registrar_referencia_seed(
    '29366453', '13529764', 4, '16754209', 2,
    '2026-03-16 14:00:00', 'Valoración médica de control para beneficiario de programa FAMES.', 'Aceptada',
    'Verificación de evolución clínica previa a la entrega de reembolso quirúrgico por intervención traumatológica.',
    '2026-03-17 09:30:00', 'Referencia aceptada. Evolución postquirúrgica satisfactoria avalada para finiquito del trámite de reembolso.'
);

-- Ref 54: Psicología -> Orientación (Aceptada)
CALL registrar_referencia_seed(
    '30467564', '17543826', 1, '13652666', 3,
    '2026-04-06 09:15:00', 'Acompañamiento en técnica de hábitos de estudio tras superación de distimia.', 'Aceptada',
    'Estudiante supera episodio distímico y requiere reestructurar su metodología de estudio para elevar índice académico.',
    '2026-04-07 10:45:00', 'Referencia aceptada. Taller individualizado de gestión del tiempo y concentración aplicado con éxito.'
);

-- Ref 55: Discapacidad -> Trabajo Social (Aceptada)
CALL registrar_referencia_seed(
    '31568675', '15928374', 5, '20481637', 4,
    '2026-04-20 13:45:00', 'Renovación de beca de apoyo especial por diversidad funcional.', 'Aceptada',
    'Estudiante con discapacidad visual tramita renovación de beca con nuevo informe actualizado de avance curricular.',
    '2026-04-21 15:00:00', 'Referencia aceptada. Estudio socioeconómico actualizado con asignación prioritaria en la nómina de becas.'
);

-- Ref 56: Medicina -> Trabajo Social (Aceptada)
CALL registrar_referencia_seed(
    '28669786', '19632847', 2, '13529764', 4,
    '2026-05-11 10:30:00', 'Solicitud urgente de apoyo económico FAMES por fractura.', 'Aceptada',
    'Beneficiario requiere clavos endomedulares y material de osteosíntesis por trauma en extremidad superior.',
    '2026-05-12 11:45:00', 'Referencia aceptada. Trámite de cobertura de material de osteosíntesis activado con carácter de urgencia.'
);

-- Ref 57: Orientación -> Psicología (Aceptada)
CALL registrar_referencia_seed(
    '27770897', '22159408', 3, '14872391', 1,
    '2026-05-25 15:00:00', 'Manejo de ansiedad ante defensa de proyecto sociointegrador (PSIT).', 'Aceptada',
    'Estudiante de Trayecto IV experimenta llanto incontrolable y temor al fracaso previo a la socialización de grado.',
    '2026-05-26 16:15:00', 'Referencia aceptada. Aplicadas técnicas de oratoria terapéutica y desensibilización sistemática in vivo.'
);

-- Ref 58: Trabajo Social -> Discapacidad (Aceptada)
CALL registrar_referencia_seed(
    '26871908', '20481637', 4, '24671583', 5,
    '2026-06-08 08:45:00', 'Registro de condición física limitante detectada en visita social.', 'Aceptada',
    'Durante estudio socioeconómico domiciliario se constata secuela de poliomielitis en extremidad inferior izquierda.',
    '2026-06-09 10:00:00', 'Referencia aceptada. Apertura formal de expediente de diversidad funcional motora y asignación de carnet.'
);

-- Ref 59: Discapacidad -> Medicina (Aceptada)
CALL registrar_referencia_seed(
    '25972019', '24671583', 5, '19632847', 2,
    '2026-06-22 11:15:00', 'Evaluación traumatológica de control por dolor articular crónico.', 'Aceptada',
    'Beneficiaria con marcha atáxica refiere incremento de dolor en rodilla izquierda durante traslados en campus.',
    '2026-06-23 12:30:00', 'Referencia aceptada. Indicada terapia analgésica y remisión a fisioterapia en centro ambulatorio regional.'
);

-- Ref 60: Psicología -> Discapacidad (Aceptada)
CALL registrar_referencia_seed(
    '29073120', '12023052', 1, '15928374', 5,
    '2026-07-06 14:00:00', 'Evaluación y adecuación de entorno para estudiante con TEA leve.', 'Aceptada',
    'Estudiante diagnosticado con Condición del Espectro Autista que requiere ambientes con menor sobrecarga sensorial.',
    '2026-07-07 15:15:00', 'Referencia aceptada. Establecidas pautas de bajo estímulo sonoro en ambientes de evaluación y laboratorios.'
);

-- Ref 61: Medicina -> Trabajo Social (Rechazada)
CALL registrar_referencia_seed(
    '30174231', '19632847', 2, '13529764', 4,
    '2026-02-23 14:30:00', 'Solicitud de viáticos de traslado médico particular.', 'Rechazada',
    'Paciente solicita reembolso de gastos de transporte en taxi para acudir a consulta médica externa privada.',
    '2026-02-24 10:00:00', 'El programa FAMES no contempla reintegro de viáticos de transporte privado o traslados no institucionalizados.'
);

-- Ref 62: Orientación -> Discapacidad (Rechazada)
CALL registrar_referencia_seed(
    '31275342', '13652666', 3, '24671583', 5,
    '2026-03-23 11:00:00', 'Solicitud de carnet de discapacidad por estrés académico.', 'Rechazada',
    'Estudiante solicita expedición de carnet alegando dificultad de concentración en exámenes bajo estrés.',
    '2026-03-24 12:15:00', 'El estrés académico es una condición reactiva transitoria y no califica como discapacidad permanente bajo Ley CONAPDIS.'
);

-- Ref 63: Psicología -> Orientación (Rechazada)
CALL registrar_referencia_seed(
    '28376453', '17543826', 1, '18364725', 3,
    '2026-05-18 09:30:00', 'Solicitud de adelanto extraordinario de evaluaciones fuera de fecha.', 'Rechazada',
    'Paciente solicita reprogramación masiva de entregas de proyectos alegando saturación emocional previa.',
    '2026-05-19 10:45:00', 'La reprogramación individual de evaluaciones compete exclusivamente al docente de unidad curricular y coordinador de PNF.'
);

-- Ref 64: Trabajo Social -> Psicología (Rechazada)
CALL registrar_referencia_seed(
    '27477564', '13529764', 4, '12023052', 1,
    '2026-06-15 15:15:00', 'Solicitud de terapia de pareja a personas ajenas a la comunidad.', 'Rechazada',
    'Estudiante solicita que se brinde consulta de mediación conyugal incluyendo a su cónyuge no universitario.',
    '2026-06-16 16:30:00', 'El servicio de psicología estudiantil atiende con carácter exclusivo a la comunidad matriculada de la UPTAEB.'
);

-- Ref 65: Discapacidad -> Medicina (Rechazada)
CALL registrar_referencia_seed(
    '26578675', '15928374', 5, '16754209', 2,
    '2026-07-20 10:00:00', 'Solicitud de informe de incapacidad laboral definitiva.', 'Rechazada',
    'Beneficiario solicita dictamen de invalidez permanente para trámite de pensión laboral en empresa externa.',
    '2026-07-21 11:15:00', 'La emisión de informes de incapacidad laboral definitiva es potestad privativa de las comisiones médicas del IVSS.'
);

-- Ref 66: Psicología -> Medicina (Pendiente - Septiembre 2026)
CALL registrar_referencia_seed(
    '25679786', '14872391', 1, '19632847', 2,
    '2026-09-28 11:30:00', 'Evaluación médica por dolor precordial no cardíaco vinculado a pánico.', 'Pendiente',
    'Paciente femenina refiere opresión torácica en situaciones de alta exigencia académica; se sugiere electrocardiograma basal.',
    NULL, NULL
);

-- Ref 67: Medicina -> Trabajo Social (Pendiente - Octubre 2026)
CALL registrar_referencia_seed(
    '29780897', '19632847', 2, '20481637', 4,
    '2026-10-13 14:00:00', 'Canalización de ayuda FAMES para tratamiento oftalmológico urgente.', 'Pendiente',
    'Beneficiaria presenta desprendimiento parcial de vítreo que amerita valoración inmediata en centro oftalmológico especializado.',
    NULL, NULL
);

-- Ref 68: Orientación -> Psicología (Pendiente - Octubre 2026)
CALL registrar_referencia_seed(
    '30881908', '18364725', 3, '17543826', 1,
    '2026-10-26 09:15:00', 'Acompañamiento psicológico por duelo reciente en periodo de exámenes.', 'Pendiente',
    'Estudiante presenta llanto fácil y bloqueo cognitivo tras pérdida súbita de familiar directo conviviente.',
    NULL, NULL
);

-- Ref 69: Trabajo Social -> Discapacidad (Pendiente - Noviembre 2026)
CALL registrar_referencia_seed(
    '31982019', '13529764', 4, '15928374', 5,
    '2026-11-04 10:45:00', 'Evaluación de accesibilidad para estudiante con fractura en yeso.', 'Pendiente',
    'Beneficiario con inmovilización rígida de peroné requiere reubicación transitoria de laboratorios de informática a planta baja.',
    NULL, NULL
);

-- Ref 70: Discapacidad -> Orientación (Pendiente - Noviembre 2026)
CALL registrar_referencia_seed(
    '28083120', '24671583', 5, '13652666', 3,
    '2026-11-09 13:30:00', 'Seguimiento pedagógico para estudiante con diversidad auditiva.', 'Pendiente',
    'Revisión semestral del desempeño y efectividad de los apoyos visuales y subtitulado en clases virtuales.',
    NULL, NULL
);

-- Ref 71: Medicina -> Psicología (Pendiente - 13 de Noviembre 2026)
CALL registrar_referencia_seed(
    '27184231', '16754209', 2, '12023052', 1,
    '2026-11-13 15:00:00', 'Manejo de trastorno del sueño e insomnio crónico severo.', 'Pendiente',
    'Paciente con insomnio de conciliación severo vinculado a sobrecarga de actividades evaluativas y laborales nocturnas.',
    NULL, NULL
);

-- Ref 72: Orientación -> Trabajo Social (Pendiente - 17 de Noviembre 2026)
CALL registrar_referencia_seed(
    '26285342', '22159408', 3, '13529764', 4,
    '2026-11-17 11:00:00', 'Solicitud urgente de evaluación socioeconómica por riesgo de deserción.', 'Pendiente',
    'Estudiante del último trayecto que manifiesta extrema dificultad económica para cubrir pasajes y culminar el semestre.',
    NULL, NULL
);

-- =====================================================================
-- 5. LIMPIEZA DEL PROCEDIMIENTO TEMPORAL
-- =====================================================================
DROP PROCEDURE IF EXISTS registrar_referencia_seed;

-- =====================================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================================
-- 1) Total por estado de referencia:
--    SELECT estado, COUNT(*) AS total FROM referencias GROUP BY estado;
--
-- 2) Flujo inter-departamental (Área Origen -> Área Destino):
--    SELECT s1.nombre_serv AS origen, s2.nombre_serv AS destino, COUNT(*) AS total
--      FROM referencias r
--      JOIN servicio s1 ON s1.id_servicios = r.id_servicio_origen
--      JOIN servicio s2 ON s2.id_servicios = r.id_servicio_destino
--     GROUP BY origen, destino
--     ORDER BY origen, total DESC;
--
-- 3) Trazabilidad en log_referencias (Total de acciones registradas):
--    SELECT estado_nuevo, COUNT(*) AS total FROM log_referencias GROUP BY estado_nuevo;
--
-- 4) Distribución anual de referencias:
--    SELECT YEAR(fecha_referencia) AS anio, COUNT(*) AS total
--      FROM referencias
--     GROUP BY anio ORDER BY anio;
--
-- 5) Especialistas emisores y receptores más activos:
--    SELECT CONCAT(e.nombre, ' ', e.apellido) AS especialista, COUNT(*) AS total_referidas
--      FROM referencias r
--      JOIN dirpoles_security.empleado e ON e.id_empleado = r.id_empleado_origen
--     GROUP BY especialista ORDER BY total_referidas DESC;
-- =====================================================================
