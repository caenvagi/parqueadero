<?php
header('Content-Type: application/json');

require_once "../conexion/conexion.php";

try {

    $sql = "SELECT cat_id, cat_nombre FROM categorias";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $categorias
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}