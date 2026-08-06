<?php
session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

$inactive = 20 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?mensaje=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$sql = "
    SELECT
        CL.id_liquidacion,
        CL.fecha_liquidacion,
        CL.total_liquidado,
        CL.observaciones,
        DATE_FORMAT(CL.fecha_liquidacion, '%Y-%m') AS mes_orden,
        COALESCE(U1.nombre, CL.entregado_por) AS entregado_por,
        COALESCE(U2.nombre, CL.recibido_por) AS recibido_por,
        COUNT(CLD.id_movimiento) AS total_movimientos,
        COALESCE(SUM(C.valor_ingreso), 0) AS total_ingresos,
        COALESCE(SUM(C.valor_egreso), 0) AS total_egresos
    FROM caja_liquidaciones AS CL
    LEFT JOIN usuarios AS U1 ON CL.entregado_por = U1.id
    LEFT JOIN usuarios AS U2 ON CL.recibido_por = U2.id
    LEFT JOIN caja_liquidaciones_detalle AS CLD ON CL.id_liquidacion = CLD.id_liquidacion
    LEFT JOIN caja AS C ON CLD.id_movimiento = C.id_movimiento
    GROUP BY
        CL.id_liquidacion,
        CL.fecha_liquidacion,
        CL.total_liquidado,
        CL.observaciones,
        DATE_FORMAT(CL.fecha_liquidacion, '%Y-%m'),
        U1.nombre,
        U2.nombre,
        CL.entregado_por,
        CL.recibido_por
    ORDER BY CL.fecha_liquidacion DESC, CL.id_liquidacion DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$liquidaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$meses = [
    '01' => 'Enero',
    '02' => 'Febrero',
    '03' => 'Marzo',
    '04' => 'Abril',
    '05' => 'Mayo',
    '06' => 'Junio',
    '07' => 'Julio',
    '08' => 'Agosto',
    '09' => 'Septiembre',
    '10' => 'Octubre',
    '11' => 'Noviembre',
    '12' => 'Diciembre',
];

function nombreMesLiquidacion($mesOrden, $meses)
{
    if (!$mesOrden) {
        return 'Sin fecha';
    }

    [$anio, $mes] = explode('-', $mesOrden);
    return ($meses[$mes] ?? $mes) . ' ' . $anio;
}

$resumenMeses = [];
foreach ($liquidaciones as $liq) {
    $mesOrden = $liq['mes_orden'] ?? '0000-00';

    if (!isset($resumenMeses[$mesOrden])) {
        $resumenMeses[$mesOrden] = [
            'nombre' => nombreMesLiquidacion($mesOrden, $meses),
            'cantidad' => 0,
            'ingresos' => 0,
            'total' => 0,
        ];
    }

    $resumenMeses[$mesOrden]['cantidad']++;
    $resumenMeses[$mesOrden]['ingresos'] += (float) $liq['total_ingresos'];
    $resumenMeses[$mesOrden]['total'] += (float) $liq['total_liquidado'];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <?php require '../logs/datatables.php'; ?>
    <style>
        .table-month-row td {
            background: #212529 !important;
            color: #fff;
            font-weight: 600;
        }

        .summary-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: #fff;
            padding: 14px 16px;
        }
    </style>
</head>

