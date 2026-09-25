<?php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\MedicinaModel;

/**
 * Módulo de diagnósticos de Medicina (id_modulo 5).
 * Controllers = SOLO funciones (regla del repositorio).
 */

function contextoMedicina(MedicinaModel $modelo): void
{
    $modelo->__set('id_usuario', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado'] ?? '');
}

function entradaMedicina(): array
{
    $entrada = json_decode(file_get_contents('php://input'), true);
    return is_array($entrada) ? $entrada : $_POST;
}

// ---------- Páginas (puerta HTML) ----------

function showCrearMedicina(): void
{
    Autorizacion::verificar('medicina', 'crear');
    require_once BASE_PATH . '/app/Views/medicina/crear.php';
}

function showConsultarMedicina(): void
{
    Autorizacion::verificar('medicina', 'leer');
    require_once BASE_PATH . '/app/Views/medicina/consultar.php';
}

// ---------- APIs (puerta JSON) ----------

function apiListarMedicina(): void
{
    Autorizacion::verificar('medicina', 'leer');
    $modelo = new MedicinaModel();
    contextoMedicina($modelo);
    Respuesta::exito($modelo->manejarAccion('listar'));
}

function apiObtenerMedicina(): void
{
    Autorizacion::verificar('medicina', 'leer');
    $modelo = new MedicinaModel();
    contextoMedicina($modelo);
    $modelo->__set('id_consulta_med', (int) ($_GET['id'] ?? 0));
    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/** Catálogos del formulario: beneficiarios, patologías e insumos aptos. */
function apiCatalogosMedicina(): void
{
    Autorizacion::verificar('medicina', 'leer');
    $modelo = new MedicinaModel();
    contextoMedicina($modelo);
    Respuesta::exito([
        'patologias' => $modelo->manejarAccion('patologias'),
        'beneficiarios' => $modelo->manejarAccion('beneficiarios'),
        'insumos' => $modelo->manejarAccion('insumos'),
    ]);
}

function apiStatsMedicina(): void
{
    Autorizacion::verificar('medicina', 'leer');
    $modelo = new MedicinaModel();
    contextoMedicina($modelo);
    Respuesta::exito($modelo->manejarAccion('stats'));
}

function apiCrearMedicina(): void
{
    Autorizacion::verificar('medicina', 'crear');
    $entrada = entradaMedicina();
    $modelo = new MedicinaModel();
    contextoMedicina($modelo);
    medicinaAsignarEntrada($modelo, $entrada);
    $nuevo = $modelo->manejarAccion('crear');
    Bitacora::registrar('Medicina', 'Registro', 'Registró una consulta médica.');
    Respuesta::exito($nuevo, 201);
}

function apiActualizarMedicina(): void
{
    Autorizacion::verificar('medicina', 'editar');
    $entrada = entradaMedicina();
    $modelo = new MedicinaModel();
    contextoMedicina($modelo);
    $modelo->__set('id_consulta_med', $entrada['id_consulta_med'] ?? 0);
    medicinaAsignarEntradaEdicion($modelo, $entrada);
    $actualizado = $modelo->manejarAccion('actualizar');
    Bitacora::registrar('Medicina', 'Actualización', 'Actualizó la consulta médica #' . $actualizado['id_consulta_med'] . '.');
    Respuesta::exito($actualizado);
}

function apiEliminarMedicina(): void
{
    Autorizacion::verificar('medicina', 'eliminar');
    $entrada = entradaMedicina();
    $modelo = new MedicinaModel();
    contextoMedicina($modelo);
    $modelo->__set('id_consulta_med', $entrada['id_consulta_med'] ?? 0);
    $eliminado = $modelo->manejarAccion('eliminar');
    $detalle = 'Eliminó la consulta médica #' . $eliminado['id_consulta_med']
        . ' de ' . ($eliminado['beneficiario'] ?: 'un beneficiario');
    if ($eliminado['insumos'] !== '') {
        $detalle .= ' (insumos usados: ' . $eliminado['insumos'] . ')';
    }
    Bitacora::registrar('Medicina', 'Eliminación', $detalle . '. El inventario no fue revertido.');
    Respuesta::exito($eliminado);
}

/**
 * Copia los campos clínicos de la petición al modelo. Los vacíos se omiten
 * (el modelo decide cuáles son obligatorios); `insumos` puede venir vacío.
 */
function medicinaAsignarEntrada(MedicinaModel $modelo, array $entrada): void
{
    foreach (['id_beneficiario', 'id_patologia', 'estatura', 'peso', 'tipo_sangre',
              'motivo_visita', 'diagnostico', 'tratamiento', 'observaciones'] as $campo) {
        if (array_key_exists($campo, $entrada) && $entrada[$campo] !== '') {
            $modelo->__set($campo, $entrada[$campo]);
        }
    }
    if (array_key_exists('insumos', $entrada) && $entrada['insumos'] !== '') {
        $modelo->__set('insumos', $entrada['insumos']);
    }
    if (in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true) && !empty($entrada['id_empleado'])) {
        $modelo->__set('id_empleado', $entrada['id_empleado']);
    }
}

/**
 * Edición restringida: SOLO los campos textuales/antropométricos del
 * diagnóstico. Beneficiario, empleado que atendió e insumos usados jamás
 * se aceptan desde el cliente (integridad de auditoría e inventario).
 */
function medicinaAsignarEntradaEdicion(MedicinaModel $modelo, array $entrada): void
{
    foreach (['id_patologia', 'estatura', 'peso', 'tipo_sangre',
              'motivo_visita', 'diagnostico', 'tratamiento', 'observaciones'] as $campo) {
        if (array_key_exists($campo, $entrada) && $entrada[$campo] !== '') {
            $modelo->__set($campo, $entrada[$campo]);
        }
    }
}
