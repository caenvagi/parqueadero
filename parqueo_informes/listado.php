<?php
session_start();
date_default_timezone_set('America/Bogota');

// Estilos
echo "<link rel='stylesheet' href='../css/styles.css'>";
echo "<link rel='stylesheet' href='../css/estilos.css'>";
echo "<link rel='stylesheet' href='../css/cargando.css'>";

// CONEXIÓN PDO
require '../conexion/conexion.php';

// VALIDAR SESIÓN
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_SESSION['id'];
$nombre = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario = $_SESSION['usuario'];
$foto = $_SESSION['avatar'];

// FILTRO SEGÚN USUARIO
$where = ($tipo_usuario == 1) ? "" : "WHERE RE.usuario = $id";


// =============================
// 🔵 CONSULTAS
// =============================



// LISTADO PRINCIPAL
$query = "
SELECT 
    RE.recibo_id,
    CJ.id_movimiento,
    CJ.id_liquidacion,
    RE.fecha_recibo,
    RE.ticket,
    RE.recibo_man,
    RE.placa,
    RE.fecha_ini,
    RE.fecha_fin,
    RE.tiempo,
    RE.valor_manual,
    RE.valor_pagado,
    RE.tarifa_recibo,
    CL.categoria,
    RE.usuario,
    US.nombre,
    CA.cat_nombre
FROM recibo AS RE
INNER JOIN cliente AS CL ON RE.placa = CL.placa
INNER JOIN usuarios AS US ON RE.usuario = US.id
INNER JOIN categorias AS CA ON RE.tarifa_recibo = CA.cat_id
INNER JOIN caja AS CJ ON CJ.recibo_id = RE.recibo_id
ORDER BY RE.recibo_id DESC
LIMIT 500
";

$stmt = $pdo->query($query);
$parqueoUlt1 = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <!-- Buttons -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <style>
        #arqueo {
    font-size: 12px; /* puedes bajar a 11px o 10px si quieres */
}

#arqueo th, 
#arqueo td {
    padding: 4px 6px; /* reduce espacio interno */
}
    </style>
</head>

<body>

    <?php require '../logs/nav-bar.php'; ?>

    <!-- <div class="cargando">
        <div class="loader-outter"></div>
        <div class="loader-inner"></div>
    </div> -->

    <div id="layoutSidenav_content">
        <main>

            <div class="card-header BG-primary mt-1">
                <b style="color:white;">Ingreso Vehículos</b>
            </div>

            <div class="container mt-3">
                <div class="card">
                    <div class="card-header">Listado Vehículos fuera del parqueadero</div>

                    <div class="card-body">
                        <div class="table-responsive">

                            <table id="arqueo" class="display nowrap table table-bordered table-sm" style="width:100%">
                                <thead>
                                    <tr>
                                        <td></td>
                                        <th>ID</th>
                                        <th>CAJA</th>
                                        <th>LIQ</th>
                                        <th>REC</th>
                                        <th>TICKET</th>
                                        <th>FECHA</th>
                                        <th>CAJERO</th>
                                        <th>PLACA</th>
                                        <th>INGRESO</th>
                                        <th>SALIDA</th>
                                        <th>TIEMPO</th>
                                        <th>VALOR</th>
                                        <th>CATEGORIA</th>
                                        <th>IMPRIMIR</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($parqueoUlt1 as $fila): ?>

                                        <tr>
                                            <td></td>
                                            <td><a href="../modulos/factura/pdf_recibo_mens.php?recibo_id=<?= $fila['recibo_id'] ?>" target="_blank">
                                                <?= $fila['recibo_id'] ?>
                                            </a></td>
                                            <td><?= $fila['id_movimiento'] ?></td>
                                            <td><a href="../modulos/factura/liquidacion_pdf.php?id_liquidacion=<?= $fila['id_liquidacion'] ?>" target="_blank">
                                                <?= $fila['id_liquidacion'] ?>
                                            </a></td>
                                            <td><?= $fila['recibo_man'] ?></td>
                                            <td><?= $fila['ticket'] ?></td>
                                            <td><?= $fila['fecha_recibo'] ?></td>
                                            <td><?= $fila['nombre'] ?></td>
                                            <td><?= $fila['placa'] ?></td>
                                            <td><?= $fila['fecha_ini'] ?></td>
                                            <td><?= $fila['fecha_fin'] ?></td>
                                            <td><?= $fila['tiempo'] ?></td>
                                            <td>$<?= number_format($fila['valor_pagado'], 0, ",", ".") ?></td>
                                            <td><?= $fila['cat_nombre'] ?></td>

                                            <td>
                                                <a href="../modulos/factura/pdf_recibo_mens.php?recibo_id=<?= $fila['recibo_id'] ?>" target="_blank" class="btn btn-secondary btn-sm">
                                                    🖨
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

        <?php require '../logs/nav-footer.php'; ?>

    </div>

    <script>
       $(document).ready(function() {
    $('#arqueo').DataTable({
        responsive: {
            details: {
                type: 'column', // 👈 muestra el "+"
                target: 0       // 👈 en la primera columna
            }
        },

        columnDefs: [
            {
                className: 'dtr-control',
                orderable: false,
                targets: 0
            }
        ],

        order: [[0, 'desc']], // ojo cambia índice porque agregamos columna

        pageLength: 25,

        dom: 'Bfrtip',

        buttons: [
            { extend: 'excelHtml5', text: 'Excel', className: 'btn btn-success' },
            { extend: 'pdfHtml5', text: 'PDF', className: 'btn btn-danger' },
            { extend: 'print', text: 'Imprimir', className: 'btn btn-info' }
        ],

        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_",
            zeroRecords: "Sin resultados",
            info: "Mostrando _START_ a _END_ de _TOTAL_",
            paginate: {
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });
});
    </script>

</body>

</html>