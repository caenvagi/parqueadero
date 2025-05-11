<?php
require_once "../conexion/conexion.php";
$usuario_id   = $_POST['usuario_id'];
$fecha_inicio = $_POST['fecha_inicio'];
$hora_inicio  = $_POST['hora_inicio'];
$fecha_fin    = $_POST['fecha_fin'];
$hora_fin     = $_POST['hora_fin'];
$valor        = $_POST['valor'];

$inicio = "$fecha_inicio $hora_inicio";
$fin    = "$fecha_fin $hora_fin";

// Validar que el fin sea posterior al inicio
if ($fin <= $inicio) {
    exit("Error: la fecha/hora de fin debe ser posterior a la de inicio.");
}

// Verificar solapamientos solo para el mismo empleado
$sql = "SELECT COUNT(*) FROM usuarios_turnos 
        WHERE usuario_id = ?
        AND (
            (? BETWEEN CONCAT(fecha_inicio, ' ', hora_inicio) AND CONCAT(fecha_fin, ' ', hora_fin))
            OR
            (? BETWEEN CONCAT(fecha_inicio, ' ', hora_inicio) AND CONCAT(fecha_fin, ' ', hora_fin))
            OR
            (CONCAT(fecha_inicio, ' ', hora_inicio) BETWEEN ? AND ?)
        )";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id, $inicio, $fin, $inicio, $fin]);
if ($stmt->fetchColumn() > 0) {
    exit("Error: este turno se solapa con otro asignado al mismo empleado.");
}

// Insertar si no hay solapamientos
$stmt = $pdo->prepare("INSERT INTO usuarios_turnos 
    (usuario_id, fecha_inicio, hora_inicio, fecha_fin, hora_fin, valor) 
    VALUES (?, ?, ?, ?, ?, ?)");
$ok = $stmt->execute([$usuario_id, $fecha_inicio, $hora_inicio, $fecha_fin, $hora_fin, $valor]);

echo $ok ? "Turno asignado correctamente." : "Error al asignar el turno.";
