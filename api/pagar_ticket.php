<?php
header('Content-Type: application/json');
require_once "../conexion/conexion.php";

$codigo = $_POST['codigo'] ?? null;

if (!$codigo) {
    echo json_encode([
        "success" => false,
        "message" => "Código vacío"
    ]);
    exit;
}

try {

    // 👉 Buscar el parqueo activo
    $sql = "SELECT 
                p.parqueo_id,
                p.placa_cli,
                p.fecha_ini,
                t.tar_valor
            FROM parqueo p
            LEFT JOIN tarifas t ON p.tarifa = t.tar_id
            WHERE p.parqueo_id = :codigo 
            AND p.estado = 'SI'
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['codigo' => $codigo]);

    $data = $stmt->fetch();

    if (!$data) {
        echo json_encode([
            "success" => false,
            "message" => "Ticket no encontrado o ya pagado"
        ]);
        exit;
    }

    // 👉 Actualizar estado
    $update = "UPDATE parqueo 
               SET estado = 'NO' 
               WHERE parqueo_id = :codigo";

    $stmtUpdate = $pdo->prepare($update);
    $stmtUpdate->execute(['codigo' => $codigo]);

    echo json_encode([
        "success" => true,
        "message" => "Pago realizado correctamente"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al pagar",
        "error" => $e->getMessage()
    ]);
}