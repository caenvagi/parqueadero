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

</head>
<?php require '../logs/nav-bar.php'; ?>
<>


    <div id="layoutSidenav_content">
        <main class="ms-5 me-5">
            <div class="container" id="cont-parqueo1">
                <div class="row">
                    <div class="col col-12 col-sm-12 col-md-5 col-lg-5 col-xl-5 m-3">
                        <!-- <div id="respuesta"></div> -->
                        <form id="formMensualidad" name="formMensualidad" action="">
                            <div class="card" id="cardForm_parqueo">
                                
                                <div class="header"><i class="bi bi-receipt-cutoff"></i>  Ingresar datos del recibo:</div>
                                <!-- INPUT NUMERO RECIBO -->
                                <div class="input-group mb-2">
                                    <input type="hidden" value="" class="form-control" id="recibo_id" name="recibo_id" placeholder="parqueo_id" aria-label="parqueo_id" aria-describedby="basic-addon1">
                                </div>
                                <!-- INPUT NUMERO RECIBO -->
                                <!-- INPUT RECIBO -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-address-card"></i>&nbsp;RECIBO</span>
                                    </div>
                                    <input type="text" oninput="this.value = this.value.toUpperCase()" class="form-control" name="recibo" id="recibo" placeholder="N° recibo" aria-label="recibo" aria-describedby="basic-addon1" required='true' autofocus>
                                </div>
                                <small id="msg_recibo"></small>

                                <!-- INPUT PLACA -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <!-- <span class="input-group-text" id="basic-addon1"><i class="fas fa-tachometer-alt"></i>&nbsp;PLACA</span> -->
                                    </div>
                                    <input type="text"
                                        value=""
                                        onkeyup="javascript:this.value=this.value.toUpperCase();"
                                        class="form-control"
                                        name="placa"
                                        id="placa"
                                        maxlength="6"
                                        placeholder="Placa"
                                        aria-label="placa"
                                        >
                                </div>
                                <!-- INPUT PLACA -->

                                <!-- INPUT FECHA_INICIAL -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-address-card"></i>&nbsp;Fecha Inicial</span>
                                    </div>
                                    <input type="datetime-local" class="form-control" name="fecha_ini" id="fecha_ini" placeholder="Fecha Inicial" aria-label="fecha_ini" aria-describedby="basic-addon1" required='true'>
                                </div>

                                <!-- INPUT FECHA_FINAL -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-address-card"></i>&nbsp;Fecha Final</span>
                                    </div>
                                    <input type="datetime-local" class="form-control" name="fecha_fin" id="fecha_fin" placeholder="Fecha Final" aria-label="fecha_fin" aria-describedby="basic-addon1" required='true'>
                                </div>

                                <!-- INPUT FECHA_FINAL -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-address-card"></i>&nbsp; TIEMPO</span>
                                    </div>
                                    <input type="text" class="form-control" name="tiempo" id="tiempo" placeholder="Tiempo" aria-label="fecha_fin" aria-describedby="basic-addon1" required='true'>
                                </div>


                                <!-- INPUT NOMBRE -->
                                <!-- <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-user-alt"></i>&nbsp;NOMBRE</span>
                                    </div>
                                    <input type="text" value="" onkeyup="javascript:this.value=this.value.toUpperCase();" class="form-control" name="nombre" id="nombre" placeholder="Nombre" aria-label="nombre" aria-describedby="basic-addon1" required autofocus>
                                </div> -->
                                <!-- INPUT NOMBRE -->
                                <!-- INPUT CEDULA -->
                                <!-- <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-address-card"></i>&nbsp;CEDULA</span>
                                    </div>
                                    <input type="number" pattern="[0-9]{10}" class="form-control" name="cedula" id="cedula" placeholder="N° Cedula" aria-label="cedula" aria-describedby="basic-addon1" required='true'>
                                </div> -->
                                <!-- INPUT CEDULA -->
                                <!-- INPUT CELULAR -->
                                <!-- <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-mobile-alt"></i>&nbsp;CELULAR</span>
                                    </div>
                                    <input type="number" pattern="[0-9]{10}" class="form-control" name="celular" id="celular" placeholder="N° Celular" aria-label="celular" aria-describedby="basic-addon1" required='true'>
                                </div> -->
                                <!-- INPUT CELULAR -->
                                <!-- INPUT TIPO DE VEHICULO -->
                                <!-- <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-car-alt"></i>&nbsp;VEHICULO</span>
                                    </div>
                                    <input type="text" class="form-control" name="vehiculo" id="vehiculo" placeholder="Vehiculo marca" onkeyup="javascript:this.value=this.value.toUpperCase();" aria-label="vehiculo" aria-describedby="basic-addon1" required='true'>
                                </div> -->
                                <!-- INPUT TIPO DE VEHICULO -->

                                <!-- SELECT CATEGORIA -->
                                <div class="mb-1">
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fas fa-sitemap"></i>&nbsp;CATEGORIA</span>
                                        </div>
                                        <select name="categoria" id="categoria" required='true' class="form-control">
                                            <option hidden selected value="">Seleccione categoria de vehiculo</option>
                                            <?php

                                            $sql1 = "SELECT * FROM categorias";
                                            $stmt1 = $pdo->query($sql1);

                                            foreach ($stmt1 as $row) {
                                                echo "<option value='{$row['cat_id']}'>{$row['cat_nombre']}</option>";
                                            }

                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- SELECT CATEGORIA -->
                                <!-- SELECT TARIFA -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class="fas fa-calendar-alt"></i>&nbsp;PLAN</span>
                                    </div>
                                    <select name="plan" id="plan" required='true' class="form-control">
                                       <?php

                                        $sql2 = "SELECT * FROM tar_tiempo order by tar_tiempo";
                                        $stmt2 = $pdo->query($sql2);

                                        foreach ($stmt2 as $row) {
                                            echo "<option value='{$row['tar_id_nombre']}' selected>{$row['tar_tiempo']}</option>";
                                        }                                     

                                        ?>

                                         <option selected value="">Seleccione plan de pago</option>
                                    </select>
                                </div>
                                <!-- SELECT TARIFA -->

                                <!-- SELECT CASETAS -->
                                <!-- <div class="mb-1">
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fas fa-warehouse"></i>&nbsp;UBICACION:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                        </div>
                                        <select name="caseta" id="caseta" required='true' class="form-control">
                                            <option hidden selected value="">Seleccione el N° de la ubicacion:</option>
                                            <?php

                                            $sql = "SELECT *
                                                                        FROM casetas
                                                                        WHERE casetas_estado = 'Disponible'
                                                                        ";

                                            $stmt = $pdo->query($sql);

                                            foreach ($stmt as $row) {
                                                echo "<option value='{$row['caseta_id']}'>{$row['casetas_nom']}</option>";
                                            }

                                            ?>

                                        </select>
                                    </div>
                                </div> -->
                                <!-- SELECT CASETAS -->
                                <!-- INPUT VALOR -->
                                <div class="mb-1" id="tarifas" name="tarifas">
                                    <!-- <label class="form-label">Categoria vehiculo </label> -->
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fas fa-hand-holding-usd"></i>&nbsp;VALOR&nbsp;</span>
                                        </div>
                                        <input type="text" class="form-control" name="valor" id="valor" placeholder="valor" onkeyup="javascript:this.value=this.value.toUpperCase();" aria-label="vehiculo" aria-describedby="basic-addon1" required='true'>
                                    </div>
                                </div>
                                <!-- INPUT VALOR -->
                                <!-- INPUT ACTIVO -->
                                <!-- <div class="mb-1" id="activo1" name="activo">
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fas fa-user-alt-slash"></i>&nbsp;Activo&nbsp;</span>
                                        </div>
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                        <div class="row justify-content-center align-items-center">
                                            <center><label class="form-label">Cliente Activo </label></center>}
                                            <div class="justify-content-center input-group mb-0 text-center">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" id="inlineCheckbox1" name="activo" value="SI">
                                                    <label class="form-check-label" for="inlineCheckbox1">SI</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" id="inlineCheckbox2" name="activo" value="NO">
                                                    <label class="form-check-label" for="inlineCheckbox2">NO</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                <!-- INPUT ACTIVO -->
                                <!-- INPUT HIDDEN ID USUARIO -->
                                <div class="input-group mb-2">
                                    <input type="hidden" value="<?php echo $id ?>" class="form-control" id="user" name="user" placeholder="user" aria-label="user" aria-describedby="basic-addon1">
                                </div>
                                <!-- INPUT HIDDEN ID USUARIO -->
                                <!-- BOTON GUARDAR -->
                                <div class="d-grid gap-2">
                                    <button onclick="" type="submit" class="btn btn-secondary btn btn-block" name="register" id="register" href="">
                                        <div class="spinner-grow spinner-grow-sm text-light" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <i class="bi bi-plus-lg text-white">&nbsp;GUARDAR</i>
                                    </button>
                                </div>
                                <!-- BOTON GUARDAR -->
                            </div>
                        </form>
                    </div>
                    <!-- 🔵 PANEL RESPUESTA -->
                    <div class="col-12 col-md-6 col-lg-5 m-3">
                        <div class="card shadow-sm">
                            <div class="card-body" id="respuesta">
                                <div class="text-center text-muted">

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 🔵 PANEL RESPUESTA -->
                </div>
            </div>
        </main>
    </div>
    <script id="v9d3hf">
