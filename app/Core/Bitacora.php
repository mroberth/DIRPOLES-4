<?php
namespace App\Core;

use PDO;
use Throwable;

/**
 * app/Core/Bitacora.php
 * ---------------------------------------------------------------
 * AUDITORÍA en una línea. Elimina el bloque repetido de
 * "crear BitacoraModel + setear 5 atributos + manejarAccion()"
 * que estaba en cada función de cada controlador:
 *
 *   ANTES (en cada función):
 *     $bitacora = new BitacoraModel();
 *     $bitacora->__set('id_empleado', $_SESSION['id_empleado']);
 *     $bitacora->__set('modulo', 'beneficiarios');
 *     $bitacora->__set('accion', 'Registro');
 *     $bitacora->__set('descripcion', "Se registró al beneficiario X");
 *     $bitacora->__set('fecha', date('Y-m-d H:i:s'));
 *     $bitacora->manejarAccion('registrar_bitacora');
 *
 *   AHORA:
 *     Bitacora::registrar('beneficiarios', 'Registro', "Se registró al beneficiario X");
 *
 * GARANTÍAS:
 *   - id_empleado se toma de la sesión automáticamente.
 *   - fecha la pone la BD con NOW().
 *   - NUNCA lanza: si falla el INSERT, solo deja log en error_log.
 *     La operación de negocio principal nunca se interrumpe por
 *     un fallo de auditoría (mismo criterio que el código original).
 */
final class Bitacora
{
    /**
     * Acciones válidas. DEBEN coincidir EXACTAMENTE con el ENUM real de la
     * columna `bitacora.accion` (ver docs/bd/dirpoles_security.sql). Si se
     * agrega una aquí sin migrar el ENUM, el INSERT falla y se traga (log).
     */
    public const ACCIONES = [
        'Registro', 'Lectura', 'Actualización', 'Eliminación',
        'Inicio de sesión', 'Cierre de sesión', 'Respaldo',
    ];

    /**
     * Registra una entrada en la bitácora.
     *
     * @param string $modulo      Nombre del módulo (máx. 50 caracteres, se recorta).
     * @param string $accion      Una de las acciones de Bitacora::ACCIONES.
     * @param string $descripcion Detalle legible (máx. 255 caracteres, se recorta).
     */
    public static function registrar(string $modulo, string $accion, string $descripcion): bool
    {
        try {
            $idEmpleado = $_SESSION['id_empleado'] ?? null;
            if ($idEmpleado === null) {
                error_log('Bitacora::registrar - sin sesión activa, no se registra: ' . $descripcion);
                return false;
            }

            if (!in_array($accion, self::ACCIONES, true)) {
                error_log("Bitacora::registrar - acción inválida '{$accion}'. Usa una de: " . implode(', ', self::ACCIONES));
                return false;
            }

            $stmt = self::conexion()->prepare(
                "INSERT INTO bitacora (id_empleado, modulo, accion, descripcion, fecha)
                 VALUES (:id_empleado, :modulo, :accion, :descripcion, NOW())"
            );
            return $stmt->execute([
                ':id_empleado' => (int) $idEmpleado,
                ':modulo'      => mb_strcut(trim($modulo), 0, 50),
                ':accion'      => $accion,
                ':descripcion' => mb_strcut(trim($descripcion), 0, 255),
            ]);
        } catch (Throwable $e) {
            // Auditoría "a prueba de fallos": log y seguir.
            error_log('Bitacora::registrar - ' . $e->getMessage() . ' | (' . $modulo . ' / ' . $accion . ')');
            return false;
        }
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
