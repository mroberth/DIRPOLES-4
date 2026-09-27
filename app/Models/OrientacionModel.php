<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Core\ExcepcionApi;

/**
 * OrientacionModel
 * ---------------------------------------------------------------
 * Diagnósticos de Orientación (id_modulo 6). Tablas: orientacion
 * y solicitud_de_servicio (id_servicios = 3). No usa detalle_patologia
 * ni inventario: es el módulo de texto simple.
 *
 * Patrón del esqueleto: __set valida → manejarAccion() despacha →
 * métodos privados hacen el SQL y lanzan ExcepcionApi.
 */
class OrientacionModel extends BusinessModel
{
    /** id_servicios = 3 → 'Orientacion' (catálogo servicio). */
    private const ID_SERVICIO_ORIENTACION = 3;

    /** Los 4 campos de texto son obligatorios y admiten hasta 5000 car. */
    private const MAX_TEXTO = 5000;

    private const CAMPOS_TEXTO = [
        'motivo_orientacion',
        'descripcion_orientacion',
        'indicaciones_orientacion',
        'obs_adic_orientacion',
    ];

    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_orientacion':
            case 'id_beneficiario':
            case 'id_empleado':
            case 'id_usuario':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion("El {$nombre} no es válido.");
                }
                $this->atributos[$nombre] = (int) $valor;
                break;

            case 'tipo_empleado':
                $this->atributos[$nombre] = trim((string) $valor);
                break;

            default:
                if (in_array($nombre, self::CAMPOS_TEXTO, true)) {
                    $valor = trim((string) $valor);
                    if ($valor === '') {
                        throw ExcepcionApi::validacion("El campo {$nombre} es obligatorio.");
                    }
                    if (mb_strlen($valor) > self::MAX_TEXTO) {
                        throw ExcepcionApi::validacion("El campo {$nombre} no puede superar " . self::MAX_TEXTO . " caracteres.");
                    }
                    $this->atributos[$nombre] = $valor;
                    break;
                }
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
            'beneficiarios' => $this->beneficiarios(),
            'listar' => $this->listar(),
            'obtener' => $this->obtener(),
            'crear' => $this->crear(),
            'actualizar' => $this->actualizar(),
            'eliminar' => $this->eliminar(),
            'stats' => $this->stats(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en OrientacionModel: '{$accion}'."),
        };
    }

    // ---------- Catálogos ----------

    private function beneficiarios(): array
    {
        $stmt = $this->conn->query(
            'SELECT id_beneficiario, nombres, apellidos, tipo_cedula, cedula
             FROM beneficiario WHERE estatus = 1 ORDER BY nombres, apellidos'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---------- Consulta ----------

    private function listar(): array
    {
        $sql = "SELECT o.id_orientacion, o.id_solicitud_serv,
                       o.motivo_orientacion, o.descripcion_orientacion,
                       o.obs_adic_orientacion, o.indicaciones_orientacion,
                       o.fecha_creacion,
                       ss.id_beneficiario, ss.id_empleado,
                       CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                       CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                       CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                       CONCAT(e.tipo_cedula, '-', e.cedula) AS cedula_empleado
                FROM orientacion o
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = o.id_solicitud_serv
                INNER JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
                INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado";
        if (!$this->esAdministrativo()) {
            $sql .= ' WHERE ss.id_empleado = :id_empleado';
        }
        $sql .= ' ORDER BY o.fecha_creacion DESC, o.id_orientacion DESC';

        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrativo()) {
            $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtener(): array
    {
        $registro = $this->asegurarAlcance((int) $this->__get('id_orientacion'));
        $stmt = $this->conn->prepare(
            "SELECT o.*, ss.id_beneficiario, ss.id_empleado,
                    CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                    CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                    CONCAT(e.tipo_cedula, '-', e.cedula) AS cedula_empleado
             FROM orientacion o
             INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = o.id_solicitud_serv
             INNER JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
             INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
             WHERE o.id_orientacion = :id"
        );
        $stmt->execute([':id' => $registro['id_orientacion']]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: $registro;
    }

    // ---------- Estadísticas ----------

    /**
     * 4 tarjetas (patrón del sistema viejo): total, del mes, sin
     * indicaciones y con observaciones. Los no administrativos solo
     * ven sus propias orientaciones.
     */
    private function stats(): array
    {
        $sql = "SELECT COUNT(*) AS total,
                       SUM(CASE WHEN MONTH(o.fecha_creacion) = MONTH(CURRENT_DATE())
                                 AND YEAR(o.fecha_creacion) = YEAR(CURRENT_DATE())
                                THEN 1 ELSE 0 END) AS del_mes,
                       SUM(CASE WHEN o.indicaciones_orientacion IS NULL
                                  OR TRIM(o.indicaciones_orientacion) = ''
                                THEN 1 ELSE 0 END) AS sin_indicaciones,
                       SUM(CASE WHEN o.obs_adic_orientacion IS NOT NULL
                                  AND TRIM(o.obs_adic_orientacion) != ''
                                THEN 1 ELSE 0 END) AS con_observaciones
                FROM orientacion o
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = o.id_solicitud_serv";
        if (!$this->esAdministrativo()) {
            $sql .= ' WHERE ss.id_empleado = :id_empleado';
        }

        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrativo()) {
            $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total' => (int) ($fila['total'] ?? 0),
            'del_mes' => (int) ($fila['del_mes'] ?? 0),
            'sin_indicaciones' => (int) ($fila['sin_indicaciones'] ?? 0),
            'con_observaciones' => (int) ($fila['con_observaciones'] ?? 0),
        ];
    }

    // ---------- Registro ----------

    private function crear(): array
    {
        $this->validarRequeridos();
        $idBeneficiario = (int) $this->__get('id_beneficiario');
        $this->validarBeneficiario($idBeneficiario);

        $idEmpleado = $this->esAdministrativo() && $this->__get('id_empleado')
            ? (int) $this->__get('id_empleado')
            : (int) $this->__get('id_usuario');

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
                 VALUES (:servicio, :id_beneficiario, :id_empleado)'
            );
            $stmt->execute([
                ':servicio' => self::ID_SERVICIO_ORIENTACION,
                ':id_beneficiario' => $idBeneficiario,
                ':id_empleado' => $idEmpleado,
            ]);
            $idSolicitud = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO orientacion
                   (id_solicitud_serv, motivo_orientacion, descripcion_orientacion,
                    indicaciones_orientacion, obs_adic_orientacion, fecha_creacion)
                 VALUES (:solicitud, :motivo, :descripcion, :indicaciones, :observaciones, CURDATE())'
            );
            $stmt->execute([
                ':solicitud' => $idSolicitud,
                ':motivo' => $this->__get('motivo_orientacion'),
                ':descripcion' => $this->__get('descripcion_orientacion'),
                ':indicaciones' => $this->__get('indicaciones_orientacion'),
                ':observaciones' => $this->__get('obs_adic_orientacion'),
            ]);
            $idOrientacion = (int) $this->conn->lastInsertId();

            $this->conn->commit();
            return [
                'id_orientacion' => $idOrientacion,
                'id_solicitud_serv' => $idSolicitud,
            ];
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            throw $e;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('OrientacionModel::crear - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo registrar la orientación por una relación inválida.')
                : ExcepcionApi::errorInterno('No se pudo registrar la orientación.');
        }
    }

    // ---------- Actualización ----------

    /**
     * Actualiza SOLO los 4 campos de texto. Beneficiario y empleado que
     * atendió (solicitud_de_servicio) jamás se tocan (integridad de auditoría).
     */
    private function actualizar(): array
    {
        $this->validarCamposTexto();
        $actual = $this->asegurarAlcance((int) $this->__get('id_orientacion'));
        try {
            $stmt = $this->conn->prepare(
                'UPDATE orientacion
                 SET motivo_orientacion = :motivo, descripcion_orientacion = :descripcion,
                     indicaciones_orientacion = :indicaciones, obs_adic_orientacion = :observaciones
                 WHERE id_orientacion = :id'
            );
            $stmt->execute([
                ':motivo' => $this->__get('motivo_orientacion'),
                ':descripcion' => $this->__get('descripcion_orientacion'),
                ':indicaciones' => $this->__get('indicaciones_orientacion'),
                ':observaciones' => $this->__get('obs_adic_orientacion'),
                ':id' => (int) $actual['id_orientacion'],
            ]);
            return ['id_orientacion' => (int) $actual['id_orientacion']];
        } catch (PDOException $e) {
            error_log('OrientacionModel::actualizar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar la orientación.');
        }
    }

    // ---------- Eliminación ----------

    /** Borra la orientación y su solicitud, en transacción. */
    private function eliminar(): array
    {
        $actual = $this->asegurarAlcance((int) $this->__get('id_orientacion'));

        $stmt = $this->conn->prepare(
            'SELECT CONCAT(b.nombres, \' \', b.apellidos) FROM beneficiario b WHERE b.id_beneficiario = :id'
        );
        $stmt->execute([':id' => (int) $actual['id_beneficiario']]);
        $beneficiario = (string) $stmt->fetchColumn();

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare('DELETE FROM orientacion WHERE id_orientacion = :id');
            $stmt->execute([':id' => (int) $actual['id_orientacion']]);
            $stmt = $this->conn->prepare('DELETE FROM solicitud_de_servicio WHERE id_solicitud_serv = :id');
            $stmt->execute([':id' => (int) $actual['id_solicitud_serv']]);
            $this->conn->commit();
            return [
                'id_orientacion' => (int) $actual['id_orientacion'],
                'beneficiario' => $beneficiario,
            ];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('OrientacionModel::eliminar - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se puede eliminar: la orientación tiene registros relacionados.')
                : ExcepcionApi::errorInterno('No se pudo eliminar la orientación.');
        }
    }

    // ---------- Validaciones de negocio ----------

    /**
     * Carga la orientación con su alcance: un orientador no administrativo
     * solo ve las orientaciones que él atendió; Admin/Superusuario ven todo.
     */
    private function asegurarAlcance(int $id): array
    {
        $sql = 'SELECT o.*, ss.id_beneficiario, ss.id_empleado
                FROM orientacion o
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = o.id_solicitud_serv
                WHERE o.id_orientacion = :id';
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
            throw ExcepcionApi::noEncontrado('La orientación no existe o no está disponible para tu usuario.');
        }
        return $registro;
    }

    /** Los 4 campos de texto son obligatorios (crear y editar). */
    private function validarCamposTexto(): void
    {
        foreach (self::CAMPOS_TEXTO as $campo) {
            $valor = $this->__get($campo);
            if ($valor === null || $valor === '') {
                throw ExcepcionApi::validacion("El campo {$campo} es obligatorio.");
            }
        }
    }

    private function validarRequeridos(): void
    {
        $this->validarCamposTexto();
        if (!$this->__get('id_beneficiario')) {
            throw ExcepcionApi::validacion('El beneficiario es obligatorio.');
        }
    }

    private function validarBeneficiario(int $id): void
    {
        $stmt = $this->conn->prepare('SELECT id_beneficiario FROM beneficiario WHERE id_beneficiario = :id AND estatus = 1');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetchColumn()) {
            throw ExcepcionApi::noEncontrado('El beneficiario no existe o está inactivo.');
        }
    }

    private function esAdministrativo(): bool
    {
        return in_array($this->__get('tipo_empleado'), ['Administrador', 'Superusuario'], true);
    }
}
