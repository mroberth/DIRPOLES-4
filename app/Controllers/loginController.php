<?php

use App\Core\Bitacora;
use App\Core\ErrorCodes;
use App\Core\ExcepcionApi;
use App\Core\JwtHandler;
use App\Core\Respuesta;
use App\Models\PermisosModel;
use App\Models\loginModel;

/**
 * Controlador de login — SOLO funciones (regla del esqueleto):
 *   1. Lee HTTP y setea atributos del modelo con __set.
 *   2. Llama manejarAccion() (el modelo lanza ExcepcionApi si algo falla).
 *   3. Efectos secundarios con los helpers de una línea (Bitacora).
 *   4. Responde con Respuesta (puerta JSON) o redirección/vista (puerta HTML).
 * Sin try/catch de formateo: si una excepción escapa, el handler global
 * de index.php la convierte en la respuesta correcta según la puerta.
 */

function showLogin()
{
    require_once BASE_PATH . '/app/Views/login.php';
}

function showInicio()
{
    // Permisos RBAC para poblar el sidebar. Si algo falla, la ExcepcionApi
    // sube al handler global, que muestra la página de error HTML.
    $permisosModel = new PermisosModel();
    $permisosModel->__set('Rol', $_SESSION['id_tipo_empleado']);

    $_SESSION['modulosPermitidos'] = $permisosModel->manejarAccion('obtenerPermisosSidebar');

    require_once BASE_PATH . '/app/Views/inicio/dashboard.php';
}

function iniciar_sesion()
{
    $correo   = trim((string) filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $password = (string) ($_POST['password'] ?? '');

    // --- Descifrado RSA: la contraseña viaja cifrada desde el cliente ---
    $keyPath = BASE_PATH . 'app/Config/Keys/login_private.pem';
    if (!file_exists($keyPath)) {
        throw ExcepcionApi::errorInterno('Llave privada de login no encontrada.');
    }

    $cifrada = base64_decode($password, true);
    $plana   = '';
    if ($cifrada === false || !openssl_private_decrypt($cifrada, $plana, (string) file_get_contents($keyPath))) {
        throw ExcepcionApi::validacion('No se pudo procesar la contraseña. Vuelve a intentarlo.');
    }

    // --- Autenticación: el modelo valida y lanza ExcepcionApi ante cualquier fallo ---
    $modelo = new loginModel();
    $modelo->__set('correo', $correo);
    $modelo->__set('password', $plana);
    $usuario = $modelo->manejarAccion('Autenticar');

    // --- Éxito: poblar la sesión ---
    // Regenerar el ID de sesión ANTES de guardar la identidad evita
    // "session fixation": un atacante que conociera el ID previo (p. ej.
    // forzado vía URL) ya no puede reutilizarlo tras el login.
    session_regenerate_id(true);

    $_SESSION['id_empleado']      = (int) $usuario['id_empleado'];
    $_SESSION['nombre']           = $usuario['nombre'];
    $_SESSION['apellido']         = $usuario['apellido'];
    $_SESSION['correo']           = $usuario['correo'];
    $_SESSION['id_tipo_empleado'] = (int) $usuario['id_tipo_empleado'];
    $_SESSION['tipo_empleado']    = $usuario['nombre_tipo'];
    $_SESSION['estatus']          = (int) $usuario['estatus'];

    // Auditoría (helper de una línea; nunca lanza)
    Bitacora::registrar('Login', 'Inicio de sesión', "El empleado {$_SESSION['nombre']} ha iniciado sesión.");

    // --- JWT + refresh token en cookies HttpOnly ---
    $jwtHandler = new JwtHandler();
    $jwtHandler->__set('data', [
        'id_empleado'      => $_SESSION['id_empleado'],
        'nombre'           => $_SESSION['nombre'],
        'id_tipo_empleado' => $_SESSION['id_tipo_empleado'],
        'tipo_empleado'    => $_SESSION['tipo_empleado'],
    ]);
    $jwtResult = $jwtHandler->manejarAccion('generar');
    if (($jwtResult['estado'] ?? '') !== 'exito') {
        throw ExcepcionApi::errorInterno('No se pudo emitir el token de sesión.');
    }

    setcookie('jwt_token', $jwtResult['token'], [
        'expires'  => $jwtResult['expiracion'],
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    $jwtHandler->__set('id_empleado', $_SESSION['id_empleado']);
    $refreshResult = $jwtHandler->manejarAccion('generar_refresh');
    if (($refreshResult['estado'] ?? '') === 'exito') {
        setcookie('refresh_token', $refreshResult['token'], [
            'expires'  => $refreshResult['expiracion'],
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    Respuesta::exito([
        'titulo'  => '¡Bienvenido!',
        'mensaje' => 'Has iniciado sesión correctamente.',
        'jwt_exp' => (int) env('JWT_EXPIRATION', '3600'),
    ]);
}

function cerrar_sesion()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Auditoría ANTES de destruir la sesión (Bitacora lee id_empleado de $_SESSION).
    if (isset($_SESSION['id_empleado'])) {
        $nombre = $_SESSION['nombre'] ?? 'Usuario desconocido';
        Bitacora::registrar('Login', 'Cierre de sesión', "El empleado {$nombre} ha cerrado sesión.");
    }

    // Destruir sesión completamente
    session_unset();
    session_destroy();

    // Eliminar cookie de sesión
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 86400, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    // Revocar el refresh token en BD (JwtHandler nunca lanza) y limpiar cookies
    if (isset($_COOKIE['refresh_token'])) {
        $jwtHandler = new JwtHandler();
        $jwtHandler->__set('refresh_token', $_COOKIE['refresh_token']);
        $jwtHandler->manejarAccion('revocar_refresh');
    }
    setcookie('jwt_token', '', time() - 3600, '/');
    setcookie('refresh_token', '', time() - 3600, '/');

    // Redirigir (puerta HTML)
    header('Location: ' . BASE_URL . 'login?logout=true');
    exit;
}

/** Renueva el JWT usando el refresh token de la cookie (puerta JSON). */
function refresh_token()
{
    $refreshToken = $_COOKIE['refresh_token'] ?? null;
    if (!$refreshToken) {
        throw new ExcepcionApi(ErrorCodes::TOKEN_FALTANTE, 401, 'No hay refresh token.');
    }

    $jwtHandler = new JwtHandler();
    $jwtHandler->__set('refresh_token', $refreshToken);
    $resultado = $jwtHandler->manejarAccion('renovar_jwt');

    if (($resultado['estado'] ?? '') !== 'exito') {
        throw new ExcepcionApi(
            ErrorCodes::TOKEN_INVALIDO,
            401,
            $resultado['mensaje'] ?? 'No se pudo renovar el token.'
        );
    }

    setcookie('jwt_token', $resultado['token'], [
        'expires'  => $resultado['expiracion'],
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    // Rotación: el refresh token anterior ya quedó revocado en BD; guardar el nuevo.
    if (!empty($resultado['refresh_token'])) {
        setcookie('refresh_token', $resultado['refresh_token'], [
            'expires'  => $resultado['refresh_expiracion'],
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    Respuesta::exito([
        'mensaje' => 'Token renovado exitosamente.',
        'jwt_exp' => (int) env('JWT_EXPIRATION', '3600'),
    ]);
}
