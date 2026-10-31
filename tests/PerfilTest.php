<?php

namespace Tests;

use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Core\Notificador;
use App\Models\PerfilModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class PerfilTest extends TestCase
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
     * CP-PERF-001: Validación y normalización de atributos básicos de contacto en PerfilModel
     */
    public function test1_Unitaria_ValidacionAtributosBasicosPerfil(): void
    {
        $modelo = new PerfilModel();

        // 1. Asignaciones válidas
        $modelo->__set('id_empleado', 5);
        $modelo->__set('correo', 'carlos@uptaeb.edu.ve');
        $modelo->__set('telefono', '0412-123.45.67'); // con formato
        $modelo->__set('direccion', 'Barquisimeto, Estado Lara');

        $this->assertSame(5, $modelo->__get('id_empleado'));
        $this->assertSame('carlos@uptaeb.edu.ve', $modelo->__get('correo'));
        // El teléfono se normaliza extrayendo solo dígitos
        $this->assertSame('04121234567', $modelo->__get('telefono'));
        $this->assertSame('Barquisimeto, Estado Lara', $modelo->__get('direccion'));

        // 2. id_empleado inválido (<= 0)
        try {
            $modelo->__set('id_empleado', 0);
            $this->fail('Debe rechazar id_empleado menor a 1');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('El id del empleado no es válido.', $e->getMessage());
        }

        // 3. Correo inválido
        try {
            $modelo->__set('correo', 'correo_invalido');
            $this->fail('Debe rechazar correo sin estructura RFC');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('El correo electrónico no es válido.', $e->getMessage());
        }

        // 4. Correo mayor a 50 caracteres
        try {
            $modelo->__set('correo', str_repeat('a', 45) . '@uptaeb.edu.ve');
            $this->fail('Debe rechazar correo mayor a 50 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('El correo no puede superar 50 caracteres.', $e->getMessage());
        }

        // 5. Teléfono con operadora inválida o longitud errónea
        try {
            $modelo->__set('telefono', '04181234567');
            $this->fail('Debe rechazar teléfono con prefijo inválido');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertStringContainsString('El teléfono debe empezar por 0412', $e->getMessage());
        }

        // 6. Teléfono vacío
        try {
            $modelo->__set('telefono', '');
            $this->fail('Debe rechazar teléfono vacío');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('El teléfono es obligatorio.', $e->getMessage());
        }

        // 7. Dirección mayor a 500 caracteres
        try {
            $modelo->__set('direccion', str_repeat('X', 505));
            $this->fail('Debe rechazar dirección mayor a 500 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('La dirección es demasiado larga (máx. 500 caracteres).', $e->getMessage());
        }
    }

    /**
     * CP-PERF-002: Validación de contraseñas (actual, nueva y límites de longitud)
     */
    public function test1_Unitaria_ValidacionContrasenasPerfil(): void
    {
        $modelo = new PerfilModel();

        // 1. clave_actual vacía
        try {
            $modelo->__set('clave_actual', '');
            $this->fail('Debe rechazar clave actual vacía');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('Debes escribir tu contraseña actual.', $e->getMessage());
        }

        $modelo->__set('clave_actual', 'ClaveActual123*');
        $this->assertSame('ClaveActual123*', $modelo->__get('clave_actual'));

        // 2. Nueva contraseña menor a 8 caracteres
        try {
            $modelo->__set('clave', 'corta');
            $this->fail('Debe rechazar contraseña menor a 8 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('La contraseña debe tener al menos 8 caracteres.', $e->getMessage());
        }

        // 3. Nueva contraseña mayor a 72 caracteres (límite bcrypt)
        try {
            $modelo->__set('clave', str_repeat('P', 75));
            $this->fail('Debe rechazar contraseña mayor a 72 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('La contraseña no puede superar 72 caracteres.', $e->getMessage());
        }

        $modelo->__set('clave', 'NuevaClaveSegura123*');
        $modelo->__set('clave_confirmacion', 'NuevaClaveSegura123*');
        $this->assertSame('NuevaClaveSegura123*', $modelo->__get('clave'));
        $this->assertSame('NuevaClaveSegura123*', $modelo->__get('clave_confirmacion'));
    }

    /**
     * CP-PERF-003: Encapsulamiento del modelo: rechazo de atributos e identificadores no válidos
     */
    public function test1_Unitaria_RechazoAtributosEAccionesNoValidas(): void
    {
        $modelo = new PerfilModel();

        // 1. Atributo no reconocido
        try {
            $modelo->__set('campo_no_permitido', 'valor');
            $this->fail('Debe rechazar atributo ajeno al modelo');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame("Atributo no reconocido: 'campo_no_permitido'.", $e->getMessage());
        }

        // 2. Acción no válida en despachador
        try {
            $modelo->manejarAccion('accion_desconocida');
            $this->fail('Debe rechazar acción ajena al despachador');
        } catch (ExcepcionApi $e) {
            $this->assertSame(500, $e->estado);
            $this->assertSame("Acción no válida en PerfilModel: 'accion_desconocida'.", $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas de Negocio, Reflexión y Seguridad)
    // =========================================================================

    /**
     * CP-PERF-004: Regla de negocio: formateo de fechas y rechazo de perfiles inexistentes
     */
    public function test2_Integracion_ReglasDeNegocioActualizacionPerfil(): void
    {
        // 1. Método privado estático fechaCorta
        $refFecha = new ReflectionMethod(PerfilModel::class, 'fechaCorta');
        $fechaFormateada = $refFecha->invoke(null, '2026-05-15');
        $this->assertSame('15/05/2026', $fechaFormateada);

        // 2. Consulta de perfil con ID inexistente (debe lanzar 404 NOT_FOUND)
        $modelo = new PerfilModel();
        $modelo->__set('id_empleado', 99999);

        try {
            $modelo->manejarAccion('obtener');
            $this->fail('Debe lanzar 404 al consultar empleado inexistente');
        } catch (ExcepcionApi $e) {
            $this->assertSame(404, $e->estado);
            $this->assertSame('El perfil solicitado no existe.', $e->getMessage());
        }
    }

    /**
     * CP-PERF-005: Introspección por reflexión de métodos privados de PerfilModel
     */
    public function test2_Integracion_VerificacionMetodosPrivadosPerfil(): void
    {
        $metodos = [
            'obtener',
            'actualizar',
            'validarCorreo',
            'validarTelefono',
            'fechaCorta',
        ];

        foreach ($metodos as $nombreMetodo) {
            $refMethod = new ReflectionMethod(PerfilModel::class, $nombreMetodo);
            $this->assertTrue(
                $refMethod->isPrivate(),
                "PerfilModel::{$nombreMetodo} debe tener visibilidad privada estricta"
            );
        }
    }

    /**
     * CP-PERF-006: Principio de autoservicio y seguridad de sesión en perfilController
     */
    public function test2_Integracion_AutoservicioYSeguridadSesionControlador(): void
    {
        load_controller('perfilController.php');

        // Sin sesión (id_empleado ausente o 0): apiObtenerPerfil asigna 0 y el modelo lanza validación HTTP 400
        $_SESSION = [];
        try {
            apiObtenerPerfil();
            $this->fail('Debe rechazar consulta de perfil sin sesión de empleado');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('El id del empleado no es válido.', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo Completo de Perfil)
    // =========================================================================

    /**
     * CP-PERF-007: Disponibilidad de funciones procedimentales, vistas y scripts JS de Perfil
     */
    public function test3_Sistema_DisponibilidadControladorVistasYScriptsPerfil(): void
    {
        load_controller('perfilController.php');

        // Funciones del controlador
        $funcionesEsperadas = [
            'showPerfil',
            'apiObtenerPerfil',
            'apiActualizarPerfil',
            'apiValidarCorreoPerfil',
            'apiValidarTelefonoPerfil',
        ];

        foreach ($funcionesEsperadas as $fn) {
            $this->assertTrue(function_exists($fn), "Función {$fn} debe existir en perfilController.php");
        }

        // Vistas en disco
        $this->assertFileExists(BASE_PATH . '/app/Views/perfil/ver.php', 'Vista ver.php debe existir');

        // Scripts JS en disco
        $this->assertFileExists(BASE_PATH . '/dist/js/modulos/perfil/perfil.js', 'Script perfil.js debe existir');
        $this->assertFileExists(BASE_PATH . '/dist/js/modulos/empleado/validaciones.js', 'Script validaciones.js debe existir');
    }

    /**
     * CP-PERF-008: Flujo funcional de consulta de datos propios, auditoría y notificación interna
     */
    public function test3_Sistema_FlujoFuncionalConsultaPerfilAuditoriaYNotificacion(): void
    {
        // 1. Sincronización con un empleado real existente en la base de datos
        try {
            $refConexion = new ReflectionMethod(Notificador::class, 'conexion');
            $pdo = $refConexion->invoke(null);
            $empleadoValido = $pdo->query("SELECT id_empleado FROM empleado ORDER BY id_empleado ASC LIMIT 1")->fetchColumn();
            $idEmpleado = $empleadoValido ? (int) $empleadoValido : 1;
        } catch (\Throwable) {
            $idEmpleado = 1;
        }

        // 2. Establecer sesión del empleado autenticado
        $_SESSION['id_empleado'] = $idEmpleado;
        $_SESSION['nombre'] = 'Carlos';
        $_SESSION['apellido'] = 'Pérez';
        $_SESSION['id_tipo_empleado'] = 2;
        $_SESSION['tipo_empleado'] = 'Médico';

        // 3. Consulta de perfil a través de PerfilModel
        $modelo = new PerfilModel();
        $modelo->__set('id_empleado', $idEmpleado);

        $perfil = $modelo->manejarAccion('obtener');

        $this->assertIsArray($perfil);
        $this->assertArrayHasKey('id_empleado', $perfil);
        $this->assertArrayHasKey('nombre', $perfil);
        $this->assertArrayHasKey('apellido', $perfil);
        $this->assertArrayHasKey('tipo_cedula', $perfil);
        $this->assertArrayHasKey('cedula', $perfil);
        $this->assertArrayHasKey('correo', $perfil);
        $this->assertArrayHasKey('telefono', $perfil);
        $this->assertArrayHasKey('estatus', $perfil);
        $this->assertArrayHasKey('nombre_tipo', $perfil);

        $this->assertSame($idEmpleado, (int) $perfil['id_empleado']);
        $this->assertNotEmpty($perfil['nombre']);

        // 4. Auditoría en Bitácora y Notificación interna de autoservicio
        Bitacora::registrar('Perfil', 'Actualización', 'Actualizó su propio perfil.');
        $notificado = Notificador::enviar($idEmpleado, 'Tu perfil fue actualizado', 'perfil/ver', 'info');
        $this->assertIsBool($notificado);
    }
}
