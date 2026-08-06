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
    $whereUsuario = "";
} else {
    $whereUsuario = "AND RE.usuario = :usuario";
}

$sql = "
    SELECT
        RE.recibo_id,
        RE.recibo_man,
        RE.fecha_recibo,
        RE.ticket,
        RE.placa,
        RE.fecha_ini,
        RE.fecha_fin,
        RE.tiempo,
        RE.valor_pagado,
        RE.cierre,
        US.nombre AS usuario_nombre,
        CA.cat_nombre,
        TT.tar_tiempo AS plan_nombre
    FROM recibo AS RE
    LEFT JOIN usuarios AS US ON RE.usuario = US.id
    LEFT JOIN categorias AS CA ON RE.tarifa_recibo = CA.cat_id
    LEFT JOIN tar_tiempo AS TT ON RE.plan = TT.tar_id_nombre
    WHERE LOWER(RE.recibo_man) LIKE :recibo
    $whereUsuario
    ORDER BY RE.recibo_id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':recibo', '%fpar%', PDO::PARAM_STR);

if ($tipo_usuario != 1) {
    $stmt->bindValue(':usuario', $id, PDO::PARAM_INT);
}

$stmt->execute();
$recibosFpar = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtConsecutivos = $pdo->prepare("
    SELECT recibo_man
    FROM recibo
    WHERE LOWER(recibo_man) LIKE :recibo
");
$stmtConsecutivos->bindValue(':recibo', '%fpar%', PDO::PARAM_STR);
$stmtConsecutivos->execute();
$recibosConsecutivosFpar = $stmtConsecutivos->fetchAll(PDO::FETCH_ASSOC);

function obtenerConsecutivoFpar($reciboManual)
{
    if (!preg_match('/fpar\s*[-]?\s*(\d+)/i', (string) $reciboManual, $coincidencia)) {
        return null;
    }

    return (int) $coincidencia[1];
}

$consecutivos = [];
$minimo = null;
$maximo = null;

foreach ($recibosConsecutivosFpar as $recibo) {
    $consecutivo = obtenerConsecutivoFpar($recibo['recibo_man']);

    if ($consecutivo === null) {
        continue;
    }

    $consecutivos[$consecutivo] = true;
}

$faltantes = [];

if (!empty($consecutivos)) {
    $numeros = array_keys($consecutivos);
    sort($numeros, SORT_NUMERIC);

    $minimo = reset($numeros);
    $maximo = end($numeros);

    for ($numero = $minimo; $numero <= $maximo; $numero++) {
        if (!isset($consecutivos[$numero])) {
            $faltantes[] = [
                'numero' => $numero,
                'recibo' => 'fpar-' . $numero
            ];
        }
    }
}

function h($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
    <style>
        #tablaRecibosFpar {
            font-size: 12px;
        }

        #tablaRecibosFpar th,
        #tablaRecibosFpar td {
            padding: 4px 6px;
            vertical-align: middle;
        }
    </style>
</head>

<body class="bg-light">
    <?php require '../logs/nav-bar.php'; ?>

    <div id="layoutSidenav_content">
        <main class="ms-5 me-5">
            <div class="container-fluid mt-4">
                <h3 class="mb-4">Listado de Recibos FPAR</h3>

                <div class="card mb-3">
                    <div class="card-header">
                        Revision de consecutivos FPAR
                    </div>
                    <div class="card-body">
                        <?php if (empty($consecutivos)): ?>
                            <div class="alert alert-warning mb-0">
                                No se encontraron numeros consecutivos dentro de los recibos FPAR.
                            </div>
                        <?php elseif (empty($faltantes)): ?>
                            <div class="alert alert-success mb-0">
                                No faltan consecutivos entre fpar-<?= h($minimo) ?> y fpar-<?= h($maximo) ?>.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                Faltan <?= count($faltantes) ?> consecutivo(s) entre fpar-<?= h($minimo) ?> y fpar-<?= h($maximo) ?>.
                            </div>

                            <div class="table-responsive">
                                <table id="tablaFaltantesFpar" class="table table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>CONSECUTIVO</th>
                                            <th>RECIBO ESPERADO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($faltantes as $faltante): ?>
                                            <tr>
                                                <td><?= h($faltante['numero']) ?></td>
                                                <td><?= h($faltante['recibo']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        Recibos con FPAR
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaRecibosFpar" class="table table-bordered table-striped table-sm nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>ID</th>
                                        <th>RECIBO</th>
                                        <th>FECHA</th>
                                        <th>TICKET</th>
                                        <th>CAJERO</th>
                                        <th>PLACA</th>
                                        <th>INGRESO</th>
                                        <th>SALIDA</th>
                                        <th>TIEMPO</th>
                                        <th>PLAN</th>
                                        <th>CATEGORIA</th>
                                        <th>VALOR</th>
                                        <th>CIERRE</th>
                                        <th>IMPRIMIR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recibosFpar as $recibo): ?>
                                        <tr>
                                            <td></td>
                                            <td>
                                                <a href="../modulos/factura/pdf_recibo_mens.php?recibo_id=<?= h($recibo['recibo_id']) ?>" target="_blank">
                                                    <?= h($recibo['recibo_id']) ?>
                                                </a>
                                            </td>
                                            <td><?= h($recibo['recibo_man']) ?></td>
                                            <td><?= h($recibo['fecha_recibo']) ?></td>
                                            <td><?= h($recibo['ticket']) ?></td>
                                            <td><?= h($recibo['usuario_nombre']) ?></td>
                                            <td><?= h($recibo['placa']) ?></td>
                                            <td><?= h($recibo['fecha_ini']) ?></td>
                                            <td><?= h($recibo['fecha_fin']) ?></td>
                                            <td><?= h($recibo['tiempo']) ?></td>
                                            <td><?= h($recibo['plan_nombre']) ?></td>
                                            <td><?= h($recibo['cat_nombre']) ?></td>
                                            <td>$<?= number_format((float) $recibo['valor_pagado'], 0, ",", ".") ?></td>
                                            <td><?= h($recibo['cierre']) ?></td>
                                            <td>
                                                <a href="../modulos/factura/pdf_recibo_mens.php?recibo_id=<?= h($recibo['recibo_id']) ?>" target="_blank" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-print"></i>
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
            $('#tablaRecibosFpar').DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [{
                    className: 'dtr-control',
                    orderable: false,
                    targets: 0
                }],
                order: [
                    [1, 'desc']
                ],
                pageLength: 25,
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'PDF',
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        text: 'Imprimir',
                        className: 'btn btn-info btn-sm'
                    }
                ],
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    zeroRecords: "Sin resultados",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    emptyTable: "No hay recibos FPAR para mostrar",
                    paginate: {
                        first: "Primero",
                        last: "Ultimo",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    buttons: {
                        copy: "Copiar",
                        print: "Imprimir"
                    }
                }
            });

            if ($('#tablaFaltantesFpar').length) {
                $('#tablaFaltantesFpar').DataTable({
                    pageLength: 10,
                    order: [
                        [0, 'desc']
                    ],
                    language: {
                        search: "Buscar:",
                        lengthMenu: "Mostrar _MENU_ registros",
                        zeroRecords: "Sin resultados",
                        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        infoEmpty: "Mostrando 0 a 0 de 0 registros",
                        infoFiltered: "(filtrado de _MAX_ registros totales)",
                        emptyTable: "No hay consecutivos faltantes",
                        paginate: {
                            first: "Primero",
                            last: "Ultimo",
                            next: "Siguiente",
                            previous: "Anterior"
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>
