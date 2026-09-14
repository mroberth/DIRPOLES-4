<?php

use App\Core\Router;
use App\Core\Respuesta;
use App\Core\ExcepcionApi;
use App\Core\ErrorCodes;

// ==================== MIDDLEWARES GLOBALES ====================
// 1° Escudo perimetral: Rate Limit (Token Bucket)
Router::antes('ALL', '.*', [App\Middlewares\RateLimitMiddleware::class, 'handle']);

// 2° Escudo perimetral: Autenticación de sesión y JWT
Router::antes('ALL', '.*', [App\Middlewares\SessionAuthMiddleware::class, 'handle']);

// ==================== RUTAS ESENCIALES (login / inicio) ====================
Router::get('', function () {
    header('Location: ' . BASE_URL . 'login');
    exit();
});

Router::get('login', function () {
    // carga perezosa del controlador de login
    load_controller('loginController.php');
    showLogin();
});

Router::post('iniciar_sesion', function () {
    load_controller('loginController.php');
    iniciar_sesion();
});

Router::get('logout', function () {
    load_controller('loginController.php');
    cerrar_sesion();
});

Router::post('refresh_token', function () {
    load_controller('loginController.php');
    refresh_token();
});


// ==================== RUTA DE INICIO (protegida) ====================
Router::get('inicio', function () {
    load_controller('loginController.php');
    showInicio();
});

// ==================== CARGAR RUTAS POR MÓDULOS ====================
// Cada módulo que crees añade un archivo aquí: app/routes/tumodulo.php
foreach (glob(BASE_PATH . 'app/routes/*.php') as $rutaArchivo) {
    require_once $rutaArchivo;
}

// ==================== MANEJO DE ERRORES DE ENRUTAMIENTO ====================
// La misma regla de las dos puertas: bajo api/* la respuesta es JSON
// (contrato {exito, error}); en páginas, HTML con su código HTTP.
Router::rutaNoEncontrada(function () {
    if (Respuesta::esApi()) {
        throw new ExcepcionApi(ErrorCodes::RUTA_NO_ENCONTRADA, 404, 'El recurso solicitado no existe.');
    }
    http_response_code(404);
    include BASE_PATH . 'app/Views/errors/404.php';
    exit();
});

Router::metodoNoPermitido(function () {
    if (Respuesta::esApi()) {
        throw new ExcepcionApi(ErrorCodes::METODO_NO_PERMITIDO, 405, 'El método HTTP no está permitido para este recurso.');
    }
    http_response_code(405);
    include BASE_PATH . 'app/Views/errors/error.php';
    exit();
});
