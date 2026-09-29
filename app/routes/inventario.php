<?php
// app/routes/inventario.php
// Módulo Inventario Médico (id_modulo 9). Regla de las dos puertas:
// páginas bajo inventario/..., API bajo api/inventario/....

use App\Core\Router;

// ---------- PUERTA HTML (páginas) ----------
Router::get('inventario/crear', function () {
    load_controller('inventarioController.php');
    showCrearInsumo();
});

Router::get('inventario/consultar', function () {
    load_controller('inventarioController.php');
    showConsultarInsumos();
});

// ---------- PUERTA JSON (API) ----------
Router::get('api/inventario/presentaciones', function () {
    load_controller('inventarioController.php');
    apiPresentacionesInsumo();
});

Router::get('api/inventario/listar', function () {
    load_controller('inventarioController.php');
    apiListarInsumos();
});

// El Router inyecta {id} en $_GET['id'].
Router::get('api/inventario/obtener/{id}', function () {
    load_controller('inventarioController.php');
    apiObtenerInsumo();
});

Router::get('api/inventario/stats', function () {
    load_controller('inventarioController.php');
    apiInventarioStats();
});

Router::get('api/inventario/movimientos', function () {
    load_controller('inventarioController.php');
    apiMovimientosInventario();
});

Router::post('api/inventario/crear', function () {
    load_controller('inventarioController.php');
    apiCrearInsumo();
});

Router::post('api/inventario/actualizar', function () {
    load_controller('inventarioController.php');
    apiActualizarInsumo();
});

Router::post('api/inventario/eliminar', function () {
    load_controller('inventarioController.php');
    apiEliminarInsumo();
});

Router::post('api/inventario/entrada', function () {
    load_controller('inventarioController.php');
    apiRegistrarEntradaInsumo();
});

Router::post('api/inventario/salida', function () {
    load_controller('inventarioController.php');
    apiRegistrarSalidaInsumo();
});

Router::post('api/inventario/validar_insumo', function () {
    load_controller('inventarioController.php');
    apiValidarInsumo();
});
