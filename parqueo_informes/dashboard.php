<?php
session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

// Control de inactividad: timeout de prueba 30 segundos (usar 20*60 en producción)
// Tiempo de inactividad en segundos (20 minutos - producción).
// Para pruebas locales, cambiar temporalmente a 30 (segundos).
$inactive = 20 * 60; // 20 minutos (producción)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?mensaje=timeout");
    exit;
}
// actualizar última actividad
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

$anio_actual = (int) date('Y');
$anio_anterior = $anio_actual - 1;
$anio_dos_anteriores = $anio_actual - 2;

$meses = [
    'Ene',
    'Feb',
    'Mar',
    'Abr',
    'May',
    'Jun',
    'Jul',
    'Ago',
    'Sep',
    'Oct',
    'Nov',
    'Dic',
];

$ventas_por_anio = [
    $anio_actual => array_fill(1, 12, 0),
    $anio_anterior => array_fill(1, 12, 0),
    $anio_dos_anteriores => array_fill(1, 12, 0),
];

$ventas_parqueadero_por_anio = [
    $anio_actual => array_fill(1, 12, 0),
    $anio_anterior => array_fill(1, 12, 0),
    $anio_dos_anteriores => array_fill(1, 12, 0),
];

$ventas_alojamiento_por_anio = [
    $anio_actual => array_fill(1, 12, 0),
    $anio_anterior => array_fill(1, 12, 0),
    $anio_dos_anteriores => array_fill(1, 12, 0),
];

$recibos_por_anio = [
    $anio_actual => array_fill(1, 12, 0),
    $anio_anterior => array_fill(1, 12, 0),
];

$sql = "
    SELECT
        YEAR(fecha_recibo) AS anio,
        MONTH(fecha_recibo) AS mes,
        SUM(valor_pagado) AS total_ventas,
        COUNT(recibo_id) AS total_recibos
    FROM recibo
    WHERE YEAR(fecha_recibo) IN (:anio_actual, :anio_anterior, :anio_dos_anteriores)
    GROUP BY anio, mes
    ORDER BY anio, mes
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':anio_actual' => $anio_actual,
    ':anio_anterior' => $anio_anterior,
    ':anio_dos_anteriores' => $anio_dos_anteriores,
]);

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
    $anio = (int) $fila['anio'];
    $mes = (int) $fila['mes'];

    if (isset($ventas_por_anio[$anio][$mes])) {
        $ventas_por_anio[$anio][$mes] = (int) $fila['total_ventas'];
    }

    if (isset($recibos_por_anio[$anio][$mes])) {
        $recibos_por_anio[$anio][$mes] = (int) $fila['total_recibos'];
    }
}

$sql_parqueadero = "
    SELECT
        YEAR(RE.fecha_recibo) AS anio,
        MONTH(RE.fecha_recibo) AS mes,
        SUM(RE.valor_pagado) AS total_ventas
    FROM recibo AS RE
    INNER JOIN cliente AS CL ON RE.placa = CL.placa
    INNER JOIN categorias AS CA ON CL.categoria = CA.cat_id
    WHERE YEAR(RE.fecha_recibo) IN (:anio_actual, :anio_anterior, :anio_dos_anteriores)
    AND (RE.plan IS NULL OR RE.plan <> 8)
    GROUP BY anio, mes
    ORDER BY anio, mes
";

$stmt = $pdo->prepare($sql_parqueadero);
$stmt->execute([
    ':anio_actual' => $anio_actual,
    ':anio_anterior' => $anio_anterior,
    ':anio_dos_anteriores' => $anio_dos_anteriores,
]);

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
    $anio = (int) $fila['anio'];
    $mes = (int) $fila['mes'];

    if (isset($ventas_parqueadero_por_anio[$anio][$mes])) {
        $ventas_parqueadero_por_anio[$anio][$mes] = (int) $fila['total_ventas'];
    }
}

$sql_alojamiento = "
    SELECT
        YEAR(RE.fecha_recibo) AS anio,
        MONTH(RE.fecha_recibo) AS mes,
        SUM(RE.valor_pagado) AS total_ventas
    FROM recibo AS RE
    INNER JOIN cliente AS CL ON RE.placa = CL.placa
    INNER JOIN categorias AS CA ON CL.categoria = CA.cat_id
    WHERE YEAR(RE.fecha_recibo) IN (:anio_actual, :anio_anterior, :anio_dos_anteriores)
    AND CA.cat_id = 6
    GROUP BY anio, mes
    ORDER BY anio, mes
