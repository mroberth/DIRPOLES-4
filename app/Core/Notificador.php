<?php
namespace App\Core;

use PDO;
use Throwable;

/**
 * app/Core/Notificador.php
 * ---------------------------------------------------------------
 * NOTIFICACIONES en una línea. Elimina el bloque repetido de
 * "crear NotificacionesModel + setear 6 atributos + transacción"
 * que estaba al final de cada operación importante:
 *
 *   ANTES (en cada función):
 *     $notif = new NotificacionesModel();
 *     $notif->__set('titulo', $titulo);
 *     $notif->__set('url', $url);
 *     $notif->__set('tipo', $tipo);
 *     $notif->__set('id_emisor', $_SESSION['id_empleado']);
 *     $notif->__set('id_receptor', $idReceptor);
 *     $notif->__set('leido', 0);
 *     $notif->manejarAccion('crear_notificacion');
 *
 *   AHORA:
 *     Notificador::enviar($idReceptor, $titulo, $url, $tipo);
 *     * GARANTÍAS:
     *   - id_emisor se toma de la sesión automáticamente; en la BD es
     *     NOT NULL con FK a empleado, así que si no hay emisor válido
     *     la notificación NO se envía (log + false).
     *   - Transacción atómica: notificaciones + notificaciones_empleados.
     *   - NUNCA lanza: si falla, deja log y continúa. Igual que Bitacora,
     *     una notificación jamás debe romper la operación principal.
     *
     * ESQUEMA REAL (dirpoles_security, ya existente):
     *   notificaciones(id_notificaciones, titulo, url, tipo, fecha_creacion)
     *   notificaciones_empleados(id_notificaciones_empleados, id_notificaciones,
     *                            id_emisor, id_receptor, leido)
     */
final class Notificador
{
    /**
     * Envía una notificación a un empleado.
     *
     * @param int      $idReceptor Empleado que recibe la notificación.
     * @param string   $titulo     Título corto (máx. 150, se recorta).
     * @param string   $url        Ruta relativa del sistema a la que navegar (ej: "beneficiarios/consultar").
     * @param string   $tipo       Tipo libre para el icono/estilo del frontend (ej: "info", "alerta").
     * @param int|null $idEmisor   Emisor; por defecto el usuario de la sesión.
     */
    public static function enviar(int $idReceptor, string $titulo, string $url, string $tipo, ?int $idEmisor = null): bool
    {
        try {
            $pdo = self::conexion();
            $pdo->beginTransaction();

            // 1. Notificación global (contenido)
            $stmt = $pdo->prepare(
                "INSERT INTO notificaciones (titulo, url, tipo, fecha_creacion)
                 VALUES (:titulo, :url, :tipo, NOW())"
            );
            $stmt->execute([
                ':titulo' => mb_strcut(trim($titulo), 0, 150),
                ':url'    => trim($url),
                ':tipo'   => mb_strcut(trim($tipo), 0, 50),
            ]);
            $idNotificacion = (int) $pdo->lastInsertId();

            // 2. Destinatario (id_emisor es NOT NULL + FK a empleado: si no hay
            // emisor válido, la notificación no se puede crear → se aborta).
            $emisor = $idEmisor ?? ($_SESSION['id_empleado'] ?? null);
            if ($emisor === null) {
                throw new \RuntimeException('No hay id_emisor (sesión ausente) y la columna es NOT NULL.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO notificaciones_empleados (id_notificaciones, id_emisor, id_receptor, leido)
                 VALUES (:id_notificaciones, :id_emisor, :id_receptor, 0)"
            );
            $stmt->bindValue(':id_notificaciones', $idNotificacion, PDO::PARAM_INT);
            $stmt->bindValue(':id_emisor', $emisor, PDO::PARAM_INT);
            $stmt->bindValue(':id_receptor', $idReceptor, PDO::PARAM_INT);
            $stmt->execute();

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Notificación "a prueba de fallos": log y seguir.
            error_log('Notificador::enviar - ' . $e->getMessage() . " | hacia {$idReceptor}: {$titulo}");
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
