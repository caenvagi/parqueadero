<?php
include('../CONEXION/CONEXION.PHP'); // conexión PDO ya configurada

date_default_timezone_set('America/Bogota');
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_SESSION['id'];
$nombre = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario = $_SESSION['usuario'];
$foto = $_SESSION['avatar'];

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE id = :id";
}

// =============================
// FUNCIONES DE CONVERSIÓN Y CÁLCULO
// =============================
function conversorSegundosHoras($tiempo_en_segundos)
{
    $anios = floor($tiempo_en_segundos / 31536000);
    $meses = floor(($tiempo_en_segundos / 2592000));
    $month = ($anios * 12) - $meses;
    $dias = floor($tiempo_en_segundos / 86400);
    $dia = floor(($month * 30) + ($dias - ($anios * 363)));
    $horas = floor($tiempo_en_segundos / 3600);
    $hour = floor($horas - ($dias * 24));
    $minutos = floor(($tiempo_en_segundos - ($horas * 3600)) / 60);

    $hora_texto = "";
    if ($anios > 0) $hora_texto .= $anios . " Años ";
    if ($meses > 0) $hora_texto .= $month . " Meses - ";
    if ($dias > 0) $hora_texto .= $dia . " Días - ";
    if ($horas > 0) $hora_texto .= $hour . " Horas y ";
    if ($minutos > 0) $hora_texto .= $minutos . " min ";
    return $hora_texto;
}

    
// CONSULTA TARIFAS PARA MOTO
   $sql = "SELECT 
        t.tar_nombre,
        t.tar_valor,
        t.tar_bloque
        FROM tarifas t
        WHERE t.tar_categoria = 1";

    $stmt = $pdo->query($sql);    

    while ($fila = $stmt->fetch()) {    
            $tarifaHora1 = $fila['tar_valor'];
            $tarifa12Horas1 = $fila['tar_bloque'];
            $tarifaDia1 = $fila['tar_bloque'] * 2;
        }

// CONSULTA TARIFAS PARA AUTOMOVIL
    $sql = "SELECT 
        t.tar_nombre,
        t.tar_valor,
        t.tar_bloque
        FROM tarifas t
        WHERE t.tar_categoria = 2";

    $stmt = $pdo->query($sql);    

    while ($fila = $stmt->fetch()) {    
            $tarifaHora2 = $fila['tar_valor'];
            $tarifa12Horas2 = $fila['tar_bloque'];
            $tarifaDia2 = $fila['tar_bloque'] * 2;
        }

// CONSULTA TARIFAS PARA TURBOS
    $sql = "SELECT 
        t.tar_nombre,
        t.tar_valor,
        t.tar_bloque
        FROM tarifas t
        WHERE t.tar_categoria = 3";

    $stmt = $pdo->query($sql);    

    while ($fila = $stmt->fetch()) {    
            $tarifaHora3 = $fila['tar_valor'];
            $tarifa12Horas3 = $fila['tar_bloque'];
            $tarifaDia3 = $fila['tar_bloque'] * 2;
        }
// CONSULTA TARIFAS PARA CAMIONES
    $sql = "SELECT 
        t.tar_nombre,
        t.tar_valor,
        t.tar_bloque
        FROM tarifas t
        WHERE t.tar_categoria = 4";

    $stmt = $pdo->query($sql);    

    while ($fila = $stmt->fetch()) {    
            $tarifaHora4 = $fila['tar_valor'];
            $tarifa12Horas4 = $fila['tar_bloque'];
            $tarifaDia4 = $fila['tar_bloque'] * 2;
        }
// CONSULTA TARIFAS PARA BUSETAS
    $sql = "SELECT 
        t.tar_nombre,
        t.tar_valor,
        t.tar_bloque
        FROM tarifas t
        WHERE t.tar_categoria = 7";

    $stmt = $pdo->query($sql);    

    while ($fila = $stmt->fetch()) {    
            $tarifaHora7 = $fila['tar_valor'];
            $tarifa12Horas7 = $fila['tar_bloque'];
            $tarifaDia7 = $fila['tar_bloque'] * 2;
        }
// -------------------------------
// FUNCIONES DE CÁLCULO POR CATEGORÍA
// -------------------------------



