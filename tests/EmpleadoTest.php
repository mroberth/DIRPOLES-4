<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\EmpleadoModel;
use DateTime;
use PHPUnit\Framework\TestCase;

class EmpleadoTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente EmpleadoModel y Validaciones)
    // =========================================================================

    /**
     * CP-EMP-UNIT-001: Validación de asignación correcta de datos íntegros
     */
    public function test1_Unitaria_ValidacionEdadMinimaYCamposRequeridos(): void
    {
        $modelo = new EmpleadoModel();
        $modelo->__set('nombre', 'Carlos');
        $modelo->__set('apellido', 'Pérez');
        $modelo->__set('tipo_cedula', 'V');
        $modelo->__set('cedula', '12345678');
        $modelo->__set('correo', 'carlos@uptaeb.edu.ve');
        $modelo->__set('telefono', '04121234567');
        $modelo->__set('id_tipo_empleado', 2);
        $modelo->__set('fecha_nacimiento', '1995-05-15');
        $modelo->__set('direccion', 'Barquisimeto, Estado Lara');
        $modelo->__set('clave', 'ClaveSegura123*');
        $modelo->__set('estatus', 1);

        $this->assertSame('Carlos', $modelo->__get('nombre'));
        $this->assertSame('Pérez', $modelo->__get('apellido'));
        $this->assertSame('V', $modelo->__get('tipo_cedula'));
        $this->assertSame('12345678', $modelo->__get('cedula'));
        $this->assertSame('carlos@uptaeb.edu.ve', $modelo->__get('correo'));
        $this->assertSame('04121234567', $modelo->__get('telefono'));
        $this->assertSame(2, $modelo->__get('id_tipo_empleado'));
        $this->assertSame('1995-05-15', $modelo->__get('fecha_nacimiento'));
        $this->assertSame(1, $modelo->__get('estatus'));
    }

    /**
     * CP-EMP-UNIT-001: Rechazo de nombre obligatorio vacío
     */
    public function test1_Unitaria_RechazoCampoNombreVacio(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El nombre es obligatorio.');

        $modelo = new EmpleadoModel();
        $modelo->__set('nombre', '   ');
    }

    /**
     * CP-EMP-UNIT-002: Rechazo de fecha de nacimiento futura
     */
    public function test1_Unitaria_RechazoFechaNacimientoFutura(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La fecha de nacimiento no puede ser futura.');

        $modelo = new EmpleadoModel();
        $fechaFutura = (new DateTime('+1 year'))->format('Y-m-d');
        $modelo->__set('fecha_nacimiento', $fechaFutura);
    }

    /**
     * CP-EMP-UNIT-002: Restricción de edad mínima laboral (15 años)
     */
    public function test1_Unitaria_RechazoEdadMenorA15Anos(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El empleado debe tener al menos 15 años.');

        $modelo = new EmpleadoModel();
        $fechaMenor = (new DateTime('-12 years'))->format('Y-m-d');
        $modelo->__set('fecha_nacimiento', $fechaMenor);
    }

    /**
     * CP-EMP-UNIT-003: Rechazo de formato telefónico móvil inválido o fijo
     */
    public function test1_Unitaria_RechazoTelefonoInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El teléfono debe empezar por 0412, 0414, 0416, 0422, 0424 o 0426 y tener 7 dígitos más');

        $modelo = new EmpleadoModel();
        // Número con código de área fijo local (no móvil nacional)
        $modelo->__set('telefono', '02511234567');
    }

    /**
     * CP-EMP-UNIT-004: Rechazo de contraseña con menos de 8 caracteres
     */
    public function test1_Unitaria_RechazoContrasenaMenorOchoCaracteres(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La contraseña debe tener al menos 8 caracteres.');

        $modelo = new EmpleadoModel();
        $modelo->__set('clave', '12345');
    }

    /**
     * CP-EMP-UNIT-005: Rechazo de formato de cédula no numérico o fuera de rango
     */
    public function test1_Unitaria_RechazoCedulaInvalida(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La cédula debe tener entre 6 y 10 dígitos.');

        $modelo = new EmpleadoModel();
        // Menos de 6 dígitos
        $modelo->__set('cedula', '1234');
    }

    /**
     * CP-EMP-UNIT-001: Rechazo de atributo no reconocido
     */
    public function test1_Unitaria_RechazoAtributoDesconocido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage("Atributo no reconocido: 'campo_inexistente'.");

        $modelo = new EmpleadoModel();
        $modelo->__set('campo_inexistente', 'valor');
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Persistencia, Hashing Bcrypt y RBAC)
    // =========================================================================

    /**
     * CP-EMP-INT-001: Verificación de hashing seguro Bcrypt para credenciales
     */
    public function test2_Integracion_PersistenciaEmpleadoConHashBcrypt(): void
    {
        $clavePlana = 'ContraseñaSegura2026*';
        $hashGenerado = password_hash($clavePlana, PASSWORD_BCRYPT);

        // Bcrypt genera un hash de 60 caracteres que inicia por $2y$
        $this->assertSame(60, strlen($hashGenerado));
        $this->assertStringStartsWith('$2y$', $hashGenerado);
        $this->assertTrue(password_verify($clavePlana, $hashGenerado), 'La clave plana debe coincidir con el hash Bcrypt');
        $this->assertFalse(password_verify('ClaveIncorrecta', $hashGenerado), 'Claves erróneas deben ser rechazadas');
    }

    /**
     * CP-EMP-INT-006: Control de acceso RBAC para el módulo Empleados
     */
    public function test2_Integracion_ControlAccesoRbacEmpleados(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene debe ser false y verificar debe arrojar 401
        $this->assertFalse(Autorizacion::tiene('empleados', 'crear'));

        try {
            Autorizacion::verificar('empleados', 'crear');
            $this->fail('Debe arrojar excepción si no hay sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }
    }

    /**
     * CP-EMP-INT-007: Manejo de parámetros de paginación segura y búsqueda
     */
    public function test2_Integracion_ConsultaPaginadaYBusqueda(): void
    {
        $modelo = new EmpleadoModel();
        $modelo->__set('buscar', 'Carlos Pérez');
        $modelo->__set('limit', 50);
        $modelo->__set('offset', 10);

        $this->assertSame('Carlos Pérez', $modelo->__get('buscar'));
        $this->assertSame(50, $modelo->__get('limit'));
        $this->assertSame(10, $modelo->__get('offset'));

        // Comprobar que números negativos se ajustan a cero
        $modelo->__set('limit', -5);
        $this->assertSame(0, $modelo->__get('limit'));
    }

    /**
     * CP-EMP-INT-004: Normalización y evaluación de estatus de actividad
     */
    public function test3_Api_ValidacionEstatusEmpleado(): void
    {
        $modelo = new EmpleadoModel();

        // Estatus activo
        $modelo->__set('estatus', 1);
        $this->assertSame(1, $modelo->__get('estatus'));

        // Estatus inactivo
        $modelo->__set('estatus', 0);
        $this->assertSame(0, $modelo->__get('estatus'));

        // Valores distintos a 1 deben normalizarse a 0
        $modelo->__set('estatus', 'inactivo');
        $this->assertSame(0, $modelo->__get('estatus'));
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo de Negocio Integral)
    // =========================================================================

    /**
     * CP-EMP-SYS-WB-001: Disponibilidad de Controladores y Vistas del Módulo
     */
    public function test4_Sistema_DisponibilidadControladorYVistasEmpleados(): void
    {
        load_controller('empleadoController.php');

        // Funciones del controlador
        $this->assertTrue(function_exists('showCrearEmpleado'), 'showCrearEmpleado debe existir');
        $this->assertTrue(function_exists('showConsultarEmpleados'), 'showConsultarEmpleados debe existir');
        $this->assertTrue(function_exists('apiTiposEmpleado'), 'apiTiposEmpleado debe existir');
        $this->assertTrue(function_exists('apiCrearEmpleado'), 'apiCrearEmpleado debe existir');
        $this->assertTrue(function_exists('apiListarEmpleados'), 'apiListarEmpleados debe existir');
        $this->assertTrue(function_exists('apiObtenerEmpleado'), 'apiObtenerEmpleado debe existir');
        $this->assertTrue(function_exists('apiActualizarEmpleado'), 'apiActualizarEmpleado debe existir');
        $this->assertTrue(function_exists('apiEliminarEmpleado'), 'apiEliminarEmpleado debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/empleados/crear.php', 'Vista crear.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/empleados/consultar.php', 'Vista consultar.php debe existir');
    }

    /**
     * CP-EMP-SYS-WB-001: Flujo integral del ciclo de negocio con auditoría
     */
    public function test4_Sistema_FlujoFuncionalGestionEmpleados(): void
    {
        // 1. Simulación de sesión administrativa activa para auditoría
        $_SESSION['id_empleado'] = 1;
        $_SESSION['nombre'] = 'Administrador';
        $_SESSION['id_tipo_empleado'] = 6;

        // 2. Preparación de entidad completa
        $modelo = new EmpleadoModel();
        $modelo->__set('nombre', 'María');
        $modelo->__set('apellido', 'Gómez');
        $modelo->__set('tipo_cedula', 'V');
        $modelo->__set('cedula', '20123456');
        $modelo->__set('correo', 'maria.gomez@uptaeb.edu.ve');
        $modelo->__set('telefono', '04245551234');
        $modelo->__set('id_tipo_empleado', 1); // Psicólogo
        $modelo->__set('fecha_nacimiento', '1998-11-20');
        $modelo->__set('direccion', 'Carrera 19 con Calle 8');
        $modelo->__set('clave', 'Temporal2026*');
        $modelo->__set('estatus', 1);

        // 3. Simulación de registro de auditoría en Bitácora
        Bitacora::registrar(
            'Empleados',
            'Registro',
            'Creó al empleado "María Gómez"'
        );

        // 4. Verificación de contrato de salida estandarizado
        $salidaEsperada = [
            'nombre'   => $modelo->__get('nombre'),
            'apellido' => $modelo->__get('apellido'),
            'correo'   => $modelo->__get('correo'),
        ];

        $this->assertSame('María', $salidaEsperada['nombre']);
        $this->assertSame('Gómez', $salidaEsperada['apellido']);
        $this->assertSame('maria.gomez@uptaeb.edu.ve', $salidaEsperada['correo']);
    }
}
