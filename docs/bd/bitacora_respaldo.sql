-- ============================================================================
-- docs/bd/bitacora_respaldo.sql
-- ----------------------------------------------------------------------------
-- Agrega la acción 'Respaldo' al ENUM de `bitacora.accion` (BD dirpoles_security),
-- para poder auditar las descargas de respaldo de base de datos.
--
-- Es IDEMPOTENTE (MODIFY con la lista completa): se puede ejecutar varias veces.
--
-- Uso:
--   mysql -u <usuario> -p dirpoles_security < docs/bd/bitacora_respaldo.sql
-- ============================================================================

USE dirpoles_security;

ALTER TABLE `bitacora`
  MODIFY COLUMN `accion`
  ENUM('Registro','Lectura','Actualización','Eliminación','Inicio de sesión','Cierre de sesión','Respaldo')
  NOT NULL;
