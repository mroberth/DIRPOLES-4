<?php
// tests/bootstrap.php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__) . '/');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/DIRPOLES-4/');
}

require_once BASE_PATH . 'vendor/autoload.php';

if (file_exists(BASE_PATH . '.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

// Entorno de pruebas seguro
$_ENV['APP_ENV'] = 'testing';

require_once BASE_PATH . 'app/bootstrap.php';

// Autoload para clases de prueba bajo el namespace Tests\
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'Tests\\')) {
        $relPath = str_replace('\\', '/', substr($class, 6)) . '.php';
        $file = __DIR__ . '/' . $relPath;
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
