<?php

namespace App\Middlewares;

use PDO;
use Throwable;
use App\Core\Respuesta;
use App\Core\ExcepcionApi;
use App\Core\ErrorCodes;

class RateLimitMiddleware
{
    /**
     * Resuelve dinámicamente la política de rate limit basada en el comportamiento (Macro-Categorías).
     *
     * @param string $method El método HTTP de la petición (GET, POST).
     * @param string $endpoint El endpoint limpio.
     * @return array Contiene ['capacity' => float, 'rate' => float]
     */
    private static function resolvePolicy(string $method, string $endpoint): array
    {
        $method = strtoupper($method);

        // CASO ESPECIAL: Login desde la App Móvil
        // Aunque api/movil es público, si la acción del JSON es "login", aplicamos el Nivel 1
        if ($endpoint === 'api/movil' && $method === 'POST') {
            $input = file_get_contents('php://input');
            $decoded = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['accion']) && $decoded['accion'] === 'login') {
                return [
                    'capacity' => 5.0,
                    'rate' => 5.0 / 300.0 // Nivel 1: 5 intentos por cada 5 minutos
                ];
            }
        }

        // Nivel Especial: Peticiones a los Microservicios de IA
        // Aplica a cualquier endpoint que termine en '_ia' (ej. reportes_general_ia, reportes_psicologia_ia)
        // tanto para GET como para POST. Excluye endpoints de salud que terminan en '_health'.
        if (preg_match('/_ia$/', $endpoint)) {
            return [
                'capacity' => 3.0,
                'rate' => 3.0 / 300.0 // 3 intentos por cada 5 minutos (300 segundos)
            ];
        }

        // NIVEL 1: Excepciones de Seguridad (Máxima Estricción)
        // Autenticación exacta ('iniciar_sesion') o actualización del perfil del empleado ('perfil_actualizar')
        $nivel1Endpoints = ['iniciar_sesion', 'perfil_actualizar'];
        if (in_array($endpoint, $nivel1Endpoints)) {
            return [
                'capacity' => 5.0,
                'rate' => 5.0 / 300.0 // 5 intentos por cada 5 minutos (300 segundos)
            ];
        }

        // NIVEL 2: Validaciones de Formulario Rápidas (Alta Tolerancia)
        // Rutas que comiencen o contengan 'validar_' o 'verificar_', sin importar el método
        if (strpos($endpoint, 'validar_') !== false || strpos($endpoint, 'verificar_') !== false) {
            return [
                'capacity' => 80.0,
                'rate' => 80.0 / 60.0 // 80 peticiones por minuto (para evitar bloqueos falsos al escribir)
            ];
        }

        // NIVEL 3: Mutaciones de Base de Datos (Escritura Estricta)
        // Cualquier otra petición POST que no sea una validación (ej. registrar, editar, eliminar)
        if ($method === 'POST') {
            return [
                'capacity' => 15.0,
                'rate' => 15.0 / 60.0 // 15 peticiones por minuto
            ];
        }

        // NIVEL 4: Navegación y Carga de Datos (Lectura General)
        // Todas las peticiones GET (vistas HTML, carga de JSON de datos de tablas, etc.)
        return [
            'capacity' => 30.0,
            'rate' => 30.0 / 60.0 // 30 peticiones por minuto (ajustado de 80 a 30 por recomendación de seguridad)
        ];
    }

    /**
     * Obtiene la conexión PDO a la base de datos de seguridad
     */
    private static function getPdo()
    {
        static $pdo = null;
        if ($pdo === null) {
            $db = new class extends \App\Models\SecurityModel {
                public function getConn()
                {
                    return $this->conn_security;
                }
            };
            $pdo = $db->getConn();
        }
        return $pdo;
    }

    /**
     * Helper para limpiar y normalizar el endpoint actual
     */
    private static function getCleanEndpoint()
    {
        $rutaSolicitada = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $rutaBase = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $rutaRelativa = substr($rutaSolicitada, strlen($rutaBase));
        $endpoint = trim($rutaRelativa, '/') ?: 'login';

        // Normalizar IDs numéricos dinámicos en la ruta para evitar evasión de rate limit
        // Ejemplo: 'beneficiario/ver/25' se convierte en 'beneficiario/ver/{id}'
        return preg_replace('/\/\d+/', '/{id}', $endpoint);
    }

    /**
     * Consulta el registro de rate limits de la IP y endpoint
     */
    private static function queryRateLimit($pdo, $ip, $endpoint)
    {
        $stmt = $pdo->prepare("SELECT tokens_actuales, ultima_peticion FROM rate_limits WHERE ip_address = :ip AND endpoint = :endpoint");
        $stmt->execute(['ip' => $ip, 'endpoint' => $endpoint]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Maneja el rate limiting del Token Bucket
     */
    public static function handle()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $endpoint = self::getCleanEndpoint();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Obtener la política dinámica basada en comportamiento (Macro-Categorías)
        $policy = self::resolvePolicy($method, $endpoint);
        $capacity = (float) $policy['capacity'];
        $rate = (float) $policy['rate'];

        $pdo = self::getPdo();
        $record = self::queryRateLimit($pdo, $ip, $endpoint);

        $currentTime = time();

        if (!$record) {
            // El cubo se inicializa lleno con la capacidad máxima, y restamos 1 para esta petición
            $tokensCalculados = $capacity;
            $ultimaPeticion = $currentTime;

            $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, endpoint, tokens_actuales, ultima_peticion) VALUES (:ip, :endpoint, :tokens, :time)");
            $stmt->execute([
                'ip' => $ip,
                'endpoint' => $endpoint,
                'tokens' => $tokensCalculados - 1.0,
                'time' => $ultimaPeticion
            ]);
            return;
        }

        // Si existe el registro, calculamos tokens regenerados basándonos en el tiempo transcurrido
        $tokensActuales = (float) $record['tokens_actuales'];
        $ultimaPeticion = (int) $record['ultima_peticion'];

        $tiempoTranscurrido = max(0, $currentTime - $ultimaPeticion);
        $tokensRegenerados = $tiempoTranscurrido * $rate;
        $tokensCalculados = min($capacity, $tokensActuales + $tokensRegenerados);

        // Si los tokens calculados son menores a 1, bloquear la petición
        if ($tokensCalculados < 1.0) {
            $tokensNecesarios = 1.0 - $tokensCalculados;
            $segundosEspera = (int) ceil($tokensNecesarios / $rate);
            $mensaje = 'Demasiadas peticiones. Por favor, intente de nuevo en ' . $segundosEspera . ' segundos.';

            if (!headers_sent()) {
                http_response_code(429);
                header('Retry-After: ' . $segundosEspera);
            }

            $expectsJson = (
                (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false) ||
                (isset($_SERVER['CONTENT_TYPE']) && strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/json') !== false) ||
                preg_match('/(_ia|_data)$/', $endpoint)
            );

            if ($expectsJson) {
                // Contrato JSON único: {exito:false, error:{codigo, estado, mensaje}}.
                Respuesta::error(new ExcepcionApi(
                    ErrorCodes::RATE_LIMIT_EXCEDIDO,
                    429,
                    $mensaje
                ));
            }

            // Responder con un template HTML premium
            http_response_code(429);
            self::renderBlockPage($segundosEspera);
            exit();
        }

        // Si tiene tokens, restamos 1 y actualizamos en la base de datos
        $nuevosTokens = $tokensCalculados - 1.0;
        $stmt = $pdo->prepare("UPDATE rate_limits SET tokens_actuales = :tokens, ultima_peticion = :time WHERE ip_address = :ip AND endpoint = :endpoint");
        $stmt->execute([
            'tokens' => $nuevosTokens,
            'time' => $currentTime,
            'ip' => $ip,
            'endpoint' => $endpoint
        ]);
    }

    /**
     * Renderiza una página HTML premium de límite de peticiones excedido
     */
    private static function renderBlockPage($retryAfter)
    {
        require_once BASE_PATH . 'app/Views/errors/rate_limit.php';
    }
}
