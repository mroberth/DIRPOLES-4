<?php
// app/Models/BitacoraModel.php

namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;

/**
 * Modelo LECTOR de la bitácora (BD de seguridad).
 *
 * La ESCRITURA de la bitácora la hace el helper App\Core\Bitacora::registrar()
 * (nunca lanza). Este modelo solo consulta: listar con filtros y estadísticas.
 * La bitácora es de solo lectura: no se edita ni se elimina (auditoría).
 */
class BitacoraModel extends SecurityModel
{
    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_empleado':
                $this->atributos['id_empleado'] = max(0, (int) $valor);
                break;

            case 'modulo':
            case 'accion':
            case 'buscar':
                $this->atributos[$nombre] = mb_substr(trim((string) $valor), 0, 100);
                break;

            case 'desde':
            case 'hasta':
                $valor = trim((string) $valor);
                if ($valor !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                    throw ExcepcionApi::validacion("La fecha '{$nombre}' debe tener el formato YYYY-MM-DD.");
                }
                $this->atributos[$nombre] = $valor;
                break;

            default:
                throw ExcepcionApi::validacion("Atributo no reconocido en BitacoraModel: '{$nombre}'.");
        }
    }

    public function __get(string $nombre): mixed
    {
        return $this->atributos[$nombre] ?? null;
    }

    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'listar'  => $this->listar(),
            'filtros' => $this->filtros(),
            'stats'   => $this->stats(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en BitacoraModel: '{$accion}'."),
        };
    }

    private function listar(): array
    {
        try {
            [$where, $params] = $this->condiciones();

            $sql = "SELECT b.id_bitacora, b.modulo,
                           CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                           b.accion, b.descripcion, b.fecha
                    FROM bitacora b
                    INNER JOIN empleado e ON e.id_empleado = b.id_empleado";
            if ($where !== '') {
                $sql .= ' WHERE ' . $where;
            }
            $sql .= ' ORDER BY b.fecha DESC LIMIT 1000';

            $stmt = $this->conn_security->prepare($sql);
            foreach ($params as $clave => $valor) {
                $stmt->bindValue($clave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('BitacoraModel::listar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo consultar la bitácora.');
        }
    }

    /** Opciones para los selects de filtros. */
    private function filtros(): array
    {
        try {
            $modulos = $this->conn_security
                ->query("SELECT DISTINCT modulo FROM bitacora ORDER BY modulo ASC")
                ->fetchAll(PDO::FETCH_COLUMN);

            $acciones = $this->conn_security
                ->query("SELECT DISTINCT accion FROM bitacora ORDER BY accion ASC")
                ->fetchAll(PDO::FETCH_COLUMN);

            $empleados = $this->conn_security
                ->query(
                    "SELECT DISTINCT e.id_empleado, CONCAT(e.nombre, ' ', e.apellido) AS empleado
                     FROM bitacora b
                     INNER JOIN empleado e ON e.id_empleado = b.id_empleado
                     ORDER BY empleado ASC"
                )
                ->fetchAll(PDO::FETCH_ASSOC);

            return ['modulos' => $modulos, 'acciones' => $acciones, 'empleados' => $empleados];
        } catch (Throwable $e) {
            error_log('BitacoraModel::filtros - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los filtros de la bitácora.');
        }
    }

    private function stats(): array
    {
        try {
            $pdo = $this->conn_security;

            $total = (int) $pdo->query("SELECT COUNT(*) FROM bitacora")->fetchColumn();
            $hoy = (int) $pdo->query("SELECT COUNT(*) FROM bitacora WHERE DATE(fecha) = CURDATE()")->fetchColumn();
            $mes = (int) $pdo->query(
                "SELECT COUNT(*) FROM bitacora
                 WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())"
            )->fetchColumn();
            $moduloTop = $pdo->query(
                "SELECT modulo FROM bitacora GROUP BY modulo ORDER BY COUNT(*) DESC LIMIT 1"
            )->fetchColumn();

            return [
                'bitacora_total'   => $total,
                'bitacora_hoy'     => $hoy,
                'bitacora_mes'     => $mes,
                'bitacora_modulo'  => $moduloTop ?: '—',
            ];
        } catch (Throwable $e) {
            error_log('BitacoraModel::stats - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar las estadísticas de la bitácora.');
        }
    }

    /** Construye el WHERE y los parámetros de los filtros activos. */
    private function condiciones(): array
    {
        $where = [];
        $params = [];

        $modulo = (string) ($this->__get('modulo') ?? '');
        $accion = (string) ($this->__get('accion') ?? '');
        $idEmpleado = (int) ($this->__get('id_empleado') ?? 0);
        $desde = (string) ($this->__get('desde') ?? '');
        $hasta = (string) ($this->__get('hasta') ?? '');
        $buscar = (string) ($this->__get('buscar') ?? '');

        if ($modulo !== '') {
            $where[] = 'b.modulo = :modulo';
            $params[':modulo'] = $modulo;
        }
        if ($accion !== '') {
            $where[] = 'b.accion = :accion';
            $params[':accion'] = $accion;
        }
        if ($idEmpleado > 0) {
            $where[] = 'b.id_empleado = :id_empleado';
            $params[':id_empleado'] = $idEmpleado;
        }
        if ($desde !== '') {
            $where[] = 'b.fecha >= :desde';
            $params[':desde'] = $desde . ' 00:00:00';
        }
        if ($hasta !== '') {
            $where[] = 'b.fecha <= :hasta';
            $params[':hasta'] = $hasta . ' 23:59:59';
        }
        if ($buscar !== '') {
            $where[] = '(b.descripcion LIKE :q1 OR b.modulo LIKE :q2 OR b.accion LIKE :q3 '
                     . "OR CONCAT(e.nombre, ' ', e.apellido) LIKE :q4)";
            $like = '%' . $buscar . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        return [implode(' AND ', $where), $params];
    }
}
