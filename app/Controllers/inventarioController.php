<?php
// app/Controllers/inventarioController.php
// Módulo Inventario Médico (id_modulo 9 = 'Inventario Medico').
// Solo funciones: Autorizacion primera, Bitacora en cada escritura,
// respuestas SIEMPRE por Respuesta. El nombre del módulo para RBAC es
// 'inventario medico' (Autorizacion resuelve contra modulo.nombre).

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\InventarioModel;

// ==================== PUERTA HTML ====================

/** Página: formulario de creación de insumo. */
function showCrearInsumo(): void
{
    Autorizacion::verificar('inventario medico', 'crear');
    require_once BASE_PATH . '/app/Views/inventario/crear.php';
}

/** Página: consulta de insumos (tabla + modales de entrada/salida/historial). */
function showConsultarInsumos(): void
{
    Autorizacion::verificar('inventario medico', 'leer');
    require_once BASE_PATH . '/app/Views/inventario/consultar.php';
}

// ==================== PUERTA JSON (API) ====================

/** API: catálogo de presentaciones de insumo (para el <select>). */
function apiPresentacionesInsumo(): void
{
    Autorizacion::verificar('inventario medico', 'leer');

    $modelo = new InventarioModel();
    Respuesta::exito($modelo->manejarAccion('presentaciones'));
}

/** API: lista de insumos (DataTable de consultar). */
function apiListarInsumos(): void
{
    Autorizacion::verificar('inventario medico', 'leer');

    $modelo = new InventarioModel();
    Respuesta::exito($modelo->manejarAccion('listar'));
}

