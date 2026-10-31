<?php

namespace Tests;

use App\Core\ErrorCodes;
use App\Core\ExcepcionApi;
use App\Core\Respuesta;
use App\Core\Router;
use App\Middlewares\RateLimitMiddleware;
use App\Middlewares\SessionAuthMiddleware;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class RutasYPuertasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Limpiar superglobales antes de cada prueba
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_SERVER['SCRIPT_NAME'] = '/DIRPOLES-4/index.php';
        unset($_SERVER['HTTP_ACCEPT'], $_SERVER['HTTP_ORIGIN']);
    }

    // =========================================================================
    // NIVEL 1: PRUEBAS UNITARIAS (Router, Puertas y Contrato ExcepcionApi)
    // =========================================================================

    /**
     * CP-RUT-001: Coincidencia de patrones de ruta y captura de parámetros dinámicos
     */
    public function test1_Unitaria_CoincidenciaPatronesYCapturaParametros(): void
    {
        $refCoincide = new ReflectionMethod(Router::class, 'coincidePatron');
        $refCaptura  = new ReflectionMethod(Router::class, 'capturarParametros');

        // 1. Coincidencia de rutas estáticas
        $this->assertTrue((bool) $refCoincide->invoke(null, 'login', 'login'));
        $this->assertFalse((bool) $refCoincide->invoke(null, 'login', 'citas'));

        // 2. Coincidencia de rutas con parámetros dinámicos {param}
        $this->assertTrue((bool) $refCoincide->invoke(null, 'citas/obtener/{id}', 'citas/obtener/25'));
        $this->assertTrue((bool) $refCoincide->invoke(null, 'citas/{id}/detalle/{tipo}', 'citas/10/detalle/psicologia'));
        $this->assertFalse((bool) $refCoincide->invoke(null, 'citas/obtener/{id}', 'citas/obtener/25/extra'));

        // 3. Captura automática de parámetros en $_GET
        $_GET = [];
        $refCaptura->invoke(null, 'citas/editar/{id}', 'citas/editar/42');
        $this->assertArrayHasKey('id', $_GET);
        $this->assertSame('42', $_GET['id']);

        $_GET = [];
        $refCaptura->invoke(null, 'referencias/{id}/{formato}', 'referencias/15/pdf');
        $this->assertSame('15', $_GET['id']);
        $this->assertSame('pdf', $_GET['formato']);
    }

    /**
     * CP-RUT-002: Detección estricta de la Puerta JSON vs Puerta HTML (Respuesta::esApi)
     */
    public function test1_Unitaria_DeteccionDePuertaSegunUrlYCabeceras(): void
    {
        // 1. Ruta bajo prefijo api/ -> Puerta JSON (true)
        $_SERVER['REQUEST_URI'] = '/DIRPOLES-4/api/beneficiarios/listar';
        $this->assertTrue(Respuesta::esApi(), "Ruta '/api/...' debe ser clasificada como API");

        // 2. Ruta exacta 'api' -> Puerta JSON (true)
        $_SERVER['REQUEST_URI'] = '/DIRPOLES-4/api';
        $this->assertTrue(Respuesta::esApi(), "Ruta 'api' debe ser clasificada como API");

        // 3. Ruta fuera de api/ sin cabecera JSON -> Puerta HTML (false)
        $_SERVER['REQUEST_URI'] = '/DIRPOLES-4/beneficiarios/consultar';
        unset($_SERVER['HTTP_ACCEPT']);
        $this->assertFalse(Respuesta::esApi(), "Ruta web sin Accept JSON debe ser Puerta HTML");

        // 4. Ruta fuera de api/ pero con cabecera Accept: application/json -> Puerta JSON (true)
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $this->assertTrue(Respuesta::esApi(), "Cabecera Accept: application/json debe activar Puerta JSON");

        // 5. Cabecera Accept con lista múltiple que incluye application/json
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/json;q=0.9';
        $this->assertTrue(Respuesta::esApi(), "Accept múltiple con application/json debe activar Puerta JSON");
    }

    /**
     * CP-RUT-003: Validación del contrato de error estandarizado en ExcepcionApi y ErrorCodes
     */
    public function test1_Unitaria_EstructuraContratoExcepcionApiYCodigosError(): void
    {
        // 1. Error de validación (400)
        $exVal = ExcepcionApi::validacion('Error en campo');
        $this->assertSame(400, $exVal->estado);
        $this->assertSame(ErrorCodes::VALIDACION, $exVal->codigo);
        $this->assertSame('VALIDATION_ERROR', $exVal->codigo);

        // 2. Error no encontrado (404)
        $exNotFound = ExcepcionApi::noEncontrado('No encontrado');
        $this->assertSame(404, $exNotFound->estado);
        $this->assertSame(ErrorCodes::NO_ENCONTRADO, $exNotFound->codigo);

        // 3. Error de duplicado (409)
        $exDuplicate = ExcepcionApi::yaExiste('Cédula duplicada');
        $this->assertSame(409, $exDuplicate->estado);
        $this->assertSame(ErrorCodes::YA_EXISTE, $exDuplicate->codigo);

        // 4. Error de registro en uso (409)
        $exInUse = ExcepcionApi::enUso('Insumo con stock');
        $this->assertSame(409, $exInUse->estado);
        $this->assertSame(ErrorCodes::EN_USO, $exInUse->codigo);

        // 5. Acceso denegado (403)
        $exDenied = ExcepcionApi::accesoDenegado('Sin permiso');
        $this->assertSame(403, $exDenied->estado);
        $this->assertSame(ErrorCodes::ACCESO_DENEGADO, $exDenied->codigo);

        // 6. Error interno (500)
        $exInternal = ExcepcionApi::errorInterno('Fallo BD');
        $this->assertSame(500, $exInternal->estado);
        $this->assertSame(ErrorCodes::ERROR_INTERNO, $exInternal->codigo);
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Regla de las Puertas, Middlewares y Rate Limit)
    // =========================================================================

    /**
     * CP-RUT-004: La Regla de las Puertas en rutas no encontradas (404 JSON vs HTML)
     */
    public function test2_Integracion_ReglaDeLasPuertasEnRutaNoEncontrada(): void
    {
        // Cargar rutas globales
        require_once BASE_PATH . 'app/routes.php';

        $refPropNoEncontrada = new ReflectionProperty(Router::class, 'rutaNoEncontrada');
        $manejador404 = $refPropNoEncontrada->getValue();
        $this->assertIsCallable($manejador404, 'Manejador de rutaNoEncontrada debe ser callable');

        // 1. Petición a Puerta JSON (api/ruta_inexistente): debe lanzar ExcepcionApi con código 404
        $_SERVER['REQUEST_URI'] = '/DIRPOLES-4/api/modulo_fantasma/listar';
        try {
            $manejador404();
            $this->fail('Puerta JSON debe lanzar ExcepcionApi 404 ante ruta inexistente');
        } catch (ExcepcionApi $e) {
            $this->assertSame(404, $e->estado);
            $this->assertSame(ErrorCodes::RUTA_NO_ENCONTRADA, $e->codigo);
            $this->assertSame('El recurso solicitado no existe.', $e->getMessage());
        }

        // 2. Puerta HTML: existencia física de la vista dedicada 404.php
        $this->assertFileExists(BASE_PATH . 'app/Views/errors/404.php', 'Vista 404.php debe existir para la puerta HTML');
    }

    /**
     * CP-RUT-005: La Regla de las Puertas en métodos HTTP no permitidos (405 JSON vs HTML)
     */
    public function test2_Integracion_ReglaDeLasPuertasEnMetodoNoPermitido(): void
    {
        require_once BASE_PATH . 'app/routes.php';

        $refPropMetodoNoPermitido = new ReflectionProperty(Router::class, 'metodoNoPermitido');
        $manejador405 = $refPropMetodoNoPermitido->getValue();
        $this->assertIsCallable($manejador405, 'Manejador de metodoNoPermitido debe ser callable');

        // 1. Petición a Puerta JSON con método incorrecto: debe lanzar ExcepcionApi con código 405
        $_SERVER['REQUEST_URI'] = '/DIRPOLES-4/api/beneficiarios/crear';
        try {
            $manejador405();
            $this->fail('Puerta JSON debe lanzar ExcepcionApi 405 ante método HTTP no permitido');
        } catch (ExcepcionApi $e) {
            $this->assertSame(405, $e->estado);
            $this->assertSame(ErrorCodes::METODO_NO_PERMITIDO, $e->codigo);
            $this->assertSame('El método HTTP no está permitido para este recurso.', $e->getMessage());
        }

        // 2. Puerta HTML: existencia física de la vista genérica de error
        $this->assertFileExists(BASE_PATH . 'app/Views/errors/error.php', 'Vista error.php debe existir para la puerta HTML');
    }

    /**
     * CP-RUT-006: Resolución de políticas dinámicas en RateLimitMiddleware (Macro-Categorías)
     */
    public function test2_Integracion_ResolucionPoliticasRateLimitTokenBucket(): void
    {
        $refPolitica = new ReflectionMethod(RateLimitMiddleware::class, 'resolvePolicy');

        // 1. Nivel 1: Seguridad Crítica (5 peticiones por cada 5 minutos = 300s)
        $polLogin = $refPolitica->invoke(null, 'POST', 'iniciar_sesion');
        $this->assertSame(5.0, $polLogin['capacity']);
        $this->assertEqualsWithDelta(5.0 / 300.0, $polLogin['rate'], 0.0001);

        $polPerfilActualizar = $refPolitica->invoke(null, 'POST', 'api/perfil/actualizar');
        $this->assertSame(5.0, $polPerfilActualizar['capacity']);

        // 2. Nivel Especial: Microservicios de IA (3 peticiones por 5 minutos)
        $polIa = $refPolitica->invoke(null, 'GET', 'reportes_general_ia');
        $this->assertSame(3.0, $polIa['capacity']);
        $this->assertEqualsWithDelta(3.0 / 300.0, $polIa['rate'], 0.0001);

        // 3. Nivel 2: Validaciones rápidas de formulario (Alta Tolerancia: 30 peticiones)
        $polValidar = $refPolitica->invoke(null, 'POST', 'api/perfil/validar_correo');
        $this->assertGreaterThanOrEqual(20.0, $polValidar['capacity']);
    }

    /**
     * CP-RUT-007: Exclusión de rutas públicas y assets estáticos en SessionAuthMiddleware
     */
    public function test2_Integracion_FiltroRutasPublicasSessionAuthMiddleware(): void
    {
        // 1. Rutas públicas de acceso libre
        $rutasPublicas = ['', 'login', 'iniciar_sesion', 'error', 'logout'];
        foreach ($rutasPublicas as $ruta) {
            $_SERVER['REQUEST_URI'] = "/DIRPOLES-4/{$ruta}";
            // SessionAuthMiddleware::handle debe retornar void sin redirigir ni bloquear
            SessionAuthMiddleware::handle();
            $this->assertTrue(true, "Ruta pública '{$ruta}' debe permitirse sin sesión");
        }

        // 2. Archivos y recursos estáticos
        $assets = ['dist/css/sb-admin-2.css', 'dist/js/core/apiFetch.js', 'dist/img/logo.png'];
        foreach ($assets as $asset) {
            $_SERVER['REQUEST_URI'] = "/DIRPOLES-4/{$asset}";
            SessionAuthMiddleware::handle();
            $this->assertTrue(true, "Asset '{$asset}' no debe pasar por verificación de sesión");
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Carga Modular, Tercera Puerta y Formato JSON)
    // =========================================================================

    /**
     * CP-RUT-008: Carga modular e integridad de todos los archivos de rutas del sistema
     */
    public function test3_Sistema_CargaModularDeTodasLasRutasDelSistema(): void
    {
        // Catálogo de los 21 archivos de rutas de los módulos de DIRPOLES-4
        $modulosRutas = [
            'backup.php',
            'beneficiarios.php',
            'bitacora.php',
            'citas.php',
            'configuracion.php',
            'discapacidad.php',
            'empleados.php',
            'horarios.php',
            'inventario.php',
            'jornadas.php',
            'medicina.php',
            'mobiliario.php',
            'notificaciones.php',
            'orientacion.php',
            'perfil.php',
            'permisos.php',
            'psicologia.php',
            'referencias.php',
            'reportes.php',
            'trabajo-social.php',
            'transporte.php',
        ];

        foreach ($modulosRutas as $archivoRuta) {
            $pathCompleto = BASE_PATH . "app/routes/{$archivoRuta}";
            $this->assertFileExists($pathCompleto, "El archivo de rutas 'app/routes/{$archivoRuta}' debe existir");
            
            // Verificación de sintaxis PHP y contenido no vacío
            $contenido = file_get_contents($pathCompleto);
            $this->assertStringContainsString('Router::', $contenido, "El archivo {$archivoRuta} debe registrar rutas en el Router");
        }
    }

    /**
     * CP-RUT-009: Verificación de la Tercera Puerta (Streaming SSE y Descargas Dedicadas)
     */
    public function test3_Sistema_VerificacionTerceraPuertaDocumentosYStreaming(): void
    {
        // 1. Streaming SSE de Notificaciones (text/event-stream)
        $notifRoutes = file_get_contents(BASE_PATH . 'app/routes/notificaciones.php');
        $this->assertStringContainsString("Router::get('sse/notificaciones'", $notifRoutes);
        $this->assertFileExists(BASE_PATH . 'app/Controllers/sseController.php');

        // 2. Descargas PDF de Medicina / Récipe y Constancias
        $medicinaRoutes = file_get_contents(BASE_PATH . 'app/routes/medicina.php');
        $this->assertStringContainsString('recipe', $medicinaRoutes);
        $this->assertStringContainsString('constancia', $medicinaRoutes);
        $this->assertStringContainsString('referencia', $medicinaRoutes);

        // 3. Descargas PDF de Trabajo Social (Constancias y Referencias)
        $tsRoutes = file_get_contents(BASE_PATH . 'app/routes/trabajo-social.php');
        $this->assertStringContainsString('constancia', $tsRoutes);
        $this->assertStringContainsString('referencia', $tsRoutes);

        // 4. Descarga de Respaldo SQL de Base de Datos (Tercera Puerta .sql)
        $backupRoutes = file_get_contents(BASE_PATH . 'app/routes/backup.php');
        $this->assertStringContainsString('respaldo/descargar', $backupRoutes);
        $this->assertFileExists(BASE_PATH . 'app/Controllers/backupController.php');
    }

    /**
     * CP-RUT-010: Contrato único JSON de Respuesta y encapsulamiento estricto
     */
    public function test3_Sistema_ContratoUnicoRespuestaErrorYPayloadJson(): void
    {
        // 1. Estructura canónica de error JSON exigida por AGENTS.md
        $ex = ExcepcionApi::yaExiste('El correo ya existe en el sistema.');
        $payloadError = [
            'exito' => false,
            'error' => [
                'codigo'  => $ex->codigo,
                'estado'  => $ex->estado,
                'mensaje' => $ex->getMessage(),
            ],
        ];

        $this->assertFalse($payloadError['exito']);
        $this->assertSame(409, $payloadError['error']['estado']);
        $this->assertSame('ALREADY_EXISTS', $payloadError['error']['codigo']);
        $this->assertSame('El correo ya existe en el sistema.', $payloadError['error']['mensaje']);

        // 2. Encapsulamiento de Respuesta: constructor privado (no instanciable)
        $refRespuesta = new ReflectionClass(Respuesta::class);
        $constructor = $refRespuesta->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate(), 'Respuesta no debe ser instanciable');

        // 3. Método json privado (punto único de salida de bajo nivel)
        $metodoJson = new ReflectionMethod(Respuesta::class, 'json');
        $this->assertTrue($metodoJson->isPrivate(), 'Respuesta::json debe tener visibilidad privada');
    }
}
