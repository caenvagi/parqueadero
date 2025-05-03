<?php
require_once "../conexion/conexion.php";
$usuario_id = $_GET['usuario_id'] ?? null;

$sql = "SELECT t.*, u.nombre 
        FROM usuarios_turnos t 
        JOIN usuarios u ON t.usuario_id = u.id 
        WHERE CONCAT(t.fecha_inicio, ' ', t.hora_inicio) >= ? 
          AND CONCAT(t.fecha_fin, ' ', t.hora_fin) <= ?";
$params = [$_GET['start'], $_GET['end']];

if (!empty($usuario_id)) {
    $sql .= " AND u.id = ?";
    $params[] = $usuario_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$eventos = [];

foreach ($stmt as $row) {
    $eventos[] = [
        'id' => $row['id_turno'], // ESTE ES EL CONSECUTIVO
        'title' => $row['nombre'] . " - $" . $row['valor'],
        'start' => $row['fecha_inicio'] . 'T' . $row['hora_inicio'],
        'end'   => $row['fecha_fin'] . 'T' . $row['hora_fin'],
        'color' => "#".substr(md5($row['usuario_id']), 0, 6) // color único por empleado
    ];
}

echo json_encode($eventos);
