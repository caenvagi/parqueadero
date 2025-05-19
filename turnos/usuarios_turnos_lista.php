<?php
require_once "../conexion/conexion.php";

// Consulta de datos
$stmt = $pdo->query("SELECT id_turno, usuario_id, fecha_inicio, fecha_fin, valor, pagado, US.nombre
                            FROM usuarios_turnos as UT
                            INNER JOIN usuarios as US on US.id = UT.usuario_id
                            ORDER BY usuario_id DESC");
$turnos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visualizar Turnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Lista de Turnos</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Empleado</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Valor</th>
                <th>Pagado</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($turnos): ?>
            <?php foreach ($turnos as $turno): ?>
                <tr>
                    <td><?= htmlspecialchars($turno['nombre']) ?></td>
                    <td><?= htmlspecialchars($turno['fecha_inicio']) ?></td>
                    <td><?= htmlspecialchars($turno['fecha_fin']) ?></td>
                    <td>$<?= number_format($turno['valor'], 2) ?></td>
                    <td>
                        <?php if ($turno['pagado']): ?>
                            <span class="badge bg-success">Sí</span>
                        <?php else: ?>
                            <span class="badge bg-danger">No</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center">No hay turnos registrados.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
