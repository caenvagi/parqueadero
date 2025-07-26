<?php
require_once "../conexion/conexion.php";
session_start();

$ids = explode(',', $_POST['ids']);
$total = $_POST['total'];
$entregado = $_POST['entregado_por'];
$recibido = $_POST['recibido_por'];
$usuario = $_SESSION['id'] ?? null;
$fecha = date('Y-m-d H:i:s');
$recibido_por = $_POST['recibido_por'] ?? null;


// Guardar en tabla de liquidaciones
$sql = "INSERT INTO caja_liquidaciones (fecha, total, entregado_por, recibido_por, usuario_id)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$fecha, $total, $entregado, $recibido, $usuario]);

$id_liquidacion = $pdo->lastInsertId();

// Marcar registros como liquidados
$sql = "UPDATE caja SET liquidado = 'SI', fecha_liquidacion = '$fecha' , user_liquida = '$usuario' WHERE id_movimiento IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge( $ids));

header("Location: caja_listado.php?liquidado=ok");
