-- =====================================================================
-- LLENADO MASIVO: TRABAJO SOCIAL (id_servicios = 4)
-- Sub-registros: becas, exoneracion, fames, gestion_emb
-- =====================================================================

SET NAMES utf8mb4;

-- 0. Limpieza preventiva en orden estricto (hijos primero, padres después)
DELETE b FROM becas b JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = b.id_solicitud_serv WHERE b.fecha_creacion >= '2024-11-01';
DELETE e FROM exoneracion e JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = e.id_solicitud_serv WHERE e.fecha_creacion >= '2024-11-01';

-- Capturar los detalle_patologia involucrados en fames y gestion_emb antes de borrar los hijos
DROP TEMPORARY TABLE IF EXISTS tmp_detalles_ts;
CREATE TEMPORARY TABLE tmp_detalles_ts AS
SELECT id_detalle_patologia FROM fames WHERE fecha_creacion >= '2024-11-01'
UNION
SELECT id_detalle_patologia FROM gestion_emb WHERE fecha_creacion >= '2024-11-01';

-- Borrar los registros hijos de fames y gestion_emb
DELETE f FROM fames f JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = f.id_solicitud_serv WHERE f.fecha_creacion >= '2024-11-01';
DELETE g FROM gestion_emb g JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = g.id_solicitud_serv WHERE g.fecha_creacion >= '2024-11-01';

-- Borrar los detalle_patologia liberados
DELETE dp FROM detalle_patologia dp JOIN tmp_detalles_ts t ON t.id_detalle_patologia = dp.id_detalle_patologia;
DROP TEMPORARY TABLE IF EXISTS tmp_detalles_ts;

-- Borrar las solicitudes de servicio huérfanas de Trabajo Social (id_servicios = 4)
DELETE ss FROM solicitud_de_servicio ss
LEFT JOIN becas b ON b.id_solicitud_serv = ss.id_solicitud_serv
LEFT JOIN exoneracion e ON e.id_solicitud_serv = ss.id_solicitud_serv
LEFT JOIN fames f ON f.id_solicitud_serv = ss.id_solicitud_serv
LEFT JOIN gestion_emb g ON g.id_solicitud_serv = ss.id_solicitud_serv
WHERE ss.id_servicios = 4 AND b.id_becas IS NULL AND e.id_exoneracion IS NULL AND f.id_fames IS NULL AND g.id_gestion IS NULL;

-- 1. Resolver IDs de Trabajadores Sociales
SET @ts_luis  = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '13529764' LIMIT 1), 1);
SET @ts_keila = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '20481637' LIMIT 1), @ts_luis);

-- 2. Resolver IDs de Patologías para FAMES y Embarazadas
SET @pat_gripe      = (SELECT id_patologia FROM patologia WHERE nombre_patologia = 'Gripe' LIMIT 1);
SET @pat_gastritis  = (SELECT id_patologia FROM patologia WHERE nombre_patologia = 'Gastritis aguda' LIMIT 1);
SET @pat_faringitis = (SELECT id_patologia FROM patologia WHERE nombre_patologia = 'Faringoamigdalitis' LIMIT 1);
SET @pat_asma       = (SELECT id_patologia FROM patologia WHERE nombre_patologia = 'Asma bronquial' LIMIT 1);

-- 3. Resolver IDs de Beneficiarios (Hombres y Mujeres para Becas/Exoneraciones/FAMES)
SET @ben_1  = COALESCE((SELECT id_beneficiario FROM beneficiario WHERE cedula = '30995937' LIMIT 1), 1);
SET @ben_3  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '18390420' LIMIT 1);
SET @ben_5  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '23408766' LIMIT 1);
SET @ben_7  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '16427112' LIMIT 1);
SET @ben_9  = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '14445458' LIMIT 1);

-- 4. Resolver IDs de Beneficiarias Femeninas (EXCLUSIVAS para Embarazadas)
SET @ben_fem_1 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '21381247' LIMIT 1); -- Andrea Rodríguez
SET @ben_fem_2 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '26399593' LIMIT 1); -- Gabriela Suárez
SET @ben_fem_3 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '29417939' LIMIT 1); -- Daniela Márquez
SET @ben_fem_4 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '24436285' LIMIT 1); -- Yorgelis Pérez
SET @ben_fem_5 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '27454631' LIMIT 1); -- Luisana Espinoza
SET @ben_fem_6 = (SELECT id_beneficiario FROM beneficiario WHERE cedula = '19472977' LIMIT 1); -- Karla Jiménez


