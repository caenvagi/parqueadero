<?php
session_start();
require_once "../conexion/conexion.php";

// Aquí puedes capturar el usuario logueado desde la sesión
$user_login = $_SESSION['usuario'] ?? 'admin'; // Cambia según tu sistema

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Movimiento de Caja</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
  <h2>Registrar Movimiento de Caja</h2>
  <form action="caja_registrar_procesar.php" method="POST">
    <div class="mb-3">
      <label for="fecha_movimiento" class="form-label">Fecha</label>
      <input type="datetime-local" name="fecha_movimiento" class="form-control" required value="<?= date('Y-m-d\TH:i') ?>">
    </div>

    <div class="mb-3">
      <label for="desc_movimiento" class="form-label">Descripción</label>
      <input type="text" name="desc_movimiento" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Tipo</label><br>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="tipo" value="ingreso" checked>
        <label class="form-check-label">Ingreso</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="tipo" value="egreso">
        <label class="form-check-label">Egreso</label>
      </div>
    </div>

    <div class="mb-3">
      <label for="valor" class="form-label">Valor</label>
      <input type="number" name="valor" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="caja_tipo" class="form-label">Caja</label>
      <select name="caja_tipo" class="form-select" required>
        <option value="principal">Principal</option>
        <option value="secundaria">Secundaria</option>
      </select>
    </div>

    <input type="hidden" name="user_login" value="<?= $user_login ?>">
    <input type="hidden" name="liquidado" value="no">

    <button type="submit" class="btn btn-primary">Guardar Movimiento</button>
  </form>
</body>
</html>
