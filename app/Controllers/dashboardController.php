<?php

use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Core\Respuesta;
use App\Models\CalendarioModel;
use App\Models\DashboardModel;

/**
 * Controlador del panel de Inicio — SOLO funciones.
 * ---------------------------------------------------------------
 * Puerta JSON (api/dashboard/* y api/calendario/*). El estado (stats y
 * eventos) SIEMPRE se acota al id_empleado de la sesión; el cliente nunca
 * lo envía.
 */

/** API: estadísticas del panel según el rol de la sesión. */
function apiDashboardStats(): void
{
    $tipo = $_SESSION['tipo_empleado'] ?? '';

    $modelo = new DashboardModel();
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);

    $datos = match (true) {
        in_array($tipo, ['Administrador', 'Superusuario'], true) => $modelo->manejarAccion('stats_admin'),
        $tipo === 'Psicologo'          => $modelo->manejarAccion('stats_psicologia'),
        $tipo === 'Medico'             => $modelo->manejarAccion('stats_medicina'),
        $tipo === 'Orientador'         => $modelo->manejarAccion('stats_orientacion'),
        $tipo === 'Trabajador Social'  => $modelo->manejarAccion('stats_trabajo_social'),
        $tipo === 'Discapacidad'       => $modelo->manejarAccion('stats_discapacidad'),
        default                        => $modelo->manejarAccion('stats_generico'),
    };

    Respuesta::exito($datos);
}

/** API: eventos del calendario personal del usuario. */
function apiCalendarioEventos(): void
{
    $modelo = new CalendarioModel();
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);
    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/** API: crear un evento personal. Body JSON: {titulo, descripcion, fecha}. */
function apiCalendarioGuardar(): void
{
    $entrada = leerEntradaJson();

    $modelo = new CalendarioModel();
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);
    $modelo->__set('titulo', $entrada['titulo'] ?? '');
    $modelo->__set('descripcion', $entrada['descripcion'] ?? '');
    $modelo->__set('fecha', $entrada['fecha'] ?? '');

    $id = $modelo->manejarAccion('agregar');

    Bitacora::registrar('Calendario', 'Registro', "Creó un evento personal (id {$id}).");

    Respuesta::exito(['id_evento' => $id], 201);
}

/** API: modificar un evento personal. Body JSON: {id_evento, titulo, descripcion, fecha}. */
function apiCalendarioActualizar(): void
{
    $entrada = leerEntradaJson();

    $modelo = new CalendarioModel();
    $modelo->__set('id_evento', $entrada['id_evento'] ?? 0);
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);
    $modelo->__set('titulo', $entrada['titulo'] ?? '');
    $modelo->__set('descripcion', $entrada['descripcion'] ?? '');
    $modelo->__set('fecha', $entrada['fecha'] ?? '');

    $filas = $modelo->manejarAccion('modificar');

    if ($filas === 0) {
        throw ExcepcionApi::noEncontrado('El evento no existe o no te pertenece.');
    }

    Bitacora::registrar('Calendario', 'Actualización', "Actualizó un evento personal (id {$entrada['id_evento']}).");

    Respuesta::exito(['filas_afectadas' => $filas]);
}

/** API: eliminar un evento personal. Body JSON: {id_evento}. */
function apiCalendarioEliminar(): void
{
    $entrada = leerEntradaJson();

    $modelo = new CalendarioModel();
    $modelo->__set('id_evento', $entrada['id_evento'] ?? 0);
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);

    $filas = $modelo->manejarAccion('eliminar');

    if ($filas === 0) {
        throw ExcepcionApi::noEncontrado('El evento no existe o no te pertenece.');
    }

    Bitacora::registrar('Calendario', 'Eliminación', "Eliminó un evento personal (id {$entrada['id_evento']}).");

    Respuesta::exito(['filas_afectadas' => $filas]);
}

/**
 * Lee el cuerpo de la petición como JSON (o cae a $_POST para formularios
 * clásicos). Helper local del controlador (funciones, sin clases).
 */
function leerEntradaJson(): array
{
    $crudo = file_get_contents('php://input');
    if ($crudo === '' || $crudo === false) {
        return $_POST;
    }

    $datos = json_decode($crudo, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($datos)) {
        throw ExcepcionApi::jsonInvalido();
    }

    return $datos;
}
