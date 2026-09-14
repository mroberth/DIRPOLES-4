<?php
namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;

Class EmpleadoModel extends SecurityModel{
    private const TIPOS_CEDULA = ['V', 'E', 'J', 'P', 'G'];
    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void {
        switch ($nombre) {
            case 'nombre':
            case 'apellido':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion(
                        $nombre === 'nombre' ? 'El nombre es obligatorio.' : 'El apellido es obligatorio.'
                    );
                }
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion(ucfirst($nombre) . ' no puede superar 100 caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'tipo_cedula':
                $valor = strtoupper(trim((string) $valor));
                if (!in_array($valor, self::TIPOS_CEDULA, true)) {
                    throw ExcepcionApi::validacion('El tipo de cédula no es válido.');
                }
                $this->atributos['tipo_cedula'] = $valor;
                break;

            case 'cedula':
                $valor = trim((string) $valor);
                if (!preg_match('/^\d{6,10}$/', $valor)) {
                    throw ExcepcionApi::validacion('La cédula debe tener entre 6 y 10 dígitos.');
                }
                $this->atributos['cedula'] = $valor;
                break;

            case 'correo':
                $valor = trim((string) $valor);
                if ($valor === '' || !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    throw ExcepcionApi::validacion('El correo electrónico no es válido.');
                }
                if (mb_strlen($valor) > 50) { // la columna es varchar(50)
                    throw ExcepcionApi::validacion('El correo no puede superar 50 caracteres.');
                }
                $this->atributos['correo'] = $valor;
                break;

            case 'telefono':
                // Obligatorio y con formato móvil venezolano.
                $valor = preg_replace('/\D/', '', (string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El teléfono es obligatorio.');
                }
                if (!preg_match('/^(0412|0414|0416|0422|0424|0426)\d{7}$/', $valor)) {
                    throw ExcepcionApi::validacion(
                        'El teléfono debe empezar por 0412, 0414, 0416, 0422, 0424 o 0426 y tener 7 dígitos más (ej: 04129298008).'
                    );
                }
                $this->atributos['telefono'] = $valor;
                break;

            case 'id_tipo_empleado':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion('Debes seleccionar un tipo de empleado.');
                }
                $this->atributos['id_tipo_empleado'] = (int) $valor;
                break;

            case 'fecha_nacimiento':
                // Obligatoria, válida, no futura y con edad mínima de 15 años.
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('La fecha de nacimiento es obligatoria.');
                }
                $fecha = \DateTime::createFromFormat('Y-m-d', $valor);
                if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
                    throw ExcepcionApi::validacion('La fecha de nacimiento no es válida.');
                }
                if ($fecha > new \DateTime('today')) {
                    throw ExcepcionApi::validacion('La fecha de nacimiento no puede ser futura.');
                }
                if ((new \DateTime('today'))->diff($fecha)->y < 15) {
                    throw ExcepcionApi::validacion('El empleado debe tener al menos 15 años.');
                }
                $this->atributos['fecha_nacimiento'] = $valor;
                break;

            case 'direccion':
                $valor = trim((string) $valor);
                if (mb_strlen($valor) > 500) {
                    throw ExcepcionApi::validacion('La dirección es demasiado larga.');
                }
                $this->atributos['direccion'] = $valor;
                break;

            case 'clave':
                if (!is_string($valor) || strlen($valor) < 8) {
                    throw ExcepcionApi::validacion('La contraseña debe tener al menos 8 caracteres.');
                }
                if (strlen($valor) > 72) { // límite de bcrypt
                    throw ExcepcionApi::validacion('La contraseña no puede superar 72 caracteres.');
                }
                $this->atributos['clave'] = $valor;
                break;

            case 'estatus':
                $this->atributos['estatus'] = ((int) $valor) === 1 ? 1 : 0;
                break;

            case 'id_excluir':
                // En edición: id del empleado que NO debe contarse como duplicado.
                $this->atributos['id_excluir'] = max(0, (int) $valor);
                break;

            case 'id_empleado':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion('El id del empleado no es válido.');
                }
                $this->atributos['id_empleado'] = (int) $valor;
                break;

            case 'buscar':
                $this->atributos['buscar'] = mb_substr(trim((string) $valor), 0, 100);
                break;

            case 'limit':
            case 'offset':
                $this->atributos[$nombre] = max(0, (int) $valor);
                break;

            default:
                throw ExcepcionApi::validacion("Atributo no reconocido: '{$nombre}'.");
        }
    }

    public function __get(string $nombre): mixed {
        return $this->atributos[$nombre] ?? null;
    }

    //=== Despachador ====
    public function manejarAccion(string $accion): mixed {
        return match ($accion) {
            'crear'            => $this->crear(),
            'listar'           => $this->listar(),
            'obtener'          => $this->obtener(),
            'actualizar'       => $this->actualizar(),
            'eliminar'         => $this->eliminar(),
            'tipos'            => $this->tipos(),
            'stats'            => $this->stats(),
            'existe_cedula'    => $this->existeCedula($this->__get('tipo_cedula'), $this->__get('cedula'), (int) $this->__get('id_excluir')),
            'existe_correo'    => $this->existeCorreo($this->__get('correo'), (int) $this->__get('id_excluir')),
            'existe_telefono'  => $this->existeTelefono($this->__get('telefono'), (int) $this->__get('id_excluir')),
            default  => throw ExcepcionApi::errorInterno("Acción no válida en EmpleadoModel: '{$accion}'."),
        };
    }

    private function crear(): array
    {
        try {
            if ($this->existeCorreo($this->__get('correo'))) {
                throw ExcepcionApi::yaExiste('Ya existe un empleado con ese correo.');
            }
            if ($this->existeCedula($this->__get('tipo_cedula'), $this->__get('cedula'))) {
                throw ExcepcionApi::yaExiste('Ya existe un empleado con esa cédula.');
            }

            $stmt = $this->conn_security->prepare(
                "INSERT INTO empleado
                   (nombre, apellido, tipo_cedula, cedula, correo, telefono,
                    id_tipo_empleado, fecha_nacimiento, direccion, clave, estatus, fecha_creacion)
                 VALUES
                   (:nombre, :apellido, :tipo_cedula, :cedula, :correo, :telefono,
                    :id_tipo_empleado, :fecha_nacimiento, :direccion, :clave, :estatus, CURDATE())"
            );
            $stmt->execute([
                ':nombre'           => $this->__get('nombre'),
                ':apellido'         => $this->__get('apellido') ?: null,
                ':tipo_cedula'      => $this->__get('tipo_cedula'),
                ':cedula'           => $this->__get('cedula'),
                ':correo'           => $this->__get('correo'),
                ':telefono'         => $this->__get('telefono') ?: null,
                ':id_tipo_empleado' => $this->__get('id_tipo_empleado'),
                ':fecha_nacimiento' => $this->__get('fecha_nacimiento'),
                ':direccion'        => $this->__get('direccion') ?: null,
                ':clave'            => password_hash($this->__get('clave'), PASSWORD_BCRYPT),
                ':estatus'          => $this->__get('estatus') ?? 1,
            ]);

            return [
                'id_empleado' => (int) $this->conn_security->lastInsertId(),
                'nombre'      => $this->__get('nombre'),
                'apellido'    => $this->__get('apellido'),
                'correo'      => $this->__get('correo'),
            ];
        } catch (ExcepcionApi $e) {
            throw $e; // regla de negocio: no la disfrazo de error técnico
        } catch (Throwable $e) {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('El correo o la cédula ya están registrados.');
            }
            error_log('EmpleadoModel::crear - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar el empleado.');
        }
    }

    /** Catálogo de tipos activos para el <select>. */
    private function tipos(): array
    {
        try {
            $stmt = $this->conn_security->query(
                "SELECT id_tipo_emp, tipo
                 FROM tipo_empleado
                 WHERE estatus = 1
                 ORDER BY tipo ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('EmpleadoModel::tipos - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los tipos de empleado.');
        }
    }

    /**
     * Lista empleados (con búsqueda opcional y paginación).
     * LIMIT/OFFSET siempre con PDO::PARAM_INT.
     */
    private function listar(): array
    {
        try {
            $buscar = trim((string) ($this->__get('buscar') ?? ''));
            $where = '';
            $params = [];

            if ($buscar !== '') {
                $where = "WHERE (e.nombre LIKE :q1 OR e.apellido LIKE :q2 OR e.correo LIKE :q3
                                OR e.cedula LIKE :q4 OR t.tipo LIKE :q5)";
                $like = '%' . $buscar . '%';
                $params = [
                    ':q1' => $like, ':q2' => $like, ':q3' => $like,
                    ':q4' => $like, ':q5' => $like,
                ];
            }

            $stmt = $this->conn_security->prepare(
                "SELECT e.id_empleado, e.nombre, e.apellido, e.tipo_cedula, e.cedula,
                        e.correo, e.telefono, e.estatus, e.fecha_creacion,
                        t.tipo AS nombre_tipo
                 FROM empleado e
                 INNER JOIN tipo_empleado t ON t.id_tipo_emp = e.id_tipo_empleado
                 $where
                 ORDER BY e.id_empleado DESC
                 LIMIT :limit OFFSET :offset"
            );

            foreach ($params as $clave => $valor) {
                $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('EmpleadoModel::listar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo listar los empleados.');
        }
    }

    /** Devuelve la fila completa de un empleado (para editar). */
    private function obtener(): array
    {
        try {
            $stmt = $this->conn_security->prepare(
                "SELECT id_empleado, nombre, apellido, tipo_cedula, cedula, correo, telefono,
                        id_tipo_empleado, fecha_nacimiento, direccion, estatus, fecha_creacion
                 FROM empleado
                 WHERE id_empleado = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                throw ExcepcionApi::noEncontrado('El empleado no existe.');
            }
            return $fila;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('EmpleadoModel::obtener - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo consultar el empleado.');
        }
    }

    /**
     * Actualiza un empleado. La clave es opcional: si no viene, no se toca.
     * Las validaciones de unicidad excluyen al propio empleado.
     */
    private function actualizar(): array
    {
        try {
            $id = (int) $this->__get('id_empleado');
            if (!$this->existeId($id)) {
                throw ExcepcionApi::noEncontrado('El empleado no existe.');
            }
            if ($this->existeCorreo($this->__get('correo'), $id)) {
                throw ExcepcionApi::yaExiste('Ya existe otro empleado con ese correo.');
            }
            if ($this->existeCedula($this->__get('tipo_cedula'), $this->__get('cedula'), $id)) {
                throw ExcepcionApi::yaExiste('Ya existe otro empleado con esa cédula.');
            }
            if ($this->existeTelefono($this->__get('telefono'), $id)) {
                throw ExcepcionApi::yaExiste('Ya existe otro empleado con ese teléfono.');
            }

            $campos = "nombre = :nombre, apellido = :apellido, tipo_cedula = :tipo_cedula,
                       cedula = :cedula, correo = :correo, telefono = :telefono,
                       id_tipo_empleado = :id_tipo_empleado, fecha_nacimiento = :fecha_nacimiento,
                       direccion = :direccion, estatus = :estatus";
            $params = [
                ':nombre'           => $this->__get('nombre'),
                ':apellido'         => $this->__get('apellido') ?: null,
                ':tipo_cedula'      => $this->__get('tipo_cedula'),
                ':cedula'           => $this->__get('cedula'),
                ':correo'           => $this->__get('correo'),
                ':telefono'         => $this->__get('telefono') ?: null,
                ':id_tipo_empleado' => $this->__get('id_tipo_empleado'),
                ':fecha_nacimiento' => $this->__get('fecha_nacimiento'),
                ':direccion'        => $this->__get('direccion') ?: null,
                ':estatus'          => $this->__get('estatus') ?? 1,
            ];

            if ($this->__get('clave') !== null) {
                $campos .= ", clave = :clave";
                $params[':clave'] = password_hash($this->__get('clave'), PASSWORD_BCRYPT);
            }

            $params[':id'] = $id;

            $stmt = $this->conn_security->prepare("UPDATE empleado SET $campos WHERE id_empleado = :id");
            $stmt->execute($params);

            return [
                'id_empleado' => $id,
                'nombre'      => $this->__get('nombre'),
                'apellido'    => $this->__get('apellido'),
            ];
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('El correo o la cédula ya están registrados.');
            }
            error_log('EmpleadoModel::actualizar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar el empleado.');
        }
    }

    /**
     * Elimina un empleado. Si tiene registros asociados (FK), se informa con
     * un 409 EN_USO en lugar de un error técnico.
     */
    private function eliminar(): bool
    {
        try {
            $id = (int) $this->__get('id_empleado');
            if (!$this->existeId($id)) {
                throw ExcepcionApi::noEncontrado('El empleado no existe.');
            }

            $stmt = $this->conn_security->prepare("DELETE FROM empleado WHERE id_empleado = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::enUso(
                    'No se puede eliminar: el empleado tiene registros asociados '
                    . '(bitácora, citas, notificaciones, etc.). Puedes desactivarlo.'
                );
            }
            error_log('EmpleadoModel::eliminar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar el empleado.');
        }
    }

    /** Tarjetas de resumen del módulo (total, activos, inactivos, nuevos del mes). */
    private function stats(): array
    {
        try {
            $total = (int) $this->conn_security
                ->query("SELECT COUNT(*) FROM empleado")->fetchColumn();
            $activos = (int) $this->conn_security
                ->query("SELECT COUNT(*) FROM empleado WHERE estatus = 1")->fetchColumn();

            $inactivos = (int) $this->conn_security
                ->query("SELECT COUNT(*) FROM empleado WHERE estatus = 0")->fetchColumn();

            $nuevosMes = (int) $this->conn_security
                ->query(
                    "SELECT COUNT(*) FROM empleado
                     WHERE MONTH(fecha_creacion) = MONTH(CURRENT_DATE())
                       AND YEAR(fecha_creacion) = YEAR(CURRENT_DATE())"
                )->fetchColumn();

            return [
                'empleados_total'      => $total,
                'empleados_activos'    => $activos,
                'empleados_inactivos'  => $inactivos,
                'empleados_nuevos_mes' => $nuevosMes,
            ];
        } catch (Throwable $e) {
            error_log('EmpleadoModel::stats - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar las estadísticas de empleados.');
        }
    }

    // ---------- Helpers privados ----------

    private function existeId(int $id): bool
    {
        $stmt = $this->conn_security->prepare("SELECT COUNT(*) FROM empleado WHERE id_empleado = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    private function existeCorreo(string $correo, int $excluir = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM empleado WHERE correo = :correo";
        $params = [':correo' => $correo];

        if ($excluir > 0) {
            $sql .= " AND id_empleado <> :excluir";
            $params[':excluir'] = $excluir;
        }

        $stmt = $this->conn_security->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function existeCedula(string $tipo, string $cedula, int $excluir = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM empleado WHERE tipo_cedula = :tipo AND cedula = :cedula";
        $params = [':tipo' => $tipo, ':cedula' => $cedula];

        if ($excluir > 0) {
            $sql .= " AND id_empleado <> :excluir";
            $params[':excluir'] = $excluir;
        }

        $stmt = $this->conn_security->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function existeTelefono(string $telefono, int $excluir = 0): bool
    {
        // Vacío no se valida (la columna es opcional).
        if (trim($telefono) === '') {
            return false;
        }

        $sql = "SELECT COUNT(*) FROM empleado WHERE telefono = :telefono";
        $params = [':telefono' => $telefono];

        if ($excluir > 0) {
            $sql .= " AND id_empleado <> :excluir";
            $params[':excluir'] = $excluir;
        }

        $stmt = $this->conn_security->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}