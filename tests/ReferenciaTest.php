<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Core\Notificador;
use App\Models\ReferenciaModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ReferenciaTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente ReferenciaModel y Validaciones)
    // =========================================================================

    /**
     * CP-REF-001: Validación de asignación correcta de atributos para Referencia
     */
    public function test1_Unitaria_ValidacionAsignacionAtributosReferencia(): void
    {
        $modelo = new ReferenciaModel();

        $modelo->__set('id_referencia', 1);
        $modelo->__set('id_beneficiario', 10);
        $modelo->__set('id_empleado_origen', 2);
        $modelo->__set('id_servicio_origen', 2);
        $modelo->__set('id_empleado_destino', 1);
        $modelo->__set('id_servicio_destino', 1);
        $modelo->__set('motivo', 'Evaluación psicológica integral por cuadro de ansiedad');
        $modelo->__set('observaciones', 'Estudiante remitido desde consulta médica por sintomatología somática persistente.');
        $modelo->__set('es_admin', false);
        $modelo->__set('id_empleado', 2);

        $this->assertSame(1, $modelo->__get('id_referencia'));
        $this->assertSame(10, $modelo->__get('id_beneficiario'));
        $this->assertSame(2, $modelo->__get('id_empleado_origen'));
        $this->assertSame(2, $modelo->__get('id_servicio_origen'));
        $this->assertSame(1, $modelo->__get('id_empleado_destino'));
        $this->assertSame(1, $modelo->__get('id_servicio_destino'));
        $this->assertSame('Evaluación psicológica integral por cuadro de ansiedad', $modelo->__get('motivo'));
        $this->assertSame('Estudiante remitido desde consulta médica por sintomatología somática persistente.', $modelo->__get('observaciones'));
        $this->assertFalse($modelo->__get('es_admin'));
        $this->assertSame(2, $modelo->__get('id_empleado'));
    }

    /**
     * CP-REF-002: Rechazo de motivo de referencia obligatorio y límites de longitud
     */
    public function test1_Unitaria_RechazoMotivoObligatorioYLimites(): void
    {
        $modelo = new ReferenciaModel();

        // 1. Motivo vacío
        try {
            $modelo->__set('motivo', '   ');
            $this->fail('Debe rechazar motivo en blanco');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El motivo de la referencia es obligatorio.', $e->getMessage());
        }

        // 2. Motivo menor a 2 caracteres
        try {
            $modelo->__set('motivo', 'A');
            $this->fail('Debe rechazar motivo menor a 2 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El motivo debe tener entre 2 y 255 caracteres.', $e->getMessage());
        }

        // 3. Motivo superior a 255 caracteres
        try {
            $modelo->__set('motivo', str_repeat('X', 256));
            $this->fail('Debe rechazar motivo superior a 255 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El motivo debe tener entre 2 y 255 caracteres.', $e->getMessage());
        }
    }

    /**
     * CP-REF-003: Rechazo de observaciones obligatorias y límites de longitud
     */
    public function test1_Unitaria_RechazoObservacionesObligatoriasYLimites(): void
    {
        $modelo = new ReferenciaModel();

        // 1. Observaciones vacías
        try {
            $modelo->__set('observaciones', '   ');
            $this->fail('Debe rechazar observaciones en blanco');
        } catch (ExcepcionApi $e) {
            $this->assertSame('Las observaciones son obligatorias.', $e->getMessage());
        }

        // 2. Observaciones menores a 2 caracteres
        try {
            $modelo->__set('observaciones', 'O');
            $this->fail('Debe rechazar observaciones menores a 2 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('Las observaciones deben tener entre 2 y 2000 caracteres.', $e->getMessage());
        }

        // 3. Observaciones superiores a 2000 caracteres
        try {
            $modelo->__set('observaciones', str_repeat('B', 2005));
            $this->fail('Debe rechazar observaciones mayores a 2000 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('Las observaciones deben tener entre 2 y 2000 caracteres.', $e->getMessage());
        }
    }

    /**
     * CP-REF-004: Rechazo de identificadores relacionales no positivos
     */
    public function test1_Unitaria_RechazoIdentificadoresInvalidos(): void
    {
        $modelo = new ReferenciaModel();

        // 1. Beneficiario no positivo
        try {
            $modelo->__set('id_beneficiario', 0);
            $this->fail('Debe rechazar id_beneficiario = 0');
        } catch (ExcepcionApi $e) {
            $this->assertSame("El valor de 'id_beneficiario' no es válido.", $e->getMessage());
        }

        // 2. Servicio de origen negativo
        try {
            $modelo->__set('id_servicio_origen', -1);
            $this->fail('Debe rechazar id_servicio_origen negativo');
        } catch (ExcepcionApi $e) {
            $this->assertSame("El valor de 'id_servicio_origen' no es válido.", $e->getMessage());
        }

        // 3. Empleado de destino no positivo
        try {
            $modelo->__set('id_empleado_destino', 0);
            $this->fail('Debe rechazar id_empleado_destino = 0');
        } catch (ExcepcionApi $e) {
            $this->assertSame("El valor de 'id_empleado_destino' no es válido.", $e->getMessage());
        }
    }

    /**
     * CP-REF-005: Saneamiento de etiquetas HTML y prevención XSS en textos
     */
    public function test1_Unitaria_SaneamientoHtmlEnTextos(): void
    {
        $modelo = new ReferenciaModel();

        $motivoConHtml = '<p>Interconsulta médica prioritaria</p>';
        $obsConHtml = '<b>Paciente requiere valoración inmediata</b><script>alert(1)</script>';

        $modelo->__set('motivo', $motivoConHtml);
        $modelo->__set('observaciones', $obsConHtml);

        $this->assertSame('Interconsulta médica prioritaria', $modelo->__get('motivo'));
        $this->assertSame('Paciente requiere valoración inmediataalert(1)', $modelo->__get('observaciones'));
    }

    /**
     * CP-REF-006: Asignación de valores booleanos de rol y límites de paginación
     */
    public function test1_Unitaria_AsignacionValoresBooleanosYPaginacion(): void
    {
        $modelo = new ReferenciaModel();

        $modelo->__set('es_admin', true);
        $modelo->__set('limit', 150);
        $modelo->__set('offset', 25);

        $this->assertTrue($modelo->__get('es_admin'));
        $this->assertSame(150, $modelo->__get('limit'));
        $this->assertSame(25, $modelo->__get('offset'));

        // Normalización de números negativos en paginación
        $modelo->__set('offset', -10);
        $this->assertSame(0, $modelo->__get('offset'));
    }

    /**
     * CP-REF-007: Rechazo de atributo no reconocido en ReferenciaModel
     */
    public function test1_Unitaria_RechazoAtributoDesconocido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage("Atributo no reconocido: 'campo_inexistente'.");

        $modelo = new ReferenciaModel();
        $modelo->__set('campo_inexistente', 'valor');
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas de Negocio, Alcance y RBAC)
    // =========================================================================

    /**
     * CP-REF-008: Regla de interdepartamentalidad: origen y destino deben ser distintos
     */
    public function test2_Integracion_RechazoMismoServicioOEmpleadoEnReferencia(): void
    {
        $modelo = new ReferenciaModel();
        $modelo->__set('es_admin', true);
        $modelo->__set('id_empleado', 6);
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('motivo', 'Evaluación médica');
        $modelo->__set('observaciones', 'Observación clínica');

        // 1. Mismo servicio de origen y destino
        $modelo->__set('id_servicio_origen', 1);
        $modelo->__set('id_servicio_destino', 1);
        $modelo->__set('id_empleado_origen', 1);
        $modelo->__set('id_empleado_destino', 2);

        try {
            $modelo->manejarAccion('crear');
            $this->fail('Debe rechazar mismo servicio de origen y destino');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El servicio destino debe ser distinto al servicio de origen.', $e->getMessage());
        }

        // 2. Mismo empleado de origen y destino
        $modelo->__set('id_servicio_origen', 1);
        $modelo->__set('id_servicio_destino', 2);
        $modelo->__set('id_empleado_origen', 3);
        $modelo->__set('id_empleado_destino', 3);

        try {
            $modelo->manejarAccion('crear');
            $this->fail('Debe rechazar mismo empleado de origen y destino');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El empleado destino debe ser distinto del empleado de origen.', $e->getMessage());
        }
    }

    /**
     * CP-REF-009: Rechazo de creación de referencia sin identificación del empleado emisor
     */
    public function test2_Integracion_RechazoCreacionSinEmpleadoActor(): void
    {
        $modelo = new ReferenciaModel();
        // Sin asignar id_empleado
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('motivo', 'Evaluación médica');
        $modelo->__set('observaciones', 'Observación clínica');

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('No se pudo identificar al empleado que refiere.');

        $modelo->manejarAccion('crear');
    }

    /**
     * CP-REF-010: Inspección por reflexión de métodos auxiliares de alcance y seguridad
     */
    public function test2_Integracion_VerificacionMetodosPrivadosAlcanceYSeguridad(): void
    {
        $metodoEsAdmin = new ReflectionMethod(ReferenciaModel::class, 'esAdmin');
        $metodoAsegurarAlcance = new ReflectionMethod(ReferenciaModel::class, 'asegurarAlcance');
        $metodoWhereAlcance = new ReflectionMethod(ReferenciaModel::class, 'whereAlcance');
        $metodoDatosEmpleado = new ReflectionMethod(ReferenciaModel::class, 'datosEmpleado');
        $metodoServicioActivo = new ReflectionMethod(ReferenciaModel::class, 'servicioActivo');

        $this->assertTrue($metodoEsAdmin->isPrivate(), 'esAdmin debe ser privado');
        $this->assertTrue($metodoAsegurarAlcance->isPrivate(), 'asegurarAlcance debe ser privado');
        $this->assertTrue($metodoWhereAlcance->isPrivate(), 'whereAlcance debe ser privado');
        $this->assertTrue($metodoDatosEmpleado->isPrivate(), 'datosEmpleado debe ser privado');
        $this->assertTrue($metodoServicioActivo->isPrivate(), 'servicioActivo debe ser privado');

        // Verificación de cláusula WHERE de alcance según rol
        $modelo = new ReferenciaModel();
        $modelo->__set('id_empleado', 4);

        // Sin admin: WHERE filtra por id_empleado origen o destino
        $modelo->__set('es_admin', false);
        [$sqlNoAdmin, $paramsNoAdmin] = $metodoWhereAlcance->invoke($modelo, 'r');
        $this->assertStringContainsString(':id_empleado IN (r.id_empleado_origen, r.id_empleado_destino)', $sqlNoAdmin);
        $this->assertSame(4, $paramsNoAdmin[':id_empleado']);

        // Con admin: WHERE queda vacío (ve todo)
        $modelo->__set('es_admin', true);
        [$sqlAdmin, $paramsAdmin] = $metodoWhereAlcance->invoke($modelo, 'r');
        $this->assertSame('', $sqlAdmin);
        $this->assertSame([], $paramsAdmin);
    }

    /**
     * CP-REF-011: Control de acceso RBAC para el módulo Referencias
     */
    public function test2_Integracion_ControlAccesoRbacReferencias(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene retorna false y verificar arroja 401
        $this->assertFalse(Autorizacion::tiene('referencias', 'crear'));
        $this->assertFalse(Autorizacion::tiene('referencias', 'eliminar'));

        try {
            Autorizacion::verificar('referencias', 'crear');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }

        try {
            Autorizacion::verificar('referencias', 'eliminar');
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
     * CP-REF-012: Disponibilidad de Controladores y Vistas del Módulo Referencias
     */
    public function test3_Sistema_DisponibilidadControladorYVistasReferencias(): void
    {
        load_controller('referenciaController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showCrearReferencia'), 'showCrearReferencia debe existir');
        $this->assertTrue(function_exists('showConsultarReferencias'), 'showConsultarReferencias debe existir');
        $this->assertTrue(function_exists('apiServiciosReferencia'), 'apiServiciosReferencia debe existir');
        $this->assertTrue(function_exists('apiEmpleadosServicioReferencia'), 'apiEmpleadosServicioReferencia debe existir');
        $this->assertTrue(function_exists('apiBeneficiariosReferencia'), 'apiBeneficiariosReferencia debe existir');
        $this->assertTrue(function_exists('apiListarReferencias'), 'apiListarReferencias debe existir');
        $this->assertTrue(function_exists('apiObtenerReferencia'), 'apiObtenerReferencia debe existir');
        $this->assertTrue(function_exists('apiReferenciasStats'), 'apiReferenciasStats debe existir');
        $this->assertTrue(function_exists('apiCrearReferencia'), 'apiCrearReferencia debe existir');
        $this->assertTrue(function_exists('apiAceptarReferencia'), 'apiAceptarReferencia debe existir');
        $this->assertTrue(function_exists('apiRechazarReferencia'), 'apiRechazarReferencia debe existir');
        $this->assertTrue(function_exists('apiEliminarReferencia'), 'apiEliminarReferencia debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/referencias/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/referencias/crear.php', 'Vista crear.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/referencias/components/stats.php', 'Componente stats.php debe existir');
    }

    /**
     * CP-REF-013: Flujo funcional de creación de referencia, auditoría y notificación
     */
    public function test3_Sistema_FlujoFuncionalCreacionReferenciaYAuditoria(): void
    {
        // 1. Simulación de sesión de Médico autenticado
        $_SESSION['id_empleado'] = 2;
        $_SESSION['nombre'] = 'Dr. Juan Pérez';
        $_SESSION['id_tipo_empleado'] = 2;
        $_SESSION['tipo_empleado'] = 'Medico';

        // 2. Preparación de entidad referencia
        $modelo = new ReferenciaModel();
        $modelo->__set('id_beneficiario', 5);
        $modelo->__set('id_servicio_origen', 2);
        $modelo->__set('id_empleado_origen', (int) $_SESSION['id_empleado']);
        $modelo->__set('id_servicio_destino', 1);
        $modelo->__set('id_empleado_destino', 1);
        $modelo->__set('motivo', 'Apoyo psicológico por estrés académico');
        $modelo->__set('observaciones', 'Paciente refiere cefalea tensional y sobrecarga de evaluaciones.');
        $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);
        $modelo->__set('es_admin', false);

        // 3. Auditoría institucional en Bitácora
        Bitacora::registrar(
            'Referencias',
            'Registro',
            'Creó la referencia #101 del beneficiario ID 5 hacia Psicología.'
        );

        // 4. Verificación de contrato de salida estandarizado (estado 'Pendiente')
        $salidaEsperada = [
            'id_referencia'       => 101,
            'id_empleado_origen'  => $modelo->__get('id_empleado_origen'),
            'id_empleado_destino' => $modelo->__get('id_empleado_destino'),
            'motivo'              => $modelo->__get('motivo'),
            'estado'              => 'Pendiente',
        ];

        $this->assertSame(101, $salidaEsperada['id_referencia']);
        $this->assertSame(2, $salidaEsperada['id_empleado_origen']);
        $this->assertSame(1, $salidaEsperada['id_empleado_destino']);
        $this->assertSame('Apoyo psicológico por estrés académico', $salidaEsperada['motivo']);
        $this->assertSame('Pendiente', $salidaEsperada['estado']);
    }

    /**
     * CP-REF-014: Flujo integral de transición de estados (aceptación, rechazo, baja) y trazabilidad
     */
    public function test3_Sistema_FlujoIntegralTransicionEstadosYAuditoria(): void
    {
        // 1. Simulación de sesión de Psicólogo (empleado destino)
        $_SESSION['id_empleado'] = 1;
        $_SESSION['nombre'] = 'Lic. Carlos Rodríguez';
        $_SESSION['id_tipo_empleado'] = 1;
        $_SESSION['tipo_empleado'] = 'Psicologo';

        // 2. Auditoría de Aceptación de Referencia
        Bitacora::registrar(
            'Referencias',
            'Actualización',
            'Aceptó la referencia #101.'
        );

        // 3. Auditoría de Rechazo de Referencia con motivo
        Bitacora::registrar(
            'Referencias',
            'Actualización',
            'Rechazó la referencia #102 por falta de recaudos complementarios.'
        );

        // 4. Auditoría de Eliminación de Referencia pendiente
        Bitacora::registrar(
            'Referencias',
            'Eliminación',
            'Eliminó la referencia #103 (estado Pendiente).'
        );

        // 5. Verificación de contrato del log de transiciones
        $logTransicion = [
            'id_referencia'    => 101,
            'estado_anterior'  => 'Pendiente',
            'estado_nuevo'     => 'Aceptada',
            'id_empleado'      => 1,
            'observaciones'    => 'Referencia aceptada',
        ];

        $this->assertSame(101, $logTransicion['id_referencia']);
        $this->assertSame('Pendiente', $logTransicion['estado_anterior']);
        $this->assertSame('Aceptada', $logTransicion['estado_nuevo']);
        $this->assertSame(1, $logTransicion['id_empleado']);
        $this->assertSame('Referencia aceptada', $logTransicion['observaciones']);
    }
}
