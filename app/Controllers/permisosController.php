<?php
// app/Controllers/permisosController.php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\PermisosModel;

// ==================== PUERTA HTML ====================

/** Página: gestión de permisos por rol × módulo. */
function showGestionarPermisos(): void
{
    Autorizacion::verificar('permisos', 'leer');

    // La matriz se carga por API (api/permisos/matriz) para no inflar el HTML.
    require_once BASE_PATH . '/app/Views/permisos/gestionar.php';
}

// ==================== PUERTA JSON (API) ====================

/**
 * API: datos para la matriz (roles del sistema, módulos, permisos y mapa).
 * Se filtra a los roles de app/Config/roles_sistema.php.
 */
function apiPermisosMatriz(): void
{
    Autorizacion::verificar('permisos', 'leer');

    $modelo = new PermisosModel();

    $roles = $modelo->manejarAccion('tipos_empleados');
    $permitidos = require BASE_PATH . '/app/Config/roles_sistema.php';
    $roles = array_values(array_filter(
        $roles,
        static fn ($r) => in_array((int) $r['id_tipo_emp'], $permitidos, true)
    ));

    Respuesta::exito([
        'roles'    => $roles,
        'modulos'  => $modelo->manejarAccion('obtener_modulos'),
        'permisos' => $modelo->manejarAccion('obtenerPermisos'),
        'mapa'     => $modelo->manejarAccion('mapa_permisos_todos'),
    ]);
}

/** API: tarjetas de resumen del módulo de permisos. */
function apiPermisosStats(): void
{
    Autorizacion::verificar('permisos', 'leer');

    $modelo = new PermisosModel();
    Respuesta::exito($modelo->manejarAccion('stats'));
}

/**
 * API: guardar en lote los permisos modificados.
 * Body JSON: { cambios: [ { id_tipo_emp, id_modulo, permisos: [1,2,3] }, ... ] }
 */
function apiGuardarPermisosLote(): void
{
    Autorizacion::verificar('permisos', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $cambios = $entrada['cambios'] ?? null;
    if (is_string($cambios)) { // por si viene como string JSON (form-urlencoded)
        $cambios = json_decode($cambios, true);
    }

    $modelo = new PermisosModel();
    $modelo->__set('cambios', is_array($cambios) ? $cambios : []);

    $resultado = $modelo->manejarAccion('guardar_permisos_lote');

    Bitacora::registrar(
        'Permisos',
        'Actualización',
        'Actualizó permisos de roles (' . count($resultado['cambios']) . ' combinaciones).'
    );

    Respuesta::exito($resultado);
}
