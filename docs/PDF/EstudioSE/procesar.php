<?php
require BASE_PATH . '/PDF/fpdf/fpdf.php';

class GenerarPDF
{
    public static function crearPDF($rutaPDF)
    {

        // FOTO

        function fixImageOrientation($ruta_temporal, $extension)
        {
            // Verificar si la librería GD está habilitada verificando una de sus funciones clave
            if (!function_exists('imagecreatefromjpeg')) {
                // Si no existe, no hacemos nada y retornamos. La imagen se usará tal cual está.
                return;
            }

            if (strtolower($extension) === 'jpg' || strtolower($extension) === 'jpeg') {
                if (function_exists('exif_read_data')) {
                    $exif = @exif_read_data($ruta_temporal);
                    isset($exif['Orientation']) ? $orientation = $exif['Orientation'] : '';

                    $image = imagecreatefromjpeg($ruta_temporal);

                    if (isset($orientation)) {
                        switch ($orientation) {
                            case 3:
                                $image = imagerotate($image, 180, 0);
                                break;
                            case 6:
                                $image = imagerotate($image, -90, 0);
                                break;
                            case 8:
                                $image = imagerotate($image, 90, 0);
                                break;
                            default:
                                $image;
                                break;
                        }
                    } else {
                        $image;
                    }

                    imagejpeg($image, $ruta_temporal);

                }
            } elseif (strtolower($extension) === 'png' && function_exists('imagecreatefrompng')) {
                $image = imagecreatefrompng($ruta_temporal);
                imagepng($image, $ruta_temporal);
            } elseif (strtolower($extension) === 'gif' && function_exists('imagecreatefromgif')) {
                $image = imagecreatefromgif($ruta_temporal);
                imagegif($image, $ruta_temporal);
            }
        }

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
            if (in_array($extension, $extensionesPermitidas)) {
                $ruta_temporal = $_FILES['imagen']['tmp_name'];
                fixImageOrientation($ruta_temporal, $extension);
            } else {
                $_SESSION['mensaje'] = "Tipo de archivo no permitido para la imagen del Estudio Socioeconomico. Solo se permiten JPG, JPEG, GIF y PNG.";
                $_SESSION['tipo_mensaje'] = 'error';

                header('location: index.php?action=vista_trabajo_social&formulario=exoneracion');
                exit();
            }
        }

                $renovacion = isset($_POST['renovacion']) ? $_POST['renovacion'] : '';
        $nueva = isset($_POST['nueva']) ? $_POST['nueva'] : '';
        $beneficio = isset($_POST['beneficio']) ? $_POST['beneficio'] : '';
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
        $nombre = mb_convert_encoding(isset($_POST['nombre']) ? $_POST['nombre'] : '', 'ISO-8859-1', 'UTF-8');
        
