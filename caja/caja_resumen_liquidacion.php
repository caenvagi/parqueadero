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

$sql = "SELECT C.id_movimiento, C.recibo_id, C.desc_movimiento, C.valor_ingreso, C.valor_egreso, R.recibo_man AS FPAR
        FROM caja as C 
        INNER JOIN recibo as R ON C.recibo_id = R.recibo_id
        WHERE C.id_movimiento IN ($in)";
$stmt = $pdo->prepare($sql);
$stmt->execute($ids);
$rows = $stmt->fetchAll();

$movimientos = [];
foreach ($rows as $row) {
    $movimientos[] = [
        'id_movimiento' => $row['id_movimiento'],
        'recibo_id' => $row['recibo_id'],
        'descripcion' => $row['desc_movimiento'],
        'valor_ingreso' => (int)$row['valor_ingreso'],
        'valor_egreso' => (int)$row['valor_egreso'],
        'recibo_man' => $row['FPAR'],
    ];
}

echo json_encode([
    'fecha' => date('d/m/Y H:i'),
    'movimientos' => $movimientos
]);

