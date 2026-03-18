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
    
</head>
<?php require '../logs/nav-bar.php'; ?>

<body class="bg-light">
    <div id="layoutSidenav_content">
        <main class="ms-5 me-5">
            <div class="container" id="cont-parqueo1">
                <div class="row">
                    <div class="col col-12 col-sm-12 col-md-5 col-lg-5 col-xl-5 m-3">
                        <div id="respuesta"></div>
                        <form id="formMensualidad" name="formMensualidad" action="">
                            <div class="card" id="cardForm_parqueo">
                                <div class="header">Ingresar datos de la mensualidad:</div>
                                <!-- INPUT NUMERO RECIBO -->
                                <div class="input-group mb-2">
                                    <input type="hidden" value="" class="form-control" id="recibo_id" name="recibo_id" placeholder="parqueo_id" aria-label="parqueo_id" aria-describedby="basic-addon1">
                                </div>
                                <!-- INPUT NUMERO RECIBO -->
                                <!-- INPUT PLACA -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <!-- <span class="input-group-text" id="basic-addon1"><i class="fas fa-tachometer-alt"></i>&nbsp;PLACA</span> -->
                                    </div>
                                    <input type="text" value="" onkeyup="javascript:this.value=this.value.toUpperCase();" class="form-control" name="placa" id="placa" placeholder="Placa" aria-label="placa" aria-describedby="basic-addon1" required='true' autofocus>
                                </div>
                                <!-- INPUT PLACA -->
                                <!-- INPUT NOMBRE -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-user-alt"></i>&nbsp;NOMBRE</span>
                                    </div>
                                    <input type="text" value="" onkeyup="javascript:this.value=this.value.toUpperCase();" class="form-control" name="nombre" id="nombre" placeholder="Nombre" aria-label="nombre" aria-describedby="basic-addon1" required='true' autofocus>
                                </div>
                                <!-- INPUT NOMBRE -->
                                <!-- INPUT CEDULA -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-address-card"></i>&nbsp;CEDULA</span>
                                    </div>
                                    <input type="number" pattern="[0-9]{10}" class="form-control" name="cedula" id="cedula" placeholder="N° Cedula" aria-label="cedula" aria-describedby="basic-addon1" required='true' autofocus>
                                </div>
                                <!-- INPUT CEDULA -->
                                <!-- INPUT CELULAR -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-mobile-alt"></i>&nbsp;CELULAR</span>
                                    </div>
                                    <input type="number" pattern="[0-9]{10}" class="form-control" name="celular" id="celular" placeholder="N° Celular" aria-label="celular" aria-describedby="basic-addon1" required='true' autofocus>
                                </div>
                                <!-- INPUT CELULAR -->
                                <!-- INPUT TIPO DE VEHICULO -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-car-alt"></i>&nbsp;VEHICULO</span>
                                    </div>
                                    <input type="text" class="form-control" name="vehiculo" id="vehiculo" placeholder="Vehiculo marca" onkeyup="javascript:this.value=this.value.toUpperCase();" aria-label="vehiculo" aria-describedby="basic-addon1" required='true' autofocus>
                                </div>
                                <!-- INPUT TIPO DE VEHICULO -->
                                <!-- SELECT TARIFA -->
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-calendar-alt"></i>&nbsp;PLAN</span>
                                    </div>
                                    <select name="plan" id="plan" required='true' class="form-control" autofocus>
                                        <option hidden selected value="">Seleccione el plan a pagar</option>
                                        <?php


                                        $sql = "SELECT * FROM tar_tiempo WHERE tar_id_nombre IN (3,6,7)";
                                        $stmt = $pdo->query($sql);

                                        foreach ($stmt as $row) {
                                            echo "<option value='{$row['tar_id_nombre']}'>{$row['tar_tiempo']}</option>";
                                        }

                                        ?>
                                    </select>
                                </div>
                                <!-- SELECT TARIFA -->
                                <!-- SELECT CATEGORIA -->
                                <div class="mb-1">
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fas fa-sitemap"></i>&nbsp;CATEGORIA</span>
                                        </div>
                                        <select name="categoria" id="categoria" required='true' class="form-control" autofocus>
                                            <option hidden selected value="">Seleccione categoria de vehiculo</option>
                                            <?php

                                            $sql = "SELECT * FROM categorias";
                                            $stmt = $pdo->query($sql);

                                            foreach ($stmt as $row) {
                                                echo "<option value='{$row['cat_id']}'>{$row['cat_nombre']}</option>";
                                            }

                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- SELECT CATEGORIA -->
                                <!-- SELECT CASETAS -->
                                <div class="mb-1">
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fas fa-warehouse"></i>&nbsp;Ubicacion:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                        </div>
                                        <select name="caseta" id="caseta" required='true' class="form-control" autofocus>
                                            <option hidden selected value="">Seleccione el N° de la ubicacion:</option>
                                            <?php

                                            $sql = "SELECT *
                                                                        FROM casetas
                                                                        WHERE caseta_id NOT IN (
                                                                        SELECT caseta FROM cliente WHERE mensualidad='SI'
                                                                        )";

                                            $stmt = $pdo->query($sql);

                                            foreach ($stmt as $row) {
                                                echo "<option value='{$row['caseta_id']}'>{$row['casetas_nom']}</option>";
                                            }

                                            ?>

                                        </select>
                                    </div>
                                </div>
                                <!-- SELECT CASETAS -->
                                <!-- INPUT VALOR -->
                                <div class="mb-1" id="tarifas" name="tarifas">
                                    <!-- <label class="form-label">Categoria vehiculo </label> -->
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fas fa-hand-holding-usd"></i>&nbsp;VALOR&nbsp;</span>
                                        </div>
                                        <input type="text" class="form-control" name="valor" id="valor" placeholder="valor" onkeyup="javascript:this.value=this.value.toUpperCase();" aria-label="vehiculo" aria-describedby="basic-addon1" required='true' autofocus>
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
                </div>
            </div>
        </main>
    </div>
        <script>
            $("#formMensualidad").submit(function(e) {

                e.preventDefault();

                $.ajax({

                    url: "mens_cliente_guardar.php",
                    type: "POST",
                    data: $(this).serialize(),

                    success: function(resp) {
                        $("#respuesta").html(resp);
                        $("#formMensualidad")[0].reset();
                    } 
                                        

                });
                

            });
            
        </script>
</body>

</html>