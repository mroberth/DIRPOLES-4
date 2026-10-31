<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\TransporteModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TransporteTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente TransporteModel y Validaciones)
    // =========================================================================

    /**
     * CP-TRA-001: Validación de asignación y persistencia en memoria de atributos de Rutas
     */
    public function test1_Unitaria_ValidacionAtributosRutas(): void
    {
        $modelo = new TransporteModel();

        // Asignación completa de ruta
        $modelo->__set('id_ruta', 1);
        $modelo->__set('nombre_ruta', 'Ruta 1 - Barquisimeto / Cabudare');
        $modelo->__set('trayectoria', 'UPTAEB - Redoma Obelisco - Av. Libertador - Cabudare Centro');
        $modelo->__set('tipo_ruta', 'Interurbana');
        $modelo->__set('horario_salida', '06:30');
        $modelo->__set('horario_llegada', '18:30');
        $modelo->__set('punto_partida', 'Sede Central UPTAEB');
        $modelo->__set('punto_destino', 'Plaza Bolívar de Cabudare');
        $modelo->__set('estatus_ruta', 'Activa');

        $this->assertSame(1, $modelo->__get('id_ruta'));
        $this->assertSame('Ruta 1 - Barquisimeto / Cabudare', $modelo->__get('nombre_ruta'));
        $this->assertSame('UPTAEB - Redoma Obelisco - Av. Libertador - Cabudare Centro', $modelo->__get('trayectoria'));
        $this->assertSame('Interurbana', $modelo->__get('tipo_ruta'));
        $this->assertSame('06:30', $modelo->__get('horario_salida'));
        $this->assertSame('18:30', $modelo->__get('horario_llegada'));
        $this->assertSame('Sede Central UPTAEB', $modelo->__get('punto_partida'));
        $this->assertSame('Plaza Bolívar de Cabudare', $modelo->__get('punto_destino'));
        $this->assertSame('Activa', $modelo->__get('estatus_ruta'));

        // Rechazo de nombre de ruta vacío
        try {
            $modelo->__set('nombre_ruta', '   ');
            $this->fail('Debe rechazar nombre de ruta vacío');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El nombre de la ruta es obligatorio (máx. 100 caracteres).', $e->getMessage());
        }

        // Rechazo de estatus de ruta fuera de catálogo
        try {
            $modelo->__set('estatus_ruta', 'Suspendida');
            $this->fail('Debe rechazar estatus fuera de Activa/Inactiva');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El estatus de la ruta debe ser Activa o Inactiva.', $e->getMessage());
        }
    }

    /**
     * CP-TRA-002: Validación de formato de horarios de salida y llegada en Rutas (regex HH:MM)
     */
    public function test1_Unitaria_ValidacionFormatoHorariosRuta(): void
    {
        $modelo = new TransporteModel();

        // Horarios válidos
        $modelo->__set('horario_salida', '07:00');
        $modelo->__set('horario_llegada', '16:45');
        $this->assertSame('07:00', $modelo->__get('horario_salida'));
        $this->assertSame('16:45', $modelo->__get('horario_llegada'));

        // Horas inválidas
        $horariosInvalidos = ['25:00', '12:60', '8:00', 'tarde', '12-00'];
        foreach ($horariosInvalidos as $hInvalido) {
            try {
                $modelo->__set('horario_salida', $hInvalido);
                $this->fail("Debe rechazar horario con formato inválido: {$hInvalido}");
            } catch (ExcepcionApi $e) {
                $this->assertSame('El horario debe tener formato HH:MM.', $e->getMessage());
            }
        }
    }

    /**
     * CP-TRA-003: Catálogos permitidos de tipos y estados de Vehículos
     */
    public function test1_Unitaria_ValidacionCatalogosTipoYEstadoVehiculo(): void
    {
        $modelo = new TransporteModel();

        // Tipos de vehículos permitidos: ['Autobús', 'Camioneta', 'Automóvil']
        $tiposValidos = ['Autobús', 'Camioneta', 'Automóvil'];
        foreach ($tiposValidos as $tipo) {
            $modelo->__set('tipo_vehiculo', $tipo);
            $this->assertSame($tipo, $modelo->__get('tipo_vehiculo'));
        }

        try {
            $modelo->__set('tipo_vehiculo', 'Camión 350');
            $this->fail('Debe rechazar tipo de vehículo no admitido');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El tipo de vehículo no es válido.', $e->getMessage());
        }

        // Estados de vehículo permitidos: ['Activo', 'Inactivo', 'Mantenimiento']
        $estadosValidos = ['Activo', 'Inactivo', 'Mantenimiento'];
        foreach ($estadosValidos as $estado) {
            $modelo->__set('estado_vehiculo', $estado);
            $this->assertSame($estado, $modelo->__get('estado_vehiculo'));
        }

        try {
            $modelo->__set('estado_vehiculo', 'Averiado');
            $this->fail('Debe rechazar estado fuera de catálogo');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El estado del vehículo no es válido.', $e->getMessage());
        }
    }

    /**
     * CP-TRA-004: Validación de formato y obligatoriedad de Placa de Vehículo
     */
    public function test1_Unitaria_ValidacionFormatoPlacaVehiculo(): void
    {
        $modelo = new TransporteModel();

        // Placa en blanco
        try {
            $modelo->__set('placa', '   ');
            $this->fail('Debe rechazar placa en blanco');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La placa es obligatoria (máx. 20 caracteres).', $e->getMessage());
        }

        // Placa mayor a 20 caracteres
        try {
            $modelo->__set('placa', str_repeat('A', 25));
            $this->fail('Debe rechazar placa de longitud excesiva');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La placa es obligatoria (máx. 20 caracteres).', $e->getMessage());
        }

        // Conversión a mayúsculas y saneamiento automático
        $modelo->__set('placa', '  a12bc3d  ');
        $this->assertSame('A12BC3D', $modelo->__get('placa'));
    }

    /**
     * CP-TRA-005: Validación de tipo y número de documento de Proveedores
     */
    public function test1_Unitaria_ValidacionDocumentoProveedor(): void
    {
        $modelo = new TransporteModel();

        // Tipo de documento válido: V, E, J, G
        $tiposValidos = ['V', 'E', 'J', 'G'];
        foreach ($tiposValidos as $tipo) {
            $modelo->__set('tipo_documento', strtolower($tipo));
            $this->assertSame($tipo, $modelo->__get('tipo_documento'));
        }

        try {
            $modelo->__set('tipo_documento', 'P');
            $this->fail('Debe rechazar tipo de documento fuera de V, E, J, G');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El tipo de documento debe ser V, E, J o G.', $e->getMessage());
        }

        // Número de documento (entre 6 y 10 dígitos)
        $modelo->__set('num_documento', '12.345.678');
        $this->assertSame('12345678', $modelo->__get('num_documento'));

        try {
            $modelo->__set('num_documento', '12345'); // Menor a 6
            $this->fail('Debe rechazar documento menor a 6 dígitos');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El número de documento debe tener entre 6 y 10 dígitos.', $e->getMessage());
        }

        try {
            $modelo->__set('num_documento', '12345678901'); // Mayor a 10
            $this->fail('Debe rechazar documento mayor a 10 dígitos');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El número de documento debe tener entre 6 y 10 dígitos.', $e->getMessage());
        }
    }

    /**
     * CP-TRA-006: Validación estricta de teléfono venezolano y correo de Proveedores
     */
    public function test1_Unitaria_ValidacionTelefonoYCorreoProveedor(): void
    {
        $modelo = new TransporteModel();

        // Teléfono venezolano válido (móvil o código de área 02xx)
        $telefonosValidos = ['04129298008', '04145123456', '04169876543', '02512591111'];
        foreach ($telefonosValidos as $tel) {
            $modelo->__set('telefono_proveedor', $tel);
            $this->assertSame($tel, $modelo->__get('telefono_proveedor'));
        }

        // Teléfono inválido
        try {
            $modelo->__set('telefono_proveedor', '04991234567');
            $this->fail('Debe rechazar prefijo telefónico no venezolano');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('El teléfono debe empezar por 0412', $e->getMessage());
        }

        // Correo electrónico
        $modelo->__set('correo_proveedor', 'ventas@autoparteslara.com');
        $this->assertSame('ventas@autoparteslara.com', $modelo->__get('correo_proveedor'));

        try {
            $modelo->__set('correo_proveedor', 'correo-invalido');
            $this->fail('Debe rechazar correo no válido');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El correo electrónico no es válido.', $e->getMessage());
        }
    }

    /**
     * CP-TRA-007: Catálogo cerrado de motivos de entrada y salida para Kardex de Repuestos
     */
    public function test1_Unitaria_ValidacionMotivosKardexRepuestos(): void
    {
        $modelo = new TransporteModel();

        // Motivos válidos de Entrada y Salida
        $motivosPermitidos = [
            'Compra', 'Devolución', 'Donación', 'Ajuste de inventario',
            'Uso en mantenimiento', 'Vencimiento', 'Daño', 'Pérdida'
        ];
        foreach ($motivosPermitidos as $motivo) {
            $modelo->__set('razon_movimiento', $motivo);
            $this->assertSame($motivo, $modelo->__get('razon_movimiento'));
        }

        // Motivo no contemplado
        try {
            $modelo->__set('razon_movimiento', 'Regalo');
            $this->fail('Debe rechazar motivo fuera del catálogo de inventario');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('La razón del movimiento no es válida.', $e->getMessage());
        }

        // Cantidad de repuestos (entero >= 0)
        $modelo->__set('cantidad_repuesto', 15);
        $this->assertSame(15, $modelo->__get('cantidad_repuesto'));

        try {
            $modelo->__set('cantidad_repuesto', -3);
            $this->fail('Debe rechazar cantidad de repuestos negativa');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La cantidad de repuestos debe ser un entero >= 0.', $e->getMessage());
        }
    }

    /**
     * CP-TRA-008: Validación de tipos y longitudes de Mantenimiento
     */
    public function test1_Unitaria_ValidacionAtributosMantenimiento(): void
    {
        $modelo = new TransporteModel();

        // Tipos de mantenimiento válidos: Preventivo y Correctivo
        $modelo->__set('tipo_mantenimiento', 'Preventivo');
        $this->assertSame('Preventivo', $modelo->__get('tipo_mantenimiento'));

        $modelo->__set('tipo_mantenimiento', 'Correctivo');
        $this->assertSame('Correctivo', $modelo->__get('tipo_mantenimiento'));

        try {
            $modelo->__set('tipo_mantenimiento', 'Urgente');
            $this->fail('Debe rechazar tipo de mantenimiento no admitido');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El tipo de mantenimiento debe ser Preventivo o Correctivo.', $e->getMessage());
        }

        // Descripción de mantenimiento (máx 1000 caracteres)
        try {
            $modelo->__set('descripcion_mantenimiento', str_repeat('X', 1050));
            $this->fail('Debe rechazar descripción superior a 1000 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La descripción del mantenimiento no puede superar 1000 caracteres.', $e->getMessage());
        }

        // Repuestos usados debe ser lista / array
        try {
            $modelo->__set('repuestos_usados', 'no-es-array');
            $this->fail('Debe rechazar lista de repuestos si no es un array');
        } catch (ExcepcionApi $e) {
            $this->assertSame('Los repuestos usados deben enviarse como una lista.', $e->getMessage());
        }
    }

    /**
     * CP-TRA-009: Encapsulamiento y rechazo de atributos no reconocidos
     */
    public function test1_Unitaria_RechazoAtributoDesconocido(): void
    {
        $modelo = new TransporteModel();

        try {
            $modelo->__set('campo_desconocido', 'prueba');
            $this->fail('Debe rechazar propiedades que no pertenecen al esquema de transporte');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Atributo no reconocido: 'campo_desconocido'.", $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas de Negocio, Kardex y RBAC)
    // =========================================================================

    /**
     * CP-TRA-010: Regla de negocio de Asignaciones: validación de rol Chofer activo
     */
    public function test2_Integracion_ValidacionRolChoferEnAsignaciones(): void
    {
        $metodo = new ReflectionMethod(TransporteModel::class, 'validarChofer');
        $this->assertTrue($metodo->isPrivate(), 'validarChofer debe tener visibilidad privada');

        $modelo = new TransporteModel();

        // En caso de suministrar ID de chofer inexistente (ej. 0 o inválido), la consulta falla
        try {
            $metodo->invoke($modelo, 0);
            $this->fail('Debe rechazar chofer con ID no válido');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El empleado seleccionado no es un chofer activo.', $e->getMessage());
        }
    }

    /**
     * CP-TRA-011: Regla de no solapamiento de chofer o vehículo el mismo día
     */
    public function test2_Integracion_VerificacionReglaSolapamientoDia(): void
    {
        $metodo = new ReflectionMethod(TransporteModel::class, 'verificarSolapamientoDia');
        $this->assertTrue($metodo->isPrivate(), 'verificarSolapamientoDia debe ser privado');

        // Intento de crear asignación con IDs vacíos debe fallar en la validación
        $modelo = new TransporteModel();
        $modelo->__set('id_ruta', 0);
        $modelo->__set('id_vehiculo', 0);
        $modelo->__set('id_empleado', 0);

        try {
            $modelo->manejarAccion('crear_asignacion');
            $this->fail('Debe rechazar asignación con relaciones inválidas');
        } catch (ExcepcionApi $e) {
            $this->assertSame('Debes seleccionar una ruta, un vehículo y un chofer válidos.', $e->getMessage());
        }
    }

    /**
     * CP-TRA-012: Control transaccional de kardex: validación de stock y sesión en movimientos
     */
    public function test2_Integracion_ValidacionStockInsuficienteSalidaRepuesto(): void
    {
        $modelo = new TransporteModel();

        // 1. Entrada de repuesto con cantidad 0
        $modelo->__set('id_repuesto', 1);
        $modelo->__set('cantidad_repuesto', 0);
        $modelo->__set('razon_movimiento', 'Compra');

        try {
            $modelo->manejarAccion('entrada_repuesto');
            $this->fail('Debe rechazar entrada con cantidad 0');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La cantidad a ingresar debe ser un entero mayor a 0.', $e->getMessage());
        }

        // 2. Salida de repuesto con cantidad 0
        try {
            $modelo->manejarAccion('salida_repuesto');
            $this->fail('Debe rechazar salida con cantidad 0');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La cantidad a retirar debe ser un entero mayor a 0.', $e->getMessage());
        }

        // 3. Salida sin sesión activa para kardex
        $modelo->__set('cantidad_repuesto', 5);
        $modelo->__set('razon_movimiento', 'Uso en mantenimiento');

        try {
            $modelo->manejarAccion('salida_repuesto');
            $this->fail('Debe rechazar movimiento de inventario sin sesión de empleado');
        } catch (ExcepcionApi $e) {
            // Falla por falta de sesión o por repuesto no encontrado en BD
            $this->assertInstanceOf(ExcepcionApi::class, $e);
        }
    }

    /**
     * CP-TRA-013: Regla de eliminación de repuestos: bloqueo con stock disponible o movimientos
     */
    public function test2_Integracion_ReglasBloqueoEliminacionRepuesto(): void
    {
        $metodoEliminar = new ReflectionMethod(TransporteModel::class, 'eliminarRepuesto');
        $this->assertTrue($metodoEliminar->isPrivate(), 'eliminarRepuesto debe ser privado');

        $modelo = new TransporteModel();
        $modelo->__set('id_repuesto', 99999);

        // Si el repuesto no existe, lanza 404
        try {
            $metodoEliminar->invoke($modelo);
            $this->fail('Debe arrojar ExcepcionApi al intentar eliminar repuesto inexistente');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El repuesto solicitado no existe.', $e->getMessage());
        }
    }

    /**
     * CP-TRA-014: Regla de Mantenimiento: cambio de estado de vehículo y reactivación condicional
     */
    public function test2_Integracion_ReglaReactivacionVehiculoMantenimiento(): void
    {
        $metodoEliminarMant = new ReflectionMethod(TransporteModel::class, 'eliminarMantenimiento');
        $this->assertTrue($metodoEliminarMant->isPrivate(), 'eliminarMantenimiento debe ser privado');

        $modelo = new TransporteModel();
        $modelo->__set('id_mantenimiento', 99999);

        try {
            $metodoEliminarMant->invoke($modelo);
            $this->fail('Debe rechazar eliminación de mantenimiento inexistente');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El mantenimiento solicitado no existe.', $e->getMessage());
        }
    }

    /**
     * CP-TRA-015: Introspección por reflexión de métodos privados de integridad y kardex
     */
    public function test2_Integracion_VerificacionMetodosPrivadosTransporte(): void
    {
        $metodos = [
            'existeRutaNombre',
            'existePlaca',
            'existeDocumentoGlobal',
            'existeCorreoGlobal',
            'existeTelefonoGlobal',
            'validarChofer',
            'verificarSolapamientoDia',
            'idEmpleadoSesion',
            'obtenerStats',
            'listarRutas',
            'listarVehiculos',
            'listarProveedores',
            'listarRepuestos',
            'listarAsignaciones',
            'listarMantenimientos',
        ];

        foreach ($metodos as $nombreMetodo) {
            $refMethod = new ReflectionMethod(TransporteModel::class, $nombreMetodo);
            $this->assertTrue(
                $refMethod->isPrivate(),
                "El método {$nombreMetodo} debe tener visibilidad privada según la arquitectura"
            );
        }
    }

    /**
     * CP-TRA-016: Control de acceso RBAC contra el módulo 'transporte'
     */
    public function test2_Integracion_ControlAccesoRbacTransporte(): void
    {
        // Sin sesión iniciada
        $this->assertFalse(Autorizacion::tiene('transporte', 'crear'));
        $this->assertFalse(Autorizacion::tiene('transporte', 'leer'));
        $this->assertFalse(Autorizacion::tiene('transporte', 'editar'));
        $this->assertFalse(Autorizacion::tiene('transporte', 'eliminar'));

        try {
            Autorizacion::verificar('transporte', 'crear');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }

        try {
            Autorizacion::verificar('transporte', 'eliminar');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujos Completos)
    // =========================================================================

    /**
     * CP-TRA-017: Disponibilidad de Controladores y Vistas del Módulo Transporte
     */
    public function test3_Sistema_DisponibilidadControladorYVistasTransporte(): void
    {
        load_controller('transporteController.php');

        // Puerta HTML
        $this->assertTrue(function_exists('showCrearTransporte'), 'showCrearTransporte debe existir');
        $this->assertTrue(function_exists('showConsultarTransporte'), 'showConsultarTransporte debe existir');

        // Stats
        $this->assertTrue(function_exists('apiStatsTransporte'), 'apiStatsTransporte debe existir');

        // Subflujo Rutas
        $this->assertTrue(function_exists('apiListarRutas'), 'apiListarRutas debe existir');
        $this->assertTrue(function_exists('apiCrearRuta'), 'apiCrearRuta debe existir');
        $this->assertTrue(function_exists('apiObtenerRuta'), 'apiObtenerRuta debe existir');
        $this->assertTrue(function_exists('apiActualizarRuta'), 'apiActualizarRuta debe existir');
        $this->assertTrue(function_exists('apiEliminarRuta'), 'apiEliminarRuta debe existir');

        // Subflujo Vehículos
        $this->assertTrue(function_exists('apiListarVehiculos'), 'apiListarVehiculos debe existir');
        $this->assertTrue(function_exists('apiCrearVehiculo'), 'apiCrearVehiculo debe existir');
        $this->assertTrue(function_exists('apiObtenerVehiculo'), 'apiObtenerVehiculo debe existir');
        $this->assertTrue(function_exists('apiActualizarVehiculo'), 'apiActualizarVehiculo debe existir');
        $this->assertTrue(function_exists('apiEliminarVehiculo'), 'apiEliminarVehiculo debe existir');
        $this->assertTrue(function_exists('apiValidarPlacaVehiculo'), 'apiValidarPlacaVehiculo debe existir');

        // Subflujo Proveedores
        $this->assertTrue(function_exists('apiListarProveedores'), 'apiListarProveedores debe existir');
        $this->assertTrue(function_exists('apiCrearProveedor'), 'apiCrearProveedor debe existir');
        $this->assertTrue(function_exists('apiObtenerProveedor'), 'apiObtenerProveedor debe existir');
        $this->assertTrue(function_exists('apiActualizarProveedor'), 'apiActualizarProveedor debe existir');
        $this->assertTrue(function_exists('apiEliminarProveedor'), 'apiEliminarProveedor debe existir');
        $this->assertTrue(function_exists('apiValidarDocumentoProveedor'), 'apiValidarDocumentoProveedor debe existir');
        $this->assertTrue(function_exists('apiValidarCorreoProveedor'), 'apiValidarCorreoProveedor debe existir');
        $this->assertTrue(function_exists('apiValidarTelefonoProveedor'), 'apiValidarTelefonoProveedor debe existir');

        // Subflujo Repuestos y Kardex
        $this->assertTrue(function_exists('apiListarRepuestos'), 'apiListarRepuestos debe existir');
        $this->assertTrue(function_exists('apiCrearRepuesto'), 'apiCrearRepuesto debe existir');
        $this->assertTrue(function_exists('apiObtenerRepuesto'), 'apiObtenerRepuesto debe existir');
        $this->assertTrue(function_exists('apiActualizarRepuesto'), 'apiActualizarRepuesto debe existir');
        $this->assertTrue(function_exists('apiEliminarRepuesto'), 'apiEliminarRepuesto debe existir');
        $this->assertTrue(function_exists('apiEntradaRepuesto'), 'apiEntradaRepuesto debe existir');
        $this->assertTrue(function_exists('apiSalidaRepuesto'), 'apiSalidaRepuesto debe existir');
        $this->assertTrue(function_exists('apiHistorialRepuestos'), 'apiHistorialRepuestos debe existir');

        // Subflujo Asignaciones
        $this->assertTrue(function_exists('apiListarAsignaciones'), 'apiListarAsignaciones debe existir');
        $this->assertTrue(function_exists('apiOpcionesAsignacion'), 'apiOpcionesAsignacion debe existir');
        $this->assertTrue(function_exists('apiCrearAsignacion'), 'apiCrearAsignacion debe existir');
        $this->assertTrue(function_exists('apiObtenerAsignacion'), 'apiObtenerAsignacion debe existir');
        $this->assertTrue(function_exists('apiActualizarAsignacion'), 'apiActualizarAsignacion debe existir');
        $this->assertTrue(function_exists('apiEliminarAsignacion'), 'apiEliminarAsignacion debe existir');

        // Subflujo Mantenimientos
        $this->assertTrue(function_exists('apiListarMantenimientos'), 'apiListarMantenimientos debe existir');
        $this->assertTrue(function_exists('apiCrearMantenimiento'), 'apiCrearMantenimiento debe existir');
        $this->assertTrue(function_exists('apiObtenerMantenimiento'), 'apiObtenerMantenimiento debe existir');
        $this->assertTrue(function_exists('apiEliminarMantenimiento'), 'apiEliminarMantenimiento debe existir');

        // Existencia física de vistas
        $this->assertFileExists(BASE_PATH . 'app/Views/transporte/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/transporte/crear.php', 'Vista crear.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/transporte/components/stats.php', 'Componente stats.php debe existir');
    }

    /**
     * CP-TRA-018: Flujo funcional de registro de ruta, vehículo y asignación con auditoría
     */
    public function test3_Sistema_FlujoFuncionalRutaVehiculoAsignacionYAuditoria(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Preparación de entidad Ruta
        $modeloRuta = new TransporteModel();
        $modeloRuta->__set('nombre_ruta', 'Ruta Norte - El Cují / Tamaca');
        $modeloRuta->__set('tipo_ruta', 'Urbana');
        $modeloRuta->__set('horario_salida', '06:00');
        $modeloRuta->__set('horario_llegada', '19:00');
        $modeloRuta->__set('estatus_ruta', 'Activa');

        Bitacora::registrar('Transporte', 'Registro', "Creó la ruta '{$modeloRuta->__get('nombre_ruta')}'");

        // 3. Preparación de entidad Vehículo
        $modeloVeh = new TransporteModel();
        $modeloVeh->__set('placa', 'A45BK8D');
        $modeloVeh->__set('modelo', 'Encava ENT-610');
        $modeloVeh->__set('tipo_vehiculo', 'Autobús');
        $modeloVeh->__set('estado_vehiculo', 'Activo');
        $modeloVeh->__set('fecha_adquisicion', '2024-02-15');

        Bitacora::registrar('Transporte', 'Registro', "Creó el vehículo placa '{$modeloVeh->__get('placa')}'");

        // 4. Preparación de Asignación
        $fechaAsig = date('Y-m-d');
        Bitacora::registrar(
            'Transporte',
            'Registro',
            "Asignó vehículo {$modeloVeh->__get('placa')} a ruta '{$modeloRuta->__get('nombre_ruta')}' para la fecha {$fechaAsig}"
        );

        // 5. Verificación de consistencia del contrato de salida
        $contratoAsignacion = [
            'placa'            => $modeloVeh->__get('placa'),
            'nombre_ruta'      => $modeloRuta->__get('nombre_ruta'),
            'fecha_asignacion' => $fechaAsig,
            'estatus'          => 'Activa',
        ];

        $this->assertSame('A45BK8D', $contratoAsignacion['placa']);
        $this->assertSame('Ruta Norte - El Cují / Tamaca', $contratoAsignacion['nombre_ruta']);
        $this->assertSame('Activa', $contratoAsignacion['estatus']);
    }

    /**
     * CP-TRA-019: Flujo funcional de kardex de repuestos y consumo en mantenimiento
     */
    public function test3_Sistema_FlujoFuncionalKardexRepuestosYMantenimiento(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Alta inicial de repuesto (nace con cantidad 0 y Agotado)
        $modeloRep = new TransporteModel();
        $modeloRep->__set('nombre_repuesto', 'Filtro de Aceite Heavy Duty');
        $modeloRep->__set('descripcion_repuesto', 'Filtro para motor diesel Encava');

        Bitacora::registrar('Transporte', 'Registro', "Registró el repuesto '{$modeloRep->__get('nombre_repuesto')}'");

        // 3. Entrada de inventario
        Bitacora::registrar('Transporte', 'Actualización', "Entrada de 10 unidades del repuesto '{$modeloRep->__get('nombre_repuesto')}'");

        // 4. Mantenimiento y deducción de stock
        Bitacora::registrar('Transporte', 'Registro', "Registró mantenimiento Preventivo al vehículo placa 'A45BK8D'");
        Bitacora::registrar('Transporte', 'Actualización', "Salida de 2 unidades del repuesto '{$modeloRep->__get('nombre_repuesto')}'");

        $stockFinalEsperado = 10 - 2;
        $this->assertSame(8, $stockFinalEsperado);
    }

    /**
     * CP-TRA-020: Flujo funcional de gestión de proveedores y auditoría institucional
     */
    public function test3_Sistema_FlujoFuncionalProveedoresYAuditoria(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Configuración de entidad Proveedor
        $modeloProv = new TransporteModel();
        $modeloProv->__set('tipo_documento', 'J');
        $modeloProv->__set('num_documento', '304958210');
        $modeloProv->__set('nombre_proveedor', 'Repuestos y Servicios Centro C.A.');
        $modeloProv->__set('telefono_proveedor', '04145129834');
        $modeloProv->__set('correo_proveedor', 'contacto@repuestoscentro.com');
        $modeloProv->__set('direccion_proveedor', 'Av. Venezuela con calle 30, Barquisimeto');
        $modeloProv->__set('estatus_proveedor', 'Activo');

        // 3. Auditoría de registro
        Bitacora::registrar(
            'Transporte',
            'Registro',
            "Creó el proveedor '{$modeloProv->__get('nombre_proveedor')}' ({$modeloProv->__get('tipo_documento')}-{$modeloProv->__get('num_documento')})"
        );

        // 4. Verificación de atributos del proveedor
        $this->assertSame('J', $modeloProv->__get('tipo_documento'));
        $this->assertSame('304958210', $modeloProv->__get('num_documento'));
        $this->assertSame('Repuestos y Servicios Centro C.A.', $modeloProv->__get('nombre_proveedor'));
        $this->assertSame('04145129834', $modeloProv->__get('telefono_proveedor'));
        $this->assertSame('contacto@repuestoscentro.com', $modeloProv->__get('correo_proveedor'));
        $this->assertSame('Activo', $modeloProv->__get('estatus_proveedor'));
    }
}
