<?php
/**
 * app/Views/errors/404.php
 * ---------------------------------------------------------------
 * Página 404 dedicada (puerta HTML). La incluye:
 *   - Router::rutaNoEncontrada()  → cuando la URL no coincide con
 *     ninguna ruta registrada en app/routes/*.
 *   - Respuesta::manejarExcepcion() → cuando una página lanza un
 *     error de negocio 404 (ExcepcionApi::noEncontrado()).
 *
 * Es AUTOCONTENIDA (no incluye head/sidebar/header) para funcionar
 * igual con o sin sesión: si el usuario está logueado ofrece "Volver
 * al inicio"; si no, "Iniciar sesión".
 *
 * El código HTTP (404) lo fija quien la incluye, no esta vista.
 */
$logueado   = isset($_SESSION['id_empleado']);
$destino    = $logueado ? BASE_URL . 'inicio' : BASE_URL . 'login';
$textoBoton = $logueado ? 'Volver al inicio' : 'Iniciar sesión';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada — DIRPOLES</title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>dist/img/dirpoles.ico" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0044ff 0%, #0033cc 100%);
            color: #1f2937;
            padding: 1.5rem;
        }

        .error-box {
            text-align: center;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            animation: aparecer .5s ease-out;
        }

        @keyframes aparecer {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            width: 84px;
            height: auto;
            object-fit: contain;
            margin-bottom: 1rem;
        }

        .codigo {
            font-size: 5.5rem;
            font-weight: 800;
            line-height: 1;
            color: #0044ff;
        }

        .titulo {
            font-size: 1.5rem;
            font-weight: 700;
            margin: .75rem 0 .5rem;
        }

        .mensaje {
            color: #6b7280;
            margin-bottom: 1.75rem;
            line-height: 1.5;
        }

        .btn-volver {
            display: inline-block;
            background: linear-gradient(180deg, #0044ff 0%, #0033cc 100%);
            color: #fff;
            text-decoration: none;
            padding: .7rem 1.6rem;
            border-radius: 10px;
            font-weight: 600;
            transition: transform .1s ease, box-shadow .1s ease;
        }

        .btn-volver:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(0, 68, 255, .25);
        }
    </style>
</head>

<body>
    <div class="error-box">
        <img class="logo" src="<?= BASE_URL ?>dist/img/dirpoles_opt.png" alt="DIRPOLES">
        <div class="codigo">404</div>
        <h1 class="titulo">Página no encontrada</h1>
        <p class="mensaje">
            La página que buscas no existe o fue movida. Verifica la dirección
            e intenta de nuevo.
        </p>
        <a class="btn-volver" href="<?= $destino ?>"><?= $textoBoton ?></a>
    </div>
</body>

</html>
