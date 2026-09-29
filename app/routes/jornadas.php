<?php
// app/routes/jornadas.php
// Módulo Jornadas Médicas (id_modulo 11). Regla de las dos puertas:
// páginas bajo jornadas/..., API bajo api/jornadas/....

use App\Core\Router;

// ---------- PUERTA HTML (páginas) ----------
Router::get('jornadas/crear', function () {
    load_controller('jornadaController.php');
    showCrearJornada();
});

Router::get('jornadas/consultar', function () {
    load_controller('jornadaController.php');
    showConsultarJornadas();
});

// Detalle de una jornada: el {id} llega a $_GET['id'].
Router::get('jornadas/detalle/{id}', function () {
    load_controller('jornadaController.php');
    showDetalleJornada();
});

// ---------- PUERTA JSON (API) ----------
Router::get('api/jornadas/catalogos', function () {
    load_controller('jornadaController.php');
    apiCatalogosJornada();
});

Router::get('api/jornadas/listar', function () {
    load_controller('jornadaController.php');
    apiListarJornadas();
});

Router::get('api/jornadas/obtener/{id}', function () {
    load_controller('jornadaController.php');
    apiObtenerJornada();
});

Router::get('api/jornadas/stats', function () {
    load_controller('jornadaController.php');
    apiJornadasStats();
});

Router::get('api/jornadas/asistentes/{id}', function () {
    load_controller('jornadaController.php');
    apiAsistentesJornada();
});

Router::get('api/jornadas/diagnosticos/{id}', function () {
    load_controller('jornadaController.php');
    apiDiagnosticosAsistente();
});

Router::get('api/jornadas/buscar_persona', function () {
    load_controller('jornadaController.php');
    apiBuscarPersonaJornada();
});

Router::get('api/jornadas/insumos_disponibles', function () {
    load_controller('jornadaController.php');
    apiInsumosDisponiblesJornada();
});

Router::post('api/jornadas/crear', function () {
    load_controller('jornadaController.php');
    apiCrearJornada();
});

Router::post('api/jornadas/actualizar', function () {
    load_controller('jornadaController.php');
    apiActualizarJornada();
});

Router::post('api/jornadas/eliminar', function () {
    load_controller('jornadaController.php');
    apiEliminarJornada();
});

Router::post('api/jornadas/agregar_asistente', function () {
    load_controller('jornadaController.php');
    apiAgregarAsistente();
});

Router::post('api/jornadas/eliminar_asistente', function () {
    load_controller('jornadaController.php');
    apiEliminarAsistente();
});

Router::post('api/jornadas/agregar_diagnostico', function () {
    load_controller('jornadaController.php');
    apiAgregarDiagnostico();
});

Router::post('api/jornadas/actualizar_diagnostico', function () {
    load_controller('jornadaController.php');
    apiActualizarDiagnostico();
});

Router::post('api/jornadas/eliminar_diagnostico', function () {
    load_controller('jornadaController.php');
    apiEliminarDiagnostico();
});
