<?php
require_once "../conexion/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $response = [];

    if ($id <= 0) {
        $response['status'] = 'error';
        $response['message'] = "ID inválido.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                $response['status'] = 'error';
                $response['message'] = "❌ El usuario no existe.";
            } elseif ($usuario['activo'] == 1) {
                $response['status'] = 'warning';
                $response['message'] = "⚠️ El usuario ya está activo.";
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET activo = 1 WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $response['status'] = 'success';
                $response['message'] = "✅ Usuario reactivado correctamente.";
            }
        } catch (PDOException $e) {
            error_log("Error al reactivar usuario: " . $e->getMessage());
            $response['status'] = 'error';
            $response['message'] = "❌ Error al reactivar el usuario.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Obtener usuarios inactivos
$stmt = $pdo->query("SELECT id, nombre, usuario, tipo_cargo FROM usuarios WHERE activo = 0 ORDER BY nombre");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reactivar Usuario (AJAX)</title>
    <script>
        function reactivarUsuario(event) {
            event.preventDefault(); // Evita recargar la página

            const form = document.getElementById('formReactivar');
            const formData = new FormData(form);
            formData.append('ajax', '1'); // Indicamos que es una petición AJAX

            fetch('usuarios_ajax_reactivar.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                const resultado = document.getElementById('resultado');
                resultado.innerHTML = data.message;
                resultado.style.color = data.status === 'success' ? 'green' : (data.status === 'warning' ? 'orange' : 'red');

                // Eliminar la opción del usuario reactivado del <select>
                if (data.status === 'success') {
                    const select = document.getElementById('id');
                    select.querySelector(`option[value="${formData.get('id')}"]`).remove();
                }
            })
            .catch(error => {
                console.error('Error AJAX:', error);
                document.getElementById('resultado').innerHTML = "❌ Error inesperado.";
            });
        }
    </script>
</head>
<body>
    <h2>Reactivar Usuario (AJAX)</h2>

    <div id="resultado"><!-- aquí se muestra la respuesta --></div>

    <?php if (count($usuarios) > 0): ?>
        <form id="formReactivar" onsubmit="reactivarUsuario(event)">
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
