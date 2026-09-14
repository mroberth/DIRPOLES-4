<?php
namespace App\Models;

use PDO;
use App\Core\ExcepcionApi;

/**
 * app/Models/NotificacionesModel.php
 * ---------------------------------------------------------------
 * Módulo de NOTIFICACIONES (bandeja del usuario). Reconstruido desde
 * el sistema antiguo (DIRPOLES_4) adaptado a las reglas del esqueleto:
 *
 *   - __set() valida y lanza ExcepcionApi::validacion().
 *   - manejarAccion() es la única puerta pública.
 *   - Los métodos privados hacen SQL y LANZAN ExcepcionApi;
 *     NUNCA devuelven ['estado' => 'error'].
 *   - LIMIT/OFFSET siempre con bindValue(..., PDO::PARAM_INT).
 *   - Extiende SecurityModel → usa $this->conn_security.
 *
 * La ESCRITURA de notificaciones (crear) NO vive aquí: la hace el
 * helper transversal App\Core\Notificador::enviar() en una línea.
 * Este modelo solo gestiona la BANDEJA del empleado autenticado:
 * listar, contar no leídas, marcar leídas y eliminar.
 */
class NotificacionesModel extends SecurityModel
{
    private $atributos = [];

    // ---------- Capa de validación (se ejecuta en CADA asignación) ----------
    public function __set($nombre, $valor)
    {
        switch ($nombre) {
            case 'id_notif':
            case 'id_empleado':
                $this->exigirEntero($nombre, $valor, 1);
                $valor = (int) $valor;
                break;

            case 'ultimoId':
            case 'offset':
                $this->exigirEntero($nombre, $valor, 0);
                $valor = (int) $valor;
                break;

            case 'limit':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]) === false) {
                    throw ExcepcionApi::validacion('limit debe ser un entero entre 1 y 50.');
                }
                $valor = (int) $valor;
                break;
        }

        $this->atributos[$nombre] = $valor;
    }

    /**
     * Validación entera estricta: filter_var devuelve el int válido o false.
     * IMPORTANTE: NUNCA usar !filter_var(...) — un cero legítimo (ej. el
     * ultimoId inicial del SSE, u offset=0) es falsy y lanzaría un falso
     * error en cada iteración del stream (bug real que colapsaba el SSE).
     */
    private function exigirEntero(string $nombre, $valor, int $minimo): void
    {
        if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => $minimo]]) === false) {
            throw ExcepcionApi::validacion("El atributo {$nombre} debe ser un entero mayor o igual a {$minimo}.");
        }
    }

    public function __get($atributo)
    {
        return $this->atributos[$atributo] ?? null;
    }

    // ---------- Despachador (única puerta pública) ----------
    public function manejarAccion($accion)
    {
        switch ($accion) {
            case 'listar':              return $this->listar();
            case 'contar':              return $this->contarNoLeidas();
            case 'marcar_leida':        return $this->marcarLeida();
            case 'marcar_todas_leidas': return $this->marcarTodasLeidas();
            case 'eliminar':            return $this->eliminar();
            case 'eliminar_todas':      return $this->eliminarTodas();
            case 'nuevas_sse':          return $this->nuevasSSE();
            case 'reconectar':          return $this->reconectar();
            default:
                throw ExcepcionApi::errorInterno("Acción no válida: {$accion}");
        }
    }

    // ---------- Acciones (SQL + reglas) ----------

    /**
     * Bandeja del usuario: no leídas + últimas 20 notificaciones.
     *
     * @return array{unread_count:int, notifications:array}
     */
    private function listar(): array
    {
        try {
            $stmt = $this->conn_security->prepare(
                "SELECT ne.id_notificaciones_empleados AS id,
                        n.titulo,
                        n.url,
                        n.tipo,
                        ne.leido,
                        CONCAT(e.nombre, ' ', e.apellido) AS nombre_empleado,
                        TIMESTAMPDIFF(MINUTE, n.fecha_creacion, NOW()) AS time_ago
                 FROM notificaciones_empleados ne
                 INNER JOIN notificaciones n ON ne.id_notificaciones = n.id_notificaciones
                 LEFT JOIN empleado e ON ne.id_emisor = e.id_empleado
                 WHERE ne.id_receptor = :id_empleado
                 ORDER BY n.fecha_creacion DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':id_empleado', $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 20), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),  PDO::PARAM_INT);
            $stmt->execute();

            return [
                'unread_count'  => $this->contarNoLeidas(),
                'notifications' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (\PDOException $e) {
            throw $this->mapearErrorBd($e, 'Error al listar las notificaciones.');
        }
    }

    /** Total de notificaciones sin leer del usuario. */
    private function contarNoLeidas(): int
    {
        try {
            $stmt = $this->conn_security->prepare(
                "SELECT COUNT(*) FROM notificaciones_empleados
                 WHERE id_receptor = :id_empleado AND leido = 0"
            );
            $stmt->bindValue(':id_empleado', $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw $this->mapearErrorBd($e, 'Error al contar las notificaciones.');
        }
    }

    /**
     * Marca UNA notificación como leída. El WHERE incluye id_receptor para
     * que un empleado solo pueda marcar las suyas. Devuelve filas afectadas.
     */
    private function marcarLeida(): int
    {
        try {
            $stmt = $this->conn_security->prepare(
                "UPDATE notificaciones_empleados
                 SET leido = 1
                 WHERE id_notificaciones_empleados = :id_notif
                   AND id_receptor = :id_empleado
                   AND leido = 0"
            );
            $stmt->bindValue(':id_notif',    $this->__get('id_notif'),    PDO::PARAM_INT);
            $stmt->bindValue(':id_empleado', $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw $this->mapearErrorBd($e, 'Error al marcar la notificación como leída.');
        }
    }

    /** Marca TODAS las notificaciones del usuario como leídas. */
    private function marcarTodasLeidas(): int
    {
        try {
            $stmt = $this->conn_security->prepare(
                "UPDATE notificaciones_empleados
                 SET leido = 1
                 WHERE id_receptor = :id_empleado AND leido = 0"
            );
            $stmt->bindValue(':id_empleado', $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw $this->mapearErrorBd($e, 'Error al marcar las notificaciones como leídas.');
        }
    }

    /** Elimina UNA notificación del usuario (solo las suyas). */
    private function eliminar(): int
    {
        try {
            $stmt = $this->conn_security->prepare(
                "DELETE FROM notificaciones_empleados
                 WHERE id_notificaciones_empleados = :id_notif
                   AND id_receptor = :id_empleado"
            );
            $stmt->bindValue(':id_notif',    $this->__get('id_notif'),    PDO::PARAM_INT);
            $stmt->bindValue(':id_empleado', $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw $this->mapearErrorBd($e, 'Error al eliminar la notificación.');
        }
    }

    /** Vacía la bandeja del usuario. */
    private function eliminarTodas(): int
    {
        try {
            $stmt = $this->conn_security->prepare(
                "DELETE FROM notificaciones_empleados WHERE id_receptor = :id_empleado"
            );
            $stmt->bindValue(':id_empleado', $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw $this->mapearErrorBd($e, 'Error al vaciar la bandeja de notificaciones.');
        }
    }

    /**
     * Notificaciones nuevas para el streaming SSE: las no leídas con
     * id mayor al último que el cliente ya conoce (reanuda por ultimoId).
     */
    private function nuevasSSE(): array
    {
        try {
            $stmt = $this->conn_security->prepare(
                "SELECT ne.id_notificaciones_empleados AS id,
                        n.titulo,
                        n.url,
                        n.tipo,
                        ne.leido,
                        CONCAT(e.nombre, ' ', e.apellido) AS nombre_empleado,
                        TIMESTAMPDIFF(MINUTE, n.fecha_creacion, NOW()) AS time_ago
                 FROM notificaciones_empleados ne
                 INNER JOIN notificaciones n ON ne.id_notificaciones = n.id_notificaciones
                 LEFT JOIN empleado e ON ne.id_emisor = e.id_empleado
                 WHERE ne.id_receptor = :id_empleado
                   AND ne.id_notificaciones_empleados > :ultimoId
                   AND ne.leido = 0
                 ORDER BY n.fecha_creacion DESC
                 LIMIT 10"
            );
            $stmt->bindValue(':id_empleado', $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->bindValue(':ultimoId',     $this->__get('ultimoId'),     PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw $this->mapearErrorBd($e, 'Error al consultar notificaciones nuevas (SSE).');
        }
    }

    // ---------- Helpers privados ----------

    /**
     * Fuerza una nueva conexión a la BD de seguridad.
     *
     * El streaming SSE mantiene una conexión PDO abierta hasta 1 hora. Si el
     * servidor MySQL se reinicia (o cierra el socket), la siguiente consulta
     * falla con "2006 MySQL server has gone away". Aquí se descarta la
     * conexión muerta y se abre una nueva para seguir el loop.
     */
    private function reconectar(): bool
    {
        try {
            $this->conn_security = null;
            $this->Security(); // Database::Security() reabre si es null
            return true;
        } catch (\Throwable $e) {
            error_log('NotificacionesModel::reconectar - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Traduce errores técnicos de MySQL a ExcepcionApi entendible.
     * 23000 = constraint violation (duplicado / FK en uso).
     */
    private function mapearErrorBd(\PDOException $e, string $mensaje): ExcepcionApi
    {
        error_log('NotificacionesModel: ' . $e->getMessage());
        if ((string) $e->getCode() === '23000') {
            return ExcepcionApi::enUso('El registro está relacionado con otros datos.');
        }
        return ExcepcionApi::errorInterno($mensaje);
    }
}