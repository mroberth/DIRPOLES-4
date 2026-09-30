-- ============================================================================
-- docs/bd/transporte_asignaciones_autoincrement.sql
-- ----------------------------------------------------------------------------
-- La tabla `asignaciones_rutas` (BD dirpoles_business) nació en el dump SIN
-- AUTO_INCREMENT en su llave primaria, por lo que todo INSERT que no incluya
-- `id_asignacion` falla con:
--   SQLSTATE[HY000]: 1364 Field 'id_asignacion' doesn't have a default value
-- (todos los demás IDs del módulo de Transporte sí lo tienen).
--
-- Es IDEMPOTENTE (MODIFY sin fijar el contador: conserva el valor actual):
-- se puede ejecutar varias veces.
--
-- Uso:
--   mysql -u <usuario> -p dirpoles_business < docs/bd/transporte_asignaciones_autoincrement.sql
-- ============================================================================

USE dirpoles_business;

ALTER TABLE `asignaciones_rutas`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;
