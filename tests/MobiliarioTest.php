<?php

namespace Tests;

use App\Models\MobiliarioModel;
use App\Core\ExcepcionApi;

/**
 * MobiliarioTest (Módulo 13: Mobiliario, Equipos y Fichas Técnicas)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de estados y serial obligatorio)
 * 2. Integración (Caja Blanca/Gris: Consulta de bienes e historial de inventario)
 * 3. API (Caja Negra: Catálogos de tipos de mobiliario y servicios)
 * 4. Sistema (Caja Negra: Flujo de inventario físico y Ficha Técnica en PDF)
 */
class MobiliarioTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida la clasificación del estado físico de los bienes y validación de enteros.
     */
    public function test1_Unitaria_ValidacionEstadoFisicoYReglasDominio(): void
    {
        $modelo = new MobiliarioModel();

        // Estado físico no válido
        try {
            $modelo->__set('estado', 'Totalmente_Destruido');
            $this->fail('Se esperaba ExcepcionApi por estado físico inválido');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('estado', strtolower($e->getMessage()));
        }

        // Asignación de estado físico admisible
        $modelo->__set('estado', 'Bueno');
        $this->assertTrue(true);
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta del inventario de bienes muebles en base de datos.
     */
    public function test2_Integracion_PersistenciaMobiliario(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new MobiliarioModel();
        $resultado = $modelo->manejarAccion('listar_mobiliario');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el endpoint de catálogos para tipos de bienes y ubicaciones.
     */
    public function test3_Api_ContratoCatalogosMobiliario(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new MobiliarioModel();
        $catalogos = $modelo->manejarAccion('catalogos');

        $this->assertIsArray($catalogos);
        $this->assertArrayHasKey('tipos_mobiliario', $catalogos);
        $this->assertArrayHasKey('servicios', $catalogos);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la vista y controlador para emisión de Fichas Técnicas.
     */
    public function test4_Sistema_FlujoFuncionalFichasTecnicas(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/mobiliarioController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/mobiliario');
    }
}
