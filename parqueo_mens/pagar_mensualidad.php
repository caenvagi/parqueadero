
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


$fecha = date("Y-m-d");
$placa = $_POST['placa'];
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'] ;
$valor = $_POST['valor_real'];
$caseta = $_POST['caseta'];
$categoria = $_POST['categoria'];
$usuario = $_SESSION['id']; // puedes reemplazar por sesión

if(!$placa ) {
    echo "error 1";
    exit;
}

try {

    $pdo->beginTransaction();

    // 1️⃣ ACTUALIZAR PAGO ACTUAL A PAGADO
    $update = $pdo->prepare("
        UPDATE pagos
        SET estado = 'PAGADO',
            fecha = CURDATE()
        WHERE id = (
            SELECT id FROM (
                SELECT id 
                FROM pagos
                WHERE placa = ?
                AND estado = 'PENDIENTE'
                ORDER BY fecha_fin DESC
                LIMIT 1
            ) AS t
        );
            ");
    $update->execute([$placa]);

    // 2️⃣ GUARDAR EN RECIBO
    $recibo = $pdo->prepare("
        INSERT INTO recibo
        (
            placa,
            recibo_man,
            fecha_ini,
            fecha_fin,
            tarifa_recibo,
            plan,
            valor_pagado,
            usuario,
            cierre
        )
        VALUES (?,?,?,?,?,?,?,?,?)
    ");

    $recibo->execute([
        $placa,
        '0',
        $fecha_inicio,
        $fecha_fin,
        $categoria,
        '3',
        $valor,
        $usuario,
        'NO'
    ]);

    // 3️⃣ GUARDAR EN CAJA
    // $caja = $pdo->prepare("
    //     INSERT INTO caja
    //     (
    //         fecha,
    //         concepto,
    //         placa,
    //         valor,
    //         usuario
    //     )
    //     VALUES
    //     (NOW(),'Mensualidad',?,?,?)
    // ");

    // $caja->execute([
    //     $placa,
    //     $valor,
    //     $usuario
    // ]);

    // 4️⃣ CREAR NUEVO PERIODO (SIGUIENTE MES)

    $nueva_inicio = date("Y-m-d", strtotime($fecha_fin . " +0 day"));
    $nueva_fin = date("Y-m-d", strtotime($nueva_inicio . " +1 MONTH"));

    $nuevo = $pdo->prepare("
        INSERT INTO pagos
        (
            fecha,
            placa,
            caseta,
            plan,
            fecha_inicio,
            fecha_fin,
            valor,
            estado,
            usuario,
            observacion
        )
        VALUES (?,?,?,?,?,?,?,?,?,?)
    ");

    $nuevo->execute([
        $fecha,
        $placa,
        $caseta,
        '3',
        $nueva_inicio,
        $nueva_fin,
        $valor,
        'PENDIENTE',
        $usuario,
        'Pago Mensualidad'

    ]);

    $pdo->commit();

    echo "ok";

} catch (Exception $e) {

    $pdo->rollBack();
    echo "error 2";

}