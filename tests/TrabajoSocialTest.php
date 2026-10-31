<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\TrabajoSocialModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TrabajoSocialTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente TrabajoSocialModel y Sub-módulos)
    // =========================================================================

    /**
     * CP-TS-001: Validación de asignación correcta en sub-módulo Becas (Banco y Cuenta BCV)
     */
    public function test1_Unitaria_ValidacionSubmoduloBecas(): void
    {
        $modelo = new TrabajoSocialModel();

        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('id_usuario', 5);
        $modelo->__set('tipo_banco', '0102'); // Banco de Venezuela
        $modelo->__set('cta_bcv', '1234567890123456'); // Exactamente 16 dígitos
        $modelo->__set('direccion_pdf', 'uploads/trabajo_social/becas/planilla_test.pdf');

        $this->assertSame(1, $modelo->__get('id_beneficiario'));
        $this->assertSame(5, $modelo->__get('id_usuario'));
        $this->assertSame('0102', $modelo->__get('tipo_banco'));
        $this->assertSame('1234567890123456', $modelo->__get('cta_bcv'));
        $this->assertSame('uploads/trabajo_social/becas/planilla_test.pdf', $modelo->__get('direccion_pdf'));
    }

    /**
     * CP-TS-002: Rechazo de cuenta BCV con formato no numérico o longitud distinta a 16 dígitos
     */
    public function test1_Unitaria_RechazoCuentaBcvInvalida(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La cuenta BCV debe tener exactamente 16 dígitos.');

        $modelo = new TrabajoSocialModel();
        // Menos de 16 dígitos
        $modelo->__set('cta_bcv', '1234567890');
    }

    /**
     * CP-TS-003: Validación de asignación correcta en sub-módulo Exoneraciones
     */
    public function test1_Unitaria_ValidacionSubmoduloExoneraciones(): void
    {
        $modelo = new TrabajoSocialModel();

        $modelo->__set('id_beneficiario', 2);
        $modelo->__set('motivo', 'Inscripción');
        $modelo->__set('otro_motivo', 'No aplica');
        $modelo->__set('carnet_discapacidad', 'CONAPDIS-12345');
        $modelo->__set('direccion_carta', 'uploads/trabajo_social/exoneracion/carta_test.pdf');

        $this->assertSame(2, $modelo->__get('id_beneficiario'));
        $this->assertSame('Inscripción', $modelo->__get('motivo'));
        $this->assertSame('No aplica', $modelo->__get('otro_motivo'));
        $this->assertSame('CONAPDIS-12345', $modelo->__get('carnet_discapacidad'));
        $this->assertSame('uploads/trabajo_social/exoneracion/carta_test.pdf', $modelo->__get('direccion_carta'));
    }

    /**
     * CP-TS-004: Rechazo de motivo de exoneración fuera del catálogo admitido
     */
    public function test1_Unitaria_RechazoMotivoExoneracionInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El motivo de exoneración no es válido.');

        $modelo = new TrabajoSocialModel();
        $modelo->__set('motivo', 'MotivoInexistente');
    }

    /**
     * CP-TS-005: Validación de asignación correcta en sub-módulo FAMES
     */
    public function test1_Unitaria_ValidacionSubmoduloFames(): void
    {
        $modelo = new TrabajoSocialModel();

        $modelo->__set('id_beneficiario', 3);
        $modelo->__set('id_patologia', 3);
        $modelo->__set('tipo_ayuda', 'Económica');
        $modelo->__set('otro_tipo', 'No aplica');

        $this->assertSame(3, $modelo->__get('id_beneficiario'));
        $this->assertSame(3, $modelo->__get('id_patologia'));
        $this->assertSame('Económica', $modelo->__get('tipo_ayuda'));
    }

    /**
     * CP-TS-006: Validación de asignación correcta en sub-módulo Embarazadas
     */
    public function test1_Unitaria_ValidacionSubmoduloEmbarazadas(): void
    {
        $modelo = new TrabajoSocialModel();

        $modelo->__set('id_beneficiario', 4);
        $modelo->__set('id_patologia', 3);
        $modelo->__set('semanas_gest', 24);
        $modelo->__set('codigo_patria', '12345678');
        $modelo->__set('serial_patria', '87654321');
        $modelo->__set('estado', 'En Proceso');

        $this->assertSame(4, $modelo->__get('id_beneficiario'));
        $this->assertSame(24, $modelo->__get('semanas_gest'));
        $this->assertSame(12345678, $modelo->__get('codigo_patria'));
        $this->assertSame(87654321, $modelo->__get('serial_patria'));
        $this->assertSame('En Proceso', $modelo->__get('estado'));
    }

    /**
     * CP-TS-007: Rechazo de semanas de gestación fuera del rango biológico (1 a 45 semanas)
     */
    public function test1_Unitaria_RechazoSemanasGestacionFueraDeRango(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('Las semanas de gestación deben estar entre 1 y 45.');

        $modelo = new TrabajoSocialModel();
        // 50 semanas de gestación (superior a 45)
        $modelo->__set('semanas_gest', 50);
    }

    /**
     * CP-TS-008: Rechazo de ruta física de estudio socioeconómico fuera de la carpeta autorizada
     */
    public function test1_Unitaria_RechazoRutaEstudioSocioeconomicoInvalida(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La ruta del estudio socioeconómico no es válida.');

        $modelo = new TrabajoSocialModel();
        // Ruta que no inicia con 'uploads/trabajo_social/'
        $modelo->__set('direccion_estudiose', 'otra_carpeta/archivo.pdf');
    }

    /**
     * CP-TS-009: Rechazo de atributo no reconocido en el modelo TrabajoSocialModel
     */
    public function test1_Unitaria_RechazoAtributoDesconocido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage("Atributo no reconocido: 'campo_inexistente'.");

        $modelo = new TrabajoSocialModel();
        $modelo->__set('campo_inexistente', 'valor');
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Sub-módulos, Alcance y RBAC)
    // =========================================================================

    /**
     * CP-TS-010: Restricción del catálogo de sub-módulos integrados de Trabajo Social
     */
    public function test2_Integracion_ValidacionTiposSubmodulosPermitidos(): void
    {
        $modelo = new TrabajoSocialModel();

        // 1. Tipos válidos del hub de Trabajo Social
        foreach (['becas', 'exoneraciones', 'fames', 'embarazadas'] as $tipoValido) {
            $modelo->__set('tipo', $tipoValido);
            $this->assertSame($tipoValido, $modelo->__get('tipo'));
        }

        // 2. Tipo fuera del hub debe arrojar excepción
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El tipo de registro no es válido.');
        $modelo->__set('tipo', 'prestamos');
    }

    /**
     * CP-TS-011: Diferenciación de alcance administrativo vs Trabajador Social
     */
    public function test2_Integracion_DiferenciacionAlcanceAdminYTrabajadorSocial(): void
    {
        $metodo = new ReflectionMethod(TrabajoSocialModel::class, 'esAdministrativo');

        // Rol Administrador por ID (6)
        $modeloAdmin = new TrabajoSocialModel();
        $modeloAdmin->__set('id_tipo_empleado', 6);
        $this->assertTrue($metodo->invoke($modeloAdmin), 'ID 6 debe ser reconocido como administrativo');

        // Rol Superusuario por ID (10)
        $modeloSuper = new TrabajoSocialModel();
        $modeloSuper->__set('id_tipo_empleado', 10);
        $this->assertTrue($metodo->invoke($modeloSuper), 'ID 10 debe ser reconocido como administrativo');

        // Rol Trabajador Social operativo (ID 3)
        $modeloTS = new TrabajoSocialModel();
        $modeloTS->__set('id_tipo_empleado', 3);
        $modeloTS->__set('tipo_empleado', 'Trabajador Social');
        $this->assertFalse($metodo->invoke($modeloTS), 'Trabajador Social no debe tener alcance administrativo global');
    }

    /**
     * CP-TS-012: Control de acceso RBAC para el módulo Trabajo Social
     */
    public function test2_Integracion_ControlAccesoRbacTrabajoSocial(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene retorna false y verificar arroja 401
        $this->assertFalse(Autorizacion::tiene('trabajador social', 'crear'));

        try {
            Autorizacion::verificar('trabajador social', 'crear');
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
     * CP-TS-013: Disponibilidad de Controladores y Vistas del Módulo Trabajo Social
     */
    public function test3_Sistema_DisponibilidadControladorYVistasTrabajoSocial(): void
    {
        load_controller('trabajoSocialController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showCrearTrabajoSocial'), 'showCrearTrabajoSocial debe existir');
        $this->assertTrue(function_exists('showConsultarTrabajoSocial'), 'showConsultarTrabajoSocial debe existir');
        $this->assertTrue(function_exists('apiCatalogosTrabajoSocial'), 'apiCatalogosTrabajoSocial debe existir');
        $this->assertTrue(function_exists('trabajoSocialSubirPdf'), 'trabajoSocialSubirPdf debe existir');
        $this->assertTrue(function_exists('apiCrearBeca'), 'apiCrearBeca debe existir');
        $this->assertTrue(function_exists('apiCrearExoneracion'), 'apiCrearExoneracion debe existir');
        $this->assertTrue(function_exists('apiExoneracionesPendientes'), 'apiExoneracionesPendientes debe existir');
        $this->assertTrue(function_exists('apiStatsTrabajoSocial'), 'apiStatsTrabajoSocial debe existir');
        $this->assertTrue(function_exists('generarConstanciaTrabajoSocial'), 'generarConstanciaTrabajoSocial debe existir');
        $this->assertTrue(function_exists('generarReferenciaTrabajoSocial'), 'generarReferenciaTrabajoSocial debe existir');
        $this->assertTrue(function_exists('apiCrearFames'), 'apiCrearFames debe existir');
        $this->assertTrue(function_exists('apiCrearEmbarazada'), 'apiCrearEmbarazada debe existir');
        $this->assertTrue(function_exists('trabajoSocialAsignarEntradaEdicion'), 'trabajoSocialAsignarEntradaEdicion debe existir');
        $this->assertTrue(function_exists('contextoTrabajoSocial'), 'contextoTrabajoSocial debe existir');
        $this->assertTrue(function_exists('entradaTrabajoSocialJson'), 'entradaTrabajoSocialJson debe existir');
        $this->assertTrue(function_exists('trabajoSocialEtiquetaTipo'), 'trabajoSocialEtiquetaTipo debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/trabajo-social/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/trabajo-social/crear.php', 'Vista crear.php debe existir');
    }

    /**
     * CP-TS-014: Flujo integral del ciclo de registro de beca y auditoría
     */
    public function test3_Sistema_FlujoFuncionalGestionTrabajoSocialYAuditoria(): void
    {
        // 1. Simulación de sesión de trabajadora social activa
        $_SESSION['id_empleado'] = 5;
        $_SESSION['nombre'] = 'Lic. Elena Ramos';
        $_SESSION['id_tipo_empleado'] = 3;
        $_SESSION['tipo_empleado'] = 'Trabajador Social';

        // 2. Preparación de entidad completa
        $modelo = new TrabajoSocialModel();
        $modelo->__set('id_usuario', (int) $_SESSION['id_empleado']);
        $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado']);
        $modelo->__set('id_tipo_empleado', (int) $_SESSION['id_tipo_empleado']);
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('tipo_banco', '0102');
        $modelo->__set('cta_bcv', '0102123456789012');
        $modelo->__set('direccion_pdf', 'uploads/trabajo_social/becas/expediente_001.pdf');

        // 3. Simulación de registro de auditoría en Bitácora
        Bitacora::registrar(
            'Trabajador Social',
            'Registro',
            'Registró una beca de trabajo social.'
        );

        // 4. Verificación de contrato de salida estandarizado
        $salidaEsperada = [
            'id_beneficiario' => $modelo->__get('id_beneficiario'),
            'tipo_banco'      => $modelo->__get('tipo_banco'),
            'cta_bcv'         => $modelo->__get('cta_bcv'),
            'direccion_pdf'   => $modelo->__get('direccion_pdf'),
        ];

        $this->assertSame(1, $salidaEsperada['id_beneficiario']);
        $this->assertSame('0102', $salidaEsperada['tipo_banco']);
        $this->assertSame('0102123456789012', $salidaEsperada['cta_bcv']);
        $this->assertSame('uploads/trabajo_social/becas/expediente_001.pdf', $salidaEsperada['direccion_pdf']);
    }
}
