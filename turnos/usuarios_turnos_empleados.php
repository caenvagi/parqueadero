<?php
require_once "../conexion/conexion.php";
$usuario_id = $_GET['usuario_id'] ?? 0;

$sql = "SELECT fecha_inicio, hora_inicio, fecha_fin, hora_fin, valor 
        FROM usuarios_turnos 
        WHERE usuario_id = ? and pagado = 0
        ORDER BY fecha_inicio, hora_inicio";

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);

echo "<ul>";
foreach ($stmt as $row) {
    echo "<li>{$row['fecha_inicio']} {$row['hora_inicio']} → {$row['fecha_fin']} {$row['hora_fin']} – $ {$row['valor']}</li>";
}
echo "</ul>";
