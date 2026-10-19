<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PDO;
use Throwable;

/**
 * TestCaseBase
 * -----------------------------------------------------------------
 * Clase base estandarizada para la suite de pruebas del Monolito DIRPOLES-4.
 * Provee conexión resiliente a doble base de datos y utilidades de auditoría.
 */
abstract class TestCaseBase extends TestCase
{
    protected ?PDO $dbBusiness = null;
    protected ?PDO $dbSecurity = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Inicialización resiliente de conexiones de prueba desde .env o defaults
        $host = env('DB_HOST', '127.0.0.1');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');

        $secUser = env('DB_SECURITY_USER', $user);
        $secPass = env('DB_SECURITY_PASS', $pass);

        // Soporte tanto para socket unix como TCP
        $dsnBusiness = str_starts_with($host, 'unix_socket=')
            ? "mysql:{$host};dbname=dirpoles_business;charset=utf8mb4"
            : "mysql:host={$host};dbname=dirpoles_business;charset=utf8mb4";

        $dsnSecurity = str_starts_with($host, 'unix_socket=')
            ? "mysql:{$host};dbname=dirpoles_security;charset=utf8mb4"
            : "mysql:host={$host};dbname=dirpoles_security;charset=utf8mb4";

        try {
            $this->dbBusiness = new PDO($dsnBusiness, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (Throwable) {
            $this->dbBusiness = null;
        }

        try {
            $this->dbSecurity = new PDO($dsnSecurity, $secUser, $secPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (Throwable) {
            $this->dbSecurity = null;
        }
    }

    /**
     * Omite la prueba de integración con un mensaje claro si la BD o las tablas no están disponibles.
     */
    protected function requerirBaseDeDatos(): void
    {
        if ($this->dbBusiness === null || $this->dbSecurity === null) {
            $this->markTestSkipped('Servidor MySQL/MariaDB no disponible para pruebas de integración en vivo.');
        }

        try {
            $this->dbBusiness->query('SELECT 1 FROM beneficiario LIMIT 1');
            $this->dbSecurity->query('SELECT 1 FROM empleado LIMIT 1');
        } catch (Throwable) {
            $this->markTestSkipped('Tablas de dirpoles_business o dirpoles_security no inicializadas en la base de datos.');
        }
    }
}
