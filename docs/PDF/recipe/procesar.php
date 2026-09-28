<?php

require_once BASE_PATH . 'docs/PDF/fpdf/fpdf.php';

/**
 * GenerarRecipe — Recipe médico (tercera puerta PDF, solo Medicina).
 *
 * Adaptado del sistema viejo (PDF/MEDICINA/procesar.php) al patrón de
 * clases de este repo (precedente: GenerarConstancia/GenerarReferencia).
 * Dibuja texto sobre docs/PDF/recipe/recipe_medico.png (210x200 mm según
 * el sistema viejo; se conservan sus dimensiones).
 *
 * La plantilla es una doble talonaria: IZQUIERDA con título "TRATAMIENTO"
 * y DERECHA (talón) con título "INDICACIONES". Decisión del usuario:
 *   - Izquierda  → diagnóstico + tratamiento (coords del sistema viejo).
 *   - Derecha    → observaciones como "INDICACIONES" (coord nueva).
 *   - Fecha      → fecha de EMISIÓN (día en que se genera el PDF).
 *
 * Coordenadas: las VIEJAS se conservan tal cual (calibradas a mano en el
 * sistema viejo); las NUEVAS están marcadas CALIBRAR — si el usuario
 * reporta desalineación, se ajustan SOLO esas constantes.
 */
class GenerarRecipe
{
    // ---------- VIEJAS: calibradas en el sistema viejo (NO mover) ----------
    private const X_NOMBRE_IZQ = 20.0;      // "PACIENTE:" (lado izquierdo)
    private const Y_NOMBRE_IZQ = 35.0;
    private const X_CEDULA_IZQ = 64.0;      // "CI:" (lado izquierdo)
    private const Y_CEDULA_IZQ = 35.0;
    private const X_FECHA_IZQ = 16.0;       // "FECHA" (lado izquierdo)
    private const Y_FECHA_IZQ = 42.0;

    private const X_NOMBRE_DER = 125.0;     // "PACIENTE:" (talón derecho)
    private const Y_NOMBRE_DER = 33.0;
    private const X_CEDULA_DER = 175.0;     // "CI:" (talón derecho)
    private const Y_CEDULA_DER = 32.0;
    private const X_FECHA_DER = 120.0;      // "FECHA" (talón derecho)
    private const Y_FECHA_DER = 40.0;

    private const Y_MEDICO = 6.0;           // cabecera blanca "DR MEDICINA"
    private const X_MEDICO_IZQ = 50.0;
    private const X_MEDICO_DER = 150.0;

    private const X_DIAGNOSTICO = 15.0;     // bloque 1 del lado izquierdo
    private const Y_DIAGNOSTICO = 70.0;
    private const X_TRATAMIENTO = 15.0;     // bloque 2 del lado izquierdo
    private const Y_TRATAMIENTO = 100.0;

    // ---------- NUEVAS: calibrar visualmente si el usuario lo indica ----------
    private const X_INDICACIONES = 120.0;   // talón derecho, bajo "INDICACIONES"
    private const Y_INDICACIONES = 70.0;

    // ---------- Medidas de los bloques MultiCell ----------
    private const ANCHO_BLOQUE = 80;        // igual que el sistema viejo
    private const ALTO_LINEA = 8;           // igual que el sistema viejo

    // Límites de truncado (evitan que un bloque invada al siguiente)
    private const MAX_DIAGNOSTICO = 150;    // y=70 → y=100 (3 líneas)
    private const MAX_TRATAMIENTO = 250;    // y=100 → pie (5 líneas)
    private const MAX_INDICACIONES = 400;   // talón derecho (holgado)

