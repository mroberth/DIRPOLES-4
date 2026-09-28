<?php
// app/bootstrap.php

require_once __DIR__ . '/Core/Database.php';

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/logs/php_errors.log');

// ---------------------------------------------------------
// HELPER: acceso a variables de entorno (.env, cargado en index.php)
// ---------------------------------------------------------
if (!function_exists('env')) {
    /**
     * Lee una variable de .env (o del entorno real) con valor por defecto.
     * Reemplaza a las constantes que vivían en app/Config/config.php.
     */
    function env(string $clave, ?string $porDefecto = null): ?string
    {
        return $_ENV[$clave] ?? $_SERVER[$clave] ?? $porDefecto;
    }
}

// ---------------------------------------------------------
// HELPER: parámetros de los PDF de tercera puerta (constancia/referencia)
// ---------------------------------------------------------
if (!function_exists('parametrosDocumento')) {
    /**
     * Lee y sanea los parámetros comunes de las rutas PDF de tercera puerta
     * (medicina/orientacion/discapacidad/psicologia/trabajo-social +
     * constancia/referencia):
     *
     *   tramite → texto libre, máx. 120 caracteres (opcional).
     *   hora    → formato HH:MM (opcional; vacío = la plantilla queda en blanco).
     *   area    → área destino de la referencia (obligatoria si $areaRequerida).
     *
     * Lanza ExcepcionApi 400 si la hora es inválida o falta el área.
     */
    function parametrosDocumento(bool $areaRequerida = false): array
    {
        $tramite = mb_substr(trim((string) ($_GET['tramite'] ?? '')), 0, 120);

        $hora = trim((string) ($_GET['hora'] ?? ''));
        if ($hora !== '' && !preg_match('/^\d{1,2}:\d{2}$/', $hora)) {
            throw \App\Core\ExcepcionApi::validacion('La hora debe tener el formato HH:MM.');
        }

        $area = mb_substr(trim((string) ($_GET['area'] ?? '')), 0, 80);
        if ($areaRequerida && $area === '') {
            throw \App\Core\ExcepcionApi::validacion('El área de destino es obligatoria para la referencia.');
        }

        return ['tramite' => $tramite, 'hora' => $hora, 'area' => $area];
    }
}

// ---------------------------------------------------------
// CONFIGURACIÓN PARA CARGA DE CONTROLADORES
// ---------------------------------------------------------

// Control: si true, fuerza precarga de TODOS los controladores al iniciar.
// Recomendado: false en desarrollo; true en producción si usas OPcache.

/**
 * Helper: carga perezosa de un controlador por nombre de archivo.
 * path relativo dentro de app/controllers, por ejemplo 'beneficiarioController.php'
 *
 * Uso:
 *   load_controller('beneficiarioController.php');
 */
function load_controller(string $file): void
{
    static $loaded = [];

    // Normalizar path
    $file = ltrim($file, '/\\');
    $path = rtrim(BASE_PATH, '/\\') . '/app/Controllers/' . $file;

    if (isset($loaded[$path])) {
        // ya cargado
        return;
    }

    if (is_readable($path)) {
        require_once $path;
        $loaded[$path] = true;
        return;
    }

    // fallback: lanza excepción para detectar errores temprano
    throw new \RuntimeException("Controlador no encontrado o no legible: {$path}");
}

/**
 * Pre-carga todos los controladores (usa glob).
 * Útil en entornos donde prefieres evitar la carga condicional (p. ej. producción + OPcache).
 */
function preload_all_controllers(): void
{
    foreach (glob(rtrim(BASE_PATH, '/\\') . '/app/Controllers/*.php') as $controlador) {
        require_once $controlador;
    }
}

// Si se desea precargar, lo ejecutamos aquí.
if (env('PRELOAD_CONTROLLERS', 'false') === 'true') {
    preload_all_controllers();
}

// ---------------------------------------------------------
// FIN bootstrap.php
// ---------------------------------------------------------
