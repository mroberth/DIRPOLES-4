<?php

require_once BASE_PATH . 'docs/PDF/fpdf/fpdf.php';

/**
 * GenerarConstancia — Constancia de Atención (tercera puerta PDF).
 *
 * Adaptado del sistema viejo (PDF/constancia/procesar.php) al patrón de
 * clases de este repo (precedente: GenerarPDF en docs/PDF/EstudioSE/).
 * Dibuja texto sobre docs/PDF/constancia/constancia.png (1063x1418 px →
 * 210x297 mm; escala X 0.1975 mm/px, escala Y 0.2095 mm/px).
 *
 * Reglas de fecha (decisión del usuario):
 *   - "del día"         → fecha de ATENCIÓN (fecha_creacion del registro).
 *   - "a los días ..."   → fecha de EMISIÓN (día en que se genera el PDF).
 *   - "en horas de"     → hora elegida en el diálogo (vacío = se omite).
 *   - "para el trámite" → texto del diálogo (máx. 2 líneas en la plantilla).
 *
 * Coordenadas: las VIEJAS se conservan tal cual (calibradas a mano en el
 * sistema viejo); las NUEVAS están agrupadas como constantes CALIBRAR — si
 * el usuario reporta desalineación, se ajustan SOLO esas constantes.
 */
class GenerarConstancia
{
    // ---------- NUEVAS: calibrar visualmente si el usuario lo indica ----------
    private const X_HORA = 135.0;       // "en horas de ______"
    private const Y_HORA = 108.8;
    private const W_HORA = 24.0;

    private const X_DIA = 20.7;         // "______ para el trámite de" (inicio)
    private const Y_DIA = 120.3;        // valor de "del día: ..." (1ra línea en blanco)
    private const W_DIA = 44.0;

    private const X_TRAMITE = 121.5;    // después de "para el trámite de"
    private const Y_TRAMITE = 120.3;    // misma línea que "del día"
    private const W_TRAMITE_1 = 66.0;   // hasta el borde derecho de la línea

    private const Y_TRAMITE_2 = 131.8;  // 2da línea (continuación del trámite)
    private const W_TRAMITE_2 = 166.0;  // ancho completo

    private const ALTO = 10;            // alto de celda (igual que nombre/cédula)

    /** Emite la constancia inline (inline + exit: es una descarga de archivo). */
    public static function generar(array $datos): void
    {
        $beneficiario = trim((string) ($datos['beneficiario'] ?? ''));
        $cedula = trim((string) ($datos['cedula'] ?? ''));
        if ($beneficiario === '' || $cedula === '') {
            throw \App\Core\ExcepcionApi::validacion('Faltan datos obligatorios para generar la constancia.');
        }

        $fechaAtencion = self::fechaAtencion((string) ($datos['fecha'] ?? ''));
        $hora = trim((string) ($datos['hora'] ?? ''));
        $tramite = trim((string) ($datos['tramite'] ?? ''));

        $hoy = new DateTimeImmutable('today');
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->Image(BASE_PATH . 'docs/PDF/constancia/constancia.png', 0, 0, 210, 297);
        $pdf->SetFont('Arial', '', 14);

        // ----- Campos calibrados del sistema viejo (NO mover) -----
        $pdf->SetXY(20.7, 63.6);
        $pdf->Cell(185, self::ALTO, self::txt($beneficiario));

        $pdf->SetXY(126.2, 75.5);
        $pdf->Cell(75, self::ALTO, self::txt($cedula));

        // ----- Campos nuevos: hora, del día y trámite -----
        if ($hora !== '') {
            $pdf->SetXY(self::X_HORA, self::Y_HORA);
            $pdf->Cell(self::W_HORA, self::ALTO, self::txt($hora));
        }
        if ($fechaAtencion !== '') {
            $pdf->SetXY(self::X_DIA, self::Y_DIA);
            $pdf->Cell(self::W_DIA, self::ALTO, self::txt($fechaAtencion));
        }
        [$linea1, $linea2] = self::partirTramite($pdf, $tramite);
        if ($linea1 !== '') {
            $pdf->SetXY(self::X_TRAMITE, self::Y_TRAMITE);
            $pdf->Cell(self::W_TRAMITE_1, self::ALTO, self::txt($linea1));
        }
        if ($linea2 !== '') {
            $pdf->SetXY(self::X_DIA, self::Y_TRAMITE_2);
            $pdf->Cell(self::W_TRAMITE_2, self::ALTO, self::txt($linea2));
        }

        // ----- Fecha de emisión (coords viejas: día / mes / año 2 dígitos) -----
        $pdf->SetXY(20.7, 167.0);
        $pdf->Cell(25, 8, $hoy->format('d'));

        $pdf->SetXY(72.3, 167.0);
        $pdf->Cell(60, 8, self::txt($meses[(int) $hoy->format('m')]));

        $pdf->SetXY(130.0, 167.0);
        $pdf->Cell(25, 8, $hoy->format('y'));

        // 'I' = mostrar en el navegador (pestaña nueva); luego corta la petición.
        $pdf->Output('I', 'constancia_atencion.pdf');
        exit;
    }

    /** Fecha de atención en dd/mm/aaaa; '' si no es parseable. */
    private static function fechaAtencion(string $fecha): string
    {
        if ($fecha === '') {
            return '';
        }
        $obj = DateTimeImmutable::createFromFormat('Y-m-d', substr($fecha, 0, 10));
        return $obj ? $obj->format('d/m/Y') : '';
    }

    /**
     * Parte el trámite en como máximo 2 líneas (la plantilla solo tiene 2
     * espacios): lo que quepa después de "para el trámite de" y el resto en
     * la línea siguiente de ancho completo; si aún así desborda, "..." final.
     */
    private static function partirTramite(FPDF $pdf, string $texto): array
    {
        if ($texto === '') {
            return ['', ''];
        }
        if ($pdf->GetStringWidth($texto) <= self::W_TRAMITE_1) {
            return [$texto, ''];
        }

        $palabras = preg_split('/\s+/', $texto) ?: [];
        $linea1 = '';
        $linea2 = '';
        $i = 0;
        for ($i = 0; $i < count($palabras); $i++) {
            $candidato = $linea1 === '' ? $palabras[$i] : $linea1 . ' ' . $palabras[$i];
            if ($linea1 !== '' && $pdf->GetStringWidth($candidato) > self::W_TRAMITE_1) {
                break;
            }
            $linea1 = $candidato;
        }
        for ($j = $i; $j < count($palabras); $j++) {
            $palabra = $palabras[$j];
            $candidato = $linea2 === '' ? $palabra : $linea2 . ' ' . $palabra;
            if ($pdf->GetStringWidth($candidato) > self::W_TRAMITE_2) {
                $linea2 = ($linea2 === '' ? mb_substr($palabra, 0, 30) : $linea2) . '...';
                return [$linea1, $linea2];
            }
            $linea2 = $candidato;
        }
        return [$linea1, $linea2];
    }

    /** UTF-8 → ISO-8859-1: las core fonts de FPDF no soportan UTF-8. */
    private static function txt(string $texto): string
    {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }
}
