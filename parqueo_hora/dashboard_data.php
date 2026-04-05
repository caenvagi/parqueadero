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
            WHERE YEAR(fecha_recibo) IN (:a1, :a2)
            AND p.tarifa_recibo = '1'
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
                INNER JOIN cliente v ON p.placa = v.placa
                WHERE p.tarifa_recibo = '1'
                AND YEAR(p.fecha_recibo) = YEAR(CURDATE())
                GROUP BY mes
                ORDER BY mes";

        $stmt = $pdo->query($sql);
        $ingresos = $stmt->fetchAll();

// KPIs
$sqlTotales = "SELECT 
        SUM(valor_pagado) AS total_dinero,
        COUNT(*) AS total_vehiculos,
        V.mensualidad,
        p.tarifa_recibo
        FROM recibo p
        INNER JOIN cliente v ON p.placa = v.placa
        WHERE YEAR(fecha_recibo) = YEAR(CURDATE())
        AND MONTH(fecha_recibo) = MONTH(CURDATE())
        AND p.tarifa_recibo = '1'";

        $totales = $pdo->query($sqlTotales)->fetch();

        $total_dinero = $totales['total_dinero'] ?? 0;
        $total_vehiculos = $totales['total_vehiculos'] ?? 0;



// // CATEGORIAS
$sqlCat = "SELECT 
                
                COUNT(p.recibo_id) total,
                cat.cat_nombre
                FROM recibo p
                inner join cliente c ON p.placa = c.placa
                inner join categorias cat ON c.categoria = cat.cat_id
                WHERE YEAR(fecha_recibo) = YEAR(CURDATE())
                AND MONTH(fecha_recibo) = MONTH(CURDATE())
                and tarifa_recibo = '1'
                GROUP by c.categoria";

$categorias = $pdo->query($sqlCat)->fetchAll();

echo json_encode([
        "comparacion"=>$response['comparacion'],
        "ingresos"=>$ingresos,
        "categorias"=>$categorias,
        "total_dinero"=>$total_dinero,
        "total_vehiculos"=>$total_vehiculos,
]);