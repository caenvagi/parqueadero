

<?php
require_once '../conexion/conexion.php';

if (!isset($_GET['id'])) {
    echo "ID no válido.";
    exit;
}

$id_liquidacion = intval($_GET['id']);

// Obtener información principal de la liquidación
$sql_liq = "SELECT * FROM caja_liquidaciones WHERE id_liquidacion = ?";
$stmt_liq = $pdo->prepare($sql_liq);
$stmt_liq->execute([$id_liquidacion]);
$liq = $stmt_liq->fetch(PDO::FETCH_ASSOC);

if (!$liq) {
    echo "Liquidación no encontrada.";
    exit;
}

// Obtener detalles de movimientos
$sql_detalle = "
    SELECT cd.id_movimiento, cm.fecha_movimiento, cm.desc_movimiento, cm.valor_ingreso, cm.valor_egreso 
    FROM caja_liquidaciones_detalle cd
    INNER JOIN caja cm ON cd.id_movimiento = cm.id_movimiento
    WHERE cd.id_liquidacion = ?
";
$stmt_det = $pdo->prepare($sql_detalle);
$stmt_det->execute([$id_liquidacion]);
$detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Liquidación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <a href="caja_liquidaciones_listado.php" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Volver al listado
    </a>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">🧾 Detalle de Liquidación #<?= $liq['id_liquidacion'] ?></h4>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Fecha:</label>
                    <div><?= date('Y-m-d H:i', strtotime($liq['fecha_liquidacion'])) ?></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Entregado por:</label>
                    <div><?= htmlspecialchars($liq['entregado_por']) ?></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Recibido por:</label>
                    <div><?= htmlspecialchars($liq['recibido_por']) ?></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Total Liquidado:</label>
                    <div class="text-success fw-bold h5">$<?= number_format($liq['total_liquidado'], 0, ',', '.') ?></div>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Observaciones:</label>
                    <div class="text-muted"><?= nl2br(htmlspecialchars($liq['observaciones'])) ?: '<em>Sin observaciones</em>' ?></div>
                </div>
            </div>

            <hr>

            <h5 class="mb-3"><i class="bi bi-receipt-cutoff me-2"></i>Facturas Incluidas</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Recibo</th>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Ingreso</th>
                            <th>Egreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($detalles) > 0): ?>
                            <?php foreach ($detalles as $d): ?>
                                <tr>
                                    <td class="text-center"><?= $d['id_movimiento'] ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($d['fecha_movimiento'])) ?></td>
                                    <td><?= htmlspecialchars($d['desc_movimiento']) ?></td>
                                    <td class="text-end text-success">$<?= number_format($d['valor_ingreso'], 0, ',', '.') ?></td>
                                    <td class="text-end text-danger">$<?= number_format($d['valor_egreso'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay facturas asociadas a esta liquidación.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <a href="caja_ticket_liquidacion.php?id=<?= $liq['id_liquidacion'] ?>" class="btn btn-outline-success">
                    <i class="bi bi-printer"></i> Imprimir Ticket
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>