<?php
namespace App\Core;
use PDO;
use Throwable;

abstract class Database {
    protected $conn;
    protected $conn_security;

    final protected function Business() {
        if ($this->conn === null) {
            try {
                $host = env('DB_HOST', 'localhost');
                $dsn = str_starts_with($host, 'unix_socket=')
                    ? 'mysql:' . $host . ';dbname=' . env('DB_NAME') . ';charset=utf8mb4'
                    : 'mysql:host=' . $host . ';dbname=' . env('DB_NAME') . ';charset=utf8mb4';
                $this->conn = new PDO($dsn, env('DB_USER'), env('DB_PASS'));
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("SET NAMES 'utf8mb4'");
            } catch (Throwable $e) {
                if (env('APP_ENV') === 'testing') {
                    return;
                }
                throw new \Exception("Error BD Negocio: " . $e->getMessage());
            }
        }
    }

    final protected function Security() {
        if ($this->conn_security === null) {
            try {
                $host = env('DB_HOST', 'localhost');
                $dsn = str_starts_with($host, 'unix_socket=')
                    ? 'mysql:' . $host . ';dbname=' . env('DB_SECURITY_NAME') . ';charset=utf8mb4'
                    : 'mysql:host=' . $host . ';dbname=' . env('DB_SECURITY_NAME') . ';charset=utf8mb4';
                $this->conn_security = new PDO($dsn, env('DB_SECURITY_USER'), env('DB_SECURITY_PASS'));
                $this->conn_security->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn_security->exec("SET NAMES 'utf8mb4'");
            } catch (Throwable $e) {
                if (env('APP_ENV') === 'testing') {
                    return;
                }
                throw new \Exception("Error BD Seguridad: " . $e->getMessage());
            }
        }
    }
}