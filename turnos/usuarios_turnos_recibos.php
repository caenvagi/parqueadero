<?php
session_start();
require_once "../conexion/conexion.php";

$sql = "SELECT t.id_turno, t.usuario_id, u.nombre, t.fecha_inicio, t.fecha_fin, t.valor
        FROM usuarios_turnos t
        JOIN usuarios u ON t.usuario_id = u.id
        WHERE t.pagado = 0
        ORDER BY t.usuario_id, t.fecha_inicio";

$stmt = $pdo->query($sql);
$turnos = $stmt->fetchAll();
?>

<form id="form-turnos" method="POST" action="previsualizar_recibo.php">
  <table border="1">
    <tr>
      <th>Usuario</th>
      <th>Fecha inicio</th>
      <th>Fecha fin</th>
      <th>Valor</th>
      <th>Seleccionar</th>
    </tr>
    <?php foreach ($turnos as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['nombre']) ?></td>
        <td><?= $row['fecha_inicio'] ?></td>
        <td><?= $row['fecha_fin'] ?></td>
        <td><?= $row['valor'] ?></td>
        <td><input type="checkbox" name="turnos[]" value="<?= $row['id_turno'] ?>"></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <br>
  <button type="submit">Generar Recibo</button>
</form>