        $fecha_nac_obj = isset($_POST['fecha_nacimiento']) ? DateTime::createFromFormat('Y-m-d', $_POST['fecha_nacimiento']) : false;
        $fecha_nac_formateada = $fecha_nac_obj ? $fecha_nac_obj->format('d/m/Y') : '';
        $lugarFechaConcat = isset($_POST['nacimiento']) ? $_POST['nacimiento']. ' - ' . $fecha_nac_formateada : '';
        $edad = isset($_POST['edad']) ? $_POST['edad'] : '';
        $edocivil = isset($_POST['estado_civil']) ? $_POST['estado_civil'] : '';
        $ci = isset($_POST['ci']) ? $_POST['ci'] : '';
        $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';
        $tr_si = isset($_POST['tr_si']) ? $_POST['tr_si'] : '';
        $tr_no = isset($_POST['tr_no']) ? $_POST['tr_no'] : '';
        $ocupacion = mb_convert_encoding(isset($_POST['ocupacion']) ? $_POST['ocupacion'] : '', 'ISO-8859-1', 'UTF-8');
        $lugar_trabajo = isset($_POST['lugar_trabajo']) ? $_POST['lugar_trabajo'] : '';
        $sueldo = isset($_POST['sueldo']) ? $_POST['sueldo'] : '';
        $carga_si = isset($_POST['cf_si']) ? $_POST['cf_si'] : '';
        $carga_no = isset($_POST['cf_no']) ? $_POST['cf_no'] : '';
        $hijos = isset($_POST['hijos']) ? $_POST['hijos'] : '';
        $dir_hab = isset($_POST['dir_hab']) ? $_POST['dir_hab'] : '';
        $dir_res = isset($_POST['dir_res']) ? $_POST['dir_res'] : '';
        $especialidad = isset($_POST['especialidad']) ? $_POST['especialidad'] : '';
        $sem_tra = isset($_POST['sem_tra']) ? $_POST['sem_tra'] : '';
        $turno = isset($_POST['turno']) ? $_POST['turno'] : '';
        $seccion = isset($_POST['seccion']) ? $_POST['seccion'] : '';
        $correo = isset($_POST['correo']) ? $_POST['correo'] : '';
        $redes = isset($_POST['redes']) ? $_POST['redes'] : '';

        // TABLA 1 - GRUPO FAMILIAR
        $nombre1 = mb_convert_encoding(isset($_POST['nombre1']) ? $_POST['nombre1'] : '', 'ISO-8859-1', 'UTF-8');
        $edad1 = isset($_POST['edad1']) ? $_POST['edad1'] : '';
        $parentesco1 = isset($_POST['parentesco1']) ? $_POST['parentesco1'] : '';
        $edoCivil1 = isset($_POST['edoCivil1']) ? $_POST['edoCivil1'] : '';
        $gradoInstruccion1 = isset($_POST['gradoInstruccion1']) ? $_POST['gradoInstruccion1'] : '';
        $ocupacion1 = isset($_POST['ocupacion1']) ? $_POST['ocupacion1'] : '';
        $lugarTrabajo1 = isset($_POST['lugarTrabajo1']) ? $_POST['lugarTrabajo1'] : '';
        $sueldo1 = isset($_POST['sueldo1']) ? $_POST['sueldo1'] : '';
        $aporteHogar1 = isset($_POST['aporteHogar1']) ? $_POST['aporteHogar1'] : '';

        $nombre2 = mb_convert_encoding(isset($_POST['nombre2']) ? $_POST['nombre2'] : '', 'ISO-8859-1', 'UTF-8');
        $edad2 = isset($_POST['edad2']) ? $_POST['edad2'] : '';
        $parentesco2 = isset($_POST['parentesco2']) ? $_POST['parentesco2'] : '';
        $edoCivil2 = isset($_POST['edoCivil2']) ? $_POST['edoCivil2'] : '';
        $gradoInstruccion2 = isset($_POST['gradoInstruccion2']) ? $_POST['gradoInstruccion2'] : '';
        $ocupacion2 = isset($_POST['ocupacion2']) ? $_POST['ocupacion2'] : '';
        $lugarTrabajo2 = isset($_POST['lugarTrabajo2']) ? $_POST['lugarTrabajo2'] : '';
        $sueldo2 = isset($_POST['sueldo2']) ? $_POST['sueldo2'] : '';
        $aporteHogar2 = isset($_POST['aporteHogar2']) ? $_POST['aporteHogar2'] : '';

        $nombre3 = mb_convert_encoding(isset($_POST['nombre3']) ? $_POST['nombre3'] : '', 'ISO-8859-1', 'UTF-8');
        $edad3 = isset($_POST['edad3']) ? $_POST['edad3'] : '';
        $parentesco3 = isset($_POST['parentesco3']) ? $_POST['parentesco3'] : '';
        $edoCivil3 = isset($_POST['edoCivil3']) ? $_POST['edoCivil3'] : '';
        $gradoInstruccion3 = isset($_POST['gradoInstruccion3']) ? $_POST['gradoInstruccion3'] : '';
        $ocupacion3 = isset($_POST['ocupacion3']) ? $_POST['ocupacion3'] : '';
        $lugarTrabajo3 = isset($_POST['lugarTrabajo3']) ? $_POST['lugarTrabajo3'] : '';
        $sueldo3 = isset($_POST['sueldo3']) ? $_POST['sueldo3'] : '';
        $aporteHogar3 = isset($_POST['aporteHogar3']) ? $_POST['aporteHogar3'] : '';

