<?php
// app/Controllers/perfilController.php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Notificador;
use App\Core\Respuesta;
use App\Models\PerfilModel;

// ==================== PUERTA HTML ====================

/** Página: perfil del empleado autenticado (solo requiere sesión). */
function showPerfil(): void
{
    // Solo exige sesión (el middleware ya la validó). NO se verifica un
    // permiso de módulo: cada empleado gestiona SU propio perfil.
    require_once BASE_PATH . '/app/Views/perfil/ver.php';
}

// ==================== PUERTA JSON (API) ====================

/** API: datos del perfil del empleado de la sesión. */
function apiObtenerPerfil(): void
{
    // El id NUNCA viene de la petición: siempre de la sesión.
    $modelo = new PerfilModel();
    $modelo->__set('id_empleado', (int) ($_SESSION['id_empleado'] ?? 0));

    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/**
 * API: actualizar MI perfil (correo, teléfono, dirección y contraseña).
 * Body JSON: {correo, telefono, direccion, clave_actual, clave?, clave_confirmacion?}
 */
function apiActualizarPerfil(): void
{
    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new PerfilModel();
    // Autoservicio: el id siempre es el del empleado autenticado.
    $modelo->__set('id_empleado', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('correo',      $entrada['correo'] ?? '');
    $modelo->__set('telefono',    $entrada['telefono'] ?? '');
    $modelo->__set('direccion',   $entrada['direccion'] ?? '');
    $modelo->__set('clave_actual', $entrada['clave_actual'] ?? '');
    if (!empty($entrada['clave'])) {
        $modelo->__set('clave', $entrada['clave']);
    }
    if (isset($entrada['clave_confirmacion']) && $entrada['clave_confirmacion'] !== '') {
        $modelo->__set('clave_confirmacion', $entrada['clave_confirmacion']);
    }

    $actualizado = $modelo->manejarAccion('actualizar');

    // Efectos secundarios (nunca lanzan).
    Bitacora::registrar('Perfil', 'Actualización',
        'Actualizó su propio perfil'
        . ($actualizado['cambio_clave'] ? ' (incluyó cambio de contraseña)' : ''));

    Notificador::enviar(
        (int) ($_SESSION['id_empleado'] ?? 0),
        'Tu perfil fue actualizado',
        'perfil/ver',
        'info'
    );

    Respuesta::exito($actualizado);
}

/** API: valida que el correo del perfil no pertenezca a otro empleado. */
function apiValidarCorreoPerfil(): void
{
    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new PerfilModel();
    $modelo->__set('id_empleado', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('correo', $entrada['correo'] ?? '');

    Respuesta::exito($modelo->manejarAccion('validar_correo'));
}

/** API: valida que el teléfono del perfil no pertenezca a otro empleado. */
function apiValidarTelefonoPerfil(): void
{
    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new PerfilModel();
    $modelo->__set('id_empleado', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('telefono', $entrada['telefono'] ?? '');

    Respuesta::exito($modelo->manejarAccion('validar_telefono'));
}
