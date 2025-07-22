<?php
require_once "../conexion/conexion.php";



// Validar que lleguen los datos
if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
    echo "<p style='color:red;'>No se recibieron movimientos válidos.</p>";
    exit;
}

$ids = $_POST['ids'];

// Preparar consulta
$placeholders = rtrim(str_repeat('?,', count($ids)), ',');
$sql = "SELECT * FROM caja WHERE id_movimiento IN ($placeholders)";
$stmt = $pdo->prepare($sql);
$stmt->execute($ids);
$registros = $stmt->fetchAll();

// Calcular total
$total = 0;
foreach ($registros as $row) {
    $ingreso = (int) $row['valor_ingreso'];
    $egreso = (int) $row['valor_egreso'];
    $total += ($ingreso - $egreso);
}
$stmt = $pdo->query("SELECT id, nombre FROM usuarios ORDER BY nombre");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h4>Resumen de Liquidación</h4>
<ul>
 <?php
 echo "<table class='table table-bordered'>";
echo "<thead><tr><th>ID</th><th>Descripción</th><th>Ingreso</th><th>Egreso</th><th>Fecha</th></tr></thead><tbody>";
foreach ($registros as $row) {
    echo "<tr>
        <td>{$row['id_movimiento']}</td>
        <td>{$row['desc_movimiento']}</td>
        <td>{$row['valor_ingreso']}</td>
        <td>{$row['valor_egreso']}</td>
        <td>{$row['fecha_movimiento']}</td>
    </tr>";
}
echo "</tbody></table>";

echo "<h5>Total: <strong>$total</strong></h5>";
?>

<form method="POST" action="caja_guardar_liquidacion.php">
    <input type="hidden" name="ids" value="<?= implode(',', $ids) ?>">
    <input type="hidden" name="total" value="<?= $total ?>">

     <h5>Total a liquidar: <?= number_format( $total) ?></h5>

    <div class="mb-2">
        <label>Entregado por</label>
        <select name="entregado_por" class="form-select" required>
    <option value="">Seleccione</option>
    <?php foreach ($usuarios as $u): ?>
        <option value="<?= $u['nombre'] ?>"><?= $u['nombre'] ?></option>
    <?php endforeach; ?>
</select>

    </div>
   
    <div class="mb-2">
        <label>Recibido por</label>
        <select name="recibido_por" class="form-select" required>
    <option value="">Seleccione</option>
    <?php foreach ($usuarios as $u): ?>
        <option value="<?= $u['nombre'] ?>"><?= $u['nombre'] ?></option>
    <?php endforeach; ?>
</select>

    </div>
    <button type="submit" class="btn btn-success">Liquidar</button>
</form>
