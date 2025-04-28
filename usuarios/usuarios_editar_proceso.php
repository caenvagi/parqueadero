<?php
session_start();
require_once "../conexion/conexion.php";

if (
    isset($_POST['id']) &&
    isset($_POST['nombre']) &&
    isset($_POST['cargo']) &&
    isset($_POST['telefono']) &&
    isset($_POST['user']) &&
    isset($_POST['tipo_usuario']) &&
    isset($_POST['avatar']) &&
    isset($_POST['activo']) &&
    isset($_POST['fecha_salida'])&&
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
    $fecha_salida   = $_POST['fecha_salida'];
    $contabilidad   = $_POST['contabilidad'];
    

    

    // Solo actualizar clave si se proporcionó
    $clave = !empty($_POST['clave']) ? password_hash($_POST['clave'], PASSWORD_DEFAULT) : null;

    try {
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
                        fecha_salida = :fecha_salida,
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
                ':fecha_salida'   => $fecha_salida,
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
                        fecha_salida = :fecha_salida,
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
                ':fecha_salida'   => $fecha_salida,
                ':contabilidad'   => $contabilidad,
                ':id'             => $id
            ];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        header("location: usuarios_editar.php?id=$id");        
        $_SESSION['success'] = "Usuario actualizado correctamente.";
        header("Location: usuarios_editar.php?id=" . $id);

    } catch (PDOException $e) {
        $_SESSION['error'] = "Hubo un error al actualizar el usuario.";
        header("Location: usuarios_editar.php?id=" . $id);
    }
} else {
    echo "Faltan datos.";
}
