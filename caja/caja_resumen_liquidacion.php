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

$sql = "SELECT C.id_movimiento, C.recibo_id, C.desc_movimiento, C.valor_ingreso, C.valor_egreso,
           C.user_login, U.nombre AS nombre_usuario, R.recibo_man AS FPAR,
           R.tipo_pago
        FROM caja as C 
        INNER JOIN recibo as R ON C.recibo_id = R.recibo_id
    LEFT JOIN usuarios AS U ON C.user_login = U.id
    WHERE C.id_movimiento IN ($in)
    ORDER BY R.recibo_man ASC";
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
        'user_login' => $row['user_login'],
        'nombre_usuario' => $row['nombre_usuario'] ?? 'Desconocido',
        'tipo_pago' => $row['tipo_pago'] ?? null,
        'recibo_man' => $row['FPAR'],
    ];
}

echo json_encode([
    'fecha' => date('d/m/Y H:i'),
    'movimientos' => $movimientos
]);

