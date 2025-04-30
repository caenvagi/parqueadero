<?php
require '../conexion/conexion.php';
session_start();

$id = $_SESSION['id'];

// cambiar contraseña
if ($_POST['action'] == 'changePassword') {
    if (!empty($_POST['passActual']) && !empty($_POST['passNuevo'])) {

        $passActual = $_POST['passActual'];
        $passNuevo = $_POST['passNuevo'];

        $code = '';
        $msg = '';
        $arrData = [];

        try {
            // Obtener la contraseña actual del usuario
            $stmt = $pdo->prepare("SELECT clave FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();

            if ($user && password_verify($passActual, $user['clave'])) {
                // Encriptar nueva contraseña
                $newPassHash = password_hash($passNuevo, PASSWORD_DEFAULT);

                // Actualizar contraseña
                $stmt = $pdo->prepare("UPDATE usuarios SET clave = :clave WHERE id = :id");
                $success = $stmt->execute([
                    ':clave' => $newPassHash,
                    ':id' => $id
                ]);

                if ($success) {
                    $code = '00';
                    $msg = "Su contraseña se ha actualizado con éxito.";
                } else {
                    $code = '2';
                    $msg = "No es posible cambiar la contraseña.";
                }
            } else {
                $code = '1';
                $msg = "La contraseña actual es incorrecta.";
            }

            $arrData = ['cod' => $code, 'msg' => $msg];
            echo json_encode($arrData, JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            error_log("Error al cambiar la contraseña: " . $e->getMessage());
            echo json_encode(['cod' => '3', 'msg' => 'Error del sistema. Intente más tarde.']);
        }

    } else {
        echo json_encode(['cod' => '4', 'msg' => 'Debe llenar todos los campos.']);
    }
    exit;
}
?>