        $nombre4 = mb_convert_encoding(isset($_POST['nombre4']) ? $_POST['nombre4'] : '', 'ISO-8859-1', 'UTF-8');
        $edad4 = isset($_POST['edad4']) ? $_POST['edad4'] : '';
        $parentesco4 = isset($_POST['parentesco4']) ? $_POST['parentesco4'] : '';
        $edoCivil4 = isset($_POST['edoCivil4']) ? $_POST['edoCivil4'] : '';
        $gradoInstruccion4 = isset($_POST['gradoInstruccion4']) ? $_POST['gradoInstruccion4'] : '';
        $ocupacion4 = isset($_POST['ocupacion4']) ? $_POST['ocupacion4'] : '';
        $lugarTrabajo4 = isset($_POST['lugarTrabajo4']) ? $_POST['lugarTrabajo4'] : '';
        $sueldo4 = isset($_POST['sueldo4']) ? $_POST['sueldo4'] : '';
        $aporteHogar4 = isset($_POST['aporteHogar4']) ? $_POST['aporteHogar4'] : '';

        $nombre5 = mb_convert_encoding(isset($_POST['nombre5']) ? $_POST['nombre5'] : '', 'ISO-8859-1', 'UTF-8');
        $edad5 = isset($_POST['edad5']) ? $_POST['edad5'] : '';
        $parentesco5 = isset($_POST['parentesco5']) ? $_POST['parentesco5'] : '';
        $edoCivil5 = isset($_POST['edoCivil5']) ? $_POST['edoCivil5'] : '';
        $gradoInstruccion5 = isset($_POST['gradoInstruccion5']) ? $_POST['gradoInstruccion5'] : '';
        $ocupacion5 = isset($_POST['ocupacion5']) ? $_POST['ocupacion5'] : '';
        $lugarTrabajo5 = isset($_POST['lugarTrabajo5']) ? $_POST['lugarTrabajo5'] : '';
        $sueldo5 = isset($_POST['sueldo5']) ? $_POST['sueldo5'] : '';
        $aporteHogar5 = isset($_POST['aporteHogar5']) ? $_POST['aporteHogar5'] : '';

        // TABLA 2 - GRUPO FAMILIAR
        $ingreso_sueldo = isset($_POST['ingreso_sueldo']) ? $_POST['ingreso_sueldo'] : '';
        $egreso_alimentacion = isset($_POST['egreso_alimentacion']) ? $_POST['egreso_alimentacion'] : '';
        $propia = isset($_POST['propia']) ? $_POST['propia'] : '';
        $casa = isset($_POST['casa']) ? $_POST['casa'] : '';

        $ingreso_trabajos = isset($_POST['ingreso_trabajos']) ? $_POST['ingreso_trabajos'] : '';
        $egreso_vivienda = isset($_POST['egreso_vivienda']) ? $_POST['egreso_vivienda'] : '';
        $opcion_compra = isset($_POST['opcion_compra']) ? $_POST['opcion_compra'] : '';
        $quinta = isset($_POST['quinta']) ? $_POST['quinta'] : '';

        $ingreso_renta = isset($_POST['ingreso_renta']) ? $_POST['ingreso_renta'] : '';
        $egreso_servicios = isset($_POST['egreso_servicios']) ? $_POST['egreso_servicios'] : '';
        $alquilada = isset($_POST['alquilada']) ? $_POST['alquilada'] : '';
        $apto = isset($_POST['apto']) ? $_POST['apto'] : '';

