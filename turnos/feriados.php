<?php
session_start();
require_once "../conexion/conexion.php";

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
$sql = "SELECT fecha FROM feriados";
$stmt = $pdo->query($sql);
$feriados = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $feriados[] = [
        'start' => $row['fecha'],
        'display' => 'background',
        'color' => '##F39495'  // Color de fondo para el día festivo
    ];
}

echo json_encode($feriados);
?>