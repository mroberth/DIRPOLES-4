<?php
require_once BASE_PATH . '/PDF/fpdf/fpdf.php';

// ============================================================
// CONSTANCIA DE ATENCIÓN — PDF generado por DIRPOLES 4
// ============================================================
// Variables esperadas del controller:
//   $beneficiario  → Nombre completo del beneficiario
//   $cedula        → Cédula del beneficiario
//   $fecha         → Fecha de atención
//   $data          → Array con datos del diagnóstico/consulta (opcional)
// ============================================================

if (empty($beneficiario) || empty($cedula)) {
    die("Faltan datos obligatorios para generar la constancia.");
}

// Formatear la fecha
if (!empty($fecha)) {
    $fechaObj = DateTime::createFromFormat('Y-m-d', substr($fecha, 0, 10));
    if ($fechaObj) {
        $dia  = $fechaObj->format('d');
        $anio = $fechaObj->format('y');
        // Mes en español
        $meses = ['01'=>'enero','02'=>'febrero','03'=>'marzo','04'=>'abril','05'=>'mayo','06'=>'junio','07'=>'julio','08'=>'agosto','09'=>'septiembre','10'=>'octubre','11'=>'noviembre','12'=>'diciembre'];
        $mes = $meses[$fechaObj->format('m')] ?? $fechaObj->format('m');
    } else {
        $dia  = date('d');
        $anio = date('y');
        $meses = ['01'=>'enero','02'=>'febrero','03'=>'marzo','04'=>'abril','05'=>'mayo','06'=>'junio','07'=>'julio','08'=>'agosto','09'=>'septiembre','10'=>'octubre','11'=>'noviembre','12'=>'diciembre'];
        $mes = $meses[date('m')] ?? date('m');
    }
} else {
    $dia  = date('d');
    $mes  = date('m');
    $anio = date('Y');
}

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();

// Imagen de fondo (template) — 1063x1418 px → 210x297 mm
$pdf->Image(BASE_PATH . '/PDF/constancia/constancia.png', 0, 0, 210, 297);

// --- CAMPOS SUPERPUESTOS SOBRE LA IMAGEN ---
// Coordenadas convertidas de pixels a mm:
//   Escala X: 210mm / 1063px = 0.1975 mm/px
//   Escala Y: 297mm / 1418px = 0.2095 mm/px

$pdf->SetFont('Arial', '', 14);

// Nombre del beneficiario
$pdf->SetXY(20.7, 63.6);
$pdf->Cell(185, 10, utf8_decode($beneficiario));

// Cédula — subido 1.7mm
$pdf->SetXY(126.2, 75.5);
$pdf->Cell(75, 10, utf8_decode($cedula));

// Fecha — Días — subido 3mm
$pdf->SetXY(20.7, 167.0);
$pdf->Cell(25, 8, $dia);

// Fecha — Mes
$pdf->SetXY(72.3, 167.0);
$pdf->Cell(60, 8, utf8_decode($mes));

// Fecha — Año — movido a la izquierda
$pdf->SetXY(130.0, 167.0);
$pdf->Cell(25, 8, $anio);

// Salida del PDF
$pdf->Output('I', 'constancia_atencion.pdf');
exit();
