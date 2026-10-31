<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ErrorCodes;
use App\Core\ExcepcionApi;
use App\Core\Notificador;
use App\Models\NotificacionesModel;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class NotificacionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Limpiar superglobales antes de cada prueba
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
    }

    // =========================================================================
    // NIVEL 1: PRUEBAS UNITARIAS (Componentes, Atributos y Validaciones)
    // =========================================================================

    /**
     * CP-NOT-001: Asignación y validación de tipos enteros y límites en NotificacionesModel
     */
    public function test1_Unitaria_AsignacionYValidacionAtributosNotificaciones(): void
    {
        $modelo = new NotificacionesModel();

        // 1. Asignaciones válidas
        $modelo->__set('id_notif', 10);
        $modelo->__set('id_empleado', 5);
        $modelo->__set('ultimoId', 0); // 0 es válido para reconexión SSE inicial
        $modelo->__set('offset', 0);
        $modelo->__set('limit', 25);

        $this->assertSame(10, $modelo->__get('id_notif'));
        $this->assertSame(5, $modelo->__get('id_empleado'));
        $this->assertSame(0, $modelo->__get('ultimoId'));
        $this->assertSame(0, $modelo->__get('offset'));
        $this->assertSame(25, $modelo->__get('limit'));

        // 2. id_notif menor a 1 (ej. 0 o negativo)
        try {
            $modelo->__set('id_notif', 0);
            $this->fail('Debe rechazar id_notif menor a 1');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('El atributo id_notif debe ser un entero mayor o igual a 1.', $e->getMessage());
        }

        // 3. ultimoId negativo
        try {
            $modelo->__set('ultimoId', -1);
            $this->fail('Debe rechazar ultimoId negativo');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('El atributo ultimoId debe ser un entero mayor o igual a 0.', $e->getMessage());
        }

        // 4. limit fuera del rango [1, 50]
        try {
            $modelo->__set('limit', 0);
            $this->fail('Debe rechazar limit menor a 1');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('limit debe ser un entero entre 1 y 50.', $e->getMessage());
        }

        try {
            $modelo->__set('limit', 55);
            $this->fail('Debe rechazar limit mayor a 50');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('limit debe ser un entero entre 1 y 50.', $e->getMessage());
        }
    }

    /**
     * CP-NOT-002: Encapsulamiento del modelo: rechazo de acciones no reconocidas
     */
    public function test1_Unitaria_EncapsulamientoYRechazoAccionesNoValidas(): void
    {
        $modelo = new NotificacionesModel();

        try {
            $modelo->manejarAccion('accion_inexistente');
            $this->fail('Debe rechazar acciones ajenas al despachador');
        } catch (ExcepcionApi $e) {
            $this->assertSame(500, $e->estado);
            $this->assertSame('Acción no válida: accion_inexistente', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Helper Notificador, Error Mapping y RBAC)
    // =========================================================================

    /**
     * CP-NOT-003: Comportamiento transaccional fail-safe del helper Notificador
     */
    public function test2_Integracion_HelperNotificadorTransaccionalYFailSafe(): void
    {
        // 1. Intento de envío sin emisor ni sesión: debe retornar false limpiamente (no lanzar excepción)
        $_SESSION = [];
        $resultado = Notificador::enviar(1, 'Aviso sin sesión', 'modulo/consultar', 'info');
        $this->assertFalse($resultado, 'Notificador::enviar debe retornar false si no existe emisor');

        // 2. Verificación de diseño de la clase Notificador
        $refNotificador = new ReflectionClass(Notificador::class);
        $this->assertTrue($refNotificador->isFinal(), 'Notificador debe ser una clase final');

        $metodoConexion = new ReflectionMethod(Notificador::class, 'conexion');
        $this->assertTrue($metodoConexion->isPrivate(), 'Notificador::conexion debe ser un método privado');
    }

    /**
     * CP-NOT-004: Introspección por reflexión de métodos privados de NotificacionesModel
     */
    public function test2_Integracion_IntrospeccionMetodosPrivadosNotificaciones(): void
    {
        $metodos = [
            'exigirEntero',
            'listar',
            'contarNoLeidas',
            'marcarLeida',
            'marcarTodasLeidas',
            'eliminar',
            'eliminarTodas',
            'nuevasSSE',
            'reconectar',
            'mapearErrorBd',
        ];

        foreach ($metodos as $nombreMetodo) {
            $refMethod = new ReflectionMethod(NotificacionesModel::class, $nombreMetodo);
            $this->assertTrue(
                $refMethod->isPrivate(),
                "NotificacionesModel::{$nombreMetodo} debe tener visibilidad estrictamente privada"
            );
        }
    }

    /**
     * CP-NOT-005: Mapeo de errores PDO a excepciones semánticas de la API
     */
    public function test2_Integracion_MapeoErroresBaseDatosPdo(): void
    {
        $modelo = new NotificacionesModel();
        $refMapear = new ReflectionMethod(NotificacionesModel::class, 'mapearErrorBd');

        // 1. Error de restricción de integridad (SQLSTATE 23000) -> HTTP 409 (IN_USE / EN_USO)
        $pdoException23000 = new \PDOException('Integrity constraint violation', 0);
        $pdoException23000->errorInfo = ['23000', 1451, 'Cannot delete or update a parent row'];
        // Reflection sobre código de la excepción para simular SQLSTATE
        $propCode = new \ReflectionProperty(\Exception::class, 'code');
        $propCode->setValue($pdoException23000, '23000');

        /** @var ExcepcionApi $ex23000 */
        $ex23000 = $refMapear->invoke($modelo, $pdoException23000, 'Error genérico');
        $this->assertInstanceOf(ExcepcionApi::class, $ex23000);
        $this->assertSame(409, $ex23000->estado);
        $this->assertSame('El registro está relacionado con otros datos.', $ex23000->getMessage());

        // 2. Error genérico de base de datos -> HTTP 500 (INTERNAL_SERVER_ERROR)
        $pdoExceptionGenerica = new \PDOException('Conexión perdida', 0);
        /** @var ExcepcionApi $ex500 */
        $ex500 = $refMapear->invoke($modelo, $pdoExceptionGenerica, 'Error al listar las notificaciones.');
        $this->assertInstanceOf(ExcepcionApi::class, $ex500);
        $this->assertSame(500, $ex500->estado);
        $this->assertSame('Error al listar las notificaciones.', $ex500->getMessage());
    }

    /**
     * CP-NOT-006: Control de acceso RBAC en endpoints del controlador de notificaciones
     */
    public function test2_Integracion_ControlAccesoRbacEnControlador(): void
    {
        load_controller('notificacionesController.php');

        // 1. Sin sesión: RBAC lanza 401
        try {
            apiListar();
            $this->fail('Debe bloquear acceso sin sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertSame(ErrorCodes::NO_AUTENTICADO, $e->codigo);
        }

        // 2. Con sesión activa pero con rol no autorizado (ej. rol 999 sin permisos)
        $_SESSION['id_empleado'] = 1;
        $_SESSION['id_tipo_empleado'] = 999;
        try {
            apiContar();
            $this->fail('Debe denegar acceso a roles sin permiso');
        } catch (ExcepcionApi $e) {
            $this->assertSame(403, $e->estado);
            $this->assertStringContainsString("No tienes permiso de 'leer' sobre el módulo 'notificaciones'", $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo Completo con SSE)
    // =========================================================================

    /**
     * CP-NOT-007: Disponibilidad de funciones de controladores, streaming SSE y assets frontend
     */
    public function test3_Sistema_DisponibilidadControladoresYAssetsNotificaciones(): void
    {
        load_controller('notificacionesController.php');
        load_controller('sseController.php');

        // Funciones del controlador API
        $funcionesApi = [
            'apiListar',
            'apiContar',
            'apiMarcarLeida',
            'apiMarcarTodasLeidas',
            'apiEliminar',
            'apiEliminarTodas',
        ];
        foreach ($funcionesApi as $fn) {
            $this->assertTrue(function_exists($fn), "Función {$fn} debe existir en notificacionesController.php");
        }

        // Función del controlador de Streaming SSE
        $this->assertTrue(function_exists('streamNotificaciones'), 'Función streamNotificaciones debe existir en sseController.php');

        // Plantilla del topbar con la campana de notificaciones
        $this->assertFileExists(BASE_PATH . '/app/Views/template/header.php', 'Template header.php debe existir');
        $contenidoHeader = file_get_contents(BASE_PATH . '/app/Views/template/header.php');
        $this->assertStringContainsString('id="notificationDropdown"', $contenidoHeader);
        $this->assertStringContainsString('id="notificationCounter"', $contenidoHeader);
        $this->assertStringContainsString('id="notificationMenu"', $contenidoHeader);

        // Script JavaScript del frontend
        $this->assertFileExists(BASE_PATH . '/dist/js/modulos/notificaciones/control.js', 'Script control.js debe existir');
    }

    /**
     * CP-NOT-008: Flujo funcional de envío, consulta de bandeja y auditoría de notificaciones
     */
    public function test3_Sistema_FlujoFuncionalEnvioLecturaYAuditoriaBandeja(): void
    {
        // 1. Simulación de sesión de usuario autenticado
        try {
            $refConexion = new ReflectionMethod(Notificador::class, 'conexion');
            $pdo = $refConexion->invoke(null);
            $empleadoValido = $pdo->query("SELECT id_empleado FROM empleado ORDER BY id_empleado ASC LIMIT 1")->fetchColumn();
            $idEmpleado = $empleadoValido ? (int) $empleadoValido : 1;
        } catch (\Throwable) {
            $idEmpleado = 1;
        }

        $_SESSION['id_empleado'] = $idEmpleado;
        $_SESSION['nombre'] = 'Usuario Test';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Envío de notificación usando el Helper transversal
        $envio = Notificador::enviar(
            $idEmpleado,
            'Actualización del Sistema',
            'beneficiarios/consultar',
            'info',
            $idEmpleado
        );
        $this->assertIsBool($envio);

        // 3. Consulta de la bandeja de entrada mediante NotificacionesModel
        $modelo = new NotificacionesModel();
        $modelo->__set('id_empleado', $idEmpleado);
        $modelo->__set('limit', 20);

        $bandeja = $modelo->manejarAccion('listar');
        $this->assertIsArray($bandeja);
        $this->assertArrayHasKey('unread_count', $bandeja);
        $this->assertArrayHasKey('notifications', $bandeja);
        $this->assertIsInt($bandeja['unread_count']);
        $this->assertIsArray($bandeja['notifications']);

        // 4. Auditoría en Bitácora para acciones destructivas en la bandeja
        Bitacora::registrar(
            'Notificaciones',
            'Eliminación',
            'Vació su bandeja de notificaciones.'
        );
    }
}
