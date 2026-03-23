<?php
session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
}
$id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE id=$id";
}


$placa = $_POST['placa'];
$nombre = $_POST['nombre'];
$cedula = $_POST['cedula'];
$celular = $_POST['celular'];
$caseta = $_POST['caseta'];
$plan = $_POST['plan'];
$plan_tarifa  = $_POST['plan_tarifa'];
$vehiculo = $_POST['vehiculo'];
$valor = $_POST['valor'];
$mensualidad = $_POST['mensualidad'];
$activo = $_POST['activo'];
$usuario = $_SESSION['id'];
$fechaInicio = date("Y-m-d");

// ===== CALCULAR FECHA FIN =====

   function calcularFechaFin($fechaInicio, $plan_tarifa)
{
    switch ($plan_tarifa) {
        case 7:
            return date('Y-m-d', strtotime($fechaInicio . ' +7 days'));
        case 6:
            return date('Y-m-d', strtotime($fechaInicio . ' +15 days'));
        case 3:
            return date('Y-m-d', strtotime($fechaInicio . ' +1 month'));
        default:
            return null; // importante para validar
    }
}


    $fecha_fin = calcularFechaFin($fechaInicio, $plan_tarifa);

    if (!$fecha_fin) {
    die("Error: el plan ($plan_tarifa) no generó fecha fin");
}

try {

    $sql = "UPDATE cliente SET 
                nombre = ?,
                cedula = ?,
                celular = ?,
                vehiculo = ?,
                valor = ?,
                cli_tar_tiempo = ?,
                mensualidad = ?,
                activo = ?
            WHERE placa = ?";

    $stmt1 = $pdo->prepare($sql);
    $stmt1->execute([
        $nombre,
        $cedula,
        $celular,
        $vehiculo,
        $valor,
        $plan_tarifa,
        $mensualidad,
        $activo,
        $placa
    ]);


    $sqlHist = "INSERT INTO mensualidad_historial 
                (placa, fecha_ingreso, caseta, valor, plan, usuario, observacion)
                VALUES (:placa, NOW(), :caseta, :valor, :plan, :usuario, 'Ingreso a mensualidad')";

    $stmt2 = $pdo->prepare($sqlHist);
    $stmt2->execute([
        'placa' => $placa,
        'caseta' => $caseta,
        'valor' => $valor,
        'plan' => $plan_tarifa,
        'usuario' => $usuario
        ]);


$sqlPlan = "SELECT tar_tiempo FROM tar_tiempo WHERE tar_id_nombre = :plan";
$stmtPlan = $pdo->prepare($sqlPlan);
$stmtPlan->execute(['plan' => $plan_tarifa]);

$planNombre = $stmtPlan->fetchColumn();

     $sqlPago = "INSERT INTO pagos 
                (fecha, estado, placa, valor, plan, fecha_inicio, fecha_fin, usuario, caseta, observacion)
         VALUES (NOW(), 'PENDIENTE', :placa, :valor, :plan, NOW(), :fecha_fin, :usuario, :caseta, :observacion)";

    $stmt3 = $pdo->prepare($sqlPago);   
    
    $stmt3->execute([
        'placa' => $placa,
        'caseta' => $caseta,
        'valor' => $valor,
        'plan' => $plan_tarifa,
        'fecha_fin' => $fecha_fin,
        'usuario' => $usuario,
        'observacion' => "Cobro " . $planNombre
        ]);   

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: '.$e->getMessage().'</div>';
}