        $ingreso_pensiones = isset($_POST['ingreso_pensiones']) ? $_POST['ingreso_pensiones'] : '';
        $egreso_educacion = isset($_POST['egreso_educacion']) ? $_POST['egreso_educacion'] : '';
        $prestada = isset($_POST['prestada']) ? $_POST['prestada'] : '';
        $rural = isset($_POST['rural']) ? $_POST['rural'] : '';

        $ingreso_ayudas = isset($_POST['ingreso_ayudas']) ? $_POST['ingreso_ayudas'] : '';
        $egreso_transporte = isset($_POST['egreso_transporte']) ? $_POST['egreso_transporte'] : '';
        $hipoteca = isset($_POST['hipoteca']) ? $_POST['hipoteca'] : '';
        $inavi = isset($_POST['inavi']) ? $_POST['inavi'] : '';

        $ingreso_otros = isset($_POST['ingreso_otros']) ? $_POST['ingreso_otros'] : '';
        $egreso_salud = isset($_POST['egreso_salud']) ? $_POST['egreso_salud'] : '';
        $pagando = isset($_POST['pagando']) ? $_POST['pagando'] : '';
        $r_r = isset($_POST['r_r']) ? $_POST['r_r'] : '';

        $total_ingresos = isset($_POST['total_ingresos']) ? $_POST['total_ingresos'] : '';
        $total_egresos = isset($_POST['total_egresos']) ? $_POST['total_egresos'] : '';
        $tenencia_otros = isset($_POST['tenencia_otros']) ? $_POST['tenencia_otros'] : '';
        $r_u = isset($_POST['r_u']) ? $_POST['r_u'] : '';

        $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';


        // Crear el PDF
        $pdf = new FPDF();
        $pdf->AddPage();

        // PARA AJUSTAR LA POSICION EN X y Y: PARA X ENTRE MENOR SEA EL NRO, EL ELEMENTO DE POSICIONARA MAS  A LA IZQUIERDA, PARA Y ENTRE MENOR SEA EL NUMERO , EL ELEMENTO SE POSICIONARA MAS ARRIBA



        // Opcional: Colocar la imagen del formulario de fondo
        // Usar ruta absoluta para la imagen de fondo
        $pdf->Image(BASE_PATH . '/PDF/EstudioSE/EstudioSocioeconomico.png', 0, 0, 210, 297); 
        $pdf->AddFont('arial', '', 'arial.php');
        // Añadir los datos en las posiciones correspondientes
        $pdf->SetFont('arial', '', 10);

        if (!empty($ruta_temporal)) {
             // La imagen se superpone. Ruta temporal es valida.
             // $extension variable needs to be defined if we use it. 
             // Since fixImageOrientation was called earlier, we know it's a valid image if $ruta_temporal is set.
             // But we need the extension string for FPDF (JPG, PNG etc)
             // In the legacy code, $extension was defined inside the if block.
             
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $pdf->Image($ruta_temporal, 165.5, 24.8, 28.6, 31.2, strtoupper($extension)); 
        }

        // Escribir los datos superpuestos en la imagen (ajusta las coordenadas según la imagen)
// $pdf->Image($ruta_temporal, 165.5, 24.8, 28.6, 31.2, strtoupper($extension)); // Especificar el tipo de imagen (JPG, PNG, etc.)

        $pdf->SetXY(63, 57.8); // Ajusta la posición X, Y
        $pdf->Cell(100, 10, "$renovacion");


        $pdf->SetXY(80.5, 57.8); // Ajusta la posición X, Y
        $pdf->Cell(100, 10, "$nueva");

        if ($beneficio) {
            $pdf->SetXY(148.5, 57.8);
            $pdf->Cell(100, 10, "$beneficio");
        }

