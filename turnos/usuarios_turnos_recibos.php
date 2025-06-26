<?php
session_start();
require_once "../conexion/conexion.php";

if (!isset($_SESSION['id'])) {
  header("Location: index.php");
}
$id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

if ($tipo_usuario == 1) {
  $where = "";
} else if ($tipo_usuario == 2) {
  $where = "WHERE id=$id";
}

$id = $_GET['id'];

$sql = "SELECT t.id_turno, t.usuario_id, u.nombre, t.fecha_inicio, t.fecha_fin, t.valor
        FROM usuarios_turnos t
        JOIN usuarios u ON t.usuario_id = u.id
        WHERE t.pagado = 0 and u.id = $id
        ORDER BY t.usuario_id, t.fecha_inicio";

$stmt = $pdo->query($sql);
$turnos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require '../logs/head.php'; ?>
  <?php require '../logs/datatables.php'; ?>
</head>

<body>
  <?php require '../logs/nav-bar.php'; ?>
  <div id="layoutSidenav_content">
    <main>
      <div class="card m-2">
        <div class="card-header">Turnos Por Pagar</div>
        <form id="form-turnos" method="POST" action="previsualizar_recibo.php">
          <table id="tabla-turnos" class="table m-2">
            <thead>
              <tr>
                <th>Usuario</th>
                <th>Fecha inicio</th>
                <th>Fecha fin</th>
                <th>Valor</th>
                <th class="text-center">Seleccionar</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($turnos as $row): ?>

                <tr>
                  <td><?= htmlspecialchars($row['nombre']) ?></td>
                  <td><?= $row['fecha_inicio'] ?></td>
                  <td><?= $row['fecha_fin'] ?></td>
                  <td><?= $row['valor'] ?></td>
                  <td class="form-check-input text-center"><input type="checkbox" name="turnos[]" value="<?= $row['id_turno'] ?>"></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="3" class="text-end">Total:</th>
                <th id="total-valor"></th>
                <th></th>
              </tr>
            </tfoot>
          </table>
          <br>
          <div class="text-center mb-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-receipt"></i> Generar Recibo
            </button>
          </div>
      </div>
    </main>
    <!-- datatable lista usuarios -->
    <script>
      $(document).ready(function() {
        $('#tabla-turnos').DataTable({
          columnDefs: [{
            targets: 3,
            render: function(data, type, row) {
              return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
              }).format(data);
            }
          }],
          responsive: true,
          dom: 'Bfrtilp',
          language: {
            // ... tu configuración actual
          },
          buttons: [
            // ... tus botones
          ],
          order: [
            [0, "asc"]
          ],
          pageLength: 25,

          footerCallback: function(row, data, start, end, display) {
            var api = this.api();

            // Remover formato y convertir a número
            var intVal = function(i) {
              return typeof i === 'string' ?
                parseInt(i.replace(/[\$,\.]/g, '')) || 0 :
                typeof i === 'number' ?
                i :
                0;
            };

            // Total en todas las páginas
            var total = api
              .column(3)
              .data()
              .reduce(function(a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            // Total en la página actual
            var pageTotal = api
              .column(3, {
                page: 'current'
              })
              .data()
              .reduce(function(a, b) {
                return intVal(a) + intVal(b);
              }, 0);

            // Actualizar el footer
            $(api.column(3).footer()).html(
              new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
              }).format(pageTotal)
            );
          }
        });


      });
    </script>
    <!-- datatable lista usuarios -->
</body>

</html>