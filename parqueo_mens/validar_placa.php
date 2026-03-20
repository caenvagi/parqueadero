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

if (isset($_POST['placa'])) {

    // Limpiar y formatear la placa
    $placa = strtoupper(trim($_POST['placa']));

    if ($placa == "") {
        echo "vacio";
        exit;
    }

    // Consultar si existe la placa
    $stmt = $pdo->prepare("SELECT placa FROM cliente WHERE placa = ?");
    $stmt->execute([$placa]);

    if ($stmt->rowCount() > 0) {
        // 🔴 La placa YA existe
        echo "existe";
    } else {
        // 🟢 La placa NO existe
        echo "no_existe";
    }
}
?>