        if ($fecha) {
            // Convertir la fecha de 'aaaa-mm-dd' a 'dd-mm-aaaa'
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if ($fechaObj) {
                $fecha_formateada = $fechaObj->format('d-m-Y');
                // Posicionar la fecha en el PDF
                $pdf->SetXY(170, 53.6);
                $pdf->Cell(100, 10, "$fecha_formateada");
            }
        }

        if ($nombre) {
            $pdf->SetXY(45, 66);
            $pdf->Cell(100, 10, "$nombre");
        }

        if ($lugarFechaConcat) {
            $pdf->SetXY(57, 70.3);
            $pdf->Cell(100, 10, "$lugarFechaConcat");
        }

        if ($edad) {
            $pdf->SetXY(22, 74.5);
            $pdf->Cell(100, 10, "$edad");
        }

        if ($edocivil) {
            $pdf->SetXY(68, 74.5);
            $pdf->Cell(100, 10, "$edocivil");
        }

        if ($ci) {
            $pdf->SetXY(103, 74.5);
            $pdf->Cell(100, 10, "$ci");
        }

        if ($telefono) {
            $pdf->SetXY(153, 74.5);
            $pdf->Cell(100, 10, "$telefono");
        }

        if ($tr_si) {
            $pdf->SetXY(30, 78.8);
            $pdf->Cell(100, 10, "$tr_si");
        }

        if ($tr_no) {
            $pdf->SetXY(44, 78.8);
            $pdf->Cell(100, 10, "$tr_no");
        }

        if ($ocupacion) {
            $pdf->SetXY(75, 78.8);
            $pdf->Cell(100, 10, "$ocupacion");
        }

        if ($lugar_trabajo) {
            $pdf->SetXY(150, 78.8);
            $pdf->Cell(100, 10, "$lugar_trabajo");
        }

        if ($sueldo) {
            $pdf->SetXY(24.5, 83);
            $pdf->Cell(100, 10, "$sueldo");
        }

        if ($carga_si) {
            $pdf->SetXY(48, 87.1);
            $pdf->Cell(100, 10, "$carga_si");
        }

        if ($carga_no) {
            $pdf->SetXY(67, 87.1);
            $pdf->Cell(100, 10, "$carga_no");
        }

        if ($hijos) {
            $pdf->SetXY(112, 87.1);
            $pdf->Cell(100, 10, "$hijos");
        }

        if ($dir_hab) {
            $pdf->SetXY(50, 91.2);
            $pdf->Cell(100, 10, "$dir_hab");
        }

        if ($dir_res) {
            $pdf->SetXY(50, 95.5);
            $pdf->Cell(100, 10, "$dir_res");
        }

        if ($especialidad) {
            $pdf->SetXY(35, 103.9);
            $pdf->Cell(100, 10, "$especialidad");
        }

        if ($sem_tra) {
            $pdf->SetXY(125, 103.9);
            $pdf->Cell(100, 10, "$sem_tra");
        }

        if ($turno) {
            $pdf->SetXY(148, 103.9);
            $pdf->Cell(100, 10, "$turno");
        }

        if ($seccion) {
            $pdf->SetXY(181, 103.9);
            $pdf->Cell(100, 10, "$seccion");
        }

        if ($correo) {
            $pdf->SetXY(42, 107.9);
            $pdf->Cell(100, 10, "$correo");
        }

        if ($redes) {
            $pdf->SetXY(137, 107.9);
            $pdf->Cell(100, 10, "$redes");
        }

        // TABLA 1 - GRUPO FAMILIAR
        $pdf->SetFont('Arial', '', 9);

        if ($nombre1) {
            $pdf->SetXY(13, 128);
            $pdf->Cell(100, 10, "$nombre1");
        }

        if ($edad1) {
            $pdf->SetXY(46, 128);
            $pdf->Cell(100, 10, "$edad1");
        }

        if ($parentesco1) {
            $pdf->SetXY(55.8, 128);
            $pdf->Cell(100, 10, "$parentesco1");
        }

