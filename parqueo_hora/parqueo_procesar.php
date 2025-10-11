<?php
session_start();
    require_once "../conexion/conexion.php";


try {
    $placa = strtoupper(trim($_POST['placa']));
    $nombre = trim($_POST['nombre']);
    $vehiculo = trim($_POST['vehiculo']);
    $categoria = (int)$_POST['categoria'];
    $caseta = (int)$_POST['caseta'];
    $usuario = (int)$_POST['usuario'];

    if(!$placa || !$nombre || !$vehiculo || !$categoria || !$caseta || !$usuario){
        throw new Exception('Datos incompletos');
    }

    // Verificar si el cliente ya existe
    $stmt = $pdo->prepare("SELECT placa FROM cliente WHERE placa = ?");
    $stmt->execute([$placa]);
    if($stmt->rowCount() == 0) {
        $sql = "INSERT INTO cliente (fecha_creacion, placa, nombre, cedula, celular, vehiculo, categoria, valor, plan_tarifa, caseta, mensualidad, activo, user)
                VALUES (NOW(), ?, ?, 0, 0, ?, ?, 0, 1, ?, 'NO', 'SI', ?)";
        $pdo->prepare($sql)->execute([$placa, $nombre, $vehiculo, $categoria, $caseta, $usuario]);
    }

    // Insertar registro en parqueo
    $sql = "INSERT INTO parqueo (placa_cli, fecha_ini, tarifa, caseta, usuario, estado)
            VALUES (?, NOW(), 1, ?, ?, 'SI')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa, $caseta, $usuario]);
    

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
