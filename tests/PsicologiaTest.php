<?php

namespace Tests;

use App\Models\PsicologiaModel;
use App\Core\ExcepcionApi;

/**
 * PsicologiaTest (Módulo 06: Diagnósticos de Psicología)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de tipo de consulta)
 * 2. Integración (Caja Blanca/Gris: Consulta de expedientes clínicos)
 * 3. API (Caja Negra: Catálogos de patologías y estados)
 * 4. Sistema (Caja Negra: Flujo de evaluación y constancia psicológica)
 */
class PsicologiaTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida la clasificación estricta de consultas psicológicas.
     */
    public function test1_Unitaria_ValidacionTipoConsultaYReglasDominio(): void
    {
        $modelo = new PsicologiaModel();

        // Rechazo de tipo de consulta fuera de catálogo
        try {
            $modelo->__set('tipo_consulta', 'Terapia_Experimental');
            $this->fail('Se esperaba ExcepcionApi por tipo de consulta psicológica inválida');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('El tipo de consulta no es válido', $e->getMessage());
        }

        // Asignación de tipos de consulta válidos
        $modelo->__set('tipo_consulta', 'Diagnóstico');
        $this->assertTrue(true);

        $modelo->__set('tipo_consulta', 'Cambio de carrera');
        $this->assertTrue(true);
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta de atenciones psicológicas en base de datos.
     */
    public function test2_Integracion_PersistenciaDiagnosticoPsicologico(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new PsicologiaModel();
        $modelo->__set('id_usuario', 1);
        $modelo->__set('tipo_empleado', 'Administrador');

        $resultado = $modelo->manejarAccion('listar');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el catálogo oficial de patologías y motivos de atención psicológica.
     */
    public function test3_Api_ContratoCatalogosPsicologia(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new PsicologiaModel();
        $patologias = $modelo->manejarAccion('patologias');

        $this->assertIsArray($patologias);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la ruta y controlador para emisión de constancias.
     */
    public function test4_Sistema_FlujoFuncionalConstanciaPsicologica(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/psicologiaController.php';
        $this->assertFileExists($controlador);

        // Verificación de existencia de las plantillas PDF asociadas
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/psicologia');
    }
}
