<?php
// app/Controllers/empleadoController.php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\EmpleadoModel;

// ==================== PUERTA HTML ====================

/** Página: formulario de creación. */
function showCrearEmpleado(): void
{
    Autorizacion::verificar('empleados', 'crear');
    require_once BASE_PATH . '/app/Views/empleados/crear.php';
}

// ==================== PUERTA JSON (API) ====================

/** API: catálogo de tipos de empleado (para el <select>). */
function apiTiposEmpleado(): void
{
    Autorizacion::verificar('empleados', 'leer');

    $modelo = new EmpleadoModel();
    Respuesta::exito($modelo->manejarAccion('tipos'));
}

/** API: crear empleado. */
function apiCrearEmpleado(): void
{
    Autorizacion::verificar('empleados', 'crear');

    // Lee JSON del body o, si no, el POST clásico.
    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new EmpleadoModel();
    $modelo->__set('nombre',           $entrada['nombre'] ?? '');
    $modelo->__set('apellido',         $entrada['apellido'] ?? '');
    $modelo->__set('tipo_cedula',      $entrada['tipo_cedula'] ?? '');
    $modelo->__set('cedula',           $entrada['cedula'] ?? '');
    $modelo->__set('correo',           $entrada['correo'] ?? '');
    $modelo->__set('telefono',         $entrada['telefono'] ?? '');
    $modelo->__set('id_tipo_empleado', $entrada['id_tipo_empleado'] ?? 0);
    $modelo->__set('fecha_nacimiento', $entrada['fecha_nacimiento'] ?? '');
    $modelo->__set('direccion',        $entrada['direccion'] ?? '');
    $modelo->__set('clave',            $entrada['clave'] ?? '');
    if (isset($entrada['estatus'])) {
        $modelo->__set('estatus', $entrada['estatus']);
    }

    $nuevo = $modelo->manejarAccion('crear');

    // Auditoría (nunca lanza). Acción válida del ENUM: 'Registro'.
    Bitacora::registrar('Empleados', 'Registro',
        'Creó al empleado "' . $nuevo['nombre'] . ' ' . $nuevo['apellido'] . '"');

    Respuesta::exito($nuevo, 201);
}

/** API: tarjetas de resumen del módulo. */
function apiEmpleadoStats(): void
{
    Autorizacion::verificar('empleados', 'leer');

    $modelo = new EmpleadoModel();
    Respuesta::exito($modelo->manejarAccion('stats'));
}

/**
 * API: validar que una cédula no esté registrada.
 * Body JSON: {tipo_cedula, cedula, id_excluir?}
 */
function apiValidarCedula(): void
{
    Autorizacion::verificar('empleados', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new EmpleadoModel();
    $modelo->__set('tipo_cedula', $entrada['tipo_cedula'] ?? '');
    $modelo->__set('cedula', $entrada['cedula'] ?? '');
    if (!empty($entrada['id_excluir'])) {
        $modelo->__set('id_excluir', $entrada['id_excluir']);
    }

    Respuesta::exito(['existe' => $modelo->manejarAccion('existe_cedula')]);
}

/** API: validar que un correo no esté registrado. Body JSON: {correo, id_excluir?} */
function apiValidarCorreo(): void
{
    Autorizacion::verificar('empleados', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new EmpleadoModel();
    $modelo->__set('correo', $entrada['correo'] ?? '');
    if (!empty($entrada['id_excluir'])) {
        $modelo->__set('id_excluir', $entrada['id_excluir']);
    }

    Respuesta::exito(['existe' => $modelo->manejarAccion('existe_correo')]);
}

/** API: validar que un teléfono no esté registrado. Body JSON: {telefono, id_excluir?} */
function apiValidarTelefono(): void
{
    Autorizacion::verificar('empleados', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new EmpleadoModel();
    $modelo->__set('telefono', $entrada['telefono'] ?? '');
    if (!empty($entrada['id_excluir'])) {
        $modelo->__set('id_excluir', $entrada['id_excluir']);
    }

    Respuesta::exito(['existe' => $modelo->manejarAccion('existe_telefono')]);
}

/** Página: tabla de empleados. */
function showConsultarEmpleados(): void
{
    Autorizacion::verificar('empleados', 'leer');
    require_once BASE_PATH . '/app/Views/empleados/consultar.php';
}

/** API: listar empleados (con búsqueda opcional ?buscar=). */
function apiListarEmpleados(): void
{
    Autorizacion::verificar('empleados', 'leer');

    $modelo = new EmpleadoModel();
    if (isset($_GET['buscar'])) {
        $modelo->__set('buscar', $_GET['buscar']);
    }
    $modelo->__set('limit',  (int) ($_GET['limit']  ?? 200));
    $modelo->__set('offset', (int) ($_GET['offset'] ?? 0));

    Respuesta::exito($modelo->manejarAccion('listar'));
}

/** API: detalle de un empleado (para el modal de edición). */
function apiObtenerEmpleado(): void
{
    Autorizacion::verificar('empleados', 'leer');

    $modelo = new EmpleadoModel();
    $modelo->__set('id_empleado', (int) ($_GET['id'] ?? 0));

    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/** API: actualizar empleado. Body JSON con id_empleado y los campos. */
function apiActualizarEmpleado(): void
{
    Autorizacion::verificar('empleados', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new EmpleadoModel();
    $modelo->__set('id_empleado',     $entrada['id_empleado'] ?? 0);
    $modelo->__set('nombre',          $entrada['nombre'] ?? '');
    $modelo->__set('apellido',        $entrada['apellido'] ?? '');
    $modelo->__set('tipo_cedula',     $entrada['tipo_cedula'] ?? '');
    $modelo->__set('cedula',          $entrada['cedula'] ?? '');
    $modelo->__set('correo',          $entrada['correo'] ?? '');
    $modelo->__set('telefono',        $entrada['telefono'] ?? '');
    $modelo->__set('id_tipo_empleado', $entrada['id_tipo_empleado'] ?? 0);
    $modelo->__set('fecha_nacimiento', $entrada['fecha_nacimiento'] ?? '');
    $modelo->__set('direccion',       $entrada['direccion'] ?? '');
    if (isset($entrada['estatus'])) {
        $modelo->__set('estatus', $entrada['estatus']);
    }
    // La clave es opcional: solo se cambia si viene con valor.
    if (!empty($entrada['clave'])) {
        $modelo->__set('clave', $entrada['clave']);
    }

    $actualizado = $modelo->manejarAccion('actualizar');

    Bitacora::registrar('Empleados', 'Actualización',
        'Actualizó al empleado "' . $actualizado['nombre'] . ' ' . $actualizado['apellido'] . '"');

    Respuesta::exito($actualizado);
}

/** API: eliminar empleado. Body JSON: {id_empleado}. */
function apiEliminarEmpleado(): void
{
    Autorizacion::verificar('empleados', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $id = (int) ($entrada['id_empleado'] ?? 0);

    $modelo = new EmpleadoModel();
    $modelo->__set('id_empleado', $id);

    // Se lee antes de borrar para la descripción de la bitácora.
    $datos = $modelo->manejarAccion('obtener');
    $modelo->manejarAccion('eliminar');

    Bitacora::registrar('Empleados', 'Eliminación',
        'Eliminó al empleado "' . $datos['nombre'] . ' ' . $datos['apellido'] . '"');

    Respuesta::exito(['id_empleado' => $id]);
}