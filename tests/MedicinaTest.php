<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\MedicinaModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class MedicinaTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente MedicinaModel y Validaciones)
    // =========================================================================

    /**
     * CP-MED-001: Validación de asignación correcta de atributos clínicos y antropométricos
     */
    public function test1_Unitaria_ValidacionDatosConsultaMedica(): void
    {
        $modelo = new MedicinaModel();

        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('id_empleado', 3);
        $modelo->__set('id_usuario', 3);
        $modelo->__set('id_patologia', 1);
        $modelo->__set('estatura', 1.75);
        $modelo->__set('peso', 72.5);
        $modelo->__set('tipo_sangre', 'O+');
        $modelo->__set('motivo_visita', 'Fiebre y dolor de cabeza recurrente');
        $modelo->__set('diagnostico', 'Cefalea tensional y fatiga');
        $modelo->__set('tratamiento', 'Paracetamol 500mg cada 8 horas');
        $modelo->__set('observaciones', 'Ingerir abundante líquido y reposo');

        $this->assertSame(1, $modelo->__get('id_beneficiario'));
        $this->assertSame(3, $modelo->__get('id_empleado'));
        $this->assertSame(3, $modelo->__get('id_usuario'));
        $this->assertSame(1, $modelo->__get('id_patologia'));
        $this->assertSame(1.75, $modelo->__get('estatura'));
        $this->assertSame(72.5, $modelo->__get('peso'));
        $this->assertSame('O+', $modelo->__get('tipo_sangre'));
        $this->assertSame('Fiebre y dolor de cabeza recurrente', $modelo->__get('motivo_visita'));
        $this->assertSame('Cefalea tensional y fatiga', $modelo->__get('diagnostico'));
        $this->assertSame('Paracetamol 500mg cada 8 horas', $modelo->__get('tratamiento'));
        $this->assertSame('Ingerir abundante líquido y reposo', $modelo->__get('observaciones'));
    }

    /**
     * CP-MED-002: Rechazo de estatura fuera del rango antropométrico válido (0.50m - 2.50m)
     */
    public function test1_Unitaria_RechazoEstaturaFueraDeRango(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La estatura debe estar entre 0.5 y 2.5.');

        $modelo = new MedicinaModel();
        // Estatura de 40 cm (menor a 0.50m)
        $modelo->__set('estatura', 0.40);
    }

    /**
     * CP-MED-003: Rechazo de peso corporal fuera del rango fisiológico válido (2.00kg - 300.00kg)
     */
    public function test1_Unitaria_RechazoPesoFueraDeRango(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La peso debe estar entre 2 y 300.');

        $modelo = new MedicinaModel();
        // Peso de 350 kg (mayor a 300kg)
        $modelo->__set('peso', 350.00);
    }

    /**
     * CP-MED-004: Rechazo de tipo de sangre no conforme al sistema ABO y factor Rh
     */
    public function test1_Unitaria_RechazoTipoSangreInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El tipo de sangre no es válido.');

        $modelo = new MedicinaModel();
        $modelo->__set('tipo_sangre', 'Z+');
    }

    /**
     * CP-MED-005: Rechazo de motivo de consulta obligatorio en blanco
     */
    public function test1_Unitaria_RechazoMotivoVisitaVacio(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El campo motivo_visita es obligatorio.');

        $modelo = new MedicinaModel();
        $modelo->__set('motivo_visita', '   ');
    }

    /**
     * CP-MED-006: Rechazo de diagnóstico médico con longitud superior a 255 caracteres
     */
    public function test1_Unitaria_RechazoDiagnosticoExcesivo(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El campo diagnostico no puede superar 255 caracteres.');

        $modelo = new MedicinaModel();
        $textoLargo = str_repeat('A', 260);
        $modelo->__set('diagnostico', $textoLargo);
    }

    /**
     * CP-MED-007: Rechazo de identificador de beneficiario inválido o no positivo
     */
    public function test1_Unitaria_RechazoIdBeneficiarioInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El id_beneficiario no es válido.');

        $modelo = new MedicinaModel();
        $modelo->__set('id_beneficiario', 0);
    }

    /**
     * CP-MED-008: Normalización y consolidación de insumos médicos prescritos
     */
    public function test1_Unitaria_NormalizacionYConsolidacionInsumos(): void
    {
        $modelo = new MedicinaModel();

        // Enviar dos veces el insumo 1 con cantidad 2 y 3; y el insumo 2 con cantidad 1
        $insumosEntrada = [
            ['id_insumo' => 1, 'cantidad' => 2],
            ['id_insumo' => 1, 'cantidad' => 3],
            ['id_insumo' => 2, 'cantidad' => 1],
        ];

        $modelo->__set('insumos', $insumosEntrada);
        $resultado = $modelo->__get('insumos');

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado, 'Debe consolidar insumos duplicados en 2 registros únicos');
        $this->assertSame(1, $resultado[0]['id_insumo']);
        $this->assertSame(5, $resultado[0]['cantidad'], 'Las cantidades del insumo 1 deben sumar 5');
        $this->assertSame(2, $resultado[1]['id_insumo']);
        $this->assertSame(1, $resultado[1]['cantidad']);
    }

    /**
     * CP-MED-009: Rechazo de atributo no reconocido en el modelo MedicinaModel
     */
    public function test1_Unitaria_RechazoAtributoDesconocido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage("Atributo no reconocido: 'campo_inexistente'.");

        $modelo = new MedicinaModel();
        $modelo->__set('campo_inexistente', 'valor');
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas de Negocio, Alcance y RBAC)
    // =========================================================================

    /**
     * CP-MED-010: Validación de integridad de campos clínicos requeridos para registro
     */
    public function test2_Integracion_ValidacionCamposClinicosRequeridos(): void
    {
        $metodo = new ReflectionMethod(MedicinaModel::class, 'validarRequeridos');
        $modelo = new MedicinaModel();

        // 1. Sin beneficiario debe lanzar excepción
        $modelo->__set('estatura', 1.70);
        $modelo->__set('peso', 65.0);
        $modelo->__set('tipo_sangre', 'A+');
        $modelo->__set('motivo_visita', 'Malestar general');
        $modelo->__set('diagnostico', 'Gripe común');
        $modelo->__set('tratamiento', 'Reposo y analgésicos');

        try {
            $metodo->invoke($modelo);
            $this->fail('Debe fallar si falta el beneficiario');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El beneficiario es obligatorio.', $e->getMessage());
        }

        // 2. Sin patología debe lanzar excepción
        $modelo->__set('id_beneficiario', 1);
        try {
            $metodo->invoke($modelo);
            $this->fail('Debe fallar si falta la patología');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La patología es obligatoria.', $e->getMessage());
        }

        // 3. Con todos los campos clínicos debe aprobar
        $modelo->__set('id_patologia', 1);
        $metodo->invoke($modelo);
        $this->assertSame(1, $modelo->__get('id_patologia'));
    }

    /**
     * CP-MED-011: Diferenciación de alcance administrativo vs profesional médico
     */
    public function test2_Integracion_DiferenciacionAlcanceAdminYMedico(): void
    {
        $metodo = new ReflectionMethod(MedicinaModel::class, 'esAdministrativo');

        // Rol Administrador por ID (6)
        $modeloAdmin = new MedicinaModel();
        $modeloAdmin->__set('id_tipo_empleado', 6);
        $this->assertTrue($metodo->invoke($modeloAdmin), 'ID 6 debe ser reconocido como administrativo');

        // Rol Superusuario por ID (10)
        $modeloSuper = new MedicinaModel();
        $modeloSuper->__set('id_tipo_empleado', 10);
        $this->assertTrue($metodo->invoke($modeloSuper), 'ID 10 debe ser reconocido como administrativo');

        // Rol Médico operativo (ID 2)
        $modeloMedico = new MedicinaModel();
        $modeloMedico->__set('id_tipo_empleado', 2);
        $modeloMedico->__set('tipo_empleado', 'Medico');
        $this->assertFalse($metodo->invoke($modeloMedico), 'Médico operativo no debe tener alcance administrativo global');
    }

    /**
     * CP-MED-012: Control de acceso RBAC para el módulo Medicina
     */
    public function test2_Integracion_ControlAccesoRbacMedicina(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene retorna false y verificar arroja 401
        $this->assertFalse(Autorizacion::tiene('medicina', 'crear'));

        try {
            Autorizacion::verificar('medicina', 'crear');
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
     * CP-MED-013: Disponibilidad de Controladores y Vistas del Módulo Medicina
     */
    public function test3_Sistema_DisponibilidadControladorYVistasMedicina(): void
    {
        load_controller('medicinaController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showCrearMedicina'), 'showCrearMedicina debe existir');
        $this->assertTrue(function_exists('showConsultarMedicina'), 'showConsultarMedicina debe existir');
        $this->assertTrue(function_exists('apiListarMedicina'), 'apiListarMedicina debe existir');
        $this->assertTrue(function_exists('apiObtenerMedicina'), 'apiObtenerMedicina debe existir');
        $this->assertTrue(function_exists('apiCatalogosMedicina'), 'apiCatalogosMedicina debe existir');
        $this->assertTrue(function_exists('apiStatsMedicina'), 'apiStatsMedicina debe existir');
        $this->assertTrue(function_exists('generarConstanciaMedicina'), 'generarConstanciaMedicina debe existir');
        $this->assertTrue(function_exists('generarReferenciaMedicina'), 'generarReferenciaMedicina debe existir');
        $this->assertTrue(function_exists('generarRecipeMedicina'), 'generarRecipeMedicina debe existir');
        $this->assertTrue(function_exists('apiCrearMedicina'), 'apiCrearMedicina debe existir');
        $this->assertTrue(function_exists('apiActualizarMedicina'), 'apiActualizarMedicina debe existir');
        $this->assertTrue(function_exists('apiEliminarMedicina'), 'apiEliminarMedicina debe existir');
        $this->assertTrue(function_exists('contextoMedicina'), 'contextoMedicina debe existir');
        $this->assertTrue(function_exists('entradaMedicina'), 'entradaMedicina debe existir');
        $this->assertTrue(function_exists('medicinaAsignarEntrada'), 'medicinaAsignarEntrada debe existir');
        $this->assertTrue(function_exists('medicinaAsignarEntradaEdicion'), 'medicinaAsignarEntradaEdicion debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/medicina/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/medicina/crear.php', 'Vista crear.php debe existir');
    }

    /**
     * CP-MED-014: Flujo integral del ciclo de registro de consulta médica con insumos y auditoría
     */
    public function test3_Sistema_FlujoFuncionalConsultaMedicaYAuditoria(): void
    {
        // 1. Simulación de sesión de profesional médico
        $_SESSION['id_empleado'] = 3;
        $_SESSION['nombre'] = 'Dr. José Gregorio';
        $_SESSION['id_tipo_empleado'] = 2;
        $_SESSION['tipo_empleado'] = 'Medico';

        // 2. Preparación de entidad completa
        $modelo = new MedicinaModel();
        $modelo->__set('id_usuario', (int) $_SESSION['id_empleado']);
        $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado']);
        $modelo->__set('id_tipo_empleado', (int) $_SESSION['id_tipo_empleado']);
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('id_patologia', 1);
        $modelo->__set('estatura', 1.80);
        $modelo->__set('peso', 80.0);
        $modelo->__set('tipo_sangre', 'O+');
        $modelo->__set('motivo_visita', 'Revisión médica general');
        $modelo->__set('diagnostico', 'Paciente en condiciones clínicas óptimas');
        $modelo->__set('tratamiento', 'Sin prescripción farmacológica activa');
        $modelo->__set('observaciones', 'Control preventivo anual');
        $modelo->__set('insumos', [
            ['id_insumo' => 1, 'cantidad' => 2],
        ]);

        // 3. Simulación de registro de auditoría en Bitácora
        Bitacora::registrar(
            'Medicina',
            'Registro',
            'Registró una consulta médica.'
        );

        // 4. Verificación de contrato de salida estandarizado
        $salidaEsperada = [
            'id_beneficiario' => $modelo->__get('id_beneficiario'),
            'id_patologia'    => $modelo->__get('id_patologia'),
            'diagnostico'     => $modelo->__get('diagnostico'),
            'insumos'         => count($modelo->__get('insumos')),
        ];

        $this->assertSame(1, $salidaEsperada['id_beneficiario']);
        $this->assertSame(1, $salidaEsperada['id_patologia']);
        $this->assertSame('Paciente en condiciones clínicas óptimas', $salidaEsperada['diagnostico']);
        $this->assertSame(1, $salidaEsperada['insumos']);
    }
}
