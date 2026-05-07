<?php
sleep(1);
session_start();

require '../conexion/conexion.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
}
$id = $_SESSION['id'];
$nombre = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario = $_SESSION['usuario'];
$usuarios = $_SESSION['usuario'];

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE id=$id";
}

//require('../fpdf/fpdf.php');
//require('../fpdf/code128.php');

require("../conexion/conexion4.php");

$mysqli = retornarConexion();

require('../fpdf/code128.php');
// $pdf = new PDF_Code128();
//     $pdf->AddPage();
//     $pdf->SetFont('Arial', '', 10);



//     //A,C,B sets
//     $code = 'ABCDEFG1234567890AbCdEf';
//     $pdf->Code128(50, 170, $code, 125, 20);
//     $pdf->SetXY(50, 195);
//     $pdf->Write(5, 'ABC sets combined: "' . $code . '"');

// $pdf->Output();


$fpdf = new PDF_Code128('P', 'mm', array(80, 175));
$fpdf->SetAutoPageBreak(true); //Disable automatic page break
$fpdf->AddPage('portrait', array(80, 175));

$fpdf->SetMargins(2, 5, 5);

cabecera($fpdf, $mysqli);
// titulosdetalle($fpdf);
// imprimirdetalle($fpdf, $mysqli);
// piedepagina($fpdf, $mysqli);
// piedepagina2($fpdf, $mysqli);
function cabecera($fpdf, $mysqli)
{
    $fpdf->Image('../assets/img/logo.png', 30, 2, 20);
    $fecha = date_default_timezone_set('America/Bogota');
    setlocale(LC_TIME, 'spanish');
    $fecha = strftime('%A, %d de %B de %Y ');

    $fpdf->Ln(5);
    $fpdf->SetFont('Arial', 'B', 10);
    $fpdf->cell(70, 5, 'Parqueadero', 0, 1, 'C');

    $fpdf->Ln(1);
    $fpdf->SetFont('Arial', 'B', 10);
    $fpdf->cell(70, 5, 'Parque de la familia', 0, 1, 'C');

    $fpdf->SetFont('Arial', '', 8);
    $fpdf->MultiCell(70, 5, 'WathsApp 314-8139800', 0, 'C');


    $fpdf->SetFont('Arial', '', 10);
    $fpdf->cell(75, 5, '-------------------------------------------------------------------------------', 0, 1, 'C');



    //$placa = $_POST['placa'];    
    sleep(1);
    $query = "      SELECT      RE.recibo_id,
                                RE.placa,
                                DATE(RE.fecha_ini) as fechaini,
                                TIME(RE.fecha_ini) as horaini,
                                DATE(RE.fecha_fin) as fechafin,
                                TIME(RE.fecha_fin) as horafin,
                                RE.tiempo,
                                RE.valor_manual,
                                -- RE.usuario,
                                RE.usuario,
                                US.nombre as nombre_cli,
                                -- tarifa,
                                -- tar_valor,
                                CL.cli_tar_tiempo,
                                TT.tar_tiempo,
                                CA.cat_nombre                              
                    FROM        recibo AS RE 
                    INNER JOIN  usuarios AS US ON US.id = RE.usuario 
                   
                    
                    
                    INNER JOIN  cliente AS CL ON CL.placa = RE.placa
                     INNER JOIN  tar_tiempo AS TT ON CL.cli_tar_tiempo = TT.tar_id_nombre
                    INNER JOIN  categorias AS CA ON CA.cat_id = CL.categoria
                      
                    ORDER BY    recibo_id
                    DESC LIMIT 1;
                    
                        ";
    $parqueo = $mysqli->query($query);

    $row = $parqueo->fetch_assoc();


    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Recibo No: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(50, 6, $row['recibo_id'], 0, 1, 'L');
    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'PLACA No: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', 'B', 20);
    $fpdf->Cell(50, 6, $row['placa'], 0, 1, 'L');
    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Tipo Vehiculo: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(50, 6, $row['cat_nombre'], 0, 1, 'L');



    //     //A,C,B sets
    //     
    //     $pdf->Code128(50, 170, $code, 125, 20);
    //     $pdf->SetXY(50, 195);
    //     $pdf->Write(5, 'ABC sets combined: "' . $code . '"');

    $fpdf->SetFont('Arial', '', 10);
    $fpdf->cell(75, 5, '-------------------------------------------------------------------------------', 0, 1, 'C');

    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Fecha Entrada: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(50, 6, $row['fechaini'], 0, 1, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Hora Entrada: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(50, 6, $row['horaini'], 0, 1, 'L');

    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Fecha Salida: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(50, 6, $row['fechafin'], 0, 1, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Hora Salida: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(50, 6, $row['horafin'], 0, 1, 'L');



    $fpdf->SetFont('Arial', '', 10);
    $fpdf->cell(75, 5, '-------------------------------------------------------------------------------', 0, 1, 'C');

    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Estadia : ', 0, 0, 'L');
    $fpdf->SetFont('Arial', 'B', 12);
    $fpdf->MultiCell(40, 6, $row['tiempo'], 0, 'L');
    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Tarifa : ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 10);
    $fpdf->Cell(50, 6,$row['tar_tiempo'], 0, 1, 'L');

    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Valor a pagar: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', 'B', 20);
    $fpdf->Cell(50, 6, '$' . number_format($row['valor_manual'], 0, ",", "."), 0, 1, 'L');

    $fpdf->SetFont('Arial', '', 10);
    $fpdf->cell(75, 5, '-------------------------------------------------------------------------------', 0, 1, 'C');

    $fpdf->Ln(1);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Cajero : ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(50, 6, $row['usuario'], 0, 1, 'L');

    $fpdf->SetFont('Arial', '', 10);
    $fpdf->cell(75, 5, '-------------------------------------------------------------------------------', 0, 1, 'C');




    // $fpdf->Ln(2);
    // $fpdf->SetFont('Arial', '', 10);
    // $code = $row['placa'];
    // $fpdf->Code128(5, 80, $code, 65, 15);
    // $fpdf->Cell(75, 35, $row['placa'], 0, 1, 'C');


    $fpdf->Ln(2);
    $fpdf->SetFont('Arial', 'U', 12);
    $fpdf->Cell(75, 0, 'REGLAMENTO', 0, 1, 'C');

    $fpdf->Ln(3);
    $fpdf->SetFont('Arial', '', 8);
    $fpdf->MultiCell(70, 3, '1-El vehiculo se entrega al portador del recibo.
2-No se aceptan ordenes telefonicas ni escritas.
3-Retirado el vehiculo no aceptamos ningun tipo de reclamo.
4-No se responde por objetos dejados en el vehiculo.
5-No se responde por la perdida, deterioro, o danos ocurridos como consecuencia de incendio, terremoto, vendavales, asonada o revolucion u otras causas similares.
6-El conductor debe asegurar bien su vehiculo (Ventanas y seguros).
7-No se permite la permanencia de personas dentro del vehiculo una vez estacionado.
    ', 2, 'L');
}

$fpdf->Output();
