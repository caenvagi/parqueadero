    

<?php

require_once "../conexion/conexion.php"; // Incluye tu archivo de conexión

session_start();
    require_once "../conexion/conexion.php";

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

    $id = $_GET['id'] ?? null;

// Obtener el ID de la liquidación desde la URL
$id_liquidacion = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_liquidacion <= 0) {
    die("ID de liquidación inválido.");
}

// Consulta principal de la liquidación
$sql = "SELECT fecha_liquidacion, nombre 
        FROM caja_liquidaciones cl
        INNER JOIN usuarios us ON us.id = cl.recibido_por
        WHERE id_liquidacion = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_liquidacion]);
$liquidacion = $stmt->fetch();

if (!$liquidacion) {
    die("No se encontró la liquidación.");
}

// Consulta principal de la liquidación
$sql1 = "SELECT fecha_liquidacion, nombre 
        FROM caja_liquidaciones cl
        INNER JOIN usuarios us ON us.id = cl.entregado_por
        WHERE id_liquidacion = ?";
$stmt1 = $pdo->prepare($sql1);
$stmt1->execute([$id_liquidacion]);
$liquidacion1 = $stmt1->fetch();

if (!$liquidacion1) {
    die("No se encontró la liquidación.");
}

// Consulta del detalle (facturas/movimientos asociados)
$sql_detalle = "
    SELECT m.id_movimiento, m.fecha_movimiento, m.desc_movimiento, m.valor_ingreso, m.valor_egreso
    FROM caja_liquidaciones_detalle d
    JOIN caja m ON d.id_movimiento = m.id_movimiento
    WHERE d.id_liquidacion = ?
";
$stmt_detalle = $pdo->prepare($sql_detalle);
$stmt_detalle->execute([$id_liquidacion]);
$detalles = $stmt_detalle->fetchAll();

$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Liquidación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
        }
        .ticket {
            padding: 10px;
            border: 1px dashed #000;
        }
        .center {
            text-align: center;
        }
        .detalle th, .detalle td {
            text-align: left;
            padding: 2px;
        }
        .detalle {
            width: 100%;
            border-collapse: collapse;
        }
        .detalle th {
            border-bottom: 1px solid #000;
        }
        .total {
            margin-top: 10px;
            font-weight: bold;
            text-align: right;
        }
        .firma {
            margin-top: 30px;
        }
        .firma div {
            border-top: 1px solid #000;
            width: 45%;
            text-align: center;
            display: inline-block;
            margin-right: 5%;
        }
    </style>
</head>
<body>

<div class="ticket">
    <div class="center">
        <h2>Parque de la Familia</h2>
        <p><strong>Fecha:</strong> <?= date("Y-m-d H:i", strtotime($liquidacion['fecha_liquidacion'])) ?></p>
    </div>

    <p><strong>Entregado por:</strong> <?= htmlspecialchars($liquidacion1['nombre']) ?></p>
    <p><strong>Recibido por:</strong> <?= htmlspecialchars($liquidacion['nombre']) ?></p>

    <h4>Detalle de facturas:</h4>
    <table class="detalle">
        <thead>
            <tr>
                <th>#</th>
                <th>Descripción</th>
                <th>Ingreso</th>
                <th>Egreso</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($detalles as $i => $row): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($row['desc_movimiento']) ?></td>
                <td>$<?= number_format($row['valor_ingreso'], 0, ',', '.') ?></td>
                <td>$<?= number_format($row['valor_egreso'], 0, ',', '.') ?></td>
            </tr>
            <?php
                $total += $row['valor_ingreso'] - $row['valor_egreso'];
            ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        Total Liquidado: $<?= number_format($total, 0, ',', '.') ?>
    </div>

    <div class="firma">
        <div><?= htmlspecialchars($liquidacion1['nombre']) ?><br>Entregado por</div><br><br><br>
        <div><?= htmlspecialchars($liquidacion['nombre']) ?><br>Recibido por</div>
    </div>
</div>

<script>
    // Imprimir automáticamente al cargar
    window.onload = function () {
        window.print();
    };
</script>

</body>
</html>
