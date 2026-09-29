<?php
namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;

/**
 * InventarioModel — módulo Inventario Médico (id_modulo 9 = 'Inventario Medico').
 *
 * Tablas: insumos (maestro con stock) e inventario_medico (kardex de movimientos).
 * Reglas permanentes (heredadas del sistema viejo, decisiones del usuario):
 *  - Crear NO lleva cantidad: el insumo nace con cantidad 0 y estatus 'Agotado';
 *    el stock entra con la acción 'entrada' desde la pantalla consultar.
 *  - La fecha de vencimiento al CREAR debe ser >= hoy (no se crean insumos ya
 *    vencidos); al EDITAR se permite cualquier fecha válida (un insumo ya
 *    registrado puede quedar vencido).
 *  - Entrada: solo insumos con estatus != 'Vencido' y fecha >= hoy; suma stock
 *    y pasa estatus a 'Disponible' si queda con cantidad > 0.
 *  - Salida: solo insumos con cantidad > 0 (los vencidos también se dan de
 *    baja); motiva en el kardex y pone 'Agotado' si el stock llega a 0.
 *  - Eliminar: bloqueado si está en detalle_insumo (Medicina), si tiene stock
 *    o si ya tiene movimientos distintos de 'Registro'.
 *  - La edición SOLO permite nombre, tipo, presentación, fecha de vencimiento
 *    y descripción (jamás cantidad ni estatus: son de la entrada/salida).
 */
