<?php

namespace App\Models;

use App\Core\ErrorCodes;
use App\Core\ExcepcionApi;
use PDO;
use Throwable;

/**
 * Modelo de autenticación (BD de seguridad).
 *
 * Contrato del esqueleto (Forma 2):
 *   - __set() valida cada atributo y lanza ExcepcionApi::validacion() si es inválido.
 *   - manejarAccion() es la única puerta pública del modelo (despachador).
 *   - Los métodos privados ejecutan SQL y aplican reglas de negocio.
 *   - Los fallos se comunican LANZANDO ExcepcionApi con sentido,
 *     nunca devolviendo arrays con 'estado' => 'error'.
 *   - Los fallos técnicos (BD, timeout, constraint...) se loguean con
 *     el detalle real y se relanzan como errorInterno(), para que el
 *     handler global responda 500 y no se confunda con un fallo de
 *     negocio (ej: 'credenciales inválidas' o 'cuenta bloqueada').
 */
class loginModel extends SecurityModel
{
    /** Intentos fallidos permitidos antes de bloquear una cuenta (no administradores). */
    private const MAX_INTENTOS = 3;

    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        switch (mb_strtolower($nombre)) {
            case 'correo':
                $correo = trim((string) $valor);
                if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    throw ExcepcionApi::validacion('El correo electrónico no es válido.');
                }
                if (mb_strlen($correo) > 100) {
                    throw ExcepcionApi::validacion('El correo electrónico es demasiado largo.');
                }
                $this->atributos['correo'] = $correo;
                break;

            case 'password':
                if (!is_string($valor) || $valor === '') {
                    throw ExcepcionApi::validacion('La contraseña es obligatoria.');
                }
                if (strlen($valor) > 255) {
                    throw ExcepcionApi::validacion('La contraseña excede la longitud permitida.');
                }
                $this->atributos['password'] = $valor;
                break;

