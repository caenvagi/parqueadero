<?php
require '../conexion/conexion.php';
$sql = "SELECT * FROM usuarios ";
$stmt = $pdo->prepare($sql);
//$stmt->execute(['email' => $email]);
$result = $stmt->fetch();
echo $result;