<?php
require_once "../conexion/conexion.php";

header('Content-Type: application/json');

// Leer datos crudos
$raw = file_get_contents("php://input");

// Decodificar JSON
$data = json_decode($raw, true);

// DEBUG (puedes activarlo si falla)
// echo $raw; exit;

// Validar JSON
if ($data === null) {
    echo json_encode([
        "status" => "error",
        "message" => "JSON inválido o vacío"
    ]);
    exit;
}

// Validar campos
$user = isset($data['usuario']) ? trim($data['usuario']) : '';
$pass = isset($data['password']) ? trim($data['password']) : '';

if ($user === '' || $pass === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Campos vacíos"
    ]);
    exit;
}

// Consulta
$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user]);

$u = $stmt->fetch();

// Validar usuario
if ($u && $pass == $u['clave']) {
    echo json_encode([
        "status" => "ok",
        "user_id" => $u['id']
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Credenciales incorrectas"
    ]);
}