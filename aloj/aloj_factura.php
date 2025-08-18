<?php
require_once '../conexion/conexion.php';
require '../modulos/fpdf/fpdf.php';
session_start();

if (!isset($_SESSION['id'])) die("No autorizado.");

// Obtener el ID de la reserva
$reserva_id = isset($_GET['reserva_id']) ? intval($_GET['reserva_id']) : 0;
if ($reserva_id <= 0) die("ID de reserva inválido");

// Obtener datos de reserva
$stmt = $pdo->prepare("
  SELECT    r.*,   
            c.nombre AS cliente_nombre,
            c.tipo_doc,
            c.documento,
            h.nombre AS habitacion_nombre, 
            c.telefono,
            c.placa_vehiculo,
            c.Procedencia,
            u.nombre
  FROM aloj_reservas r
  JOIN aloj_clientes c ON r.cliente_id = c.id
  JOIN aloj_habitaciones h ON r.habitacion_id = h.id
  JOIN usuarios u ON r.usuario_id = u.id
  WHERE r.id = ?
");
$stmt->execute([$reserva_id]);
$reserva = $stmt->fetch();

if (!$reserva) die("Reserva no encontrada");

// Pagos realizados
$stmt = $pdo->prepare("SELECT * FROM aloj_pagos WHERE reserva_id = ?");
$stmt->execute([$reserva_id]);
$pagos = $stmt->fetchAll();

// Total pagado
$total_pagado = array_sum(array_column($pagos, 'monto'));

// Obtener acompañantes
$stmt = $pdo->prepare("SELECT nombre, tipo_docu, documento, parentesco FROM aloj_acompanantes WHERE reserva_id = ?");
$stmt->execute([$reserva_id]);
$acompanantes = $stmt->fetchAll();

$fecha_ingreso = new DateTime($reserva['fecha_ingreso']);
$fecha_salida = new DateTime($reserva['fecha_salida']);

// La diferencia en días naturales
$noches = $fecha_ingreso->diff($fecha_salida)->days;

// Noches = días - 1 si es una sola noche (opcional según política)
$dias = $noches + 1;




// FACTURA PDF

 class PDF extends FPDF {
  function Header() {
    // Ruta del logo (ajusta si está en otra carpeta)
    $this->Image('../assets/logos/logo_aloj.png', 10, 8, 50); // (ruta, x, y, ancho)

    // Título al centro
    $this->SetFont('Arial','B',14);
    $this->Cell(0,10,utf8_decode('FACTURA DE RESERVA'),0,1,'C');
    $this->Ln(5);
    $this->SetFont('Arial','',10);
    $this->Cell(0,5,'Alojamiento Parque de la Familia', 0, 1, 'C');
    $this->Ln(10);
  }

  function Footer() {
    $this->SetY(-15);
    $this->SetFont('Arial','I',8);
    $this->Cell(0,10,utf8_decode('Página ') . $this->PageNo().'/{nb}',0,0,'C');
  }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Datos del cliente
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,6,"Reserva No:  " . utf8_decode($reserva['id']), 0, 1);
$pdf->Cell(0,6,"Cliente:          " . utf8_decode($reserva['cliente_nombre']), 0, 1);
$pdf->Cell(0,6,"Documento:   ". $reserva['tipo_doc'] . ' ' . $reserva['documento'], 0, 1);
$pdf->Cell(0,6,"Telefono:       " . $reserva['telefono'], 0, 1);
$pdf->Cell(0,6,"Placa:            " . $reserva['placa_vehiculo'], 0, 1);
$pdf->Cell(0,6,"Procedencia: " . $reserva['Procedencia'], 0, 1);

$pdf->Cell(0,6,"Habitacion:    " . utf8_decode($reserva['habitacion_nombre']), 0, 1);
$pdf->Cell(0,6,"Fechas:         " . $reserva['fecha_ingreso'] . ' 03:00 pm' . " al " . $reserva['fecha_salida'] . ' 01:00 pm', 0, 1);
$pdf->Ln(1);



$pdf->SetFont('Arial','',11);
$pdf->Cell(0,6,"Estadia:         $dias dia(s) / $noches noche(s)", 0, 1);

$pdf->Ln(1);
$pdf->SetFont('Arial', 'B', 11);

$total_personas = 1 + count($acompanantes); // 1 titular + acompañantes
$pdf->Cell(0, 8, "Total de personas en la reserva: " . $total_personas, 0, 1);

$pdf->SetFont('Arial','B',12);
$pdf->Ln(1);
$pdf->Cell(0,8,'Acompanantes de la Reserva (' . count($acompanantes) . ')', 0, 1);

if (count($acompanantes)) {
    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(80, 6, 'Nombre', 1, 0, 'C', true);
    $pdf->Cell(20, 6, 'Tipo', 1, 0, 'C', true);
    $pdf->Cell(50, 6, 'Documento', 1, 0, 'C', true);
    $pdf->Cell(40, 6, 'Parentesco', 1, 1, 'C', true);

    $pdf->SetFont('Arial','',10);
    foreach ($acompanantes as $a) {
        $pdf->Cell(80, 6, utf8_decode($a['nombre']), 1);
        $pdf->Cell(20, 6, $a['tipo_docu'], 1,0,'C');
        $pdf->Cell(50, 6, $a['documento'], 1,0,'C');
        $pdf->Cell(40, 6, utf8_decode($a['parentesco']), 1);
        $pdf->Ln();
    }
} else {
    $pdf->SetFont('Arial','I',10);
    $pdf->Cell(0, 6, 'No se registraron acompanantes para esta reserva.', 0, 1);
}



// Total reserva
$pdf->SetFont('Arial','B',12);
$pdf->Ln(3);
$pdf->Cell(95,6,"Total de la Reserva",1,0,'C');
$pdf->Cell(95,6,"$ " . number_format($reserva['valor_total'], 0, ',', '.'),1,1,'C');

// Pagos
$pdf->SetFont('Arial','B',11);
$pdf->Ln(3);
$pdf->Cell(0,6,"Pagos Realizados", 0, 1);

$pdf->SetFont('Arial','',10);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(40,6,"Fecha",1,0,'C',true);
$pdf->Cell(30,6,"Monto",1,0,'C',true);
$pdf->Cell(40,6,"Metodo",1,0,'C',true);
$pdf->Cell(40,6,"Tipo",1,0,'C',true);
$pdf->Cell(40,6,"Obs.",1,1,'C',true);

foreach ($pagos as $p) {
  $pdf->Cell(40,6,substr($p['fecha_pago'], 0, 16),1);
  $pdf->Cell(30,6,"$" . number_format($p['monto'], 0, ',', '.'),1);
  $pdf->Cell(40,6,ucfirst($p['metodo_pago']),1);
  $pdf->Cell(40,6,ucfirst($p['tipo_pago']),1);
  $pdf->Cell(40,6,utf8_decode(substr($p['observaciones'], 0, 20)),1);
  $pdf->Ln();
}

// Total pagado
$pdf->SetFont('Arial','B',11);
$pdf->Ln(5);
$pdf->Cell(95,6,"Total Pagado",1,0,'C');
$pdf->Cell(95,6,"$ " . number_format($total_pagado, 0, ',', '.'),1,1,'C');

// Saldo pendiente
$pendiente = floatval($reserva['valor_total']) - $total_pagado;
$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor( ($pendiente > 0) ? 200 : 0 , 0, 0);
$pdf->Ln(5);
$pdf->Cell(95,8,"Saldo Pendiente",1,0,'C');
$pdf->Cell(95,8,"$ " . number_format($pendiente, 0, ',', '.'),1,1,'C');

$pdf->Ln(5);
$pdf->Cell(0, 8, "Atendido por: " . $reserva['nombre'], 0, 1);

$pdf->Output("I", "factura_reserva_{$reserva_id}.pdf");
?>
