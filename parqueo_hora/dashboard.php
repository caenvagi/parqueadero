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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
.card-kpi {
    border-left: 5px solid #0d6efd;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
</style>
    
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">

       <body class="bg-light">

<div class="container-fluid mt-4">

    <h3 class="mb-4">📊 Dashboard Parqueadero</h3>

    <!-- FILTROS -->
    <div class="row mb-4">
        <div class="col-md-2">
            <select id="anio" class="form-select">
                <?php for($i=date('Y'); $i>=2020; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card card-kpi p-3">
                <h6>💰 Total Ingresos</h6>
                <h3 id="totalDinero">$0</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-kpi p-3">
                <h6>🚗 Vehículos</h6>
                <h3 id="totalVehiculos">0</h3>
            </div>
        </div>

    </div>

    <!-- GRÁFICOS -->
    <div class="row">

        <div class="col-md-8">
            <div class="card p-3">
                <h6>📈 Ingresos por Mes</h6>
                <canvas id="graficoLineas"></canvas>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3">
                <h6>🥧 Tipos de Vehículos</h6>
                <canvas id="graficoTorta"></canvas>
            </div>
        </div>

    </div>

    <div class="card">
    <div class="card-header">
        <h5>📊 Comparación entre años</h5>
    </div>
    <div class="card-body">
        <canvas id="comparacionChart"></canvas>
    </div>
</div>

</div>

<script>
let chartLine, chartPie;

function cargarDashboard() {

    //let anio = document.getElementById("anio").value;

    fetch("dashboard_data.php")
    .then(res => res.json())
    .then(data => {
        console.log(data);

        // KPIs
        document.getElementById("totalDinero").innerText = 
        "$ " + Number(data.total_dinero).toLocaleString('es-CO');
        document.getElementById("totalVehiculos").innerText = data.total_vehiculos;

        // DATOS GRAFICO
        let meses = [];
        let dinero = [];
        // let vehiculos = [];

        data.ingresos.forEach(item => {
            meses.push(item.mes);
            dinero.push(item.dinero);
            console.log(item.dinero);
            /* vehiculos.push(item.vehiculos); */
        });

        if(chartLine) chartLine.destroy();

        chartLine = new Chart(document.getElementById("graficoLineas"), {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [
                    {
                        label: 'Dinero',
                        data: dinero
                    },
                    // {
                    //     label: 'Vehículos',
                    //     data: vehiculos
                    // }
                ]
            }
        });

//         // TORTA
        let labels = [];
        let valores = [];

        data.categorias.forEach(item => {
            labels.push(item.cat_nombre);
            valores.push(item.total);
            console.log(item.total);
        });

        if(chartPie) chartPie.destroy();

        chartPie = new Chart(document.getElementById("graficoTorta"), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: valores
                }]
            }
        });

//         // ✅ AQUÍ VA 👇
//     cargarComparacion(data);

//     });
// }

//document.getElementById("anio").addEventListener("change", cargarDashboard);

// let chartComparacion;

// function cargarComparacion(data){

//     let meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

//     if(chartComparacion) chartComparacion.destroy();

//     chartComparacion = new Chart(document.getElementById("comparacionChart"), {
//     type: 'line',
//     data: {
//         labels: meses,
//         datasets: [
//             {
//                 label: data.comparacion.anio1,
//                 data: data.comparacion.data1,
//                 borderColor: '#28a745',
//                 backgroundColor: 'rgba(40,167,69,0.1)',
//                 tension: 0.4,
//                 fill: true,
//                 pointRadius: 4,
//                 pointHoverRadius: 6
//             },
//             {
//                 label: data.comparacion.anio2,
//                 data: data.comparacion.data2,
//                 borderColor: '#6c757d',
//                 backgroundColor: 'rgba(108,117,125,0.1)',
//                 tension: 0.4,
//                 fill: true,
//                 borderDash: [5,5],
//                 pointRadius: 3
//             }
//         ]
//     },
//     options: {
//         responsive: true,
//         plugins: {
//             legend: {
//                 labels: {
//                     color: '#333',
//                     font: {
//                         size: 12
//                     }
//                 }
//             },
//             tooltip: {
//                 callbacks: {
//                     label: function(ctx){
//                         return '$' + ctx.raw.toLocaleString('es-CO');
//                     }
//                 }
//             }
//         },
//         scales: {
//             x: {
//                 grid: {
//                     display: false
//                 }
//             },
//             y: {
//                 grid: {
//                     color: 'rgba(0,0,0,0.05)'
//                 },
//                 ticks: {
//                     callback: function(value){
//                         return '$' + value.toLocaleString('es-CO');
//                     }
//                 }
//             }
//         }
//     }
 });

 }


cargarDashboard();
</script>

</body>
    </main>
</div>

</html>