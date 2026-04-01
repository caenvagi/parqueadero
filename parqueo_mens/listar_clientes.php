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

try {

    $sql = "SELECT 
                placa,
                nombre,
                vehiculo,
                fecha_creacion AS fecha_inicio,
                fecha_retiro AS fecha_fin,
                valor
            FROM cliente
            WHERE mensualidad = 'SI'
            AND activo = 'SI'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($datos);

} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}