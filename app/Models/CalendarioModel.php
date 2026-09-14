<?php
namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;

/**
 * app/Models/CalendarioModel.php
 * ---------------------------------------------------------------
 * Calendario PERSONAL del empleado (BD de negocio, tabla
 * `eventos_calendario_personal`). Cada empleado solo puede ver/editar
 * SUS eventos: todas las consultas filtran por id_empleado (de la sesión).
 *
 * Patrón del esqueleto:
 *   - __set() valida y lanza ExcepcionApi::validacion().
 *   - manejarAccion() es la única puerta pública.
 *   - Los métodos privados hacen SQL y lanzan ExcepcionApi.
 */
class CalendarioModel extends BusinessModel
{
    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_empleado':
            case 'id_evento':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion("El atributo {$nombre} debe ser un entero positivo.");
                }
                $this->atributos[$nombre] = (int) $valor;
                break;

            case 'titulo':
                $titulo = trim((string) $valor);
                if ($titulo === '') {
                    throw ExcepcionApi::validacion('El título del evento es obligatorio.');
                }
                if (mb_strlen($titulo) > 100) {
                    throw ExcepcionApi::validacion('El título no puede superar 100 caracteres.');
                }
                $this->atributos['titulo'] = $titulo;
                break;

            case 'descripcion':
                $this->atributos['descripcion'] = trim((string) $valor);
                break;

            case 'fecha':
                $fecha = str_replace('T', ' ', trim((string) $valor));
                if ($fecha !== '' && strlen($fecha) === 16) {
                    $fecha .= ':00'; // datetime-local -> Y-m-d H:i:s
                }
                if ($fecha === '' || strtotime($fecha) === false) {
                    throw ExcepcionApi::validacion('La fecha del evento no es válida.');
                }
                $this->atributos['fecha'] = date('Y-m-d H:i:s', strtotime($fecha));
                break;
        }
    }

    public function __get(string $atributo): mixed
    {
        return $this->atributos[$atributo] ?? null;
    }

    /** Única puerta pública (despachador). */
    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'obtener'   => $this->obtener(),
            'agregar'   => $this->agregar(),
            'modificar' => $this->modificar(),
            'eliminar'  => $this->eliminar(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en CalendarioModel: '{$accion}'."),
        };
    }

    /** Eventos del empleado (para FullCalendar). */
    private function obtener(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id_evento, titulo, descripcion, fecha
                 FROM eventos_calendario_personal
                 WHERE id_empleado = :id_empleado
                 ORDER BY fecha ASC"
            );
            $stmt->bindValue(':id_empleado', $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('CalendarioModel::obtener - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los eventos del calendario.');
        }
    }

    private function agregar(): int
    {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO eventos_calendario_personal (id_empleado, titulo, descripcion, fecha, fecha_creacion)
                 VALUES (:id_empleado, :titulo, :descripcion, :fecha, NOW())"
            );
            $stmt->execute([
                ':id_empleado' => $this->__get('id_empleado'),
                ':titulo'      => $this->__get('titulo'),
                ':descripcion' => $this->__get('descripcion') ?? '',
                ':fecha'       => $this->__get('fecha'),
            ]);
            return (int) $this->conn->lastInsertId();
        } catch (Throwable $e) {
            error_log('CalendarioModel::agregar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo guardar el evento.');
        }
    }

    private function modificar(): int
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE eventos_calendario_personal
                 SET titulo = :titulo, descripcion = :descripcion, fecha = :fecha
                 WHERE id_evento = :id_evento AND id_empleado = :id_empleado"
            );
            $stmt->execute([
                ':titulo'      => $this->__get('titulo'),
                ':descripcion' => $this->__get('descripcion') ?? '',
                ':fecha'       => $this->__get('fecha'),
                ':id_evento'   => $this->__get('id_evento'),
                ':id_empleado' => $this->__get('id_empleado'),
            ]);
            return $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('CalendarioModel::modificar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar el evento.');
        }
    }

    private function eliminar(): int
    {
        try {
            $stmt = $this->conn->prepare(
                "DELETE FROM eventos_calendario_personal
                 WHERE id_evento = :id_evento AND id_empleado = :id_empleado"
            );
            $stmt->execute([
                ':id_evento'   => $this->__get('id_evento'),
                ':id_empleado' => $this->__get('id_empleado'),
            ]);
            return $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('CalendarioModel::eliminar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar el evento.');
        }
    }
}
