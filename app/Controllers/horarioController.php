<?php
// app/Controllers/horarioController.php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\HorarioModel;

function verificarAdministradorHorarios(): void
{
    $tipo = $_SESSION['tipo_empleado'] ?? '';
    if (!in_array($tipo, ['Administrador', 'Superusuario'], true)) {
        throw \App\Core\ExcepcionApi::accesoDenegado('Solo el Administrador puede gestionar horarios.');
    }
}

function entradaHorario(): array
{
    $json = json_decode(file_get_contents('php://input'), true);
    return is_array($json) ? $json : $_POST;
}

function contextoHorario(HorarioModel $modelo, array $entrada = []): void
{
    if (isset($entrada['id_horario'])) $modelo->__set('id_horario', $entrada['id_horario']);
    if (isset($entrada['id_empleado'])) $modelo->__set('id_empleado', $entrada['id_empleado']);
    if (isset($entrada['dia_semana'])) $modelo->__set('dia_semana', $entrada['dia_semana']);
    if (isset($entrada['hora_inicio'])) $modelo->__set('hora_inicio', $entrada['hora_inicio']);
    if (isset($entrada['hora_fin'])) $modelo->__set('hora_fin', $entrada['hora_fin']);
}

function showConsultarHorarios(): void
{
    Autorizacion::verificar('horarios', 'leer');
    verificarAdministradorHorarios();
    require_once BASE_PATH . '/app/Views/horarios/consultar.php';
}

function showCrearHorario(): void
{
    Autorizacion::verificar('horarios', 'crear');
    verificarAdministradorHorarios();
    require_once BASE_PATH . '/app/Views/horarios/crear.php';
}

function apiListarHorarios(): void
{
    Autorizacion::verificar('horarios', 'leer');
    verificarAdministradorHorarios();
    $modelo = new HorarioModel();
    Respuesta::exito($modelo->manejarAccion('listar'));
}

function apiObtenerHorario(): void
{
    Autorizacion::verificar('horarios', 'leer');
    verificarAdministradorHorarios();
    $modelo = new HorarioModel();
    $modelo->__set('id_horario', (int) ($_GET['id'] ?? 0));
    Respuesta::exito($modelo->manejarAccion('obtener'));
}

function apiPsicologosHorario(): void
{
    Autorizacion::verificar('horarios', 'leer');
    verificarAdministradorHorarios();
    $modelo = new HorarioModel();
    Respuesta::exito($modelo->manejarAccion('psicologos'));
}

function apiStatsHorarios(): void
{
    Autorizacion::verificar('horarios', 'leer');
    verificarAdministradorHorarios();
    $modelo = new HorarioModel();
    Respuesta::exito($modelo->manejarAccion('stats'));
}

function apiCrearHorario(): void
{
    Autorizacion::verificar('horarios', 'crear');
    verificarAdministradorHorarios();
    $modelo = new HorarioModel();
    contextoHorario($modelo, entradaHorario());
    $nuevo = $modelo->manejarAccion('crear');
    Bitacora::registrar('Horarios', 'Registro', 'Registró un horario para un psicólogo.');
    Respuesta::exito($nuevo, 201);
}

function apiActualizarHorario(): void
{
    Autorizacion::verificar('horarios', 'editar');
    verificarAdministradorHorarios();
    $modelo = new HorarioModel();
    contextoHorario($modelo, entradaHorario());
    $actualizado = $modelo->manejarAccion('actualizar');
    Bitacora::registrar('Horarios', 'Actualización', 'Actualizó el horario de un psicólogo.');
    Respuesta::exito($actualizado);
}

function apiEliminarHorario(): void
{
    Autorizacion::verificar('horarios', 'eliminar');
    verificarAdministradorHorarios();
    $modelo = new HorarioModel();
    contextoHorario($modelo, entradaHorario());
    $eliminado = $modelo->manejarAccion('eliminar');
    Bitacora::registrar('Horarios', 'Eliminación', 'Eliminó el horario de un psicólogo.');
    Respuesta::exito($eliminado);
}
