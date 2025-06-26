<?php
session_start();
require_once "../conexion/conexion.php";
require_once "../modulos/fpdf/fpdf.php";

if (!isset($_SESSION['id'])) {
  header("Location: index.php");
}
$id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

if ($tipo_usuario == 1) {
  $where = "";
} else if ($tipo_usuario == 2) {
  $where = "WHERE id=$id";
};

function numeroALetrasCOP($numero)
{
    $formatter = new NumberFormatter("es", NumberFormatter::SPELLOUT);

    // Aseguramos formato correcto
    $numero = number_format($numero, 0, '', ''); // sin decimales, sin separadores

    $enLetras = $formatter->format($numero);
    $enLetras = ucfirst($enLetras); // Primera letra mayúscula

    // Opcional: reemplazar "uno" por "un" para moneda
    $enLetras = str_replace("uno", "un", $enLetras);

    return "$enLetras pesos m/cte.";
}

// Obtener el último ID de recibo
$stmt = $pdo->query("SELECT MAX(recibos_id) AS ultimo_id FROM usuarios_recibos");
$ultimo = $stmt->fetchColumn();
$nuevo_recibo_id = $ultimo ? $ultimo + 1 : 1;

$stmt = $pdo->query("SELECT nombre FROM usuarios where id=$id");
$nombre_usuario = $stmt->fetchColumn();




