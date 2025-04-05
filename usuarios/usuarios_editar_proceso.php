<?php
require_once "../conexion/conexion.php";

if (
    isset($_POST['id']) &&
    isset($_POST['nombre']) &&
    isset($_POST['tipo_cargo']) &&
    isset($_POST['telefono']) &&
    isset($_POST['usuario']) &&
    isset($_POST['tipo_usuario']) &&
    isset($_POST['avatar']) &&
    isset($_POST['activo']) &&
    isset($_POST['contabilidad'])
) {
    $id             = $_POST['id'];
    $nombre         = $_POST['nombre'];
    $tipo_cargo     = $_POST['tipo_cargo'];
    $telefono       = $_POST['telefono'];
    $usuario        = $_POST['usuario'];
    $tipo_usuario   = $_POST['tipo_usuario'];
    $avatar         = $_POST['avatar'];
    $activo         = $_POST['activo'];
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
                ':contabilidad'   => $contabilidad,
                ':id'             => $id
            ];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo "Usuario actualizado correctamente. <a href='usuarios_editar_ajax.php?id=$id'>Volver</a>";

    } catch (PDOException $e) {
        error_log("Error al actualizar usuario: " . $e->getMessage());
        echo "Hubo un error al actualizar el usuario.";
    }
} else {
    echo "Faltan datos.";
}
