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

$recibo = $_POST['recibo'];

// Validar si ya existe
$sql = "SELECT COUNT(*) FROM recibo WHERE recibo_man = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$recibo]);
$existe = $stmt->fetchColumn();

if ($existe > 0) {
    echo "<div class='alert alert-danger'>Error: Este número de recibo ya existe.</div>";
    exit;
}

// Validar cliente: si existe actualizar, si no crear
$placa_cli = strtoupper(trim($_POST['placa'] ?? ''));
$categoria_cli = $_POST['categoria'] ?? null;
$valor_cli = $_POST['valor'] ?? null;
$plan_cli = $_POST['plan'] ?? null;
$user_cli = $id;

if ($placa_cli) {
    $stmtC = $pdo->prepare("SELECT placa FROM cliente WHERE placa = ? LIMIT 1");
    $stmtC->execute([$placa_cli]);
    if ($stmtC->rowCount() > 0) {
        $updateC = $pdo->prepare("UPDATE cliente SET categoria = ?, valor = ?, cli_tar_tiempo = ?, user = ? WHERE placa = ?");
        $updateC->execute([$categoria_cli, $valor_cli, $plan_cli, $user_cli, $placa_cli]);
    } else {
        $insertC = $pdo->prepare("INSERT INTO cliente (
                                                fecha_creacion, 
                                                placa, 
                                                nombre, 
                                                cedula, 
                                                celular, 
                                                vehiculo, 
                                                categoria, 
                                                valor, 
                                                cli_tar_tiempo, 
                                                caseta, 
                                                mensualidad, 
                                                activo, 
                                                user) 
                                                VALUES (
                                                NOW(), 
                                                ?, 
                                                'CLIENTE GENERAL', 
                                                0, 
                                                '', 
                                                '', 
                                                ?, 
                                                ?, 
                                                ?, 
                                                85, 
                                                'NO', 
                                                'NO', 
                                                ?)");
                            $insertC->execute([ $placa_cli, 
                                                $categoria_cli, 
                                                $valor_cli, 
                                                $plan_cli, 
                                                $user_cli]);
    }
}

try {

    $sql = "INSERT INTO recibo 
    (recibo_man, fecha_recibo, ticket, placa, fecha_ini, fecha_fin, tiempo, tarifa_recibo, plan, valor_manual, valor_pagado, usuario, cierre, periodo)
    VALUES 
    (:recibo_man, NOW(), :ticket, :placa, :fecha_ini, :fecha_fin, :tiempo, :tarifa_recibo, :plan, :valor_manual, :valor_pagado, :usuario, :cierre, :periodo)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':recibo_man' => $_POST['recibo'],
        ':ticket' => 0,
        ':placa' => strtoupper($_POST['placa']),
        ':fecha_ini' => $_POST['fecha_ini'] ?: null,
        ':fecha_fin' => $_POST['fecha_fin'] ?: null,
        ':tiempo' => $_POST['tiempo'],
        ':tarifa_recibo' => $_POST['categoria'],
        ':plan' => $_POST['plan'],
        ':valor_manual' => 0,
        ':valor_pagado' => $_POST['valor'],
        ':usuario' => $id,
        ':cierre' => 'NO',
        ':periodo' => 0
    ]);

    // 2️⃣ Calcular el tiempo transcurrido
        $fecha_ini = new DateTime($_POST['fecha_ini']);
        $fecha_fin = new DateTime('now', new DateTimeZone('America/Bogota'));
        $intervalo = $fecha_ini->diff($fecha_fin);

        $dias = $intervalo->days;
        $horas = $intervalo->h;
        $minutos = $intervalo->i;
        $segundos = $intervalo->s;

        // Calcular el total de minutos para la tarifa
        $tiempo_minutos = ($dias * 24 * 60) + ($horas * 60) + $minutos;

        
         // 🕒 Nuevo formato del tiempo: Días, Horas y Minutos
    if ($dias > 0) {
        $tiempo_txt = sprintf("%d Dias %02d Horas %02d Min", $dias, $horas, $minutos);
    } else {
        $tiempo_txt = sprintf("%02dh %02dm", $horas, $minutos);
    }  
    
    sleep(1);

     $recibo_id = $pdo->lastInsertId();

 $stmt = $pdo->query("  SELECT R.recibo_id,
                               R.tarifa_recibo as categoria,
                               C.cat_nombre,
                               T.tar_tiempo as plan
                        FROM recibo AS R
                        INNER JOIN categorias as C ON R.tarifa_recibo = C.cat_id
                        INNER JOIN tar_tiempo AS T ON R.plan = T.tar_id_nombre
                        ORDER BY recibo_id 
                        DESC LIMIT 1");
$recibo = $stmt->fetch();

$categoria = $recibo['categoria'];
$cat_nombre = $recibo['cat_nombre'];
$plan = $recibo['plan'];

    // 6️⃣ Registrar movimiento en caja
    $stmt2 = $pdo->prepare("
        INSERT INTO caja (recibo_id, fecha_movimiento, movimiento, desc_movimiento, valor_ingreso, user_login, caja_tipo, caja, liquidado )
        VALUES (:recibo, NOW(), '3', :tiempo, :valor, :usuario,'INGRESO', 'PARQUEADERO', 'NO')
    ");
    $stmt2->execute([
        ':recibo' => $recibo_id,
        ':valor' => $_POST['valor'],
        ':tiempo' => $cat_nombre . '-' . $_POST['placa'] . '  Tarifa por '.$plan,
        ':usuario' => $_SESSION['id'] ?? 'sistema'
    ]);

    echo "<div class='alert alert-success'>Recibo guardado correctamente</div>";

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}