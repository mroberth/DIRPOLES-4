<?php
// tests/EmpleadoTest.php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\EmpleadoModel;
use App\Core\ExcepcionApi;
use PDO;

class EmpleadoTest extends TestCase
{
    private PDO $dbSecurity;
    private array $empleadosCreados = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbSecurity = new PDO('mysql:unix_socket=/tmp/mysql.sock;dbname=dirpoles_security;charset=utf8mb4', 'roberth', '');
        $this->dbSecurity->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function tearDown(): void
    {
        if (!empty($this->empleadosCreados)) {
            $in = implode(',', array_map('intval', $this->empleadosCreados));
            $this->dbSecurity->exec("DELETE FROM empleado WHERE id_empleado IN ($in)");
            $this->empleadosCreados = [];
        }
        parent::tearDown();
    }

    private function generarTelefonoUnico(): string
    {
        return '0412' . str_pad((string) mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
    }

    // ==========================================
    // CASO DE PRUEBA CP-001: REGISTRAR EMPLEADO
    // ==========================================

    /**
     * Escenario 1.1: Registro exitoso con datos válidos
     */
    public function testRegistrarEmpleadoExitosoConDatosValidos(): void
    {
        $modelo = new EmpleadoModel();
        $cedulaUnica = '99' . str_pad((string) mt_rand(1000, 99999), 6, '0', STR_PAD_LEFT);
        $correoUnico = "auditor.emp.{$cedulaUnica}@test.com";

        $modelo->__set('nombre', 'Carlos');
        $modelo->__set('apellido', 'Auditor');
        $modelo->__set('tipo_cedula', 'V');
        $modelo->__set('cedula', $cedulaUnica);
        $modelo->__set('correo', $correoUnico);
        $modelo->__set('telefono', $this->generarTelefonoUnico());
        $modelo->__set('id_tipo_empleado', 1); // Administrador / Médico
        $modelo->__set('fecha_nacimiento', '1990-05-15');
        $modelo->__set('direccion', 'Edificio Central UPTAEB');
        $modelo->__set('clave', 'ClaveSegura123');
        $modelo->__set('estatus', 1);

        $resultado = $modelo->manejarAccion('crear');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('id_empleado', $resultado);
        $this->assertGreaterThan(0, $resultado['id_empleado']);
        $this->assertEquals('Carlos', $resultado['nombre']);
        $this->assertEquals($correoUnico, $resultado['correo']);

        $this->empleadosCreados[] = $resultado['id_empleado'];
    }

    /**
     * Escenario 1.2: Validación de campo obligatorio vacío (nombre)
     */
    public function testRegistrarEmpleadoFallaCampoNombreVacio(): void
    {
        $modelo = new EmpleadoModel();

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El nombre es obligatorio.');

        $modelo->__set('nombre', '   ');
    }

    /**
     * Escenario 1.3: Validación de cédula duplicada
     */
    public function testRegistrarEmpleadoFallaCedulaDuplicada(): void
    {
        // 1. Registramos primero un empleado base
        $modeloBase = new EmpleadoModel();
        $cedulaDuplicada = '98' . str_pad((string) mt_rand(1000, 99999), 6, '0', STR_PAD_LEFT);
        $correoBase = "base.{$cedulaDuplicada}@test.com";

        $modeloBase->__set('nombre', 'Empleado');
        $modeloBase->__set('apellido', 'Original');
        $modeloBase->__set('tipo_cedula', 'V');
        $modeloBase->__set('cedula', $cedulaDuplicada);
        $modeloBase->__set('correo', $correoBase);
        $modeloBase->__set('telefono', $this->generarTelefonoUnico());
        $modeloBase->__set('id_tipo_empleado', 1);
        $modeloBase->__set('fecha_nacimiento', '1992-03-10');
        $modeloBase->__set('direccion', 'Barquisimeto');
        $modeloBase->__set('clave', 'ClaveOriginal123');
        $resBase = $modeloBase->manejarAccion('crear');
        $this->empleadosCreados[] = $resBase['id_empleado'];

        // 2. Intentamos registrar otro con la MISMA cédula
        $modeloNuevo = new EmpleadoModel();
        $modeloNuevo->__set('nombre', 'Otro');
        $modeloNuevo->__set('apellido', 'Copia');
        $modeloNuevo->__set('tipo_cedula', 'V');
        $modeloNuevo->__set('cedula', $cedulaDuplicada); // Cédula ya existente
        $modeloNuevo->__set('correo', "otro.correo.{$cedulaDuplicada}@test.com");
        $modeloNuevo->__set('telefono', $this->generarTelefonoUnico());
        $modeloNuevo->__set('id_tipo_empleado', 1);
        $modeloNuevo->__set('fecha_nacimiento', '1995-01-01');
        $modeloNuevo->__set('direccion', 'Barquisimeto');
        $modeloNuevo->__set('clave', 'ClaveSegura123');

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('Esa cédula ya está registrada en el sistema.');

        $modeloNuevo->manejarAccion('crear');
    }

    /**
     * Escenario 1.4: Validación de formato de teléfono venezolano inválido
     */
    public function testRegistrarEmpleadoFallaTelefonoInvalido(): void
    {
        $modelo = new EmpleadoModel();

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El teléfono debe empezar por 0412, 0414, 0416, 0422, 0424 o 0426');

        $modelo->__set('telefono', '02511234567'); // Código fijo no permitido
    }

    /**
     * Escenario 1.5: Validación de regla de negocio: edad mínima menor a 15 años
     */
    public function testRegistrarEmpleadoFallaEdadMenorA15Anos(): void
    {
        $modelo = new EmpleadoModel();
        $fechaMenor = date('Y-m-d', strtotime('-12 years'));

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El empleado debe tener al menos 15 años.');

        $modelo->__set('fecha_nacimiento', $fechaMenor);
    }

    // ==========================================
    // CASO DE PRUEBA CP-002: ACTUALIZAR EMPLEADO
    // ==========================================

    /**
     * Escenario 2.1: Modificación correcta de empleado existente
     */
    public function testActualizarEmpleadoExitoso(): void
    {
        // 1. Crear empleado a modificar
        $modeloCrear = new EmpleadoModel();
        $cedula = '97' . str_pad((string) mt_rand(1000, 99999), 6, '0', STR_PAD_LEFT);
        $modeloCrear->__set('nombre', 'NombreInicial');
        $modeloCrear->__set('apellido', 'ApellidoInicial');
        $modeloCrear->__set('tipo_cedula', 'V');
        $modeloCrear->__set('cedula', $cedula);
        $modeloCrear->__set('correo', "inicial.{$cedula}@test.com");
        $tel = $this->generarTelefonoUnico();
        $modeloCrear->__set('telefono', $tel);
        $modeloCrear->__set('id_tipo_empleado', 1);
        $modeloCrear->__set('fecha_nacimiento', '1988-08-20');
        $modeloCrear->__set('direccion', 'Direccion Anterior');
        $modeloCrear->__set('clave', 'ClaveSegura123');
        $res = $modeloCrear->manejarAccion('crear');
        $idEmpleado = $res['id_empleado'];
        $this->empleadosCreados[] = $idEmpleado;

        // 2. Actualizar datos
        $modeloEdit = new EmpleadoModel();
        $modeloEdit->__set('id_empleado', $idEmpleado);
        $modeloEdit->__set('nombre', 'NombreModificado');
        $modeloEdit->__set('apellido', 'ApellidoModificado');
        $modeloEdit->__set('tipo_cedula', 'V');
        $modeloEdit->__set('cedula', $cedula); // Conserva cédula
        $modeloEdit->__set('correo', "modificado.{$cedula}@test.com");
        $modeloEdit->__set('telefono', $tel);
        $modeloEdit->__set('id_tipo_empleado', 1);
        $modeloEdit->__set('fecha_nacimiento', '1988-08-20');
        $modeloEdit->__set('direccion', 'Nueva Direccion Actualizada');
        $modeloEdit->__set('estatus', 1);

        $resUpdate = $modeloEdit->manejarAccion('actualizar');

        $this->assertEquals($idEmpleado, $resUpdate['id_empleado']);
        $this->assertEquals('NombreModificado', $resUpdate['nombre']);

        // Verificar en BD
        $stmt = $this->dbSecurity->prepare('SELECT nombre, correo, direccion FROM empleado WHERE id_empleado = :id');
        $stmt->execute([':id' => $idEmpleado]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('NombreModificado', $fila['nombre']);
        $this->assertEquals("modificado.{$cedula}@test.com", $fila['correo']);
        $this->assertEquals('Nueva Direccion Actualizada', $fila['direccion']);
    }

    /**
     * Escenario 2.2: Intento de actualización con ID inexistente
     */
    public function testActualizarEmpleadoFallaIdInexistente(): void
    {
        $modelo = new EmpleadoModel();
        $modelo->__set('id_empleado', 999999);
        $modelo->__set('nombre', 'Inexistente');
        $modelo->__set('apellido', 'User');
        $modelo->__set('tipo_cedula', 'V');
        $modelo->__set('cedula', '99123456');
        $modelo->__set('correo', 'inexistente@test.com');
        $modelo->__set('telefono', '04121234567');
        $modelo->__set('id_tipo_empleado', 1);
        $modelo->__set('fecha_nacimiento', '1990-01-01');
        $modelo->__set('direccion', 'Calle Falsa 123');

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El empleado no existe.');

        $modelo->manejarAccion('actualizar');
    }

    /**
     * Escenario 2.3: Intento de actualizar asignando cédula de otro empleado ya registrado
     */
    public function testActualizarEmpleadoFallaCedulaDuplicadaConOtroEmpleado(): void
    {
        // 1. Crear Empleado A
        $modeloA = new EmpleadoModel();
        $cedulaA = '96' . str_pad((string) mt_rand(1000, 99999), 6, '0', STR_PAD_LEFT);
        $modeloA->__set('nombre', 'EmpleadoA');
        $modeloA->__set('apellido', 'Prueba');
        $modeloA->__set('tipo_cedula', 'V');
        $modeloA->__set('cedula', $cedulaA);
        $modeloA->__set('correo', "empA.{$cedulaA}@test.com");
        $telA = $this->generarTelefonoUnico();
        $modeloA->__set('telefono', $telA);
        $modeloA->__set('id_tipo_empleado', 1);
        $modeloA->__set('fecha_nacimiento', '1985-02-14');
        $modeloA->__set('clave', 'ClaveSegura123');
        $resA = $modeloA->manejarAccion('crear');
        $this->empleadosCreados[] = $resA['id_empleado'];

        // 2. Crear Empleado B
        $modeloB = new EmpleadoModel();
        $cedulaB = '95' . str_pad((string) mt_rand(1000, 99999), 6, '0', STR_PAD_LEFT);
        $modeloB->__set('nombre', 'EmpleadoB');
        $modeloB->__set('apellido', 'Prueba');
        $modeloB->__set('tipo_cedula', 'V');
        $modeloB->__set('cedula', $cedulaB);
        $modeloB->__set('correo', "empB.{$cedulaB}@test.com");
        $telB = $this->generarTelefonoUnico();
        $modeloB->__set('telefono', $telB);
        $modeloB->__set('id_tipo_empleado', 1);
        $modeloB->__set('fecha_nacimiento', '1987-03-20');
        $modeloB->__set('clave', 'ClaveSegura123');
        $resB = $modeloB->manejarAccion('crear');
        $this->empleadosCreados[] = $resB['id_empleado'];

        // 3. Intentar actualizar Empleado B poniéndole la cédula de Empleado A
        $modeloBEdit = new EmpleadoModel();
        $modeloBEdit->__set('id_empleado', $resB['id_empleado']);
        $modeloBEdit->__set('nombre', 'EmpleadoB');
        $modeloBEdit->__set('apellido', 'Prueba');
        $modeloBEdit->__set('tipo_cedula', 'V');
        $modeloBEdit->__set('cedula', $cedulaA); // Intento de usurpación de cédula
        $modeloBEdit->__set('correo', "empB.{$cedulaB}@test.com");
        $modeloBEdit->__set('telefono', $telB);
        $modeloBEdit->__set('id_tipo_empleado', 1);
        $modeloBEdit->__set('fecha_nacimiento', '1987-03-20');

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('Esa cédula ya está registrada en el sistema.');

        $modeloBEdit->manejarAccion('actualizar');
    }

    /**
     * Escenario 2.4: Intento de asignación de atributo inválido o no reconocido
     */
    public function testActualizarEmpleadoFallaAtributoNoReconocido(): void
    {
        $modelo = new EmpleadoModel();

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage("Atributo no reconocido: 'campo_inexistente'.");

        $modelo->__set('campo_inexistente', 'valor');
    }
}
