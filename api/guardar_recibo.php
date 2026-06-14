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
         (recibo_man, fecha_recibo, ticket, placa, fecha_ini, fecha_fin, tiempo, tarifa_recibo, plan, valor_pagado, valor_manual, usuario, cierre, periodo)
         VALUES 
         ('0', NOW(), :ticket, :placa, :fecha_ini, :fecha_fin, :tiempo, :tarifa, :plan, :valor, 0, 2, 'NO', 1)";

     $stmt = $pdo->prepare($sql);
     $stmt->execute([
         ':ticket' => $ticket,
         ':placa' => $placa,
         ':fecha_ini' => $fecha_ini,
         ':fecha_fin' => $fecha_fin,
         ':tiempo' => $tiempo,
         ':tarifa' => $tarifa,
         ':plan' => '1',
         ':valor' => $valor

     ]);

     SLEEP(1);

      // 6️⃣ Registrar movimiento en caja

$stmt = $pdo->query("  SELECT  R.recibo_id,
                               R.tarifa_recibo as categoria,
                               C.cat_nombre,
                               T.tar_tiempo as plan
                        FROM recibo AS R
                        INNER JOIN categorias as C ON R.tarifa_recibo = C.cat_id
                        INNER JOIN tar_tiempo AS T ON R.plan = T.tar_id_nombre
                        ORDER BY recibo_id 
                        DESC LIMIT 1");
$recibo = $stmt->fetch();

$recibo_id = $recibo['recibo_id'];
$categoria = $recibo['categoria'];
$cat_nombre = $recibo['cat_nombre'];
$plan = $recibo['plan'];


    $stmt2 = $pdo->prepare("
        INSERT INTO caja ( fecha_movimiento, recibo_id, movimiento, desc_movimiento, valor_ingreso, user_login, caja_tipo, caja, liquidado )
        VALUES ( NOW(), :recibo, '3', :tiempo, :valor, :usuario,'INGRESO', 'PARQUEADERO', 'NO')
    ");
    $stmt2->execute([
        ':recibo' => $recibo_id,
        ':valor' => $_POST['valor'],
        ':tiempo' => $cat_nombre . '-' . $_POST['placa'] . '  Tarifa por '.$plan,
        ':usuario' =>'2'
    ]);

     

    // ✅ CERRAR PARQUEO
    $sql2 = "UPDATE parqueo SET estado='NO' WHERE parqueo_id=:id";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([':id' => $ticket]);

     $reciboUlt =  $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => "Pago registrado correctamente",
        'ticket' => (int)$ticket,
        'recibo' => (int)$reciboUlt
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}