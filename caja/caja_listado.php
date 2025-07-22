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

// Valores por defecto para filtros
if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
} else {
    // Mostrar todo sin filtrar si no hay fechas
    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // o una fecha inicial antigua
    $fecha_fin = date('Y-m-d');
}
$caja = $_GET['caja'] ?? '';

// Parámetros para la consulta
$params = [
    ':fecha_inicio' => $fecha_inicio . ' 00:00:00',
    ':fecha_fin' => $fecha_fin . ' 23:59:59',
];

// Consulta base
$sql = "SELECT caja.*, usuarios.nombre AS nombre_usuario
        FROM caja
        LEFT JOIN usuarios ON caja.user_login = usuarios.id
        WHERE fecha_movimiento BETWEEN :fecha_inicio AND :fecha_fin";

// Filtro por caja si se selecciona
if (!empty($caja)) {
    $sql .= " AND caja.caja = :caja";
    $params[':caja'] = $caja;
}

$sql .= " ORDER BY fecha_movimiento DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movimientos = $stmt->fetchAll();

// Obtener valores únicos de caja para el select
$cajas_unicas = $pdo->query("SELECT DISTINCT caja FROM caja ORDER BY caja")->fetchAll();

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
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">
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
            <select name="caja" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($cajas_unicas as $c): ?>
                    <option value="<?= htmlspecialchars($c['caja']) ?>" <?= $caja === $c['caja'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($c['caja'])) ?>
                    </option>
                <?php endforeach; ?>
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
                <th>id</th>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Ingreso</th>
                <th>Egreso</th>
                <th>Tipo</th>
                <th>Usuario</th>
                <th>Liquidado</th>
                <th>Origen</th>
                <th><input type="checkbox" id="selectAll"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movimientos as $mov): ?>
                <tr>
                    <?php $fecha = new DateTime($mov['fecha_movimiento']); ?>
                    <td><?= $mov['id_movimiento'] ?></td>
                    <td><?= $fecha->format('d/m/Y H:i') ?></td>
                    <td><?= htmlspecialchars($mov['desc_movimiento']) ?></td>
                    <td class="text-success"><?= $mov['valor_ingreso'] ? number_format($mov['valor_ingreso']) : '' ?></td>
                    <td class="text-danger"><?= $mov['valor_egreso'] ? number_format($mov['valor_egreso']) : '' ?></td>
                    <td><?= htmlspecialchars($mov['caja_tipo']) ?></td>
                    <td><?= htmlspecialchars($mov['nombre_usuario'] ?? 'Desconocido') ?></td>
                    <td><?= $mov['liquidado'] === 'SI' ? '✔️' : '❌' ?></td>
                    <td><?= htmlspecialchars($mov['caja']) ?></td>
                    <td><input type="checkbox" name="ids[]" value="<?= $mov['id_movimiento'] ?>" class="chk-movimiento"></td>
                    
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="table-secondary">
                <th colspan="3">Totales</th>
                <th class="text-success"><?= number_format($total_ingresos) ?></th>
                <th class="text-danger"><?= number_format($total_egresos) ?></th>
                <th colspan="5">Saldo: <strong><?= number_format($saldo) ?></strong></th>
            </tr>
        </tfoot>
        
    </table>
    <button type="button" id="btnPreliquidar" class="btn btn-primary">Preliquidar</button>
<div id="resultadoLiquidacion"></div>

</main>
</div>
<script>
document.getElementById('btnPreliquidar').addEventListener('click', function () {
    const checkboxes = document.querySelectorAll('.chk-movimiento:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) {
        alert("Selecciona al menos un movimiento.");
        return;
    }
    const params = new URLSearchParams();
ids.forEach(id => params.append('ids[]', id));



    // Enviar con Fetch a aloj_resumen_liquidacion.php
    fetch('caja_resumen_liquidacion.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: params
})
    .then(res => res.text())
    .then(html => {
        document.getElementById('resultadoLiquidacion').innerHTML = html;
    })
    .catch(error => {
        console.error("Error:", error);
    });
});
</script>
</body>
</html>
