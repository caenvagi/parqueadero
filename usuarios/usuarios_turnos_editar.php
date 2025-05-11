<?php
require_once "../conexion/conexion.php";

$id_turno   = $_POST['id_turno'];
$usuario_id = $_POST['usuario_id'];
$inicio     = $_POST['inicio']; // Formato: 2025-05-06T08:00
$fin        = $_POST['fin'];    // Formato: 2025-05-06T17:00
$valor      = $_POST['valor'];
$pagado     = 0;

// Convertir a formato DATETIME compatible con MySQL
$fecha_inicio = date('Y-m-d H:i:s', strtotime($inicio));
$fecha_fin    = date('Y-m-d H:i:s', strtotime($fin));

$sql = "UPDATE usuarios_turnos 
        SET usuario_id = ?, fecha_inicio = ?, fecha_fin = ?, valor = ?, pagado = ? 
        WHERE id_turno = ?";

$stmt = $pdo->prepare($sql);
$result = $stmt->execute([$usuario_id, $fecha_inicio, $fecha_fin, $valor, $pagado, $id_turno]);

echo $result ? "ok" : "error";
