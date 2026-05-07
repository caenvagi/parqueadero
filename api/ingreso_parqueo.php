<?php
require_once "../conexion/conexion.php";
ob_clean();
header('Content-Type: application/json');
error_reporting(0);


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

sleep(1);

$ticket = $pdo->lastInsertId();

echo json_encode([
    "success" => true,
    "message" => "Ingreso registrado",
    "ticket" => (int)$ticket
]);