class InventarioModel extends BusinessModel
{
    private const TIPOS_INSUMO  = ['Medicamento', 'Material', 'Quirúrgico'];
    private const ESTATOS       = ['Disponible', 'Agotado', 'Vencido'];
    private const MOTIVOS_SALIDA = ['Vencimiento', 'Daño', 'Pérdida', 'Donación', 'Uso Interno'];
    private const LIMITE_INT    = 2147483647; // columna INT de MySQL
    private const MAX_ENTRADA   = 1000;       // máximo por UNA entrada de stock

    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'nombre_insumo':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El nombre del insumo es obligatorio.');
                }
                if (mb_strlen($valor) > 100) { // la columna es varchar(100)
                    throw ExcepcionApi::validacion('El nombre del insumo no puede superar 100 caracteres.');
                }
                if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\-]{2,100}$/u', $valor)) {
                    throw ExcepcionApi::validacion(
                        'El nombre del insumo solo puede contener letras, números, espacios, puntos y guiones (mínimo 2 caracteres).'
                    );
                }
                $this->atributos['nombre_insumo'] = $valor;
                break;

            case 'tipo_insumo':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::TIPOS_INSUMO, true)) {
                    throw ExcepcionApi::validacion('El tipo de insumo no es válido.');
                }
                $this->atributos['tipo_insumo'] = $valor;
                break;

            case 'id_presentacion':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion('Debes seleccionar la presentación del insumo.');
                }
                $this->atributos['id_presentacion'] = (int) $valor;
                break;

            case 'fecha_vencimiento':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('La fecha de vencimiento es obligatoria.');
                }
                $fecha = \DateTime::createFromFormat('Y-m-d', $valor);
                if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
                    throw ExcepcionApi::validacion('La fecha de vencimiento no es válida.');
                }
                $this->atributos['fecha_vencimiento'] = $valor;
                break;

            case 'descripcion':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('La descripción es obligatoria.');
                }
                if (mb_strlen($valor) > 250) {
                    throw ExcepcionApi::validacion('La descripción no puede superar 250 caracteres.');
                }
                if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s,.\-#]{2,250}$/u', $valor)) {
                    throw ExcepcionApi::validacion(
                        'La descripción solo puede contener letras, números, espacios, comas, puntos, guiones y #.'
                    );
                }
                $this->atributos['descripcion'] = $valor;
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

            case 'motivo':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::MOTIVOS_SALIDA, true)) {
                    throw ExcepcionApi::validacion('El motivo de la salida no es válido.');
                }
                $this->atributos['motivo'] = $valor;
                break;

            case 'estatus':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::ESTATOS, true)) {
                    throw ExcepcionApi::validacion('El estatus del insumo no es válido.');
                }
                $this->atributos['estatus'] = $valor;
                break;

            case 'id_insumo':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion('El insumo seleccionado no es válido.');
                }
                $this->atributos['id_insumo'] = (int) $valor;
                break;

            case 'id_empleado':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion('No se pudo identificar al empleado responsable.');
                }
                $this->atributos['id_empleado'] = (int) $valor;
                break;

            case 'id_excluir':
                // En edición: id del insumo que NO debe contarse como duplicado.
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
            'presentaciones' => $this->presentaciones(),
            'listar'         => $this->listar(),
            'obtener'        => $this->obtener(),
            'crear'          => $this->crear(),
            'actualizar'     => $this->actualizar(),
            'eliminar'       => $this->eliminar(),
            'stats'          => $this->stats(),
            'entrada'        => $this->entrada(),
            'salida'         => $this->salida(),
            'movimientos'    => $this->movimientos(),
            'existe_insumo'  => $this->existeIgual(
                (int) $this->__get('id_presentacion'),
                (string) $this->__get('nombre_insumo'),
                (string) $this->__get('tipo_insumo'),
                (string) $this->__get('fecha_vencimiento'),
                (int) $this->__get('id_excluir')
            ),
            default => throw ExcepcionApi::errorInterno("Acción no válida en InventarioModel: '{$accion}'."),
        };
    }

    //=== Catálogos ====

    /** Presentaciones de insumo (catálogo administrado en Configuración). */
    private function presentaciones(): array
    {
        try {
            $stmt = $this->conn->query(
                'SELECT id_presentacion, nombre_presentacion
                 FROM presentacion_insumo
                 ORDER BY nombre_presentacion ASC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('InventarioModel::presentaciones - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar las presentaciones de insumo.');
        }
    }

    //=== CRUD ====

    /**
     * Crea el insumo con cantidad 0 y estatus 'Agotado' (la cantidad NO se
     * envía desde el formulario) y deja el movimiento 'Registro' en el kardex.
     * La fecha de vencimiento debe ser >= hoy.
     */
    private function crear(): array
    {
        try {
            if ($this->__get('fecha_vencimiento') < date('Y-m-d')) {
                throw ExcepcionApi::validacion('La fecha de vencimiento no puede ser una fecha pasada.');
            }
            if ($this->existeIgual(
                (int) $this->__get('id_presentacion'),
                (string) $this->__get('nombre_insumo'),
                (string) $this->__get('tipo_insumo'),
                (string) $this->__get('fecha_vencimiento')
            )) {
                throw ExcepcionApi::yaExiste('Ese insumo ya está registrado en el inventario médico.');
            }

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "INSERT INTO insumos
                   (id_presentacion, nombre_insumo, descripcion, tipo_insumo,
                    fecha_vencimiento, fecha_creacion, cantidad, estatus)
                 VALUES
                   (:presentacion, :nombre, :descripcion, :tipo,
                    :vencimiento, CURDATE(), 0, 'Agotado')"
            );
            $stmt->execute([
                ':presentacion' => $this->__get('id_presentacion'),
                ':nombre'       => $this->__get('nombre_insumo'),
                ':descripcion'  => $this->__get('descripcion'),
                ':tipo'         => $this->__get('tipo_insumo'),
                ':vencimiento'  => $this->__get('fecha_vencimiento'),
            ]);
            $idInsumo = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                "INSERT INTO inventario_medico
                   (id_insumo, id_empleado, tipo_movimiento, cantidad, descripcion)
                 VALUES (:insumo, :empleado, 'Registro', 0, 'Nuevo registro')"
            );
            $stmt->execute([
                ':insumo'    => $idInsumo,
                ':empleado'  => $this->__get('id_empleado'),
            ]);

            $this->conn->commit();

            return [
                'id_insumo'       => $idInsumo,
                'nombre_insumo'   => $this->__get('nombre_insumo'),
                'tipo_insumo'     => $this->__get('tipo_insumo'),
                'fecha_vencimiento' => $this->__get('fecha_vencimiento'),
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
                throw ExcepcionApi::yaExiste('Ese insumo ya está registrado en el inventario médico.');
            }
            error_log('InventarioModel::crear - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar el insumo.');
        }
    }

    /** Lista los insumos con su presentación (para la DataTable). */
    private function listar(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT i.id_insumo, i.id_presentacion, i.nombre_insumo, i.descripcion,
                        i.tipo_insumo, i.fecha_vencimiento, i.fecha_creacion,
                        i.cantidad, i.estatus, p.nombre_presentacion
                 FROM insumos i
                 INNER JOIN presentacion_insumo p ON p.id_presentacion = i.id_presentacion
                 ORDER BY i.nombre_insumo ASC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('InventarioModel::listar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron listar los insumos.');
        }
    }

    /** Devuelve la fila completa de un insumo (para editar). */
    private function obtener(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT i.id_insumo, i.id_presentacion, i.nombre_insumo, i.descripcion,
                        i.tipo_insumo, i.fecha_vencimiento, i.fecha_creacion,
                        i.cantidad, i.estatus, p.nombre_presentacion
                 FROM insumos i
                 INNER JOIN presentacion_insumo p ON p.id_presentacion = i.id_presentacion
                 WHERE i.id_insumo = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', $this->__get('id_insumo'), PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                throw ExcepcionApi::noEncontrado('El insumo no existe.');
            }
            return $fila;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('InventarioModel::obtener - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo consultar el insumo.');
        }
    }

    /**
     * Actualiza SOLO los campos editables: nombre, tipo, presentación,
     * fecha de vencimiento y descripción. Jamás cantidad ni estatus
     * (se modifican con entrada/salida).
     */
    private function actualizar(): array
    {
        try {
            $id = (int) $this->__get('id_insumo');
            if (!$this->existeId($id)) {
                throw ExcepcionApi::noEncontrado('El insumo no existe.');
            }
            if ($this->existeIgual(
                (int) $this->__get('id_presentacion'),
                (string) $this->__get('nombre_insumo'),
                (string) $this->__get('tipo_insumo'),
                (string) $this->__get('fecha_vencimiento'),
                $id
            )) {
                throw ExcepcionApi::yaExiste('Ese insumo ya está registrado en el inventario médico.');
            }

            $stmt = $this->conn->prepare(
                "UPDATE insumos
                 SET nombre_insumo = :nombre,
                     tipo_insumo = :tipo,
                     id_presentacion = :presentacion,
                     fecha_vencimiento = :vencimiento,
                     descripcion = :descripcion
                 WHERE id_insumo = :id"
            );
            $stmt->execute([
                ':nombre'       => $this->__get('nombre_insumo'),
                ':tipo'         => $this->__get('tipo_insumo'),
                ':presentacion' => $this->__get('id_presentacion'),
                ':vencimiento'  => $this->__get('fecha_vencimiento'),
                ':descripcion'  => $this->__get('descripcion'),
                ':id'           => $id,
            ]);

            return [
                'id_insumo'     => $id,
                'nombre_insumo' => $this->__get('nombre_insumo'),
                'tipo_insumo'   => $this->__get('tipo_insumo'),
            ];
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('Ese insumo ya está registrado en el inventario médico.');
            }
            error_log('InventarioModel::actualizar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar el insumo.');
        }
    }

    /**
     * Elimina el insumo y su movimiento 'Registro' inicial. Bloquea (409) si:
     *  - está en detalle_insumo (se usó en diagnósticos de Medicina);
     *  - tiene stock disponible;
     *  - ya tiene movimientos reales (entrada/salida).
     */
    private function eliminar(): bool
    {
        try {
            $id = (int) $this->__get('id_insumo');

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'SELECT nombre_insumo, cantidad FROM insumos WHERE id_insumo = :id FOR UPDATE'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $insumo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$insumo) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('El insumo no existe.');
            }

            if ($this->estaEnDetalleInsumo($id)) {
                $this->conn->rollBack();
                throw ExcepcionApi::enUso(
                    'No se puede eliminar: el insumo ya fue usado en diagnósticos médicos.'
                );
            }

            if ((int) $insumo['cantidad'] > 0) {
                $this->conn->rollBack();
                throw ExcepcionApi::enUso(
                    'No se puede eliminar: el insumo tiene stock disponible (' . (int) $insumo['cantidad']
                    . '). Registra primero una salida.'
                );
            }

            if ($this->tieneMovimientosReales($id)) {
                $this->conn->rollBack();
                throw ExcepcionApi::enUso(
                    'No se puede eliminar: el insumo ya tiene movimientos de entrada o salida.'
                );
            }

            $stmt = $this->conn->prepare('DELETE FROM inventario_medico WHERE id_insumo = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->conn->prepare('DELETE FROM insumos WHERE id_insumo = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

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
                throw ExcepcionApi::enUso('No se puede eliminar: el insumo tiene registros asociados.');
            }
            error_log('InventarioModel::eliminar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar el insumo.');
        }
    }

    //=== Movimientos (entrada / salida / kardex) ====

    /**
     * Registra una ENTRADA de stock. Solo insumos no vencidos
     * (estatus != 'Vencido' y fecha >= hoy).
     */
    private function entrada(): array
    {
        try {
            $idInsumo   = (int) $this->__get('id_insumo');
            $cantidad   = (int) $this->__get('cantidad');
            $descripcion = (string) $this->__get('descripcion');

            if ($cantidad > self::MAX_ENTRADA) {
                throw ExcepcionApi::validacion(
                    'La cantidad máxima por entrada es ' . self::MAX_ENTRADA . ' unidades.'
                );
            }

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'SELECT nombre_insumo, cantidad, estatus, fecha_vencimiento
                 FROM insumos WHERE id_insumo = :id FOR UPDATE'
            );
            $stmt->bindValue(':id', $idInsumo, PDO::PARAM_INT);
            $stmt->execute();
            $insumo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$insumo) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('El insumo ya no existe.');
            }

            if ($insumo['estatus'] === 'Vencido'
                || $insumo['fecha_vencimiento'] < date('Y-m-d')) {
                $this->conn->rollBack();
                throw ExcepcionApi::validacion(
                    'El insumo "' . $insumo['nombre_insumo'] . '" está vencido y no admite entradas.'
                );
            }

            $nuevoStock = (int) $insumo['cantidad'] + $cantidad;
            if ($nuevoStock > self::LIMITE_INT) {
                $this->conn->rollBack();
                throw ExcepcionApi::validacion('La cantidad a ingresar deja el stock fuera de rango.');
            }

            $stmt = $this->conn->prepare(
                'UPDATE insumos
                 SET cantidad = :nuevo,
                     estatus = CASE WHEN :nuevo > 0 THEN \'Disponible\' ELSE estatus END
                 WHERE id_insumo = :id'
            );
            $stmt->execute([
                ':nuevo' => $nuevoStock,
                ':id'    => $idInsumo,
            ]);

            $stmt = $this->conn->prepare(
                "INSERT INTO inventario_medico
                   (id_insumo, id_empleado, tipo_movimiento, cantidad, descripcion)
                 VALUES (:insumo, :empleado, 'Entrada', :cantidad, :descripcion)"
            );
            $stmt->execute([
                ':insumo'      => $idInsumo,
                ':empleado'    => $this->__get('id_empleado'),
                ':cantidad'    => $cantidad,
                ':descripcion' => $descripcion,
            ]);

            $this->conn->commit();

            return [
                'id_insumo'     => $idInsumo,
                'nombre_insumo' => $insumo['nombre_insumo'],
                'cantidad'      => $cantidad,
                'stock_total'   => $nuevoStock,
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
            error_log('InventarioModel::entrada - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar la entrada.');
        }
    }

    /**
     * Registra una SALIDA de stock (motivo en el kardex). Solo insumos con
     * cantidad > 0 (los vencidos también se dan de baja).
     */
    private function salida(): array
    {
        try {
            $idInsumo    = (int) $this->__get('id_insumo');
            $cantidad    = (int) $this->__get('cantidad');
            $motivo      = (string) $this->__get('motivo');
            $descripcion = (string) $this->__get('descripcion');

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'SELECT nombre_insumo, cantidad FROM insumos WHERE id_insumo = :id FOR UPDATE'
            );
            $stmt->bindValue(':id', $idInsumo, PDO::PARAM_INT);
            $stmt->execute();
            $insumo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$insumo) {
                $this->conn->rollBack();
                throw ExcepcionApi::noEncontrado('El insumo ya no existe.');
            }

            if ((int) $insumo['cantidad'] < $cantidad) {
                $this->conn->rollBack();
                throw ExcepcionApi::validacion(sprintf(
                    'Stock insuficiente para "%s" (disponible: %d).',
                    $insumo['nombre_insumo'],
                    (int) $insumo['cantidad']
                ));
            }

            $nuevoStock = (int) $insumo['cantidad'] - $cantidad;

            $stmt = $this->conn->prepare(
                'UPDATE insumos
                 SET cantidad = :nuevo,
                     estatus = CASE WHEN :nuevo = 0 THEN \'Agotado\' ELSE estatus END
                 WHERE id_insumo = :id'
            );
            $stmt->execute([
                ':nuevo' => $nuevoStock,
                ':id'    => $idInsumo,
            ]);

            $stmt = $this->conn->prepare(
                "INSERT INTO inventario_medico
                   (id_insumo, id_empleado, tipo_movimiento, cantidad, descripcion)
                 VALUES (:insumo, :empleado, 'Salida', :cantidad, :descripcion)"
            );
            $stmt->execute([
                ':insumo'      => $idInsumo,
                ':empleado'    => $this->__get('id_empleado'),
                ':cantidad'    => $cantidad,
                ':descripcion' => $motivo . ' - ' . $descripcion,
            ]);

            $this->conn->commit();

            return [
                'id_insumo'     => $idInsumo,
                'nombre_insumo' => $insumo['nombre_insumo'],
                'cantidad'      => $cantidad,
                'motivo'        => $motivo,
                'stock_total'   => $nuevoStock,
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
            error_log('InventarioModel::salida - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar la salida.');
        }
    }

    /** Kardex completo: movimientos con insumo y empleado responsable. */
    private function movimientos(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT im.id_inv_med, im.id_insumo, i.nombre_insumo,
                        CONCAT(e.nombre, ' ', e.apellido) AS responsable,
                        im.fecha_movimiento, im.tipo_movimiento,
                        im.cantidad, im.descripcion
                 FROM inventario_medico im
                 INNER JOIN insumos i ON i.id_insumo = im.id_insumo
                 INNER JOIN dirpoles_security.empleado e ON e.id_empleado = im.id_empleado
                 ORDER BY im.fecha_movimiento DESC, im.id_inv_med DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 500), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('InventarioModel::movimientos - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo cargar el historial de movimientos.');
        }
    }

    //=== Estadísticas ====

    /** Tarjetas: total, disponibles, por vencer (≤30 días) y stock crítico. */
    private function stats(): array
    {
        try {
            $total = (int) $this->conn
                ->query('SELECT COUNT(*) FROM insumos')->fetchColumn();

            $disponibles = (int) $this->conn
                ->query("SELECT COUNT(*) FROM insumos WHERE estatus = 'Disponible'")->fetchColumn();

            $porVencer = (int) $this->conn->query(
                "SELECT COUNT(*) FROM insumos
                 WHERE fecha_vencimiento BETWEEN CURDATE()
                     AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
            )->fetchColumn();

            $criticos = (int) $this->conn->query(
                "SELECT COUNT(*) FROM insumos
                 WHERE cantidad < 10 AND estatus = 'Disponible'"
            )->fetchColumn();

            return [
                'insumos_total'       => $total,
                'insumos_disponibles' => $disponibles,
                'insumos_por_vencer'  => $porVencer,
                'insumos_criticos'    => $criticos,
            ];
        } catch (Throwable $e) {
            error_log('InventarioModel::stats - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar las estadísticas del inventario.');
        }
    }

    //=== Helpers privados ====

    private function existeId(int $id): bool
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM insumos WHERE id_insumo = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Duplicado: misma presentación + nombre + tipo + fecha de vencimiento
     * (la misma regla del sistema viejo). $excluir evita marcar al propio
     * insumo durante la edición.
     */
    private function existeIgual(
        int $idPresentacion,
        string $nombre,
        string $tipo,
        string $fecha,
        int $excluir = 0
    ): bool {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM insumos
             WHERE id_presentacion = :presentacion
               AND nombre_insumo = :nombre
               AND tipo_insumo = :tipo
               AND fecha_vencimiento = :fecha
               AND id_insumo <> :excluir'
        );
        $stmt->execute([
            ':presentacion' => $idPresentacion,
            ':nombre'       => $nombre,
            ':tipo'         => $tipo,
            ':fecha'        => $fecha,
            ':excluir'      => $excluir,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** ¿El insumo se usó en algún diagnóstico de Medicina? */
    private function estaEnDetalleInsumo(int $id): bool
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM detalle_insumo WHERE id_insumo = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    /** ¿Tiene movimientos distintos del 'Registro' inicial? */
    private function tieneMovimientosReales(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM inventario_medico
             WHERE id_insumo = :id AND tipo_movimiento <> 'Registro'"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }
}
