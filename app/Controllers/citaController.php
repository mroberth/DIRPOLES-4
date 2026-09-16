<?php
// app/Controllers/citaController.php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Notificador;
use App\Core\Respuesta;
use App\Models\CitaModel;

function contextoCita(CitaModel $modelo): void
{
    $modelo->__set('id_usuario', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado'] ?? '');
}

function entradaCita(): array
{
    $json = json_decode(file_get_contents('php://input'), true);
    return is_array($json) ? $json : $_POST;
}

function showConsultarCitas(): void
{
    Autorizacion::verificar('citas', 'leer');
    require_once BASE_PATH . '/app/Views/citas/consultar.php';
}

function showCrearCita(): void
{
    Autorizacion::verificar('citas', 'crear');
    require_once BASE_PATH . '/app/Views/citas/crear.php';
}

function apiListarCitas(): void
{
    Autorizacion::verificar('citas', 'leer');
    $modelo = new CitaModel();
    contextoCita($modelo);
    Respuesta::exito($modelo->manejarAccion('listar'));
}

function apiObtenerCita(): void
{
    Autorizacion::verificar('citas', 'leer');
    $modelo = new CitaModel();
    contextoCita($modelo);
    $modelo->__set('id_cita', (int) ($_GET['id'] ?? 0));
    Respuesta::exito($modelo->manejarAccion('obtener'));
}

function apiPsicologosCita(): void
{
    Autorizacion::verificar('citas', 'leer');
    $modelo = new CitaModel();
    contextoCita($modelo);
    Respuesta::exito($modelo->manejarAccion('psicologos'));
}

function apiBeneficiariosCita(): void
{
    Autorizacion::verificar('citas', 'leer');
    $modelo = new CitaModel();
    contextoCita($modelo);
    Respuesta::exito($modelo->manejarAccion('beneficiarios'));
}

function apiEstadosCita(): void
{
    Autorizacion::verificar('citas', 'leer');
    $modelo = new CitaModel();
    contextoCita($modelo);
    Respuesta::exito($modelo->manejarAccion('estados'));
}

function apiHorarioCita(): void
{
    Autorizacion::verificar('citas', 'leer');
    $modelo = new CitaModel();
    contextoCita($modelo);
    if (isset($_GET['id_empleado'])) {
        $modelo->__set('id_empleado', (int) $_GET['id_empleado']);
    }
    Respuesta::exito($modelo->manejarAccion('horario'));
}

function apiDisponibilidadCita(): void
{
    Autorizacion::verificar('citas', 'leer');
    $entrada = entradaCita();
    $modelo = new CitaModel();
    contextoCita($modelo);
    $modelo->__set('fecha', $entrada['fecha'] ?? '');
    $modelo->__set('hora', $entrada['hora'] ?? '');
    if (!empty($entrada['id_empleado'])) {
        $modelo->__set('id_empleado', $entrada['id_empleado']);
    }
    if (!empty($entrada['id_cita'])) {
        $modelo->__set('id_cita', $entrada['id_cita']);
    }
    Respuesta::exito($modelo->manejarAccion('disponibilidad'));
}

function apiStatsCitas(): void
{
    Autorizacion::verificar('citas', 'leer');
    $modelo = new CitaModel();
    contextoCita($modelo);
    Respuesta::exito($modelo->manejarAccion('stats'));
}

function apiCrearCita(): void
{
    Autorizacion::verificar('citas', 'crear');
    $entrada = entradaCita();
    $modelo = new CitaModel();
    contextoCita($modelo);
    $modelo->__set('id_beneficiario', $entrada['id_beneficiario'] ?? 0);
    $modelo->__set('fecha', $entrada['fecha'] ?? '');
    $modelo->__set('hora', $entrada['hora'] ?? '');
    if (($_SESSION['tipo_empleado'] ?? '') === 'Administrador'
        || ($_SESSION['tipo_empleado'] ?? '') === 'Superusuario') {
        $modelo->__set('id_empleado', $entrada['id_empleado'] ?? 0);
    }

    $nueva = $modelo->manejarAccion('crear');
    Bitacora::registrar('Citas', 'Registro', 'Registró una cita para un beneficiario.');
    if ($nueva['id_empleado'] !== (int) ($_SESSION['id_empleado'] ?? 0)) {
        Notificador::enviar($nueva['id_empleado'], 'Nueva cita asignada', 'citas/consultar', 'info');
    }
    Respuesta::exito($nueva, 201);
}

function apiActualizarCita(): void
{
    Autorizacion::verificar('citas', 'editar');
    $entrada = entradaCita();
    $modelo = new CitaModel();
    contextoCita($modelo);
    $modelo->__set('id_cita', $entrada['id_cita'] ?? 0);
    $modelo->__set('fecha', $entrada['fecha'] ?? '');
    $modelo->__set('hora', $entrada['hora'] ?? '');
    if (isset($entrada['estatus'])) {
        $modelo->__set('estatus', $entrada['estatus']);
    }
    if (($_SESSION['tipo_empleado'] ?? '') === 'Administrador'
        || ($_SESSION['tipo_empleado'] ?? '') === 'Superusuario') {
        $modelo->__set('id_empleado', $entrada['id_empleado'] ?? 0);
        $modelo->__set('id_beneficiario', $entrada['id_beneficiario'] ?? 0);
    }

    $actualizada = $modelo->manejarAccion('actualizar');
    Bitacora::registrar('Citas', 'Actualización', 'Actualizó una cita.');
    Respuesta::exito($actualizada);
}

function apiActualizarEstadoCita(): void
{
    Autorizacion::verificar('citas', 'editar');
    $entrada = entradaCita();
    $modelo = new CitaModel();
    contextoCita($modelo);
    $modelo->__set('id_cita', $entrada['id_cita'] ?? 0);
    $modelo->__set('estatus', $entrada['estatus'] ?? 0);
    $actualizada = $modelo->manejarAccion('actualizar_estado');
    Bitacora::registrar('Citas', 'Actualización', 'Actualizó el estado de una cita.');
    Respuesta::exito($actualizada);
}

function apiEliminarCita(): void
{
    Autorizacion::verificar('citas', 'eliminar');
    $entrada = entradaCita();
    $modelo = new CitaModel();
    contextoCita($modelo);
    $modelo->__set('id_cita', $entrada['id_cita'] ?? 0);
    $eliminada = $modelo->manejarAccion('eliminar');
    Bitacora::registrar('Citas', 'Eliminación', 'Eliminó una cita.');
    Respuesta::exito($eliminada);
}
