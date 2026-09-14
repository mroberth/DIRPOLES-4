<?php
const BASE_PATH = __DIR__ . '/';
define('BASE_URL', str_replace('\\', '/', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')) . '/');

require_once BASE_PATH . 'vendor/autoload.php';

// Cargar variables de entorno PRIMERO (CORS y las conexiones de BD dependen de ellas)
if (file_exists(BASE_PATH . '.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

// ==================== CORS MIDDLEWARE GLOBAL ====================
// Los orígenes permitidos se definen en .env (CORS_ALLOWED_ORIGINS), separados por coma.
$origenSolicitado = $_SERVER['HTTP_ORIGIN'] ?? '';
$origenesPermitidos = array_filter(array_map('trim', explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '')));

if (in_array($origenSolicitado, $origenesPermitidos, true)) {
    header("Access-Control-Allow-Origin: " . $origenSolicitado);
    header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept, X-Requested-With");
header("Access-Control-Max-Age: 86400");

// Respuesta inmediata a peticiones Preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// ================================================================

// La sesión PHP debe durar, como mínimo, lo mismo que el refresh token.
// Si el servidor la recolecta antes (por defecto php.ini usa 1440 s = 24 min),
// un usuario inactivo pierde la sesión aunque su JWT siga vigente, y el
// auto-refresh no puede renovarlo (el endpoint refresh_token exige sesión).
$vidaSesion = max(
    (int) ($_ENV['JWT_EXPIRATION'] ?? 3600),
    (int) ($_ENV['REFRESH_EXPIRATION'] ?? 1296000)
);
ini_set('session.gc_maxlifetime', (string) $vidaSesion);

session_start();

require_once BASE_PATH . 'app/bootstrap.php';
require_once BASE_PATH . 'app/routes.php';

// ==================== MANEJO GLOBAL DE ERRORES ====================
// Toda excepción no capturada termina AQUÍ. El formato lo decide la puerta:
//   - Ruta bajo api/*  → JSON del contrato {exito, error} (Respuesta::error)
//   - Cualquier página → log del error real + página HTML genérica
// Así NINGÚN controlador necesita try/catch para formatear errores:
// si algo escapa, el Core lo convierte en la respuesta correcta.
set_exception_handler([App\Core\Respuesta::class, 'manejarExcepcion']);

// Red de seguridad final: errores fatales (E_ERROR, E_PARSE...) que no
// viajan como excepciones. Garantiza el contrato aunque PHP muera.
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    error_log("ERROR FATAL: {$error['message']} en {$error['file']}:{$error['line']}");

    if (!headers_sent()) {
        http_response_code(500);
    }
    if (App\Core\Respuesta::esApi()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'exito' => false,
            'error' => [
                'codigo'  => App\Core\ErrorCodes::ERROR_INTERNO,
                'estado'  => 500,
                'mensaje' => 'Ocurrió un error inesperado en el servidor.',
            ],
        ], JSON_UNESCAPED_UNICODE);
    } else {
        include BASE_PATH . 'app/Views/errors/error.php';
    }
});

// Ejecutar el Router
App\Core\Router::ejecutar();
