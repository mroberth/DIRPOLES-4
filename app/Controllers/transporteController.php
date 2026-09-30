<?php
/**
 * app/Controllers/transporteController.php
 * ---------------------------------------------------------------
 * Controlador procedimental para el módulo de Transporte (id_modulo = 13).
 */

use App\Models\TransporteModel;
use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;

// ==================== PUERTA HTML ====================

function showCrearTransporte(): void
{
    Autorizacion::verificar('transporte', 'crear');
    require_once BASE_PATH . '/app/Views/transporte/crear.php';
}

function showConsultarTransporte(): void
{
    Autorizacion::verificar('transporte', 'leer');

    // La vista oculta los botones que el rol no puede usar (el backend
    // vuelve a verificar cada escritura).
    $puedeCrear    = Autorizacion::tiene('transporte', 'crear');
    $puedeEditar   = Autorizacion::tiene('transporte', 'editar');
    $puedeEliminar = Autorizacion::tiene('transporte', 'eliminar');

    require_once BASE_PATH . '/app/Views/transporte/consultar.php';
}

// ==================== PUERTA JSON (API) ====================

function apiStatsTransporte(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $modelo = new TransporteModel();
    Respuesta::exito($modelo->manejarAccion('stats'));
}

// ---------- RUTAS ----------

function apiListarRutas(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $modelo = new TransporteModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('listar_rutas'));
}

function apiCrearRuta(): void
{
    Autorizacion::verificar('transporte', 'crear');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('nombre_ruta',     $input['nombre_ruta'] ?? '');
    $modelo->__set('trayectoria',     $input['trayectoria'] ?? '');
    $modelo->__set('tipo_ruta',       $input['tipo_ruta'] ?? '');
    $modelo->__set('horario_salida',  $input['horario_salida'] ?? '');
    $modelo->__set('horario_llegada', $input['horario_llegada'] ?? '');
    $modelo->__set('punto_partida',   $input['punto_partida'] ?? '');
    $modelo->__set('punto_destino',   $input['punto_destino'] ?? '');
    $modelo->__set('estatus_ruta',    $input['estatus'] ?? '');

    $nueva = $modelo->manejarAccion('crear_ruta');

    Bitacora::registrar('Transporte', 'Registro', "Creó la ruta '{$nueva['nombre_ruta']}'");
    Respuesta::exito($nueva, 201);
}

function apiObtenerRuta(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $id = (int) ($_GET['id'] ?? 0);

    $modelo = new TransporteModel();
    $modelo->__set('id_ruta', $id);
    Respuesta::exito($modelo->manejarAccion('obtener_ruta'));
}

function apiActualizarRuta(): void
{
    Autorizacion::verificar('transporte', 'editar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_ruta',         (int) ($input['id_ruta'] ?? 0));
    $modelo->__set('nombre_ruta',     $input['nombre_ruta'] ?? '');
    $modelo->__set('trayectoria',     $input['trayectoria'] ?? '');
    $modelo->__set('tipo_ruta',       $input['tipo_ruta'] ?? '');
    $modelo->__set('horario_salida',  $input['horario_salida'] ?? '');
    $modelo->__set('horario_llegada', $input['horario_llegada'] ?? '');
    $modelo->__set('punto_partida',   $input['punto_partida'] ?? '');
    $modelo->__set('punto_destino',   $input['punto_destino'] ?? '');
    $modelo->__set('estatus_ruta',    $input['estatus'] ?? '');

    $act = $modelo->manejarAccion('actualizar_ruta');

    Bitacora::registrar('Transporte', 'Actualización', "Actualizó la ruta '{$act['nombre_ruta']}'");
    Respuesta::exito($act);
}

function apiEliminarRuta(): void
{
    Autorizacion::verificar('transporte', 'eliminar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_ruta', (int) ($input['id_ruta'] ?? 0));

    $res = $modelo->manejarAccion('eliminar_ruta');

    Bitacora::registrar('Transporte', 'Eliminación', "Eliminó la ruta '{$res['ruta']['nombre_ruta']}'");
    Respuesta::exito($res);
}

// ---------- VEHÍCULOS ----------

function apiListarVehiculos(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $modelo = new TransporteModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('listar_vehiculos'));
}

