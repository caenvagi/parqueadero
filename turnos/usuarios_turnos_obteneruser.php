<?php
require_once "../conexion/conexion.php";
$stmt = $pdo->query("SELECT id, nombre FROM usuarios WHERE activo = 1");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
