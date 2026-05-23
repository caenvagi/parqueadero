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
$plan = $_POST['plan'] ?? '';
$valor = $_POST['valor'] ?? '';
$caseta = $_POST['caseta'] ?? '';
$usuario = $_SESSION['id'];

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
    $pdo->beginTransaction();

    $fecha_inicio = date('Y-m-d');
    $fecha_fin = calcularFechaFinPeriodo($fecha_inicio, $plan);

    $sqlHist = "INSERT INTO mensualidad_historial
        (placa, fecha_ingreso, fecha_retiro, caseta, valor, plan, usuario, observacion)
        VALUES (:placa, CURDATE(), NULL, :caseta, :valor, :plan, :usuario, 'Reactivacion desde historial')";

    $stmt = $pdo->prepare($sqlHist);
    $stmt->execute([
        ':placa' => $placa,
        ':caseta' => $caseta,
        ':valor' => $valor,
        ':plan' => $plan,
        ':usuario' => $usuario
    ]);

    $sqlPago = "INSERT INTO pagos
        (fecha, placa, valor, plan, fecha_inicio, fecha_fin, estado, usuario, caseta, observacion)
        VALUES (CURDATE(), :placa, :valor, :plan, :fecha_inicio, :fecha_fin, 'PENDIENTE', :usuario, :caseta, 'Pago mensualidad')";

    $stmt = $pdo->prepare($sqlPago);
    $stmt->execute([
        ':placa' => $placa,
        ':valor' => $valor,
        ':plan' => $plan,
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin,
        ':usuario' => $usuario,
        ':caseta' => $caseta
    ]);

    $sqlCliente = "UPDATE cliente
        SET mensualidad = 'SI', activo = 'SI', cli_tar_tiempo = :plan, valor = :valor, caseta = :caseta
        WHERE placa = :placa";

    $stmt = $pdo->prepare($sqlCliente);
    $stmt->execute([
        ':placa' => $placa,
        ':plan' => $plan,
        ':valor' => $valor,
        ':caseta' => $caseta
    ]);

    $pdo->commit();

    echo "Mensualidad reactivada correctamente";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "Error: " . $e->getMessage();
}
