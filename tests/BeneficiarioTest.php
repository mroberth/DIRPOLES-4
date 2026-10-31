<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\BeneficiarioModel;
use DateTime;
use PHPUnit\Framework\TestCase;

class BeneficiarioTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente BeneficiarioModel y Validaciones)
    // =========================================================================

    /**
     * CP-BEN-001: Validación de asignación correcta de expediente estudiantil
     */
    public function test1_Unitaria_ValidacionDatosExpediente(): void
    {
        $modelo = new BeneficiarioModel();
        $modelo->__set('id_pnf', 1);
        $modelo->__set('seccion', 'INFO-3101');
        $modelo->__set('nombres', 'Ana María');
        $modelo->__set('apellidos', 'Rodríguez Gil');
        $modelo->__set('tipo_cedula', 'V');
        $modelo->__set('cedula', '28123456');
        $modelo->__set('fecha_nac', '2003-04-12');
        $modelo->__set('telefono', '04145558899');
        $modelo->__set('correo', 'ana.rodriguez@uptaeb.edu.ve');
        $modelo->__set('genero', 'F');
        $modelo->__set('direccion', 'Barquisimeto, Estado Lara');
        $modelo->__set('estatus', 1);

        $this->assertSame(1, $modelo->__get('id_pnf'));
        $this->assertSame('INFO-3101', $modelo->__get('seccion'));
        $this->assertSame('Ana María', $modelo->__get('nombres'));
        $this->assertSame('Rodríguez Gil', $modelo->__get('apellidos'));
        $this->assertSame('V', $modelo->__get('tipo_cedula'));
        $this->assertSame('28123456', $modelo->__get('cedula'));
        $this->assertSame('2003-04-12', $modelo->__get('fecha_nac'));
        $this->assertSame('04145558899', $modelo->__get('telefono'));
        $this->assertSame('ana.rodriguez@uptaeb.edu.ve', $modelo->__get('correo'));
        $this->assertSame('F', $modelo->__get('genero'));
        $this->assertSame('Barquisimeto, Estado Lara', $modelo->__get('direccion'));
        $this->assertSame(1, $modelo->__get('estatus'));
    }

    /**
     * CP-BEN-002: Rechazo de nombres obligatorios vacíos o con espacios
     */
    public function test1_Unitaria_RechazoNombreVacio(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El nombre es obligatorio.');

        $modelo = new BeneficiarioModel();
        $modelo->__set('nombres', '   ');
    }

    /**
     * CP-BEN-003: Rechazo de apellidos obligatorios vacíos o con espacios
     */
    public function test1_Unitaria_RechazoApellidoVacio(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El apellido es obligatorio.');

        $modelo = new BeneficiarioModel();
        $modelo->__set('apellidos', '   ');
    }

    /**
     * CP-BEN-004: Rechazo de género inválido fuera de catálogo M/F
     */
    public function test1_Unitaria_RechazoGeneroInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El género debe ser M (Masculino) o F (Femenino).');

        $modelo = new BeneficiarioModel();
        $modelo->__set('genero', 'X');
    }

    /**
     * CP-BEN-005: Rechazo de fecha de nacimiento futura
     */
    public function test1_Unitaria_RechazoFechaNacimientoFutura(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La fecha de nacimiento no puede ser futura.');

        $modelo = new BeneficiarioModel();
        $fechaFutura = (new DateTime('+1 day'))->format('Y-m-d');
        $modelo->__set('fecha_nac', $fechaFutura);
    }

    /**
     * CP-BEN-006: Rechazo de teléfono inválido o de red fija
     */
    public function test1_Unitaria_RechazoTelefonoInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El teléfono debe empezar por 0412, 0414, 0416, 0422, 0424 o 0426 y tener 7 dígitos más');

        $modelo = new BeneficiarioModel();
        $modelo->__set('telefono', '02511234567');
    }

    /**
     * CP-BEN-007: Rechazo de formato de cédula con longitud menor a 6 dígitos
     */
    public function test1_Unitaria_RechazoCedulaInvalida(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La cédula debe tener entre 6 y 10 dígitos.');

        $modelo = new BeneficiarioModel();
        $modelo->__set('cedula', '12345');
    }

    /**
     * CP-BEN-008: Rechazo de sección académica vacía
     */
    public function test1_Unitaria_RechazoSeccionInvalida(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La sección es obligatoria.');

        $modelo = new BeneficiarioModel();
        $modelo->__set('seccion', '   ');
    }

    /**
     * CP-BEN-009: Rechazo de selección inválida de PNF
     */
    public function test1_Unitaria_RechazoPnfInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('Debes seleccionar un PNF.');

        $modelo = new BeneficiarioModel();
        $modelo->__set('id_pnf', 0);
    }

    /**
     * CP-BEN-010: Rechazo de atributo no reconocido en el modelo
     */
    public function test1_Unitaria_RechazoAtributoDesconocido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage("Atributo no reconocido: 'campo_inexistente'.");

        $modelo = new BeneficiarioModel();
        $modelo->__set('campo_inexistente', 'valor');
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (RBAC, Paginación y Reglas de Estado)
    // =========================================================================

    /**
     * CP-BEN-011: Control de acceso RBAC para el módulo Beneficiarios
     */
    public function test2_Integracion_ControlAccesoRbacBeneficiarios(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene debe ser false y verificar debe arrojar 401
        $this->assertFalse(Autorizacion::tiene('beneficiarios', 'crear'));

        try {
            Autorizacion::verificar('beneficiarios', 'crear');
            $this->fail('Debe arrojar excepción si no hay sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }
    }

    /**
     * CP-BEN-012: Manejo seguro de parámetros de paginación y búsqueda
     */
    public function test2_Integracion_ParametrosPaginacionYBusqueda(): void
    {
        $modelo = new BeneficiarioModel();
        $modelo->__set('buscar', 'Ana Rodríguez');
        $modelo->__set('limit', 50);
        $modelo->__set('offset', 10);

        $this->assertSame('Ana Rodríguez', $modelo->__get('buscar'));
        $this->assertSame(50, $modelo->__get('limit'));
        $this->assertSame(10, $modelo->__get('offset'));

        // Números negativos deben ajustarse a 0 con max(0, (int)$valor)
        $modelo->__set('limit', -10);
        $modelo->__set('offset', -5);
        $this->assertSame(0, $modelo->__get('limit'));
        $this->assertSame(0, $modelo->__get('offset'));

        // Búsqueda recortada a 100 caracteres con mb_substr
        $textoLargo = str_repeat('A', 150);
        $modelo->__set('buscar', $textoLargo);
        $this->assertSame(100, mb_strlen($modelo->__get('buscar')));
    }

    /**
     * CP-BEN-013: Normalización y evaluación de estatus de actividad del estudiante
     */
    public function test2_Integracion_ValidacionEstatusEstudiantil(): void
    {
        $modelo = new BeneficiarioModel();

        // Estatus activo válido
        $modelo->__set('estatus', 1);
        $this->assertSame(1, $modelo->__get('estatus'));

        // Estatus inactivo válido
        $modelo->__set('estatus', 0);
        $this->assertSame(0, $modelo->__get('estatus'));

        // Estatus fuera de [0, 1] lanza excepción de validación
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El estatus debe ser 0 (Inactivo) o 1 (Activo).');
        $modelo->__set('estatus', 99);
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo Integral de Negocio)
    // =========================================================================

    /**
     * CP-BEN-014: Disponibilidad de Controladores y Vistas del Módulo Beneficiarios
     */
    public function test3_Sistema_DisponibilidadControladorYVistasBeneficiarios(): void
    {
        load_controller('beneficiarioController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showCrearBeneficiario'), 'showCrearBeneficiario debe existir');
        $this->assertTrue(function_exists('showConsultarBeneficiarios'), 'showConsultarBeneficiarios debe existir');
        $this->assertTrue(function_exists('apiPnfs'), 'apiPnfs debe existir');
        $this->assertTrue(function_exists('apiBeneficiarioStats'), 'apiBeneficiarioStats debe existir');
        $this->assertTrue(function_exists('apiListarBeneficiarios'), 'apiListarBeneficiarios debe existir');
        $this->assertTrue(function_exists('apiObtenerBeneficiario'), 'apiObtenerBeneficiario debe existir');
        $this->assertTrue(function_exists('apiCrearBeneficiario'), 'apiCrearBeneficiario debe existir');
        $this->assertTrue(function_exists('apiActualizarBeneficiario'), 'apiActualizarBeneficiario debe existir');
        $this->assertTrue(function_exists('apiEliminarBeneficiario'), 'apiEliminarBeneficiario debe existir');
        $this->assertTrue(function_exists('apiValidarCedulaBeneficiario'), 'apiValidarCedulaBeneficiario debe existir');
        $this->assertTrue(function_exists('apiValidarCorreoBeneficiario'), 'apiValidarCorreoBeneficiario debe existir');
        $this->assertTrue(function_exists('apiValidarTelefonoBeneficiario'), 'apiValidarTelefonoBeneficiario debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/beneficiarios/crear.php', 'Vista crear.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/beneficiarios/consultar.php', 'Vista consultar.php debe existir');
    }

    /**
     * CP-BEN-015: Flujo integral del ciclo de registro de expediente estudiantil y auditoría
     */
    public function test3_Sistema_FlujoFuncionalExpedienteEstudiantil(): void
    {
        // 1. Simulación de sesión administrativa activa para auditoría
        $_SESSION['id_empleado'] = 1;
        $_SESSION['nombre'] = 'Roberth';
        $_SESSION['id_tipo_empleado'] = 6;

        // 2. Preparación de entidad completa de estudiante beneficiario
        $modelo = new BeneficiarioModel();
        $modelo->__set('id_pnf', 1);
        $modelo->__set('seccion', 'INFO-3101');
        $modelo->__set('nombres', 'Ana');
        $modelo->__set('apellidos', 'Rodríguez');
        $modelo->__set('tipo_cedula', 'V');
        $modelo->__set('cedula', '28123456');
        $modelo->__set('fecha_nac', '2003-04-12');
        $modelo->__set('telefono', '04145558899');
        $modelo->__set('correo', 'ana.rodriguez@uptaeb.edu.ve');
        $modelo->__set('genero', 'F');
        $modelo->__set('direccion', 'Barquisimeto, Carrera 19 con Calle 8');
        $modelo->__set('estatus', 1);

        // 3. Simulación de registro de auditoría en Bitácora
        Bitacora::registrar(
            'Beneficiarios',
            'Registro',
            'Creó al beneficiario "' . $modelo->__get('nombres') . ' ' . $modelo->__get('apellidos') . '"'
        );

        // 4. Verificación de contrato de salida estandarizado
        $salidaEsperada = [
            'nombres'   => $modelo->__get('nombres'),
            'apellidos' => $modelo->__get('apellidos'),
            'cedula'    => $modelo->__get('cedula'),
        ];

        $this->assertSame('Ana', $salidaEsperada['nombres']);
        $this->assertSame('Rodríguez', $salidaEsperada['apellidos']);
        $this->assertSame('28123456', $salidaEsperada['cedula']);
    }
}
