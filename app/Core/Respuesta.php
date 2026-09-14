<?php
namespace App\Core;

/**
 * app/Core/Respuesta.php
 * ---------------------------------------------------------------
 * EL ÚNICO PUNTO por donde sale JSON del sistema, y el formateador
 * del handler global de errores.
 *
 * CONTRATO JSON (idéntico en todo el sistema):
 *
 *   Éxito:  { "exito": true,  "datos": <cualquier cosa> }
 *   Error:  { "exito": false, "error": { "codigo": "...", "estado": 409, "mensaje": "..." } }
 *
 * LAS DOS PUERTAS (convención de frontera del monolito):
 *   - Ruta bajo api/  (o Accept: application/json) → SIEMPRE JSON.
 *   - Cualquier otra ruta → SIEMPRE HTML (vista o redirección).
 *
 * USO EN EL CONTROLADOR:
 *
 *   function consultar_productos() {
 *       Autorizacion::verificar('productos', 'leer');
 *       try {
 *           $modelo = new ProductoModel();
 *           Respuesta::exito($modelo->manejarAccion('consultar'));
 *       } catch (Throwable $e) {
 *           Respuesta::error($e);   // vuelca codigo/estado/mensaje de la ExcepcionApi
 *       }
 *   }
 */
final class Respuesta
{
    /** Responde JSON de éxito y TERMINA la ejecución. */
    public static function exito(mixed $datos = null, int $estado = 200): never
    {
        self::json(['exito' => true, 'datos' => $datos], $estado);
    }

    /** Responde JSON de error y TERMINA la ejecución.
     *  Si $e es ExcepcionApi vuelca sus metadatos; si no, log + 500 genérico. */
    public static function error(\Throwable $e): never
    {
        if ($e instanceof ExcepcionApi) {
            $error = [
                'codigo'  => $e->codigo,
                'estado'  => $e->estado,
                'mensaje' => $e->getMessage(),
            ];
            if ($e->detalles !== null) {
                $error['detalles'] = $e->detalles;
            }
            self::json(['exito' => false, 'error' => $error], $e->estado);
        }

        // Error inesperado (PDOException, TypeError...): registrar el real,
        // responder el genérico. Solo en APP_DEBUG se expone el detalle.
        error_log('ERROR NO CONTROLADO (' . get_class($e) . '): ' . $e->getMessage()
            . ' en ' . $e->getFile() . ':' . $e->getLine());

        $mensaje = (env('APP_DEBUG', 'false') === 'true')
            ? $e->getMessage()
            : 'Ocurrió un error inesperado en el servidor.';

        self::json([
            'exito' => false,
            'error' => ['codigo' => ErrorCodes::ERROR_INTERNO, 'estado' => 500, 'mensaje' => $mensaje],
        ], 500);
    }

    /**
     * HANDLER GLOBAL de excepciones (se registra en index.php).
     * Decide el formato según la puerta: api/* → JSON del contrato;
     * página → log + página de error HTML (nunca JSON al navegador).
     */
    public static function manejarExcepcion(\Throwable $e): void
    {
        if (self::esApi()) {
            self::error($e);
            return;
        }

        // Página HTML: registrar el error real y mostrar la página genérica.
        error_log('ERROR EN PÁGINA (' . get_class($e) . '): ' . $e->getMessage()
            . ' en ' . $e->getFile() . ':' . $e->getLine());

        $estado = ($e instanceof ExcepcionApi) ? $e->estado : 500;
        $errorDebug = (env('APP_DEBUG', 'false') === 'true')
            ? get_class($e) . ': ' . $e->getMessage()
            : null;

        if (!headers_sent()) {
            http_response_code($estado >= 400 ? $estado : 500);
        }

        // Los 404 tienen página dedicada; el resto usa la genérica.
        $vista = ($estado === 404)
            ? BASE_PATH . 'app/Views/errors/404.php'
            : BASE_PATH . 'app/Views/errors/error.php';

        include $vista;
        exit;
    }

    /**
     * ¿Es una petición a la puerta JSON?
     * - Ruta relativa bajo api/
     * - o Accept: application/json exacto (clientes que piden JSON explícito)
     */
    public static function esApi(): bool
    {
        $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $relativa = trim(substr($ruta, strlen($base)), '/');

        if ($relativa === 'api' || str_starts_with($relativa, 'api/')) {
            return true;
        }
        // Acepta tanto 'application/json' exacto como listas tipo
        // 'application/json, text/plain, */*'.
        return str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
    }

    /** Emite el JSON y termina. Punto único de salida. */
    private static function json(mixed $payload, int $estado): never
    {
        if (!headers_sent()) {
            http_response_code($estado);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** No instanciable: solo métodos estáticos. */
    private function __construct()
    {
    }
}
