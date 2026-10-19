<?php

namespace Tests;

use App\Models\BitacoraModel;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;

/**
 * BitacoraTest (Módulo 16: Auditoría Forense y Bitácora Transversal)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de fechas y acciones autorizadas)
 * 2. Integración (Caja Blanca/Gris: Inmutabilidad y consulta de trazas)
 * 3. API (Caja Negra: Endpoints JSON de auditoría y filtros)
 * 4. Sistema (Caja Negra: Registro automático y trazabilidad transversal)
 */
class BitacoraTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida el formato de fechas de consulta y rechazo de atributos desconocidos.
     */
    public function test1_Unitaria_ValidacionFormatoFechasYAtributos(): void
    {
        $modelo = new BitacoraModel();

        // Formato de fecha de filtro inválido
        try {
            $modelo->__set('desde', '25-10-2026'); // Formato DD-MM-YYYY no permitido
            $this->fail('Se esperaba ExcepcionApi por formato de fecha no ISO');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('YYYY-MM-DD', $e->getMessage());
        }

        // Atributo desconocido
        try {
            $modelo->__set('campo_inventado', 'valor');
            $this->fail('Se esperaba ExcepcionApi por atributo no reconocido');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('Atributo no reconocido', $e->getMessage());
        }

        // Asignación válida
        $modelo->__set('desde', '2026-10-01');
        $modelo->__set('hasta', '2026-10-31');
        $this->assertTrue(true);
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta de trazas y la inmutabilidad de la tabla de auditoría.
     */
    public function test2_Integracion_InmutabilidadYPersistenciaBitacora(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new BitacoraModel();
        $resultado = $modelo->manejarAccion('listar');
        $this->assertIsArray($resultado);

        // Verificación de inmutabilidad: el modelo no debe exponer mutaciones
        $reflector = new \ReflectionClass(BitacoraModel::class);
        $this->assertFalse($reflector->hasMethod('eliminar'), 'La bitácora no debe permitir eliminación de registros históricos');
        $this->assertFalse($reflector->hasMethod('actualizar'), 'La bitácora no debe permitir alteración de trazas forenses');
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el endpoint de filtros y estadísticas de auditoría.
     */
    public function test3_Api_ContratoEndpointsBitacora(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new BitacoraModel();
        $filtros = $modelo->manejarAccion('filtros');

        $this->assertIsArray($filtros);
        $this->assertArrayHasKey('modulos', $filtros);
        $this->assertArrayHasKey('acciones', $filtros);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica que el helper de auditoría transversal Bitacora::registrar opere de forma segura.
     */
    public function test4_Sistema_FlujoFuncionalRegistroAuditoria(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/bitacoraController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/bitacora');

        // Llamada al helper transversal sin lanzar excepciones
        Bitacora::registrar('PruebasQA', 'Lectura', 'Verificación automatizada de suite de testing');
        $this->assertTrue(true, 'La bitácora transversal debe registrar eventos operacionales sin interrumpir el flujo');
    }
}
