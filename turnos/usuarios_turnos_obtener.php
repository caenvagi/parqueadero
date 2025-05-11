<?php
require_once "../conexion/conexion.php";


header('Content-Type: application/json');

$id_turno = $_GET['id_turno'] ?? null;

if (!$id_turno) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro id_turno']);
    exit;
}

// Ajustar el nombre de la columna según tu base de datos:
// Si es `id`, cambia la consulta:
$stmt = $pdo->prepare("SELECT * FROM usuarios_turnos WHERE id_turno = ?");
$stmt->execute([$id_turno]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if ($turno) {
    echo json_encode($turno);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Turno no encontrado']);
}