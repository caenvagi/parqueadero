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


// 💰 1. Total ingresos mensualidades (pagadas)
$sql_ingresos = "SELECT SUM(valor_pagado) as total 
                 FROM recibo 
                 WHERE cierre = 'SI'
                 AND MONTH(fecha_recibo) = MONTH(CURDATE()) 
                 AND YEAR(fecha_recibo) = YEAR(CURDATE())"; // ajusta si usas otro campo

$stmt = $pdo->query($sql_ingresos);
$total_ingresos = $stmt->fetch()['total'] ?? 0;


// 🚗 2. Total vehículos en mensualidad
$sql_vehiculos = "SELECT COUNT(*) as total 
                  FROM cliente 
                  WHERE mensualidad = 'SI' AND activo = 'SI'
                  ";

$stmt = $pdo->query($sql_vehiculos);
$total_vehiculos = $stmt->fetch()['total'];


// ✅ 3. Vehículos que ya pagaron
$sql_pagados = "SELECT COUNT(DISTINCT placa) as total 
                FROM recibo 
                WHERE plan = '3'
                AND MONTH(fecha_recibo) = MONTH(CURDATE()) 
                AND YEAR(fecha_recibo) = YEAR(CURDATE())  "; // puedes filtrar por mes si quieres

$stmt = $pdo->query($sql_pagados);
$vehiculos_pagados = $stmt->fetch()['total'];

$sql = "SELECT 
            YEAR(fecha_recibo) as anio,
            MONTH(fecha_recibo) as mes,
            SUM(valor_pagado) as total
        FROM recibo r
        INNER JOIN cliente c ON c.placa = r.placa
        WHERE r.cierre = 'NO'
        AND c.mensualidad = 'SI'
        AND YEAR(fecha_recibo) IN (YEAR(CURDATE()), YEAR(CURDATE()) - 1)
        GROUP BY anio, mes
        ORDER BY anio, mes";

$stmt = $pdo->query($sql);
$resultados = $stmt->fetchAll();

$anio_actual = date('Y');
$anio_anterior = $anio_actual - 1;

$datos_actual = array_fill(1, 12, 0);
$datos_anterior = array_fill(1, 12, 0);

foreach ($resultados as $row) {
    if ($row['anio'] == $anio_actual) {
        $datos_actual[(int)$row['mes']] = (int)$row['total'];
    } else {
        $datos_anterior[(int)$row['mes']] = (int)$row['total'];
    }
}

$anio_actual = date('Y');
$anio_anterior = $anio_actual - 1;

$meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];

$mes = $meses[date('n')];
$anio = date('Y');

echo "$mes $anio";

$sql = "SELECT 
            cat.cat_nombre AS categoria,
            COUNT(*) AS total
        FROM cliente c
        INNER JOIN categorias cat ON cat.cat_id = c.categoria
        WHERE c.mensualidad = 'SI' 
        AND c.activo = 'SI'
        GROUP BY cat.cat_nombre
        ORDER BY total DESC";

$stmt = $pdo->query($sql);
$datos = $stmt->fetchAll();

$labels = [];
$valores = [];

foreach ($datos as $row) {
    $labels[] = $row['categoria'];
    $valores[] = (int)$row['total'];
}

$sql_nuevos = "SELECT COUNT(*) as total
               FROM mensualidad_historial
               WHERE MONTH(fecha_ingreso) = MONTH(CURDATE())
               AND YEAR(fecha_ingreso) = YEAR(CURDATE())";

$stmt = $pdo->query($sql_nuevos);
$vehiculos_nuevos = $stmt->fetch()['total'];

$sql_retirados = "SELECT COUNT(*) as total
                  FROM mensualidad_historial
                  WHERE MONTH(fecha_retiro) = MONTH(CURDATE())
                  AND YEAR(fecha_retiro) = YEAR(CURDATE())";

$stmt = $pdo->query($sql_retirados);
$vehiculos_retirados = $stmt->fetch()['total'];

$anio = date('Y');

$sql = "SELECT 
            m.mes,
            COUNT(ch.placa) as total
        FROM (
            SELECT 1 mes UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
            UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 
            UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
        ) m
        LEFT JOIN mensualidad_historial ch 
            ON MONTH(ch.fecha_ingreso) <= m.mes
            AND YEAR(ch.fecha_ingreso) <= $anio
            AND (ch.fecha_retiro IS NULL 
                 OR MONTH(ch.fecha_retiro) >= m.mes 
                 AND YEAR(ch.fecha_retiro) >= $anio)
        GROUP BY m.mes
        ORDER BY m.mes";

