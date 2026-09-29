<?php
namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;

/**
 * MobiliarioModel — módulo Mobiliario (id_modulo 12 = 'Mobiliario').
 *
 * Tablas: mobiliario, equipos, fichas_tecnicas, detalle_ficha_mobiliario,
 * detalle_ficha_equipo e historial_inventario (kardex de movimientos).
 * Reglas permanentes (decisiones del usuario, 2026-09-29):
 *  - Ficha técnica: UNA por empleado responsable (activa), y SÍ enlaza ítems
 *    reales (reactiva detalle_ficha_* que el sistema viejo nunca usó).
 *  - Alta pieza a pieza; el historial registra alta ('asignacion'),
 *    reubicación ('reubicacion'), modificación ('modificacion') y baja lógica
 *    ('baja'); el sistema viejo solo registraba altas.
 *  - Equipos: serial ÚNICO y obligatorio (validación remota con id_excluir).
 *  - La disponibilidad de mobiliario se calcula (cantidad − asignado en fichas
 *    activas) sin mutar mobiliario.cantidad; un equipo no puede estar en dos
 *    fichas activas.
 *  - Baja lógica (estatus 'Inactivo') bloqueada si el ítem está en una ficha
 *    activa; eliminación bloqueada si está en detalle_ficha_* o inventario_mob.
 *  - La edición de mobiliario no puede bajar la cantidad por debajo de lo ya
 *    asignado en fichas activas; estatus nunca se edita (solo baja).
 */
class MobiliarioModel extends BusinessModel
{
    private const ESTADOS     = ['Nuevo', 'Bueno', 'Regular', 'Malo', 'En reparación'];
    private const TIPOS_ITEM  = ['mobiliario', 'equipo'];
    private const LIMITE_INT  = 2147483647; // columna INT de MySQL
    private const MAX_DETALLES = 100;       // filas máximas por ficha

    private const RX_SERIO = '/^[A-Za-z0-9.\-\/_]{3,100}$/';
    private const RX_TEXTO = '/^[\p{L}\p{N}\s,.\-#¿¡!?:;()°%ºª\/]+$/u';

    private array $atributos = [];

    //=== Validación por asignación ====

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_tipo_mobiliario':
                $this->atributos[$nombre] = $this->enteroPositivo($valor, 'Debes seleccionar el tipo de mobiliario.');
                break;

            case 'id_tipo_equipo':
                $this->atributos[$nombre] = $this->enteroPositivo($valor, 'Debes seleccionar el tipo de equipo.');
                break;

            case 'id_servicios':
                $this->atributos[$nombre] = $this->enteroPositivo($valor, 'Debes seleccionar la ubicación (servicio).');
                break;

            case 'id_servicio':
                $this->atributos[$nombre] = $this->enteroPositivo($valor, 'Debes seleccionar el servicio de la ficha.');
                break;

            case 'id_empleado_responsable':
                $this->atributos[$nombre] = $this->enteroPositivo($valor, 'Debes seleccionar el empleado responsable.');
                break;

            case 'id_empleado':
                $this->atributos[$nombre] = $this->enteroPositivo($valor, 'No se pudo identificar al empleado responsable.');
                break;

            case 'id_mobiliario':
                $this->atributos[$nombre] = $this->enteroPositivo($valor, 'El mobiliario seleccionado no es válido.');
                break;

            case 'id_equipo':
                $this->atributos[$nombre] = $this->enteroPositivo($valor, 'El equipo seleccionado no es válido.');
                break;

            case 'id_ficha':
                $this->atributos[$nombre] = $this->enteroPositivo($valor, 'La ficha seleccionada no es válida.');
                break;

            case 'cantidad':
                if (filter_var($valor, FILTER_VALIDATE_INT) === false || (int) $valor < 1) {
                    throw ExcepcionApi::validacion('La cantidad debe ser un número entero mayor o igual a 1.');
                }
                if ((int) $valor > self::LIMITE_INT) {
                    throw ExcepcionApi::validacion('La cantidad es demasiado grande.');
                }
                $this->atributos['cantidad'] = (int) $valor;
                break;

