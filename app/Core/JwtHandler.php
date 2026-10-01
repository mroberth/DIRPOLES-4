<?php

namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use Throwable;
use PDO;

class JwtHandler
{
    private $atributos = [];

    public function __set($nombre, $valor)
    {
        $this->atributos[$nombre] = $valor;
    }

    public function __get($atributo)
    {
        return isset($this->atributos[$atributo]) ? $this->atributos[$atributo] : null;
    }

    /**
     * Obtiene conexión a la BD de seguridad
     */
    private function getSecurityConnection()
    {
        static $pdo = null;
        if ($pdo === null) {
            $pdo = new PDO(
                'mysql:host=' . env('DB_HOST', 'localhost') . ';dbname=' . env('DB_SECURITY_NAME') . ';charset=utf8mb4',
                env('DB_SECURITY_USER'),
                env('DB_SECURITY_PASS')
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $pdo;
    }

    /**
     * Extrae el token JWT del entorno (Header Authorization o Cookie).
     * @return string|null
     */
    public static function obtenerToken()
    {
        // 1. Intentar desde el encabezado Authorization (Bearer <token>)
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // 2. Intentar desde la cookie
        return $_COOKIE['jwt_token'] ?? null;
    }

    /**
     * Obtiene el refresh token de la cookie
     */
    public static function obtenerRefreshToken()
    {
        return $_COOKIE['refresh_token'] ?? null;
    }

    /**
     * Controlador de acciones de la clase.
     */
    public function manejarAccion($accion)
    {
        switch ($accion) {
            case 'generar':
                return $this->generarToken();
            case 'validar':
                return $this->validarToken();
            case 'generar_refresh':
                return $this->generarRefreshToken();
            case 'renovar_jwt':
                return $this->renovarJWT();
            case 'revocar_refresh':
                return $this->revocarRefreshToken();
            case 'limpiar_tokens':
                return $this->limpiarTokensExpirados();
            default:
                throw new Exception("Acción no reconocida: $accion");
        }
    }

    /**
     * Genera un nuevo token JWT basado en los atributos seteados.
     */
    private function generarToken()
    {
        try {
            $issuedAt = time();
            $expire = $issuedAt + (int) env('JWT_EXPIRATION', '3600');

            $payload = [
                'iat'  => $issuedAt,
                'exp'  => $expire,
                'data' => $this->__get('data') ?? []
            ];

            $keyPath = BASE_PATH . 'app/Config/Keys/jwt_private.pem';
            if (!file_exists($keyPath)) {
                throw new Exception("Llave privada JWT no encontrada.");
            }
            $privateKey = file_get_contents($keyPath);

            $jwt = JWT::encode($payload, $privateKey, 'RS256');

            return [
                'estado' => 'exito',
                'token' => $jwt,
                'expiracion' => $expire
            ];
        } catch (Throwable $e) {
            error_log("Error al generar JWT: " . $e->getMessage());
            return [
                'estado' => 'error',
                'mensaje' => 'No se pudo generar el token de seguridad.'
            ];
        }
    }

    /**
     * Genera un refresh token y lo almacena en la BD
     */
    /**
     * Genera un refresh token y almacena únicamente su hash SHA-256 en la BD.
     */
    private function generarRefreshToken()
    {
        try {
            $id_empleado = $this->__get('id_empleado');
            if (!$id_empleado) {
                throw new Exception("id_empleado es requerido para generar refresh token.");
            }

            // Generar token aleatorio seguro (cadena original para la cookie)
            $token = bin2hex(random_bytes(64));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + (int) env('REFRESH_EXPIRATION', '2592000'));

            // Almacenar únicamente el HASH en BD
            $pdo = $this->getSecurityConnection();
            $stmt = $pdo->prepare(
                "INSERT INTO refresh_tokens (id_empleado, token, expires_at) 
                 VALUES (:id_empleado, :token, :expires_at)"
            );
            $stmt->execute([
                ':id_empleado' => $id_empleado,
                ':token'       => $tokenHash,
                ':expires_at'  => $expiresAt
            ]);

            return [
                'estado' => 'exito',
                'token'  => $token,
                'expiracion' => time() + (int) env('REFRESH_EXPIRATION', '2592000')
            ];
        } catch (Throwable $e) {
            error_log("Error al generar refresh token: " . $e->getMessage());
            return [
                'estado' => 'error',
                'mensaje' => 'No se pudo generar el refresh token.'
            ];
        }
    }

    /**
     * Renueva el JWT usando un refresh token válido (verificado mediante su hash SHA-256).
     *
     * ROTACIÓN (one-time use): el refresh token recibido se marca como
     * revocado y se emite uno NUEVO en la misma transacción. Así, si un
     * atacante roba la cookie, el token deja de servir en cuanto el dueño
     * lo usa una vez (y el reuso del robado queda rechazado). El SELECT
     * ... FOR UPDATE evita que dos peticiones concurrentes usen el mismo
     * token a la vez.
     */
    private function renovarJWT()
    {
        $pdo = null;
        try {
            $refreshToken = $this->__get('refresh_token');
            if (!$refreshToken) {
                return ['estado' => 'error', 'mensaje' => 'Refresh token no proporcionado'];
            }

            $tokenHash = hash('sha256', $refreshToken);

            $pdo = $this->getSecurityConnection();
            $pdo->beginTransaction();

            // Bloquear la fila mientras dure la transacción (evita doble uso).
            $stmt = $pdo->prepare(
                "SELECT id, id_empleado, expires_at FROM refresh_tokens 
                 WHERE token = :token AND revoked = 0 AND expires_at > NOW()
                 FOR UPDATE"
            );
            $stmt->execute([':token' => $tokenHash]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $pdo->rollBack();
                return ['estado' => 'error', 'mensaje' => 'Refresh token inválido o expirado'];
            }

            // 1. Revocar el token usado (rotación).
            $stmt = $pdo->prepare(
                "UPDATE refresh_tokens SET revoked = 1 WHERE id = :id"
            );
            $stmt->execute([':id' => $record['id']]);

            // 2. Emitir un refresh token NUEVO (persistir solo su hash).
            $nuevoRefreshToken = bin2hex(random_bytes(64));
            $nuevoTokenHash = hash('sha256', $nuevoRefreshToken);
            $nuevaExpiracion = time() + (int) env('REFRESH_EXPIRATION', '2592000');

            $stmt = $pdo->prepare(
                "INSERT INTO refresh_tokens (id_empleado, token, expires_at) 
                 VALUES (:id_empleado, :token, :expires_at)"
            );
            $stmt->execute([
                ':id_empleado' => $record['id_empleado'],
                ':token'       => $nuevoTokenHash,
                ':expires_at'  => date('Y-m-d H:i:s', $nuevaExpiracion)
            ]);

            // 3. Generar nuevo JWT (no toca BD: si falla, se revierte todo).
            $jwtHandler = new JwtHandler();
            $jwtHandler->__set('data', [
                'id_empleado' => $record['id_empleado']
            ]);
            $jwtResult = $jwtHandler->manejarAccion('generar');

            if ($jwtResult['estado'] !== 'exito') {
                $pdo->rollBack();
                return $jwtResult;
            }

            $pdo->commit();

            return [
                'estado'             => 'exito',
                'token'              => $jwtResult['token'],
                'expiracion'         => $jwtResult['expiracion'],
                'refresh_token'      => $nuevoRefreshToken,
                'refresh_expiracion' => $nuevaExpiracion
            ];
        } catch (Throwable $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error al renovar JWT: " . $e->getMessage());
            return [
                'estado' => 'error',
                'mensaje' => 'Error al renovar el token.'
            ];
        }
    }

    /**
     * Revoca un refresh token buscando por su hash SHA-256
     */
    private function revocarRefreshToken()
    {
        try {
            $token = $this->__get('refresh_token');
            if (!$token) {
                return ['estado' => 'error', 'mensaje' => 'Token no proporcionado'];
            }

            $tokenHash = hash('sha256', $token);

            $pdo = $this->getSecurityConnection();
            $stmt = $pdo->prepare(
                "UPDATE refresh_tokens SET revoked = 1 WHERE token = :token"
            );
            $stmt->execute([':token' => $tokenHash]);

            return ['estado' => 'exito', 'mensaje' => 'Refresh token revocado'];
        } catch (Throwable $e) {
            error_log("Error al revocar refresh token: " . $e->getMessage());
            return ['estado' => 'error', 'mensaje' => 'Error al revocar token'];
        }
    }

    /**
     * Limpia tokens expirados o revocados de la BD
     */
    private function limpiarTokensExpirados()
    {
        try {
            $pdo = $this->getSecurityConnection();
            $stmt = $pdo->prepare(
                "DELETE FROM refresh_tokens WHERE expires_at < NOW() OR revoked = 1"
            );
            $stmt->execute();
            $eliminados = $stmt->rowCount();

            return [
                'estado' => 'exito',
                'mensaje' => "$eliminados tokens eliminados"
            ];
        } catch (Throwable $e) {
            error_log("Error al limpiar tokens: " . $e->getMessage());
            return ['estado' => 'error', 'mensaje' => 'Error al limpiar tokens'];
        }
    }

    /**
     * Valida un token JWT proporcionado.
     */
    private function validarToken()
    {
        try {
            $token = $this->__get('token');
            if (!$token) {
                return ['estado' => 'error', 'mensaje' => 'Token no proporcionado'];
            }

            $keyPath = BASE_PATH . 'app/Config/Keys/jwt_public.pem';
            if (!file_exists($keyPath)) {
                throw new Exception("Llave pública JWT no encontrada.");
            }
            $publicKey = file_get_contents($keyPath);

            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            return [
                'estado' => 'exito',
                'data' => (array) $decoded->data
            ];
        } catch (Throwable $e) {
            error_log("Error al validar JWT: " . $e->getMessage());
            return [
                'estado' => 'error',
                'mensaje' => 'Token inválido o expirado.'
            ];
        }
    }
}
