<?php
require_once "../conexion/conexion.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID de usuario no especificado.");
}

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

    <form id="formEditarUsuario">
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

        <label>Avatar:
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

    <div id="mensaje"></div>

    <script>
    document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('usuarios_editar_proceso.php', {
            method: 'POST',
            body: formData
        })
        .then(resp => resp.text())
        .then(data => {
            document.getElementById('mensaje').innerHTML = data;
        })
        .catch(error => {
            console.error('Error en AJAX:', error);
            document.getElementById('mensaje').innerHTML = 'Ocurrió un error al guardar.';
        });
    });
    </script>
</body>
</html>