            default:
                throw ExcepcionApi::validacion(
                    "Atributo desconocido para el login: '{$nombre}'."
                );
        }
    }

    public function __get(string $atributo): mixed
    {
        return $this->atributos[$atributo] ?? null;
    }

    /** Única puerta pública del modelo (despachador). */
    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'Autenticar'         => $this->autenticar(),
            'Verificar_username' => $this->existeUsuario(),
            'Deshabilitar'       => $this->deshabilitarUsuario(),

            default => throw ExcepcionApi::errorInterno(
                "Acción no reconocida en loginModel: '{$accion}'."
            ),
        };
    }

    // ------------------------------------------------------------------
    // Métodos privados: SQL + reglas de negocio
    // ------------------------------------------------------------------

    /**
     * Autentica al empleado contra la BD de seguridad.
     *
     * Exito -> devuelve la fila del empleado (incluye nombre_tipo).
     * Fallo -> lanza ExcepcionApi con sentido:
     *   - VALIDACION (400)     -> credenciales invalidas
     *   - ACCOUNT_LOCKED (403) -> cuenta bloqueada o bloqueada por intentos
     *
     * Regla de negocio: tras MAX_INTENTOS contraseñas incorrectas de un
     * usuario NO administrador, la cuenta se deshabilita (estatus = 0).
     */
    private function autenticar(): mixed
    {
        $correo   = $this->__get('correo');
        $password = $this->__get('password');

        if ($correo === null || $password === null) {
            throw ExcepcionApi::validacion('Correo y contraseña son obligatorios.');
        }

        $usuario = $this->buscarPorCorreo($correo);

        // El mismo mensaje siempre: no revelamos si el correo existe o no.
        if (!$usuario) {
            throw ExcepcionApi::validacion('Credenciales inválidas.');
        }

        if ((int) $usuario['estatus'] === 0) {
            throw new ExcepcionApi(
                ErrorCodes::CUENTA_BLOQUEADA,
                403,
                'Cuenta bloqueada, contacte al administrador.'
            );
        }

        if (password_verify($password, $usuario['clave'])) {
            $this->limpiarIntentos($correo);
            return $usuario;
        }

        // Contraseña incorrecta: la politica de bloqueo no aplica a
        // administradores.
        if (self::esAdministrador($usuario['nombre_tipo'])) {
            throw ExcepcionApi::validacion('Credenciales inválidas.');
        }

        $intentos = $this->registrarIntentoFallido($correo);

        if ($intentos >= self::MAX_INTENTOS) {
            $this->deshabilitarUsuario();
            $this->limpiarIntentos($correo);
            throw new ExcepcionApi(
                ErrorCodes::CUENTA_BLOQUEADA,
                403,
                'Cuenta bloqueada tras ' . self::MAX_INTENTOS
                . ' intentos fallidos. Contacte al administrador.'
            );
        }

        throw ExcepcionApi::validacion('Credenciales inválidas.');
    }

    /** Busca el empleado por correo (null si no existe). */
    private function buscarPorCorreo(string $correo): ?array
    {
        try {
            $stmt = $this->conn_security->prepare(
                "SELECT empleado.*, tipo_empleado.tipo AS nombre_tipo
                 FROM empleado
                 JOIN tipo_empleado ON empleado.id_tipo_empleado = tipo_empleado.id_tipo_emp
                 WHERE empleado.correo = :correo
                 LIMIT 1"
            );
            $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            return $usuario ?: null;

        } catch (Throwable $e) {
            error_log(
                'loginModel::buscarPorCorreo - '
                . get_class($e) . ': ' . $e->getMessage()
                . ' en ' . $e->getFile() . ':' . $e->getLine()
            );
            throw ExcepcionApi::errorInterno(
                'No se pudo consultar el empleado para el inicio de sesión.'
            );
        }
    }

    /** ¿El tipo de empleado es administrador o superusuario? */
    private static function esAdministrador(string $nombreTipo): bool
    {
        $tipo = mb_strtolower($nombreTipo);
        return str_contains($tipo, 'administrador') || str_contains($tipo, 'superusuario');
    }

    /**
     * Incrementa (atómico) el contador PERSISTENTE de intentos fallidos del
     * correo en la tabla `login_intentos` y devuelve el total acumulado.
     *
     * A diferencia de la versión anterior (en $_SESSION), este contador no se
     * puede evadir borrando la cookie de sesión: vive en la BD de seguridad.
     */
    private function registrarIntentoFallido(string $correo): int
    {
        try {
            $stmt = $this->conn_security->prepare(
                "INSERT INTO login_intentos (correo, intentos_fallidos, ip_address, ultimo_intento)
                 VALUES (:correo, 1, :ip, NOW())
                 ON DUPLICATE KEY UPDATE
                    intentos_fallidos = intentos_fallidos + 1,
                    ip_address       = VALUES(ip_address),
                    ultimo_intento   = NOW()"
            );
            $stmt->execute([
                ':correo' => $correo,
                ':ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $stmt = $this->conn_security->prepare(
                "SELECT intentos_fallidos FROM login_intentos WHERE correo = :correo LIMIT 1"
            );
            $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log(
                'loginModel::registrarIntentoFallido - '
                . get_class($e) . ': ' . $e->getMessage()
                . ' en ' . $e->getFile() . ':' . $e->getLine()
            );
            throw ExcepcionApi::errorInterno(
                'No se pudo registrar el intento de inicio de sesión.'
            );
        }
    }

    /**
     * Reinicia el contador de intentos del correo: al autenticar con éxito o
     * al bloquear la cuenta. Persistido en BD.
     */
    private function limpiarIntentos(string $correo): void
    {
        try {
            $stmt = $this->conn_security->prepare(
                "DELETE FROM login_intentos WHERE correo = :correo"
            );
            $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Throwable $e) {
            // Un fallo al limpiar el contador no debe romper el login exitoso.
            error_log('loginModel::limpiarIntentos - ' . $e->getMessage());
        }
    }

    /**
     * Deshabilita la cuenta (estatus = 0).
     * Usada por la politica de bloqueo por intentos fallidos.
     */
    private function deshabilitarUsuario(): bool
    {
        try {
            $stmt = $this->conn_security->prepare(
                "UPDATE empleado SET estatus = 0 WHERE correo = :correo"
            );
            $stmt->bindValue(
                ':correo',
                $this->__get('correo'),
                PDO::PARAM_STR
            );
            $stmt->execute();
            return true;

        } catch (Throwable $e) {
            error_log(
                'loginModel::deshabilitarUsuario - '
                . get_class($e) . ': ' . $e->getMessage()
                . ' en ' . $e->getFile() . ':' . $e->getLine()
            );
            throw ExcepcionApi::errorInterno(
                'No se pudo deshabilitar la cuenta bloqueada.'
            );
        }
    }

    /**
     * Indica si un correo ya está registrado
     * (para validaciones futuras o formularios de alta).
     */
    private function existeUsuario(): ?array
    {
        if ($this->__get('correo') === null) {
            throw ExcepcionApi::validacion('El correo es obligatorio.');
        }

        try {
            $stmt = $this->conn_security->prepare(
                "SELECT correo FROM empleado WHERE correo = :correo LIMIT 1"
            );
            $stmt->bindValue(
                ':correo',
                $this->__get('correo'),
                PDO::PARAM_STR
            );
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila ?: null;

        } catch (Throwable $e) {
            error_log(
                'loginModel::existeUsuario - '
                . get_class($e) . ': ' . $e->getMessage()
                . ' en ' . $e->getFile() . ':' . $e->getLine()
            );
            throw ExcepcionApi::errorInterno(
                'No se pudo verificar si el correo existe.'
            );
        }
    }
}
