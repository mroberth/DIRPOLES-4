<?php
// app/routes/ayuda.php

use App\Core\Router;

// ---------- PUERTA HTML (páginas renderizadas) ----------
Router::get('ayuda', function () {
    load_controller('ayudaController.php');
    showAyuda();
});

Router::get('ayuda/consultar', function () {
    load_controller('ayudaController.php');
    showAyuda();
});

// ---------- PUERTA JSON (API, prefijo api/) ----------
Router::get('api/ayuda/guias', function () {
    load_controller('ayudaController.php');
    apiObtenerGuias();
});
