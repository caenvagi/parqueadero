<?php
require_once "../conexion/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha_movimiento'];
    $movimiento = $_POST['movimiento'];
    $desc = $_POST['desc_movimiento'];
    $tipo = $_POST['tipo'];
    $valor = (int) $_POST['valor'];
    $caja_tipo = $_POST['caja_tipo'];
    $user_login = $_POST['user_login'];
    $liquidado = $_POST['liquidado'];
    $caja = $_POST['caja_tipo'];
    $recibo = trim($_POST['recibo'] ?? '');

    $valor_ingreso = ($tipo === 'INGRESO') ? $valor : 0;
    $valor_egreso = ($tipo === 'EGRESO') ? $valor : 0;

    $conceptoStmt = $pdo->prepare("SELECT nombre_concepto FROM caja_conceptos WHERE id_concepto = ?");
    $conceptoStmt->execute([$movimiento]);
    $conceptoNombre = strtolower(trim($conceptoStmt->fetchColumn() ?: ''));
    $esConsignacion = $tipo === 'EGRESO' && strpos($conceptoNombre, 'consign') !== false;

    if ($esConsignacion && $recibo === '') {
        die('El número de recibo es obligatorio para consignaciones.');
    }

    $reciboValor = $esConsignacion ? $recibo : null;

    try {
        $checkColumn = $pdo->query("SHOW COLUMNS FROM caja LIKE 'recibo_id'");
        if (!$checkColumn->fetch()) {
            $pdo->exec("ALTER TABLE caja ADD COLUMN recibo_id VARCHAR(100) NULL");
        }

        $sql = "INSERT INTO caja (fecha_movimiento, movimiento, desc_movimiento, valor_ingreso, valor_egreso, user_login, liquidado, caja_tipo, caja, recibo_id)
                VALUES (:fecha, :movimiento, :descripcion, :valor_ingreso, :valor_egreso, :user_login, :liquidado, :caja_tipo, :caja, :recibo_id)";

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
            ':caja' => $caja,
            ':recibo_id' => $reciboValor
        ]);

        echo "OK";
        exit;

    } catch (PDOException $e) {
        die("Error al registrar el movimiento: " . $e->getMessage());
    }
} else {
    die("Método no permitido");
}
