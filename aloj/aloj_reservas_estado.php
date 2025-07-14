<?php
require_once "../conexion/conexion.php";
session_start();
if (!isset($_SESSION['id'])) die("No autorizado.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reserva_id = $_POST['reserva_id'];
    $nuevo_estado = $_POST['estado'];

    // Obtener el id de la habitación asociada a la reserva
    $sql = "SELECT habitacion_id FROM aloj_reservas WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reserva_id]);
    $habitacion = $stmt->fetchColumn();

    // Actualizar estado de la reserva
    $sql = "UPDATE aloj_reservas SET estado = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nuevo_estado, $reserva_id]);

    // Si se cambia a cancelada o finalizada, poner la habitación en mantenimiento
    if (in_array($nuevo_estado, ['cancelada', 'finalizada', 'otros'])) {
        $sql = "UPDATE aloj_habitaciones SET estado = 'mantenimiento' WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$habitacion]);
    }

    header("Location: aloj_reservas_listado.php");
    exit;
}
?>
