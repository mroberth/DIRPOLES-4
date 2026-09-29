<?php
// app/Controllers/mobiliarioController.php
// Módulo Mobiliario (id_modulo 12 = 'Mobiliario').
// Solo funciones: Autorizacion primera, Bitacora en cada escritura,
// respuestas SIEMPRE por Respuesta. El nombre del módulo para RBAC es
// 'mobiliario' (Autorizacion resuelve contra modulo.nombre).
// Tres sub-flujos: mobiliario, equipos y fichas técnicas.

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Core\Respuesta;
use App\Models\MobiliarioModel;

// ==================== PUERTA HTML ====================

/** Página: hub de creación con las pestañas Mobiliario / Equipo / Ficha. */
function showCrearMobiliario(): void
{
    Autorizacion::verificar('mobiliario', 'crear');
    require_once BASE_PATH . '/app/Views/mobiliario/crear.php';
}

/** Página: consulta con pestañas Mobiliario / Equipos / Fichas + historial. */
function showConsultarMobiliario(): void
{
    Autorizacion::verificar('mobiliario', 'leer');
    require_once BASE_PATH . '/app/Views/mobiliario/consultar.php';
}

// ==================== PUERTA JSON (API) ====================

/** API: catálogos (tipos, servicios y empleados activos). */
function apiCatalogosMobiliario(): void
{
    Autorizacion::verificar('mobiliario', 'leer');

    $modelo = new MobiliarioModel();
    Respuesta::exito($modelo->manejarAccion('catalogos'));
}

/** API: lista de mobiliario (DataTable de la pestaña Mobiliario). */
function apiListarMobiliario(): void
{
    Autorizacion::verificar('mobiliario', 'leer');

    $modelo = new MobiliarioModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('listar_mobiliario'));
}

/** API: lista de equipos (DataTable de la pestaña Equipos). */
function apiListarEquipos(): void
{
    Autorizacion::verificar('mobiliario', 'leer');

    $modelo = new MobiliarioModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('listar_equipos'));
}

/** API: lista de fichas técnicas (DataTable de la pestaña Fichas). */
function apiListarFichas(): void
{
    Autorizacion::verificar('mobiliario', 'leer');

    $modelo = new MobiliarioModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('listar_fichas'));
}

/** API: kardex de movimientos (alta, reubicación, modificación y baja). */
function apiHistorialMobiliario(): void
{
    Autorizacion::verificar('mobiliario', 'leer');

    $modelo = new MobiliarioModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    Respuesta::exito($modelo->manejarAccion('historial'));
}

/**
 * API: fila completa para el modal de edición.
 * Ruta: api/mobiliario/obtener/{tipo}/{id} con tipo en mobiliario|equipo|ficha.
 */
function apiObtenerMobiliario(): void
{
    Autorizacion::verificar('mobiliario', 'leer');

    $tipo = trim((string) ($_GET['tipo'] ?? ''));
    if (!in_array($tipo, ['mobiliario', 'equipo', 'ficha'], true)) {
        Respuesta::error(ExcepcionApi::validacion('El tipo de registro no es válido.'));
    }

    $modelo = new MobiliarioModel();
    $modelo->__set('id_' . $tipo, $_GET['id'] ?? 0);
    Respuesta::exito($modelo->manejarAccion('obtener_' . $tipo));
}

/** API: tarjetas de resumen del módulo. */
function apiMobiliarioStats(): void
{
    Autorizacion::verificar('mobiliario', 'leer');

    $modelo = new MobiliarioModel();
    Respuesta::exito($modelo->manejarAccion('stats'));
}

/**
 * API: ítems libres para asignar a una ficha.
 * Query: id_excluir = ficha propia en edición (0 al crear).
 */
function apiItemsDisponibles(): void
{
    Autorizacion::verificar('mobiliario', 'leer');

    $modelo = new MobiliarioModel();
    $modelo->__set('id_excluir', $_GET['id_excluir'] ?? 0);
    Respuesta::exito($modelo->manejarAccion('items_disponibles'));
}