function apiCrearVehiculo(): void
{
    Autorizacion::verificar('transporte', 'crear');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('placa',             $input['placa'] ?? '');
    $modelo->__set('modelo',            $input['modelo'] ?? '');
    $modelo->__set('tipo_vehiculo',     $input['tipo'] ?? '');
    $modelo->__set('fecha_adquisicion', $input['fecha_adquisicion'] ?? '');
    $modelo->__set('estado_vehiculo',   $input['estado'] ?? '');

    $nuevo = $modelo->manejarAccion('crear_vehiculo');

    Bitacora::registrar('Transporte', 'Registro', "Creó el vehículo placa '{$nuevo['placa']}' ({$nuevo['tipo']})");
    Respuesta::exito($nuevo, 201);
}

function apiObtenerVehiculo(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $id = (int) ($_GET['id'] ?? 0);

    $modelo = new TransporteModel();
    $modelo->__set('id_vehiculo', $id);
    Respuesta::exito($modelo->manejarAccion('obtener_vehiculo'));
}

function apiActualizarVehiculo(): void
{
    Autorizacion::verificar('transporte', 'editar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_vehiculo',       (int) ($input['id_vehiculo'] ?? 0));
    $modelo->__set('placa',             $input['placa'] ?? '');
    $modelo->__set('modelo',            $input['modelo'] ?? '');
    $modelo->__set('tipo_vehiculo',     $input['tipo'] ?? '');
    $modelo->__set('fecha_adquisicion', $input['fecha_adquisicion'] ?? '');
    $modelo->__set('estado_vehiculo',   $input['estado'] ?? '');

    $act = $modelo->manejarAccion('actualizar_vehiculo');

    Bitacora::registrar('Transporte', 'Actualización', "Actualizó el vehículo placa '{$act['placa']}'");
    Respuesta::exito($act);
}

function apiEliminarVehiculo(): void
{
    Autorizacion::verificar('transporte', 'eliminar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_vehiculo', (int) ($input['id_vehiculo'] ?? 0));

    $res = $modelo->manejarAccion('eliminar_vehiculo');

    Bitacora::registrar('Transporte', 'Eliminación', "Eliminó el vehículo placa '{$res['vehiculo']['placa']}'");
    Respuesta::exito($res);
}

function apiValidarPlacaVehiculo(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('placa',      $input['placa'] ?? '');
    $modelo->__set('id_excluir', (int) ($input['id_excluir'] ?? 0));

    Respuesta::exito($modelo->manejarAccion('validar_placa'));
}

// ---------- PROVEEDORES ----------

function apiListarProveedores(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $modelo = new TransporteModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('listar_proveedores'));
}

function apiCrearProveedor(): void
{
    Autorizacion::verificar('transporte', 'crear');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('tipo_documento',     $input['tipo_documento'] ?? '');
    $modelo->__set('num_documento',      $input['num_documento'] ?? '');
    $modelo->__set('nombre_proveedor',   $input['nombre'] ?? '');
    $modelo->__set('telefono_proveedor', $input['telefono'] ?? '');
    $modelo->__set('correo_proveedor',   $input['correo'] ?? '');
    $modelo->__set('direccion_proveedor',$input['direccion'] ?? '');
    $modelo->__set('estatus_proveedor',  $input['estatus'] ?? '');

    $nuevo = $modelo->manejarAccion('crear_proveedor');

    Bitacora::registrar('Transporte', 'Registro', "Creó el proveedor '{$nuevo['nombre']}' ({$nuevo['tipo_documento']}-{$nuevo['num_documento']})");
    Respuesta::exito($nuevo, 201);
}

function apiObtenerProveedor(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $id = (int) ($_GET['id'] ?? 0);

    $modelo = new TransporteModel();
    $modelo->__set('id_proveedor', $id);
    Respuesta::exito($modelo->manejarAccion('obtener_proveedor'));
}

function apiActualizarProveedor(): void
{
    Autorizacion::verificar('transporte', 'editar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_proveedor',       (int) ($input['id_proveedor'] ?? 0));
    $modelo->__set('tipo_documento',     $input['tipo_documento'] ?? '');
    $modelo->__set('num_documento',      $input['num_documento'] ?? '');
    $modelo->__set('nombre_proveedor',   $input['nombre'] ?? '');
    $modelo->__set('telefono_proveedor', $input['telefono'] ?? '');
    $modelo->__set('correo_proveedor',   $input['correo'] ?? '');
    $modelo->__set('direccion_proveedor',$input['direccion'] ?? '');
    $modelo->__set('estatus_proveedor',  $input['estatus'] ?? '');

    $act = $modelo->manejarAccion('actualizar_proveedor');

    Bitacora::registrar('Transporte', 'Actualización', "Actualizó el proveedor '{$act['nombre']}'");
    Respuesta::exito($act);
}

