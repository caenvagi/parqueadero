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
    <style>
        /* TIMELINE */
        .timeline {
            position: relative;
            margin: 20px 0;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            width: 4px;
            height: 100%;
            background: #0d6efd;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 5px;
            width: 15px;
            height: 15px;
            background: #0d6efd;
            border-radius: 50%;
        }

        .timeline-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .timeline-date {
            font-size: 13px;
            color: #6c757d;
        }

        .estado-activo {
            color: #198754;
            font-weight: bold;
        }

        .estado-inactivo {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">
        <body class="bg-light">
            <div class="container mt-4">
                <h4 class="mb-3">Historial del Vehículo</h4>
                <!-- BUSCAR -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="placa" class="form-control" placeholder="Ingrese placa"
                            value="<?php echo $_GET['placa'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary">Buscar</button>
                    </div>
                </form>
                <?php
                if (isset($_GET['placa'])) {

                    $placa = $_GET['placa'];

                    $sql = "SELECT mh.*, c.nombre as cliente, c.vehiculo, tt.tar_tiempo, u.nombre
                    FROM mensualidad_historial mh
                    LEFT JOIN cliente c ON mh.placa = c.placa
                    left JOIN tar_tiempo tt ON mh.plan = tt.tar_id_nombre
                    left JOIN usuarios u ON mh.usuario = u.id
                    WHERE mh.placa = :placa
                    ORDER BY mh.id desc";

                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':placa', $placa);
                    $stmt->execute();

                    $datos = $stmt->fetchAll();

                    if ($datos) {
                ?>
                        <div class="timeline">
                            <?php foreach ($datos as $row):
                                $activo = empty($row['fecha_retiro']);
                            ?>
                                <div class="timeline-item">
                                    <div class="timeline-card">
                                        <strong><?php echo $row['id']; ?></strong>
                                            <div class="d-flex justify-content-between">

                                                <strong><?php echo $row['placa']; ?></strong>

                                                <?php if ($activo): ?>
                                                    <span class="estado-activo">● ACTIVO</span>
                                                <?php else: ?>
                                                    <span class="estado-inactivo">● FINALIZADO</span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="timeline-date mt-1">
                                                Ingreso: <?php echo $row['fecha_ingreso']; ?> |
                                                Retiro: <?php echo $row['fecha_retiro'] ?: '---'; ?>
                                            </div>

                                            <hr>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <b>Cliente:</b> <?php echo $row['cliente'] ?? '---'; ?><br>
                                                    <b>Vehículo:</b> <?php echo $row['vehiculo'] ?? '---'; ?><br>
                                                    <b>Caseta:</b> <?php echo $row['caseta']; ?>
                                                </div>

                                                <div class="col-md-6">
                                                    <b>Plan:</b> <?php echo $row['tar_tiempo']; ?><br>
                                                    <b>Valor:</b> $<?php echo number_format($row['valor']); ?><br>
                                                    <b>Usuario:</b> <?php echo $row['nombre']; ?>
                                                </div>
                                            </div>

                                            <?php if (!empty($row['observacion'])): ?>
                                                <div class="mt-2">
                                                    <b>Observación:</b><br>
                                                    <?php echo $row['observacion']; ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- 🔥 AQUÍ VA EL BOTÓN -->
                                            <?php if (!$activo): ?>
                                                <button class="btn btn-success btn-sm mt-2 btn-reactivar"
                                                    data-placa="<?php echo $row['placa']; ?>"
                                                    data-plan="<?php echo $row['plan']; ?>"
                                                    data-valor="<?php echo $row['valor']; ?>"
                                                    data-caseta="<?php echo $row['caseta']; ?>">
                                                    🔄 Reactivar mensualidad
                                                </button>
                                            <?php endif; ?>
                                    </div>
                                </div>

                            <?php endforeach; ?>

                        </div>

                <?php
                    } else {
                        echo "<div class='alert alert-warning'>No hay historial para esta placa</div>";
                    }
                }
                ?>
            </div>
            <script>
                $(document).on('click', '.btn-reactivar', function() {

                    if (!confirm("¿Reactivar mensualidad de este vehículo?")) return;

                    let placa = $(this).data('placa');
                    let plan = $(this).data('plan');
                    let valor = $(this).data('valor');
                    let caseta = $(this).data('caseta');

                    $.ajax({
                        url: 'reactivar_mensualidad.php',
                        type: 'POST',
                        data: {
                            placa: placa,
                            plan: plan,
                            valor: valor,
                            caseta: caseta
                        },
                        success: function(resp) {
                            alert(resp);
                            location.reload();
                        }
                    });

                });
            </script>
        </body>
    </main>
</div>

</html>