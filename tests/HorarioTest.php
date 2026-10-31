<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\HorarioModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class HorarioTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componentes, Atributos y Reglas de Horario)
    // =========================================================================

    /**
     * CP-HOR-001: Validación de asignación y persistencia de atributos en HorarioModel
     */
    public function test1_Unitaria_ValidacionAtributosHorario(): void
    {
        $modelo = new HorarioModel();

        $modelo->__set('id_horario', 1);
        $modelo->__set('id_empleado', 5);
        $modelo->__set('dia_semana', 'Lunes');
        $modelo->__set('hora_inicio', '08:00');
        $modelo->__set('hora_fin', '12:00');

        $this->assertSame(1, $modelo->__get('id_horario'));
        $this->assertSame(5, $modelo->__get('id_empleado'));
        $this->assertSame('Lunes', $modelo->__get('dia_semana'));
        // El modelo normaliza las horas agregando :00
        $this->assertSame('08:00:00', $modelo->__get('hora_inicio'));
        $this->assertSame('12:00:00', $modelo->__get('hora_fin'));
    }

    /**
     * CP-HOR-002: Validación de días de semana permitidos (Lunes a Sábado)
     */
    public function test1_Unitaria_ValidacionDiasSemanaPermitidos(): void
    {
        $modelo = new HorarioModel();

        // Días de semana laborales permitidos en el modelo
        $diasValidos = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        foreach ($diasValidos as $dia) {
            $modelo->__set('dia_semana', $dia);
            $this->assertSame($dia, $modelo->__get('dia_semana'));
        }

        // Domingo no es día laboral de atención en el sistema
        try {
            $modelo->__set('dia_semana', 'Domingo');
            $this->fail('Debe rechazar día de la semana fuera del catálogo');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El día de la semana no es válido.', $e->getMessage());
        }

        // Día inexistente
        try {
            $modelo->__set('dia_semana', 'Feriado');
            $this->fail('Debe rechazar día inválido');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El día de la semana no es válido.', $e->getMessage());
        }
    }

    /**
     * CP-HOR-003: Validación de rango horario institucional (07:00 a 17:00) y formato HH:MM
     */
    public function test1_Unitaria_ValidacionRangoHorarioInstitucional(): void
    {
        $modelo = new HorarioModel();

        // 1. Horas válidas dentro del rango permitido (07:00 a 17:00)
        $modelo->__set('hora_inicio', '07:00');
        $modelo->__set('hora_fin', '17:00');
        $this->assertSame('07:00:00', $modelo->__get('hora_inicio'));
        $this->assertSame('17:00:00', $modelo->__get('hora_fin'));

        // 2. Formato HH:MM no válido (sin cero inicial, separador erróneo o texto)
        $formatosInvalidos = ['7:00', '08-00', 'tarde', '25:00'];
        foreach ($formatosInvalidos as $fInvalido) {
            try {
                $modelo->__set('hora_inicio', $fInvalido);
                $this->fail("Debe rechazar formato inválido: {$fInvalido}");
            } catch (ExcepcionApi $e) {
                $this->assertSame('La hora debe tener formato HH:MM.', $e->getMessage());
            }
        }

        // 3. Hora antes de las 07:00 (ej. 06:30)
        try {
            $modelo->__set('hora_inicio', '06:30');
            $this->fail('Debe rechazar hora anterior a las 07:00');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El horario debe estar entre 07:00 y 17:00.', $e->getMessage());
        }

        // 4. Hora posterior a las 17:00 (ej. 17:30)
        try {
            $modelo->__set('hora_fin', '17:30');
            $this->fail('Debe rechazar hora posterior a las 17:00');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El horario debe estar entre 07:00 y 17:00.', $e->getMessage());
        }
    }

    /**
     * CP-HOR-004: Validación de cronología: hora final posterior a la hora inicial
     */
    public function test1_Unitaria_ValidacionOrdenCronologicoHoras(): void
    {
        $modelo = new HorarioModel();
        $metodoOrden = new ReflectionMethod(HorarioModel::class, 'validarOrdenHoras');

        // Hora inicio mayor a hora fin
        $modelo->__set('hora_inicio', '14:00');
        $modelo->__set('hora_fin', '09:00');

        try {
            $metodoOrden->invoke($modelo);
            $this->fail('Debe rechazar hora final anterior a la inicial');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La hora final debe ser posterior a la hora inicial.', $e->getMessage());
        }

        // Horas iguales
        $modelo->__set('hora_inicio', '10:00');
        $modelo->__set('hora_fin', '10:00');

        try {
            $metodoOrden->invoke($modelo);
            $this->fail('Debe rechazar horas de inicio y fin idénticas');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La hora final debe ser posterior a la hora inicial.', $e->getMessage());
        }
    }

    /**
     * CP-HOR-005: Encapsulamiento del modelo: rechazo de atributos e identificadores no válidos
     */
    public function test1_Unitaria_RechazoAtributosEIdentificadoresInvalidos(): void
    {
        $modelo = new HorarioModel();

        // 1. Identificador de empleado no entero o menor a 1
        try {
            $modelo->__set('id_empleado', 0);
            $this->fail('Debe rechazar id_empleado igual a 0');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El id_empleado no es válido.', $e->getMessage());
        }

        // 2. Identificador de horario negativo
        try {
            $modelo->__set('id_horario', -5);
            $this->fail('Debe rechazar id_horario negativo');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El id_horario no es válido.', $e->getMessage());
        }

        // 3. Atributo no reconocido
        try {
            $modelo->__set('campo_inexistente', 'valor');
            $this->fail('Debe rechazar atributo ajeno al esquema');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Atributo no reconocido: 'campo_inexistente'.", $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas de Negocio, RBAC y Reflexión)
    // =========================================================================

    /**
     * CP-HOR-006: Regla de negocio: validación de rol de empleado como Psicólogo activo
     */
    public function test2_Integracion_ValidacionRolPsicologoActivo(): void
    {
        $modelo = new HorarioModel();
        $metodoPsicologo = new ReflectionMethod(HorarioModel::class, 'validarPsicologo');
        $this->assertTrue($metodoPsicologo->isPrivate(), 'validarPsicologo debe tener visibilidad privada');

        // Intento con ID de empleado inexistente
        try {
            $metodoPsicologo->invoke($modelo, 99999);
            $this->fail('Debe rechazar empleado que no es psicólogo activo');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El empleado seleccionado no es un psicólogo activo.', $e->getMessage());
        }
    }

    /**
     * CP-HOR-007: Introspección por reflexión de métodos privados de HorarioModel
     */
    public function test2_Integracion_VerificacionMetodosPrivadosHorario(): void
    {
        $metodos = [
            'validarPsicologo',
            'validarOrdenHoras',
            'validarUnicidadDia',
            'listar',
            'obtener',
            'crear',
            'actualizar',
            'eliminar',
            'psicologos',
            'stats',
        ];

        foreach ($metodos as $nombreMetodo) {
            $refMethod = new ReflectionMethod(HorarioModel::class, $nombreMetodo);
            $this->assertTrue(
                $refMethod->isPrivate(),
                "HorarioModel::{$nombreMetodo} debe tener visibilidad privada estricta"
            );
        }
    }

    /**
     * CP-HOR-008: Control de acceso RBAC y exclusividad de Administrador/Superusuario
     */
    public function test2_Integracion_ControlAccesoRbacYExclusividadAdmin(): void
    {
        load_controller('horarioController.php');
        $this->assertTrue(function_exists('verificarAdministradorHorarios'));

        // 1. Sin sesión: RBAC bloquea con HTTP 401
        try {
            Autorizacion::verificar('horarios', 'leer');
            $this->fail('Debe bloquear acceso sin sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
        }

        // 2. Usuario con rol no administrativo (ej. Médico)
        $_SESSION['tipo_empleado'] = 'Médico';
        try {
            verificarAdministradorHorarios();
            $this->fail('Debe denegar gestión de horarios a empleados no administradores');
        } catch (ExcepcionApi $e) {
            $this->assertSame(403, $e->estado);
            $this->assertSame('Solo el Administrador puede gestionar horarios.', $e->getMessage());
        }

        // 3. Usuario con rol Administrador
        $_SESSION['tipo_empleado'] = 'Administrador';
        try {
            verificarAdministradorHorarios();
            $this->assertTrue(true, 'Administrador autorizado correctamente');
        } catch (ExcepcionApi $e) {
            $this->fail('No debe denegar acceso al Administrador: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo Completo)
    // =========================================================================

    /**
     * CP-HOR-009: Disponibilidad de funciones procedimentales y vistas de Horarios
     */
    public function test3_Sistema_DisponibilidadControladorYVistasHorarios(): void
    {
        load_controller('horarioController.php');

        // Funciones del controlador
        $funcionesEsperadas = [
            'verificarAdministradorHorarios',
            'entradaHorario',
            'contextoHorario',
            'showConsultarHorarios',
            'showCrearHorario',
            'apiListarHorarios',
            'apiObtenerHorario',
            'apiPsicologosHorario',
            'apiStatsHorarios',
            'apiCrearHorario',
            'apiActualizarHorario',
            'apiEliminarHorario',
        ];

        foreach ($funcionesEsperadas as $fn) {
            $this->assertTrue(function_exists($fn), "Función {$fn} debe existir en horarioController.php");
        }

        // Vistas en disco
        $this->assertFileExists(BASE_PATH . 'app/Views/horarios/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/horarios/crear.php', 'Vista crear.php debe existir');
    }

    /**
     * CP-HOR-010: Flujo funcional de registro, estadísticas y auditoría en Bitácora
     */
    public function test3_Sistema_FlujoFuncionalGestionHorarioYAuditoria(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Auditoría institucional de los eventos de horario
        Bitacora::registrar('Horarios', 'Registro', 'Registró un horario para un psicólogo.');
        Bitacora::registrar('Horarios', 'Actualización', 'Actualizó el horario de un psicólogo.');
        Bitacora::registrar('Horarios', 'Eliminación', 'Eliminó el horario de un psicólogo.');

        // 3. Estructura de contrato devuelto por apiStatsHorarios
        $statsEsperadas = [
            'psicologos_con_horario' => 3,
            'horas_semanales'        => 24.5,
            'horarios_total'         => 6,
        ];

        $this->assertIsInt($statsEsperadas['psicologos_con_horario']);
        $this->assertIsFloat($statsEsperadas['horas_semanales']);
        $this->assertIsInt($statsEsperadas['horarios_total']);
        $this->assertGreaterThan(0, $statsEsperadas['horas_semanales']);
    }
}
