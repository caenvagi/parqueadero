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

// Aquí puedes capturar el usuario logueado desde la sesión
$user_login = $_SESSION['id']; // Cambia según tu sistema

// Cargar los conceptos desde la base de datos
$conceptos = $pdo->query("SELECT id_concepto, nombre_concepto FROM caja_conceptos ORDER BY nombre_concepto ASC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require '../logs/head.php'; ?>
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
  <main class="ms-2 me-2">

    <body class="container mt-10">
      <div class="card m-10 p-10">
        <div class="card-header">
          <h2>Registrar Movimiento de Caja</h2>
        </div>
        <div class="card-body ms-5 me-5">
          <form action="caja_registrar_procesar.php" method="POST" id="formCaja">
            <div class="mb-1">
              <label for="fecha_movimiento" class="form-label">Fecha</label>
              <?php date_default_timezone_set('America/Bogota'); ?>
              <input type="datetime-local" name="fecha_movimiento" class="form-control" required value="<?= date('Y-m-d\TH:i') ?>">
            </div>

            <div class="mb-1">
              <label for="desc_movimiento" class="form-label">Descripción</label>
              <input type="text" name="desc_movimiento" class="form-control" required>
            </div>

            <div class="mb-1">
              <label class="form-label">Tipo</label><br>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="tipo" value="INGRESO" checked>
                <label class="form-check-label">Ingreso</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="tipo" value="EGRESO">
                <label class="form-check-label">Egreso</label>
              </div>
            </div>
            <div class="mb-1">
              <label for="movimiento" class="form-label">Tipo de Movimiento</label>
              <select name="movimiento" class="form-select" required>
                <option value="">Seleccione un concepto</option>
                <?php foreach ($conceptos as $concepto): ?>
                  <option value="<?= $concepto['id_concepto'] ?>">
                    <?= htmlspecialchars($concepto['nombre_concepto']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-1">
              <label for="valor" class="form-label">Valor</label>
              <input type="number" name="valor" class="form-control" required>
            </div>

            <div class="mb-1">
              <label for="caja_tipo" class="form-label">Caja</label>
              <select name="caja_tipo" class="form-select" required>
                <option value="PARQUEADERO">Parqueadero</option>
                <option value="ALOJAMIENTO">Alojamiento</option>
              </select>
            </div>

            <input type="hidden" name="user_login" value="<?= $user_login ?>">
            <input type="hidden" name="liquidado" value="NO">

            <button type="submit" class="btn btn-primary">Guardar Movimiento</button>
          </form>
        </div>
      </div>
      <script>
        $('#formCaja').on('submit', function(e) {
          e.preventDefault();

          $.ajax({
            url: 'caja_registrar_procesar.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {

              if (res.trim() === "OK") {
                alert("Guardado correctamente");
                $('#formCaja')[0].reset();
              } else {
                alert(res); // solo si hay error
              }
            }
          });
        });
      </script>
    </body>
  </main>
</div>

</html>