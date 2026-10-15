<?php
// tests/bootstrap.php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__) . '/');
}

require_once BASE_PATH . 'vendor/autoload.php';

// Configuración de entorno de prueba apuntando al servidor MariaDB
$socketPath = '/tmp/mysql.sock';
putenv("DB_HOST=localhost;unix_socket={$socketPath}");
putenv("DB_NAME=dirpoles_business");
putenv("DB_USER=roberth");
putenv("DB_PASS=");
putenv("DB_SECURITY_NAME=dirpoles_security");
putenv("DB_SECURITY_USER=roberth");
putenv("DB_SECURITY_PASS=");

$_ENV['DB_HOST'] = "localhost;unix_socket={$socketPath}";
$_ENV['DB_NAME'] = 'dirpoles_business';
$_ENV['DB_USER'] = 'roberth';
$_ENV['DB_PASS'] = '';
$_ENV['DB_SECURITY_NAME'] = 'dirpoles_security';
$_ENV['DB_SECURITY_USER'] = 'roberth';
$_ENV['DB_SECURITY_PASS'] = '';

$_SERVER['DB_HOST'] = "localhost;unix_socket={$socketPath}";
$_SERVER['DB_NAME'] = 'dirpoles_business';
$_SERVER['DB_USER'] = 'roberth';
$_SERVER['DB_PASS'] = '';
$_SERVER['DB_SECURITY_NAME'] = 'dirpoles_security';
$_SERVER['DB_SECURITY_USER'] = 'roberth';
$_SERVER['DB_SECURITY_PASS'] = '';

require_once BASE_PATH . 'app/bootstrap.php';
