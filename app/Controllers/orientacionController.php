<?php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\OrientacionModel;

/**
 * Módulo de diagnósticos de Orientación (id_modulo 6).
 * Controllers = SOLO funciones (regla del repositorio).
 */

function contextoOrientacion(OrientacionModel $modelo): void
{
    $modelo->__set('id_usuario', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado'] ?? '');
}

function entradaOrientacion(): array
{
    $entrada = json_decode(file_get_contents('php://input'), true);
    return is_array($entrada) ? $entrada : $_POST;
}

// ---------- Páginas (puerta HTML) ----------

function showCrearOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'crear');
    require_once BASE_PATH . '/app/Views/orientacion/crear.php';
}

function showConsultarOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'leer');
    require_once BASE_PATH . '/app/Views/orientacion/consultar.php';
}

// ---------- APIs (puerta JSON) ----------

function apiListarOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'leer');
    $modelo = new OrientacionModel();
    contextoOrientacion($modelo);
    Respuesta::exito($modelo->manejarAccion('listar'));
}

function apiObtenerOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'leer');
    $modelo = new OrientacionModel();
    contextoOrientacion($modelo);
    $modelo->__set('id_orientacion', (int) ($_GET['id'] ?? 0));
    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/** Catálogo del formulario: beneficiarios activos. */
function apiCatalogosOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'leer');
    $modelo = new OrientacionModel();
    contextoOrientacion($modelo);
    Respuesta::exito([
        'beneficiarios' => $modelo->manejarAccion('beneficiarios'),
    ]);
}

function apiStatsOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'leer');
    $modelo = new OrientacionModel();
    contextoOrientacion($modelo);
    Respuesta::exito($modelo->manejarAccion('stats'));
}

// ---------- Documentos (tercera puerta PDF) ----------

/** Constancia de Atención (GET orientacion/constancia/{id} + ?tramite=&hora=). */
function generarConstanciaOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'leer');
    $parametros = parametrosDocumento();
    $modelo = new OrientacionModel();
    contextoOrientacion($modelo);
    $modelo->__set('id_orientacion', (int) ($_GET['id'] ?? 0));
    $datos = array_merge($modelo->manejarAccion('datos_documento'), $parametros);
    Bitacora::registrar('Orientacion', 'Registro', 'Generó la constancia de atención de ' . $datos['beneficiario'] . '.');
    require_once BASE_PATH . 'docs/PDF/constancia/procesar.php';
    GenerarConstancia::generar($datos);
}

/** Referencia a otra área (GET orientacion/referencia/{id} + ?area= obligatoria). */
function generarReferenciaOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'leer');
    $parametros = parametrosDocumento(true);
    $modelo = new OrientacionModel();
    contextoOrientacion($modelo);
    $modelo->__set('id_orientacion', (int) ($_GET['id'] ?? 0));
    $datos = array_merge($modelo->manejarAccion('datos_documento'), $parametros);
    Bitacora::registrar('Orientacion', 'Registro',
        'Generó la referencia al área ' . $datos['area'] . ' de ' . $datos['beneficiario'] . '.');
    require_once BASE_PATH . 'docs/PDF/referencia/procesar.php';
    GenerarReferencia::generar($datos);
}

function apiCrearOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'crear');
    $entrada = entradaOrientacion();
    $modelo = new OrientacionModel();
    contextoOrientacion($modelo);
    orientacionAsignarEntrada($modelo, $entrada);
    $nuevo = $modelo->manejarAccion('crear');
    Bitacora::registrar('Orientacion', 'Registro', 'Registró una orientación.');
    Respuesta::exito($nuevo, 201);
}

function apiActualizarOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'editar');
    $entrada = entradaOrientacion();
    $modelo = new OrientacionModel();
    contextoOrientacion($modelo);
    $modelo->__set('id_orientacion', $entrada['id_orientacion'] ?? 0);
    orientacionAsignarEntradaEdicion($modelo, $entrada);
    $actualizado = $modelo->manejarAccion('actualizar');
    Bitacora::registrar('Orientacion', 'Actualización', 'Actualizó la orientación #' . $actualizado['id_orientacion'] . '.');
    Respuesta::exito($actualizado);
}

function apiEliminarOrientacion(): void
{
    Autorizacion::verificar('orientacion', 'eliminar');
    $entrada = entradaOrientacion();
    $modelo = new OrientacionModel();
    contextoOrientacion($modelo);
    $modelo->__set('id_orientacion', $entrada['id_orientacion'] ?? 0);
    $eliminado = $modelo->manejarAccion('eliminar');
    Bitacora::registrar('Orientacion', 'Eliminación', 'Eliminó la orientación #'
        . $eliminado['id_orientacion'] . ' de ' . ($eliminado['beneficiario'] ?: 'un beneficiario') . '.');
    Respuesta::exito($eliminado);
}

/**
 * Copia los campos del formulario al modelo. Los vacíos se omiten (el
 * modelo decide cuáles son obligatorios: los 4 textos y el beneficiario).
 */
function orientacionAsignarEntrada(OrientacionModel $modelo, array $entrada): void
{
    foreach (['id_beneficiario', 'motivo_orientacion', 'descripcion_orientacion',
              'indicaciones_orientacion', 'obs_adic_orientacion'] as $campo) {
        if (array_key_exists($campo, $entrada) && $entrada[$campo] !== '') {
            $modelo->__set($campo, $entrada[$campo]);
        }
    }
    if (in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true) && !empty($entrada['id_empleado'])) {
        $modelo->__set('id_empleado', $entrada['id_empleado']);
    }
}

/**
 * Edición restringida: SOLO los 4 campos de texto. Beneficiario y
 * empleado que atendió jamás se aceptan desde el cliente.
 */
function orientacionAsignarEntradaEdicion(OrientacionModel $modelo, array $entrada): void
{
    foreach (['motivo_orientacion', 'descripcion_orientacion',
              'indicaciones_orientacion', 'obs_adic_orientacion'] as $campo) {
        if (array_key_exists($campo, $entrada) && $entrada[$campo] !== '') {
            $modelo->__set($campo, $entrada[$campo]);
        }
    }
}