<body class="bg-light">
    <?php require '../logs/nav-bar.php'; ?>

    <div id="layoutSidenav_content">
        <main class="ms-5 me-5">
            <div class="container-fluid mt-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <div>
                        <h3 class="mb-1">Listado de liquidaciones</h3>
                        <div class="text-muted">Liquidaciones realizadas en el sistema, agrupadas por mes.</div>
                    </div>
                    <a href="caja_liquidacion.php" class="btn btn-primary">
                        <i class="bi bi-cash-coin"></i> Nueva liquidacion
                    </a>
                </div>

                <?php if (count($resumenMeses) > 0): ?>
                    <div class="row g-3 mb-4">
                        <?php foreach ($resumenMeses as $resumen): ?>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="summary-card h-100">
                                    <div class="fw-semibold"><?= htmlspecialchars($resumen['nombre']) ?></div>
                                    <div class="small text-muted"><?= (int) $resumen['cantidad'] ?> liquidacion(es)</div>
                                    <div class="small text-success fw-semibold mt-2">
                                        Ingresos: $<?= number_format($resumen['ingresos'], 0, ',', '.') ?>
                                    </div>
                                    <div class="small text-primary fw-semibold">
                                        Liquidado: $<?= number_format($resumen['total'], 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaLiquidaciones" class="table table-bordered table-striped align-middle w-100">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Mes</th>
                                        <th>Mes orden</th>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Total ingresos</th>
                                        <th>Total egresos</th>
                                        <th>Total liquidado</th>
                                        <th>Movimientos</th>
                                        <th>Entregado por</th>
                                        <th>Recibido por</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($liquidaciones as $liq): ?>
                                        <?php
                                        $fecha = new DateTime($liq['fecha_liquidacion']);
                                        $mesNombre = nombreMesLiquidacion($liq['mes_orden'], $meses);
                                        $idLiquidacion = (int) $liq['id_liquidacion'];
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($mesNombre) ?></td>
                                            <td><?= htmlspecialchars($liq['mes_orden']) ?></td>
                                            <td><?= $idLiquidacion ?></td>
                                            <td data-order="<?= htmlspecialchars($liq['fecha_liquidacion']) ?>">
                                                <?= $fecha->format('d/m/Y H:i') ?>
                                            </td>
                                            <td class="text-end text-success fw-semibold" data-order="<?= (float) $liq['total_ingresos'] ?>">
                                                $<?= number_format((float) $liq['total_ingresos'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-end text-danger fw-semibold" data-order="<?= (float) $liq['total_egresos'] ?>">
                                                $<?= number_format((float) $liq['total_egresos'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-end text-primary fw-semibold" data-order="<?= (float) $liq['total_liquidado'] ?>">
                                                $<?= number_format((float) $liq['total_liquidado'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center"><?= (int) $liq['total_movimientos'] ?></td>
                                            <td><?= htmlspecialchars($liq['entregado_por'] ?? 'Sin registro') ?></td>
                                            <td><?= htmlspecialchars($liq['recibido_por'] ?? 'Sin registro') ?></td>
                                            <td><?= htmlspecialchars($liq['observaciones'] ?: 'Sin observaciones') ?></td>
                                            <td class="text-nowrap text-center">
                                                <a href="caja_liquidaciones_detalle.php?id=<?= $idLiquidacion ?>" class="btn btn-outline-primary btn-sm" title="Ver detalle">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="../modulos/factura/liquidacion_pdf.php?id_liquidacion=<?= $idLiquidacion ?>" target="_blank" class="btn btn-outline-danger btn-sm" title="Reimprimir PDF">
                                                    <i class="bi bi-file-earmark-pdf"></i> Reimprimir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            const table = $('#tablaLiquidaciones').DataTable({
                responsive: true,
                orderFixed: [[1, 'desc']],
                order: [[3, 'desc']],
                pageLength: 25,
                columnDefs: [
                    { targets: 0, visible: false },
                    { targets: 1, visible: false, searchable: false },
                    { targets: 11, orderable: false, searchable: false }
                ],
                language: {
                    decimal: '',
                    emptyTable: 'No hay liquidaciones registradas',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                    infoFiltered: '(filtrado de _MAX_ registros en total)',
                    lengthMenu: 'Mostrar _MENU_ registros',
                    loadingRecords: 'Cargando...',
                    processing: 'Procesando...',
                    search: 'Buscar:',
                    zeroRecords: 'Sin resultados encontrados',
                    paginate: {
                        first: 'Primero',
                        last: 'Ultimo',
                        next: 'Siguiente',
                        previous: 'Anterior'
                    }
                },
                drawCallback: function() {
                    const api = this.api();
                    const rows = api.rows({ page: 'current' }).nodes();
                    let last = null;

                    api.column(0, { page: 'current' }).data().each(function(group, i) {
                        if (last !== group) {
                            $(rows).eq(i).before(
                                '<tr class="table-month-row"><td colspan="10">' + group + '</td></tr>'
                            );
                            last = group;
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
