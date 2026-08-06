<?php
session_start();
    require_once "../conexion/conexion.php";

    date_default_timezone_set('America/Bogota');

    // Control de inactividad: cerrar sesión después de 3 minutos (180 segundos)
    $inactive = 3 * 60; // 3 minutos
    // Para producción cambiar a: 20 * 60 (20 minutos)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive) {
        session_unset();
        session_destroy();
        echo "timeout";
        exit();
    }
    // actualizar último tiempo de actividad
    $_SESSION['last_activity'] = time();

    if (!isset($_SESSION['id'])) {
        header("Location: ../index.php");
    }
    $id = $_SESSION['id'];
    $tipo_usuario = $_SESSION['tipo_usuario'];
    
    if ($tipo_usuario == 1) {
        $where = "";
    } else if ($tipo_usuario == 2) {
        $where = "WHERE id=$id";
    }


$recibo = $_POST['recibo'] ?? '';

$sql = "SELECT COUNT(*) FROM recibo WHERE recibo_man = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$recibo]);

if ($stmt->fetchColumn() > 0) {
    echo "existe";
} else {
    echo "ok";
}