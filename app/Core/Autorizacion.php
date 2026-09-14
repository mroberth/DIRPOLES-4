<?php
namespace App\Core;

use PDO;
use Throwable;

/**
 * app/Core/Autorizacion.php
 * ---------------------------------------------------------------
 * MOTOR RBAC en una línea. Elimina el boilerplate repetido al
 * inicio de cada función de controlador:
 *
 *   ANTES (en cada función):
 *     $permisosModel = new PermisosModel();
 *     $permisosModel->__set('Modulo', 'beneficiarios');
 *     $permisosModel->__set('Permiso', 'crear');
 *     $permisosModel->__set('Rol', $_SESSION['id_tipo_empleado']);
 *     if (!$permisosModel->manejarAccion('Verificar')) { ... }
 *
 *   AHORA:
 *     Autorizacion::verificar('beneficiarios', 'crear');
 *
 * COMPORTAMIENTO:
 *   - Con permiso  → no hace nada, la ejecución continúa.
 *   - Sin permiso  → lanza ExcepcionApi (ACCESO_DENEGADO, 403),
 *     que el controlador responde con Respuesta::error($e) o el
 *     handler global de index.php convierte en respuesta.
 *
 * (La resolución de nombres → IDs usa las tablas modulo / permiso /
 *  rol_modulo_permiso de dirpoles_security, igual que PermisosModel.)
 */
final class Autorizacion
{
    /** Caché por petición: evita repetir las mismas consultas. */
    private static array $cacheIdsModulo = [];
    private static array $cacheIdsPermiso = [];

    /**
     * Verifica que el rol de la sesión tenga $permiso sobre $modulo.
     * Lanza ExcepcionApi (403) si el permiso no está concedido.
     */
    public static function verificar(string $modulo, string $permiso): void
    {
        $rol = $_SESSION['id_tipo_empleado'] ?? null;
        if ($rol === null) {
            // Sin sesión no hay rol: es un problema de autenticación, no de permisos.
            throw new ExcepcionApi(ErrorCodes::NO_AUTENTICADO, 401, 'Sesión no válida. Inicia sesión de nuevo.');
        }

        $idModulo  = self::idModulo($modulo);
        $idPermiso = self::idPermiso($permiso);

        try {
            $pdo = self::conexion();
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM rol_modulo_permiso
                 WHERE id_tipo_emp = :rol
                   AND id_modulo   = :id_modulo
                   AND id_permiso  = :id_permiso"
            );
            $stmt->execute([
                ':rol'        => (int) $rol,
                ':id_modulo'  => $idModulo,
                ':id_permiso' => $idPermiso,
            ]);
            $concedido = (int) $stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            error_log('Autorizacion::verificar - error de BD: ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron verificar los permisos.');
        }

        if (!$concedido) {
            throw ExcepcionApi::accesoDenegado(
                "No tienes permiso de '{$permiso}' sobre el módulo '{$modulo}'."
            );
        }
    }

    /** ¿Tiene el permiso? Variante booleana para decisiones dentro de la vista/lógica. */
    public static function tiene(string $modulo, string $permiso): bool
    {
        try {
            self::verificar($modulo, $permiso);
            return true;
        } catch (ExcepcionApi) {
            return false;
        }
    }

    // ---------- Internos ----------

    /** Resuelve nombre de módulo → id_modulo (con caché). */
    private static function idModulo(string $nombre): int
    {
        $clave = mb_strtolower(trim($nombre));
        if (isset(self::$cacheIdsModulo[$clave])) {
            return self::$cacheIdsModulo[$clave];
        }

        $stmt = self::conexion()->prepare(
            "SELECT id_modulo FROM modulo WHERE LOWER(nombre) = :nombre LIMIT 1"
        );
        $stmt->execute([':nombre' => $clave]);
        $id = $stmt->fetchColumn();

        if ($id === false) {
            // Error de configuración, no de permisos: el módulo no está registrado.
            error_log("Autorizacion: el módulo '{$nombre}' no existe en la tabla modulo.");
            throw ExcepcionApi::errorInterno("Módulo desconocido: {$nombre}");
        }

        return self::$cacheIdsModulo[$clave] = (int) $id;
    }

    /** Resuelve clave de permiso → id_permiso (con caché). */
    private static function idPermiso(string $clavePermiso): int
    {
        $clave = mb_strtolower(trim($clavePermiso));
        if (isset(self::$cacheIdsPermiso[$clave])) {
            return self::$cacheIdsPermiso[$clave];
        }

        $stmt = self::conexion()->prepare(
            "SELECT id_permiso FROM permiso WHERE LOWER(clave) = :clave LIMIT 1"
        );
        $stmt->execute([':clave' => $clave]);
        $id = $stmt->fetchColumn();

        if ($id === false) {
            error_log("Autorizacion: el permiso '{$clavePermiso}' no existe en la tabla permiso.");
            throw ExcepcionApi::errorInterno("Permiso desconocido: {$clavePermiso}");
        }

        return self::$cacheIdsPermiso[$clave] = (int) $id;
    }

    /** Conexión PDO a la BD de seguridad (mismo patrón que JwtHandler). */
    private static function conexion(): PDO
    {
        static $pdo = null;
        if ($pdo === null) {
            $pdo = new PDO(
                'mysql:host=' . env('DB_HOST', 'localhost') . ';dbname=' . env('DB_SECURITY_NAME') . ';charset=utf8mb4',
                env('DB_SECURITY_USER'),
                env('DB_SECURITY_PASS')
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $pdo;
    }

    private function __construct()
    {
    }
}
