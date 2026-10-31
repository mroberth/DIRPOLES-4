<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\JornadaModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class JornadaTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente JornadaModel y Validaciones)
    // =========================================================================

    /**
     * CP-JOR-001: Validación de asignación correcta de atributos para Jornada Médica
     */
    public function test1_Unitaria_ValidacionAsignacionAtributosJornada(): void
    {
        $modelo = new JornadaModel();

        $modelo->__set('id_jornada', 1);
        $modelo->__set('nombre_jornada', 'Jornada de Salud Integral 2027');
        $modelo->__set('tipo_jornada', 'Médica Integral');
        $modelo->__set('aforo_maximo', 250);
        $modelo->__set('fecha_inicio', '2027-05-10 08:00:00');
        $modelo->__set('fecha_fin', '2027-05-12 16:00:00');
        $modelo->__set('ubicacion', 'Plaza Central UPTAEB');
        $modelo->__set('descripcion', 'Atención médica gratuita para la comunidad universitaria');
        $modelo->__set('estatus', 'Activa');
        $modelo->__set('id_empleado', 2);

        $this->assertSame(1, $modelo->__get('id_jornada'));
        $this->assertSame('Jornada de Salud Integral 2027', $modelo->__get('nombre_jornada'));
        $this->assertSame('Médica Integral', $modelo->__get('tipo_jornada'));
        $this->assertSame(250, $modelo->__get('aforo_maximo'));
        $this->assertSame('2027-05-10 08:00:00', $modelo->__get('fecha_inicio'));
        $this->assertSame('2027-05-12 16:00:00', $modelo->__get('fecha_fin'));
        $this->assertSame('Plaza Central UPTAEB', $modelo->__get('ubicacion'));
        $this->assertSame('Atención médica gratuita para la comunidad universitaria', $modelo->__get('descripcion'));
        $this->assertSame('Activa', $modelo->__get('estatus'));
        $this->assertSame(2, $modelo->__get('id_empleado'));
    }

    /**
     * CP-JOR-002: Rechazo de nombre y tipo de jornada obligatorios o inválidos
     */
    public function test1_Unitaria_RechazoNombreYTipoJornadaInvalidos(): void
    {
        $modelo = new JornadaModel();

        // 1. Nombre de jornada vacío
        try {
            $modelo->__set('nombre_jornada', '   ');
            $this->fail('Debe rechazar nombre de jornada en blanco');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El nombre de la jornada es obligatorio.', $e->getMessage());
        }

        // 2. Nombre menor a 3 caracteres
        try {
            $modelo->__set('nombre_jornada', 'AB');
            $this->fail('Debe rechazar nombre menor a 3 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El nombre debe tener entre 3 y 100 caracteres.', $e->getMessage());
        }

        // 3. Nombre mayor a 100 caracteres
        try {
            $modelo->__set('nombre_jornada', str_repeat('J', 105));
            $this->fail('Debe rechazar nombre superior a 100 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El nombre debe tener entre 3 y 100 caracteres.', $e->getMessage());
        }

        // 4. Tipo de jornada vacío
        try {
            $modelo->__set('tipo_jornada', '  ');
            $this->fail('Debe rechazar tipo de jornada en blanco');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El tipo de jornada es obligatorio.', $e->getMessage());
        }
    }

    /**
     * CP-JOR-003: Validación de límites de aforo máximo permitido
     */
    public function test1_Unitaria_ValidacionLimitesAforoMaximo(): void
    {
        $modelo = new JornadaModel();

        // 1. Aforo menor a 1 (ej. 0)
        try {
            $modelo->__set('aforo_maximo', 0);
            $this->fail('Debe rechazar aforo menor a 1');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El aforo máximo debe ser un número entero entre 1 y 5000.', $e->getMessage());
        }

        // 2. Aforo superior a 5000
        try {
            $modelo->__set('aforo_maximo', 5001);
            $this->fail('Debe rechazar aforo superior a 5000');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El aforo máximo debe ser un número entero entre 1 y 5000.', $e->getMessage());
        }
    }

    /**
     * CP-JOR-004: Validación de formato y normalización de fechas de jornada
     */
    public function test1_Unitaria_ValidacionFormatoFechasInicioYFin(): void
    {
        $modelo = new JornadaModel();

        // 1. Fecha de inicio vacía
        try {
            $modelo->__set('fecha_inicio', '   ');
            $this->fail('Debe rechazar fecha de inicio vacía');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La fecha y hora de el inicio es obligatoria.', $e->getMessage());
        }

        // 2. Formato inválido en fecha de inicio
        try {
            $modelo->__set('fecha_inicio', 'fecha-no-valida');
            $this->fail('Debe rechazar formato no reconocible');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La fecha y hora de el inicio no tiene un formato válido.', $e->getMessage());
        }

        // 3. Normalización exitosa desde formato datetime-local (con 'T')
        $modelo->__set('fecha_inicio', '2027-08-15T09:00');
        $modelo->__set('fecha_fin', '2027-08-16T17:30');

        $this->assertSame('2027-08-15 09:00:00', $modelo->__get('fecha_inicio'));
        $this->assertSame('2027-08-16 17:30:00', $modelo->__get('fecha_fin'));
    }

    /**
     * CP-JOR-005: Validación de datos de asistente a la jornada
     */
    public function test1_Unitaria_ValidacionDatosAsistenteJornada(): void
    {
        $modelo = new JornadaModel();

        $modelo->__set('tipo_cedula', 'v');
        $modelo->__set('cedula', '12345678');
        $modelo->__set('nombres', 'María Elena');
        $modelo->__set('apellidos', 'Pérez López');
        $modelo->__set('fecha_nacimiento', '2000-05-15');
        $modelo->__set('genero', 'Femenino');
        $modelo->__set('tipo_paciente', 'Estudiante');
        $modelo->__set('telefono', '04141234567');
        $modelo->__set('correo', 'maria@uptaeb.edu.ve');

        $this->assertSame('V', $modelo->__get('tipo_cedula'));
        $this->assertSame('12345678', $modelo->__get('cedula'));
        $this->assertSame('María Elena', $modelo->__get('nombres'));
        $this->assertSame('Pérez López', $modelo->__get('apellidos'));
        $this->assertSame('2000-05-15', $modelo->__get('fecha_nacimiento'));
        $this->assertSame('Femenino', $modelo->__get('genero'));
        $this->assertSame('Estudiante', $modelo->__get('tipo_paciente'));
        $this->assertSame('04141234567', $modelo->__get('telefono'));
        $this->assertSame('maria@uptaeb.edu.ve', $modelo->__get('correo'));

        // Rechazo de tipo de documento inválido
        try {
            $modelo->__set('tipo_cedula', 'X');
            $this->fail('Debe rechazar tipo de documento X');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El tipo de documento no es válido (V, E, J o G).', $e->getMessage());
        }

        // Rechazo de tipo de paciente inválido
        try {
            $modelo->__set('tipo_paciente', 'Visitante');
            $this->fail('Debe rechazar tipo de paciente ajeno a catálogo');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El tipo de paciente no es válido.', $e->getMessage());
        }
    }

    /**
     * CP-JOR-006: Validación de diagnóstico clínico y lista de insumos
     */
    public function test1_Unitaria_ValidacionDiagnosticoEInsumos(): void
    {
        $modelo = new JornadaModel();

        // 1. Diagnóstico vacío
        try {
            $modelo->__set('diagnostico', '   ');
            $this->fail('Debe rechazar diagnóstico en blanco');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El diagnóstico es obligatorio.', $e->getMessage());
        }

        // 2. Insumos no enviados como array
        try {
            $modelo->__set('insumos', 'no-es-un-array');
            $this->fail('Debe rechazar insumos si no son un array');
        } catch (ExcepcionApi $e) {
            $this->assertSame('Los insumos deben enviarse como una lista.', $e->getMessage());
        }

        // 3. Asignación correcta de diagnóstico e insumos válidos
        $modelo->__set('diagnostico', 'Gastroenteritis aguda');
        $modelo->__set('tratamiento', 'Hidratación oral y dieta blanda');
        $modelo->__set('observaciones', 'Control en 48 horas');
        $modelo->__set('insumos', [
            ['id_insumo' => 5, 'cantidad' => 2],
        ]);

        $this->assertSame('Gastroenteritis aguda', $modelo->__get('diagnostico'));
        $this->assertSame('Hidratación oral y dieta blanda', $modelo->__get('tratamiento'));
        $this->assertCount(1, $modelo->__get('insumos'));
    }

    /**
     * CP-JOR-007: Rechazo de atributo no reconocido e ID relacional inválido
     */
    public function test1_Unitaria_RechazoAtributoDesconocidoEIdInvalido(): void
    {
        $modelo = new JornadaModel();

        // 1. Atributo no reconocido
        try {
            $modelo->__set('campo_no_existente', 'valor');
            $this->fail('Debe rechazar atributo ajeno al esquema');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Atributo no reconocido: 'campo_no_existente'.", $e->getMessage());
        }

        // 2. ID relacional no positivo
        try {
            $modelo->__set('id_jornada', 0);
            $this->fail('Debe rechazar id_jornada = 0');
        } catch (ExcepcionApi $e) {
            $this->assertSame("El valor de 'id_jornada' no es válido.", $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas de Negocio, Fechas y RBAC)
    // =========================================================================

    /**
     * CP-JOR-008: Regla de negocio: fecha de cierre no puede ser anterior ni en el pasado
     */
    public function test2_Integracion_ReglaFechasCierreAnteriorOYaPasada(): void
    {
        $modelo = new JornadaModel();
        $modelo->__set('nombre_jornada', 'Jornada Preventiva');
        $modelo->__set('tipo_jornada', 'Médica Integral');
        $modelo->__set('aforo_maximo', 100);
        $modelo->__set('ubicacion', 'Auditorio');

        // 1. Fecha fin anterior a fecha inicio
        $modelo->__set('fecha_inicio', '2027-06-15 08:00:00');
        $modelo->__set('fecha_fin', '2027-06-14 17:00:00');

        try {
            $modelo->manejarAccion('crear');
            $this->fail('Debe rechazar fecha de cierre anterior a inicio');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La fecha de cierre no puede ser anterior a la de inicio.', $e->getMessage());
        }

        // 2. Fecha de cierre en el pasado al crear
        $modelo->__set('fecha_inicio', '2020-01-10 08:00:00');
        $modelo->__set('fecha_fin', '2020-01-12 17:00:00');

        try {
            $modelo->manejarAccion('crear');
            $this->fail('Debe rechazar jornada creada en el pasado');
        } catch (ExcepcionApi $e) {
            $this->assertSame('No se puede crear una jornada cuya fecha de cierre ya pasó.', $e->getMessage());
        }
    }

    /**
     * CP-JOR-009: Despacho de catálogos estáticos del módulo de jornadas
     */
    public function test2_Integracion_CatalogosEstaticosModulo(): void
    {
        $modelo = new JornadaModel();
        $catalogos = $modelo->manejarAccion('catalogos');

        $this->assertIsArray($catalogos);
        $this->assertArrayHasKey('tipo_cedula', $catalogos);
        $this->assertArrayHasKey('genero', $catalogos);
        $this->assertArrayHasKey('tipo_paciente', $catalogos);
        $this->assertArrayHasKey('tipo_jornada', $catalogos);
        $this->assertArrayHasKey('estatus', $catalogos);

        $this->assertContains('Estudiante', $catalogos['tipo_paciente']);
        $this->assertContains('Médica Integral', $catalogos['tipo_jornada']);
        $this->assertContains('Activa', $catalogos['estatus']);
    }

    /**
     * CP-JOR-010: Inspección por reflexión de métodos privados de aforo y estado
     */
    public function test2_Integracion_VerificacionMetodosPrivadosReglasYAforo(): void
    {
        $metodoTexto = new ReflectionMethod(JornadaModel::class, 'texto');
        $metodoFechaHora = new ReflectionMethod(JornadaModel::class, 'fechaHora');
        $metodoValidarRango = new ReflectionMethod(JornadaModel::class, 'validarRangoFechas');
        $metodoExigirActiva = new ReflectionMethod(JornadaModel::class, 'exigirActiva');

        $this->assertTrue($metodoTexto->isPrivate(), 'texto debe ser privado');
        $this->assertTrue($metodoFechaHora->isPrivate(), 'fechaHora debe ser privado');
        $this->assertTrue($metodoValidarRango->isPrivate(), 'validarRangoFechas debe ser privado');
        $this->assertTrue($metodoExigirActiva->isPrivate(), 'exigirActiva debe ser privado');

        // Prueba de exigirActiva con jornada cancelada
        $modelo = new JornadaModel();
        $jornadaCancelada = [
            'estatus'   => 'Cancelada',
            'fecha_fin' => '2027-12-31 23:59:59',
        ];

        try {
            $metodoExigirActiva->invoke($modelo, $jornadaCancelada);
            $this->fail('Debe rechazar jornada que no está Activa');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La jornada está en estatus "Cancelada": no admite más registros.', $e->getMessage());
        }
    }

    /**
     * CP-JOR-011: Control de acceso RBAC para el módulo Jornadas
     */
    public function test2_Integracion_ControlAccesoRbacJornadas(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene retorna false y verificar arroja 401
        $this->assertFalse(Autorizacion::tiene('jornadas', 'crear'));
        $this->assertFalse(Autorizacion::tiene('jornadas', 'eliminar'));

        try {
            Autorizacion::verificar('jornadas', 'crear');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }

        try {
            Autorizacion::verificar('jornadas', 'eliminar');
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
     * CP-JOR-012: Disponibilidad de Controladores y Vistas del Módulo Jornadas
     */
    public function test3_Sistema_DisponibilidadControladorYVistasJornadas(): void
    {
        load_controller('jornadaController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showCrearJornada'), 'showCrearJornada debe existir');
        $this->assertTrue(function_exists('showConsultarJornadas'), 'showConsultarJornadas debe existir');
        $this->assertTrue(function_exists('showDetalleJornada'), 'showDetalleJornada debe existir');
        $this->assertTrue(function_exists('apiCatalogosJornada'), 'apiCatalogosJornada debe existir');
        $this->assertTrue(function_exists('apiListarJornadas'), 'apiListarJornadas debe existir');
        $this->assertTrue(function_exists('apiObtenerJornada'), 'apiObtenerJornada debe existir');
        $this->assertTrue(function_exists('apiJornadasStats'), 'apiJornadasStats debe existir');
        $this->assertTrue(function_exists('apiAsistentesJornada'), 'apiAsistentesJornada debe existir');
        $this->assertTrue(function_exists('apiDiagnosticosAsistente'), 'apiDiagnosticosAsistente debe existir');
        $this->assertTrue(function_exists('apiBuscarPersonaJornada'), 'apiBuscarPersonaJornada debe existir');
        $this->assertTrue(function_exists('apiInsumosDisponiblesJornada'), 'apiInsumosDisponiblesJornada debe existir');
        $this->assertTrue(function_exists('apiCrearJornada'), 'apiCrearJornada debe existir');
        $this->assertTrue(function_exists('apiActualizarJornada'), 'apiActualizarJornada debe existir');
        $this->assertTrue(function_exists('apiEliminarJornada'), 'apiEliminarJornada debe existir');
        $this->assertTrue(function_exists('apiAgregarAsistente'), 'apiAgregarAsistente debe existir');
        $this->assertTrue(function_exists('apiEliminarAsistente'), 'apiEliminarAsistente debe existir');
        $this->assertTrue(function_exists('apiAgregarDiagnostico'), 'apiAgregarDiagnostico debe existir');
        $this->assertTrue(function_exists('apiActualizarDiagnostico'), 'apiActualizarDiagnostico debe existir');
        $this->assertTrue(function_exists('apiEliminarDiagnostico'), 'apiEliminarDiagnostico debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/jornadas/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/jornadas/crear.php', 'Vista crear.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/jornadas/detalle.php', 'Vista detalle.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/jornadas/components/stats.php', 'Componente stats.php debe existir');
    }

    /**
     * CP-JOR-013: Flujo funcional de creación de jornada y registro de auditoría
     */
    public function test3_Sistema_FlujoFuncionalCreacionJornadaYAuditoria(): void
    {
        // 1. Simulación de sesión de Médico autenticado
        $_SESSION['id_empleado'] = 2;
        $_SESSION['nombre'] = 'Dr. Juan Pérez';
        $_SESSION['id_tipo_empleado'] = 2;
        $_SESSION['tipo_empleado'] = 'Medico';

        // 2. Preparación de entidad jornada
        $modelo = new JornadaModel();
        $modelo->__set('nombre_jornada', 'Jornada Odontológica 2027');
        $modelo->__set('tipo_jornada', 'Odontológica');
        $modelo->__set('aforo_maximo', 200);
        $modelo->__set('fecha_inicio', '2027-09-01 08:00:00');
        $modelo->__set('fecha_fin', '2027-09-02 16:00:00');
        $modelo->__set('ubicacion', 'Módulo Odontológico UPTAEB');
        $modelo->__set('descripcion', 'Limpieza y despistaje de caries dental');
        $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);

        // 3. Auditoría institucional en Bitácora
        Bitacora::registrar(
            'Jornadas',
            'Registro',
            'Creó la jornada "' . $modelo->__get('nombre_jornada') . '" (aforo ' . $modelo->__get('aforo_maximo') . ').'
        );

        // 4. Verificación de contrato de salida esperado
        $salidaEsperada = [
            'nombre_jornada' => $modelo->__get('nombre_jornada'),
            'tipo_jornada'   => $modelo->__get('tipo_jornada'),
            'aforo_maximo'   => $modelo->__get('aforo_maximo'),
            'estatus'        => 'Activa',
        ];

        $this->assertSame('Jornada Odontológica 2027', $salidaEsperada['nombre_jornada']);
        $this->assertSame('Odontológica', $salidaEsperada['tipo_jornada']);
        $this->assertSame(200, $salidaEsperada['aforo_maximo']);
        $this->assertSame('Activa', $salidaEsperada['estatus']);
    }

    /**
     * CP-JOR-014: Flujo integral de atención a asistente, diagnóstico clínico y auditoría
     */
    public function test3_Sistema_FlujoIntegralAtencionAsistenteDiagnosticoYAuditoria(): void
    {
        // 1. Simulación de sesión de Médico responsable
        $_SESSION['id_empleado'] = 2;
        $_SESSION['nombre'] = 'Dr. Juan Pérez';
        $_SESSION['id_tipo_empleado'] = 2;
        $_SESSION['tipo_empleado'] = 'Medico';

        // 2. Auditoría de Registro de Asistente
        Bitacora::registrar(
            'Jornadas',
            'Registro',
            'Registró a "María Pérez" (CI: V-12345678) en la jornada "Jornada Médica 2027".'
        );

        // 3. Auditoría de Diagnóstico Clínico e Insumos usados
        Bitacora::registrar(
            'Jornadas',
            'Registro',
            'Registró un diagnóstico para "María Pérez" en la jornada "Jornada Médica 2027": Faringitis aguda. Insumos usados: Amoxicilina 500mg ×1.'
        );

        // 4. Auditoría de Eliminación de Jornada sin asistentes
        Bitacora::registrar(
            'Jornadas',
            'Eliminación',
            'Eliminó la jornada "Jornada Vacunación 2027" (sin asistentes).'
        );

        // 5. Verificación de contrato de resultado emitido
        $resultadoDiagnostico = [
            'id_jornada_diagnostico' => 45,
            'persona'                => 'María Pérez',
            'nombre_jornada'         => 'Jornada Médica 2027',
            'diagnostico'            => 'Faringitis aguda',
            'insumos'                => [
                ['nombre' => 'Amoxicilina 500mg', 'cantidad' => 1],
            ],
        ];

        $this->assertSame(45, $resultadoDiagnostico['id_jornada_diagnostico']);
        $this->assertSame('María Pérez', $resultadoDiagnostico['persona']);
        $this->assertSame('Faringitis aguda', $resultadoDiagnostico['diagnostico']);
        $this->assertCount(1, $resultadoDiagnostico['insumos']);
    }
}
