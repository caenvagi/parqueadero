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

$response = ['ok' => false, 'error' => ''];

if (!isset($_POST['parqueo_id'])) {
    $response['error'] = 'ID de parqueo no recibido';
    echo json_encode($response);
    exit;
}

try {
    $pdo->beginTransaction();

    $id = $_POST['parqueo_id'];

    // 1️⃣ Obtener los datos del parqueo y su cliente
    $stmt = $pdo->prepare("
        SELECT 
            p.parqueo_id,
            p.placa_cli,
            p.fecha_ini,
            t.tar_valor AS tarifa_hora,
            t.tar_bloque,
            t.tar_categoria,
            c.nombre AS cliente_nombre,
            cat.cat_nombre AS categoria
        FROM parqueo p
        INNER JOIN cliente c ON p.placa_cli = c.placa
        INNER JOIN tarifas t ON c.categoria = t.tar_categoria
        INNER JOIN categorias cat ON c.categoria = cat.cat_id
        WHERE p.parqueo_id = :id
    ");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception('Registro no encontrado');
    }

    // 2️⃣ Calcular el tiempo transcurrido
    $fecha_ini = new DateTime($data['fecha_ini']);
    $fecha_fin = new DateTime('now', new DateTimeZone('America/Bogota'));
    $intervalo = $fecha_ini->diff($fecha_fin);
    $horas = $intervalo->days * 24 + $intervalo->h;
    $minutos = $intervalo->i;
    $segundos = $intervalo->s;

    // 3️⃣ Calcular el total con 15 min de gracia y bloque de 12 horas
    $valor_hora = (float) $data['tarifa_hora'];
    $valor_bloque = (float) $data['tar_bloque'];

    $tiempo_minutos = ($horas * 60) + $minutos;

    if ($tiempo_minutos <= 15) {
        $total = 0; // período de gracia
    } elseif ($tiempo_minutos <= 12 * 60) {
        // Cobro normal por hora
        $horas_cobro = ceil(($tiempo_minutos - 15) / 60);
        $total = $horas_cobro * $valor_hora;
        if ($total > $valor_bloque) $total = $valor_bloque;
    } else {
        // Más de 12 horas: calcular por bloques
        $bloques = floor($tiempo_minutos / (12 * 60));
        $restante = $tiempo_minutos % (12 * 60);
        $total = $bloques * $valor_bloque;

        if ($restante > 15) {
            $horas_extra = ceil(($restante - 15) / 60);
            $total += min($valor_bloque, $horas_extra * $valor_hora);
        }
    }

    // 4️⃣ Actualizar el estado del parqueo
    $stmt = $pdo->prepare("UPDATE parqueo SET estado = 'NO' WHERE parqueo_id = :id");
    $stmt->execute([':id' => $id]);

    // 5️⃣ Insertar en recibo
    $stmt = $pdo->prepare("
        INSERT INTO recibo (ticket, placa, fecha_ini, fecha_fin, tiempo, tarifa_recibo, valor_manual, usuario, cierre)
        VALUES (:pid, :placa, :fini, :ffin, :tiempo, '1', :valor, :usuario, 'NO')
    ");
    $tiempo_txt = sprintf("%02dh %02dm %02ds", $horas, $minutos, $segundos);
    $stmt->execute([
        ':pid' => $id,
        ':placa' => $data['placa_cli'],
        ':fini' => $data['fecha_ini'],
        ':ffin' => $fecha_fin->format('Y-m-d H:i:s'),
        ':tiempo' => $tiempo_txt,
        ':valor' => $total,
        ':usuario' => $_SESSION['id'] ?? 'sistema'
    ]);

    $recibo_id = $pdo->lastInsertId();

    // 6️⃣ Registrar movimiento en caja
    $stmt = $pdo->prepare("
        INSERT INTO caja (recibo_id, fecha_movimiento, movimiento, desc_movimiento, valor_ingreso, user_login, caja_tipo, caja, liquidado )
        VALUES (:recibo, NOW(), '3', :tiempo, :valor, :usuario,'INGRESO', 'Parqueadero', 'NO')
    ");
    $stmt->execute([
        ':recibo' => $recibo_id,
        ':valor' => $total,
        ':tiempo' => 'Tarifa por '.$tiempo_txt,
        ':usuario' => $_SESSION['id'] ?? 'sistema'
    ]);

    $pdo->commit();

    $response['ok'] = true;
    $response['total'] = $total;
    $response['tiempo'] = $tiempo_txt;

} catch (Exception $e) {
    $pdo->rollBack();
    $response['ok'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