if (isset($_POST['turnos']) && is_array($_POST['turnos'])) {
    $turnos = array_map('intval', $_POST['turnos']);
    $placeholders = implode(',', array_fill(0, count($turnos), '?'));

    // Obtener info de los turnos seleccionados
    $stmt = $pdo->prepare("SELECT id_turno, usuario_id, fecha_inicio, fecha_fin, valor, pagado
                       FROM usuarios_turnos 
                       WHERE id_turno IN ($placeholders)");
    $stmt->execute($turnos);
    $datos = $stmt->fetchAll();

    $usuarios_turnos = [];

foreach ($datos as $row) {
    $usuario_id = $row['usuario_id'];
    $usuarios_turnos[$usuario_id][] = $row;
}


    $elaborado = $_SESSION['usuario'] ?? 'sistema';
    $fecha_actual = date('Y-m-d H:i:s');

      $meses = [
        '01' => 'enero',
        '02' => 'febrero',
        '03' => 'marzo',
        '04' => 'abril',
        '05' => 'mayo',
        '06' => 'junio',
        '07' => 'julio',
        '08' => 'agosto',
        '09' => 'septiembre',
        '10' => 'octubre',
        '11' => 'noviembre',
        '12' => 'diciembre'
      ];

      $dia = date('d');
      $mes = $meses[date('m')];
      $anio = date('Y');

      $fecha_formateada = "$dia $mes $anio"; // Ejemplo: 21 mayo 2025


  // Crear instancia PDF
  $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',16);
    $pdf->SetXY(100, 10);
    $pdf->Cell(0,16,'Comprobante de Egreso','TLR',1,'C');
    
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetXY(100, 20);
    $pdf->Cell(0,10,"No: $nuevo_recibo_id",'BLR',0,'C');

    $pdf->Ln(10); 

      foreach ($usuarios_turnos as $usuario_id => $turnos_usuario) {
      // Sumar el total de todos los turnos del usuario
      $total = array_sum(array_column($turnos_usuario, 'valor'));
        $id_turno = $row['id_turno'];
        $usuario_id = $row['usuario_id'];
        $fecha_inicio = $row['fecha_inicio'];
        $fecha_fin = $row['fecha_fin'];
        $pagado = $row['pagado'];

        $valorenLetras = numeroALetrasCOP($total);

        // Insertar recibo en la BD
        $insert = $pdo->prepare("INSERT INTO usuarios_recibos (recibos_id, recibo_fecha, recibo_usuario, recibo_concepto, recibo_valor, recibo_elaborado)
                                VALUES (?, ?, ?, 'Pago de turnos', ?, ?)");
        $insert->execute([$nuevo_recibo_id, $fecha_actual, $usuario_id, $total, $elaborado]);

        // Obtener nombre del usuario
        $nombre = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
        $nombre->execute([$usuario_id]);
        $usuario_nombre = $nombre->fetchColumn();

        $cedula = $pdo->prepare("SELECT cedula FROM usuarios WHERE id = ?");
        $cedula->execute([$usuario_id]);
        $usuario_cedula = $cedula->fetchColumn();      

        // Agregar al PDF
        $pdf->SetFont('Arial','B',14);
        $pdf->SetXY(10, 30);
        $pdf->Cell(0, 10, "  CIUDAD:", 1, 0);

        $pdf->SetXY(40, 30);
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0, 10, " Montenegro - Quindio", 0, 0);

        $pdf->SetXY(90, 30);
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0, 10, "  FECHA:", 1, 1);
        $pdf->SetXY(115, 30);
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0, 10, "$fecha_formateada", 0, 1);

        $pdf->SetXY(150, 30);
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0, 10, "VALOR:", 1, 1);
        $pdf->SetXY(175, 30);
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0, 10, '$ ' . number_format($total, 0, ',', '.'), 0, 1);


        $pdf->SetFont('Arial','B',14);
        $pdf->SetXY(10, 40);
        $pdf->Cell(0, 15, "  PAGADO A:  ", 1, 1);
        $pdf->SetFont('Arial','',12);
        $pdf->SetXY(50, 40);
        $pdf->Cell(0, 15, "$usuario_nombre" . "  C.C:  " .  number_format($usuario_cedula, 0, ',', '.'), 0, 1);
          
        
        // 🟢 Aquí listamos todos los turnos de este usuario
        $concepto = "  POR CONCEPTO DE:         ";
        foreach ($turnos_usuario as $t) {
            $concepto .= "Turno-{$t['id_turno']} ({$t['fecha_inicio']}) , ";
        }
        $concepto = rtrim($concepto, ', ');
        $pdf->SetXY(10, 55);
        $pdf->MultiCell(0, 10, $concepto, 1);

        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0, 15, "  LA SUMA DE: (en letras)     $valorenLetras", 1, 1);
        // $pdf->SetFont('Arial','',14);
        // $pdf->SetXY(100, 85);
        // $pdf->Cell(0, 15, "$valorenLetras", 0, 1);

        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0, 10, "  ELABORO:    $nombre_usuario     USUARIO: $elaborado", 1, 1);
        // $pdf->SetFont('Arial','',12);
        // $pdf->SetXY(50, 100);
        // $pdf->Cell(0, 10, " $nombre_usuario     USUARIO: $elaborado", 0, 1);

        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0, 10, "  FIRMA Y SELLO DEL BENEFICIARIO:", 'TLR', 1);
        $pdf->Cell(0, 20, "", 'LR', 1);
        $pdf->Cell(0, 10, "  C.C No:                                                             Fecha de recibido:", 'BLR', 1);

        
        
        // $pdf->Cell(0, 10, "Recibo: $nuevo_recibo_id", 0, 1);
        // $pdf->Cell(0, 10, "Turno: $id_turno", 0, 1);
        // $pdf->Cell(0, 10, "Usuario: $usuario_nombre (ID: $usuario_id)", 0, 1);
        // $pdf->Cell(0, 10, "Fecha: $fecha_actual", 0, 1);
        // $pdf->Cell(0, 10, "Total pagado: $ $total", 0, 1);
        // $pdf->Cell(0, 10, "Elaborado por: $elaborado", 0, 1);
        // $pdf->Ln(5);
    }

    // Marcar turnos como pagados
    $update = $pdo->prepare("UPDATE usuarios_turnos SET pagado = 1 WHERE id_turno IN ($placeholders)");
    $update->execute($turnos);

    // Descargar el PDF
    $pdf->Output('I', 'recibo_turnos_' . date('Ymd_His') . '.pdf');
    exit;
} else {
    echo "⚠️ No se seleccionaron turnos.";
}
