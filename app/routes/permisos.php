<?php
// app/routes/permisos.php

use App\Core\Router;

// ---------- PUERTA HTML (páginas) ----------
Router::get('permisos/gestionar', function () {
    load_controller('permisosController.php');
    showGestionarPermisos();
});

// ---------- PUERTA JSON (API) ----------
Router::get('api/permisos/matriz', function () {
    load_controller('permisosController.php');
    apiPermisosMatriz();
});

Router::get('api/permisos/stats', function () {
    load_controller('permisosController.php');
    apiPermisosStats();
});

Router::post('api/permisos/guardar', function () {
    load_controller('permisosController.php');
    apiGuardarPermisosLote();
});
