<?php
namespace App\Models;

use App\Models\SecurityModel;
use App\Core\ExcepcionApi;
use PDO;
use Throwable;

class PermisosModel extends SecurityModel
{
    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        $this->atributos[$nombre] = $valor;
    }

    public function __get(string $atributo): mixed
    {
        return $this->atributos[$atributo] ?? null;
    }

    /**
     * MANEJADOR - unica puerta publica del modelo.
     *
     * Regla general del esqueleto:
     *   - Exito  -> retorno normal (array u otra cosa) o void.
     *   - Fallo  -> lanzo ExcepcionApi; el handler global la convierte
     *     en el JSON del contrato. NUNCA devuelvo ["exito"=>false,
     *     "mensaje"=>...].
     *
     * Excepciones validas segun la accion:
     *   - ACCESO_DENEGADO  -> el rol no tiene ese permiso.
     *   - ERROR_INTERNO    -> la BD murio, tiempo fuera, restriccion rota...
     *   - VALIDACION       -> parametros mal formados (uso puntual).
     */
    public function manejarAccion(string $action): mixed
    {
        return match ($action) {
            'Verificar'                  => $this->verificarPermisos(),
            'obtener_modulos'            => $this->obtener_modulos(),
            'tipos_empleados'            => $this->tipos_empleados(),
            'obtenerPermisos'            => $this->obtenerPermisos(),
            'obtener_permisos_por_rol'   => $this->obtener_permisos_por_rol(),
            'guardar_permisos_lote'      => $this->guardar_permisos_lote(),
            'obtenerPermisosSidebar'     => $this->obtenerPermisosSidebar(),
            'mapa_permisos_todos'        => $this->mapa_permisos_todos(),
            'stats'                      => $this->stats(),

            default => throw ExcepcionApi::errorInterno(
                "Accion no valida: {$action}"
            ),
        };
    }

    // ---------- Lecturas del esqueleto (nunca fallan silenciosamente) ----------

    private function obtener_modulos(): array
    {
        $stmt = $this->conn_security->prepare(
            "SELECT id_modulo, nombre FROM modulo ORDER BY id_modulo ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function tipos_empleados(): array
    {
        $stmt = $this->conn_security->prepare(
            "SELECT id_tipo_emp, tipo FROM tipo_empleado
             WHERE estatus = 1
             ORDER BY id_tipo_emp ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerPermisos(): array
    {
        $stmt = $this->conn_security->prepare(
            "SELECT id_permiso, clave FROM permiso ORDER BY id_permiso ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve el listado de módulos visible para el rol actual
     * con permiso de lectura.
     */
    private function obtenerPermisosSidebar(): array
    {
        $stmt = $this->conn_security->prepare(
            "SELECT DISTINCT m.id_modulo, m.nombre AS nombre_modulo, m.descripcion
             FROM rol_modulo_permiso rpm
             JOIN modulo m ON rpm.id_modulo = m.id_modulo
             WHERE rpm.id_tipo_emp = :rol
               AND rpm.id_permiso = 2
             ORDER BY m.id_modulo ASC"
        );
        $stmt->bindValue(':rol', $this->__get('Rol'), PDO::PARAM_INT);
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['id_modulo']] = [
                'nombre'      => $row['nombre_modulo'],
                'descripcion' => $row['descripcion'],
            ];
        }
        return $result;
    }

    /**
     * Devuelve el mapa conocido de permisos para un rol.
     * Exito -> array con el mapa.
     * Fallo -> lanzo errorInterno (jamas devuelve un array de error).
     */
    private function obtener_permisos_por_rol(): array
    {
        $id_tipo_emp = $this->__get('id_tipo_emp');
        if ($id_tipo_emp === null || $id_tipo_emp === '') {
            throw ExcepcionApi::validacion('Falta id_tipo_emp para consultar permisos.');
        }

        $stmt = $this->conn_security->prepare(
            "SELECT id_modulo, id_permiso
             FROM rol_modulo_permiso
             WHERE id_tipo_emp = :id_tipo_emp"
        );
        $stmt->execute([':id_tipo_emp' => (int) $id_tipo_emp]);

        $mapa = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $mapa[(int) $row['id_modulo']][] = (int) $row['id_permiso'];
        }

        return [
            'id_tipo_emp' => (int) $id_tipo_emp,
            'mapa'        => $mapa,
        ];
    }

    /**
     * Persiste el lote de cambios de permisos para uno o varios modulos.
     *
     * Exito -> ["cambios" => ...]
     * Fallo -> lanzo ExcepcionApi (validacion si el payload es malo,
     *          errorInterno si la BD / transaccion falla).
     */
    private function guardar_permisos_lote(): array
    {
        $cambios = $this->__get('cambios');

        if (!
            is_array($cambios)
            || array_filter($cambios, static fn ($a) => is_array($a)) !== $cambios
        ) {
            throw ExcepcionApi::validacion(
                'No se recibieron cambios validos para procesar.'
            );
        }

        if (count($cambios) === 0) {
            throw ExcepcionApi::validacion(
                'No se recibieron cambios para procesar.'
            );
        }

        try {
            $pdo = $this->conn_security;
            $pdo->beginTransaction();

            $stmt_del = $pdo->prepare(
                "DELETE FROM rol_modulo_permiso
                 WHERE id_tipo_emp = :rol AND id_modulo = :mod"
            );
            $stmt_ins = $pdo->prepare(
                "INSERT INTO rol_modulo_permiso
                   (id_tipo_emp, id_modulo, id_permiso)
                 VALUES (:rol, :mod, :perm)"
            );

            foreach ($cambios as $c) {
                if (!
                    isset($c['id_tipo_emp'], $c['id_modulo'], $c['permisos'])
                    || !is_array($c['permisos'])
                ) {
                    $pdo->rollBack();
                    throw ExcepcionApi::validacion(
                        'Datos de cambios incompletos.'
                    );
                }

                $id_tipo_emp = (int) $c['id_tipo_emp'];
                $id_modulo   = (int) $c['id_modulo'];

                $stmt_del->bindValue(
                    ':rol', $id_tipo_emp, PDO::PARAM_INT
                );
                $stmt_del->bindValue(
                    ':mod', $id_modulo, PDO::PARAM_INT
                );
                $stmt_del->execute();

                foreach ($c['permisos'] as $perm) {
                    $stmt_ins->bindValue(
                        ':rol', $id_tipo_emp, PDO::PARAM_INT
                    );
                    $stmt_ins->bindValue(
                        ':mod', $id_modulo, PDO::PARAM_INT
                    );
                    $stmt_ins->bindValue(
                        ':perm', (int) $perm, PDO::PARAM_INT
                    );
                    $stmt_ins->execute();
                }
            }

            $pdo->commit();

            error_log(
                'Permisos guardados correctamente: ' . count($cambios)
                . ' modulos actualizados.'
            );

            return ['cambios' => $cambios];

        } catch (ExcepcionApi $e) {
            $this->deshacerTransaccionSilenciosamente();
            throw $e;

        } catch (Throwable $e) {
            $this->deshacerTransaccionSilenciosamente();

            error_log(
                'Excepcion guardar_permisos_lote: ' . $e->getMessage()
                . "\nPayload: " . json_encode($cambios)
            );

            throw ExcepcionApi::errorInterno(
                'Error inesperado al guardar permisos. Revisa los logs del servidor.'
            );
        }
    }

    /**
     * Rollback defensivo: si la transaccion de la conexion esta abierta,
     * se revierte; si todo ya esta cerrado o la conexion falla, se
     * registra pero no tira mas.
     */
    private function deshacerTransaccionSilenciosamente(): void
    {
        try {
            if ($this->conn_security instanceof PDO
                && $this->conn_security->inTransaction()
            ) {
                $this->conn_security->rollBack();
            }
        } catch (Throwable $_) {
            error_log(
                'PermisosModel: no se pudo hacer rollback limpio.'
            );
        }
    }

    /**
     * Mapa completo: rol x modulo x permisos.
     * Exito -> array del mapa.
     * Fallo -> lanzo errorInterno (nunca devuelve ["exito"=>false]).
     */
    private function mapa_permisos_todos(): array
    {
        $stmt = $this->conn_security->prepare(
            "SELECT id_tipo_emp, id_modulo, id_permiso
             FROM rol_modulo_permiso
             ORDER BY id_tipo_emp ASC, id_modulo ASC, id_permiso ASC"
        );
        $stmt->execute();

        $mapa = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $rid = (int) $r['id_tipo_emp'];
            $mid = (int) $r['id_modulo'];
            $pid = (int) $r['id_permiso'];

            $mapa[$rid][$mid][] = $pid;
        }

        return $mapa;
    }

    /** Tarjetas de resumen del módulo de permisos. */
    private function stats(): array
    {
        try {
            $pdo = $this->conn_security;

            $roles = (int) $pdo->query("SELECT COUNT(DISTINCT id_tipo_emp) FROM rol_modulo_permiso")->fetchColumn();
            $total = (int) $pdo->query("SELECT COUNT(*) FROM rol_modulo_permiso")->fetchColumn();

            $moduloMasUsado = $pdo->query(
                "SELECT m.nombre
                 FROM rol_modulo_permiso rpm
                 INNER JOIN modulo m ON m.id_modulo = rpm.id_modulo
                 GROUP BY rpm.id_modulo, m.nombre
                 ORDER BY COUNT(*) DESC
                 LIMIT 1"
            )->fetchColumn();

            $accionMasFrecuente = $pdo->query(
                "SELECT p.clave
                 FROM rol_modulo_permiso rpm
                 INNER JOIN permiso p ON p.id_permiso = rpm.id_permiso
                 GROUP BY rpm.id_permiso, p.clave
                 ORDER BY COUNT(*) DESC
                 LIMIT 1"
            )->fetchColumn();

            return [
                'permisos_roles'  => $roles,
                'permisos_total'  => $total,
                'permisos_modulo' => $moduloMasUsado ?: '—',
                'permisos_accion' => $accionMasFrecuente ? ucfirst($accionMasFrecuente) : '—',
            ];
        } catch (Throwable $e) {
            error_log('PermisosModel::stats - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar las estadísticas de permisos.');
        }
    }

    /**
     * VERIFICACI ON DE PERMISOS (usada por Autorizacion::verificar),
     * y tambien por controladores que no usan Autorizacion.
     *
     * Contrato:
     *   - true  -> el rol TIENE el permiso.
     *   - false -> el rol NO TIENE el permiso.
     *
     * Fallos tecnicos:
     *   No se devuelven false confusamente. Si la consulta falla por
     *   la base de datos, se loguea el detalle real y se lanza
     *   ExcepcionApi::errorInterno(), para que el handler global
     *   responda 500 con el contrato y no se confunda con "no tienes
     *   permiso".
     */
    private function verificarPermisos(): bool
    {
        $nombreModulo = $this->__get('Modulo');
        $clavePermiso = $this->__get('Permiso');
        $idRol         = $this->__get('Rol');

        if (!is_string($nombreModulo) || trim($nombreModulo) === '') {
            error_log(
                'PermisosModel: Modulo vacio al verificar permisos.'
            );
            return false;
        }

        if (!is_string($clavePermiso) || trim($clavePermiso) === '') {
            error_log(
                'PermisosModel: Permiso vacio al verificar permisos.'
            );
            return false;
        }

        if ($idRol === null || $idRol === '') {
            error_log(
                'PermisosModel: Rol vacio al verificar permisos.'
            );
            return false;
        }

        try {
            $idModulo = $this->obtenerIdModuloPorNombre($nombreModulo);
            if ($idModulo === null) {
                error_log(
                    "PermisosModel: modulo desconocido '{$nombreModulo}'."
                );
                return false;
            }

            $idPermiso = $this->obtenerIdPermisoPorClave($clavePermiso);
            if ($idPermiso === null) {
                error_log(
                    "PermisosModel: permiso desconocido '{$clavePermiso}'."
                );
                return false;
            }

            $stmt = $this->conn_security->prepare(
                "SELECT COUNT(*) FROM rol_modulo_permiso
                 WHERE id_tipo_emp = :rol
                   AND id_modulo   = :id_modulo
                   AND id_permiso  = :permiso"
            );
            $stmt->execute([
                ':rol'        => (int) $idRol,
                ':id_modulo' => $idModulo,
                ':permiso'   => $idPermiso,
            ]);

            return (int) $stmt->fetchColumn() > 0;

        } catch (Throwable $e) {
            error_log(
                'PermisosModel: error de BD en verificarPermisos: '
                . get_class($e) . ': ' . $e->getMessage()
                . ' en ' . $e->getFile() . ':' . $e->getLine()
            );

            throw ExcepcionApi::errorInterno(
                'No se pudo verificar el permiso.'
            );
        }
    }

    // ---------- Internos ----------

    /** Resuelve nombre de modulo -> id_modulo (NULL si no existe). */
    private function obtenerIdModuloPorNombre(string $nombre): ?int
    {
        $stmt = $this->conn_security->prepare(
            "SELECT id_modulo FROM modulo WHERE nombre = :nombre LIMIT 1"
        );
        $stmt->execute([':nombre' => $nombre]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    /** Resuelve clave de permiso -> id_permiso (NULL si no existe). */
    private function obtenerIdPermisoPorClave(string $clave): ?int
    {
        $stmt = $this->conn_security->prepare(
            "SELECT id_permiso FROM permiso WHERE clave = :clave LIMIT 1"
        );
        $stmt->execute([':clave' => $clave]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }
}