-- =====================================================================
-- SUB-MÓDULO 1: BECAS (6 registros)
-- =====================================================================

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_1, @ts_luis);
INSERT INTO becas (id_solicitud_serv, cta_bcv, direccion_pdf, tipo_banco, fecha_creacion)
VALUES (LAST_INSERT_ID(), '0102012345678901', 'uploads/trabajo_social/planilla_beca_1.pdf', '0102', '2024-11-28');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_3, @ts_luis);
INSERT INTO becas (id_solicitud_serv, cta_bcv, direccion_pdf, tipo_banco, fecha_creacion)
VALUES (LAST_INSERT_ID(), '0134098765432109', 'uploads/trabajo_social/planilla_beca_2.pdf', '0134', '2025-01-15');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_5, @ts_luis);
INSERT INTO becas (id_solicitud_serv, cta_bcv, direccion_pdf, tipo_banco, fecha_creacion)
VALUES (LAST_INSERT_ID(), '0108112233445566', 'uploads/trabajo_social/planilla_beca_3.pdf', '0108', '2025-03-10');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_7, @ts_keila);
INSERT INTO becas (id_solicitud_serv, cta_bcv, direccion_pdf, tipo_banco, fecha_creacion)
VALUES (LAST_INSERT_ID(), '0105665544332211', 'uploads/trabajo_social/planilla_beca_4.pdf', '0105', '2025-05-20');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_9, @ts_keila);
INSERT INTO becas (id_solicitud_serv, cta_bcv, direccion_pdf, tipo_banco, fecha_creacion)
VALUES (LAST_INSERT_ID(), '0191998877665544', 'uploads/trabajo_social/planilla_beca_5.pdf', '0191', '2025-09-12');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_fem_1, @ts_keila);
INSERT INTO becas (id_solicitud_serv, cta_bcv, direccion_pdf, tipo_banco, fecha_creacion)
VALUES (LAST_INSERT_ID(), '0102443322110099', 'uploads/trabajo_social/planilla_beca_6.pdf', '0102', '2026-02-14');


-- =====================================================================
-- SUB-MÓDULO 2: EXONERACIONES (6 registros: 3 Pendientes, 3 Completadas)
-- =====================================================================

-- Exoneración 1 (Completada - Estudio Generado)
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_1, @ts_luis);
INSERT INTO exoneracion (id_solicitud_serv, motivo, otro_motivo, direccion_carta, direccion_estudiose, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_1.pdf', 'uploads/trabajo_social/estudio_se_1.pdf', 'No posee', '2024-12-02');

-- Exoneración 2 (Pendiente de Estudio Socioeconómico)
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_3, @ts_luis);
INSERT INTO exoneracion (id_solicitud_serv, motivo, otro_motivo, direccion_carta, direccion_estudiose, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Paquete de Grado', 'No aplica', 'uploads/trabajo_social/carta_exo_2.pdf', NULL, 'CONAPDIS-091823', '2025-02-18');

-- Exoneración 3 (Completada - Estudio Generado)
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_5, @ts_luis);
INSERT INTO exoneracion (id_solicitud_serv, motivo, otro_motivo, direccion_carta, direccion_estudiose, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Otro', 'Exoneración arancelaria especial', 'uploads/trabajo_social/carta_exo_3.pdf', 'uploads/trabajo_social/estudio_se_3.pdf', 'No posee', '2025-04-22');

-- Exoneración 4 (Pendiente de Estudio Socioeconómico)
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_7, @ts_keila);
INSERT INTO exoneracion (id_solicitud_serv, motivo, otro_motivo, direccion_carta, direccion_estudiose, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_4.pdf', NULL, 'No posee', '2025-06-14');

-- Exoneración 5 (Completada - Estudio Generado)
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_9, @ts_keila);
INSERT INTO exoneracion (id_solicitud_serv, motivo, otro_motivo, direccion_carta, direccion_estudiose, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Paquete de Grado', 'No aplica', 'uploads/trabajo_social/carta_exo_5.pdf', 'uploads/trabajo_social/estudio_se_5.pdf', 'CONAPDIS-772109', '2025-10-08');

-- Exoneración 6 (Pendiente de Estudio Socioeconómico - Octubre 2026)
INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_fem_2, @ts_keila);
INSERT INTO exoneracion (id_solicitud_serv, motivo, otro_motivo, direccion_carta, direccion_estudiose, carnet_discapacidad, fecha_creacion)
VALUES (LAST_INSERT_ID(), 'Inscripción', 'No aplica', 'uploads/trabajo_social/carta_exo_6.pdf', NULL, 'No posee', '2026-10-04');


