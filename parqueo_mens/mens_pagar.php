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
                <h3 class="mb-4">Pago de Mensualidades</h3>

                <div class="container py-4">

                    <div class="card shadow-lg border-0">

                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0">Pago de Mensualidades</h4>
                        </div>

                        <div class="card-body">

                            <div class="row g-3">

                                <!-- BUSCAR PLACA -->
                                <div class="col-md-4">

                                    <label class="form-label">Placa</label>

                                    <input type="text"
                                        class="form-control form-control-lg"
                                        id="placa"
                                        placeholder="Digite placa">

                                    <div id="mensaje"></div>

                                </div>

                                <!-- NOMBRE -->
                                <div class="col-md-4">

                                    <label class="form-label">Cliente</label>

                                    <input type="text"
                                        class="form-control"
                                        id="nombre"
                                        readonly>

                                </div>

                                <!-- CEDULA -->
                                <div class="col-md-4">

                                    <label class="form-label">Cedula</label>

                                    <input type="text"
                                        class="form-control"
                                        id="cedula"
                                        readonly>

                                </div>

                                <!-- VEHICULO -->
                                <div class="col-md-4">

                                    <label class="form-label">Vehiculo</label>

                                    <input type="text"
                                        class="form-control"
                                        id="vehiculo"
                                        readonly>

                                </div>

                                <!-- PAGO -->
                                <div class="col-md-4">

                                    <label class="form-label">Pago</label>

                                    <select name="valor" id="valor">
                                        <?php
                                        $sql = "SELECT *
                                                FROM pagos
                                                WHERE placa = 'aaa999'";

                                            $stmt = $pdo->query($sql);

                                            foreach ($stmt as $row) {
                                                echo "<option value='{$row['id']}'>{$row['fecha_inicio']} - {$row['fecha_fin']}</option>";
                                            }

                                            ?>
                                        
                                    </select>

                                </div>

                                <!-- FECHA INICIO -->
                                <!-- <div class="col-md-4">

                                    <label class="form-label">Inicio Periodo</label>

                                    <input type="date"
                                        class="form-control"
                                        id="fecha_inicio">

                                </div> -->

                                <!-- FECHA FIN -->
                                <!-- <div class="col-md-4">

                                    <label class="form-label">Fin Periodo</label>

                                    <input type="date"
                                        class="form-control"
                                        id="fecha_fin">

                                </div> -->

                                <!-- VALOR -->
                                <div class="col-md-4">

                                    <label class="form-label">Valor Mensualidad</label>

                                    <input type="number"
                                        class="form-control"
                                        id="valor">

                                </div>

                                <div class="col-md-8 d-flex align-items-end">

                                    <button class="btn btn-success btn-lg w-100" id="btnPagar">

                                        Pagar Mensualidad

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="card shadow mt-4">

                        <div class="card-header bg-primary text-white">
                            Historial de Pagos
                        </div>

                        <div class="card-body">

                            <table class="table table-striped">

                                <thead>

                                    <tr>
                                        <th>Id</th>
                                        <th>Fecha</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        <th>Valor</th>
                                        <th>Estado</th>
                                    </tr>

                                </thead>

                                <tbody id="tablaPagos">

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <script>
                    $('#placa').on('keyup', function() {

                        this.value = this.value.toUpperCase();

                        let placa = $(this).val();

                        if (placa.length == 6) {

                            $.ajax({

                                url: 'buscar_placa.php',
                                type: 'POST',
                                data: {
                                    placa: placa
                                },
                                dataType: 'json',

                                success: function(resp) {

                                    if (resp.existe) {

                                        $('#nombre').val(resp.nombre);
                                        $('#cedula').val(resp.cedula);
                                        $('#vehiculo').val(resp.vehiculo);
                                        $('#valor').val(resp.valor);
                                        console.log(resp);
                                        cargarPagos(placa);

                                    } else {

                                        $('#mensaje').html(`
                                        <div class="alert alert-warning mt-2">
                                        Cliente no existe
                                        <a href="mens_cliente_nuevo.php?placa=${placa}" class="btn btn-sm btn-primary">
                                        Crear cliente
                                        </a>
                                        </div>
                                        `);

                                    }
                                    
                                }
                                
                            });
                            
                        }

                    });

                    function cargarPagos(placa) {

                        $.ajax({

                            url: 'historial_pagos.php',
                            type: 'POST',
                            data: {
                                placa: placa
                            },

                            success: function(data) {
                                console.log(data);
                                $('#tablaPagos').html(data);

                            }

                        });

                    }
                </script>
        </body>
    </main>
</div>

</html>