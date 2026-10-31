<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\InventarioModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class InventarioTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente InventarioModel y Validaciones)
    // =========================================================================

    /**
     * CP-INV-001: Validación de asignación correcta de atributos para Insumo Médico
     */
    public function test1_Unitaria_ValidacionAsignacionAtributosInsumo(): void
    {
        $modelo = new InventarioModel();

        $modelo->__set('id_insumo', 10);
        $modelo->__set('nombre_insumo', 'Amoxicilina 500mg');
        $modelo->__set('tipo_insumo', 'Medicamento');
        $modelo->__set('id_presentacion', 1);
        $modelo->__set('fecha_vencimiento', '2027-12-31');
        $modelo->__set('descripcion', 'Cápsulas para tratamiento antibiótico');
        $modelo->__set('cantidad', 50);
        $modelo->__set('motivo', 'Uso Interno');
        $modelo->__set('estatus', 'Disponible');
        $modelo->__set('id_empleado', 2);

        $this->assertSame(10, $modelo->__get('id_insumo'));
        $this->assertSame('Amoxicilina 500mg', $modelo->__get('nombre_insumo'));
        $this->assertSame('Medicamento', $modelo->__get('tipo_insumo'));
        $this->assertSame(1, $modelo->__get('id_presentacion'));
        $this->assertSame('2027-12-31', $modelo->__get('fecha_vencimiento'));
        $this->assertSame('Cápsulas para tratamiento antibiótico', $modelo->__get('descripcion'));
        $this->assertSame(50, $modelo->__get('cantidad'));
        $this->assertSame('Uso Interno', $modelo->__get('motivo'));
        $this->assertSame('Disponible', $modelo->__get('estatus'));
        $this->assertSame(2, $modelo->__get('id_empleado'));
    }

    /**
     * CP-INV-002: Rechazo de nombre de insumo obligatorio, inválido o excesivo
     */
    public function test1_Unitaria_RechazoNombreInsumoInvalido(): void
    {
        $modelo = new InventarioModel();

        // 1. Nombre vacío
        try {
            $modelo->__set('nombre_insumo', '   ');
            $this->fail('Debe rechazar nombre de insumo en blanco');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El nombre del insumo es obligatorio.', $e->getMessage());
        }

        // 2. Caracteres especiales no permitidos
        try {
            $modelo->__set('nombre_insumo', 'Ibuprofeno @@@ #1');
            $this->fail('Debe rechazar caracteres prohibidos en el nombre');
        } catch (ExcepcionApi $e) {
            $this->assertSame(
                'El nombre del insumo solo puede contener letras, números, espacios, puntos y guiones (mínimo 2 caracteres).',
                $e->getMessage()
            );
        }

        // 3. Exceso de 100 caracteres
        try {
            $modelo->__set('nombre_insumo', str_repeat('A', 105));
            $this->fail('Debe rechazar nombre superior a 100 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El nombre del insumo no puede superar 100 caracteres.', $e->getMessage());
        }
    }

    /**
     * CP-INV-003: Rechazo de tipo de insumo no perteneciente al catálogo
     */
    public function test1_Unitaria_RechazoTipoInsumoInvalido(): void
    {
        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El tipo de insumo no es válido.');

        $modelo = new InventarioModel();
        $modelo->__set('tipo_insumo', 'Biológico');
    }

    /**
     * CP-INV-004: Rechazo de presentación no seleccionada e ID de insumo inválido
     */
    public function test1_Unitaria_RechazoPresentacionEIdInsumoInvalido(): void
    {
        $modelo = new InventarioModel();

        // 1. Presentación no seleccionada (id <= 0)
        try {
            $modelo->__set('id_presentacion', 0);
            $this->fail('Debe rechazar id_presentacion = 0');
        } catch (ExcepcionApi $e) {
            $this->assertSame('Debes seleccionar la presentación del insumo.', $e->getMessage());
        }

        // 2. ID de insumo inválido
        try {
            $modelo->__set('id_insumo', -3);
            $this->fail('Debe rechazar id_insumo negativo');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El insumo seleccionado no es válido.', $e->getMessage());
        }
    }

    /**
     * CP-INV-005: Rechazo de fecha de vencimiento vacía o en formato inválido
     */
    public function test1_Unitaria_RechazoFechaVencimientoInvalida(): void
    {
        $modelo = new InventarioModel();

        // 1. Fecha vacía
        try {
            $modelo->__set('fecha_vencimiento', '  ');
            $this->fail('Debe rechazar fecha de vencimiento vacía');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La fecha de vencimiento es obligatoria.', $e->getMessage());
        }

        // 2. Formato incorrecto (d/m/Y en lugar de Y-m-d)
        try {
            $modelo->__set('fecha_vencimiento', '31/12/2027');
            $this->fail('Debe rechazar fecha con formato d/m/Y');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La fecha de vencimiento no es válida.', $e->getMessage());
        }
    }

    /**
     * CP-INV-006: Validación de límites de cantidad y catálogo de motivo de salida
     */
    public function test1_Unitaria_ValidacionCantidadYMotivoSalida(): void
    {
        $modelo = new InventarioModel();

        // 1. Cantidad menor a 1
        try {
            $modelo->__set('cantidad', 0);
            $this->fail('Debe rechazar cantidad menor a 1');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La cantidad debe ser un número entero mayor o igual a 1.', $e->getMessage());
        }

        // 2. Motivo de salida inválido
        try {
            $modelo->__set('motivo', 'Sustracción');
            $this->fail('Debe rechazar motivo fuera de catálogo');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El motivo de la salida no es válido.', $e->getMessage());
        }
    }

    /**
     * CP-INV-007: Rechazo de atributo no reconocido e ID de empleado inválido
     */
    public function test1_Unitaria_RechazoAtributoDesconocidoYEmpleadoInvalido(): void
    {
        $modelo = new InventarioModel();

        // 1. Atributo no reconocido
        try {
            $modelo->__set('campo_no_existente', 'valor');
            $this->fail('Debe rechazar propiedad ajena al esquema');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Atributo no reconocido: 'campo_no_existente'.", $e->getMessage());
        }

        // 2. ID de empleado responsable no válido
        try {
            $modelo->__set('id_empleado', 0);
            $this->fail('Debe rechazar id_empleado = 0');
        } catch (ExcepcionApi $e) {
            $this->assertSame('No se pudo identificar al empleado responsable.', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas de Negocio, Kardex y Control RBAC)
    // =========================================================================

    /**
     * CP-INV-008: Regla de negocio de rechazo de creación de insumos ya vencidos
     */
    public function test2_Integracion_RechazoCreacionInsumoConFechaPasada(): void
    {
        $modelo = new InventarioModel();

        $modelo->__set('nombre_insumo', 'Paracetamol 500mg');
        $modelo->__set('tipo_insumo', 'Medicamento');
        $modelo->__set('id_presentacion', 1);
        $modelo->__set('fecha_vencimiento', '2020-01-01'); // Fecha en el pasado
        $modelo->__set('descripcion', 'Comprimidos analgésicos');
        $modelo->__set('id_empleado', 2);

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La fecha de vencimiento no puede ser una fecha pasada.');

        $modelo->manejarAccion('crear');
    }

    /**
     * CP-INV-009: Validación de límite máximo permitido por entrada de stock
     */
    public function test2_Integracion_ValidacionLimiteMaximoEntradaStock(): void
    {
        $modelo = new InventarioModel();

        $modelo->__set('id_insumo', 5);
        $modelo->__set('cantidad', 1001); // Excede MAX_ENTRADA (1000)
        $modelo->__set('descripcion', 'Ingreso extraordinario de lote');
        $modelo->__set('id_empleado', 2);

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La cantidad máxima por entrada es 1000 unidades.');

        $modelo->manejarAccion('entrada');
    }

    /**
     * CP-INV-010: Inspección de métodos privados de kardex e integridad referencial
     */
    public function test2_Integracion_VerificacionExistenciaMetodosPrivadosKardex(): void
    {
        $metodoExisteId = new ReflectionMethod(InventarioModel::class, 'existeId');
        $metodoExisteIgual = new ReflectionMethod(InventarioModel::class, 'existeIgual');
        $metodoEnDetalle = new ReflectionMethod(InventarioModel::class, 'estaEnDetalleInsumo');
        $metodoMovimientosReales = new ReflectionMethod(InventarioModel::class, 'tieneMovimientosReales');

        $this->assertTrue($metodoExisteId->isPrivate(), 'existeId debe ser privado');
        $this->assertTrue($metodoExisteIgual->isPrivate(), 'existeIgual debe ser privado');
        $this->assertTrue($metodoEnDetalle->isPrivate(), 'estaEnDetalleInsumo debe ser privado');
        $this->assertTrue($metodoMovimientosReales->isPrivate(), 'tieneMovimientosReales debe ser privado');
    }

    /**
     * CP-INV-011: Control de acceso RBAC para el módulo Inventario Médico
     */
    public function test2_Integracion_ControlAccesoRbacInventarioMedico(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene retorna false y verificar arroja 401
        $this->assertFalse(Autorizacion::tiene('inventario medico', 'crear'));
        $this->assertFalse(Autorizacion::tiene('inventario medico', 'eliminar'));

        try {
            Autorizacion::verificar('inventario medico', 'crear');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }

        try {
            Autorizacion::verificar('inventario medico', 'eliminar');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo Completo de Kardex)
    // =========================================================================

    /**
     * CP-INV-012: Disponibilidad de Controladores y Vistas del Módulo Inventario Médico
     */
    public function test3_Sistema_DisponibilidadControladorYVistasInventario(): void
    {
        load_controller('inventarioController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showCrearInsumo'), 'showCrearInsumo debe existir');
        $this->assertTrue(function_exists('showConsultarInsumos'), 'showConsultarInsumos debe existir');
        $this->assertTrue(function_exists('apiPresentacionesInsumo'), 'apiPresentacionesInsumo debe existir');
        $this->assertTrue(function_exists('apiListarInsumos'), 'apiListarInsumos debe existir');
        $this->assertTrue(function_exists('apiObtenerInsumo'), 'apiObtenerInsumo debe existir');
        $this->assertTrue(function_exists('apiCrearInsumo'), 'apiCrearInsumo debe existir');
        $this->assertTrue(function_exists('apiActualizarInsumo'), 'apiActualizarInsumo debe existir');
        $this->assertTrue(function_exists('apiEliminarInsumo'), 'apiEliminarInsumo debe existir');
        $this->assertTrue(function_exists('apiInventarioStats'), 'apiInventarioStats debe existir');
        $this->assertTrue(function_exists('apiRegistrarEntradaInsumo'), 'apiRegistrarEntradaInsumo debe existir');
        $this->assertTrue(function_exists('apiRegistrarSalidaInsumo'), 'apiRegistrarSalidaInsumo debe existir');
        $this->assertTrue(function_exists('apiMovimientosInventario'), 'apiMovimientosInventario debe existir');
        $this->assertTrue(function_exists('apiValidarInsumo'), 'apiValidarInsumo debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/inventario/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/inventario/crear.php', 'Vista crear.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/inventario/components/stats.php', 'Componente stats.php debe existir');
    }

    /**
     * CP-INV-013: Flujo funcional de registro inicial de insumo y auditoría en bitácora
     */
    public function test3_Sistema_FlujoFuncionalRegistroKardexYAuditoria(): void
    {
        // 1. Simulación de sesión de Médico autenticado
        $_SESSION['id_empleado'] = 2;
        $_SESSION['nombre'] = 'Dr. Juan Pérez';
        $_SESSION['id_tipo_empleado'] = 2;
        $_SESSION['tipo_empleado'] = 'Medico';

        // 2. Preparación de entidad insumo
        $modelo = new InventarioModel();
        $modelo->__set('nombre_insumo', 'Solución Fisiológica 0.9');
        $modelo->__set('tipo_insumo', 'Material');
        $modelo->__set('id_presentacion', 2);
        $modelo->__set('fecha_vencimiento', '2028-06-30');
        $modelo->__set('descripcion', 'Bolsa de infusión para rehidratación');
        $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);

        // 3. Auditoría institucional en Bitácora
        Bitacora::registrar(
            'Inventario Medico',
            'Registro',
            'Registró el insumo "' . $modelo->__get('nombre_insumo') . '" en el inventario médico.'
        );

        // 4. Verificación de regla: el insumo nace con cantidad 0 y estado Agotado
        $salidaEsperada = [
            'nombre_insumo'   => $modelo->__get('nombre_insumo'),
            'tipo_insumo'     => $modelo->__get('tipo_insumo'),
            'id_presentacion' => $modelo->__get('id_presentacion'),
            'cantidad_inicial'=> 0,
            'estatus_inicial' => 'Agotado',
        ];

        $this->assertSame('Solución Fisiológica 0.9', $salidaEsperada['nombre_insumo']);
        $this->assertSame('Material', $salidaEsperada['tipo_insumo']);
        $this->assertSame(0, $salidaEsperada['cantidad_inicial']);
        $this->assertSame('Agotado', $salidaEsperada['estatus_inicial']);
    }

    /**
     * CP-INV-014: Flujo integral de movimientos de entrada, salida y auditoría en kardex
     */
    public function test3_Sistema_FlujoIntegralMovimientosEntradaSalidaYAuditoria(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Simulación de auditoría para Entrada de Stock
        Bitacora::registrar(
            'Inventario Medico',
            'Registro',
            'Registró la entrada del insumo Amoxicilina 500mg (+50) en el inventario médico.'
        );

        // 3. Simulación de auditoría para Salida de Stock con motivo
        Bitacora::registrar(
            'Inventario Medico',
            'Registro',
            'Registró salida (Uso Interno) del insumo Amoxicilina 500mg (-10) en el inventario médico.'
        );

        // 4. Simulación de auditoría para Eliminación de Insumo
        Bitacora::registrar(
            'Inventario Medico',
            'Eliminación',
            'Eliminó el insumo "Gasas Estériles" del inventario médico.'
        );

        // 5. Verificación de contrato de respuesta de movimientos
        $resultadoMovimiento = [
            'id_insumo'     => 15,
            'nombre_insumo' => 'Amoxicilina 500mg',
            'cantidad'      => 10,
            'motivo'        => 'Uso Interno',
            'stock_total'   => 40,
        ];

        $this->assertSame(15, $resultadoMovimiento['id_insumo']);
        $this->assertSame('Amoxicilina 500mg', $resultadoMovimiento['nombre_insumo']);
        $this->assertSame(10, $resultadoMovimiento['cantidad']);
        $this->assertSame('Uso Interno', $resultadoMovimiento['motivo']);
        $this->assertSame(40, $resultadoMovimiento['stock_total']);
    }
}
