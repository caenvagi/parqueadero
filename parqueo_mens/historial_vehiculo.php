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



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">

        <body class="bg-light">
            <div class="container mt-4">
                <h4 class="mb-3">Historial de Vehículo</h4>

                <!-- BUSCAR PLACA -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="placa" class="form-control" placeholder="Ingrese placa" required
                            value="<?php echo $_GET['placa'] ?? ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary">Buscar</button>
                    </div>
                </form>

                <?php
                if (isset($_GET['placa'])) {
                    $placa = $_GET['placa'];

                    $sql = 
                    "   SELECT mh.placa,
                        mh.fecha_ingreso,
                        mh.fecha_retiro,
                        mh.caseta,
                        mh.valor,
                        tt.tar_tiempo,
                        u.nombre,
                        mh.observacion

                        FROM mensualidad_historial mh
                        INNER JOIN tar_tiempo tt ON mh.plan = tt.tar_id_nombre
                        INNER JOIN usuarios u ON mh.usuario = u.id
                        WHERE placa = :placa 
                        ORDER BY fecha_ingreso DESC";

                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':placa', $placa);
                    $stmt->execute();

                    $resultados = $stmt->fetchAll();

                    if ($resultados) {
                ?>

                        <!-- TABLA -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Placa</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Fecha Retiro</th>
                                        <th>Caseta</th>
                                        <th>Valor</th>
                                        <th>Plan</th>
                                        <th>Usuario</th>
                                        <th>Observación</th>
                                        <th>Activo</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php foreach ($resultados as $row):

                                        // ACTIVO SI NO
                                        $activo = empty($row['fecha_retiro']) ? 'SI' : 'NO';
                                        $badge = $activo == 'SI' ? 'success' : 'danger';
                                    ?>

                                        <tr>
                                            <td><?php echo $row['placa']; ?></td>
                                            <td><?php echo $row['fecha_ingreso']; ?></td>
                                            <td><?php echo $row['fecha_retiro'] ?: '---'; ?></td>
                                            <td><?php echo $row['caseta']; ?></td>
                                            <td>$<?php echo number_format($row['valor']); ?></td>
                                            <td><?php echo $row['tar_tiempo']; ?></td>
                                            <td><?php echo $row['nombre']; ?></td>
                                            <td><?php echo $row['observacion']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $badge; ?>">
                                                    <?php echo $activo; ?>
                                                </span>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>

                <?php
                    } else {
                        echo "<div class='alert alert-warning'>No hay historial para esta placa</div>";
                    }
                }
                ?>



            </div>
        </body>
    </main>
</div>

</html>