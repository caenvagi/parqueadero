
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
$nombre = $_POST['nombre'];
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'] ;
$plan = $_POST['plan'];
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

    // 🔥 Obtener el ID del recibo recién creado
    $recibo_id = $pdo->lastInsertId();

    // 3️⃣ GUARDAR EN CAJA
    $caja = $pdo->prepare("
        INSERT INTO caja
        (
            fecha_movimiento,
            movimiento,
            desc_movimiento,
            recibo_id,
            rec_manual,
            valor_ingreso,
            valor_egreso,
            user_login,
            caja_tipo,
            caja
        )
        VALUES        
        (
        NOW(),
        '3',
        '$plan - $placa - $nombre',
        ?,
        '-',
        ?,
        '0',
        ?,
        'INGRESO',
        'PARQUEADERO'

        
        
        )
    ");

    $caja->execute([
        $recibo_id,
         //$placa,
        $valor,
        $usuario
    ]);

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
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "Error: " . $e->getMessage();
    echo "<br>Línea: " . $e->getLine();
    echo "<br>Archivo: " . $e->getFile();
}