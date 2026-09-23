<?php
sleep(1);
session_start();

require '../../conexion/conexion.php';

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


$fpdf = new PDF_Code128('P', 'mm', array(210, 280));
$fpdf->SetAutoPageBreak(true); //Disable automatic page break
$fpdf->AddPage('portrait', array(210, 280));

$fpdf->SetMargins(2, 2, 5);

// cabecera($fpdf, $pdo);
// // titulosdetalle($fpdf);
// // imprimirdetalle($fpdf, $mysqli);
// // piedepagina($fpdf, $mysqli);
// // piedepagina2($fpdf, $mysqli);
// function cabecera($fpdf, $pdo)
// {
//     $fpdf->Image('../../assets/img/logo.png', 30, 2, 20);
//     $fecha = date_default_timezone_set('America/Bogota');
//     setlocale(LC_TIME, 'spanish');
//     $fecha = strftime('%A, %d de %B de %Y ');

//     $fpdf->Ln(5);
//     $fpdf->SetFont('Arial', 'B', 10);
//     $fpdf->cell(70, 2, 'Parqueadero', 0, 1, 'C');

//     $fpdf->Ln(1);
//     $fpdf->SetFont('Arial', 'B', 10);
//     $fpdf->cell(70, 2, 'Parque de la familia', 0, 1, 'C');

//     $fpdf->SetFont('Arial', '', 8);
//     $fpdf->MultiCell(70, 5, 'WathsApp 300-1087869', 0, 'C');

//     // $fpdf->SetFont('Arial', '', 12);
//     // $fpdf->MultiCell(70, 5, 'Mensualidad', 0, 'C');


//     $fpdf->SetFont('Arial', '', 10);
//     $fpdf->cell(75, 5, '-------------------------------------------------------------------------------', 0, 1, 'C');

 //  }  

//$fpdf->Ln(1);

$id_liquidacion = $_GET['id_liquidacion'] ?? null;

$query1 = " SELECT * , U1.nombre AS entregado_por, U2.nombre AS recibido_por
            FROM caja_liquidaciones as CL
            INNER JOIN usuarios AS U1 ON CL.entregado_por = U1.id
            INNER JOIN usuarios AS U2 ON CL.recibido_por = U2.id
            where id_liquidacion = $id_liquidacion";

$stmt1 = $pdo->prepare($query1);
$stmt1->execute();
$rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// TITULO
$fpdf->SetFont('Arial', 'B', 14);
$fpdf->Cell(190, 8, 'RESUMEN DE LIQUIDACION', 0, 1, 'C');

$fpdf->SetFont('Arial', 'B', 10);
$fpdf->Cell(25, 6, 'Fecha: ', 0, 0, 'L');
$fpdf->SetFont('Arial', '', 10);
$fpdf->Cell(55, 6, $rows1[0]['fecha_liquidacion'], 0, 0, 'L');
$fpdf->SetFont('Arial', 'B', 10);
$fpdf->Cell(30, 6, 'Movimiento No: ', 0, 0, 'L');
$fpdf->SetFont('Arial', '', 10);
$fpdf->Cell(50, 6,$rows1[0]['id_liquidacion'], 0, 1, 'L');
$fpdf->SetFont('Arial', 'B', 10);
$fpdf->Cell(25, 6, 'Entregado Por: ', 0, 0, 'L');
$fpdf->SetFont('Arial', '', 10);
$fpdf->Cell(55, 6,$rows1[0]['entregado_por'], 0, 0, 'L');
$fpdf->SetFont('Arial', 'B', 10);
$fpdf->Cell(30, 6, 'Recibido Por: ', 0, 0, 'L');
$fpdf->SetFont('Arial', '', 10);
$fpdf->Cell(50, 6,$rows1[0]['recibido_por'], 0, 1, 'L');

$fpdf->Ln(3);

sleep(1);
$query = "  SELECT  
    CL.id_liquidacion,
    CL.fecha_liquidacion,
    CL.total_liquidado,
    CL.observaciones,

    U1.nombre AS entrega,
    U2.nombre AS recibe,
    U3.nombre AS usuario_liquida,

    -- Detalle
    CLD.id_movimiento,

    -- Datos del movimiento (tabla caja)
    C.id_movimiento,
    C.fecha_movimiento,
    C.recibo_id,
    C.movimiento,
    C.desc_movimiento,
    C.valor_ingreso,
    C.valor_egreso,
    C.user_login,
    U4.nombre AS usuario_movimiento,

    -- Fpar en recibo
    R.recibo_man,
    R.tipo_pago

FROM caja_liquidaciones AS CL

