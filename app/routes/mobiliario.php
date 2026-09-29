<?php
// app/routes/mobiliario.php
// Módulo Mobiliario (id_modulo 12). Regla de las dos puertas:
// páginas bajo mobiliario/..., API bajo api/mobiliario/....
// Tres sub-flujos: mobiliario, equipos y fichas técnicas.

use App\Core\Router;

// ---------- PUERTA HTML (páginas) ----------
Router::get('mobiliario/crear', function () {
    load_controller('mobiliarioController.php');
    showCrearMobiliario();
});

Router::get('mobiliario/consultar', function () {
    load_controller('mobiliarioController.php');
    showConsultarMobiliario();
});

// ---------- PUERTA JSON (API) · lecturas ----------
Router::get('api/mobiliario/catalogos', function () {
    load_controller('mobiliarioController.php');
    apiCatalogosMobiliario();
});

Router::get('api/mobiliario/listar_mobiliario', function () {
    load_controller('mobiliarioController.php');
    apiListarMobiliario();
});

Router::get('api/mobiliario/listar_equipos', function () {
    load_controller('mobiliarioController.php');
    apiListarEquipos();
});

Router::get('api/mobiliario/listar_fichas', function () {
    load_controller('mobiliarioController.php');
    apiListarFichas();
});

Router::get('api/mobiliario/historial', function () {
    load_controller('mobiliarioController.php');
    apiHistorialMobiliario();
});

// El Router inyecta {tipo} y {id} en $_GET.
Router::get('api/mobiliario/obtener/{tipo}/{id}', function () {
    load_controller('mobiliarioController.php');
    apiObtenerMobiliario();
});

Router::get('api/mobiliario/stats', function () {
    load_controller('mobiliarioController.php');
    apiMobiliarioStats();
});

Router::get('api/mobiliario/items_disponibles', function () {
    load_controller('mobiliarioController.php');
    apiItemsDisponibles();
});

// ---------- PUERTA JSON (API) · creaciones ----------
Router::post('api/mobiliario/crear_mobiliario', function () {
    load_controller('mobiliarioController.php');
    apiCrearMobiliario();
});

Router::post('api/mobiliario/crear_equipo', function () {
    load_controller('mobiliarioController.php');
    apiCrearEquipo();
});

Router::post('api/mobiliario/crear_ficha', function () {
    load_controller('mobiliarioController.php');
    apiCrearFicha();
});

// ---------- PUERTA JSON (API) · ediciones ----------
Router::post('api/mobiliario/actualizar_mobiliario', function () {
    load_controller('mobiliarioController.php');
    apiActualizarMobiliario();
});

Router::post('api/mobiliario/actualizar_equipo', function () {
    load_controller('mobiliarioController.php');
    apiActualizarEquipo();
});

Router::post('api/mobiliario/actualizar_ficha', function () {
    load_controller('mobiliarioController.php');
    apiActualizarFicha();
});

// ---------- PUERTA JSON (API) · bajas y borrados ----------
Router::post('api/mobiliario/reubicar', function () {
    load_controller('mobiliarioController.php');
    apiReubicarItem();
});

Router::post('api/mobiliario/baja', function () {
    load_controller('mobiliarioController.php');
    apiBajaItem();
});

Router::post('api/mobiliario/eliminar_mobiliario', function () {
    load_controller('mobiliarioController.php');
    apiEliminarMobiliario();
});

Router::post('api/mobiliario/eliminar_equipo', function () {
    load_controller('mobiliarioController.php');
    apiEliminarEquipo();
});

Router::post('api/mobiliario/eliminar_ficha', function () {
    load_controller('mobiliarioController.php');
    apiEliminarFicha();
});

// ---------- PUERTA JSON (API) · validaciones en vivo ----------
Router::post('api/mobiliario/validar_serial', function () {
    load_controller('mobiliarioController.php');
    apiValidarSerial();
});

Router::post('api/mobiliario/validar_ficha_empleado', function () {
    load_controller('mobiliarioController.php');
    apiValidarFichaEmpleado();
});
