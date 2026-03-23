<?php
session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['id'])) {
    die("Sesión no válida");
}

$placa = $_POST['placa'] ?? '';

if (!$placa) {
    die("Placa no válida");
}

try {

    // 🔹 1. Desactivar cliente
    $sqlCliente = "UPDATE cliente 
                   SET mensualidad = 'NO', activo = 'NO'
                   WHERE placa = ?";
    $stmt = $pdo->prepare($sqlCliente);
    $stmt->execute([$placa]);


    // 🔹 2. Actualizar fecha_retiro en el último historial
    $sqlHist = "UPDATE mensualidad_historial 
                SET fecha_retiro = CURDATE()
                WHERE placa = ?
                ORDER BY id DESC
                LIMIT 1";

    $stmt2 = $pdo->prepare($sqlHist);
    $stmt2->execute([$placa]);


    echo "<div class='alert alert-success'>
            Mensualidad desactivada correctamente
          </div>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            Error: " . $e->getMessage() . "
          </div>";
}