<?php
// tests/CitasTest.php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\CitaModel;
use App\Core\ExcepcionApi;
use PDO;
use DateTime;

class CitasTest extends TestCase
{
    private PDO $dbBusiness;
    private array $citasCreadas = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbBusiness = new PDO('mysql:unix_socket=/tmp/mysql.sock;dbname=dirpoles_business;charset=utf8mb4', 'roberth', '');
        $this->dbBusiness->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function tearDown(): void
    {
        if (!empty($this->citasCreadas)) {
            $in = implode(',', array_map('intval', $this->citasCreadas));
            $this->dbBusiness->exec("DELETE FROM cita WHERE id_cita IN ($in)");
            $this->citasCreadas = [];
        }
        parent::tearDown();
    }

    /** Obtiene el próximo lunes hábil en formato Y-m-d (el psicólogo id 3 tiene horario de 08:00 a 13:00) */
    private function getProximoLunesHabil(): string
    {
        $fecha = new DateTime('next monday');
        // Si ya hay citas en esa fecha de pruebas anteriores, sumamos 2 semanas para garantizar fecha despejada
        $fecha->modify('+14 days');
        return $fecha->format('Y-m-d');
    }

    // =========================================================================
    // CASO DE PRUEBA CP-005: AGENDAMIENTO Y SOLAPAMIENTO TEMPORAL DE CITAS
    // =========================================================================

    /**
     * Escenario 5.1: Agendamiento exitoso en día y hora hábil
     */
    public function testAgendamientoCitaExitoso(): void
    {
        $fechaValida = $this->getProximoLunesHabil();

        $modelo = new CitaModel();
        $modelo->__set('tipo_empleado', 'Administrador');
        $modelo->__set('id_usuario', 1);
        $modelo->__set('id_empleado', 3); // Iris Alvarez (Psicóloga activa)
        $modelo->__set('id_beneficiario', 1); // Beneficiario activo
        $modelo->__set('fecha', $fechaValida);
        $modelo->__set('hora', '09:00');

        $resultado = $modelo->manejarAccion('crear');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('id_cita', $resultado);
        $this->assertGreaterThan(0, $resultado['id_cita']);
        $this->assertEquals(1, $resultado['estatus']); // Pendiente

        $this->citasCreadas[] = $resultado['id_cita'];
    }

    /**
     * Escenario 5.2: Validación de fecha pasada rechazada
     */
    public function testAgendamientoCitaFallaFechaPasada(): void
    {
        $modelo = new CitaModel();
        $modelo->__set('tipo_empleado', 'Administrador');

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La fecha de la cita no puede ser pasada.');

        $modelo->__set('fecha', '2022-01-10');
    }

    /**
     * Escenario 5.3: Validación de intervalo de minutos inválido (no en punto ni a media hora)
     */
    public function testAgendamientoCitaFallaMinutosNoEnPuntoNiMedia(): void
    {
        $fechaValida = $this->getProximoLunesHabil();

        $modelo = new CitaModel();
        $modelo->__set('tipo_empleado', 'Administrador');
        $modelo->__set('id_usuario', 1);
        $modelo->__set('id_empleado', 3);
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('fecha', $fechaValida);
        $modelo->__set('hora', '09:15'); // 15 minutos no es múltiplo de 30

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('Las citas deben iniciar en punto o a media hora.');

        $modelo->manejarAccion('crear');
    }

    /**
     * Escenario 5.4: Validación de hora fuera del horario asignado al profesional
     */
    public function testAgendamientoCitaFallaHoraFueraDeHorario(): void
    {
        $fechaValida = $this->getProximoLunesHabil();

        $modelo = new CitaModel();
        $modelo->__set('tipo_empleado', 'Administrador');
        $modelo->__set('id_usuario', 1);
        $modelo->__set('id_empleado', 3); // Su horario los lunes es 08:00 a 13:00
        $modelo->__set('id_beneficiario', 1);
        $modelo->__set('fecha', $fechaValida);
        $modelo->__set('hora', '16:00'); // Fuera de horario

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La hora seleccionada está fuera del horario del psicólogo.');

        $modelo->manejarAccion('crear');
    }

