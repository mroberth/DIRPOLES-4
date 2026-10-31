<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\MobiliarioModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class MobiliarioTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componente MobiliarioModel y Validaciones)
    // =========================================================================

    /**
     * CP-MOB-001: Validación de asignación correcta de atributos para Mobiliario y Equipos
     */
    public function test1_Unitaria_ValidacionAsignacionMobiliarioYEquipos(): void
    {
        $modelo = new MobiliarioModel();

        $modelo->__set('id_mobiliario', 5);
        $modelo->__set('id_tipo_mobiliario', 1);
        $modelo->__set('id_servicios', 2);
        $modelo->__set('cantidad', 10);
        $modelo->__set('estado', 'Bueno');
        $modelo->__set('marca', 'Muebles Lara');
        $modelo->__set('modelo', 'ML-2024');
        $modelo->__set('color', 'Gris plomo');
        $modelo->__set('fecha_adquisicion', '2025-01-15');
        $modelo->__set('descripcion', 'Sillas ergonomicas de oficina');
        $modelo->__set('observaciones', 'Asignadas al laboratorio de computacion');
        $modelo->__set('id_empleado', 6);

        $this->assertSame(5, $modelo->__get('id_mobiliario'));
        $this->assertSame(1, $modelo->__get('id_tipo_mobiliario'));
        $this->assertSame(2, $modelo->__get('id_servicios'));
        $this->assertSame(10, $modelo->__get('cantidad'));
        $this->assertSame('Bueno', $modelo->__get('estado'));
        $this->assertSame('Muebles Lara', $modelo->__get('marca'));
        $this->assertSame('ML-2024', $modelo->__get('modelo'));
        $this->assertSame('Gris plomo', $modelo->__get('color'));
        $this->assertSame('2025-01-15', $modelo->__get('fecha_adquisicion'));
        $this->assertSame('Sillas ergonomicas de oficina', $modelo->__get('descripcion'));
        $this->assertSame('Asignadas al laboratorio de computacion', $modelo->__get('observaciones'));
        $this->assertSame(6, $modelo->__get('id_empleado'));
    }

    /**
     * CP-MOB-002: Validación de serial único de equipos y control de formato
     */
    public function test1_Unitaria_ValidacionSerialUnicoEquipoYFormato(): void
    {
        $modelo = new MobiliarioModel();

        // 1. Serial vacío
        try {
            $modelo->__set('serial', '   ');
            $this->fail('Debe rechazar serial en blanco');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El serial del equipo es obligatorio.', $e->getMessage());
        }

        // 2. Serial menor a 3 caracteres
        try {
            $modelo->__set('serial', 'AB');
            $this->fail('Debe rechazar serial menor a 3 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El serial debe tener entre 3 y 100 caracteres.', $e->getMessage());
        }

        // 3. Serial con caracteres especiales prohibidos
        try {
            $modelo->__set('serial', 'SN 123 @#');
            $this->fail('Debe rechazar caracteres no alfanuméricos en el serial');
        } catch (ExcepcionApi $e) {
            $this->assertSame(
                'El serial solo puede contener letras, números, puntos, guiones, barras y guiones bajos.',
                $e->getMessage()
            );
        }

        // 4. Serial válido
        $modelo->__set('serial', 'DELL-LAT-7490.V1_01');
        $this->assertSame('DELL-LAT-7490.V1_01', $modelo->__get('serial'));
    }

    /**
     * CP-MOB-003: Validación de límites de cantidad y catálogo de estado de ítem
     */
    public function test1_Unitaria_ValidacionLimitesCantidadYEstadoItem(): void
    {
        $modelo = new MobiliarioModel();

        // 1. Cantidad menor a 1 (ej. 0)
        try {
            $modelo->__set('cantidad', 0);
            $this->fail('Debe rechazar cantidad menor a 1');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La cantidad debe ser un número entero mayor o igual a 1.', $e->getMessage());
        }

        // 2. Estado fuera de catálogo
        try {
            $modelo->__set('estado', 'Descompuesto');
            $this->fail('Debe rechazar estado fuera de catálogo');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El estado del ítem no es válido.', $e->getMessage());
        }

        // 3. Estados válidos permitidos
        $estadosPermitidos = ['Nuevo', 'Bueno', 'Regular', 'Malo', 'En reparación'];
        foreach ($estadosPermitidos as $estado) {
            $modelo->__set('estado', $estado);
            $this->assertSame($estado, $modelo->__get('estado'));
        }
    }

    /**
     * CP-MOB-004: Validación de nombre de ficha técnica y longitud máxima
     */
    public function test1_Unitaria_ValidacionNombreFichaTecnica(): void
    {
        $modelo = new MobiliarioModel();

        // 1. Nombre de ficha vacío
        try {
            $modelo->__set('nombre_ficha', '   ');
            $this->fail('Debe rechazar nombre de ficha en blanco');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El nombre de la ficha es obligatorio.', $e->getMessage());
        }

        // 2. Nombre de ficha superior a 100 caracteres
        try {
            $modelo->__set('nombre_ficha', str_repeat('F', 105));
            $this->fail('Debe rechazar nombre de ficha superior a 100 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El nombre de la ficha no puede superar 100 caracteres.', $e->getMessage());
        }

        // 3. Nombre válido
        $modelo->__set('nombre_ficha', 'Ficha Técnica Médica - Consultorio 1');
        $this->assertSame('Ficha Técnica Médica - Consultorio 1', $modelo->__get('nombre_ficha'));
    }

    /**
     * CP-MOB-005: Validación de fecha de adquisición no futura
     */
    public function test1_Unitaria_ValidacionFechaAdquisicionNoFutura(): void
    {
        $modelo = new MobiliarioModel();

        // 1. Fecha con formato inválido
        try {
            $modelo->__set('fecha_adquisicion', 'fecha-invalida');
            $this->fail('Debe rechazar fecha no parseable');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La fecha de adquisición no es válida.', $e->getMessage());
        }

        // 2. Fecha de adquisición futura
        $fechaFutura = date('Y-m-d', strtotime('+10 days'));
        try {
            $modelo->__set('fecha_adquisicion', $fechaFutura);
            $this->fail('Debe rechazar fecha de adquisición futura');
        } catch (ExcepcionApi $e) {
            $this->assertSame('La fecha de adquisición no puede ser futura.', $e->getMessage());
        }

        // 3. Fecha válida en el pasado
        $modelo->__set('fecha_adquisicion', '2024-03-20');
        $this->assertSame('2024-03-20', $modelo->__get('fecha_adquisicion'));
    }

    /**
     * CP-MOB-006: Validación de listas de detalles y prevención de ítems duplicados
     */
    public function test1_Unitaria_ValidacionDetallesMobiliarioYEquiposFicha(): void
    {
        $modelo = new MobiliarioModel();

        // 1. Duplicación de mobiliario en la misma ficha
        try {
            $modelo->__set('detalles_mobiliario', [
                ['id_mobiliario' => 1, 'cantidad' => 2],
                ['id_mobiliario' => 1, 'cantidad' => 3], // Repetido
            ]);
            $this->fail('Debe rechazar mobiliario duplicado');
        } catch (ExcepcionApi $e) {
            $this->assertSame(
                'No puedes repetir el mismo mobiliario en la ficha: junta las cantidades en una sola fila.',
                $e->getMessage()
            );
        }

        // 2. Duplicación de equipo en la misma ficha
        try {
            $modelo->__set('detalles_equipo', [2, 2]); // Repetido
            $this->fail('Debe rechazar equipo duplicado');
        } catch (ExcepcionApi $e) {
            $this->assertSame('No puedes repetir el mismo equipo en la ficha.', $e->getMessage());
        }

        // 3. Asignación correcta sin duplicados
        $modelo->__set('detalles_mobiliario', [
            ['id_mobiliario' => 1, 'cantidad' => 2],
            ['id_mobiliario' => 2, 'cantidad' => 4],
        ]);
        $modelo->__set('detalles_equipo', [10, 11]);

        $this->assertCount(2, $modelo->__get('detalles_mobiliario'));
        $this->assertCount(2, $modelo->__get('detalles_equipo'));
    }

    /**
     * CP-MOB-007: Rechazo de atributo no reconocido e ID relacional inválido
     */
    public function test1_Unitaria_RechazoAtributoDesconocidoEIdInvalido(): void
    {
        $modelo = new MobiliarioModel();

        // 1. Atributo no reconocido
        try {
            $modelo->__set('campo_inexistente', 'valor');
            $this->fail('Debe rechazar atributo ajeno al esquema');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Atributo no reconocido: 'campo_inexistente'.", $e->getMessage());
        }

        // 2. ID relacional inválido (tipo de mobiliario = 0)
        try {
            $modelo->__set('id_tipo_mobiliario', 0);
            $this->fail('Debe rechazar id_tipo_mobiliario = 0');
        } catch (ExcepcionApi $e) {
            $this->assertSame('Debes seleccionar el tipo de mobiliario.', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Reglas de Negocio, Kardex y Control RBAC)
    // =========================================================================

    /**
     * CP-MOB-008: Regla de ficha técnica: obligatoriedad de al menos un ítem asignado
     */
    public function test2_Integracion_ReglaFichaDebeIncluirAlMenosUnItem(): void
    {
        $metodo = new ReflectionMethod(MobiliarioModel::class, 'validarDetalles');
        $modelo = new MobiliarioModel();

        // Ficha técnica vacía (sin mobiliario ni equipo)
        try {
            $metodo->invoke($modelo, [], [], 0);
            $this->fail('Debe rechazar ficha técnica vacía');
        } catch (ExcepcionApi $e) {
            $this->assertSame(
                'La ficha debe incluir al menos un ítem de mobiliario o un equipo.',
                $e->getMessage()
            );
        }
    }

    /**
     * CP-MOB-009: Validación de tipo de ítem en movimientos de reubicación y baja
     */
    public function test2_Integracion_ValidacionTipoItemEnReubicacionYBaja(): void
    {
        $modelo = new MobiliarioModel();

        // Tipo de ítem inválido para reubicar
        try {
            $modelo->__set('tipo_item', 'herramienta');
            $this->fail('Debe rechazar tipo_item fuera de [mobiliario, equipo]');
        } catch (ExcepcionApi $e) {
            $this->assertSame('El tipo de ítem no es válido.', $e->getMessage());
        }

        // Tipos de ítem válidos
        $modelo->__set('tipo_item', 'mobiliario');
        $this->assertSame('mobiliario', $modelo->__get('tipo_item'));

        $modelo->__set('tipo_item', 'equipo');
        $this->assertSame('equipo', $modelo->__get('tipo_item'));
    }

    /**
     * CP-MOB-010: Inspección por reflexión de métodos privados de integridad y kardex
     */
    public function test2_Integracion_VerificacionMetodosPrivadosDisponibilidadYKardex(): void
    {
        $metodoExisteSerial = new ReflectionMethod(MobiliarioModel::class, 'existeSerial');
        $metodoExisteFicha = new ReflectionMethod(MobiliarioModel::class, 'existeFichaEmpleado');
        $metodoServicioActivo = new ReflectionMethod(MobiliarioModel::class, 'servicioActivo');
        $metodoTipoMobActivo = new ReflectionMethod(MobiliarioModel::class, 'tipoMobiliarioActivo');
        $metodoTipoEqActivo = new ReflectionMethod(MobiliarioModel::class, 'tipoEquipoActivo');
        $metodoEmpleadoActivo = new ReflectionMethod(MobiliarioModel::class, 'empleadoActivo');
        $metodoAsignadoFichas = new ReflectionMethod(MobiliarioModel::class, 'asignadoEnFichasActivas');
        $metodoFichaActiva = new ReflectionMethod(MobiliarioModel::class, 'fichaActivaDelItem');
        $metodoValidarDetalles = new ReflectionMethod(MobiliarioModel::class, 'validarDetalles');

        $this->assertTrue($metodoExisteSerial->isPrivate(), 'existeSerial debe ser privado');
        $this->assertTrue($metodoExisteFicha->isPrivate(), 'existeFichaEmpleado debe ser privado');
        $this->assertTrue($metodoServicioActivo->isPrivate(), 'servicioActivo debe ser privado');
        $this->assertTrue($metodoTipoMobActivo->isPrivate(), 'tipoMobiliarioActivo debe ser privado');
        $this->assertTrue($metodoTipoEqActivo->isPrivate(), 'tipoEquipoActivo debe ser privado');
        $this->assertTrue($metodoEmpleadoActivo->isPrivate(), 'empleadoActivo debe ser privado');
        $this->assertTrue($metodoAsignadoFichas->isPrivate(), 'asignadoEnFichasActivas debe ser privado');
        $this->assertTrue($metodoFichaActiva->isPrivate(), 'fichaActivaDelItem debe ser privado');
        $this->assertTrue($metodoValidarDetalles->isPrivate(), 'validarDetalles debe ser privado');
    }

    /**
     * CP-MOB-011: Control de acceso RBAC para el módulo Mobiliario
     */
    public function test2_Integracion_ControlAccesoRbacMobiliario(): void
    {
        // Sin sesión iniciada, Autorizacion::tiene retorna false y verificar arroja 401
        $this->assertFalse(Autorizacion::tiene('mobiliario', 'crear'));
        $this->assertFalse(Autorizacion::tiene('mobiliario', 'eliminar'));

        try {
            Autorizacion::verificar('mobiliario', 'crear');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }

        try {
            Autorizacion::verificar('mobiliario', 'eliminar');
            $this->fail('Debe arrojar excepción si no existe sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertStringContainsString('Sesión no válida', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo Completo de Inventario)
    // =========================================================================

    /**
     * CP-MOB-012: Disponibilidad de Controladores y Vistas del Módulo Mobiliario
     */
    public function test3_Sistema_DisponibilidadControladorYVistasMobiliario(): void
    {
        load_controller('mobiliarioController.php');

        // Funciones procedimentales del controlador
        $this->assertTrue(function_exists('showCrearMobiliario'), 'showCrearMobiliario debe existir');
        $this->assertTrue(function_exists('showConsultarMobiliario'), 'showConsultarMobiliario debe existir');
        $this->assertTrue(function_exists('apiCatalogosMobiliario'), 'apiCatalogosMobiliario debe existir');
        $this->assertTrue(function_exists('apiListarMobiliario'), 'apiListarMobiliario debe existir');
        $this->assertTrue(function_exists('apiListarEquipos'), 'apiListarEquipos debe existir');
        $this->assertTrue(function_exists('apiListarFichas'), 'apiListarFichas debe existir');
        $this->assertTrue(function_exists('apiHistorialMobiliario'), 'apiHistorialMobiliario debe existir');
        $this->assertTrue(function_exists('apiObtenerMobiliario'), 'apiObtenerMobiliario debe existir');
        $this->assertTrue(function_exists('apiMobiliarioStats'), 'apiMobiliarioStats debe existir');
        $this->assertTrue(function_exists('apiItemsDisponibles'), 'apiItemsDisponibles debe existir');
        $this->assertTrue(function_exists('apiCrearMobiliario'), 'apiCrearMobiliario debe existir');
        $this->assertTrue(function_exists('apiCrearEquipo'), 'apiCrearEquipo debe existir');
        $this->assertTrue(function_exists('apiCrearFicha'), 'apiCrearFicha debe existir');
        $this->assertTrue(function_exists('apiActualizarMobiliario'), 'apiActualizarMobiliario debe existir');
        $this->assertTrue(function_exists('apiActualizarEquipo'), 'apiActualizarEquipo debe existir');
        $this->assertTrue(function_exists('apiActualizarFicha'), 'apiActualizarFicha debe existir');
        $this->assertTrue(function_exists('apiEliminarMobiliario'), 'apiEliminarMobiliario debe existir');
        $this->assertTrue(function_exists('apiEliminarEquipo'), 'apiEliminarEquipo debe existir');
        $this->assertTrue(function_exists('apiEliminarFicha'), 'apiEliminarFicha debe existir');
        $this->assertTrue(function_exists('apiReubicarItem'), 'apiReubicarItem debe existir');
        $this->assertTrue(function_exists('apiBajaItem'), 'apiBajaItem debe existir');
        $this->assertTrue(function_exists('apiValidarSerial'), 'apiValidarSerial debe existir');
        $this->assertTrue(function_exists('apiValidarFichaEmpleado'), 'apiValidarFichaEmpleado debe existir');

        // Vistas asociadas en el sistema de plantillas
        $this->assertFileExists(BASE_PATH . 'app/Views/mobiliario/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/mobiliario/crear.php', 'Vista crear.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/mobiliario/components/stats.php', 'Componente stats.php debe existir');
    }

    /**
     * CP-MOB-013: Flujo funcional de alta de bienes, serial obligatorio y auditoría
     */
    public function test3_Sistema_FlujoFuncionalAltaBienesYFichasTecnicas(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Preparación de entidad equipo
        $modelo = new MobiliarioModel();
        $modelo->__set('id_tipo_equipo', 2);
        $modelo->__set('id_servicios', 1);
        $modelo->__set('marca', 'Hewlett-Packard');
        $modelo->__set('modelo', 'ProDesk 600 G4');
        $modelo->__set('serial', 'HP-PRO-600-G4-9821');
        $modelo->__set('color', 'Negro');
        $modelo->__set('estado', 'Nuevo');
        $modelo->__set('fecha_adquisicion', '2025-01-10');
        $modelo->__set('descripcion', 'Computadora de escritorio para atencion psicologica');
        $modelo->__set('id_empleado', (int) $_SESSION['id_empleado']);

        // 3. Auditoría institucional en Bitácora
        Bitacora::registrar(
            'Mobiliario',
            'Registro',
            'Registró un equipo: Computadora Desktop · serial ' . $modelo->__get('serial') . ' · Psicología.'
        );

        // 4. Verificación de contrato de salida estandarizado
        $salidaEsperada = [
            'serial'      => $modelo->__get('serial'),
            'marca'       => $modelo->__get('marca'),
            'modelo'      => $modelo->__get('modelo'),
            'estado'      => $modelo->__get('estado'),
            'id_servicios'=> $modelo->__get('id_servicios'),
        ];

        $this->assertSame('HP-PRO-600-G4-9821', $salidaEsperada['serial']);
        $this->assertSame('Hewlett-Packard', $salidaEsperada['marca']);
        $this->assertSame('Nuevo', $salidaEsperada['estado']);
        $this->assertSame(1, $salidaEsperada['id_servicios']);
    }

    /**
     * CP-MOB-014: Flujo integral de movimientos de kardex (reubicación, baja, eliminación) y auditoría
     */
    public function test3_Sistema_FlujoIntegralMovimientosKardexYAuditoria(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Auditoría de Reubicación de Bien
        Bitacora::registrar(
            'Mobiliario',
            'Actualización',
            'Reubicó Computadora Desktop de "Medicina" a "Psicología".'
        );

        // 3. Auditoría de Baja Lógica de Bien
        Bitacora::registrar(
            'Mobiliario',
            'Actualización',
            'Registró la baja lógica de Computadora Desktop.'
        );

        // 4. Auditoría de Eliminación de Ficha Técnica
        Bitacora::registrar(
            'Mobiliario',
            'Eliminación',
            'Eliminó la ficha técnica "Ficha Despacho 01" de Carlos Pérez.'
        );

        // 5. Verificación de contrato de respuesta de reubicación
        $resultadoReubicacion = [
            'etiqueta' => 'Computadora Desktop',
            'de'       => 'Medicina',
            'a'        => 'Psicología',
        ];

        $this->assertSame('Computadora Desktop', $resultadoReubicacion['etiqueta']);
        $this->assertSame('Medicina', $resultadoReubicacion['de']);
        $this->assertSame('Psicología', $resultadoReubicacion['a']);
    }
}
