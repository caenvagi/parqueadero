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
    #crearModal,
    #editarModal {
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
    #crearModal>div,
    #EditarModal>div {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      width: 350px;
      position: relative;
    }

    #cerrarModal,
    #cerrarCrear,
    #editarModal {
      position: absolute;
      top: 5px;
      right: 10px;
      cursor: pointer;
    }

    #crearModal {
      z-index: 1050 !important;
    }

    #turnoModal {
      z-index: 1050 !important;
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
              <label>Empleado:</label>
              <select class="form-control" name="usuario_id" id="nuevo_usuario_id" style="width:100%; margin:0px;" required autofocus></select><br>

              <label style="width:100%;margin:0px;">Inicio:</label>
              <input class="form-control" type="datetime-local" name="inicio" required style="width:100%;margin:0px;"><br>

              <label>Fin:</label>
              <input class="form-control" type="datetime-local" name="fin" required style="width:100%;margin:0px;"><br>

              <label style="margin-bottom:0px;">Valor:</label>
              <input class="form-control" type="number" name="valor" required style="width:100%; margin:0px;"><br><br>

              <button type="submit" class="btn btn-secondary btn btn-block">Guardar Turno</button>
            </form>
          </div>
        </div>
        <!-- Modal Crear Turno -->
        <!-- Modal Editar Turno -->
        <div id="editarModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); justify-content: center; align-items: center; z-index: 1050;">
          <div style="background: #fff; padding: 20px; border-radius: 10px; width: 350px; position: relative;">
            <span id="cerrarEditar">✖</span>
            <h3>Editar Turno</h3>
            <form id="formEditarTurno">
              <label>id turno:</label><br>
              <input type="text" name="id_turno" id="edit_id_turno"><br>
              <label>Empleado:</label><br>
              <select name="usuario_id" id="edit_usuario_id" required style="width:100%; margin-bottom:8px;"></select><br>
              <label>Inicio:</label><br>
              <input type="datetime-local" name="inicio" id="edit_inicio" required style="width:100%;"><br>
              <label>Fin:</label><br>
              <input type="datetime-local" name="fin" id="edit_fin" required style="width:100%;"><br>
              <label>Valor:</label><br>
              <input type="number" name="valor" id="edit_valor" required style="width:100%;"><br><br>
              <button type="submit">Guardar Cambios</button>
            </form>
          </div>
        </div>


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

                const opcionesFormato = {
                  day: '2-digit',
                  month: '2-digit',
                  year: 'numeric',
                  hour: 'numeric',
                  minute: '2-digit',
                  hour12: true // Esto activa el "a. m." / "p. m."
                };
                const inicio = new Date(info.event.start).toLocaleString('es-ES', opcionesFormato);
                const fin = new Date(info.event.end).toLocaleString('es-ES', opcionesFormato);

                document.getElementById('modalInicio').textContent = inicio;
                document.getElementById('modalFin').textContent = fin;
                document.getElementById('modalValor').textContent = valor;
                document.getElementById('modalTurnoId').textContent = info.event.id;
                document.getElementById('turnoModal').style.display = 'flex';
                const id_turno = document.getElementById('modalTurnoId').textContent = info.event.id;
              },

              dateClick: function(info) {
                const inicioInput = document.querySelector('#formCrearTurno [name="inicio"]');
                const finInput = document.querySelector('#formCrearTurno [name="fin"]');
                const valorInput = document.querySelector('#formCrearTurno [name="valor"]');
                const fecha = info.dateStr;
                inicioInput.value = fecha + "T19:00";
                finInput.value = fecha + "T07:00";
                valorInput.value = "65000";
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
            document.getElementById('cerrarEditar').onclick = () => {
              document.getElementById('editarModal').style.display = 'none';
            };

            window.onclick = e => {
              if (e.target.id === "turnoModal") document.getElementById('turnoModal').style.display = 'none';
              if (e.target.id === "crearModal") document.getElementById('crearModal').style.display = 'none';
              if (e.target.id === "editarModal") document.getElementById('editarModal').style.display = 'none';
            };

            //crear turno
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
                    Swal.fire({
                      icon: 'success',
                      title: 'Turno creado',
                      text: 'El turno se ha creado correctamente.'
                    }).then(() => {
                      document.getElementById('crearModal').style.display = 'none';
                      calendar.refetchEvents();
                    });
                  } else {
                    Swal.fire({
                      icon: 'error',
                      title: 'Error al crear turno',
                      text: res
                    });
                  }
                })
                .catch(err => {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al crear el turno. Intenta nuevamente.'
                  });
                });
            });
            //crear turno     
            //editar turno
            // Llenar modal de edición
            // Función para convertir objeto Date a datetime-local string

            function convertirFechaParaDatetime(fechaTexto) {
              // Eliminar espacios invisibles y comas
              fechaTexto = fechaTexto.replace(/\u200e/g, '').replace(',', '').trim();

              const regex = /^(\d{2})\/(\d{2})\/(\d{4}) (\d{1,2}):(\d{2}) (a\. m\.|p\. m\.)$/i;
              const match = fechaTexto.match(regex);

              if (!match) return '';

              let [, dia, mes, anio, hora, minutos, ampm] = match;

              hora = parseInt(hora, 10);
              minutos = parseInt(minutos, 10);

              // Ajustar hora según AM/PM
              if (ampm.toLowerCase().includes('p') && hora < 12) {
                hora += 12;
              } else if (ampm.toLowerCase().includes('a') && hora === 12) {
                hora = 0;
              }

              // Formato "YYYY-MM-DDTHH:MM"
              return `${anio}-${mes}-${dia}T${String(hora).padStart(2, '0')}:${String(minutos).padStart(2, '0')}`;
            }
            // Abrir modal editar y cargar datos
            document.getElementById('editarTurnoBtn').addEventListener('click', function() {
              const id = document.getElementById('modalTurnoId').textContent;
              const empleado = document.getElementById('modalEmpleado').textContent;

              const inicioText = document.getElementById('modalInicio').textContent;
              const finText = document.getElementById('modalFin').textContent;
              const valor = document.getElementById('modalValor').textContent;

              document.getElementById('edit_id_turno').value = id;
              document.getElementById('edit_inicio').value = convertirFechaParaDatetime(inicioText);
              document.getElementById('edit_fin').value = convertirFechaParaDatetime(finText);
              document.getElementById('edit_valor').value = valor;

              console.log(inicioText);
              // Cargar select de usuarios si aún no está
              const editarUsuarioSelect = document.getElementById('edit_usuario_id');
              editarUsuarioSelect.innerHTML = '';
              fetch('usuarios_turnos_obteneruser.php')
                .then(res => res.json())
                .then(data => {
                  data.forEach(usuario => {
                    const opt = document.createElement('option');
                    opt.value = usuario.id;
                    opt.textContent = usuario.nombre;
                    if (usuario.nombre === empleado) {
                      opt.selected = true;
                    }
                    editarUsuarioSelect.appendChild(opt);
                  });
                });

              document.getElementById('turnoModal').style.display = 'none';
              document.getElementById('editarModal').style.display = 'flex';
            });

            // Cerrar modal editar
            document.getElementById('cerrarEditar').onclick = () => {
              document.getElementById('editarModal').style.display = 'none';
            };

            // Envío del formulario editar
            document.getElementById('formEditarTurno').addEventListener('submit', function(e) {
              e.preventDefault();
              const formData = new FormData(this);
              fetch('usuarios_turnos_editar.php', {
                  method: 'POST',
                  body: formData
                })
                .then(res => res.text())
                .then(res => {
                  console.log("Respuesta del servidor:", res); // Ver esto en la consola del navegador
                  if (res === "ok") {
                    Swal.fire({
                      icon: 'success',
                      title: 'Turno actualizado',
                      text: 'El turno fue modificado correctamente.'
                    }).then(() => {
                      document.getElementById('editarModal').style.display = 'none';
                      calendar.refetchEvents();
                    });
                  } else {
                    Swal.fire({
                      icon: 'error',
                      title: 'Error',
                      text: 'No se pudo actualizar el turno.'
                    });
                  }
                });
            });
            //editar turno
            // //eliminar turno
            document.getElementById('eliminarTurnoBtn').addEventListener('click', function() {
              const turnoId = id_turno;
              if (!turnoId) {
                Swal.fire({
                  icon: 'warning',
                  title: 'ID no disponible',
                  text: 'No se puede eliminar el turno porque falta el ID.'
                });
                return;
              }

              // Confirmación con SweetAlert2
              Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el turno permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
              }).then((result) => {
                if (result.isConfirmed) {
                  eliminarTurno(turnoId);
                }
              });
            });

            function eliminarTurno(turnoId) {
              const btnEliminar = document.getElementById('eliminarTurnoBtn');

              // Guardar texto original y actualizar botón
              const originalText = btnEliminar.innerHTML;
              btnEliminar.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Eliminando...`;
              btnEliminar.disabled = true;

              let turno_id = document.getElementById("modalTurnoId").textContent;

              fetch(`usuarios_turnos_eliminar.php?id_turno=${turno_id}`, {
                  method: 'POST'
                })
                .then(res => res.text())
                .then(data => {
                  if (data === "ok") {
                    Swal.fire({
                      icon: 'success',
                      title: 'Turno eliminado',
                      text: 'El turno fue eliminado correctamente.'
                    }).then(() => {
                      calendar.refetchEvents();
                      document.getElementById('turnoModal').style.display = 'none';
                    });
                  } else {
                    Swal.fire({
                      icon: 'error',
                      title: 'Error',
                      text: 'No se pudo eliminar el turno.'
                    });
                  }
                })
                .catch(error => {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error de red',
                    text: 'Ocurrió un problema al intentar eliminar el turno.'
                  });
                })
                .finally(() => {
                  // Restaurar estado del botón
                  btnEliminar.innerHTML = originalText;
                  btnEliminar.disabled = false;
                });
            }
            //eliminar turno
          });
        </script>
      </div>
    </main>
  </div>
</body>

</html>