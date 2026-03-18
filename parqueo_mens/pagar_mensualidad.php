
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
// $fecha_inicio = $_POST['inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ;
$valor = $_POST['valor'];
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
    // $recibo = $pdo->prepare("
    //     INSERT INTO recibo
    //     (
    //         placa,
    //         fecha_ini,
    //         fecha_fin,
    //         valor_pagado,
    //         usuario
    //     )
    //     VALUES (?,?,?,?,?)
    // ");

    // $recibo->execute([
    //     $placa,
    //     $fecha_inicio,
    //     $fecha_fin,
    //     $valor,
    //     $usuario
    // ]);

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
            placa,
            fecha_inicio,
            fecha_fin,
            valor,
            estado,
            usuario
        )
        VALUES (?,?,?,?,?,?)
    ");

    $nuevo->execute([
        $placa,
        $nueva_inicio,
        $nueva_fin,
        $valor,
        'PENDIENTE',
        $usuario
    ]);

    $pdo->commit();

    echo "ok";

} catch (Exception $e) {

    $pdo->rollBack();
    echo "error 2";

}