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

$sql = "SELECT 
            fecha,
            fecha_inicio,
            fecha_fin,
            valor,
            estado
        FROM pagos
        WHERE placa = ?
        ORDER BY fecha DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$placa]);

$data = [];

while ($row = $stmt->fetch()) {

    $row['valor'] = "$" . number_format($row['valor']);

<<<<<<< HEAD
    if ($row['estado'] == 'PENDIENTE') {
=======
    if ($row['estado'] == 'pendiente') {
>>>>>>> 0c01399d41973a4d211c2dd4c4f6aab373b4577b
        $row['estado'] = '<span class="badge bg-warning">Pendiente</span>';
    } else {
        $row['estado'] = '<span class="badge bg-success">Pagado</span>';
    }

    $data[] = $row;
}

echo json_encode(["data" => $data]);