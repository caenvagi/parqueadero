<?php
session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
$id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$where = "";

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE PA.usuario = :usuario_id";
}

$sql = "
    SELECT
        PA.parqueo_id,
        PA.placa_cli,
        PA.fecha_ini,
        PA.estado,
        CA.cat_nombre AS categoria,
        US.nombre AS cajero,
        CS.casetas_nom AS caseta
    FROM parqueo AS PA
    LEFT JOIN categorias AS CA ON PA.tarifa = CA.cat_id
    LEFT JOIN usuarios AS US ON PA.usuario = US.id
    LEFT JOIN casetas AS CS ON PA.caseta = CS.caseta_id
    $where
    ORDER BY PA.parqueo_id DESC
";

$stmt = $pdo->prepare($sql);

if ($tipo_usuario == 2) {
    $stmt->bindValue(':usuario_id', $id, PDO::PARAM_INT);
}

$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        <body class="bg-light">
            <div class="container mt-4">
                <h3 class="mb-4">Tickets de parqueo por horas</h3>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaTickets" class="table table-striped table-hover table-bordered align-middle w-100">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Ticket</th>
                                        <th>Placa</th>
                                        <th>Categoria</th>
                                        <th>Fecha ingreso</th>
                                        <th>Estado</th>
                                        <th>Caseta</th>
                                        <th>Cajero</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($ticket['parqueo_id']) ?></td>
                                            <td><?= htmlspecialchars($ticket['placa_cli']) ?></td>
                                            <td><?= htmlspecialchars($ticket['categoria'] ?? 'Sin categoria') ?></td>
                                            <td><?= htmlspecialchars($ticket['fecha_ini']) ?></td>
                                            <td>
                                                <?php if ($ticket['estado'] === 'SI'): ?>
                                                    <span class="badge text-bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-secondary">Cerrado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($ticket['caseta'] ?? 'Sin caseta') ?></td>
                                            <td><?= htmlspecialchars($ticket['cajero'] ?? 'Sin cajero') ?></td>
                                            <td class="text-center">
                                                <a
                                                    class="btn btn-sm btn-primary"
                                                    href="../modulos/factura/pdf_ticket.php?parqueo_id=<?= urlencode($ticket['parqueo_id']) ?>"
                                                    target="_blank"
                                                    rel="noopener"
                                                >
                                                    <i class="bi bi-printer-fill"></i>
                                                    Reimprimir
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
            <script>
                $(document).ready(function() {
                    $('#tablaTickets').DataTable({
                        order: [[0, 'desc']],
                        pageLength: 25,
                        language: {
                            decimal: ',',
                            thousands: '.',
                            emptyTable: 'No hay tickets registrados',
                            info: 'Mostrando _START_ a _END_ de _TOTAL_ tickets',
                            infoEmpty: 'Mostrando 0 a 0 de 0 tickets',
                            infoFiltered: '(filtrado de _MAX_ tickets)',
                            lengthMenu: 'Mostrar _MENU_ tickets',
                            loadingRecords: 'Cargando...',
                            processing: 'Procesando...',
                            search: 'Buscar:',
                            zeroRecords: 'No se encontraron tickets',
                            paginate: {
                                first: 'Primero',
                                last: 'Ultimo',
                                next: 'Siguiente',
                                previous: 'Anterior'
                            }
                        }
                    });
                });
            </script>
        </body>
    </main>
</div>

</html>
