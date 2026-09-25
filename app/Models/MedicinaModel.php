<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Core\ExcepcionApi;

/**
 * MedicinaModel
 * ---------------------------------------------------------------
 * Diagnósticos de Medicina (id_modulo 5). Tablas: consulta_medica,
 * detalle_patologia, solicitud_de_servicio y detalle_insumo.
 *
 * Patrón del esqueleto: __set valida → manejarAccion() despacha →
 * métodos privados hacen el SQL y lanzan ExcepcionApi.
 */
class MedicinaModel extends BusinessModel
{
    private const TIPOS_SANGRE = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    /** id_servicios = 2 → 'Medicina' (catálogo servicio). */
    private const ID_SERVICIO_MEDICINA = 2;

    /** insumos.cantidad por debajo de este valor se considera bajo stock. */
    private const STOCK_BAJO = 10;

    private const RANGO_ESTATURA = [0.50, 2.50];
    private const RANGO_PESO = [2.00, 300.00];

    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_consulta_med':
            case 'id_beneficiario':
            case 'id_patologia':
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

            case 'estatura':
            case 'peso':
                if (!is_numeric($valor)) {
                    throw ExcepcionApi::validacion('La ' . ($nombre === 'estatura' ? 'estatura' : 'peso') . ' debe ser un número.');
                }
                $rango = $nombre === 'estatura' ? self::RANGO_ESTATURA : self::RANGO_PESO;
                $valor = round((float) $valor, 2);
                if ($valor < $rango[0] || $valor > $rango[1]) {
                    throw ExcepcionApi::validacion(sprintf(
                        'La %s debe estar entre %s y %s.',
                        $nombre === 'estatura' ? 'estatura' : 'peso',
                        $rango[0],
                        $rango[1]
                    ));
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'tipo_sangre':
                $valor = strtoupper(trim((string) $valor));
                if (!in_array($valor, self::TIPOS_SANGRE, true)) {
                    throw ExcepcionApi::validacion('El tipo de sangre no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'motivo_visita':
            case 'diagnostico':
            case 'tratamiento':
            case 'observaciones':
                $valor = trim((string) $valor);
                if (mb_strlen($valor) > 255) {
                    throw ExcepcionApi::validacion("El campo {$nombre} no puede superar 255 caracteres.");
                }
                // motivo_visita / diagnostico / tratamiento son NOT NULL en BD:
                // el vacío solo es aceptable en observaciones (se guarda '').
                if ($valor === '' && $nombre !== 'observaciones') {
                    throw ExcepcionApi::validacion("El campo {$nombre} es obligatorio.");
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'insumos':
                $this->atributos['insumos'] = $this->normalizarInsumos($valor);
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
            'insumos' => $this->insumos(),
            'listar' => $this->listar(),
            'obtener' => $this->obtener(),
            'crear' => $this->crear(),
            'actualizar' => $this->actualizar(),
            'eliminar' => $this->eliminar(),
            'stats' => $this->stats(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en MedicinaModel: '{$accion}'."),
        };
    }

    // ---------- Catálogos ----------

    private function patologias(): array
    {
        $stmt = $this->conn->query(
            "SELECT id_patologia, nombre_patologia, tipo_patologia
             FROM patologia
             WHERE LOWER(tipo_patologia) IN ('médica', 'medica', 'general')
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

    /** Insumos aptos para recetar: disponibles, con stock y sin vencer. */
    private function insumos(): array
    {
        $stmt = $this->conn->query(
            "SELECT id_insumo, nombre_insumo, tipo_insumo, cantidad, fecha_vencimiento
             FROM insumos
             WHERE estatus = 'Disponible'
               AND cantidad >= 1
               AND fecha_vencimiento > CURDATE()
             ORDER BY nombre_insumo"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---------- Consulta ----------

    private function listar(): array
    {
        $sql = "SELECT cm.id_consulta_med, cm.id_solicitud_serv, cm.id_detalle_patologia,
                       cm.estatura, cm.peso, cm.tipo_sangre, cm.motivo_visita,
                       cm.diagnostico, cm.tratamiento, cm.observaciones, cm.fecha_creacion,
                       ss.id_beneficiario, ss.id_empleado,
                       CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                       CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                       CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                       CONCAT(e.tipo_cedula, '-', e.cedula) AS cedula_empleado,
                       p.nombre_patologia AS patologia,
                       dp.id_patologia AS id_patologia,
                       (SELECT GROUP_CONCAT(CONCAT(i.nombre_insumo, ' (', di.cantidad_usada, ')') SEPARATOR ', ')
                        FROM detalle_insumo di
                        INNER JOIN insumos i ON i.id_insumo = di.id_insumo
                        WHERE di.id_consulta_med = cm.id_consulta_med) AS insumos_usados
                FROM consulta_medica cm
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cm.id_solicitud_serv
                INNER JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
                INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
                LEFT JOIN detalle_patologia dp ON dp.id_detalle_patologia = cm.id_detalle_patologia
                LEFT JOIN patologia p ON p.id_patologia = dp.id_patologia";
        if (!$this->esAdministrativo()) {
            $sql .= ' WHERE ss.id_empleado = :id_empleado';
        }
        $sql .= ' ORDER BY cm.fecha_creacion DESC, cm.id_consulta_med DESC';

        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrativo()) {
            $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtener(): array
    {
        $registro = $this->asegurarAlcance((int) $this->__get('id_consulta_med'));
        $stmt = $this->conn->prepare(
            "SELECT cm.*, ss.id_beneficiario, ss.id_empleado,
                    CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                    p.nombre_patologia AS patologia,
                    dp.id_patologia AS id_patologia
             FROM consulta_medica cm
             INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cm.id_solicitud_serv
             INNER JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
             LEFT JOIN detalle_patologia dp ON dp.id_detalle_patologia = cm.id_detalle_patologia
             LEFT JOIN patologia p ON p.id_patologia = dp.id_patologia
             WHERE cm.id_consulta_med = :id"
        );
        $stmt->execute([':id' => $registro['id_consulta_med']]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC) ?: $registro;

        $stmt = $this->conn->prepare(
            'SELECT di.id_insumo, di.cantidad_usada, i.nombre_insumo
             FROM detalle_insumo di
             INNER JOIN insumos i ON i.id_insumo = di.id_insumo
             WHERE di.id_consulta_med = :id
             ORDER BY i.nombre_insumo'
        );
        $stmt->execute([':id' => (int) $fila['id_consulta_med']]);
        $fila['insumos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $fila;
    }

    // ---------- Estadísticas ----------

    private function stats(): array
    {
        $datos = [
            'total' => 0,
            'insumos_disponibles' => 0,
            'insumos_bajo_stock' => 0,
            'beneficiarios_atendidos' => 0,
        ];

        // Consultas médicas (y beneficiarios atendidos) del usuario actual
        // si no es administrativo, igual que Psicología.
        $sql = 'SELECT COUNT(*) AS total, COUNT(DISTINCT ss.id_beneficiario) AS atendidos
                FROM consulta_medica cm
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cm.id_solicitud_serv';
        if (!$this->esAdministrativo()) {
            $sql .= ' WHERE ss.id_empleado = :id_empleado';
        }
        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrativo()) {
            $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $datos['total'] = (int) ($fila['total'] ?? 0);
        $datos['beneficiarios_atendidos'] = (int) ($fila['atendidos'] ?? 0);

        // Insumos: globales (el inventario no es por empleado).
        $stmt = $this->conn->query(
            'SELECT COUNT(*) AS disponibles,
                    SUM(CASE WHEN cantidad < ' . self::STOCK_BAJO . ' THEN 1 ELSE 0 END) AS bajo
             FROM insumos
             WHERE estatus = \'Disponible\' AND fecha_vencimiento > CURDATE()'
        );
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $datos['insumos_disponibles'] = (int) ($fila['disponibles'] ?? 0);
        $datos['insumos_bajo_stock'] = (int) ($fila['bajo'] ?? 0);

        return $datos;
    }

    // ---------- Registro ----------

    private function crear(): array
    {
        $this->validarRequeridos();
        $idBeneficiario = (int) $this->__get('id_beneficiario');
        $idPatologia = (int) $this->__get('id_patologia');
        $this->validarBeneficiario($idBeneficiario);
        $this->validarPatologia($idPatologia);

        $idEmpleado = $this->esAdministrativo() && $this->__get('id_empleado')
            ? (int) $this->__get('id_empleado')
            : (int) $this->__get('id_usuario');

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare('INSERT INTO detalle_patologia (id_patologia) VALUES (:id)');
            $stmt->execute([':id' => $idPatologia]);
            $idDetalle = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
                 VALUES (:servicio, :id_beneficiario, :id_empleado)'
            );
            $stmt->execute([
                ':servicio' => self::ID_SERVICIO_MEDICINA,
                ':id_beneficiario' => $idBeneficiario,
                ':id_empleado' => $idEmpleado,
            ]);
            $idSolicitud = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO consulta_medica
                   (id_detalle_patologia, id_solicitud_serv, estatura, peso, tipo_sangre,
                    motivo_visita, diagnostico, tratamiento, observaciones, fecha_creacion)
                 VALUES (:detalle, :solicitud, :estatura, :peso, :tipo_sangre,
                         :motivo, :diagnostico, :tratamiento, :observaciones, CURDATE())'
            );
            $stmt->execute([
                ':detalle' => $idDetalle,
                ':solicitud' => $idSolicitud,
                ':estatura' => $this->__get('estatura'),
                ':peso' => $this->__get('peso'),
                ':tipo_sangre' => $this->__get('tipo_sangre'),
                ':motivo' => $this->__get('motivo_visita'),
                ':diagnostico' => $this->__get('diagnostico'),
                ':tratamiento' => $this->__get('tratamiento'),
                ':observaciones' => $this->__get('observaciones') ?? '',
            ]);
            $idConsulta = (int) $this->conn->lastInsertId();

            $this->registrarInsumos($idConsulta, $idEmpleado);

            $this->conn->commit();
            return [
                'id_consulta_med' => $idConsulta,
                'id_solicitud_serv' => $idSolicitud,
                'insumos' => count($this->__get('insumos') ?? []),
            ];
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            throw $e;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('MedicinaModel::crear - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo registrar la consulta por una relación inválida.')
                : ExcepcionApi::errorInterno('No se pudo registrar la consulta médica.');
        }
    }

    /**
     * Inserta el detalle de insumos, descuenta el stock y registra la salida
     * en inventario_medico, todo dentro de la transacción de crear().
     */
    private function registrarInsumos(int $idConsulta, int $idEmpleado): void
    {
        foreach ($this->__get('insumos') ?? [] as $fila) {
            $idInsumo = (int) $fila['id_insumo'];
            $cantidad = (int) $fila['cantidad'];

            // FOR UPDATE: bloquea la fila para que dos consultas simultáneas
            // no descuenten el mismo stock dos veces.
            $stmt = $this->conn->prepare(
                'SELECT nombre_insumo, cantidad, estatus, fecha_vencimiento
                 FROM insumos WHERE id_insumo = :id FOR UPDATE'
            );
            $stmt->execute([':id' => $idInsumo]);
            $insumo = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$insumo) {
                throw ExcepcionApi::noEncontrado('Uno de los insumos seleccionados ya no existe.');
            }
            if ($insumo['estatus'] !== 'Disponible'
                || strtotime($insumo['fecha_vencimiento']) < strtotime(date('Y-m-d'))) {
                throw ExcepcionApi::validacion('Uno de los insumos seleccionados ya no está disponible.');
            }
            if ((int) $insumo['cantidad'] < $cantidad) {
                throw ExcepcionApi::validacion(sprintf(
                    'Stock insuficiente para "%s" (disponible: %d).',
                    $insumo['nombre_insumo'],
                    (int) $insumo['cantidad']
                ));
            }

            $stmt = $this->conn->prepare(
                'INSERT INTO detalle_insumo (id_consulta_med, id_insumo, cantidad_usada)
                 VALUES (:consulta, :insumo, :cantidad)'
            );
            $stmt->execute([
                ':consulta' => $idConsulta,
                ':insumo' => $idInsumo,
                ':cantidad' => (string) $cantidad,
            ]);

            $stmt = $this->conn->prepare(
                'UPDATE insumos
                 SET cantidad = cantidad - :cantidad,
                     estatus = CASE WHEN (cantidad - :cantidad) <= 0 THEN \'Agotado\' ELSE estatus END
                 WHERE id_insumo = :id'
            );
            $stmt->execute([':cantidad' => $cantidad, ':id' => $idInsumo]);

            $stmt = $this->conn->prepare(
                'INSERT INTO inventario_medico
                   (id_insumo, id_empleado, tipo_movimiento, cantidad, descripcion)
                 VALUES (:insumo, :empleado, \'Salida\', :cantidad, :descripcion)'
            );
            $stmt->execute([
                ':insumo' => $idInsumo,
                ':empleado' => $idEmpleado,
                ':cantidad' => $cantidad,
                ':descripcion' => 'Salida por consulta médica #' . $idConsulta,
            ]);
        }
    }

    // ---------- Actualización ----------

    /**
     * Actualiza SOLO datos textuales/antropométricos del diagnóstico:
     * patología, estatura, peso, tipo de sangre, motivo, diagnóstico,
     * tratamiento y observaciones. NO toca el beneficiario, ni el empleado
     * que atendió (solicitud_de_servicio), ni los insumos usados.
     */
    private function actualizar(): array
    {
        $this->validarCamposEditables();
        $actual = $this->asegurarAlcance((int) $this->__get('id_consulta_med'));
        try {
            $this->conn->beginTransaction();

            $idDetalle = $actual['id_detalle_patologia'] !== null
                ? (int) $actual['id_detalle_patologia']
                : null;
            if ($idDetalle) {
                $stmt = $this->conn->prepare('UPDATE detalle_patologia SET id_patologia = :patologia WHERE id_detalle_patologia = :detalle');
                $stmt->execute([':patologia' => (int) $this->__get('id_patologia'), ':detalle' => $idDetalle]);
            } else {
                $stmt = $this->conn->prepare('INSERT INTO detalle_patologia (id_patologia) VALUES (:patologia)');
                $stmt->execute([':patologia' => (int) $this->__get('id_patologia')]);
                $idDetalle = (int) $this->conn->lastInsertId();
            }

            $stmt = $this->conn->prepare(
                'UPDATE consulta_medica
                 SET id_detalle_patologia = :detalle, estatura = :estatura, peso = :peso,
                     tipo_sangre = :tipo_sangre, motivo_visita = :motivo,
                     diagnostico = :diagnostico, tratamiento = :tratamiento,
                     observaciones = :observaciones
                 WHERE id_consulta_med = :id'
            );
            $stmt->execute([
                ':detalle' => $idDetalle,
                ':estatura' => $this->__get('estatura'),
                ':peso' => $this->__get('peso'),
                ':tipo_sangre' => $this->__get('tipo_sangre'),
                ':motivo' => $this->__get('motivo_visita'),
                ':diagnostico' => $this->__get('diagnostico'),
                ':tratamiento' => $this->__get('tratamiento'),
                ':observaciones' => $this->__get('observaciones') ?? '',
                ':id' => (int) $actual['id_consulta_med'],
            ]);
            $this->conn->commit();
            return ['id_consulta_med' => (int) $actual['id_consulta_med']];
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            throw $e;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('MedicinaModel::actualizar - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo actualizar: la consulta tiene registros relacionados.')
                : ExcepcionApi::errorInterno('No se pudo actualizar la consulta médica.');
        }
    }

    // ---------- Eliminación ----------

    /**
     * Borra SOLO el registro clínico (detalle de insumos, consulta, solicitud
     * y detalle de patología). El inventario NO se revierte: el insumo ya se
     * usó, por lo que insumos.cantidad y los movimientos de inventario_medico
     * quedan intactos. Devuelve el resumen para la Bitácora.
     */
    private function eliminar(): array
    {
        $actual = $this->asegurarAlcance((int) $this->__get('id_consulta_med'));

        $stmt = $this->conn->prepare(
            'SELECT CONCAT(b.nombres, \' \', b.apellidos) FROM beneficiario b WHERE b.id_beneficiario = :id'
        );
        $stmt->execute([':id' => (int) $actual['id_beneficiario']]);
        $beneficiario = (string) $stmt->fetchColumn();

        $stmt = $this->conn->prepare(
            "SELECT GROUP_CONCAT(CONCAT(i.nombre_insumo, ' (', di.cantidad_usada, ')') SEPARATOR ', ')
             FROM detalle_insumo di
             INNER JOIN insumos i ON i.id_insumo = di.id_insumo
             WHERE di.id_consulta_med = :id"
        );
        $stmt->execute([':id' => (int) $actual['id_consulta_med']]);
        $insumos = (string) ($stmt->fetchColumn() ?: '');

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare('DELETE FROM detalle_insumo WHERE id_consulta_med = :id');
            $stmt->execute([':id' => (int) $actual['id_consulta_med']]);
            $stmt = $this->conn->prepare('DELETE FROM consulta_medica WHERE id_consulta_med = :id');
            $stmt->execute([':id' => (int) $actual['id_consulta_med']]);
            $stmt = $this->conn->prepare('DELETE FROM solicitud_de_servicio WHERE id_solicitud_serv = :id');
            $stmt->execute([':id' => (int) $actual['id_solicitud_serv']]);
            if (!empty($actual['id_detalle_patologia'])) {
                $stmt = $this->conn->prepare('DELETE FROM detalle_patologia WHERE id_detalle_patologia = :id');
                $stmt->execute([':id' => (int) $actual['id_detalle_patologia']]);
            }
            $this->conn->commit();
            return [
                'id_consulta_med' => (int) $actual['id_consulta_med'],
                'beneficiario' => $beneficiario,
                'insumos' => $insumos,
            ];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('MedicinaModel::eliminar - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se puede eliminar: la consulta tiene registros relacionados.')
                : ExcepcionApi::errorInterno('No se pudo eliminar la consulta médica.');
        }
    }

    // ---------- Validaciones de negocio ----------

    /**
     * Carga la consulta con su alcance: un médico no administrativo solo ve
     * las consultas que él mismo atendió; Administrador/Superusuario ven todo.
     */
    private function asegurarAlcance(int $id): array
    {
        $sql = 'SELECT cm.*, ss.id_beneficiario, ss.id_empleado
                FROM consulta_medica cm
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cm.id_solicitud_serv
                WHERE cm.id_consulta_med = :id';
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
            throw ExcepcionApi::noEncontrado('La consulta médica no existe o no está disponible para tu usuario.');
        }
        return $registro;
    }

    /** Requiere los campos editables (no incluye beneficiario ni insumos). */
    private function validarCamposEditables(): void
    {
        foreach (['id_patologia', 'estatura', 'peso', 'tipo_sangre',
                  'motivo_visita', 'diagnostico', 'tratamiento'] as $campo) {
            $valor = $this->__get($campo);
            if ($valor === null || $valor === '') {
                throw ExcepcionApi::validacion("El campo {$campo} es obligatorio.");
            }
        }
        $this->validarPatologia((int) $this->__get('id_patologia'));
    }

    private function validarRequeridos(): void
    {
        foreach (['estatura', 'peso', 'tipo_sangre', 'motivo_visita', 'diagnostico', 'tratamiento'] as $campo) {
            $valor = $this->__get($campo);
            if ($valor === null || $valor === '') {
                throw ExcepcionApi::validacion("El campo {$campo} es obligatorio.");
            }
        }
        if (!$this->__get('id_beneficiario')) {
            throw ExcepcionApi::validacion('El beneficiario es obligatorio.');
        }
        if (!$this->__get('id_patologia')) {
            throw ExcepcionApi::validacion('La patología es obligatoria.');
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

    /** Valida que la patología exista y sea de tipo médica o general. */
    private function validarPatologia(int $idPatologia): void
    {
        $stmt = $this->conn->prepare(
            "SELECT id_patologia FROM patologia
             WHERE id_patologia = :id AND LOWER(tipo_patologia) IN ('médica', 'medica', 'general')"
        );
        $stmt->execute([':id' => $idPatologia]);
        if (!$stmt->fetchColumn()) {
            throw ExcepcionApi::validacion('La patología seleccionada no es válida.');
        }
    }

    /**
     * Normaliza y valida la lista de insumos enviada por el formulario:
     * [{ id_insumo: 1, cantidad: 2 }, ...]. Acumula cantidades repetidas del
     * mismo insumo en una sola fila y exige cantidad >= 1.
     */
    private function normalizarInsumos(mixed $valor): array
    {
        if ($valor === null || $valor === '' || $valor === []) {
            return [];
        }
        if (!is_array($valor)) {
            throw ExcepcionApi::validacion('La lista de insumos no es válida.');
        }

        $acumulados = [];
        foreach ($valor as $fila) {
            if (!is_array($fila) || !isset($fila['id_insumo'], $fila['cantidad'])) {
                throw ExcepcionApi::validacion('Cada insumo debe indicar id_insumo y cantidad.');
            }
            $id = filter_var($fila['id_insumo'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $cantidad = filter_var($fila['cantidad'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false || $cantidad === false) {
                throw ExcepcionApi::validacion('El insumo o la cantidad no son válidos.');
            }
            $acumulados[$id] = ($acumulados[$id] ?? 0) + $cantidad;
        }

        $resultado = [];
        foreach ($acumulados as $id => $cantidad) {
            $resultado[] = ['id_insumo' => $id, 'cantidad' => $cantidad];
        }
        return $resultado;
    }

    private function esAdministrativo(): bool
    {
        return in_array($this->__get('tipo_empleado'), ['Administrador', 'Superusuario'], true);
    }
}
