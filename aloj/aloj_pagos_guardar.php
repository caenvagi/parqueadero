<?php
session_start();
require_once "../conexion/conexion.php";

if (!isset($_SESSION['id'])) {
    die("No autorizado.");
}

$usuario_id = $_SESSION['id'];
$created_at = date("Y-m-d H:i:s");

try {
    // === VALIDAR ===
    if (!isset($_POST['reserva_id']) || !is_numeric($_POST['reserva_id'])) {
        throw new Exception("Reserva inválida.");
    }

    $reserva_id = intval($_POST['reserva_id']);
    $monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $tipo_pago = $_POST['tipo_pago'] ?? '';
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($monto <= 0) {
        throw new Exception("El monto debe ser mayor a 0.");
    }

    if (!in_array($metodo_pago, ['efectivo', 'tarjeta', 'transferencia'])) {
        throw new Exception("Método de pago inválido.");
    }

    if (!in_array($tipo_pago, ['abono', 'saldo'])) {
        throw new Exception("Tipo de pago inválido.");
    }

    // === GUARDAR ===
    $stmt = $pdo->prepare("
        INSERT INTO aloj_pagos 
        (reserva_id, fecha_pago, monto, metodo_pago, tipo_pago, observaciones, usuario_id, created_at)
        VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)
    ");

    // Registrar movimiento en caja
$desc = "Pago de reserva #$reserva_id ($tipo_pago)";
$stmtCaja = $pdo->prepare("INSERT INTO caja (
    fecha_movimiento,
    movimiento,
    desc_movimiento,
    valor_ingreso,
    valor_egreso,
    user_login,
    liquidado,
    caja_tipo
  ) VALUES (
    NOW(), 4, ?, ?, 0, ?, 'NO', 'INGRESO'
  )");

$stmtCaja->execute([
               // movimiento
  $desc,                   // descripción
  $monto,                  // valor_ingreso
  $usuario_id              // user_login
]);


    $stmt->execute([
        $reserva_id,
        $monto,
        $metodo_pago,
        $tipo_pago,
        $observaciones,
        $usuario_id,
        $created_at
    ]);

    // 1) Obtener datos de la reserva, especialmente el habitacion_id y valor_total
$stmt2 = $pdo->prepare("SELECT habitacion_id, valor_total FROM aloj_reservas WHERE id = ?");
$stmt2->execute([$reserva_id]);
$res = $stmt2->fetch();

if ($res) {
    $habitacion_id = $res['habitacion_id'];
    $valor_total   = floatval($res['valor_total']);

    // 2) Sumar todos los pagos registrados para esta reserva
    $stmt3 = $pdo->prepare("SELECT SUM(monto) AS total_pagado FROM aloj_pagos WHERE reserva_id = ?");
    $stmt3->execute([$reserva_id]);
    $pagos = $stmt3->fetch();
    $total_pagado = floatval($pagos['total_pagado']);

    // 3) Si ya cubre el valor total, actualizamos reserva y habitación
    if ($total_pagado >= $valor_total) {
        // a) Marcamos la reserva como cancelada
        $upd1 = $pdo->prepare("UPDATE aloj_reservas SET estado = 'confirmada' WHERE id = ?");
        $upd1->execute([$reserva_id]);

        // b) Marcamos la habitación como ocupada
        $upd2 = $pdo->prepare("UPDATE aloj_habitaciones SET estado = 'ocupada' WHERE id = ?");
        $upd2->execute([$habitacion_id]);
    }
}

    echo "<script>
            alert('✅ Pago registrado correctamente.');
            window.location.href = 'aloj_pagos.php?reserva_id=$reserva_id';
            window.location.href = 'aloj_ticket_pago.php?reserva_id=$reserva_id';
            window.location.href = '../modulos/imprimir_ticket_php/ticket.php?reserva_id=$reserva_id';
        </script>";

} catch (Exception $e) {
    echo "<script>
        alert('❌ Error: " . $e->getMessage() . "');
        window.history.back();
    </script>";
} catch (PDOException $e) {
    echo "<script>
        alert('❌ Error de base de datos: " . $e->getMessage() . "');
        window.history.back();
    </script>";
}
?>
