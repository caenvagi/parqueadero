<?php
require_once "../conexion/conexion.php";
session_start();

$mensaje = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $capacidad = intval($_POST['capacidad']);
    $cantidad_camas = intval($_POST['cantidad_camas']);
    $tipo_cama = trim($_POST['tipo_cama']);
    $descripcion = trim($_POST['descripcion']);
    $usuario_id = $_SESSION['id'] ?? null;

    if ($_POST['modo'] == 'editar') {
        $id = intval($_POST['id']);
        $sql = "UPDATE aloj_habitaciones SET nombre=?, capacidad=?, cantidad_camas=?, tipo_cama=?, descripcion=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $capacidad, $cantidad_camas, $tipo_cama, $descripcion, $id]);
        $mensaje = "✅ Habitación actualizada correctamente.";
        header('Location: aloj_habitaciones.php?msg=actualizado');
        exit;
    } else {
        $sql = "INSERT INTO aloj_habitaciones (nombre, capacidad, cantidad_camas, tipo_cama, estado, descripcion, created_at)
                VALUES (?, ?, ?, ?, 'disponible', ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $capacidad, $cantidad_camas, $tipo_cama, $descripcion]);
        $mensaje = "✅ Habitación registrada correctamente.";
    }
}

// Obtener habitaciones


$habitaciones = [];

$modo_edicion = false;
$habitacion_editar = null;

if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $modo_edicion = true;
    $id_editar = $_GET['editar'];

    $stmt = $pdo->prepare("SELECT * FROM aloj_habitaciones WHERE id = ?");
    $stmt->execute([$id_editar]);
    $habitacion_editar = $stmt->fetch();
}
try {
    $stmt = $pdo->query("SELECT * FROM aloj_habitaciones ORDER BY id ASC");
    $habitaciones = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = "❌ Error al cargar habitaciones: " . $e->getMessage();
}

// Calcular suma total de capacidad
$total_personas = 0;
try {
    $stmt = $pdo->query("SELECT SUM(capacidad) AS total FROM aloj_habitaciones");
    $resultado = $stmt->fetch();
    $total_personas = $resultado['total'] ?? 0;
} catch (PDOException $e) {
    $total_personas = "Error";
}

// Calcular suma total de capacidad y camas
$total_personas = 0;
$total_camas = 0;

try {
    $stmt = $pdo->query("SELECT SUM(capacidad) AS total_personas, SUM(cantidad_camas) AS total_camas FROM aloj_habitaciones");
    $resultado = $stmt->fetch();
    $total_personas = $resultado['total_personas'] ?? 0;
    $total_camas = $resultado['total_camas'] ?? 0;
} catch (PDOException $e) {
    $total_personas = "Error";
    $total_camas = "Error";
}
// Contar habitaciones por estado
$estado_habitaciones = [
    'disponible' => 0,
    'ocupada' => 0,
    'mantenimiento' => 0
];

try {
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM aloj_habitaciones GROUP BY estado");
    while ($row = $stmt->fetch()) {
        $estado_habitaciones[$row['estado']] = $row['total'];
    }
} catch (PDOException $e) {
    // En caso de error, se dejan en 0
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">

        <body class="bg-light">
            <div class="container mt-4">
                <h2 class="mb-4">Gestión de Habitaciones</h2>

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-info"><?= $mensaje ?></div>
                <?php endif; ?>

                <div class="row">
                    <!-- Formulario -->
                    <div class="col-md-6">
                        <div class="card p-4 shadow-sm">
                            <h5>Registrar Nueva Habitación</h5>
                            <form method="POST" action="">
                                <input type="hidden" name="modo" value="<?= $modo_edicion ? 'editar' : 'crear' ?>">
                                <?php if ($modo_edicion): ?>
                                    <input type="hidden" name="id" value="<?= $habitacion_editar['id'] ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input
                                        type="text"
                                        name="nombre"
                                        class="form-control"
                                        required
                                        value="<?= $modo_edicion ? htmlspecialchars($habitacion_editar['nombre']) : '' ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Capacidad (personas)</label>
                                    <input
                                        type="number"
                                        name="capacidad"
                                        class="form-control"
                                        min="1"
                                        required
                                        value="<?= $modo_edicion ? $habitacion_editar['capacidad'] : '' ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Cantidad de camas</label>
                                    <input
                                        type="number"
                                        name="cantidad_camas"
                                        class="form-control"
                                        min="1"
                                        required
                                        value="<?= $modo_edicion ? $habitacion_editar['cantidad_camas'] : '' ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tipo de cama</label>
                                    <select name="tipo_cama" class="form-select" required>
                                        <option value="">Seleccionar...</option>
                                        <?php
                                        $tipos = ['sencilla', 'doble', 'queen', 'king'];
                                        foreach ($tipos as $tipo): ?>
                                            <option
                                                value="<?= $tipo ?>"
                                                <?= $modo_edicion && $habitacion_editar['tipo_cama'] === $tipo ? 'selected' : '' ?>>
                                                <?= ucfirst($tipo) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea
                                        name="descripcion"
                                        class="form-control"
                                        rows="3"><?= $modo_edicion ? htmlspecialchars($habitacion_editar['descripcion']) : '' ?></textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-<?= $modo_edicion ? 'warning' : 'primary' ?>">
                                    <?= $modo_edicion ? 'Actualizar' : 'Guardar' ?>
                                </button>
                                <?php if ($modo_edicion): ?>
                                    <a href="aloj_habitaciones.php" class="btn btn-secondary">Cancelar</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Listado -->
                    <div class="col-md-6">
                        <div class="card p-4 shadow-sm">
                            <h5>Habitaciones Registradas</h5>
                            <p>
                                <strong>Total capacidad (personas):</strong> <?= $total_personas ?> <br>
                                <strong>Total de camas:</strong> <?= $total_camas ?>
                            </p>
                            <p class="mt-3">
                                <strong>Estado de habitaciones:</strong><br>
                                🟢 Disponibles: <?= $estado_habitaciones['disponible'] ?><br>
                                🔴 Ocupadas: <?= $estado_habitaciones['ocupada'] ?><br>
                                🟡 En mantenimiento: <?= $estado_habitaciones['mantenimiento'] ?>
                            </p>
                            <?php if (count($habitaciones) > 0): ?>
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Cap.</th>
                                                <th>Camas</th>
                                                <th>Tipo</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($habitaciones as $hab): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($hab['nombre']) ?></td>
                                                    <td><?= $hab['capacidad'] ?></td>
                                                    <td><?= $hab['cantidad_camas'] ?></td>
                                                    <td><?= ucfirst($hab['tipo_cama']) ?></td>
                                                    <td><?= ucfirst($hab['estado']) ?></td>
                                                    <td>
                                                        <a href="?editar=<?= $hab['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No hay habitaciones registradas aún.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </main>
</div>

</html>