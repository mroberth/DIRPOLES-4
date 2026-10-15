<?php
// tests/InventarioMedicoTest.php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\InventarioModel;
use App\Core\ExcepcionApi;
use PDO;

class InventarioMedicoTest extends TestCase
{
    private PDO $dbBusiness;
    private array $insumosCreados = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbBusiness = new PDO('mysql:unix_socket=/tmp/mysql.sock;dbname=dirpoles_business;charset=utf8mb4', 'roberth', '');
        $this->dbBusiness->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function tearDown(): void
    {
        if (!empty($this->insumosCreados)) {
            $in = implode(',', array_map('intval', $this->insumosCreados));
            $this->dbBusiness->exec("DELETE FROM inventario_medico WHERE id_insumo IN ($in)");
            $this->dbBusiness->exec("DELETE FROM insumos WHERE id_insumo IN ($in)");
            $this->insumosCreados = [];
        }
        parent::tearDown();
    }

    // =========================================================================
    // CASO DE PRUEBA CP-007: REGISTRO DE INSUMO MÉDICO Y CONTROL DE ALTA EN KARDEX
    // =========================================================================

    /**
     * Escenario 7.1: Registro exitoso de insumo (nace con cantidad 0, estatus Agotado y asiento Registro en kardex)
     */
    public function testRegistrarInsumoExitoso(): void
    {
        $modelo = new InventarioModel();
        $sufijo = mt_rand(1000, 9999);
        $nombreInsumo = "Amoxicilina {$sufijo}mg";
        $fechaFutura = date('Y-m-d', strtotime('+1 year'));

        $modelo->__set('nombre_insumo', $nombreInsumo);
        $modelo->__set('tipo_insumo', 'Medicamento');
        $modelo->__set('id_presentacion', 1); // Pastillas
        $modelo->__set('fecha_vencimiento', $fechaFutura);
        $modelo->__set('descripcion', 'Antibiotico de amplio espectro lote QA');
        $modelo->__set('id_empleado', 1);

        $resultado = $modelo->manejarAccion('crear');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('id_insumo', $resultado);
        $this->assertGreaterThan(0, $resultado['id_insumo']);
        $idInsumo = $resultado['id_insumo'];
        $this->insumosCreados[] = $idInsumo;

        // 1. Verificar tabla insumos: stock 0 y estatus Agotado
        $stmt = $this->dbBusiness->prepare('SELECT cantidad, estatus FROM insumos WHERE id_insumo = :id');
        $stmt->execute([':id' => $idInsumo]);
        $insumo = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(0, (int) $insumo['cantidad']);
        $this->assertEquals('Agotado', $insumo['estatus']);

        // 2. Verificar tabla kardex inventario_medico: movimiento Registro con cantidad 0
        $stmtKardex = $this->dbBusiness->prepare(
            'SELECT tipo_movimiento, cantidad FROM inventario_medico WHERE id_insumo = :id'
        );
        $stmtKardex->execute([':id' => $idInsumo]);
        $kardex = $stmtKardex->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('Registro', $kardex['tipo_movimiento']);
        $this->assertEquals(0, (int) $kardex['cantidad']);
    }

    /**
     * Escenario 7.2: Validación de fecha de vencimiento no futura (insumo vencido rechazado al alta)
     */
    public function testRegistrarInsumoFallaFechaVencimientoPasada(): void
    {
        $modelo = new InventarioModel();
        $modelo->__set('nombre_insumo', 'Ibuprofeno Vencido');
        $modelo->__set('tipo_insumo', 'Medicamento');
        $modelo->__set('id_presentacion', 1);
        $modelo->__set('fecha_vencimiento', '2023-01-01'); // Fecha pasada
        $modelo->__set('descripcion', 'Lote caducado');
        $modelo->__set('id_empleado', 1);

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('La fecha de vencimiento no puede ser una fecha pasada.');

        $modelo->manejarAccion('crear');
    }

    /**
     * Escenario 7.3: Validación de tipo de insumo no válido
     */
    public function testRegistrarInsumoFallaTipoInvalido(): void
    {
        $modelo = new InventarioModel();

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El tipo de insumo no es válido.');

        $modelo->__set('tipo_insumo', 'Comestible'); // Catálogo es Medicamento, Material, Quirúrgico
    }

