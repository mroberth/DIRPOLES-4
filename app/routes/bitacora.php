<?php
// app/routes/bitacora.php

use App\Core\Router;

// ---------- PUERTA HTML (páginas) ----------
Router::get('bitacora/consultar', function () {
    load_controller('bitacoraController.php');
    showConsultarBitacora();
});

// ---------- PUERTA JSON (API) ----------
Router::get('api/bitacora/listar', function () {
    load_controller('bitacoraController.php');
    apiBitacoraListar();
});

Router::get('api/bitacora/filtros', function () {
    load_controller('bitacoraController.php');
    apiBitacoraFiltros();
});

Router::get('api/bitacora/stats', function () {
    load_controller('bitacoraController.php');
    apiBitacoraStats();
});
