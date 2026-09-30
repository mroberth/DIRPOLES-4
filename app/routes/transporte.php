<?php
/**
 * app/routes/transporte.php
 * ---------------------------------------------------------------
 * Rutas del módulo de Transporte (id_modulo = 13).
 */

use App\Core\Router;

// ==================== PUERTA HTML (Páginas) ====================

Router::get('transporte/crear', function () {
    load_controller('transporteController.php');
    showCrearTransporte();
});

Router::get('transporte/consultar', function () {
    load_controller('transporteController.php');
    showConsultarTransporte();
});

// ==================== PUERTA JSON (API) ====================

// Stats
Router::get('api/transporte/stats', function () {
    load_controller('transporteController.php');
    apiStatsTransporte();
});

// ---------- Rutas ----------
Router::get('api/transporte/rutas/listar', function () {
    load_controller('transporteController.php');
    apiListarRutas();
});

Router::post('api/transporte/rutas/crear', function () {
    load_controller('transporteController.php');
    apiCrearRuta();
});

Router::get('api/transporte/rutas/obtener/{id}', function () {
    load_controller('transporteController.php');
    apiObtenerRuta();
});

Router::post('api/transporte/rutas/actualizar', function () {
    load_controller('transporteController.php');
    apiActualizarRuta();
});

Router::post('api/transporte/rutas/eliminar', function () {
    load_controller('transporteController.php');
    apiEliminarRuta();
});

// ---------- Vehículos ----------
Router::get('api/transporte/vehiculos/listar', function () {
    load_controller('transporteController.php');
    apiListarVehiculos();
});

Router::post('api/transporte/vehiculos/crear', function () {
    load_controller('transporteController.php');
    apiCrearVehiculo();
});

Router::get('api/transporte/vehiculos/obtener/{id}', function () {
    load_controller('transporteController.php');
    apiObtenerVehiculo();
});

Router::post('api/transporte/vehiculos/actualizar', function () {
    load_controller('transporteController.php');
    apiActualizarVehiculo();
});

Router::post('api/transporte/vehiculos/eliminar', function () {
    load_controller('transporteController.php');
    apiEliminarVehiculo();
});

Router::post('api/transporte/vehiculos/validar_placa', function () {
    load_controller('transporteController.php');
    apiValidarPlacaVehiculo();
});

// ---------- Proveedores ----------
Router::get('api/transporte/proveedores/listar', function () {
    load_controller('transporteController.php');
    apiListarProveedores();
});

Router::post('api/transporte/proveedores/crear', function () {
    load_controller('transporteController.php');
    apiCrearProveedor();
});

Router::get('api/transporte/proveedores/obtener/{id}', function () {
    load_controller('transporteController.php');
    apiObtenerProveedor();
});

Router::post('api/transporte/proveedores/actualizar', function () {
    load_controller('transporteController.php');
    apiActualizarProveedor();
});

Router::post('api/transporte/proveedores/eliminar', function () {
    load_controller('transporteController.php');
    apiEliminarProveedor();
});

Router::post('api/transporte/proveedores/validar_documento', function () {
    load_controller('transporteController.php');
    apiValidarDocumentoProveedor();
});

Router::post('api/transporte/proveedores/validar_correo', function () {
    load_controller('transporteController.php');
    apiValidarCorreoProveedor();
});

Router::post('api/transporte/proveedores/validar_telefono', function () {
    load_controller('transporteController.php');
    apiValidarTelefonoProveedor();
});

// ---------- Repuestos ----------
Router::get('api/transporte/repuestos/listar', function () {
    load_controller('transporteController.php');
    apiListarRepuestos();
});

Router::post('api/transporte/repuestos/crear', function () {
    load_controller('transporteController.php');
    apiCrearRepuesto();
});

Router::get('api/transporte/repuestos/obtener/{id}', function () {
    load_controller('transporteController.php');
    apiObtenerRepuesto();
});

Router::post('api/transporte/repuestos/actualizar', function () {
    load_controller('transporteController.php');
    apiActualizarRepuesto();
});

Router::post('api/transporte/repuestos/eliminar', function () {
    load_controller('transporteController.php');
    apiEliminarRepuesto();
});

Router::post('api/transporte/repuestos/entrada', function () {
    load_controller('transporteController.php');
    apiEntradaRepuesto();
});

Router::post('api/transporte/repuestos/salida', function () {
    load_controller('transporteController.php');
    apiSalidaRepuesto();
});

Router::get('api/transporte/repuestos/historial', function () {
    load_controller('transporteController.php');
    apiHistorialRepuestos();
});

// ---------- Asignaciones ----------
Router::get('api/transporte/asignaciones/listar', function () {
    load_controller('transporteController.php');
    apiListarAsignaciones();
});

Router::get('api/transporte/asignaciones/opciones', function () {
    load_controller('transporteController.php');
    apiOpcionesAsignacion();
});

Router::post('api/transporte/asignaciones/crear', function () {
    load_controller('transporteController.php');
    apiCrearAsignacion();
});

Router::get('api/transporte/asignaciones/obtener/{id}', function () {
    load_controller('transporteController.php');
    apiObtenerAsignacion();
});

Router::post('api/transporte/asignaciones/actualizar', function () {
    load_controller('transporteController.php');
    apiActualizarAsignacion();
});

Router::post('api/transporte/asignaciones/eliminar', function () {
    load_controller('transporteController.php');
    apiEliminarAsignacion();
});

// ---------- Mantenimientos ----------
Router::get('api/transporte/mantenimientos/listar', function () {
    load_controller('transporteController.php');
    apiListarMantenimientos();
});

Router::post('api/transporte/mantenimientos/crear', function () {
    load_controller('transporteController.php');
    apiCrearMantenimiento();
});

Router::get('api/transporte/mantenimientos/obtener/{id}', function () {
    load_controller('transporteController.php');
    apiObtenerMantenimiento();
});

Router::post('api/transporte/mantenimientos/eliminar', function () {
    load_controller('transporteController.php');
    apiEliminarMantenimiento();
});
