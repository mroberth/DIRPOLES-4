<?php
// app/routes/empleados.php

use App\Core\Router;

// ---------- PUERTA HTML (páginas) ----------
Router::get('empleados/crear', function () {
    load_controller('empleadoController.php');
    showCrearEmpleado();
});

Router::get('empleados/consultar', function () {
    load_controller('empleadoController.php');
    showConsultarEmpleados();
});

// ---------- PUERTA JSON (API) ----------
Router::get('api/empleados/tipos', function () {
    load_controller('empleadoController.php');
    apiTiposEmpleado();
});

Router::get('api/empleados/listar', function () {
    load_controller('empleadoController.php');
    apiListarEmpleados();
});

// El Router inyecta {id} en $_GET['id'].
Router::get('api/empleados/obtener/{id}', function () {
    load_controller('empleadoController.php');
    apiObtenerEmpleado();
});

Router::post('api/empleados/actualizar', function () {
    load_controller('empleadoController.php');
    apiActualizarEmpleado();
});

Router::post('api/empleados/eliminar', function () {
    load_controller('empleadoController.php');
    apiEliminarEmpleado();
});

Router::get('api/empleados/stats', function () {
    load_controller('empleadoController.php');
    apiEmpleadoStats();
});

Router::post('api/empleados/validar_cedula', function () {
    load_controller('empleadoController.php');
    apiValidarCedula();
});

Router::post('api/empleados/validar_correo', function () {
    load_controller('empleadoController.php');
    apiValidarCorreo();
});

Router::post('api/empleados/validar_telefono', function () {
    load_controller('empleadoController.php');
    apiValidarTelefono();
});

Router::post('api/empleados/crear', function () {
    load_controller('empleadoController.php');
    apiCrearEmpleado();
});