        if ($edoCivil1) {
            $pdf->SetXY(77, 128);
            $pdf->Cell(100, 10, "$edoCivil1");
        }

        if ($gradoInstruccion1) {
            $pdf->SetXY(96, 128);
            $pdf->Cell(100, 10, "$gradoInstruccion1");
        }

        if ($ocupacion1) {
            $pdf->SetXY(117, 128);
            $pdf->Cell(100, 10, "$ocupacion1");
        }

        if ($lugarTrabajo1) {
            $pdf->SetXY(137.5, 128);
            $pdf->Cell(100, 10, "$lugarTrabajo1");
        }

        if ($sueldo1) {
            $pdf->SetXY(157.5, 128);
            $pdf->Cell(100, 10, "$sueldo1");
        }

        if ($aporteHogar1) {
            $pdf->SetXY(177.5, 128);
            $pdf->Cell(100, 10, "$aporteHogar1");
        }

        // GRUPO FAMILIAR (continuación)

        if ($nombre2) {
            $pdf->SetXY(13, 132.3);
            $pdf->Cell(100, 10, "$nombre2");
        }

        if ($edad2) {
            $pdf->SetXY(46, 132.3);
            $pdf->Cell(100, 10, "$edad2");
        }

        if ($parentesco2) {
            $pdf->SetXY(55.8, 132.3);
            $pdf->Cell(100, 10, "$parentesco2");
        }

        if ($edoCivil2) {
            $pdf->SetXY(77, 132.3);
            $pdf->Cell(100, 10, "$edoCivil2");
        }

        if ($gradoInstruccion2) {
            $pdf->SetXY(96, 132.3);
            $pdf->Cell(100, 10, "$gradoInstruccion2");
        }

        if ($ocupacion2) {
            $pdf->SetXY(117, 132.3);
            $pdf->Cell(100, 10, "$ocupacion2");
        }

        if ($lugarTrabajo2) {
            $pdf->SetXY(137.5, 132.3);
            $pdf->Cell(100, 10, "$lugarTrabajo2");
        }

        if ($sueldo2) {
            $pdf->SetXY(157.5, 132.3);
            $pdf->Cell(100, 10, "$sueldo2");
        }

        if ($aporteHogar2) {
            $pdf->SetXY(177.5, 132.3);
            $pdf->Cell(100, 10, "$aporteHogar2");
        }

        //-------------------------
        if ($nombre3) {
            $pdf->SetXY(13, 136.3);
            $pdf->Cell(100, 10, "$nombre3");
        }

        if ($edad3) {
            $pdf->SetXY(46, 136.3);
            $pdf->Cell(100, 10, "$edad3");
        }

        if ($parentesco3) {
            $pdf->SetXY(55.8, 136.3);
            $pdf->Cell(100, 10, "$parentesco3");
        }

        if ($edoCivil3) {
            $pdf->SetXY(77, 136.3);
            $pdf->Cell(100, 10, "$edoCivil3");
        }

        if ($gradoInstruccion3) {
            $pdf->SetXY(96, 136.3);
            $pdf->Cell(100, 10, "$gradoInstruccion3");
        }

        if ($ocupacion3) {
            $pdf->SetXY(117, 136.3);
            $pdf->Cell(100, 10, "$ocupacion3");
        }

        if ($lugarTrabajo3) {
            $pdf->SetXY(137.5, 136.3);
            $pdf->Cell(100, 10, "$lugarTrabajo3");
        }

        if ($sueldo3) {
            $pdf->SetXY(157.5, 136.3);
            $pdf->Cell(100, 10, "$sueldo3");
        }

        if ($aporteHogar3) {
            $pdf->SetXY(177.5, 136.3);
            $pdf->Cell(100, 10, "$aporteHogar3");
        }

        //--------------------------------------------
        if ($nombre4) {
            $pdf->SetXY(13, 140.3);
            $pdf->Cell(100, 10, "$nombre4");
        }

