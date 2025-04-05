<?php
require_once "../conexion/conexion.php";

// Si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        $mensaje = "ID inválido.";
    } else {
        try {
            // Verificar si el usuario existe y está inactivo
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                $mensaje = "❌ El usuario no existe.";
            } elseif ($usuario['activo'] == 1) {
                $mensaje = "⚠️ El usuario ya está activo.";
            } else {
                // Reactivar usuario
                $stmt = $pdo->prepare("UPDATE usuarios SET activo = 1 WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $mensaje = "✅ Usuario reactivado correctamente.";
            }
        } catch (PDOException $e) {
            error_log("Error al reactivar usuario: " . $e->getMessage());
            $mensaje = "❌ Ocurrió un error al intentar reactivar el usuario.";
        }
    }
}

// Obtener lista de usuarios inactivos para el select
$stmt = $pdo->query("SELECT id, nombre, usuario, tipo_cargo FROM usuarios WHERE activo = 0 ORDER BY nombre");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reactivar Usuario</title>
</head>
<body>
    <h2>Reactivar Usuario</h2>

    <?php if (isset($mensaje)): ?>
        <p><strong><?php echo $mensaje; ?></strong></p>
    <?php endif; ?>

    <?php if (count($usuarios) > 0): ?>
        <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas reactivar este usuario?');">
            <label for="id">Selecciona un Usuario Inactivo:</label>
            <select name="id" id="id" required>
                <option value="">-- Selecciona --</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id'] ?>">
                        <?= htmlspecialchars($u['nombre']) ?> (<?= htmlspecialchars($u['usuario']) ?> - <?= $u['tipo_cargo'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <input type="submit" value="Reactivar Usuario">
        </form>
    <?php else: ?>
        <p>No hay usuarios inactivos para mostrar.</p>
    <?php endif; ?>
</body>
</html>
