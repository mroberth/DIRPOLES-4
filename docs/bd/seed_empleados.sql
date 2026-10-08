-- =====================================================================
-- SEED DE EMPLEADOS — 10 registros (2 por tipo de empleado)
-- Módulo: Empleados (id_modulo 1)
--
--   2 Psicologo (mujeres)          → id_tipo_emp 1
--   2 Medico (1 hombre, 1 mujer)   → id_tipo_emp 2
--   2 Trabajador Social (1 h, 1 m) → id_tipo_emp 3
--   2 Orientador (1 hombre, 1 m.)  → id_tipo_emp 4
--   2 Discapacidad (1 hombre, 1 m.)→ id_tipo_emp 5
--
-- Los datos (nombres, cédulas, teléfonos, correos, direcciones) son
-- SINTÉTICOS pero coherentes con personas que viven en Barquisimeto:
-- NO corresponden a personas reales.
--
-- Respeta las validaciones del módulo (backend EmpleadoModel +
-- frontend dist/js/modulos/empleado/validaciones.js):
--   * nombre/apellido: solo letras, acentos y espacios (2 a 50).
--   * tipo_cedula en (V,E,J,P,G); cedula de 6 a 10 dígitos.
--   * Unicidad GLOBAL de cédula/correo/teléfono contra
--     dirpoles_security.empleado, dirpoles_business.beneficiario y
--     dirpoles_business.proveedores (mismo tipo de documento).
--   * correo válido y <= 50 caracteres (la columna es varchar(50)).
--   * telefono 11 dígitos: 0412|0414|0416|0422|0424|0426 + 7 dígitos.
--   * fecha_nacimiento: válida, no futura y con al menos 15 años.
--   * direccion: 5 a 500 caracteres, solo letras, números, espacios,
--     comas, puntos, guiones y # (sin / ni paréntesis).
--   * estatus 0/1; el tipo de empleado se resuelve POR NOMBRE contra
--     la tabla tipo_empleado (estatus = 1).
--   * RBAC: NO requiere SQL nuevo, los permisos viven en
--     rol_modulo_permiso por id_tipo_emp (ya existentes para 1..5).
--
-- ES IDEMPOTENTE: solo inserta las filas cuya cédula/correo/teléfono
-- todavía no existen en ninguna de las tres tablas.
--
-- CONTRASEÑA (clave): las filas nacen con clave = NULL y se les asigna
-- una contraseña MANUALMENTE desde la UI (Empleados → Editar →
-- Contraseña; en edición es opcional: vacío = no cambiar). El login no
-- funcionará hasta que se asigne.
--
-- TODAS las tablas van NOMBREADAS COMPLETAS (dirpoles_security.* y
-- dirpoles_business.*), así que el script se puede importar con
-- CUALQUIER base seleccionada. Ejecutar con un usuario que tenga
-- permisos sobre ambos esquemas:
--   mysql -u <usuario> -p < docs/bd/seed_empleados.sql
-- o, si prefieres fijar la BD de seguridad:
--   mysql -u <usuario> -p -D dirpoles_security < docs/bd/seed_empleados.sql
--
-- Si el usuario que conecta no tiene permisos de lectura sobre el otro
-- esquema, otórgalos antes:
--   GRANT SELECT ON dirpoles_security.* TO '<usuario>'@'<host>';
--   GRANT SELECT ON dirpoles_business.* TO '<usuario>'@'<host>';
--   FLUSH PRIVILEGES;
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO dirpoles_security.empleado
    (nombre, apellido, tipo_cedula, cedula, correo, telefono,
     id_tipo_empleado, fecha_nacimiento, direccion, clave, estatus, fecha_creacion)
SELECT v.nombre, v.apellido, v.tipo_cedula, v.cedula, v.correo, v.telefono,
       t.id_tipo_emp, v.fecha_nacimiento, v.direccion, NULL, v.estatus, v.fecha_creacion
