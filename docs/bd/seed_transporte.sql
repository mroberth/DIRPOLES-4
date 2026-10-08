-- =====================================================================
-- LLENADO MASIVO: TRANSPORTE Y FLOTA VEHICULAR (id_modulo 13)
-- Tablas involucradas: vehiculos, rutas, proveedores, repuestos_vehiculos,
--                      inventario_repuestos, asignaciones_rutas,
--                      mantenimiento_vehiculos, repuestos_mantenimiento
-- =====================================================================

SET NAMES utf8mb4;

-- 0. Limpieza preventiva en orden seguro por Foreign Keys
DELETE FROM inventario_repuestos;
DELETE FROM repuestos_mantenimiento;
DELETE FROM repuestos_vehiculos;
DELETE FROM mantenimiento_vehiculos;
DELETE FROM asignaciones_rutas;
DELETE FROM rutas;
DELETE FROM vehiculos;
DELETE FROM proveedores WHERE num_documento IN ('J-30495821-0', 'J-40123987-5', 'J-12345678-9');

-- 1. Insertar Chofer secundario en dirpoles_security si no existe
INSERT INTO dirpoles_security.empleado (nombre, apellido, tipo_cedula, cedula, correo, telefono, id_tipo_empleado, fecha_nacimiento, direccion, estatus, fecha_creacion)
SELECT 'Pedro', 'Bermúdez', 'V', '15890123', 'pedro.chofer@gmail.com', '04245112233', 8, '1985-05-14', 'Av. 20 con calle 42 Barquisimeto', 1, '2025-01-15'
WHERE NOT EXISTS (SELECT 1 FROM dirpoles_security.empleado WHERE cedula = '15890123');

-- Resolver IDs de Choferes
SET @emp_chofer1 = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE id_tipo_empleado = 8 LIMIT 1), 4);
SET @emp_chofer2 = COALESCE((SELECT id_empleado FROM dirpoles_security.empleado WHERE id_tipo_empleado = 8 AND id_empleado != @emp_chofer1 LIMIT 1), @emp_chofer1);

-- 2. Insertar Proveedores de Repuestos y Mantenimiento
INSERT INTO proveedores (tipo_documento, num_documento, nombre, telefono, correo, direccion, estatus, fecha_creacion)
VALUES ('J', 'J-30495821-0', 'Distribuidora Yutong Lara, C.A.', '0251-4458899', 'ventas@yutonglara.com.ve', 'Av. Las Industrias Calle 5 Barquisimeto', 'Activo', '2025-01-10 09:00:00');
SET @prov_yutong = LAST_INSERT_ID();

INSERT INTO proveedores (tipo_documento, num_documento, nombre, telefono, correo, direccion, estatus, fecha_creacion)
VALUES ('J', 'J-40123987-5', 'Autopartes y Mantenimiento Barquisimeto, F.P.', '0251-2334455', 'contacto@autobarquisimeto.com', 'Av. Venezuela con Calle 29', 'Activo', '2025-01-15 10:00:00');
SET @prov_autop = LAST_INSERT_ID();


-- =====================================================================
-- 3. VEHÍCULOS INSTITUCIONALES (5 Unidades Realistas para UPTAEB)
-- =====================================================================

-- Vehículo 1: Bus Yutong Grande (Activo)
INSERT INTO vehiculos (placa, modelo, tipo, fecha_adquisicion, estado)
VALUES ('A01AB2C', 'Yutong ZK6118H', 'Autobús', '2024-03-15', 'Activo');
SET @veh_1 = LAST_INSERT_ID();

-- Vehículo 2: Bus Encava Mediano (Activo)
INSERT INTO vehiculos (placa, modelo, tipo, fecha_adquisicion, estado)
VALUES ('A02CD3E', 'Encava ENT-610', 'Autobús', '2024-06-20', 'Activo');
SET @veh_2 = LAST_INSERT_ID();

-- Vehículo 3: Bus Yutong Corto (En Mantenimiento)
INSERT INTO vehiculos (placa, modelo, tipo, fecha_adquisicion, estado)
VALUES ('A03EF4G', 'Yutong ZK6852', 'Autobús', '2024-09-10', 'Mantenimiento');
SET @veh_3 = LAST_INSERT_ID();

-- Vehículo 4: Camioneta Operativa (Activo)
INSERT INTO vehiculos (placa, modelo, tipo, fecha_adquisicion, estado)
VALUES ('A04GH5I', 'Chevrolet LUV D-Max', 'Camioneta', '2025-01-15', 'Activo');
SET @veh_4 = LAST_INSERT_ID();

