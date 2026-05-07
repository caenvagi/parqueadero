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
   
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">

        <body class="p-4 bg-light">
            <div class="container">
                <h3 class="mt-3 mb-4 text-center">Registro de Parqueo por Horas</h3>

                <div class="card p-2">
                    <div class="row">
                        <div class="col col-12 col-sm-12 col-md-3 col-lg-4 col-xl-4 m-3">
                            <form id="formParqueo" class="row g-3">
                                <div class="card" id="cardForm_parqueo">
                                    <div class="header">Ingresar datos del vehiculo:</div>
                                    <div class="input-group mb-2">

                                    </div>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <!-- <span class="input-group-text" id="basic-addon1"><i class="fas fa-tachometer-alt"></i>&nbsp;PLACA</span> -->
                                        </div>
                                        <input type="text" value="" onkeyup="javascript:this.value=this.value.toUpperCase();" class="form-control" name="placa" id="placa" placeholder="Placa" aria-label="placa" aria-describedby="basic-addon1" required='true' autofocus>
                                    </div>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <!-- <span class="input-group-text" id="basic-addon1"><i class="fas fa-tachometer-alt"></i>&nbsp;NOMBRE</span> -->
                                        </div>
                                        <input type="text" value="" onkeyup="javascript:this.value=this.value.toUpperCase();" class="form-control" name="nombre" id="nombre" placeholder="Nombre" aria-label="nombre" aria-describedby="basic-addon1" required='true' autofocus>
                                    </div>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <!-- <span class="input-group-text" id="basic-addon1"><i class="fas fa-tachometer-alt"></i>&nbsp;NOMBRE</span> -->
                                        </div>
                                        <input type="number" value="" onkeyup="javascript:this.value=this.value.toUpperCase();" class="form-control" name="celular" id="celular" placeholder="Celular" aria-label="celular" aria-describedby="basic-addon1" required='true' autofocus>
                                    </div>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <!-- <span class="input-group-text" id="basic-addon1"><i class="fas fa-tachometer-alt"></i>&nbsp;VEHICULO</span> -->
                                        </div>
                                        <input type="text" value="" onkeyup="javascript:this.value=this.value.toUpperCase();" class="form-control" name="vehiculo" id="vehiculo" placeholder="Vehiculo" aria-label="vehiculo" aria-describedby="basic-addon1" required='true' autofocus>
                                    </div>
                                    <div class="mb-1" id="select" name="select">
                                        <!-- <label class="form-label">Categoria vehiculo </label> -->
                                        <div class="input-group mb-1">
                                            <div class="input-group-prepend">
                                                <!-- <span class="input-group-text" id="basic-addon1"><i class="fas fa-tachometer-alt"></i>&nbsp;CATEGORIA</span> -->
                                            </div>

                                            <select name="categoria" id="categoria" required='true' class="form-control" autofocus>
                                                <option hidden selected value="">Seleccione categoria de vehiculo</option>
                                                <?php
                                                $cats = $pdo->query("SELECT cat_id, cat_nombre FROM categorias ORDER BY cat_nombre");
                                                foreach ($cats as $c) {
                                                    echo "<option value='{$c['cat_id']}'>{$c['cat_nombre']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-1" id="caseta" name="caseta">
                                        <!-- <label class="form-label">Categoria vehiculo </label> -->
                                        <div class="input-group mb-1">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-tachometer-alt"></i>&nbsp;CASETA&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                            </div>

                                            <select name="casetas" id="casetas" required='true' class="form-control" autofocus>
                                                <option hidden selected value="">Seleccione el # de la caseta</option>
                                                <?php
                                                $casetas = $pdo->query("SELECT caseta_id, casetas_nom FROM casetas WHERE casetas_estado='Disponible' order by casetas_nom");
                                                foreach ($casetas as $c) {
                                                    echo "<option value='{$c['caseta_id']}'>{$c['casetas_nom']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <input type="hidden" name="usuario" value="<?php echo $_SESSION['id']; ?>" class="form-control" maxlength="6" required>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success btn btn-block">
                                            <div class="spinner-grow spinner-grow-sm text-light" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <i class="bi bi-plus-lg text-white">&nbsp;Registrar Ingreso</i>
                                        </button>
                                    </div>


                                </div>
                            </form>
                        </div>
                        <!-- cards vehiculos-->
                        <div class="col col-lg-7">
                            <div id="cards" class="row">
                            </div>
                        </div>
                        <!-- fin cards vehiculos-->
                    </div>
                </div>
                <div class="my-4">

                    <!-- Tabla -->
                    <h4>Vehículos en Parqueo</h4>
                    <div id="tablaParqueo"></div>
                </div>


                <script>
                    $(document).ready(function() {
                        // Cargar tabla al inicio
                        cargarTabla();

                        //blur
                        //focus



                        document.getElementById("placa").addEventListener("keyup", function() {

                            let placa = this.value;

                            if (placa.length < 5) return;

                            fetch("buscar_vehiculo.php?placa=" + placa)
                                .then(res => res.json())
                                .then(data => {

                                    if (data) {

                                        document.getElementById("nombre").value = data.nombre ?? "";
                                        document.getElementById("celular").value = data.celular ?? "";
                                        document.getElementById("vehiculo").value = data.vehiculo ?? "";
                                        document.getElementById("categoria").value = data.categoria ?? "";

                                    }

                                });

                        });


                           
                            

                        // Envío del formulario con AJAX
                        $('#formParqueo').on('submit', function(e) {

                            e.preventDefault();

                            let form = this;

                            $.ajax({
                                url: 'parqueo_procesar.php',
                                type: 'POST',
                                data: $(form).serialize(),
                                dataType: 'json',

                                beforeSend: function() {
                                    $('button[type=submit]').prop('disabled', true);
                                },

                                success: function(response) {

                                    console.log(response);
                                    alert(response.message);

                                    // ❌ Si hay error (placa ya registrada)
                                    if (response.status === 'error') {

                                        form.reset(); // limpiar formulario
                                        $('#placa').focus(); // volver al campo placa

                                    }

                                    // ✅ Registro exitoso
                                    if (response.status === 'success') {

                                        window.open(
                                            '../modulos/imprimir_ticket_php/ticket_hora.php',
                                            '_blank',
                                            'width=400,height=600'
                                        );

                                        form.reset(); // limpiar formulario
                                        $('#placa').focus();

                                        cargarTabla();
                                        location.reload();

                                    }

                                },

                                complete: function() {
                                    $('button[type=submit]').prop('disabled', false);
                                }

                            });

                        });
                        // Recarga periódica cada 15 segundos sin congelar el navegador
                        setInterval(cargarTabla, 15000);
                        setInterval(obtenerCards, 15000);
                        // Envío del formulario con AJAX  

                        //ajax list parqueo cards 
                        function formatCurrency(value, currency) {
                            return new Intl.NumberFormat('es-ES', {
                                style: 'currency',
                                currency: 'COP',
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                        let cargando = false;

                        function obtenerCards() {
                            if (cargando) return; // evita duplicar peticiones
                            cargando = true;
                            $.ajax({
                                url: 'park-list.php',
                                cache: false,
                                type: 'POST',
                                success: function(response) {
                                    let parks = JSON.parse(response);
                                    // console.log(response);
                                    let template = '';

                                    parks.forEach(park => {
                                        template +=
                                            `<div parkId="${park.placa_cli}" 
                                        ticketId="${park.parqueo_id}"
                                        fechaIni="${park.fecha_ini}"
                                        fechaFin="${park.fecha_fin}"
                                        tiempoId="${park.tiempo}"
                                        valorId="${park.valor}"
                                        usuarioId="${park.usuario}"
                                        categoria="${park.categoria}"
                                        cat_nombre="${park.cat_nombre}"

                                        caja_movimientoId="1"
                                        caja_desc_movimientoId="${park.placa_cli} por ${park.tar_tiempo}"
                                        caja_egresosId="0"
                                        liquidadoId="NO"
                                        caja_tipoId="ingreso"

                                        class="col col-lg-3" 
                                        id="btn_parqueo" name="btn_parqueo">
                                        <form id="pagar" class="">
                                            
                                            <span><img class="logo_parqueo" id="logo_parqueo" src="${park.cat_imagen}"></img></span>                    
                                            <h7 class="placa_parqueo" id="placa_cli">${park.placa_cli} </h7> <br>                        
                                                
                                            <h6 class="tiempo_parqueo" id="tiempo_parqueo">${park.tiempo}</h6>             

                                            <h7 class="avisos_parqueo">Valor a pagar:</h7>
                                            <h6 class="pago_parqueo" id="pago_parqueo">$${formatCurrency(park.valor, 'COP')}</h6>
                                            
                                            <h6 class="pago_parqueo" id="pago_parqueo">${park.cat_nombre}</h6>

                                            
                                            <button type="submit"
                                                    id="btnParqueo_pagar" 
                                                    onclick=""
                                                    class="btnParqueo_pagar" name="btnParqueo_pagar"  href="">
                                                        
                                                    <div class="spinner-grow spinner-grow-sm text-light" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <i class="bi bi-plus-lg text-white">&nbsp;PAGAR</i>
                                            </button>
                                            
                                        </form>
                                    </div> `
                                    });
                                    $('#cards').html(template);
                                },
                                complete: function() {
                                    cargando = false; // libera cuando termina
                                },
                                error: function() {
                                    cargando = false;
                                }
                            });
                        }
                        //ajax list parqueo cards    

                        obtenerCards(); // primera carga
                        setTimeout(() => {
                            obtenerCards(); // primera carga
                            setInterval(obtenerCards, 30000);
                        }, 5000);
                    });

                    setInterval(() => {
                        location.reload();
                    }, 600000); // 30 min

                    function cargarTabla() {
                        $.get('parqueo_listar.php', function(html) {
                            $('#tablaParqueo').html(html);
                        });
                    }
                </script>
    </main>
</div>
</body>

</html>