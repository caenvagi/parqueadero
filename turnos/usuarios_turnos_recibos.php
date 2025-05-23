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

$sql = "SELECT t.id_turno, t.usuario_id, u.nombre, t.fecha_inicio, t.fecha_fin, t.valor
        FROM usuarios_turnos t
        JOIN usuarios u ON t.usuario_id = u.id
        WHERE t.pagado = 0
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
          </table>
          <br>
          <button type="submit">Generar Recibo</button>
        </form>
      </div>
    </main>
    <!-- datatable lista usuarios -->
    <script>
      $(document).ready(function() {
      $('#tabla-turnos').DataTable({
        columnDefs: [{
          targets: 3, // Índice de la columna que deseas formatear
          render: function(data, type, row) {
            // Formatear como moneda (ejemplo en pesos colombianos)
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
          "decimal": "",
          "emptyTable": "No hay información",
          "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
          "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
          "infoFiltered": "(Filtrado de _MAX_ total entradas)",
          "infoPostFix": "",
          "thousands": ",",
          "lengthMenu": "Mostrar _MENU_ Entradas",
          "loadingRecords": "Cargando...",
          "processing": "Procesando...",
          "search": "Buscar:",
          "zeroRecords": "Sin resultados encontrados",
          "paginate": {
            "first": "Primero",
            "last": "Ultimo",
            "next": "Siguiente",
            "previous": "Anterior"
          },
        },
        buttons: [{
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-x"></i> ',
            titleAttr: 'Exportar a Excel',
            className: 'btn btn-success'
          },
          {
            extend: 'pdfHtml5',
            text: '<i class="bi bi-file-earmark-pdf"></i> ',
            titleAttr: 'Exportar a PDF',
            className: 'btn btn-danger'
          },
          {
            extend: 'print',
            text: '<i class="bi bi-printer"></i> ',
            titleAttr: 'Imprimir',
            className: 'btn btn-info'
          },
        ],
        "order": [
          [0, "asc"]
        ],
        'pageLength': 25,

        // createdRow: function(row, data, dataIndex) {
        //     const estado = $('td:eq(4)', row).attr('data-activo');
        //     if (estado === "0") {
        //         $(row).addClass('fila-inactiva');
        //     }
        // }

      });

      });
    </script>
    <!-- datatable lista usuarios -->
</body>