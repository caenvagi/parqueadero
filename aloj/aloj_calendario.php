<?php
require_once "../conexion/conexion.php";
session_start();
if (!isset($_SESSION['id'])) die("No autorizado.");

// Obtener habitaciones con su estado
$sql = "SELECT id, nombre, estado FROM aloj_habitaciones";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>  
    <?php require '../logs/head.php'; ?>
  <!-- FullCalendar CSS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">

  <!-- FullCalendar JS (debe estar antes de tu script) -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

  <!-- Idioma español -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.global.min.js"></script>
  
  <style>
    #calendar {
      max-width: 1000px;
      margin: 30px auto;
    }
  </style>
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
  <main class="m-3">
<body>
  <style>
  .badge {
    padding: 8px 12px;
    font-size: 0.9rem;
    color: white;
  }
</style>

<div class="d-flex justify-content-center flex-wrap gap-3 my-3">
  <div><span class="badge" style="background-color: #dc3545;">Ocupada</span></div>
  <div><span class="badge" style="background-color: #fd7e14;">Pendiente</span></div>
  <div><span class="badge" style="background-color: #6c757d;">Cancelada</span></div>
  <div><span class="badge" style="background-color: #198754;">Finalizada</span></div>
  <div><span class="badge" style="background-color: #0d6efd;">Otros</span></div>
</div>

<div class="container my-4">
  <div class="row justify-content-center g-3">
    <?php foreach ($habitaciones as $hab): ?>
      <?php
        // Determinar clase de color según estado
        switch (strtolower($hab['estado'])) {
          case 'ocupada':
            $badgeClass = 'bg-danger';
            break;
          case 'lista':
            $badgeClass = 'bg-success';
            break;
          case 'mantenimiento':
            $badgeClass = 'bg-warning text-dark';
            break;
          default:
            $badgeClass = 'bg-secondary';
        }
      ?>
      <div class="col-auto">
        <div class="card shadow-sm rounded-3 border-0" style="min-width: 160px;">
          <div class="card-body text-center">
            <h6 class="card-title mb-2"><?= htmlspecialchars($hab['nombre']) ?></h6>
            <span class="badge <?= $badgeClass ?> text-uppercase px-3 py-2">
              <?= ucfirst($hab['estado']) ?>
            </span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>




  <div id="calendar"></div>
  
  <!-- Modal para ver detalles -->
<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalReservaLabel">Detalles de Reserva</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p><strong>Reserva ID:</strong> <span id="reservaId"></span></p>
        <p><strong>Habitación:</strong> <span id="habitacion"></span></p>
        <p><strong>Estado:</strong> <span id="estado"></span></p>
        <p><strong>Desde:</strong> <span id="fechaInicio"></span></p>
        <p><strong>Hasta:</strong> <span id="fechaFin"></span></p>
      </div>
      <div class="modal-footer">
        <a id="editarBtn" class="btn btn-warning" href="#">Editar</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


  <!-- Tu script debe ir después de cargar FullCalendar -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var calendarEl = document.getElementById('calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek'
        },
        events: 'aloj_reservas_calendario.php',
        eventClick: function(info) {
  const reserva = info.event.extendedProps;

  document.getElementById('reservaId').textContent = reserva.id;
  document.getElementById('habitacion').textContent = reserva.habitacion;
  document.getElementById('estado').textContent = reserva.estado;
  document.getElementById('fechaInicio').textContent = info.event.startStr;
  document.getElementById('fechaFin').textContent = info.event.endStr;

  document.getElementById('editarBtn').href = `aloj_reservas_editar.php?id=${reserva.id}`;

  const modal = new bootstrap.Modal(document.getElementById('modalReserva'));
  modal.show();
},
         dateClick: function(info) {
  const fecha = info.dateStr;
  const confirmar = confirm(`¿Deseas crear una nueva reserva para el ${fecha}?`);
  if (confirmar) {
    window.location.href = `aloj_clientes.php?fecha=${fecha}`;
  }
}, 

      });
      calendar.render();
    });
  </script>
</body>
</main>
</div>
</html>
