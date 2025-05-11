<?php
require_once "../conexion/conexion.php";

$usuario_id = $_GET['usuario_id'] ?? null;
$fecha_inicio = $_GET['start'];
$fecha_fin = $_GET['end'];

$sql = "SELECT t.*, u.nombre 
        FROM usuarios_turnos t 
        JOIN usuarios u ON t.usuario_id = u.id 
        WHERE t.fecha_inicio >= ? 
          AND t.fecha_fin <= ?";
$params = [$fecha_inicio, $fecha_fin];

if (!empty($usuario_id)) {
    $sql .= " AND t.usuario_id = ?";
    $params[] = $usuario_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$eventos = [];

foreach ($stmt as $row) {
    $eventos[] = [
        'id'    => $row['id_turno'],
        'title' => $row['nombre'] . " - $" . $row['valor'],
        'start' => $row['fecha_inicio'],
        'end'   => $row['fecha_fin'],
        'color' => "#" . substr(md5($row['usuario_id']), 0, 6)
    ];
}

echo json_encode($eventos);
