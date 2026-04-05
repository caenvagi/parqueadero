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

    $placa = $_POST['placa'];

    $sql = "SELECT * FROM pagos WHERE placa = :placa and estado = 'PENDIENTE'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['placa' => $placa]);

    if ($stmt->rowCount() > 0) {
        echo '<option value="">Seleccione un pago</option>';

        foreach ($stmt as $row) {
            echo "<option value='{$row['id']}'>
                    {$row['fecha_inicio']} - {$row['fecha_fin']}
                  </option>";
        }
    } else {
        echo '<option value="">No hay pagos registrados</option>';
    }
}

