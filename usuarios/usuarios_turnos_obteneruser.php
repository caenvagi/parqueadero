<?php
require_once "../conexion/conexion.php";
$stmt = $pdo->query("SELECT id, nombre FROM usuarios");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
