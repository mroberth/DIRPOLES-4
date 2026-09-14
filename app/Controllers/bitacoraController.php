<?php
// app/Controllers/bitacoraController.php

use App\Core\Autorizacion;
use App\Core\Respuesta;
use App\Models\BitacoraModel;

// ==================== PUERTA HTML ====================

/** Página: consultar la bitácora (solo lectura). */
function showConsultarBitacora(): void
{
    Autorizacion::verificar('bitacora', 'leer');
    require_once BASE_PATH . '/app/Views/bitacora/consultar.php';
}

// ==================== PUERTA JSON (API) ====================

/** API: listar movimientos con filtros opcionales. */
function apiBitacoraListar(): void
{
    Autorizacion::verificar('bitacora', 'leer');

    $modelo = new BitacoraModel();
    foreach (['modulo', 'accion', 'buscar', 'desde', 'hasta'] as $campo) {
        if (isset($_GET[$campo]) && $_GET[$campo] !== '') {
            $modelo->__set($campo, $_GET[$campo]);
        }
    }
    if (!empty($_GET['id_empleado'])) {
        $modelo->__set('id_empleado', $_GET['id_empleado']);
    }

    Respuesta::exito($modelo->manejarAccion('listar'));
}

/** API: opciones de los filtros (módulos, acciones, empleados). */
function apiBitacoraFiltros(): void
{
    Autorizacion::verificar('bitacora', 'leer');

    $modelo = new BitacoraModel();
    Respuesta::exito($modelo->manejarAccion('filtros'));
}

/** API: tarjetas de resumen de la bitácora. */
function apiBitacoraStats(): void
{
    Autorizacion::verificar('bitacora', 'leer');

    $modelo = new BitacoraModel();
    Respuesta::exito($modelo->manejarAccion('stats'));
}
