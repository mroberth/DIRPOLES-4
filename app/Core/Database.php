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
                $this->conn = new PDO('mysql:host=' . env('DB_HOST', 'localhost') . ';dbname=' . env('DB_NAME') . ';charset=utf8mb4', env('DB_USER'), env('DB_PASS'));
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("SET NAMES 'utf8mb4'");
            } catch (Throwable $e) {
                throw new \Exception("Error BD Negocio: " . $e->getMessage());
            }
        }
    }

    final protected function Security() {
        if ($this->conn_security === null) {
            try {
                $this->conn_security = new PDO('mysql:host=' . env('DB_HOST', 'localhost') . ';dbname=' . env('DB_SECURITY_NAME') . ';charset=utf8mb4', env('DB_SECURITY_USER'), env('DB_SECURITY_PASS'));
                $this->conn_security->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn_security->exec("SET NAMES 'utf8mb4'");
            } catch (Throwable $e) {
                throw new \Exception("Error BD Seguridad: " . $e->getMessage());
            }
        }
    }
}