<?php
// app/Models/BeneficiarioModel.php

namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;

/**
 * Modelo del módulo BENEFICIARIOS (BD de negocio → BusinessModel).
 *
 * Patrón del esqueleto:
 *   - __set() valida y lanza ExcepcionApi::validacion().
 *   - manejarAccion() es la única puerta pública (despachador).
 *   - Los métodos privados hacen SQL y lanzan ExcepcionApi.
 *   - LIMIT/OFFSET siempre con bindValue(..., PDO::PARAM_INT).
 */
class BeneficiarioModel extends BusinessModel
{
    private const TIPOS_CEDULA = ['V', 'E', 'J', 'P', 'G'];
    private const GENEROS = ['M', 'F'];
    private const ESTATUS = [0, 1];

    private array $atributos = [];

    // ---------- Capa de validación (en CADA asignación) ----------
    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'nombres':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El nombre es obligatorio.');
                }
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El nombre no puede superar 100 caracteres.');
                }
                $this->atributos['nombres'] = $valor;
                break;

            case 'apellidos':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El apellido es obligatorio.');
                }
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El apellido no puede superar 100 caracteres.');
                }
                $this->atributos['apellidos'] = $valor;
                break;

            case 'tipo_cedula':
                $valor = strtoupper(trim((string) $valor));
                if (!in_array($valor, self::TIPOS_CEDULA, true)) {
                    throw ExcepcionApi::validacion('El tipo de cédula no es válido.');
                }
                $this->atributos['tipo_cedula'] = $valor;
                break;

            case 'cedula':
                $valor = trim((string) $valor);
                if (!preg_match('/^\d{6,10}$/', $valor)) {
                    throw ExcepcionApi::validacion('La cédula debe tener entre 6 y 10 dígitos.');
                }
                $this->atributos['cedula'] = $valor;
                break;

            case 'correo':
                $valor = trim((string) $valor);
                if ($valor === '' || !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    throw ExcepcionApi::validacion('El correo electrónico no es válido.');
                }
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El correo no puede superar 100 caracteres.');
                }
                $this->atributos['correo'] = $valor;
                break;

            case 'telefono':
                // Obligatorio y con formato móvil venezolano.
                $valor = preg_replace('/\D/', '', (string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El teléfono es obligatorio.');
                }
                if (!preg_match('/^(0412|0414|0416|0422|0424|0426)\d{7}$/', $valor)) {
                    throw ExcepcionApi::validacion(
                        'El teléfono debe empezar por 0412, 0414, 0416, 0422, 0424 o 0426 y tener 7 dígitos más (ej: 04129298008).'
                    );
                }
                $this->atributos['telefono'] = $valor;
                break;

            case 'id_pnf':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion('Debes seleccionar un PNF.');
                }
                $this->atributos['id_pnf'] = (int) $valor;
                break;

            case 'seccion':
                $valor = strtoupper(trim((string) $valor));
                if ($valor === '') {
                    throw ExcepcionApi::validacion('La sección es obligatoria.');
                }
                if (mb_strlen($valor) > 20) {
                    throw ExcepcionApi::validacion('La sección no puede superar 20 caracteres.');
                }
                $this->atributos['seccion'] = $valor;
                break;

            case 'fecha_nac':
                // Obligatoria, válida y no futura.
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('La fecha de nacimiento es obligatoria.');
                }
                $fecha = \DateTime::createFromFormat('Y-m-d', $valor);
                if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
                    throw ExcepcionApi::validacion('La fecha de nacimiento no es válida.');
                }
                if ($fecha > new \DateTime('today')) {
                    throw ExcepcionApi::validacion('La fecha de nacimiento no puede ser futura.');
                }
                $this->atributos['fecha_nac'] = $valor;
                break;

            case 'genero':
                $valor = strtoupper(trim((string) $valor));
                if (!in_array($valor, self::GENEROS, true)) {
                    throw ExcepcionApi::validacion('El género debe ser M (Masculino) o F (Femenino).');
                }
                $this->atributos['genero'] = $valor;
                break;

            case 'direccion':
                $valor = trim((string) $valor);
                if (mb_strlen($valor) > 255) {
                    throw ExcepcionApi::validacion('La dirección no puede superar 255 caracteres.');
                }
                $this->atributos['direccion'] = $valor;
                break;

            case 'estatus':
                $v = (int) $valor;
                if (!in_array($v, self::ESTATUS, true)) {
                    throw ExcepcionApi::validacion('El estatus debe ser 0 (Inactivo) o 1 (Activo).');
                }
                $this->atributos['estatus'] = $v;
                break;

            case 'id_beneficiario':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion('El id del beneficiario no es válido.');
                }
                $this->atributos['id_beneficiario'] = (int) $valor;
                break;

            case 'id_excluir':
                $this->atributos['id_excluir'] = max(0, (int) $valor);
                break;

            case 'buscar':
                $this->atributos['buscar'] = mb_substr(trim((string) $valor), 0, 100);
                break;

            case 'limit':
            case 'offset':
                $this->atributos[$nombre] = max(0, (int) $valor);
                break;

            default:
                throw ExcepcionApi::validacion("Atributo no reconocido: '{$nombre}'.");
        }
    }

    public function __get(string $nombre): mixed
    {
        return $this->atributos[$nombre] ?? null;
    }

    //=== Despachador ====
    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'crear'           => $this->crear(),
            'listar'          => $this->listar(),
            'obtener'         => $this->obtener(),
            'actualizar'      => $this->actualizar(),
            'eliminar'        => $this->eliminar(),
            'pnfs'            => $this->pnfs(),
            'stats'           => $this->stats(),
            'existe_cedula'   => $this->existeCedula($this->__get('tipo_cedula'), $this->__get('cedula'), (int) $this->__get('id_excluir')),
            'existe_correo'   => $this->existeCorreo($this->__get('correo'), (int) $this->__get('id_excluir')),
            'existe_telefono' => $this->existeTelefono($this->__get('telefono'), (int) $this->__get('id_excluir')),
            default => throw ExcepcionApi::errorInterno("Acción no válida en BeneficiarioModel: '{$accion}'."),
        };
    }

    // ---------- Acciones ----------

    private function crear(): array
    {
        try {
            if ($this->existeCedula($this->__get('tipo_cedula'), $this->__get('cedula'))) {
                throw ExcepcionApi::yaExiste('Ya existe un beneficiario con esa cédula.');
            }
            if ($this->existeCorreo($this->__get('correo'))) {
                throw ExcepcionApi::yaExiste('Ya existe un beneficiario con ese correo.');
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO beneficiario
                   (id_pnf, seccion, nombres, apellidos, tipo_cedula, cedula,
                    fecha_nac, telefono, correo, genero, direccion, estatus, fecha_creacion)
                 VALUES
                   (:id_pnf, :seccion, :nombres, :apellidos, :tipo_cedula, :cedula,
                    :fecha_nac, :telefono, :correo, :genero, :direccion, :estatus, CURDATE())"
            );
            $stmt->execute([
                ':id_pnf'     => $this->__get('id_pnf'),
                ':seccion'    => $this->__get('seccion'),
                ':nombres'    => $this->__get('nombres'),
                ':apellidos'  => $this->__get('apellidos') ?: null,
                ':tipo_cedula' => $this->__get('tipo_cedula'),
                ':cedula'     => $this->__get('cedula'),
                ':fecha_nac'  => $this->__get('fecha_nac'),
                ':telefono'   => $this->__get('telefono') ?: null,
                ':correo'     => $this->__get('correo'),
                ':genero'     => $this->__get('genero'),
                ':direccion'  => $this->__get('direccion') ?: null,
                ':estatus'    => $this->__get('estatus') ?? 1,
            ]);

            return [
                'id_beneficiario' => (int) $this->conn->lastInsertId(),
                'nombres'         => $this->__get('nombres'),
                'apellidos'       => $this->__get('apellidos'),
                'cedula'          => $this->__get('cedula'),
            ];
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('La cédula ya está registrada.');
            }
            error_log('BeneficiarioModel::crear - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar el beneficiario.');
        }
    }

    private function listar(): array
    {
        try {
            $buscar = trim((string) ($this->__get('buscar') ?? ''));
            $where = '';
            $params = [];

            if ($buscar !== '') {
                $where = "WHERE (b.nombres LIKE :q1 OR b.apellidos LIKE :q2 OR b.correo LIKE :q3
                                OR b.cedula LIKE :q4 OR p.nombre_pnf LIKE :q5)";
                $like = '%' . $buscar . '%';
                $params = [
                    ':q1' => $like, ':q2' => $like, ':q3' => $like,
                    ':q4' => $like, ':q5' => $like,
                ];
            }

            $stmt = $this->conn->prepare(
                "SELECT b.id_beneficiario, b.id_pnf, b.seccion, b.nombres, b.apellidos,
                        b.tipo_cedula, b.cedula, b.fecha_nac, b.telefono, b.correo,
                        b.genero, b.direccion, b.estatus, b.fecha_creacion,
                        p.nombre_pnf
                 FROM beneficiario b
                 LEFT JOIN pnf p ON p.id_pnf = b.id_pnf
                 $where
                 ORDER BY b.id_beneficiario DESC
                 LIMIT :limit OFFSET :offset"
            );

            foreach ($params as $clave => $valor) {
                $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('BeneficiarioModel::listar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo listar los beneficiarios.');
        }
    }

    private function obtener(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id_beneficiario, id_pnf, seccion, nombres, apellidos, tipo_cedula,
                        cedula, fecha_nac, telefono, correo, genero, direccion, estatus, fecha_creacion
                 FROM beneficiario
                 WHERE id_beneficiario = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', $this->__get('id_beneficiario'), PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                throw ExcepcionApi::noEncontrado('El beneficiario no existe.');
            }
            return $fila;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('BeneficiarioModel::obtener - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo consultar el beneficiario.');
        }
    }

    private function actualizar(): array
    {
        try {
            $id = (int) $this->__get('id_beneficiario');
            if (!$this->existeId($id)) {
                throw ExcepcionApi::noEncontrado('El beneficiario no existe.');
            }
            if ($this->existeCedula($this->__get('tipo_cedula'), $this->__get('cedula'), $id)) {
                throw ExcepcionApi::yaExiste('Ya existe otro beneficiario con esa cédula.');
            }
            if ($this->existeCorreo($this->__get('correo'), $id)) {
                throw ExcepcionApi::yaExiste('Ya existe otro beneficiario con ese correo.');
            }
            if ($this->existeTelefono($this->__get('telefono'), $id)) {
                throw ExcepcionApi::yaExiste('Ya existe otro beneficiario con ese teléfono.');
            }

            $stmt = $this->conn->prepare(
                "UPDATE beneficiario SET
                    id_pnf = :id_pnf, seccion = :seccion, nombres = :nombres, apellidos = :apellidos,
                    tipo_cedula = :tipo_cedula, cedula = :cedula, fecha_nac = :fecha_nac,
                    telefono = :telefono, correo = :correo, genero = :genero,
                    direccion = :direccion, estatus = :estatus
                 WHERE id_beneficiario = :id"
            );
            $stmt->execute([
                ':id_pnf'     => $this->__get('id_pnf'),
                ':seccion'    => $this->__get('seccion'),
                ':nombres'    => $this->__get('nombres'),
                ':apellidos'  => $this->__get('apellidos') ?: null,
                ':tipo_cedula' => $this->__get('tipo_cedula'),
                ':cedula'     => $this->__get('cedula'),
                ':fecha_nac'  => $this->__get('fecha_nac'),
                ':telefono'   => $this->__get('telefono') ?: null,
                ':correo'     => $this->__get('correo'),
                ':genero'     => $this->__get('genero'),
                ':direccion'  => $this->__get('direccion') ?: null,
                ':estatus'    => $this->__get('estatus') ?? 1,
                ':id'         => $id,
            ]);

            return [
                'id_beneficiario' => $id,
                'nombres'         => $this->__get('nombres'),
                'apellidos'       => $this->__get('apellidos'),
            ];
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('La cédula ya está registrada.');
            }
            error_log('BeneficiarioModel::actualizar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar el beneficiario.');
        }
    }

    private function eliminar(): bool
    {
        try {
            $id = (int) $this->__get('id_beneficiario');
            if (!$this->existeId($id)) {
                throw ExcepcionApi::noEncontrado('El beneficiario no existe.');
            }

            $stmt = $this->conn->prepare("DELETE FROM beneficiario WHERE id_beneficiario = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($e instanceof \PDOException && $e->getCode() === '23000') {
                throw ExcepcionApi::enUso(
                    'No se puede eliminar: el beneficiario tiene registros asociados '
                    . '(citas, consultas, jornadas, etc.). Puedes desactivarlo.'
                );
            }
            error_log('BeneficiarioModel::eliminar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar el beneficiario.');
        }
    }

    /** Catálogo de PNF activos para el <select>. */
    private function pnfs(): array
    {
        try {
            $stmt = $this->conn->query(
                "SELECT id_pnf, nombre_pnf FROM pnf WHERE estatus = 1 ORDER BY nombre_pnf ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('BeneficiarioModel::pnfs - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los PNF.');
        }
    }

    /** Tarjetas de resumen del módulo. */
    private function stats(): array
    {
        try {
            $total = (int) $this->conn->query("SELECT COUNT(*) FROM beneficiario")->fetchColumn();
            $activos = (int) $this->conn->query("SELECT COUNT(*) FROM beneficiario WHERE estatus = 1")->fetchColumn();
            $inactivos = (int) $this->conn->query("SELECT COUNT(*) FROM beneficiario WHERE estatus = 0")->fetchColumn();
            $nuevosMes = (int) $this->conn->query(
                "SELECT COUNT(*) FROM beneficiario
                 WHERE MONTH(fecha_creacion) = MONTH(CURRENT_DATE())
                   AND YEAR(fecha_creacion) = YEAR(CURRENT_DATE())"
            )->fetchColumn();

            return [
                'beneficiarios_total'      => $total,
                'beneficiarios_activos'    => $activos,
                'beneficiarios_inactivos'  => $inactivos,
                'beneficiarios_nuevos_mes' => $nuevosMes,
            ];
        } catch (Throwable $e) {
            error_log('BeneficiarioModel::stats - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar las estadísticas de beneficiarios.');
        }
    }

    // ---------- Helpers privados ----------

    private function existeId(int $id): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM beneficiario WHERE id_beneficiario = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    private function existeCedula(string $tipo, string $cedula, int $excluir = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM beneficiario WHERE tipo_cedula = :tipo AND cedula = :cedula";
        $params = [':tipo' => $tipo, ':cedula' => $cedula];

        if ($excluir > 0) {
            $sql .= " AND id_beneficiario <> :excluir";
            $params[':excluir'] = $excluir;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function existeCorreo(string $correo, int $excluir = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM beneficiario WHERE correo = :correo";
        $params = [':correo' => $correo];

        if ($excluir > 0) {
            $sql .= " AND id_beneficiario <> :excluir";
            $params[':excluir'] = $excluir;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function existeTelefono(string $telefono, int $excluir = 0): bool
    {
        if (trim($telefono) === '') {
            return false;
        }

        $sql = "SELECT COUNT(*) FROM beneficiario WHERE telefono = :telefono";
        $params = [':telefono' => $telefono];

        if ($excluir > 0) {
            $sql .= " AND id_beneficiario <> :excluir";
            $params[':excluir'] = $excluir;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
