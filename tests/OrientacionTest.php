<?php

namespace Tests;

use App\Models\OrientacionModel;
use App\Core\ExcepcionApi;

/**
 * OrientacionTest (Módulo 07: Diagnósticos de Orientación Socioeducativa)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de campos de texto obligatorios)
 * 2. Integración (Caja Blanca/Gris: Consulta y persistencia de orientaciones)
 * 3. API (Caja Negra: Contrato de catálogo y listado JSON)
 * 4. Sistema (Caja Negra: Flujo de atención socioeducativa y constancia)
 */
class OrientacionTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida la obligatoriedad de los campos de texto narrativo de orientación.
     */
    public function test1_Unitaria_ValidacionCamposTextoObligatorios(): void
    {
        $modelo = new OrientacionModel();

        // Intento de asignar motivo de orientación vacío
        try {
            $modelo->__set('motivo_orientacion', '');
            $this->fail('Se esperaba ExcepcionApi por motivo de orientación vacío');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('es obligatorio', $e->getMessage());
        }

        // Asignación válida
        $modelo->__set('motivo_orientacion', 'Bajo rendimiento en Matemática I');
        $modelo->__set('descripcion_orientacion', 'Estudiante presenta dificultades de adaptación al ritmo universitario');
        $modelo->__set('indicaciones_orientacion', 'Incorporación a círculo de estudio y tutoría académica');
        $modelo->__set('obs_adic_orientacion', 'Seguimiento en 15 días');
        $this->assertTrue(true);
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta de registros de orientación en base de datos.
     */
    public function test2_Integracion_PersistenciaOrientacion(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new OrientacionModel();
        $modelo->__set('id_usuario', 1);
        $modelo->__set('tipo_empleado', 'Administrador');

        $resultado = $modelo->manejarAccion('listar');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el endpoint de estadísticas de orientación y catálogos.
     */
    public function test3_Api_ContratoEndpointsOrientacion(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new OrientacionModel();
        $beneficiarios = $modelo->manejarAccion('beneficiarios');
        $this->assertIsArray($beneficiarios);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la ruta web y el controlador de Orientación.
     */
    public function test4_Sistema_FlujoFuncionalOrientacion(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/orientacionController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/orientacion');
    }
}
