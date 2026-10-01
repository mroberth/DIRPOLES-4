<?php
/**
 * app/Controllers/reportesController.php
 * ---------------------------------------------------------------
 * Controlador del módulo de Reportes Estadísticos (Módulo 15).
 * Cumple estrictamente con las Reglas del Proyecto:
 * - SOLO funciones públicas (cero declaración de clases).
 * - Puerta HTML fuera de api/* (renderiza plantillas).
 * - Puerta JSON bajo api/* (Respuesta::exito / ExcepcionApi).
 *
 * Los endpoints JSON aceptan filtros server-side por query params
 * (fecha_inicio, fecha_fin, genero, pnf, area, estado, ...), de modo que
 * el futuro microservicio IA consuma los mismos datos que la interfaz.
 */

use App\Core\Autorizacion;
use App\Core\Respuesta;
use App\Core\Bitacora;
use App\Models\ReportesModel;

/**
 * Aplica al modelo los filtros de query params conocidos.
 * Solo se itera una lista blanca: los parámetros ajenos se ignoran y un
 * valor inválido dispara ExcepcionApi 400 desde __set del modelo.
 */
function reportesAplicarFiltros(ReportesModel $modelo): void
{
    $claves = [
        'fecha_inicio', 'fecha_fin', 'genero', 'pnf', 'servicio_destino',
        'area', 'estado', 'tipo_consulta', 'submodulo', 'grado',
        'tipo_discapacidad', 'tipo_bien', 'tipo_vehiculo', 'seccion_transporte',
        'reporte', 'limit',
    ];

    foreach ($claves as $clave) {
        if (isset($_GET[$clave]) && is_scalar($_GET[$clave])) {
            $modelo->$clave = $_GET[$clave];
        }
    }
}

/**
 * Verifica permiso de 'leer' en reportes, y además si el reporte requiere
 * ser Administrador o requiere permiso de 'leer' en un módulo de especialidad.
 */
function verificarAccesoReporte(string $moduloEspecialidad = '', bool $soloAdmin = false): void
{
    Autorizacion::verificar('reportes', 'leer');

    $esAdmin = (isset($_SESSION['tipo_empleado']) &&
        (strpos(strtolower($_SESSION['tipo_empleado']), 'administrador') !== false ||
         strpos(strtolower($_SESSION['tipo_empleado']), 'superusuario') !== false));

    if ($esAdmin) {
        return;
    }

    if ($soloAdmin) {
        throw App\Core\ExcepcionApi::accesoDenegado('Este reporte solo puede ser consultado por administradores.');
    }

    if ($moduloEspecialidad !== '' && !Autorizacion::tiene($moduloEspecialidad, 'leer')) {
        throw App\Core\ExcepcionApi::accesoDenegado("No tienes permiso para ver el reporte de {$moduloEspecialidad}.");
    }
}

// ==================== PUERTA HTML (Páginas Web) ====================

function showReportesGeneral(): void
{
    verificarAccesoReporte('', true);
    require BASE_PATH . '/app/Views/reportes/general.php';
}

function showReportesMedicina(): void
{
    verificarAccesoReporte('medicina');
    require BASE_PATH . '/app/Views/reportes/medicina.php';
}

function showReportesPsicologia(): void
{
    verificarAccesoReporte('psicologia');
    require BASE_PATH . '/app/Views/reportes/psicologia.php';
}

function showReportesOrientacion(): void
{
    verificarAccesoReporte('orientacion');
    require BASE_PATH . '/app/Views/reportes/orientacion.php';
}

function showReportesTrabajoSocial(): void
{
    verificarAccesoReporte('trabajo-social');
    require BASE_PATH . '/app/Views/reportes/trabajo_social.php';
}

function showReportesDiscapacidad(): void
{
    verificarAccesoReporte('discapacidad');
    require BASE_PATH . '/app/Views/reportes/discapacidad.php';
}

