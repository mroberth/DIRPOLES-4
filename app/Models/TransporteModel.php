<?php
namespace App\Models;

use PDO;
use PDOException;
use Throwable;
use DateTime;
use App\Core\ExcepcionApi;

/**
 * app/Models/TransporteModel.php
 * ---------------------------------------------------------------
 * Modelo de datos para el módulo de Transporte (id_modulo = 13).
 * Maneja 6 sub-flujos:
 *   1. Rutas (`rutas`)
 *   2. Vehículos (`vehiculos`)
 *   3. Proveedores (`proveedores`)
 *   4. Repuestos (`repuestos_vehiculos`, `inventario_repuestos`)
 *   5. Asignaciones (`asignaciones_rutas`)
 *   6. Mantenimientos (`mantenimiento_vehiculos`, `repuestos_mantenimiento`)
 */
class TransporteModel extends BusinessModel
{
    /** Motivos permitidos para una ENTRADA manual de stock (kardex). */
    private const MOTIVOS_ENTRADA = ['Compra', 'Devolución', 'Donación', 'Ajuste de inventario'];

    /** Motivos permitidos para una SALIDA manual de stock (kardex). */
    private const MOTIVOS_SALIDA = ['Uso en mantenimiento', 'Vencimiento', 'Daño', 'Pérdida', 'Donación'];

    private array $atributos = [];

    // ==================== CAPA DE VALIDACIÓN (SET) ====================

