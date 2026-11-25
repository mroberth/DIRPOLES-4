<?php
/**
 * DIRPOLES-4 — Script Multiplataforma de Importación de Seeds (Tanda 2)
 * Ubicación: scripts/importar_seed_nuevas.php
 *
 * Ejecuta en orden topológico estricto los 8 archivos de seed sintéticos
 * ubicados en docs/bd/seed_nuevas/ utilizando las credenciales configuradas
 * en el archivo .env.
 *
 * Compatible con Linux, macOS y Windows (XAMPP).
 *
 * Uso:
 *   php scripts/importar_seed_nuevas.php
 */

declare(strict_types=1);

$baseDir = dirname(__DIR__);
$seedsDir = $baseDir . '/docs/bd/seed_nuevas';
$envFile  = $baseDir . '/.env';

echo "======================================================================\n";
echo "   DIRPOLES-4 — IMPORTADOR DE SEEDS SINTÉTICOS (TANDA 2: 2024 - 2026)  \n";
echo "======================================================================\n\n";

// 1. Cargar variables de entorno
$config = [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'dirpoles_business',
    'DB_USER' => 'root',
    'DB_PASS' => '',
];

if (file_exists($envFile)) {
    echo "[INFO] Leyendo variables de entorno desde .env...\n";
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }
    }
} else {
    echo "[AVISO] Archivo .env no encontrado. Usando valores predeterminados.\n";
}

// 2. Localizar el binario de mysql
function obtenerBinarioMysql(): string {
    if (PHP_OS_FAMILY === 'Windows') {
        $candidatos = [
            'mysql',
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'C:\\wamp64\\bin\\mysql\\mysql.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe',
        ];
        foreach ($candidatos as $cmd) {
            $test = @exec("where \"$cmd\" 2>NUL");
            if (!empty($test) || file_exists($cmd)) {
                return $cmd;
            }
        }
        return 'mysql';
    }

    $salida = trim((string) @shell_exec('which mysql 2>/dev/null'));
    return !empty($salida) ? $salida : 'mysql';
}

$mysqlBin = obtenerBinarioMysql();

// 3. Probar conexión básica
echo "[INFO] Verificando conexión con el servidor MySQL ({$config['DB_HOST']})...\n";
try {
    $dsn = "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "[OK] Conexión establecida con la base de datos '{$config['DB_NAME']}'.\n\n";
} catch (Throwable $e) {
    fwrite(STDERR, "[ERROR] No se pudo conectar a la base de datos: " . $e->getMessage() . "\n");
    fwrite(STDERR, "        Verifica que MySQL esté corriendo y las credenciales en .env sean correctas.\n");
    exit(1);
}

// 4. Lista ordenada de seeds
$seeds = [
    'seed_beneficiarios2.sql',
    'seed_citas2.sql',
    'seed_psicologia2.sql',
    'seed_medicina2.sql',
    'seed_orientacion2.sql',
    'seed_discapacidad2.sql',
    'seed_trabajo_social2.sql',
    'seed_referencias2.sql',
];

$total = count($seeds);
$exitos = 0;

echo "Iniciando importación secuencial de {$total} archivos:\n";
echo "----------------------------------------------------------------------\n";

foreach ($seeds as $idx => $archivo) {
    $num = $idx + 1;
    $rutaArchivo = $seedsDir . '/' . $archivo;

    echo sprintf("[%d/%d] Importando %-28s ... ", $num, $total, $archivo);

    if (!file_exists($rutaArchivo)) {
        echo "[ERROR: Archivo no encontrado]\n";
        fwrite(STDERR, "        Ruta esperada: {$rutaArchivo}\n");
        exit(1);
    }

    // Construcción del comando seguro
    $passPart = !empty($config['DB_PASS']) ? '-p' . escapeshellarg($config['DB_PASS']) : '';
    $cmd = sprintf(
        '%s --default-character-set=utf8mb4 -h %s -u %s %s %s < %s',
        escapeshellcmd($mysqlBin),
        escapeshellarg($config['DB_HOST']),
        escapeshellarg($config['DB_USER']),
        $passPart,
        escapeshellarg($config['DB_NAME']),
        escapeshellarg($rutaArchivo)
    );

    $retCode = 0;
    $output = [];
    exec($cmd, $output, $retCode);

    if ($retCode === 0) {
        echo "[OK]\n";
        $exitos++;
    } else {
        echo "[FALLÓ]\n";
        fwrite(STDERR, "[ERROR] Código de salida {$retCode} al importar {$archivo}.\n");
        exit(1);
    }
}

echo "----------------------------------------------------------------------\n";
echo "¡Importación completada con éxito! ({$exitos} de {$total} archivos aplicados).\n\n";

// 5. Estadísticas consolidadas
echo "======================================================================\n";
echo "                RESUMEN DE REGISTROS EN BASE DE DATOS                 \n";
echo "======================================================================\n";

$consultas = [
    'Beneficiarios'               => 'SELECT COUNT(*) FROM beneficiario',
    'Citas de Psicología'         => 'SELECT COUNT(*) FROM citas',
    'Consultas de Psicología'     => 'SELECT COUNT(*) FROM psicologia',
    'Consultas de Medicina'       => 'SELECT COUNT(*) FROM medicina',
    'Atenciones de Orientación'   => 'SELECT COUNT(*) FROM orientacion',
    'Expedientes de Discapacidad' => 'SELECT COUNT(*) FROM discapacidad',
    'Becas Estudiantiles'         => 'SELECT COUNT(*) FROM becas',
    'Exoneraciones de Aranceles'  => 'SELECT COUNT(*) FROM exoneracion',
    'Ayudas Médicas FAMES'        => 'SELECT COUNT(*) FROM fames',
    'Atención a Gestantes'        => 'SELECT COUNT(*) FROM gestion_emb',
    'Referencias Registradas'     => 'SELECT COUNT(*) FROM referencias',
    'Trazabilidad Log Referencias'=> 'SELECT COUNT(*) FROM log_referencias',
];

printf("%-35s | %10s\n", "Módulo / Concepto", "Total Filas");
echo str_repeat('-', 48) . "\n";

foreach ($consultas as $etiqueta => $sql) {
    try {
        $stmt = $pdo->query($sql);
        $conteo = (int) $stmt->fetchColumn();
        printf("%-35s | %10d\n", $etiqueta, $conteo);
    } catch (Throwable $e) {
        printf("%-35s | %10s\n", $etiqueta, 'Error');
    }
}

echo "\nDistribución de Referencias por Estado:\n";
try {
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM referencias GROUP BY estado");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("  - %-12s: %d\n", $row['estado'], (int)$row['total']);
    }
} catch (Throwable $e) {
    echo "  (No se pudo obtener la distribución de referencias)\n";
}

echo "\n[LISTO] Todos los datos sintéticos (2024 - 17/11/2026) están listos para el microservicio de IA.\n";
