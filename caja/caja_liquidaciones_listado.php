<?php
require_once '../conexion/conexion.php';

$sql = "SELECT * FROM caja_liquidaciones ORDER BY fecha_liquidacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$liquidaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Liquidaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">📋 Listado de Liquidaciones</h2>
        <a href="crear_liquidacion.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Nueva Liquidación
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-primary">
                        <tr class="text-center">
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Total Liquidado</th>
                            <th>Entregado por</th>
                            <th>Recibido por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($liquidaciones) > 0): ?>
                            <?php foreach ($liquidaciones as $liq): ?>
                                <tr>
                                    <td class="text-center"><?= $liq['id_liquidacion'] ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($liq['fecha_liquidacion'])) ?></td>
                                    <td class="text-end text-success fw-bold">$<?= number_format($liq['total_liquidado'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($liq['entregado_por']) ?></td>
                                    <td><?= htmlspecialchars($liq['recibido_por']) ?></td>
                                    <td class="text-center">
                                        <a href="caja_liquidaciones_detalle.php?id=<?= $liq['id_liquidacion'] ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No hay liquidaciones registradas.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
