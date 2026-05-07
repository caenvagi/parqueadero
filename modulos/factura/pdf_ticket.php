<?php
usleep(500000);
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


$fpdf = new PDF_Code128('P', 'mm', array(80, 165));
$fpdf->SetAutoPageBreak(true); //Disable automatic page break
$fpdf->AddPage('portrait', array(80, 165));

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
    $fpdf->MultiCell(70, 5, 'WathApp 314-8139800', 0, 'C');

    $fpdf->SetFont('Arial', '', 10);
    $fpdf->cell(75, 5, '-------------------------------------------------------------------------------', 0, 1, 'C');

    //$placa = $_POST['placa'];    
    usleep(500000);
    $query = "      SELECT      parqueo_id,
                                placa_cli,
                                DATE(fecha_ini) as fecha,
                                TIME(fecha_ini) as hora,
                                PD.usuario,
                                tarifa,
                                tar_valor,
                                tar_tiempo,
                                nombre
                    FROM        parqueo AS PD 
                    INNER JOIN  usuarios AS US ON US.id = PD.usuario 
                    INNER JOIN  tarifas AS TA  ON TA.tar_id = PD.tarifa
                    INNER JOIN  tar_tiempo As TT ON TT.tar_id_nombre = TA.tar_nombre  
                    ORDER BY    parqueo_id
                    DESC LIMIT 1;
                    
                        ";
    $parqueo = $mysqli->query($query);

    $row = $parqueo->fetch_assoc();

    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Ticket No: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 14);
    $fpdf->Cell(40, 6, $row['parqueo_id'], 0, 1, 'L');
    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'PLACA No: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', 'B', 16);
    $fpdf->Cell(40, 6, $row['placa_cli'], 0, 1, 'L');



    //     //A,C,B sets
    //     
    //     $pdf->Code128(50, 170, $code, 125, 20);
    //     $pdf->SetXY(50, 195);
    //     $pdf->Write(5, 'ABC sets combined: "' . $code . '"');


    $fpdf->Ln(0);

    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Fecha Entrada: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(40, 6, $row['fecha'], 0, 1, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Hora Entrada: ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(40, 6, $row['hora'], 0, 1, 'L');
    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 6, 'Tarifa : ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 10);
    $fpdf->MultiCell(40, 6, '$' . number_format($row['tar_valor'], 0, ",", ".") . " * " . $row['tar_tiempo'] . " Y/O FRACCION ", 0, 'L');


    $fpdf->SetFont('Arial', '', 10);
    $fpdf->cell(75, 5, '-------------------------------------------------------------------------------', 0, 1, 'C');

    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 12);
    $fpdf->Cell(30, 5, 'Cajero : ', 0, 0, 'L');
    $fpdf->SetFont('Arial', '', 10);
    $fpdf->Cell(40, 5, $row['nombre'], 0, 1, 'L');

    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 10);
    $fpdf->cell(75, 5, '-------------------------------------------------------------------------------', 0, 1, 'C');

    $fpdf->Ln(0);
    $fpdf->SetFont('Arial', '', 10);
    $code = $row['placa_cli'];
    $fpdf->Code128(5, 90, $code, 70, 15);
    $fpdf->Cell(0, 40, $row['placa_cli'], 0, 1, 'C');

    $fpdf->Ln(-20);
    $fpdf->SetFont('Arial', '', 10);
    $fpdf->cell(75, 5, '-------------------------------------------------------------------------------', 0, 1, 'C');



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
