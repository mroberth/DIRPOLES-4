<?php
// app/routes/perfil.php

use App\Core\Router;

// ---------- PUERTA HTML (página renderizada) ----------
Router::get('perfil/ver', function () {
    load_controller('perfilController.php');
    showPerfil();
});

// ---------- PUERTA JSON (API) ----------
Router::get('api/perfil/obtener', function () {
    load_controller('perfilController.php');
    apiObtenerPerfil();
});

Router::post('api/perfil/validar_correo', function () {
    load_controller('perfilController.php');
    apiValidarCorreoPerfil();
});

Router::post('api/perfil/validar_telefono', function () {
    load_controller('perfilController.php');
    apiValidarTelefonoPerfil();
});

// Escritura → POST. El RateLimitMiddleware trata 'perfil_actualizar'
// como endpoint de Nivel 1 (máxima restricción: 5 por 5 minutos),
// así que mantengo ese nombre de acción para heredar la política.
Router::post('api/perfil/actualizar', function () {
    load_controller('perfilController.php');
    apiActualizarPerfil();
});
