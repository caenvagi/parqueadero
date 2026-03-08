<?php
require '../conexion/conexion.php';

$placa = strtoupper($_GET['placa']);

$stmt = $pdo->prepare("
SELECT 
v.placa_cli,
c.vehiculo,
c.nombre,
c.celular,
c.categoria
FROM parqueo v
LEFT JOIN cliente c 
ON v.placa_cli = c.placa
WHERE v.placa_cli = ?
LIMIT 1
");

$stmt->execute([$placa]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);