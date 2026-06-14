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

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
    <!-- DataTables Buttons
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

        <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">-->

      <!-- Para Excel
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>-->

     <!-- Para PDF
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>-->




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
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow-lg border-0 h-100">
                                <div class="card-header bg-dark text-white">
                                    <h4 class="mb-0">Pago de Mensualidades</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row g-1">
                                        <form id="formPagos" name="formPagos" action="">
                                            <!-- BUSCAR PLACA -->
                                            <div class="col-md-12">
                                                <!-- <label class="form-label">Placa</label> -->
                                                <input type="text"
                                                    class="form-control form-control-lg"
                                                    id="placa"
                                                    name="placa"
                                                    placeholder="Digite placa"
                                                    value="<?= htmlspecialchars($placa) ?>"
                                                    autofocus>
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
                                                    name="nombre"
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
                                            <input type="hidden"
                                                class="form-control"
                                                id="plan"
                                                name="plan"
                                                placeholder="plan"
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
                        </div>
                        <!-- TABLA -->
                        <div class="col-md-8">
                            <div class="card shadow h-100">
                                <div class="card-header bg-dark text-white">
                                    Listado de clientes con mensualidad activa
                                </div>
                                <div class="card-body">
                                    <table id="tablaClientes" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Placa</th>
                                                <th>Nombre</th>
                                                <th>Vehiculo</th>
                                                <!-- <th>Fecha Inicio</th>
                                                <th>Fecha Fin</th> -->
                                                <th>Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaMens"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- TABLA -->
                    <div class="col-md-12 mt-5">
                        <div class="card shadow h-100">
                            <div class="card-header bg-dark text-white">
                                Historial de Pagos
                            </div>
                            <div class="card-body">
                                <table id="tablaHistorial" name="tablaHistorial" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Fecha Pago</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Valor</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaPagos"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <script>
                       
                        $(document).ready(function() {
                            cargarClientes();
                        });

                        $('#placa').on('focus', function() {

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

                                            $('#mensaje').html(''); // 👈 LIMPIAR MENSAJE
                                            cargarPagos(placa);
                                            $('#nombre').val(resp.nombre);
                                            $('#cedula').val(resp.cedula);
                                            $('#vehiculo').val(resp.vehiculo);
                                            $('#caseta').val(resp.caseta);
                                            $('#valor_real').val(resp.valor);
                                            $('#categoria').val(resp.categoria);
                                            $('#plan').val(resp.plan);
                                            let valorFormateado = new Intl.NumberFormat('es-CO', {
                                                style: 'currency',
                                                currency: 'COP',
                                                minimumFractionDigits: 0
                                            }).format(resp.valor);
                                            $('#valor').val(valorFormateado);
                                            $('#fecha_inicio').val(resp.fecha_inicio);
                                            $('#fecha_fin').val(resp.fecha_fin);

                                            


                                        } else {
                                            
                                            $('#nombre').val('');
                                            $('#cedula').val('');
                                            $('#vehiculo').val('');
                                            $('#caseta').val('');
                                            $('#valor_real').val('');
                                            $('#categoria').val('');
                                            $('#plan').val('');
                                            $('#valor').val('');
                                            $('#fecha_inicio').val('');
                                            $('#fecha_fin').val('');
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
                                            $('#mensaje').html(''); // 👈 LIMPIAR MENSAJE
                                            $('#nombre').val(resp.nombre);
                                            $('#cedula').val(resp.cedula);
                                            $('#vehiculo').val(resp.vehiculo);
                                            $('#caseta').val(resp.caseta);
                                            $('#valor_real').val(resp.valor);
                                            $('#categoria').val(resp.categoria);
                                            $('#plan').val(resp.plan);
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
                                       $('#nombre').val('');
                                        $('#cedula').val('');
                                        $('#vehiculo').val('');
                                        $('#caseta').val('');
                                        $('#valor_real').val('');
                                        $('#categoria').val('');
                                        $('#plan').val('');
                                        $('#valor').val('');
                                        $('#fecha_inicio').val('');
                                        $('#fecha_fin').val('');
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

                        $('#placa').on('focus', function() {
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
                                $('#pagos').html('<option value="">Ingrese una placa válida</option>');
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
                                    window.open(
                                            '../modulos/imprimir_ticket_php/recibomens.php',
                                            '_blank',
                                            'width=400,height=600'
                                        );
                                    $("#respuesta").html(resp);
                                    $("#formPagos")[0].reset();
                                    $("#placa").val("");
                                    $('#tablaPagos').html('');
                                    //location.reload(); // 🔥 recarga la página
                                }
                            });
                        });

                        let tabla;

                         $(document).ready(function() {
                            tabla = $('#tablaHistorial').DataTable({
                                "language": {
                                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                                },
                                "pageLength": 10,
                                "ordering": true,
                                "order": [
                                    [0, "desc"]
                                ],
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
                                    // destruir DataTable antes de recargar
                                    if ($.fn.DataTable.isDataTable('#tablaHistorial')) {
                                        $('#tablaHistorial').DataTable().destroy();
                                    }
                                    // cargar datos nuevos
                                    $('#tablaPagos').html(data);
                                    // volver a inicializar
                                    tabla = $('#tablaHistorial').DataTable({
                                        "language": {
                                            "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                                        },
                                        "lengthMenu": [5, 10, 25, 50, 100],
                                        "pageLength": 10,
                                        "ordering": true,
                                        "order": [
                                            [0, "desc"]
                                        ],
                                    });
                                }
                            });
                        }

                        $(document).ready(function() {

                            const urlParams = new URLSearchParams(window.location.search);
                            const placa = urlParams.get("placa");

                            if (placa) {
                                $("#placa").val(placa);

                                // 🔥 LIMPIAR LA URL (sin recargar)
                                window.history.replaceState({}, document.title, window.location.pathname);
                            }

                        });        

                        // Si la URL trae datos de un pago pendiente, autocompletar el formulario
                        $(document).ready(function() {
                            const params = new URLSearchParams(window.location.search);
                            const pagoId = params.get('id');
                            const placaParam = params.get('placa');
                            const fechaInicio = params.get('fecha_inicio');
                            const fechaFin = params.get('fecha_fin');
                            const valorParam = params.get('valor');

                            if (placaParam) {
                                // establecer placa y cargar datos del cliente
                                $("#placa").val(placaParam);
                                // disparar búsqueda de datos del cliente
                                if (placaParam.length == 6) {
                                    $.ajax({
                                        url: 'buscar_placa.php',
                                        type: 'POST',
                                        data: { placa: placaParam },
                                        dataType: 'json',
                                        success: function(resp) {
                                            if (resp.existe) {
                                                $('#mensaje').html('');
                                                $('#nombre').val(resp.nombre);
                                                $('#cedula').val(resp.cedula);
                                                $('#vehiculo').val(resp.vehiculo);
                                                $('#caseta').val(resp.caseta);
                                                $('#valor_real').val(resp.valor);
                                                $('#categoria').val(resp.categoria);
                                                $('#plan').val(resp.plan);
                                                let valorFormateado = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(resp.valor);
                                                $('#valor').val(valorFormateado);
                                            }
                                        }
                                    });
                                }
                            }

                            if (fechaInicio) {
                                $('#fecha_inicio').val(fechaInicio);
                            }
                            if (fechaFin) {
                                $('#fecha_fin').val(fechaFin);
                            }
                            if (valorParam) {
                                // asignar valor real y formato
                                $('#valor_real').val(valorParam);
                                let valorFormateado2 = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(parseFloat(valorParam));
                                $('#valor').val(valorFormateado2);
                            }

                            if (pagoId && placaParam) {
                                // cargar opciones de pagos y seleccionar el id correspondiente
                                $.ajax({
                                    url: 'buscar_pagos.php',
                                    type: 'POST',
                                    data: { placa: placaParam },
                                    success: function(respuesta) {
                                        $('#pagos').html(respuesta);
                                        // seleccionar la opción con el id recibido
                                        setTimeout(function() {
                                            $('#pagos').val(pagoId);
                                        }, 200);
                                    }
                                });
                            }

                            // limpiar la URL para mayor limpieza
                            if (pagoId || fechaInicio || fechaFin || valorParam) {
                                window.history.replaceState({}, document.title, window.location.pathname + '?placa=' + (placaParam || ''));
                            }
                        });

                        function cargarClientes() {
                            $.ajax({
                                url: "listar_clientes.php",
                                type: "GET",
                                dataType: "json",
                                success: function(data) {

                                    let html = "";

                                    data.forEach(function(cliente) {

                                        html += `
                                            <tr>
                                                <td>
                                                    <a href="#" class="seleccionarPlaca" data-placa="${cliente.placa}">
                                                        ${cliente.placa}
                                                    </a>
                                                </td>
                                                <td>${cliente.nombre}</td>
                                                <td>${cliente.vehiculo}</td>
                                                
                                                <td>$${parseInt(cliente.valor).toLocaleString()}</td>
                                            </tr>
                                        `;
                                    });

                                    $("#tablaMens").html(html);

                                    // Si usas DataTable
                                    if ($.fn.DataTable.isDataTable('#tablaClientes')) {
                                        $('#tablaClientes').DataTable().destroy();
                                    }

                                    $('#tablaClientes').DataTable();

                                },
                                error: function(err) {
                                    console.log(err);
                                }
                            });
                        }

                        $(document).on("click", ".seleccionarPlaca", function(e) {
                            e.preventDefault();

                            let placa = $(this).data("placa");

                            // Llenar input
                            $("#placa").val(placa);

                            // Opcional: poner foco
                            $("#placa").focus();

                            // Opcional: disparar evento change o keyup si tienes lógica
                            //$("#placa").trigger("keyup");
                        });
                    </script>
        </body>
    </main>
</div>

</html>