";

$stmt = $pdo->prepare($sql_alojamiento);
$stmt->execute([
    ':anio_actual' => $anio_actual,
    ':anio_anterior' => $anio_anterior,
    ':anio_dos_anteriores' => $anio_dos_anteriores,
]);

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
    $anio = (int) $fila['anio'];
    $mes = (int) $fila['mes'];

    if (isset($ventas_alojamiento_por_anio[$anio][$mes])) {
        $ventas_alojamiento_por_anio[$anio][$mes] = (int) $fila['total_ventas'];
    }
}

$ventas_datasets = [
    [
        'label' => (string) $anio_actual,
        'data' => array_values($ventas_por_anio[$anio_actual]),
        'backgroundColor' => 'rgba(13, 110, 253, 0.72)',
        'borderColor' => 'rgba(13, 110, 253, 1)',
        'borderWidth' => 1,
    ],
    [
        'label' => (string) $anio_anterior,
        'data' => array_values($ventas_por_anio[$anio_anterior]),
        'backgroundColor' => 'rgba(25, 135, 84, 0.72)',
        'borderColor' => 'rgba(25, 135, 84, 1)',
        'borderWidth' => 1,
    ],
    [
        'label' => (string) $anio_dos_anteriores,
        'data' => array_values($ventas_por_anio[$anio_dos_anteriores]),
        'backgroundColor' => 'rgba(255, 193, 7, 0.76)',
        'borderColor' => 'rgba(255, 193, 7, 1)',
        'borderWidth' => 1,
    ],
];

$recibos_datasets = [
    [
        'label' => (string) $anio_actual,
        'data' => array_values($recibos_por_anio[$anio_actual]),
        'backgroundColor' => 'rgba(111, 66, 193, 0.72)',
        'borderColor' => 'rgba(111, 66, 193, 1)',
        'borderWidth' => 1,
    ],
    [
        'label' => (string) $anio_anterior,
        'data' => array_values($recibos_por_anio[$anio_anterior]),
        'backgroundColor' => 'rgba(220, 53, 69, 0.72)',
        'borderColor' => 'rgba(220, 53, 69, 1)',
        'borderWidth' => 1,
    ],
];

$line_colors = [
    $anio_actual => [
        'borderColor' => 'rgba(13, 110, 253, 1)',
        'backgroundColor' => 'rgba(13, 110, 253, 0.12)',
    ],
    $anio_anterior => [
        'borderColor' => 'rgba(25, 135, 84, 1)',
        'backgroundColor' => 'rgba(25, 135, 84, 0.12)',
    ],
    $anio_dos_anteriores => [
        'borderColor' => 'rgba(255, 193, 7, 1)',
        'backgroundColor' => 'rgba(255, 193, 7, 0.16)',
    ],
];

$parqueadero_datasets = [];
$alojamiento_datasets = [];

foreach ([$anio_actual, $anio_anterior, $anio_dos_anteriores] as $anio) {
    $parqueadero_datasets[] = [
        'label' => (string) $anio,
        'data' => array_values($ventas_parqueadero_por_anio[$anio]),
        'borderColor' => $line_colors[$anio]['borderColor'],
        'backgroundColor' => $line_colors[$anio]['backgroundColor'],
        'borderWidth' => 2,
        'fill' => false,
        'tension' => 0.35,
    ];

    $alojamiento_datasets[] = [
        'label' => (string) $anio,
        'data' => array_values($ventas_alojamiento_por_anio[$anio]),
        'borderColor' => $line_colors[$anio]['borderColor'],
        'backgroundColor' => $line_colors[$anio]['backgroundColor'],
        'borderWidth' => 2,
        'fill' => false,
        'tension' => 0.35,
    ];
}

$total_ventas_actual = array_sum($ventas_por_anio[$anio_actual]);
$total_recibos_actual = array_sum($recibos_por_anio[$anio_actual]);
$mes_actual = (int) date('n');
$total_ventas_mes_actual = $ventas_por_anio[$anio_actual][$mes_actual] ?? 0;
$total_recibos_mes_actual = $recibos_por_anio[$anio_actual][$mes_actual] ?? 0;

