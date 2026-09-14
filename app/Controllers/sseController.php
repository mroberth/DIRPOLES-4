<?php

use App\Models\NotificacionesModel;

/**
 * Controlador del streaming SSE de notificaciones — SOLO funciones.
 * ---------------------------------------------------------------
 * PUERTA ESPECIAL: responde text/event-stream (no HTML ni JSON), por
 * eso vive fuera de api/* y NO usa Respuesta. Es la excepción deliberada
 * a la regla de las dos puertas que ya existía en el sistema antiguo.
 *
 * Flujo:
 *   1. Los middlewares globales (RateLimit + SessionAuth) ya validaron
 *      sesión y JWT antes de llegar aquí (Router los ejecuta primero).
 *   2. Leemos el id_empleado de la sesión y la CERRAMOS con
 *      session_write_close(): la conexión es larga y no debe bloquear
 *      las demás peticiones del mismo navegador (PHP bloquea por sesión).
 *   3. Loop: consulta notificaciones nuevas (id > ultimoId) cada 3s,
 *      emite el evento 'nueva-notificacion' y un heartbeat cada 15s.
 *   4. Aquí SÍ hay try/catch interno: es un loop de streaming que debe
 *      seguir vivo; un error puntual se envía al cliente como evento
 *      'error' en lugar de tumbar la conexión.
 */
function streamNotificaciones(): void
{
    // ---- Cabeceras SSE ANTES que cualquier otra cosa ----
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no'); // evita buffering con PHP-FPM/nginx

    if (function_exists('apache_setenv')) {
        apache_setenv('no-gzip', '1'); // la compresión rompe el streaming
    }

    set_time_limit(0);
    ignore_user_abort(true);

    $idEmpleado = $_SESSION['id_empleado'] ?? null;
    if (!$idEmpleado) {
        echo "event: error\n";
        echo "data: {\"mensaje\": \"No autenticado\"}\n\n";
        flush();
        exit();
    }

    // La sesión ya no se necesita: liberar el lock para el resto del sitio.
    session_write_close();

    $ultimoId = max(0, (int) ($_GET['ultimoId'] ?? 0));

    $modelo = new NotificacionesModel();
    $iteracion = 0;

    while (true) {
        // Cliente desconectado → terminar el worker limpio.
        if (connection_aborted()) {
            break;
        }

        try {
            $modelo->__set('id_empleado', $idEmpleado);
            $modelo->__set('ultimoId', $ultimoId);

            $nuevas = $modelo->manejarAccion('nuevas_sse');

            foreach ($nuevas as $notif) {
                // El cliente reanuda desde el ID más alto que ya recibió.
                if ($notif['id'] > $ultimoId) {
                    $ultimoId = (int) $notif['id'];
                }

                echo "event: nueva-notificacion\n";
                echo 'data: ' . json_encode($notif, JSON_UNESCAPED_UNICODE) . "\n\n";

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                usleep(50000); // pequeña pausa para no saturar
            }
        } catch (Throwable $e) {
            // Un fallo puntual NO tumba la conexión: se avisa al cliente.
            error_log('SSE ERROR: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());

            // Si la conexión PDO murió (MySQL reiniciado o socket cerrado con
            // "2006 MySQL server has gone away"), se reabre para no quedar
            // fallando en cada iteración.
            $modelo->manejarAccion('reconectar');

            echo "event: error\n";
            echo "data: {\"mensaje\": \"Error interno del servidor\"}\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }

        // Heartbeat cada ~15s (5 iteraciones × 3s): mantiene viva la conexión.
        if ($iteracion % 5 === 0) {
            echo ": heartbeat\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }

        sleep(3);
        $iteracion++;

        // Límite de seguridad: ~1 hora de conexión continua (3s × 1200).
        if ($iteracion >= 1200) {
            break;
        }
    }
}