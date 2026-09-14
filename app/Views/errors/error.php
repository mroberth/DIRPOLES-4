<?php
/**
 * app/Views/errors/error.php
 * ---------------------------------------------------------------
 * Página de error genérica (puerta HTML). La muestra el handler
 * global de index.php cuando una excepción escapa en una página.
 * El error REAL nunca se muestra aquí: se registra en logs/.
 *
 * Variables disponibles del handler:
 *   $errorDebug → string|null (solo con APP_DEBUG=true, para desarrollo)
 */
$titulo = "Error";
$httpCode = http_response_code() ?: 500;
$mensajes = [
    400 => 'Solicitud incorrecta',
    403 => 'Acceso denegado',
    404 => 'Página no encontrada',
    405 => 'Método no permitido',
    500 => 'Error interno del servidor',
];
$tituloError = $mensajes[$httpCode] ?? 'Error inesperado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloError ?> — DIRPOLES</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: #f4f6f9; color: #333;
        }
        .error-box { text-align: center; padding: 3rem 2rem; max-width: 480px; }
        .codigo { font-size: 5rem; font-weight: 800; color: #4e73df; line-height: 1; }
        .titulo { font-size: 1.4rem; font-weight: 600; margin: 1rem 0 .5rem; }
        .mensaje { color: #6c757d; margin-bottom: 1.5rem; }
        .debug {
            text-align: left; background: #fff3cd; border: 1px solid #ffeeba;
            color: #856404; padding: .75rem 1rem; border-radius: .35rem;
            font-family: monospace; font-size: .85rem; margin-bottom: 1.5rem;
            word-break: break-word;
        }
        .btn-volver {
            display: inline-block; background: #4e73df; color: #fff; text-decoration: none;
            padding: .6rem 1.4rem; border-radius: .35rem; font-weight: 600;
        }
        .btn-volver:hover { background: #3a5ccc; }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="codigo"><?= (int) $httpCode ?></div>
        <div class="titulo"><?= htmlspecialchars($tituloError) ?></div>
        <p class="mensaje">Ocurrió un problema al procesar tu solicitud. El error fue registrado en el servidor.</p>

        <?php if (!empty($errorDebug)): ?>
            <div class="debug"><?= htmlspecialchars($errorDebug) ?></div>
        <?php endif; ?>

        <a class="btn-volver" href="<?= BASE_URL ?>login">Volver al inicio de sesión</a>
    </div>
</body>
</html>
