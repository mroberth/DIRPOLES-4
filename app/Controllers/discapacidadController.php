<?php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\DiscapacidadModel;

/**
 * Módulo de diagnósticos de Discapacidad (id_modulo 8).
 * Controllers = SOLO funciones (regla del repositorio).
 */

function contextoDiscapacidad(DiscapacidadModel $modelo): void
{
    $modelo->__set('id_usuario', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado'] ?? '');
}

function entradaDiscapacidad(): array
{
    $entrada = json_decode(file_get_contents('php://input'), true);
    return is_array($entrada) ? $entrada : $_POST;
}

// ---------- Páginas (puerta HTML) ----------

function showCrearDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'crear');
    require_once BASE_PATH . '/app/Views/discapacidad/crear.php';
}

function showConsultarDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'leer');
    require_once BASE_PATH . '/app/Views/discapacidad/consultar.php';
}

// ---------- APIs (puerta JSON) ----------

function apiListarDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'leer');
    $modelo = new DiscapacidadModel();
    contextoDiscapacidad($modelo);
    Respuesta::exito($modelo->manejarAccion('listar'));
}

function apiObtenerDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'leer');
    $modelo = new DiscapacidadModel();
    contextoDiscapacidad($modelo);
    $modelo->__set('id_discapacidad', (int) ($_GET['id'] ?? 0));
    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/** Catálogo del formulario: beneficiarios activos. */
function apiCatalogosDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'leer');
    $modelo = new DiscapacidadModel();
    contextoDiscapacidad($modelo);
    Respuesta::exito([
        'beneficiarios' => $modelo->manejarAccion('beneficiarios'),
    ]);
}

function apiStatsDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'leer');
    $modelo = new DiscapacidadModel();
    contextoDiscapacidad($modelo);
    Respuesta::exito($modelo->manejarAccion('stats'));
}

// ---------- Documentos (tercera puerta PDF) ----------

/** Constancia de Atención (GET discapacidad/constancia/{id} + ?tramite=&hora=). */
function generarConstanciaDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'leer');
    $parametros = parametrosDocumento();
    $modelo = new DiscapacidadModel();
    contextoDiscapacidad($modelo);
    $modelo->__set('id_discapacidad', (int) ($_GET['id'] ?? 0));
    $datos = array_merge($modelo->manejarAccion('datos_documento'), $parametros);
    Bitacora::registrar('Discapacidad', 'Registro', 'Generó la constancia de atención de ' . $datos['beneficiario'] . '.');
    require_once BASE_PATH . 'docs/PDF/constancia/procesar.php';
    GenerarConstancia::generar($datos);
}

/** Referencia a otra área (GET discapacidad/referencia/{id} + ?area= obligatoria). */
function generarReferenciaDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'leer');
    $parametros = parametrosDocumento(true);
    $modelo = new DiscapacidadModel();
    contextoDiscapacidad($modelo);
    $modelo->__set('id_discapacidad', (int) ($_GET['id'] ?? 0));
    $datos = array_merge($modelo->manejarAccion('datos_documento'), $parametros);
    Bitacora::registrar('Discapacidad', 'Registro',
        'Generó la referencia al área ' . $datos['area'] . ' de ' . $datos['beneficiario'] . '.');
    require_once BASE_PATH . 'docs/PDF/referencia/procesar.php';
    GenerarReferencia::generar($datos);
}

function apiCrearDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'crear');
    $entrada = entradaDiscapacidad();
    $modelo = new DiscapacidadModel();
    contextoDiscapacidad($modelo);
    discapacidadAsignarEntrada($modelo, $entrada);
    $nuevo = $modelo->manejarAccion('crear');
    Bitacora::registrar('Discapacidad', 'Registro', 'Registró un diagnóstico de discapacidad.');
    Respuesta::exito($nuevo, 201);
}

/**
 * Copia los campos del formulario al modelo. El vacío llega tal cual:
 * el modelo distingue obligatorios (error) de opcionales (NULL).
 */
function discapacidadAsignarEntrada(DiscapacidadModel $modelo, array $entrada): void
{
    $campos = ['id_beneficiario', 'tipo_discapacidad', 'disc_especifica', 'diagnostico', 'grado',
               'medicamentos', 'habilidades_funcionales', 'requiere_asistencia', 'dispositivo_asistencia',
               'observaciones', 'recomendaciones', 'carnet_discapacidad'];
    foreach ($campos as $campo) {
        if (array_key_exists($campo, $entrada)) {
            $modelo->__set($campo, $entrada[$campo]);
        }
    }
    if (in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true) && !empty($entrada['id_empleado'])) {
        $modelo->__set('id_empleado', $entrada['id_empleado']);
    }
}

function apiActualizarDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'editar');
    $entrada = entradaDiscapacidad();
    $modelo = new DiscapacidadModel();
    contextoDiscapacidad($modelo);
    $modelo->__set('id_discapacidad', $entrada['id_discapacidad'] ?? 0);
    discapacidadAsignarEntradaEdicion($modelo, $entrada);
    $actualizado = $modelo->manejarAccion('actualizar');
    Bitacora::registrar('Discapacidad', 'Actualización', 'Actualizó el diagnóstico de discapacidad #'
        . $actualizado['id_discapacidad'] . '.');
    Respuesta::exito($actualizado);
}

function apiEliminarDiscapacidad(): void
{
    Autorizacion::verificar('discapacidad', 'eliminar');
    $entrada = entradaDiscapacidad();
    $modelo = new DiscapacidadModel();
    contextoDiscapacidad($modelo);
    $modelo->__set('id_discapacidad', $entrada['id_discapacidad'] ?? 0);
    $eliminado = $modelo->manejarAccion('eliminar');
    Bitacora::registrar('Discapacidad', 'Eliminación', 'Eliminó el diagnóstico de discapacidad #'
        . $eliminado['id_discapacidad'] . ' de ' . ($eliminado['beneficiario'] ?: 'un beneficiario') . '.');
    Respuesta::exito($eliminado);
}

/**
 * Edición restringida: SOLO los 11 campos de la tabla discapacidad.
 * Beneficiario y empleado que atendió jamás se aceptan desde el cliente.
 */
function discapacidadAsignarEntradaEdicion(DiscapacidadModel $modelo, array $entrada): void
{
    $campos = ['tipo_discapacidad', 'disc_especifica', 'diagnostico', 'grado',
               'medicamentos', 'habilidades_funcionales', 'requiere_asistencia', 'dispositivo_asistencia',
               'observaciones', 'recomendaciones', 'carnet_discapacidad'];
    foreach ($campos as $campo) {
        if (array_key_exists($campo, $entrada)) {
            $modelo->__set($campo, $entrada[$campo]);
        }
    }
}
