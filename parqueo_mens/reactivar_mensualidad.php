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

$placa = $_POST['placa'];
$plan = $_POST['plan'];
$valor = $_POST['valor'];
$caseta = $_POST['caseta'];
$usuario = 1; // ajusta sesión

try {

    $pdo->beginTransaction();

    // 1. INSERTAR NUEVO HISTORIAL
    $sqlHist = "INSERT INTO mensualidad_historial 
        (placa, fecha_ingreso, fecha_retiro, caseta, valor, plan, usuario, observacion)
        VALUES (:placa, CURDATE(), NULL, :caseta, :valor, :plan, :usuario, 'Reactivación desde historial')";

    $stmt = $pdo->prepare($sqlHist);
    $stmt->execute([
        ':placa' => $placa,
        ':caseta' => $caseta,
        ':valor' => $valor,
        ':plan' => $plan,
        ':usuario' => $usuario
    ]);

    // 2. CREAR PAGO PENDIENTE
    $sqlPago = "INSERT INTO pagos 
        (fecha, placa, valor, plan, fecha_inicio, fecha_fin, estado, usuario, caseta, observacion)
        VALUES (CURDATE(), :placa, :valor, :plan, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'PENDIENTE', :usuario, :caseta, 'Pago mensualidad')";

    $stmt = $pdo->prepare($sqlPago);
    $stmt->execute([
        ':placa' => $placa,
        ':valor' => $valor,
        ':plan' => $plan,
        ':usuario' => $usuario,
        ':caseta' => $caseta
    ]);

    // 3. ACTUALIZAR CLIENTE
    $sqlCliente = "UPDATE cliente 
        SET mensualidad = 'SI', activo = 'SI', cli_tar_tiempo = :plan, valor = :valor, caseta = :caseta
        WHERE placa = :placa";

    $stmt = $pdo->prepare($sqlCliente);
    $stmt->execute([
        ':placa' => $placa,
        ':plan' => $plan,
        ':valor' => $valor,
        ':caseta' => $caseta
    ]);

    $pdo->commit();

    echo "✅ Mensualidad reactivada correctamente";

} catch (Exception $e) {

    $pdo->rollBack();
    echo "❌ Error: " . $e->getMessage();
}