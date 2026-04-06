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

    echo "<div class='alert alert-success'>Recibo guardado correctamente</div>";

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}