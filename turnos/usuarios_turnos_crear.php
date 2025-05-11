<?php
require_once "../conexion/conexion.php";

// Obtener y validar datos
$usuario_id = $_POST['usuario_id'];
$inicio = $_POST['inicio']; // formato ISO: 2025-05-12T07:00
$fin    = $_POST['fin'];
$valor  = $_POST['valor'];

// Convertir a formato DATETIME de MySQL
$fecha_inicio = date('Y-m-d H:i:s', strtotime($inicio));
$fecha_fin    = date('Y-m-d H:i:s', strtotime($fin));

// Verificar solapamiento de turnos para el mismo usuario
$sql = "SELECT COUNT(*) FROM usuarios_turnos
        WHERE usuario_id = ? AND (
          (fecha_inicio < ? AND fecha_fin > ?) OR
          (fecha_inicio >= ? AND fecha_inicio < ?)
        )";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id, $fecha_fin, $fecha_inicio, $fecha_inicio, $fecha_fin]);

if ($stmt->fetchColumn() > 0) {
  echo "Conflicto: el empleado ya tiene un turno en ese rango";
  exit;
}

// Insertar el nuevo turno
$stmt = $pdo->prepare("INSERT INTO usuarios_turnos (usuario_id, fecha_inicio, fecha_fin, valor)
                       VALUES (?, ?, ?, ?)");
$stmt->execute([$usuario_id, $fecha_inicio, $fecha_fin, $valor]);

echo "ok";