LEFT JOIN usuarios AS U1 ON CL.entregado_por = U1.id
LEFT JOIN usuarios AS U2 ON CL.recibido_por = U2.id
LEFT JOIN usuarios AS U3 ON CL.usuario_liquida = U3.id

INNER JOIN caja_liquidaciones_detalle AS CLD ON CL.id_liquidacion = CLD.id_liquidacion

INNER JOIN caja AS C  ON CLD.id_movimiento = C.id_movimiento
LEFT JOIN usuarios AS U4 ON C.user_login = U4.id

INNER JOIN recibo AS R ON C.recibo_id = R.recibo_id

WHERE CL.id_liquidacion = $id_liquidacion


ORDER BY R.recibo_man ASC, R.recibo_id ASC, C.fecha_movimiento ASC, C.id_movimiento ASC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    die("No se encontró el recibo");
}


// ENCABEZADO TABLA
$fpdf->SetFont('Arial', 'B', 9);
$fpdf->SetFillColor(40, 40, 40);
$fpdf->SetTextColor(255, 255, 255);

$fpdf->Cell(25, 8, 'Fecha', 1, 0, 'C', true);
$fpdf->Cell(10, 8, 'Rec', 1, 0, 'C', true);
$fpdf->Cell(14, 8, 'F-PAR', 1, 0, 'C', true);
$fpdf->Cell(16, 8, 'Medio', 1, 0, 'C', true);
$fpdf->Cell(95, 8, 'Descripcion', 1, 0, 'C', true);
$fpdf->Cell(18, 8, 'Ingreso', 1, 0, 'C', true);
$fpdf->Cell(18, 8, 'Egreso', 1, 1, 'C', true);

// RESET COLOR TEXTO
$fpdf->SetTextColor(0, 0, 0);
$fpdf->SetFont('Arial', '', 7);

$total_ingreso = 0;
$total_egreso = 0;
$resumen_usuario_pago = [];

foreach ($rows as $row) {


    $total_ingreso += $row['valor_ingreso'];
    $total_egreso += $row['valor_egreso'];

    $usuario_movimiento = $row['usuario_movimiento'] ?: 'Desconocido';
    $tipo_pago = strtolower(trim($row['tipo_pago'] ?? '')) ?: 'no registrado';
    $clave_resumen = (string) ($row['user_login'] ?? 'sin_usuario') . '|' . $tipo_pago;
    if (!isset($resumen_usuario_pago[$clave_resumen])) {
        $resumen_usuario_pago[$clave_resumen] = [
            'usuario' => $usuario_movimiento,
            'tipo_pago' => $tipo_pago,
            'movimientos' => 0,
            'ingreso' => 0,
            'egreso' => 0,
        ];
    }
    $resumen_usuario_pago[$clave_resumen]['movimientos']++;
    $resumen_usuario_pago[$clave_resumen]['ingreso'] += (float) $row['valor_ingreso'];
    $resumen_usuario_pago[$clave_resumen]['egreso'] += (float) $row['valor_egreso'];
    // Alternar color de fila
    static $fill = false;
    $fill = !$fill;
    $fpdf->SetFillColor(230, 230, 230);

    $fpdf->Cell(25, 5, $row['fecha_movimiento'], 1, 0, 'C', $fill);
    $fpdf->Cell(10, 5, $row['recibo_id'], 1, 0, 'L', $fill);
    $fpdf->Cell(14, 5, $row['recibo_man'], 1, 0, 'L', $fill);    
    $fpdf->Cell(16, 5, ucfirst($row['tipo_pago'] ?? 'No registrado'), 1, 0, 'L', $fill);
    $fpdf->Cell(95, 5, $row['desc_movimiento'], 1, 0, 'L', $fill);

    // INGRESO
    
        $fpdf->SetTextColor(0, 128, 0);
        $fpdf->Cell(18, 5, number_format($row['valor_ingreso'], 0, ",", "."), 1, 0, 'R', $fill);

        // $fpdf->SetTextColor(0, 0, 0);
        // $fpdf->Cell(25, 7, '', 1, 1, 'R', $fill);
        // $total_ingreso += $row['ingreso'];

        $fpdf->SetTextColor(255, 0, 0);
        $fpdf->Cell(18, 5, number_format($row['valor_egreso'], 0, ",", "."), 1, 1, 'R', $fill);

        $fpdf->SetTextColor(0, 0, 0); // ← RESET OBLIGATORIO

        //$fpdf->SetTextColor(0, 0, 0);
        // $total_egreso += $row['egreso'];

    

    // SALTO DE PAGINA AUTOMATICO
    if ($fpdf->GetY() > 260) {
        $fpdf->AddPage();
    }
    
}

