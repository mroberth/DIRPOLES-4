<?php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\PsicologiaModel;

function contextoPsicologia(PsicologiaModel $modelo): void
{
    $modelo->__set('id_usuario', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado'] ?? '');
}

function entradaPsicologia(): array
{
    $entrada = json_decode(file_get_contents('php://input'), true);
    return is_array($entrada) ? $entrada : $_POST;
}

function showConsultarPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'leer');
    require_once BASE_PATH . '/app/Views/psicologia/consultar.php';
}

function showCrearPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'crear');
    require_once BASE_PATH . '/app/Views/psicologia/crear.php';
}

function apiListarPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'leer');
    $modelo = new PsicologiaModel();
    contextoPsicologia($modelo);
    Respuesta::exito($modelo->manejarAccion('listar'));
}

function apiObtenerPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'leer');
    $modelo = new PsicologiaModel();
    contextoPsicologia($modelo);
    $modelo->__set('id_psicologia', (int) ($_GET['id'] ?? 0));
    Respuesta::exito($modelo->manejarAccion('obtener'));
}

function apiCatalogosPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'leer');
    $modelo = new PsicologiaModel();
    contextoPsicologia($modelo);
    Respuesta::exito([
        'patologias' => $modelo->manejarAccion('patologias'),
        'beneficiarios' => $modelo->manejarAccion('beneficiarios'),
    ]);
}

function apiStatsPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'leer');
    $modelo = new PsicologiaModel();
    contextoPsicologia($modelo);
    Respuesta::exito($modelo->manejarAccion('stats'));
}

// ---------- Documentos (tercera puerta PDF) ----------

/** Constancia de Atención (GET psicologia/constancia/{id} + ?tramite=&hora=). */
function generarConstanciaPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'leer');
    $parametros = parametrosDocumento();
    $modelo = new PsicologiaModel();
    contextoPsicologia($modelo);
    $modelo->__set('id_psicologia', (int) ($_GET['id'] ?? 0));
    $datos = array_merge($modelo->manejarAccion('datos_documento'), $parametros);
    Bitacora::registrar('Psicologia', 'Registro', 'Generó la constancia de atención de ' . $datos['beneficiario'] . '.');
    require_once BASE_PATH . 'docs/PDF/constancia/procesar.php';
    GenerarConstancia::generar($datos);
}

/** Referencia a otra área (GET psicologia/referencia/{id} + ?area= obligatoria). */
function generarReferenciaPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'leer');
    $parametros = parametrosDocumento(true);
    $modelo = new PsicologiaModel();
    contextoPsicologia($modelo);
    $modelo->__set('id_psicologia', (int) ($_GET['id'] ?? 0));
    $datos = array_merge($modelo->manejarAccion('datos_documento'), $parametros);
    Bitacora::registrar('Psicologia', 'Registro',
        'Generó la referencia al área ' . $datos['area'] . ' de ' . $datos['beneficiario'] . '.');
    require_once BASE_PATH . 'docs/PDF/referencia/procesar.php';
    GenerarReferencia::generar($datos);
}

function apiCrearPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'crear');
    $entrada = entradaPsicologia();
    $modelo = new PsicologiaModel();
    contextoPsicologia($modelo);
    psicologiaAsignarEntrada($modelo, $entrada);
    $nuevo = $modelo->manejarAccion('crear');
    Bitacora::registrar('Psicologia', 'Registro', 'Registró una consulta psicológica de tipo ' . $nuevo['tipo_consulta'] . '.');
    Respuesta::exito($nuevo, 201);
}

function apiActualizarPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'editar');
    $entrada = entradaPsicologia();
    $modelo = new PsicologiaModel();
    contextoPsicologia($modelo);
    $modelo->__set('id_psicologia', $entrada['id_psicologia'] ?? 0);
    psicologiaAsignarEntrada($modelo, $entrada);
    $actualizado = $modelo->manejarAccion('actualizar');
    Bitacora::registrar('Psicologia', 'Actualización', 'Actualizó una consulta psicológica.');
    Respuesta::exito($actualizado);
}

function apiEliminarPsicologia(): void
{
    Autorizacion::verificar('psicologia', 'eliminar');
    $entrada = entradaPsicologia();
    $modelo = new PsicologiaModel();
    contextoPsicologia($modelo);
    $modelo->__set('id_psicologia', $entrada['id_psicologia'] ?? 0);
    $eliminado = $modelo->manejarAccion('eliminar');
    Bitacora::registrar('Psicologia', 'Eliminación', 'Eliminó una consulta psicológica.');
    Respuesta::exito($eliminado);
}

function psicologiaAsignarEntrada(PsicologiaModel $modelo, array $entrada): void
{
    foreach (['id_beneficiario', 'id_patologia', 'tipo_consulta', 'diagnostico', 'tratamiento_gen', 'motivo_retiro', 'duracion_retiro', 'motivo_cambio', 'observaciones'] as $campo) {
        if (array_key_exists($campo, $entrada) && $entrada[$campo] !== '') {
            $modelo->__set($campo, $entrada[$campo]);
        }
    }
    if (in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true) && !empty($entrada['id_empleado'])) {
        $modelo->__set('id_empleado', $entrada['id_empleado']);
    }
}