<?php

require '../comexion/conexion.php';

$ticket = $_POST['ticket'];

// consultar datos
$stmt = $pdo->prepare("SELECT * FROM recibo WHERE ticket = ?");
$stmt->execute([$ticket]);
$data = $stmt->fetch();

if(!$data){
    echo json_encode(["success"=>false]);
    exit;
}

// TEXTO DEL TICKET
$texto = "
PARQUEADERO
------------------------
Ticket: {$data['ticket']}
Placa: {$data['placa']}
Fecha: {$data['fecha_ini']}
Valor: {$data['valor_pagado']}
------------------------
";

// 👇 ENVIAR A IMPRESORA (Windows)
file_put_contents("LPT1", $texto);

echo json_encode(["success"=>true]);