$sql_categorias_mes = "
    SELECT
        CA.cat_nombre AS categoria,
        SUM(RE.valor_pagado) AS total_ventas,
        COUNT(RE.recibo_id) AS total_recibos
    FROM recibo AS RE
    INNER JOIN cliente AS CL ON RE.placa = CL.placa
    INNER JOIN categorias AS CA ON CL.categoria = CA.cat_id
    WHERE YEAR(RE.fecha_recibo) = :anio_actual
    AND MONTH(RE.fecha_recibo) = :mes_actual
    GROUP BY CA.cat_nombre
    ORDER BY total_ventas DESC
";

$stmt = $pdo->prepare($sql_categorias_mes);
$stmt->execute([
    ':anio_actual' => $anio_actual,
    ':mes_actual' => $mes_actual,
]);

$ventas_categorias_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categorias_labels = [];
$categorias_valores = [];
$categorias_colores = [
    'rgba(13, 110, 253, 0.78)',
    'rgba(25, 135, 84, 0.78)',
    'rgba(255, 193, 7, 0.82)',
    'rgba(220, 53, 69, 0.78)',
    'rgba(111, 66, 193, 0.78)',
    'rgba(13, 202, 240, 0.78)',
    'rgba(108, 117, 125, 0.78)',
    'rgba(253, 126, 20, 0.78)',
];

foreach ($ventas_categorias_mes as $categoria) {
    $categorias_labels[] = $categoria['categoria'];
    $categorias_valores[] = (int) $categoria['total_ventas'];
}

$mensualidades_activas = (int) $pdo->query("
    SELECT COUNT(*) AS total
    FROM cliente
    WHERE mensualidad = 'SI'
    AND activo = 'SI'
")->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM mensualidad_historial
    WHERE YEAR(fecha_ingreso) = :anio_actual
    AND MONTH(fecha_ingreso) = :mes_actual
");
$stmt->execute([
    ':anio_actual' => $anio_actual,
    ':mes_actual' => $mes_actual,
]);
$mensualidades_nuevas_mes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM mensualidad_historial
    WHERE YEAR(fecha_retiro) = :anio_actual
    AND MONTH(fecha_retiro) = :mes_actual
");
$stmt->execute([
    ':anio_actual' => $anio_actual,
    ':mes_actual' => $mes_actual,
]);
$mensualidades_retiradas_mes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(valor_pagado), 0) AS total
    FROM recibo
    WHERE YEAR(fecha_recibo) = :anio_actual
    AND MONTH(fecha_recibo) = :mes_actual
    AND plan IN (3, 6)
");
$stmt->execute([
    ':anio_actual' => $anio_actual,
    ':mes_actual' => $mes_actual,
]);
$total_mensualidad_quincena_mes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(valor_pagado), 0) AS total
    FROM recibo
    WHERE YEAR(fecha_recibo) = :anio_actual
    AND MONTH(fecha_recibo) = :mes_actual
    AND plan IN (1, 2, 7)
");
$stmt->execute([
    ':anio_actual' => $anio_actual,
    ':mes_actual' => $mes_actual,
]);
$total_hora_doce_semana_mes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$movimiento_horas = array_fill(0, 24, [
    'recibos' => 0,
    'ventas' => 0,
]);

$stmt = $pdo->prepare("
    SELECT
        HOUR(fecha_recibo) AS hora,
        COUNT(recibo_id) AS total_recibos,
        COALESCE(SUM(valor_pagado), 0) AS total_ventas
    FROM recibo
    WHERE YEAR(fecha_recibo) = :anio_actual
    AND MONTH(fecha_recibo) = :mes_actual
    AND (plan IS NULL OR plan <> 8)
    GROUP BY hora
    ORDER BY hora
");
$stmt->execute([
    ':anio_actual' => $anio_actual,
    ':mes_actual' => $mes_actual,
]);

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
    $hora = (int) $fila['hora'];
    $movimiento_horas[$hora] = [
        'recibos' => (int) $fila['total_recibos'],
        'ventas' => (int) $fila['total_ventas'],
    ];
}

$horas_labels = [];
$horas_recibos = [];
$horas_ventas = [];

foreach ($movimiento_horas as $hora => $datos) {
    $horas_labels[] = str_pad((string) $hora, 2, '0', STR_PAD_LEFT) . ':00';
    $horas_recibos[] = $datos['recibos'];
    $horas_ventas[] = $datos['ventas'];
}

