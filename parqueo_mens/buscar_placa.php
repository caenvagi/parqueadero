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
            v.valor,
            v.placa,
            v.vehiculo,
            v.nombre,
            v.cedula,
            v.caseta,
            v.categoria,
            c.tar_tiempo as plan,
            p.fecha_inicio,
            p.fecha_fin
        FROM cliente v
        inner join pagos p on v.placa = p.placa
        inner join tar_tiempo c on v.cli_tar_tiempo = c.tar_id_nombre
        WHERE v.placa = ?
        ORDER BY p.fecha_fin DESC
        limit 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$placa]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if($data){

echo json_encode([
    'existe'=>true,
    'nombre'=>$data['nombre'],
    'cedula'=>$data['cedula'],
    'caseta'=>$data['caseta'],
    'categoria'=>$data['categoria'],
    'valor'=>$data['valor'],
    'vehiculo'=>$data['vehiculo'],
    'fecha_inicio'=>$data['fecha_inicio'],
    'fecha_fin'=>$data['fecha_fin'],
    'plan'=>$data['plan']
]);

}else{

echo json_encode([
    'existe'=>false
]);

}