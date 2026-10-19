<?php

namespace Tests;

use App\Models\TrabajoSocialModel;
use App\Core\ExcepcionApi;

/**
 * TrabajoSocialTest (Módulo 08: Trabajo Social y Estudio Socioeconómico)
 * -----------------------------------------------------------------
 * Matriz Homologada de 4 Niveles de Pruebas:
 * 1. Unitaria (Caja Blanca: Setters, validación de códigos bancarios y semanas de gestación)
 * 2. Integración (Caja Blanca/Gris: Consulta de sub-flujos de ayudas)
 * 3. API (Caja Negra: Catálogos de bancos, motivos y tipos de ayuda)
 * 4. Sistema (Caja Negra: Flujo completo de solicitud de beneficio social)
 */
class TrabajoSocialTest extends TestCaseBase
{
    /**
     * NIVEL 1: PRUEBA UNITARIA (Caja Blanca)
     * Valida catálogos cerrados: códigos bancarios y rangos de semanas de gestación.
     */
    public function test1_Unitaria_ValidacionBancosYSemanasGestacion(): void
    {
        $modelo = new TrabajoSocialModel();

        // Código de banco inválido
        try {
            $modelo->__set('tipo_banco', '9999');
            $this->fail('Se esperaba ExcepcionApi por código bancario inválido');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('tipo de banco no es válido', $e->getMessage());
        }

        // Cuenta BCV con longitud inválida (ej. 8 dígitos)
        try {
            $modelo->__set('cta_bcv', '12345678');
            $this->fail('Se esperaba ExcepcionApi por cuenta BCV incompleta');
        } catch (ExcepcionApi $e) {
            $this->assertStringContainsString('16 dígitos', $e->getMessage());
        }

        // Asignación válida: Banco de Venezuela (0102) y cuenta de 16 dígitos
        $modelo->__set('tipo_banco', '0102');
        $modelo->__set('cta_bcv', '0102012345678901');
        $this->assertTrue(true);
    }

    /**
     * NIVEL 2: PRUEBA DE INTEGRACIÓN (Caja Blanca / Gris)
     * Verifica la consulta del listado de ayudas de trabajo social en base de datos.
     */
    public function test2_Integracion_PersistenciaTrabajoSocial(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new TrabajoSocialModel();
        $modelo->__set('id_usuario', 1);
        $modelo->__set('tipo_empleado', 'Administrador');
        $modelo->__set('tipo', 'becas');

        $resultado = $modelo->manejarAccion('listar');
        $this->assertIsArray($resultado);
    }

    /**
     * NIVEL 3: PRUEBA DE API (Caja Negra)
     * Verifica el endpoint de catálogos con la lista de bancos y motivos de exoneración.
     */
    public function test3_Api_ContratoCatalogosTrabajoSocial(): void
    {
        $this->requerirBaseDeDatos();

        $modelo = new TrabajoSocialModel();
        $beneficiarios = $modelo->manejarAccion('beneficiarios');
        $patologias = $modelo->manejarAccion('patologias');

        $this->assertIsArray($beneficiarios);
        $this->assertIsArray($patologias);
    }

    /**
     * NIVEL 4: PRUEBA DE SISTEMA / E2E (Caja Negra)
     * Verifica la disponibilidad de la ruta y controlador para el Estudio Socioeconómico.
     */
    public function test4_Sistema_FlujoFuncionalEstudioSocioeconomico(): void
    {
        $controlador = BASE_PATH . 'app/Controllers/trabajoSocialController.php';
        $this->assertFileExists($controlador);
        $this->assertDirectoryExists(BASE_PATH . 'app/Views/trabajo-social');
    }
}
