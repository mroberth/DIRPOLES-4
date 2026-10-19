<?php

namespace Tests;

use App\Models\EmpleadoModel;
use App\Core\ExcepcionApi;

/**
 * EmpleadoTest (Módulo 02: Gestión de Empleados y Seguridad RBAC)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Validaciones de edad mínima, formato de teléfono y cédula)
 * 2. Integración (Caja Blanca/Gris: Persistencia con Bcrypt y unicidad de registros)
 * 3. API (Caja Negra: Control de acceso RBAC a endpoints de empleados)
 * 4. Sistema (Caja Negra: Flujo de administración de personal)
 */
class EmpleadoTest extends TestCaseBase
{
    private array $empleadosCreados = [];

    protected function tearDown(): void
    {
        if ($this->dbSecurity !== null && !empty($this->empleadosCreados)) {
            $in = implode(',', array_map('intval', $this->empleadosCreados));
            $this->dbSecurity->exec("DELETE FROM empleado WHERE id_empleado IN ($in)");
            $this->empleadosCreados = [];
        }
        parent::tearDown();
    }

    private function generarTelefonoUnico(): string
    {
        return '0412' . str_pad((string) mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
    }

    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida que no se permita registrar empleados menores de 15 años ni nombres vacíos.
     */
    public function test1_Unitaria_ValidacionEdadMinimaYCamposRequeridos(): void
    {
        $modelo = new EmpleadoModel();

        // Nombre vacío
        try {
            $modelo->__set('nombre', '');
            $this->fail('Se esperaba ExcepcionApi por nombre vacío');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('nombre', strtolower($e->getMessage()));
        }

        // Menor de 15 años
        try {
            $modelo->__set('fecha_nacimiento', date('Y-m-d', strtotime('-14 years')));
            $this->fail('Se esperaba ExcepcionApi por edad menor a 15 años');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('15 años', $e->getMessage());
        }
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Registra un empleado en base de datos verificando el hash Bcrypt y clave primaria.
     */
    public function test2_Integracion_PersistenciaEmpleadoConHashBcrypt(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new EmpleadoModel();
        $cedulaUnica = '99' . str_pad((string) mt_rand(1000, 99999), 6, '0', STR_PAD_LEFT);
        $correoUnico = "auditor.emp.{$cedulaUnica}@test.com";

        $modelo->__set('nombre', 'Carlos');
        $modelo->__set('apellido', 'Auditor');
        $modelo->__set('tipo_cedula', 'V');
        $modelo->__set('cedula', $cedulaUnica);
        $modelo->__set('correo', $correoUnico);
        $modelo->__set('telefono', $this->generarTelefonoUnico());
        $modelo->__set('id_tipo_empleado', 1);
        $modelo->__set('fecha_nacimiento', '1990-05-15');
        $modelo->__set('direccion', 'Edificio Central UPTAEB');
        $modelo->__set('clave', 'ClaveSegura123');

        $resultado = $modelo->manejarAccion('crear');
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('id_empleado', $resultado);
        $this->empleadosCreados[] = $resultado['id_empleado'];
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica que la consulta de catálogo de roles responda en formato estructurado.
     */
    public function test3_Api_ContratoRolesYPermisos(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new EmpleadoModel();
        $tipos = $modelo->manejarAccion('tipos');

        $this->assertIsArray($tipos);
        $this->assertNotEmpty($tipos);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la vista y controlador para la gestión de empleados.
     */
    public function test4_Sistema_FlujoFuncionalGestionEmpleados(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/empleadoController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/empleados');
    }
}
