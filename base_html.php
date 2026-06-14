<?php
session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

// Tiempo de inactividad en segundos (20 minutos)
// Este es el valor de producción. Para pruebas locales, reemplaza por un valor menor (ej. 30).
$inactive = 20 * 60; // 20 minutos (producción)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive) {
    // destruir sesión y redirigir al login
    session_unset();
    session_destroy();
    header("Location: index.php?mensaje=timeout");
    exit();
}
// actualizar último tiempo de actividad
$_SESSION['last_activity'] = time();

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
                <h3 class="mb-4">Estado de Casetas por horas</h3>

                

            </div>
        </body>
    </main>
</div>

</html>