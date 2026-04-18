<?php
header('Content-Type: application/json');

require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

try {

    $sql = "SELECT 
                p.parqueo_id, 
                p.placa_cli, 
                p.fecha_ini,
                t.tar_valor AS tarifa_hora,
                t.tar_bloque
            FROM parqueo p
            INNER JOIN cliente c ON p.placa_cli = c.placa
            INNER JOIN tarifas t ON c.categoria = t.tar_categoria
            WHERE p.estado = 'SI' 
            ORDER BY p.fecha_ini DESC
            
            ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $datos = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $fecha_ini = new DateTime($row['fecha_ini']);
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

        $valor_hora = (float) $row['tarifa_hora'];
        $valor_bloque = (float) $row['tar_bloque'];

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

        // =========================
        // 📦 RESPUESTA
        // =========================

        $datos[] = [
            "parqueo_id" => $row['parqueo_id'],
            "placa" => $row['placa_cli'],
            "fecha_ini" => $row['fecha_ini'],
            "tiempo" => $tiempo,
            "valor" => $total
        ];
    }

    echo json_encode([
        "success" => true,
        "data" => $datos
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}