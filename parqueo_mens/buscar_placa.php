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
// $placa = 'AAA999';

$sql = "SELECT 
            v.valor,
            v.placa,
            v.vehiculo,
            v.nombre,
            v.cedula
        FROM cliente v
        WHERE v.placa = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$placa]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if($data){

echo json_encode([
    'existe'=>true,
    'nombre'=>$data['nombre'],
    'cedula'=>$data['cedula'],
    'valor'=>$data['valor'],
    'vehiculo'=>$data['vehiculo']
]);

}else{

echo json_encode([
    'existe'=>false
]);

}