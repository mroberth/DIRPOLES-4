<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\DiscapacidadModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DiscapacidadTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente DiscapacidadModel y Validaciones)
    // =========================================================================

    /**
     * CP-DIS-001: Validación de asignación correcta de atributos para Discapacidad
     */
    public function test1_Unitaria_ValidacionDatosDiscapacidad(): void
    {
        $modelo = new DiscapacidadModel();

        $modelo->__set('id_discapacidad', 12);
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('id_empleado', 5);
        $modelo->__set('id_usuario', 5);
        $modelo->__set('id_tipo_empleado', 5);
        $modelo->__set('tipo_empleado', 'Discapacidad');
        $modelo->__set('tipo_discapacidad', 'Física');
        $modelo->__set('disc_especifica', 'Paraplejía espástica de miembros inferiores');
        $modelo->__set('diagnostico', 'Secuela motora por traumatismo raquimedular');
        $modelo->__set('grado', 'Grave');
        $modelo->__set('medicamentos', 'Baclofeno 10mg diario');
        $modelo->__set('habilidades_funcionales', 'Desplazamiento independiente con silla de ruedas');
        $modelo->__set('requiere_asistencia', 'No');
        $modelo->__set('dispositivo_asistencia', 'Silla de ruedas manual');
        $modelo->__set('observaciones', 'Requiere rampas de acceso en edificios de aulas');
        $modelo->__set('recomendaciones', 'Asignación de aula en planta baja');
        $modelo->__set('carnet_discapacidad', 'CONAPDIS-00982');

        $this->assertSame(12, $modelo->__get('id_discapacidad'));
        $this->assertSame(1, $modelo->__get('id_beneficiario'));
        $this->assertSame(5, $modelo->__get('id_empleado'));
        $this->assertSame(5, $modelo->__get('id_usuario'));
        $this->assertSame(5, $modelo->__get('id_tipo_empleado'));
        $this->assertSame('Discapacidad', $modelo->__get('tipo_empleado'));
        $this->assertSame('Física', $modelo->__get('tipo_discapacidad'));
        $this->assertSame('Paraplejía espástica de miembros inferiores', $modelo->__get('disc_especifica'));
        $this->assertSame('Secuela motora por traumatismo raquimedular', $modelo->__get('diagnostico'));
        $this->assertSame('Grave', $modelo->__get('grado'));
        $this->assertSame('Baclofeno 10mg diario', $modelo->__get('medicamentos'));
        $this->assertSame('Desplazamiento independiente con silla de ruedas', $modelo->__get('habilidades_funcionales'));
        $this->assertSame('No', $modelo->__get('requiere_asistencia'));
        $this->assertSame('Silla de ruedas manual', $modelo->__get('dispositivo_asistencia'));
        $this->assertSame('Requiere rampas de acceso en edificios de aulas', $modelo->__get('observaciones'));
        $this->assertSame('Asignación de aula en planta baja', $modelo->__get('recomendaciones'));
        $this->assertSame('CONAPDIS-00982', $modelo->__get('carnet_discapacidad'));
    }

    /**
     * CP-DIS-002: Rechazo de tipo de discapacidad no perteneciente al catálogo
     */
    public function test1_Unitaria_RechazoTipoDiscapacidadInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El tipo de discapacidad no es válido.');

        $modelo = new DiscapacidadModel();
        $modelo->__set('tipo_discapacidad', 'Cognitiva');
    }

    /**
     * CP-DIS-003: Rechazo de grado de discapacidad no válido
     */
    public function test1_Unitaria_RechazoGradoDiscapacidadInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El grado no es válido (Leve, Moderado o Grave).');

        $modelo = new DiscapacidadModel();
        $modelo->__set('grado', 'Severo');
    }

    /**
     * CP-DIS-004: Rechazo de campos obligatorios vacíos y validación de asistencia y longitud
     */
    public function test1_Unitaria_RechazoCamposObligatoriosVaciosYLongitud(): void
    {
        $modelo = new DiscapacidadModel();

        // 1. Campo obligatorio vacío (diagnóstico)
        try {
            $modelo->__set('diagnostico', '   ');
            $this->fail('Debe rechazar diagnóstico en blanco');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El campo diagnostico es obligatorio.', $e->getMessage());
        }

        // 2. Campo "¿Requiere asistencia?" con valor no permitido
        try {
            $modelo->__set('requiere_asistencia', 'A veces');
            $this->fail('Debe rechazar valor de asistencia inválido');
        } catch (ExcepcionApi $e) {
            $this->assertSame('"¿Requiere asistencia?" solo admite Si o No.', $e->getMessage());
        }

        // 3. Exceso de longitud en carnet_discapacidad (> 20 caracteres)
        try {
            $modelo->__set('carnet_discapacidad', 'CONAPDIS-9876543210123456');
            $this->fail('Debe rechazar carnet mayor a 20 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El campo carnet_discapacidad no puede superar 20 caracteres.', $e->getMessage());
        }
    }

    /**
     * CP-DIS-005: Rechazo de atributo no reconocido e identificador de beneficiario inválido
     */
    public function test1_Unitaria_RechazoAtributoDesconocidoEIdInvalido(): void
    {
        $modelo = new DiscapacidadModel();

        // 1. Atributo no reconocido
        try {
            $modelo->__set('campo_inexistente', 'valor');
            $this->fail('Debe rechazar atributo inexistente');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Atributo no reconocido: 'campo_inexistente'.", $e->getMessage());
        }

        // 2. Identificador de beneficiario no positivo
        try {
            $modelo->__set('id_beneficiario', 0);
            $this->fail('Debe rechazar id_beneficiario = 0');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El id_beneficiario no es válido.', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas de Negocio y Control RBAC)
    // =========================================================================

    /**
     * CP-DIS-006: Validación de obligatoriedad de campos requeridos y beneficiario
     */
    public function test2_Integracion_ValidacionRequeridosBeneficiario(): void
    {
        $metodo = new ReflectionMethod(DiscapacidadModel::class, 'validarRequeridos');
        $modelo = new DiscapacidadModel();

        // 1. Con los campos clínicos pero sin beneficiario debe fallar
        $modelo->__set('tipo_discapacidad', 'Sensorial');
        $modelo->__set('diagnostico', 'Hipoacusia neurosensorial bilateral');
        $modelo->__set('grado', 'Moderado');
        $modelo->__set('habilidades_funcionales', 'Lectura labiofacial y lengua de señas');
        $modelo->__set('observaciones', 'Ubicación preferencial en primera fila');

        try {
            $metodo->invoke($modelo);
            $this->fail('Debe fallar si falta el beneficiario');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El beneficiario es obligatorio.', $e->getMessage());
        }

        // 2. Con beneficiario asignado la validación pasa exitosamente
        $modelo->__set('id_beneficiario', 1);
        $metodo->invoke($modelo);
        $this->assertSame(1, $modelo->__get('id_beneficiario'));
    }

    /**
     * CP-DIS-007: Control de acceso RBAC para el módulo Discapacidad
     */
    public function test2_Integracion_ControlAccesoRbacDiscapacidad(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene retorna false y verificar arroja 401
        $this->assertFalse(Autorizacion::tiene('discapacidad', 'crear'));
        $this->assertFalse(Autorizacion::tiene('discapacidad', 'eliminar'));

        try {
            Autorizacion::verificar('discapacidad', 'crear');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }

        try {
            Autorizacion::verificar('discapacidad', 'eliminar');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo Completo de Negocio)
    // =========================================================================

    /**
     * CP-DIS-008: Disponibilidad de Controladores y Vistas del Módulo Discapacidad
     */
    public function test3_Sistema_DisponibilidadControladorYVistasDiscapacidad(): void
    {
        load_controller('discapacidadController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showCrearDiscapacidad'), 'showCrearDiscapacidad debe existir');
        $this->assertTrue(function_exists('showConsultarDiscapacidad'), 'showConsultarDiscapacidad debe existir');
        $this->assertTrue(function_exists('apiListarDiscapacidad'), 'apiListarDiscapacidad debe existir');
        $this->assertTrue(function_exists('apiObtenerDiscapacidad'), 'apiObtenerDiscapacidad debe existir');
        $this->assertTrue(function_exists('apiCatalogosDiscapacidad'), 'apiCatalogosDiscapacidad debe existir');
        $this->assertTrue(function_exists('apiStatsDiscapacidad'), 'apiStatsDiscapacidad debe existir');
        $this->assertTrue(function_exists('generarConstanciaDiscapacidad'), 'generarConstanciaDiscapacidad debe existir');
        $this->assertTrue(function_exists('generarReferenciaDiscapacidad'), 'generarReferenciaDiscapacidad debe existir');
        $this->assertTrue(function_exists('apiCrearDiscapacidad'), 'apiCrearDiscapacidad debe existir');
        $this->assertTrue(function_exists('apiActualizarDiscapacidad'), 'apiActualizarDiscapacidad debe existir');
        $this->assertTrue(function_exists('apiEliminarDiscapacidad'), 'apiEliminarDiscapacidad debe existir');
        $this->assertTrue(function_exists('contextoDiscapacidad'), 'contextoDiscapacidad debe existir');
        $this->assertTrue(function_exists('entradaDiscapacidad'), 'entradaDiscapacidad debe existir');
        $this->assertTrue(function_exists('discapacidadAsignarEntrada'), 'discapacidadAsignarEntrada debe existir');
        $this->assertTrue(function_exists('discapacidadAsignarEntradaEdicion'), 'discapacidadAsignarEntradaEdicion debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/discapacidad/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/discapacidad/crear.php', 'Vista crear.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/discapacidad/components/stats.php', 'Componente stats.php debe existir');
    }

    /**
     * CP-DIS-009: Flujo integral del ciclo de gestión de discapacidad y auditoría
     */
    public function test3_Sistema_FlujoFuncionalGestionDiscapacidadYAuditoria(): void
    {
        // 1. Simulación de sesión de profesional de discapacidad activo
        $_SESSION['id_empleado'] = 5;
        $_SESSION['nombre'] = 'Lcda. Elena Ramos';
        $_SESSION['id_tipo_empleado'] = 5;
        $_SESSION['tipo_empleado'] = 'Discapacidad';

        // 2. Preparación de entidad completa
        $modelo = new DiscapacidadModel();
        $modelo->__set('id_usuario', (int) $_SESSION['id_empleado']);
        $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado']);
        $modelo->__set('id_tipo_empleado', (int) $_SESSION['id_tipo_empleado']);
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('tipo_discapacidad', 'Intelectual');
        $modelo->__set('disc_especifica', 'Dificultad de aprendizaje procedimental');
        $modelo->__set('diagnostico', 'Trastorno del neurodesarrollo adaptativo');
        $modelo->__set('grado', 'Leve');
        $modelo->__set('habilidades_funcionales', 'Lectoescritura funcional y razonamiento práctico guiado');
        $modelo->__set('requiere_asistencia', 'No');
        $modelo->__set('observaciones', 'Adaptación de tiempos en evaluaciones teóricas');
        $modelo->__set('recomendaciones', 'Tutoría académica personalizada');

        // 3. Simulación de registro de auditoría en Bitácora
        Bitacora::registrar(
            'Discapacidad',
            'Registro',
            'Registró un diagnóstico de discapacidad para el beneficiario ID 1.'
        );

        // 4. Verificación de contrato de salida estandarizado
        $salidaEsperada = [
            'id_beneficiario'        => $modelo->__get('id_beneficiario'),
            'tipo_discapacidad'      => $modelo->__get('tipo_discapacidad'),
            'grado'                  => $modelo->__get('grado'),
            'diagnostico'            => $modelo->__get('diagnostico'),
            'habilidades_funcionales'=> $modelo->__get('habilidades_funcionales'),
        ];

        $this->assertSame(1, $salidaEsperada['id_beneficiario']);
        $this->assertSame('Intelectual', $salidaEsperada['tipo_discapacidad']);
        $this->assertSame('Leve', $salidaEsperada['grado']);
        $this->assertSame('Trastorno del neurodesarrollo adaptativo', $salidaEsperada['diagnostico']);
        $this->assertSame('Lectoescritura funcional y razonamiento práctico guiado', $salidaEsperada['habilidades_funcionales']);
    }
}
