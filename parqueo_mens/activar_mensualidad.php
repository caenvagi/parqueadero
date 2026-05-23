<?php
session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE id=$id";
}

$placa = $_POST['placa'] ?? '';
$usuario = $_SESSION['id'] ?? 'Desconocido';

if (!$placa) {
    die("Placa no valida");
}

function calcularFechaFinPeriodo($fechaInicio, $plan)
{
    switch ((int)$plan) {
        case 7:
            return date('Y-m-d', strtotime($fechaInicio . ' +7 days'));
        case 6:
            return calcularFechaFinQuincena($fechaInicio);
        case 3:
            return calcularFechaFinMes($fechaInicio);
        default:
            throw new Exception("Plan de pago no valido");
    }
}

function calcularFechaFinQuincena($fechaInicio)
{
    $inicio = new DateTime($fechaInicio);
    $dia = (int)$inicio->format('j');
    $mes = (int)$inicio->format('n');
    $anio = (int)$inicio->format('Y');

    if ($dia <= 15) {
        return crearFechaConDia($anio, $mes, $dia + 15);
    }

    $inicio->modify('first day of next month');
    return crearFechaConDia((int)$inicio->format('Y'), (int)$inicio->format('n'), $dia - 15);
}

function calcularFechaFinMes($fechaInicio)
{
    $inicio = new DateTime($fechaInicio);
    $dia = (int)$inicio->format('j');
    $inicio->modify('first day of next month');

    return crearFechaConDia((int)$inicio->format('Y'), (int)$inicio->format('n'), $dia);
}

function crearFechaConDia($anio, $mes, $dia)
{
    $primerDia = DateTime::createFromFormat('!Y-n-j', "$anio-$mes-1");
    $ultimoDia = (int)$primerDia->format('t');
    $dia = min($dia, $ultimoDia);

    return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
}

try {
    $sqlCliente = "UPDATE cliente
                   SET mensualidad = 'SI', activo = 'SI', fecha_creacion = CURDATE()
                   WHERE placa = ?";
    $stmt = $pdo->prepare($sqlCliente);
    $stmt->execute([$placa]);

    $sql = "SELECT COUNT(*) FROM mensualidad_historial
            WHERE placa = ? AND fecha_retiro IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa]);

    if ($stmt->fetchColumn() > 0) {
        die("Este vehiculo ya tiene una mensualidad activa");
    }

    $sql = "SELECT caseta, valor, cli_tar_tiempo AS plan
            FROM cliente
            WHERE placa = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        die("Cliente no encontrado");
    }

    $caseta = $cliente['caseta'];
    $valor = $cliente['valor'];
    $plan = $cliente['plan'];
    $fecha_inicio = date('Y-m-d');
    $fecha_fin = calcularFechaFinPeriodo($fecha_inicio, $plan);

    $sql = "INSERT INTO mensualidad_historial
            (placa, fecha_ingreso, caseta, valor, plan, usuario, observacion)
            VALUES (?, CURDATE(), ?, ?, ?, ?, 'Reingreso de mensualidad')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $placa,
        $caseta,
        $valor,
        $plan,
        $usuario
    ]);

    $sql2 = "INSERT INTO pagos
            (fecha, estado, placa, valor, plan, fecha_inicio, fecha_fin, usuario, caseta, observacion)
            VALUES (NOW(), 'PENDIENTE', ?, ?, ?, ?, ?, ?, ?, 'Pago generado por activacion de mensualidad')";

    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([
        $placa,
        $valor,
        $plan,
        $fecha_inicio,
        $fecha_fin,
        $usuario,
        $caseta
    ]);

    echo "Mensualidad activada correctamente";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
