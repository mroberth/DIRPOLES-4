<?php
// app/routes/horarios.php

use App\Core\Router;

Router::get('horarios/consultar', function () {
    load_controller('horarioController.php');
    showConsultarHorarios();
});

Router::get('horarios/crear', function () {
    load_controller('horarioController.php');
    showCrearHorario();
});

Router::get('api/horarios/listar', function () {
    load_controller('horarioController.php');
    apiListarHorarios();
});

Router::get('api/horarios/obtener/{id}', function () {
    load_controller('horarioController.php');
    apiObtenerHorario();
});

Router::get('api/horarios/psicologos', function () {
    load_controller('horarioController.php');
    apiPsicologosHorario();
});

Router::get('api/horarios/stats', function () {
    load_controller('horarioController.php');
    apiStatsHorarios();
});

Router::post('api/horarios/crear', function () {
    load_controller('horarioController.php');
    apiCrearHorario();
});

Router::post('api/horarios/actualizar', function () {
    load_controller('horarioController.php');
    apiActualizarHorario();
});

Router::post('api/horarios/eliminar', function () {
    load_controller('horarioController.php');
    apiEliminarHorario();
});
