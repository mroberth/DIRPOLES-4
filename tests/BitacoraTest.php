<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ErrorCodes;
use App\Core\ExcepcionApi;
use App\Models\BitacoraModel;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class BitacoraTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componentes, Atributos y Reglas de Entrada)
    // =========================================================================

    /**
     * CP-BIT-001: Asignación y normalización de atributos en BitacoraModel
     */
    public function test1_Unitaria_AsignacionYValidacionAtributosBitacora(): void
    {
        $modelo = new BitacoraModel();

        $modelo->__set('id_empleado', '5');
        $modelo->__set('modulo', 'Beneficiarios');
        $modelo->__set('accion', 'Registro');
        $modelo->__set('buscar', 'Carlos Pérez');
        $modelo->__set('desde', '2026-01-01');
        $modelo->__set('hasta', '2026-01-31');

        $this->assertSame(5, $modelo->__get('id_empleado'));
        $this->assertSame('Beneficiarios', $modelo->__get('modulo'));
        $this->assertSame('Registro', $modelo->__get('accion'));
        $this->assertSame('Carlos Pérez', $modelo->__get('buscar'));
        $this->assertSame('2026-01-01', $modelo->__get('desde'));
        $this->assertSame('2026-01-31', $modelo->__get('hasta'));

        // Truncado automático de strings a 100 caracteres
        $stringLargo = str_repeat('A', 150);
        $modelo->__set('modulo', $stringLargo);
        $this->assertSame(100, mb_strlen($modelo->__get('modulo')));
    }

    /**
     * CP-BIT-002: Validación estricta de formato de fechas YYYY-MM-DD en filtros
     */
    public function test1_Unitaria_ValidacionFormatoFechasFiltro(): void
    {
        $modelo = new BitacoraModel();

        $formatosInvalidos = [
            '01-01-2026',
            '2026/01/01',
            '2026-1-1',
            'fecha-invalida',
            '2026-1-01',
            '2026-01-1',
            '26-01-01',
        ];

        foreach ($formatosInvalidos as $fInvalido) {
            try {
                $modelo->__set('desde', $fInvalido);
                $this->fail("Debe rechazar formato inválido en 'desde': {$fInvalido}");
            } catch (ExcepcionApi $e) {
                $this->assertSame(400, $e->estado);
                $this->assertSame("La fecha 'desde' debe tener el formato YYYY-MM-DD.", $e->getMessage());
            }

            try {
                $modelo->__set('hasta', $fInvalido);
                $this->fail("Debe rechazar formato inválido en 'hasta': {$fInvalido}");
            } catch (ExcepcionApi $e) {
                $this->assertSame(400, $e->estado);
                $this->assertSame("La fecha 'hasta' debe tener el formato YYYY-MM-DD.", $e->getMessage());
            }
        }
    }

    /**
     * CP-BIT-003: Rechazo de atributos no reconocidos y acciones no válidas en BitacoraModel
     */
    public function test1_Unitaria_RechazoAtributoInexistenteYAccionNoValida(): void
    {
        $modelo = new BitacoraModel();

        // 1. Atributo no reconocido
        try {
            $modelo->__set('campo_no_existente', 'valor');
            $this->fail('Debe rechazar atributo ajeno al modelo');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame("Atributo no reconocido en BitacoraModel: 'campo_no_existente'.", $e->getMessage());
        }

        // 2. Acción no válida en manejarAccion
        try {
            $modelo->manejarAccion('eliminar_bitacora');
            $this->fail('Debe rechazar acciones no soportadas por ser solo lectura');
        } catch (ExcepcionApi $e) {
            $this->assertSame(500, $e->estado);
            $this->assertSame("Acción no válida en BitacoraModel: 'eliminar_bitacora'.", $e->getMessage());
        }
    }

    /**
     * CP-BIT-004: Validación de lista blanca de acciones permitidas en Bitacora::ACCIONES
     */
    public function test1_Unitaria_ValidacionEnumAccionesEnHelperBitacora(): void
    {
        $accionesEsperadas = [
            'Registro',
            'Lectura',
            'Actualización',
            'Eliminación',
            'Inicio de sesión',
            'Cierre de sesión',
            'Respaldo',
        ];

        $this->assertSame($accionesEsperadas, Bitacora::ACCIONES, 'Las acciones deben coincidir exactamente con el ENUM de BD');

        // Intento de registrar con acción fuera del ENUM
        $_SESSION['id_empleado'] = 5;
        $resultado = Bitacora::registrar('Módulo', 'AccionInexistente', 'Prueba de acción inválida');
        $this->assertFalse($resultado, 'Debe retornar false y no registrar ante acción fuera del catálogo');
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Helper Bitacora, SQL Query Builder y RBAC)
    // =========================================================================

    /**
     * CP-BIT-005: Comportamiento a prueba de fallos y requerimiento de sesión en Bitacora::registrar
     */
    public function test2_Integracion_HelperBitacoraSinSesionYResiliencia(): void
    {
        // 1. Sin sesión activa: no debe lanzar excepción, debe retornar false limpiamente
        $_SESSION = [];
        $resultadoSinSesion = Bitacora::registrar('Beneficiarios', 'Registro', 'Intento de registro sin sesión');
        $this->assertFalse($resultadoSinSesion, 'Bitacora::registrar debe retornar false si id_empleado no está en sesión');

        // 2. Con sesión activa: valida el recorte preventivo de longitudes
        $_SESSION['id_empleado'] = 6;
        $moduloLargo = str_repeat('M', 100);
        $descripcionLarga = str_repeat('D', 400);

        // Debe ejecutarse sin lanzar excepciones respetando el principio de fail-safe
        $resultado = Bitacora::registrar($moduloLargo, 'Registro', $descripcionLarga);
        $this->assertIsBool($resultado);
    }

    /**
     * CP-BIT-006: Construcción de filtros SQL dinámicos y enlace de parámetros PDO
     */
    public function test2_Integracion_GeneracionCondicionesFiltrosSql(): void
    {
        $modelo = new BitacoraModel();
        $refCondiciones = new ReflectionMethod(BitacoraModel::class, 'condiciones');

        // 1. Filtros vacíos: WHERE vacío y parámetros vacíos
        [$whereVacio, $paramsVacios] = $refCondiciones->invoke($modelo);
        $this->assertSame('', $whereVacio);
        $this->assertSame([], $paramsVacios);

        // 2. Filtros activos combinados
        $modelo->__set('modulo', 'Medicina');
        $modelo->__set('accion', 'Lectura');
        $modelo->__set('id_empleado', 4);
        $modelo->__set('desde', '2026-02-01');
        $modelo->__set('hasta', '2026-02-28');
        $modelo->__set('buscar', 'consulta');

        [$where, $params] = $refCondiciones->invoke($modelo);

        $this->assertStringContainsString('b.modulo = :modulo', $where);
        $this->assertStringContainsString('b.accion = :accion', $where);
        $this->assertStringContainsString('b.id_empleado = :id_empleado', $where);
        $this->assertStringContainsString('b.fecha >= :desde', $where);
        $this->assertStringContainsString('b.fecha <= :hasta', $where);
        $this->assertStringContainsString('b.descripcion LIKE :q1', $where);

        $this->assertSame('Medicina', $params[':modulo']);
        $this->assertSame('Lectura', $params[':accion']);
        $this->assertSame(4, $params[':id_empleado']);
        $this->assertSame('2026-02-01 00:00:00', $params[':desde']);
        $this->assertSame('2026-02-28 23:59:59', $params[':hasta']);
        $this->assertSame('%consulta%', $params[':q1']);
    }

    /**
     * CP-BIT-007: Introspección por reflexión de métodos privados e inmutabilidad de Bitacora
     */
    public function test2_Integracion_VerificacionMetodosPrivadosBitacora(): void
    {
        // 1. Métodos privados de BitacoraModel
        $metodosModelo = ['listar', 'filtros', 'stats', 'condiciones'];
        foreach ($metodosModelo as $metodo) {
            $refMethod = new ReflectionMethod(BitacoraModel::class, $metodo);
            $this->assertTrue(
                $refMethod->isPrivate(),
                "BitacoraModel::{$metodo} debe tener visibilidad privada estricta"
            );
        }

        // 2. Helper Bitacora: constructor privado (no instanciable) y conexión privada
        $refBitacora = new ReflectionClass(Bitacora::class);
        $constructor = $refBitacora->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate(), 'Bitacora no debe ser instanciable');

        $metodoConexion = new ReflectionMethod(Bitacora::class, 'conexion');
        $this->assertTrue($metodoConexion->isPrivate(), 'Bitacora::conexion debe ser privada');
    }

    /**
     * CP-BIT-008: Control de acceso RBAC en puntos de entrada del controlador de bitácora
     */
    public function test2_Integracion_ControlAccesoRbacEnControlador(): void
    {
        load_controller('bitacoraController.php');

        // 1. Sin sesión: RBAC lanza 401
        try {
            showConsultarBitacora();
            $this->fail('Debe bloquear acceso sin sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertSame(ErrorCodes::NO_AUTENTICADO, $e->codigo);
        }

        // 2. Con sesión activa pero sin permiso de lectura sobre bitácora (ej. Chofer ID 8)
        $_SESSION['id_tipo_empleado'] = 8;
        try {
            apiBitacoraListar();
            $this->fail('Debe denegar acceso al listado a roles sin permiso');
        } catch (ExcepcionApi $e) {
            $this->assertSame(403, $e->estado);
            $this->assertStringContainsString("No tienes permiso de 'leer' sobre el módulo 'bitacora'", $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo Completo de Auditoría)
    // =========================================================================

    /**
     * CP-BIT-009: Disponibilidad de funciones procedimentales, vistas y assets de Bitácora
     */
    public function test3_Sistema_DisponibilidadControladorYVistasBitacora(): void
    {
        load_controller('bitacoraController.php');

        // Funciones del controlador
        $funcionesEsperadas = [
            'showConsultarBitacora',
            'apiBitacoraListar',
            'apiBitacoraFiltros',
            'apiBitacoraStats',
        ];

        foreach ($funcionesEsperadas as $fn) {
            $this->assertTrue(function_exists($fn), "Función {$fn} debe existir en bitacoraController.php");
        }

        // Vistas en disco
        $this->assertFileExists(BASE_PATH . '/app/Views/bitacora/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . '/app/Views/bitacora/components/stats.php', 'Componente stats.php debe existir');

        // Scripts JS en disco
        $this->assertFileExists(BASE_PATH . '/dist/js/modulos/bitacora/stats.js', 'Script stats.js debe existir');
        $this->assertFileExists(BASE_PATH . '/dist/js/modulos/bitacora/tour.js', 'Script tour.js debe existir');
        $this->assertFileExists(BASE_PATH . '/dist/js/modulos/bitacora/consultar.js', 'Script consultar.js debe existir');
    }

    /**
     * CP-BIT-010: Flujo funcional de registro, consulta de estadísticas y contrato de datos
     */
    public function test3_Sistema_FlujoFuncionalRegistroAuditoriaYStats(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 1;
        $_SESSION['nombre'] = 'Admin Auditor';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // Sincronizar con un ID de empleado real existente en la base de datos de seguridad
        try {
            $refConexion = new ReflectionMethod(Bitacora::class, 'conexion');
            $pdo = $refConexion->invoke(null);
            $empleadoValido = $pdo->query("SELECT id_empleado FROM empleado ORDER BY id_empleado ASC LIMIT 1")->fetchColumn();
            if ($empleadoValido) {
                $_SESSION['id_empleado'] = (int) $empleadoValido;
            }
        } catch (\Throwable) {
            // Mantener sesión simulada
        }

        // 2. Registro de auditoría real
        $registroExitoso = Bitacora::registrar(
            'Bitacora',
            'Lectura',
            'Consultó el historial de auditoría del sistema.'
        );
        $this->assertTrue($registroExitoso, 'El registro de bitácora debe ser exitoso');

        // 3. Consulta de estadísticas desde el modelo
        $modelo = new BitacoraModel();
        $stats = $modelo->manejarAccion('stats');

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('bitacora_total', $stats);
        $this->assertArrayHasKey('bitacora_hoy', $stats);
        $this->assertArrayHasKey('bitacora_mes', $stats);
        $this->assertArrayHasKey('bitacora_modulo', $stats);

        $this->assertIsInt($stats['bitacora_total']);
        $this->assertIsInt($stats['bitacora_hoy']);
        $this->assertIsInt($stats['bitacora_mes']);
        $this->assertIsString($stats['bitacora_modulo']);
        $this->assertGreaterThanOrEqual(1, $stats['bitacora_total']);
    }
}
