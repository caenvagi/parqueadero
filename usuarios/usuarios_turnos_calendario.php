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
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require '../logs/head.php'; ?>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
  <style>
    #calendar {
      max-width: 900px;
      margin: 40px auto;
    }

    #turnoModal,
    #crearModal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    #turnoModal>div,
    #crearModal>div {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      width: 350px;
      position: relative;
    }

    #cerrarModal,
    #cerrarCrear {
      position: absolute;
      top: 5px;
      right: 10px;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <?php require '../logs/nav-bar.php'; ?>
  <div id="layoutSidenav_content">
    <main>
      <div class="">
        <h2 style="text-align:center;">Calendario de Turnos</h2>

        <label style="margin-left: 20px;">Empleado:
          <select id="usuario_id">
            <option value="">Todos</option>
          </select>
        </label>

        <div id="calendar"></div>

        <!-- Modal Detalle Turno -->
        <div id="turnoModal">
          <div>
            <span id="cerrarModal">✖</span>
            <h3>Detalle del Turno</h3>
            <input type="hidden" id="id_turno">
            <p><strong>Turno id:</strong> <span id="modalTurnoId"></span></p>
            <p><strong>Empleado:</strong> <span id="modalEmpleado"></span></p>
            <p><strong>Inicio:</strong> <span id="modalInicio"></span></p>
            <p><strong>Fin:</strong> <span id="modalFin"></span></p>
            <p><strong>Valor:</strong>$<span id="modalValor"></span></p>
            <button id="editarTurnoBtn">Editar</button>
            <button id="eliminarTurnoBtn">Eliminar</button>
          </div>
        </div>

        <!-- Modal Crear Turno -->
        <div id="crearModal">
          <div>
            <span id="cerrarCrear">✖</span>
            <h3>Crear Turno</h3>
            <form id="formCrearTurno">
              <label>Empleado:</label><br>
              <select name="usuario_id" id="nuevo_usuario_id" required style="width:100%; margin-bottom:8px;"></select><br>

              <label>Inicio:</label><br>
              <input type="datetime-local" name="inicio" required style="width:100%;"><br>

              <label>Fin:</label><br>
              <input type="datetime-local" name="fin" required style="width:100%;"><br>

              <label>Valor:</label><br>
              <input type="number" name="valor" required style="width:100%;"><br><br>

              <button type="submit">Guardar Turno</button>
            </form>
          </div>
        </div>
        <!-- Modal Crear Turno -->
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
              slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
              }, //se visualizara de esta manera 01:00 AM en la columna de horas
              eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
              }, //y este código se visualizara de la misma manera pero en el titulo del evento creado en fullcalendar
              initialView: 'dayGridMonth',
              locale: 'es',
              headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
              },
              events: fetchTurnos,
              eventClick: function(info) {
               
                const [nombre, valor] = info.event.title.split(' - $');
                document.getElementById('modalEmpleado').textContent = nombre;
                document.getElementById('modalInicio').textContent = new Date(info.event.start).toLocaleString();
                document.getElementById('modalFin').textContent = new Date(info.event.end).toLocaleString();
                document.getElementById('modalValor').textContent = valor;
                // Mostrar el ID del turno
                document.getElementById('modalTurnoId').textContent = info.event.id;
                //console.log(info.event.id);
                document.getElementById('turnoModal').style.display = 'flex';
                const id_turno = document.getElementById('modalTurnoId').textContent = info.event.id;
              },
              
              dateClick: function(info) {
                const inicioInput = document.querySelector('#formCrearTurno [name="inicio"]');
                const finInput = document.querySelector('#formCrearTurno [name="fin"]');
                const fecha = info.dateStr;
                inicioInput.value = fecha + "T08:00";
                finInput.value = fecha + "T17:00";
                document.getElementById('crearModal').style.display = 'flex';
              }
            });

            calendar.render();

            const select = document.getElementById('usuario_id');
            const nuevoUsuarioSelect = document.getElementById('nuevo_usuario_id');

            fetch('usuarios_turnos_obteneruser.php')
              .then(res => res.json())
              .then(data => {
                data.forEach(usuario => {
                  const opt = document.createElement('option');
                  opt.value = usuario.id;
                  opt.textContent = usuario.nombre;
                  select.appendChild(opt.cloneNode(true));
                  nuevoUsuarioSelect.appendChild(opt);
                });
              });

            select.addEventListener('change', () => {
              calendar.refetchEvents();
            });

            function fetchTurnos(info, successCallback, failureCallback) {
              const usuarioId = document.getElementById('usuario_id').value;
              const url = `usuarios_turnos_eventos.php?start=${info.startStr}&end=${info.endStr}&usuario_id=${usuarioId}`;
              fetch(url)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
            }

            // Cerrar modales
            document.getElementById('cerrarModal').onclick = () => {
              document.getElementById('turnoModal').style.display = 'none';
            };
            document.getElementById('cerrarCrear').onclick = () => {
              document.getElementById('crearModal').style.display = 'none';
            };

            window.onclick = e => {
              if (e.target.id === "turnoModal") document.getElementById('turnoModal').style.display = 'none';
              if (e.target.id === "crearModal") document.getElementById('crearModal').style.display = 'none';
            };

            document.getElementById('formCrearTurno').addEventListener('submit', function(e) {
              e.preventDefault();
              const formData = new FormData(this);

              fetch('usuarios_turnos_crear.php', {
                  method: 'POST',
                  body: formData
                })
                .then(res => res.text())
                .then(res => {
                  if (res === "ok") {
                    alert("Turno creado correctamente");
                    document.getElementById('crearModal').style.display = 'none';
                    calendar.refetchEvents();
                  } else {
                    alert("Error: " + res);
                  }
                })
                .catch(err => alert("Error al crear turno"));
            });
             document.getElementById('editarTurnoBtn').addEventListener('click', function() {
              let turno_id = document.getElementById("modalTurnoId").textContent;
              fetchTurnoParaEdicion(turno_id);
              
          });
          
          function fetchTurnoParaEdicion(turno_id) {
            console.log(turno_id);
            fetch(`usuarios_turnos_obtener.php?id_turno=${turno_id}`)

              .then(res => res.json())
              .then(data => {
                document.getElementById('modalId').value = data.id_turno;
                document.getElementById('nuevo_usuario_id').value = data.usuario_id;
                document.querySelector('#formCrearTurno [name="inicio"]').value = data.inicio;
                document.querySelector('#formCrearTurno [name="fin"]').value = data.fin;
                document.querySelector('#formCrearTurno [name="valor"]').value = data.valor;
                document.getElementById('crearModal').style.display = 'flex';
              });
          }
          document.getElementById('eliminarTurnoBtn').addEventListener('click', function() {
            const turnoId = id_turno;
            if (!turnoId) {
              alert("ID de turno no disponible");
              return;
            }
            if (confirm("¿Seguro que quieres eliminar este turno?")) {
              eliminarTurno(turnoId);
            }
          });

          function eliminarTurno(turnoId) {
            
            let turno_id = document.getElementById("modalTurnoId").textContent;
            
            fetch(`usuarios_turnos_eliminar.php?id_turno=${turno_id}`, {
                method: 'POST'                
              })
              //console.log(`usuarios_turnos_eliminar.php?id_turno=${id_turno}`)
              .then(res => res.text())
              .then(data => {
                if (data === "ok") {
                  //console.log(data);
                  alert("Turno eliminado");
                  calendar.refetchEvents();
                  document.getElementById('turnoModal').style.display = 'none';
                } else {
                  alert("Error al eliminar el turno");
                }
              });
          }
          });
         
        </script>
      </div>
    </main>
  </div>
</body>

</html>