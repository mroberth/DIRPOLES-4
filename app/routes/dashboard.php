<?php
// app/routes/dashboard.php

use App\Core\Router;

// ==================== PUERTA JSON (API, prefijo api/) ====================

// Estadísticas del panel (según el rol de la sesión).
Router::get('api/dashboard/stats', function () {
    load_controller('dashboardController.php');
    apiDashboardStats();
});

// Calendario personal (solo eventos del usuario autenticado).
Router::get('api/calendario/eventos', function () {
    load_controller('dashboardController.php');
    apiCalendarioEventos();
});

Router::post('api/calendario/guardar', function () {
    load_controller('dashboardController.php');
    apiCalendarioGuardar();
});

Router::post('api/calendario/actualizar', function () {
    load_controller('dashboardController.php');
    apiCalendarioActualizar();
});

Router::post('api/calendario/eliminar', function () {
    load_controller('dashboardController.php');
    apiCalendarioEliminar();
});
