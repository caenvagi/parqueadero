<?php
require_once "../conexion/conexion.php";
ob_clean();
header('Content-Type: application/json');
error_reporting(0);
date_default_timezone_set('America/Bogota');

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        throw new Exception("Datos invalidos");
    }

    $placa = strtoupper(trim($data['placa'] ?? ''));
    $tarifa = (int)($data['tarifa'] ?? 0);
    $caseta = (int)($data['caseta'] ?? 0);
    $usuario = (int)($data['usuario'] ?? 0);

    if (!$placa || !$tarifa || !$caseta || !$usuario) {
        throw new Exception("Datos incompletos");
    }

    $stmt = $pdo->prepare("SELECT 1 FROM parqueo WHERE placa_cli = :placa AND estado = 'SI'");
    $stmt->execute(['placa' => $placa]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "El vehiculo con placa $placa ya se encuentra en el parqueadero."
        ]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT 1
                           FROM cliente
                           WHERE placa = :placa
                           AND activo = 'SI'
                           AND mensualidad = 'SI'");
    $stmt->execute(['placa' => $placa]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "El vehiculo con placa $placa tiene mensualidad activa y no puede ingresar por horas."
        ]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT placa FROM cliente WHERE placa = ?");
    $stmt->execute([$placa]);

    if ($stmt->rowCount() == 0) {
        echo json_encode([
            "success" => false,
            "message" => "El cliente con placa $placa no existe en la base de datos."
        ]);
        exit;
    }

    

    $pdo->beginTransaction();

    $sql = "INSERT INTO parqueo
            (placa_cli, fecha_ini, tarifa, caseta, usuario, estado)
            VALUES (?, NOW(), ?, ?, ?, 'SI')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa, $tarifa, $caseta, $usuario]);

    $ticket = $pdo->lastInsertId();

    $update = $pdo->prepare("UPDATE casetas SET casetas_estado = 'Ocupado' WHERE caseta_id = ?");
    $update->execute([$caseta]);

    $pdo->commit();



    sleep(1);

    echo json_encode([
        "success" => true,
        "message" => "Ingreso registrado",
        "ticket" => (int)$ticket
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
