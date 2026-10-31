<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\CitaModel;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CitaTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente CitaModel y Validaciones)
    // =========================================================================

    /**
     * CP-CIT-001: Validación de asignación correcta de atributos íntegros de una cita
     */
    public function test1_Unitaria_ValidacionDatosCita(): void
    {
        $modelo = new CitaModel();
        $fechaValida = (new DateTime('+2 days'))->format('Y-m-d');

        $modelo->__set('id_cita', 1);
        $modelo->__set('id_beneficiario', 5);
        $modelo->__set('id_empleado', 2);
        $modelo->__set('id_usuario', 2);
        $modelo->__set('fecha', $fechaValida);
        $modelo->__set('hora', '09:30');
        $modelo->__set('estatus', 1);
        $modelo->__set('tipo_empleado', 'Psicologo');

        $this->assertSame(1, $modelo->__get('id_cita'));
        $this->assertSame(5, $modelo->__get('id_beneficiario'));
        $this->assertSame(2, $modelo->__get('id_empleado'));
        $this->assertSame(2, $modelo->__get('id_usuario'));
        $this->assertSame($fechaValida, $modelo->__get('fecha'));
        $this->assertSame('09:30:00', $modelo->__get('hora'));
        $this->assertSame(1, $modelo->__get('estatus'));
        $this->assertSame('Psicologo', $modelo->__get('tipo_empleado'));
    }

    /**
     * CP-CIT-002: Rechazo de identificador de beneficiario inválido o no positivo
     */
    public function test1_Unitaria_RechazoIdBeneficiarioInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El id_beneficiario no es válido.');

        $modelo = new CitaModel();
        $modelo->__set('id_beneficiario', 0);
    }

    /**
     * CP-CIT-003: Rechazo de identificador de psicólogo/empleado inválido
     */
    public function test1_Unitaria_RechazoIdEmpleadoInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El id_empleado no es válido.');

        $modelo = new CitaModel();
        $modelo->__set('id_empleado', -1);
    }

    /**
     * CP-CIT-004: Rechazo de agendamiento en fechas pasadas
     */
    public function test1_Unitaria_RechazoFechaPasada(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La fecha de la cita no puede ser pasada.');

        $modelo = new CitaModel();
        $fechaPasada = (new DateTime('-1 day'))->format('Y-m-d');
        $modelo->__set('fecha', $fechaPasada);
    }

    /**
     * CP-CIT-005: Rechazo de formato de fecha inválido
     */
    public function test1_Unitaria_RechazoFormatoFechaInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La fecha de la cita no es válida.');

        $modelo = new CitaModel();
        $modelo->__set('fecha', '30/10/2026');
    }

    /**
     * CP-CIT-006: Rechazo de formato de hora inválido
     */
    public function test1_Unitaria_RechazoHoraInvalida(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La hora de la cita no es válida.');

        $modelo = new CitaModel();
        $modelo->__set('hora', '25:00');
    }

    /**
     * CP-CIT-007: Normalización automática de hora con segundos a :00
     */
    public function test1_Unitaria_NormalizacionHoraConSegundos(): void
    {
        $modelo = new CitaModel();

        // Formato HH:MM sin segundos
        $modelo->__set('hora', '08:30');
        $this->assertSame('08:30:00', $modelo->__get('hora'));

        // Formato HH:MM:SS con segundos
        $modelo->__set('hora', '14:00:00');
        $this->assertSame('14:00:00', $modelo->__get('hora'));
    }

    /**
     * CP-CIT-008: Rechazo de estado de cita fuera del catálogo permitido (1-5)
     */
    public function test1_Unitaria_RechazoEstatusInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El estado de la cita no es válido.');

        $modelo = new CitaModel();
        $modelo->__set('estatus', 99);
    }

    /**
     * CP-CIT-009: Rechazo de atributo no reconocido en el modelo CitaModel
     */
    public function test1_Unitaria_RechazoAtributoDesconocido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage("Atributo no reconocido: 'campo_inexistente'.");

        $modelo = new CitaModel();
        $modelo->__set('campo_inexistente', 'valor');
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Roles, Restricciones y Control RBAC)
    // =========================================================================

    /**
     * CP-CIT-010: Restricción de acceso exclusivo para Psicólogo y Administradores
     */
    public function test2_Integracion_RestriccionRolPsicologoYAdmin(): void
    {
        $metodo = new ReflectionMethod(CitaModel::class, 'asegurarPsicologoSesion');

        // 1. Empleado no psicólogo (ej: Médico) debe ser rechazado con HTTP 403
        $modeloInvalido = new CitaModel();
        $modeloInvalido->__set('tipo_empleado', 'Medico');

        try {
            $metodo->invoke($modeloInvalido);
            $this->fail('Debe rechazar roles que no sean Psicólogo o Administrador');
        } catch (ExcepcionApi $e) {
            $this->assertSame(403, $e->estado);
            $this->assertStringContainsString('Solo los empleados psicólogos pueden gestionar citas', $e->getMessage());
        }

        // 2. Rol Psicólogo debe tener acceso permitido
        $modeloPsicologo = new CitaModel();
        $modeloPsicologo->__set('tipo_empleado', 'Psicologo');
        $metodo->invoke($modeloPsicologo);
        $this->assertSame('Psicologo', $modeloPsicologo->__get('tipo_empleado'));

        // 3. Rol Administrador debe tener acceso permitido
        $modeloAdmin = new CitaModel();
        $modeloAdmin->__set('tipo_empleado', 'Administrador');
        $metodo->invoke($modeloAdmin);
        $this->assertSame('Administrador', $modeloAdmin->__get('tipo_empleado'));

        // 4. Rol Superusuario debe tener acceso permitido
        $modeloSuper = new CitaModel();
        $modeloSuper->__set('tipo_empleado', 'Superusuario');
        $metodo->invoke($modeloSuper);
        $this->assertSame('Superusuario', $modeloSuper->__get('tipo_empleado'));
    }

    /**
     * CP-CIT-011: Control de acceso RBAC para el módulo Citas
     */
    public function test2_Integracion_ControlAccesoRbacCitas(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene debe retornar false y verificar debe lanzar 401
        $this->assertFalse(Autorizacion::tiene('citas', 'crear'));

        try {
            Autorizacion::verificar('citas', 'crear');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }
    }

    /**
     * CP-CIT-012: Evaluación integral de estados válidos del ciclo de vida de la cita
     */
    public function test2_Integracion_EstadosValidosCita(): void
    {
        $modelo = new CitaModel();

        // 1 = Pendiente
        $modelo->__set('estatus', 1);
        $this->assertSame(1, $modelo->__get('estatus'));

        // 2 = Confirmada
        $modelo->__set('estatus', 2);
        $this->assertSame(2, $modelo->__get('estatus'));

        // 3 = Atendida
        $modelo->__set('estatus', 3);
        $this->assertSame(3, $modelo->__get('estatus'));

        // 4 = Cancelada
        $modelo->__set('estatus', 4);
        $this->assertSame(4, $modelo->__get('estatus'));

        // 5 = No asistió
        $modelo->__set('estatus', 5);
        $this->assertSame(5, $modelo->__get('estatus'));
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo Completo de Negocio)
    // =========================================================================

    /**
     * CP-CIT-013: Disponibilidad de Controladores y Vistas del Módulo Citas
     */
    public function test3_Sistema_DisponibilidadControladorYVistasCitas(): void
    {
        load_controller('citaController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showConsultarCitas'), 'showConsultarCitas debe existir');
        $this->assertTrue(function_exists('showCrearCita'), 'showCrearCita debe existir');
        $this->assertTrue(function_exists('apiListarCitas'), 'apiListarCitas debe existir');
        $this->assertTrue(function_exists('apiObtenerCita'), 'apiObtenerCita debe existir');
        $this->assertTrue(function_exists('apiPsicologosCita'), 'apiPsicologosCita debe existir');
        $this->assertTrue(function_exists('apiBeneficiariosCita'), 'apiBeneficiariosCita debe existir');
        $this->assertTrue(function_exists('apiEstadosCita'), 'apiEstadosCita debe existir');
        $this->assertTrue(function_exists('apiHorarioCita'), 'apiHorarioCita debe existir');
        $this->assertTrue(function_exists('apiDisponibilidadCita'), 'apiDisponibilidadCita debe existir');
        $this->assertTrue(function_exists('apiStatsCitas'), 'apiStatsCitas debe existir');
        $this->assertTrue(function_exists('apiCrearCita'), 'apiCrearCita debe existir');
        $this->assertTrue(function_exists('apiActualizarCita'), 'apiActualizarCita debe existir');
        $this->assertTrue(function_exists('apiActualizarEstadoCita'), 'apiActualizarEstadoCita debe existir');
        $this->assertTrue(function_exists('apiEliminarCita'), 'apiEliminarCita debe existir');
        $this->assertTrue(function_exists('contextoCita'), 'contextoCita debe existir');
        $this->assertTrue(function_exists('entradaCita'), 'entradaCita debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/citas/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/citas/crear.php', 'Vista crear.php debe existir');
    }

    /**
     * CP-CIT-014: Flujo integral del ciclo de agendamiento y auditoría de citas
     */
    public function test3_Sistema_FlujoFuncionalGestionCitas(): void
    {
        // 1. Simulación de sesión administrativa activa
        $_SESSION['id_empleado'] = 1;
        $_SESSION['nombre'] = 'Roberth';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Preparación de entidad completa de cita
        $modelo = new CitaModel();
        $fechaCita = (new DateTime('+3 days'))->format('Y-m-d');

        $modelo->__set('id_usuario', (int) $_SESSION['id_empleado']);
        $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado']);
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('id_empleado', 2); // Psicólogo asignado
        $modelo->__set('fecha', $fechaCita);
        $modelo->__set('hora', '10:00');
        $modelo->__set('estatus', 1);

        // 3. Simulación de registro de auditoría en Bitácora
        Bitacora::registrar(
            'Citas',
            'Registro',
            'Registró una cita para un beneficiario.'
        );

        // 4. Verificación de contrato de salida estandarizado
        $salidaEsperada = [
            'id_empleado' => $modelo->__get('id_empleado'),
            'estatus'     => $modelo->__get('estatus'),
            'fecha'       => $modelo->__get('fecha'),
            'hora'        => $modelo->__get('hora'),
        ];

        $this->assertSame(2, $salidaEsperada['id_empleado']);
        $this->assertSame(1, $salidaEsperada['estatus']);
        $this->assertSame($fechaCita, $salidaEsperada['fecha']);
        $this->assertSame('10:00:00', $salidaEsperada['hora']);
    }
}
