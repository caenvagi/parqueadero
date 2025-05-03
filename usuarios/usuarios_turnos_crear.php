<?php
require_once "../conexion/conexion.php";

// Obtener y validar
$usuario_id = $_POST['usuario_id'];
$inicio = $_POST['inicio'];
$fin = $_POST['fin'];
$valor = $_POST['valor'];

// Separar fechas y horas
$fecha_inicio = explode("T", $inicio)[0];
$hora_inicio = explode("T", $inicio)[1];

$fecha_fin = explode("T", $fin)[0];
$hora_fin = explode("T", $fin)[1];

// Validar solapamiento
$sql = "SELECT COUNT(*) FROM usuarios_turnos
        WHERE usuario_id = ? AND
        (
          (fecha_inicio <= ? AND fecha_fin >= ?) OR
          (fecha_inicio <= ? AND fecha_fin >= ?) OR
          (? <= fecha_inicio AND ? >= fecha_fin)
        ) AND (
          hora_inicio < ? AND hora_fin > ?
        )";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id, $fecha_inicio, $fecha_inicio, $fecha_fin, $fecha_fin, $fecha_inicio, $fecha_fin, $hora_fin, $hora_inicio]);

if ($stmt->fetchColumn() > 0) {
  echo "Conflicto: el empleado ya tiene un turno en ese rango";
  exit;
}

// Insertar turno
$stmt = $pdo->prepare("INSERT INTO usuarios_turnos (usuario_id, fecha_inicio, hora_inicio, fecha_fin, hora_fin, valor)
                       VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$usuario_id, $fecha_inicio, $hora_inicio, $fecha_fin, $hora_fin, $valor]);

echo "ok";
