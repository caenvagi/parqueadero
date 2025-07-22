<?php
session_start();
require_once "../conexion/conexion.php";

// Valores por defecto para filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$caja_tipo = $_GET['caja_tipo'] ?? '';

$params = [
    ':fecha_inicio' => $fecha_inicio . ' 00:00:00',
    ':fecha_fin' => $fecha_fin . ' 23:59:59',
];

// Consulta dinámica con filtros
$sql = "SELECT caja.*, usuarios.nombre AS nombre_usuario
        FROM caja
        LEFT JOIN usuarios ON caja.user_login = usuarios.id
        WHERE fecha_movimiento BETWEEN :fecha_inicio AND :fecha_fin";

$sql .= " ORDER BY fecha_movimiento DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movimientos = $stmt->fetchAll();

// Totales
$total_ingresos = 0;
$total_egresos = 0;
foreach ($movimientos as $m) {
    $total_ingresos += $m['valor_ingreso'];
    $total_egresos += $m['valor_egreso'];
}
$saldo = $total_ingresos - $total_egresos;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Caja</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

    <h2>Listado de Movimientos de Caja</h2>

    <!-- Filtros -->
    <form class="row g-3 mb-4" method="GET">
        <div class="col-md-3">
            <label class="form-label">Fecha inicio</label>
            <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Fecha fin</label>
            <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Caja</label>
            <select name="caja_tipo" class="form-select">
                <option value="">Todas</option>
                <option value="principal" <?= $caja_tipo === 'principal' ? 'selected' : '' ?>>Principal</option>
                <option value="secundaria" <?= $caja_tipo === 'secundaria' ? 'selected' : '' ?>>Secundaria</option>
            </select>
        </div>
        
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <!-- Tabla -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Ingreso</th>
                <th>Egreso</th>
                <th>Caja</th>
                <th>Usuario</th>
                <th>Liquidado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movimientos as $mov): ?>
                <tr>
                    <?php
                        $fecha = new DateTime($mov['fecha_movimiento']);
                    ?>
                    <td><?= $fecha->format('d/m/Y H:i') ?></td>

                    <td><?= htmlspecialchars($mov['desc_movimiento']) ?></td>
                    <td class="text-success"><?= $mov['valor_ingreso'] ? number_format($mov['valor_ingreso']) : '' ?></td>
                    <td class="text-danger"><?= $mov['valor_egreso'] ? number_format($mov['valor_egreso']) : '' ?></td>
                    <td><?= $mov['caja_tipo'] ?></td>
                    <td><?= htmlspecialchars($mov['nombre_usuario'] ?? 'Desconocido') ?></td>
                    <td><?= $mov['liquidado'] === 'sí' ? '✔️' : '❌' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="table-secondary">
                <th colspan="2">Totales</th>
                <th class="text-success"><?= number_format($total_ingresos) ?></th>
                <th class="text-danger"><?= number_format($total_egresos) ?></th>
                <th colspan="3">Saldo: <strong><?= number_format($saldo) ?></strong></th>
            </tr>
        </tfoot>
    </table>

</body>
</html>
