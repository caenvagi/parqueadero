<?php
// Incluir archivo de conexión
require_once "../conexion/conexion.php";

// Validar que se reciben todos los datos necesarios
if (
    isset($_POST['id']) &&
    isset($_POST['nombre']) &&
    isset($_POST['tipo_cargo']) &&
    isset($_POST['telefono']) &&
    isset($_POST['usuario']) &&
    isset($_POST['clave']) &&
    isset($_POST['tipo_usuario']) &&
    isset($_POST['avatar']) &&
    isset($_POST['activo']) &&
    isset($_POST['contabilidad'])
) {
    // Obtener datos del formulario
    $id             = $_POST['id'];
    $nombre         = $_POST['nombre'];
    $tipo_cargo     = $_POST['tipo_cargo'];
    $telefono       = $_POST['telefono'];
    $usuario        = $_POST['usuario'];
    $clave          = password_hash($_POST['clave'], PASSWORD_DEFAULT); // Encriptar la clave
    $tipo_usuario   = $_POST['tipo_usuario'];
    $avatar         = $_POST['avatar'];
    $activo         = $_POST['activo'];
    $contabilidad   = $_POST['contabilidad'];

    try {
        // Preparar la consulta SQL con parámetros
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

        $stmt = $pdo->prepare($sql);

        // Ejecutar con los parámetros vinculados
        $stmt->execute([
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
        ]);

        echo "Usuario actualizado correctamente.";

    } catch (PDOException $e) {
        error_log("Error al actualizar usuario: " . $e->getMessage());
        echo "Hubo un error al actualizar el usuario.";
    }
} else {
    echo "Faltan datos para actualizar el usuario.";
}
$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID de usuario no especificado.");
}

// Obtener los datos del usuario para prellenar el formulario
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        die("Usuario no encontrado.");
    }
} catch (PDOException $e) {
    error_log("Error al obtener usuario: " . $e->getMessage());
    die("Error al cargar datos del usuario.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
</head>
<body>
    <h2>Editar Usuario</h2>
    <form method="POST" action="usuarios_procesaredicion.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">

        <label>Nombre:
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
        </label><br>

        <label>Tipo de Cargo:
            <input type="text" name="tipo_cargo" value="<?= htmlspecialchars($usuario['tipo_cargo']) ?>" required>
        </label><br>

        <label>Teléfono:
            <input type="tel" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" required>
        </label><br>

        <label>Usuario:
            <input type="text" name="usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
        </label><br>

        <label>Clave (deja en blanco si no deseas cambiarla):
            <input type="password" name="clave">
        </label><br>

        <label>Tipo de Usuario:
            <input type="text" name="tipo_usuario" value="<?= htmlspecialchars($usuario['tipo_usuario']) ?>" required>
        </label><br>

        <label>Avatar (URL o ruta):
            <input type="text" name="avatar" value="<?= htmlspecialchars($usuario['avatar']) ?>">
        </label><br>

        <label>Activo:
            <select name="activo" required>
                <option value="1" <?= $usuario['activo'] == 1 ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= $usuario['activo'] == 0 ? 'selected' : '' ?>>No</option>
            </select>
        </label><br>

        <label>Contabilidad:
            <select name="contabilidad" required>
                <option value="1" <?= $usuario['contabilidad'] == 1 ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= $usuario['contabilidad'] == 0 ? 'selected' : '' ?>>No</option>
            </select>
        </label><br><br>

        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>