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
    die("Placa no válida");
}

$stmt = $pdo->prepare(
    "SELECT * 
    FROM cliente 
    INNER JOIN categorias ON cliente.categoria = categorias.cat_id
    INNER JOIN casetas ON cliente.caseta = casetas.caseta_id
    INNER JOIN tar_tiempo ON cliente.cli_tar_tiempo = tar_tiempo.tar_id_nombre
    WHERE placa = ?"
);
$stmt->execute([$placa]);
$cliente = $stmt->fetch();

if (!$cliente) {
    die("Cliente no encontrado");
}

$stmtCat = $pdo->query("SELECT cat_id, cat_nombre FROM categorias");
$categorias = $stmtCat->fetchAll();

$stmtCas = $pdo->query("SELECT caseta_id, casetas_nom FROM casetas");
$casetas = $stmtCas->fetchAll();

$stmtPlan = $pdo->query("SELECT tar_id_nombre, tar_tiempo FROM tar_tiempo WHERE tar_id_nombre IN (3, 6, 7)");
$planes = $stmtPlan->fetchAll();

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

                <div class="container mt-4">
                    <h4>Editar Cliente</h4>

                    <div id="respuesta"></div>

                    <form id="formEditarCliente">
                        <input type="hidden" name="placa" value="<?= $cliente['placa'] ?>">

                        <div class="row">

                            <div class="col-md-4">
                                <label>Placa</label>
                                <input type="text" id="placa" name="placa" class="form-control" value="<?= $cliente['placa'] ?>">
                            </div>

                            <div class="col-md-4">
                                <label>Propietario</label>
                                <input type="text" name="nombre" class="form-control" value="<?= $cliente['nombre'] ?>">
                            </div>

                            <div class="col-md-4">
                                <label>Cédula</label>
                                <input type="number" name="cedula" class="form-control" value="<?= $cliente['cedula'] ?>">
                            </div>

                            <div class="col-md-4">
                                <label>Celular</label>
                                <input type="number" name="celular" class="form-control" value="<?= $cliente['celular'] ?>">
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Categoría</label>
                                <select name="categoria" class="form-select" required>
                                    <option value="">Seleccione...</option>


                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['cat_id'] ?>"
                                            <?= $cliente['categoria'] == $cat['cat_id'] ? 'selected' : '' ?>>
                                            <?= $cat['cat_nombre'] ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Caseta</label>
                                <select name="caseta" class="form-select" required>
                                    <option value="">Seleccione...</option>


                                    <?php foreach ($casetas as $cas): ?>
                                        <option value="<?= $cas['caseta_id'] ?>"
                                            <?= $cliente['caseta'] == $cas['caseta_id'] ? 'selected' : '' ?>>
                                            <?= $cas['casetas_nom'] ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Vehículo</label>
                                <input type="text" name="vehiculo" class="form-control" value="<?= $cliente['vehiculo'] ?>">
                            </div>

                            <input type="hidden" name="plan" class="form-control" value="<?= $cliente['cli_tar_tiempo'] ?>">

                            <div class="col-md-4 mt-2">
                                <label>Plan</label>
                                <select name="plan_tarifa" class="form-select" required>
                                    <option value="">Seleccione...</option>


                                    <?php foreach ($planes as $plan): ?>
                                        <option value="<?= $plan['tar_id_nombre'] ?>"
                                            <?= $cliente['cli_tar_tiempo'] == $plan['tar_id_nombre'] ? 'selected' : '' ?>>
                                            <?= $plan['tar_tiempo'] ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Valor</label>
                                <input type="number" name="valor" class="form-control" value="<?= $cliente['valor'] ?>">
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Mensualidad</label>
                                <select name="mensualidad" class="form-control">
                                    <option value="SI" <?= $cliente['mensualidad'] == 'SI' ? 'selected' : '' ?>>SI</option>
                                    <option value="NO" <?= $cliente['mensualidad'] == 'NO' ? 'selected' : '' ?>>NO</option>
                                </select>
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Estado</label>
                                <select name="activo" class="form-control">
                                    <option value="SI" <?= $cliente['activo'] == 'SI' ? 'selected' : '' ?>>Activo</option>
                                    <option value="NO" <?= $cliente['activo'] == 'NO' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <button class="btn btn-primary mt-3">Actualizar</button>

                        <button type="button" id="btnDesactivar" class="btn btn-danger mt-3">
                            Desactivar Mensualidad
                        </button>
                    </form>
                </div>

                <script>
                    $("#formEditarCliente").submit(function(e) {
                        e.preventDefault();
                         let placa = $("#placa").val(); // ✅ obtener placa                     
                        $.ajax({
                            url: "actualizar_cliente.php",
                            type: "POST",
                            data: $(this).serialize(),
                            success: function(resp) {
                                $("#respuesta").html(resp);
                                console.log(placa); // ✅ depuración                                
                                    window.location.href = "mens_pagar.php?placa=" + placa;
                                 
                            }
                        });
                    });

                    $("#btnDesactivar").click(function() {

                        if (!confirm("¿Seguro que deseas desactivar la mensualidad?")) {
                            return;
                        }

                        let placa = $("input[name='placa']").val();

                        $.ajax({
                            url: "desactivar_mensualidad.php",
                            type: "POST",
                            data: {
                                placa: placa
                            },
                            success: function(resp) {
                                $("#respuesta").html(resp);

                                setTimeout(function() {
                                    window.location.href = "clientes_mensualidad.php";
                                }, 1000);
                            }
                        });

                    });
                </script>



            </div>
        </body>
    </main>
</div>

</html>