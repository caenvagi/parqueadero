<?php
header('Content-Type: application/json');

require_once "../conexion/conexion.php";

try {

    $sql = "SELECT caseta_id, casetas_nom FROM casetas";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $casetas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $casetas
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}