function apiEliminarProveedor(): void
{
    Autorizacion::verificar('transporte', 'eliminar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_proveedor', (int) ($input['id_proveedor'] ?? 0));

    $res = $modelo->manejarAccion('eliminar_proveedor');

    Bitacora::registrar('Transporte', 'Eliminación', "Eliminó el proveedor '{$res['proveedor']['nombre']}'");
    Respuesta::exito($res);
}

function apiValidarDocumentoProveedor(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('tipo_documento', $input['tipo_documento'] ?? '');
    $modelo->__set('num_documento',  $input['num_documento'] ?? '');
    $modelo->__set('id_excluir',     (int) ($input['id_excluir'] ?? 0));

    Respuesta::exito($modelo->manejarAccion('validar_documento_proveedor'));
}

function apiValidarCorreoProveedor(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('correo_proveedor', $input['correo'] ?? '');
    $modelo->__set('id_excluir',       (int) ($input['id_excluir'] ?? 0));

    Respuesta::exito($modelo->manejarAccion('validar_correo_proveedor'));
}

function apiValidarTelefonoProveedor(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('telefono_proveedor', $input['telefono'] ?? '');
    $modelo->__set('id_excluir',         (int) ($input['id_excluir'] ?? 0));

    Respuesta::exito($modelo->manejarAccion('validar_telefono_proveedor'));
}

// ---------- REPUESTOS ----------

function apiListarRepuestos(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $modelo = new TransporteModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('listar_repuestos'));
}

function apiCrearRepuesto(): void
{
    Autorizacion::verificar('transporte', 'crear');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('nombre_repuesto',      $input['nombre'] ?? '');
    $modelo->__set('descripcion_repuesto', $input['descripcion'] ?? '');
    $modelo->__set('id_proveedor',         (int) ($input['id_proveedor'] ?? 0));

    $nuevo = $modelo->manejarAccion('crear_repuesto');

    Bitacora::registrar('Transporte', 'Registro', "Registró el repuesto '{$nuevo['nombre']}'");
    Respuesta::exito($nuevo, 201);
}

function apiObtenerRepuesto(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $id = (int) ($_GET['id'] ?? 0);

    $modelo = new TransporteModel();
    $modelo->__set('id_repuesto', $id);
    Respuesta::exito($modelo->manejarAccion('obtener_repuesto'));
}

function apiActualizarRepuesto(): void
{
    Autorizacion::verificar('transporte', 'editar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_repuesto',           (int) ($input['id_repuesto'] ?? 0));
    $modelo->__set('nombre_repuesto',      $input['nombre'] ?? '');
    $modelo->__set('descripcion_repuesto', $input['descripcion'] ?? '');
    $modelo->__set('id_proveedor',         (int) ($input['id_proveedor'] ?? 0));

    $act = $modelo->manejarAccion('actualizar_repuesto');

    Bitacora::registrar('Transporte', 'Actualización', "Actualizó el repuesto '{$act['nombre']}'");
    Respuesta::exito($act);
}

function apiEliminarRepuesto(): void
{
    Autorizacion::verificar('transporte', 'eliminar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_repuesto', (int) ($input['id_repuesto'] ?? 0));

    $res = $modelo->manejarAccion('eliminar_repuesto');

    Bitacora::registrar('Transporte', 'Eliminación', "Eliminó el repuesto '{$res['repuesto']['nombre']}'");
    Respuesta::exito($res);
}

function apiEntradaRepuesto(): void
{
    Autorizacion::verificar('transporte', 'crear');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_repuesto',       (int) ($input['id_repuesto'] ?? 0));
    $modelo->__set('cantidad_repuesto', (int) ($input['cantidad'] ?? 0));
    $modelo->__set('razon_movimiento',  $input['razon'] ?? '');

    $res = $modelo->manejarAccion('entrada_repuesto');

    Bitacora::registrar('Transporte', 'Actualización', "Entrada de {$input['cantidad']} unidades del repuesto '{$res['nombre']}'");
    Respuesta::exito($res);
}

function apiSalidaRepuesto(): void
{
    Autorizacion::verificar('transporte', 'crear');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_repuesto',       (int) ($input['id_repuesto'] ?? 0));
    $modelo->__set('cantidad_repuesto', (int) ($input['cantidad'] ?? 0));
    $modelo->__set('razon_movimiento',  $input['razon'] ?? '');

    $res = $modelo->manejarAccion('salida_repuesto');

    Bitacora::registrar('Transporte', 'Actualización', "Salida de {$input['cantidad']} unidades del repuesto '{$res['nombre']}'");
    Respuesta::exito($res);
}

