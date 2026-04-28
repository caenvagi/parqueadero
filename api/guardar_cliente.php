<?php

header('Content-Type: application/json');

require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

try {

    // 📌 Recibir datos desde Android (POST)
    $placa = ($_POST['placa'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $vehiculo = trim($_POST['vehiculo'] ?? '');
    $categoria = (int)($_POST['categoria'] ?? 0);
    $caseta = (int)($_POST['caseta'] ?? 0);

    if (!$placa || !$nombre || !$celular || !$vehiculo || !$categoria || !$caseta ) {
        throw new Exception('Datos incompletos');
    }

    // 1️⃣ Validar si ya está en parqueo
    $sql_check = "SELECT 1 FROM parqueo WHERE placa_cli = :placa AND estado = 'SI'";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute(['placa' => $placa]);

    if ($stmt_check->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => "⚠️ El vehículo con placa $placa ya está en el parqueadero."
        ]);
        exit;
    }

    // 2️⃣ Validar mensualidad activa
    $sql_mensual = "SELECT 1 FROM cliente 
                    WHERE placa = :placa 
                    AND activo = 'SI' 
                    AND mensualidad = 'SI'";

    $stmt_mensual = $pdo->prepare($sql_mensual);
    $stmt_mensual->execute(['placa' => $placa]);

    if ($stmt_mensual->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => "⚠️ Vehiculo $placa tiene mensualidad activa"
        ]);
        exit;
    }

    // 3️⃣ Verificar si cliente existe
    $stmt = $pdo->prepare("SELECT placa FROM cliente WHERE placa = ?");
    $stmt->execute([$placa]);

    if ($stmt->rowCount() == 0) {
        $sql = "INSERT INTO cliente 
                (fecha_creacion, placa, nombre, cedula, celular, vehiculo, categoria, valor, cli_tar_tiempo, caseta, mensualidad, activo)
                VALUES (NOW(), ?, ?, 0, ?, ?, ?, 0, 1, ?, 'NO', 'NO')";

        $pdo->prepare($sql)->execute([
            $placa, $nombre, $celular, $vehiculo, $categoria, $caseta
        ]);
    }

    // 4️⃣ Insertar parqueo
    $sql = "INSERT INTO parqueo (placa_cli, fecha_ini, tarifa, caseta, usuario, estado)
            VALUES (?, NOW(), ?, ?, 2, 'SI')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa, $categoria, $caseta]);

     $ticket =  $pdo->lastInsertId();

    // 5️⃣ Actualizar caseta
    $update = $pdo->prepare("UPDATE casetas SET casetas_estado = 'Ocupado' WHERE caseta_id = ?");
    $update->execute([$caseta]);   
   

    echo json_encode([
        'success' => true,
        'message' => "✅ Vehículo $placa ingresado correctamente ...🖨️imprimiendo ticket $ticket",
        'ticket' => (int)$ticket,    
        'recibo' => 0
    ]);

} 
catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}