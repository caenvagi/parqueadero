
<?php
require '../conexion/conexion.php';

$stmt = $pdo->query("
    SELECT p.parqueo_id, p.placa_cli, c.vehiculo, cat.cat_nombre, cs.casetas_nom, p.fecha_ini
    FROM parqueo p
    JOIN cliente c ON p.placa_cli = c.placa
    JOIN categorias cat ON c.categoria = cat.cat_id
    JOIN casetas cs ON p.caseta = cs.caseta_id
    WHERE p.estado = 'SI'
    ORDER BY p.fecha_ini DESC
");
?>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Placa</th>
            <th>Vehículo</th>
            <th>Categoría</th>
            <th>Caseta</th>
            <th>Fecha Ingreso</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($stmt as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['parqueo_id']) ?></td>
            <td><?= htmlspecialchars($r['placa_cli']) ?></td>
            <td><?= htmlspecialchars($r['vehiculo']) ?></td>
            <td><?= htmlspecialchars($r['cat_nombre']) ?></td>
            <td><?= htmlspecialchars($r['casetas_nom']) ?></td>
            <td><?= htmlspecialchars($r['fecha_ini']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
