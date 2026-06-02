<?php
session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE id=$id";
}

$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

if ($fecha_inicio > $fecha_fin) {
    $fecha_inicio = $fecha_fin;
}

$sql = "SELECT
            mh.id,
            mh.placa,
            mh.fecha_ingreso,
            mh.fecha_retiro,
            mh.caseta,
            mh.plan,
            mh.valor,
            mh.observacion,
            cl.nombre AS cliente,
            cl.cedula,
            cl.celular,
            cl.vehiculo,
            cl.mensualidad,
            cl.activo,
            ca.casetas_nom,
            cat.cat_nombre,
            tt.tar_tiempo,
            us.nombre AS usuario_nombre
        FROM mensualidad_historial mh
        LEFT JOIN cliente cl ON mh.placa = cl.placa
        LEFT JOIN casetas ca ON mh.caseta = ca.caseta_id
        LEFT JOIN categorias cat ON cl.categoria = cat.cat_id
        LEFT JOIN tar_tiempo tt ON mh.plan = tt.tar_id_nombre
        LEFT JOIN usuarios us ON mh.usuario = us.id
        WHERE mh.fecha_retiro IS NOT NULL
        AND DATE(mh.fecha_retiro) BETWEEN :fecha_inicio AND :fecha_fin
        ORDER BY mh.fecha_retiro DESC, mh.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':fecha_inicio' => $fecha_inicio,
    ':fecha_fin' => $fecha_fin
]);
$retiros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
    <style>
        body {
            background-color: #f4f6f9;
        }

        .table thead {
            background: #343a40;
            color: #fff;
        }

        .placa-tabla {
            display: inline-block;
            min-width: 74px;
            background: linear-gradient(to bottom, #FFD700, #e6c200);
            color: #000;
            font-weight: 900;
            font-size: 14px;
            letter-spacing: 1px;
            padding: 3px 8px;
            border-radius: 6px;
            border: 2px solid #111;
            font-family: 'Arial Black', Arial, sans-serif;
            text-align: center;
        }

        #tablaRetiradas {
            font-size: 12px;
        }

        #tablaRetiradas th,
        #tablaRetiradas td {
            padding: 5px 7px;
            vertical-align: middle;
        }
    </style>
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">

        <body class="bg-light">
            <div class="container mt-4">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Clientes retirados de mensualidad</h5>
                        <span class="badge bg-secondary">
                            <?= count($retiros) ?> registros
                        </span>
                    </div>

                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end mb-4">
                            <div class="col-md-3">
                                <label for="fecha_inicio" class="form-label">Fecha inicial</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                    value="<?= htmlspecialchars($fecha_inicio) ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_fin" class="form-label">Fecha final</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                                    value="<?= htmlspecialchars($fecha_fin) ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a href="mens_retiradas.php" class="btn btn-secondary">Mes actual</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle nowrap" id="tablaRetiradas" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Retiro</th>
                                        <th>Ingreso</th>
                                        <th>Placa</th>
                                        <th>Cliente</th>
                                        <th>Cedula</th>
                                        <th>Celular</th>
                                        <th>Vehiculo</th>
                                        <th>Categoria</th>
                                        <th>Caseta</th>
                                        <th>Plan</th>
                                        <th>Valor</th>
                                        <th>Estado actual</th>
                                        <th>Usuario</th>
                                        <th>Observacion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($retiros as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['fecha_retiro']) ?></td>
                                            <td><?= htmlspecialchars($row['fecha_ingreso']) ?></td>
                                            <td>
                                                <a href="../parqueo_mens/cliente_mens.php?placa=<?= urlencode($row['placa']) ?>" class="text-decoration-none">
                                                    <span class="placa-tabla"><?= htmlspecialchars(strtoupper($row['placa'])) ?></span>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($row['cliente'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['cedula'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['celular'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['vehiculo'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['cat_nombre'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['casetas_nom'] ?? $row['caseta'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['tar_tiempo'] ?? $row['plan'] ?? '') ?></td>
                                            <td>$<?= number_format((float)$row['valor'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php if ($row['mensualidad'] == 'SI' && $row['activo'] == 'SI'): ?>
                                                    <span class="badge bg-success">Reactivado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Retirado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($row['usuario_nombre'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['observacion'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    $('#tablaRetiradas').DataTable({
                        responsive: true,
                        pageLength: 25,
                        order: [[0, 'desc']],
                        dom: 'Bfrtip',
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                text: 'Excel',
                                className: 'btn btn-success btn-sm',
                                title: 'Mensualidades_retiradas_<?= $fecha_inicio ?>_<?= $fecha_fin ?>'
                            },
                            {
                                extend: 'pdfHtml5',
                                text: 'PDF',
                                className: 'btn btn-danger btn-sm',
                                title: 'Mensualidades retiradas',
                                messageTop: 'Rango: <?= $fecha_inicio ?> a <?= $fecha_fin ?>',
                                orientation: 'landscape',
                                pageSize: 'A4'
                            },
                            {
                                extend: 'print',
                                text: 'Imprimir',
                                className: 'btn btn-secondary btn-sm',
                                title: 'Mensualidades retiradas',
                                messageTop: 'Rango: <?= $fecha_inicio ?> a <?= $fecha_fin ?>'
                            }
                        ],
                        language: {
                            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                        }
                    });
                });
            </script>
        </body>
    </main>
</div>

</html>