function iconoCategoria($categoria)
{
    $nombre = function_exists('mb_strtoupper')
        ? mb_strtoupper($categoria, 'UTF-8')
        : strtoupper($categoria);

    if (strpos($nombre, 'MOTO') !== false) {
        return '<i class="bi bi-bicycle"></i>';
    }

    if (strpos($nombre, 'CARRO') !== false || strpos($nombre, 'AUTO') !== false || strpos($nombre, 'VEHIC') !== false) {
        return '<i class="bi bi-car-front"></i>';
    }

    if (strpos($nombre, 'BICI') !== false || strpos($nombre, 'CICLA') !== false) {
        return '<i class="bi bi-bicycle"></i>';
    }

    if (strpos($nombre, 'TURBO') !== false || strpos($nombre, 'TURBO') !== false) {
        return '<i class="bi bi-truck"></i>';
    }

    if (strpos($nombre, 'CAMION') !== false || strpos($nombre, 'CAMIÓN') !== false) {
        return '<i class="bi bi-truck-flatbed"></i>';
    }

    if (strpos($nombre, 'BUS') !== false || strpos($nombre, 'BUSETA') !== false) {
        return '<i class="bi bi-bus-front"></i>';
    }

    if (strpos($nombre, 'PARQUE') !== false || strpos($nombre, 'PARQUE') !== false) {
        return '<i class="bi bi-water"></i>';
    }

    return '🏷️';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <?php require '../logs/datatables.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 0.35rem 1rem rgba(15, 23, 42, 0.08);
        }

        .chart-box {
            min-height: 380px;
        }

        .chart-area {
            height: 320px;
            position: relative;
        }

        .pie-chart-area {
            height: 360px;
            margin: 0 auto;
            max-width: 560px;
            position: relative;
        }

        .kpi-value {
            font-size: 1.6rem;
            font-weight: 700;
        }

        .category-icon {
            align-items: center;
            background: #f1f5f9;
            border-radius: 8px;
            display: inline-flex;
            font-size: 1.6rem;
            height: 48px;
            justify-content: center;
            width: 48px;
        }

        .monthly-card-icon {
            font-size: 2.4rem;
            line-height: 1;
            opacity: 0.92;
        }

        canvas {
            width: 100%;
        }
    </style>
</head>

