<?php
require('pdf/fpdf/fpdf.php');

// class GenerarRecipe
// {
//     public static function crearPDF($rutaPDF)
//     {
        // Recoge los datos del formulario
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
        $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : '';
        $centro_salud = isset($_POST['centro_salud']) ? $_POST['centro_salud'] : '';
        $ciudad = isset($_POST['ciudad']) ? $_POST['ciudad'] : '';
        $dia = isset($_POST['dia']) ? $_POST['dia'] : '';
        $mes = isset($_POST['mes']) ? $_POST['mes'] : '';
        $anio = isset($_POST['anio']) ? $_POST['anio'] : '';


        // Crear el PDF
        $pdf = new FPDF();
        $pdf->AddPage();

        // PARA AJUSTAR LA POSICION EN X y Y: PARA X ENTRE MENOR SEA EL NRO, EL ELEMENTO DE POSICIONARA MAS  A LA IZQUIERDA, PARA Y ENTRE MENOR SEA EL NUMERO , EL ELEMENTO SE POSICIONARA MAS ARRIBA

        // Opcional: Colocar la imagen del formulario de fondo
        $pdf->Image('formatoFames.png', 0, 0, 210, 297); // Ajusta las coordenadas y dimensiones según la imagen y tamaño de página (A4 en mm)

        // Añadir los datos en las posiciones correspondientes
        $pdf->SetFont('Arial', 'B', 11);


        // Escribir los datos superpuestos en la imagen (ajusta las coordenadas según la imagen)

        if ($nombre) {
            $pdf->SetXY(25, 57); // Ajusta la posición X, Y
            $pdf->Cell(100, 10, "$nombre");
        }

        if ($cedula) {
            $pdf->SetXY(25, 66.8); // Ajusta la posición X, Y
            $pdf->Cell(100, 10, "$cedula");
        }

        // Definir los anchos y las posiciones para los cuadros rojos
        $ancho_cuadro_1 = 85;  // Ancho del primer cuadro
        $ancho_cuadro_2 = 100; // Ancho del segundo cuadro

        $x_cuadro_1 = 105; // Posición X del primer cuadro
        $y_cuadro_1 = 76.2; // Posición Y del primer cuadro

        $x_cuadro_2 = 19; // Posición X del segundo cuadro
        $y_cuadro_2 = 85.8; // Posición Y del segundo cuadro

        // Extraer el texto que cabe en el primer cuadro
        $linea1 = '';
        $linea2 = '';

        // Límite de caracteres para el primer cuadro
        $limite_caracteres_cuadro_1 = 85; // Ajusta este número según el ancho del cuadro

        // Contar caracteres y acumular texto
        for ($i = 0; $i < strlen($centro_salud); $i++) {
            $caracter_temp = $centro_salud[$i];

            // Verificar si el ancho del texto excede el ancho del primer cuadro
            if ($pdf->GetStringWidth($linea1 . $caracter_temp) > $ancho_cuadro_1) {
                $linea2 .= $caracter_temp;  // Si se excede, agregar al segundo cuadro
            } else {
                $linea1 .= $caracter_temp;  // Si no, continuar acumulando en la primera línea
            }
        }

        // Asegurarse de que no exceda el límite de caracteres en la línea 1
        if (strlen($linea1) > $limite_caracteres_cuadro_1) {
            // Dividir en caso de que se exceda el límite
            $linea2 = substr($linea1, $limite_caracteres_cuadro_1) . $linea2;
            $linea1 = substr($linea1, 0, $limite_caracteres_cuadro_1);
        }

        // Colocar el texto en los cuadros
        $pdf->SetXY($x_cuadro_1, $y_cuadro_1);
        $pdf->Cell($ancho_cuadro_1, 10, $linea1, 0, 1, 'L'); // Primer cuadro

        $pdf->SetXY($x_cuadro_2, $y_cuadro_2);
        $pdf->Cell($ancho_cuadro_2, 10, $linea2, 0, 1, 'L'); // Segundo cuadro

        // Dibujar el texto en el primer cuadro
        $pdf->SetXY($x_cuadro_1, $y_cuadro_1);
        $pdf->Cell($ancho_cuadro_1, 10, $linea1, 0, 1);

        // Dibujar el texto restante en el segundo cuadro
        $pdf->SetXY($x_cuadro_2, $y_cuadro_2);
        $pdf->Cell($ancho_cuadro_2, 10, $linea2, 0, 1);


        if ($ciudad) {
            $pdf->SetXY(63, 158.3); // Ajusta la posición X, Y
            $pdf->Cell(100, 10, "$ciudad");
        }

        if ($dia) {
            $pdf->SetXY(157, 158.3); // Ajusta la posición X, Y
            $pdf->Cell(100, 10, "$dia");
        }

        if ($mes) {
            $pdf->SetXY(40, 167.7); // Ajusta la posición X, Y
            $pdf->Cell(100, 10, "$mes");
        }

        if ($anio) {
            $pdf->SetXY(90, 167.7); // Ajusta la posición X, Y
            $pdf->Cell(100, 10, "$anio");
        }


        // Generar el PDF
        $pdf->Output('F', $rutaPDF);

        return true;
        
        // 'I' para que el PDF se muestre en el navegador, 'D' para descargar, 'F' Guardar el archivo en un archivo local en el servidor, 'S' Devolver el documento como una cadena de caracteres (string), 'FI' Guardar en el servidor y enviar en línea al navegador, 'FD' Guardar en el servidor y forzar la descarga, 'E' Enviar el archivo por correo electrónico (como archivo adjunto).
//     }
// }
