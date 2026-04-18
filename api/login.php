<?php
header("Content-Type: application/json");
require_once "../conexion/conexion.php";

// Leer JSON
$data = json_decode(file_get_contents("php://input"), true);

$usuario = $data['usuario'] ?? '';
$clave   = $data['clave'] ?? '';

// Validar
if (empty($usuario) || empty($clave)) {
    echo json_encode([
        "success" => false,
        "message" => "Campos vacios"
    ]);
    exit;
}

try {

    $sql = "SELECT * FROM usuarios 
            WHERE usuario = :usuario 
            AND activo = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario' => $usuario]);

    $user = $stmt->fetch();

    if ($user && password_verify($clave, $user['clave'])) {

        echo json_encode([
            "success" => true,
            "message" => "Login correcto",
            "data" => $user
        ]);

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Usuario o contraseña incorrectos"
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}