FROM (
    SELECT 'Yaselis Gabriela' AS nombre, 'Contreras Salas' AS apellido,
           'V' AS tipo_cedula, '14872391' AS cedula,
           'yaselis.contreras21@gmail.com' AS correo, '04147318264' AS telefono,
           'Psicologo' AS tipo_empleado, '1991-03-12' AS fecha_nacimiento,
           'Av. 5 de Julio con calle 14, Urb. Don Rómulo, Barquisimeto' AS direccion,
           1 AS estatus, '2024-11-18' AS fecha_creacion
    UNION ALL SELECT 'María Alejandra','Ríos Rodríguez','V','17543826','maria.alejandra.rios28@gmail.com','04247645193','Psicologo','1989-07-24','Carrera 16 con calle 51, Barrio Belén, Barquisimeto',1,'2025-01-13'
    UNION ALL SELECT 'Carlos Eduardo','Sánchez Bracamonte','V','19632847','carlos.sanchez47@hotmail.com','04167029458','Medico','1985-11-05','Urb. Buen Pastor, calle 12 con Av. Las Américas, Barquisimeto',1,'2024-08-05'
    UNION ALL SELECT 'Yuri Alexandra','Boscán Anzola','V','16754209','yuri.boscan18@gmail.com','04127483621','Medico','1993-04-18','Calle 8 con carrera 17, Sector Centro, Barquisimeto',1,'2025-03-10'
    UNION ALL SELECT 'Luis Armando','Gutiérrez Nieves','V','13529764','luis.gutierrez29@gmail.com','04267195842','Trabajador Social','1988-01-30','Av. Libertador con calle 6, Urb. Sucre, Barquisimeto',1,'2024-09-16'
    UNION ALL SELECT 'Keila Andrea','Montiel Aponte','V','20481637','keila.montiel34@gmail.com','04227562938','Trabajador Social','1994-09-14','Sector El Tránsito, calle 30 con carrera 9, Barquisimeto',1,'2026-09-08'
    UNION ALL SELECT 'Edward José','Urdaneta Briceño','V','18364725','edward.urdaneta52@gmail.com','04147036185','Orientador','1987-06-02','Urb. El Tostao, calle 14 con Av. 5 de Julio, Barquisimeto',1,'2024-12-02'
    UNION ALL SELECT 'Nohelia Alexandra','Quintero Márquez','V','22159408','nohelia.quintero45@gmail.com','04247419627','Orientador','1992-10-27','Carrera 11 con calle 29, Barrio El Maní, Barquisimeto',1,'2025-08-18'
    UNION ALL SELECT 'Yhon Alexander','Guerra Marcano','V','15928374','yhon.guerra37@gmail.com','04127854036','Discapacidad','1990-02-16','Av. Carabobo con calle 15, Urb. Daniel Camacho, Barquisimeto',1,'2025-02-03'
    UNION ALL SELECT 'Marielva Yulieth','Hernández Briceño','V','24671583','marielva.hernandez61@gmail.com','04167268491','Discapacidad','1996-08-08','Calle 28 con carrera 6, Sector Pío Tamayo, Barquisimeto',1,'2026-10-02'
) v
INNER JOIN dirpoles_security.tipo_empleado t
    ON t.tipo = v.tipo_empleado
   AND t.estatus = 1
WHERE NOT EXISTS (
    SELECT 1 FROM (
        SELECT e.tipo_cedula AS tipo, e.cedula AS doc
          FROM dirpoles_security.empleado e
         WHERE e.tipo_cedula IS NOT NULL AND e.cedula IS NOT NULL
        UNION ALL
        SELECT b.tipo_cedula, b.cedula
          FROM dirpoles_business.beneficiario b
         WHERE b.cedula IS NOT NULL
        UNION ALL
        SELECT pr.tipo_documento, pr.num_documento
          FROM dirpoles_business.proveedores pr
    ) x
    WHERE x.tipo = v.tipo_cedula AND x.doc = v.cedula
)
AND NOT EXISTS (
    SELECT 1 FROM (
        SELECT e.correo
          FROM dirpoles_security.empleado e
         WHERE e.correo IS NOT NULL
        UNION ALL
        SELECT b.correo
          FROM dirpoles_business.beneficiario b
         WHERE b.correo IS NOT NULL
        UNION ALL
        SELECT pr.correo
          FROM dirpoles_business.proveedores pr
    ) y
    WHERE y.correo = v.correo
)
AND NOT EXISTS (
    SELECT 1 FROM (
        SELECT e.telefono
          FROM dirpoles_security.empleado e
         WHERE e.telefono IS NOT NULL
        UNION ALL
        SELECT b.telefono
          FROM dirpoles_business.beneficiario b
         WHERE b.telefono IS NOT NULL
        UNION ALL
        SELECT pr.telefono
          FROM dirpoles_business.proveedores pr
    ) z
    WHERE z.telefono = v.telefono
);

-- =====================================================================
-- 1) VERIFICACIÓN (ejecutar a mano después del seed)
-- =====================================================================
--    Total esperado: 4 empleados existentes + 10 nuevos = 14
--      SELECT COUNT(*) FROM dirpoles_security.empleado;
--
--    Los 10 nuevos (esperado: 10 filas con clave = NULL):
--      SELECT e.id_empleado, t.tipo, e.nombre, e.apellido, e.cedula,
--             e.correo, e.telefono, e.fecha_nacimiento, e.clave IS NULL AS sin_clave
--        FROM dirpoles_security.empleado e
--        JOIN dirpoles_security.tipo_empleado t ON t.id_tipo_emp = e.id_tipo_empleado
--       WHERE e.cedula IN ('14872391','17543826','19632847','16754209','13529764',
--                          '20481637','18364725','22159408','15928374','24671583')
--       ORDER BY t.tipo, e.nombre;
--
--    Sin duplicados contra beneficiarios/proveedores (esperado: 0 filas):
--      SELECT e.cedula, e.correo, e.telefono
--        FROM dirpoles_security.empleado e
--        JOIN dirpoles_business.beneficiario b
--          ON (b.tipo_cedula = e.tipo_cedula AND b.cedula = e.cedula)
--          OR b.correo = e.correo OR b.telefono = e.telefono;
--
--    Si el total NO es 14: revisa (a) unicidades ya existentes (los NOT
--    EXISTS omiten la fila) o (b) los nombres de tipo_empleado:
--      SELECT id_tipo_emp, tipo, estatus FROM dirpoles_security.tipo_empleado;
--    (Se usan exactamente: Psicologo, Medico, Trabajador Social,
--     Orientador, Discapacidad.)
--
-- =====================================================================
-- 2) CONTRASEÑAS: se asignan DESDE LA UI (Empleados → Editar →
--    Contraseña). En edición el campo es opcional (vacío = no cambiar),
--    mínimo 8 caracteres con letra, número y carácter especial.
--    Hasta que se asigne, ese empleado no puede iniciar sesión.
-- =====================================================================
