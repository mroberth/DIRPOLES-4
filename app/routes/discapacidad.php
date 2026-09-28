<?php

use App\Core\Router;

// ---------- Páginas (puerta HTML) ----------

Router::get('discapacidad/crear', function () {
    load_controller('discapacidadController.php');
    showCrearDiscapacidad();
});

Router::get('discapacidad/consultar', function () {
    load_controller('discapacidadController.php');
    showConsultarDiscapacidad();
});

// ---------- Documentos (tercera puerta PDF: neither HTML nor JSON) ----------

Router::get('discapacidad/constancia/{id}', function () {
    load_controller('discapacidadController.php');
    generarConstanciaDiscapacidad();
});

Router::get('discapacidad/referencia/{id}', function () {
    load_controller('discapacidadController.php');
    generarReferenciaDiscapacidad();
});

// ---------- APIs (puerta JSON) ----------

Router::get('api/discapacidad/catalogos', function () {
    load_controller('discapacidadController.php');
    apiCatalogosDiscapacidad();
});

Router::get('api/discapacidad/stats', function () {
    load_controller('discapacidadController.php');
    apiStatsDiscapacidad();
});

Router::post('api/discapacidad/crear', function () {
    load_controller('discapacidadController.php');
    apiCrearDiscapacidad();
});

Router::get('api/discapacidad/listar', function () {
    load_controller('discapacidadController.php');
    apiListarDiscapacidad();
});

Router::get('api/discapacidad/obtener/{id}', function () {
    load_controller('discapacidadController.php');
    apiObtenerDiscapacidad();
});

Router::post('api/discapacidad/actualizar', function () {
    load_controller('discapacidadController.php');
    apiActualizarDiscapacidad();
});

Router::post('api/discapacidad/eliminar', function () {
    load_controller('discapacidadController.php');
    apiEliminarDiscapacidad();
});
