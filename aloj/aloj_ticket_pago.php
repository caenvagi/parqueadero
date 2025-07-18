<?php
require_once "../conexion/conexion.php";
session_start();
if (!isset($_SESSION['id'])) die("No autorizado.");

$reserva_id = isset($_GET['reserva_id']) ? intval($_GET['reserva_id']) : 0;
if ($reserva_id <= 0) die("Reserva inválida.");

$stmt = $pdo->prepare("
    SELECT r.*, c.nombre AS cliente_nombre, 
                h.nombre AS habitacion_nombre, 
                u.nombre AS atendido_por,
                r.fecha_ingreso,
                r.fecha_salida
    FROM aloj_reservas r
    JOIN aloj_clientes c ON r.cliente_id = c.id
    JOIN aloj_habitaciones h ON r.habitacion_id = h.id
    LEFT JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$reserva_id]);
$reserva = $stmt->fetch();

if (!$reserva) die("Reserva no encontrada.");

$fecha_ingreso = new DateTime($reserva['fecha_ingreso']);
$fecha_salida = new DateTime($reserva['fecha_salida']);
$intervalo = $fecha_ingreso->diff($fecha_salida);


$noches_estadia = $intervalo->days; ; // Suponiendo que 1 día = 1 noche
$dias_estadia = $noches_estadia + 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Reserva</title>
    <style>
        * {
            font-family: monospace;
            font-size: 12px;
        }
        .ticket {
            width: 300px;
            padding: 10px;
            margin: auto;
        }
        .center {
            text-align: center;
        }
        img.logo {
            max-width: 100px;
            margin-bottom: 10px;
        }
        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print();">
    <div class="ticket">
        <div class="center">
            <img src="../assets/logos/logo_aloj.png" class="logo" alt="LOGO"><br>
            <strong>ALOJAMIENTO PARQUE DE LA FAMILIA</strong><br>
            NIT: 801-001-111<br>
            Cra 4 # 26-09<br>
            Tel: 301 1087964
        </div>
        <div class="line"></div>
        <strong>Cliente:</strong><?= ".........".htmlspecialchars($reserva['cliente_nombre']) ?><br>
        <strong>Reserva No:</strong><?= "......".$reserva['id'] ?><br>
        <strong>Fecha recbo:</strong><?= ".....".date('Y-m-d H:i') ?><br>
        <strong>Fecha Ingreso:</strong><?="...".$reserva['fecha_ingreso'] ?><br>
        <strong>Fecha Salida:</strong><?="....".$reserva['fecha_salida'] ?><br>
        <strong>Estadía:</strong><?= ".........".$dias_estadia ?> día<?= $dias_estadia == 1 ? '' : 's' ?> / 
<?= $noches_estadia ?> noche<?= $noches_estadia == 1 ? '' : 's' ?><br>
        

        <strong>Habitación:</strong><?= "......".htmlspecialchars($reserva['habitacion_nombre']) ?><br>
        <strong>Personas:</strong><?= "........".$reserva['cantidad_personas'] ?> persona<?= $reserva['cantidad_personas'] == 1 ? '' : 's' ?><br>
        <strong>Valor:</strong><?= "...........$".number_format($reserva['valor_total'], 0, ',', '.') ?><br>
        <strong>Atendido por:</strong><?= "....".htmlspecialchars($reserva['atendido_por'] ?? 'N/D') ?><br>
        <div class="line"></div>
        <div class="center">
            ¡Gracias por su visita!
        </div>
    </div>
    <div class="center no-print mt-3">
        <button onclick="window.print()">🖨 Imprimir</button>
        <a href="aloj_reservas_listado.php" class="btn">🔙 Volver</a>
    </div>
</body>
</html>
