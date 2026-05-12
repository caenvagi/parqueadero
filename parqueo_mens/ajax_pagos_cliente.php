<?php
ob_start();

session_start();
require_once "../conexion/conexion.php";

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

date_default_timezone_set('America/Bogota');

try {

    if (!isset($_SESSION['id'])) {
        echo json_encode([
            "data" => [],
            "error" => "Sesión no iniciada"
        ]);
        exit;
    }

    $placa = $_GET['placa'] ?? '';

    $sql = "SELECT 
                fecha,
                fecha_inicio,
                fecha_fin,
                valor,
                estado
            FROM pagos
            WHERE placa = ?
            ORDER BY fecha DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa]);

    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $estadoHtml = '';

        $estado = trim($row['estado']);

        if ($estado === 'PENDIENTE') {

            $estadoHtml = '<span class="badge bg-warning">Pendiente</span>';

        } elseif ($estado === 'RETIRADO') {

            $estadoHtml = '<span class="badge bg-danger">Retirado</span>';

        } else {

            $estadoHtml = '<span class="badge bg-success">Pagado</span>';
        }

        $data[] = [
            "fecha" => $row['fecha'],
            "fecha_inicio" => $row['fecha_inicio'],
            "fecha_fin" => $row['fecha_fin'],
            "valor" => "$" . number_format($row['valor']),
            "estado" => $estadoHtml
        ];
    }

    ob_clean();

    echo json_encode([
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    ob_clean();

    echo json_encode([
        "data" => [],
        "error" => $e->getMessage()
    ]);
}