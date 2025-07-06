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
        SELECT r.*, c.nombre AS cliente_nombre, c.documento,
              h.nombre AS habitacion_nombre
        FROM aloj_reservas r
        JOIN aloj_clientes c ON r.cliente_id = c.id
        JOIN aloj_habitaciones h ON r.habitacion_id = h.id
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
// Obtener acompañantes de la reserva
$acompanantes = [];
$stmt = $pdo->prepare("SELECT nombre, documento, edad FROM aloj_acompanantes WHERE reserva_id = ?");
$stmt->execute([$reserva_id]);
$acompanantes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <?php require '../logs/head.php'; ?>
  </head>
  <?php require '../logs/nav-bar.php'; ?>
  <div id="layoutSidenav_content">
    <main class="ms-5 me-5">
      <body>
        <?php if ($reserva): ?>
          <?php
          $noches = (new DateTime($reserva['fecha_ingreso']))->diff(new DateTime($reserva['fecha_salida']))->days;
          ?>
              
              <div class="card shadow mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                  <div>
                    <span class="badge bg-dark fs-6">Reserva No <?= $reserva['id'] ?></span>
                    <h5 class="mb-0"><?= htmlspecialchars($reserva['cliente_nombre']) ?></h5>
                  </div>
                  <a href="aloj_reservas_listado.php" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Reservas
                  </a>                
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
                    <div class="col-md-3 mt-2">
                      <div class="border p-3 rounded bg-light h-100">
                        <strong>Fecha Ingreso:</strong><br>
                        <?= date('Y-m-d', strtotime($reserva['fecha_ingreso'])) ?>
                      </div>
                    </div>
                    <div class="col-md-3 mt-2">
                      <div class="border p-3 rounded bg-light h-100">
                        <strong>Fecha Salida:</strong><br>
                        <?= date('Y-m-d', strtotime($reserva['fecha_salida'])) ?>
                      </div>
                    </div>
                    <div class="col-md-3 mt-2">
                      <div class="border p-3 rounded bg-light h-100">
                        <strong>Noches / Personas:</strong><br>
                        <?= $noches ?> noche<?= $noches == 1 ? '' : 's' ?> /
                        <?= $reserva['cantidad_personas'] ?> persona<?= $reserva['cantidad_personas'] == 1 ? '' : 's' ?>
                      </div>
                    </div>
                    <div class="col-md-3 mt-2">
                      <div class="border p-3 rounded bg-light h-100">
                        <strong>Habitación Asignada:</strong><br>
                        <?= htmlspecialchars($reserva['habitacion_nombre']) ?>
                      </div>
                    </div>
                  </div>
                </div>
                  <?php if ($total_pagado >= floatval($reserva['valor_total'])): ?>
                  <div class="alert alert-success">
                    ✅ Esta reserva ya está pagada en su totalidad.
                  </div>
                  <?php else: ?>
                  <input type="hidden" name="reserva_id" value="<?= $reserva_id ?>">

                   <div class="card-body mt-0">
                        <form action="aloj_pagos_guardar.php" method="POST" class="row g-4">

                            <input type="hidden" name="reserva_id" value="<?= $reserva_id ?>">  
                             
                            <div class="mb-1 col col-md-6">
                              <label class="form-label">Metodo pago</label>
                            <select class="form-select" name="metodo_pago" required aria-label="Default select example">
                              <option selected>Selecione...</option>
                              <option value="efectivo">Efectivo</option>
                              <option value="tarjeta">Tarjeta</option>
                              <option value="transferencia">Transferencia</option>
                            </select>
                            </div>

                            <div class="mb-1 col col-md-6">
                              <label class="form-label">Tipo pago</label>
                            <select class="form-select"  name="tipo_pago" aria-label="Default select example2" required>
                              <option selected>Selecione...</option>
                              <option value="abono">Abono</option>
                              <option value="saldo">Saldo</option>
                            </select>
                            </div>
                            
                            <!-- Valor total -->
                            <div class="col-md-4">
                                <label class="form-label">Valor Total</label>
                                <input type="text" class="form-control fw-bold text-success" 
                                      value="$<?= number_format($reserva['valor_total'], 0, ',', '.') ?>" readonly>
                            </div>

                            <!-- Abono -->
                            <div class="col-md-4">
                                <label class="form-label">Abono (opcional)</label>
                                <input type="number" name="monto" class="form-control" placeholder="Ej: 50000" min="0" step="1000">
                            </div>

                            <!-- Pago al ingreso -->
                            <div class="col-md-4">
                                <label class="form-label">Pago al Ingreso</label>
                                <input type="number" name="pago_completo" class="form-control" placeholder="Ej: 150000" min="0" step="1000" required>
                            </div>
                            <div class="mb-1">
                            <label for="exampleTextarea" class="form-label">observaciones:</label>
                              <textarea class="form-control" name="observaciones" id="observaciones" rows="2" placeholder="Observaciones del pago..."></textarea>
                            </div>

                           


                            <!-- Botón -->
                            <div class="col-12 text-end mt-3">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bi bi-cash-stack me-2"></i>Guardar Pago
                                </button>
                            </div>
                        </form>
                    </div>
                  <?php endif; ?>
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
            <!-- Historial de pagos: SIEMPRE visible -->
            <!-- Acompañantes de la Reserva -->
              <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                  Acompañantes de la Reserva
                </div>
                <div class="card-body">
                  <?php if (count($acompanantes) > 0): ?>
                    <div class="table-responsive">
                      <table class="table table-bordered table-sm">
                        <thead class="table-light">
                          <tr>
                            <th>Nombre</th>
                            <th>Documento</th>
<<<<<<< HEAD
                            <th>Parentesco</th>
=======
                            <th>Edad</th>
>>>>>>> 6c41453f82896d91c2aab7ee8e1416c219b13dfc
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($acompanantes as $a): ?>
                            <tr>
                              <td><?= htmlspecialchars($a['nombre']) ?></td>
                              <td><?= htmlspecialchars($a['documento']) ?></td>
<<<<<<< HEAD
                              <td><?= htmlspecialchars($a['parentesco']) ?></td>
=======
                              <td><?= htmlspecialchars($a['edad']) ?></td>
>>>>>>> 6c41453f82896d91c2aab7ee8e1416c219b13dfc
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <div class="alert alert-warning mb-0">No hay acompañantes registrados para esta reserva.</div>
                  <?php endif; ?>
                </div>
              </div>
            <!-- Acompañantes de la Reserva -->
            <?php else: ?>
              <div class="alert alert-danger">❌ No se encontró la reserva.</div>
              <a href="aloj_reservas_listado.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver a Reservas
              </a>
            <?php endif; ?>

            
      </body>
    </main>
  </div>
</html>