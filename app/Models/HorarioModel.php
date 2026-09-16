<?php
// app/Models/HorarioModel.php

namespace App\Models;

use PDO;
use PDOException;
use App\Core\ExcepcionApi;

class HorarioModel extends BusinessModel
{
    private const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    private array $atributos = [];

    public function __construct()
    {
        parent::__construct();
        $this->Security();
    }

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_horario':
            case 'id_empleado':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion("El {$nombre} no es válido.");
                }
                $this->atributos[$nombre] = (int) $valor;
                break;
            case 'dia_semana':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::DIAS, true)) {
                    throw ExcepcionApi::validacion('El día de la semana no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;
            case 'hora_inicio':
            case 'hora_fin':
                $valor = trim((string) $valor);
                if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $valor)) {
                    throw ExcepcionApi::validacion('La hora debe tener formato HH:MM.');
                }
                $minutos = ((int) substr($valor, 0, 2)) * 60 + (int) substr($valor, 3, 2);
                if ($minutos < 420 || $minutos > 1020) {
                    throw ExcepcionApi::validacion('El horario debe estar entre 07:00 y 17:00.');
                }
                $this->atributos[$nombre] = $valor . ':00';
                break;
            default:
                throw ExcepcionApi::validacion("Atributo no reconocido: '{$nombre}'.");
        }
    }

    public function __get(string $nombre): mixed
    {
        return $this->atributos[$nombre] ?? null;
    }

    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'listar' => $this->listar(),
            'obtener' => $this->obtener(),
            'crear' => $this->crear(),
            'actualizar' => $this->actualizar(),
            'eliminar' => $this->eliminar(),
            'psicologos' => $this->psicologos(),
            'stats' => $this->stats(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en HorarioModel: '{$accion}'."),
        };
    }

    private function validarPsicologo(int $idEmpleado): void
    {
        $stmt = $this->conn_security->prepare(
            "SELECT COUNT(*) FROM empleado e
             INNER JOIN tipo_empleado t ON t.id_tipo_emp = e.id_tipo_empleado
             WHERE e.id_empleado = :id AND e.estatus = 1 AND LOWER(t.tipo) = 'psicologo'"
        );
        $stmt->execute([':id' => $idEmpleado]);
        if ((int) $stmt->fetchColumn() === 0) {
            throw ExcepcionApi::validacion('El empleado seleccionado no es un psicólogo activo.');
        }
    }

    private function validarOrdenHoras(): void
    {
        if ((string) $this->__get('hora_inicio') >= (string) $this->__get('hora_fin')) {
            throw ExcepcionApi::validacion('La hora final debe ser posterior a la hora inicial.');
        }
    }

    private function validarUnicidadDia(?int $idExcluir = null): void
    {
        $sql = 'SELECT COUNT(*) FROM horario WHERE id_empleado = :empleado AND dia_semana = :dia';
        if ($idExcluir !== null) $sql .= ' AND id_horario <> :excluir';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':empleado', (int) $this->__get('id_empleado'), PDO::PARAM_INT);
        $stmt->bindValue(':dia', $this->__get('dia_semana'));
        if ($idExcluir !== null) $stmt->bindValue(':excluir', $idExcluir, PDO::PARAM_INT);
        $stmt->execute();
        if ((int) $stmt->fetchColumn() > 0) {
            throw ExcepcionApi::yaExiste('El psicólogo ya tiene un horario registrado para ese día.');
        }
    }

    private function listar(): array
    {
        $stmt = $this->conn->query(
            "SELECT h.id_horario, h.id_empleado, h.dia_semana,
                    TIME_FORMAT(h.hora_inicio, '%H:%i') AS hora_inicio,
                    TIME_FORMAT(h.hora_fin, '%H:%i') AS hora_fin,
                    CONCAT(e.nombre, ' ', e.apellido) AS psicologo,
                    CONCAT(e.tipo_cedula, '-', e.cedula) AS cedula
             FROM horario h
             INNER JOIN dirpoles_security.empleado e ON e.id_empleado = h.id_empleado
             ORDER BY e.nombre, e.apellido, FIELD(h.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'), h.hora_inicio"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtener(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id_horario, id_empleado, dia_semana,
                    TIME_FORMAT(hora_inicio, '%H:%i') AS hora_inicio,
                    TIME_FORMAT(hora_fin, '%H:%i') AS hora_fin
             FROM horario WHERE id_horario = :id LIMIT 1"
        );
        $stmt->execute([':id' => (int) $this->__get('id_horario')]);
        $horario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$horario) throw ExcepcionApi::noEncontrado('El horario solicitado no existe.');
        return $horario;
    }

    private function crear(): array
    {
        $idEmpleado = (int) $this->__get('id_empleado');
        $this->validarPsicologo($idEmpleado);
        $this->validarOrdenHoras();
        $this->validarUnicidadDia();
        try {
            $stmt = $this->conn->prepare(
                'INSERT INTO horario (id_empleado, dia_semana, hora_inicio, hora_fin)
                 VALUES (:empleado, :dia, :inicio, :fin)'
            );
            $stmt->execute([
                ':empleado' => $idEmpleado,
                ':dia' => $this->__get('dia_semana'),
                ':inicio' => $this->__get('hora_inicio'),
                ':fin' => $this->__get('hora_fin'),
            ]);
            return ['id_horario' => (int) $this->conn->lastInsertId()];
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') throw ExcepcionApi::enUso('El horario está relacionado con otros datos.');
            error_log('HorarioModel::crear - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar el horario.');
        }
    }

    private function actualizar(): array
    {
        $idHorario = (int) $this->__get('id_horario');
        $this->obtener();
        $this->validarPsicologo((int) $this->__get('id_empleado'));
        $this->validarOrdenHoras();
        $this->validarUnicidadDia($idHorario);
        $stmt = $this->conn->prepare(
            'UPDATE horario SET id_empleado = :empleado, dia_semana = :dia,
                    hora_inicio = :inicio, hora_fin = :fin WHERE id_horario = :id'
        );
        $stmt->execute([
            ':empleado' => (int) $this->__get('id_empleado'),
            ':dia' => $this->__get('dia_semana'),
            ':inicio' => $this->__get('hora_inicio'),
            ':fin' => $this->__get('hora_fin'),
            ':id' => $idHorario,
        ]);
        return ['id_horario' => $idHorario];
    }

    private function eliminar(): array
    {
        $this->obtener();
        $stmt = $this->conn->prepare('DELETE FROM horario WHERE id_horario = :id');
        $stmt->execute([':id' => (int) $this->__get('id_horario')]);
        return ['id_horario' => (int) $this->__get('id_horario')];
    }

    private function psicologos(): array
    {
        $stmt = $this->conn_security->query(
            "SELECT e.id_empleado, e.nombre, e.apellido, e.tipo_cedula, e.cedula
             FROM empleado e INNER JOIN tipo_empleado t ON t.id_tipo_emp = e.id_tipo_empleado
             WHERE e.estatus = 1 AND LOWER(t.tipo) = 'psicologo'
             ORDER BY e.nombre, e.apellido"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function stats(): array
    {
        $horas = (float) ($this->conn->query(
            'SELECT COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(hora_fin, hora_inicio))) / 3600, 0) FROM horario'
        )->fetchColumn() ?: 0);
        return [
            'psicologos_con_horario' => (int) $this->conn->query('SELECT COUNT(DISTINCT id_empleado) FROM horario')->fetchColumn(),
            'horas_semanales' => round($horas, 1),
            'horarios_total' => (int) $this->conn->query('SELECT COUNT(*) FROM horario')->fetchColumn(),
        ];
    }
}
