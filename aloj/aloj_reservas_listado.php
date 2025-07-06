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

// Total por mes (mes actual)
$sql_mes = "
  SELECT SUM(valor_total) AS total_mes
  FROM aloj_reservas
  WHERE estado IN ('pendiente','confirmada')
    AND MONTH(created_at) = MONTH(CURDATE())
    AND YEAR(created_at) = YEAR(CURDATE())
";
$total_mes = $pdo->query($sql_mes)->fetchColumn();

// Total por año (año actual)
$sql_anio = "
  SELECT SUM(valor_total) AS total_anio
  FROM aloj_reservas
  WHERE estado IN ('pendiente','confirmada')
    AND YEAR(created_at) = YEAR(CURDATE())
";
$total_anio = $pdo->query($sql_anio)->fetchColumn();

// Totales por mes del año actual
$sql_grafico = "
  SELECT 
    MONTH(created_at) AS mes,
    SUM(valor_total) AS total
  FROM aloj_reservas
  WHERE estado IN ('pendiente','confirmada')
    AND YEAR(created_at) = YEAR(CURDATE())
  GROUP BY MONTH(created_at)
  ORDER BY mes
";

$datos_mensuales = $pdo->query($sql_grafico)->fetchAll();

// Preparar datos para JavaScript
$meses = [
  1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
  5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
  9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$labels = [];
$valores = [];

foreach ($datos_mensuales as $fila) {
  $labels[] = $meses[(int)$fila['mes']];
  $valores[] = $fila['total'];
}



?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <?php require '../logs/head.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  </head>
  <?php require '../logs/nav-bar.php'; ?>
  <div id="layoutSidenav_content">
    <main class="m-3">
      <body>

      <div class="row mb-4">
  <div class="col-md-6">
    <div class="card border-success shadow-sm">
      <div class="card-body">
        <h6 class="card-title text-success mb-1"><i class="bi bi-calendar3"></i> Total reservas del Mes</h6>
        <h5 class="mb-0 fw-bold">$<?= number_format($total_mes ?? 0, 0, ',', '.') ?></h5>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-primary shadow-sm">
      <div class="card-body">
        <h6 class="card-title text-primary mb-1"><i class="bi bi-calendar-range"></i> Total reservas del Año</h6>
        <h5 class="mb-0 fw-bold">$<?= number_format($total_anio ?? 0, 0, ',', '.') ?></h5>
      </div>
    </div>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header bg-secondary text-white">
    <h6 class="mb-0">Total Reservas por Mes - <?= date('Y') ?></h6>
  </div>
  <div class="card-body">
    <canvas id="graficoReservas" height="100"></canvas>
  </div>
</div>
    

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
<script>
  const ctx = document.getElementById('graficoReservas').getContext('2d');
  const grafico = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'Valor total ($)',
        data: <?= json_encode($valores) ?>,
        backgroundColor: 'rgba(13, 110, 253, 0.6)',
        borderColor: 'rgba(13, 110, 253, 1)',
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '$' + value.toLocaleString('es-CO');
            }
          }
        }
      }
    }
  });
</script>
</body>
    </main>
  </div>
</html>
