-- =====================================================================
-- LLENADO MASIVO: INVENTARIO MÉDICO Y KARDEX (id_modulo 9)
-- Tablas involucradas: insumos, inventario_medico
-- =====================================================================

SET NAMES utf8mb4;

-- 0. Limpieza preventiva de insumos del seed (preserva el insumo #1 si está en uso)
DELETE FROM inventario_medico WHERE id_insumo > 1 OR descripcion LIKE '%Stock inicial%' OR descripcion LIKE '%Registro de prueba%';
DELETE FROM insumos WHERE id_insumo > 1;

-- Actualizar/Asegurar el Insumo #1 existente (Acetaminofén 500mg)
UPDATE insumos 
SET nombre_insumo = 'Acetaminofén 500mg',
    tipo_insumo = 'Medicamento',
    cantidad = 15,
    estatus = 'Disponible',
    fecha_vencimiento = '2027-08-20'
WHERE id_insumo = 1;

-- 1. Resolver IDs de Médicos / Administradores para el Kardex
SET @med_carlos = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '19632847' LIMIT 1), 1);
SET @med_yuri   = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE cedula = '16754209' LIMIT 1), @med_carlos);

-- 2. Insertar Insumos Razonables del Dispensario
-- Insumo 2: Ibuprofeno 400mg (Bajo stock / Crítico: 8 unidades)
INSERT INTO insumos (id_presentacion, nombre_insumo, descripcion, tipo_insumo, fecha_vencimiento, fecha_creacion, cantidad, estatus)
VALUES (2, 'Ibuprofeno 400mg', 'Cápsulas analgésicas y antiinflamatorias.', 'Medicamento', '2027-11-15', '2025-01-10', 8, 'Disponible');
SET @ins_2 = LAST_INSERT_ID();
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_2, @med_carlos, '2025-01-10 09:00:00', 'Registro', 0, 'Nuevo registro de insumo');
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_2, @med_carlos, '2025-01-10 09:15:00', 'Entrada', 8, 'Stock inicial dispensario');

-- Insumo 3: Alcohol Antiséptico 70% (Líquida - 12 frascos)
INSERT INTO insumos (id_presentacion, nombre_insumo, descripcion, tipo_insumo, fecha_vencimiento, fecha_creacion, cantidad, estatus)
VALUES (4, 'Alcohol Antiséptico 70%', 'Frasco de 500ml para desinfección de superficies y piel.', 'Desinfectante', '2028-04-30', '2025-01-15', 12, 'Disponible');
SET @ins_3 = LAST_INSERT_ID();
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_3, @med_yuri, '2025-01-15 10:00:00', 'Registro', 0, 'Nuevo registro de insumo');
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_3, @med_yuri, '2025-01-15 10:10:00', 'Entrada', 12, 'Dotación de material desinfectante');

-- Insumo 4: Gasas Estériles 7.5x7.5cm (Material Médico - 25 sobres)
INSERT INTO insumos (id_presentacion, nombre_insumo, descripcion, tipo_insumo, fecha_vencimiento, fecha_creacion, cantidad, estatus)
VALUES (5, 'Gasas Estériles 7.5x7.5cm', 'Sobres individuales de gasa estéril para curaciones básicas.', 'Material Médico', '2027-12-01', '2025-02-01', 25, 'Disponible');
SET @ins_4 = LAST_INSERT_ID();
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_4, @med_carlos, '2025-02-01 08:30:00', 'Registro', 0, 'Nuevo registro de insumo');
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_4, @med_carlos, '2025-02-01 08:45:00', 'Entrada', 25, 'Caja de material de curación');

-- Insumo 5: Jeringas Desechables 5ml (Material Médico - Crítico: 5 unidades)
INSERT INTO insumos (id_presentacion, nombre_insumo, descripcion, tipo_insumo, fecha_vencimiento, fecha_creacion, cantidad, estatus)
VALUES (5, 'Jeringas Desechables 5ml', 'Jeringas descartables con aguja 21G para aplicación parenteral.', 'Material Médico', '2027-09-15', '2025-02-10', 5, 'Disponible');
SET @ins_5 = LAST_INSERT_ID();
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_5, @med_yuri, '2025-02-10 11:00:00', 'Registro', 0, 'Nuevo registro de insumo');
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_5, @med_yuri, '2025-02-10 11:15:00', 'Entrada', 5, 'Lote de jeringas parenterales');

