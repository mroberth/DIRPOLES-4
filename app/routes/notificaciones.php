<?php
// app/routes/notificaciones.php

use App\Core\Router;

// ==================== STREAMING SSE (puerta especial) ====================
// text/event-stream: NO es HTML ni JSON, por eso no lleva prefijo api/.
// El EventSource del navegador reanuda desde ?ultimoId=<id>.
Router::get('sse/notificaciones', function () {
    load_controller('sseController.php');
    streamNotificaciones();
});

// ==================== PUERTA JSON (API, prefijo api/) ====================
// Lectura
Router::get('api/notificaciones/listar', function () {
    load_controller('notificacionesController.php');
    apiListar();
});

Router::get('api/notificaciones/contar', function () {
    load_controller('notificacionesController.php');
    apiContar();
});

// Escritura (gestión de la bandeja del usuario autenticado)
Router::post('api/notificaciones/marcar_leida', function () {
    load_controller('notificacionesController.php');
    apiMarcarLeida();
});

Router::post('api/notificaciones/marcar_todas_leidas', function () {
    load_controller('notificacionesController.php');
    apiMarcarTodasLeidas();
});

Router::post('api/notificaciones/eliminar', function () {
    load_controller('notificacionesController.php');
    apiEliminar();
});

Router::post('api/notificaciones/eliminar_todas', function () {
    load_controller('notificacionesController.php');
    apiEliminarTodas();
});