$stmt = $pdo->query($sql);
$resultados = $stmt->fetchAll();

$datos_meses = array_fill(1, 12, 0);

foreach ($resultados as $row) {
    $datos_meses[(int)$row['mes']] = (int)$row['total'];
}



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">

        <body class="bg-light">
            <div class="container mt-4">
                <h5 class="text-muted">
                    Ingresos de Mensualidades - <?php echo "$mes $anio"; ?>
                </h5>
                <div class="container mt-4">
                    <div class="row">

                        <!-- 💰 Total ingresos -->
                        <div class="col-md-4">
                            <div class="card text-white bg-success shadow">
                                <div class="card-body">
                                    <h5 class="card-title">Ingresos Mensualidades</h5>
                                    <h3>$<?php echo number_format($total_ingresos, 0, ',', '.'); ?></h3>
                                </div>
                            </div>
                        </div>

                        <!-- 🚗 Total vehículos -->
                        <div class="col-md-4">
                            <div class="card text-white bg-primary shadow">
                                <div class="card-body">
                                    <h5 class="card-title">Vehículos en Mensualidad</h5>
                                    <h3><?php echo $total_vehiculos; ?></h3>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ Vehículos pagos -->
                        <div class="col-md-4">
                            <div class="card text-white bg-warning shadow">
                                <div class="card-body">
                                    <h5 class="card-title">Vehículos al Día</h5>
                                    <h3><?php echo $vehiculos_pagados; ?></h3>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row mt-1">
                    <div class="card m-4 shadow col-md-5">
                        <div class="card-body">
                            <canvas id="graficoMensualidades"></canvas>
                        </div>
                    </div>
                     <div class="card m-4 shadow col-md-5">
                        <div class="card-body">
                            <canvas id="graficoActivos"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">

                    <!-- 📊 Gráfico -->
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-body text-center">
                                <h6 class="card-title">Vehículos por Categoría</h6>

                                <div style="width: 300px; height: 300px; margin:auto;">
                                    <canvas id="graficoCategorias"></canvas>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- 📦 Cards al lado -->
                    <div class="col-md-6">

                        <!-- Card 1 -->
                        <div class="card shadow mb-3 text-white bg-info">
                            <div class="card-body text-center">
                                <h6>Mensualidades Nuevas</h6>
                                <h3><?php echo $vehiculos_nuevos; ?></h3>
                                <small>Mes actual</small>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="card shadow text-white bg-danger">
                            <div class="card-body text-center">
                                <h6>Mensualidades Retiradas</h6>
                                <h3><?php echo $vehiculos_retirados; ?></h3>
                                <small>Mes actual</small>
                            </div>
                        </div>

                    </div>

            </div>
            <script>
                const datosActual = <?php echo json_encode(array_values($datos_actual)); ?>;
                const datosAnterior = <?php echo json_encode(array_values($datos_anterior)); ?>;
            </script>
            <script>
                const activosMes = <?php echo json_encode(array_values($datos_meses)); ?>;
            </script>
            <script>
                const categorias = <?php echo json_encode($labels); ?>;
                const cantidades = <?php echo json_encode($valores); ?>;
            </script>

            <script>
                const ctx = document.getElementById('graficoMensualidades');

                new Chart(ctx, {
                    type: 'bar',

                    data: {
                        labels: [
                            'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                            'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
                        ],
                        datasets: [{
                                label: '<?php echo $anio_actual; ?>',
                                data: datosActual,
                                borderWidth: 1
                            },
                            {
                                label: '<?php echo $anio_anterior; ?>',
                                data: datosAnterior,
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Comparativo de Ingresos por Mensualidades <?php echo "$mes $anio"; ?>',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>
            <script>
const ctxBar = document.getElementById('graficoActivos');

new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: [
            'Ene','Feb','Mar','Abr','May','Jun',
            'Jul','Ago','Sep','Oct','Nov','Dic'
        ],
        datasets: [{
            label: 'Activos',
            data: activosMes,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Mensualidades Activas por Mes'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
            <script>
                const ctxPie = document.getElementById('graficoCategorias');

                new Chart(ctxPie, {
                    type: 'pie',
                    data: {
                        labels: categorias,
                        datasets: [{
                            data: cantidades
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Vehículos por Categoría'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        let valor = context.raw;
                                        let porcentaje = ((valor / total) * 100).toFixed(1);
                                        return `${context.label}: ${valor} (${porcentaje}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            </script>
        </body>
    </main>
</div>

</html>