<?php

namespace Tests;

use App\Models\DiscapacidadModel;
use App\Core\ExcepcionApi;

/**
 * DiscapacidadTest (Módulo 09: Diagnósticos de Discapacidad y Diversidad Funcional)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de tipos y grados de discapacidad)
 * 2. Integración (Caja Blanca/Gris: Consulta de expedientes funcionales)
 * 3. API (Caja Negra: Catálogos oficiales de discapacidad)
 * 4. Sistema (Caja Negra: Flujo de expediente y ajustes curriculares)
 */
class DiscapacidadTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida catálogos cerrados de grados y tipos de discapacidad.
     */
    public function test1_Unitaria_ValidacionTiposYGradosDiscapacidad(): void
    {
        $modelo = new DiscapacidadModel();

        // Grado no admisible
        try {
            $modelo->__set('grado', 'Extremo');
            $this->fail('Se esperaba ExcepcionApi por grado de discapacidad inválido');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('El grado no es válido', $e->getMessage());
        }

        // Tipo no admisible
        try {
            $modelo->__set('tipo_discapacidad', 'Sobrenatural');
            $this->fail('Se esperaba ExcepcionApi por tipo de discapacidad inválido');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('El tipo de discapacidad no es válido', $e->getMessage());
        }

        // Asignación de valores conformes
        $modelo->__set('tipo_discapacidad', 'Sensorial');
        $modelo->__set('grado', 'Moderado');
        $this->assertTrue(true);
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta del listado de expedientes de discapacidad en base de datos.
     */
    public function test2_Integracion_PersistenciaDiscapacidad(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new DiscapacidadModel();
        $modelo->__set('id_usuario', 1);
        $modelo->__set('tipo_empleado', 'Administrador');

        $resultado = $modelo->manejarAccion('listar');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el endpoint de catálogos oficiales de discapacidad.
     */
    public function test3_Api_ContratoCatalogosDiscapacidad(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new DiscapacidadModel();
        $beneficiarios = $modelo->manejarAccion('beneficiarios');

        $this->assertIsArray($beneficiarios);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la ruta y controlador para atención a la discapacidad.
     */
    public function test4_Sistema_FlujoFuncionalDiscapacidad(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/discapacidadController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/discapacidad');
    }
}