function apiHistorialRepuestos(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $modelo = new TransporteModel();
    Respuesta::exito($modelo->manejarAccion('historial_repuestos'));
}

// ---------- ASIGNACIONES ----------

function apiListarAsignaciones(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $modelo = new TransporteModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('listar_asignaciones'));
}

function apiOpcionesAsignacion(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $modelo = new TransporteModel();
    Respuesta::exito($modelo->manejarAccion('opciones_asignacion'));
}

function apiCrearAsignacion(): void
{
    Autorizacion::verificar('transporte', 'crear');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_ruta',            (int) ($input['id_ruta'] ?? 0));
    $modelo->__set('id_vehiculo',        (int) ($input['id_vehiculo'] ?? 0));
    $modelo->__set('id_empleado',        (int) ($input['id_empleado'] ?? 0));
    $modelo->__set('fecha_asignacion',   $input['fecha_asignacion'] ?? '');
    $modelo->__set('estatus_asignacion', $input['estatus'] ?? '');

    $nueva = $modelo->manejarAccion('crear_asignacion');

    Bitacora::registrar('Transporte', 'Registro', "Asignó vehículo {$nueva['placa']} a ruta '{$nueva['nombre_ruta']}'");
    Respuesta::exito($nueva, 201);
}

function apiObtenerAsignacion(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $id = (int) ($_GET['id'] ?? 0);

    $modelo = new TransporteModel();
    $modelo->__set('id_asignacion', $id);
    Respuesta::exito($modelo->manejarAccion('obtener_asignacion'));
}

function apiActualizarAsignacion(): void
{
    Autorizacion::verificar('transporte', 'editar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_asignacion',      (int) ($input['id_asignacion'] ?? 0));
    $modelo->__set('id_ruta',            (int) ($input['id_ruta'] ?? 0));
    $modelo->__set('id_vehiculo',        (int) ($input['id_vehiculo'] ?? 0));
    $modelo->__set('id_empleado',        (int) ($input['id_empleado'] ?? 0));
    $modelo->__set('fecha_asignacion',   $input['fecha_asignacion'] ?? '');
    $modelo->__set('estatus_asignacion', $input['estatus'] ?? '');

    $act = $modelo->manejarAccion('actualizar_asignacion');

    Bitacora::registrar('Transporte', 'Actualización', "Actualizó asignación de vehículo {$act['placa']}");
    Respuesta::exito($act);
}

function apiEliminarAsignacion(): void
{
    Autorizacion::verificar('transporte', 'eliminar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_asignacion', (int) ($input['id_asignacion'] ?? 0));

    $res = $modelo->manejarAccion('eliminar_asignacion');

    Bitacora::registrar('Transporte', 'Eliminación', "Eliminó asignación de vehículo {$res['asignacion']['placa']}");
    Respuesta::exito($res);
}

// ---------- MANTENIMIENTOS ----------

function apiListarMantenimientos(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $modelo = new TransporteModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('listar_mantenimientos'));
}

function apiCrearMantenimiento(): void
{
    Autorizacion::verificar('transporte', 'crear');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_vehiculo',               (int) ($input['id_vehiculo'] ?? 0));
    $modelo->__set('tipo_mantenimiento',        $input['tipo'] ?? '');
    $modelo->__set('fecha_mantenimiento',       $input['fecha'] ?? '');
    $modelo->__set('descripcion_mantenimiento', $input['descripcion'] ?? '');
    $modelo->__set('repuestos_usados',           $input['repuestos'] ?? []);

    $nuevo = $modelo->manejarAccion('crear_mantenimiento');

    Bitacora::registrar('Transporte', 'Registro', "Registró mantenimiento {$nuevo['tipo']} al vehículo placa '{$nuevo['placa']}'");
    Respuesta::exito($nuevo, 201);
}

function apiObtenerMantenimiento(): void
{
    Autorizacion::verificar('transporte', 'leer');
    $id = (int) ($_GET['id'] ?? 0);

    $modelo = new TransporteModel();
    $modelo->__set('id_mantenimiento', $id);
    Respuesta::exito($modelo->manejarAccion('obtener_mantenimiento'));
}

function apiEliminarMantenimiento(): void
{
    Autorizacion::verificar('transporte', 'eliminar');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new TransporteModel();
    $modelo->__set('id_mantenimiento', (int) ($input['id_mantenimiento'] ?? 0));

    $res = $modelo->manejarAccion('eliminar_mantenimiento');

    Bitacora::registrar('Transporte', 'Eliminación', "Eliminó registro de mantenimiento del vehículo placa '{$res['mantenimiento']['placa']}'");
    Respuesta::exito($res);
}
