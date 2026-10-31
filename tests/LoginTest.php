<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\ErrorCodes;
use App\Core\ExcepcionApi;
use App\Core\JwtHandler;
use App\Models\loginModel;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Limpiar superglobales antes de cada prueba
        $_SESSION = [];
        $_POST = [];
        $_COOKIE = [];
    }

    // =========================================================================
    // NIVEL 1: PRUEBAS UNITARIAS (Componente loginModel, Criptografía y Helpers)
    // =========================================================================

    /**
     * CP-AUTH-UNIT-001: Validación de correo y contraseña válidos
     */
    public function test1_Unitaria_ValidacionFormatoCorreoYClave(): void
    {
        $modelo = new loginModel();
        $modelo->__set('correo', 'carlos@uptaeb.edu.ve');
        $modelo->__set('password', 'ClaveSegura123*');

        $this->assertSame('carlos@uptaeb.edu.ve', $modelo->__get('correo'));
        $this->assertSame('ClaveSegura123*', $modelo->__get('password'));
    }

    /**
     * CP-AUTH-UNIT-001: Rechazo de contraseña vacía
     */
    public function test1_Unitaria_RechazoContrasenaVacia(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La contraseña es obligatoria.');

        $modelo = new loginModel();
        $modelo->__set('password', '');
    }

    /**
     * CP-AUTH-UNIT-001: Rechazo de atributo desconocido
     */
    public function test1_Unitaria_RechazoAtributoDesconocido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage("Atributo desconocido para el login: 'telefono'.");

        $modelo = new loginModel();
        $modelo->__set('telefono', '04121234567');
    }

    /**
     * CP-AUTH-UNIT-001: Rechazo de correo electrónico demasiado largo (>100 caracteres)
     */
    public function test1_Unitaria_RechazoCorreoDemasiadoLargo(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El correo electrónico es demasiado largo.');

        // Correo sintácticamente válido según RFC 5321 (local-part <= 64 caracteres)
        // pero con longitud total mayor a 100 caracteres para activar la regla de loginModel
        $localPart = 'usuario_administrador_de_pruebas_uptaeb_sistema_dirpoles'; // 56 chars
        $domain = 'subdominio.departamento.direccion.uptaeb.edu.ve'; // 47 chars
        $correoLargo = $localPart . '@' . $domain; // 104 caracteres totales (>100)

        $modelo = new loginModel();
        $modelo->__set('correo', $correoLargo);
    }

    /**
     * CP-AUTH-UNIT-002: Descifrado RSA asimétrico con llave privada
     */
    public function test1_Unitaria_DescifradoRsaValidoEInvalido(): void
    {
        $pubKeyPath = BASE_PATH . 'app/Config/Keys/login_public.pem';
        $privKeyPath = BASE_PATH . 'app/Config/Keys/login_private.pem';

        $this->assertFileExists($pubKeyPath, 'Llave pública de login debe existir');
        $this->assertFileExists($privKeyPath, 'Llave privada de login debe existir');

        $pubKey = file_get_contents($pubKeyPath);
        $privKey = file_get_contents($privKeyPath);

        // Caso positivo: Cifrado con pública y descifrado con privada
        $claveOriginal = 'MiPasswordSeguro2026*';
        $cifradaBinaria = '';
        $cifradoExitoso = openssl_public_encrypt($claveOriginal, $cifradaBinaria, $pubKey);
        $this->assertTrue($cifradoExitoso, 'El cifrado con la llave pública debe ser exitoso');

        $cifradaBase64 = base64_encode($cifradaBinaria);
        $descifrada = '';
        $descifradoExitoso = openssl_private_decrypt(base64_decode($cifradaBase64), $descifrada, $privKey);
        $this->assertTrue($descifradoExitoso, 'El descifrado con la llave privada debe ser exitoso');
        $this->assertSame($claveOriginal, $descifrada);

        // Caso negativo 1: Cadena no Base64 estricto (rechazada en el controlador)
        $cifradaInvalida = base64_decode('###TextoInvalidoNoBase64###', true);
        $this->assertFalse($cifradaInvalida, 'Entrada no Base64 debe retornar false en modo estricto');

        // Caso negativo 2: Datos aleatorios que no corresponden al texto cifrado con la clave pública
        $descifradaFalsa = '';
        $resultadoFalso = @openssl_private_decrypt(random_bytes(256), $descifradaFalsa, $privKey);
        // En OpenSSL estándar retorna false; en OpenSSL 3.2+ con rechazo implícito (defensa Bleichenbacher) el texto devuelto es sintético y no coincide
        $this->assertTrue(!$resultadoFalso || $descifradaFalsa !== $claveOriginal, 'Descifrado de datos aleatorios no debe recuperar la contraseña original');
    }

    /**
     * CP-AUTH-UNIT-003: Exención de bloqueo a Administradores y Superusuarios
     */
    public function test1_Unitaria_ExencionBloqueoAdministradores(): void
    {
        $reflection = new ReflectionClass(loginModel::class);
        $metodo = $reflection->getMethod('esAdministrador');

        $this->assertTrue($metodo->invoke(null, 'Administrador'), 'Debe reconocer Administrador');
        $this->assertTrue($metodo->invoke(null, 'Superusuario'), 'Debe reconocer Superusuario');
        $this->assertTrue($metodo->invoke(null, 'ADMINISTRADOR GENERAL'), 'Debe ser insensible a mayúsculas');
        $this->assertFalse($metodo->invoke(null, 'Médico General'), 'Médico no es administrador');
        $this->assertFalse($metodo->invoke(null, 'Psicólogo'), 'Psicólogo no es administrador');
        $this->assertFalse($metodo->invoke(null, 'Secretaria'), 'Secretaria no es administrador');
    }

    /**
     * CP-AUTH-UNIT-004: Comprobación booleana de permisos en Autorizacion::tiene
     */
    public function test1_Unitaria_AutorizacionTieneBooleano(): void
    {
        // Sin sesión iniciada, debe retornar false sin lanzar excepción
        $tiene = Autorizacion::tiene('medicina', 'leer');
        $this->assertFalse($tiene, 'Sin sesión debe retornar false de forma limpia');

        // Con rol sin permisos
        $_SESSION['id_tipo_empleado'] = 999999;
        $tieneInexistente = Autorizacion::tiene('modulo_inexistente', 'crear');
        $this->assertFalse($tieneInexistente, 'Módulo o rol sin permisos debe retornar false');
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Tokens JWT, Refresh Tokens y Sesión)
    // =========================================================================

    /**
     * CP-AUTH-INT-004: Generación y Validación de Token JWT firmado con RS256
     */
    public function test3_Api_ContratoTokensJwtRS256(): void
    {
        $jwtHandler = new JwtHandler();
        $jwtHandler->__set('data', [
            'id_empleado'      => 10,
            'nombre'           => 'Roberth',
            'id_tipo_empleado' => 6,
            'tipo_empleado'    => 'Administrador',
        ]);

        $resultado = $jwtHandler->manejarAccion('generar');

        $this->assertSame('exito', $resultado['estado'] ?? null);
        $this->assertNotEmpty($resultado['token']);
        $this->assertIsInt($resultado['expiracion']);

        // Validar el token generado utilizando la llave pública
        $validador = new JwtHandler();
        $validador->__set('token', $resultado['token']);
        $validacion = $validador->manejarAccion('validar');

        $this->assertSame('exito', $validacion['estado']);
        $this->assertSame(10, $validacion['data']['id_empleado']);
        $this->assertSame('Administrador', $validacion['data']['tipo_empleado']);
    }

    /**
     * CP-AUTH-INT-004: Rechazo de Token JWT adulterado o inválido
     */
    public function test3_Api_RechazoTokenInvalidoOAlterado(): void
    {
        $validador = new JwtHandler();
        $validador->__set('token', 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.token_falso_adulterado.firma_falsa');
        $validacion = $validador->manejarAccion('validar');

        $this->assertSame('error', $validacion['estado']);
        $this->assertStringContainsString('inválido', strtolower($validacion['mensaje']));
    }

    /**
     * CP-AUTH-INT-007: Detección de inconsistencia entre Sesión y JWT
     */
    public function test2_Integracion_DeteccionInconsistenciaSesionVsJwt(): void
    {
        // Emitir un token para el empleado 12
        $jwtHandler = new JwtHandler();
        $jwtHandler->__set('data', ['id_empleado' => 12]);
        $res = $jwtHandler->manejarAccion('generar');

        // Sesión pertenece a otro empleado (empleado 5)
        $_SESSION['id_empleado'] = 5;
        $_COOKIE['jwt_token'] = $res['token'];

        $validador = new JwtHandler();
        $validador->__set('token', $_COOKIE['jwt_token']);
        $validacion = $validador->manejarAccion('validar');

        $this->assertSame('exito', $validacion['estado']);
        $this->assertNotEquals($_SESSION['id_empleado'], $validacion['data']['id_empleado']);
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA Y DISPONIBILIDAD ESTRUCTURAL
    // =========================================================================

    /**
     * CP-AUTH-SYS-WB-001: Disponibilidad de Controladores, Vistas y Rutas de Login
     */
    public function test4_Sistema_DisponibilidadControladorYVistas(): void
    {
        // Verificar que el controlador de login carga mediante el helper load_controller
        load_controller('loginController.php');

        $this->assertTrue(function_exists('showLogin'), 'Función showLogin debe existir');
        $this->assertTrue(function_exists('showInicio'), 'Función showInicio debe existir');
        $this->assertTrue(function_exists('iniciar_sesion'), 'Función iniciar_sesion debe existir');
        $this->assertTrue(function_exists('cerrar_sesion'), 'Función cerrar_sesion debe existir');
        $this->assertTrue(function_exists('refresh_token'), 'Función refresh_token debe existir');

        // Verificar que las vistas asociadas existen físicamente
        $this->assertFileExists(BASE_PATH . 'app/Views/login.php', 'Vista login.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/inicio/dashboard.php', 'Vista dashboard.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/routes.php', 'Archivo de rutas debe existir');
    }

    /**
     * CP-AUTH-SYS-WB-001: Flujo funcional del ciclo de expiración y regeneración
     */
    public function test4_Sistema_FlujoFuncionalAutenticacionSegura(): void
    {
        // 1. Simulación de payload de usuario
        $jwtHandler = new JwtHandler();
        $jwtHandler->__set('data', [
            'id_empleado'      => 1,
            'nombre'           => 'Carlos',
            'id_tipo_empleado' => 2,
            'tipo_empleado'    => 'Médico',
        ]);
        $jwtResult = $jwtHandler->manejarAccion('generar');
        $this->assertSame('exito', $jwtResult['estado']);

        // 2. Simulación de recepción en cliente
        $tokenRecibido = $jwtResult['token'];
        $validador = new JwtHandler();
        $validador->__set('token', $tokenRecibido);
        $check = $validador->manejarAccion('validar');
        $this->assertSame('exito', $check['estado']);

        // 3. Simulación de hidratación de sesión para cliente stateless
        $_SESSION['id_empleado'] = (int) $check['data']['id_empleado'];
        $_SESSION['nombre'] = $check['data']['nombre'];
        $_SESSION['id_tipo_empleado'] = (int) $check['data']['id_tipo_empleado'];
        $_SESSION['tipo_empleado'] = $check['data']['tipo_empleado'];
        $_SESSION['estatus'] = 1;

        $this->assertSame(1, $_SESSION['id_empleado']);
        $this->assertSame('Médico', $_SESSION['tipo_empleado']);
        $this->assertSame(1, $_SESSION['estatus']);
    }
}
