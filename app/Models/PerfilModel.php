<?php
// app/Models/PerfilModel.php

namespace App\Models;

use PDO;
use DateTime;
use Throwable;
use PDOException;
use App\Core\ExcepcionApi;

/**
 * PerfilModel
 * ---------------------------------------------------------------
 * Módulo "Perfil del empleado": consulta y autoservicio de datos
 * PROPIOS del empleado autenticado (id tomado de la SESIÓN, nunca
 * de la petición). CRUD de terceros vive en EmpleadoModel.
 *
 * Extiende SecurityModel: la tabla `empleado` vive en
 * dirpoles_security. Conexión: $this->conn_security (tablas propias)
 * y $this->conn (business) para la unicidad GLOBAL de correo/teléfono
 * contra beneficiario y proveedores.
 *
 * Patrón del esqueleto: __set valida → manejarAccion() despacha →
 * métodos privados hacen el SQL y lanzan ExcepcionApi.
 */
class PerfilModel extends SecurityModel
{
    private array $atributos = [];

    // === Capa de validación (se ejecuta en CADA asignación) ===

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_empleado':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion('El id del empleado no es válido.');
                }
                $this->atributos['id_empleado'] = (int) $valor;
                break;

            case 'correo':
                $valor = trim((string) $valor);
                if ($valor === '' || !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    throw ExcepcionApi::validacion('El correo electrónico no es válido.');
                }
                if (mb_strlen($valor) > 50) { // la columna es varchar(50)
                    throw ExcepcionApi::validacion('El correo no puede superar 50 caracteres.');
                }
                $this->atributos['correo'] = $valor;
                break;

            case 'telefono':
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

            case 'direccion':
                $valor = trim((string) $valor);
                if (mb_strlen($valor) > 500) {
                    throw ExcepcionApi::validacion('La dirección es demasiado larga (máx. 500 caracteres).');
                }
                $this->atributos['direccion'] = $valor;
                break;

            case 'clave_actual':
                if (!is_string($valor) || $valor === '') {
                    throw ExcepcionApi::validacion('Debes escribir tu contraseña actual.');
                }
                $this->atributos['clave_actual'] = $valor;
                break;

            case 'clave':
                if (!is_string($valor) || strlen($valor) < 8) {
                    throw ExcepcionApi::validacion('La contraseña debe tener al menos 8 caracteres.');
                }
                if (strlen($valor) > 72) { // límite de bcrypt
                    throw ExcepcionApi::validacion('La contraseña no puede superar 72 caracteres.');
                }
                $this->atributos['clave'] = $valor;
                break;

            case 'clave_confirmacion':
                // Solo pasa por aquí si el cliente la envía; la comparación se hace en actualizar().
                $this->atributos['clave_confirmacion'] = (string) $valor;
                break;

            default:
                throw ExcepcionApi::validacion("Atributo no reconocido: '{$nombre}'.");
        }
    }

    public function __get(string $nombre): mixed
    {
        return $this->atributos[$nombre] ?? null;
    }

    // === Despachador (única puerta pública) ===

    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'obtener'    => $this->obtener(),
            'actualizar' => $this->actualizar(),
            'validar_correo'   => $this->validarCorreo(),
            'validar_telefono' => $this->validarTelefono(),
            default      => throw ExcepcionApi::errorInterno("Acción no válida en PerfilModel: '{$accion}'."),
        };
    }

    // === Acciones (SQL + reglas de negocio) ===

    /**
     * Devuelve el perfil del empleado de la sesión con datos útiles
     * de solo lectura (nombre, cédula, rol) + los campos editables.
     */
    private function obtener(): array
    {
        try {
            $stmt = $this->conn_security->prepare(
                "SELECT e.id_empleado, e.nombre, e.apellido, e.tipo_cedula, e.cedula,
                        e.correo, e.telefono, e.direccion, e.estatus, e.fecha_creacion,
                        t.tipo AS nombre_tipo
                 FROM empleado e
                 INNER JOIN tipo_empleado t ON t.id_tipo_emp = e.id_tipo_empleado
                 WHERE e.id_empleado = :id
                 LIMIT 1"
            );
            $stmt->bindValue(':id', (int) $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                throw ExcepcionApi::noEncontrado('El perfil solicitado no existe.');
            }
            return $fila;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('PerfilModel::obtener - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo consultar el perfil.');
        }
    }

    /**
     * Autoservicio: actualiza SOLO correo, teléfono, dirección y (opcional)
     * contraseña del empleado de la sesión. Reglas:
     *  - Exige clave_actual válida (confirmación de identidad).
     *  - Si viene 'clave', exige 'clave_confirmacion' igual (si el cliente
     *    la envía) y no puede repetir la actual.
     *  - El correo nuevo no puede pertenecer a otro empleado.
     */
    private function actualizar(): array
    {
        try {
            $id = (int) $this->__get('id_empleado');

            $stmt = $this->conn_security->prepare(
                "SELECT correo, clave FROM empleado WHERE id_empleado = :id LIMIT 1"
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                throw ExcepcionApi::noEncontrado('El perfil solicitado no existe.');
            }

            // 1. Confirmación de identidad: la clave actual es obligatoria y válida.
            if (!password_verify((string) $this->__get('clave_actual'), (string) $fila['clave'])) {
                throw ExcepcionApi::validacion('La contraseña actual no es correcta.');
            }

            // 2. Unicidad GLOBAL de correo y teléfono (excluyéndose a sí mismo).
            if ($this->validarCorreo()['existe']) {
                throw ExcepcionApi::yaExiste('Ese correo ya está registrado en el sistema.');
            }
            if ($this->validarTelefono()['existe']) {
                throw ExcepcionApi::yaExiste('Ese teléfono ya está registrado en el sistema.');
            }

            // 3. Si cambia la contraseña: confirmación y no repetir la actual.
            $cambiaClave = $this->__get('clave') !== null;
            if ($cambiaClave) {
                if ($this->__get('clave_confirmacion') !== null
                    && $this->__get('clave_confirmacion') !== $this->__get('clave')) {
                    throw ExcepcionApi::validacion('La confirmación de la contraseña no coincide.');
                }
                if (password_verify($this->__get('clave'), (string) $fila['clave'])) {
                    throw ExcepcionApi::validacion('La nueva contraseña no puede ser igual a la actual.');
                }
            }

            // 4. UPDATE acotado: jamás toca rol, cédula, estatus, etc.
            $sql = "UPDATE empleado
                    SET correo = :correo, telefono = :telefono, direccion = :direccion";
            $params = [
                ':correo'   => $this->__get('correo'),
                ':telefono' => $this->__get('telefono'),
                ':direccion' => $this->__get('direccion') ?: null,
                ':id'       => $id,
            ];
            if ($cambiaClave) {
                $sql .= ", clave = :clave";
                $params[':clave'] = password_hash((string) $this->__get('clave'), PASSWORD_BCRYPT);
            }
            $sql .= " WHERE id_empleado = :id";

            $stmt = $this->conn_security->prepare($sql);
            $stmt->execute($params);

            return [
                'id_empleado'  => $id,
                'correo'       => $this->__get('correo'),
                'telefono'     => $this->__get('telefono'),
                'cambio_clave' => $cambiaClave,
            ];
        } catch (ExcepcionApi $e) {
            throw $e; // regla de negocio: no la disfrazo de error técnico
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw ExcepcionApi::yaExiste('Ese correo ya está registrado en el sistema.');
            }
            error_log('PerfilModel::actualizar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar el perfil.');
        } catch (Throwable $e) {
            error_log('PerfilModel::actualizar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar el perfil.');
        }
    }

    private function validarCorreo(): array
    {
        $this->Business(); // conexión a BD de negocio para consultar beneficiarios y proveedores

        // Unicidad GLOBAL de correo: empleado (excluyendo el propio) +
        // beneficiario + proveedores.
        $sql = 'SELECT COUNT(*) FROM (
                    SELECT id_empleado FROM dirpoles_security.empleado
                     WHERE correo = :c1 AND id_empleado <> :x1
                    UNION ALL
                    SELECT id_beneficiario FROM dirpoles_business.beneficiario
                     WHERE correo = :c2
                    UNION ALL
                    SELECT id_proveedor FROM dirpoles_business.proveedores
                     WHERE correo = :c3
                ) t';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':c1' => $this->__get('correo'),
            ':x1' => (int) $this->__get('id_empleado'),
            ':c2' => $this->__get('correo'),
            ':c3' => $this->__get('correo'),
        ]);

        return ['existe' => (int) $stmt->fetchColumn() > 0];
    }

    private function validarTelefono(): array
    {
        // Vacío no se valida (la columna es opcional).
        if (trim((string) $this->__get('telefono')) === '') {
            return ['existe' => false];
        }

        $this->Business(); // conexión a BD de negocio para consultar beneficiarios y proveedores

        // Unicidad GLOBAL de teléfono: empleado (excluyendo el propio) +
        // beneficiario + proveedores.
        $sql = 'SELECT COUNT(*) FROM (
                    SELECT id_empleado FROM dirpoles_security.empleado
                     WHERE telefono = :t1 AND id_empleado <> :x1
                    UNION ALL
                    SELECT id_beneficiario FROM dirpoles_business.beneficiario
                     WHERE telefono = :t2
                    UNION ALL
                    SELECT id_proveedor FROM dirpoles_business.proveedores
                     WHERE telefono = :t3
                ) t';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':t1' => $this->__get('telefono'),
            ':x1' => (int) $this->__get('id_empleado'),
            ':t2' => $this->__get('telefono'),
            ':t3' => $this->__get('telefono'),
        ]);

        return ['existe' => (int) $stmt->fetchColumn() > 0];
    }

    /**
     * Convierte 'Y-m-d' a 'd/m/Y' para mostrar en la vista.
     * (Mantenida privada: es lógica de presentación del modelo.)
     */
    private static function fechaCorta(string $fechaMysql): string
    {
        $fecha = DateTime::createFromFormat('Y-m-d', $fechaMysql);
        return $fecha ? $fecha->format('d/m/Y') : $fechaMysql;
    }
}
