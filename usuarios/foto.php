<?php 
session_start();

require '../conexion/conexion.php'; // Asegúrate que aquí esté el archivo PDO

$id = $_POST['id'];
$foto = $_FILES['nfoto'];

$tmp_name = $foto['tmp_name']; 
$img_file = $foto['name'];
$img_type = $foto['type'];

$directorio_destino = "images/";
$destino_relativo = $directorio_destino . $img_file;
$destino_absoluto = "../usuarios/" . $destino_relativo;

if ((strpos($img_type, "gif") || strpos($img_type, "jpeg") ||
     strpos($img_type, "jpg") || strpos($img_type, "png"))) {

    if (move_uploaded_file($tmp_name, $destino_absoluto)) {
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET avatar = :avatar WHERE id = :id");
            $stmt->execute([
                ':avatar' => $destino_relativo,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar avatar: " . $e->getMessage());
            die("Error al actualizar la imagen. Intente más tarde.");
        }
        ?>
        <script type="text/javascript">
            window.location = "usuarios_nuevos.php";
        </script>
        <?php
    } else {
        echo "Error al mover el archivo.";
    }
} else {
    echo "El archivo no es una imagen válida.";
}
?>
