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
    <meta charset="UTF-8">
    <title>Registro de Parqueo por Horas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="p-4 bg-light">
<div class="container">
    <h3 class="mb-4 text-center">Registro de Parqueo por Horas</h3>

    <!-- Formulario -->
    <form id="formParqueo" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Placa</label>
            <input type="text" name="placa" class="form-control" maxlength="6" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Nombre del Cliente</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Vehículo</label>
            <input type="text" name="vehiculo" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Categoría</label>
            <select name="categoria" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php
                $cats = $pdo->query("SELECT cat_id, cat_nombre FROM categorias ORDER BY cat_nombre");
                foreach ($cats as $c) {
                    echo "<option value='{$c['cat_id']}'>{$c['cat_nombre']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Caseta</label>
            <select name="caseta" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php
                $casetas = $pdo->query("SELECT caseta_id, casetas_nom FROM casetas WHERE casetas_estado='Disponible' order by casetas_nom");
                foreach ($casetas as $c) {
                    echo "<option value='{$c['caseta_id']}'>{$c['casetas_nom']}</option>";
                }
                ?>
            </select>
        </div>

        <input type="hidden" name="usuario" value="<?php echo $_SESSION['id']; ?>" class="form-control" maxlength="6" required>        

        <div class="col-md-12 text-center mt-3">
            <button type="submit" class="btn btn-success px-4">Registrar Ingreso</button>
        </div>
    </form>

    <hr class="my-4">

    <!-- Tabla -->
    <h4>Vehículos en Parqueo</h4>
    <div id="tablaParqueo"></div>
</div>

<script>
$(document).ready(function(){
    // Cargar tabla al inicio
    cargarTabla();

    // Envío del formulario con AJAX
    $('#formParqueo').on('submit', function(e){
        e.preventDefault();

        $.ajax({
            url: 'parqueo_procesar.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function(){
                $('button[type=submit]').prop('disabled', true).text('Guardando...');
            },
            success: function(resp){
                if(resp.ok){
                    alert('Registro guardado correctamente');
                    $('#formParqueo')[0].reset();
                    cargarTabla();
                } else {
                    alert('Error: ' + resp.error);
                }
            },
            complete: function(){
                $('button[type=submit]').prop('disabled', false).text('Registrar Ingreso');
            }
        });
    });

    // Recarga periódica cada 15 segundos sin congelar el navegador
    setInterval(cargarTabla, 15000);
});

function cargarTabla(){
    $.get('parqueo_listar.php', function(html){
        $('#tablaParqueo').html(html);
    });
}
</script>
</body>
</html>