$fpdf->Ln(0);

$fpdf->SetFont('Arial', 'B', 10);

// TOTAL INGRESOS
$fpdf->Cell(160, 8, 'TOTAL INGRESOS', 1, 0, 'R');
$fpdf->SetTextColor(0, 128, 0);
$fpdf->Cell(18, 8, number_format($total_ingreso, 0, ",", "."), 1, 0, 'R');
$fpdf->Cell(18, 8, '', 1, 1);

// TOTAL EGRESOS
$fpdf->SetTextColor(0, 0, 0);
$fpdf->Cell(160, 8, 'TOTAL EGRESOS', 1, 0, 'R');
$fpdf->Cell(18, 8, '', 1, 0);
$fpdf->SetTextColor(255, 0, 0);
$fpdf->Cell(18, 8, number_format($total_egreso, 0, ",", "."), 1, 1, 'R');

// TOTAL GENERAL
$fpdf->SetTextColor(0, 0, 0);
$fpdf->Cell(160, 8, 'TOTAL A ENTREGAR', 1, 0, 'R');
$fpdf->Cell(36, 8, number_format($total_ingreso - $total_egreso, 0, ",", "."), 1, 1, 'R');

// RESUMEN POR USUARIO Y TIPO DE PAGO
$fpdf->Ln(4);
$fpdf->SetFont('Arial', 'B', 10);
$fpdf->SetTextColor(0, 0, 0);
$fpdf->Cell(197, 7, 'RESUMEN POR USUARIO Y TIPO DE PAGO', 1, 1, 'C');

$fpdf->SetFont('Arial', 'B', 8);
$fpdf->SetFillColor(40, 40, 40);
$fpdf->SetTextColor(255, 255, 255);
$fpdf->Cell(55, 7, 'Usuario', 1, 0, 'C', true);
$fpdf->Cell(28, 7, 'Tipo de pago', 1, 0, 'C', true);
$fpdf->Cell(14, 7, 'Mov.', 1, 0, 'C', true);
$fpdf->Cell(34, 7, 'Ingresos', 1, 0, 'C', true);
$fpdf->Cell(34, 7, 'Egresos', 1, 0, 'C', true);
$fpdf->Cell(32, 7, 'Saldo', 1, 1, 'C', true);

$fpdf->SetFont('Arial', '', 8);
$fpdf->SetTextColor(0, 0, 0);
$fill_resumen = false;
foreach ($resumen_usuario_pago as $resumen) {
    $saldo_resumen = $resumen['ingreso'] - $resumen['egreso'];
    $fill_resumen = !$fill_resumen;
    $fpdf->SetFillColor(230, 230, 230);
    $fpdf->Cell(55, 6, $resumen['usuario'], 1, 0, 'L', $fill_resumen);
    $fpdf->Cell(28, 6, ucfirst($resumen['tipo_pago']), 1, 0, 'L', $fill_resumen);
    $fpdf->Cell(14, 6, $resumen['movimientos'], 1, 0, 'C', $fill_resumen);
    $fpdf->SetTextColor(0, 128, 0);
    $fpdf->Cell(34, 6, number_format($resumen['ingreso'], 0, ",", "."), 1, 0, 'R', $fill_resumen);
    $fpdf->SetTextColor(255, 0, 0);
    $fpdf->Cell(34, 6, number_format($resumen['egreso'], 0, ",", "."), 1, 0, 'R', $fill_resumen);
    $fpdf->SetTextColor(0, 0, 0);
    $fpdf->Cell(32, 6, number_format($saldo_resumen, 0, ",", "."), 1, 1, 'R', $fill_resumen);
}

$stmtObs = $pdo->prepare("
    SELECT observaciones 
    FROM caja_liquidaciones
    WHERE id_liquidacion = $id_liquidacion
");
$stmtObs->execute();

$obs = $stmtObs->fetchColumn();
// Mover a la derecha antes de MultiCell

$fpdf->SetTextColor(0, 0, 0);
$fpdf->SetFont('Arial', 'B', 10);
$fpdf->Ln(0);

$fpdf->Cell(55, 7, 'OBSERVACIONES:', 1, 0, 'R');
$fpdf->SetFont('Arial', '', 10);
// Mover a la derecha antes de MultiCell
$x = $fpdf->GetX();
$y = $fpdf->GetY();
$fpdf->MultiCell(142, 7, $obs, 1, 'L');
// Volver a la misma línea si necesitas seguir
$fpdf->SetXY($x + 144, $y);

//$fpdf->Ln(8);


$fpdf->Output();
