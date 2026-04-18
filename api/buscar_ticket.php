<?php
header('Content-Type: application/json');
require_once "../conexion/conexion.php";

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
                t.tar_valor
            FROM parqueo p
            LEFT JOIN tarifas t ON p.tarifa = t.tar_id
            WHERE p.parqueo_id = :codigo 
            AND p.estado = 'SI'
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['codigo' => $codigo]);

    $data = $stmt->fetch();

    if ($data) {

        // 👉 calcular tiempo transcurrido
        $fecha_ini = new DateTime($data['fecha_ini']);
        $ahora = new DateTime();

        $diff = $fecha_ini->diff($ahora);

        $horas = ($diff->days * 24) + $diff->h;
        $minutos = $diff->i;

        // 👉 calcular valor (ejemplo simple)
        $valor_hora = $data['tar_valor'];
        $valor_total = ($horas * $valor_hora);

        echo json_encode([
            "success" => true,
            "ticket" => $data['parqueo_id'],
            "placa" => $data['placa_cli'],
            "tiempo" => "{$horas}h {$minutos}m",
            "valor" => $valor_total
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