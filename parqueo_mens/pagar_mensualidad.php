<?php

session_start();
require_once "../conexion/conexion.php";

date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['id'];
$fecha = date("Y-m-d");
$placa = strtoupper(trim($_POST['placa'] ?? ''));
$pago_id = $_POST['pagos'] ?? '';

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

function construirTextoTiempo($fechaInicio, $fechaFin)
{
    $inicio = new DateTime($fechaInicio);
    $fin = new DateTime($fechaFin);

    if ($fin < $inicio) {
        throw new Exception("La fecha final no puede ser menor a la inicial");
    }

    $diferencia = $inicio->diff($fin);
    $meses = $diferencia->m + ($diferencia->y * 12);
    $dias = $diferencia->d;
    $horas = $diferencia->h;
    $minutos = $diferencia->i;
    $partes = [];

    if ($meses > 0) {
        $partes[] = $meses . ' ' . ($meses == 1 ? 'mes' : 'meses');
    }

    if ($dias > 0) {
        if ($dias % 7 == 0) {
            $semanas = $dias / 7;
            $partes[] = $semanas . ' ' . ($semanas == 1 ? 'semana' : 'semanas');
        } else {
            $partes[] = $dias . ' ' . ($dias == 1 ? 'dia' : 'dias');
        }
    }

    if ($horas > 0) {
        $partes[] = $horas . ' ' . ($horas == 1 ? 'hora' : 'horas');
    }

    if ($minutos > 0) {
        $partes[] = $minutos . ' ' . ($minutos == 1 ? 'minuto' : 'minutos');
    }

    return empty($partes) ? "0 minutos" : implode(', ', $partes);
}

if (!$placa) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['resultado' => 'ERROR', 'error' => 'Placa no enviada']);
    exit;
}

try {
    $pdo->beginTransaction();

    $sqlCliente = $pdo->prepare("
        SELECT
            c.nombre,
            c.caseta,
            c.categoria,
            c.valor,
            c.cli_tar_tiempo,
            tt.tar_tiempo
        FROM cliente c
        LEFT JOIN tar_tiempo tt ON c.cli_tar_tiempo = tt.tar_id_nombre
        WHERE c.placa = ?
        LIMIT 1
    ");
    $sqlCliente->execute([$placa]);
    $cliente = $sqlCliente->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        throw new Exception("Cliente no encontrado");
    }

    $plan_id = (int)$cliente['cli_tar_tiempo'];
    $plan_nombre = $cliente['tar_tiempo'] ?: 'PLAN';
    $nombre = $cliente['nombre'];
    $caseta = $cliente['caseta'];
    $categoria = $cliente['categoria'];
    $valor = $cliente['valor'];

    if ($pago_id !== '') {
        $sqlPago = $pdo->prepare("
            SELECT *
            FROM pagos
            WHERE id = ?
              AND placa = ?
              AND estado = 'PENDIENTE'
            LIMIT 1
        ");
        $sqlPago->execute([$pago_id, $placa]);
    } else {
        $sqlPago = $pdo->prepare("
            SELECT *
            FROM pagos
            WHERE placa = ?
              AND estado = 'PENDIENTE'
            ORDER BY fecha_inicio ASC, id ASC
            LIMIT 1
        ");
        $sqlPago->execute([$placa]);
    }

    $pago = $sqlPago->fetch(PDO::FETCH_ASSOC);

    if (!$pago) {
        throw new Exception("No hay pago pendiente para esta placa");
    }

    $fecha_inicio = $pago['fecha_inicio'];
    $fecha_fin = calcularFechaFinPeriodo($fecha_inicio, $plan_id);
    $valor = $pago['valor'] ?: $valor;
    $tiempo_txt = construirTextoTiempo($fecha_inicio, $fecha_fin);

    $update = $pdo->prepare("
        UPDATE pagos
        SET estado = 'PAGADO',
            fecha = CURDATE(),
            plan = ?,
            fecha_fin = ?
        WHERE id = ?
    ");
    $update->execute([
        $plan_id,
        $fecha_fin,
        $pago['id']
    ]);

    $recibo = $pdo->prepare("
        INSERT INTO recibo
        (
            fecha_recibo,
            ticket,
            placa,
            recibo_man,
            fecha_ini,
            fecha_fin,
            tiempo,
            tarifa_recibo,
            plan,
            valor_pagado,
            usuario,
            cierre
        )
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $recibo->execute([
        date("Y-m-d-H:i:s"),
        '0',
        $placa,
        '0',
        $fecha_inicio,
        $fecha_fin,
        $tiempo_txt,
        $categoria,
        $plan_id,
        $valor,
        $usuario,
        'NO'
    ]);

    $recibo_id = $pdo->lastInsertId();

    $sqlCategoria = $pdo->prepare("
        SELECT cat_nombre
        FROM categorias
        WHERE cat_id = ?
    ");
    $sqlCategoria->execute([$categoria]);
    $nombre_categoria = $sqlCategoria->fetchColumn() ?: 'Categoria';

    $desc_movimiento = "$nombre_categoria - $plan_nombre - $placa - $nombre - $fecha_inicio a $fecha_fin";

    $caja = $pdo->prepare("
        INSERT INTO caja
        (
            fecha_movimiento,
            movimiento,
            desc_movimiento,
            recibo_id,
            valor_ingreso,
            valor_egreso,
            user_login,
            caja_tipo,
            caja,
            liquidado
        )
        VALUES (NOW(), '3', ?, ?, ?, '0', ?, 'INGRESO', 'PARQUEADERO', 'NO')
    ");

    $caja->execute([
        $desc_movimiento,
        $recibo_id,
        $valor,
        $usuario
    ]);

    $nueva_inicio = date("Y-m-d", strtotime($fecha_fin . " +0 day"));
    $nueva_fin = calcularFechaFinPeriodo($nueva_inicio, $plan_id);

    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'Spanish');
    $mes_anio = ucfirst(strftime('%B %Y', strtotime($nueva_inicio)));

    $nuevo = $pdo->prepare("
        INSERT INTO pagos
        (
            fecha,
            placa,
            caseta,
            plan,
            fecha_inicio,
            fecha_fin,
            valor,
            estado,
            usuario,
            observacion
        )
        VALUES (?,?,?,?,?,?,?,?,?,?)
    ");

    $nuevo->execute([
        $fecha,
        $placa,
        $caseta,
        $plan_id,
        $nueva_inicio,
        $nueva_fin,
        $valor,
        'PENDIENTE',
        $usuario,
        $mes_anio
    ]);

    $pdo->commit();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['resultado' => 'OK', 'recibo_id' => $recibo_id]);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'resultado' => 'ERROR',
        'error' => $e->getMessage(),
        'linea' => $e->getLine(),
        'archivo' => $e->getFile(),
    ]);
    exit;
}
