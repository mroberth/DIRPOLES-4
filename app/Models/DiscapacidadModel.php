<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Core\ExcepcionApi;

/**
 * DiscapacidadModel
 * ---------------------------------------------------------------
 * Diagnósticos de Discapacidad (id_modulo 8). Tablas: discapacidad
 * y solicitud_de_servicio (id_servicios = 5). Sin inventario ni
 * tablas hijas: es un módulo de formularios de texto + catálogos ENUM.
 *
 * Patrón del esqueleto: __set valida → manejarAccion() despacha →
 * métodos privados hacen el SQL y lanzan ExcepcionApi.
 */
class DiscapacidadModel extends BusinessModel
{
    /** id_servicios = 5 → 'Discapacidad' (catálogo servicio). */
    private const ID_SERVICIO_DISCAPACIDAD = 5;

    private const TIPOS = ['Física', 'Sensorial', 'Intelectual', 'Múltiple', 'Otro'];
    private const GRADOS = ['Leve', 'Moderado', 'Grave'];
    private const ASISTENCIAS = ['Si', 'No'];

    /** Campos obligatorios (decisión: formulario del sistema viejo). */
    private const REQUERIDOS = ['tipo_discapacidad', 'diagnostico', 'grado', 'habilidades_funcionales', 'observaciones'];

    /** Límites de longitud según el esquema de la tabla. */
    private const LONGITUDES = [
        'disc_especifica'        => 200,
        'diagnostico'            => 255,
        'medicamentos'           => 255,
        'habilidades_funcionales' => 255,
        'dispositivo_asistencia' => 255,
        'carnet_discapacidad'    => 20,
    ];

    /** Todos los campos de texto de la tabla (obligatorios u opcionales). */
    private const TEXTOS = [
        'disc_especifica', 'diagnostico', 'medicamentos', 'habilidades_funcionales',
        'dispositivo_asistencia', 'carnet_discapacidad', 'observaciones', 'recomendaciones',
    ];

    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_discapacidad':
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