function calcularMotos($minutos, $tarifaHora1, $tarifa12Horas1, $tarifaDia1)
{
    $tarifaHora = $tarifaHora1;
    $tarifa12Horas =  $tarifa12Horas1;
    $tarifaDia = $tarifaDia1;

    if ($minutos <= 10) return 0;

    $horasI = floor($minutos / 60);
    $minutosRestantes = $minutos % 60;
    $horas = max(1, $horasI);

    if ($minutosRestantes > 15 && $horasI >= 1) $horas++;

    if ($horas <= 12) return min($horas * $tarifaHora, $tarifa12Horas);

    elseif ($horas <= 24) {
        $horasExtra = $horas - 12;
        $costo = $tarifa12Horas + ($horasExtra * $tarifaHora);
        return min($costo, $tarifaDia);
    } else {
        $diasCompletos = floor($horas / 24);
        $horasRestantes = $horas % 24;
        return ($diasCompletos * $tarifaDia) + calcularMotos($horasRestantes * 60,$tarifaHora1,  $tarifa12Horas1, $tarifaDia1);
    }
}
function calcularAutomoviles($minutos,$tarifaHora2, $tarifa12Horas2, $tarifaDia2)
{
    $tarifaHora = $tarifaHora2;
    $tarifa12Horas = $tarifa12Horas2;
    $tarifaDia = $tarifaDia2;

    if ($minutos <= 10) return 0;
    $horasI = floor($minutos / 60);
    $minutosRestantes = $minutos % 60;
    $horas = max(1, $horasI);

    if ($minutosRestantes > 15 && $horasI >= 1) $horas++;
    if ($horas <= 12) return min($horas * $tarifaHora, $tarifa12Horas);
    elseif ($horas <= 24) {
        $horasExtra = $horas - 12;
        $costo = $tarifa12Horas + ($horasExtra * $tarifaHora);
        return min($costo, $tarifaDia);
    } else {
        $diasCompletos = floor($horas / 24);
        $horasRestantes = $horas % 24;
        return ($diasCompletos * $tarifaDia) + calcularAutomoviles($horasRestantes * 60,$tarifaHora2,  $tarifa12Horas2, $tarifaDia2);
    }
}

function calcularTurbos($minutos,$tarifaHora3, $tarifa12Horas3, $tarifaDia3)
{
    $tarifaHora = $tarifaHora3;
    $tarifa12Horas = $tarifa12Horas3;
    $tarifaDia = $tarifaDia3;

    if ($minutos <= 10) return 0;
    $horasI = floor($minutos / 60);
    $minutosRestantes = $minutos % 60;
    $horas = max(1, $horasI);

    if ($minutosRestantes > 15 && $horasI >= 1) $horas++;
    if ($horas <= 12) return min($horas * $tarifaHora, $tarifa12Horas);
    elseif ($horas <= 24) {
        $horasExtra = $horas - 12;
        $costo = $tarifa12Horas + ($horasExtra * $tarifaHora);
        return min($costo, $tarifaDia);
    } else {
        $diasCompletos = floor($horas / 24);
        $horasRestantes = $horas % 24;
        return ($diasCompletos * $tarifaDia) + calcularTurbos($horasRestantes * 60,$tarifaHora3, $tarifa12Horas3, $tarifaDia3);
    }
}

function calcularCamiones($minutos,$tarifaHora4, $tarifa12Horas4, $tarifaDia4)
{
    $tarifaHora = $tarifaHora4;
    $tarifa12Horas = $tarifa12Horas4;
    $tarifaDia = $tarifaDia4;

    if ($minutos <= 10) return 0;
    $horasI = floor($minutos / 60);
    $minutosRestantes = $minutos % 60;
    $horas = max(1, $horasI);

    if ($minutosRestantes > 15 && $horasI >= 1) $horas++;
    if ($horas <= 12) return min($horas * $tarifaHora, $tarifa12Horas);
    elseif ($horas <= 24) {
        $horasExtra = $horas - 12;
        $costo = $tarifa12Horas + ($horasExtra * $tarifaHora);
        return min($costo, $tarifaDia);
    } else {
        $diasCompletos = floor($horas / 24);
        $horasRestantes = $horas % 24;
        return ($diasCompletos * $tarifaDia) + calcularCamiones($horasRestantes * 60,$tarifaHora4, $tarifa12Horas4, $tarifaDia4);
    }
}

