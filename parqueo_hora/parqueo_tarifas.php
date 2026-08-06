<?php
session_start();
require_once "../conexion/conexion.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Bogota');

// Control de inactividad: cerrar sesión después de 3 minutos (180 segundos)
$inactive = 3 * 60; // 3 minutos
// Para producción cambiar a: 20 * 60 (20 minutos)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?mensaje=timeout");
    exit();
}
// actualizar último tiempo de actividad
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
}
$id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE id=$id";
}

$sql = "SELECT 
t.tar_id,
c.cat_nombre,
tt.tar_tiempo,
t.tar_valor,
t.tar_bloque
FROM tarifas t
INNER JOIN categorias c ON c.cat_id = t.tar_categoria
INNER JOIN tar_tiempo tt ON tt.tar_id_nombre = t.tar_nombre
ORDER BY c.cat_nombre";

$stmt = $pdo->query($sql);

$datos = $stmt->fetchAll();

if(!$stmt){
    print_r($pdo->errorInfo());
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

                <h3 class="mb-4">Tarifas por horas y periodos de vehiculos</h3>
                
     <?php           


?>
                <table class="table table-bordered table-hover">
<thead class="table-dark">
<tr>
<!-- <th>ID</th> -->
<th>Categoria</th>
<!-- <th>Tiempo</th> -->
<th>$ Hora</th>
<th>$ 12 Horas</th>
<th>Acción</th>
</tr>
</thead>

<tbody>

<?php foreach($datos as $row){ ?>

<tr>
<!-- <td><?= $row['tar_id'] ?></td> -->
<td><?= $row['cat_nombre'] ?></td>
<!-- <td><?= $row['tar_tiempo'] ?></td> -->

<td>
<input 
type="text"
class="form-control dinero"

data-id="<?= $row['tar_id'] ?>"
value="$<?= number_format($row['tar_valor'],0,',','.') ?>">
</td>

<td>
<input 
type="text"
class="form-control bloque"

data-id="<?= $row['tar_id'] ?>"
value="$<?= number_format($row['tar_bloque'],0,',','.') ?>">
</td>



<td>
<button class="btn btn-primary btnGuardar" data-id="<?= $row['tar_id'] ?>">
Guardar
</button>
</td>

</tr>

<?php } ?>

</tbody>
</table>
            </div>
           

<script>

function formatoCOP(valor){
    
    valor = valor.replace(/\D/g,'');

    return '$' + new Intl.NumberFormat('es-CO').format(valor);
}

document.querySelectorAll('.dinero').forEach(input => {

    input.addEventListener('keyup', function(){

        let numero = this.value.replace(/\D/g,'');

        if(numero !== ''){
            this.value = formatoCOP(numero);
        }

    });

});

function formatoCOP(bloque){
    
    bloque = bloque.replace(/\D/g,'');

    return '$' + new Intl.NumberFormat('es-CO').format(bloque);
}

document.querySelectorAll('.bloque').forEach(input => {

    input.addEventListener('keyup', function(){

        let numero = this.value.replace(/\D/g,'');

        if(numero !== ''){
            this.value = formatoCOP(numero);
        }

    });

});



$('.btnGuardar').click(function(){

    let id = $(this).data('id');

    let valor = $('input.dinero[data-id="'+id+'"]').val();
    let bloque = $('input.bloque[data-id="'+id+'"]').val();

    // quitar formato $
    valor = valor.replace(/\$/g,'').replace(/\./g,'');
    bloque = bloque.replace(/\$/g,'').replace(/\./g,'');

    $.ajax({
        url:'actualizar_tarifa.php',
        type:'POST',
        data:{
            id:id,
            valor:valor,
            bloque:bloque,
        },

        success:function(){

            alert("Tarifa actualizada");

            setTimeout(function(){
                location.reload();
            },800);

        }

    });

});







</script>

        </body>
    </main>
</div>


</html>