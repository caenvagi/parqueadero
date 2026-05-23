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
    // 1) Obtener datos de la reserva para recibo y para el cierre si se cubre el monto total
    $stmtReserva = $pdo->prepare("SELECT habitacion_id, valor_total, fecha_ingreso, fecha_salida FROM aloj_reservas WHERE id = ?");
    $stmtReserva->execute([$reserva_id]);
    $reserva = $stmtReserva->fetch();

    if (!$reserva) {
        throw new Exception("Reserva no encontrada.");
    }

    $stmt = $pdo->prepare(
        "INSERT INTO aloj_pagos 
        (reserva_id, fecha_pago, monto, metodo_pago, tipo_pago, observaciones, usuario_id, created_at)
        VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $reserva_id,
        $monto,
        $metodo_pago,
        $tipo_pago,
        $observaciones,
        $usuario_id,
        $created_at
    ]);

    // 2) Registrar en recibo
    $stmtRecibo = $pdo->prepare(
        "INSERT INTO recibo (
            recibo_man,
            fecha_recibo,
            ticket,
            placa,
            fecha_ini,
            fecha_fin,
            tiempo,
            tarifa_recibo,
            plan,
            valor_manual,
            valor_pagado,
            usuario,
            cierre,
            periodo
            
        ) VALUES (
            ? ,
            NOW(),
            0,
            'ZZZ888' ,
            ?,
            ?,
            TIMESTAMPDIFF(DAY, ?, ?),
            6,
            8,
            0,
            ?,
            ?,
            'NO',
            1
           
        )"
    );

    //$descripcionRecibo = "Alojamiento reserva #$reserva_id";

    $stmtRecibo->execute([
        'ALOJ-'.$reserva_id,
        $reserva['fecha_ingreso'],
        $reserva['fecha_salida'],
        $reserva['fecha_ingreso'],
        $reserva['fecha_salida'],
        $monto,
        $usuario_id
    ]);
        
    sleep(1); // Asegurar que el ID se genere antes de continuar
    $recibo_id = $pdo->lastInsertId();

    //Registrar movimiento en caja
    $desc = "Pago de reserva #$reserva_id ($tipo_pago)";
    $stmtCaja = $pdo->prepare("INSERT INTO caja (
        fecha_movimiento,
        movimiento,
        desc_movimiento,
        valor_ingreso,
        valor_egreso,
        user_login,
        liquidado,
        caja_tipo,
        caja,
        recibo_id
      ) VALUES (
        NOW(), 3, ?, ?, 0, ?, 'NO', 'INGRESO','Alojamiento', ?
      )");

    $stmtCaja->execute([
        $desc,
        $monto,
        $usuario_id,
        $recibo_id
    ]);

    // 3) Sumar todos los pagos registrados para esta reserva
    $habitacion_id = $reserva['habitacion_id'];
    $valor_total   = floatval($reserva['valor_total']);

    $stmt3 = $pdo->prepare("SELECT SUM(monto) AS total_pagado FROM aloj_pagos WHERE reserva_id = ?");
    $stmt3->execute([$reserva_id]);
    $pagos = $stmt3->fetch();
    $total_pagado = floatval($pagos['total_pagado']);

    if ($total_pagado >= $valor_total) {
        $upd1 = $pdo->prepare("UPDATE aloj_reservas SET estado = 'confirmada' WHERE id = ?");
        $upd1->execute([$reserva_id]);

        $upd2 = $pdo->prepare("UPDATE aloj_habitaciones SET estado = 'ocupada' WHERE id = ?");
        $upd2->execute([$habitacion_id]);
    }

    echo "<script>
            alert('✅ Pago registrado correctamente.');
            window.location.href = 'aloj_pagos.php?reserva_id=$reserva_id';
           
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

//  window.location.href = '../modulos/imprimir_ticket_php/ticket.php?reserva_id=$reserva_id';
?>