/** API: crear mobiliario (pieza a pieza; alta + movimiento en el kardex). */
function apiCrearMobiliario(): void
{
    Autorizacion::verificar('mobiliario', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('id_tipo_mobiliario',  $entrada['id_tipo_mobiliario'] ?? 0);
    $modelo->__set('id_servicios',        $entrada['id_servicios'] ?? 0);
    $modelo->__set('cantidad',            $entrada['cantidad'] ?? 0);
    $modelo->__set('estado',              $entrada['estado'] ?? '');
    $modelo->__set('marca',               $entrada['marca'] ?? '');
    $modelo->__set('modelo',              $entrada['modelo'] ?? '');
    $modelo->__set('color',               $entrada['color'] ?? '');
    $modelo->__set('fecha_adquisicion',   $entrada['fecha_adquisicion'] ?? '');
    $modelo->__set('descripcion',         $entrada['descripcion'] ?? '');
    $modelo->__set('observaciones',       $entrada['observaciones'] ?? '');
    $modelo->__set('id_empleado',         (int) ($_SESSION['id_empleado'] ?? 0));

    $nuevo = $modelo->manejarAccion('crear_mobiliario');

    Bitacora::registrar(
        'Mobiliario',
        'Registro',
        'Registró mobiliario: ' . $nuevo['resumen'] . '.'
    );

    Respuesta::exito($nuevo, 201);
}

/** API: crear equipo (serial único y obligatorio). */
function apiCrearEquipo(): void
{
    Autorizacion::verificar('mobiliario', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('id_tipo_equipo',     $entrada['id_tipo_equipo'] ?? 0);
    $modelo->__set('id_servicios',       $entrada['id_servicios'] ?? 0);
    $modelo->__set('marca',              $entrada['marca'] ?? '');
    $modelo->__set('modelo',             $entrada['modelo'] ?? '');
    $modelo->__set('serial',             $entrada['serial'] ?? '');
    $modelo->__set('color',              $entrada['color'] ?? '');
    $modelo->__set('estado',             $entrada['estado'] ?? '');
    $modelo->__set('fecha_adquisicion',  $entrada['fecha_adquisicion'] ?? '');
    $modelo->__set('descripcion',        $entrada['descripcion'] ?? '');
    $modelo->__set('observaciones',      $entrada['observaciones'] ?? '');
    $modelo->__set('id_empleado',        (int) ($_SESSION['id_empleado'] ?? 0));

    $nuevo = $modelo->manejarAccion('crear_equipo');

    Bitacora::registrar(
        'Mobiliario',
        'Registro',
        'Registró un equipo: ' . $nuevo['resumen'] . '.'
    );

    Respuesta::exito($nuevo, 201);
}

/** API: crear ficha técnica (una activa por empleado, con sus ítems). */
function apiCrearFicha(): void
{
    Autorizacion::verificar('mobiliario', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('nombre_ficha',            $entrada['nombre_ficha'] ?? '');
    $modelo->__set('id_servicio',             $entrada['id_servicio'] ?? 0);
    $modelo->__set('id_empleado_responsable', $entrada['id_empleado_responsable'] ?? 0);
    $modelo->__set('descripcion',             $entrada['descripcion'] ?? '');
    $modelo->__set('detalles_mobiliario',     $entrada['detalles_mobiliario'] ?? []);
    $modelo->__set('detalles_equipo',         $entrada['detalles_equipo'] ?? []);
    $modelo->__set('id_empleado',             (int) ($_SESSION['id_empleado'] ?? 0));

    $nuevo = $modelo->manejarAccion('crear_ficha');

    Bitacora::registrar(
        'Mobiliario',
        'Registro',
        'Registró la ficha técnica ' . $nuevo['resumen'] . '.'
    );

    Respuesta::exito($nuevo, 201);
}

/** API: editar mobiliario (nunca estatus; cantidad >= lo asignado). */
function apiActualizarMobiliario(): void
{
    Autorizacion::verificar('mobiliario', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('id_mobiliario',         $entrada['id_mobiliario'] ?? 0);
    $modelo->__set('id_tipo_mobiliario',    $entrada['id_tipo_mobiliario'] ?? 0);
    $modelo->__set('id_servicios',          $entrada['id_servicios'] ?? 0);
    $modelo->__set('cantidad',              $entrada['cantidad'] ?? 0);
    $modelo->__set('estado',                $entrada['estado'] ?? '');
    $modelo->__set('marca',                 $entrada['marca'] ?? '');
    $modelo->__set('modelo',                $entrada['modelo'] ?? '');
    $modelo->__set('color',                 $entrada['color'] ?? '');
    $modelo->__set('fecha_adquisicion',     $entrada['fecha_adquisicion'] ?? '');
    $modelo->__set('descripcion',           $entrada['descripcion'] ?? '');
    $modelo->__set('observaciones',         $entrada['observaciones'] ?? '');
    $modelo->__set('id_empleado',           (int) ($_SESSION['id_empleado'] ?? 0));

    $actualizado = $modelo->manejarAccion('actualizar_mobiliario');

    Bitacora::registrar(
        'Mobiliario',
        'Actualización',
        'Actualizó el mobiliario: ' . $actualizado['resumen'] . '.'
    );

    Respuesta::exito($actualizado);
}

/** API: editar equipo (serial único con id_excluir; nunca estatus). */
function apiActualizarEquipo(): void
{
    Autorizacion::verificar('mobiliario', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('id_equipo',             $entrada['id_equipo'] ?? 0);
    $modelo->__set('id_tipo_equipo',        $entrada['id_tipo_equipo'] ?? 0);
    $modelo->__set('id_servicios',          $entrada['id_servicios'] ?? 0);
    $modelo->__set('marca',                 $entrada['marca'] ?? '');
    $modelo->__set('modelo',                $entrada['modelo'] ?? '');
    $modelo->__set('serial',                $entrada['serial'] ?? '');
    $modelo->__set('color',                 $entrada['color'] ?? '');
    $modelo->__set('estado',                $entrada['estado'] ?? '');
    $modelo->__set('fecha_adquisicion',     $entrada['fecha_adquisicion'] ?? '');
    $modelo->__set('descripcion',           $entrada['descripcion'] ?? '');
    $modelo->__set('observaciones',         $entrada['observaciones'] ?? '');
    $modelo->__set('id_empleado',           (int) ($_SESSION['id_empleado'] ?? 0));

    $actualizado = $modelo->manejarAccion('actualizar_equipo');

    Bitacora::registrar(
        'Mobiliario',
        'Actualización',
        'Actualizó el equipo: ' . $actualizado['resumen'] . '.'
    );

    Respuesta::exito($actualizado);
}

/** API: editar ficha (cabecera + reemplazo de sus ítems). */
function apiActualizarFicha(): void
{
    Autorizacion::verificar('mobiliario', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('id_ficha',                  $entrada['id_ficha'] ?? 0);
    $modelo->__set('nombre_ficha',              $entrada['nombre_ficha'] ?? '');
    $modelo->__set('id_servicio',               $entrada['id_servicio'] ?? 0);
    $modelo->__set('id_empleado_responsable',   $entrada['id_empleado_responsable'] ?? 0);
    $modelo->__set('descripcion',               $entrada['descripcion'] ?? '');
    $modelo->__set('detalles_mobiliario',       $entrada['detalles_mobiliario'] ?? []);
    $modelo->__set('detalles_equipo',           $entrada['detalles_equipo'] ?? []);
    $modelo->__set('id_empleado',               (int) ($_SESSION['id_empleado'] ?? 0));

    $actualizado = $modelo->manejarAccion('actualizar_ficha');

    Bitacora::registrar(
        'Mobiliario',
        'Actualización',
        'Actualizó la ficha técnica ' . $actualizado['resumen'] . '.'
    );

    Respuesta::exito($actualizado);
}

/** API: eliminar mobiliario (bloqueado si está en detalle_ficha_* o inventario_mob). */
function apiEliminarMobiliario(): void
{
    Autorizacion::verificar('mobiliario', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('id_mobiliario', $entrada['id_mobiliario'] ?? 0);

    // Se consulta antes de borrar para auditar con el nombre real.
    $fila = $modelo->manejarAccion('obtener_mobiliario');
    $modelo->manejarAccion('eliminar_mobiliario');

    Bitacora::registrar(
        'Mobiliario',
        'Eliminación',
        'Eliminó el mobiliario "' . $fila['tipo_mobiliario'] . ' #' . $fila['id_mobiliario']
        . '" (' . $fila['cantidad'] . ' unidades en ' . $fila['servicio'] . ').'
    );

    Respuesta::exito(['eliminado' => true]);
}

/** API: eliminar equipo (bloqueado si está en detalle_ficha_equipo). */
function apiEliminarEquipo(): void
{
    Autorizacion::verificar('mobiliario', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('id_equipo', $entrada['id_equipo'] ?? 0);

    $fila = $modelo->manejarAccion('obtener_equipo');
    $modelo->manejarAccion('eliminar_equipo');

    Bitacora::registrar(
        'Mobiliario',
        'Eliminación',
        'Eliminó el equipo "' . $fila['serial'] . '" (' . $fila['tipo_equipo']
        . ' en ' . $fila['servicio'] . ').'
    );

    Respuesta::exito(['eliminado' => true]);
}

/** API: eliminar ficha técnica (borra sus detalles primero, en transacción). */
function apiEliminarFicha(): void
{
    Autorizacion::verificar('mobiliario', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('id_ficha', $entrada['id_ficha'] ?? 0);

    $fila = $modelo->manejarAccion('obtener_ficha');
    $modelo->manejarAccion('eliminar_ficha');

    Bitacora::registrar(
        'Mobiliario',
        'Eliminación',
        'Eliminó la ficha técnica "' . $fila['nombre_ficha'] . '" de '
        . $fila['responsable'] . '.'
    );

    Respuesta::exito(['eliminado' => true]);
}

/** API: reubicar un ítem (cambia ubicación y registra 'reubicacion'). */
function apiReubicarItem(): void
{
    Autorizacion::verificar('mobiliario', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    // tipo_item se valida primero: si es inválido el modelo lanza 400 y no se
    // intenta leer la llave primaria equivocada.
    $modelo->__set('tipo_item',    $entrada['tipo_item'] ?? '');
    $llave                        = ($entrada['tipo_item'] ?? '') === 'equipo' ? 'id_equipo' : 'id_mobiliario';
    $modelo->__set($llave,        $entrada[$llave] ?? 0);
    $modelo->__set('id_servicios', $entrada['id_servicios'] ?? 0);
    $modelo->__set('id_empleado',  (int) ($_SESSION['id_empleado'] ?? 0));

    $resultado = $modelo->manejarAccion('reubicar');

    Bitacora::registrar(
        'Mobiliario',
        'Actualización',
        'Reubicó ' . $resultado['etiqueta'] . ' de "' . $resultado['de']
        . '" a "' . $resultado['a'] . '".'
    );

    Respuesta::exito($resultado);
}

/** API: baja lógica (estatus 'Inactivo'; bloqueada si está en ficha activa). */
function apiBajaItem(): void
{
    Autorizacion::verificar('mobiliario', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('tipo_item',    $entrada['tipo_item'] ?? '');
    $llave                        = ($entrada['tipo_item'] ?? '') === 'equipo' ? 'id_equipo' : 'id_mobiliario';
    $modelo->__set($llave,        $entrada[$llave] ?? 0);
    $modelo->__set('id_empleado',  (int) ($_SESSION['id_empleado'] ?? 0));

    $resultado = $modelo->manejarAccion('baja');

    Bitacora::registrar(
        'Mobiliario',
        'Actualización',
        'Registró la baja lógica de ' . $resultado['etiqueta'] . '.'
    );

    Respuesta::exito($resultado);
}

/**
 * API: validar serial duplicado en vivo.
 * Body: {serial, id_excluir?}
 */
function apiValidarSerial(): void
{
    Autorizacion::verificar('mobiliario', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('serial', $entrada['serial'] ?? '');
    if (!empty($entrada['id_excluir'])) {
        $modelo->__set('id_excluir', $entrada['id_excluir']);
    }

    Respuesta::exito(['existe' => $modelo->manejarAccion('existe_serial')]);
}

/**
 * API: validar que el empleado no tenga ya una ficha activa.
 * Body: {id_empleado_responsable, id_excluir?}
 */
function apiValidarFichaEmpleado(): void
{
    Autorizacion::verificar('mobiliario', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new MobiliarioModel();
    $modelo->__set('id_empleado_responsable', $entrada['id_empleado_responsable'] ?? 0);
    if (!empty($entrada['id_excluir'])) {
        $modelo->__set('id_excluir', $entrada['id_excluir']);
    }

    Respuesta::exito(['existe' => $modelo->manejarAccion('existe_ficha_empleado')]);
}
