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

    $idTipo = (int) ($_SESSION['id_tipo_empleado'] ?? 0);
    $tipo = mb_strtolower(trim((string) ($_SESSION['tipo_empleado'] ?? '')));
    $esAdmin = ($idTipo === 6 || $idTipo === 10 || str_contains($tipo, 'administrador') || str_contains($tipo, 'superusuario'));

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

/**
 * Llama al microservicio DIRPOLES-IA y devuelve ['informe_markdown' => ..., 'meta' => ...].
 * La X-API-Key y el id_empleado se inyectan AQUÍ (server-side): el navegador
 * nunca ve la clave ni decide la visibilidad de datos.
 */
function reportesIaGenerar(string $modulo, string $intencion, array $filtros, ?string $observacion): array
{
    $urlRaw = $_ENV['DIRPOLES_IA_URL'] ?? $_SERVER['DIRPOLES_IA_URL'] ?? getenv('DIRPOLES_IA_URL') ?: 'http://127.0.0.1:8000';
    $url = rtrim((string) $urlRaw, '/');
    $clave = (string) ($_ENV['DIRPOLES_IA_API_KEY'] ?? $_SERVER['DIRPOLES_IA_API_KEY'] ?? getenv('DIRPOLES_IA_API_KEY') ?: '');
    if ($clave === '') {
        throw App\Core\ExcepcionApi::errorInterno('Servicio de IA no configurado (falta DIRPOLES_IA_API_KEY).');
    }


    // Visibilidad: admin ve todo; el resto solo SUS registros (espejo de filtroEmpleado).
    $esAdmin = isset($_SESSION['tipo_empleado']) &&
        (stripos($_SESSION['tipo_empleado'], 'administrador') !== false ||
         stripos($_SESSION['tipo_empleado'], 'superusuario') !== false);

    $filtrosLimpios = [];
    foreach ($filtros as $k => $v) {
        if ($v !== null && $v !== '') {
            if ($k === 'pnf' || $k === 'servicio_destino' || $k === 'limit') {
                $filtrosLimpios[$k] = (int) $v;
            } else {
                $filtrosLimpios[$k] = $v;
            }
        }
    }

    $payload = [
        'modulo' => $modulo,
        'intencion' => $intencion,
        'filtros' => empty($filtrosLimpios) ? (object) [] : $filtrosLimpios,
        'observacion_usuario' => $observacion ?: null,
        'id_empleado' => $esAdmin ? null : (int) ($_SESSION['id_empleado'] ?? 0),
    ];


    $ch = curl_init($url . '/api/v1/reportes/generar');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_NOPROXY => '*',

        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . $clave,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    $cuerpo = curl_exec($ch);
    $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $fallo = curl_error($ch);
    curl_close($ch);

    if ($cuerpo === false) {
        throw App\Core\ExcepcionApi::errorInterno('Servicio de IA no disponible: ' . $fallo);
    }
    $datos = json_decode($cuerpo, true);
    if ($codigo === 200 && ($datos['exito'] ?? false) === true) {
        return [
            'informe_markdown' => $datos['informe_markdown'],
            'meta' => $datos['meta'],
        ];
    }

    // Mapear los códigos del microservicio a mensajes del monolito (§8).
    $detalle = $datos['detail'] ?? null;
    if ($codigo === 401) {
        throw App\Core\ExcepcionApi::errorInterno('Servicio de IA no configurado.');
    }
    if ($codigo === 422 && is_array($detalle)) {
        $msg = $detalle[0]['msg'] ?? 'Datos inválidos para el informe.';
        throw App\Core\ExcepcionApi::validacion('El informe no pudo generarse: ' . $msg);
    }
    if ($codigo === 503) {
        $msg503 = is_array($detalle)
            ? ($detalle['detalle'] ?? ($detalle['error'] ?? 'Datos no disponibles temporalmente para el informe de IA.'))
            : (is_string($detalle) && $detalle !== '' ? $detalle : 'Datos no disponibles temporalmente para el informe de IA.');
        throw App\Core\ExcepcionApi::errorInterno((string) $msg503);
    }
    throw App\Core\ExcepcionApi::errorInterno('El servicio de IA respondió un error (' . $codigo . ').');
}

/**
 * POST api/reportes/ia/informe
 * Body del navegador: { modulo, intencion, filtros, observacion_usuario }
 * (id_empleado e X-API-Key NO vienen del navegador: los agrega reportesIaGenerar).
 */
function apiReportesIaInforme(): void
{
    Autorizacion::verificar('reportes', 'leer');

    $entrada = json_decode(file_get_contents('php://input'), true);
    if (!is_array($entrada)) {
        throw App\Core\ExcepcionApi::jsonInvalido();
    }

    $modulo = (string) ($entrada['modulo'] ?? '');
    // 'general' es solo administrador (misma regla que la vista tradicional).
    if ($modulo === 'general') {
        verificarAccesoReporte('', true);
    } elseif ($modulo !== '') {
        $moduloEsp = match ($modulo) {
            'trabajo_social' => 'trabajo-social',
            default => $modulo,
        };
        verificarAccesoReporte($moduloEsp);
    }

    $resultado = reportesIaGenerar(
        $modulo,
        (string) ($entrada['intencion'] ?? ''),
        is_array($entrada['filtros'] ?? null) ? $entrada['filtros'] : [],
        isset($entrada['observacion_usuario']) ? (string) $entrada['observacion_usuario'] : null
    );

    // Auditoría: misma acción 'Lectura' que los endpoints de reportes.
    Bitacora::registrar('Reportes', 'Lectura', 'Generó informe de IA: ' . $modulo);

    Respuesta::exito($resultado);
}

