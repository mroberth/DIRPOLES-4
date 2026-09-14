<?php
// app/Controllers/beneficiarioController.php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\BeneficiarioModel;

// ==================== PUERTA HTML ====================

/** Página: formulario de creación. */
function showCrearBeneficiario(): void
{
    Autorizacion::verificar('beneficiarios', 'crear');
    require_once BASE_PATH . '/app/Views/beneficiarios/crear.php';
}

/** Página: tabla de beneficiarios. */
function showConsultarBeneficiarios(): void
{
    Autorizacion::verificar('beneficiarios', 'leer');
    require_once BASE_PATH . '/app/Views/beneficiarios/consultar.php';
}

// ==================== PUERTA JSON (API) ====================

/** API: catálogo de PNF (para el <select>). */
function apiPnfs(): void
{
    Autorizacion::verificar('beneficiarios', 'leer');

    $modelo = new BeneficiarioModel();
    Respuesta::exito($modelo->manejarAccion('pnfs'));
}

/** API: tarjetas de resumen del módulo. */
function apiBeneficiarioStats(): void
{
    Autorizacion::verificar('beneficiarios', 'leer');

    $modelo = new BeneficiarioModel();
    Respuesta::exito($modelo->manejarAccion('stats'));
}

/** API: listar beneficiarios (con búsqueda opcional ?buscar=). */
function apiListarBeneficiarios(): void
{
    Autorizacion::verificar('beneficiarios', 'leer');

    $modelo = new BeneficiarioModel();
    if (isset($_GET['buscar'])) {
        $modelo->__set('buscar', $_GET['buscar']);
    }
    $modelo->__set('limit',  (int) ($_GET['limit']  ?? 200));
    $modelo->__set('offset', (int) ($_GET['offset'] ?? 0));

    Respuesta::exito($modelo->manejarAccion('listar'));
}

/** API: detalle de un beneficiario (para el modal de edición). */
function apiObtenerBeneficiario(): void
{
    Autorizacion::verificar('beneficiarios', 'leer');

    $modelo = new BeneficiarioModel();
    $modelo->__set('id_beneficiario', (int) ($_GET['id'] ?? 0));

    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/** API: crear beneficiario. */
function apiCrearBeneficiario(): void
{
    Autorizacion::verificar('beneficiarios', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new BeneficiarioModel();
    $modelo->__set('id_pnf',      $entrada['id_pnf'] ?? 0);
    $modelo->__set('seccion',     $entrada['seccion'] ?? '');
    $modelo->__set('nombres',     $entrada['nombres'] ?? '');
    $modelo->__set('apellidos',   $entrada['apellidos'] ?? '');
    $modelo->__set('tipo_cedula', $entrada['tipo_cedula'] ?? '');
    $modelo->__set('cedula',      $entrada['cedula'] ?? '');
    $modelo->__set('fecha_nac',   $entrada['fecha_nac'] ?? '');
    $modelo->__set('telefono',    $entrada['telefono'] ?? '');
    $modelo->__set('correo',      $entrada['correo'] ?? '');
    $modelo->__set('genero',      $entrada['genero'] ?? '');
    $modelo->__set('direccion',   $entrada['direccion'] ?? '');
    if (isset($entrada['estatus'])) {
        $modelo->__set('estatus', $entrada['estatus']);
    }

    $nuevo = $modelo->manejarAccion('crear');

    Bitacora::registrar('Beneficiarios', 'Registro',
        'Creó al beneficiario "' . $nuevo['nombres'] . ' ' . $nuevo['apellidos'] . '"');

    Respuesta::exito($nuevo, 201);
}

/** API: actualizar beneficiario. Body JSON con id_beneficiario y los campos. */
function apiActualizarBeneficiario(): void
{
    Autorizacion::verificar('beneficiarios', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new BeneficiarioModel();
    $modelo->__set('id_beneficiario', $entrada['id_beneficiario'] ?? 0);
    $modelo->__set('id_pnf',         $entrada['id_pnf'] ?? 0);
    $modelo->__set('seccion',        $entrada['seccion'] ?? '');
    $modelo->__set('nombres',        $entrada['nombres'] ?? '');
    $modelo->__set('apellidos',      $entrada['apellidos'] ?? '');
    $modelo->__set('tipo_cedula',    $entrada['tipo_cedula'] ?? '');
    $modelo->__set('cedula',         $entrada['cedula'] ?? '');
    $modelo->__set('fecha_nac',      $entrada['fecha_nac'] ?? '');
    $modelo->__set('telefono',       $entrada['telefono'] ?? '');
    $modelo->__set('correo',         $entrada['correo'] ?? '');
    $modelo->__set('genero',         $entrada['genero'] ?? '');
    $modelo->__set('direccion',      $entrada['direccion'] ?? '');
    if (isset($entrada['estatus'])) {
        $modelo->__set('estatus', $entrada['estatus']);
    }

    $actualizado = $modelo->manejarAccion('actualizar');

    Bitacora::registrar('Beneficiarios', 'Actualización',
        'Actualizó al beneficiario "' . $actualizado['nombres'] . ' ' . $actualizado['apellidos'] . '"');

    Respuesta::exito($actualizado);
}

/** API: eliminar beneficiario. Body JSON: {id_beneficiario}. */
function apiEliminarBeneficiario(): void
{
    Autorizacion::verificar('beneficiarios', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $id = (int) ($entrada['id_beneficiario'] ?? 0);

    $modelo = new BeneficiarioModel();
    $modelo->__set('id_beneficiario', $id);

    $datos = $modelo->manejarAccion('obtener');
    $modelo->manejarAccion('eliminar');

    Bitacora::registrar('Beneficiarios', 'Eliminación',
        'Eliminó al beneficiario "' . $datos['nombres'] . ' ' . $datos['apellidos'] . '"');

    Respuesta::exito(['id_beneficiario' => $id]);
}

// ==================== Validaciones remotas de unicidad ====================

/** API: validar que una cédula no esté registrada. */
function apiValidarCedulaBeneficiario(): void
{
    Autorizacion::verificar('beneficiarios', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new BeneficiarioModel();
    $modelo->__set('tipo_cedula', $entrada['tipo_cedula'] ?? '');
    $modelo->__set('cedula', $entrada['cedula'] ?? '');
    if (!empty($entrada['id_excluir'])) {
        $modelo->__set('id_excluir', $entrada['id_excluir']);
    }

    Respuesta::exito(['existe' => $modelo->manejarAccion('existe_cedula')]);
}

/** API: validar que un correo no esté registrado. */
function apiValidarCorreoBeneficiario(): void
{
    Autorizacion::verificar('beneficiarios', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new BeneficiarioModel();
    $modelo->__set('correo', $entrada['correo'] ?? '');
    if (!empty($entrada['id_excluir'])) {
        $modelo->__set('id_excluir', $entrada['id_excluir']);
    }

    Respuesta::exito(['existe' => $modelo->manejarAccion('existe_correo')]);
}

/** API: validar que un teléfono no esté registrado. */
function apiValidarTelefonoBeneficiario(): void
{
    Autorizacion::verificar('beneficiarios', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new BeneficiarioModel();
    $modelo->__set('telefono', $entrada['telefono'] ?? '');
    if (!empty($entrada['id_excluir'])) {
        $modelo->__set('id_excluir', $entrada['id_excluir']);
    }

    Respuesta::exito(['existe' => $modelo->manejarAccion('existe_telefono')]);
}
