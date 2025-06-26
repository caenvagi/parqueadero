<?php
require_once "../conexion/conexion.php";
session_start();
if (!isset($_SESSION['id'])) die("No autorizado.");

// 1) Consulta reservas activas con cálculo de noches
$sql = "
  SELECT 
    r.id,
    c.nombre         AS cliente,
    h.nombre         AS habitacion,
    r.fecha_ingreso,
    r.fecha_salida,
    DATEDIFF(r.fecha_salida, r.fecha_ingreso) AS noches,
    r.cantidad_personas,
    r.valor_total,
    r.estado,
    DATE_FORMAT(r.created_at, '%Y-%m-%d %H:%i') AS creado_por_fecha
  FROM aloj_reservas r
  INNER JOIN aloj_clientes c ON r.cliente_id = c.id
  INNER JOIN aloj_habitaciones h ON r.habitacion_id = h.id
  WHERE r.estado IN ('pendiente','confirmada')
  ORDER BY r.created_at DESC
";
$reservas_activas = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require '../logs/head.php'; ?>
</head>
<body>
    

<div class="card shadow">
  <div class="card-header bg-info text-white">
    <h5 class="mb-0">Reservas Activas</h5>
  </div>
  <div class="card-body p-0">
    <?php if (count($reservas_activas)): ?>
      <div class="table-responsive">
        <table class="table table-striped table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th>Reserva</th>
              <th>Cliente</th>
              <th>Habitación</th>
              <th>Ingreso</th>
              <th>Salida</th>
              <th>Noches</th>            <!-- Nueva columna -->
              <th>Personas</th>
              <th>Valor Total</th>
              <th>Estado</th>
              <th>Creada en</th>
              <th>Acciones</th> <!-- Nueva columna -->
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reservas_activas as $r): ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['cliente']) ?></td>
                <td><?= htmlspecialchars($r['habitacion']) ?></td>
                <td><?= $r['fecha_ingreso'] ?></td>
                <td><?= $r['fecha_salida'] ?></td>
                <td><?= $r['noches'] ?></td>            <!-- Mostrar noches -->
                <td><?= $r['cantidad_personas'] ?></td>
                <td>$<?= number_format($r['valor_total'], 0, ',', '.') ?></td>
                <td>
                  <span class="badge bg-<?= $r['estado']=='confirmada'?'success':'warning' ?>">
                    <?= ucfirst($r['estado']) ?>
                  </span>
                </td>
                <td><?= $r['creado_por_fecha'] ?></td>
                <td>
                  <a href="aloj_pagos.php?reserva_id=<?= $r['id'] ?>"
                     class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-cash-stack me-1"></i>Pagar
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="p-3 text-muted">No hay reservas activas en este momento.</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
