<?php
// app/routes/beneficiarios.php

use App\Core\Router;

// ---------- PUERTA HTML (páginas) ----------
Router::get('beneficiarios/crear', function () {
    load_controller('beneficiarioController.php');
    showCrearBeneficiario();
});

Router::get('beneficiarios/consultar', function () {
    load_controller('beneficiarioController.php');
    showConsultarBeneficiarios();
});

// ---------- PUERTA JSON (API) ----------
Router::get('api/beneficiarios/pnfs', function () {
    load_controller('beneficiarioController.php');
    apiPnfs();
});

Router::get('api/beneficiarios/stats', function () {
    load_controller('beneficiarioController.php');
    apiBeneficiarioStats();
});

Router::get('api/beneficiarios/listar', function () {
    load_controller('beneficiarioController.php');
    apiListarBeneficiarios();
});

// El Router inyecta {id} en $_GET['id'].
Router::get('api/beneficiarios/obtener/{id}', function () {
    load_controller('beneficiarioController.php');
    apiObtenerBeneficiario();
});

Router::post('api/beneficiarios/validar_cedula', function () {
    load_controller('beneficiarioController.php');
    apiValidarCedulaBeneficiario();
});

Router::post('api/beneficiarios/validar_correo', function () {
    load_controller('beneficiarioController.php');
    apiValidarCorreoBeneficiario();
});

Router::post('api/beneficiarios/validar_telefono', function () {
    load_controller('beneficiarioController.php');
    apiValidarTelefonoBeneficiario();
});

Router::post('api/beneficiarios/crear', function () {
    load_controller('beneficiarioController.php');
    apiCrearBeneficiario();
});

Router::post('api/beneficiarios/actualizar', function () {
    load_controller('beneficiarioController.php');
    apiActualizarBeneficiario();
});

Router::post('api/beneficiarios/eliminar', function () {
    load_controller('beneficiarioController.php');
    apiEliminarBeneficiario();
});