    /**
     * Escenario 7.4: Validación de insumo duplicado (misma presentación, nombre, tipo y fecha)
     */
    public function testRegistrarInsumoFallaDuplicado(): void
    {
        $sufijo = mt_rand(1000, 9999);
        $nombre = "Paracetamol {$sufijo}mg";
        $fecha = date('Y-m-d', strtotime('+2 years'));

        // 1. Crear insumo inicial
        $modelo1 = new InventarioModel();
        $modelo1->__set('nombre_insumo', $nombre);
        $modelo1->__set('tipo_insumo', 'Medicamento');
        $modelo1->__set('id_presentacion', 1);
        $modelo1->__set('fecha_vencimiento', $fecha);
        $modelo1->__set('descripcion', 'Analgesico general');
        $modelo1->__set('id_empleado', 1);
        $res1 = $modelo1->manejarAccion('crear');
        $this->insumosCreados[] = $res1['id_insumo'];

        // 2. Intentar registrar el mismo insumo con idéntica presentación, tipo y fecha
        $modelo2 = new InventarioModel();
        $modelo2->__set('nombre_insumo', $nombre);
        $modelo2->__set('tipo_insumo', 'Medicamento');
        $modelo2->__set('id_presentacion', 1);
        $modelo2->__set('fecha_vencimiento', $fecha);
        $modelo2->__set('descripcion', 'Segundo intento duplicado');
        $modelo2->__set('id_empleado', 1);

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('Ese insumo ya está registrado en el inventario médico.');

        $modelo2->manejarAccion('crear');
    }

    // =========================================================================
    // CASO DE PRUEBA CP-008: SALIDA DE INSUMOS, KARDEX Y PREVENCIÓN DE STOCK NEGATIVO
    // =========================================================================

    /**
     * Escenario 8.1: Salida válida de stock con existencia suficiente y asiento en kardex
     */
    public function testSalidaInsumoValida(): void
    {
        // 1. Crear insumo y darle entrada de 20 unidades
        $sufijo = mt_rand(1000, 9999);
        $modeloCrear = new InventarioModel();
        $modeloCrear->__set('nombre_insumo', "Gasa Esteril {$sufijo}");
        $modeloCrear->__set('tipo_insumo', 'Material');
        $modeloCrear->__set('id_presentacion', 5);
        $modeloCrear->__set('fecha_vencimiento', date('Y-m-d', strtotime('+1 year')));
        $modeloCrear->__set('descripcion', 'Material de cura');
        $modeloCrear->__set('id_empleado', 1);
        $res = $modeloCrear->manejarAccion('crear');
        $idInsumo = $res['id_insumo'];
        $this->insumosCreados[] = $idInsumo;

        $modeloEntrada = new InventarioModel();
        $modeloEntrada->__set('id_insumo', $idInsumo);
        $modeloEntrada->__set('cantidad', 20);
        $modeloEntrada->__set('descripcion', 'Ingreso inicial por donacion');
        $modeloEntrada->__set('id_empleado', 1);
        $modeloEntrada->manejarAccion('entrada');

        // 2. Registrar salida de 5 unidades con motivo Uso Interno
        $modeloSalida = new InventarioModel();
        $modeloSalida->__set('id_insumo', $idInsumo);
        $modeloSalida->__set('cantidad', 5);
        $modeloSalida->__set('motivo', 'Uso Interno');
        $modeloSalida->__set('descripcion', 'Entrega a enfermeria');
        $modeloSalida->__set('id_empleado', 1);

        $resultado = $modeloSalida->manejarAccion('salida');

        $this->assertIsArray($resultado);
        $this->assertEquals(5, $resultado['cantidad']);
        $this->assertEquals(15, $resultado['stock_total']);

        // Verificar en BD saldo 15 y estatus Disponible
        $stmt = $this->dbBusiness->prepare('SELECT cantidad, estatus FROM insumos WHERE id_insumo = :id');
        $stmt->execute([':id' => $idInsumo]);
        $insumo = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(15, (int) $insumo['cantidad']);
        $this->assertEquals('Disponible', $insumo['estatus']);

        // Verificar movimiento en inventario_medico
        $stmtKardex = $this->dbBusiness->prepare(
            "SELECT tipo_movimiento, cantidad, descripcion FROM inventario_medico WHERE id_insumo = :id AND tipo_movimiento = 'Salida'"
        );
        $stmtKardex->execute([':id' => $idInsumo]);
        $mov = $stmtKardex->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('Salida', $mov['tipo_movimiento']);
        $this->assertEquals(5, (int) $mov['cantidad']);
    }