    /**
     * Escenario 5.5: Validación de solapamiento temporal de citas (misma hora y fecha)
     */
    public function testAgendamientoCitaFallaSolapamiento(): void
    {
        $fechaValida = $this->getProximoLunesHabil();

        // 1. Agendar primera cita a las 10:00
        $modelo1 = new CitaModel();
        $modelo1->__set('tipo_empleado', 'Administrador');
        $modelo1->__set('id_usuario', 1);
        $modelo1->__set('id_empleado', 3);
        $modelo1->__set('id_beneficiario', 1);
        $modelo1->__set('fecha', $fechaValida);
        $modelo1->__set('hora', '10:00');
        $res1 = $modelo1->manejarAccion('crear');
        $this->citasCreadas[] = $res1['id_cita'];

        // 2. Intentar agendar segunda cita exactamente en el mismo bloque horario (10:00)
        $modelo2 = new CitaModel();
        $modelo2->__set('tipo_empleado', 'Administrador');
        $modelo2->__set('id_usuario', 1);
        $modelo2->__set('id_empleado', 3);
        $modelo2->__set('id_beneficiario', 1);
        $modelo2->__set('fecha', $fechaValida);
        $modelo2->__set('hora', '10:00'); // Horario solapado

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El psicólogo ya tiene una cita en ese horario.');

        $modelo2->manejarAccion('crear');
    }

    // =========================================================================
    // CASO DE PRUEBA CP-006: TRANSICIÓN DE ESTADOS Y CONTROL DE ALCANCE
    // =========================================================================

    /**
     * Escenario 6.1: Transición exitosa de estado de cita (Pendiente -> Confirmada)
     */
    public function testActualizarEstadoCitaExitoso(): void
    {
        // 1. Crear cita de prueba
        $fechaValida = $this->getProximoLunesHabil();
        $modeloCrear = new CitaModel();
        $modeloCrear->__set('tipo_empleado', 'Administrador');
        $modeloCrear->__set('id_usuario', 1);
        $modeloCrear->__set('id_empleado', 3);
        $modeloCrear->__set('id_beneficiario', 1);
        $modeloCrear->__set('fecha', $fechaValida);
        $modeloCrear->__set('hora', '11:00');
        $res = $modeloCrear->manejarAccion('crear');
        $idCita = $res['id_cita'];
        $this->citasCreadas[] = $idCita;

        // 2. Actualizar estado a 2 (Confirmada)
        $modeloEstado = new CitaModel();
        $modeloEstado->__set('tipo_empleado', 'Administrador');
        $modeloEstado->__set('id_usuario', 1);
        $modeloEstado->__set('id_cita', $idCita);
        $modeloEstado->__set('estatus', 2);

        $resultado = $modeloEstado->manejarAccion('actualizar_estado');

        $this->assertIsArray($resultado);
        $this->assertEquals($idCita, $resultado['id_cita']);
        $this->assertEquals(2, $resultado['estatus']);

        // Verificar en BD
        $stmt = $this->dbBusiness->prepare('SELECT estatus FROM cita WHERE id_cita = :id');
        $stmt->execute([':id' => $idCita]);
        $this->assertEquals(2, (int) $stmt->fetchColumn());
    }

    /**
     * Escenario 6.2: Validación de estado de cita inexistente o inválido
     */
    public function testActualizarEstadoCitaFallaEstadoInvalido(): void
    {
        $modelo = new CitaModel();

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El estado de la cita no es válido.');

        $modelo->__set('estatus', 99); // Catálogo permitido es [1, 2, 3, 4, 5]
    }

    /**
     * Escenario 6.3: Control de alcance: psicólogo no autorizado intentando modificar cita ajena
     */
    public function testActualizarEstadoCitaFallaControlDeAlcance(): void
    {
        // La cita id 1 pertenece al psicólogo id 3 (Iris Alvarez)
        // Simulamos un psicólogo con id_usuario 99 intentando acceder
        $modelo = new CitaModel();
        $modelo->__set('tipo_empleado', 'Psicologo');
        $modelo->__set('id_usuario', 99); // Psicólogo ajeno
        $modelo->__set('id_cita', 1);
        $modelo->__set('estatus', 2);

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La cita no existe o no está disponible para tu usuario.');

        $modelo->manejarAccion('actualizar_estado');
    }
}
