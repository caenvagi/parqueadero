<?php
session_start();
require_once "../conexion/conexion.php";

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
}
$id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE id=$id";
}

if (
    isset($_POST['id']) &&
    isset($_POST['nombre']) &&
    isset($_POST['cargo']) &&
    isset($_POST['telefono']) &&
    isset($_POST['user']) &&
    isset($_POST['tipo_usuario']) &&
    isset($_POST['avatar']) &&
    isset($_POST['activo']) &&
    isset($_POST['fecha_retiro']) &&
    isset($_POST['contabilidad'])
) {
    $id             = $_POST['id'];
    $nombre         = $_POST['nombre'];
    $tipo_cargo     = $_POST['cargo'];
    $telefono       = $_POST['telefono'];
    $usuario        = $_POST['user'];
    $tipo_usuario   = $_POST['tipo_usuario'];
    $avatar         = $_POST['avatar'];
    $activo         = $_POST['activo'];
    $fecha_retiro   = $_POST['fecha_retiro'];
    $contabilidad   = $_POST['contabilidad'];

    $reactivado     = isset($_POST['reactivado']) ? $_POST['reactivado'] : '0';

    $clave = !empty($_POST['clave']) ? password_hash($_POST['clave'], PASSWORD_DEFAULT) : null;

    try {
        // Actualizar tabla usuarios
        if ($clave) {
            $sql = "UPDATE usuarios SET 
                        nombre = :nombre,
                        tipo_cargo = :tipo_cargo,
                        telefono = :telefono,
                        usuario = :usuario,
                        clave = :clave,
                        tipo_usuario = :tipo_usuario,
                        avatar = :avatar,
                        activo = :activo,
                        fecha_retiro = :fecha_retiro,
                        contabilidad = :contabilidad
                    WHERE id = :id";

            $params = [
                ':nombre'         => $nombre,
                ':tipo_cargo'     => $tipo_cargo,
                ':telefono'       => $telefono,
                ':usuario'        => $usuario,
                ':clave'          => $clave,
                ':tipo_usuario'   => $tipo_usuario,
                ':avatar'         => $avatar,
                ':activo'         => $activo,
                ':fecha_retiro'   => $fecha_retiro,
                ':contabilidad'   => $contabilidad,
                ':id'             => $id
            ];
        } else {
            $sql = "UPDATE usuarios SET 
                        nombre = :nombre,
                        tipo_cargo = :tipo_cargo,
                        telefono = :telefono,
                        usuario = :usuario,
                        tipo_usuario = :tipo_usuario,
                        avatar = :avatar,
                        activo = :activo,
                        fecha_retiro = :fecha_retiro,
                        contabilidad = :contabilidad
                    WHERE id = :id";

            $params = [
                ':nombre'         => $nombre,
                ':tipo_cargo'     => $tipo_cargo,
                ':telefono'       => $telefono,
                ':usuario'        => $usuario,
                ':tipo_usuario'   => $tipo_usuario,
                ':avatar'         => $avatar,
                ':activo'         => $activo,
                ':fecha_retiro'   => $fecha_retiro,
                ':contabilidad'   => $contabilidad,
                ':id'             => $id
            ];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Manejo de historial
        if (strtolower($activo) === '0') {
            // Si se desactiva el usuario, actualizar fecha_retiro en el último registro sin cerrar
            $sql_historia = "UPDATE usuarios_historia 
                            SET fecha_retiro = :fecha_retiro 
                            WHERE user_hist_id = (
                                SELECT MAX(user_hist_id) 
                                FROM usuarios_historia 
                                WHERE usuario = :id)";
            $params_historia = [
                ':fecha_retiro' => $fecha_retiro,
                ':id'           => $id
            ];
            $stmt1 = $pdo->prepare($sql_historia);
            $stmt1->execute($params_historia);
        } elseif (strtolower($activo) === '1' && $reactivado === '1') {
            // Si se reactiva, insertar un nuevo ingreso
            $sql_insert_historia = "INSERT INTO usuarios_historia (usuario, fecha_ingreso, cargo, user) 
                                    VALUES (:usuario_id, NOW(), :cargo, :user)";
            $stmt2 = $pdo->prepare($sql_insert_historia);
            $stmt2->execute([
                ':usuario_id' => $id,
                ':cargo'      => $tipo_cargo,
                'user' => $_SESSION['id'],
            ]);
        }

        $_SESSION['success'] = "Usuario actualizado correctamente.";
        header("Location: usuarios_lista.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Hubo un error al actualizar el usuario: " . $e->getMessage();
        header("Location: usuarios_editar.php?id=" . $id);
        exit;
    }
} else {
    echo "Faltan datos.";
}
