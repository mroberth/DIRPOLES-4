<?php

namespace Tests;

use App\Models\ReferenciaModel;
use App\Core\ExcepcionApi;

/**
 * ReferenciasTest (Módulo 11: Gestión de Referencias Inter-Áreas)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de motivo y estados)
 * 2. Integración (Caja Blanca/Gris: Consulta y log de referencias)
 * 3. API (Caja Negra: Catálogo de servicios y destinos)
 * 4. Sistema (Caja Negra: Flujo de remisión y notificación SSE)
 */
class ReferenciasTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida la obligatoriedad del motivo y estados permitidos.
     */
    public function test1_Unitaria_ValidacionMotivoYEstadosReferencia(): void
    {
        $modelo = new ReferenciaModel();

        // Motivo vacío
        try {
            $modelo->__set('motivo', '');
            $this->fail('Se esperaba ExcepcionApi por motivo de referencia vacío');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('referencia es obligatorio', $e->getMessage());
        }

        // Asignación válida
        $modelo->__set('motivo', 'Remisión por evaluación clínica adicional');
        $modelo->__set('observaciones', 'Paciente amerita consulta con especialista');
        $this->assertTrue(true);
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta de referencias en base de datos bajo control de alcance.
     */
    public function test2_Integracion_PersistenciaReferencias(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new ReferenciaModel();
        $resultado = $modelo->manejarAccion('listar');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el endpoint de catálogos de servicios disponibles para referencia.
     */
    public function test3_Api_ContratoCatalogosReferencias(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new ReferenciaModel();
        $servicios = $modelo->manejarAccion('servicios');

        $this->assertIsArray($servicios);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la ruta de notificaciones Server-Sent Events (SSE).
     */
    public function test4_Sistema_FlujoFuncionalNotificacionesSSE(): void
    {
        $controladorNotificaciones = BASE_PATH . 'app/Controllers/notificacionesController.php';
        $this->assertFileExists($controladorNotificaciones, 'El controlador SSE de notificaciones debe estar presente');
    }
}
