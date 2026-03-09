<?php
require '../conexion/conexion.php';

$id = $_POST['id'];
$valor = $_POST['valor'];
$bloque = $_POST['bloque'];

$sql = "UPDATE tarifas 
        SET tar_valor = ?, tar_bloque = ?
        WHERE tar_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$valor,$bloque,$id]);

echo "ok";