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

$placa = $_GET['placa'] ?? '';

if (!$placa) {
    die("Placa no especificada");
}

// 🔹 DATOS CLIENTE
$sqlCliente = "SELECT * FROM cliente WHERE placa = ?";
$stmt = $pdo->prepare($sqlCliente);
$stmt->execute([$placa]);
$cliente = $stmt->fetch();
$diaPago = date('d', strtotime($cliente['fecha_creacion']));

// 🔹 HISTORIAL
$sqlHistorial = "SELECT * FROM mensualidad_historial WHERE placa = ? ORDER BY fecha_ingreso DESC";
$stmtH = $pdo->prepare($sqlHistorial);
$stmtH->execute([$cliente['placa']]);
$historial = $stmtH->fetchAll();




?>


<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
    <style>
        .card-cliente {
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 10px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            width: 2px;
            height: 100%;
            background: #dee2e6;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }



        .timeline-content {
            background: #fff;
            padding: 10px 12px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .timeline-icon {
            position: absolute;
            left: -8px;
            top: 0;
            width: 26px;
            height: 26px;
            background: #f5f6f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .timeline-item.ingreso .timeline-icon {
            background: #198754;
            /* verde */
        }

        .timeline-item.retirado .timeline-icon {
            background: #dc3545;
            /* rojo */
        }

        .placa {
            display: inline-block;
            background: #FFD700;
            /* amarillo */
            color: #000;
            font-weight: bold;
            font-size: 28px;
            letter-spacing: 3px;
            padding: 8px 16px;
            border-radius: 8px;
            border: 3px solid #000;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.3), 0 3px 6px rgba(0, 0, 0, 0.2);
            font-family: 'Arial Black', Arial, sans-serif;
        }
    </style>
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">

        <body class="bg-light">
            <div class="container mt-4">
                <h3 class="mb-4">Estado de Clientes mensualidad</h3>
                <div class="container-fluid mt-4">

                    <!-- 🔷 DATOS CLIENTE -->
                    <div class="card card-cliente mb-4 p-3">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="placa">
                                    <?= strtoupper($cliente['placa']) ?>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-4"><b>Nombre:</b> <?= $cliente['nombre'] ?></div>
                                    <div class="col-md-3"><b>Cédula:</b> <?= $cliente['cedula'] ?></div>
                                    <div class="col-md-3"><b>Celular:</b> <?= $cliente['celular'] ?></div>
                                    <div class="col-md-4"><b>Vehículo:</b> <?= $cliente['vehiculo'] ?></div>

                                    <div class="col-md-3"><b>Mensualidad:</b> <?= $cliente['mensualidad'] ?></div>
                                    <div class="col-md-3"><b>Activo:</b> <?= $cliente['activo'] ?></div>
                                    <div class="col-md-4"><b>Valor:</b> $<?= number_format($cliente['valor']) ?></div>
                                    <div class="col-md-5"><b>Dia de pago:</b><?php if ($cliente['mensualidad'] == 'SI' AND $cliente['activo'] == 'SI'): ?>

                                            <?php
                                            $diaPago = date('d', strtotime($cliente['fecha_creacion']));
                                            ?>
                                          

                                            <span class="badge bg-success">
                                                Día <?= $diaPago ?> de cada mes
                                            </span>

                                        <?php elseif ($cliente['mensualidad'] == 'NO'): ?>

                                            <span class="badge bg-primary">
                                                Cliente por horas
                                            </span>

                                        <?php elseif ($cliente['activo'] == 'NO'): ?>

                                            <span class="badge bg-secondary">
                                                Mensualidad inactiva
                                            </span>    

                                        <?php endif; ?>
                                    </div>
                                    
                                </div>
                            </div>
                            <!-- 🔷 CONTENIDO PRINCIPAL -->
                            <div class="row">

                                <!-- 🟦 PAGOS (3/4) -->
                                <div class="col-md-9 mt-4">
                                    <div class="card p-3 card-cliente">
                                        <h5>Pagos del Cliente</h5>

                                        <table id="tablaPagos" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Fecha pago</th>
                                                    <th>Inicio</th>
                                                    <th>Fin</th>
                                                    <th>Valor</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>

                                <!-- 🟩 HISTORIAL (1/4) -->
                                <div class="col-md-3 mt-4">
                                    <div class="card p-3 card-cliente">
                                        <h6>Historial</h6>

                                        <div class="timeline">
                                            <?php foreach ($historial as $h): ?>

                                                <div class="timeline-item <?= $h['fecha_retiro'] ? 'retirado' : 'ingreso' ?>">
                                                    <div class="timeline-icon">
                                                        <?php if ($h['fecha_retiro']): ?>
                                                            <i class="bi bi-box-arrow-left text-white"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-box-arrow-in-right text-white"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="timeline-content">

                                                        <div class="d-flex justify-content-between">
                                                            <small class="text-muted">
                                                                <?= date('d/m/Y', strtotime($h['fecha_ingreso'])) ?>
                                                            </small>

                                                            <?php if ($h['fecha_retiro']): ?>
                                                                <span class="badge bg-danger">Retirado</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">Activo</span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="mt-1">
                                                            <b>Ingreso:</b> <?= $h['fecha_ingreso'] ?><br>

                                                            <?php if ($h['fecha_retiro']): ?>
                                                                <b>Retiro:</b> <?= $h['fecha_retiro'] ?><br>
                                                            <?php endif; ?>

                                                            <?php if (!empty($h['observacion'])): ?>
                                                                <small class="text-muted">
                                                                    <?= $h['observacion'] ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>

                                                    </div>
                                                </div>

                                            <?php endforeach; ?>
                                        </div>

                                    </div>
                                </div>

                            </div>


                        </div>


                    </div>
                    <script>
                        $(document).ready(function() {

                            let placa = "<?= $placa ?>";

                            $('#tablaPagos').DataTable({
                                ajax: {
                                    url: 'ajax_pagos_cliente.php',
                                    type: 'POST',
                                    data: {
                                        placa: placa
                                    }
                                },
                                columns: [{
                                        data: 'fecha'
                                    },
                                    {
                                        data: 'fecha_inicio'
                                    },
                                    {
                                        data: 'fecha_fin'
                                    },
                                    {
                                        data: 'valor'
                                    },
                                    {
                                        data: 'estado'
                                    }
                                ],
                                language: {
                                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                                }
                            });

                        });
                    </script>
        </body>
    </main>
</div>

</html>