-- Insumo 6: Solución Fisiológica 0.9% 500ml (Insumo de Emergencia - Crítico: 4 frascos)
INSERT INTO insumos (id_presentacion, nombre_insumo, descripcion, tipo_insumo, fecha_vencimiento, fecha_creacion, cantidad, estatus)
VALUES (4, 'Solución Fisiológica 0.9%', 'Frasco de 500ml para hidratación y lavado de heridas.', 'Insumo de Emergencia', '2027-06-30', '2025-03-05', 4, 'Disponible');
SET @ins_6 = LAST_INSERT_ID();
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_6, @med_carlos, '2025-03-05 14:00:00', 'Registro', 0, 'Nuevo registro de insumo');
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_6, @med_carlos, '2025-03-05 14:20:00', 'Entrada', 4, 'Dotación de soluciones parenterales');

-- Insumo 7: Omeprazol 20mg (Agotado: 0 unidades)
INSERT INTO insumos (id_presentacion, nombre_insumo, descripcion, tipo_insumo, fecha_vencimiento, fecha_creacion, cantidad, estatus)
VALUES (2, 'Omeprazol 20mg', 'Cápsulas protectoras gástricas para tratamiento de gastritis.', 'Medicamento', '2027-05-10', '2025-03-15', 0, 'Agotado');
SET @ins_7 = LAST_INSERT_ID();
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_7, @med_yuri, '2025-03-15 09:30:00', 'Registro', 0, 'Nuevo registro de insumo');

-- Insumo 8: Vendas Elásticas 10cm (Material Médico - 6 unidades)
INSERT INTO insumos (id_presentacion, nombre_insumo, descripcion, tipo_insumo, fecha_vencimiento, fecha_creacion, cantidad, estatus)
VALUES (5, 'Vendas Elásticas 10cm', 'Rollos de venda elástica para inmovilización de esguinces.', 'Material Médico', '2028-10-10', '2025-04-01', 6, 'Disponible');
SET @ins_8 = LAST_INSERT_ID();
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_8, @med_carlos, '2025-04-01 10:00:00', 'Registro', 0, 'Nuevo registro de insumo');
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_8, @med_carlos, '2025-04-01 10:15:00', 'Entrada', 6, 'Adquisición de vendas elásticas');

-- Insumo 9: Amoxicilina 500mg (Por Vencer: vence el 28 de octubre de 2026)
INSERT INTO insumos (id_presentacion, nombre_insumo, descripcion, tipo_insumo, fecha_vencimiento, fecha_creacion, cantidad, estatus)
VALUES (2, 'Amoxicilina 500mg', 'Cápsulas antibióticas de amplio espectro.', 'Medicamento', '2026-10-28', '2025-05-10', 10, 'Disponible');
SET @ins_9 = LAST_INSERT_ID();
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_9, @med_yuri, '2025-05-10 15:00:00', 'Registro', 0, 'Nuevo registro de insumo');
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_9, @med_yuri, '2025-05-10 15:20:00', 'Entrada', 10, 'Donación institucional de antibióticos');

-- Insumo 10: Diclofenac Sódico Ampollas (Vencido: venció en mayo 2026)
INSERT INTO insumos (id_presentacion, nombre_insumo, descripcion, tipo_insumo, fecha_vencimiento, fecha_creacion, cantidad, estatus)
VALUES (4, 'Diclofenac Sódico Ampollas', 'Ampollas inyectables de 75mg/3ml para analgesia profunda.', 'Medicamento', '2026-05-15', '2025-01-05', 0, 'Vencido');
SET @ins_10 = LAST_INSERT_ID();
INSERT INTO inventario_medico (id_insumo, id_empleado, fecha_movimiento, tipo_movimiento, cantidad, descripcion)
VALUES (@ins_10, @med_carlos, '2025-01-05 11:00:00', 'Registro', 0, 'Nuevo registro de insumo');

-- =====================================================================
-- FIN DEL SCRIPT DE INVENTARIO MÉDICO
-- =====================================================================