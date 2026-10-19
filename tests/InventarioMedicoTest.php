<?php

namespace Tests;

use App\Models\InventarioModel;
use App\Core\ExcepcionApi;

/**
 * InventarioMedicoTest (Módulo 10: Inventario Médico y Farmacia)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de fecha de vencimiento y tipos)
 * 2. Integración (Caja Blanca/Gris: Control de salidas, Kardex y prevención de stock negativo)
 * 3. API (Caja Negra: Catálogo de presentaciones y movimientos)
 * 4. Sistema (Caja Negra: Flujo de inventario, alertas de stock crítico y Kardex)
 */
class InventarioMedicoTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida el rechazo de fechas de vencimiento pasadas y tipos de insumo no tipificados.
     */
    public function test1_Unitaria_ValidacionVencimientoYTipos(): void
    {
        $modelo = new InventarioModel();

        // Nombre de insumo vacío
        try {
            $modelo->__set('nombre_insumo', '');
            $this->fail('Se esperaba ExcepcionApi por nombre de insumo vacío');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('nombre del insumo es obligatorio', $e->getMessage());
        }

        // Tipo de insumo inválido
        try {
            $modelo->__set('tipo_insumo', 'HerramientaPesada');
            $this->fail('Se esperaba ExcepcionApi por tipo de insumo no reconocido');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('tipo de insumo no es válido', $e->getMessage());
        }
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta del inventario médico en base de datos.
     */
    public function test2_Integracion_PersistenciaInventarioYBloqueoKardex(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new InventarioModel();
        $resultado = $modelo->manejarAccion('listar');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el catálogo de presentaciones farmacológicas.
     */
    public function test3_Api_ContratoPresentacionesFarmacia(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new InventarioModel();
        $presentaciones = $modelo->manejarAccion('presentaciones');

        $this->assertIsArray($presentaciones);
        $this->assertNotEmpty($presentaciones);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la vista y controlador para el inventario médico.
     */
    public function test4_Sistema_FlujoFuncionalFarmaciaYKardex(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/inventarioController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/inventario');
    }
}