-- Vehículo 5: Automóvil Institucional (Inactivo)
INSERT INTO vehiculos (placa, modelo, tipo, fecha_adquisicion, estado)
VALUES ('A05IJ6K', 'Toyota Corolla', 'Automóvil', '2025-02-01', 'Inactivo');
SET @veh_5 = LAST_INSERT_ID();


-- =====================================================================
-- 4. RUTAS DE TRANSPORTE UNIVERSITARIO
-- =====================================================================

INSERT INTO rutas (nombre_ruta, trayectoria, tipo_ruta, horario_salida, horario_llegada, punto_partida, punto_destino, estatus, fecha_creacion)
VALUES ('Ruta 1 - Tamaca / El Cují', 'UPTAEB - Av. Intercomunal Barquisimeto-Duaca - El Cují - Tamaca', 'Urbana', '06:30:00', '08:00:00', 'Sede UPTAEB', 'Sector Tamaca Centro', 'Activa', '2025-01-15 08:00:00');
SET @rut_1 = LAST_INSERT_ID();

INSERT INTO rutas (nombre_ruta, trayectoria, tipo_ruta, horario_salida, horario_llegada, punto_partida, punto_destino, estatus, fecha_creacion)
VALUES ('Ruta 2 - Cabudare / Palavecino', 'UPTAEB - Av. Ribereña - Distribuidor Tarabana - Plaza Bolívar Cabudare', 'Suburbana', '06:45:00', '08:15:00', 'Sede UPTAEB', 'Plaza Bolívar de Cabudare', 'Activa', '2025-01-15 08:30:00');
SET @rut_2 = LAST_INSERT_ID();

INSERT INTO rutas (nombre_ruta, trayectoria, tipo_ruta, horario_salida, horario_llegada, punto_partida, punto_destino, estatus, fecha_creacion)
VALUES ('Ruta 3 - Oeste / La Carucieña', 'UPTAEB - Av. Florencio Jiménez - Pueblo Nuevo - La Carucieña', 'Urbana', '07:00:00', '08:30:00', 'Sede UPTAEB', 'Redoma de La Carucieña', 'Activa', '2025-01-15 09:00:00');
SET @rut_3 = LAST_INSERT_ID();

INSERT INTO rutas (nombre_ruta, trayectoria, tipo_ruta, horario_salida, horario_llegada, punto_partida, punto_destino, estatus, fecha_creacion)
VALUES ('Ruta 4 - Quíbor / Traslado Especial', 'UPTAEB - Carretera Lara-Zulia - Entrada Quíbor', 'Extraurbana', '06:00:00', '08:00:00', 'Sede UPTAEB', 'Plaza Bolívar de Quíbor', 'Inactiva', '2025-02-01 10:00:00');
SET @rut_4 = LAST_INSERT_ID();


-- =====================================================================
-- 5. ASIGNACIONES DE RUTAS (Sin Solapamientos del mismo Chofer/Día)
-- =====================================================================

INSERT INTO asignaciones_rutas (id_ruta, id_vehiculo, id_empleado, fecha_asignacion, estatus)
VALUES (@rut_1, @veh_1, @emp_chofer1, '2025-05-10', 'Inactiva');

INSERT INTO asignaciones_rutas (id_ruta, id_vehiculo, id_empleado, fecha_asignacion, estatus)
VALUES (@rut_2, @veh_2, @emp_chofer2, '2025-05-12', 'Inactiva');

INSERT INTO asignaciones_rutas (id_ruta, id_vehiculo, id_empleado, fecha_asignacion, estatus)
VALUES (@rut_1, @veh_1, @emp_chofer1, '2026-10-01', 'Activa');
SET @asig_3 = LAST_INSERT_ID();

INSERT INTO asignaciones_rutas (id_ruta, id_vehiculo, id_empleado, fecha_asignacion, estatus)
VALUES (@rut_2, @veh_2, @emp_chofer2, '2026-10-02', 'Activa');
SET @asig_4 = LAST_INSERT_ID();

INSERT INTO asignaciones_rutas (id_ruta, id_vehiculo, id_empleado, fecha_asignacion, estatus)
VALUES (@rut_3, @veh_4, @emp_chofer1, '2026-10-05', 'Activa');
SET @asig_5 = LAST_INSERT_ID();


-- =====================================================================
-- 6. REPUESTOS Y KARDEX DE INVENTARIO DE TRANSPORTE
-- =====================================================================

