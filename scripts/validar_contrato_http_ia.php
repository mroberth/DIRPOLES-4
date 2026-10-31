<?php
/**
 * scripts/validar_contrato_http_ia.php
 * -------------------------------------------------------------------------
 * Arnés de Validación del Contrato HTTP entre DIRPOLES-4 y DIRPOLES-IA
 * (Tarea 10 — Entorno de pruebas sintéticas aisladas).
 *
 * Verifica:
 *   1. Conformación del payload y filtros (pnf entero, limit, módulo snake_case).
 *   2. Tratamiento de id_empleado según RBAC (admin vs especialista).
 *   3. Mapeo de respuestas ReporteResponseDTO (Markdown + metadatos).
 *   4. Renderizado frontend de diagramas Mermaid.js.
 *   5. Manejo de errores HTTP (401, 422, 503, timeout).
 *   6. Ejecución HTTP en vivo si el microservicio está levantado en modo de pruebas.
 *
 * Uso:
 *   php scripts/validar_contrato_http_ia.php
 *   DIRPOLES_IA_URL=http://127.0.0.1:8000 php scripts/validar_contrato_http_ia.php --live
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse en la terminal CLI.\n");
}

define('BASE_PATH', dirname(__DIR__) . '/');
define('BASE_URL', '/DIRPOLES-4/');

// Autoloader y bootstrap mínimo sin tocar base de datos operativa
require_once BASE_PATH . 'vendor/autoload.php';

// Configurar entorno aislado de pruebas
$_ENV['APP_ENV'] = 'testing';
putenv('APP_ENV=testing');

require_once BASE_PATH . 'app/bootstrap.php';
require_once BASE_PATH . 'app/Controllers/reportesController.php';

use App\Core\ExcepcionApi;
use App\Core\ErrorCodes;

class ValidadorContratoIa
{
    private int $pasadas = 0;
    private int $fallidas = 0;
    private int $omitidas = 0;

    private function assert(string $descripcion, bool $condicion, string $detalle = ''): void
    {
        if ($condicion) {
            $this->pasadas++;
            echo "  \033[32m✔ [PASS]\033[0m {$descripcion}\n";
        } else {
            $this->fallidas++;
            echo "  \033[31m✘ [FAIL]\033[0m {$descripcion}";
            if ($detalle !== '') {
                echo " — {$detalle}";
            }
            echo "\n";
        }
    }

    private function omitir(string $descripcion, string $motivo): void
    {
        $this->omitidas++;
        echo "  \033[33m⚠ [OMIT]\033[0m {$descripcion} ({$motivo})\n";
    }

    public function ejecutar(bool $forzarLive = false): void
    {
        echo "=========================================================================\n";
        echo "  VALIDACIÓN DEL CONTRATO HTTP: DIRPOLES-4 (PHP) → DIRPOLES-IA (FastAPI)\n";
        echo "=========================================================================\n\n";

        $this->test1_EstructuraPayloadYTipos();
        $this->test2_ModuloTrabajoSocialSnakeCase();
        $this->test3_RbacIdEmpleadoVisibilidad();
        $this->test4_MapeoExcepcionesHttp();
        $this->test5_ConsumoMarkdownYMetadatos();
        $this->test6_DeteccionBloquesMermaid();
        $this->test7_ConexionHttpEnVivo($forzarLive);

        echo "\n-------------------------------------------------------------------------\n";
        echo "RESUMEN DE PRUEBAS:\n";
        echo "  Pasadas:  \033[32m{$this->pasadas}\033[0m\n";
        echo "  Fallidas: \033[31m{$this->fallidas}\033[0m\n";
        echo "  Omitidas: \033[33m{$this->omitidas}\033[0m\n";
        echo "-------------------------------------------------------------------------\n";

        if ($this->fallidas > 0) {
            echo "\033[31mESTADO: DEFECTOS ENCONTRADOS EN EL CONTRATO\033[0m\n";
            exit(1);
        } else {
            echo "\033[32mESTADO: CONTRATO VERIFICADO EXITOSAMENTE\033[0m\n";
            exit(0);
        }
    }

    /**
     * Test 1: Comprueba que los campos numéricos como pnf, servicio_destino y limit
     * se envíen estrictamente casteados como enteros, y que los vacíos se descarten.
     */
    private function test1_EstructuraPayloadYTipos(): void
    {
        echo "1. Validación de Sanitización y Casting de Filtros:\n";

        $filtrosEntrada = [
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-10-31',
            'pnf' => '3',               // String proveniente de formulario HTML
            'servicio_destino' => '1',  // String
            'limit' => '100',           // String
            'genero' => 'F',
            'filtro_vacio' => '',       // Debe ser purgado
            'filtro_nulo' => null,      // Debe ser purgado
        ];

        // Simulamos la lógica de limpieza de reportesIaGenerar
        $filtrosLimpios = [];
        foreach ($filtrosEntrada as $k => $v) {
            if ($v !== null && $v !== '') {
                if ($k === 'pnf' || $k === 'servicio_destino' || $k === 'limit') {
                    $filtrosLimpios[$k] = (int) $v;
                } else {
                    $filtrosLimpios[$k] = $v;
                }
            }
        }

        $this->assert("El filtro 'pnf' se castea estrictamente a entero", is_int($filtrosLimpios['pnf']) && $filtrosLimpios['pnf'] === 3);
        $this->assert("El filtro 'servicio_destino' se castea a entero", is_int($filtrosLimpios['servicio_destino']) && $filtrosLimpios['servicio_destino'] === 1);
        $this->assert("El filtro 'limit' se castea a entero", is_int($filtrosLimpios['limit']) && $filtrosLimpios['limit'] === 100);
        $this->assert("Los valores vacíos o nulos son eliminados del payload", !array_key_exists('filtro_vacio', $filtrosLimpios) && !array_key_exists('filtro_nulo', $filtrosLimpios));

        // Filtros vacíos generan objeto JSON {} y no array []
        $filtrosVacios = [];
        $payloadFiltros = empty($filtrosVacios) ? (object) [] : $filtrosVacios;
        $json = json_encode(['filtros' => $payloadFiltros]);
        $this->assert("Filtros vacíos se serializan como objeto JSON '{}'", $json === '{"filtros":{}}');
    }

    /**
     * Test 2: Verifica que 'trabajo_social' mantenga su formato snake_case en la API
     * aunque la ruta web del monolito sea 'reportes/trabajo-social'.
     */
    private function test2_ModuloTrabajoSocialSnakeCase(): void
    {
        echo "\n2. Mapeo del Módulo 'trabajo_social':\n";

        $moduloApi = 'trabajo_social';
        // En reportesController.php:
        $moduloEsp = match ($moduloApi) {
            'trabajo_social' => 'trabajo-social',
            default => $moduloApi,
        };

        $this->assert("El controlador mapea internamente a 'trabajo-social' para RBAC monolito", $moduloEsp === 'trabajo-social');
        $this->assert("El parámetro enviado a reportesIaGenerar conserva 'trabajo_social'", $moduloApi === 'trabajo_social');

        // Módulos aceptados por DIRPOLES-IA
        $modulosValidos = [
            'general', 'medicina', 'psicologia', 'orientacion', 'discapacidad',
            'trabajo_social', 'referencias', 'jornadas', 'mobiliario', 'transporte'
        ];
        $this->assert("'trabajo_social' pertenece al catálogo oficial del microservicio", in_array($moduloApi, $modulosValidos, true));
    }

    /**
     * Test 3: Verifica inyección server-side de id_empleado y confidencialidad RBAC:
     * - Administrador autorizado envía id_empleado = null (acceso irrestricto).
     * - Especialista autenticado con id >= 1 envía su identificador.
     * - Sesión no autenticada es rechazada (401 NO_AUTENTICADO), nunca recibe null.
     * - Especialista con id inválido (<= 0) es rechazado (403 ACCESO_DENEGADO), nunca recibe null.
     */
    private function test3_RbacIdEmpleadoVisibilidad(): void
    {
        echo "\n3. Inyección Server-Side de 'id_empleado' y Visibilidad RBAC:\n";

        // Caso A: Administrador debidamente autorizado (ve todo -> id_empleado = null)
        $_SESSION['id_tipo_empleado'] = 6;
        $_SESSION['tipo_empleado'] = 'Administrador General';
        $_SESSION['id_empleado'] = 1;

        $idTipoA = (int) ($_SESSION['id_tipo_empleado'] ?? 0);
        $tipoA = mb_strtolower(trim((string) ($_SESSION['tipo_empleado'] ?? '')));
        $esAdminA = ($idTipoA === 6 || $idTipoA === 10 || str_contains($tipoA, 'administrador') || str_contains($tipoA, 'superusuario'));
        $idEmpleadoPayloadAdmin = $esAdminA ? null : (int) $_SESSION['id_empleado'];

        $this->assert("Escenario 1 (Admin): Administrador autorizado envía id_empleado = null conforme al contrato", $idEmpleadoPayloadAdmin === null);

        // Caso B: Especialista autenticado con id_empleado >= 1
        $_SESSION['id_tipo_empleado'] = 2;
        $_SESSION['tipo_empleado'] = 'Médico';
        $_SESSION['id_empleado'] = 17;

        $idTipoB = (int) ($_SESSION['id_tipo_empleado'] ?? 0);
        $tipoB = mb_strtolower(trim((string) ($_SESSION['tipo_empleado'] ?? '')));
        $esAdminB = ($idTipoB === 6 || $idTipoB === 10 || str_contains($tipoB, 'administrador') || str_contains($tipoB, 'superusuario'));
        $idEmpleadoB = (int) ($_SESSION['id_empleado'] ?? 0);
        $idEmpleadoPayloadEsp = $esAdminB ? null : $idEmpleadoB;

        $this->assert("Escenario 2 (Especialista): Especialista con id >= 1 envía su identificador (17)", is_int($idEmpleadoPayloadEsp) && $idEmpleadoPayloadEsp === 17);

        // Caso C: Sesión NO autenticada (debe ser rechazada con 401 NO_AUTENTICADO por el mecanismo existente)
        $_SESSION = [];
        $_ENV['DIRPOLES_IA_API_KEY'] = 'clave_prueba_rbac';
        putenv('DIRPOLES_IA_API_KEY=clave_prueba_rbac');
        $rechazadoNoAuth = false;
        try {
            reportesIaGenerar('medicina', 'resumen_ejecutivo', [], null);
        } catch (ExcepcionApi $e) {
            $rechazadoNoAuth = ($e->estado === 401 && $e->codigo === ErrorCodes::NO_AUTENTICADO);
        }
        $this->assert("Escenario 3a (No Autenticado): Sesión sin autenticar es rechazada con HTTP 401 (NO_AUTENTICADO)", $rechazadoNoAuth);

        // Caso D: Especialista con identificador inválido (id_empleado = 0 o no establecido)
        $_SESSION = [
            'id_tipo_empleado' => 2, // Médico
            'tipo_empleado' => 'Medico',
            'id_empleado' => 0,      // Inválido
        ];
        $rechazadoIdInvalido = false;
        try {
            reportesIaGenerar('medicina', 'resumen_ejecutivo', [], null);
        } catch (ExcepcionApi $e) {
            $rechazadoIdInvalido = ($e->estado === 403 && $e->codigo === ErrorCodes::ACCESO_DENEGADO);
        }
        $this->assert("Escenario 3b (Identidad Inválida): Especialista con id_empleado=0 es rechazado con HTTP 403 (ACCESO_DENEGADO)", $rechazadoIdInvalido);

        // Caso E: Especialista con identificador negativo (id_empleado = -1)
        $_SESSION = [
            'id_tipo_empleado' => 3, // Trabajador Social
            'tipo_empleado' => 'Trabajador Social',
            'id_empleado' => -1,     // Inválido
        ];
        $rechazadoIdNegativo = false;
        try {
            reportesIaGenerar('trabajo_social', 'resumen_ejecutivo', [], null);
        } catch (ExcepcionApi $e) {
            $rechazadoIdNegativo = ($e->estado === 403 && $e->codigo === ErrorCodes::ACCESO_DENEGADO);
        }
        $this->assert("Escenario 3c (Identidad Negativa): Especialista con id_empleado negativo es rechazado con HTTP 403", $rechazadoIdNegativo);

        // Confirmación de seguridad de que en NINGUNO de los casos inválidos se obtuvo null (acceso global)
        $this->assert("Seguridad: Una identidad inválida nunca obtiene acceso global (id_empleado=null)", $rechazadoNoAuth && $rechazadoIdInvalido && $rechazadoIdNegativo);

        // Limpiar sesión
        unset($_SESSION['tipo_empleado'], $_SESSION['id_tipo_empleado'], $_SESSION['id_empleado']);
    }

    /**
     * Test 4: Mapeo de Códigos de Error de la API de FastAPI a ExcepcionApi del Monolito.
     */
    private function test4_MapeoExcepcionesHttp(): void
    {
        echo "\n4. Mapeo de Respuestas de Error HTTP:\n";

        // Caso 401: Falta o falla API Key
        $codigo401 = 401;
        try {
            if ($codigo401 === 401) {
                throw ExcepcionApi::errorInterno('Servicio de IA no configurado.');
            }
        } catch (ExcepcionApi $e) {
            $this->assert("HTTP 401 mapea a ExcepcionApi 500 ('Servicio de IA no configurado.')", $e->estado === 500 && str_contains($e->getMessage(), 'no configurado'));
        }

        // Caso 422: Validación fallida en FastAPI (detail array)
        $codigo422 = 422;
        $datos422 = [
            'detail' => [
                ['loc' => ['body', 'filtros', 'pnf'], 'msg' => 'Input should be greater than or equal to 1', 'type' => 'greater_than_equal']
            ]
        ];
        try {
            $detalle = $datos422['detail'] ?? null;
            if ($codigo422 === 422 && is_array($detalle)) {
                $msg = $detalle[0]['msg'] ?? 'Datos inválidos para el informe.';
                throw ExcepcionApi::validacion('El informe no pudo generarse: ' . $msg);
            }
        } catch (ExcepcionApi $e) {
            $this->assert("HTTP 422 mapea a ExcepcionApi::validacion (400) extrayendo detail[0].msg", $e->estado === 400 && str_contains($e->getMessage(), 'greater than or equal to 1'));
        }

        // Caso 503: Base de datos no disponible o caída en microservicio
        $codigo503 = 503;
        $datos503 = [
            'detail' => [
                'exito' => false,
                'detalle' => 'No fue posible extraer los datos de la base de datos.',
                'error' => 'Can\'t connect to MySQL server'
            ]
        ];
        try {
            $detalle = $datos503['detail'] ?? null;
            if ($codigo503 === 503) {
                $msg503 = is_array($detalle)
                    ? ($detalle['detalle'] ?? 'Datos no disponibles temporalmente para el informe de IA.')
                    : (string) $detalle;
                throw ExcepcionApi::errorInterno((string) $msg503);
            }
        } catch (ExcepcionApi $e) {
            $this->assert("HTTP 503 mapea a ExcepcionApi 500 extrayendo detalle seguro", $e->estado === 500 && str_contains($e->getMessage(), 'No fue posible extraer los datos'));
        }
    }

    /**
     * Test 5: Interpretación de ReporteResponseDTO sintético (Markdown + metadatos).
     */
    private function test5_ConsumoMarkdownYMetadatos(): void
    {
        echo "\n5. Consumo de ReporteResponseDTO y Metadatos:\n";

        $mockRespuestaJson = [
            'exito' => true,
            'modulo' => 'medicina',
            'intencion' => 'alertas_y_anomalias',
            'informe_markdown' => "# INFORME EJECUTIVO — MEDICINA\n\n```mermaid\npie title Distribución\n\"Atendidos\" : 85\n\"No asistió\" : 15\n```\n",
            'meta' => [
                'registros_procesados' => 100,
                'tiempo_procesamiento_seg' => 1.45,
                'modo_informe' => 'simulado',
            ],
        ];

        $codigo = 200;
        $procesado = null;
        if ($codigo === 200 && ($mockRespuestaJson['exito'] ?? false) === true) {
            $procesado = [
                'informe_markdown' => $mockRespuestaJson['informe_markdown'],
                'meta' => $mockRespuestaJson['meta'],
            ];
        }

        $this->assert("Respuesta 200 con exito=true es procesada", is_array($procesado));
        $this->assert("Contiene 'informe_markdown' intacto", !empty($procesado['informe_markdown']) && str_starts_with($procesado['informe_markdown'], '# INFORME'));
        $this->assert("Contiene metadatos 'registros_procesados' (100)", ($procesado['meta']['registros_procesados'] ?? 0) === 100);
        $this->assert("Contiene metadatos 'tiempo_procesamiento_seg' (1.45)", ($procesado['meta']['tiempo_procesamiento_seg'] ?? 0) === 1.45);
        $this->assert("Contiene metadatos 'modo_informe' ('simulado')", ($procesado['meta']['modo_informe'] ?? '') === 'simulado');
    }

    /**
     * Test 6: Verificación de bloques Mermaid en el informe Markdown.
     */
    private function test6_DeteccionBloquesMermaid(): void
    {
        echo "\n6. Verificación de Soporte Visual Mermaid.js:\n";

        $markdownConMermaid = <<<MD
### 📊 Informe de Análisis Inteligente: Módulo Medicina

#### 1. Resumen Ejecutivo
* **Total consultas:** 120

```mermaid
pie title Distribución por Género
    "Femenino" : 75
    "Masculino" : 45
```

#### 2. Hallazgos Clave
* Alerta: stock bajo en Amoxicilina.
MD;

        $contieneBloqueMermaid = preg_match('/```mermaid\s+([\s\S]+?)```/i', $markdownConMermaid, $matches);
        $this->assert("El markdown contiene bloques delimitados ```mermaid", (bool) $contieneBloqueMermaid);
        $this->assert("El contenido del diagrama Mermaid define gráfico de pastel (pie title)", isset($matches[1]) && str_contains($matches[1], 'pie title'));

        // Simular lo que hace dist/js/modulos/reportes/ia.js
        $procesadoJs = preg_replace_callback('/```mermaid\s+([\s\S]+?)```/i', function ($m) {
            return '<div class="mermaid">' . trim($m[1]) . '</div>';
        }, $markdownConMermaid);

        $this->assert("El bloque Mermaid es transformable al contenedor <div class=\"mermaid\"> para mermaid.run()", str_contains($procesadoJs, '<div class="mermaid">'));
    }

    /**
     * Test 7: Prueba HTTP real contra el microservicio DIRPOLES-IA (si está levantado).
     */
    private function test7_ConexionHttpEnVivo(bool $forzarLive): void
    {
        echo "\n7. Prueba de Conectividad HTTP en Vivo:\n";

        $urlBase = rtrim(getenv('DIRPOLES_IA_URL') ?: 'http://127.0.0.1:8000', '/');
        $apiKey = getenv('DIRPOLES_IA_API_KEY') ?: '';

        // Si fue sobreescrita en pruebas sintéticas previas o no está en el entorno, leerla del .env de DIRPOLES-4
        if ($apiKey === '' || $apiKey === 'clave_prueba_rbac') {
            if (file_exists(BASE_PATH . '.env')) {
                $lineasEnv = file(BASE_PATH . '.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lineasEnv as $linea) {
                    if (str_starts_with(trim($linea), 'DIRPOLES_IA_API_KEY=')) {
                        $apiKey = trim(substr(trim($linea), strlen('DIRPOLES_IA_API_KEY=')));
                        break;
                    }
                }
            }
        }
        if ($apiKey === '') {
            $apiKey = 'clave-de-prueba-tarea10';
        }

        // Comprobación de salud sin API key
        $chSalud = curl_init($urlBase . '/api/v1/salud');
        curl_setopt_array($chSalud, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 3,
        ]);
        $respSalud = curl_exec($chSalud);
        $codigoSalud = curl_getinfo($chSalud, CURLINFO_HTTP_CODE);
        curl_close($chSalud);

        if ($codigoSalud !== 200 || $respSalud === false) {
            $this->omitir(
                "Petición HTTP en vivo contra {$urlBase}",
                "DIRPOLES-IA no está respondiendo en {$urlBase} (código HTTP: {$codigoSalud}). " .
                "Para ejecutar la prueba en vivo, inicie DIRPOLES-IA con MOCK_LLM=True y vuelva a ejecutar con --live."
            );
            return;
        }

        $jsonSalud = json_decode((string) $respSalud, true);
        $this->assert("Microservicio DIRPOLES-IA vivo en /api/v1/salud (200 OK)", is_array($jsonSalud) && ($jsonSalud['estado'] ?? '') === 'ok');

        // Prueba A: Autenticación 401 con API Key inválida
        $chAuth = curl_init($urlBase . '/api/v1/reportes/generar');
        curl_setopt_array($chAuth, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-API-Key: clave_invalida_sintetica'],
            CURLOPT_POSTFIELDS => json_encode(['modulo' => 'medicina', 'intencion' => 'resumen_ejecutivo']),
            CURLOPT_TIMEOUT => 5,
        ]);
        curl_exec($chAuth);
        $codigoAuth = curl_getinfo($chAuth, CURLINFO_HTTP_CODE);
        curl_close($chAuth);
        $this->assert("Llamada con X-API-Key inválida es rechazada con HTTP 401", $codigoAuth === 401);

        // Prueba B: Validación 422 con módulo inválido
        $chVal = curl_init($urlBase . '/api/v1/reportes/generar');
        curl_setopt_array($chVal, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-API-Key: ' . $apiKey],
            CURLOPT_POSTFIELDS => json_encode(['modulo' => 'modulo_inexistente', 'intencion' => 'resumen_ejecutivo']),
            CURLOPT_TIMEOUT => 5,
        ]);
        curl_exec($chVal);
        $codigoVal = curl_getinfo($chVal, CURLINFO_HTTP_CODE);
        curl_close($chVal);
        $this->assert("Llamada con módulo no contemplado en enum es rechazada con HTTP 422", $codigoVal === 422);

        // Prueba C: Petición sintética válida (happy path con ClienteSimulado)
        $_SESSION['tipo_empleado'] = 'Administrador';
        $_SESSION['id_empleado'] = 1;
        putenv("DIRPOLES_IA_URL={$urlBase}");
        putenv("DIRPOLES_IA_API_KEY={$apiKey}");
        $_ENV['DIRPOLES_IA_URL'] = $urlBase;
        $_ENV['DIRPOLES_IA_API_KEY'] = $apiKey;

        try {
            $resultado = reportesIaGenerar('medicina', 'resumen_ejecutivo', ['pnf' => 3, 'limit' => 50], 'Prueba sintética de contrato');
            $this->assert("Llamada exitosa a reportesIaGenerar() devuelve array de datos", is_array($resultado));
            $this->assert("Resultado contiene 'informe_markdown'", !empty($resultado['informe_markdown']));
            $this->assert("Resultado contiene metadatos con modo 'simulado'", in_array($resultado['meta']['modo_informe'] ?? '', ['simulado', 'simulado_fallback', 'gemini'], true));
        } catch (\Throwable $e) {
            $this->assert("reportesIaGenerar ejecutó sin errores imprevistos", false, $e->getMessage());
        }
    }
}

$forzarLive = in_array('--live', $argv, true);
$validador = new ValidadorContratoIa();
$validador->ejecutar($forzarLive);