/** API: fila completa de un insumo (abrir modal de edición). */
function apiObtenerInsumo(): void
{
    Autorizacion::verificar('inventario medico', 'leer');

    $modelo = new InventarioModel();
    $modelo->__set('id_insumo', $_GET['id'] ?? 0);
    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/** API: crear insumo (cantidad 0 y estatus 'Agotado' los pone el modelo). */
function apiCrearInsumo(): void
{
    Autorizacion::verificar('inventario medico', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new InventarioModel();
    $modelo->__set('nombre_insumo',     $entrada['nombre_insumo'] ?? '');
    $modelo->__set('tipo_insumo',       $entrada['tipo_insumo'] ?? '');
    $modelo->__set('id_presentacion',   $entrada['id_presentacion'] ?? 0);
    $modelo->__set('fecha_vencimiento', $entrada['fecha_vencimiento'] ?? '');
    $modelo->__set('descripcion',       $entrada['descripcion'] ?? '');
    $modelo->__set('id_empleado',       (int) ($_SESSION['id_empleado'] ?? 0));

    $nuevo = $modelo->manejarAccion('crear');

    Bitacora::registrar(
        'Inventario Medico',
        'Registro',
        'Registró el insumo "' . $nuevo['nombre_insumo'] . '" en el inventario médico.'
    );

    Respuesta::exito($nuevo, 201);
}

/**
 * API: editar insumo. SOLO nombre, tipo, presentación, fecha de vencimiento
 * y descripción; cantidad y estatus no se modifican desde aquí.
 */
function apiActualizarInsumo(): void
{
    Autorizacion::verificar('inventario medico', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new InventarioModel();
    $modelo->__set('id_insumo',        $entrada['id_insumo'] ?? 0);
    $modelo->__set('nombre_insumo',    $entrada['nombre_insumo'] ?? '');
    $modelo->__set('tipo_insumo',      $entrada['tipo_insumo'] ?? '');
    $modelo->__set('id_presentacion',  $entrada['id_presentacion'] ?? 0);
    $modelo->__set('fecha_vencimiento', $entrada['fecha_vencimiento'] ?? '');
    $modelo->__set('descripcion',      $entrada['descripcion'] ?? '');

    $actualizado = $modelo->manejarAccion('actualizar');

    Bitacora::registrar(
        'Inventario Medico',
        'Actualización',
        'Actualizó el insumo "' . $actualizado['nombre_insumo'] . '" del inventario médico.'
    );

    Respuesta::exito($actualizado);
}

/** API: eliminar insumo (solo sin stock, sin movimientos reales ni uso en Medicina). */
function apiEliminarInsumo(): void
{
    Autorizacion::verificar('inventario medico', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new InventarioModel();
    $modelo->__set('id_insumo', $entrada['id_insumo'] ?? 0);

    // Se consulta antes de borrar para auditar con el nombre real.
    $insumo = $modelo->manejarAccion('obtener');
    $modelo->manejarAccion('eliminar');

    Bitacora::registrar(
        'Inventario Medico',
        'Eliminación',
        'Eliminó el insumo "' . $insumo['nombre_insumo'] . '" del inventario médico.'
    );

    Respuesta::exito(['eliminado' => true]);
}

/** API: tarjetas de resumen del módulo. */
function apiInventarioStats(): void
{
    Autorizacion::verificar('inventario medico', 'leer');

    $modelo = new InventarioModel();
    Respuesta::exito($modelo->manejarAccion('stats'));
}

/**
 * API: registrar ENTRADA de stock. Se decide con permiso 'crear' (ambos
 * movimientos crean registros nuevos en inventario_medico).
 */
function apiRegistrarEntradaInsumo(): void
{
    Autorizacion::verificar('inventario medico', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new InventarioModel();
    $modelo->__set('id_insumo',    $entrada['id_insumo'] ?? 0);
    $modelo->__set('cantidad',     $entrada['cantidad'] ?? 0);
    $modelo->__set('descripcion',  $entrada['descripcion'] ?? '');
    $modelo->__set('id_empleado',  (int) ($_SESSION['id_empleado'] ?? 0));

    $resultado = $modelo->manejarAccion('entrada');

    Bitacora::registrar(
        'Inventario Medico',
        'Registro',
        'Registró la entrada del insumo ' . $resultado['nombre_insumo']
        . ' (+' . $resultado['cantidad'] . ') en el inventario médico.'
    );

    Respuesta::exito($resultado, 201);
}

/** API: registrar SALIDA de stock con motivo (Vencimiento/Daño/Pérdida/…). */
function apiRegistrarSalidaInsumo(): void
{
    Autorizacion::verificar('inventario medico', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new InventarioModel();
    $modelo->__set('id_insumo',    $entrada['id_insumo'] ?? 0);
    $modelo->__set('cantidad',     $entrada['cantidad'] ?? 0);
    $modelo->__set('motivo',       $entrada['motivo'] ?? '');
    $modelo->__set('descripcion',  $entrada['descripcion'] ?? '');
    $modelo->__set('id_empleado',  (int) ($_SESSION['id_empleado'] ?? 0));

    $resultado = $modelo->manejarAccion('salida');

    Bitacora::registrar(
        'Inventario Medico',
        'Registro',
        'Registró salida (' . $resultado['motivo'] . ') del insumo '
        . $resultado['nombre_insumo'] . ' (-' . $resultado['cantidad']
        . ') en el inventario médico.'
    );

    Respuesta::exito($resultado, 201);
}

/** API: kardex de movimientos (historial). */
function apiMovimientosInventario(): void
{
    Autorizacion::verificar('inventario medico', 'leer');

    $modelo = new InventarioModel();
    Respuesta::exito($modelo->manejarAccion('movimientos'));
}

/**
 * API: validar duplicado en vivo (presentación + nombre + tipo + fecha).
 * Body JSON: {id_presentacion, nombre_insumo, tipo_insumo, fecha_vencimiento, id_excluir?}
 */
function apiValidarInsumo(): void
{
    Autorizacion::verificar('inventario medico', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new InventarioModel();
    $modelo->__set('nombre_insumo',     $entrada['nombre_insumo'] ?? '');
    $modelo->__set('tipo_insumo',       $entrada['tipo_insumo'] ?? '');
    $modelo->__set('id_presentacion',   $entrada['id_presentacion'] ?? 0);
    $modelo->__set('fecha_vencimiento', $entrada['fecha_vencimiento'] ?? '');
    if (!empty($entrada['id_excluir'])) {
        $modelo->__set('id_excluir', $entrada['id_excluir']);
    }

    Respuesta::exito(['existe' => $modelo->manejarAccion('existe_insumo')]);
}
