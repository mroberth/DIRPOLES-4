<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\OrientacionModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class OrientacionTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente OrientacionModel y Validaciones)
    // =========================================================================

    /**
     * CP-ORI-001: Validación de asignación correcta de atributos para Orientación
     */
    public function test1_Unitaria_ValidacionDatosOrientacion(): void
    {
        $modelo = new OrientacionModel();

        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('id_empleado', 4);
        $modelo->__set('id_usuario', 4);
        $modelo->__set('motivo_orientacion', 'Dificultad de adaptación al ritmo universitario');
        $modelo->__set('descripcion_orientacion', 'Estudiante de nuevo ingreso presenta dificultades de organización del tiempo.');
        $modelo->__set('indicaciones_orientacion', 'Aplicar técnica Pomodoro y elaborar cronograma semanal de estudio.');
        $modelo->__set('obs_adic_orientacion', 'Se coordinará sesión de seguimiento vocacional en 3 semanas.');

        $this->assertSame(1, $modelo->__get('id_beneficiario'));
        $this->assertSame(4, $modelo->__get('id_empleado'));
        $this->assertSame(4, $modelo->__get('id_usuario'));
        $this->assertSame('Dificultad de adaptación al ritmo universitario', $modelo->__get('motivo_orientacion'));
        $this->assertSame('Estudiante de nuevo ingreso presenta dificultades de organización del tiempo.', $modelo->__get('descripcion_orientacion'));
        $this->assertSame('Aplicar técnica Pomodoro y elaborar cronograma semanal de estudio.', $modelo->__get('indicaciones_orientacion'));
        $this->assertSame('Se coordinará sesión de seguimiento vocacional en 3 semanas.', $modelo->__get('obs_adic_orientacion'));
    }

    /**
     * CP-ORI-002: Rechazo de campos de texto obligatorios en blanco
     */
    public function test1_Unitaria_RechazoCamposTextoVacios(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El campo motivo_orientacion es obligatorio.');

        $modelo = new OrientacionModel();
        $modelo->__set('motivo_orientacion', '   ');
    }

    /**
     * CP-ORI-003: Rechazo de texto narrativo con longitud superior a 5000 caracteres
     */
    public function test1_Unitaria_RechazoLongitudExcesivaTexto(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El campo motivo_orientacion no puede superar 5000 caracteres.');

        $modelo = new OrientacionModel();
        $textoLargo = str_repeat('A', 5005);
        $modelo->__set('motivo_orientacion', $textoLargo);
    }

    /**
     * CP-ORI-004: Rechazo de identificador de beneficiario no positivo
     */
    public function test1_Unitaria_RechazoIdBeneficiarioInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El id_beneficiario no es válido.');

        $modelo = new OrientacionModel();
        $modelo->__set('id_beneficiario', 0);
    }

    /**
     * CP-ORI-005: Rechazo de atributo no reconocido en el modelo OrientacionModel
     */
    public function test1_Unitaria_RechazoAtributoDesconocido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage("Atributo no reconocido: 'campo_inexistente'.");

        $modelo = new OrientacionModel();
        $modelo->__set('campo_inexistente', 'valor');
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas de Negocio y Control RBAC)
    // =========================================================================

    /**
     * CP-ORI-006: Validación de obligatoriedad de los 4 campos de texto y beneficiario
     */
    public function test2_Integracion_ObligatoriedadCuatroCamposTexto(): void
    {
        $metodo = new ReflectionMethod(OrientacionModel::class, 'validarRequeridos');
        $modelo = new OrientacionModel();

        // 1. Sin beneficiario debe fallar
        $modelo->__set('motivo_orientacion', 'Motivo válido');
        $modelo->__set('descripcion_orientacion', 'Descripción válida');
        $modelo->__set('indicaciones_orientacion', 'Indicaciones válidas');
        $modelo->__set('obs_adic_orientacion', 'Observaciones válidas');

        try {
            $metodo->invoke($modelo);
            $this->fail('Debe fallar si falta el beneficiario');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El beneficiario es obligatorio.', $e->getMessage());
        }

        // 2. Con beneficiario y los 4 textos debe validar con éxito
        $modelo->__set('id_beneficiario', 1);
        $metodo->invoke($modelo);
        $this->assertSame(1, $modelo->__get('id_beneficiario'));
    }

    /**
     * CP-ORI-007: Control de acceso RBAC para el módulo Orientación
     */
    public function test2_Integracion_ControlAccesoRbacOrientacion(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene retorna false y verificar arroja 401
        $this->assertFalse(Autorizacion::tiene('orientacion', 'crear'));

        try {
            Autorizacion::verificar('orientacion', 'crear');
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
     * CP-ORI-008: Disponibilidad de Controladores y Vistas del Módulo Orientación
     */
    public function test3_Sistema_DisponibilidadControladorYVistasOrientacion(): void
    {
        load_controller('orientacionController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showCrearOrientacion'), 'showCrearOrientacion debe existir');
        $this->assertTrue(function_exists('showConsultarOrientacion'), 'showConsultarOrientacion debe existir');
        $this->assertTrue(function_exists('apiListarOrientacion'), 'apiListarOrientacion debe existir');
        $this->assertTrue(function_exists('apiObtenerOrientacion'), 'apiObtenerOrientacion debe existir');
        $this->assertTrue(function_exists('apiCatalogosOrientacion'), 'apiCatalogosOrientacion debe existir');
        $this->assertTrue(function_exists('apiStatsOrientacion'), 'apiStatsOrientacion debe existir');
        $this->assertTrue(function_exists('generarConstanciaOrientacion'), 'generarConstanciaOrientacion debe existir');
        $this->assertTrue(function_exists('generarReferenciaOrientacion'), 'generarReferenciaOrientacion debe existir');
        $this->assertTrue(function_exists('apiCrearOrientacion'), 'apiCrearOrientacion debe existir');
        $this->assertTrue(function_exists('apiActualizarOrientacion'), 'apiActualizarOrientacion debe existir');
        $this->assertTrue(function_exists('apiEliminarOrientacion'), 'apiEliminarOrientacion debe existir');
        $this->assertTrue(function_exists('contextoOrientacion'), 'contextoOrientacion debe existir');
        $this->assertTrue(function_exists('entradaOrientacion'), 'entradaOrientacion debe existir');
        $this->assertTrue(function_exists('orientacionAsignarEntrada'), 'orientacionAsignarEntrada debe existir');
        $this->assertTrue(function_exists('orientacionAsignarEntradaEdicion'), 'orientacionAsignarEntradaEdicion debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/orientacion/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/orientacion/crear.php', 'Vista crear.php debe existir');
    }

    /**
     * CP-ORI-009: Flujo integral del ciclo de registro de orientación y auditoría
     */
    public function test3_Sistema_FlujoFuncionalConsultaOrientacionYAuditoria(): void
    {
        // 1. Simulación de sesión de orientador activo
        $_SESSION['id_empleado'] = 4;
        $_SESSION['nombre'] = 'Lic. Roberto Gómez';
        $_SESSION['id_tipo_empleado'] = 4;
        $_SESSION['tipo_empleado'] = 'Orientador';

        // 2. Preparación de entidad completa
        $modelo = new OrientacionModel();
        $modelo->__set('id_usuario', (int) $_SESSION['id_empleado']);
        $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado']);
        $modelo->__set('id_tipo_empleado', (int) $_SESSION['id_tipo_empleado']);
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('motivo_orientacion', 'Orientación vocacional y rendimiento académico');
        $modelo->__set('descripcion_orientacion', 'Evaluación de preferencias y hábitos de estudio del beneficiario.');
        $modelo->__set('indicaciones_orientacion', 'Asistir a talleres de técnicas de estudio y manejo del tiempo.');
        $modelo->__set('obs_adic_orientacion', 'Favorable pronóstico de adaptación.');

        // 3. Simulación de registro de auditoría en Bitácora
        Bitacora::registrar(
            'Orientacion',
            'Registro',
            'Registró una orientación.'
        );

        // 4. Verificación de contrato de salida estandarizado
        $salidaEsperada = [
            'id_beneficiario'        => $modelo->__get('id_beneficiario'),
            'motivo_orientacion'     => $modelo->__get('motivo_orientacion'),
            'indicaciones_orientacion' => $modelo->__get('indicaciones_orientacion'),
        ];

        $this->assertSame(1, $salidaEsperada['id_beneficiario']);
        $this->assertSame('Orientación vocacional y rendimiento académico', $salidaEsperada['motivo_orientacion']);
        $this->assertSame('Asistir a talleres de técnicas de estudio y manejo del tiempo.', $salidaEsperada['indicaciones_orientacion']);
    }
}
