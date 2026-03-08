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

$sql = "SELECT 
            c.caseta_id,
            c.casetas_nom,
            c.casetas_loc,
            c.casetas_estado,
            IF(p.parqueo_id IS NULL,'Disponible','Ocupada') estado,
            p.placa_cli
            
           
        FROM casetas c

        LEFT JOIN parqueo p 
            ON p.caseta = c.caseta_id 
            AND p.estado = 'SI'
        
        ORDER BY c.caseta_id";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$casetas = $stmt->fetchAll();
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

    <style>
        .caseta-card {
            transition: 0.3s;
        }

        .caseta-card:hover {
            transform: scale(1.05);
        }

        .Disponible {
            border-left: 6px solid #198754;
        }

        .Ocupado {
            border-left: 6px solid #dc3545;
        }
    </style>
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">

        <body class="bg-light">

<div class="container mt-4">

<h3 class="mb-4">Estado de Casetas</h3>

<div class="row g-3">

<?php foreach($casetas as $c): ?>

<div class="col-md-3 col-sm-6">

<div class="card caseta-card 
<?php echo ($c['casetas_estado']=='Disponible') ? 'Disponible':'Ocupado'; ?>">

<div class="card-body text-center">

<h5 class="card-title">
<?php echo $c['casetas_nom']; ?>
</h5>

<p class="card-text text-muted">
<?php echo $c['casetas_loc']; ?>
</p>

<?php if($c['casetas_estado']=='Disponible'): ?>

<span class="badge bg-success fs-6">
Disponible
</span>

<?php else: ?>

<span class="badge bg-danger fs-6">
Ocupado
</span>

<div class="mt-2">
Placa: <strong><?php echo $c['placa_cli']; ?></strong>
</div>

<?php endif; ?>

</div>
</div>

</div>

<?php endforeach; ?>

</div>
</div>

</body>
    </main>
</div>


</html>