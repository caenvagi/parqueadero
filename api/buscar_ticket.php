<?php
header('Content-Type: application/json');
require_once "../conexion/conexion.php";
date_default_timezone_set('America/Bogota');

$codigo = $_POST['codigo'] ?? null;

if (!$codigo) {
    echo json_encode([
        "success" => false,
        "message" => "Código vacío"
    ]);
    exit;
}

try {

    $sql = "SELECT 
                p.parqueo_id,
                p.placa_cli,
                p.fecha_ini,
                t.tar_valor as tarifa_hora,
                t.tar_bloque
            FROM parqueo p
            INNER JOIN tarifas t ON p.tarifa = t.tar_categoria
            WHERE p.parqueo_id = :codigo 
            AND p.estado = 'SI'
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['codigo' => $codigo]);

    $data = $stmt->fetch();

    if ($data) {

        // 👉 calcular tiempo transcurrido
        $fecha_ini = new DateTime($data['fecha_ini']);
        $fecha_actual = new DateTime();

        $diff = $fecha_ini->diff($fecha_actual);

        // 🔥 FORMATEAR TIEMPO (LO DEJAMOS IGUAL)
        $tiempo = "";

        if ($diff->y > 0) $tiempo .= $diff->y . "a ";
        if ($diff->m > 0) $tiempo .= $diff->m . "m ";
        if ($diff->d > 0) $tiempo .= $diff->d . "d ";
        if ($diff->h > 0) $tiempo .= $diff->h . "h ";
        if ($diff->i > 0) $tiempo .= $diff->i . "min ";

        if ($tiempo == "") $tiempo = "0 min";

        // =========================
        // 💰 CALCULO DEL VALOR
        // =========================

        $dias = $diff->days;
        $horas = $diff->h;
        $minutos = $diff->i;

        // Total minutos
        $tiempo_minutos = ($dias * 24 * 60) + ($horas * 60) + $minutos;

        $valor_hora = (float) $data['tarifa_hora'];
        $valor_bloque = (float) $data['tar_bloque'];

        if ($tiempo_minutos <= 15) {
            $total = 0;
        } else {

            // Horas cobrables
            $horas_totales = ceil(($tiempo_minutos - 15) / 60);

            if ($horas_totales < 1) {
                $horas_totales = 1;
            }

            // 🔁 BLOQUES DE 12 HORAS
            $bloques = floor($horas_totales / 12);
            $horas_restantes = $horas_totales % 12;

            $total = $bloques * $valor_bloque;

            if ($horas_restantes > 0) {

                $subtotal = $horas_restantes * $valor_hora;

                if ($subtotal > $valor_bloque) {
                    $subtotal = $valor_bloque;
                }

                $total += $subtotal;
            }
        }

        echo json_encode([
            "success" => true,
            "ticket" => $data['parqueo_id'],
            "placa" => $data['placa_cli'],
            "fecha_ini" => $data['fecha_ini'],
            "tiempo" => $tiempo,
            "valor" => $total
        ]);

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Ticket no existe o ya pagado"
            
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error servidor",
        "error" => $e->getMessage()
    ]);
}