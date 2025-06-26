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

  <link href="../modulos/sweetalert/sweetalert2.min.css" rel="stylesheet">
  <script src="../modulos/sweetalert/sweetalert2.all.min.js"></script></style>

  <!-- jsPDF y AutoTable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<!-- SheetJS (XLSX) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

</head>

<body>
  <?php require '../logs/nav-bar.php'; ?>
  <div id="layoutSidenav_content">
    <main>
      <div class="">
        <h2 style="text-align:center;">Calendario de Turnos</h2>

        <div class="text-center my-3">
          <button id="btnImprimirCalendario" class="btn btn-secondary">🖨️ Imprimir Calendario</button>
          <button id="btnExportPDF" class="btn btn-danger">Exportar PDF</button>
          <button id="btnExportExcel" class="btn btn-success">Exportar Excel</button>
          <button hidden id="btnImprimir" class="btn btn-secondary">Imprimir</button>
        </div>

        <label style="margin-left: 20px;">Empleado:
          <select id="usuario_id">
            <option value="">Todos</option>
          </select>
        </label>

        <h1 id="tituloMesImpresion" class="d-none-print" style="text-align:center; font-size:32px; margin-bottom: 10px;"></h1>

        <div id="calendar" class="col col-xl-12 col-md-12"></div>

        <!-- Modal Detalle Turno -->
        <div id="turnoModal">
          <div>
            <span id="cerrarModal">✖</span>
            <h3>Detalle del Turno</h3>
            <input type="hidden" id="id_turno">
            <p><strong>Empleado:</strong> <span id="modalEmpleado"></span></p>
            <p><strong>Inicio:</strong> <span id="modalInicio"></span></p>
            <!-- <p><strong>Fin visible:</strong> <span id="modalFin"></span></p> -->
            <p><strong>Fin:</strong> <span id="modalHoraRealFin"></span></p>
            <p><strong>Valor:</strong> $<span id="modalValor"></span></p>
            <p><strong>Estado:</strong> <span id="modalEstado"></span></p>
            <p style="display: none;"><span id="modalTurnoId"></span></p>
            <!-- Editar botón envuelto para tooltip -->
            <span id="tooltipEditarWrapper" class="tooltip-wrapper" data-bs-toggle="tooltip" title="">
              <button id="editarTurnoBtn" class="btn btn-secondary" disabled>Editar</button>
            </span>

            <!-- Eliminar botón envuelto para tooltip -->
            <span id="tooltipEliminarWrapper" class="tooltip-wrapper" data-bs-toggle="tooltip" title="">
              <button id="eliminarTurnoBtn" class="btn btn-danger" disabled>Eliminar</button>
            </span>

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
              <button type="submit" class="btn btn-secondary btn btn-block">Guardar Cambios</button>
            </form>
          </div>
        </div>

        <!-- Tabla oculta para exportación -->
         <!--  -->
          <table id="tablaTurnos" class="table table-bordered table-striped m-0 p-0" style="display:none;">
            <thead>
              <tr>
                <th>Empleado</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Valor</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>


    
        <script>
          document.addEventListener('DOMContentLoaded', function() {

            document.addEventListener('DOMContentLoaded', function () {
                          const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                          tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                            new bootstrap.Tooltip(tooltipTriggerEl);
                          });
                        });

                        


            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {

              initialView: 'dayGridMonth',
              firstDay: 1, // Establece el lunes como el primer día de la semana
              locale: 'es', // Opcional: para mostrar los días en español
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
              events:
              fetchTurnos,
                  eventClick: function(info) {
                  const props = info.event.extendedProps;

                  const nombre = props.nombre || 'Sin nombre';
                  const valor = props.valor || 0;
                  const pagado = props.pagado == 1;
                  const horaRealFin = props.hora_real_fin;

                  const opcionesFormato = {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                  };

                  const inicio = new Date(info.event.start).toLocaleString('es-CO', opcionesFormato);
                  const finVisible = new Date(info.event.end).toLocaleString('es-CO', opcionesFormato);
                  const finReal = horaRealFin
                    ? new Date(horaRealFin).toLocaleString('es-CO', opcionesFormato)
                    : finVisible;

                    const estadoEl = document.getElementById('modalEstado');

                  if (pagado) {
                    estadoEl.innerHTML = '<span class="badge bg-success">Pagado</span>';
                  } else {
                    estadoEl.innerHTML = '<span class="badge bg-danger">Por pagar</span>';
                  }

                  // Llenar campos del modal
                  document.getElementById('modalEmpleado').textContent = nombre;
                  document.getElementById('modalValor').textContent = valor.toLocaleString();
                  document.getElementById('modalInicio').textContent = inicio;
                  document.getElementById('modalHoraRealFin').textContent = finReal;
                  document.getElementById('modalTurnoId').textContent = info.event.id;

                  const btnEditar = document.getElementById('editarTurnoBtn');
                  const btnEliminar = document.getElementById('eliminarTurnoBtn');

                  const wrapperEditar = document.getElementById('tooltipEditarWrapper');
                  const wrapperEliminar = document.getElementById('tooltipEliminarWrapper');

                  if (pagado) {
                    btnEditar.disabled = true;
                    btnEliminar.disabled = true;

                    wrapperEditar.setAttribute('title', 'Este turno ya fue pagado y no se puede editar');
                    wrapperEliminar.setAttribute('title', 'Este turno ya fue pagado y no se puede eliminar');

                    wrapperEditar.setAttribute('data-bs-toggle', 'tooltip');
                    wrapperEliminar.setAttribute('data-bs-toggle', 'tooltip');
                  } else {
                    btnEditar.disabled = false;
                    btnEliminar.disabled = false;

                    wrapperEditar.removeAttribute('title');
                    wrapperEliminar.removeAttribute('title');
                    wrapperEditar.removeAttribute('data-bs-toggle');
                    wrapperEliminar.removeAttribute('data-bs-toggle');
                  }


                  // Mostrar modal
                  document.getElementById('turnoModal').style.display = 'flex';

                  const tooltipList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                  tooltipList.forEach(function (el) {
                    new bootstrap.Tooltip(el);
                  });                  
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
                  },
                  eventDidMount: function(info) {
                      if (info.event.display === 'background') return;

                      const nombre = info.event.extendedProps.nombre || 'Sin nombre';
                      const valor = info.event.extendedProps.valor || 0;
                      const pagado = info.event.extendedProps.pagado == 1;
                      const horaRealFin = info.event.extendedProps.hora_real_fin;

                      const estadoTexto = pagado ? 'Pagado' : 'Por pagar';
                      const estadoClase = pagado ? 'text-success' : 'text-danger';
                      const borderColor = pagado ? '#28a745' : '#dc3545'; // verde o rojo

                      const horaInicio = new Date(info.event.start).toLocaleTimeString('es-CO', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                      });
                      const horaFin = horaRealFin
                        ? new Date(horaRealFin).toLocaleTimeString('es-CO', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                          })
                        : '';
                     
                        
                      info.el.innerHTML = `
                        <div style="font-size: 0.85rem">
                          <strong>${nombre}</strong><br>
                          <span><i class="bi bi-clock"></i> ${horaInicio}</span> <br>
                           a &nbsp; <span>${horaFin}</span><br>
                          <span class="${estadoClase} fw-bold">${estadoTexto}</span>
                        </div>
                      `;

                      // Estilo visual del evento
                      info.el.style.backgroundColor = 'transparent';
                      info.el.style.border = `1px solid ${borderColor}`;
                      info.el.style.borderRadius = '6px';
                      info.el.style.padding = '2px';
                      info.el.style.color = 'inherit';

                      // Popover
                      const opciones = {
                        day: '2-digit', month: '2-digit', year: 'numeric',
                        hour: '2-digit', minute: '2-digit', hour12: true
                      };
                      const inicio = new Date(info.event.start).toLocaleString('es-CO', opciones);

                      const finReal = info.event.extendedProps.hora_real_fin;
                      const fin = finReal
                        ? new Date(finReal).toLocaleString('es-CO', opciones)
                        : new Date(info.event.end).toLocaleString('es-CO', opciones);


                      info.el.setAttribute('data-bs-toggle', 'popover');
                      info.el.setAttribute('data-bs-trigger', 'hover focus');
                      info.el.setAttribute('data-bs-placement', 'top');
                      info.el.setAttribute('title', nombre);
                      info.el.setAttribute('data-bs-html', 'true');
                      info.el.setAttribute('data-bs-content', `
                        <strong>Inicio:</strong> ${inicio}<br>
                        <strong>Fin:</strong> ${fin}<br>
                        <strong>Valor:</strong> $${valor.toLocaleString()}<br>
                        <strong>Estado:</strong> ${estadoTexto}
                      `);

                      new bootstrap.Popover(info.el);
                    }





                });

                function actualizarTablaTurnos(calendar) {
  const eventos = calendar.getEvents();
  const tbody = document.querySelector('#tablaTurnos tbody');
  tbody.innerHTML = '';

  eventos.forEach(evento => {
    const props    = evento.extendedProps;
    const nombre   = props.nombre || evento.title;
    const valorNum = props.valor ? parseInt(props.valor) : 0;

    // Fecha real de inicio y fin
    const inicioReal = evento.start;
    const finReal    = props.hora_real_fin 
      ? new Date(props.hora_real_fin) 
      : evento.end;

    // Creamos la fila
    const fila = document.createElement('tr');
    fila.innerHTML = `
      <td>${nombre}</td>
      <td data-fecha-inicio="${inicioReal.toISOString()}">
        ${inicioReal.toLocaleString('es-CO')}
      </td>
      <td data-fecha-fin="${finReal.toISOString()}">
        ${finReal.toLocaleString('es-CO')}
      </td>
      <td data-valor="${valorNum}">$${valorNum.toLocaleString('es-CO')}</td>
      <td>${props.pagado == 1 ? 'Pagado' : 'Por pagar'}</td>
    `;
    tbody.appendChild(fila);
  });
}


                  calendar.render();

                  

                 document.getElementById('btnImprimirCalendario').addEventListener('click', () => {
                      const fechaActual = calendar.getDate(); // obtiene el mes actual visible
                      const opciones = { month: 'long', year: 'numeric' };
                      const mesTexto = fechaActual.toLocaleDateString('es-CO', opciones);
                      const titulo = document.getElementById('tituloMesImpresion');
                      titulo.textContent = mesTexto.toUpperCase();
                      titulo.style.display = 'block'; // mostrarlo antes de imprimir

                      window.print();

                      // Luego ocultarlo de nuevo por si el usuario sigue navegando
                      setTimeout(() => {
                        titulo.style.display = 'none';
                      }, 1000);
                    });

                  // document.getElementById('btnImprimirCalendario').addEventListener('click', () => {
                  //     window.print();
                  //   });

                  // Función auxiliar para formatear la fecha como "YYYY-MM-DD"
                    function hoyComoString() {
                      const d = new Date();
                      const yyyy = d.getFullYear();
                      const mm   = String(d.getMonth() + 1).padStart(2, '0');
                      const dd   = String(d.getDate()).padStart(2, '0');
                      return `${yyyy}${mm}${dd}`;
                    }

                              // 👉 Eventos para exportar
                  // Exportar PDF con fecha en el nombre
                    document.getElementById('btnExportPDF').addEventListener('click', () => {
                      actualizarTablaTurnos(calendar);
                      const { jsPDF } = window.jspdf;
                      const doc = new jsPDF();
                      doc.text("Turnos del mes actual", 14, 15);
                      doc.autoTable({ html: '#tablaTurnos', startY: 20 });

                      const fecha = hoyComoString();  
                      doc.save(`turnos_${fecha}.pdf`);
                    });

                  document.getElementById('btnExportExcel').addEventListener('click', () => {
  // Opciones para formatear con día de la semana, fecha y hora en 12 h
  const opciones = {
    weekday: 'long',   // día de la semana
    day:     '2-digit',
    month:   'long',
    hour:    '2-digit',
    minute:  '2-digit',
    hour12:  true
  };

  // Mapear los eventos visibles
  const data = calendar.getEvents().map(ev => {
    const props = ev.extendedProps;

    // formatea "martes 24 de junio 07:00 p. m."
    const inicio = ev.start.toLocaleString('es-CO', opciones);
    const finDate = props.hora_real_fin
      ? new Date(props.hora_real_fin)
      : ev.end;
    const fin = finDate.toLocaleString('es-CO', opciones);

    return {
      Empleado: props.nombre || ev.title,
      Inicio:   inicio,
      Fin:      fin,
      Valor:    props.valor ? parseInt(props.valor) : 0,
      Estado:   props.pagado == 1 ? 'Pagado' : 'Por pagar'
    };
  });

  // Genera el libro y la hoja
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.json_to_sheet(data);

  // Ajusta anchos de columna
  ws['!cols'] = [
    { wch: 20 }, // Empleado
    { wch: 30 }, // Inicio (más ancho para el día de la semana)
    { wch: 30 }, // Fin
    { wch: 10 }, // Valor
    { wch: 12 }  // Estado
  ];

  XLSX.utils.book_append_sheet(wb, ws, 'Turnos');
  const fecha = hoyComoString();
  XLSX.writeFile(wb, `turnos_${fecha}.xlsx`);
});
                  document.getElementById('btnImprimir').addEventListener('click', () => {
                    actualizarTablaTurnos(calendar);
                    const contenido = document.getElementById('tablaTurnos').outerHTML;
                    const ventana = window.open('', '', 'height=600,width=800');
                    ventana.document.write('<html><head><title>Imprimir Turnos</title>');
                    ventana.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">');
                    ventana.document.write('</head><body>');
                    ventana.document.write('<h3>Turnos visibles en calendario</h3>');
                    ventana.document.write(contenido);
                    ventana.document.write('</body></html>');
                    ventana.document.close();
                    ventana.print();
                  });


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
                      text: 'El turno se ha creado correctamente.',
                      customClass: {
                      container: 'customClassName'
                    }
                    }).then(() => {
                      document.getElementById('crearModal').style.display = 'none';
                      calendar.refetchEvents();
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 3000); // 3000 milisegundos = 3 segundos

                  } else {
                    Swal.fire({
                      icon: 'error',
                      title: 'Error al crear turno',
                      text: res,
                      customClass: {
                      container: 'customClassName'
                    }
                    });
                  }
                })
                .catch(err => {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al crear el turno. Intenta nuevamente.',
                    customClass: {
                      container: 'customClassName'
                    }
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
              const finText = document.getElementById('modalHoraRealFin').textContent;
              const valor = document.getElementById('modalValor').textContent;

              // ✅ Obtener el evento actual
              const evento = calendar.getEventById(id);
              const horaRealFin = evento.extendedProps.hora_real_fin;

              document.getElementById('edit_id_turno').value = id;
              document.getElementById('edit_inicio').value = convertirFechaParaDatetime(inicioText);

            // ✅ Usar hora real para el campo fin
            document.getElementById('edit_fin').value = horaRealFin
              ? horaRealFin.slice(0, 16)
              : convertirFechaParaDatetime(finText);

            document.getElementById('edit_valor').value = valor;

            // Cargar usuarios
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
                      text: 'El turno fue modificado correctamente.',
                      customClass: {
                      container: 'customClassName'
                      }
                    }).then(() => {
                      document.getElementById('editarModal').style.display = 'none';
                      calendar.refetchEvents();
                      setTimeout(function() {
                        location.reload();
                    }, 3000); // 3000 milisegundos = 3 segundos
                    });
                  } else {
                    Swal.fire({
                      icon: 'error',
                      title: 'Error',
                      text: 'No se pudo actualizar el turno.',
                      customClass: {
                      container: 'customClassName'
                      }
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
                  text: 'No se puede eliminar el turno porque falta el ID.',
                  customClass: {
                  container: 'customClassName'
                  }
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
                cancelButtonText: 'Cancelar',
                customClass: {
                container: 'customClassName'
                }
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