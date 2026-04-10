<?php
require_once "../conexion/conexion.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$sql = "INSERT INTO parqueo 
(placa_cli, tarifa, caseta, usuario, estado)
VALUES (?, ?, ?, ?, 'A')";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $data['placa'],
    $data['tarifa'],
    $data['caseta'],
    $data['usuario']
]);

echo json_encode(["status" => "ok"]);