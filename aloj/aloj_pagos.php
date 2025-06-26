<?php
require_once "../conexion/conexion.php";
session_start();
if (!isset($_SESSION['id'])) die("No autorizado.");

$reserva_id = isset($_GET['reserva_id']) ? intval($_GET['reserva_id']) : 0;
$reserva = null;
$total_pagado = 0;
$historial_pagos = []; // Evita error si no se llena

if ($reserva_id > 0) {
    // 1) Obtener la reserva con cliente
    $stmt = $pdo->prepare("
        SELECT r.*, c.nombre, c.documento
        FROM aloj_reservas r
        JOIN aloj_clientes c ON r.cliente_id = c.id
        WHERE r.id = ?
    ");
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch();

    if ($reserva) {
        // 2) Calcular total pagado hasta ahora
        $stmt2 = $pdo->prepare("SELECT SUM(monto) AS total FROM aloj_pagos WHERE reserva_id = ?");
        $stmt2->execute([$reserva_id]);
        $pagosRow = $stmt2->fetch();
        $total_pagado = floatval($pagosRow['total']);

        // 3) Obtener historial de pagos
        $stmt3 = $pdo->prepare("
            SELECT p.*, u.usuario AS usuario
            FROM aloj_pagos p
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.reserva_id = ?
            ORDER BY p.fecha_pago ASC
        ");
        $stmt3->execute([$reserva_id]);
        $historial_pagos = $stmt3->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
     <?php require '../logs/head.php'; ?>
</head>
<body>
    

<?php if ($reserva): ?>
  <div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="aloj_reservas_listado.php" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i> Volver a Reservas
    </a>
    
  </div>
    
  <div class="card shadow mb-4">
    <div class="card-header bg-info text-white">
        <span class="badge bg-dark fs-6">Reserva No <?= $reserva['id'] ?></span>
      <h5 class="mb-0">
      <?= htmlspecialchars($reserva['nombre']) ?></h5>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-4">
          <div class="border p-3 rounded bg-light">
            <strong>Valor Total de la Reserva:</strong><br>
            <span class="text-success fs-5">
              $<?= number_format($reserva['valor_total'], 0, ',', '.') ?>
            </span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="border p-3 rounded bg-light">
            <strong>Total Pagado:</strong><br>
            <span class="text-primary fs-5">
              $<?= number_format($total_pagado, 0, ',', '.') ?>
            </span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="border p-3 rounded bg-light">
            <strong>Documento del Cliente:</strong><br>
            <span class="text-muted"><?= htmlspecialchars($reserva['documento']) ?></span>
          </div>
        </div>
      </div>

      <?php if ($total_pagado >= floatval($reserva['valor_total'])): ?>
        <div class="alert alert-success">
          ✅ Esta reserva ya está pagada en su totalidad.
        </div>
      <?php else: ?>
        <!-- Aquí va el formulario de pago (ya lo tienes en tu archivo actual) -->
      <?php endif; ?>
    </div>
  </div>

  <!-- Historial de pagos: SIEMPRE visible -->
  <div class="card mt-4 shadow border-primary">
    <div class="card-header bg-primary text-white">
      <h6 class="mb-0">Historial de Pagos</h6>
    </div>
    <div class="card-body p-0">
      <?php if (count($historial_pagos)): ?>
        <div class="table-responsive">
          <table class="table table-striped table-bordered table-sm m-0">
            <thead class="table-light">
              <tr>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Tipo</th>
                <th>Observaciones</th>
                <th>Registrado por</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($historial_pagos as $pago): ?>
                <tr>
                  <td><?= date("Y-m-d H:i", strtotime($pago['fecha_pago'])) ?></td>
                  <td>$<?= number_format($pago['monto'], 0, ',', '.') ?></td>
                  <td><?= ucfirst($pago['metodo_pago']) ?></td>
                  <td><?= ucfirst($pago['tipo_pago']) ?></td>
                  <td><?= nl2br(htmlspecialchars($pago['observaciones'])) ?></td>
                  <td><?= htmlspecialchars($pago['usuario'] ?? 'N/D') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="p-3 text-muted">🔔 No se han registrado pagos aún para esta reserva.</p>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>
  <div class="alert alert-danger">❌ No se encontró la reserva.</div>
  <a href="aloj_reservas_listado.php" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Volver a Reservas
  </a>
<?php endif; ?>
</body>
</html>