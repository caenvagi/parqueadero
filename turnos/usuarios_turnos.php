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
</head>

<body>
    <?php require '../logs/nav-bar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
        <div class="d-flex justify-content-between">
            <div class="card m-3 border-4 rounded-3 col-md-6">
                <div class="card-header">
                    <h5 class="modal-title" id="modalTitleId"><i class="fa fa-user-circle" style='font-size:24px'></i>&nbsp;&nbsp;Asignar Turno</h5>
                </div>
                <div class="card-body">
                    <form id="turnoForm">
                        <div class="mb-0 mt-0 pt-0">
                            <label class="form-label"></label>
                            <div class="input-group mb-1">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="inputGroupSelect01">Empleado:&nbsp;</label>
                                </div>
                                <select class="form-select custom-select" name="usuario_id" id="usuario_id" required autofocus></select>
                            </div>
                        </div>
                        <div class="card mb-2 mt-3 p-2 border-1 rounded-3">
                            <div class="input-group mb-1">
                                <div id="turnosEmpleado"></div>
                            </div>
                        </div>

                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Fecha Inicio&nbsp;</span>
                            </div>
                            <input type="date" class="form-control" name="fecha_inicio" placeholder="fecha_inicio" aria-label="fecha_inicio" aria-describedby="basic-addon1" minlength="5" required autofocus>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Hora Inicio&nbsp;</span>
                            </div>
                            <input type="time" class="form-control" name="hora_inicio" placeholder="hora_inicio" aria-label="hora_inicio" aria-describedby="basic-addon1" minlength="5" required autofocus>
                        </div>

                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Fecha Fin&nbsp;</span>
                            </div>
                            <input type="date" class="form-control" name="fecha_fin" placeholder="fecha_fin" aria-label="fecha_fin" aria-describedby="basic-addon1" minlength="5" required autofocus>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Hora Fin&nbsp;</span>
                            </div>
                            <input type="time" class="form-control" name="hora_fin" placeholder="hora_fin" aria-label="hora_fin" aria-describedby="basic-addon1" minlength="5" required autofocus>
                        </div>

                        <!-- <label>Fecha Inicio:</label>
                        <input type="date" name="fecha_inicio" required>
                        <label>Hora Inicio:</label>
                        <input type="time" name="hora_inicio" required><br>

                        <label>Fecha Fin:</label>
                        <input type="date" name="fecha_fin" required>
                        <label>Hora Fin:</label>
                        <input type="time" name="hora_fin" required><br> -->

                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">Valor $&nbsp;</span>
                            </div>
                            <input type="number" class="form-control" name="valor" placeholder="valor" aria-label="valor" aria-describedby="basic-addon1" minlength="5" required autofocus>
                        </div>

                        <!-- <label>Valor ($):</label>
                        <input type="number" name="valor" step="0.01" required><br> -->

                        <!-- <button type="submit">Asignar Turno</button> -->

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-secondary btn btn-block" name="" href=""><i class="bi bi-plus-lg text-white">&nbsp;GUARDAR</i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card m-3 border-4 rounded-3 col-md-5">
                <div class="card-header">
                    <h3>Turnos Asignados (Todos)</h3>
                </div>
                <div class="card-body">
                    <div id="turnos"></div>
                </div>
            </div>
        </div>


    <script>
        const selectEmpleado = document.getElementById('usuario_id');

        // Cargar lista de usuarios
fetch('usuarios_turnos_obteneruser.php')
  .then(res => res.json())
  .then(data => {
    // Agregar la opción por defecto
    const optionInicial = document.createElement('option');
    optionInicial.value = '';
    optionInicial.textContent = 'Seleccione el empleado';
    optionInicial.disabled = true;
    optionInicial.selected = true;
    selectEmpleado.appendChild(optionInicial);

    // Agregar las opciones de los empleados
    data.forEach(usuario => {
      const option = document.createElement('option');
      option.value = usuario.id;
      option.textContent = usuario.nombre;
      selectEmpleado.appendChild(option);
    });
  });

            

        // Mostrar turnos de un empleado
        selectEmpleado.addEventListener('change', () => {
            const id = selectEmpleado.value;
            fetch(`usuarios_turnos_empleados.php?usuario_id=${id}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('turnosEmpleado').innerHTML = `
                        <h4>Turnos actuales de este empleado:</h4>
                        ${html}`;
                });
        });

        // Enviar formulario
        document.getElementById('turnoForm').addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            fetch('usuarios_turnos_guardar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(msg => {
                    alert(msg);
                    e.target.reset();
                    document.getElementById('usuario_id').dispatchEvent(new Event('change'));
                    cargarTurnos();
                });
        });

        // Mostrar todos los turnos
        function cargarTurnos() {
            fetch('usuarios_turnos_listar.php')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('turnos').innerHTML = html;
                });
        }

        cargarTurnos();
    </script>

    </main>
    </div>
</body>

</html>