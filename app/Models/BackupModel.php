<?php
// app/Models/BackupModel.php

namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;

/**
 * Modelo de RESPALDO de base de datos.
 *
 * Genera el volcado SQL (estructura + datos) de cada BD usando PDO (no
 * depende de `mysqldump`). Necesita las dos conexiones, por eso extiende
 * BusinessModel y además abre la de seguridad.
 *
 * Mejora sobre el dump básico: maneja VISTAS (no las trata como tablas) e
 * inserta los datos en bloques de 500 filas.
 */
class BackupModel extends BusinessModel
{
    private const FILAS_POR_INSERT = 500;

    public function __construct()
    {
        parent::__construct();
        $this->Security();
    }

    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'backup_business' => $this->generar($this->conn, (string) env('DB_NAME')),
            'backup_security' => $this->generar($this->conn_security, (string) env('DB_SECURITY_NAME')),
            default => throw ExcepcionApi::errorInterno("Acción no válida en BackupModel: '{$accion}'."),
        };
    }

    /** Devuelve el contenido SQL del respaldo de la BD indicada. */
    private function generar(PDO $pdo, string $dbName): string
    {
        $pdo->exec("SET NAMES 'utf8mb4'");

        $tablas = [];
        $vistas = [];
        $stmt = $pdo->query("SHOW FULL TABLES");
        while ($fila = $stmt->fetch(PDO::FETCH_NUM)) {
            if (($fila[1] ?? '') === 'VIEW') {
                $vistas[] = $fila[0];
            } else {
                $tablas[] = $fila[0];
            }
        }

        $sql  = "-- ======================================================\n";
        $sql .= "-- Respaldo de la base de datos: `{$dbName}`\n";
        $sql .= "-- Generado el: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Archivo generado por DIRPOLES-4\n";
        $sql .= "-- ======================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET NAMES utf8mb4;\n\n";

        // --- Tablas: estructura + datos ---
        foreach ($tablas as $tabla) {
            $create = $pdo->query("SHOW CREATE TABLE `{$tabla}`")->fetch(PDO::FETCH_NUM);

            $sql .= "-- ------------------------------------------------------\n";
            $sql .= "-- Tabla `{$tabla}`\n";
            $sql .= "-- ------------------------------------------------------\n\n";
            $sql .= "DROP TABLE IF EXISTS `{$tabla}`;\n";
            $sql .= ($create[1] ?? '') . ";\n\n";

            $sql .= $this->volcarDatos($pdo, $tabla);
        }

        // --- Vistas: solo estructura ---
        foreach ($vistas as $vista) {
            $create = $pdo->query("SHOW CREATE VIEW `{$vista}`")->fetch(PDO::FETCH_NUM);
            $sql .= "-- Vista `{$vista}`\n";
            $sql .= "DROP VIEW IF EXISTS `{$vista}`;\n";
            $sql .= ($create[1] ?? '') . ";\n\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $sql;
    }

    /** Volcado de datos de una tabla en bloques de INSERT. */
    private function volcarDatos(PDO $pdo, string $tabla): string
    {
        $stmt = $pdo->query("SELECT * FROM `{$tabla}`");
        $cols = $stmt->columnCount();
        if ($cols === 0) {
            return '';
        }

        $sql = '';
        $bloque = [];
        $total = 0;

        while ($fila = $stmt->fetch(PDO::FETCH_NUM)) {
            $valores = [];
            for ($i = 0; $i < $cols; $i++) {
                $valores[] = ($fila[$i] === null) ? 'NULL' : $pdo->quote((string) $fila[$i]);
            }
            $bloque[] = '(' . implode(', ', $valores) . ')';
            $total++;

            if (count($bloque) >= self::FILAS_POR_INSERT) {
                $sql .= "INSERT INTO `{$tabla}` VALUES\n" . implode(",\n", $bloque) . ";\n";
                $bloque = [];
            }
        }

        if (!empty($bloque)) {
            $sql .= "INSERT INTO `{$tabla}` VALUES\n" . implode(",\n", $bloque) . ";\n";
        }
        if ($total > 0) {
            $sql .= "\n";
        }

        return $sql;
    }
}
