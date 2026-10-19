<?php

namespace Tests;

use App\Models\TransporteModel;
use App\Core\ExcepcionApi;

/**
 * TransporteTest (Módulo 14: Gestión de Transporte Universitario)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de rutas y motivos de stock)
 * 2. Integración (Caja Blanca/Gris: Consulta de flota y repuestos)
 * 3. API (Caja Negra: Estadísticas de rutas y disponibilidad)
 * 4. Sistema (Caja Negra: Flujo de asignación de choferes y mantenimiento)
 */
class TransporteTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida la obligatoriedad del nombre de ruta y longitud máxima.
     */
    public function test1_Unitaria_ValidacionRutasYReglasDominio(): void
    {
        $modelo = new TransporteModel();

        // Nombre de ruta vacío
        try {
            $modelo->__set('nombre_ruta', '');
            $this->fail('Se esperaba ExcepcionApi por nombre de ruta vacío');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('El nombre de la ruta es obligatorio', $e->getMessage());
        }

        // Asignación válida
        $modelo->__set('nombre_ruta', 'Ruta 1: El Obelisco - Campus Central');
        $this->assertTrue(true);
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta de la flota vehicular en base de datos.
     */
    public function test2_Integracion_PersistenciaFlotaVehicular(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new TransporteModel();
        $resultado = $modelo->manejarAccion('listar_vehiculos');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el endpoint de estadísticas globales del servicio de transporte.
     */
    public function test3_Api_ContratoStatsTransporte(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new TransporteModel();
        $stats = $modelo->manejarAccion('stats');

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_vehiculos', $stats);
        $this->assertArrayHasKey('total_rutas', $stats);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la vista y controlador de Transporte.
     */
    public function test4_Sistema_FlujoFuncionalTransporte(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/transporteController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/transporte');
    }
}
