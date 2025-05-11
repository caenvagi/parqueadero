<?php
require_once "../conexion/conexion.php";


// Validar que se recibió un ID por GET
if (!isset($_GET['id_turno']) || !is_numeric($_GET['id_turno'])) {
    http_response_code(400);
    echo "ID inválido";
    exit;
}

$id = (int)$_GET['id_turno'];

// Preparar y ejecutar la eliminación
$stmt = $pdo->prepare("DELETE FROM usuarios_turnos WHERE id_turno = ?");
if ($stmt->execute([$id])) {
    echo "ok";
} else {
    http_response_code(500);
    echo "Error al eliminar";
}