-- =====================================================================
-- SUB-MÓDULO 3: FAMES (6 registros)
-- =====================================================================

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_1, @ts_luis);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gripe);
SET @det = LAST_INSERT_ID();
INSERT INTO fames (id_solicitud_serv, id_detalle_patologia, tipo_ayuda, otro_tipo, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 'Económica', 'No aplica', '2024-12-10');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_3, @ts_luis);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gastritis);
SET @det = LAST_INSERT_ID();
INSERT INTO fames (id_solicitud_serv, id_detalle_patologia, tipo_ayuda, otro_tipo, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 'Exámenes', 'No aplica', '2025-03-15');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_5, @ts_luis);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_asma);
SET @det = LAST_INSERT_ID();
INSERT INTO fames (id_solicitud_serv, id_detalle_patologia, tipo_ayuda, otro_tipo, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 'Operaciones', 'No aplica', '2025-05-28');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_7, @ts_keila);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_faringitis);
SET @det = LAST_INSERT_ID();
INSERT INTO fames (id_solicitud_serv, id_detalle_patologia, tipo_ayuda, otro_tipo, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 'Otros', 'Insumos y material quirúrgico', '2025-08-14');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_9, @ts_keila);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gastritis);
SET @det = LAST_INSERT_ID();
INSERT INTO fames (id_solicitud_serv, id_detalle_patologia, tipo_ayuda, otro_tipo, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 'Económica', 'No aplica', '2025-11-20');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_fem_3, @ts_keila);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_asma);
SET @det = LAST_INSERT_ID();
INSERT INTO fames (id_solicitud_serv, id_detalle_patologia, tipo_ayuda, otro_tipo, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 'Exámenes', 'No aplica', '2026-03-08');


-- =====================================================================
-- SUB-MÓDULO 4: EMBARAZADAS (6 registros - Solo Género Femenino)
-- =====================================================================

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_fem_1, @ts_luis);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gripe);
SET @det = LAST_INSERT_ID();
INSERT INTO gestion_emb (id_solicitud_serv, id_detalle_patologia, semanas_gest, codigo_patria, serial_patria, estado, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 12, 1029384756, 1987654321, 'Aprobado', '2024-12-18');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_fem_2, @ts_luis);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gastritis);
SET @det = LAST_INSERT_ID();
INSERT INTO gestion_emb (id_solicitud_serv, id_detalle_patologia, semanas_gest, codigo_patria, serial_patria, estado, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 24, 1082736451, 1981273645, 'Aprobado', '2025-02-22');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_fem_3, @ts_luis);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_faringitis);
SET @det = LAST_INSERT_ID();
INSERT INTO gestion_emb (id_solicitud_serv, id_detalle_patologia, semanas_gest, codigo_patria, serial_patria, estado, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 16, NULL, NULL, 'Rechazado', '2025-06-10');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_fem_4, @ts_keila);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gripe);
SET @det = LAST_INSERT_ID();
INSERT INTO gestion_emb (id_solicitud_serv, id_detalle_patologia, semanas_gest, codigo_patria, serial_patria, estado, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 32, 1049281736, 1983029182, 'Aprobado', '2025-09-28');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_fem_5, @ts_keila);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_gastritis);
SET @det = LAST_INSERT_ID();
INSERT INTO gestion_emb (id_solicitud_serv, id_detalle_patologia, semanas_gest, codigo_patria, serial_patria, estado, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 8, 1073829104, 1981920384, 'En Proceso', '2026-04-12');

INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado) VALUES (4, @ben_fem_6, @ts_keila);
INSERT INTO detalle_patologia (id_patologia) VALUES (@pat_asma);
SET @det = LAST_INSERT_ID();
INSERT INTO gestion_emb (id_solicitud_serv, id_detalle_patologia, semanas_gest, codigo_patria, serial_patria, estado, fecha_creacion)
VALUES (LAST_INSERT_ID(), @det, 18, 1092830192, 1984019283, 'En Proceso', '2026-10-06');

-- =====================================================================
-- FIN DEL SCRIPT DE TRABAJO SOCIAL CORREGIDO
-- =====================================================================