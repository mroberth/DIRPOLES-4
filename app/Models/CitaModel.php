<?php
// app/Models/CitaModel.php

namespace App\Models;

use PDO;
use DateTime;
use PDOException;
use App\Core\ExcepcionApi;

class CitaModel extends BusinessModel
{
    private const ESTADO_PENDIENTE = 1;
    private const ESTADOS_VALIDOS = [1, 2, 3, 4, 5];
    private const DIAS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    private array $atributos = [];

    public function __construct()
    {
        parent::__construct();
        $this->Security();
    }

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_cita':
            case 'id_beneficiario':
            case 'id_empleado':
            case 'id_usuario':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion("El {$nombre} no es válido.");
                }
                $this->atributos[$nombre] = (int) $valor;
                break;

            case 'fecha':
                $valor = trim((string) $valor);
                $fecha = DateTime::createFromFormat('Y-m-d', $valor);
                if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
                    throw ExcepcionApi::validacion('La fecha de la cita no es válida.');
                }
                if ($fecha < new DateTime('today')) {
                    throw ExcepcionApi::validacion('La fecha de la cita no puede ser pasada.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'hora':
                $valor = trim((string) $valor);
                if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $valor)) {
                    throw ExcepcionApi::validacion('La hora de la cita no es válida.');
                }
                $this->atributos[$nombre] = substr($valor, 0, 5) . ':00';
                break;

            case 'estatus':
                $valor = (int) $valor;
                if (!in_array($valor, self::ESTADOS_VALIDOS, true)) {
                    throw ExcepcionApi::validacion('El estado de la cita no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'tipo_empleado':
                $this->atributos[$nombre] = trim((string) $valor);
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
            'listar'          => $this->listar(),
            'obtener'         => $this->obtener(),
            'crear'           => $this->crear(),
            'actualizar'      => $this->actualizar(),
            'eliminar'        => $this->eliminar(),
            'actualizar_estado' => $this->actualizarEstado(),
            'psicologos'      => $this->psicologos(),
            'beneficiarios'   => $this->beneficiarios(),
            'estados'         => $this->estados(),
            'disponibilidad'  => $this->disponibilidad(),
            'horario'         => $this->horario(),
            'stats'           => $this->stats(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en CitaModel: '{$accion}'."),
        };
    }

    private function esAdministrador(): bool
    {
        return in_array($this->__get('tipo_empleado'), ['Administrador', 'Superusuario'], true);
    }

    private function asegurarPsicologoSesion(): void
    {
        if (!$this->esAdministrador() && $this->__get('tipo_empleado') !== 'Psicologo') {
            throw ExcepcionApi::accesoDenegado('Solo los empleados psicólogos pueden gestionar citas.');
        }
    }

    private function asegurarAlcanceCita(int $idCita): array
    {
        $sql = "SELECT c.id_cita, c.id_beneficiario, c.id_empleado, c.fecha, c.hora, c.estatus
                FROM cita c
                WHERE c.id_cita = :id";
        if (!$this->esAdministrador()) {
            $sql .= ' AND c.id_empleado = :id_usuario';
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idCita, PDO::PARAM_INT);
        if (!$this->esAdministrador()) {
            $stmt->bindValue(':id_usuario', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cita) {
            throw ExcepcionApi::noEncontrado('La cita no existe o no está disponible para tu usuario.');
        }
        return $cita;
    }

    private function listar(): array
    {
        $this->asegurarPsicologoSesion();
        $sql = "SELECT c.id_cita, c.fecha, c.hora,
                       DATE_FORMAT(c.fecha, '%d/%m/%Y') AS fecha_formateada,
                       TIME_FORMAT(c.hora, '%H:%i') AS hora_formateada,
                       c.id_beneficiario, c.id_empleado, c.estatus,
                       b.nombres AS beneficiario_nombres, b.apellidos AS beneficiario_apellidos,
                       CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                       CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                       CONCAT(e.nombre, ' ', e.apellido) AS psicologo,
                       CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                       CONCAT(e.tipo_cedula, '-', e.cedula) AS cedula_psicologo,
                       ec.nombre AS nombre_estado
                FROM cita c
                INNER JOIN beneficiario b ON b.id_beneficiario = c.id_beneficiario
                INNER JOIN dirpoles_security.empleado e ON e.id_empleado = c.id_empleado
                INNER JOIN estado_cita ec ON ec.id_estado = c.estatus";
        if (!$this->esAdministrador()) {
            $sql .= ' WHERE c.id_empleado = :id_usuario';
        }
        $sql .= ' ORDER BY c.fecha DESC, c.hora DESC, c.id_cita DESC';
        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrador()) {
            $stmt->bindValue(':id_usuario', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtener(): array
    {
        $cita = $this->asegurarAlcanceCita((int) $this->__get('id_cita'));
        $stmt = $this->conn->prepare(
            "SELECT c.id_cita, c.fecha, c.hora, c.id_beneficiario, c.id_empleado, c.estatus,
                    CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                    CONCAT(e.nombre, ' ', e.apellido) AS psicologo,
                    CONCAT(e.tipo_cedula, '-', e.cedula) AS cedula_psicologo,
                    ec.nombre AS nombre_estado
             FROM cita c
             INNER JOIN beneficiario b ON b.id_beneficiario = c.id_beneficiario
             INNER JOIN dirpoles_security.empleado e ON e.id_empleado = c.id_empleado
             INNER JOIN estado_cita ec ON ec.id_estado = c.estatus
             WHERE c.id_cita = :id"
        );
        $stmt->execute([':id' => $cita['id_cita']]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: $cita;
    }

    private function crear(): array
    {
        $this->asegurarPsicologoSesion();
        $idEmpleado = $this->esAdministrador()
            ? (int) $this->__get('id_empleado')
            : (int) $this->__get('id_usuario');
        $this->validarPsicologo($idEmpleado);
        $this->validarBeneficiario((int) $this->__get('id_beneficiario'));
        $this->validarDisponibilidad($idEmpleado, null);

        try {
            $stmt = $this->conn->prepare(
                'INSERT INTO cita (fecha, hora, id_beneficiario, id_empleado, estatus, fecha_creacion)
                 VALUES (:fecha, :hora, :beneficiario, :empleado, :estatus, CURRENT_DATE())'
            );
            $stmt->execute([
                ':fecha' => $this->__get('fecha'),
                ':hora' => $this->__get('hora'),
                ':beneficiario' => (int) $this->__get('id_beneficiario'),
                ':empleado' => $idEmpleado,
                ':estatus' => self::ESTADO_PENDIENTE,
            ]);
            return ['id_cita' => (int) $this->conn->lastInsertId(), 'id_empleado' => $idEmpleado, 'estatus' => self::ESTADO_PENDIENTE];
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw ExcepcionApi::enUso('No se pudo registrar la cita porque uno de sus datos ya no está disponible.');
            }
            error_log('CitaModel::crear - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar la cita.');
        }
    }

    private function actualizar(): array
    {
        $this->asegurarPsicologoSesion();
        $actual = $this->asegurarAlcanceCita((int) $this->__get('id_cita'));
        $idEmpleado = $this->esAdministrador() ? (int) $this->__get('id_empleado') : (int) $actual['id_empleado'];
        $idBeneficiario = $this->esAdministrador() ? (int) $this->__get('id_beneficiario') : (int) $actual['id_beneficiario'];
        $this->validarPsicologo($idEmpleado);
        $this->validarBeneficiario($idBeneficiario);
        $this->validarDisponibilidad($idEmpleado, (int) $actual['id_cita']);

        $stmt = $this->conn->prepare(
            'UPDATE cita SET fecha = :fecha, hora = :hora, id_beneficiario = :beneficiario,
                    id_empleado = :empleado, estatus = :estatus WHERE id_cita = :id'
        );
        $stmt->execute([
            ':fecha' => $this->__get('fecha'),
            ':hora' => $this->__get('hora'),
            ':beneficiario' => $idBeneficiario,
            ':empleado' => $idEmpleado,
            ':estatus' => $this->__get('estatus') ?? (int) $actual['estatus'],
            ':id' => (int) $actual['id_cita'],
        ]);
        return ['id_cita' => (int) $actual['id_cita'], 'id_empleado' => $idEmpleado, 'id_beneficiario' => $idBeneficiario];
    }

    private function eliminar(): array
    {
        $this->asegurarPsicologoSesion();
        $cita = $this->asegurarAlcanceCita((int) $this->__get('id_cita'));
        $stmt = $this->conn->prepare('DELETE FROM cita WHERE id_cita = :id');
        $stmt->execute([':id' => (int) $cita['id_cita']]);
        return ['id_cita' => (int) $cita['id_cita']];
    }

    private function actualizarEstado(): array
    {
        $this->asegurarPsicologoSesion();
        $cita = $this->asegurarAlcanceCita((int) $this->__get('id_cita'));
        $stmt = $this->conn->prepare('UPDATE cita SET estatus = :estatus WHERE id_cita = :id');
        $stmt->execute([':estatus' => (int) $this->__get('estatus'), ':id' => (int) $cita['id_cita']]);
        return ['id_cita' => (int) $cita['id_cita'], 'estatus' => (int) $this->__get('estatus')];
    }

    private function psicologos(): array
    {
        $this->asegurarPsicologoSesion();
        if (!$this->esAdministrador()) {
            $stmt = $this->conn_security->prepare(
                "SELECT id_empleado, nombre, apellido, tipo_cedula, cedula
                 FROM empleado WHERE id_empleado = :id AND estatus = 1 LIMIT 1"
            );
            $stmt->execute([':id' => (int) $this->__get('id_usuario')]);
        } else {
            $stmt = $this->conn_security->query(
                "SELECT e.id_empleado, e.nombre, e.apellido, e.tipo_cedula, e.cedula
                 FROM empleado e INNER JOIN tipo_empleado t ON t.id_tipo_emp = e.id_tipo_empleado
                 WHERE e.estatus = 1 AND LOWER(t.tipo) = 'psicologo'
                 ORDER BY e.nombre, e.apellido"
            );
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function beneficiarios(): array
    {
        $this->asegurarPsicologoSesion();
        $stmt = $this->conn->query(
            "SELECT id_beneficiario, nombres, apellidos, tipo_cedula, cedula
             FROM beneficiario WHERE estatus = 1 ORDER BY nombres, apellidos"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function estados(): array
    {
        $this->asegurarPsicologoSesion();
        $stmt = $this->conn->query(
            'SELECT id_estado, nombre, descripcion FROM estado_cita WHERE es_activo = 1 ORDER BY id_estado'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function horario(): array
    {
        $this->asegurarPsicologoSesion();
        $idEmpleado = $this->esAdministrador() ? (int) $this->__get('id_empleado') : (int) $this->__get('id_usuario');
        $this->validarPsicologo($idEmpleado);
        $stmt = $this->conn->prepare(
            "SELECT dia_semana, TIME_FORMAT(hora_inicio, '%H:%i') AS hora_inicio,
                    TIME_FORMAT(hora_fin, '%H:%i') AS hora_fin
             FROM horario WHERE id_empleado = :id
             ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'), hora_inicio"
        );
        $stmt->execute([':id' => $idEmpleado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function disponibilidad(): array
    {
        $this->asegurarPsicologoSesion();
        $idEmpleado = $this->esAdministrador() ? (int) $this->__get('id_empleado') : (int) $this->__get('id_usuario');
        $this->validarPsicologo($idEmpleado);
        $this->validarDisponibilidad($idEmpleado, $this->__get('id_cita') ? (int) $this->__get('id_cita') : null);
        return ['disponible' => true];
    }

    private function stats(): array
    {
        $this->asegurarPsicologoSesion();
        $where = $this->esAdministrador() ? '' : ' WHERE id_empleado = :id_usuario';
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(estatus = 1) AS pendientes,
                    SUM(estatus = 2) AS confirmadas,
                    SUM(estatus = 3) AS atendidas,
                    SUM(estatus IN (4, 5)) AS cerradas
             FROM cita{$where}"
        );
        if (!$this->esAdministrador()) {
            $stmt->bindValue(':id_usuario', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return array_map(static fn ($valor) => (int) ($valor ?? 0), $fila);
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

    private function validarBeneficiario(int $idBeneficiario): void
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM beneficiario WHERE id_beneficiario = :id AND estatus = 1');
        $stmt->execute([':id' => $idBeneficiario]);
        if ((int) $stmt->fetchColumn() === 0) {
            throw ExcepcionApi::noEncontrado('El beneficiario seleccionado no existe o está inactivo.');
        }
    }

    private function validarDisponibilidad(int $idEmpleado, ?int $idExcluir): void
    {
        $fecha = (string) $this->__get('fecha');
        $hora = (string) $this->__get('hora');
        $dia = self::DIAS[(int) (new DateTime($fecha))->format('w')];
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM horario
             WHERE id_empleado = :empleado AND dia_semana = :dia
               AND hora_inicio <= :hora_inicio
               AND ADDTIME(:hora_fin_base, '01:00:00') <= hora_fin"
        );
        $stmt->execute([
            ':empleado' => $idEmpleado,
            ':dia' => $dia,
            ':hora_inicio' => $hora,
            ':hora_fin_base' => $hora,
        ]);
        if ((int) $stmt->fetchColumn() === 0) {
            throw ExcepcionApi::validacion('La hora seleccionada está fuera del horario del psicólogo.');
        }
        if ((int) substr($hora, 3, 2) % 30 !== 0) {
            throw ExcepcionApi::validacion('Las citas deben iniciar en punto o a media hora.');
        }

        $sql = "SELECT COUNT(*) FROM cita
                WHERE id_empleado = :empleado AND fecha = :fecha AND estatus <> 4
                                    AND NOT (ADDTIME(hora, '01:00:00') <= :hora_inicio
                                                     OR hora >= ADDTIME(:hora_fin_base, '01:00:00'))";
        if ($idExcluir !== null) {
            $sql .= ' AND id_cita <> :id_excluir';
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':empleado', $idEmpleado, PDO::PARAM_INT);
        $stmt->bindValue(':fecha', $fecha);
        $stmt->bindValue(':hora_inicio', $hora);
        $stmt->bindValue(':hora_fin_base', $hora);
        if ($idExcluir !== null) {
            $stmt->bindValue(':id_excluir', $idExcluir, PDO::PARAM_INT);
        }
        $stmt->execute();
        if ((int) $stmt->fetchColumn() > 0) {
            throw ExcepcionApi::yaExiste('El psicólogo ya tiene una cita en ese horario.');
        }
    }
}
