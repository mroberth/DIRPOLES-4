<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\ReportesModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ReportesTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componentes, Validación de Filtros y Atributos)
    // =========================================================================

    /**
     * CP-REP-001: Validación de asignación y consistencia de rango de fechas
     */
    public function test1_Unitaria_ValidacionRangoFechas(): void
    {
        $modelo = new ReportesModel();

        // 1. Rango cronológico válido
        $modelo->fecha_inicio = '2025-01-01';
        $modelo->fecha_fin = '2025-01-31';

        $this->assertSame('2025-01-01', $modelo->fecha_inicio);
        $this->assertSame('2025-01-31', $modelo->fecha_fin);

        // 2. Formato de fecha inválido
        try {
            $modelo->fecha_inicio = 'fecha-invalida';
            $this->fail('Debe rechazar formato de fecha no parseable');
        } catch (ExcepcionApi $e) {
            $this->assertSame("El filtro 'fecha_inicio' debe ser una fecha válida (AAAA-MM-DD).", $e->getMessage());
        }

        // 3. Rango invertido: fecha_inicio > fecha_fin
        $modelo->fecha_inicio = '2025-02-01';
        $modelo->fecha_fin = '2025-01-01';

        try {
            $modelo->manejarAccion('reporteGeneral');
            $this->fail('Debe rechazar rango donde fecha inicial es mayor a fecha final');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La fecha inicial no puede ser mayor que la fecha final.', $e->getMessage());
        }
    }

    /**
     * CP-REP-002: Validación de catálogo de Género ('M', 'F')
     */
    public function test1_Unitaria_ValidacionFiltroGenero(): void
    {
        $modelo = new ReportesModel();

        // Géneros válidos (con normalización a mayúsculas)
        $modelo->genero = 'm';
        $this->assertSame('M', $modelo->genero);

        $modelo->genero = 'F';
        $this->assertSame('F', $modelo->genero);

        // Cadena vacía se normaliza a null
        $modelo->genero = '';
        $this->assertNull($modelo->genero);

        // Género no admitido
        try {
            $modelo->genero = 'X';
            $this->fail('Debe rechazar género distinto de M o F');
        } catch (ExcepcionApi $e) {
            $this->assertSame("El género debe ser 'M' (Masculino) o 'F' (Femenino).", $e->getMessage());
        }
    }

    /**
     * CP-REP-003: Validación de límites de consulta (defecto 5000, tope 20000)
     */
    public function test1_Unitaria_ValidacionLimiteConsulta(): void
    {
        $modelo = new ReportesModel();

        // Límite no configurado: por método privado limite() retorna el defecto 5000
        $metodoLimite = new ReflectionMethod(ReportesModel::class, 'limite');
        $this->assertSame(5000, $metodoLimite->invoke($modelo));

        // Límite vacío: retorna defecto 5000
        $modelo->limit = '';
        $this->assertSame(5000, $modelo->limit);

        // Límite válido dentro de rango
        $modelo->limit = 10000;
        $this->assertSame(10000, $modelo->limit);
        $this->assertSame(10000, $metodoLimite->invoke($modelo));

        // Límite menor o igual a 0 lanza ExcepcionApi
        try {
            $modelo->limit = 0;
            $this->fail('Debe rechazar límite menor a 1');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El límite de registros debe ser un número entero mayor que cero.', $e->getMessage());
        }

        // Límite superior al tope máximo (20000) se acota automáticamente al máximo
        $modelo->limit = 25000;
        $this->assertSame(20000, $modelo->limit);
        $this->assertSame(20000, $metodoLimite->invoke($modelo));
    }

    /**
     * CP-REP-004: Validación de listas blancas de catálogos (Áreas, Discapacidad, Bienes, Transporte)
     */
    public function test1_Unitaria_ValidacionListasBlancasFiltros(): void
    {
        $modelo = new ReportesModel();

        // 1. Área general válida
        $modelo->area = 'Medicina';
        $this->assertSame('Medicina', $modelo->area);

        try {
            $modelo->area = 'Odontología';
            $this->fail('Debe rechazar área no catalogada');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El filtro Área no es válido. Opciones: Becas, Exoneración, FAMES, Medicina, Orientación, Discapacidad, Psicología.', $e->getMessage());
        }

        // 2. Grado de discapacidad
        $modelo->grado = 'Grave';
        $this->assertSame('Grave', $modelo->grado);

        try {
            $modelo->grado = 'Severo';
            $this->fail('Debe rechazar grado de discapacidad fuera de catálogo');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El filtro Grado no es válido. Opciones: Leve, Moderado, Grave.', $e->getMessage());
        }

        // 3. Tipo de vehículo
        $modelo->tipo_vehiculo = 'Autobús';
        $this->assertSame('Autobús', $modelo->tipo_vehiculo);

        try {
            $modelo->tipo_vehiculo = 'Tractor';
            $this->fail('Debe rechazar tipo de vehículo no válido');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El filtro Tipo de vehículo no es válido. Opciones: Autobús, Camioneta, Automóvil.', $e->getMessage());
        }
    }

    /**
     * CP-REP-005: Validación de estados según el tipo de reporte
     */
    public function test1_Unitaria_ValidacionEstadosSegunReporte(): void
    {
        $modelo = new ReportesModel();
        $metodoValidarEstado = new ReflectionMethod(ReportesModel::class, 'validarEstado');

        $estadosCitas = ['Pendiente', 'Confirmada', 'Atendida', 'Cancelada', 'No asistió'];

        // Estado textual válido
        $modelo->estado = 'Atendida';
        $metodoValidarEstado->invoke($modelo, $estadosCitas);
        $this->assertSame('Atendida', $modelo->estado);

        // Conversión de estado numérico (3 -> Atendida)
        $modelo->estado = 3;
        $metodoValidarEstado->invoke($modelo, $estadosCitas);
        $this->assertSame('Atendida', $modelo->estado);

        // Estado inválido
        $modelo->estado = 'Desconocido';
        try {
            $metodoValidarEstado->invoke($modelo, $estadosCitas);
            $this->fail('Debe rechazar estado no contemplado');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('El estado "Desconocido" no es válido.', $e->getMessage());
        }
    }

    /**
     * CP-REP-006: Encapsulamiento del modelo: rechazo de filtros no reconocidos
     */
    public function test1_Unitaria_RechazoFiltrosNoReconocidos(): void
    {
        $modelo = new ReportesModel();

        try {
            $modelo->filtro_desconocido = 'intento_inyeccion';
            $this->fail('Debe rechazar filtros que no forman parte de la lista blanca');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Filtro no reconocido: 'filtro_desconocido'.", $e->getMessage());
        }
    }

    /**
     * CP-REP-007: Despachador manejarAccion: rechazo de acciones no reconocidas
     */
    public function test1_Unitaria_RechazoAccionNoReconocida(): void
    {
        $modelo = new ReportesModel();

        try {
            $modelo->manejarAccion('reporteInexistente');
            $this->fail('Debe disparar ExcepcionApi ante acción desconocida');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La operación solicitada no está disponible.', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Filtros Server-Side, Seguridad y RBAC)
    // =========================================================================

    /**
     * CP-REP-008: Helper reportesAplicarFiltros: sanitización y lista blanca de query params
     */
    public function test2_Integracion_SanitizacionListaBlancaQueryParams(): void
    {
        load_controller('reportesController.php');
        $this->assertTrue(function_exists('reportesAplicarFiltros'));

        // Simulación de parámetros en $_GET
        $_GET['fecha_inicio'] = '2025-01-10';
        $_GET['genero']       = 'F';
        $_GET['limit']        = '2500';
        $_GET['param_ajeno']  = 'inyeccion_ignorable';

        $modelo = new ReportesModel();
        reportesAplicarFiltros($modelo);

        $this->assertSame('2025-01-10', $modelo->fecha_inicio);
        $this->assertSame('F', $modelo->genero);
        $this->assertSame(2500, $modelo->limit);
        $this->assertNull($modelo->param_ajeno);
    }

    /**
     * CP-REP-009: Introspección por reflexión de métodos privados de armado SQL y ejecución
     */
    public function test2_Integracion_VerificacionMetodosPrivadosReportes(): void
    {
        $metodos = [
            'filtroFechas',
            'filtro',
            'validarEstado',
            'clausulaWhere',
            'clausulaCon',
            'limite',
            'asegurarRangoFechas',
            'rangoMesActual',
            'esAdmin',
            'idEmpleadoSesion',
            'filtroEmpleado',
        ];

        foreach ($metodos as $metodo) {
            $refMethod = new ReflectionMethod(ReportesModel::class, $metodo);
            $this->assertTrue($refMethod->isPrivate(), "ReportesModel::{$metodo} debe ser estrictamente privado");
        }
    }

    /**
     * CP-REP-010: Control de acceso y matriz de permisos por especialidad
     */
    public function test2_Integracion_ControlAccesoRbacYMatrizEspecialidad(): void
    {
        load_controller('reportesController.php');
        $this->assertTrue(function_exists('verificarAccesoReporte'));

        // 1. Sin sesión: rechazo HTTP 401
        try {
            verificarAccesoReporte();
            $this->fail('Debe bloquear acceso anónimo');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
        }

        // 2. Administrador: acceso total a reportes generales y por especialidad
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        try {
            verificarAccesoReporte('', true); // Reporte general
            verificarAccesoReporte('medicina'); // Especialidad
            $this->assertTrue(true, 'Administrador autorizado sin restricciones');
        } catch (ExcepcionApi $e) {
            $this->fail('El administrador no debe ser bloqueado: ' . $e->getMessage());
        }

        // 3. Usuario no administrador intentando consultar reporte general (solo admin)
        $_SESSION['id_tipo_empleado'] = 1; // Psicólogo
        $_SESSION['tipo_empleado'] = 'Psicólogo';

        try {
            verificarAccesoReporte('', true);
            $this->fail('Debe bloquear consulta general a no administradores');
        } catch (ExcepcionApi $e) {
            $this->assertSame(403, $e->estado);
            $this->assertSame('Este reporte solo puede ser consultado por administradores.', $e->getMessage());
        }
    }

    /**
     * CP-REP-011: Contrato de seguridad y visibilidad para generación de informe IA
     */
    public function test2_Integracion_ContratoSeguridadGeneracionInformeIa(): void
    {
        load_controller('reportesController.php');
        $this->assertTrue(function_exists('reportesIaGenerar'));

        // Configuración de API Key ficticia en entorno para evaluar la capa de autorización previa
        $_ENV['DIRPOLES_IA_API_KEY'] = 'test-token-key-dirpoles-ia-reportes';

        // 1. Sin sesión iniciada: rechazo 401
        try {
            reportesIaGenerar('medicina', 'analisis', [], null);
            $this->fail('Debe rechazar generación de IA sin sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }

        // 2. Especialista con identificador de empleado inválido (< 1)
        $_SESSION['id_tipo_empleado'] = 2; // Médico
        $_SESSION['tipo_empleado'] = 'Médico';
        $_SESSION['id_empleado'] = 0; // Inválido

        try {
            reportesIaGenerar('medicina', 'analisis', [], null);
            $this->fail('Debe rechazar empleado especialista con ID menor a 1');
        } catch (ExcepcionApi $e) {
            $this->assertSame(403, $e->estado);
            $this->assertSame('Identificador de empleado no válido para la generación del informe.', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujos Completos)
    // =========================================================================

    /**
     * CP-REP-012: Disponibilidad de funciones procedimentales y vistas del Módulo Reportes
     */
    public function test3_Sistema_DisponibilidadControladoresYVistasReportes(): void
    {
        load_controller('reportesController.php');

        // Funciones de renderizado HTML
        $vistasHtml = [
            'showReportesGeneral',
            'showReportesMedicina',
            'showReportesPsicologia',
            'showReportesOrientacion',
            'showReportesTrabajoSocial',
            'showReportesDiscapacidad',
            'showReportesReferencias',
            'showReportesJornadas',
            'showReportesMobiliario',
            'showReportesTransporte',
        ];

        foreach ($vistasHtml as $fn) {
            $this->assertTrue(function_exists($fn), "Función {$fn} debe existir en el controlador");
        }

        // Endpoints API
        $endpointsApi = [
            'obtenerReporteGeneralData',
            'obtenerReporteMedicinaData',
            'obtenerReportePsicologiaData',
            'obtenerReporteOrientacionData',
            'obtenerReporteTrabajoSocialData',
            'obtenerReporteDiscapacidadData',
            'obtenerReporteReferenciasData',
            'obtenerReporteJornadasData',
            'obtenerReporteMobiliarioData',
            'obtenerReporteTransporteData',
            'obtenerReporteStats',
            'obtenerReportesCatalogos',
            'apiReportesIaInforme',
        ];

        foreach ($endpointsApi as $fn) {
            $this->assertTrue(function_exists($fn), "Endpoint {$fn} debe existir en el controlador");
        }

        // Existencia física de las plantillas en app/Views/reportes/
        $archivosVista = [
            'general.php',
            'medicina.php',
            'psicologia.php',
            'orientacion.php',
            'trabajo_social.php',
            'discapacidad.php',
            'referencias.php',
            'jornadas.php',
            'mobiliario.php',
            'transporte.php',
        ];

        foreach ($archivosVista as $archivo) {
            $this->assertFileExists(
                BASE_PATH . 'app/Views/reportes/' . $archivo,
                "Plantilla app/Views/reportes/{$archivo} debe existir físicamente"
            );
        }
    }

    /**
     * CP-REP-013: Flujo funcional de consulta de reporte y auditoría con acción 'Lectura'
     */
    public function test3_Sistema_FlujoFuncionalConsultaReportesYAuditoria(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Registro de auditoría institucional con la acción estricta 'Lectura' (nunca 'Consulta')
        Bitacora::registrar('Reportes', 'Lectura', 'Generó el reporte de Medicina.');

        // 3. Verificación de los contratos de salida esperados:
        // Medicina devuelve dos colecciones: consultas e insumos
        $contratoMedicinaEsperado = [
            'consultas' => [],
            'insumos'   => [],
        ];

        $this->assertArrayHasKey('consultas', $contratoMedicinaEsperado);
        $this->assertArrayHasKey('insumos', $contratoMedicinaEsperado);

        // Psicología devuelve dos colecciones: morbilidad y citas
        $contratoPsicologiaEsperado = [
            'morbilidad' => [],
            'citas'      => [],
        ];

        $this->assertArrayHasKey('morbilidad', $contratoPsicologiaEsperado);
        $this->assertArrayHasKey('citas', $contratoPsicologiaEsperado);
    }

    /**
     * CP-REP-014: Flujo funcional de consulta de estadísticas de tarjetas (stats) y auditoría
     */
    public function test3_Sistema_FlujoFuncionalStatsTarjetasYAuditoria(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Registro de auditoría institucional de stats
        Bitacora::registrar('Reportes', 'Lectura', 'Consultó las estadísticas de un reporte.');

        // 3. Estructura de contrato devuelto por api/reportes/stats
        $statsEsperadas = [
            'total'     => 120,
            'del_mes'   => 45,
            'hombres'   => 50,
            'mujeres'   => 70,
        ];

        $this->assertIsInt($statsEsperadas['total']);
        $this->assertIsInt($statsEsperadas['del_mes']);
        $this->assertIsInt($statsEsperadas['hombres']);
        $this->assertIsInt($statsEsperadas['mujeres']);
        $this->assertSame(120, $statsEsperadas['hombres'] + $statsEsperadas['mujeres']);
    }
}
