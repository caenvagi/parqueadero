<?php
require_once "../conexion/conexion.php";

// Si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        $mensaje = "ID inválido.";
    } else {
        try {
            // Verificar si el usuario existe y está activo
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                $mensaje = "❌ El usuario no existe.";
            } elseif ($usuario['activo'] == 0) {
                $mensaje = "⚠️ El usuario ya está inactivo.";
            } else {
                // Desactivar usuario en lugar de eliminarlo
                $stmt = $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $mensaje = "✅ Usuario desactivado correctamente.";
            }
        } catch (PDOException $e) {
            error_log("Error al desactivar usuario: " . $e->getMessage());
            $mensaje = "❌ Ocurrió un error al intentar desactivar el usuario.";
        }
    }
}

// Obtener lista de usuarios activos para el select
$stmt = $pdo->query("SELECT id, nombre, usuario, tipo_cargo FROM usuarios WHERE activo = 1 ORDER BY nombre");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Desactivar Usuario</title>
</head>
<body>
    <h2>Desactivar Usuario</h2>

    <?php if (isset($mensaje)): ?>
        <p><strong><?php echo $mensaje; ?></strong></p>
    <?php endif; ?>

    <?php if (count($usuarios) > 0): ?>
        <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas desactivar este usuario?');">
            <label for="id">Selecciona un Usuario:</label>
            <select name="id" id="id" required>
                <option value="">-- Selecciona --</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id'] ?>">
                        <?= htmlspecialchars($u['nombre']) ?> (<?= htmlspecialchars($u['usuario']) ?> - <?= $u['tipo_cargo'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <input type="submit" value="Desactivar Usuario">
        </form>
    <?php else: ?>
        <p>No hay usuarios activos para mostrar.</p>
    <?php endif; ?>
</body>
</html>
