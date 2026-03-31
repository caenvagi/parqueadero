<?php
require '../conexion/conexion.php';

error_reporting(0);
ini_set('display_errors', 0);


$anio = date('Y');      // Año actual
$anio2 = $anio - 1;     // Año anterior

$sqlComp = "SELECT 
            YEAR(fecha_recibo) anio,
            MONTH(fecha_recibo) mes,
            SUM(valor_pagado) total
            FROM recibo p
            INNER JOIN cliente v ON p.placa = v.placa
            WHERE YEAR(fecha_recibo) IN (:a1, :a2) AND
            v.mensualidad = 'NO'
            AND v.cli_tar_tiempo = '1'
            GROUP BY anio, mes";

$stmt = $pdo->prepare($sqlComp);
$stmt->execute([
    'a1'=>$anio,
    'a2'=>$anio2
]);

$comparacion = $stmt->fetchAll();

// Inicializar meses en 0
$dataA1 = array_fill(1,12,0);
$dataA2 = array_fill(1,12,0);

foreach($comparacion as $row){
    if($row['anio'] == $anio){
        $dataA1[$row['mes']] = (int)$row['total'];
    } else {
        $dataA2[$row['mes']] = (int)$row['total'];
    }
}

$response['comparacion'] = [
    "anio1"=>$anio,
    "anio2"=>$anio2,
    "data1"=>array_values($dataA1),
    "data2"=>array_values($dataA2)
];

// INGRESOS POR MES
        $sql = "SELECT 
        MONTH(p.fecha_recibo) AS mes,
        SUM(p.valor_pagado) AS dinero
        FROM recibo p
        INNER JOIN cliente v 
        ON p.placa = v.placa
        WHERE 
        v.mensualidad = 'NO'
        AND v.cli_tar_tiempo = '1'
        AND YEAR(p.fecha_recibo) = YEAR(CURDATE())
        GROUP BY mes
        ORDER BY mes;";

$stmt = $pdo->query($sql);
$ingresos = $stmt->fetchAll();

// KPIs
$sqlTotales = "SELECT 
        SUM(valor_pagado) AS total_dinero,
        COUNT(*) AS total_vehiculos,
        V.mensualidad
        FROM recibo p
        INNER JOIN cliente v ON p.placa = v.placa
        WHERE YEAR(fecha_recibo) = YEAR(CURDATE())
        AND MONTH(fecha_recibo) = MONTH(CURDATE())
        AND v.mensualidad = 'NO'
        AND v.cli_tar_tiempo = '1'";

        $totales = $pdo->query($sqlTotales)->fetch();

        $total_dinero = $totales['total_dinero'] ?? 0;
        $total_vehiculos = $totales['total_vehiculos'] ?? 0;



// // CATEGORIAS
$sqlCat = "SELECT 
                cat.cat_nombre,
                COUNT(p.parqueo_id) total
                FROM parqueo p
                INNER JOIN cliente v ON p.placa_cli = v.placa
                INNER JOIN categorias cat ON v.categoria = cat.cat_id
                WHERE YEAR(fecha_ini) = YEAR(CURDATE())
                AND MONTH(fecha_ini) = MONTH(CURDATE())               
                GROUP BY cat.cat_nombre";

$categorias = $pdo->query($sqlCat)->fetchAll();

echo json_encode([
        "comparacion"=>$response['comparacion'],
        "ingresos"=>$ingresos,
        "categorias"=>$categorias,
        "total_dinero"=>$total_dinero,
        "total_vehiculos"=>$total_vehiculos,
]);