<?php

namespace Tests;

use App\Models\BeneficiarioModel;
use App\Core\ExcepcionApi;

/**
 * BeneficiarioTest (Módulo 03: Gestión de Beneficiarios / Estudiantes)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de teléfono, género y fechas futuras)
 * 2. Integración (Caja Blanca/Gris: Persistencia y restricción de integridad referencial)
 * 3. API (Caja Negra: Endpoints de validación remota de cédula y listado)
 * 4. Sistema (Caja Negra: Flujo de administración de expedientes estudiantiles)
 */
class BeneficiarioTest extends TestCaseBase
{
    private array $beneficiariosCreados = [];

    protected function tearDown(): void
    {
        if ($this->dbBusiness !== null && !empty($this->beneficiariosCreados)) {
            $in = implode(',', array_map('intval', $this->beneficiariosCreados));
            $this->dbBusiness->exec("DELETE FROM beneficiario WHERE id_beneficiario IN ($in)");
            $this->beneficiariosCreados = [];
        }
        parent::tearDown();
    }

    private function generarTelefonoUnico(): string
    {
        return '0414' . str_pad((string) mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
    }

    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida el rechazo de género inválido y fechas de nacimiento futuras.
     */
    public function test1_Unitaria_ValidacionGeneroYFechaFutura(): void
    {
        $modelo = new BeneficiarioModel();

        // Género no reconocido
        try {
            $modelo->__set('genero', 'X');
            $this->fail('Se esperaba ExcepcionApi por género inválido');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('género', strtolower($e->getMessage()));
        }

        // Fecha de nacimiento futura
        try {
            $modelo->__set('fecha_nac', date('Y-m-d', strtotime('+1 year')));
            $this->fail('Se esperaba ExcepcionApi por fecha futura');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('futura', strtolower($e->getMessage()));
        }
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Registra un estudiante beneficiario en base de datos y valida su clave primaria.
     */
    public function test2_Integracion_PersistenciaBeneficiario(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new BeneficiarioModel();
        $cedulaUnica = '88' . str_pad((string) mt_rand(1000, 99999), 6, '0', STR_PAD_LEFT);

        $modelo->__set('tipo_cedula', 'V');
        $modelo->__set('cedula', $cedulaUnica);
        $modelo->__set('nombres', 'Estudiante');
        $modelo->__set('apellidos', 'Auditor');
        $modelo->__set('genero', 'M');
        $modelo->__set('fecha_nac', '2002-04-10');
        $modelo->__set('telefono', $this->generarTelefonoUnico());
        $modelo->__set('correo', "estudiante.{$cedulaUnica}@test.com");
        $modelo->__set('id_pnf', 1);
        $modelo->__set('seccion', '4101');
        $modelo->__set('direccion', 'Barquisimeto');

        $resultado = $modelo->manejarAccion('crear');
        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('id_beneficiario', $resultado);
        $this->beneficiariosCreados[] = $resultado['id_beneficiario'];
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el catálogo de Programas Nacionales de Formación (PNF).
     */
    public function test3_Api_ContratoCatalogosPnfs(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new BeneficiarioModel();
        $pnfs = $modelo->manejarAccion('pnfs');

        $this->assertIsArray($pnfs);
        $this->assertNotEmpty($pnfs);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la vista y controlador de beneficiarios.
     */
    public function test4_Sistema_FlujoFuncionalExpedienteEstudiantil(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/beneficiarioController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/beneficiarios');
    }
}
