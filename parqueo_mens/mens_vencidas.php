<?php
session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

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

$sql = "SELECT
            CL.fecha_creacion,
            CL.placa,
            CL.nombre,
            CL.cedula,
            CL.celular,
            CL.categoria AS categoria_id,
            CL.vehiculo,
            CL.caseta,
            CL.activo,
            CA.cat_nombre AS categoria,
            CT.casetas_nom,
            PA.fecha,
            PA.fecha_inicio,
            PA.estado,
            DATEDIFF(CURDATE(), DATE(PA.fecha_inicio)) AS dias_atraso
        FROM cliente AS CL
        INNER JOIN categorias AS CA ON CL.categoria = CA.cat_id
        INNER JOIN casetas AS CT ON CL.caseta = CT.caseta_id
        INNER JOIN pagos AS PA ON CL.placa = PA.placa
        WHERE CL.activo = 'SI'
        AND PA.fecha_inicio <= NOW()
        AND PA.estado = 'PENDIENTE'
        ORDER BY LOWER(dias_atraso) desc";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$clientes_vencidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function claseAtraso($dias)
{
    if ($dias <= 5) {
        return 'table-success';
    }

    if ($dias <= 10) {
        return 'table-warning';
    }

    return 'table-danger';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        .table thead {
            background: #343a40;
            color: #fff;
        }

        .placa-tabla {
            display: inline-block;
            background: linear-gradient(to bottom, #FFD700, #e6c200);
            border: 2px solid #111;
            border-radius: 6px;
            color: #000;
            font-family: 'Arial Black', sans-serif;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 2px;
            padding: 4px 10px;
        }

        @media print {

            .table-success,
            .table-warning,
            .table-danger {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
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
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-x"></i> Mensualidades vencidas
                        </h5>
                        <span class="badge bg-dark"><?php echo count($clientes_vencidos); ?> vencidas</span>
                    </div>

                    <div class="card-body">
                        <div class="mb-3 d-flex flex-wrap gap-2">
                            <span class="badge text-dark"  style="background-color:#cfe8d5;">1 a 5 dias</span>
                            <span class="badge text-dark" style="background-color:#e9dfb7;">6 a 10 dias</span>
                            <span class="badge text-dark" style="background-color:#e7b8be;">Mas de 10 dias</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle" id="tablaVencidas">
                                <thead>
                                    <tr>
                                        <th>Placa</th>
                                        <th>Nombre</th>
                                        <th>Categoria</th>
                                        <th>Vehiculo</th>
                                        <th>Fecha de pago</th>
                                        <th>Fecha de vencimiento</th>
                                        <th>Dias atrasado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes_vencidos as $row): ?>
                                        <?php $dias_atraso = (int) $row['dias_atraso']; ?>
                                        <tr class="<?php echo claseAtraso($dias_atraso); ?>">
                                            <td>
                                                <span class="placa-tabla">
                                                    <?php echo htmlspecialchars(strtoupper($row['placa'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($row['categoria'] ?? 'Sin categoria'); ?></td>
                                            <td><?php echo htmlspecialchars($row['vehiculo']); ?></td>
                                            <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                                            <td><?php echo  htmlspecialchars($row['fecha']); ?></td>
                                            <td>
                                                <span class="badge bg-dark">
                                                    <?php echo $dias_atraso; ?> dias
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
                <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
                <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
                <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
                <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
                <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
                <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

                <script>
                    $(document).ready(function() {
                        $('#tablaVencidas').DataTable({
                            responsive: true,
                            pageLength: 50,
                            order: [
                                [4, 'asc']
                            ],
                            dom: 'Bfrtip',
                            buttons: [{
                                    extend: 'excelHtml5',
                                    text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                                    className: 'btn btn-success btn-sm',
                                    title: 'Mensualidades_Vencidas',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6]
                                    }
                                },
                                {
                                    extend: 'pdfHtml5',
                                    text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                                    className: 'btn btn-danger btn-sm',
                                    title: 'Mensualidades_Vencidas',
                                    orientation: 'landscape',
                                    pageSize: 'A4',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6]
                                    }
                                },
                                {
                                    extend: 'print',
                                    text: '<i class="bi bi-printer"></i> Imprimir',
                                    className: 'btn btn-secondary btn-sm',
                                    title: 'Mensualidades vencidas',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5, 6]
                                    }
                                }
                            ],
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            }
                        });
                    });
                </script>
            </div>
        </body>
    </main>
</div>

</html>
