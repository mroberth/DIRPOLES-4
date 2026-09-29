<?php
namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;

/**
 * ReferenciaModel — módulo Referencias (id_modulo 10 = 'Referencias').
 *
 * Tablas: referencias (flujo de referencias entre áreas) y log_referencias
 * (historial de cambios de estado). FK log_referencias → referencias: al
 * ELIMINAR se borra el log PRIMERO y luego la referencia, en transacción.
 *
 * Reglas permanentes (decisiones del usuario):
 *  - El ADMINISTRADOR elige quién refiere y quién recibe; cualquier otro
 *    empleado es SIEMPRE el origen (se fuerza en servidor, jamás se confía
 *    en lo que envíe el cliente).
 *  - El servicio destino debe ser DISTINTO al de origen (referir entre áreas).
 *  - motivo y observaciones son OBLIGATORIOS al crear (como el sistema viejo).
 *  - Aceptar/Rechazar: solo el empleado DESTINO o un Administrador, y solo
 *    desde estado 'Pendiente' (fila con FOR UPDATE). El rechazo exige motivo
 *    y se guarda en log_referencias.observaciones (NO pisa las observaciones
 *    originales de la referencia).
 *  - Eliminar: solo el ORIGEN o un Administrador, y solo 'Pendiente'
 *    (se borra log_referencias primero por la FK).
 *  - Alcance: sin admin solo se listan/stats referencias donde el empleado
 *    es origen o destino.
 *  - Cada cambio inserta log con estado_anterior REAL (el sistema viejo lo
 *    hardcodeaba en 'Pendiente' y no validaba el estado previo).
 */
