<?php
require_once "../conexion/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha_movimiento'];
    $desc = $_POST['desc_movimiento'];
    $tipo = $_POST['tipo'];
    $valor = (int) $_POST['valor'];
    $caja_tipo = $_POST['caja_tipo'];
    $user_login = $_POST['user_login'];
    $liquidado = $_POST['liquidado'];

    $valor_ingreso = ($tipo === 'ingreso') ? $valor : 0;
    $valor_egreso = ($tipo === 'egreso') ? $valor : 0;

    try {
        $sql = "INSERT INTO caja (fecha_movimiento, desc_movimiento, valor_ingreso, valor_egreso, user_login, liquidado, caja_tipo)
                VALUES (:fecha, :descripcion, :valor_ingreso, :valor_egreso, :user_login, :liquidado, :caja_tipo)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fecha' => $fecha,
            ':descripcion' => $desc,
            ':valor_ingreso' => $valor_ingreso,
            ':valor_egreso' => $valor_egreso,
            ':user_login' => $user_login,
            ':liquidado' => $liquidado,
            ':caja_tipo' => $caja_tipo
        ]);

        header("Location: caja_listado.php?mensaje=ok");
        exit;

    } catch (PDOException $e) {
        error_log("Error al insertar en caja: " . $e->getMessage());
        die("Error al registrar el movimiento.");
    }
} else {
    die("Método no permitido");
}
