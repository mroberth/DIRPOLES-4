-- ============================================================================
-- docs/bd/notificaciones_modulo.sql
-- ----------------------------------------------------------------------------
-- Registra el módulo "Notificaciones" en el RBAC (BD dirpoles_security) y le
-- concede los 4 permisos (Crear/Leer/Editar/Eliminar) a TODOS los roles
-- existentes (tabla tipo_empleado), porque la campana es para todo el que
-- inicia sesión.
--
-- IMPORTANTE: este script NO crea ni modifica las tablas de notificaciones
-- (`notificaciones` y `notificaciones_empleados`): esas YA existen en la BD.
-- Aquí solo se inserta la fila de configuración del módulo en `modulo` y sus
-- permisos en `rol_modulo_permiso`, que es lo que exige el RBAC del esqueleto
-- (Autorizacion::verificar('notificaciones', 'leer') lanza "Módulo desconocido"
-- si la fila no existe).
--
-- Es IDEMPOTENTE: se puede ejecutar varias veces sin duplicar nada.
--
-- Uso:
--   mysql -u <usuario> -p dirpoles_security < docs/bd/notificaciones_modulo.sql
-- ============================================================================

USE dirpoles_security;

-- 1. Registrar el módulo si no existe (nombre con mayúscula inicial,
--    igual que los demás módulos de la tabla: Empleados, Beneficiarios...).
INSERT INTO `modulo` (`nombre`, `descripcion`)
SELECT 'Notificaciones', 'Bandeja de notificaciones del usuario'
WHERE NOT EXISTS (SELECT 1 FROM `modulo` WHERE LOWER(`nombre`) = 'notificaciones');

-- 2. Tomar su id_modulo (recién insertado o ya existente).
SET @id_mod_notificaciones = (SELECT `id_modulo` FROM `modulo` WHERE LOWER(`nombre`) = 'notificaciones' LIMIT 1);

-- 3. Conceder los 4 permisos a todos los roles (tipo_empleado.id_tipo_emp)
--    que aún no los tengan.
INSERT INTO `rol_modulo_permiso` (`id_tipo_emp`, `id_modulo`, `id_permiso`)
SELECT t.`id_tipo_emp`, @id_mod_notificaciones, p.`id_permiso`
FROM `tipo_empleado` t
CROSS JOIN `permiso` p
WHERE NOT EXISTS (
    SELECT 1
    FROM `rol_modulo_permiso` r
    WHERE r.`id_tipo_emp` = t.`id_tipo_emp`
      AND r.`id_modulo`   = @id_mod_notificaciones
      AND r.`id_permiso`  = p.`id_permiso`
);

-- Verificación:
-- SELECT * FROM `modulo` WHERE `nombre` = 'Notificaciones';
-- SELECT r.* FROM `rol_modulo_permiso` r WHERE r.`id_modulo` = @id_mod_notificaciones;