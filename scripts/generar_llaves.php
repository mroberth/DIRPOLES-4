<?php
/**
 * DIRPOLES-4 — Generador Criptográfico de Llaves RSA
 *
 * Genera de forma multiplataforma (Windows / Linux / macOS) los pares de llaves RSA-2048:
 *  - login_private.pem / login_public.pem  (Cifrado de contraseña en tránsito)
 *  - jwt_private.pem   / jwt_public.pem    (Firma asimétrica RS256 de tokens)
 *
 * Uso:
 *   php scripts/generar_llaves.php
 */

$directorioLlaves = dirname(__DIR__) . '/app/Config/Keys';

if (!is_dir($directorioLlaves)) {
    if (!mkdir($directorioLlaves, 0755, true)) {
        fwrite(STDERR, "[ERROR] No se pudo crear el directorio: {$directorioLlaves}\n");
        exit(1);
    }
}

echo "=== DIRPOLES-4: Generador de Llaves Criptográficas RSA ===\n\n";

function generarParRSA(string $nombre, string $directorio): void {
    $privadaPath = "{$directorio}/{$nombre}_private.pem";
    $publicaPath = "{$directorio}/{$nombre}_public.pem";

    if (file_exists($privadaPath) && file_exists($publicaPath)) {
        echo "  [INFO] Las llaves para '{$nombre}' ya existen. Omitiendo...\n";
        return;
    }

    $configuracion = [
        "digest_alg"       => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];

    $recurso = openssl_pkey_new($configuracion);
    if (!$recurso) {
        fwrite(STDERR, "  [ERROR] Falló la creación de la clave RSA para '{$nombre}'. Revisa la configuración de OpenSSL en PHP.\n");
        return;
    }

    $clavePrivada = '';
    openssl_pkey_export($recurso, $clavePrivada);

    $detalles = openssl_pkey_get_details($recurso);
    $clavePublica = $detalles['key'] ?? '';

    file_put_contents($privadaPath, $clavePrivada);
    file_put_contents($publicaPath, $clavePublica);

    // Permisos restringidos si el SO es Unix/Linux
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        chmod($privadaPath, 0600);
        chmod($publicaPath, 0644);
    }

    echo "  [OK] Llaves para '{$nombre}' generadas con éxito.\n";
}

generarParRSA('login', $directorioLlaves);
generarParRSA('jwt', $directorioLlaves);

echo "\nProceso finalizado. Llaves disponibles en: app/Config/Keys/\n";