            case 'tipo_discapacidad':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::TIPOS, true)) {
                    throw ExcepcionApi::validacion('El tipo de discapacidad no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'grado':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::GRADOS, true)) {
                    throw ExcepcionApi::validacion('El grado no es válido (Leve, Moderado o Grave).');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'requiere_asistencia':
                if ($valor === null || $valor === '') {
                    $this->atributos[$nombre] = null;
                    break;
                }
                $valor = trim((string) $valor);
                if (!in_array($valor, self::ASISTENCIAS, true)) {
                    throw ExcepcionApi::validacion('"¿Requiere asistencia?" solo admite Si o No.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            default:
                if (in_array($nombre, self::TEXTOS, true)) {
                    $valor = trim((string) $valor);
                    if ($valor === '') {
                        // Opcional vacío → NULL; obligatorio vacío → error.
                        if (in_array($nombre, self::REQUERIDOS, true)) {
                            throw ExcepcionApi::validacion("El campo {$nombre} es obligatorio.");
                        }
                        $this->atributos[$nombre] = null;
                        break;
                    }
                    $maximo = self::LONGITUDES[$nombre] ?? null;
                    if ($maximo !== null && mb_strlen($valor) > $maximo) {
                        throw ExcepcionApi::validacion("El campo {$nombre} no puede superar {$maximo} caracteres.");
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
            default => throw ExcepcionApi::errorInterno("Acción no válida en DiscapacidadModel: '{$accion}'."),
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
        $sql = "SELECT d.id_discapacidad, d.id_solicitud_serv,
                       d.tipo_discapacidad, d.disc_especifica, d.diagnostico, d.grado,
                       d.medicamentos, d.habilidades_funcionales, d.requiere_asistencia,
                       d.dispositivo_asistencia, d.observaciones, d.recomendaciones,
                       d.carnet_discapacidad, d.fecha_creacion,
                       ss.id_beneficiario, ss.id_empleado,
                       CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                       CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                       CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                       CONCAT(e.tipo_cedula, '-', e.cedula) AS cedula_empleado
                FROM discapacidad d
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = d.id_solicitud_serv
                INNER JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
                INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado";
        if (!$this->esAdministrativo()) {
            $sql .= ' WHERE ss.id_empleado = :id_empleado';
        }
        $sql .= ' ORDER BY d.fecha_creacion DESC, d.id_discapacidad DESC';

        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrativo()) {
            $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtener(): array
    {
        $registro = $this->asegurarAlcance((int) $this->__get('id_discapacidad'));
        $stmt = $this->conn->prepare(
            "SELECT d.*, ss.id_beneficiario, ss.id_empleado,
                    CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                    CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                    CONCAT(e.tipo_cedula, '-', e.cedula) AS cedula_empleado
             FROM discapacidad d
             INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = d.id_solicitud_serv
             INNER JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
             INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
             WHERE d.id_discapacidad = :id"
        );
        $stmt->execute([':id' => $registro['id_discapacidad']]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: $registro;
    }

    // ---------- Estadísticas ----------

    /**
     * 4 tarjetas (patrón del sistema viejo): total, del mes, graves y con
     * carnet. Los no administrativos solo ven sus propias discapacidades.
     */
    private function stats(): array
    {
        $sql = "SELECT COUNT(*) AS total,
                       SUM(CASE WHEN MONTH(d.fecha_creacion) = MONTH(CURRENT_DATE())
                                 AND YEAR(d.fecha_creacion) = YEAR(CURRENT_DATE())
                                THEN 1 ELSE 0 END) AS del_mes,
                       SUM(CASE WHEN d.grado = 'Grave' THEN 1 ELSE 0 END) AS graves,
                       SUM(CASE WHEN d.carnet_discapacidad IS NOT NULL
                                  AND TRIM(d.carnet_discapacidad) != ''
                                THEN 1 ELSE 0 END) AS con_carnet
                FROM discapacidad d
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = d.id_solicitud_serv";
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
            'total'      => (int) ($fila['total'] ?? 0),
            'del_mes'    => (int) ($fila['del_mes'] ?? 0),
            'graves'     => (int) ($fila['graves'] ?? 0),
            'con_carnet' => (int) ($fila['con_carnet'] ?? 0),
        ];
    }

    // ---------- Registro ----------

    /** Transacción: solicitud_de_servicio (servicio 5) → discapacidad. */
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
                ':servicio' => self::ID_SERVICIO_DISCAPACIDAD,
                ':id_beneficiario' => $idBeneficiario,
                ':id_empleado' => $idEmpleado,
            ]);
            $idSolicitud = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO discapacidad
                   (id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico, grado,
                    medicamentos, habilidades_funcionales, requiere_asistencia, dispositivo_asistencia,
                    observaciones, recomendaciones, carnet_discapacidad, fecha_creacion)
                 VALUES (:solicitud, :tipo, :especifica, :diagnostico, :grado,
                        :medicamentos, :habilidades, :asistencia, :dispositivo,
                        :observaciones, :recomendaciones, :carnet, CURDATE())'
            );
            $stmt->execute([
                ':solicitud'    => $idSolicitud,
                ':tipo'         => $this->__get('tipo_discapacidad'),
                ':especifica'   => $this->__get('disc_especifica'),
                ':diagnostico'  => $this->__get('diagnostico'),
                ':grado'        => $this->__get('grado'),
                ':medicamentos' => $this->__get('medicamentos'),
                ':habilidades'  => $this->__get('habilidades_funcionales'),
                ':asistencia'   => $this->__get('requiere_asistencia'),
                ':dispositivo'  => $this->__get('dispositivo_asistencia'),
                ':observaciones' => $this->__get('observaciones'),
                ':recomendaciones' => $this->__get('recomendaciones'),
                ':carnet'       => $this->__get('carnet_discapacidad'),
            ]);
            $idDiscapacidad = (int) $this->conn->lastInsertId();

            $this->conn->commit();
            return [
                'id_discapacidad' => $idDiscapacidad,
                'id_solicitud_serv' => $idSolicitud,
            ];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('DiscapacidadModel::crear - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo registrar la discapacidad por una relación inválida.')
                : ExcepcionApi::errorInterno('No se pudo registrar el diagnóstico de discapacidad.');
        }
    }

    // ---------- Actualización ----------

    /**
     * Actualiza SOLO los 11 campos de la tabla discapacidad. Beneficiario
     * y empleado que atendió (solicitud_de_servicio) jamás se tocan
     * (integridad de auditoría).
     */
    private function actualizar(): array
    {
        foreach (self::REQUERIDOS as $campo) {
            $valor = $this->__get($campo);
            if ($valor === null || $valor === '') {
                throw ExcepcionApi::validacion("El campo {$campo} es obligatorio.");
            }
        }
        $actual = $this->asegurarAlcance((int) $this->__get('id_discapacidad'));
        try {
            $stmt = $this->conn->prepare(
                'UPDATE discapacidad SET
                    tipo_discapacidad = :tipo, disc_especifica = :especifica,
                    diagnostico = :diagnostico, grado = :grado,
                    medicamentos = :medicamentos, habilidades_funcionales = :habilidades,
                    requiere_asistencia = :asistencia, dispositivo_asistencia = :dispositivo,
                    observaciones = :observaciones, recomendaciones = :recomendaciones,
                    carnet_discapacidad = :carnet
                 WHERE id_discapacidad = :id'
            );
            $stmt->execute([
                ':tipo'         => $this->__get('tipo_discapacidad'),
                ':especifica'   => $this->__get('disc_especifica'),
                ':diagnostico'  => $this->__get('diagnostico'),
                ':grado'        => $this->__get('grado'),
                ':medicamentos' => $this->__get('medicamentos'),
                ':habilidades'  => $this->__get('habilidades_funcionales'),
                ':asistencia'   => $this->__get('requiere_asistencia'),
                ':dispositivo'  => $this->__get('dispositivo_asistencia'),
                ':observaciones' => $this->__get('observaciones'),
                ':recomendaciones' => $this->__get('recomendaciones'),
                ':carnet'       => $this->__get('carnet_discapacidad'),
                ':id' => (int) $actual['id_discapacidad'],
            ]);
            return ['id_discapacidad' => (int) $actual['id_discapacidad']];
        } catch (PDOException $e) {
            error_log('DiscapacidadModel::actualizar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar el diagnóstico de discapacidad.');
        }
    }

    // ---------- Eliminación ----------

    /** Borra la discapacidad y su solicitud, en transacción. */
    private function eliminar(): array
    {
        $actual = $this->asegurarAlcance((int) $this->__get('id_discapacidad'));

        $stmt = $this->conn->prepare(
            'SELECT CONCAT(b.nombres, \' \', b.apellidos) FROM beneficiario b WHERE b.id_beneficiario = :id'
        );
        $stmt->execute([':id' => (int) $actual['id_beneficiario']]);
        $beneficiario = (string) $stmt->fetchColumn();

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare('DELETE FROM discapacidad WHERE id_discapacidad = :id');
            $stmt->execute([':id' => (int) $actual['id_discapacidad']]);
            $stmt = $this->conn->prepare('DELETE FROM solicitud_de_servicio WHERE id_solicitud_serv = :id');
            $stmt->execute([':id' => (int) $actual['id_solicitud_serv']]);
            $this->conn->commit();
            return [
                'id_discapacidad' => (int) $actual['id_discapacidad'],
                'beneficiario' => $beneficiario,
            ];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('DiscapacidadModel::eliminar - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se puede eliminar: el diagnóstico tiene registros relacionados.')
                : ExcepcionApi::errorInterno('No se pudo eliminar el diagnóstico de discapacidad.');
        }
    }

    // ---------- Validaciones de negocio ----------

    /**
     * Carga la discapacidad con su alcance: un empleado no administrativo
     * solo ve los diagnósticos que él registró; Admin/Superusuario ven todo.
     */
    private function asegurarAlcance(int $id): array
    {
        $sql = 'SELECT d.*, ss.id_beneficiario, ss.id_empleado
                FROM discapacidad d
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = d.id_solicitud_serv
                WHERE d.id_discapacidad = :id';
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
            throw ExcepcionApi::noEncontrado('El diagnóstico de discapacidad no existe o no está disponible para tu usuario.');
        }
        return $registro;
    }

    private function validarRequeridos(): void
    {
        foreach (self::REQUERIDOS as $campo) {
            $valor = $this->__get($campo);
            if ($valor === null || $valor === '') {
                throw ExcepcionApi::validacion("El campo {$campo} es obligatorio.");
            }
        }
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
