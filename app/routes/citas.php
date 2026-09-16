<?php
// app/routes/citas.php

use App\Core\Router;

Router::get('citas/consultar', function () {
    load_controller('citaController.php');
    showConsultarCitas();
});

Router::get('citas/crear', function () {
    load_controller('citaController.php');
    showCrearCita();
});

Router::get('api/citas/listar', function () {
    load_controller('citaController.php');
    apiListarCitas();
});

Router::get('api/citas/obtener/{id}', function () {
    load_controller('citaController.php');
    apiObtenerCita();
});

Router::get('api/citas/psicologos', function () {
    load_controller('citaController.php');
    apiPsicologosCita();
});

Router::get('api/citas/beneficiarios', function () {
    load_controller('citaController.php');
    apiBeneficiariosCita();
});

Router::get('api/citas/estados', function () {
    load_controller('citaController.php');
    apiEstadosCita();
});

Router::get('api/citas/horario', function () {
    load_controller('citaController.php');
    apiHorarioCita();
});

Router::get('api/citas/stats', function () {
    load_controller('citaController.php');
    apiStatsCitas();
});

Router::post('api/citas/disponibilidad', function () {
    load_controller('citaController.php');
    apiDisponibilidadCita();
});

Router::post('api/citas/crear', function () {
    load_controller('citaController.php');
    apiCrearCita();
});

Router::post('api/citas/actualizar', function () {
    load_controller('citaController.php');
    apiActualizarCita();
});

Router::post('api/citas/actualizar_estado', function () {
    load_controller('citaController.php');
    apiActualizarEstadoCita();
});

Router::post('api/citas/eliminar', function () {
    load_controller('citaController.php');
    apiEliminarCita();
});
