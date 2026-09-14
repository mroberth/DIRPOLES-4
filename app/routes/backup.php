<?php
// app/routes/backup.php

use App\Core\Router;

// ---------- PUERTA HTML (páginas) ----------
Router::get('respaldo', function () {
    load_controller('backupController.php');
    showRespaldo();
});

// ---------- TERCERA PUERTA: descarga de archivo .sql ----------
Router::get('respaldo/descargar', function () {
    load_controller('backupController.php');
    descargarRespaldo();
});
