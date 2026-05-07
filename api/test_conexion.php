<?php
ob_clean(); // 🔥 limpia cualquier salida previa
header('Content-Type: application/json');

require_once "../conexion/conexion.php";

try {
    $pdo->query("SELECT 1");

    echo json_encode([
        "success" => true,
        "mensaje" => "Conexion exitosa"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Error en la base de datos"
    ]);
}

exit; // 🔥 corta cualquier salida extra