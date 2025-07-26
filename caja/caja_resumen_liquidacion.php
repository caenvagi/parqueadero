<?php
require_once "../conexion/conexion.php";

if (!isset($_POST['ids'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No hay IDs']);
    exit;
}

date_default_timezone_set('America/Bogota');

$ids = $_POST['ids'];
$in  = str_repeat('?,', count($ids) - 1) . '?';

$sql = "SELECT id_movimiento, desc_movimiento, valor_ingreso, valor_egreso
        FROM caja WHERE id_movimiento IN ($in)";
$stmt = $pdo->prepare($sql);
$stmt->execute($ids);
$rows = $stmt->fetchAll();

$movimientos = [];
foreach ($rows as $row) {
    $movimientos[] = [
        'descripcion' => $row['desc_movimiento'],
        'valor_ingreso' => (int)$row['valor_ingreso'],
        'valor_egreso' => (int)$row['valor_egreso'],
    ];
}

echo json_encode([
    'fecha' => date('d/m/Y H:i'),
    'movimientos' => $movimientos
]);

