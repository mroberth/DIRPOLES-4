<?php
// tests/BeneficiarioTest.php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\BeneficiarioModel;
use App\Core\ExcepcionApi;
use PDO;

class BeneficiarioTest extends TestCase
{
    private PDO $dbBusiness;
    private array $beneficiariosCreados = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbBusiness = new PDO('mysql:unix_socket=/tmp/mysql.sock;dbname=dirpoles_business;charset=utf8mb4', 'roberth', '');
        $this->dbBusiness->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function tearDown(): void
    {
        if (!empty($this->beneficiariosCreados)) {
            $in = implode(',', array_map('intval', $this->beneficiariosCreados));
            $this->dbBusiness->exec("DELETE FROM beneficiario WHERE id_beneficiario IN ($in)");
            $this->beneficiariosCreados = [];
        }
        parent::tearDown();
    }

    private function generarTelefonoUnico(): string
    {
        return '0412' . str_pad((string) mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
    }

    // ===============================================
    // CASO DE PRUEBA CP-003: REGISTRAR BENEFICIARIO
    // ===============================================

    /**
     * Escenario 3.1: Registro exitoso de beneficiario con datos válidos
     */
    public function testRegistrarBeneficiarioExitoso(): void
    {
        $modelo = new BeneficiarioModel();
        $cedula = '88' . str_pad((string) mt_rand(1000, 99999), 6, '0', STR_PAD_LEFT);
        $correo = "ben.{$cedula}@test.com";

        $modelo->__set('id_pnf', 1);
        $modelo->__set('seccion', 'INFO-3101');
        $modelo->__set('nombres', 'Estudiante');
        $modelo->__set('apellidos', 'Beneficiario');
        $modelo->__set('tipo_cedula', 'V');
        $modelo->__set('cedula', $cedula);
        $modelo->__set('fecha_nac', '2002-04-10');
        $modelo->__set('telefono', $this->generarTelefonoUnico());
        $modelo->__set('correo', $correo);
        $modelo->__set('genero', 'M');
        $modelo->__set('direccion', 'Barquisimeto, Sector La Floresta');
        $modelo->__set('estatus', 1);

        $resultado = $modelo->manejarAccion('crear');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('id_beneficiario', $resultado);
        $this->assertGreaterThan(0, $resultado['id_beneficiario']);
        $this->assertEquals('Estudiante', $resultado['nombres']);
        $this->assertEquals($cedula, $resultado['cedula']);

        $this->beneficiariosCreados[] = $resultado['id_beneficiario'];
    }

    /**
     * Escenario 3.2: Validación de cédula duplicada en beneficiarios
     */
    public function testRegistrarBeneficiarioFallaCedulaDuplicada(): void
    {
        // 1. Crear un beneficiario inicial
        $modeloA = new BeneficiarioModel();
        $cedula = '87' . str_pad((string) mt_rand(1000, 99999), 6, '0', STR_PAD_LEFT);
        $correoA = "benA.{$cedula}@test.com";

        $modeloA->__set('id_pnf', 1);
        $modeloA->__set('seccion', 'INFO-1101');
        $modeloA->__set('nombres', 'Beneficiario');
        $modeloA->__set('apellidos', 'Original');
        $modeloA->__set('tipo_cedula', 'V');
        $modeloA->__set('cedula', $cedula);
        $modeloA->__set('fecha_nac', '2001-01-15');
        $modeloA->__set('telefono', $this->generarTelefonoUnico());
        $modeloA->__set('correo', $correoA);
        $modeloA->__set('genero', 'F');
        $resA = $modeloA->manejarAccion('crear');
        $this->beneficiariosCreados[] = $resA['id_beneficiario'];

        // 2. Intentar registrar otro con la MISMA cédula
        $modeloB = new BeneficiarioModel();
        $modeloB->__set('id_pnf', 1);
        $modeloB->__set('seccion', 'INFO-1101');
        $modeloB->__set('nombres', 'Beneficiario');
        $modeloB->__set('apellidos', 'Copia');
        $modeloB->__set('tipo_cedula', 'V');
        $modeloB->__set('cedula', $cedula); // Cédula ya existente
        $modeloB->__set('fecha_nac', '2003-05-20');
        $modeloB->__set('telefono', $this->generarTelefonoUnico());
        $modeloB->__set('correo', "benB.{$cedula}@test.com");
        $modeloB->__set('genero', 'F');

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('Esa cédula ya está registrada en el sistema.');

        $modeloB->manejarAccion('crear');
    }

    /**
     * Escenario 3.3: Validación de género inválido
     */
    public function testRegistrarBeneficiarioFallaGeneroInvalido(): void
    {
        $modelo = new BeneficiarioModel();

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El género debe ser M (Masculino) o F (Femenino).');

        $modelo->__set('genero', 'X'); // No contemplado en catálogo
    }

    /**
     * Escenario 3.4: Validación de formato de teléfono inválido
     */
    public function testRegistrarBeneficiarioFallaTelefonoInvalido(): void
    {
        $modelo = new BeneficiarioModel();

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El teléfono debe empezar por 0412, 0414, 0416, 0422, 0424 o 0426');

        $modelo->__set('telefono', '01234567890');
    }

    /**
     * Escenario 3.5: Validación de fecha de nacimiento futura
     */
    public function testRegistrarBeneficiarioFallaFechaNacimientoFutura(): void
    {
        $modelo = new BeneficiarioModel();
        $fechaFutura = date('Y-m-d', strtotime('+1 year'));

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La fecha de nacimiento no puede ser futura.');

        $modelo->__set('fecha_nac', $fechaFutura);
    }

    // =========================================================================
    // CASO DE PRUEBA CP-004: ELIMINAR Y RESTRICCIÓN DE INTEGRIDAD REFERENCIAL
    // =========================================================================

    /**
     * Escenario 4.1: Intento de eliminar beneficiario con registros asociados en el sistema (Integridad Referencial)
     */
    public function testEliminarBeneficiarioConRegistrosAsociadosBloqueado(): void
    {
        // El beneficiario ID 1 tiene citas registradas en los seeds de dirpoles_business
        $modelo = new BeneficiarioModel();
        $modelo->__set('id_beneficiario', 1);

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('No se puede eliminar: el beneficiario tiene registros asociados');

        $modelo->manejarAccion('eliminar');
    }

    /**
     * Escenario 4.2: Intento de eliminar beneficiario con ID inexistente
     */
    public function testEliminarBeneficiarioInexistente(): void
    {
        $modelo = new BeneficiarioModel();
        $modelo->__set('id_beneficiario', 999999);

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El beneficiario no existe.');

        $modelo->manejarAccion('eliminar');
    }

    /**
     * Escenario 4.3: Eliminación limpia de un beneficiario sin registros asociados
     */
    public function testEliminarBeneficiarioLimpioExitoso(): void
    {
        // 1. Crear un beneficiario sin ningún servicio asociado
        $modeloCrear = new BeneficiarioModel();
        $cedula = '86' . str_pad((string) mt_rand(1000, 99999), 6, '0', STR_PAD_LEFT);

        $modeloCrear->__set('id_pnf', 1);
        $modeloCrear->__set('seccion', 'INFO-LIMPIO');
        $modeloCrear->__set('nombres', 'Para');
        $modeloCrear->__set('apellidos', 'Borrar');
        $modeloCrear->__set('tipo_cedula', 'V');
        $modeloCrear->__set('cedula', $cedula);
        $modeloCrear->__set('fecha_nac', '2000-01-01');
        $modeloCrear->__set('telefono', $this->generarTelefonoUnico());
        $modeloCrear->__set('correo', "borrar.{$cedula}@test.com");
        $modeloCrear->__set('genero', 'M');
        $res = $modeloCrear->manejarAccion('crear');
        $idBeneficiario = $res['id_beneficiario'];

        // 2. Eliminarlo
        $modeloEliminar = new BeneficiarioModel();
        $modeloEliminar->__set('id_beneficiario', $idBeneficiario);
        $eliminado = $modeloEliminar->manejarAccion('eliminar');

        $this->assertTrue($eliminado);

        // 3. Verificar que ya no existe en la base de datos
        $stmt = $this->dbBusiness->prepare('SELECT COUNT(*) FROM beneficiario WHERE id_beneficiario = :id');
        $stmt->execute([':id' => $idBeneficiario]);
        $this->assertEquals(0, (int) $stmt->fetchColumn());
    }
}