    /**
     * Escenario 8.2: Prevención de stock negativo (cantidad solicitada mayor al stock actual)
     */
    public function testSalidaInsumoFallaStockInsuficiente(): void
    {
        // 1. Crear insumo con entrada de 10 unidades
        $sufijo = mt_rand(1000, 9999);
        $modelo = new InventarioModel();
        $modelo->__set('nombre_insumo', "Jeringas 5ml {$sufijo}");
        $modelo->__set('tipo_insumo', 'Material');
        $modelo->__set('id_presentacion', 5);
        $modelo->__set('fecha_vencimiento', date('Y-m-d', strtotime('+1 year')));
        $modelo->__set('descripcion', 'Material descartable');
        $modelo->__set('id_empleado', 1);
        $res = $modelo->manejarAccion('crear');
        $idInsumo = $res['id_insumo'];
        $this->insumosCreados[] = $idInsumo;

        $modeloEntrada = new InventarioModel();
        $modeloEntrada->__set('id_insumo', $idInsumo);
        $modeloEntrada->__set('cantidad', 10);
        $modeloEntrada->__set('descripcion', 'Ingreso inicial');
        $modeloEntrada->__set('id_empleado', 1);
        $modeloEntrada->manejarAccion('entrada');

        // 2. Intentar retirar 25 unidades cuando solo hay 10
        $modeloSalida = new InventarioModel();
        $modeloSalida->__set('id_insumo', $idInsumo);
        $modeloSalida->__set('cantidad', 25);
        $modeloSalida->__set('motivo', 'Uso Interno');
        $modeloSalida->__set('descripcion', 'Intento de retiro masivo');
        $modeloSalida->__set('id_empleado', 1);

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('Stock insuficiente para');

        $modeloSalida->manejarAccion('salida');
    }

    /**
     * Escenario 8.3: Validación de motivo de salida inválido
     */
    public function testSalidaInsumoFallaMotivoInvalido(): void
    {
        $modelo = new InventarioModel();

        $this->expectException(ExcepcionApi::class);
        $this->expectExceptionMessage('El motivo de la salida no es válido.');

        $modelo->__set('motivo', 'MotivoInvalido'); // Solo: Vencimiento, Daño, Pérdida, Donación, Uso Interno
    }

    /**
     * Escenario 8.4: Salida total de stock y actualización automática de estatus a 'Agotado'
     */
    public function testSalidaTotalActualizaEstatusAgotado(): void
    {
        // 1. Crear insumo con entrada de 8 unidades
        $sufijo = mt_rand(1000, 9999);
        $modelo = new InventarioModel();
        $modelo->__set('nombre_insumo', "Alcohol 70 Grados {$sufijo}");
        $modelo->__set('tipo_insumo', 'Material');
        $modelo->__set('id_presentacion', 4);
        $modelo->__set('fecha_vencimiento', date('Y-m-d', strtotime('+1 year')));
        $modelo->__set('descripcion', 'Desinfectante');
        $modelo->__set('id_empleado', 1);
        $res = $modelo->manejarAccion('crear');
        $idInsumo = $res['id_insumo'];
        $this->insumosCreados[] = $idInsumo;

        $modeloEntrada = new InventarioModel();
        $modeloEntrada->__set('id_insumo', $idInsumo);
        $modeloEntrada->__set('cantidad', 8);
        $modeloEntrada->__set('descripcion', 'Stock limitado');
        $modeloEntrada->__set('id_empleado', 1);
        $modeloEntrada->manejarAccion('entrada');

        // 2. Retirar exactamente las 8 unidades
        $modeloSalida = new InventarioModel();
        $modeloSalida->__set('id_insumo', $idInsumo);
        $modeloSalida->__set('cantidad', 8);
        $modeloSalida->__set('motivo', 'Uso Interno');
        $modeloSalida->__set('descripcion', 'Vaciado total de existencia');
        $modeloSalida->__set('id_empleado', 1);

        $resultado = $modeloSalida->manejarAccion('salida');

        $this->assertEquals(0, $resultado['stock_total']);

        // Verificar en BD que cantidad es 0 y estatus es 'Agotado'
        $stmt = $this->dbBusiness->prepare('SELECT cantidad, estatus FROM insumos WHERE id_insumo = :id');
        $stmt->execute([':id' => $idInsumo]);
        $insumo = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(0, (int) $insumo['cantidad']);
        $this->assertEquals('Agotado', $insumo['estatus']);
    }
}
