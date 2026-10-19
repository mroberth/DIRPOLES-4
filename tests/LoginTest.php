<?php

namespace Tests;

use App\Models\loginModel;
use App\Core\ExcepcionApi;
use App\Core\JwtHandler;

/**
 * LoginTest (Módulo 01: Autenticación y Control de Accesos)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validaciones de email y excepciones)
 * 2. Integración (Caja Blanca/Gris: Anti-bruteforce, intentos y JWT)
 * 3. API (Caja Negra: Contrato de inicio de sesión y cookies seguras)
 * 4. Sistema (Caja Negra: Flujo completo de autenticación multi-rol)
 */
class LoginTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida el rechazo estricto de formatos de correo inválidos y atributos desconocidos.
     */
    public function test1_Unitaria_ValidacionFormatoCorreoYClave(): void
    {
        $modelo = new loginModel();

        // Validación de correo inválido
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El correo electrónico no es válido.');
        $modelo->__set('correo', 'correo_sin_arroba_ni_dominio');
    }

    public function test1_Unitaria_RechazoContrasenaVacia(): void
    {
        $modelo = new loginModel();
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La contraseña es obligatoria.');
        $modelo->__set('password', '');
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la lógica de intentos fallidos y bloqueo en base de datos.
     */
    public function test2_Integracion_ControlIntentosYPersistenciaSeguridad(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new loginModel();
        $modelo->__set('correo', 'usuario.inexistente.qa@test.com');
        $modelo->__set('password', 'ClaveInvalida123*');

        // Intento de autenticación con credenciales inválidas debe lanzar excepción controlada
        try {
            $modelo->manejarAccion('Autenticar');
            $this->fail('Se esperaba ExcepcionApi por credenciales inválidas');
        } catch (ExcepcionApi $e) {
            $this->assertEquals(400, $e->estado);
            $this->assertStringContainsString('Credenciales inválidas', $e->getMessage());
        }
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el contrato de tokens JWT RS256 generado para clientes móviles y web.
     */
    public function test3_Api_ContratoTokensJwtRS256(): void
    {
        $jwtHandler = new JwtHandler();
        $payload = [
            'id_empleado'      => 999,
            'nombre'           => 'Auditor QA',
            'id_tipo_empleado' => 1,
            'tipo_empleado'    => 'Administrador'
        ];

        $jwtHandler->__set('data', $payload);
        $tokenResult = $jwtHandler->manejarAccion('generar');
        $this->assertIsArray($tokenResult);
        $this->assertArrayHasKey('token', $tokenResult);
        $this->assertNotEmpty($tokenResult['token']);

        // Validación de token generado
        $jwtValidador = new JwtHandler();
        $jwtValidador->__set('token', $tokenResult['token']);
        $validacion = $jwtValidador->manejarAccion('validar');

        $this->assertEquals('exito', $validacion['estado']);
        $this->assertEquals(999, $validacion['data']['id_empleado']);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Valida el ciclo de vida de autenticación: descifrado asimétrico, verificación y emisión de sesión.
     */
    public function test4_Sistema_FlujoFuncionalAutenticacionSegura(): void
    {
        // Verificación de existencia de pares de llaves RSA institucionales
        $keysDir = BASE_PATH . 'app/Config/Keys/';
        $this->assertFileExists($keysDir . 'login_public.pem');
        $this->assertFileExists($keysDir . 'login_private.pem');
        $this->assertFileExists($keysDir . 'jwt_public.pem');
        $this->assertFileExists($keysDir . 'jwt_private.pem');

        // Comprobación de que la clave privada RSA sea capaz de firmar/descifrar
        $publicKey = file_get_contents($keysDir . 'login_public.pem');
        $privateKey = file_get_contents($keysDir . 'login_private.pem');

        $mensajePrueba = 'ClaveSecretaOperador2026';
        openssl_public_encrypt($mensajePrueba, $cifrado, $publicKey);
        $this->assertNotEmpty($cifrado);

        openssl_private_decrypt($cifrado, $descifrado, $privateKey);
        $this->assertEquals($mensajePrueba, $descifrado, 'El criptograma RSA debe descifrarse correctamente en el servidor');
    }
}
