<?php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\NotificacionesModel;

/**
 * Controlador del módulo de NOTIFICACIONES — SOLO funciones.
 * ---------------------------------------------------------------
 * Reglas del esqueleto:
 *   - Autorizacion::verificar() SIEMPRE primera línea.
 *   - Sin try/catch de formateo: si el modelo lanza ExcepcionApi,
 *     el handler global de index.php responde el JSON del contrato.
 *   - Toda salida pasa por Respuesta::exito() (puerta api/*).
 *   - Bitácora solo en acciones destructivas (eliminar); marcar leída
 *     es estado de interfaz, no un registro de negocio con valor.
 *
 * El id_empleado SIEMPRE sale de la sesión (nunca se recibe del cliente):
 * un empleado solo gestiona SU bandeja.
 */

/** API: bandeja completa (no leídas + últimas 20) — puerta JSON. */
function apiListar(): void
{
    Autorizacion::verificar('notificaciones', 'leer');

    $modelo = new NotificacionesModel();
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);
    $modelo->__set('limit', 20);
    Respuesta::exito($modelo->manejarAccion('listar'));
}

/** API: solo el contador de no leídas (fallback polling del badge). */
function apiContar(): void
{
    Autorizacion::verificar('notificaciones', 'leer');

    $modelo = new NotificacionesModel();
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);
    Respuesta::exito(['total' => $modelo->manejarAccion('contar')]);
}

/** API: marcar una notificación como leída (body: {id}). */
function apiMarcarLeida(): void
{
    Autorizacion::verificar('notificaciones', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = (int) ($entrada['id'] ?? 0);

    $modelo = new NotificacionesModel();
    $modelo->__set('id_notif', $id);
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);
    Respuesta::exito(['filas_afectadas' => $modelo->manejarAccion('marcar_leida')]);
}

/** API: marcar TODAS las notificaciones como leídas. */
function apiMarcarTodasLeidas(): void
{
    Autorizacion::verificar('notificaciones', 'leer');

    $modelo = new NotificacionesModel();
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);
    Respuesta::exito(['filas_afectadas' => $modelo->manejarAccion('marcar_todas_leidas')]);
}

/** API: eliminar una notificación (body: {id}). */
function apiEliminar(): void
{
    Autorizacion::verificar('notificaciones', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = (int) ($entrada['id'] ?? 0);

    $modelo = new NotificacionesModel();
    $modelo->__set('id_notif', $id);
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);
    $filas = $modelo->manejarAccion('eliminar');

    Bitacora::registrar('Notificaciones', 'Eliminación', "Eliminó una notificación de su bandeja (id {$id}).");

    Respuesta::exito(['filas_afectadas' => $filas]);
}

/** API: vaciar toda la bandeja. */
function apiEliminarTodas(): void
{
    Autorizacion::verificar('notificaciones', 'leer');

    $modelo = new NotificacionesModel();
    $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);
    $filas = $modelo->manejarAccion('eliminar_todas');

    Bitacora::registrar('Notificaciones', 'Eliminación', "Vació su bandeja de notificaciones ({$filas} eliminadas).");

    Respuesta::exito(['filas_afectadas' => $filas]);
}