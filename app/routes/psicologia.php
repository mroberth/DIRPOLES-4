<?php

use App\Core\Router;

Router::get('psicologia/crear', function () {
    load_controller('psicologiaController.php');
    showCrearPsicologia();
});

Router::get('psicologia/consultar', function () {
    load_controller('psicologiaController.php');
    showConsultarPsicologia();
});

// ---------- Documentos (tercera puerta PDF: neither HTML nor JSON) ----------

Router::get('psicologia/constancia/{id}', function () {
    load_controller('psicologiaController.php');
    generarConstanciaPsicologia();
});

Router::get('psicologia/referencia/{id}', function () {
    load_controller('psicologiaController.php');
    generarReferenciaPsicologia();
});

// ---------- APIs (puerta JSON) ----------

Router::get('api/psicologia/listar', function () {
    load_controller('psicologiaController.php');
    apiListarPsicologia();
});

Router::get('api/psicologia/catalogos', function () {
    load_controller('psicologiaController.php');
    apiCatalogosPsicologia();
});

Router::get('api/psicologia/stats', function () {
    load_controller('psicologiaController.php');
    apiStatsPsicologia();
});

Router::get('api/psicologia/obtener/{id}', function () {
    load_controller('psicologiaController.php');
    apiObtenerPsicologia();
});

Router::post('api/psicologia/crear', function () {
    load_controller('psicologiaController.php');
    apiCrearPsicologia();
});

Router::post('api/psicologia/actualizar', function () {
    load_controller('psicologiaController.php');
    apiActualizarPsicologia();
});

Router::post('api/psicologia/eliminar', function () {
    load_controller('psicologiaController.php');
    apiEliminarPsicologia();
});