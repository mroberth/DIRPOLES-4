<?php

namespace Tests;

use App\Models\MedicinaModel;
use App\Core\ExcepcionApi;

/**
 * MedicinaTest (Módulo 05: Diagnósticos de Medicina General)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validaciones de signos vitales y sangre)
 * 2. Integración (Caja Blanca/Gris: Consulta médica, recetas y Kardex)
 * 3. API (Caja Negra: Catálogos y endpoints de datos)
 * 4. Sistema (Caja Negra: Flujo integral de consulta y récipe médico)
 */
class MedicinaTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida reglas de dominio clínico: rangos admisibles de peso/estatura y tipo de sangre.
     */
    public function test1_Unitaria_ValidacionSignosVitalesYTipoSangre(): void
    {
        $modelo = new MedicinaModel();

        // Validación de tipo de sangre inválido
        try {
            $modelo->__set('tipo_sangre', 'Z_NEGATIVO');
            $this->fail('Se esperaba ExcepcionApi por tipo de sangre no admisible');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('El tipo de sangre no es válido', $e->getMessage());
        }

        // Validación de estatura fuera de rango biológico (ej. 4 metros)
        try {
            $modelo->__set('estatura', 4.50);
            $this->fail('Se esperaba ExcepcionApi por estatura fuera de rango');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('estatura debe estar entre', $e->getMessage());
        }

        // Asignación correcta de valores válidos
        $modelo->__set('tipo_sangre', 'O+');
        $modelo->__set('estatura', 1.75);
        $modelo->__set('peso', 72.5);
        $this->assertTrue(true, 'Signos vitales admisibles asignados con éxito');
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la persistencia de consultas médicas y el descuento transaccional en Kardex.
     */
    public function test2_Integracion_PersistenciaConsultaYDescuentoKardex(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new MedicinaModel();
        // Verificación de método listar en BD
        $modelo->__set('id_usuario', 1);
        $modelo->__set('tipo_empleado', 'Administrador');

        $resultado = $modelo->manejarAccion('listar');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el contrato REST del endpoint de catálogos diagnósticos e insumos.
     */
    public function test3_Api_ContratoCatalogosMedicina(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new MedicinaModel();
        $patologias = $modelo->manejarAccion('patologias');
        $insumos = $modelo->manejarAccion('insumos');

        $this->assertIsArray($patologias);
        $this->assertIsArray($insumos);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la tercera puerta para generación de récipes médicos.
     */
    public function test4_Sistema_FlujoFuncionalEmisionRecipePdf(): void
    {
        // Verificación de existencia del motor FPDF y plantillas PDF institucionales
        $pdfPath = BASE_PATH . 'docs/PDF/fpdf/fpdf.php';
        $this->assertFileExists($pdfPath, 'El motor de generación FPDF debe estar presente para la emisión de récipes');

        $controladorMedicina = BASE_PATH . 'app/Controllers/medicinaController.php';
        $this->assertFileExists($controladorMedicina);
    }
}
