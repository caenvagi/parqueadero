
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

$inicio = new DateTime($fecha_inicio);
$fin    = new DateTime($fecha_fin);

if ($fin < $inicio) {
    die("Error: la fecha final no puede ser menor a la inicial");
}

$diferencia = $inicio->diff($fin);

// Meses totales
$meses = $diferencia->m + ($diferencia->y * 12);
$dias  = $diferencia->d;
$horas = $diferencia->h;
$minutos = $diferencia->i;

// 🧠 Construcción del texto
$partes = [];

// Meses
if ($meses > 0) {
    $partes[] = $meses . ' ' . ($meses == 1 ? 'mes' : 'meses');
}

// 🔥 Lógica especial para días/semanas
if ($dias > 0) {
    if ($dias % 7 == 0) {
        // Es múltiplo exacto de 7 → semanas
        $semanas = $dias / 7;
        $partes[] = $semanas . ' ' . ($semanas == 1 ? 'semana' : 'semanas');
    } else {
        // No es múltiplo → días normales
        $partes[] = $dias . ' ' . ($dias == 1 ? 'día' : 'días');
    }
}

// Horas
if ($horas > 0) {
    $partes[] = $horas . ' ' . ($horas == 1 ? 'hora' : 'horas');
}

// Minutos
if ($minutos > 0) {
    $partes[] = $minutos . ' ' . ($minutos == 1 ? 'minuto' : 'minutos');
}

// Resultado final
$tiempo_txt = empty($partes) ? "0 minutos" : implode(', ', $partes);



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
            tiempo,
            tarifa_recibo,
            plan,
            valor_pagado,
            usuario,
            cierre
        )
        VALUES (?,?,?,?,?,?,?,?,?,?)
    ");

    $recibo->execute([
        $placa,
        '0',
        $fecha_inicio,
        $fecha_fin,
        $tiempo_txt,
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