        if ($edad4) {
            $pdf->SetXY(46, 140.3);
            $pdf->Cell(100, 10, "$edad4");
        }

        if ($parentesco4) {
            $pdf->SetXY(55.8, 140.3);
            $pdf->Cell(100, 10, "$parentesco4");
        }

        if ($edoCivil4) {
            $pdf->SetXY(77, 140.3);
            $pdf->Cell(100, 10, "$edoCivil4");
        }

        if ($gradoInstruccion4) {
            $pdf->SetXY(96, 140.3);
            $pdf->Cell(100, 10, "$gradoInstruccion4");
        }

        if ($ocupacion4) {
            $pdf->SetXY(117, 140.3);
            $pdf->Cell(100, 10, "$ocupacion4");
        }

        if ($lugarTrabajo4) {
            $pdf->SetXY(137.5, 140.3);
            $pdf->Cell(100, 10, "$lugarTrabajo4");
        }

        if ($sueldo4) {
            $pdf->SetXY(157.5, 140.3);
            $pdf->Cell(100, 10, "$sueldo4");
        }

        if ($aporteHogar4) {
            $pdf->SetXY(177.5, 140.3);
            $pdf->Cell(100, 10, "$aporteHogar4");
        }

        //-------------------------------------------------------

        if ($nombre5) {
            $pdf->SetXY(13, 144.3);
            $pdf->Cell(100, 10, "$nombre5");
        }

        if ($edad5) {
            $pdf->SetXY(46, 144.3);
            $pdf->Cell(100, 10, "$edad5");
        }

        if ($parentesco5) {
            $pdf->SetXY(55.8, 144.3);
            $pdf->Cell(100, 10, "$parentesco5");
        }

        if ($edoCivil5) {
            $pdf->SetXY(77, 144.3);
            $pdf->Cell(100, 10, "$edoCivil5");
        }

        if ($gradoInstruccion5) {
            $pdf->SetXY(96, 144.3);
            $pdf->Cell(100, 10, "$gradoInstruccion5");
        }

        if ($ocupacion5) {
            $pdf->SetXY(117, 144.3);
            $pdf->Cell(100, 10, "$ocupacion5");
        }

        if ($lugarTrabajo5) {
            $pdf->SetXY(137.5, 144.3);
            $pdf->Cell(100, 10, "$lugarTrabajo5");
        }

        if ($sueldo5) {
            $pdf->SetXY(157.5, 144.3);
            $pdf->Cell(100, 10, "$sueldo5");
        }

        if ($aporteHogar5) {
            $pdf->SetXY(177.5, 144.3);
            $pdf->Cell(100, 10, "$aporteHogar5");
        }
        // TABLA 2 - GRUPO FAMILIAR (Ingresos y Egresos)

        if ($ingreso_sueldo) {
            $pdf->SetXY(35, 162.3);
            $pdf->Cell(100, 10, "$ingreso_sueldo");
        }

        if ($egreso_alimentacion) {
            $pdf->SetXY(82, 162.3);
            $pdf->Cell(100, 10, "$egreso_alimentacion");
        }

        if ($propia) {
            $pdf->SetXY(147.5, 162.3);
            $pdf->Cell(100, 10, "$propia");
        }

        if ($casa) {
            $pdf->SetXY(191.5, 162.3);
            $pdf->Cell(100, 10, "$casa");
        }

        //----------------------------------------------------

        if ($ingreso_trabajos) {
            $pdf->SetXY(35, 170);
            $pdf->Cell(100, 10, "$ingreso_trabajos");
        }

        if ($egreso_vivienda) {
            $pdf->SetXY(82, 170);
            $pdf->Cell(100, 10, "$egreso_vivienda");
        }

        if ($opcion_compra) {
            $pdf->SetXY(147.5, 170);
            $pdf->Cell(100, 10, "$opcion_compra");
        }

