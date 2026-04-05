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
// ✅ CONSULTA
$sql = "SELECT 
            cl.placa,
            cl.nombre,
            cl.cedula,
            cl.celular,
            cl.vehiculo,
            cl.valor,
            cl.mensualidad,
            cl.activo,
            c.casetas_nom,
            cat.cat_nombre
        FROM cliente cl
        LEFT JOIN casetas c ON cl.caseta = c.caseta_id
        LEFT JOIN categorias cat ON cl.categoria = cat.cat_id
        ORDER BY cl.placa ASC";

// ✅ EJECUCIÓN
$stmt = $pdo->prepare($sql);
$stmt->execute();

// ✅ RESULTADOS
$clientes = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Responsive -->
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons -->
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .card {
            border-radius: 15px;
        }

        .table thead {
            background: #343a40;
            color: white;
        }

        .badge-activo {
            background-color: #198754;
        }

        .badge-inactivo {
            background-color: #dc3545;
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
                            <i class="bi bi-people"></i> Todos los Clientes
                        </h5>


                    </div>

                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="tablaClientes">
                                <thead>
                                    <tr>
                                        <th>Placa</th>
                                        <th>Nombre</th>
                                        <th>Vehículo</th>
                                        <th>Categoría</th>
                                        <th>Caseta</th>
                                        <th>Valor</th>
                                        <th>Tipo</th>
                                        <th>Mens.</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($clientes as $row): ?>
                                        <tr>
                                            <td><strong><?= $row['placa'] ?></strong></td>
                                            <td><?= $row['nombre'] ?></td>
                                            <td><?= $row['vehiculo'] ?></td>
                                            <td><?= $row['cat_nombre'] ?></td>
                                            <td><?= $row['casetas_nom'] ?></td>
                                            <td><?= $row['valor'] ?></td>

                                            <td>
                                                <?php if ($row['mensualidad'] == 'SI'): ?>
                                                    <span class="badge bg-primary">Mens</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Hora</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['activo'] == 'SI'): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>




                                            <td>
                                                <!-- Botón pagar -->
                                                <?php if ($row['mensualidad'] == 'SI' && $row['activo'] == 'SI'): ?>

                                                    <a href="mens_pagar.php?placa=<?= $row['placa'] ?>"
                                                        class="btn btn-sm btn-success"
                                                        data-bs-toggle="tooltip"
                                                        title="Pagar mensualidad">
                                                        <i class="bi bi-cash"></i>
                                                    </a>

                                                <?php else: ?>

                                                    <button class="btn btn-sm btn-secondary" disabled
                                                        data-bs-toggle="tooltip"
                                                        title="Cliente inactivo o no es mensualidad">
                                                        <i class="bi bi-cash"></i>
                                                    </button>

                                                <?php endif; ?>

                                                <a href="editar_cliente.php?placa=<?= $row['placa'] ?>"
                                                    class="btn btn-sm btn-secondary"
                                                    data-bs-toggle="tooltip"
                                                    title="Editar cliente">
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                                <!-- 🔥 NUEVO: Historial -->
                                                <a href="historial_timeline.php?placa=<?= $row['placa'] ?>"
                                                    class="btn btn-sm btn-info"
                                                    title="Ver historial">
                                                    <i class="bi bi-clock-history"></i>
                                                </a>

                                                <!-- <button class="btn btn-sm btn-danger btnEliminar"
                                                    data-placa="<?= $row['placa'] ?>"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar cliente">

                                                    <i class="bi bi-trash"></i>
                                                </button> -->
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>

            </div>

            <!-- JS -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



            <script>
                $(document).ready(function() {
                    $('#tablaClientes').DataTable({
                        responsive: true,
                        pageLength: 25,

                        dom: 'Bfrtip',

                        buttons: [{
                                extend: 'excelHtml5',
                                text: '📊 Excel',
                                className: 'btn btn-success btn-sm',
                                title: 'Clientes_Mensualidad',
                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                text: '📄 PDF',
                                className: 'btn btn-danger btn-sm',
                                title: 'Clientes_Mensualidad',
                                orientation: 'landscape',
                                pageSize: 'A4',
                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                                }
                            },
                            {
                                extend: 'print',
                                text: '🖨️ Imprimir',
                                className: 'btn btn-secondary btn-sm',
                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                                }
                            }
                        ],

                        language: {
                            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                        },

                        // columnDefs: [
                        //     { orderable: false, targets: 7 } // columna Acciones
                        // ]
                    });
                });
            </script>

            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

            <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

            <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

            <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>

            <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

            <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

            <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

            <!-- Excel -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

            <!-- PDF -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>


</div>
</body>
</main>
</div>

</html>