    public function __set($nombre, $valor): void
    {
        switch ($nombre) {
            // ----- IDs (enteros; la existencia se valida en cada método) -----
            case 'id_ruta':
            case 'id_vehiculo':
            case 'id_proveedor':
            case 'id_repuesto':
            case 'id_asignacion':
            case 'id_mantenimiento':
            case 'id_empleado':
            case 'id_excluir':
                $valor = (int) $valor;
                break;

            // ----- RUTAS -----
            case 'nombre_ruta':
                $valor = $this->texto($valor);
                if ($valor === '' || mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El nombre de la ruta es obligatorio (máx. 100 caracteres).');
                }
                break;

            case 'trayectoria':
                $valor = $this->texto($valor);
                break;

            case 'tipo_ruta':
                $valor = $this->texto($valor);
                if ($valor === '' || mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El tipo de ruta es obligatorio.');
                }
                break;

            case 'horario_salida':
            case 'horario_llegada':
                $valor = $this->hora($valor);
                break;

            case 'punto_partida':
            case 'punto_destino':
                $valor = $this->texto($valor);
                if (mb_strlen($valor) > 255) {
                    throw ExcepcionApi::validacion('El punto de partida o destino no puede superar 255 caracteres.');
                }
                break;

            case 'estatus_ruta':
                $valor = $this->texto($valor);
                if (!in_array($valor, ['Activa', 'Inactiva'], true)) {
                    throw ExcepcionApi::validacion('El estatus de la ruta debe ser Activa o Inactiva.');
                }
                break;

            // ----- VEHÍCULOS -----
            case 'placa':
                $valor = strtoupper(trim((string) $valor));
                if ($valor === '' || mb_strlen($valor) > 20) {
                    throw ExcepcionApi::validacion('La placa es obligatoria (máx. 20 caracteres).');
                }
                break;

            case 'modelo':
                $valor = $this->texto($valor);
                if (mb_strlen($valor) > 50) {
                    throw ExcepcionApi::validacion('El modelo no puede superar 50 caracteres.');
                }
                break;

            case 'tipo_vehiculo':
                $valor = $this->texto($valor);
                if (!in_array($valor, ['Autobús', 'Camioneta', 'Automóvil'], true)) {
                    throw ExcepcionApi::validacion('El tipo de vehículo no es válido.');
                }
                break;

            case 'estado_vehiculo':
                $valor = $this->texto($valor);
                if (!in_array($valor, ['Activo', 'Inactivo', 'Mantenimiento'], true)) {
                    throw ExcepcionApi::validacion('El estado del vehículo no es válido.');
                }
                break;

            case 'fecha_adquisicion':
                $valor = $this->fecha($valor, 'de adquisición', false);
                break;

            // ----- PROVEEDORES -----
            case 'tipo_documento':
                $valor = strtoupper(trim((string) $valor));
                if (!in_array($valor, ['V', 'E', 'J', 'G'], true)) {
                    throw ExcepcionApi::validacion('El tipo de documento debe ser V, E, J o G.');
                }
                break;

            case 'num_documento':
                $valor = preg_replace('/\D/', '', (string) $valor);
                if (!preg_match('/^\d{6,10}$/', $valor)) {
                    throw ExcepcionApi::validacion('El número de documento debe tener entre 6 y 10 dígitos.');
                }
                break;

            case 'nombre_proveedor':
                $valor = $this->texto($valor);
                if ($valor === '' || mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El nombre del proveedor es obligatorio (máx. 100 caracteres).');
                }
                break;

            case 'telefono_proveedor':
                $valor = preg_replace('/\D/', '', (string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El teléfono del proveedor es obligatorio.');
                }
                if (!preg_match('/^(0412|0414|0416|0422|0424|0426|02\d{2})\d{7}$/', $valor)) {
                    throw ExcepcionApi::validacion(
                        'El teléfono debe empezar por 0412, 0414, 0416, 0422, 0424, 0426 o código de área (02xx) y tener 11 dígitos en total (ej: 04129298008).'
                    );
                }
                break;

            case 'correo_proveedor':
                $valor = trim((string) $valor);
                if ($valor === '' || mb_strlen($valor) > 100 || !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    throw ExcepcionApi::validacion('El correo electrónico no es válido.');
                }
                break;

            case 'direccion_proveedor':
                $valor = $this->texto($valor);
                if ($valor === '' || mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('La dirección del proveedor es obligatoria.');
                }
                break;

            case 'estatus_proveedor':
                $valor = $this->texto($valor);
                if (!in_array($valor, ['Activo', 'Inactivo'], true)) {
                    throw ExcepcionApi::validacion('El estatus del proveedor debe ser Activo o Inactivo.');
                }
                break;

            // ----- REPUESTOS -----
            case 'nombre_repuesto':
                $valor = $this->texto($valor);
                if ($valor === '' || mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El nombre del repuesto es obligatorio.');
                }
                break;

            case 'descripcion_repuesto':
                $valor = $this->texto($valor);
                if (mb_strlen($valor) > 255) {
                    throw ExcepcionApi::validacion('La descripción del repuesto no puede superar 255 caracteres.');
                }
                break;

            case 'cantidad_repuesto':
                if (filter_var($valor, FILTER_VALIDATE_INT) === false || (int) $valor < 0) {
                    throw ExcepcionApi::validacion('La cantidad de repuestos debe ser un entero >= 0.');
                }
                $valor = (int) $valor;
                break;

            case 'razon_movimiento':
                $valor = $this->texto($valor);
                if (!in_array($valor, array_merge(self::MOTIVOS_ENTRADA, self::MOTIVOS_SALIDA), true)) {
                    throw ExcepcionApi::validacion(
                        'La razón del movimiento no es válida. Debe ser uno del catálogo: Compra, Devolución, Donación, Ajuste de inventario, Uso en mantenimiento, Vencimiento, Daño o Pérdida.'
                    );
                }
                break;

            // ----- ASIGNACIONES -----
            case 'fecha_asignacion':
                $valor = $this->fecha($valor, 'de la asignación', true);
                break;

            case 'estatus_asignacion':
                $valor = $this->texto($valor);
                if (!in_array($valor, ['Activa', 'Inactiva'], true)) {
                    throw ExcepcionApi::validacion('El estatus de la asignación debe ser Activa o Inactiva.');
                }
                break;

            // ----- MANTENIMIENTO -----
            case 'tipo_mantenimiento':
                $valor = $this->texto($valor);
                if (!in_array($valor, ['Preventivo', 'Correctivo'], true)) {
                    throw ExcepcionApi::validacion('El tipo de mantenimiento debe ser Preventivo o Correctivo.');
                }
                break;

            case 'fecha_mantenimiento':
                $valor = $this->fecha($valor, 'del mantenimiento', true);
                break;

            case 'descripcion_mantenimiento':
                $valor = $this->texto($valor);
                if (mb_strlen($valor) > 1000) {
                    throw ExcepcionApi::validacion('La descripción del mantenimiento no puede superar 1000 caracteres.');
                }
                break;

            case 'repuestos_usados':
                if (!is_array($valor)) {
                    throw ExcepcionApi::validacion('Los repuestos usados deben enviarse como una lista.');
                }
                break;

            // ----- PAGINACIÓN (listados) -----
            case 'limit':
            case 'offset':
                if (filter_var($valor, FILTER_VALIDATE_INT) === false || (int) $valor < 0) {
                    throw ExcepcionApi::validacion("El parámetro '{$nombre}' debe ser un entero >= 0.");
                }
                $valor = (int) $valor;
                break;

            default:
                throw ExcepcionApi::validacion("Atributo no reconocido: '{$nombre}'.");
        }

        $this->atributos[$nombre] = $valor;
    }

    public function __get($nombre): mixed
    {
        return $this->atributos[$nombre] ?? null;
    }

    // ==================== HELPERS PRIVADOS ====================

    /** Limpia y sanea un texto libre (anti-XSS). */
    private function texto(mixed $valor): string
    {
        $valor = str_replace(['<', '>'], '', (string) $valor);
        return trim($valor);
    }

    /** Valida una fecha 'Y-m-d'; devuelve null si es opcional y está vacía. */
    private function fecha(mixed $valor, string $campo, bool $obligatoria): ?string
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            if ($obligatoria) {
                throw ExcepcionApi::validacion("La fecha {$campo} es obligatoria.");
            }
            return null;
        }
        $f = DateTime::createFromFormat('Y-m-d', $valor);
        if (!$f || $f->format('Y-m-d') !== $valor) {
            throw ExcepcionApi::validacion("La fecha {$campo} no es válida (formato AAAA-MM-DD).");
        }
        return $valor;
    }

    /** Valida una hora 'HH:MM'; devuelve null si está vacía. */
    private function hora(mixed $valor): ?string
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $valor)) {
            throw ExcepcionApi::validacion('El horario debe tener formato HH:MM.');
        }
        return $valor;
    }

    /** id_empleado de la sesión para el kardex (nunca inventa un ID). */
    private function idEmpleadoSesion(): int
    {
        $id = (int) ($_SESSION['id_empleado'] ?? 0);
        if ($id <= 0) {
            throw ExcepcionApi::accesoDenegado('Debes iniciar sesión para registrar movimientos de inventario.');
        }
        return $id;
    }

    // ==================== DESPACHADOR PÚBLICO ====================

    public function manejarAccion($accion): mixed
    {
        return match ($accion) {
            'stats'                        => $this->obtenerStats(),

            // Rutas
            'listar_rutas'                 => $this->listarRutas(),
            'crear_ruta'                  => $this->crearRuta(),
            'obtener_ruta'                => $this->obtenerRuta(),
            'actualizar_ruta'             => $this->actualizarRuta(),
            'eliminar_ruta'               => $this->eliminarRuta(),

            // Vehículos
            'listar_vehiculos'            => $this->listarVehiculos(),
            'crear_vehiculo'              => $this->crearVehiculo(),
            'obtener_vehiculo'            => $this->obtenerVehiculo(),
            'actualizar_vehiculo'         => $this->actualizarVehiculo(),
            'eliminar_vehiculo'           => $this->eliminarVehiculo(),
            'validar_placa'               => $this->validarPlacaRemota(),

            // Proveedores
            'listar_proveedores'          => $this->listarProveedores(),
            'crear_proveedor'             => $this->crearProveedor(),
            'obtener_proveedor'           => $this->obtenerProveedor(),
            'actualizar_proveedor'        => $this->actualizarProveedor(),
            'eliminar_proveedor'          => $this->eliminarProveedor(),
            'validar_documento_proveedor' => $this->validarDocumentoProveedorRemoto(),
            'validar_correo_proveedor'    => $this->validarCorreoProveedorRemoto(),
            'validar_telefono_proveedor'  => $this->validarTelefonoProveedorRemoto(),

            // Repuestos
            'listar_repuestos'            => $this->listarRepuestos(),
            'crear_repuesto'              => $this->crearRepuesto(),
            'obtener_repuesto'            => $this->obtenerRepuesto(),
            'actualizar_repuesto'         => $this->actualizarRepuesto(),
            'eliminar_repuesto'           => $this->eliminarRepuesto(),
            'entrada_repuesto'            => $this->registrarEntradaRepuesto(),
            'salida_repuesto'             => $this->registrarSalidaRepuesto(),
            'historial_repuestos'         => $this->obtenerHistorialRepuestos(),

            // Asignaciones
            'listar_asignaciones'         => $this->listarAsignaciones(),
            'crear_asignacion'            => $this->crearAsignacion(),
            'obtener_asignacion'          => $this->obtenerAsignacion(),
            'actualizar_asignacion'       => $this->actualizarAsignacion(),
            'eliminar_asignacion'         => $this->eliminarAsignacion(),
            'opciones_asignacion'         => $this->obtenerOpcionesAsignacion(),

            // Mantenimientos
            'listar_mantenimientos'       => $this->listarMantenimientos(),
            'crear_mantenimiento'         => $this->crearMantenimiento(),
            'obtener_mantenimiento'       => $this->obtenerMantenimiento(),
            'eliminar_mantenimiento'       => $this->eliminarMantenimiento(),

            default => throw ExcepcionApi::errorInterno("Acción no válida en TransporteModel: '{$accion}'."),
        };
    }

    // ==================== STATS DEL MÓDULO ====================

    private function obtenerStats(): array
    {
        try {
            $totalVehiculos    = (int) $this->conn->query("SELECT COUNT(*) FROM vehiculos")->fetchColumn();
            $vehiculosActivos  = (int) $this->conn->query("SELECT COUNT(*) FROM vehiculos WHERE estado = 'Activo'")->fetchColumn();
            $vehiculosMant     = (int) $this->conn->query("SELECT COUNT(*) FROM vehiculos WHERE estado = 'Mantenimiento'")->fetchColumn();

            $totalRutas        = (int) $this->conn->query("SELECT COUNT(*) FROM rutas")->fetchColumn();
            $rutasActivas      = (int) $this->conn->query("SELECT COUNT(*) FROM rutas WHERE estatus = 'Activa'")->fetchColumn();

            $totalProveedores  = (int) $this->conn->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
            $totalRepuestos    = (int) $this->conn->query("SELECT COUNT(*) FROM repuestos_vehiculos")->fetchColumn();
            $repuestosBajoStock= (int) $this->conn->query("SELECT COUNT(*) FROM repuestos_vehiculos WHERE cantidad < 5")->fetchColumn();
            $asignacionesAct   = (int) $this->conn->query("SELECT COUNT(*) FROM asignaciones_rutas WHERE estatus = 'Activa'")->fetchColumn();

            return [
                'total_vehiculos'      => $totalVehiculos,
                'vehiculos_activos'    => $vehiculosActivos,
                'vehiculos_mantenimiento' => $vehiculosMant,
                'total_rutas'          => $totalRutas,
                'rutas_activas'        => $rutasActivas,
                'total_proveedores'    => $totalProveedores,
                'total_repuestos'      => $totalRepuestos,
                'repuestos_bajo_stock' => $repuestosBajoStock,
                'asignaciones_activas' => $asignacionesAct,
            ];
        } catch (PDOException $e) {
            error_log('TransporteModel::obtenerStats - ' . $e->getMessage());
            return [];
        }
    }

    // ==================== 1. RUTAS ====================

    private function listarRutas(): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM rutas ORDER BY id_ruta DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function crearRuta(): array
    {
        $nombre = $this->__get('nombre_ruta');
        if ($this->existeRutaNombre($nombre)) {
            throw ExcepcionApi::yaExiste("Ya existe una ruta registrada con el nombre '{$nombre}'.");
        }

        $stmt = $this->conn->prepare("
            INSERT INTO rutas (nombre_ruta, trayectoria, tipo_ruta, horario_salida, horario_llegada, punto_partida, punto_destino, estatus)
            VALUES (:nombre, :trayectoria, :tipo, :salida, :llegada, :partida, :destino, :estatus)
        ");
        $stmt->execute([
            ':nombre'      => $nombre,
            ':trayectoria' => $this->__get('trayectoria'),
            ':tipo'        => $this->__get('tipo_ruta') ?? 'Urbana',
            ':salida'      => $this->__get('horario_salida') ?: null,
            ':llegada'     => $this->__get('horario_llegada') ?: null,
            ':partida'     => $this->__get('punto_partida') ?: null,
            ':destino'     => $this->__get('punto_destino') ?: null,
            ':estatus'     => $this->__get('estatus_ruta') ?? 'Activa',
        ]);

        $id = (int) $this->conn->lastInsertId();
        return $this->obtenerRutaPorId($id);
    }

    private function obtenerRuta(): array
    {
        $id = (int) ($this->__get('id_ruta') ?? 0);
        return $this->obtenerRutaPorId($id);
    }

    private function obtenerRutaPorId(int $id): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM rutas WHERE id_ruta = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            throw ExcepcionApi::noEncontrado('La ruta solicitada no existe.');
        }
        return $res;
    }

    private function actualizarRuta(): array
    {
        $id = (int) ($this->__get('id_ruta') ?? 0);
        $this->obtenerRutaPorId($id);

        $nombre = $this->__get('nombre_ruta');
        if ($this->existeRutaNombre($nombre, $id)) {
            throw ExcepcionApi::yaExiste("Ya existe otra ruta registrada con el nombre '{$nombre}'.");
        }

        $stmt = $this->conn->prepare("
            UPDATE rutas SET
                nombre_ruta = :nombre,
                trayectoria = :trayectoria,
                tipo_ruta = :tipo,
                horario_salida = :salida,
                horario_llegada = :llegada,
                punto_partida = :partida,
                punto_destino = :destino,
                estatus = :estatus
            WHERE id_ruta = :id
        ");
        $stmt->execute([
            ':nombre'      => $nombre,
            ':trayectoria' => $this->__get('trayectoria'),
            ':tipo'        => $this->__get('tipo_ruta') ?? 'Urbana',
            ':salida'      => $this->__get('horario_salida') ?: null,
            ':llegada'     => $this->__get('horario_llegada') ?: null,
            ':partida'     => $this->__get('punto_partida') ?: null,
            ':destino'     => $this->__get('punto_destino') ?: null,
            ':estatus'     => $this->__get('estatus_ruta') ?? 'Activa',
            ':id'          => $id,
        ]);

        return $this->obtenerRutaPorId($id);
    }

    private function eliminarRuta(): array
    {
        $id = (int) ($this->__get('id_ruta') ?? 0);

        $this->conn->beginTransaction();
        try {
            // FOR UPDATE: dos eliminaciones simultáneas no superan ambas el chequeo.
            $stmt = $this->conn->prepare("SELECT * FROM rutas WHERE id_ruta = :id FOR UPDATE");
            $stmt->execute([':id' => $id]);
            $ruta = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$ruta) {
                throw ExcepcionApi::noEncontrado('La ruta solicitada no existe.');
            }

            $stmtCheck = $this->conn->prepare("SELECT COUNT(*) FROM asignaciones_rutas WHERE id_ruta = :id");
            $stmtCheck->execute([':id' => $id]);
            if ((int) $stmtCheck->fetchColumn() > 0) {
                throw ExcepcionApi::enUso('No se puede eliminar la ruta porque está asociada a asignaciones de choferes.');
            }

            $stmtDel = $this->conn->prepare("DELETE FROM rutas WHERE id_ruta = :id");
            $stmtDel->execute([':id' => $id]);

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            if ($e instanceof ExcepcionApi) throw $e;
            throw ExcepcionApi::errorInterno('Error al eliminar la ruta.');
        }

        return ['mensaje' => 'Ruta eliminada correctamente.', 'ruta' => $ruta];
    }

    private function existeRutaNombre(string $nombre, int $excluirId = 0): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM rutas WHERE nombre_ruta = :n AND id_ruta <> :x");
        $stmt->execute([':n' => $nombre, ':x' => $excluirId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ==================== 2. VEHÍCULOS ====================

    private function listarVehiculos(): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM vehiculos ORDER BY id_vehiculo DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function crearVehiculo(): array
    {
        $placa = $this->__get('placa');
        if ($this->existePlaca($placa)) {
            throw ExcepcionApi::yaExiste("Ya existe un vehículo registrado con la placa '{$placa}'.");
        }

        $stmt = $this->conn->prepare("
            INSERT INTO vehiculos (placa, modelo, tipo, fecha_adquisicion, estado)
            VALUES (:placa, :modelo, :tipo, :fecha, :estado)
        ");
        $stmt->execute([
            ':placa'  => $placa,
            ':modelo' => $this->__get('modelo') ?: null,
            ':tipo'   => $this->__get('tipo_vehiculo') ?? 'Autobús',
            ':fecha'  => $this->__get('fecha_adquisicion') ?: null,
            ':estado' => $this->__get('estado_vehiculo') ?? 'Activo',
        ]);

        $id = (int) $this->conn->lastInsertId();
        return $this->obtenerVehiculoPorId($id);
    }

    private function obtenerVehiculo(): array
    {
        $id = (int) ($this->__get('id_vehiculo') ?? 0);
        return $this->obtenerVehiculoPorId($id);
    }

    private function obtenerVehiculoPorId(int $id): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM vehiculos WHERE id_vehiculo = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            throw ExcepcionApi::noEncontrado('El vehículo solicitado no existe.');
        }
        return $res;
    }

    private function actualizarVehiculo(): array
    {
        $id = (int) ($this->__get('id_vehiculo') ?? 0);
        $this->obtenerVehiculoPorId($id);

        $placa = $this->__get('placa');
        if ($this->existePlaca($placa, $id)) {
            throw ExcepcionApi::yaExiste("Ya existe otro vehículo registrado con la placa '{$placa}'.");
        }

        $stmt = $this->conn->prepare("
            UPDATE vehiculos SET
                placa = :placa,
                modelo = :modelo,
                tipo = :tipo,
                fecha_adquisicion = :fecha,
                estado = :estado
            WHERE id_vehiculo = :id
        ");
        $stmt->execute([
            ':placa'  => $placa,
            ':modelo' => $this->__get('modelo') ?: null,
            ':tipo'   => $this->__get('tipo_vehiculo') ?? 'Autobús',
            ':fecha'  => $this->__get('fecha_adquisicion') ?: null,
            ':estado' => $this->__get('estado_vehiculo') ?? 'Activo',
            ':id'     => $id,
        ]);

        return $this->obtenerVehiculoPorId($id);
    }

    private function eliminarVehiculo(): array
    {
        $id = (int) ($this->__get('id_vehiculo') ?? 0);

        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare("SELECT * FROM vehiculos WHERE id_vehiculo = :id FOR UPDATE");
            $stmt->execute([':id' => $id]);
            $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$vehiculo) {
                throw ExcepcionApi::noEncontrado('El vehículo solicitado no existe.');
            }

            $stmtCheck1 = $this->conn->prepare("SELECT COUNT(*) FROM asignaciones_rutas WHERE id_vehiculo = :id");
            $stmtCheck1->execute([':id' => $id]);
            if ((int) $stmtCheck1->fetchColumn() > 0) {
                throw ExcepcionApi::enUso('No se puede eliminar el vehículo porque tiene asignaciones de ruta asociadas.');
            }

            $stmtCheck2 = $this->conn->prepare("SELECT COUNT(*) FROM mantenimiento_vehiculos WHERE id_vehiculo = :id");
            $stmtCheck2->execute([':id' => $id]);
            if ((int) $stmtCheck2->fetchColumn() > 0) {
                throw ExcepcionApi::enUso('No se puede eliminar el vehículo porque tiene mantenimientos registrados.');
            }

            $stmtDel = $this->conn->prepare("DELETE FROM vehiculos WHERE id_vehiculo = :id");
            $stmtDel->execute([':id' => $id]);

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            if ($e instanceof ExcepcionApi) throw $e;
            throw ExcepcionApi::errorInterno('Error al eliminar el vehículo.');
        }

        return ['mensaje' => 'Vehículo eliminado correctamente.', 'vehiculo' => $vehiculo];
    }

    private function existePlaca(string $placa, int $excluirId = 0): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM vehiculos WHERE placa = :p AND id_vehiculo <> :x");
        $stmt->execute([':p' => $placa, ':x' => $excluirId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function validarPlacaRemota(): array
    {
        $placa = strtoupper(trim((string) ($this->__get('placa') ?? '')));
        $excluir = (int) ($this->__get('id_excluir') ?? 0);

        if ($placa === '') {
            return ['existe' => false];
        }

        return ['existe' => $this->existePlaca($placa, $excluir)];
    }

    // ==================== 3. PROVEEDORES ====================

    private function listarProveedores(): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM proveedores ORDER BY id_proveedor DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function crearProveedor(): array
    {
        $tipoDoc = $this->__get('tipo_documento');
        $numDoc  = $this->__get('num_documento');
        $correo  = $this->__get('correo_proveedor');
        $tel     = $this->__get('telefono_proveedor');

        if ($this->existeDocumentoGlobal($tipoDoc, $numDoc)) {
            throw ExcepcionApi::yaExiste("El documento {$tipoDoc}-{$numDoc} ya está registrado en el sistema.");
        }

        if ($this->existeCorreoGlobal($correo)) {
            throw ExcepcionApi::yaExiste("El correo electrónico '{$correo}' ya está registrado en el sistema.");
        }

        if ($this->existeTelefonoGlobal($tel)) {
            throw ExcepcionApi::yaExiste("El teléfono '{$tel}' ya está registrado en el sistema.");
        }

        $stmt = $this->conn->prepare("
            INSERT INTO proveedores (tipo_documento, num_documento, nombre, telefono, correo, direccion, estatus)
            VALUES (:tipo, :num, :nombre, :tel, :correo, :dir, :estatus)
        ");
        $stmt->execute([
            ':tipo'    => $tipoDoc,
            ':num'     => $numDoc,
            ':nombre'  => $this->__get('nombre_proveedor'),
            ':tel'     => $tel,
            ':correo'  => $correo,
            ':dir'     => $this->__get('direccion_proveedor'),
            ':estatus' => $this->__get('estatus_proveedor') ?? 'Activo',
        ]);

        $id = (int) $this->conn->lastInsertId();
        return $this->obtenerProveedorPorId($id);
    }

    private function obtenerProveedor(): array
    {
        $id = (int) ($this->__get('id_proveedor') ?? 0);
        return $this->obtenerProveedorPorId($id);
    }

    private function obtenerProveedorPorId(int $id): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM proveedores WHERE id_proveedor = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            throw ExcepcionApi::noEncontrado('El proveedor solicitado no existe.');
        }
        return $res;
    }

    private function actualizarProveedor(): array
    {
        $id = (int) ($this->__get('id_proveedor') ?? 0);
        $this->obtenerProveedorPorId($id);

        $tipoDoc = $this->__get('tipo_documento');
        $numDoc  = $this->__get('num_documento');
        $correo  = $this->__get('correo_proveedor');
        $tel     = $this->__get('telefono_proveedor');

        if ($this->existeDocumentoGlobal($tipoDoc, $numDoc, $id)) {
            throw ExcepcionApi::yaExiste("El documento {$tipoDoc}-{$numDoc} ya está registrado en el sistema.");
        }

        if ($this->existeCorreoGlobal($correo, $id)) {
            throw ExcepcionApi::yaExiste("El correo electrónico '{$correo}' ya está registrado en el sistema.");
        }

        if ($this->existeTelefonoGlobal($tel, $id)) {
            throw ExcepcionApi::yaExiste("El teléfono '{$tel}' ya está registrado en el sistema.");
        }

        $stmt = $this->conn->prepare("
            UPDATE proveedores SET
                tipo_documento = :tipo,
                num_documento = :num,
                nombre = :nombre,
                telefono = :tel,
                correo = :correo,
                direccion = :dir,
                estatus = :estatus
            WHERE id_proveedor = :id
        ");
        $stmt->execute([
            ':tipo'    => $tipoDoc,
            ':num'     => $numDoc,
            ':nombre'  => $this->__get('nombre_proveedor'),
            ':tel'     => $tel,
            ':correo'  => $correo,
            ':dir'     => $this->__get('direccion_proveedor'),
            ':estatus' => $this->__get('estatus_proveedor') ?? 'Activo',
            ':id'      => $id,
        ]);

        return $this->obtenerProveedorPorId($id);
    }

    private function eliminarProveedor(): array
    {
        $id = (int) ($this->__get('id_proveedor') ?? 0);

        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare("SELECT * FROM proveedores WHERE id_proveedor = :id FOR UPDATE");
            $stmt->execute([':id' => $id]);
            $prov = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$prov) {
                throw ExcepcionApi::noEncontrado('El proveedor solicitado no existe.');
            }

            $stmtCheck = $this->conn->prepare("SELECT COUNT(*) FROM repuestos_vehiculos WHERE id_proveedor = :id");
            $stmtCheck->execute([':id' => $id]);
            if ((int) $stmtCheck->fetchColumn() > 0) {
                throw ExcepcionApi::enUso('No se puede eliminar el proveedor porque tiene repuestos asociados en el inventario.');
            }

            $stmtDel = $this->conn->prepare("DELETE FROM proveedores WHERE id_proveedor = :id");
            $stmtDel->execute([':id' => $id]);

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            if ($e instanceof ExcepcionApi) throw $e;
            throw ExcepcionApi::errorInterno('Error al eliminar el proveedor.');
        }

        return ['mensaje' => 'Proveedor eliminado correctamente.', 'proveedor' => $prov];
    }

    // Unicidad GLOBAL (empleado + beneficiario + proveedores)
    private function existeDocumentoGlobal(string $tipo, string $doc, int $excluirId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM (
                    SELECT id_empleado FROM dirpoles_security.empleado
                     WHERE tipo_cedula = :t1 AND cedula = :c1
                    UNION ALL
                    SELECT id_beneficiario FROM dirpoles_business.beneficiario
                     WHERE tipo_cedula = :t2 AND cedula = :c2
                    UNION ALL
                    SELECT id_proveedor FROM dirpoles_business.proveedores
                     WHERE tipo_documento = :t3 AND num_documento = :c3 AND id_proveedor <> :x3
                ) t";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':t1' => $tipo, ':c1' => $doc,
            ':t2' => $tipo, ':c2' => $doc,
            ':t3' => $tipo, ':c3' => $doc, ':x3' => $excluirId
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function existeCorreoGlobal(string $correo, int $excluirId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM (
                    SELECT id_empleado FROM dirpoles_security.empleado WHERE correo = :c1
                    UNION ALL
                    SELECT id_beneficiario FROM dirpoles_business.beneficiario WHERE correo = :c2
                    UNION ALL
                    SELECT id_proveedor FROM dirpoles_business.proveedores WHERE correo = :c3 AND id_proveedor <> :x3
                ) t";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':c1' => $correo, ':c2' => $correo, ':c3' => $correo, ':x3' => $excluirId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function existeTelefonoGlobal(string $tel, int $excluirId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM (
                    SELECT id_empleado FROM dirpoles_security.empleado WHERE telefono = :t1
                    UNION ALL
                    SELECT id_beneficiario FROM dirpoles_business.beneficiario WHERE telefono = :t2
                    UNION ALL
                    SELECT id_proveedor FROM dirpoles_business.proveedores WHERE telefono = :t3 AND id_proveedor <> :x3
                ) t";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':t1' => $tel, ':t2' => $tel, ':t3' => $tel, ':x3' => $excluirId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function validarDocumentoProveedorRemoto(): array
    {
        $tipo = strtoupper(trim((string) ($this->__get('tipo_documento') ?? 'V')));
        $num  = trim((string) ($this->__get('num_documento') ?? ''));
        $excluir = (int) ($this->__get('id_excluir') ?? 0);

        if ($num === '') {
            return ['existe' => false];
        }
        return ['existe' => $this->existeDocumentoGlobal($tipo, $num, $excluir)];
    }

    private function validarCorreoProveedorRemoto(): array
    {
        $correo = trim((string) ($this->__get('correo_proveedor') ?? ''));
        $excluir = (int) ($this->__get('id_excluir') ?? 0);

        if ($correo === '') {
            return ['existe' => false];
        }
        return ['existe' => $this->existeCorreoGlobal($correo, $excluir)];
    }

    private function validarTelefonoProveedorRemoto(): array
    {
        $tel = trim((string) ($this->__get('telefono_proveedor') ?? ''));
        $excluir = (int) ($this->__get('id_excluir') ?? 0);

        if ($tel === '') {
            return ['existe' => false];
        }
        return ['existe' => $this->existeTelefonoGlobal($tel, $excluir)];
    }

    // ==================== 4. REPUESTOS ====================

    private function listarRepuestos(): array
    {
        $stmt = $this->conn->prepare("
            SELECT r.*, p.nombre AS nombre_proveedor
            FROM repuestos_vehiculos r
            LEFT JOIN proveedores p ON r.id_proveedor = p.id_proveedor
            ORDER BY r.id_repuesto DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function crearRepuesto(): array
    {
        $nombre = $this->__get('nombre_repuesto');
        $provId = (int) ($this->__get('id_proveedor') ?? 0);

        $stmt = $this->conn->prepare("
            INSERT INTO repuestos_vehiculos (nombre, descripcion, cantidad, id_proveedor, fecha_creacion, estatus)
            VALUES (:nombre, :desc, 0, :prov, CURRENT_DATE(), 'Agotado')
        ");
        $stmt->execute([
            ':nombre' => $nombre,
            ':desc'   => $this->__get('descripcion_repuesto') ?: null,
            ':prov'   => $provId > 0 ? $provId : null,
        ]);

        $id = (int) $this->conn->lastInsertId();

        // Registrar en Kardex
        $idEmp = $this->idEmpleadoSesion();
        $stmtK = $this->conn->prepare("
            INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento)
            VALUES (:id, :emp, '0', 'Registro', 'Alta inicial de repuesto')
        ");
        $stmtK->execute([':id' => $id, ':emp' => $idEmp]);

        return $this->obtenerRepuestoPorId($id);
    }

    private function obtenerRepuesto(): array
    {
        $id = (int) ($this->__get('id_repuesto') ?? 0);
        return $this->obtenerRepuestoPorId($id);
    }

    private function obtenerRepuestoPorId(int $id): array
    {
        $stmt = $this->conn->prepare("
            SELECT r.*, p.nombre AS nombre_proveedor
            FROM repuestos_vehiculos r
            LEFT JOIN proveedores p ON r.id_proveedor = p.id_proveedor
            WHERE r.id_repuesto = :id
        ");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            throw ExcepcionApi::noEncontrado('El repuesto solicitado no existe.');
        }
        return $res;
    }

    private function actualizarRepuesto(): array
    {
        $id = (int) ($this->__get('id_repuesto') ?? 0);
        $rep = $this->obtenerRepuestoPorId($id);

        $provId = (int) ($this->__get('id_proveedor') ?? 0);

        $stmt = $this->conn->prepare("
            UPDATE repuestos_vehiculos SET
                nombre = :nombre,
                descripcion = :desc,
                id_proveedor = :prov
            WHERE id_repuesto = :id
        ");
        $stmt->execute([
            ':nombre' => $this->__get('nombre_repuesto'),
            ':desc'   => $this->__get('descripcion_repuesto') ?: null,
            ':prov'   => $provId > 0 ? $provId : null,
            ':id'     => $id,
        ]);

        return $this->obtenerRepuestoPorId($id);
    }

    private function eliminarRepuesto(): array
    {
        $id = (int) ($this->__get('id_repuesto') ?? 0);
        $rep = $this->obtenerRepuestoPorId($id);

        if ((int) $rep['cantidad'] > 0) {
            throw ExcepcionApi::enUso('No se puede eliminar un repuesto que tiene stock disponible. Realiza una Salida primero.');
        }

        // Verificar si se usó en mantenimientos
        $stmtCheck = $this->conn->prepare("SELECT COUNT(*) FROM repuestos_mantenimiento WHERE id_repuesto = :id");
        $stmtCheck->execute([':id' => $id]);
        if ((int) $stmtCheck->fetchColumn() > 0) {
            throw ExcepcionApi::enUso('No se puede eliminar el repuesto porque ha sido utilizado en mantenimientos registrados.');
        }

        // El kardex es auditoría: si ya hubo entradas/salidas reales, el
        // repuesto no se elimina (solo se borra la fila de alta 'Registro').
        $stmtK = $this->conn->prepare("
            SELECT COUNT(*) FROM inventario_repuestos
            WHERE id_repuesto = :id AND tipo_movimiento <> 'Registro'
        ");
        $stmtK->execute([':id' => $id]);
        if ((int) $stmtK->fetchColumn() > 0) {
            throw ExcepcionApi::enUso('No se puede eliminar el repuesto porque tiene movimientos de entrada/salida en su historial (kardex).');
        }

        $this->conn->beginTransaction();
        try {
            $stmt1 = $this->conn->prepare("DELETE FROM inventario_repuestos WHERE id_repuesto = :id AND tipo_movimiento = 'Registro'");
            $stmt1->execute([':id' => $id]);

            $stmt2 = $this->conn->prepare("DELETE FROM repuestos_vehiculos WHERE id_repuesto = :id");
            $stmt2->execute([':id' => $id]);

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw ExcepcionApi::errorInterno('Error al eliminar el repuesto.');
        }

        return ['mensaje' => 'Repuesto eliminado correctamente.', 'repuesto' => $rep];
    }

    private function registrarEntradaRepuesto(): array
    {
        $idRep = (int) ($this->__get('id_repuesto') ?? 0);
        $cant  = (int) ($this->__get('cantidad_repuesto') ?? 0);
        $razon = trim((string) ($this->__get('razon_movimiento') ?? ''));

        if ($cant <= 0) {
            throw ExcepcionApi::validacion('La cantidad a ingresar debe ser un entero mayor a 0.');
        }
        if (!in_array($razon, self::MOTIVOS_ENTRADA, true)) {
            throw ExcepcionApi::validacion('El motivo de la entrada debe ser: Compra, Devolución, Donación o Ajuste de inventario.');
        }

        $this->conn->beginTransaction();
        try {
            // FOR UPDATE
            $stmtSel = $this->conn->prepare("SELECT cantidad FROM repuestos_vehiculos WHERE id_repuesto = :id FOR UPDATE");
            $stmtSel->execute([':id' => $idRep]);
            $curr = $stmtSel->fetchColumn();
            if ($curr === false) {
                throw ExcepcionApi::noEncontrado('El repuesto no existe.');
            }

            $nuevoStock = ((int) $curr) + $cant;
            $nuevoEstatus = $nuevoStock > 0 ? 'Disponible' : 'Agotado';

            $stmtUp = $this->conn->prepare("
                UPDATE repuestos_vehiculos SET cantidad = :c, estatus = :e WHERE id_repuesto = :id
            ");
            $stmtUp->execute([':c' => $nuevoStock, ':e' => $nuevoEstatus, ':id' => $idRep]);

            // Kardex
            $idEmp = $this->idEmpleadoSesion();
            $stmtK = $this->conn->prepare("
                INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento)
                VALUES (:id, :emp, :cant, 'Entrada', :razon)
            ");
            $stmtK->execute([
                ':id'    => $idRep,
                ':emp'   => $idEmp,
                ':cant'  => (string) $cant,
                ':razon' => $razon,
            ]);

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            if ($e instanceof ExcepcionApi) throw $e;
            throw ExcepcionApi::errorInterno('Error al procesar la entrada de repuesto.');
        }

        return $this->obtenerRepuestoPorId($idRep);
    }

    private function registrarSalidaRepuesto(): array
    {
        $idRep = (int) ($this->__get('id_repuesto') ?? 0);
        $cant  = (int) ($this->__get('cantidad_repuesto') ?? 0);
        $razon = trim((string) ($this->__get('razon_movimiento') ?? ''));

        if ($cant <= 0) {
            throw ExcepcionApi::validacion('La cantidad a retirar debe ser un entero mayor a 0.');
        }
        if (!in_array($razon, self::MOTIVOS_SALIDA, true)) {
            throw ExcepcionApi::validacion('El motivo de la salida debe ser: Uso en mantenimiento, Vencimiento, Daño, Pérdida o Donación.');
        }

        $this->conn->beginTransaction();
        try {
            $stmtSel = $this->conn->prepare("SELECT cantidad FROM repuestos_vehiculos WHERE id_repuesto = :id FOR UPDATE");
            $stmtSel->execute([':id' => $idRep]);
            $curr = $stmtSel->fetchColumn();
            if ($curr === false) {
                throw ExcepcionApi::noEncontrado('El repuesto no existe.');
            }

            $stockActual = (int) $curr;
            if ($stockActual < $cant) {
                throw ExcepcionApi::validacion("Stock insuficiente. Disponible: {$stockActual}, Solicitado: {$cant}.");
            }

            $nuevoStock = $stockActual - $cant;
            $nuevoEstatus = $nuevoStock > 0 ? 'Disponible' : 'Agotado';

            $stmtUp = $this->conn->prepare("
                UPDATE repuestos_vehiculos SET cantidad = :c, estatus = :e WHERE id_repuesto = :id
            ");
            $stmtUp->execute([':c' => $nuevoStock, ':e' => $nuevoEstatus, ':id' => $idRep]);

            // Kardex
            $idEmp = $this->idEmpleadoSesion();
            $stmtK = $this->conn->prepare("
                INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento)
                VALUES (:id, :emp, :cant, 'Salida', :razon)
            ");
            $stmtK->execute([
                ':id'    => $idRep,
                ':emp'   => $idEmp,
                ':cant'  => (string) $cant,
                ':razon' => $razon,
            ]);

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            if ($e instanceof ExcepcionApi) throw $e;
            throw ExcepcionApi::errorInterno('Error al procesar la salida de repuesto.');
        }

        return $this->obtenerRepuestoPorId($idRep);
    }

    private function obtenerHistorialRepuestos(): array
    {
        $stmt = $this->conn->prepare("
            SELECT k.*, r.nombre AS nombre_repuesto, e.nombre AS nombres, e.apellido AS apellidos
            FROM inventario_repuestos k
            INNER JOIN repuestos_vehiculos r ON k.id_repuesto = r.id_repuesto
            LEFT JOIN dirpoles_security.empleado e ON k.id_empleado = e.id_empleado
            ORDER BY k.id_inventario DESC
            LIMIT 100
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== 5. ASIGNACIONES ====================

    private function listarAsignaciones(): array
    {
        $stmt = $this->conn->prepare("
            SELECT a.*, r.nombre_ruta, v.placa, v.modelo,
                   e.nombre AS nombres_chofer, e.apellido AS apellidos_chofer, e.cedula AS cedula_chofer
            FROM asignaciones_rutas a
            INNER JOIN rutas r ON a.id_ruta = r.id_ruta
            INNER JOIN vehiculos v ON a.id_vehiculo = v.id_vehiculo
            LEFT JOIN dirpoles_security.empleado e ON a.id_empleado = e.id_empleado
            ORDER BY a.id_asignacion DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerOpcionesAsignacion(): array
    {
        // Rutas activas
        $stmtR = $this->conn->prepare("SELECT id_ruta, nombre_ruta, tipo_ruta FROM rutas WHERE estatus = 'Activa' ORDER BY nombre_ruta ASC");
        $stmtR->execute();
        $rutas = $stmtR->fetchAll(PDO::FETCH_ASSOC);

        // Vehículos activos (no inactivos ni en mantenimiento)
        $stmtV = $this->conn->prepare("SELECT id_vehiculo, placa, modelo, tipo FROM vehiculos WHERE estado = 'Activo' ORDER BY placa ASC");
        $stmtV->execute();
        $vehiculos = $stmtV->fetchAll(PDO::FETCH_ASSOC);

        // Choferes activos: SOLO empleados con tipo_empleado 'Chofer'
        // (los choferes no usan el sistema, pero son los únicos asignables).
        $stmtE = $this->conn->prepare("
            SELECT e.id_empleado, e.tipo_cedula, e.cedula, e.nombre AS nombres, e.apellido AS apellidos
            FROM dirpoles_security.empleado e
            INNER JOIN dirpoles_security.tipo_empleado t ON e.id_tipo_empleado = t.id_tipo_emp
            WHERE e.estatus = 1 AND LOWER(t.tipo) = 'chofer'
            ORDER BY e.nombre ASC
        ");
        $stmtE->execute();
        $empleados = $stmtE->fetchAll(PDO::FETCH_ASSOC);

        return [
            'rutas'     => $rutas,
            'vehiculos' => $vehiculos,
            'empleados' => $empleados,
        ];
    }

    /**
     * Regla del usuario: solo un empleado con tipo_empleado 'Chofer' (y
     * activo) puede ser asignado como chofer de una ruta.
     */
    private function validarChofer(int $idEmp): void
    {
        $stmt = $this->conn->prepare("
            SELECT 1
            FROM dirpoles_security.empleado e
            INNER JOIN dirpoles_security.tipo_empleado t ON e.id_tipo_empleado = t.id_tipo_emp
            WHERE e.id_empleado = :id AND e.estatus = 1 AND LOWER(t.tipo) = 'chofer'
            LIMIT 1
        ");
        $stmt->execute([':id' => $idEmp]);
        if (!$stmt->fetchColumn()) {
            throw ExcepcionApi::validacion('El empleado seleccionado no es un chofer activo.');
        }
    }

    private function crearAsignacion(): array
    {
        $idRuta = (int) ($this->__get('id_ruta') ?? 0);
        $idVeh  = (int) ($this->__get('id_vehiculo') ?? 0);
        $idEmp  = (int) ($this->__get('id_empleado') ?? 0);
        $fecha  = $this->__get('fecha_asignacion') ?? date('Y-m-d');
        $est    = $this->__get('estatus_asignacion') ?? 'Activa';

        if ($idRuta <= 0 || $idVeh <= 0 || $idEmp <= 0) {
            throw ExcepcionApi::validacion('Debes seleccionar una ruta, un vehículo y un chofer válidos.');
        }
        $this->validarChofer($idEmp);

        // Regla del usuario: un vehículo o un chofer no pueden tener dos
        // asignaciones 'Activa' el MISMO día (varias activas en días
        // distintos sí están permitidas).
        if ($est === 'Activa') {
            $this->verificarSolapamientoDia($idVeh, $idEmp, $fecha);
        }

        $stmt = $this->conn->prepare("
            INSERT INTO asignaciones_rutas (id_ruta, id_vehiculo, id_empleado, fecha_asignacion, estatus)
            VALUES (:r, :v, :e, :f, :est)
        ");
        $stmt->execute([
            ':r'   => $idRuta,
            ':v'   => $idVeh,
            ':e'   => $idEmp,
            ':f'   => $fecha,
            ':est' => $est,
        ]);

        $id = (int) $this->conn->lastInsertId();
        return $this->obtenerAsignacionPorId($id);
    }

    private function obtenerAsignacion(): array
    {
        $id = (int) ($this->__get('id_asignacion') ?? 0);
        return $this->obtenerAsignacionPorId($id);
    }

    private function obtenerAsignacionPorId(int $id): array
    {
        $stmt = $this->conn->prepare("
            SELECT a.*, r.nombre_ruta, v.placa, v.modelo,
                   e.nombre AS nombres_chofer, e.apellido AS apellidos_chofer, e.cedula AS cedula_chofer
            FROM asignaciones_rutas a
            INNER JOIN rutas r ON a.id_ruta = r.id_ruta
            INNER JOIN vehiculos v ON a.id_vehiculo = v.id_vehiculo
            LEFT JOIN dirpoles_security.empleado e ON a.id_empleado = e.id_empleado
            WHERE a.id_asignacion = :id
        ");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            throw ExcepcionApi::noEncontrado('La asignación solicitada no existe.');
        }
        return $res;
    }

    /**
     * Regla del usuario: un vehículo O un chofer no pueden tener dos
     * asignaciones 'Activa' con la MISMA fecha (varias activas en días
     * distintos sí están permitidas). $excluirId evita el choque consigo
     * mismo al editar.
     */
    private function verificarSolapamientoDia(int $idVeh, int $idEmp, string $fecha, int $excluirId = 0): void
    {
        $stmt = $this->conn->prepare("
            SELECT id_vehiculo, id_empleado
            FROM asignaciones_rutas
            WHERE fecha_asignacion = :f
              AND estatus = 'Activa'
              AND id_asignacion <> :x
              AND (id_vehiculo = :v OR id_empleado = :e)
            LIMIT 1
        ");
        $stmt->execute([':f' => $fecha, ':x' => $excluirId, ':v' => $idVeh, ':e' => $idEmp]);
        $conflicto = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$conflicto) {
            return;
        }

        if ((int) $conflicto['id_vehiculo'] === $idVeh) {
            throw ExcepcionApi::yaExiste("El vehículo ya tiene una asignación activa registrada para el día {$fecha}.");
        }
        throw ExcepcionApi::yaExiste("El chofer ya tiene una asignación activa registrada para el día {$fecha}.");
    }

    private function actualizarAsignacion(): array
    {
        $id = (int) ($this->__get('id_asignacion') ?? 0);
        $this->obtenerAsignacionPorId($id);

        $idRuta = (int) ($this->__get('id_ruta') ?? 0);
        $idVeh  = (int) ($this->__get('id_vehiculo') ?? 0);
        $idEmp  = (int) ($this->__get('id_empleado') ?? 0);
        $fecha  = $this->__get('fecha_asignacion') ?? date('Y-m-d');
        $est    = $this->__get('estatus_asignacion') ?? 'Activa';

        if ($idRuta <= 0 || $idVeh <= 0 || $idEmp <= 0) {
            throw ExcepcionApi::validacion('Debes seleccionar una ruta, un vehículo y un chofer válidos.');
        }
        $this->validarChofer($idEmp);

        // Misma regla de solapamiento por día, excluyendo esta asignación.
        if ($est === 'Activa') {
            $this->verificarSolapamientoDia($idVeh, $idEmp, $fecha, $id);
        }

        $stmt = $this->conn->prepare("
            UPDATE asignaciones_rutas SET
                id_ruta = :r,
                id_vehiculo = :v,
                id_empleado = :e,
                fecha_asignacion = :f,
                estatus = :est
            WHERE id_asignacion = :id
        ");
        $stmt->execute([
            ':r'   => $idRuta,
            ':v'   => $idVeh,
            ':e'   => $idEmp,
            ':f'   => $fecha,
            ':est' => $est,
            ':id'  => $id,
        ]);

        return $this->obtenerAsignacionPorId($id);
    }

    private function eliminarAsignacion(): array
    {
        $id = (int) ($this->__get('id_asignacion') ?? 0);
        $asig = $this->obtenerAsignacionPorId($id);

        $stmt = $this->conn->prepare("DELETE FROM asignaciones_rutas WHERE id_asignacion = :id");
        $stmt->execute([':id' => $id]);

        return ['mensaje' => 'Asignación eliminada correctamente.', 'asignacion' => $asig];
    }

    // ==================== 6. MANTENIMIENTOS ====================

    private function listarMantenimientos(): array
    {
        $stmt = $this->conn->prepare("
            SELECT m.*, v.placa, v.modelo, v.tipo AS tipo_vehiculo
            FROM mantenimiento_vehiculos m
            INNER JOIN vehiculos v ON m.id_vehiculo = v.id_vehiculo
            ORDER BY m.id_mantenimiento DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
        $stmt->execute();
        $mantenimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$mantenimientos) {
            return $mantenimientos;
        }

        // Repuestos consumidos de UNA sola query (sin N+1 por fila).
        $ids = array_map('intval', array_column($mantenimientos, 'id_mantenimiento'));
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $stmtR = $this->conn->prepare("
            SELECT rm.id_mantenimiento, rm.cantidad, r.nombre AS nombre_repuesto
            FROM repuestos_mantenimiento rm
            INNER JOIN repuestos_vehiculos r ON rm.id_repuesto = r.id_repuesto
            WHERE rm.id_mantenimiento IN ({$marcas})
        ");
        $stmtR->execute($ids);

        $porMantenimiento = [];
        foreach ($stmtR->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $porMantenimiento[(int) $fila['id_mantenimiento']][] = [
                'cantidad'         => $fila['cantidad'],
                'nombre_repuesto'  => $fila['nombre_repuesto'],
            ];
        }
        foreach ($mantenimientos as &$m) {
            $m['repuestos'] = $porMantenimiento[(int) $m['id_mantenimiento']] ?? [];
        }

        return $mantenimientos;
    }

    private function crearMantenimiento(): array
    {
        $idVeh = (int) ($this->__get('id_vehiculo') ?? 0);
        $tipo  = $this->__get('tipo_mantenimiento');
        $fecha = $this->__get('fecha_mantenimiento');
        $desc  = trim((string) ($this->__get('descripcion_mantenimiento') ?? ''));
        $repuestosUsados = $this->__get('repuestos_usados') ?? []; // array de ['id_repuesto' => X, 'cantidad' => Y]

        if ($idVeh <= 0) {
            throw ExcepcionApi::validacion('Debes seleccionar un vehículo para registrar el mantenimiento.');
        }

        $this->conn->beginTransaction();
        try {
            // 1. Insertar Mantenimiento
            $stmtM = $this->conn->prepare("
                INSERT INTO mantenimiento_vehiculos (id_vehiculo, tipo, fecha, descripcion)
                VALUES (:v, :t, :f, :d)
            ");
            $stmtM->execute([
                ':v' => $idVeh,
                ':t' => $tipo,
                ':f' => $fecha,
                ':d' => $desc ?: null,
            ]);
            $idMant = (int) $this->conn->lastInsertId();

            // 2. Cambiar estado del vehículo a 'Mantenimiento'
            $stmtV = $this->conn->prepare("UPDATE vehiculos SET estado = 'Mantenimiento' WHERE id_vehiculo = :v");
            $stmtV->execute([':v' => $idVeh]);

            // 3. Procesar repuestos consumidos si existen
            if (is_array($repuestosUsados) && count($repuestosUsados) > 0) {
                $idEmp = $this->idEmpleadoSesion();

                foreach ($repuestosUsados as $item) {
                    $idRep = (int) ($item['id_repuesto'] ?? 0);
                    $cant  = (int) ($item['cantidad'] ?? 0);

                    if ($idRep > 0 && $cant > 0) {
                        // FOR UPDATE sobre el repuesto
                        $stmtSel = $this->conn->prepare("SELECT cantidad, nombre FROM repuestos_vehiculos WHERE id_repuesto = :id FOR UPDATE");
                        $stmtSel->execute([':id' => $idRep]);
                        $repInfo = $stmtSel->fetch(PDO::FETCH_ASSOC);

                        if (!$repInfo) {
                            throw ExcepcionApi::noEncontrado("El repuesto ID {$idRep} no existe.");
                        }

                        $stockActual = (int) $repInfo['cantidad'];
                        if ($stockActual < $cant) {
                            throw ExcepcionApi::validacion("Stock insuficiente de '{$repInfo['nombre']}'. Disponible: {$stockActual}, Requerido: {$cant}.");
                        }

                        // Restar stock
                        $nuevoStock = $stockActual - $cant;
                        $nuevoEstatus = $nuevoStock > 0 ? 'Disponible' : 'Agotado';
                        $stmtUp = $this->conn->prepare("UPDATE repuestos_vehiculos SET cantidad = :c, estatus = :e WHERE id_repuesto = :id");
                        $stmtUp->execute([':c' => $nuevoStock, ':e' => $nuevoEstatus, ':id' => $idRep]);

                        // Enlazar repuesto con el mantenimiento
                        $stmtRM = $this->conn->prepare("
                            INSERT INTO repuestos_mantenimiento (id_mantenimiento, id_repuesto, cantidad)
                            VALUES (:m, :r, :c)
                        ");
                        $stmtRM->execute([':m' => $idMant, ':r' => $idRep, ':c' => (string) $cant]);

                        // Registrar movimiento de Salida por Mantenimiento en Kardex
                        $stmtK = $this->conn->prepare("
                            INSERT INTO inventario_repuestos (id_repuesto, id_empleado, cantidad, tipo_movimiento, razon_movimiento)
                            VALUES (:r, :emp, :cant, 'Salida', :razon)
                        ");
                        $stmtK->execute([
                            ':r'     => $idRep,
                            ':emp'   => $idEmp,
                            ':cant'  => (string) $cant,
                            ':razon' => "Consumo en mantenimiento ID {$idMant}",
                        ]);
                    }
                }
            }

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            if ($e instanceof ExcepcionApi) throw $e;
            throw ExcepcionApi::errorInterno('Error al registrar el mantenimiento.');
        }

        return $this->obtenerMantenimientoPorId($idMant);
    }

    private function obtenerMantenimiento(): array
    {
        $id = (int) ($this->__get('id_mantenimiento') ?? 0);
        return $this->obtenerMantenimientoPorId($id);
    }

    private function obtenerMantenimientoPorId(int $id): array
    {
        $stmt = $this->conn->prepare("
            SELECT m.*, v.placa, v.modelo, v.tipo AS tipo_vehiculo
            FROM mantenimiento_vehiculos m
            INNER JOIN vehiculos v ON m.id_vehiculo = v.id_vehiculo
            WHERE m.id_mantenimiento = :id
        ");
        $stmt->execute([':id' => $id]);
        $mant = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$mant) {
            throw ExcepcionApi::noEncontrado('El mantenimiento solicitado no existe.');
        }

        $stmtR = $this->conn->prepare("
            SELECT rm.cantidad, r.nombre AS nombre_repuesto
            FROM repuestos_mantenimiento rm
            INNER JOIN repuestos_vehiculos r ON rm.id_repuesto = r.id_repuesto
            WHERE rm.id_mantenimiento = :id
        ");
        $stmtR->execute([':id' => $id]);
        $mant['repuestos'] = $stmtR->fetchAll(PDO::FETCH_ASSOC);

        return $mant;
    }

    private function eliminarMantenimiento(): array
    {
        $id = (int) ($this->__get('id_mantenimiento') ?? 0);
        $mant = $this->obtenerMantenimientoPorId($id);

        $this->conn->beginTransaction();
        try {
            // Borrar de repuestos_mantenimiento
            $stmt1 = $this->conn->prepare("DELETE FROM repuestos_mantenimiento WHERE id_mantenimiento = :id");
            $stmt1->execute([':id' => $id]);

            // Borrar mantenimiento
            $stmt2 = $this->conn->prepare("DELETE FROM mantenimiento_vehiculos WHERE id_mantenimiento = :id");
            $stmt2->execute([':id' => $id]);

            // Reactivar el vehículo SOLO si ya no le quedan mantenimientos
            // (si queda alguno, sigue en 'Mantenimiento').
            $stmtRestan = $this->conn->prepare("SELECT COUNT(*) FROM mantenimiento_vehiculos WHERE id_vehiculo = :v");
            $stmtRestan->execute([':v' => $mant['id_vehiculo']]);
            if ((int) $stmtRestan->fetchColumn() === 0) {
                $stmtV = $this->conn->prepare("UPDATE vehiculos SET estado = 'Activo' WHERE id_vehiculo = :v AND estado = 'Mantenimiento'");
                $stmtV->execute([':v' => $mant['id_vehiculo']]);
            }

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw ExcepcionApi::errorInterno('Error al eliminar el mantenimiento.');
        }

        return ['mensaje' => 'Mantenimiento eliminado correctamente.', 'mantenimiento' => $mant];
    }
}
