<?php
require '../conexion/conexion.php';
header('Content-Type: application/json');

date_default_timezone_set('America/Bogota');

try {
    $id = (int)$_POST['id'];
    if(!$id) throw new Exception('ID no recibido');

    // 1️⃣ Obtener datos del parqueo y categoría
    $sql = "SELECT p.fecha_ini, c.categoria, c.placa, cat.cat_nombre
            FROM parqueo p
            JOIN cliente c ON p.placa_cli = c.placa
            JOIN categorias cat ON c.categoria = cat.cat_id
            WHERE p.parqueo_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    if(!$data) throw new Exception('Registro no encontrado');

    $fecha_ini = new DateTime($data['fecha_ini']);
    $fecha_fin = new DateTime(); // salida actual
    $diff = $fecha_ini->diff($fecha_fin);

    // 2️⃣ Calcular tiempo total en horas
    $horas = $diff->days * 24 + $diff->h + ($diff->i / 60);
    $horas_redondeadas = ceil($horas); // redondea hacia arriba

    // 3️⃣ Obtener tarifa por categoría (puedes cambiar por tu propia lógica)
    $sqlTarifa = "SELECT tar_tiempo, `tar_T.desc` AS descripcion FROM tar_tiempo WHERE tar_id_nombre = ?";
    $tarifa = $pdo->prepare($sqlTarifa);
    $tarifa->execute([$data['categoria']]);
    $tar = $tarifa->fetch();

    // Si no hay tarifa en la tabla, usar un valor fijo por defecto (ejemplo)
    $valor_hora = 2000; // valor por hora
    if ($tar && is_numeric($tar['tar_tiempo'])) {
        $valor_hora = (int)$tar['tar_tiempo'];
    }

    // 4️⃣ Calcular valor total
    $valor_total = $valor_hora * $horas_redondeadas;

    // 5️⃣ Actualizar parqueo
    $sql = "UPDATE parqueo 
            SET estado='F', tarifa=?, usuario=1 
            WHERE parqueo_id=?";
    $pdo->prepare($sql)->execute([$valor_total, $id]);

    echo json_encode([
        'ok' => true,
        'tiempo' => "{$horas_redondeadas} hora(s)",
        'valor' => number_format($valor_total, 0, ',', '.')
    ]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
