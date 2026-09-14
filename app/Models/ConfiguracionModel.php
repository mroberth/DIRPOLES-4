<?php
// app/Models/ConfiguracionModel.php

namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;

/**
 * Modelo del módulo CONFIGURACIÓN (catálogos del sistema).
 *
 * Un solo modelo gestiona los 7 catálogos definidos en
 * app/Config/configuracion_catalogos.php (patología, PNF, servicio,
 * tipo_empleado, tipo_mobiliario, tipo_equipo, presentación).
 * Según la config usa la conexión de negocio o la de seguridad.
 */
class ConfiguracionModel extends BusinessModel
{
    private array $atributos = [];
    private array $catalogos;

    public function __construct()
    {
        parent::__construct();
        $this->catalogos = require BASE_PATH . 'app/Config/configuracion_catalogos.php';
    }

    public function __set(string $nombre, mixed $valor): void
    {
        $this->atributos[$nombre] = $valor;
    }

    public function __get(string $nombre): mixed
    {
        return $this->atributos[$nombre] ?? null;
    }

    //=== Despachador ====
    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'crear'      => $this->crear(),
            'listar'     => $this->listar(),
            'obtener'    => $this->obtener(),
            'actualizar' => $this->actualizar(),
            'validar'    => $this->validar(),
            'servicios'  => $this->servicios(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en ConfiguracionModel: '{$accion}'."),
        };
    }

    // ---------- Internos ----------

    /**
     * Valida y normaliza los valores de un catálogo según la config.
     * @param bool $lanzar true  → lanza ExcepcionApi si algo es inválido.
     *                     false → solo normaliza (para la validación remota).
     */
    private function validarYNormalizar(array $cfg, array $datos, bool $lanzar): array
    {
        $valores = [];
        foreach ($cfg['campos'] as $campo) {
            $name = $campo['name'];
            $val = isset($datos[$name]) ? trim((string) $datos[$name]) : '';

            if ($lanzar) {
                if (!empty($campo['required']) && $val === '') {
                    throw ExcepcionApi::validacion("El campo '{$campo['label']}' es obligatorio.");
                }
                if ($val !== '' && !empty($campo['min']) && mb_strlen($val) < (int) $campo['min']) {
                    throw ExcepcionApi::validacion("El campo '{$campo['label']}' debe tener al menos {$campo['min']} caracteres.");
                }
                if (!empty($campo['max']) && mb_strlen($val) > (int) $campo['max']) {
                    throw ExcepcionApi::validacion("El campo '{$campo['label']}' no puede superar {$campo['max']} caracteres.");
                }
                if ($val !== '' && !empty($campo['regex'])) {
                    $flags = $campo['regex_flags'] ?? '';
                    if (!preg_match('/' . $campo['regex'] . '/' . $flags, $val)) {
                        throw ExcepcionApi::validacion("El formato de '{$campo['label']}' no es válido.");
                    }
                }
                if (($campo['tipo'] ?? '') === 'select' && !empty($campo['opciones']) && !array_key_exists($val, $campo['opciones'])) {
                    throw ExcepcionApi::validacion("Valor no válido para '{$campo['label']}'.");
                }
            }

            // Prefijo (ej: "PNF ") para guardar y comparar.
            if (!empty($campo['prefijo']) && $val !== '' && !str_starts_with($val, $campo['prefijo'])) {
                $val = $campo['prefijo'] . $val;
            }

            $valores[$name] = $val;
        }
        return $valores;
    }

    private function catalogo(string $tipo): array
    {
        if (!isset($this->catalogos[$tipo])) {
            throw ExcepcionApi::validacion("Catálogo no válido: '{$tipo}'.");
        }
        return $this->catalogos[$tipo];
    }

    private function pdo(array $cfg): PDO
    {
        if (($cfg['conexion'] ?? 'business') === 'security') {
            $this->Security();
            return $this->conn_security;
        }
        return $this->conn;
    }

    private function crear(): array
    {
        $tipo = (string) $this->__get('tipo');
        $datos = $this->__get('datos');
        if (!is_array($datos)) {
            $datos = [];
        }

        $cfg = $this->catalogo($tipo);
        $pdo = $this->pdo($cfg);

        try {
            // 1) Validar/normalizar cada campo según la config
            $valores = $this->validarYNormalizar($cfg, $datos, true);

            // 2) Claves foráneas declaradas en la config
            foreach (($cfg['fk'] ?? []) as $col => $ref) {
                $pdoRef = ($ref['conexion'] ?? 'business') === 'security'
                    ? $this->pdo(['conexion' => 'security'])
                    : $this->conn;
                $stmt = $pdoRef->prepare("SELECT COUNT(*) FROM `{$ref['tabla']}` WHERE `{$ref['col']}` = :v");
                $stmt->execute([':v' => $valores[$col] ?? '']);
                if ((int) $stmt->fetchColumn() === 0) {
                    throw ExcepcionApi::validacion("El valor seleccionado en '{$col}' no existe.");
                }
            }

            // 3) Unicidad COMPUESTA declarada en la config (todas las columnas juntas)
            if (!empty($cfg['unico'])) {
                $conds = [];
                $uParams = [];
                foreach (array_values($cfg['unico']) as $i => $col) {
                    $conds[] = "`{$col}` = :u{$i}";
                    $uParams[":u{$i}"] = $valores[$col] ?? '';
                }
                $stmt = $pdo->prepare(
                    "SELECT COUNT(*) FROM `{$cfg['tabla']}` WHERE " . implode(' AND ', $conds)
                );
                $stmt->execute($uParams);
                if ((int) $stmt->fetchColumn() > 0) {
                    throw ExcepcionApi::yaExiste('Ya existe un registro con esos datos.');
                }
            }

            // 4) INSERT dinámico
            $cols = array_keys($valores);
            $ph = array_map(static fn ($c) => ':' . $c, $cols);
            $params = [];
            foreach ($valores as $c => $v) {
                $params[':' . $c] = $v;
            }
            if (!empty($cfg['con_estatus'])) {
                $cols[] = 'estatus';
                $ph[] = '1';
            }
            $cols[] = 'fecha_creacion';
            $ph[] = 'NOW()';

            $sql = "INSERT INTO `{$cfg['tabla']}` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $ph) . ")";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, PDO::PARAM_STR);
            }
            $stmt->execute();

            return ['id' => (int) $pdo->lastInsertId(), 'tipo' => $tipo];
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('El registro ya existe o viola una restricción.');
            }
            error_log('ConfiguracionModel::crear - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar la configuración.');
        }
    }

    /** Valida en vivo (unicidad compuesta) sin escribir. Devuelve {existe:bool}. */
    private function validar(): array
    {
        $tipo = (string) $this->__get('tipo');
        $datos = $this->__get('datos');
        if (!is_array($datos)) {
            $datos = [];
        }

        $cfg = $this->catalogo($tipo);
        if (empty($cfg['unico'])) {
            return ['existe' => false];
        }

        $valores = $this->validarYNormalizar($cfg, $datos, false);
        $excluir = (int) $this->__get('id_excluir');

        try {
            $pdo = $this->pdo($cfg);
            $conds = [];
            $params = [];
            foreach (array_values($cfg['unico']) as $i => $col) {
                $conds[] = "`{$col}` = :u{$i}";
                $params[":u{$i}"] = $valores[$col] ?? '';
            }
            $sql = "SELECT COUNT(*) FROM `{$cfg['tabla']}` WHERE " . implode(' AND ', $conds);
            if ($excluir > 0) {
                $sql .= " AND `{$cfg['pk']}` <> :excluir";
                $params[':excluir'] = $excluir;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return ['existe' => (int) $stmt->fetchColumn() > 0];
        } catch (Throwable $e) {
            error_log('ConfiguracionModel::validar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo validar el registro.');
        }
    }

    /** Devuelve una fila por su PK. */
    private function obtener(): array
    {
        $tipo = (string) $this->__get('tipo');
        $id = (int) $this->__get('id');
        $cfg = $this->catalogo($tipo);
        $pdo = $this->pdo($cfg);

        try {
            $stmt = $pdo->prepare("SELECT * FROM `{$cfg['tabla']}` WHERE `{$cfg['pk']}` = :id LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                throw ExcepcionApi::noEncontrado('El registro no existe.');
            }
            return $fila;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('ConfiguracionModel::obtener - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo consultar el registro.');
        }
    }

    /** Actualiza una fila (sin eliminar). `estatus` es opcional si la tabla lo tiene. */
    private function actualizar(): array
    {
        $tipo = (string) $this->__get('tipo');
        $id = (int) $this->__get('id');
        $datos = $this->__get('datos');
        if (!is_array($datos)) {
            $datos = [];
        }

        $cfg = $this->catalogo($tipo);
        $pdo = $this->pdo($cfg);

        try {
            $valores = $this->validarYNormalizar($cfg, $datos, true);

            // FK
            foreach (($cfg['fk'] ?? []) as $col => $ref) {
                $pdoRef = ($ref['conexion'] ?? 'business') === 'security'
                    ? $this->pdo(['conexion' => 'security'])
                    : $this->conn;
                $stmt = $pdoRef->prepare("SELECT COUNT(*) FROM `{$ref['tabla']}` WHERE `{$ref['col']}` = :v");
                $stmt->execute([':v' => $valores[$col] ?? '']);
                if ((int) $stmt->fetchColumn() === 0) {
                    throw ExcepcionApi::validacion("El valor seleccionado en '{$col}' no existe.");
                }
            }

            // Unicidad compuesta excluyendo el registro actual
            if (!empty($cfg['unico'])) {
                $conds = [];
                $uParams = [':excluir' => $id];
                foreach (array_values($cfg['unico']) as $i => $col) {
                    $conds[] = "`{$col}` = :u{$i}";
                    $uParams[":u{$i}"] = $valores[$col] ?? '';
                }
                $stmt = $pdo->prepare(
                    "SELECT COUNT(*) FROM `{$cfg['tabla']}` WHERE " . implode(' AND ', $conds)
                    . " AND `{$cfg['pk']}` <> :excluir"
                );
                $stmt->execute($uParams);
                if ((int) $stmt->fetchColumn() > 0) {
                    throw ExcepcionApi::yaExiste('Ya existe otro registro con esos datos.');
                }
            }

            // SET dinámico (+ estatus opcional)
            $sets = [];
            $params = [':id' => $id];
            foreach ($valores as $col => $v) {
                $sets[] = "`{$col}` = :s_{$col}";
                $params[":s_{$col}"] = $v;
            }
            if (!empty($cfg['con_estatus']) && array_key_exists('estatus', $datos)) {
                $sets[] = "`estatus` = :estatus";
                $params[':estatus'] = ((int) $datos['estatus']) === 1 ? 1 : 0;
            }

            $sql = "UPDATE `{$cfg['tabla']}` SET " . implode(', ', $sets) . " WHERE `{$cfg['pk']}` = :id";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, $k === ':id' || $k === ':estatus' ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();

            return ['id' => $id, 'tipo' => $tipo];
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('El registro viola una restricción.');
            }
            error_log('ConfiguracionModel::actualizar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar la configuración.');
        }
    }

    private function listar(): array
    {
        $tipo = (string) $this->__get('tipo');
        $cfg = $this->catalogo($tipo);
        $pdo = $this->pdo($cfg);

        try {
            $stmt = $pdo->query("SELECT * FROM `{$cfg['tabla']}` ORDER BY 1 DESC LIMIT 200");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('ConfiguracionModel::listar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo listar la configuración.');
        }
    }

    /** Servicios activos (opciones para el catálogo tipo_empleado). */
    private function servicios(): array
    {
        try {
            $stmt = $this->conn->query(
                "SELECT id_servicios, nombre_serv FROM servicio WHERE estatus = 1 ORDER BY nombre_serv ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('ConfiguracionModel::servicios - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los servicios.');
        }
    }
}
