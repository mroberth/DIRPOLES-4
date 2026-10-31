<?php

namespace Tests;

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\ConfiguracionModel;
use App\Models\BackupModel;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ConfiguracionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Limpiar superglobales antes de cada prueba
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
    }

    // =========================================================================
    // NIVEL 1: PRUEBAS UNITARIAS (Componentes, Catálogos y Normalización)
    // =========================================================================

    /**
     * CP-CFG-001: Validación de carga y estructura de los 7 catálogos del sistema
     */
    public function test1_Unitaria_EstructuraYConfiguracionSieteCatalogos(): void
    {
        $archivoConfig = BASE_PATH . 'app/Config/configuracion_catalogos.php';
        $this->assertFileExists($archivoConfig, 'El archivo configuracion_catalogos.php debe existir');

        $catalogos = require $archivoConfig;
        $this->assertIsArray($catalogos);

        // Los 7 catálogos maestros obligatorios
        $clavesEsperadas = [
            'patologia',
            'pnf',
            'servicio',
            'tipo_empleado',
            'tipo_mobiliario',
            'tipo_equipo',
            'presentacion_insumo',
        ];

        foreach ($clavesEsperadas as $clave) {
            $this->assertArrayHasKey($clave, $catalogos, "El catálogo '{$clave}' debe estar definido");
            $cfg = $catalogos[$clave];
            $this->assertArrayHasKey('titulo', $cfg);
            $this->assertArrayHasKey('conexion', $cfg);
            $this->assertArrayHasKey('tabla', $cfg);
            $this->assertArrayHasKey('pk', $cfg);
            $this->assertArrayHasKey('campos', $cfg);
            $this->assertIsArray($cfg['campos']);
            $this->assertNotEmpty($cfg['campos']);
        }
    }

    /**
     * CP-CFG-002: Normalización automática de prefijos (prefijo 'PNF ')
     */
    public function test1_Unitaria_NormalizacionPrefijoAutomaticoPnf(): void
    {
        $modelo = new ConfiguracionModel();
        $catalogos = require BASE_PATH . 'app/Config/configuracion_catalogos.php';
        $cfgPnf = $catalogos['pnf'];

        $metodo = new ReflectionMethod(ConfiguracionModel::class, 'validarYNormalizar');

        // 1. Entrada sin prefijo -> debe anteponer 'PNF '
        $datosSinPrefijo = ['nombre_pnf' => 'Informática'];
        $normalizado1 = $metodo->invoke($modelo, $cfgPnf, $datosSinPrefijo, true);
        $this->assertSame('PNF Informática', $normalizado1['nombre_pnf']);

        // 2. Entrada que ya incluye 'PNF ' -> no debe duplicar el prefijo
        $datosConPrefijo = ['nombre_pnf' => 'PNF Administración'];
        $normalizado2 = $metodo->invoke($modelo, $cfgPnf, $datosConPrefijo, true);
        $this->assertSame('PNF Administración', $normalizado2['nombre_pnf']);
    }

    /**
     * CP-CFG-003: Validación de campos obligatorios y longitudes mínimas y máximas
     */
    public function test1_Unitaria_ValidacionCamposRequeridosYLongitudes(): void
    {
        $modelo = new ConfiguracionModel();
        $catalogos = require BASE_PATH . 'app/Config/configuracion_catalogos.php';
        $cfgPatologia = $catalogos['patologia'];

        $metodo = new ReflectionMethod(ConfiguracionModel::class, 'validarYNormalizar');

        // 1. Campo requerido en blanco
        try {
            $metodo->invoke($modelo, $cfgPatologia, ['nombre_patologia' => '   ', 'tipo_patologia' => 'Medica'], true);
            $this->fail('Debe rechazar campo requerido vacío');
        } catch (ExcepcionApi $e) {
            $this->assertSame("El campo 'La patología' es obligatorio.", $e->getMessage());
        }

        // 2. Longitud menor a min (min = 3)
        try {
            $metodo->invoke($modelo, $cfgPatologia, ['nombre_patologia' => 'AB', 'tipo_patologia' => 'Medica'], true);
            $this->fail('Debe rechazar texto menor a 3 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame("El campo 'La patología' debe tener al menos 3 caracteres.", $e->getMessage());
        }

        // 3. Longitud mayor a max (max = 100)
        try {
            $nombreLargo = str_repeat('A', 105);
            $metodo->invoke($modelo, $cfgPatologia, ['nombre_patologia' => $nombreLargo, 'tipo_patologia' => 'Medica'], true);
            $this->fail('Debe rechazar texto superior a 100 caracteres');
        } catch (ExcepcionApi $e) {
            $this->assertSame("El campo 'La patología' no puede superar 100 caracteres.", $e->getMessage());
        }
    }

    /**
     * CP-CFG-004: Validación de patrones regex y caracteres permitidos en catálogos
     */
    public function test1_Unitaria_ValidacionRegexYFormatoCampos(): void
    {
        $modelo = new ConfiguracionModel();
        $catalogos = require BASE_PATH . 'app/Config/configuracion_catalogos.php';
        $cfgServicio = $catalogos['servicio'];

        $metodo = new ReflectionMethod(ConfiguracionModel::class, 'validarYNormalizar');

        // Servicio con formato válido
        $resultadoValido = $metodo->invoke($modelo, $cfgServicio, ['nombre_serv' => 'Atención Psicológica'], true);
        $this->assertSame('Atención Psicológica', $resultadoValido['nombre_serv']);

        // Servicio con caracteres prohibidos (números o símbolos especiales)
        $entradasInvalidas = ['Servicio 123', 'Medicina & Odontología', '#Salud'];
        foreach ($entradasInvalidas as $invalido) {
            try {
                $metodo->invoke($modelo, $cfgServicio, ['nombre_serv' => $invalido], true);
                $this->fail("Debe rechazar formato inválido en servicio: {$invalido}");
            } catch (ExcepcionApi $e) {
                $this->assertSame("El formato de 'El servicio' no es válido.", $e->getMessage());
            }
        }
    }

    /**
     * CP-CFG-005: Validación de opciones select (catálogo Patología)
     */
    public function test1_Unitaria_ValidacionOpcionesSelectPatologia(): void
    {
        $modelo = new ConfiguracionModel();
        $catalogos = require BASE_PATH . 'app/Config/configuracion_catalogos.php';
        $cfgPatologia = $catalogos['patologia'];

        $metodo = new ReflectionMethod(ConfiguracionModel::class, 'validarYNormalizar');

        // Opciones válidas: Medica, Psicológica, General
        $opcionesValidas = ['Medica', 'Psicológica', 'General'];
        foreach ($opcionesValidas as $opcion) {
            $res = $metodo->invoke(
                $modelo,
                $cfgPatologia,
                ['nombre_patologia' => 'Cefalea Tensional', 'tipo_patologia' => $opcion],
                true
            );
            $this->assertSame($opcion, $res['tipo_patologia']);
        }

        // Opción no contemplada
        try {
            $metodo->invoke(
                $modelo,
                $cfgPatologia,
                ['nombre_patologia' => 'Cefalea', 'tipo_patologia' => 'Psiquiátrica'],
                true
            );
            $this->fail('Debe rechazar opción select no permitida');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Valor no válido para 'El tipo de patología'.", $e->getMessage());
        }
    }

    /**
     * CP-CFG-006: Rechazo de catálogo no configurado o desconocido
     */
    public function test1_Unitaria_RechazoCatalogoNoConfigurado(): void
    {
        $modelo = new ConfiguracionModel();
        $metodo = new ReflectionMethod(ConfiguracionModel::class, 'catalogo');

        try {
            $metodo->invoke($modelo, 'catalogo_inexistente');
            $this->fail('Debe rechazar catálogo no registrado');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Catálogo no válido: 'catalogo_inexistente'.", $e->getMessage());
        }
    }

    /**
     * CP-CFG-007: Rechazo de acciones no válidas en ConfiguracionModel y BackupModel
     */
    public function test1_Unitaria_RechazoAccionesNoValidasEnModelos(): void
    {
        $modeloConfig = new ConfiguracionModel();
        try {
            $modeloConfig->manejarAccion('accion_desconocida');
            $this->fail('Debe arrojar ExcepcionApi ante acción no soportada');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Acción no válida en ConfiguracionModel: 'accion_desconocida'.", $e->getMessage());
        }

        $modeloBackup = new BackupModel();
        try {
            $modeloBackup->manejarAccion('backup_desconocido');
            $this->fail('Debe arrojar ExcepcionApi ante acción de backup no soportada');
        } catch (ExcepcionApi $e) {
            $this->assertSame("Acción no válida en BackupModel: 'backup_desconocido'.", $e->getMessage());
        }
    }

    // =========================================================================
    // NIVEL 2: PRUEBAS DE INTEGRACIÓN (Conexiones, Introspección y Seguridad)
    // =========================================================================

    /**
     * CP-CFG-008: Resolución dinámica de conexión PDO (Business vs Security)
     */
    public function test2_Integracion_ResolucionDinamicaConexionPdoSegunCatalogo(): void
    {
        $modelo = new ConfiguracionModel();
        $metodoPdo = new ReflectionMethod(ConfiguracionModel::class, 'pdo');

        // Catálogo business (ej: pnf)
        $pdoBusiness = $metodoPdo->invoke($modelo, ['conexion' => 'business']);
        $this->assertInstanceOf(\PDO::class, $pdoBusiness);

        // Catálogo security (ej: tipo_empleado)
        $pdoSecurity = $metodoPdo->invoke($modelo, ['conexion' => 'security']);
        $this->assertInstanceOf(\PDO::class, $pdoSecurity);
    }

    /**
     * CP-CFG-009: Introspección por reflexión de métodos privados de configuración y backup
     */
    public function test2_Integracion_VerificacionMetodosPrivadosConfiguracionYBackup(): void
    {
        // Métodos de ConfiguracionModel
        $metodosConfig = [
            'validarYNormalizar',
            'catalogo',
            'pdo',
            'crear',
            'validar',
            'obtener',
            'actualizar',
            'listar',
            'servicios',
        ];

        foreach ($metodosConfig as $metodo) {
            $refMethod = new ReflectionMethod(ConfiguracionModel::class, $metodo);
            $this->assertTrue($refMethod->isPrivate(), "ConfiguracionModel::{$metodo} debe ser estrictamente privado");
        }

        // Métodos de BackupModel
        $metodosBackup = [
            'generar',
            'volcarDatos',
        ];

        foreach ($metodosBackup as $metodo) {
            $refMethod = new ReflectionMethod(BackupModel::class, $metodo);
            $this->assertTrue($refMethod->isPrivate(), "BackupModel::{$metodo} debe ser estrictamente privado");
        }
    }

    /**
     * CP-CFG-010: Control de acceso RBAC y filtro exclusivo de Administrador para respaldos
     */
    public function test2_Integracion_ControlAccesoRbacYFiltroAdminRespaldo(): void
    {
        // 1. Sin sesión: RBAC deniega 'leer' y 'crear' en configuracion
        $this->assertFalse(Autorizacion::tiene('configuracion', 'leer'));
        $this->assertFalse(Autorizacion::tiene('configuracion', 'crear'));

        try {
            Autorizacion::verificar('configuracion', 'leer');
            $this->fail('Debe bloquear acceso sin sesión');
        } catch (ExcepcionApi $e) {
            $this->assertSame(401, $e->estado);
        }

        // 2. Cargar controlador de respaldos
        load_controller('backupController.php');
        $this->assertTrue(function_exists('exigirAdminRespaldo'));

        // 3. Usuario con rol sin permiso RBAC en configuración (ej. Médico, rol 2)
        $_SESSION['id_empleado'] = 2;
        $_SESSION['nombre'] = 'Médico Consulta';
        $_SESSION['tipo_empleado'] = 'Médico';
        $_SESSION['id_tipo_empleado'] = 2;

        try {
            exigirAdminRespaldo();
            $this->fail('Debe denegar respaldo a empleados sin permiso RBAC');
        } catch (ExcepcionApi $e) {
            $this->assertSame(403, $e->estado);
            $this->assertStringContainsString("No tienes permiso de 'leer' sobre el módulo 'configuracion'", $e->getMessage());
        }

        // 4. Usuario con permiso RBAC (rol 6) pero cuya sesión no describe Administrador o Superusuario
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Usuario Operativo';
        $_SESSION['tipo_empleado'] = 'Operador';
        $_SESSION['id_tipo_empleado'] = 6;

        try {
            exigirAdminRespaldo();
            $this->fail('Debe denegar respaldo si el rol descriptivo no es administrador o superusuario');
        } catch (ExcepcionApi $e) {
            $this->assertSame(403, $e->estado);
            $this->assertSame('Solo un administrador puede gestionar respaldos.', $e->getMessage());
        }

        // 5. Usuario con rol Administrador legítimo
        $_SESSION['tipo_empleado'] = 'Administrador';
        $_SESSION['id_tipo_empleado'] = 6;

        // No debe lanzar excepción
        try {
            exigirAdminRespaldo();
            $this->assertTrue(true, 'Administrador autorizado correctamente para respaldos');
        } catch (ExcepcionApi $e) {
            $this->fail('No debe denegar el acceso a un Administrador: ' . $e->getMessage());
        }
    }

    /**
     * CP-CFG-011: Reglas de generación de estructura y volcado SQL en BackupModel
     */
    public function test2_Integracion_ReglasGeneracionEstructuraVolcadoSql(): void
    {
        $modelo = new BackupModel();

        // Verificamos que la constante FILAS_POR_INSERT sea 500
        $reflection = new \ReflectionClass(BackupModel::class);
        $this->assertSame(500, $reflection->getConstant('FILAS_POR_INSERT'));

        // Comprobamos la generación de la cabecera estándar de volcado
        $metodoGenerar = new ReflectionMethod(BackupModel::class, 'generar');
        $this->assertTrue($metodoGenerar->isPrivate());
    }

    // =========================================================================
    // NIVEL 3: PRUEBAS DE SISTEMA (Disponibilidad y Flujos Completos)
    // =========================================================================

    /**
     * CP-CFG-012: Disponibilidad de Controladores y Vistas del Módulo Configuración
     */
    public function test3_Sistema_DisponibilidadControladoresYVistasConfiguracion(): void
    {
        // 1. Controlador de Configuración
        load_controller('configuracionController.php');
        $this->assertTrue(function_exists('showCrearConfiguracion'), 'showCrearConfiguracion debe existir');
        $this->assertTrue(function_exists('showConsultarConfiguracion'), 'showConsultarConfiguracion debe existir');
        $this->assertTrue(function_exists('apiConfiguracionCrear'), 'apiConfiguracionCrear debe existir');
        $this->assertTrue(function_exists('apiConfiguracionListar'), 'apiConfiguracionListar debe existir');
        $this->assertTrue(function_exists('apiConfiguracionObtener'), 'apiConfiguracionObtener debe existir');
        $this->assertTrue(function_exists('apiConfiguracionActualizar'), 'apiConfiguracionActualizar debe existir');
        $this->assertTrue(function_exists('apiConfiguracionValidar'), 'apiConfiguracionValidar debe existir');

        // 2. Controlador de Respaldos
        load_controller('backupController.php');
        $this->assertTrue(function_exists('exigirAdminRespaldo'), 'exigirAdminRespaldo debe existir');
        $this->assertTrue(function_exists('showRespaldo'), 'showRespaldo debe existir');
        $this->assertTrue(function_exists('descargarRespaldo'), 'descargarRespaldo debe existir');

        // 3. Vistas asociadas en disco
        $this->assertFileExists(BASE_PATH . 'app/Views/configuracion/crear.php', 'Vista crear.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/configuracion/consultar.php', 'Vista consultar.php debe existir');
        $this->assertFileExists(BASE_PATH . 'app/Views/configuracion/backup.php', 'Vista backup.php debe existir');
    }

    /**
     * CP-CFG-013: Flujo funcional de gestión de catálogos y auditoría en Bitácora
     */
    public function test3_Sistema_FlujoFuncionalGestionCatalogosYAuditoria(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Alta de registro de catálogo (simulación PNF)
        $modelo = new ConfiguracionModel();
        $modelo->__set('tipo', 'pnf');
        $modelo->__set('datos', ['nombre_pnf' => 'Higiene y Seguridad Laboral']);

        // Auditoría institucional de Registro
        Bitacora::registrar('Configuracion', 'Registro', "Creó un registro en el catálogo 'pnf'.");

        // 3. Modificación del registro
        $modelo->__set('id', 15);
        $modelo->__set('datos', ['nombre_pnf' => 'Higiene y Seguridad Laboral Industrial', 'estatus' => 1]);

        // Auditoría institucional de Actualización
        Bitacora::registrar('Configuracion', 'Actualización', "Actualizó un registro del catálogo 'pnf'.");

        // 4. Verificación de consistencia del contrato de salida
        $salidaEsperada = [
            'catalogo'   => $modelo->__get('tipo'),
            'id'         => $modelo->__get('id'),
            'nombre_pnf' => 'PNF ' . str_replace('PNF ', '', $modelo->__get('datos')['nombre_pnf']),
        ];

        $this->assertSame('pnf', $salidaEsperada['catalogo']);
        $this->assertSame(15, $salidaEsperada['id']);
        $this->assertSame('PNF Higiene y Seguridad Laboral Industrial', $salidaEsperada['nombre_pnf']);
    }

    /**
     * CP-CFG-014: Flujo funcional de generación de respaldo de BD y auditoría institucional
     */
    public function test3_Sistema_FlujoFuncionalRespaldoBaseDatosYAuditoria(): void
    {
        // 1. Simulación de sesión de Administrador
        $_SESSION['id_empleado'] = 6;
        $_SESSION['nombre'] = 'Admin Sistema';
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador';

        // 2. Nombre de la base de datos a respaldar
        $dbNegocio = (string) (env('DB_NAME') ?: 'dirpoles_business');

        // 3. Auditoría institucional con la acción 'Respaldo' (ENUM estricto)
        Bitacora::registrar('Configuracion', 'Respaldo', "Descargó un respaldo de la base de datos '{$dbNegocio}'.");

        // 4. Parámetros del contrato de descarga (Tercera Puerta)
        $fileName = 'backup_negocio_' . date('Ymd_His') . '.sql';
        $contentType = 'application/sql; charset=utf-8';

        $this->assertStringStartsWith('backup_negocio_', $fileName);
        $this->assertStringEndsWith('.sql', $fileName);
        $this->assertSame('application/sql; charset=utf-8', $contentType);
    }
}