-- Repuesto 1: Filtro de Aceite Diésel Yutong (Stock: 6)
INSERT INTO repuestos_vehiculos (nombre, descripcion, cantidad, id_proveedor, fecha_creacion, estatus)
VALUES ('Filtro de Aceite Diésel Yutong', 'Filtro para motor Yutong ZK6118H', 6, @prov_yutong, '2025-01-20', 'Disponible');
SET @rep_1 = LAST_INSERT_ID();
INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_1, @emp_chofer1, '0', 'Registro', 'Alta inicial de repuesto', '2025-01-20 09:00:00');
INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_1, @emp_chofer1, '8', 'Entrada', 'Compra a proveedor', '2025-01-20 09:15:00');

-- Repuesto 2: Filtro de Combustible Yutong (Stock: 3 -> Bajo Stock < 5)
INSERT INTO repuestos_vehiculos (nombre, descripcion, cantidad, id_proveedor, fecha_creacion, estatus)
VALUES ('Filtro de Combustible Yutong', 'Filtro primario de trampa de agua', 3, @prov_yutong, '2025-02-01', 'Disponible');
SET @rep_2 = LAST_INSERT_ID();
INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_2, @emp_chofer1, '0', 'Registro', 'Alta inicial de repuesto', '2025-02-01 10:00:00');
INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_2, @emp_chofer1, '3', 'Entrada', 'Compra a proveedor', '2025-02-01 10:15:00');

-- Repuesto 3: Liga de Freno DOT4 1L (Stock: 8)
INSERT INTO repuestos_vehiculos (nombre, descripcion, cantidad, id_proveedor, fecha_creacion, estatus)
VALUES ('Liga de Freno DOT4 1L', 'Envase de 1 litro de fluido de frenos', 8, @prov_autop, '2025-02-10', 'Disponible');
SET @rep_3 = LAST_INSERT_ID();
INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_3, @emp_chofer1, '0', 'Registro', 'Alta inicial de repuesto', '2025-02-10 11:00:00');
INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_3, @emp_chofer1, '8', 'Entrada', 'Compra a proveedor', '2025-02-10 11:15:00');

-- Repuesto 4: Correa de Alternador Encava (Stock: 2 -> Bajo Stock < 5)
INSERT INTO repuestos_vehiculos (nombre, descripcion, cantidad, id_proveedor, fecha_creacion, estatus)
VALUES ('Correa de Alternador Encava', 'Correa acanalada para motor ISUZU/Encava', 2, @prov_autop, '2025-02-15', 'Disponible');
SET @rep_4 = LAST_INSERT_ID();
INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_4, @emp_chofer1, '0', 'Registro', 'Alta inicial de repuesto', '2025-02-15 08:30:00');
INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_4, @emp_chofer1, '3', 'Entrada', 'Compra a proveedor', '2025-02-15 08:45:00');

-- Repuesto 5: Batería 800Ah 12V Heavy Duty (Stock: 0 -> Agotado)
INSERT INTO repuestos_vehiculos (nombre, descripcion, cantidad, id_proveedor, fecha_creacion, estatus)
VALUES ('Batería 800Ah 12V Heavy Duty', 'Batería de alto amperaje para autobús', 0, @prov_autop, '2025-03-01', 'Agotado');
SET @rep_5 = LAST_INSERT_ID();
INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_5, @emp_chofer1, '0', 'Registro', 'Alta inicial de repuesto', '2025-03-01 09:00:00');


-- =====================================================================
-- 7. MANTENIMIENTO DE VEHÍCULOS Y USO DE REPUESTOS
-- =====================================================================

-- Mantenimiento Correctivo para Vehículo 3 (Yutong ZK6852)
INSERT INTO mantenimiento_vehiculos (id_vehiculo, tipo, fecha, descripcion)
VALUES (@veh_3, 'Correctivo', '2026-10-02', 'Reemplazo de filtro de aceite por saturación y sustitución de correa de alternador agrietada.');
SET @mant_1 = LAST_INSERT_ID();

INSERT INTO repuestos_mantenimiento (id_mantenimiento, id_repuesto, cantidad)
VALUES (@mant_1, @rep_1, '2');

INSERT INTO repuestos_mantenimiento (id_mantenimiento, id_repuesto, cantidad)
VALUES (@mant_1, @rep_4, '1');

-- Registrar consumo en el Kardex
INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_1, @emp_chofer1, '2', 'Salida', 'Uso en mantenimiento rutinario', '2026-10-02 10:00:00');

INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento, fecha_movimiento)
VALUES (@rep_4, @emp_chofer1, '1', 'Salida', 'Uso en mantenimiento rutinario', '2026-10-02 10:05:00');
