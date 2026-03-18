<?php
session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
}
$id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE id=$id";
}



if (!isset($_POST['ids']) || !isset($_SESSION['id'])) {
    http_response_code(400);
    exit("Solicitud inválida.");
}

$ids = $_POST['ids'];
$id_usuario = $_SESSION['id'];
$observaciones = $_POST['observaciones'] ?? '';
$recibido_por = $_POST['recibido_por'] ?? null;

try {
    $pdo->beginTransaction();

    // 1. Calcular totales
    $in = str_repeat('?,', count($ids) - 1) . '?';
    $sql_total = "SELECT SUM(valor_ingreso - valor_egreso) AS total FROM caja WHERE id_movimiento IN ($in)";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute($ids);
    $total = (int) $stmt_total->fetchColumn();

    if (!$recibido_por) {
    die("Error: No se seleccionó quién recibe la liquidación.");
}

    // 2. Insertar encabezado de liquidación
   $sql = "INSERT INTO caja_liquidaciones 
        (fecha_liquidacion, total_liquidado, entregado_por, recibido_por, usuario_liquida, observaciones)
        VALUES (NOW(), :total, :entregado_por, :recibido_por, :usuario_liquida, :observaciones)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':total' => $total,
    ':usuario_liquida' => $_SESSION['id'],
    ':entregado_por' => $_SESSION['id'],
    ':recibido_por' => $recibido_por,
    ':observaciones' => $observaciones
]);



    // ⚠️ Asegurarse de capturar el ID justo después del execute
    $id_liquidacion = $pdo->lastInsertId();
    if (!$id_liquidacion) {
        throw new Exception("Error al generar ID de liquidación.");
    }

    // 3. Insertar detalles (con descripción, ingreso, egreso, etc)
    $sql_detalles = "SELECT id_movimiento, desc_movimiento, valor_ingreso, valor_egreso, fecha_movimiento
                     FROM caja WHERE id_movimiento IN ($in)";
    $stmt_detalles = $pdo->prepare($sql_detalles);
    $stmt_detalles->execute($ids);
    $movs = $stmt_detalles->fetchAll();

    $stmt_detalle = $pdo->prepare("
        INSERT INTO caja_liquidaciones_detalle 
        (id_liquidacion, id_movimiento)
        VALUES (?, ?)
    ");

    foreach ($movs as $mov) {
        $valor = $mov['valor_ingreso'] - $mov['valor_egreso'];
        $stmt_detalle->execute([
            $id_liquidacion,
            $mov['id_movimiento']
        ]);
    }

    // 4. Actualizar movimientos como liquidados
    $stmt_update = $pdo->prepare("UPDATE caja 
    SET liquidado = 'SI',
    fecha_liquidacion = NOW(),
    user_liquida = $id,
    id_liquidacion = $id_liquidacion
    WHERE id_movimiento IN ($in)");
    $stmt_update->execute($ids);

    $pdo->commit();

    header('Content-Type: application/json');
    echo json_encode([
    'success' => true,
    'message' => "✅ Liquidación #$id_liquidacion registrada correctamente.",
    'id_liquidacion' => $id_liquidacion
]);

    sleep(1);
    
    

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo "❌ Error al guardar liquidación: " . $e->getMessage();
}
