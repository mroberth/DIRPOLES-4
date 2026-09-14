<?php
// app/routes/configuracion.php

use App\Core\Router;

// ---------- PUERTA HTML (páginas) ----------
Router::get('configuracion/crear', function () {
    load_controller('configuracionController.php');
    showCrearConfiguracion();
});

Router::get('configuracion/consultar', function () {
    load_controller('configuracionController.php');
    showConsultarConfiguracion();
});

// ---------- PUERTA JSON (API) ----------
Router::post('api/configuracion/crear', function () {
    load_controller('configuracionController.php');
    apiConfiguracionCrear();
});

Router::get('api/configuracion/listar', function () {
    load_controller('configuracionController.php');
    apiConfiguracionListar();
});

// El Router inyecta {catalogo} e {id} en $_GET.
Router::get('api/configuracion/obtener/{catalogo}/{id}', function () {
    load_controller('configuracionController.php');
    apiConfiguracionObtener();
});

Router::post('api/configuracion/actualizar', function () {
    load_controller('configuracionController.php');
    apiConfiguracionActualizar();
});

Router::post('api/configuracion/validar', function () {
    load_controller('configuracionController.php');
    apiConfiguracionValidar();
});
