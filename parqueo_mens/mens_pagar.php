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
    <!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" /> -->
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">

        <body class="bg-light">
            <div class="container  mt-0">
                <div class="container py-2">
                    <div class="card col-md-4 shadow-lg border-0">
                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0">Pago de Mensualidades</h4>
                        </div>
                        <div class="card-body  ">
                            <div class="row g-1">
                                <form id="formPagos" name="formPagos" action="">
                                    <!-- BUSCAR PLACA -->
                                    <div class="col-md-12">
                                        <!-- <label class="form-label">Placa</label> -->
                                        <input type="text"
                                            class="form-control form-control-lg"
                                            id="placa"
                                            name="placa"
                                            placeholder="Digite placa">
                                    </div>

                                    <div class="col-md-12" id="mensaje"></div>

                                    <!-- NOMBRE -->
                                    <div class="input-group mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-user-alt"></i>&nbsp;NOMBRE</span>
                                    </div>
                                        <input type="text"
                                            class="form-control"
                                            id="nombre"
                                            placeholder="Cliente"
                                            readonly>
                                    </div>

                                    <!-- CEDULA -->
                                    <div class="input-group mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-address-card"></i>&nbsp;CASETA</span>
                                    </div>
                                        <input type="text"
                                            class="form-control"
                                            id="caseta"
                                            name="caseta"
                                            placeholder="Caseta"
                                            readonly>
                                    </div>

                                    <!-- VEHICULO -->
                                    <div class="input-group mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-car-alt"></i>&nbsp;VEHICULO</span>
                                    </div>
                                        <input type="text"
                                            class="form-control"
                                            id="vehiculo"
                                            placeholder="Vehiculo"
                                            readonly>
                                    </div>

                                    <!-- VALOR -->
                                    <div class="input-group mt-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-car-alt"></i>&nbsp;VALOR $</span>
                                    </div>
                                        <input type="text"
                                            class="form-control"
                                            id="valor"
                                            name="valor"
                                            placeholder="Valor mensualidad"
                                            readonly>
                                    </div>

                                    <input type="hidden"
                                            class="form-control"
                                            id="valor_real"
                                            name="valor_real"
                                            placeholder="Valor_real"
                                            readonly>
                                    
                                     <input type="hidden"
                                            class="form-control"
                                            id="categoria"
                                            name="categoria"
                                            placeholder="Categoria"
                                            readonly>       

                                    <!-- PAGO -->
                                    <div class="col-md-12 mt-2">
                                        <!-- <label for="valor" class="form-label">Pago</label> -->
                                        <select name="pagos" id="pagos" class="form-select">
                                            <option value="">Seleccione un Periodo</option>
                                        </select>
                                    </div>

                                    <!-- FECHA INICIO -->
                                    <!-- <div class="col-md-12">
                                            <label class="form-label">Inicio Periodo</label>-->
                                            <input type="hidden"
                                                class="form-control"
                                                id="fecha_inicio"
                                                name="fecha_inicio"
                                                placeholder="fecha_inicio">
                                    <!-- </div> -->

                                    <!-- FECHA FIN -->
                                        <!-- <div class="col-md-12">
                                                <label class="form-label">Fin Periodo</label> -->
                                                <input type="hidden"
                                                    class="form-control"
                                                    id="fecha_fin"
                                                    name="fecha_fin">
                                        <!-- </div> -->

                                    <div class="col-md-12 mt-4 d-flex align-items-end">
                                        <button class="btn btn-success btn-lg w-100" id="btnPagar">
                                            Pagar Mensualidad
                                        </button>

                                    </div>

                                     <div id="respuesta"></div>
                                     
                                </form>
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
                                        <th>Fecha Pago</th>
                                        <th>fecha Inicio</th>
                                        <th>fecha Fin</th>
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
                                        $('#caseta').val(resp.caseta);
                                        $('#valor_real').val(resp.valor);
                                        $('#categoria').val(resp.categoria);
                                        let valorFormateado = new Intl.NumberFormat('es-CO', {
                                            style: 'currency',
                                            currency: 'COP',
                                            minimumFractionDigits: 0
                                        }).format(resp.valor);
                                        $('#valor').val(valorFormateado);
                                        $('#fecha_inicio').val(resp.fecha_inicio);
                                        $('#fecha_fin').val(resp.fecha_fin);
                                        cargarPagos(placa);


                                    } else {

                                        $('#mensaje').html(`
                                        <div class="alert alert-warning mt-2">
                                        Cliente no existe
                                        <a href="mens_cliente_nuevo.php?placa=${placa}" class="btn btn-sm btn-secondary ms-2">
                                        Crear cliente
                                        </a>
                                        </div>
                                        `);

                                    }

                                }

                            });

                        }

                    });

                    $('#placa').on('keyup', function() {
                        let placa = $(this).val();

                        if (placa.length == 6) { // evita consultas innecesarias
                            $.ajax({
                                url: 'buscar_pagos.php',
                                type: 'POST',
                                data: {
                                    placa: placa
                                },
                                success: function(respuesta) {
                                    $('#pagos').html(respuesta);
                                }
                            });
                        } else {
                            $('#valor').html('<option value="">Ingrese una placa válida</option>');
                        }
                    });

                     $("#formPagos").submit(function(e) {
                        e.preventDefault();
                        $.ajax({
                            url: "pagar_mensualidad.php",
                            type: "POST",
                            data: $(this).serialize(),

                            success: function(resp) {
                                $("#respuesta").html(resp);
                                console.log(resp);
                                $("#formPagos")[0].reset();
                                $('#tablaPagos').html('');
                            }
                        });
                    });

                    function cargarPagos(placa) {

                        $.ajax({

                            url: 'historial_pagos.php',
                            type: 'POST',
                            data: {
                                placa: placa
                            },

                            success: function(data) {
                                $('#tablaPagos').html(data);

                            }

                        });

                    }

                   
                </script>
        </body>
    </main>
</div>

</html>