let reciboValido = false;

$("#recibo").on("keyup blur", function() {

    let recibo = $(this).val().trim();

    if (recibo === "") {
        $("#msg_recibo").html("");
        reciboValido = false;
        return;
    }

    $.ajax({
        url: "validar_recibo.php",
        type: "POST",
        data: {recibo: recibo},
        success: function(resp) {

            if (resp === "existe") {
                $("#msg_recibo").html("<span class='text-danger'>❌ Ya existe este recibo</span>");
                $("#recibo").addClass("is-invalid").removeClass("is-valid");
                reciboValido = false;
            } else {
                $("#msg_recibo").html("<span class='text-success'>✔ Disponible</span>");
                $("#recibo").addClass("is-valid").removeClass("is-invalid");
                reciboValido = true;
            }
        }
    });
});
</script>
    <script>
        $("#formMensualidad").submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "guardar_recibo.php",
                type: "POST",
                data: $(this).serialize(),

                success: function(resp) {
                    $("#respuesta").html(resp);
                    $("#formMensualidad")[0].reset();
                }
            });
        });


        function calcularTiempo() {
            const fechaIni = document.querySelector('[name="fecha_ini"]').value;
            const fechaFin = document.querySelector('[name="fecha_fin"]').value;

            if (fechaIni && fechaFin) {
                let inicio = new Date(fechaIni);
                let fin = new Date(fechaFin);

                if (fin > inicio) {

                    let temp = new Date(inicio);

                    let meses = 0;
                    while (true) {
                        let next = new Date(temp);
                        next.setMonth(next.getMonth() + 1);

                        if (next <= fin) {
                            temp = next;
                            meses++;
                        } else break;
                    }

                    let dias = 0;
                    while (true) {
                        let next = new Date(temp);
                        next.setDate(next.getDate() + 1);

                        if (next <= fin) {
                            temp = next;
                            dias++;
                        } else break;
                    }

                    let horas = 0;
                    while (true) {
                        let next = new Date(temp);
                        next.setHours(next.getHours() + 1);

                        if (next <= fin) {
                            temp = next;
                            horas++;
                        } else break;
                    }

                    let minutos = 0;
                    while (true) {
                        let next = new Date(temp);
                        next.setMinutes(next.getMinutes() + 1);

                        if (next <= fin) {
                            temp = next;
                            minutos++;
                        } else break;
                    }

                    let resultado = [];

                    if (meses > 0) resultado.push(meses + (meses == 1 ? ' mes' : ' meses'));
                    if (dias > 0) resultado.push(dias + (dias == 1 ? ' día' : ' días'));
                    if (horas > 0) resultado.push(horas + (horas == 1 ? ' hora' : ' horas'));
                    if (minutos > 0) resultado.push(minutos + (minutos == 1 ? ' minuto' : ' minutos'));

                    if (resultado.length === 0) {
                        resultado.push('0 minutos');
                    }

                    document.getElementById('tiempo').value = resultado.join(', ');

                } else {
                    document.getElementById('tiempo').value = '';
                }
            }
        }

        // Eventos automáticos
        document.querySelector('[name="fecha_ini"]').addEventListener('change', calcularTiempo);
        document.querySelector('[name="fecha_fin"]').addEventListener('change', calcularTiempo);
    </script>
    </body>

</html>