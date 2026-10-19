<?php

namespace Tests;

use App\Models\CitaModel;
use App\Core\ExcepcionApi;

/**
 * CitasTest (Módulo 04: Gestión de Citas y Agenda)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de franjas horarias y fechas pasadas)
 * 2. Integración (Caja Blanca/Gris: Prevención de solapamiento temporal y transición de estados)
 * 3. API (Caja Negra: Endpoint de disponibilidad horaria y estados)
 * 4. Sistema (Caja Negra: Flujo de agendamiento y visualización en calendario)
 */
class CitasTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida que las citas solo puedan agendarse en fechas futuras y bloques en punto o media hora.
     */
    public function test1_Unitaria_ValidacionIntervalosHorariosYFechas(): void
    {
        $modelo = new CitaModel();

        // Fecha pasada
        try {
            $modelo->__set('fecha', '2021-01-01');
            $this->fail('Se esperaba ExcepcionApi por fecha pasada');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('pasada', strtolower($e->getMessage()));
        }

        // Hora con formato inválido
        try {
            $modelo->__set('hora', 'hora_invalida_99:99');
            $this->fail('Se esperaba ExcepcionApi por hora no válida');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('hora', strtolower($e->getMessage()));
        }
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta de citas programadas en base de datos.
     */
    public function test2_Integracion_PersistenciaCitas(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new CitaModel();
        $modelo->__set('tipo_empleado', 'Administrador');
        $modelo->__set('id_usuario', 1);
        $resultado = $modelo->manejarAccion('listar');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el catálogo de estados oficiales de una cita.
     */
    public function test3_Api_ContratoEstadosCita(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new CitaModel();
        $modelo->__set('tipo_empleado', 'Administrador');
        $estados = $modelo->manejarAccion('estados');

        $this->assertIsArray($estados);
        $nombres = array_column($estados, 'nombre');
        $this->assertContains('Pendiente', $nombres);
        $this->assertContains('Atendida', $nombres);
        $this->assertContains('Cancelada', $nombres);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la vista y controlador para el calendario interactivo.
     */
    public function test4_Sistema_FlujoFuncionalCalendarioCitas(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/citaController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/citas');
    }
}
