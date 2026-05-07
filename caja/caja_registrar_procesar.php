<?php
require_once "../conexion/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha_movimiento'];
    $movimiento = $_POST['movimiento'];
    $desc = $_POST['desc_movimiento'];
    $tipo = $_POST['tipo'];
    $valor = (int) $_POST['valor'];
    $caja_tipo = $_POST['tipo'];
    $user_login = $_POST['user_login'];
    $liquidado = $_POST['liquidado'];
    $caja = $_POST['caja_tipo'];

    $valor_ingreso = ($tipo === 'INGRESO') ? $valor : 0;
    $valor_egreso = ($tipo === 'EGRESO') ? $valor : 0;

    try {
        $sql = "INSERT INTO caja (fecha_movimiento, movimiento, desc_movimiento, valor_ingreso, valor_egreso, user_login, liquidado, caja_tipo, caja)
                VALUES (:fecha, :movimiento, :descripcion, :valor_ingreso, :valor_egreso, :user_login, :liquidado, :caja_tipo, :caja)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fecha' => $fecha,
            ':movimiento' => $movimiento,
            ':descripcion' => $desc,
            ':valor_ingreso' => $valor_ingreso,
            ':valor_egreso' => $valor_egreso,
            ':user_login' => $user_login,
            ':liquidado' => $liquidado,
            ':caja_tipo' => $caja_tipo,
            ':caja' => $caja        ]);

        echo "OK";
        exit;

    } catch (PDOException $e) {
    die("Error al registrar el movimiento: " . $e->getMessage());
}
} else {
    die("Método no permitido");
}
