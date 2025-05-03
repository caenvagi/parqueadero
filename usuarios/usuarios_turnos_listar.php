<?php
require_once "../conexion/conexion.php";
$sql = "SELECT t.*, u.nombre 
        FROM usuarios_turnos t 
        JOIN usuarios u ON t.usuario_id = u.id 
        WHERE pagado = 0
        ORDER BY t.fecha_inicio, t.hora_inicio";
$stmt = $pdo->query($sql);

echo "<ul>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<li><strong>{$row['nombre']}</strong>: 
        del {$row['fecha_inicio']} {$row['hora_inicio']} 
        al {$row['fecha_fin']} {$row['hora_fin']} – $ {$row['valor']}</li>";
}
echo "</ul>";