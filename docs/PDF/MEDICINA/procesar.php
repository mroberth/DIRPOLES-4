<?php
require_once 'PDF/fpdf/fpdf.php';

// Obtener el id de la consulta médica
$id_consulta_med = isset($_GET['id_consulta_med']) ? intval($_GET['id_consulta_med']) : 0;

if ($id_consulta_med <= 0) {
    die('ID de consulta médica no válido.');
}

if (!$consulta) {
    die('No se encontró la consulta médica.');
}

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('PDF/MEDICINA/recipe_medico.png', 0, 0, 210, 200); // A4: 210mm x 297mm

// Añadir datos a la plantilla
$pdf->SetFont('Arial', '', 12);

// Nombre del beneficiario
$pdf->SetXY(20, 35); // Ajusta la posición X, Y
$pdf->Cell(100, 10, utf8_decode($consulta['nombre_beneficiario']));

$pdf->SetXY(64, 35);
$pdf->Cell(100, 10, utf8_decode($consulta['cedula']));

// Fecha
$pdf->SetXY(16, 42); // Ajusta la posición X, Y
$pdf->Cell(40, 10, date('d/m/Y'));

// Médico
$pdf->SetXY(50, 6);
$pdf->SetTextColor(255, 255, 255);  // Establecer el color del texto a blanco
$pdf->Cell(100, 10, utf8_decode($consulta['nombres_empleado']));

// Establecer un límite en el eje X
$x_limit = 100; // Máximo X permitido para la columna izquierda

// Diagnóstico
$pdf->SetXY(15, 70); // Coordenada inicial
$pdf->SetTextColor(0, 0, 0);
if ($pdf->GetX() + 80 <= $x_limit) { // 80 es el ancho de cada línea
    $pdf->MultiCell(80, 8, utf8_decode($consulta['diagnostico']));
} else {
    $pdf->MultiCell(80, 8, utf8_decode(substr($consulta['diagnostico'], 0, 200)) . '...'); // Truncar si excede
}

// Tratamiento
$pdf->SetXY(15, 100); // Coordenada para la sección de tratamiento
if ($pdf->GetX() + 80 <= $x_limit) {
    $pdf->MultiCell(80, 8, utf8_decode($consulta['tratamiento']));
} else {
    $pdf->MultiCell(80, 8, utf8_decode(substr($consulta['tratamiento'], 0, 200)) . '...');
}


// Observaciones
$pdf->SetXY(15, 140); // Coordenada para la sección de observaciones
if ($pdf->GetX() + 80 <= $x_limit) {
    $pdf->MultiCell(80, 8, utf8_decode($consulta['observaciones']));
} else {
    $pdf->MultiCell(80, 8, utf8_decode(substr($consulta['observaciones'], 0, 200)) . '...');
}



//Pagina de un lado

// Nombre del beneficiario
$pdf->SetXY(125, 33); // Ajusta la posición X, Y
$pdf->Cell(100, 10, utf8_decode($consulta['nombre_beneficiario']));

$pdf->SetXY(175, 32);
$pdf->Cell(100, 10, utf8_decode($consulta['cedula']));

// Fecha
$pdf->SetXY(120, 40); // Ajusta la posición X, Y
$pdf->Cell(40, 10, date('d/m/Y'));

// Médico
$pdf->SetXY(150, 6);
$pdf->SetTextColor(255, 255, 255);  // Establecer el color del texto a blanco
$pdf->Cell(100, 10, utf8_decode($consulta['nombres_empleado']));

// Salida del PDF
$pdf->Output('I', 'recipe_medico.pdf'); // 'I' para mostrar en el navegador
