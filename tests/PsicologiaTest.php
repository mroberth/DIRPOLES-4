<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\PsicologiaModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class PsicologiaTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente PsicologiaModel y Validaciones)
    // =========================================================================

    /**
     * CP-PSI-001: Validación de asignación correcta de atributos para Diagnóstico
     */
    public function test1_Unitaria_ValidacionDatosDiagnostico(): void
    {
        $modelo = new PsicologiaModel();

        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('id_empleado', 2);
        $modelo->__set('id_usuario', 2);
        $modelo->__set('tipo_consulta', 'Diagnóstico');
        $modelo->__set('id_patologia', 1);
        $modelo->__set('diagnostico', 'Cuadro de estrés académico y ansiedad situacional.');
        $modelo->__set('tratamiento_gen', 'Terapia cognitivo conductual semanal.');
        $modelo->__set('observaciones', 'Paciente refiere mejoría con técnicas de respiración.');

        $this->assertSame(1, $modelo->__get('id_beneficiario'));
        $this->assertSame(2, $modelo->__get('id_empleado'));
        $this->assertSame(2, $modelo->__get('id_usuario'));
        $this->assertSame('Diagnóstico', $modelo->__get('tipo_consulta'));
        $this->assertSame(1, $modelo->__get('id_patologia'));
        $this->assertSame('Cuadro de estrés académico y ansiedad situacional.', $modelo->__get('diagnostico'));
        $this->assertSame('Terapia cognitivo conductual semanal.', $modelo->__get('tratamiento_gen'));
        $this->assertSame('Paciente refiere mejoría con técnicas de respiración.', $modelo->__get('observaciones'));
    }

    /**
     * CP-PSI-002: Rechazo de tipo de consulta fuera del catálogo institucional
     */
    public function test1_Unitaria_RechazoTipoConsultaInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El tipo de consulta no es válido.');

        $modelo = new PsicologiaModel();
        $modelo->__set('tipo_consulta', 'Consulta general');
    }

    /**
     * CP-PSI-003: Rechazo de identificador de beneficiario no positivo
     */
    public function test1_Unitaria_RechazoIdBeneficiarioInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El id_beneficiario no es válido.');

        $modelo = new PsicologiaModel();
        $modelo->__set('id_beneficiario', 0);
    }

    /**
     * CP-PSI-004: Rechazo de identificador de patología negativo
     */
    public function test1_Unitaria_RechazoIdPatologiaInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El id_patologia no es válido.');

        $modelo = new PsicologiaModel();
        $modelo->__set('id_patologia', -1);
    }

    /**
     * CP-PSI-005: Rechazo de longitud excesiva en motivo de cambio de carrera (>100 caracteres)
     */
    public function test1_Unitaria_RechazoLongitudExcesivaMotivoCambio(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El motivo del cambio no puede superar 100 caracteres.');

        $modelo = new PsicologiaModel();
        $textoLargo = str_repeat('A', 105);
        $modelo->__set('motivo_cambio', $textoLargo);
    }

    /**
     * CP-PSI-006: Rechazo de longitud excesiva en duración del retiro temporal (>50 caracteres)
     */
    public function test1_Unitaria_RechazoLongitudExcesivaDuracionRetiro(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La duración del retiro no puede superar 50 caracteres.');

        $modelo = new PsicologiaModel();
        $textoLargo = str_repeat('A', 55);
        $modelo->__set('duracion_retiro', $textoLargo);
    }

    /**
     * CP-PSI-007: Rechazo de longitud excesiva en texto del diagnóstico clínico (>5000 caracteres)
     */
    public function test1_Unitaria_RechazoLongitudExcesivaDiagnostico(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El campo diagnostico no puede superar 5000 caracteres.');

        $modelo = new PsicologiaModel();
        $textoLargo = str_repeat('A', 5005);
        $modelo->__set('diagnostico', $textoLargo);
    }

    /**
     * CP-PSI-008: Conversión automática de campos de texto vacíos a valor null
     */
    public function test1_Unitaria_ConversionCamposVaciosANull(): void
    {
        $modelo = new PsicologiaModel();

        $modelo->__set('observaciones', '   ');
        $this->assertNull($modelo->__get('observaciones'));

        $modelo->__set('tratamiento_gen', '');
        $this->assertNull($modelo->__get('tratamiento_gen'));

        $modelo->__set('motivo_retiro', '   ');
        $this->assertNull($modelo->__get('motivo_retiro'));
    }

    /**
     * CP-PSI-009: Rechazo de atributo no reconocido en el modelo PsicologiaModel
     */
    public function test1_Unitaria_RechazoAtributoDesconocido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage("Atributo no reconocido: 'campo_inexistente'.");

        $modelo = new PsicologiaModel();
        $modelo->__set('campo_inexistente', 'valor');
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas Condicionales, Alcance y RBAC)
    // =========================================================================

    /**
     * CP-PSI-010: Validación de reglas condicionales de campos requeridos según el tipo de consulta
     */
    public function test2_Integracion_ValidacionCondicionalPorTipoConsulta(): void
    {
        $metodo = new ReflectionMethod(PsicologiaModel::class, 'validarTipo');

        // 1. Tipo 'Diagnóstico' requiere id_patologia y diagnostico
        $modeloDiag = new PsicologiaModel();
        $modeloDiag->__set('tipo_consulta', 'Diagnóstico');

        try {
            $metodo->invoke($modeloDiag);
            $this->fail('Debe fallar si falta id_patologia o diagnostico en Diagnóstico');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('obligatorio para Diagnóstico', $e->getMessage());
        }

        // Al suministrar ambos campos en Diagnóstico, debe superar la validación
        $modeloDiag->__set('id_patologia', 1);
        $modeloDiag->__set('diagnostico', 'Ansiedad leve');
        $metodo->invoke($modeloDiag);
        $this->assertSame('Ansiedad leve', $modeloDiag->__get('diagnostico'));

        // 2. Tipo 'Retiro temporal' requiere motivo_retiro y duracion_retiro
        $modeloRetiro = new PsicologiaModel();
        $modeloRetiro->__set('tipo_consulta', 'Retiro temporal');

        try {
            $metodo->invoke($modeloRetiro);
            $this->fail('Debe fallar si faltan campos en Retiro temporal');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('obligatorio para Retiro temporal', $e->getMessage());
        }

        $modeloRetiro->__set('motivo_retiro', 'Motivos de salud física prolongada');
        $modeloRetiro->__set('duracion_retiro', '1 semestre');
        $metodo->invoke($modeloRetiro);
        $this->assertSame('1 semestre', $modeloRetiro->__get('duracion_retiro'));

        // 3. Tipo 'Cambio de carrera' requiere motivo_cambio
        $modeloCambio = new PsicologiaModel();
        $modeloCambio->__set('tipo_consulta', 'Cambio de carrera');

        try {
            $metodo->invoke($modeloCambio);
            $this->fail('Debe fallar si falta motivo_cambio en Cambio de carrera');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('El campo motivo_cambio es obligatorio para Cambio de carrera', $e->getMessage());
        }

        $modeloCambio->__set('motivo_cambio', 'Vocación hacia la informática');
        $metodo->invoke($modeloCambio);
        $this->assertSame('Vocación hacia la informática', $modeloCambio->__get('motivo_cambio'));
    }

    /**
     * CP-PSI-011: Diferenciación de alcance administrativo vs profesional operativo
     */
    public function test2_Integracion_DiferenciacionAlcanceAdminYPsicologo(): void
    {
        $metodo = new ReflectionMethod(PsicologiaModel::class, 'esAdministrativo');

        // Rol Administrador por ID (6)
        $modeloAdminId = new PsicologiaModel();
        $modeloAdminId->__set('id_tipo_empleado', 6);
        $this->assertTrue($metodo->invoke($modeloAdminId), 'ID 6 debe ser reconocido como administrativo');

        // Rol Superusuario por ID (10)
        $modeloSuperId = new PsicologiaModel();
        $modeloSuperId->__set('id_tipo_empleado', 10);
        $this->assertTrue($metodo->invoke($modeloSuperId), 'ID 10 debe ser reconocido como administrativo');

        // Rol Administrador por cadena descriptiva
        $modeloAdminStr = new PsicologiaModel();
        $modeloAdminStr->__set('tipo_empleado', 'Administrador');
        $this->assertTrue($metodo->invoke($modeloAdminStr), 'Texto Administrador debe ser administrativo');

        // Rol Psicólogo operativo (ID 1)
        $modeloPsicologo = new PsicologiaModel();
        $modeloPsicologo->__set('id_tipo_empleado', 1);
        $modeloPsicologo->__set('tipo_empleado', 'Psicologo');
        $this->assertFalse($metodo->invoke($modeloPsicologo), 'Psicólogo no debe tener alcance administrativo total');
    }

    /**
     * CP-PSI-012: Control de acceso RBAC para el módulo Psicología
     */
    public function test2_Integracion_ControlAccesoRbacPsicologia(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene retorna false y verificar arroja 401
        $this->assertFalse(Autorizacion::tiene('psicologia', 'crear'));

        try {
            Autorizacion::verificar('psicologia', 'crear');
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
     * CP-PSI-013: Disponibilidad de Controladores y Vistas del Módulo Psicología
     */
    public function test3_Sistema_DisponibilidadControladorYVistasPsicologia(): void
    {
        load_controller('psicologiaController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showConsultarPsicologia'), 'showConsultarPsicologia debe existir');
        $this->assertTrue(function_exists('showCrearPsicologia'), 'showCrearPsicologia debe existir');
        $this->assertTrue(function_exists('apiListarPsicologia'), 'apiListarPsicologia debe existir');
        $this->assertTrue(function_exists('apiObtenerPsicologia'), 'apiObtenerPsicologia debe existir');
        $this->assertTrue(function_exists('apiCatalogosPsicologia'), 'apiCatalogosPsicologia debe existir');
        $this->assertTrue(function_exists('apiStatsPsicologia'), 'apiStatsPsicologia debe existir');
        $this->assertTrue(function_exists('generarConstanciaPsicologia'), 'generarConstanciaPsicologia debe existir');
        $this->assertTrue(function_exists('generarReferenciaPsicologia'), 'generarReferenciaPsicologia debe existir');
        $this->assertTrue(function_exists('apiCrearPsicologia'), 'apiCrearPsicologia debe existir');
        $this->assertTrue(function_exists('apiActualizarPsicologia'), 'apiActualizarPsicologia debe existir');
        $this->assertTrue(function_exists('apiEliminarPsicologia'), 'apiEliminarPsicologia debe existir');
        $this->assertTrue(function_exists('contextoPsicologia'), 'contextoPsicologia debe existir');
        $this->assertTrue(function_exists('entradaPsicologia'), 'entradaPsicologia debe existir');
        $this->assertTrue(function_exists('psicologiaAsignarEntrada'), 'psicologiaAsignarEntrada debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/psicologia/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/psicologia/crear.php', 'Vista crear.php debe existir');
    }

    /**
     * CP-PSI-014: Flujo integral del ciclo de registro de consulta psicológica y auditoría
     */
    public function test3_Sistema_FlujoFuncionalConsultaPsicologicaYAuditoria(): void
    {
        // 1. Simulación de sesión de profesional psicólogo
        $_SESSION['id_empleado'] = 2;
        $_SESSION['nombre'] = 'Lic. Carmen Mendoza';
        $_SESSION['id_tipo_empleado'] = 1;
        $_SESSION['tipo_empleado'] = 'Psicologo';

        // 2. Preparación de entidad completa
        $modelo = new PsicologiaModel();
        $modelo->__set('id_usuario', (int) $_SESSION['id_empleado']);
        $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado']);
        $modelo->__set('id_tipo_empleado', (int) $_SESSION['id_tipo_empleado']);
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('tipo_consulta', 'Diagnóstico');
        $modelo->__set('id_patologia', 1);
        $modelo->__set('diagnostico', 'Evaluación psicológica de ingreso con aptitud vocacional favorable.');
        $modelo->__set('tratamiento_gen', 'Orientación académica complementaria.');
        $modelo->__set('observaciones', 'Beneficiario con motivación alta.');

        // 3. Simulación de registro de auditoría en Bitácora
        Bitacora::registrar(
            'Psicologia',
            'Registro',
            'Registró una consulta psicológica de tipo ' . $modelo->__get('tipo_consulta') . '.'
        );

        // 4. Verificación de contrato de salida estandarizado
        $salidaEsperada = [
            'tipo_consulta' => $modelo->__get('tipo_consulta'),
            'diagnostico'   => $modelo->__get('diagnostico'),
            'id_patologia'  => $modelo->__get('id_patologia'),
        ];

        $this->assertSame('Diagnóstico', $salidaEsperada['tipo_consulta']);
        $this->assertSame(1, $salidaEsperada['id_patologia']);
        $this->assertSame('Evaluación psicológica de ingreso con aptitud vocacional favorable.', $salidaEsperada['diagnostico']);
    }
}
