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

// ==================== PUERTA HTML (Páginas Web) ====================

function showReportesGeneral(): void
{
    Autorizacion::verificar('reportes', 'leer');
    require BASE_PATH . '/app/Views/reportes/general.php';
}

function showReportesMedicina(): void
{
    Autorizacion::verificar('reportes', 'leer');
    require BASE_PATH . '/app/Views/reportes/medicina.php';
}

function showReportesPsicologia(): void
{
    Autorizacion::verificar('reportes', 'leer');
    require BASE_PATH . '/app/Views/reportes/psicologia.php';
}

function showReportesOrientacion(): void
{
    Autorizacion::verificar('reportes', 'leer');
    require BASE_PATH . '/app/Views/reportes/orientacion.php';
}

function showReportesTrabajoSocial(): void
{
    Autorizacion::verificar('reportes', 'leer');
    require BASE_PATH . '/app/Views/reportes/trabajo_social.php';
}

function showReportesDiscapacidad(): void
{
    Autorizacion::verificar('reportes', 'leer');
    require BASE_PATH . '/app/Views/reportes/discapacidad.php';
}

function showReportesReferencias(): void
{
    Autorizacion::verificar('reportes', 'leer');
    require BASE_PATH . '/app/Views/reportes/referencias.php';
}

function showReportesJornadas(): void
{
    Autorizacion::verificar('reportes', 'leer');
    require BASE_PATH . '/app/Views/reportes/jornadas.php';
}

function showReportesMobiliario(): void
{
    Autorizacion::verificar('reportes', 'leer');
    require BASE_PATH . '/app/Views/reportes/mobiliario.php';
}

function showReportesTransporte(): void
{
    Autorizacion::verificar('reportes', 'leer');
    require BASE_PATH . '/app/Views/reportes/transporte.php';
}


// ==================== PUERTA JSON (Endpoints API) ====================

function obtenerReporteGeneralData(): void
{
    Autorizacion::verificar('reportes', 'leer');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteGeneral');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte estadístico general.');
    Respuesta::exito($datos);
}

function obtenerReporteMedicinaData(): void
{
    Autorizacion::verificar('reportes', 'leer');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteMedicina');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Medicina.');
    Respuesta::exito($datos);
}

function obtenerReportePsicologiaData(): void
{
    Autorizacion::verificar('reportes', 'leer');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reportePsicologia');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Psicología.');
    Respuesta::exito($datos);
}

function obtenerReporteOrientacionData(): void
{
    Autorizacion::verificar('reportes', 'leer');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteOrientacion');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Orientación.');
    Respuesta::exito($datos);
}

function obtenerReporteTrabajoSocialData(): void
{
    Autorizacion::verificar('reportes', 'leer');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteTrabajoSocial');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Trabajo Social.');
    Respuesta::exito($datos);
}

function obtenerReporteDiscapacidadData(): void
{
    Autorizacion::verificar('reportes', 'leer');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteDiscapacidad');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Discapacidad.');
    Respuesta::exito($datos);
}

function obtenerReporteReferenciasData(): void
{
    Autorizacion::verificar('reportes', 'leer');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteReferencias');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Referencias.');
    Respuesta::exito($datos);
}

function obtenerReporteJornadasData(): void
{
    Autorizacion::verificar('reportes', 'leer');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteJornadas');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Jornadas Médicas.');
    Respuesta::exito($datos);
}

function obtenerReporteMobiliarioData(): void
{
    Autorizacion::verificar('reportes', 'leer');
    $modelo = new ReportesModel();
    reportesAplicarFiltros($modelo);
    $datos = $modelo->manejarAccion('reporteMobiliario');
    Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Mobiliario y Equipos.');
    Respuesta::exito($datos);
}

function obtenerReporteTransporteData(): void
{
    Autorizacion::verificar('reportes', 'leer');
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
    Autorizacion::verificar('reportes', 'leer');
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
    Autorizacion::verificar('reportes', 'leer');
    $modelo = new ReportesModel();
    $datos = $modelo->manejarAccion('catalogos');
    Respuesta::exito($datos);
}
