-- ============================================================================
-- docs/bd/login_intentos.sql
-- ----------------------------------------------------------------------------
-- Crea la tabla `login_intentos` (BD dirpoles_security), que persiste el
-- contador de intentos fallidos de inicio de sesión por correo.
--
-- ANTES el contador vivía en $_SESSION['intentos_login'][correo], así que se
-- evadía borrando la cookie de sesión. Persistirlo en BD cierra ese hueco y
-- permite bloquear la cuenta de forma confiable tras MAX_INTENTOS.
--
-- Es IDEMPOTENTE (CREATE TABLE IF NOT EXISTS): se puede ejecutar varias veces.
--
-- Uso:
--   mysql -u <usuario> -p dirpoles_security < docs/bd/login_intentos.sql
-- ============================================================================

USE dirpoles_security;

CREATE TABLE IF NOT EXISTS `login_intentos` (
  `correo` varchar(100) NOT NULL,
  `intentos_fallidos` int(11) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `ultimo_intento` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`correo`),
  KEY `idx_login_intentos_fecha` (`ultimo_intento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
