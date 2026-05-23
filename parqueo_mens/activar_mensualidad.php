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

$placa = $_POST['placa'] ?? '';
$caseta = $_POST['caseta'] ?? '';
$valor = $_POST['valor'] ?? '';
$plan = $_POST['plan_tarifa'] ?? '';
$usuario = $_SESSION['id'] ?? 'Desconocido';

if (!$placa) {
    die("Placa no válida");
}



try {

    // 🔹 1. Activar cliente
    $sqlCliente = "UPDATE cliente 
                   SET mensualidad = 'SI', activo = 'SI', fecha_creacion = CURDATE()
                   WHERE placa = ?";
    $stmt = $pdo->prepare($sqlCliente);
    $stmt->execute([$placa]);

    // 🔹 1. Validar si ya tiene mensualidad activa
    $sql = "SELECT COUNT(*) FROM mensualidad_historial 
            WHERE placa = ? AND fecha_retiro IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa]);

    if ($stmt->fetchColumn() > 0) {
        die("Este vehículo ya tiene una mensualidad activa");
    }

    // 🔹 2. Obtener datos del cliente/vehículo
    $sql = "SELECT caseta, valor, cli_tar_tiempo AS plan 
            FROM cliente 
            WHERE placa = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        die("Cliente no encontrado");
    }

    $caseta = $cliente['caseta'];
    $valor  = $cliente['valor'];
    $plan   = $cliente['plan'];

    // 🔹 3. Insertar como REINGRESO
    $sql = "INSERT INTO mensualidad_historial 
            (placa, fecha_ingreso, caseta, valor, plan, usuario, observacion)
            VALUES (?, CURDATE(), ?, ?, ?, ?, 'Reingreso de mensualidad')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $placa,
        $caseta,
        $valor,
        $plan,
        $usuario
    ]);

    // 🔹 4. Insertar PAGO nuevo con inicio de creacion
    $sql2 = "INSERT INTO pagos
            (fecha,estado,placa,valor,plan,fecha_inicio,fecha_fin,usuario,caseta,observacion)
            VALUES (now(),'PENDIENTE',?,?,?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), ?, ?, 'Pago generado por activación de mensualidad')";

    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([
        $placa,
        $valor,
        $plan,
        $usuario,
        $caseta
    ]);

  
        
    

    echo "✅ Mensualidad activada correctamente";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}