<body class="bg-light">
    <?php require '../logs/nav-bar.php'; ?>

    <div id="layoutSidenav_content">
        <main class="ms-5 me-5">
            <div class="container-fluid mt-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h3 class="mb-1"><i class="bi bi-speedometer"> </i>Dashboard de informes</h3>
                        <p class="text-muted mb-0">Ventas y recibos por mes</p>
                    </div>
                    <div class="text-muted">
                        Año actual: <strong><?= $anio_actual ?></strong>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <div class="text-muted mb-1">Ventas <?= $anio_actual ?></div>
                                <div class="kpi-value">$<?= number_format($total_ventas_actual, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <div class="text-muted mb-1">Recibos <?= $anio_actual ?></div>
                                <div class="kpi-value"><?= number_format($total_recibos_actual, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <div class="text-muted mb-1">Ventas <?= $meses[$mes_actual - 1] ?> <?= $anio_actual ?></div>
                                <div class="kpi-value">$<?= number_format($total_ventas_mes_actual, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <div class="text-muted mb-1">Recibos <?= $meses[$mes_actual - 1] ?> <?= $anio_actual ?></div>
                                <div class="kpi-value"><?= number_format($total_recibos_mes_actual, 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary shadow h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="monthly-card-icon" aria-hidden="true"><i class="bi bi-car-front-fill"></i></div>
                                <div>
                                    <h5 class="card-title">Mensualidades activas</h5>
                                    <h3 class="mb-1"><?= number_format($mensualidades_activas, 0, ',', '.') ?></h3>
                                    <small>Clientes con mensualidad activa</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card text-white bg-success shadow h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="monthly-card-icon" aria-hidden="true"><i class="bi bi-sign-intersection-fill"></i></div>
                                <div>
                                    <h5 class="card-title">Mensualidades nuevas</h5>
                                    <h3 class="mb-1"><?= number_format($mensualidades_nuevas_mes, 0, ',', '.') ?></h3>
                                    <small><?= $meses[$mes_actual - 1] ?> <?= $anio_actual ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card text-white bg-danger shadow h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="monthly-card-icon" aria-hidden="true"><i class="bi bi-sign-stop-fill"></i></div>
                                <div>
                                    <h5 class="card-title">Mensualidades retiradas</h5>
                                    <h3 class="mb-1"><?= number_format($mensualidades_retiradas_mes, 0, ',', '.') ?></h3>
                                    <small><?= $meses[$mes_actual - 1] ?> <?= $anio_actual ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card dashboard-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="category-icon" aria-hidden="true"><i class="bi bi-calendar3"></i></div>
                                <div>
                                    <div class="text-muted mb-1">Mensualidad + Quincena</div>
                                    <div class="kpi-value">$<?= number_format($total_mensualidad_quincena_mes, 0, ',', '.') ?></div>
                                    <small class="text-muted"><?= $meses[$mes_actual - 1] ?> <?= $anio_actual ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card dashboard-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="category-icon" aria-hidden="true"><i class="bi bi-calendar-week"></i></div>
                                <div>
                                    <div class="text-muted mb-1">Hora + 12 horas + Semana</div>
                                    <div class="kpi-value">$<?= number_format($total_hora_doce_semana_mes, 0, ',', '.') ?></div>
                                    <small class="text-muted"><?= $meses[$mes_actual - 1] ?> <?= $anio_actual ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card dashboard-card chart-box">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Valor total de ventas por mes</h5>
                                <div class="chart-area">
                                    <canvas id="ventasChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card dashboard-card chart-box">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Cantidad de recibos por mes</h5>
                                <div class="chart-area">
                                    <canvas id="recibosChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-lg-6">
                        <div class="card dashboard-card chart-box">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Ventas de parqueadero por mes</h5>
                                <div class="chart-area">
                                    <canvas id="parqueaderoLineChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card dashboard-card chart-box">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Ventas de alojamiento por mes</h5>
                                <div class="chart-area">
                                    <canvas id="alojamientoLineChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($ventas_categorias_mes)): ?>
                    <div class="mt-4 mb-4">
                        <h5 class="mb-3">Ventas por categoría - <?= $meses[$mes_actual - 1] ?> <?= $anio_actual ?></h5>
                        <div class="row g-3">
                            <?php foreach ($ventas_categorias_mes as $categoria): ?>
                                <div class="col-md-6 col-xl-3">
                                    <div class="card dashboard-card h-100">
                                        <div class="card-body d-flex gap-3 align-items-center">
                                            <div class="category-icon" aria-hidden="true">
                                                <?= iconoCategoria($categoria['categoria']) ?>
                                            </div>
                                            <div>
                                                <div class="text-muted mb-1"><?= htmlspecialchars($categoria['categoria']) ?></div>
                                                <div class="kpi-value">$<?= number_format((int) $categoria['total_ventas'], 0, ',', '.') ?></div>
                                                <small class="text-muted">
                                                    <?= number_format((int) $categoria['total_recibos'], 0, ',', '.') ?> recibos
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="card dashboard-card mt-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><i class="bi bi-pie-chart-fill"></i>&nbsp;Participación por categoría</h5>
                                <div class="pie-chart-area">
                                    <canvas id="categoriasPieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card dashboard-card mt-4 mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><i class="bi bi-activity"></i>&nbsp;Movimiento del parqueadero por hora - <?= $meses[$mes_actual - 1] ?> <?= $anio_actual ?></h5>
                        <div class="chart-area">
                            <canvas id="movimientoHorasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const meses = <?= json_encode($meses); ?>;
        const ventasDatasets = <?= json_encode($ventas_datasets, JSON_NUMERIC_CHECK); ?>;
        const recibosDatasets = <?= json_encode($recibos_datasets, JSON_NUMERIC_CHECK); ?>;
        const parqueaderoDatasets = <?= json_encode($parqueadero_datasets, JSON_NUMERIC_CHECK); ?>;
        const alojamientoDatasets = <?= json_encode($alojamiento_datasets, JSON_NUMERIC_CHECK); ?>;
        const categoriasLabels = <?= json_encode($categorias_labels); ?>;
        const categoriasValores = <?= json_encode($categorias_valores, JSON_NUMERIC_CHECK); ?>;
        const categoriasColores = <?= json_encode($categorias_colores); ?>;
        const horasLabels = <?= json_encode($horas_labels); ?>;
        const horasRecibos = <?= json_encode($horas_recibos, JSON_NUMERIC_CHECK); ?>;
        const horasVentas = <?= json_encode($horas_ventas, JSON_NUMERIC_CHECK); ?>;

        function currencyChartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + Number(context.raw).toLocaleString('es-CO');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + Number(value).toLocaleString('es-CO');
                            }
                        }
                    }
                }
            };
        }

        new Chart(document.getElementById('ventasChart'), {
            type: 'bar',
            data: {
                labels: meses,
                datasets: ventasDatasets
            },
            options: currencyChartOptions()
        });

        new Chart(document.getElementById('recibosChart'), {
            type: 'bar',
            data: {
                labels: meses,
                datasets: recibosDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + Number(context.raw).toLocaleString('es-CO') + ' recibos';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('parqueaderoLineChart'), {
            type: 'line',
            data: {
                labels: meses,
                datasets: parqueaderoDatasets
            },
            options: currencyChartOptions()
        });

        new Chart(document.getElementById('alojamientoLineChart'), {
            type: 'line',
            data: {
                labels: meses,
                datasets: alojamientoDatasets
            },
            options: currencyChartOptions()
        });

        const categoriasPieCanvas = document.getElementById('categoriasPieChart');
        const pieLabelsPlugin = {
            id: 'pieLabelsPlugin',
            afterDatasetsDraw(chart) {
                const {
                    ctx
                } = chart;
                const dataset = chart.data.datasets[0];
                const total = dataset.data.reduce((sum, value) => sum + Number(value), 0);

                if (!total) {
                    return;
                }

                ctx.save();
                ctx.font = '600 12px Arial';
                ctx.fillStyle = '#111827';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                chart.getDatasetMeta(0).data.forEach((arc, index) => {
                    const valor = Number(dataset.data[index]);
                    const porcentaje = ((valor / total) * 100).toFixed(1);

                    if (valor <= 0 || porcentaje < 3) {
                        return;
                    }

                    const posicion = arc.tooltipPosition();
                    const label = chart.data.labels[index];

                    ctx.fillText(label, posicion.x, posicion.y - 7);
                    ctx.fillText(porcentaje + '%', posicion.x, posicion.y + 8);
                });

                ctx.restore();
            }
        };

        if (categoriasPieCanvas) {
            new Chart(categoriasPieCanvas, {
                type: 'pie',
                data: {
                    labels: categoriasLabels,
                    datasets: [{
                        data: categoriasValores,
                        backgroundColor: categoriasColores,
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((sum, value) => sum + Number(value), 0);
                                    const valor = Number(context.raw);
                                    const porcentaje = total > 0 ? ((valor / total) * 100).toFixed(1) : 0;

                                    return context.label + ': $' + valor.toLocaleString('es-CO') + ' (' + porcentaje + '%)';
                                }
                            }
                        }
                    }
                },
                plugins: [pieLabelsPlugin]
            });
        }

        new Chart(document.getElementById('movimientoHorasChart'), {
            type: 'bar',
            data: {
                labels: horasLabels,
                datasets: [{
                    label: 'Recibos',
                    data: horasRecibos,
                    backgroundColor: 'rgba(13, 110, 253, 0.72)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const index = context.dataIndex;
                                return [
                                    'Recibos: ' + Number(horasRecibos[index]).toLocaleString('es-CO'),
                                    'Ventas: $' + Number(horasVentas[index]).toLocaleString('es-CO')
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
    <script>
        // Cliente: redirigir automáticamente a logout después de 30s de inactividad
        (function() {
            var inactivityTime = function () {
                var time;
                // Tiempo inactividad cliente (20 minutos en producción).
                // Para pruebas locales, use 30 * 1000 (30s).
                var maxInactive = 20 * 60 * 1000; // 20 minutos

                function logout() {
                    // Redirige a logout para destruir sesión en el servidor
                    window.location.href = '../logout.php?timeout=1';
                }

                function resetTimer() {
                    clearTimeout(time);
                    time = setTimeout(logout, maxInactive);
                }

                // Eventos que resetearán el temporizador
                window.onload = resetTimer;
                document.onmousemove = resetTimer;
                document.onmousedown = resetTimer; // touchscreen
                document.onclick = resetTimer;
                document.onscroll = resetTimer;
                document.onkeypress = resetTimer;
                document.addEventListener('touchstart', resetTimer, false);
            };

            inactivityTime();
        })();
    </script>
</body>

</html>