function calcularBusetas($minutos, $tarifaHora7, $tarifa12Horas7, $tarifaDia7)
{
    $tarifaHora = $tarifaHora7;
    $tarifa12Horas = $tarifa12Horas7;
    $tarifaDia = $tarifaDia7;

    if ($minutos <= 10) return 0;
    $horasI = floor($minutos / 60);
    $minutosRestantes = $minutos % 60;
    $horas = max(1, $horasI);

    if ($minutosRestantes > 15 && $horasI >= 1) $horas++;
    if ($horas <= 12) return min($horas * $tarifaHora, $tarifa12Horas);
    elseif ($horas <= 24) {
        $horasExtra = $horas - 12;
        $costo = $tarifa12Horas + ($horasExtra * $tarifaHora);
        return min($costo, $tarifaDia);
    } else {
        $diasCompletos = floor($horas / 24);
        $horasRestantes = $horas % 24;
        return ($diasCompletos * $tarifaDia) + calcularBusetas($horasRestantes * 60, $tarifaHora7, $tarifa12Horas7, $tarifaDia7);
    }
}

// =============================
// CONSULTA PRINCIPAL CON PDO
// =============================
try {
    $sql = "
        SELECT 
            PA.parqueo_id,
            PA.placa_cli,
            PA.fecha_ini,
            PA.tarifa,
            PA.usuario,
            PA.estado,
            CA.cat_imagen,
            TT.tar_tiempo,
            TT.tar_id_nombre,
            US.nombre,
            TA.tar_valor,
            TA.tar_bloque,
            CL.categoria,
            CA.cat_nombre
        FROM parqueo AS PA
        INNER JOIN cliente AS CL ON PA.placa_cli = CL.placa
        INNER JOIN usuarios AS US ON PA.usuario = US.id
        INNER JOIN tarifas AS TA ON PA.tarifa = TA.tar_id
        INNER JOIN tar_tiempo AS TT ON TA.tar_nombre = TT.tar_id_nombre
        INNER JOIN categorias AS CA ON CA.CAT_ID = CL.categoria
        
        WHERE PA.estado = 'SI'
        ORDER BY PA.parqueo_id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll();

    $json = [];
    $fechaActual = date('Y-m-d G:i:s');

    foreach ($resultados as $row) {
        $fecha_ini = strtotime($row['fecha_ini']);
        $fecha_fin = strtotime($fechaActual);
        $tiempo_transcurrido = $fecha_fin - $fecha_ini;
        $tiempo1 = conversorSegundosHoras($tiempo_transcurrido);
        $minutos = floor($tiempo_transcurrido / 60);

        switch ($row['categoria']) {
            case 1: $valor = calcularMotos($minutos,$tarifaHora1,   $tarifa12Horas1, $tarifaDia1); break;
            case 2: $valor = calcularAutomoviles($minutos,$tarifaHora2,  $tarifa12Horas2, $tarifaDia2); break;
            case 3: $valor = calcularTurbos($minutos,$tarifaHora3, $tarifa12Horas3, $tarifaDia3); break;
            case 4: $valor = calcularCamiones($minutos,$tarifaHora4, $tarifa12Horas4, $tarifaDia4); break;
            case 7: $valor = calcularBusetas($minutos, $tarifaHora7, $tarifa12Horas7, $tarifaDia7); break;
            default: $valor = 0;
        }

        $json[] = [
            'parqueo_id' => $row['parqueo_id'],
            'fecha_ini' => $row['fecha_ini'],
            'fecha_fin' => $fechaActual,
            'tiempo' => $tiempo1,
            'valor' => $valor,
            'tarifas' => $row['tar_valor'],
            'cat_imagen' => $row['cat_imagen'],
            'tar_tiempo' => $row['tar_tiempo'],
            'estado' => $row['estado'],
            'nombre' => $row['nombre'],
            'usuario' => $id,
            'placa_cli' => $row['placa_cli'],
            'categoria' => $row['categoria'],
            'cat_nombre' => $row['cat_nombre'],
            'tar_bloque' => $row['tar_bloque']
        ];
    }

    echo json_encode($json);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
}
?>