function showReportesReferencias(): void
{
    verificarAccesoReporte();
    require BASE_PATH . '/app/Views/reportes/referencias.php';
}

function showReportesJornadas(): void
{
    verificarAccesoReporte('jornadas');
    require BASE_PATH . '/app/Views/reportes/jornadas.php';
}

function showReportesMobiliario(): void
{
    verificarAccesoReporte('mobiliario');
    require BASE_PATH . '/app/Views/reportes/mobiliario.php';
}

function showReportesTransporte(): void
{
    verificarAccesoReporte('transporte');
    require BASE_PATH . '/app/Views/reportes/transporte.php';
}


// ==================== PUERTA JSON (Endpoints API) ====================

function obtenerReporteGeneralData(): void
{
    verificarAccesoReporte('', true);
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteGeneral');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte estadístico general.');
    Respuesta::exito($datos);
}

function obtenerReporteMedicinaData(): void
{
    verificarAccesoReporte('medicina');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteMedicina');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Medicina.');
    Respuesta::exito($datos);
}

function obtenerReportePsicologiaData(): void
{
    verificarAccesoReporte('psicologia');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reportePsicologia');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Psicología.');
    Respuesta::exito($datos);
}

function obtenerReporteOrientacionData(): void
{
    verificarAccesoReporte('orientacion');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteOrientacion');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Orientación.');
    Respuesta::exito($datos);
}

function obtenerReporteTrabajoSocialData(): void
{
    verificarAccesoReporte('trabajo-social');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteTrabajoSocial');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Trabajo Social.');
    Respuesta::exito($datos);
}

function obtenerReporteDiscapacidadData(): void
{
    verificarAccesoReporte('discapacidad');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteDiscapacidad');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Discapacidad.');
    Respuesta::exito($datos);
}

function obtenerReporteReferenciasData(): void
{
    verificarAccesoReporte();
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteReferencias');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Referencias.');
    Respuesta::exito($datos);
}

function obtenerReporteJornadasData(): void
{
    verificarAccesoReporte('jornadas');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteJornadas');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Jornadas Médicas.');
    Respuesta::exito($datos);
}

function obtenerReporteMobiliarioData(): void
{
    verificarAccesoReporte('mobiliario');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteMobiliario');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Mobiliario y Equipos.');
    Respuesta::exito($datos);
}

function obtenerReporteTransporteData(): void
{
    verificarAccesoReporte('transporte');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteTransporte');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Transporte.');
    Respuesta::exito($datos);
}

/**
 * Estadísticas de las tarjetas [data-stat] de cada reporte.
 * Acepta los mismos filtros que el reporte + ?reporte=<clave>.
 */
function obtenerReporteStats(): void
{
    $reporte = $_GET['reporte'] ?? '';
    match ($reporte) {
        'general' => verificarAccesoReporte('', true),
        'medicina' => verificarAccesoReporte('medicina'),
        'psicologia' => verificarAccesoReporte('psicologia'),
        'orientacion' => verificarAccesoReporte('orientacion'),
        'trabajo_social' => verificarAccesoReporte('trabajo-social'),
        'discapacidad' => verificarAccesoReporte('discapacidad'),
        'jornadas' => verificarAccesoReporte('jornadas'),
        'mobiliario' => verificarAccesoReporte('mobiliario'),
        'transporte' => verificarAccesoReporte('transporte'),
        default => verificarAccesoReporte(),
    };
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('stats');
    Bitacora::registrar('Reportes', 'Lectura', 'Consultó las estadísticas de un reporte.');
    Respuesta::exito($datos);
}

/**
 * Catálogos para llenar los selects (PNF, servicios, estados de cita).
 * Sin bitácora: es una carga auxiliar de la interfaz, no una consulta de datos.
 */
function obtenerReportesCatalogos(): void
{
    verificarAccesoReporte();
    $modelo = new ReportesModel();
    $datos = $modelo->manejarAccion('catalogos');
    Respuesta::exito($datos);
}
