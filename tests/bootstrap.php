<?php
// tests/bootstrap.php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__) . '/');
}

require_once BASE_PATH . 'vendor/autoload.php';

// Cargar variables de entorno del archivo .env si existe
if (file_exists(BASE_PATH . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

putenv("APP_ENV=testing");
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

// Detección automática del socket de MySQL/MariaDB en entornos Linux/Unix
$defaultHost = $_ENV['DB_HOST'] ?? 'localhost';
if ($defaultHost === 'localhost' || $defaultHost === '127.0.0.1') {
    if (file_exists('/var/run/mysqld/mysqld.sock')) {
        $socketPath = '/var/run/mysqld/mysqld.sock';
        putenv("DB_HOST=unix_socket={$socketPath}");
        $_ENV['DB_HOST'] = "unix_socket={$socketPath}";
        $_SERVER['DB_HOST'] = "unix_socket={$socketPath}";
    } elseif (file_exists('/tmp/mysql.sock')) {
        $socketPath = '/tmp/mysql.sock';
        putenv("DB_HOST=unix_socket={$socketPath}");
        $_ENV['DB_HOST'] = "unix_socket={$socketPath}";
        $_SERVER['DB_HOST'] = "unix_socket={$socketPath}";
    } else {
        putenv("DB_HOST=127.0.0.1");
        $_ENV['DB_HOST'] = '127.0.0.1';
        $_SERVER['DB_HOST'] = '127.0.0.1';
    }
}

require_once BASE_PATH . 'app/bootstrap.php';
require_once __DIR__ . '/TestCaseBase.php';
