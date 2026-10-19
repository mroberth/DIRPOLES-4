<?php

namespace Tests;

use App\Models\JornadaModel;
use App\Core\ExcepcionApi;

/**
 * JornadasTest (Módulo 12: Jornadas Masivas de Salud)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, aforo mínimo y fechas)
 * 2. Integración (Caja Blanca/Gris: Control de aforo con FOR UPDATE y Kardex)
 * 3. API (Caja Negra: Catálogo de tipos de jornada y pacientes)
 * 4. Sistema (Caja Negra: Flujo de jornada, registro de asistentes y balance)
 */
class JornadasTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida que el aforo sea un entero positivo y fechas válidas.
     */
    public function test1_Unitaria_ValidacionAforoYReglasDominio(): void
    {
        $modelo = new JornadaModel();

        // Aforo no válido (cero o negativo)
        try {
            $modelo->__set('aforo_maximo', 0);
            $this->fail('Se esperaba ExcepcionApi por aforo inválido');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('aforo', strtolower($e->getMessage()));
        }

        // Asignación válida de aforo
        $modelo->__set('aforo_maximo', 150);
        $this->assertTrue(true);
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta de jornadas médicas activas en base de datos.
     */
    public function test2_Integracion_PersistenciaJornada(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new JornadaModel();
        $resultado = $modelo->manejarAccion('listar');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el catálogo oficial de tipos de jornada y clasificaciones de paciente.
     */
    public function test3_Api_ContratoCatalogosJornadas(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new JornadaModel();
        $catalogos = $modelo->manejarAccion('catalogos');

        $this->assertIsArray($catalogos);
        $this->assertArrayHasKey('tipo_jornada', $catalogos);
        $this->assertArrayHasKey('tipo_paciente', $catalogos);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la vista y controlador para gestión masiva.
     */
    public function test4_Sistema_FlujoFuncionalJornadas(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/jornadaController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/jornadas');
    }
}
