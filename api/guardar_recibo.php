<?php
header('Content-Type: application/json');
require_once "../conexion/conexion.php";
date_default_timezone_set('America/Bogota');

try {

    // ✅ VALIDAR DATOS
    if (
        !isset($_POST['ticket']) ||
        !isset($_POST['placa']) ||
        !isset($_POST['fecha_ini']) ||
        !isset($_POST['tiempo']) ||
         !isset($_POST['tarifa']) ||
         !isset($_POST['valor']) ||
         !isset($_POST['usuario'])
    ) {
        echo json_encode([
            "success" => false,
            "message" => "Faltan datos"
        ]);
        exit;
    }

    $ticket = $_POST['ticket'];
    $placa = $_POST['placa'];
     $fecha_ini = $_POST['fecha_ini'];
     $fecha_fin = date("Y-m-d H:i:s");
     $tiempo = $_POST['tiempo'];
     $tarifa = $_POST['tarifa'];
     $valor = $_POST['valor'];

    // ✅ INSERT RECIBO
     $sql = "INSERT INTO recibo 
         (recibo_man, fecha_recibo, ticket, placa, fecha_ini, fecha_fin, tiempo, tarifa_recibo, valor_pagado, valor_manual, usuario, cierre, periodo)
         VALUES 
         ('NO', NOW(), :ticket, :placa, :fecha_ini, :fecha_fin, :tiempo, :tarifa, :valor, 0, 2, 'NO', 1)";

     $stmt = $pdo->prepare($sql);
     $stmt->execute([
         ':ticket' => $ticket,
         ':placa' => $placa,
         ':fecha_ini' => $fecha_ini,
         ':fecha_fin' => $fecha_fin,
         ':tiempo' => $tiempo,
         ':tarifa' => $tarifa,
         ':valor' => $valor

     ]);
      $recibo =  $pdo->lastInsertId();

    // ✅ CERRAR PARQUEO
    $sql2 = "UPDATE parqueo SET estado='NO' WHERE parqueo_id=:id";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([':id' => $ticket]);

    echo json_encode([
        'success' => true,
        'message' => "Pago registrado correctamente",
        'ticket' => (int)$ticket,
        'recibo' => (int)$recibo
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}