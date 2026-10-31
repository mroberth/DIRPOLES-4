<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ErrorCodes;
use App\Core\ExcepcionApi;
use App\Models\PermisosModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class PermisosTest extends TestCase
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
    // NIVEL 1: PRUEBAS UNITARIAS (Componentes, Atributos y Validaciones de Lote)
    // =========================================================================

    /**
     * CP-PERM-001: Validación de asignación y persistencia de atributos en PermisosModel
     */
    public function test1_Unitaria_AsignacionYPersistenciaAtributos(): void
    {
        $modelo = new PermisosModel();

        $modelo->__set('Modulo', 'beneficiarios');
        $modelo->__set('Permiso', 'crear');
        $modelo->__set('Rol', 6);
        $modelo->__set('id_tipo_emp', 2);
        $modelo->__set('cambios', [
            ['id_tipo_emp' => 2, 'id_modulo' => 3, 'permisos' => [1, 2]]
        ]);

        $this->assertSame('beneficiarios', $modelo->__get('Modulo'));
        $this->assertSame('crear', $modelo->__get('Permiso'));
        $this->assertSame(6, $modelo->__get('Rol'));
        $this->assertSame(2, $modelo->__get('id_tipo_emp'));
        $this->assertIsArray($modelo->__get('cambios'));
        $this->assertCount(1, $modelo->__get('cambios'));
    }

    /**
     * CP-PERM-002: Validación de requerimiento y formato del lote de cambios de permisos
     */
    public function test1_Unitaria_ValidacionLoteCambiosRequeridos(): void
    {
        $modelo = new PermisosModel();

        // 1. Array vacío sin cambios
        $modelo->__set('cambios', []);
        try {
            $modelo->manejarAccion('guardar_permisos_lote');
            $this->fail('Debe rechazar array de cambios vacío');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('No se recibieron cambios para procesar.', $e->getMessage());
        }

        // 2. Valor no array (ej. string o null)
        $modelo->__set('cambios', 'cambio_invalido');
        try {
            $modelo->manejarAccion('guardar_permisos_lote');
            $this->fail('Debe rechazar cambios que no sean array');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('No se recibieron cambios validos para procesar.', $e->getMessage());
        }

        // 3. Array unidimensional (elementos que no son arrays)
        $modelo->__set('cambios', ['no_es_array']);
        try {
            $modelo->manejarAccion('guardar_permisos_lote');
            $this->fail('Debe rechazar array de cambios con elementos no array');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('No se recibieron cambios validos para procesar.', $e->getMessage());
        }
    }

    /**
     * CP-PERM-003: Validación de payload incompleto en elementos del lote de permisos
     */
    public function test1_Unitaria_ValidacionPayloadIncompletoEnLote(): void
    {
        $modelo = new PermisosModel();

        // Falta la clave 'permisos'
        $modelo->__set('cambios', [
            ['id_tipo_emp' => 1, 'id_modulo' => 2]
        ]);

        try {
            $modelo->manejarAccion('guardar_permisos_lote');
            $this->fail('Debe rechazar elemento de lote sin permisos');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('Datos de cambios incompletos.', $e->getMessage());
        }

        // 'permisos' no es un array (ej. string '1,2')
        $modelo->__set('cambios', [
            ['id_tipo_emp' => 1, 'id_modulo' => 2, 'permisos' => '1,2']
        ]);

        try {
            $modelo->manejarAccion('guardar_permisos_lote');
            $this->fail('Debe rechazar permisos que no sean array');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('Datos de cambios incompletos.', $e->getMessage());
        }
    }

    /**
     * CP-PERM-004: Validación de parámetros requeridos en verificación booleana de permisos
     */
    public function test1_Unitaria_ValidacionParametrosVerificarPermisos(): void
    {
        $modelo = new PermisosModel();

        // 1. Módulo vacío o no string
        $modelo->__set('Modulo', '');
        $modelo->__set('Permiso', 'leer');
        $modelo->__set('Rol', 6);
        $this->assertFalse($modelo->manejarAccion('Verificar'), 'Debe retornar false si Modulo está vacío');

        // 2. Permiso vacío
        $modelo->__set('Modulo', 'beneficiarios');
        $modelo->__set('Permiso', '');
        $modelo->__set('Rol', 6);
        $this->assertFalse($modelo->manejarAccion('Verificar'), 'Debe retornar false si Permiso está vacío');

        // 3. Rol nulo o vacío
        $modelo->__set('Modulo', 'beneficiarios');
        $modelo->__set('Permiso', 'leer');
        $modelo->__set('Rol', null);
        $this->assertFalse($modelo->manejarAccion('Verificar'), 'Debe retornar false si Rol es null');
    }

    /**
     * CP-PERM-005: Encapsulamiento del modelo: rechazo de acciones no reconocidas
     */
    public function test1_Unitaria_RechazoAccionNoValidaEnModelo(): void
    {
        $modelo = new PermisosModel();

        try {
            $modelo->manejarAccion('accion_inexistente');
            $this->fail('Debe rechazar acción ajena al despachador');
        } catch (ExcepcionApi $e) {
            $this->assertSame(500, $e->estado);
            $this->assertSame('Accion no valida: accion_inexistente', $e->getMessage());
        }
    }

    /**
     * CP-PERM-006: Validación de parámetro id_tipo_emp en consulta de permisos por rol
     */
    public function test1_Unitaria_ValidacionParametroConsultaPermisosPorRol(): void
    {
        $modelo = new PermisosModel();

        // id_tipo_emp no asignado
        try {
            $modelo->manejarAccion('obtener_permisos_por_rol');
            $this->fail('Debe rechazar consulta de permisos por rol sin id_tipo_emp');
        } catch (ExcepcionApi $e) {
            $this->assertSame(400, $e->estado);
            $this->assertSame('Falta id_tipo_emp para consultar permisos.', $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Motor Autorización, RBAC y Matriz de Roles)
    // =========================================================================

    /**
     * CP-PERM-007: Integración del motor RBAC: verificación y evaluación booleana (Autorizacion)
     */
    public function test2_Integracion_MotorRbacAutorizacionVerificarYTiene(): void
    {
        // 1. Sin sesión activa: Autorizacion::verificar lanza HTTP 401
        try {
            Autorizacion::verificar('beneficiarios', 'leer');
            $this->fail('Debe lanzar 401 si no hay sesión activa');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
            $this->assertSame(ErrorCodes::NO_AUTENTICADO, $e->codigo);
        }

        // Autorizacion::tiene debe retornar false sin lanzar excepción
        $this->assertFalse(Autorizacion::tiene('beneficiarios', 'leer'));

        // 2. Con sesión activa pero módulo desconocido
        $_SESSION['id_tipo_empleado'] = 6;
        try {
            Autorizacion::verificar('modulo_fantasma_xyz', 'leer');
            $this->fail('Debe lanzar error 500 para módulos no registrados');
        } catch (ExcepcionApi $e) {
            $this->assertSame(500, $e->estado);
            $this->assertStringContainsString('Módulo desconocido', $e->getMessage());
        }

        // 3. Con sesión activa pero permiso desconocido
        try {
            Autorizacion::verificar('beneficiarios', 'permiso_fantasma_xyz');
            $this->fail('Debe lanzar error 500 para permisos no registrados');
        } catch (ExcepcionApi $e) {
            $this->assertSame(500, $e->estado);
            $this->assertStringContainsString('Permiso desconocido', $e->getMessage());
        }
    }

    /**
     * CP-PERM-008: Regla de configuración: lista blanca de roles del sistema en roles_sistema.php
     */
    public function test2_Integracion_FiltroRolesSistemaPermitidos(): void
    {
        $archivoRoles = BASE_PATH . '/app/Config/roles_sistema.php';
        $this->assertFileExists($archivoRoles, 'roles_sistema.php debe existir');

        $rolesPermitidos = require $archivoRoles;
        $this->assertIsArray($rolesPermitidos);

        // Roles clave interactivos esperados:
        // 1: Psicólogo, 2: Médico, 3: Trabajador Social, 4: Orientador,
        // 5: Discapacidad, 6: Administrador, 7: Secretaria, 10: Superusuario, 12: Enfermero
        $rolesEsperados = [1, 2, 3, 4, 5, 6, 7, 10, 12];
        foreach ($rolesEsperados as $rolId) {
            $this->assertContains($rolId, $rolesPermitidos, "El rol ID {$rolId} debe estar en roles_sistema.php");
        }

        // Roles no interactivos (ej. Chofer ID 8) NO deben figurar en la lista
        $this->assertNotContains(8, $rolesPermitidos, 'El rol Chofer (ID 8) no debe figurar en la matriz de permisos');
    }

    /**
     * CP-PERM-009: Introspección por reflexión de métodos privados de PermisosModel
     */
    public function test2_Integracion_VerificacionMetodosPrivadosPermisos(): void
    {
        $metodos = [
            'obtener_modulos',
            'tipos_empleados',
            'obtenerPermisos',
            'obtenerPermisosSidebar',
            'obtener_permisos_por_rol',
            'guardar_permisos_lote',
            'deshacerTransaccionSilenciosamente',
            'mapa_permisos_todos',
            'stats',
            'verificarPermisos',
            'obtenerIdModuloPorNombre',
            'obtenerIdPermisoPorClave',
        ];

        foreach ($metodos as $nombreMetodo) {
            $refMethod = new ReflectionMethod(PermisosModel::class, $nombreMetodo);
            $this->assertTrue(
                $refMethod->isPrivate(),
                "PermisosModel::{$nombreMetodo} debe tener visibilidad privada estricta"
            );
        }
    }

    /**
     * CP-PERM-010: Control de acceso RBAC en puntos de entrada del controlador de permisos
     */
    public function test2_Integracion_ControlAccesoRbacEnControlador(): void
    {
        load_controller('permisosController.php');

        // Usuario autenticado con rol que carece de permisos de administración (ej. Chofer o rol sin acceso)
        $_SESSION['id_tipo_empleado'] = 8; // Chofer

        // 1. showGestionarPermisos exige 'leer' sobre 'permisos'
        try {
            showGestionarPermisos();
            $this->fail('Debe denegar acceso a showGestionarPermisos sin permiso de lectura');
        } catch (ExcepcionApi $e) {
            $this->assertSame(403, $e->estado);
            $this->assertStringContainsString("No tienes permiso de 'leer' sobre el módulo 'permisos'", $e->getMessage());
        }

        // 2. apiGuardarPermisosLote exige 'editar' sobre 'permisos'
        try {
            apiGuardarPermisosLote();
            $this->fail('Debe denegar acceso a apiGuardarPermisosLote sin permiso de edición');
        } catch (ExcepcionApi $e) {
            $this->assertSame(403, $e->estado);
            $this->assertStringContainsString("No tienes permiso de 'editar' sobre el módulo 'permisos'", $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujo Completo con Auditoría)
    // =========================================================================

    /**
     * CP-PERM-011: Disponibilidad de funciones procedimentales, vistas y assets de Permisos
     */
    public function test3_Sistema_DisponibilidadControladorYVistasPermisos(): void
    {
        load_controller('permisosController.php');

        // Funciones procedimentales esperadas
        $funcionesEsperadas = [
            'showGestionarPermisos',
            'apiPermisosMatriz',
            'apiPermisosStats',
            'apiGuardarPermisosLote',
        ];

        foreach ($funcionesEsperadas as $fn) {
            $this->assertTrue(function_exists($fn), "Función procedimental {$fn} debe existir");
        }

        // Vistas en disco
        $this->assertFileExists(BASE_PATH . '/app/Views/permisos/gestionar.php', 'Vista gestionar.php debe existir');
        $this->assertFileExists(BASE_PATH . '/app/Views/permisos/components/stats.php', 'Componente stats.php debe existir');

        // Scripts JS en disco
        $this->assertFileExists(BASE_PATH . '/dist/js/modulos/permisos/stats.js', 'Script stats.js debe existir');
        $this->assertFileExists(BASE_PATH . '/dist/js/modulos/permisos/permisos_manager.js', 'Script permisos_manager.js debe existir');
    }

    /**
     * CP-PERM-012: Flujo funcional de actualización en lote, estadísticas y auditoría en Bitácora
     */
    public function test3_Sistema_FlujoFuncionalGestionPermisosYAuditoria(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Permisos';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Registro de auditoría institucional sobre el módulo Permisos
        Bitacora::registrar(
            'Permisos',
            'Actualización',
            'Actualizó permisos de roles (2 combinaciones).'
        );

        // 3. Verificación de contrato devuelto por estadísticas de permisos
        $modelo = new PermisosModel();
        $stats = $modelo->manejarAccion('stats');

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('permisos_roles', $stats);
        $this->assertArrayHasKey('permisos_total', $stats);
        $this->assertArrayHasKey('permisos_modulo', $stats);
        $this->assertArrayHasKey('permisos_accion', $stats);

        $this->assertIsInt($stats['permisos_roles']);
        $this->assertIsInt($stats['permisos_total']);
        $this->assertIsString($stats['permisos_modulo']);
        $this->assertIsString($stats['permisos_accion']);
    }
}
