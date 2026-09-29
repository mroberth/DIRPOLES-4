<?php
// app/routes/referencias.php
// Módulo Referencias (id_modulo 10). Regla de las dos puertas:
// páginas bajo referencias/..., API bajo api/referencias/....

use App\Core\Router;

// ---------- PUERTA HTML (páginas) ----------
Router::get('referencias/crear', function () {
    load_controller('referenciaController.php');
    showCrearReferencia();
});

Router::get('referencias/consultar', function () {
    load_controller('referenciaController.php');
    showConsultarReferencias();
});

// ---------- PUERTA JSON (API) ----------
Router::get('api/referencias/servicios', function () {
    load_controller('referenciaController.php');
    apiServiciosReferencia();
});

// Empleados activos de un servicio: ?id_servicio=N (query param).
Router::get('api/referencias/empleados', function () {
    load_controller('referenciaController.php');
    apiEmpleadosServicioReferencia();
});

Router::get('api/referencias/beneficiarios', function () {
    load_controller('referenciaController.php');
    apiBeneficiariosReferencia();
});

Router::get('api/referencias/listar', function () {
    load_controller('referenciaController.php');
    apiListarReferencias();
});

// El Router inyecta {id} en $_GET['id'].
Router::get('api/referencias/obtener/{id}', function () {
    load_controller('referenciaController.php');
    apiObtenerReferencia();
});

Router::get('api/referencias/stats', function () {
    load_controller('referenciaController.php');
    apiReferenciasStats();
});

Router::post('api/referencias/crear', function () {
    load_controller('referenciaController.php');
    apiCrearReferencia();
});

Router::post('api/referencias/aceptar', function () {
    load_controller('referenciaController.php');
    apiAceptarReferencia();
});

Router::post('api/referencias/rechazar', function () {
    load_controller('referenciaController.php');
    apiRechazarReferencia();
});

Router::post('api/referencias/eliminar', function () {
    load_controller('referenciaController.php');
    apiEliminarReferencia();
});
