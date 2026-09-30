<?php
/**
 * app/routes/reportes.php
 * ---------------------------------------------------------------
 * Rutas para el módulo de Reportes Estadísticos (Módulo 15).
 * Cumple con la Regla de las Dos Puertas:
 * - HTML: reportes/* -> renderizado de vistas
 * - JSON: api/reportes/* -> endpoints de datos estadísticos
 */

use App\Core\Router;

// ==================== PUERTA HTML (Páginas de Reportes) ====================

Router::get('reportes/general', function () {
    load_controller('reportesController.php');
    showReportesGeneral();
});

Router::get('reportes/medicina', function () {
    load_controller('reportesController.php');
    showReportesMedicina();
});

Router::get('reportes/psicologia', function () {
    load_controller('reportesController.php');
    showReportesPsicologia();
});

Router::get('reportes/orientacion', function () {
    load_controller('reportesController.php');
    showReportesOrientacion();
});

Router::get('reportes/trabajo-social', function () {
    load_controller('reportesController.php');
    showReportesTrabajoSocial();
});

Router::get('reportes/discapacidad', function () {
    load_controller('reportesController.php');
    showReportesDiscapacidad();
});

Router::get('reportes/referencias', function () {
    load_controller('reportesController.php');
    showReportesReferencias();
});

Router::get('reportes/jornadas', function () {
    load_controller('reportesController.php');
    showReportesJornadas();
});

Router::get('reportes/mobiliario', function () {
    load_controller('reportesController.php');
    showReportesMobiliario();
});

Router::get('reportes/transporte', function () {
    load_controller('reportesController.php');
    showReportesTransporte();
});


// ==================== PUERTA JSON (Endpoints API Data) ====================

Router::get('api/reportes/general', function () {
    load_controller('reportesController.php');
    obtenerReporteGeneralData();
});

Router::get('api/reportes/medicina', function () {
    load_controller('reportesController.php');
    obtenerReporteMedicinaData();
});

Router::get('api/reportes/psicologia', function () {
    load_controller('reportesController.php');
    obtenerReportePsicologiaData();
});

Router::get('api/reportes/orientacion', function () {
    load_controller('reportesController.php');
    obtenerReporteOrientacionData();
});

Router::get('api/reportes/trabajo-social', function () {
    load_controller('reportesController.php');
    obtenerReporteTrabajoSocialData();
});

Router::get('api/reportes/discapacidad', function () {
    load_controller('reportesController.php');
    obtenerReporteDiscapacidadData();
});

Router::get('api/reportes/referencias', function () {
    load_controller('reportesController.php');
    obtenerReporteReferenciasData();
});

Router::get('api/reportes/jornadas', function () {
    load_controller('reportesController.php');
    obtenerReporteJornadasData();
});

Router::get('api/reportes/mobiliario', function () {
    load_controller('reportesController.php');
    obtenerReporteMobiliarioData();
});

Router::get('api/reportes/transporte', function () {
    load_controller('reportesController.php');
    obtenerReporteTransporteData();
});

// Estadísticas de las tarjetas de cada reporte (?reporte=<clave> + filtros)
Router::get('api/reportes/stats', function () {
    load_controller('reportesController.php');
    obtenerReporteStats();
});

// Catálogos para los selects (PNF, servicios, estados de cita)
Router::get('api/reportes/catalogos', function () {
    load_controller('reportesController.php');
    obtenerReportesCatalogos();
});
