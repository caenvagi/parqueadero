<?php
session_start();
require_once "../conexion/conexion.php";

if (isset($_POST['turnos']) && is_array($_POST['turnos'])) {
    $turnos = array_map('intval', $_POST['turnos']);

    // Convertir a placeholders para la consulta
    $placeholders = implode(',', array_fill(0, count($turnos), '?'));

    // Obtener info de los turnos seleccionados
    $stmt = $pdo->prepare("SELECT usuario_id, SUM(valor) AS total FROM usuarios_turnos WHERE id_turno IN ($placeholders) GROUP BY usuario_id");
    $stmt->execute($turnos);
    $datos = $stmt->fetchAll();

    foreach ($datos as $row) {
        $usuario_id = $row['usuario_id'];
        $total = $row['total'];
        $elaborado = $_SESSION['usuario'] ?? 'sistema';

        // Insertar recibo
        $insert = $pdo->prepare("INSERT INTO usuarios_recibos (recibo_fecha, recibo_usuario, recibo_concepto, recibo_valor, recibo_elaborado)
                                 VALUES (NOW(), ?, 'Pago de turnos', ?, ?)");
        $insert->execute([$usuario_id, $total, $elaborado]);
    }

    // Marcar turnos como pagados
    $update = $pdo->prepare("UPDATE usuarios_turnos SET pagado = 1 WHERE id_turno IN ($placeholders)");
    $update->execute($turnos);

    echo "✅ Recibo(s) generado(s) y turnos marcados como pagados.";
} else {
    echo "⚠️ No se seleccionaron turnos.";
}
