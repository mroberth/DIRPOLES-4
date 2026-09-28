<?php

use App\Core\Router;

// ---------- Páginas (puerta HTML) ----------

Router::get('orientacion/crear', function () {
    load_controller('orientacionController.php');
    showCrearOrientacion();
});

Router::get('orientacion/consultar', function () {
    load_controller('orientacionController.php');
    showConsultarOrientacion();
});

// ---------- Documentos (tercera puerta PDF: neither HTML nor JSON) ----------

Router::get('orientacion/constancia/{id}', function () {
    load_controller('orientacionController.php');
    generarConstanciaOrientacion();
});

Router::get('orientacion/referencia/{id}', function () {
    load_controller('orientacionController.php');
    generarReferenciaOrientacion();
});

// ---------- APIs (puerta JSON) ----------

Router::get('api/orientacion/listar', function () {
    load_controller('orientacionController.php');
    apiListarOrientacion();
});

Router::get('api/orientacion/obtener/{id}', function () {
    load_controller('orientacionController.php');
    apiObtenerOrientacion();
});

Router::get('api/orientacion/catalogos', function () {
    load_controller('orientacionController.php');
    apiCatalogosOrientacion();
});

Router::get('api/orientacion/stats', function () {
    load_controller('orientacionController.php');
    apiStatsOrientacion();
});

Router::post('api/orientacion/crear', function () {
    load_controller('orientacionController.php');
    apiCrearOrientacion();
});

Router::post('api/orientacion/actualizar', function () {
    load_controller('orientacionController.php');
    apiActualizarOrientacion();
});

Router::post('api/orientacion/eliminar', function () {
    load_controller('orientacionController.php');
    apiEliminarOrientacion();
});
