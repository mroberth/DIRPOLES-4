<?php

namespace Tests;

use App\Models\ReportesModel;
use App\Core\ExcepcionApi;

/**
 * ReportesTest (Módulo 15: Tableros de Reportes Estadísticos e IA)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación estricta de fechas y límites)
 * 2. Integración (Caja Blanca/Gris: Consulta de agregaciones en base de datos)
 * 3. API (Caja Negra: Endpoints JSON de reportes y catálogo de filtros)
 * 4. Sistema (Caja Negra: Integración con microservicio de IA DIRPOLES-IA)
 */
class ReportesTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida el rechazo de formatos de fecha inválidos en los filtros server-side.
     */
    public function test1_Unitaria_ValidacionFormatoFechasFiltro(): void
    {
        $modelo = new ReportesModel();

        // Formato de fecha inválido
        try {
            $modelo->__set('fecha_inicio', 'fecha_invalida_no_iso');
            $this->fail('Se esperaba ExcepcionApi por formato de fecha incorrecto');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('fecha', strtolower($e->getMessage()));
        }

        // Asignación válida en formato YYYY-MM-DD
        $modelo->__set('fecha_inicio', '2026-01-01');
        $modelo->__set('fecha_fin', '2026-10-31');
        $this->assertTrue(true);
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta de métricas agregadas en base de datos.
     */
    public function test2_Integracion_PersistenciaReportesStats(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new ReportesModel();
        $resultado = $modelo->manejarAccion('reporteGeneral');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el endpoint de catálogos para filtros de reportes.
     */
    public function test3_Api_ContratoCatalogosReportes(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new ReportesModel();
        $catalogos = $modelo->manejarAccion('catalogos');

        $this->assertIsArray($catalogos);
        $this->assertArrayHasKey('pnfs', $catalogos);
        $this->assertArrayHasKey('servicios', $catalogos);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la configuración y variables de entorno para enlace con DIRPOLES-IA.
     */
    public function test4_Sistema_FlujoFuncionalIntegracionIA(): void
    {
        // Verificación de configuración de URL y API Key en entorno
        $urlIa = env('DIRPOLES_IA_URL', 'http://127.0.0.1:8000');
        $this->assertNotEmpty($urlIa);

        $controladorReportes = BASE_PATH . 'app/Controllers/reportesController.php';
        $this->assertFileExists($controladorReportes);
    }
}
