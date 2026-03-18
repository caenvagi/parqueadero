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


$sql = "SELECT 
            id,
            fecha_inicio,
            fecha_fin,
            valor,
            estado,
            fecha
        FROM pagos
        WHERE placa = ?
        ORDER BY fecha_inicio DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$placa]);

$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!$pagos){
    echo "<tr><td colspan='5' class='text-center'>No hay pagos registrados</td></tr>";
    exit;
}

foreach($pagos as $p){

    if($p['estado']=='PAGADO'){
        $estado = "<span class='badge bg-success'>Pagado</span>";
    }elseif($p['estado']=='PENDIENTE'){
        $estado = "<span class='badge bg-warning text-dark'>Pendiente</span>";
    }else{
        $estado = "<span class='badge bg-danger'>Pendiente</span>";
    }

    echo "<tr>
        <td>{$p['id']}</td>
        <td>{$p['fecha']}</td>
        <td>{$p['fecha_inicio']}</td>
        <td>{$p['fecha_fin']}</td>
        <td>$ ".number_format($p['valor'],0,',','.')."</td>
        <td>$estado</td>

    </tr>";
}