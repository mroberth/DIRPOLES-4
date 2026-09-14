<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Límite de peticiones excedido</title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>dist/img/dirpoles.ico" type="image/x-icon">
    <link href="<?= BASE_URL ?>plugins/fonts/oufit/oufit.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: #f8fafc;
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 450px;
            width: 90%;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #f43f5e, #fb7185);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #f1f5f9;
        }

        p {
            color: #94a3b8;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .timer {
            font-size: 1.2rem;
            font-weight: 600;
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.1);
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            display: inline-block;
            margin-bottom: 2rem;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        .btn-retry {
            display: inline-block;
            background: linear-gradient(to right, #6366f1, #4f46e5);
            color: white;
            text-decoration: none;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2);
        }

        .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon">⚠️</div>
        <h1>Límite Excedido</h1>
        <p>Has realizado demasiadas solicitudes en poco tiempo. Por favor, espera antes de continuar.</p>
        <div class="timer">Reintentar en <span id="countdown"><?= htmlspecialchars($retryAfter) ?></span>s</div>
        <div>
            <a href="" class="btn-retry" onclick="location.reload(); return false;">Actualizar</a>
        </div>
    </div>
    <script>
        let seconds = <?= (int)$retryAfter ?>;
        const countdownEl = document.getElementById('countdown');
        const interval = setInterval(() => {
            seconds--;
            if (seconds <= 0) {
                clearInterval(interval);
                location.reload();
            } else {
                countdownEl.textContent = seconds;
            }
        }, 1000);
    </script>
</body>

</html>
