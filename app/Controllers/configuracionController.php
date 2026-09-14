<?php
// app/Controllers/configuracionController.php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\ConfiguracionModel;

// ==================== PUERTA HTML ====================

/** Página: crear registros en los catálogos de configuración. */
function showCrearConfiguracion(): void
{
    Autorizacion::verificar('configuracion', 'leer');

    $catalogos = require BASE_PATH . '/app/Config/configuracion_catalogos.php';

    $modelo = new ConfiguracionModel();
    $servicios = $modelo->manejarAccion('servicios'); // opciones del catálogo tipo_empleado

    require_once BASE_PATH . '/app/Views/configuracion/crear.php';
}

/** Página: tabla de catálogos (solo editar; no eliminar). */
function showConsultarConfiguracion(): void
{
    Autorizacion::verificar('configuracion', 'leer');

    $catalogos = require BASE_PATH . '/app/Config/configuracion_catalogos.php';

    $modelo = new ConfiguracionModel();
    $servicios = $modelo->manejarAccion('servicios');

    require_once BASE_PATH . '/app/Views/configuracion/consultar.php';
}

// ==================== PUERTA JSON (API) ====================

/** API: crear un registro en un catálogo. Body JSON: {catalogo, ...campos}. */
function apiConfiguracionCrear(): void
{
    Autorizacion::verificar('configuracion', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    // OJO: la clave del catálogo viaja como 'catalogo' (no 'tipo') porque el
    // catálogo tipo_empleado TIENE un campo llamado 'tipo'.
    $catalogo = (string) ($entrada['catalogo'] ?? '');
    unset($entrada['catalogo']);

    $modelo = new ConfiguracionModel();
    $modelo->__set('tipo', $catalogo);
    $modelo->__set('datos', $entrada);

    $resultado = $modelo->manejarAccion('crear');

    Bitacora::registrar('Configuracion', 'Registro', "Creó un registro en el catálogo '{$catalogo}'.");

    Respuesta::exito($resultado, 201);
}

/** API: listar un catálogo. GET ?tipo=pnf */
function apiConfiguracionListar(): void
{
    Autorizacion::verificar('configuracion', 'leer');

    $modelo = new ConfiguracionModel();
    $modelo->__set('tipo', $_GET['tipo'] ?? '');

    Respuesta::exito($modelo->manejarAccion('listar'));
}

/** API: obtener un registro. GET ?catalogo=pnf&id=3 (o ruta con {catalogo}/{id}). */
function apiConfiguracionObtener(): void
{
    Autorizacion::verificar('configuracion', 'leer');

    $modelo = new ConfiguracionModel();
    $modelo->__set('tipo', $_GET['catalogo'] ?? '');
    $modelo->__set('id', (int) ($_GET['id'] ?? 0));

    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/** API: actualizar un registro. Body JSON: {catalogo, id, ...campos, estatus?}. */
function apiConfiguracionActualizar(): void
{
    Autorizacion::verificar('configuracion', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $catalogo = (string) ($entrada['catalogo'] ?? '');
    $id = (int) ($entrada['id'] ?? 0);
    unset($entrada['catalogo'], $entrada['id']);

    $modelo = new ConfiguracionModel();
    $modelo->__set('tipo', $catalogo);
    $modelo->__set('id', $id);
    $modelo->__set('datos', $entrada);

    $resultado = $modelo->manejarAccion('actualizar');

    Bitacora::registrar('Configuracion', 'Actualización', "Actualizó un registro del catálogo '{$catalogo}'.");

    Respuesta::exito($resultado);
}

/** API: validar unicidad en vivo. Body JSON: {catalogo, id_excluir?, ...campos}. */
function apiConfiguracionValidar(): void
{
    Autorizacion::verificar('configuracion', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $catalogo = (string) ($entrada['catalogo'] ?? '');
    $idExcluir = (int) ($entrada['id_excluir'] ?? 0);
    unset($entrada['catalogo'], $entrada['id_excluir']);

    $modelo = new ConfiguracionModel();
    $modelo->__set('tipo', $catalogo);
    $modelo->__set('id_excluir', $idExcluir);
    $modelo->__set('datos', $entrada);

    Respuesta::exito($modelo->manejarAccion('validar'));
}