        if ($quinta) {
            $pdf->SetXY(191.5, 170);
            $pdf->Cell(100, 10, "$quinta");
        }

        //----------------------------------------------------

        if ($ingreso_renta) {
            $pdf->SetXY(35, 178);
            $pdf->Cell(100, 10, "$ingreso_renta");
        }

        if ($egreso_servicios) {
            $pdf->SetXY(82, 178);
            $pdf->Cell(100, 10, "$egreso_servicios");
        }

        if ($alquilada) {
            $pdf->SetXY(147.5, 178);
            $pdf->Cell(100, 10, "$alquilada");
        }

        if ($apto) {
            $pdf->SetXY(191.5, 178);
            $pdf->Cell(100, 10, "$apto");
        }

        //------------------------------------------------------------

        if ($ingreso_pensiones) {
            $pdf->SetXY(35, 186);
            $pdf->Cell(100, 10, "$ingreso_pensiones");
        }

        if ($egreso_educacion) {
            $pdf->SetXY(82, 186);
            $pdf->Cell(100, 10, "$egreso_educacion");
        }

        if ($prestada) {
            $pdf->SetXY(147.5, 186);
            $pdf->Cell(100, 10, "$prestada");
        }

        if ($rural) {
            $pdf->SetXY(191.5, 186);
            $pdf->Cell(100, 10, "$rural");
        }

        //-----------------------------------------------------------

        if ($ingreso_ayudas) {
            $pdf->SetXY(35, 194);
            $pdf->Cell(100, 10, "$ingreso_ayudas");
        }

        if ($egreso_transporte) {
            $pdf->SetXY(82, 194);
            $pdf->Cell(100, 10, "$egreso_transporte");
        }

        if ($hipoteca) {
            $pdf->SetXY(147.5, 194);
            $pdf->Cell(100, 10, "$hipoteca");
        }

        if ($inavi) {
            $pdf->SetXY(191.5, 194);
            $pdf->Cell(100, 10, "$inavi");
        }

        //-----------------------------------------------------------

        if ($ingreso_otros) {
            $pdf->SetXY(35, 202);
            $pdf->Cell(100, 10, "$ingreso_otros");
        }

        if ($egreso_salud) {
            $pdf->SetXY(82, 202);
            $pdf->Cell(100, 10, "$egreso_salud");
        }

        if ($pagando) {
            $pdf->SetXY(147.5, 202);
            $pdf->Cell(100, 10, "$pagando");
        }

        if ($r_r) {
            $pdf->SetXY(191.5, 202);
            $pdf->Cell(100, 10, "$r_r");
        }

        //------------------------------------------------------------

        if ($total_ingresos) {
            $pdf->SetXY(35, 210);
            $pdf->Cell(100, 10, "$total_ingresos");
        }

        if ($total_egresos) {
            $pdf->SetXY(82, 210);
            $pdf->Cell(100, 10, "$total_egresos");
        }

        if ($tenencia_otros) {
            $pdf->SetXY(147.5, 210);
            $pdf->Cell(100, 10, "$tenencia_otros");
        }

        if ($r_u) {
            $pdf->SetXY(191.5, 210);
            $pdf->Cell(100, 10, "$r_u");
        }

        $pdf->SetFont('Arial', '', 10);


        if ($observaciones) {
            $pdf->SetXY(13, 227);
            $pdf->MultiCell(185, 7, "$observaciones");
        }

        // Generar el PDF
        $pdf->Output('F', $rutaPDF);

        return true;
        // 'I' para que el PDF se muestre en el navegador, 'D' para descargar, 'F' Guardar el archivo en un archivo local en el servidor, 'S' Devolver el documento como una cadena de caracteres (string), 'FI' Guardar en el servidor y enviar en línea al navegador, 'FD' Guardar en el servidor y forzar la descarga, 'E' Enviar el archivo por correo electrónico (como archivo adjunto).
    }
}