    /** Emite el recipe inline (inline + exit: es una descarga de archivo). */
    public static function generar(array $datos): void
    {
        $beneficiario = trim((string) ($datos['beneficiario'] ?? ''));
        $cedula = trim((string) ($datos['cedula'] ?? ''));
        $empleado = trim((string) ($datos['empleado'] ?? ''));
        if ($beneficiario === '' || $cedula === '') {
            throw \App\Core\ExcepcionApi::validacion('Faltan datos obligatorios para generar el recipe médico.');
        }

        $diagnostico = self::cortar((string) ($datos['diagnostico'] ?? ''), self::MAX_DIAGNOSTICO);
        $tratamiento = self::cortar((string) ($datos['tratamiento'] ?? ''), self::MAX_TRATAMIENTO);
        $indicaciones = self::cortar((string) ($datos['observaciones'] ?? ''), self::MAX_INDICACIONES);

        // Emisión: hoy (la fecha de atención no tiene campo en esta plantilla).
        $fecha = (new DateTimeImmutable('today'))->format('d/m/Y');

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->Image(BASE_PATH . 'docs/PDF/recipe/recipe_medico.png', 0, 0, 210, 200);
        $pdf->SetFont('Arial', '', 12);

        // ----- Cabecera: nombre del médico en blanco sobre la banda azul -----
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(self::X_MEDICO_IZQ, self::Y_MEDICO);
        $pdf->Cell(100, 10, self::txt($empleado));

        $pdf->SetTextColor(0, 0, 0);

        // ----- Lado izquierdo: identificación (coords viejas) -----
        $pdf->SetXY(self::X_NOMBRE_IZQ, self::Y_NOMBRE_IZQ);
        $pdf->Cell(60, 10, self::txt($beneficiario));

        $pdf->SetXY(self::X_CEDULA_IZQ, self::Y_CEDULA_IZQ);
        $pdf->Cell(60, 10, self::txt($cedula));

        $pdf->SetXY(self::X_FECHA_IZQ, self::Y_FECHA_IZQ);
        $pdf->Cell(40, 10, $fecha);

        // ----- Lado izquierdo: TRATAMIENTO (diagnóstico + tratamiento) -----
        if ($diagnostico !== '') {
            $pdf->SetXY(self::X_DIAGNOSTICO, self::Y_DIAGNOSTICO);
            $pdf->MultiCell(self::ANCHO_BLOQUE, self::ALTO_LINEA, self::txt($diagnostico));
        }
        if ($tratamiento !== '') {
            $pdf->SetXY(self::X_TRATAMIENTO, self::Y_TRATAMIENTO);
            $pdf->MultiCell(self::ANCHO_BLOQUE, self::ALTO_LINEA, self::txt($tratamiento));
        }

        // ----- Talón derecho: identificación (coords viejas) -----
        $pdf->SetXY(self::X_NOMBRE_DER, self::Y_NOMBRE_DER);
        $pdf->Cell(80, 10, self::txt($beneficiario));

        $pdf->SetXY(self::X_CEDULA_DER, self::Y_CEDULA_DER);
        $pdf->Cell(60, 10, self::txt($cedula));

        $pdf->SetXY(self::X_FECHA_DER, self::Y_FECHA_DER);
        $pdf->Cell(40, 10, $fecha);

        // ----- Talón derecho: INDICACIONES (coord nueva CALIBRAR) -----
        if ($indicaciones !== '') {
            $pdf->SetXY(self::X_INDICACIONES, self::Y_INDICACIONES);
            $pdf->MultiCell(self::ANCHO_BLOQUE, self::ALTO_LINEA, self::txt($indicaciones));
        }

        // ----- Cabecera del talón: médico en blanco sobre la banda azul -----
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(self::X_MEDICO_DER, self::Y_MEDICO);
        $pdf->Cell(100, 10, self::txt($empleado));

        $pdf->SetTextColor(0, 0, 0);

        // 'I' = mostrar en el navegador (pestaña nueva); luego corta la petición.
        $pdf->Output('I', 'recipe_medico.pdf');
        exit;
    }

    /** Recorta un texto a $max caracteres (multi-byte) con "..." final. */
    private static function cortar(string $texto, int $max): string
    {
        $texto = trim($texto);
        if (mb_strlen($texto) > $max) {
            return mb_substr($texto, 0, $max - 3) . '...';
        }
        return $texto;
    }

    /** UTF-8 → ISO-8859-1: las core fonts de FPDF no soportan UTF-8. */
    private static function txt(string $texto): string
    {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }
}
