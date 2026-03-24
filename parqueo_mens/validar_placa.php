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

if ($placa != '') {

    // 🔴 VALIDAR SI ESTÁ EN PARQUEO ACTIVO
    $sqlParqueo = "SELECT parqueo_id FROM parqueo 
                   WHERE placa_cli = ? AND estado = 'SI' 
                   LIMIT 1";

    $stmtParqueo = $pdo->prepare($sqlParqueo);
    $stmtParqueo->execute([$placa]);

    if ($stmtParqueo->fetch()) {
        echo "parqueo_activo";
        exit;
    }
    // 🔵 2. VALIDAR CLIENTE INACTIVO (NO mensualidad y NO activo)
    $sqlInactivo = "SELECT placa FROM cliente 
                    WHERE placa = ? 
                    AND mensualidad = 'NO' 
                    AND activo = 'NO'
                    LIMIT 1";

    $stmtInactivo = $pdo->prepare($sqlInactivo);
    $stmtInactivo->execute([$placa]);

    if ($stmtInactivo->fetch()) {
        echo "cliente_inactivo";
        exit;
    }

    // 🟡 VALIDAR SI EXISTE EN MENSUALIDAD
    $sqlCliente = "SELECT placa FROM cliente WHERE placa = ? LIMIT 1";
    $stmtCliente = $pdo->prepare($sqlCliente);
    $stmtCliente->execute([$placa]);

    if ($stmtCliente->fetch()) {
        echo "existe";
    } else {
        echo "no_existe";
    }
}
?>