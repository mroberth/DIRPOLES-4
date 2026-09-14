<?php
require_once BASE_PATH . '/PDF/fpdf/fpdf.php';

// ============================================================
// REFERENCIA — PDF generado por DIRPOLES 4
// ============================================================
// Variables esperadas del controller:
//   $data → Array con los datos de la consulta/diagnóstico
//
// Coordenadas obtenidas de signiflow.com/xy (puntos PDF)
// Convertidas a milímetros: mm = puntos / 2.835
// ============================================================

if (empty($data)) {
    die("No se encontraron datos para generar la referencia.");
}

// Extraer datos con soporte para diferentes nombres de columnas
$nombre   = $data['nombre_beneficiario'] ?? $data['nombres'] ?? '';
$apellido = $data['apellido_beneficiario'] ?? $data['apellidos'] ?? '';
$cedula   = $data['cedula'] ?? '';
$cargo    = $data['tipo'] ?? $data['cargo'] ?? '';
$telefono = $data['telefono'] ?? '';
$fecha_raw = $data['fecha_creacion'] ?? $data['fecha_psicologia'] ?? '';

// Empleado: nombre + apellido (sin cédula entre paréntesis)
$empleadoRaw = $data['empleado'] ?? $data['nombres_empleado'] ?? '';
$empleadoNombreApellido = preg_replace('/\s*\([^)]+\)\s*$/', '', trim($empleadoRaw));

// Nombre completo del beneficiario
$nombreCompleto = trim($nombre . ' ' . $apellido);

// Formatear la fecha
if (!empty($fecha_raw)) {
    $fechaObj = DateTime::createFromFormat('Y-m-d', substr($fecha_raw, 0, 10));
    if ($fechaObj) {
        $dia = $fechaObj->format('d');
        $mesNumero = intval($fechaObj->format('m'));
        $anio = $fechaObj->format('y'); // Solo 2 dígitos: 26

        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        $mesLiteral = $meses[$mesNumero] ?? '';
    } else {
        $dia = '';
        $mesLiteral = '';
        $anio = '';
    }
} else {
    $dia = date('d');
    $mesNumero = intval(date('m'));
    $mesLiteral = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'][$mesNumero];
    $anio = date('y');
}

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();

// Imagen de fondo (template) — tamaño A4 completo
$pdf->Image(BASE_PATH . '/PDF/referencia/referencia.png', 0, 0, 210, 297);

// --- CAMPOS SUPERPUESTOS SOBRE LA IMAGEN ---

// Nombre del beneficiario
$pdf->SetFont('Arial', '', 11);
$pdf->SetXY(20.5, 65.7);
$pdf->Cell(120, 8, utf8_decode($nombreCompleto));

// Cédula del beneficiario
$pdf->SetXY(113.9, 77.1);
$pdf->Cell(60, 8, utf8_decode($cedula));

// Día — subido 2mm
$pdf->SetXY(20.5, 203.3);
$pdf->Cell(20, 8, utf8_decode($dia));

// Mes — subido 2mm
$pdf->SetXY(65.6, 203.3);
$pdf->Cell(40, 8, utf8_decode($mesLiteral));

// Año (solo 2 dígitos) — subido 2mm
$pdf->SetXY(129.1, 203.3);
$pdf->Cell(15, 8, utf8_decode($anio));

// Empleado nombre + apellido — subido 2mm
if (!empty($empleadoNombreApellido)) {
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetXY(98.1, 241.0);
    $pdf->Cell(120, 8, utf8_decode($empleadoNombreApellido));
}

// Tipo de empleado (cargo) — subido 2mm
if (!empty($cargo)) {
    $pdf->SetXY(42.0, 253.7);
    $pdf->Cell(120, 8, utf8_decode($cargo));
}

// Teléfono — subido 10mm para que no caiga en página 2
if (!empty($telefono)) {
    $pdf->SetXY(49.4, 265.0);
    $pdf->Cell(60, 8, utf8_decode($telefono));
}

// Salida del PDF
$pdf->Output('I', 'referencia.pdf');
exit();
