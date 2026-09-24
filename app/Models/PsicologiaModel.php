<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Core\ExcepcionApi;

class PsicologiaModel extends BusinessModel
{
    private const TIPOS_CONSULTA = ['Diagnóstico', 'Retiro temporal', 'Cambio de carrera'];

    /**
     * Patología "Sin patología general" ya registrada en la BD. Se usa para
     * los tipos de consulta que no manejan patología (Retiro temporal y
     * Cambio de carrera), porque `consulta_psicologica.id_detalle_patologia`
     * no puede quedar nulo.
     */
    private const ID_PATOLOGIA_SIN_APLICAR = 3;

    private array $atributos = [];

    public function __construct()
    {
        parent::__construct();
        $this->Security();
    }

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_psicologia':
            case 'id_solicitud_serv':
            case 'id_beneficiario':
            case 'id_empleado':
            case 'id_usuario':
            case 'id_patologia':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion("El {$nombre} no es válido.");
                }
                $this->atributos[$nombre] = (int) $valor;
                break;

            case 'tipo_empleado':
                $this->atributos[$nombre] = trim((string) $valor);
                break;

            case 'tipo_consulta':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::TIPOS_CONSULTA, true)) {
                    throw ExcepcionApi::validacion('El tipo de consulta no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'diagnostico':
            case 'tratamiento_gen':
            case 'motivo_retiro':
            case 'observaciones':
                $valor = trim((string) $valor);
                if (mb_strlen($valor) > 5000) {
                    throw ExcepcionApi::validacion("El campo {$nombre} no puede superar 5000 caracteres.");
                }
                $this->atributos[$nombre] = $valor === '' ? null : $valor;
                break;

            case 'duracion_retiro':
                $valor = trim((string) $valor);
                if (mb_strlen($valor) > 50) {
                    throw ExcepcionApi::validacion('La duración del retiro no puede superar 50 caracteres.');
                }
                $this->atributos[$nombre] = $valor === '' ? null : $valor;
                break;

            case 'motivo_cambio':
                $valor = trim((string) $valor);
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El motivo del cambio no puede superar 100 caracteres.');
                }
                $this->atributos[$nombre] = $valor === '' ? null : $valor;
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
            'patologias' => $this->patologias(),
            'beneficiarios' => $this->beneficiarios(),
            'listar' => $this->listar(),
            'obtener' => $this->obtener(),
            'crear' => $this->crear(),
            'actualizar' => $this->actualizar(),
            'eliminar' => $this->eliminar(),
            'stats' => $this->stats(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en PsicologiaModel: '{$accion}'."),
        };
    }

    private function esAdministrativo(): bool
    {
        return in_array($this->__get('tipo_empleado'), ['Administrador', 'Superusuario'], true);
    }

    private function validarTipo(): void
    {
        $tipo = $this->__get('tipo_consulta');
        $requeridos = match ($tipo) {
            'Diagnóstico' => ['id_patologia', 'diagnostico'],
            'Retiro temporal' => ['motivo_retiro', 'duracion_retiro'],
            'Cambio de carrera' => ['motivo_cambio'],
        };
        foreach ($requeridos as $campo) {
            if (($this->__get($campo) ?? '') === '') {
                throw ExcepcionApi::validacion("El campo {$campo} es obligatorio para {$tipo}.");
            }
        }
    }

    private function asegurarAlcance(int $id): array
    {
        $sql = "SELECT cp.*, ss.id_beneficiario, ss.id_empleado
                FROM consulta_psicologica cp
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cp.id_solicitud_serv
                WHERE cp.id_psicologia = :id";
        if (!$this->esAdministrativo()) {
            $sql .= ' AND ss.id_empleado = :id_empleado';
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$this->esAdministrativo()) {
            $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$registro) {
            throw ExcepcionApi::noEncontrado('El diagnóstico no existe o no está disponible para tu usuario.');
        }
        return $registro;
    }

    private function patologias(): array
    {
        $stmt = $this->conn->query(
            "SELECT id_patologia, nombre_patologia, tipo_patologia
             FROM patologia
             WHERE LOWER(tipo_patologia) IN ('psicológica', 'psicologica', 'general')
             ORDER BY nombre_patologia"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function beneficiarios(): array
    {
        $stmt = $this->conn->query(
            "SELECT id_beneficiario, nombres, apellidos, tipo_cedula, cedula
             FROM beneficiario WHERE estatus = 1 ORDER BY nombres, apellidos"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function listar(): array
    {
        $sql = "SELECT cp.id_psicologia, cp.id_solicitud_serv, cp.id_detalle_patologia,
                       cp.tipo_consulta, cp.diagnostico, cp.tratamiento_gen,
                       cp.motivo_retiro, cp.duracion_retiro, cp.motivo_cambio,
                       cp.observaciones, cp.fecha_creacion,
                       ss.id_beneficiario, ss.id_empleado,
                       CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                       CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                       CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                       CONCAT(e.tipo_cedula, '-', e.cedula) AS cedula_empleado,
                       p.nombre_patologia AS patologia,
                       dp.id_patologia AS id_patologia
                FROM consulta_psicologica cp
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cp.id_solicitud_serv
                INNER JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
                INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
                LEFT JOIN detalle_patologia dp ON dp.id_detalle_patologia = cp.id_detalle_patologia
                LEFT JOIN patologia p ON p.id_patologia = dp.id_patologia";
        if (!$this->esAdministrativo()) {
            $sql .= ' WHERE ss.id_empleado = :id_empleado';
        }
        $sql .= ' ORDER BY cp.fecha_creacion DESC, cp.id_psicologia DESC';
        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrativo()) {
            $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtener(): array
    {
        $registro = $this->asegurarAlcance((int) $this->__get('id_psicologia'));
        $stmt = $this->conn->prepare(
            "SELECT cp.*, ss.id_beneficiario, ss.id_empleado,
                    CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                    p.nombre_patologia AS patologia,
                    dp.id_patologia AS id_patologia
             FROM consulta_psicologica cp
             INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cp.id_solicitud_serv
             INNER JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
             LEFT JOIN detalle_patologia dp ON dp.id_detalle_patologia = cp.id_detalle_patologia
             LEFT JOIN patologia p ON p.id_patologia = dp.id_patologia
             WHERE cp.id_psicologia = :id"
        );
        $stmt->execute([':id' => $registro['id_psicologia']]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: $registro;
    }

    private function crear(): array
    {
        $this->validarTipo();
        $idEmpleado = $this->esAdministrativo() && $this->__get('id_empleado')
            ? (int) $this->__get('id_empleado')
            : (int) $this->__get('id_usuario');
        $this->validarBeneficiario((int) $this->__get('id_beneficiario'));

        try {
            $this->conn->beginTransaction();

            // Los tipos que no manejan patología (Retiro temporal / Cambio de
            // carrera) igual deben registrar un detalle: usamos la patología
            // general ya existente para que id_detalle_patologia no sea nulo.
            $idPatologia = $this->__get('id_patologia') ?? self::ID_PATOLOGIA_SIN_APLICAR;
            $this->validarPatologia((int) $idPatologia);

            $stmt = $this->conn->prepare('INSERT INTO detalle_patologia (id_patologia) VALUES (:id)');
            $stmt->execute([':id' => (int) $idPatologia]);
            $idDetalle = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
                 VALUES (1, :id_beneficiario, :id_empleado)'
            );
            $stmt->execute([
                ':id_beneficiario' => (int) $this->__get('id_beneficiario'),
                ':id_empleado' => $idEmpleado,
            ]);
            $idSolicitud = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO consulta_psicologica
                 (id_solicitud_serv, id_detalle_patologia, tipo_consulta, diagnostico,
                  tratamiento_gen, motivo_retiro, duracion_retiro, motivo_cambio, observaciones)
                 VALUES (:solicitud, :detalle, :tipo, :diagnostico, :tratamiento,
                         :motivo_retiro, :duracion, :motivo_cambio, :observaciones)'
            );
            // El INSERT incluye :solicitud, que no forma parte de los datos clínicos.
            $parametros = $this->parametrosConsulta($idDetalle);
            $parametros[':solicitud'] = $idSolicitud;
            $stmt->execute($parametros);
            $id = (int) $this->conn->lastInsertId();
            $this->conn->commit();
            return ['id_psicologia' => $id, 'id_solicitud_serv' => $idSolicitud, 'tipo_consulta' => $this->__get('tipo_consulta')];
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            throw $e;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('PsicologiaModel::crear - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo registrar la consulta por una relación inválida.')
                : ExcepcionApi::errorInterno('No se pudo registrar la consulta psicológica.');
        }
    }

    private function actualizar(): array
    {
        $this->validarTipo();
        $actual = $this->asegurarAlcance((int) $this->__get('id_psicologia'));
        try {
            $this->conn->beginTransaction();
            $idDetalle = $actual['id_detalle_patologia'] !== null
                ? (int) $actual['id_detalle_patologia']
                : null;
            if ($this->__get('id_patologia') !== null) {
                $this->validarPatologia((int) $this->__get('id_patologia'));
                if ($idDetalle) {
                    $stmt = $this->conn->prepare('UPDATE detalle_patologia SET id_patologia = :patologia WHERE id_detalle_patologia = :detalle');
                    $stmt->execute([':patologia' => (int) $this->__get('id_patologia'), ':detalle' => (int) $idDetalle]);
                } else {
                    $stmt = $this->conn->prepare('INSERT INTO detalle_patologia (id_patologia) VALUES (:patologia)');
                    $stmt->execute([':patologia' => (int) $this->__get('id_patologia')]);
                    $idDetalle = (int) $this->conn->lastInsertId();
                }
            } elseif ($idDetalle === null) {
                // Registros sin patología (tipos que no aplican): garantizamos un
                // detalle con la patología general para no dejar el campo nulo.
                $stmt = $this->conn->prepare('INSERT INTO detalle_patologia (id_patologia) VALUES (:patologia)');
                $stmt->execute([':patologia' => self::ID_PATOLOGIA_SIN_APLICAR]);
                $idDetalle = (int) $this->conn->lastInsertId();
            }
            $stmt = $this->conn->prepare(
                'UPDATE consulta_psicologica SET id_detalle_patologia = :detalle, tipo_consulta = :tipo,
                 diagnostico = :diagnostico, tratamiento_gen = :tratamiento, motivo_retiro = :motivo_retiro,
                 duracion_retiro = :duracion, motivo_cambio = :motivo_cambio, observaciones = :observaciones
                 WHERE id_psicologia = :id'
            );
            // El UPDATE no modifica la solicitud: solo datos clínicos + id.
            $parametros = $this->parametrosConsulta($idDetalle);
            $parametros[':id'] = (int) $actual['id_psicologia'];
            $stmt->execute($parametros);
            $this->conn->commit();
            return ['id_psicologia' => (int) $actual['id_psicologia'], 'tipo_consulta' => $this->__get('tipo_consulta')];
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            throw $e;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('PsicologiaModel::actualizar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar la consulta psicológica.');
        }
    }

    private function eliminar(): array
    {
        $actual = $this->asegurarAlcance((int) $this->__get('id_psicologia'));
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare('DELETE FROM consulta_psicologica WHERE id_psicologia = :id');
            $stmt->execute([':id' => (int) $actual['id_psicologia']]);
            $stmt = $this->conn->prepare('DELETE FROM solicitud_de_servicio WHERE id_solicitud_serv = :id');
            $stmt->execute([':id' => (int) $actual['id_solicitud_serv']]);
            if ($actual['id_detalle_patologia']) {
                $stmt = $this->conn->prepare('DELETE FROM detalle_patologia WHERE id_detalle_patologia = :id');
                $stmt->execute([':id' => (int) $actual['id_detalle_patologia']]);
            }
            $this->conn->commit();
            return ['id_psicologia' => (int) $actual['id_psicologia']];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('PsicologiaModel::eliminar - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se puede eliminar: la consulta tiene registros relacionados.')
                : ExcepcionApi::errorInterno('No se pudo eliminar la consulta psicológica.');
        }
    }

    private function stats(): array
    {
        $sql = 'SELECT tipo_consulta, COUNT(*) AS cantidad
                FROM consulta_psicologica cp
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cp.id_solicitud_serv';
        if (!$this->esAdministrativo()) $sql .= ' WHERE ss.id_empleado = :id_empleado';
        $sql .= ' GROUP BY tipo_consulta';
        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrativo()) $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        $stmt->execute();
        $datos = ['total' => 0, 'diagnosticos' => 0, 'retiros' => 0, 'cambios' => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $cantidad = (int) $fila['cantidad'];
            $datos['total'] += $cantidad;
            $datos[match ($fila['tipo_consulta']) {
                'Diagnóstico' => 'diagnosticos',
                'Retiro temporal' => 'retiros',
                default => 'cambios',
            }] = $cantidad;
        }
        return $datos;
    }

    private function validarBeneficiario(int $id): void
    {
        $stmt = $this->conn->prepare('SELECT id_beneficiario FROM beneficiario WHERE id_beneficiario = :id AND estatus = 1');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetchColumn()) throw ExcepcionApi::noEncontrado('El beneficiario no existe o está inactivo.');
    }

    /** Valida que la patología exista y sea de tipo psicológica o general. */
    private function validarPatologia(int $idPatologia): void
    {
        $stmt = $this->conn->prepare(
            "SELECT id_patologia FROM patologia
             WHERE id_patologia = :id AND LOWER(tipo_patologia) IN ('psicológica', 'psicologica', 'general')"
        );
        $stmt->execute([':id' => $idPatologia]);
        if (!$stmt->fetchColumn()) {
            throw ExcepcionApi::validacion('La patología seleccionada no es válida.');
        }
    }

    /**
     * Parámetros de los datos clínicos de la consulta (sin :solicitud ni :id).
     * `crear()` añade :solicitud y `actualizar()` añade :id según su sentencia.
     */
    private function parametrosConsulta(?int $idDetalle): array
    {
        return [
            ':detalle' => $idDetalle,
            ':tipo' => $this->__get('tipo_consulta'),
            ':diagnostico' => $this->__get('diagnostico'),
            ':tratamiento' => $this->__get('tratamiento_gen'),
            ':motivo_retiro' => $this->__get('motivo_retiro'),
            ':duracion' => $this->__get('duracion_retiro'),
            ':motivo_cambio' => $this->__get('motivo_cambio'),
            ':observaciones' => $this->__get('observaciones'),
        ];
    }
}