            case 'estado':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::ESTADOS, true)) {
                    throw ExcepcionApi::validacion('El estado del ítem no es válido.');
                }
                $this->atributos['estado'] = $valor;
                break;

            case 'serial':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El serial del equipo es obligatorio.');
                }
                if (strlen($valor) < 3 || strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El serial debe tener entre 3 y 100 caracteres.');
                }
                if (!preg_match(self::RX_SERIO, $valor)) {
                    throw ExcepcionApi::validacion(
                        'El serial solo puede contener letras, números, puntos, guiones, barras y guiones bajos.'
                    );
                }
                $this->atributos['serial'] = $valor;
                break;

            case 'nombre_ficha':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El nombre de la ficha es obligatorio.');
                }
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El nombre de la ficha no puede superar 100 caracteres.');
                }
                if (!preg_match('/^[\p{L}\p{N}\s.,\-#°%ºª\/]+$/u', $valor)) {
                    throw ExcepcionApi::validacion('El nombre de la ficha contiene caracteres no permitidos.');
                }
                $this->atributos['nombre_ficha'] = $valor;
                break;

            case 'marca':
            case 'modelo':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    $this->atributos[$nombre] = null;
                    break;
                }
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion("El campo '{$nombre}' no puede superar 100 caracteres.");
                }
                if (!preg_match(self::RX_TEXTO, $valor)) {
                    throw ExcepcionApi::validacion("El campo '{$nombre}' contiene caracteres no permitidos.");
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'color':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    $this->atributos['color'] = null;
                    break;
                }
                if (mb_strlen($valor) > 50) {
                    throw ExcepcionApi::validacion('El color no puede superar 50 caracteres.');
                }
                if (!preg_match(self::RX_TEXTO, $valor)) {
                    throw ExcepcionApi::validacion('El color contiene caracteres no permitidos.');
                }
                $this->atributos['color'] = $valor;
                break;

            case 'fecha_adquisicion':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    $this->atributos['fecha_adquisicion'] = null;
                    break;
                }
                $fecha = \DateTime::createFromFormat('Y-m-d', $valor);
                if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
                    throw ExcepcionApi::validacion('La fecha de adquisición no es válida.');
                }
                if ($valor > date('Y-m-d')) {
                    throw ExcepcionApi::validacion('La fecha de adquisición no puede ser futura.');
                }
                $this->atributos['fecha_adquisicion'] = $valor;
                break;

            case 'descripcion':
            case 'observaciones':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    $this->atributos[$nombre] = null;
                    break;
                }
                if (mb_strlen($valor) > 500) {
                    throw ExcepcionApi::validacion("El campo '{$nombre}' no puede superar 500 caracteres.");
                }
                if (!preg_match(self::RX_TEXTO, $valor)) {
                    throw ExcepcionApi::validacion("El campo '{$nombre}' contiene caracteres no permitidos.");
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'tipo_item':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::TIPOS_ITEM, true)) {
                    throw ExcepcionApi::validacion('El tipo de ítem no es válido.');
                }
                $this->atributos['tipo_item'] = $valor;
                break;

            case 'detalles_mobiliario':
                if (!is_array($valor)) {
                    throw ExcepcionApi::validacion('Los detalles de mobiliario no son válidos.');
                }
                if (count($valor) > self::MAX_DETALLES) {
                    throw ExcepcionApi::validacion('La ficha no puede superar ' . self::MAX_DETALLES . ' filas de mobiliario.');
                }
                $limpios = [];
                $vistos  = [];
                foreach ($valor as $fila) {
                    if (!is_array($fila)) {
                        throw ExcepcionApi::validacion('Los detalles de mobiliario no son válidos.');
                    }
                    $id = filter_var($fila['id_mobiliario'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    $cant = filter_var($fila['cantidad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false || $cant === false || (int) $cant > self::LIMITE_INT) {
                        throw ExcepcionApi::validacion('Cada fila de mobiliario necesita un ítem y una cantidad válida.');
                    }
                    if (isset($vistos[$id])) {
                        throw ExcepcionApi::validacion('No puedes repetir el mismo mobiliario en la ficha: junta las cantidades en una sola fila.');
                    }
                    $vistos[$id] = true;
                    $limpios[] = ['id_mobiliario' => (int) $id, 'cantidad' => (int) $cant];
                }
                $this->atributos['detalles_mobiliario'] = $limpios;
                break;

            case 'detalles_equipo':
                if (!is_array($valor)) {
                    throw ExcepcionApi::validacion('Los detalles de equipos no son válidos.');
                }
                if (count($valor) > self::MAX_DETALLES) {
                    throw ExcepcionApi::validacion('La ficha no puede superar ' . self::MAX_DETALLES . ' equipos.');
                }
                $limpios = [];
                $vistos  = [];
                foreach ($valor as $fila) {
                    $id = is_array($fila) ? ($fila['id_equipo'] ?? null) : $fila;
                    $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                    if ($id === false) {
                        throw ExcepcionApi::validacion('Cada fila de equipo necesita un ítem válido.');
                    }
                    if (isset($vistos[$id])) {
                        throw ExcepcionApi::validacion('No puedes repetir el mismo equipo en la ficha.');
                    }
                    $vistos[$id] = true;
                    $limpios[] = (int) $id;
                }
                $this->atributos['detalles_equipo'] = $limpios;
                break;

            case 'id_excluir':
                // En edición: id del propio registro que NO debe contarse como
                // duplicado (serial de equipo, ficha del empleado, ficha propia
                // al calcular la disponibilidad de sus ítems).
                $this->atributos['id_excluir'] = max(0, (int) $valor);
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
            'catalogos'            => $this->catalogos(),
            'listar_mobiliario'    => $this->listarMobiliario(),
            'listar_equipos'       => $this->listarEquipos(),
            'listar_fichas'        => $this->listarFichas(),
            'historial'            => $this->historial(),
            'obtener_mobiliario'   => $this->obtenerMobiliario(),
            'obtener_equipo'       => $this->obtenerEquipo(),
            'obtener_ficha'        => $this->obtenerFicha(),
            'crear_mobiliario'     => $this->crearMobiliario(),
            'crear_equipo'         => $this->crearEquipo(),
            'crear_ficha'          => $this->crearFicha(),
            'actualizar_mobiliario' => $this->actualizarMobiliario(),
            'actualizar_equipo'    => $this->actualizarEquipo(),
            'actualizar_ficha'     => $this->actualizarFicha(),
            'eliminar_mobiliario'  => $this->eliminarMobiliario(),
            'eliminar_equipo'      => $this->eliminarEquipo(),
            'eliminar_ficha'       => $this->eliminarFicha(),
            'reubicar'             => $this->reubicar(),
            'baja'                 => $this->baja(),
            'stats'                => $this->stats(),
            'items_disponibles'    => $this->itemsDisponibles(),
            'existe_serial'        => $this->existeSerial(
                (string) $this->__get('serial'),
                (int) $this->__get('id_excluir')
            ),
            'existe_ficha_empleado' => $this->existeFichaEmpleado(
                (int) $this->__get('id_empleado_responsable'),
                (int) $this->__get('id_excluir')
            ),
            default => throw ExcepcionApi::errorInterno("Acción no válida en MobiliarioModel: '{$accion}'."),
        };
    }

    //=== Catálogos ====

    /** Tipos, servicios y empleados activos para los <select> del módulo. */
    private function catalogos(): array
    {
        try {
            $tiposMob = $this->conn->query(
                'SELECT id_tipo_mobiliario, nombre FROM tipo_mobiliario WHERE estatus = 1 ORDER BY nombre ASC'
            )->fetchAll(PDO::FETCH_ASSOC);

            $tiposEq = $this->conn->query(
                'SELECT id_tipo_equipo, nombre FROM tipo_equipo WHERE estatus = 1 ORDER BY nombre ASC'
            )->fetchAll(PDO::FETCH_ASSOC);

            $servicios = $this->conn->query(
                'SELECT id_servicios, nombre_serv FROM servicio WHERE estatus = 1 ORDER BY nombre_serv ASC'
            )->fetchAll(PDO::FETCH_ASSOC);

            $empleados = $this->conn->query(
                "SELECT id_empleado, CONCAT(nombre, ' ', IFNULL(apellido, '')) AS nombre_completo
                 FROM dirpoles_security.empleado
                 WHERE estatus = 1
                 ORDER BY nombre ASC"
            )->fetchAll(PDO::FETCH_ASSOC);

            return [
                'tipos_mobiliario' => $tiposMob,
                'tipos_equipo'     => $tiposEq,
                'servicios'        => $servicios,
                'empleados'        => $empleados,
            ];
        } catch (Throwable $e) {
            error_log('MobiliarioModel::catalogos - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los catálogos del módulo.');
        }
    }

    //=== Consultas (DataTables) ====

    /** Mobiliario con tipo, ubicación y unidades disponibles. */
    private function listarMobiliario(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT m.id_mobiliario, m.id_tipo_mobiliario, tm.nombre AS tipo_mobiliario,
                        m.id_servicios, s.nombre_serv AS servicio, m.cantidad, m.estado, m.estatus,
                        m.marca, m.modelo, m.color, m.fecha_adquisicion, m.fecha_registro,
                        m.descripcion_adicional, m.observaciones,
                        x.disponible
                 FROM mobiliario m
                 LEFT JOIN tipo_mobiliario tm ON tm.id_tipo_mobiliario = m.id_tipo_mobiliario
                 LEFT JOIN servicio s ON s.id_servicios = m.id_servicios
                 INNER JOIN (
                     SELECT mm.id_mobiliario,
                            mm.cantidad - IFNULL((
                                SELECT SUM(d.cantidad)
                                FROM detalle_ficha_mobiliario d
                                INNER JOIN fichas_tecnicas f
                                    ON f.id_ficha = d.id_ficha AND f.estatus = 1
                                WHERE d.id_mobiliario = mm.id_mobiliario
                            ), 0) AS disponible
                     FROM mobiliario mm
                 ) x ON x.id_mobiliario = m.id_mobiliario
                 ORDER BY m.id_mobiliario DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('MobiliarioModel::listarMobiliario - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo listar el mobiliario.');
        }
    }

    /** Equipos con tipo, ubicación y ficha activa en la que están asignados. */
    private function listarEquipos(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT e.id_equipo, e.id_tipo_equipo, te.nombre AS tipo_equipo,
                        e.id_servicios, s.nombre_serv AS servicio, e.serial, e.marca, e.modelo,
                        e.color, e.estado, e.estatus, e.fecha_adquisicion, e.fecha_registro,
                        e.descripcion, e.observaciones,
                        (SELECT f.nombre_ficha
                         FROM detalle_ficha_equipo d
                         INNER JOIN fichas_tecnicas f
                             ON f.id_ficha = d.id_ficha AND f.estatus = 1
                         WHERE d.id_equipo = e.id_equipo
                         LIMIT 1) AS ficha_activa
                 FROM equipos e
                 LEFT JOIN tipo_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
                 LEFT JOIN servicio s ON s.id_servicios = e.id_servicios
                 ORDER BY e.id_equipo DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('MobiliarioModel::listarEquipos - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron listar los equipos.');
        }
    }

    /** Fichas técnicas con responsable, servicio y conteo de ítems. */
    private function listarFichas(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT ft.id_ficha, ft.nombre_ficha, ft.id_servicio, s.nombre_serv AS servicio,
                        ft.id_empleado_responsable,
                        CONCAT(emp.nombre, ' ', IFNULL(emp.apellido, '')) AS responsable,
                        ft.descripcion, ft.fecha_creacion, ft.estatus,
                        (SELECT COUNT(*) FROM detalle_ficha_mobiliario d WHERE d.id_ficha = ft.id_ficha) AS total_mobiliario,
                        (SELECT COUNT(*) FROM detalle_ficha_equipo d WHERE d.id_ficha = ft.id_ficha) AS total_equipos
                 FROM fichas_tecnicas ft
                 LEFT JOIN dirpoles_security.empleado emp ON emp.id_empleado = ft.id_empleado_responsable
                 LEFT JOIN servicio s ON s.id_servicios = ft.id_servicio
                 ORDER BY ft.id_ficha DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('MobiliarioModel::listarFichas - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron listar las fichas técnicas.');
        }
    }

    /** Kardex: movimientos de mobiliario y equipos con responsable. */
    private function historial(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT hi.id_historial, hi.id_empleado,
                        CONCAT(emp.nombre, ' ', IFNULL(emp.apellido, '')) AS responsable,
                        hi.tipo_item, hi.id_item, hi.tipo_movimiento, hi.id_ficha,
                        hi.id_servicio_anterior, sa.nombre_serv AS servicio_anterior,
                        hi.id_servicio_nuevo, sn.nombre_serv AS servicio_nuevo,
                        hi.descripcion, hi.fecha_movimiento,
                        CASE WHEN hi.tipo_item = 'mobiliario' THEN tm.nombre
                             WHEN hi.tipo_item = 'equipo' THEN te.nombre
                        END AS nombre_tipo,
                        CASE WHEN hi.tipo_item = 'mobiliario' THEN CONCAT('Mobiliario #', hi.id_item)
                             WHEN hi.tipo_item = 'equipo' THEN CONCAT('Equipo #', hi.id_item)
                        END AS etiqueta_item
                 FROM historial_inventario hi
                 INNER JOIN dirpoles_security.empleado emp ON emp.id_empleado = hi.id_empleado
                 LEFT JOIN servicio sn ON sn.id_servicios = hi.id_servicio_nuevo
                 LEFT JOIN servicio sa ON sa.id_servicios = hi.id_servicio_anterior
                 LEFT JOIN mobiliario m ON hi.tipo_item = 'mobiliario' AND m.id_mobiliario = hi.id_item
                 LEFT JOIN tipo_mobiliario tm ON tm.id_tipo_mobiliario = m.id_tipo_mobiliario
                 LEFT JOIN equipos eq ON hi.tipo_item = 'equipo' AND eq.id_equipo = hi.id_item
                 LEFT JOIN tipo_equipo te ON te.id_tipo_equipo = eq.id_tipo_equipo
                 ORDER BY hi.id_historial DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 500), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('MobiliarioModel::historial - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo cargar el historial de movimientos.');
        }
    }

    //=== Obtener (detalle / edición) ====

    private function obtenerMobiliario(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT m.*, tm.nombre AS tipo_mobiliario, s.nombre_serv AS servicio
                 FROM mobiliario m
                 LEFT JOIN tipo_mobiliario tm ON tm.id_tipo_mobiliario = m.id_tipo_mobiliario
                 LEFT JOIN servicio s ON s.id_servicios = m.id_servicios
                 WHERE m.id_mobiliario = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', $this->__get('id_mobiliario'), PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$fila) {
                throw ExcepcionApi::noEncontrado('El mobiliario no existe.');
            }
            return $fila;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('MobiliarioModel::obtenerMobiliario - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo consultar el mobiliario.');
        }
    }

    private function obtenerEquipo(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT e.*, te.nombre AS tipo_equipo, s.nombre_serv AS servicio
                 FROM equipos e
                 LEFT JOIN tipo_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
                 LEFT JOIN servicio s ON s.id_servicios = e.id_servicios
                 WHERE e.id_equipo = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', $this->__get('id_equipo'), PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$fila) {
                throw ExcepcionApi::noEncontrado('El equipo no existe.');
            }
            return $fila;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('MobiliarioModel::obtenerEquipo - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo consultar el equipo.');
        }
    }

    /** Ficha con responsable, servicio y sus ítems ya asignados. */
    private function obtenerFicha(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT ft.*, s.nombre_serv AS servicio,
                        CONCAT(emp.nombre, ' ', IFNULL(emp.apellido, '')) AS responsable
                 FROM fichas_tecnicas ft
                 LEFT JOIN dirpoles_security.empleado emp ON emp.id_empleado = ft.id_empleado_responsable
                 LEFT JOIN servicio s ON s.id_servicios = ft.id_servicio
                 WHERE ft.id_ficha = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', $this->__get('id_ficha'), PDO::PARAM_INT);
            $stmt->execute();
            $ficha = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$ficha) {
                throw ExcepcionApi::noEncontrado('La ficha técnica no existe.');
            }

            $stmt = $this->conn->prepare(
                "SELECT d.id_mobiliario, d.cantidad, tm.nombre AS tipo_mobiliario, s.nombre_serv AS servicio
                 FROM detalle_ficha_mobiliario d
                 INNER JOIN mobiliario m ON m.id_mobiliario = d.id_mobiliario
                 LEFT JOIN tipo_mobiliario tm ON tm.id_tipo_mobiliario = m.id_tipo_mobiliario
                 LEFT JOIN servicio s ON s.id_servicios = m.id_servicios
                 WHERE d.id_ficha = :id
                 ORDER BY d.id_detalle ASC"
            );
            $stmt->bindValue(':id', $this->__get('id_ficha'), PDO::PARAM_INT);
            $stmt->execute();
            $ficha['detalles_mobiliario'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->conn->prepare(
                "SELECT d.id_equipo, e.serial, te.nombre AS tipo_equipo, s.nombre_serv AS servicio
                 FROM detalle_ficha_equipo d
                 INNER JOIN equipos e ON e.id_equipo = d.id_equipo
                 LEFT JOIN tipo_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
                 LEFT JOIN servicio s ON s.id_servicios = e.id_servicios
                 WHERE d.id_ficha = :id
                 ORDER BY d.id_detalle ASC"
            );
            $stmt->bindValue(':id', $this->__get('id_ficha'), PDO::PARAM_INT);
            $stmt->execute();
            $ficha['detalles_equipo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $ficha;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('MobiliarioModel::obtenerFicha - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo consultar la ficha técnica.');
        }
    }

    //=== Crear ====

    /** Alta de mobiliario + movimiento 'asignacion' en el historial. */
    private function crearMobiliario(): array
    {
        $tipoNombre    = $this->tipoMobiliarioActivo((int) $this->__get('id_tipo_mobiliario'));
        $servicioNombre = $this->servicioActivo((int) $this->__get('id_servicios'));

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "INSERT INTO mobiliario
                   (id_tipo_mobiliario, id_servicios, cantidad, estado, marca, modelo, color,
                    fecha_adquisicion, descripcion_adicional, observaciones)
                 VALUES
                   (:tipo, :servicios, :cantidad, :estado, :marca, :modelo, :color,
                    :fecha, :descripcion, :observaciones)"
            );
            $stmt->execute([
                ':tipo'          => $this->__get('id_tipo_mobiliario'),
                ':servicios'     => $this->__get('id_servicios'),
                ':cantidad'      => $this->__get('cantidad'),
                ':estado'        => $this->__get('estado'),
                ':marca'         => $this->__get('marca'),
                ':modelo'        => $this->__get('modelo'),
                ':color'         => $this->__get('color'),
                ':fecha'         => $this->__get('fecha_adquisicion'),
                ':descripcion'   => $this->__get('descripcion'),
                ':observaciones' => $this->__get('observaciones'),
            ]);
            $id = (int) $this->conn->lastInsertId();

            $this->registrarHistorial(
                'mobiliario', $id, 'asignacion', null, null,
                (int) $this->__get('id_servicios'),
                'Alta de mobiliario: ' . $tipoNombre
            );

            $this->conn->commit();

            return [
                'id_mobiliario' => $id,
                'resumen'       => sprintf('%s x%d en %s', $tipoNombre, (int) $this->__get('cantidad'), $servicioNombre),
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
            error_log('MobiliarioModel::crearMobiliario - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar el mobiliario.');
        }
    }

    /** Alta de equipo (serial único) + movimiento 'asignacion'. */
    private function crearEquipo(): array
    {
        $tipoNombre     = $this->tipoEquipoActivo((int) $this->__get('id_tipo_equipo'));
        $servicioNombre = $this->servicioActivo((int) $this->__get('id_servicios'));

        if ($this->existeSerial((string) $this->__get('serial'))) {
            throw ExcepcionApi::yaExiste('Ya existe un equipo registrado con ese serial.');
        }

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "INSERT INTO equipos
                   (id_tipo_equipo, id_servicios, marca, modelo, serial, color, estado,
                    fecha_adquisicion, descripcion, observaciones)
                 VALUES
                   (:tipo, :servicios, :marca, :modelo, :serial, :color, :estado,
                    :fecha, :descripcion, :observaciones)"
            );
            $stmt->execute([
                ':tipo'          => $this->__get('id_tipo_equipo'),
                ':servicios'     => $this->__get('id_servicios'),
                ':marca'         => $this->__get('marca'),
                ':modelo'        => $this->__get('modelo'),
                ':serial'        => $this->__get('serial'),
                ':color'         => $this->__get('color'),
                ':estado'        => $this->__get('estado'),
                ':fecha'         => $this->__get('fecha_adquisicion'),
                ':descripcion'   => $this->__get('descripcion'),
                ':observaciones' => $this->__get('observaciones'),
            ]);
            $id = (int) $this->conn->lastInsertId();

            $this->registrarHistorial(
                'equipo', $id, 'asignacion', null, null,
                (int) $this->__get('id_servicios'),
                'Alta de equipo: ' . $tipoNombre . ' (serial ' . $this->__get('serial') . ')'
            );

            $this->conn->commit();

            return [
                'id_equipo' => $id,
                'resumen'   => sprintf('%s · serial %s · %s', $tipoNombre, $this->__get('serial'), $servicioNombre),
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
            error_log('MobiliarioModel::crearEquipo - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar el equipo.');
        }
    }

    /**
     * Ficha técnica: UNA activa por empleado, con sus ítems (mobiliario con
     * cantidad y equipos) en transacción. No escribe historial: la membresía
     * vive en detalle_ficha_*.
     */
    private function crearFicha(): array
    {
        $servicioNombre  = $this->servicioActivo((int) $this->__get('id_servicio'));
        $empleadoNombre  = $this->empleadoActivo((int) $this->__get('id_empleado_responsable'));
        $detallesMob     = (array) ($this->__get('detalles_mobiliario') ?? []);
        $detallesEq      = (array) ($this->__get('detalles_equipo') ?? []);

        if ($this->existeFichaEmpleado((int) $this->__get('id_empleado_responsable'))) {
            throw ExcepcionApi::yaExiste('Ese empleado ya tiene una ficha técnica activa.');
        }
        $this->validarDetalles($detallesMob, $detallesEq, 0);
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "INSERT INTO fichas_tecnicas
                   (nombre_ficha, id_servicio, id_empleado_responsable, descripcion, fecha_creacion, estatus)
                 VALUES
                   (:nombre, :servicio, :empleado, :descripcion, CURDATE(), 1)"
            );
            $stmt->execute([
                ':nombre'      => $this->__get('nombre_ficha'),
                ':servicio'    => $this->__get('id_servicio'),
                ':empleado'    => $this->__get('id_empleado_responsable'),
                ':descripcion' => $this->__get('descripcion'),
            ]);
            $idFicha = (int) $this->conn->lastInsertId();

            $this->insertarDetalles($idFicha, $detallesMob, $detallesEq);

            $this->conn->commit();

            return [
                'id_ficha' => $idFicha,
                'resumen'  => $this->__get('nombre_ficha') . ' — ' . $empleadoNombre . ' (' . $servicioNombre . ')',
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
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('Ese empleado ya tiene una ficha técnica registrada.');
            }
            error_log('MobiliarioModel::crearFicha - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar la ficha técnica.');
        }
    }

    //=== Actualizar ====

    /**
     * Edición del mobiliario: jamás cambia estatus; no baja la cantidad por
     * debajo de lo ya asignado en fichas activas; registra 'reubicacion' si
     * cambia la ubicación o 'modificacion' en caso contrario.
     */
    private function actualizarMobiliario(): array
    {
        $id             = (int) $this->__get('id_mobiliario');
        $tipoNombre     = $this->tipoMobiliarioActivo((int) $this->__get('id_tipo_mobiliario'));
        $servicioNombre = $this->servicioActivo((int) $this->__get('id_servicios'));

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'SELECT id_servicios FROM mobiliario WHERE id_mobiliario = :id FOR UPDATE'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $actual = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$actual) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('El mobiliario no existe.');
            }

            $asignado = $this->asignadoEnFichasActivas('mobiliario', $id);
            if ((int) $this->__get('cantidad') < $asignado) {
                $this->conn->rollBack();
                throw ExcepcionApi::validacion(sprintf(
                    'No puedes dejar la cantidad en %d: hay %d unidades asignadas en fichas activas. Quita primero las unidades de la ficha.',
                    (int) $this->__get('cantidad'),
                    $asignado
                ));
            }

            $stmt = $this->conn->prepare(
                "UPDATE mobiliario
                 SET id_tipo_mobiliario = :tipo, id_servicios = :servicios, cantidad = :cantidad,
                     estado = :estado, marca = :marca, modelo = :modelo, color = :color,
                     fecha_adquisicion = :fecha, descripcion_adicional = :descripcion,
                     observaciones = :observaciones
                 WHERE id_mobiliario = :id"
            );
            $stmt->execute([
                ':tipo'          => $this->__get('id_tipo_mobiliario'),
                ':servicios'     => $this->__get('id_servicios'),
                ':cantidad'      => $this->__get('cantidad'),
                ':estado'        => $this->__get('estado'),
                ':marca'         => $this->__get('marca'),
                ':modelo'        => $this->__get('modelo'),
                ':color'         => $this->__get('color'),
                ':fecha'         => $this->__get('fecha_adquisicion'),
                ':descripcion'   => $this->__get('descripcion'),
                ':observaciones' => $this->__get('observaciones'),
                ':id'            => $id,
            ]);

            $anterior = (int) $actual['id_servicios'];
            $nuevo    = (int) $this->__get('id_servicios');
            if ($anterior !== $nuevo) {
                $this->registrarHistorial(
                    'mobiliario', $id, 'reubicacion', null, $anterior, $nuevo,
                    'Reubicación de mobiliario (' . $tipoNombre . ')'
                );
            } else {
                $this->registrarHistorial(
                    'mobiliario', $id, 'modificacion', null, null, $nuevo,
                    'Modificación de datos del mobiliario (' . $tipoNombre . ')'
                );
            }

            $this->conn->commit();

            return [
                'id_mobiliario' => $id,
                'resumen'       => sprintf('%s (cantidad %d) en %s', $tipoNombre, (int) $this->__get('cantidad'), $servicioNombre),
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
            error_log('MobiliarioModel::actualizarMobiliario - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar el mobiliario.');
        }
    }

    /** Edición del equipo: serial único (id_excluir) e historial como arriba. */
    private function actualizarEquipo(): array
    {
        $id             = (int) $this->__get('id_equipo');
        $tipoNombre     = $this->tipoEquipoActivo((int) $this->__get('id_tipo_equipo'));
        $servicioNombre = $this->servicioActivo((int) $this->__get('id_servicios'));

        if ($this->existeSerial((string) $this->__get('serial'), $id)) {
            throw ExcepcionApi::yaExiste('Ya existe un equipo registrado con ese serial.');
        }

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'SELECT id_servicios FROM equipos WHERE id_equipo = :id FOR UPDATE'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $actual = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$actual) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('El equipo no existe.');
            }

            $stmt = $this->conn->prepare(
                "UPDATE equipos
                 SET id_tipo_equipo = :tipo, id_servicios = :servicios, marca = :marca,
                     modelo = :modelo, serial = :serial, color = :color, estado = :estado,
                     fecha_adquisicion = :fecha, descripcion = :descripcion,
                     observaciones = :observaciones
                 WHERE id_equipo = :id"
            );
            $stmt->execute([
                ':tipo'          => $this->__get('id_tipo_equipo'),
                ':servicios'     => $this->__get('id_servicios'),
                ':marca'         => $this->__get('marca'),
                ':modelo'        => $this->__get('modelo'),
                ':serial'        => $this->__get('serial'),
                ':color'         => $this->__get('color'),
                ':estado'        => $this->__get('estado'),
                ':fecha'         => $this->__get('fecha_adquisicion'),
                ':descripcion'   => $this->__get('descripcion'),
                ':observaciones' => $this->__get('observaciones'),
                ':id'            => $id,
            ]);

            $anterior = (int) $actual['id_servicios'];
            $nuevo    = (int) $this->__get('id_servicios');
            if ($anterior !== $nuevo) {
                $this->registrarHistorial(
                    'equipo', $id, 'reubicacion', null, $anterior, $nuevo,
                    'Reubicación de equipo ' . $this->__get('serial')
                );
            } else {
                $this->registrarHistorial(
                    'equipo', $id, 'modificacion', null, null, $nuevo,
                    'Modificación de datos del equipo ' . $this->__get('serial')
                );
            }

            $this->conn->commit();

            return [
                'id_equipo' => $id,
                'resumen'   => sprintf('%s · serial %s · %s', $tipoNombre, $this->__get('serial'), $servicioNombre),
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
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('Ya existe un equipo registrado con ese serial.');
            }
            error_log('MobiliarioModel::actualizarEquipo - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar el equipo.');
        }
    }

    /** Edición de ficha: cabecera + REEMPLAZO completo de sus detalles. */
    private function actualizarFicha(): array
    {
        $id             = (int) $this->__get('id_ficha');
        $servicioNombre = $this->servicioActivo((int) $this->__get('id_servicio'));
        $empleadoNombre = $this->empleadoActivo((int) $this->__get('id_empleado_responsable'));
        $detallesMob    = (array) ($this->__get('detalles_mobiliario') ?? []);
        $detallesEq     = (array) ($this->__get('detalles_equipo') ?? []);

        if ($this->existeFichaEmpleado((int) $this->__get('id_empleado_responsable'), $id)) {
            throw ExcepcionApi::yaExiste('Ese empleado ya tiene otra ficha técnica activa.');
        }
        $this->validarDetalles($detallesMob, $detallesEq, $id);

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare('SELECT id_ficha FROM fichas_tecnicas WHERE id_ficha = :id FOR UPDATE');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('La ficha técnica no existe.');
            }

            $stmt = $this->conn->prepare(
                "UPDATE fichas_tecnicas
                 SET nombre_ficha = :nombre, id_servicio = :servicio,
                     id_empleado_responsable = :empleado, descripcion = :descripcion
                 WHERE id_ficha = :id"
            );
            $stmt->execute([
                ':nombre'      => $this->__get('nombre_ficha'),
                ':servicio'    => $this->__get('id_servicio'),
                ':empleado'    => $this->__get('id_empleado_responsable'),
                ':descripcion' => $this->__get('descripcion'),
                ':id'          => $id,
            ]);

            // Reemplazo total de los ítems (patrón Referencias con su log).
            $stmt = $this->conn->prepare('DELETE FROM detalle_ficha_mobiliario WHERE id_ficha = :id');
            $stmt->execute([':id' => $id]);
            $stmt = $this->conn->prepare('DELETE FROM detalle_ficha_equipo WHERE id_ficha = :id');
            $stmt->execute([':id' => $id]);

            $this->insertarDetalles($id, $detallesMob, $detallesEq);

            $this->conn->commit();

            return [
                'id_ficha' => $id,
                'resumen'  => $this->__get('nombre_ficha') . ' — ' . $empleadoNombre . ' (' . $servicioNombre . ')',
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
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('Ese empleado ya tiene una ficha técnica registrada.');
            }
            error_log('MobiliarioModel::actualizarFicha - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar la ficha técnica.');
        }
    }

    //=== Eliminar ====

    /** Bloqueado si está en detalle_ficha_* o en inventario_mob (legado). */
    private function eliminarMobiliario(): bool
    {
        $id = (int) $this->__get('id_mobiliario');
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare('SELECT id_mobiliario FROM mobiliario WHERE id_mobiliario = :id FOR UPDATE');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('El mobiliario no existe.');
            }

            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM detalle_ficha_mobiliario WHERE id_mobiliario = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ((int) $stmt->fetchColumn() > 0) {
                $this->conn->rollBack();
                throw ExcepcionApi::enUso('No se puede eliminar: el mobiliario está asignado en fichas técnicas.');
            }

            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM inventario_mob WHERE id_mobiliario = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ((int) $stmt->fetchColumn() > 0) {
                $this->conn->rollBack();
                throw ExcepcionApi::enUso('No se puede eliminar: el mobiliario tiene registros de inventario.');
            }

            $stmt = $this->conn->prepare('DELETE FROM mobiliario WHERE id_mobiliario = :id');
            $stmt->execute([':id' => $id]);

            $this->conn->commit();
            return true;
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('MobiliarioModel::eliminarMobiliario - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar el mobiliario.');
        }
    }

    /** Bloqueado si está en detalle_ficha_equipo. */
    private function eliminarEquipo(): bool
    {
        $id = (int) $this->__get('id_equipo');
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare('SELECT id_equipo FROM equipos WHERE id_equipo = :id FOR UPDATE');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('El equipo no existe.');
            }

            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM detalle_ficha_equipo WHERE id_equipo = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ((int) $stmt->fetchColumn() > 0) {
                $this->conn->rollBack();
                throw ExcepcionApi::enUso('No se puede eliminar: el equipo está asignado en fichas técnicas.');
            }

            $stmt = $this->conn->prepare('DELETE FROM equipos WHERE id_equipo = :id');
            $stmt->execute([':id' => $id]);

            $this->conn->commit();
            return true;
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('MobiliarioModel::eliminarEquipo - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar el equipo.');
        }
    }

    /** Borra sus detalles primero y luego la ficha, en transacción. */
    private function eliminarFicha(): bool
    {
        $id = (int) $this->__get('id_ficha');
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare('SELECT id_ficha FROM fichas_tecnicas WHERE id_ficha = :id FOR UPDATE');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('La ficha técnica no existe.');
            }

            $stmt = $this->conn->prepare('DELETE FROM detalle_ficha_mobiliario WHERE id_ficha = :id');
            $stmt->execute([':id' => $id]);
            $stmt = $this->conn->prepare('DELETE FROM detalle_ficha_equipo WHERE id_ficha = :id');
            $stmt->execute([':id' => $id]);
            $stmt = $this->conn->prepare('DELETE FROM fichas_tecnicas WHERE id_ficha = :id');
            $stmt->execute([':id' => $id]);

            $this->conn->commit();
            return true;
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
                throw ExcepcionApi::enUso('No se puede eliminar: la ficha técnica tiene registros asociados.');
            }
            error_log('MobiliarioModel::eliminarFicha - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar la ficha técnica.');
        }
    }

    //=== Movimientos ====

    /** Cambia la ubicación del ítem y deja 'reubicacion' en el historial. */
    private function reubicar(): array
    {
        $tipo      = (string) $this->__get('tipo_item');
        $id        = (int) ($this->__get($tipo === 'mobiliario' ? 'id_mobiliario' : 'id_equipo'));
        $nuevoServ = (int) $this->__get('id_servicios');
        $servNuevo = $this->servicioActivo($nuevoServ);

        try {
            $this->conn->beginTransaction();

            if ($tipo === 'mobiliario') {
                $stmt = $this->conn->prepare(
                    "SELECT m.id_servicios,
                            CONCAT(IFNULL(tm.nombre, 'Mobiliario'), ' #', m.id_mobiliario) AS etiqueta
                     FROM mobiliario m
                     LEFT JOIN tipo_mobiliario tm ON tm.id_tipo_mobiliario = m.id_tipo_mobiliario
                     WHERE m.id_mobiliario = :id FOR UPDATE"
                );
            } else {
                $stmt = $this->conn->prepare(
                    "SELECT e.id_servicios,
                            CONCAT('Equipo ', IFNULL(e.serial, CONCAT('#', e.id_equipo))) AS etiqueta
                     FROM equipos e
                     WHERE e.id_equipo = :id FOR UPDATE"
                );
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('El ítem no existe.');
            }

            $anterior = (int) $fila['id_servicios'];
            if ($anterior === $nuevoServ) {
                $this->conn->rollBack();
                throw ExcepcionApi::validacion('La nueva ubicación es la misma que la actual.');
            }

            $tabla = $tipo === 'mobiliario' ? 'mobiliario' : 'equipos';
            $campo = $tipo === 'mobiliario' ? 'id_mobiliario' : 'id_equipo';
            $stmt = $this->conn->prepare(
                "UPDATE {$tabla} SET id_servicios = :servicios WHERE {$campo} = :id"
            );
            $stmt->execute([':servicios' => $nuevoServ, ':id' => $id]);

            $servAnterior = $this->nombreServicio($anterior);
            $this->registrarHistorial(
                $tipo, $id, 'reubicacion', null, $anterior, $nuevoServ,
                'Reubicación de ' . $fila['etiqueta']
            );

            $this->conn->commit();

            return [
                'id'       => $id,
                'etiqueta' => $fila['etiqueta'],
                'de'       => $servAnterior,
                'a'        => $servNuevo,
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
            error_log('MobiliarioModel::reubicar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo reubicar el ítem.');
        }
    }

    /** Baja lógica ('Inactivo') + 'baja' en el historial. Bloqueada en ficha activa. */
    private function baja(): array
    {
        $tipo = (string) $this->__get('tipo_item');
        $id   = (int) ($this->__get($tipo === 'mobiliario' ? 'id_mobiliario' : 'id_equipo'));

        try {
            $this->conn->beginTransaction();

            if ($tipo === 'mobiliario') {
                $tabla = 'mobiliario';
                $campo = 'id_mobiliario';
                $stmt = $this->conn->prepare(
                    "SELECT m.estatus, m.id_servicios,
                            CONCAT(IFNULL(tm.nombre, 'Mobiliario'), ' #', m.id_mobiliario) AS etiqueta
                     FROM mobiliario m
                     LEFT JOIN tipo_mobiliario tm ON tm.id_tipo_mobiliario = m.id_tipo_mobiliario
                     WHERE m.id_mobiliario = :id FOR UPDATE"
                );
            } else {
                $tabla = 'equipos';
                $campo = 'id_equipo';
                $stmt = $this->conn->prepare(
                    "SELECT e.estatus, e.id_servicios,
                            CONCAT('Equipo ', IFNULL(e.serial, CONCAT('#', e.id_equipo))) AS etiqueta
                     FROM equipos e
                     WHERE e.id_equipo = :id FOR UPDATE"
                );
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('El ítem no existe.');
            }
            if (($fila['estatus'] ?? '') === 'Inactivo') {
                $this->conn->rollBack();
                throw ExcepcionApi::validacion('El ítem ya está dado de baja.');
            }

            $ficha = $this->fichaActivaDelItem($tipo, $id);
            if ($ficha !== null) {
                $this->conn->rollBack();
                throw ExcepcionApi::enUso(
                    'No se puede dar de baja: el ítem está asignado a la ficha activa "' . $ficha . '". Quítalo primero.'
                );
            }

            $stmt = $this->conn->prepare("UPDATE {$tabla} SET estatus = 'Inactivo' WHERE {$campo} = :id");
            $stmt->execute([':id' => $id]);

            $this->registrarHistorial(
                $tipo, $id, 'baja', null, null, (int) $fila['id_servicios'],
                'Baja lógica de ' . $fila['etiqueta']
            );

            $this->conn->commit();

            return ['id' => $id, 'etiqueta' => $fila['etiqueta']];
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('MobiliarioModel::baja - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo dar de baja el ítem.');
        }
    }

    //=== Estadísticas ====

    /** Tarjetas: unidades de mobiliario, equipos, fichas activas y del mes. */
    private function stats(): array
    {
        try {
            $totalMobiliarios = (int) $this->conn
                ->query("SELECT IFNULL(SUM(cantidad), 0) FROM mobiliario WHERE estatus = 'Activo'")
                ->fetchColumn();

            $totalEquipos = (int) $this->conn
                ->query("SELECT COUNT(*) FROM equipos WHERE estatus = 'Activo'")
                ->fetchColumn();

            $fichasActivas = (int) $this->conn
                ->query('SELECT COUNT(*) FROM fichas_tecnicas WHERE estatus = 1')
                ->fetchColumn();

            // "Altas del mes": registros dados de alta en el sistema en el mes
            // en curso (fecha_registro, que además tiene DEFAULT current_timestamp).
            // Se hacen dos consultas aparte: MariaDB/MySQL no admite sumar dos
            // subqueries escalares en el SELECT (error 1064).
            $delMesMobiliario = (int) $this->conn->query(
                "SELECT IFNULL(SUM(cantidad), 0) FROM mobiliario
                  WHERE fecha_registro >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')"
            )->fetchColumn();

            $delMesEquipos = (int) $this->conn->query(
                "SELECT COUNT(*) FROM equipos
                  WHERE fecha_registro >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')"
            )->fetchColumn();

            $delMes = $delMesMobiliario + $delMesEquipos;

            return [
                'total_mobiliarios' => $totalMobiliarios,
                'total_equipos'     => $totalEquipos,
                'fichas_activas'    => $fichasActivas,
                'inventario_mes'    => $delMes,
            ];
        } catch (Throwable $e) {
            error_log('MobiliarioModel::stats - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar las estadísticas del módulo.');
        }
    }

    /**
     * Ítems libres para asignar a una ficha. id_excluir = ficha propia (0 al
     * crear): sus asignaciones no descuentan la disponibilidad.
     */
    private function itemsDisponibles(): array
    {
        $excluir = (int) ($this->__get('id_excluir') ?? 0);
        try {
            $stmt = $this->conn->prepare(
                "SELECT x.id_mobiliario, x.id_tipo_mobiliario, x.tipo_mobiliario, x.id_servicios,
                        x.servicio, x.cantidad, x.marca, x.modelo, x.disponible
                 FROM (
                     SELECT m.id_mobiliario, m.id_tipo_mobiliario, tm.nombre AS tipo_mobiliario,
                            m.id_servicios, s.nombre_serv AS servicio, m.cantidad, m.marca, m.modelo,
                            m.cantidad - IFNULL((
                                SELECT SUM(d.cantidad)
                                FROM detalle_ficha_mobiliario d
                                INNER JOIN fichas_tecnicas f
                                    ON f.id_ficha = d.id_ficha AND f.estatus = 1 AND f.id_ficha <> :excluir
                                WHERE d.id_mobiliario = m.id_mobiliario
                            ), 0) AS disponible
                     FROM mobiliario m
                     LEFT JOIN tipo_mobiliario tm ON tm.id_tipo_mobiliario = m.id_tipo_mobiliario
                     LEFT JOIN servicio s ON s.id_servicios = m.id_servicios
                     WHERE m.estatus = 'Activo'
                 ) x
                 WHERE x.disponible > 0
                 ORDER BY IFNULL(x.tipo_mobiliario, ''), x.id_mobiliario ASC"
            );
            $stmt->bindValue(':excluir', $excluir, PDO::PARAM_INT);
            $stmt->execute();
            $mobiliario = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->conn->prepare(
                "SELECT e.id_equipo, e.id_tipo_equipo, te.nombre AS tipo_equipo,
                        e.id_servicios, s.nombre_serv AS servicio, e.serial, e.marca, e.modelo
                 FROM equipos e
                 LEFT JOIN tipo_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
                 LEFT JOIN servicio s ON s.id_servicios = e.id_servicios
                 WHERE e.estatus = 'Activo'
                   AND NOT EXISTS (
                       SELECT 1 FROM detalle_ficha_equipo d
                       INNER JOIN fichas_tecnicas f
                           ON f.id_ficha = d.id_ficha AND f.estatus = 1 AND f.id_ficha <> :excluir
                       WHERE d.id_equipo = e.id_equipo
                   )
                 ORDER BY IFNULL(te.nombre, ''), e.id_equipo ASC"
            );
            $stmt->bindValue(':excluir', $excluir, PDO::PARAM_INT);
            $stmt->execute();
            $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['mobiliario' => $mobiliario, 'equipos' => $equipos];
        } catch (Throwable $e) {
            error_log('MobiliarioModel::itemsDisponibles - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los ítems disponibles.');
        }
    }

    //=== Validaciones en vivo ====

    /** ¿Existe ya un equipo con ese serial? ($excluir evita el propio en edición). */
    private function existeSerial(string $serial, int $excluir = 0): bool
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM equipos WHERE serial = :serial AND id_equipo <> :excluir'
        );
        $stmt->execute([':serial' => $serial, ':excluir' => $excluir]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** ¿Ese empleado ya tiene una ficha ACTIVA? ($excluir = la propia). */
    private function existeFichaEmpleado(int $idEmpleado, int $excluir = 0): bool
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM fichas_tecnicas
             WHERE id_empleado_responsable = :empleado AND estatus = 1 AND id_ficha <> :excluir'
        );
        $stmt->execute([':empleado' => $idEmpleado, ':excluir' => $excluir]);
        return (int) $stmt->fetchColumn() > 0;
    }

    //=== Helpers privados ====

    private function enteroPositivo(mixed $valor, string $mensaje): int
    {
        if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw ExcepcionApi::validacion($mensaje);
        }
        return (int) $valor;
    }

    /** Servicio activo; devuelve su nombre o lanza 400. */
    private function servicioActivo(int $id): string
    {
        $stmt = $this->conn->prepare(
            'SELECT nombre_serv FROM servicio WHERE id_servicios = :id AND estatus = 1 LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $nombre = $stmt->fetchColumn();
        if ($nombre === false) {
            throw ExcepcionApi::validacion('El servicio seleccionado no existe o está inactivo.');
        }
        return (string) $nombre;
    }

    /** Nombre del servicio (aunque esté inactivo; para historial). */
    private function nombreServicio(int $id): string
    {
        $stmt = $this->conn->prepare('SELECT nombre_serv FROM servicio WHERE id_servicios = :id LIMIT 1');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (string) ($stmt->fetchColumn() ?: 'Sin ubicación');
    }

    private function tipoMobiliarioActivo(int $id): string
    {
        $stmt = $this->conn->prepare(
            'SELECT nombre FROM tipo_mobiliario WHERE id_tipo_mobiliario = :id AND estatus = 1 LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $nombre = $stmt->fetchColumn();
        if ($nombre === false) {
            throw ExcepcionApi::validacion('El tipo de mobiliario seleccionado no existe o está inactivo.');
        }
        return (string) $nombre;
    }

    private function tipoEquipoActivo(int $id): string
    {
        $stmt = $this->conn->prepare(
            'SELECT nombre FROM tipo_equipo WHERE id_tipo_equipo = :id AND estatus = 1 LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $nombre = $stmt->fetchColumn();
        if ($nombre === false) {
            throw ExcepcionApi::validacion('El tipo de equipo seleccionado no existe o está inactivo.');
        }
        return (string) $nombre;
    }

    /** Empleado activo (esquema security); devuelve su nombre o lanza 400. */
    private function empleadoActivo(int $id): string
    {
        $stmt = $this->conn->prepare(
            "SELECT CONCAT(nombre, ' ', IFNULL(apellido, ''))
             FROM dirpoles_security.empleado
             WHERE id_empleado = :id AND estatus = 1
             LIMIT 1"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $nombre = $stmt->fetchColumn();
        if ($nombre === false) {
            throw ExcepcionApi::validacion('El empleado responsable no existe o está inactivo.');
        }
        return (string) $nombre;
    }

    /** Unidades del ítem ya asignadas en fichas ACTIVAS. */
    private function asignadoEnFichasActivas(string $tipo, int $id): int
    {
        $tabla = $tipo === 'mobiliario' ? 'detalle_ficha_mobiliario' : 'detalle_ficha_equipo';
        $campo = $tipo === 'mobiliario' ? 'id_mobiliario' : 'id_equipo';
        if ($tipo === 'mobiliario') {
            $sql = "SELECT IFNULL(SUM(d.cantidad), 0)
                    FROM {$tabla} d
                    INNER JOIN fichas_tecnicas f ON f.id_ficha = d.id_ficha AND f.estatus = 1
                    WHERE d.{$campo} = :id";
        } else {
            $sql = "SELECT COUNT(*)
                    FROM {$tabla} d
                    INNER JOIN fichas_tecnicas f ON f.id_ficha = d.id_ficha AND f.estatus = 1
                    WHERE d.{$campo} = :id";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /** Nombre de la ficha activa que usa el ítem, o null si está libre. */
    private function fichaActivaDelItem(string $tipo, int $id): ?string
    {
        if ($tipo === 'mobiliario') {
            $sql = "SELECT f.nombre_ficha
                    FROM detalle_ficha_mobiliario d
                    INNER JOIN fichas_tecnicas f ON f.id_ficha = d.id_ficha AND f.estatus = 1
                    WHERE d.id_mobiliario = :id
                    LIMIT 1";
        } else {
            $sql = "SELECT f.nombre_ficha
                    FROM detalle_ficha_equipo d
                    INNER JOIN fichas_tecnicas f ON f.id_ficha = d.id_ficha AND f.estatus = 1
                    WHERE d.id_equipo = :id
                    LIMIT 1";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $nombre = $stmt->fetchColumn();
        return $nombre === false ? null : (string) $nombre;
    }

    /**
     * Valida que cada fila de la ficha sea asignable:
     *  - mobiliario activo con disponible suficiente (cantidad − asignado en
     *    otras fichas activas, excluyendo la propia);
     *  - equipo activo no usado en otra ficha activa.
     */
    private function validarDetalles(array $detallesMob, array $detallesEq, int $excluirFicha): void
    {
        // Una ficha sin ítems no tiene sentido (decisión 2026-09-29): se exige
        // al menos un mobiliario o un equipo, tanto al crear como al editar.
        if (!$detallesMob && !$detallesEq) {
            throw ExcepcionApi::validacion(
                'La ficha debe incluir al menos un ítem de mobiliario o un equipo.'
            );
        }

        foreach ($detallesMob as $fila) {
            $id = (int) $fila['id_mobiliario'];
            $cantidad = (int) $fila['cantidad'];

            $stmt = $this->conn->prepare(
                "SELECT m.cantidad, m.estatus,
                        IFNULL((
                            SELECT SUM(d.cantidad)
                            FROM detalle_ficha_mobiliario d
                            INNER JOIN fichas_tecnicas f
                                ON f.id_ficha = d.id_ficha AND f.estatus = 1 AND f.id_ficha <> :excluir
                            WHERE d.id_mobiliario = m.id_mobiliario
                        ), 0) AS asignado
                 FROM mobiliario m
                 WHERE m.id_mobiliario = :id
                 LIMIT 1"
            );
            $stmt->execute([':id' => $id, ':excluir' => $excluirFicha]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                throw ExcepcionApi::validacion('Uno de los mobiliarios seleccionados ya no existe.');
            }
            if (($item['estatus'] ?? '') !== 'Activo') {
                throw ExcepcionApi::validacion('Solo se pueden asignar mobiliarios activos a la ficha.');
            }
            $disponible = (int) $item['cantidad'] - (int) $item['asignado'];
            if ($cantidad > $disponible) {
                throw ExcepcionApi::validacion(sprintf(
                    'La cantidad solicitada supera lo disponible para el mobiliario #%d (disponible: %d).',
                    $id,
                    max(0, $disponible)
                ));
            }
        }

        foreach ($detallesEq as $idEquipo) {
            $idEquipo = (int) $idEquipo;
            $stmt = $this->conn->prepare(
                "SELECT e.serial, e.estatus
                 FROM equipos e
                 WHERE e.id_equipo = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', $idEquipo, PDO::PARAM_INT);
            $stmt->execute();
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                throw ExcepcionApi::validacion('Uno de los equipos seleccionados ya no existe.');
            }
            if (($item['estatus'] ?? '') !== 'Activo') {
                throw ExcepcionApi::validacion(
                    'El equipo "' . $item['serial'] . '" no está activo y no puede asignarse a la ficha.'
                );
            }

            $ficha = $this->fichaActivaDelItem('equipo', $idEquipo);
            if ($ficha !== null) {
                throw ExcepcionApi::enUso(
                    'El equipo "' . $item['serial'] . '" ya está asignado a la ficha activa "' . $ficha . '".'
                );
            }
        }
    }

    /** Inserta los detalles de la ficha (llamar dentro de transacción). */
    private function insertarDetalles(int $idFicha, array $detallesMob, array $detallesEq): void
    {
        if ($detallesMob) {
            $stmt = $this->conn->prepare(
                'INSERT INTO detalle_ficha_mobiliario (id_ficha, id_mobiliario, cantidad)
                 VALUES (:ficha, :mobiliario, :cantidad)'
            );
            foreach ($detallesMob as $fila) {
                $stmt->execute([
                    ':ficha'      => $idFicha,
                    ':mobiliario' => (int) $fila['id_mobiliario'],
                    ':cantidad'   => (int) $fila['cantidad'],
                ]);
            }
        }

        if ($detallesEq) {
            $stmt = $this->conn->prepare(
                'INSERT INTO detalle_ficha_equipo (id_ficha, id_equipo) VALUES (:ficha, :equipo)'
            );
            foreach ($detallesEq as $idEquipo) {
                $stmt->execute([':ficha' => $idFicha, ':equipo' => (int) $idEquipo]);
            }
        }
    }

    /**
     * Movimiento en el kardex. id_empleado y fecha salen de la sesión y del
     * DEFAULT de la tabla; NUNCA se inserta 'fecha_movimiento' a mano.
     */
    private function registrarHistorial(
        string $tipoItem,
        int $idItem,
        string $tipoMovimiento,
        ?int $idFicha,
        ?int $servicioAnterior,
        ?int $servicioNuevo,
        string $descripcion
    ): void {
        $stmt = $this->conn->prepare(
            "INSERT INTO historial_inventario
               (id_empleado, tipo_item, id_item, tipo_movimiento, id_ficha,
                id_servicio_anterior, id_servicio_nuevo, descripcion)
             VALUES
               (:empleado, :tipo_item, :id_item, :movimiento, :ficha,
                :serv_anterior, :serv_nuevo, :descripcion)"
        );
        $stmt->execute([
            ':empleado'     => (int) $this->__get('id_empleado'),
            ':tipo_item'    => $tipoItem,
            ':id_item'      => $idItem,
            ':movimiento'   => $tipoMovimiento,
            ':ficha'        => $idFicha,
            ':serv_anterior' => $servicioAnterior,
            ':serv_nuevo'   => $servicioNuevo,
            ':descripcion'  => $descripcion,
        ]);
    }
}