class ReferenciaModel extends BusinessModel
{
    private const ESTADOS = ['Pendiente', 'Aceptada', 'Rechazada'];
    private const MAX_MOTIVO       = 255;   // referencias.motivo varchar(255)
    private const MAX_OBSERVACIONES = 2000; // referencias.observaciones text

    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_referencia':
            case 'id_beneficiario':
            case 'id_empleado_origen':
            case 'id_servicio_origen':
            case 'id_empleado_destino':
            case 'id_servicio_destino':
            case 'id_servicio':
            case 'id_empleado':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion("El valor de '{$nombre}' no es válido.");
                }
                $this->atributos[$nombre] = (int) $valor;
                break;

            case 'motivo':
                $valor = trim((string) $valor);
                $valor = strip_tags($valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El motivo de la referencia es obligatorio.');
                }
                if (mb_strlen($valor) < 2 || mb_strlen($valor) > self::MAX_MOTIVO) {
                    throw ExcepcionApi::validacion('El motivo debe tener entre 2 y ' . self::MAX_MOTIVO . ' caracteres.');
                }
                $this->atributos['motivo'] = $valor;
                break;

            case 'observaciones':
                $valor = trim((string) $valor);
                $valor = strip_tags($valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('Las observaciones son obligatorias.');
                }
                if (mb_strlen($valor) < 2 || mb_strlen($valor) > self::MAX_OBSERVACIONES) {
                    throw ExcepcionApi::validacion(
                        'Las observaciones deben tener entre 2 y ' . self::MAX_OBSERVACIONES . ' caracteres.'
                    );
                }
                $this->atributos['observaciones'] = $valor;
                break;

            case 'es_admin':
                $this->atributos['es_admin'] = filter_var($valor, FILTER_VALIDATE_BOOLEAN);
                break;

            case 'limit':
            case 'offset':
                $this->atributos[$nombre] = max(0, (int) $valor);
                break;

            default:
                throw ExcepcionApi::validacion("Atributo no reconocido: '{$nombre}'.");
        }
    }

    public function __get(string $nombre): mixed
    {
        return $this->atributos[$nombre] ?? null;
    }

    //=== Despachador ====
    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'servicios'             => $this->servicios(),
            'empleados_por_servicio' => $this->empleadosPorServicio(),
            'servicio_empleado'     => $this->servicioEmpleado(),
            'beneficiarios'         => $this->beneficiarios(),
            'crear'                 => $this->crear(),
            'listar'                => $this->listar(),
            'obtener'               => $this->obtener(),
            'stats'                 => $this->stats(),
            'aceptar'               => $this->cambiarEstado('Aceptada'),
            'rechazar'              => $this->cambiarEstado('Rechazada'),
            'eliminar'              => $this->eliminar(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en ReferenciaModel: '{$accion}'."),
        };
    }

    //=== Catálogos (para el formulario de creación) ====

    /** Servicios activos (el destino debe elegirse entre estos). */
    private function servicios(): array
    {
        try {
            $stmt = $this->conn->query(
                'SELECT id_servicios, nombre_serv
                 FROM servicio
                 WHERE estatus = 1
                 ORDER BY nombre_serv ASC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('ReferenciaModel::servicios - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los servicios.');
        }
    }

    /**
     * Empleados ACTIVOS de un servicio (cruzado contra dirpoles_security:
     * tipo_empleado.id_servicios define a qué área pertenece cada empleado).
     */
    private function empleadosPorServicio(): array
    {
        try {
            $idServicio = (int) $this->__get('id_servicio');
            if (!$this->servicioActivo($idServicio)) {
                throw ExcepcionApi::validacion('El servicio seleccionado no es válido.');
            }

            $stmt = $this->conn->prepare(
                "SELECT e.id_empleado,
                        CONCAT(e.nombre, ' ', e.apellido) AS nombre_completo
                 FROM dirpoles_security.empleado e
                 INNER JOIN dirpoles_security.tipo_empleado te ON te.id_tipo_emp = e.id_tipo_empleado
                 WHERE te.id_servicios = :servicio AND e.estatus = 1
                 ORDER BY e.nombre, e.apellido"
            );
            $stmt->bindValue(':servicio', $idServicio, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('ReferenciaModel::empleadosPorServicio - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los empleados del servicio.');
        }
    }

    /** Servicio propio del empleado de la sesión (para fijar el ORIGEN). */
    private function servicioEmpleado(): ?array
    {
        try {
            $datos = $this->datosEmpleado((int) $this->__get('id_empleado'));
            if (!$datos) {
                return null;
            }
            $stmt = $this->conn->prepare(
                'SELECT nombre_serv FROM servicio WHERE id_servicios = :id LIMIT 1'
            );
            $stmt->bindValue(':id', (int) $datos['id_servicio'], PDO::PARAM_INT);
            $stmt->execute();
            $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'id_servicio'     => (int) $datos['id_servicio'],
                'nombre_servicio' => $servicio['nombre_serv'] ?? '',
            ];
        } catch (Throwable $e) {
            error_log('ReferenciaModel::servicioEmpleado - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo determinar tu servicio.');
        }
    }

    /** Beneficiarios activos (select del formulario). */
    private function beneficiarios(): array
    {
        try {
            $stmt = $this->conn->query(
                'SELECT id_beneficiario, nombres, apellidos, tipo_cedula, cedula
                 FROM beneficiario
                 WHERE estatus = 1
                 ORDER BY nombres, apellidos'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('ReferenciaModel::beneficiarios - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los beneficiarios.');
        }
    }

    //=== CRUD / flujo ====

    /**
     * Crea la referencia en estado 'Pendiente'.
     * El origen se fuerza al empleado de la sesión salvo que sea admin.
     */
    private function crear(): array
    {
        try {
            $actor = (int) $this->__get('id_empleado');
            if ($actor < 1) {
                throw ExcepcionApi::validacion('No se pudo identificar al empleado que refiere.');
            }

            if (!$this->esAdmin()) {
                $propio = $this->datosEmpleado($actor);
                if (!$propio || (int) $propio['id_servicio'] < 1) {
                    throw ExcepcionApi::validacion(
                        'Tu perfil no tiene un servicio asignado: no puedes crear referencias.'
                    );
                }
                // Origen SIEMPRE el propio (se ignora lo enviado por el cliente).
                $this->atributos['id_empleado_origen'] = $actor;
                $this->atributos['id_servicio_origen'] = (int) $propio['id_servicio'];
            }

            $idBeneficiario = (int) $this->__get('id_beneficiario');
            $servOrigen     = (int) $this->__get('id_servicio_origen');
            $servDestino    = (int) $this->__get('id_servicio_destino');
            $empOrigen      = (int) $this->__get('id_empleado_origen');
            $empDestino     = (int) $this->__get('id_empleado_destino');

            if ($servOrigen === $servDestino) {
                throw ExcepcionApi::validacion('El servicio destino debe ser distinto al servicio de origen.');
            }
            if ($empOrigen === $empDestino) {
                throw ExcepcionApi::validacion('El empleado destino debe ser distinto del empleado de origen.');
            }
            if (!$this->servicioActivo($servOrigen)) {
                throw ExcepcionApi::validacion('El servicio de origen no es válido.');
            }
            if (!$this->servicioActivo($servDestino)) {
                throw ExcepcionApi::validacion('El servicio destino no es válido.');
            }

            $beneficiario = $this->datosBeneficiario($idBeneficiario);
            if (!$beneficiario) {
                throw ExcepcionApi::noEncontrado('El beneficiario seleccionado no existe.');
            }

            $origen = $this->datosEmpleado($empOrigen);
            if (!$origen || (int) $origen['estatus'] !== 1) {
                throw ExcepcionApi::validacion('El empleado de origen no está activo.');
            }
            if ((int) $origen['id_servicio'] !== $servOrigen) {
                throw ExcepcionApi::validacion('El empleado de origen no pertenece al servicio de origen.');
            }

            $destino = $this->datosEmpleado($empDestino);
            if (!$destino || (int) $destino['estatus'] !== 1) {
                throw ExcepcionApi::validacion('El empleado destino no está activo.');
            }
            if ((int) $destino['id_servicio'] !== $servDestino) {
                throw ExcepcionApi::validacion('El empleado destino no pertenece al servicio destino.');
            }

            $stmt = $this->conn->prepare(
                'INSERT INTO referencias
                   (id_beneficiario, id_empleado_origen, id_servicio_origen,
                    id_empleado_destino, id_servicio_destino,
                    fecha_referencia, motivo, estado, observaciones)
                 VALUES
                   (:beneficiario, :empleado_origen, :servicio_origen,
                    :empleado_destino, :servicio_destino,
                    NOW(), :motivo, \'Pendiente\', :observaciones)'
            );
            $stmt->execute([
                ':beneficiario'      => $idBeneficiario,
                ':empleado_origen'   => $empOrigen,
                ':servicio_origen'   => $servOrigen,
                ':empleado_destino'  => $empDestino,
                ':servicio_destino'  => $servDestino,
                ':motivo'            => $this->__get('motivo'),
                ':observaciones'     => $this->__get('observaciones'),
            ]);
            $id = (int) $this->conn->lastInsertId();

            return [
                'id_referencia'        => $id,
                'id_empleado_destino'  => $empDestino,
                'id_empleado_origen'   => $empOrigen,
                'beneficiario'         => trim($beneficiario['nombres'] . ' ' . $beneficiario['apellidos']),
                'nombre_origen'        => $origen['nombre_completo'],
                'nombre_destino'       => $destino['nombre_completo'],
                'estado'               => 'Pendiente',
            ];
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::validacion('Alguno de los datos seleccionados ya no existe. Recarga la página.');
            }
            error_log('ReferenciaModel::crear - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar la referencia.');
        }
    }

    /** Lista las referencias según alcance (la DataTable). */
    private function listar(): array
    {
        try {
            [$where, $parametros] = $this->whereAlcance('r');

            $sql = "SELECT r.id_referencia, r.id_empleado_origen, r.id_empleado_destino,
                           CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                           CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                           CONCAT(e1.nombre, ' ', e1.apellido) AS empleado_origen,
                           s1.nombre_serv AS servicio_origen,
                           CONCAT(e2.nombre, ' ', e2.apellido) AS empleado_destino,
                           s2.nombre_serv AS servicio_destino,
                           r.fecha_referencia, r.estado, r.motivo, r.observaciones
                    FROM referencias r
                    INNER JOIN beneficiario b ON b.id_beneficiario = r.id_beneficiario
                    INNER JOIN dirpoles_security.empleado e1 ON e1.id_empleado = r.id_empleado_origen
                    INNER JOIN servicio s1 ON s1.id_servicios = r.id_servicio_origen
                    LEFT JOIN dirpoles_security.empleado e2 ON e2.id_empleado = r.id_empleado_destino
                    LEFT JOIN servicio s2 ON s2.id_servicios = r.id_servicio_destino"
                . $where
                . ' ORDER BY r.fecha_referencia DESC, r.id_referencia DESC
                    LIMIT :limit OFFSET :offset';

            $stmt = $this->conn->prepare($sql);
            foreach ($parametros as $clave => $valor) {
                $stmt->bindValue($clave, $valor, PDO::PARAM_INT);
            }
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 500), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('ReferenciaModel::listar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron listar las referencias.');
        }
    }

    /** Detalle de una referencia + historial del log (con alcance). */
    private function obtener(): array
    {
        try {
            $id = (int) $this->__get('id_referencia');

            $stmt = $this->conn->prepare(
                "SELECT r.id_referencia, r.id_empleado_origen, r.id_empleado_destino,
                        r.id_servicio_origen, r.id_servicio_destino,
                        CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                        CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_beneficiario,
                        b.telefono AS telefono_beneficiario,
                        CONCAT(e1.nombre, ' ', e1.apellido) AS empleado_origen,
                        s1.nombre_serv AS servicio_origen,
                        CONCAT(e2.nombre, ' ', e2.apellido) AS empleado_destino,
                        s2.nombre_serv AS servicio_destino,
                        r.fecha_referencia, r.estado, r.motivo, r.observaciones
                 FROM referencias r
                 INNER JOIN beneficiario b ON b.id_beneficiario = r.id_beneficiario
                 INNER JOIN dirpoles_security.empleado e1 ON e1.id_empleado = r.id_empleado_origen
                 INNER JOIN servicio s1 ON s1.id_servicios = r.id_servicio_origen
                 LEFT JOIN dirpoles_security.empleado e2 ON e2.id_empleado = r.id_empleado_destino
                 LEFT JOIN servicio s2 ON s2.id_servicios = r.id_servicio_destino
                 WHERE r.id_referencia = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                throw ExcepcionApi::noEncontrado('La referencia no existe.');
            }
            $this->asegurarAlcance((int) $fila['id_empleado_origen'], (int) $fila['id_empleado_destino']);

            $stmt = $this->conn->prepare(
                "SELECT l.estado_anterior, l.estado_nuevo, l.fecha_accion, l.observaciones,
                        CONCAT(e.nombre, ' ', e.apellido) AS empleado
                 FROM log_referencias l
                 INNER JOIN dirpoles_security.empleado e ON e.id_empleado = l.id_empleado
                 WHERE l.id_referencia = :id
                 ORDER BY l.fecha_accion ASC, l.id_log ASC"
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $fila['historial'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $fila;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('ReferenciaModel::obtener - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo consultar la referencia.');
        }
    }

    /**
     * Acepta o rechaza la referencia. Solo el DESTINO o un admin, y solo
     * desde 'Pendiente' (FOR UPDATE contra carreras). El rechazo guarda el
     * motivo obligatorio en el log (no pisa observaciones originales).
     */
    private function cambiarEstado(string $nuevo): array
    {
        try {
            if (!in_array($nuevo, self::ESTADOS, true) || $nuevo === 'Pendiente') {
                throw ExcepcionApi::errorInterno('Estado destino no válido.');
            }
            $id    = (int) $this->__get('id_referencia');
            $actor = (int) $this->__get('id_empleado');
            $motivoRechazo = ($nuevo === 'Rechazada') ? (string) $this->__get('observaciones') : null;

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'SELECT id_empleado_origen, id_empleado_destino, estado
                 FROM referencias WHERE id_referencia = :id FOR UPDATE'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $referencia = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$referencia) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('La referencia no existe.');
            }

            // Aceptar/rechazar: solo el destino o un administrador.
            if (!$this->esAdmin() && (int) $referencia['id_empleado_destino'] !== $actor) {
                $this->conn->rollBack();
                throw ExcepcionApi::accesoDenegado('Solo el empleado al que se le refirió puede gestionar esta referencia.');
            }

            if ($referencia['estado'] !== 'Pendiente') {
                $this->conn->rollBack();
                throw ExcepcionApi::validacion(
                    'La referencia ya fue respondida (estado actual: ' . $referencia['estado'] . ').'
                );
            }

            $stmt = $this->conn->prepare(
                'UPDATE referencias SET estado = :nuevo WHERE id_referencia = :id'
            );
            $stmt->execute([':nuevo' => $nuevo, ':id' => $id]);

            $observaciones = ($nuevo === 'Rechazada')
                ? $motivoRechazo
                : ($nuevo === 'Aceptada' ? 'Referencia aceptada' : null);

            $stmt = $this->conn->prepare(
                'INSERT INTO log_referencias
                   (id_referencia, estado_anterior, estado_nuevo, id_empleado, fecha_accion, observaciones)
                 VALUES (:referencia, :anterior, :nuevo, :empleado, NOW(), :observaciones)'
            );
            $stmt->execute([
                ':referencia'    => $id,
                ':anterior'      => $referencia['estado'],
                ':nuevo'         => $nuevo,
                ':empleado'      => $actor,
                ':observaciones' => $observaciones,
            ]);

            $this->conn->commit();

            return [
                'id_referencia'       => $id,
                'estado'              => $nuevo,
                'id_empleado_origen'  => (int) $referencia['id_empleado_origen'],
                'id_empleado_destino' => (int) $referencia['id_empleado_destino'],
            ];
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('ReferenciaModel::cambiarEstado - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar la referencia.');
        }
    }

    /**
     * Elimina la referencia. Solo ORIGEN o admin, y solo 'Pendiente'.
     * Borra log_referencias primero (FK) y luego la fila, en transacción.
     */
    private function eliminar(): array
    {
        try {
            $id    = (int) $this->__get('id_referencia');
            $actor = (int) $this->__get('id_empleado');

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'SELECT id_referencia, id_empleado_origen, id_empleado_destino, estado, motivo
                 FROM referencias WHERE id_referencia = :id FOR UPDATE'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $referencia = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$referencia) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('La referencia no existe.');
            }

            // Eliminar: solo el origen (o un admin).
            if (!$this->esAdmin() && (int) $referencia['id_empleado_origen'] !== $actor) {
                $this->conn->rollBack();
                throw ExcepcionApi::accesoDenegado('Solo quien creó la referencia puede eliminarla.');
            }

            if ($referencia['estado'] !== 'Pendiente') {
                $this->conn->rollBack();
                throw ExcepcionApi::validacion(
                    'Solo se pueden eliminar referencias pendientes (estado actual: ' . $referencia['estado'] . ').'
                );
            }

            $stmt = $this->conn->prepare('DELETE FROM log_referencias WHERE id_referencia = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->conn->prepare('DELETE FROM referencias WHERE id_referencia = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();

            $referencia['id_referencia'] = $id;
            return $referencia;
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::enUso('No se puede eliminar: la referencia tiene registros asociados.');
            }
            error_log('ReferenciaModel::eliminar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar la referencia.');
        }
    }

    //=== Estadísticas ====

    /** Tarjetas del módulo: total, pendientes, aceptadas y del mes (con alcance). */
    private function stats(): array
    {
        try {
            [$where, $parametros] = $this->whereAlcance('r');

            $consultas = [
                'referencias_total'      => 'SELECT COUNT(*) FROM referencias r' . $where,
                'referencias_pendientes' => "SELECT COUNT(*) FROM referencias r" . $where
                    . ($where === '' ? ' WHERE' : ' AND') . " r.estado = 'Pendiente'",
                'referencias_aceptadas'  => "SELECT COUNT(*) FROM referencias r" . $where
                    . ($where === '' ? ' WHERE' : ' AND') . " r.estado = 'Aceptada'",
                'referencias_mes'        => "SELECT COUNT(*) FROM referencias r" . $where
                    . ($where === '' ? ' WHERE' : ' AND')
                    . ' MONTH(r.fecha_referencia) = MONTH(CURRENT_DATE())
                       AND YEAR(r.fecha_referencia) = YEAR(CURRENT_DATE())',
            ];

            $resultado = [];
            foreach ($consultas as $clave => $sql) {
                $stmt = $this->conn->prepare($sql);
                foreach ($parametros as $k => $v) {
                    $stmt->bindValue($k, $v, PDO::PARAM_INT);
                }
                $stmt->execute();
                $resultado[$clave] = (int) $stmt->fetchColumn();
            }
            return $resultado;
        } catch (Throwable $e) {
            error_log('ReferenciaModel::stats - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar las estadísticas de referencias.');
        }
    }

    //=== Helpers privados ====

    private function esAdmin(): bool
    {
        return (bool) ($this->__get('es_admin') ?? false);
    }

    /** Alcance de datos: admin ve todo; el resto solo lo suyo (origen/destino). */
    private function asegurarAlcance(int $idOrigen, int $idDestino): void
    {
        if ($this->esAdmin()) {
            return;
        }
        $actor = (int) $this->__get('id_empleado');
        if ($actor !== $idOrigen && $actor !== $idDestino) {
            throw ExcepcionApi::accesoDenegado('Esta referencia no está en tu ámbito de consulta.');
        }
    }

    /** Devuelve ['WHERE ...', [':id_empleado' => X]] o ['', []] para admin.
     *  El placeholder aparece UNA sola vez (IN) por compatibilidad con
     *  prepares nativos de PDO, donde un parámetro repetido falla. */
    private function whereAlcance(string $alias = ''): array
    {
        if ($this->esAdmin()) {
            return ['', []];
        }
        $prefijo = $alias !== '' ? $alias . '.' : '';
        return [
            ' WHERE :id_empleado IN (' . $prefijo . 'id_empleado_origen, '
                . $prefijo . 'id_empleado_destino)',
            [':id_empleado' => (int) $this->__get('id_empleado')],
        ];
    }

    /** Datos básicos de un empleado activo + su servicio (id_servicio). */
    private function datosEmpleado(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT e.id_empleado, e.estatus, e.nombre, e.apellido, te.id_servicios AS id_servicio,
                    CONCAT(e.nombre, ' ', e.apellido) AS nombre_completo
             FROM dirpoles_security.empleado e
             INNER JOIN dirpoles_security.tipo_empleado te ON te.id_tipo_emp = e.id_tipo_empleado
             WHERE e.id_empleado = :id
             LIMIT 1"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    private function datosBeneficiario(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT id_beneficiario, nombres, apellidos, estatus
             FROM beneficiario WHERE id_beneficiario = :id LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila || (int) $fila['estatus'] !== 1) {
            return null;
        }
        return $fila;
    }

    private function servicioActivo(int $id): bool
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM servicio WHERE id_servicios = :id AND estatus = 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }
}
