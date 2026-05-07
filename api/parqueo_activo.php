<?php
header('Content-Type: application/json');
require_once "../conexion/conexion.php";

$sql = "SELECT parqueo_id, placa_cli, fecha_ini 
        FROM parqueo 
        WHERE estado = 'SI'";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?>