<?php
header('Content-Type: application/json');

require_once "../conexion/conexion.php";
require __DIR__ . '/../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// 📥 Leer JSON
$data = json_decode(file_get_contents("php://input"), true);

$placa = $data['placa'];
$fecha_ini = $data['fecha_ini'];
$fecha_fin = $data['fecha_fin'];
$valor = $data['valor'];
$tarifa = $data['tarifa'];
$usuario = $data['usuario'];

try {

    // 🔹 1. GUARDAR EN BD
    $stmt = $pdo->prepare("
        INSERT INTO recibo 
        (recibo_man, placa, fecha_ini, fecha_fin, tiempo, tarifa_recibo, valor_pagado, valor_manual, usuario, cierre, periodo)
        VALUES 
        ('AUTO', :placa, :fecha_ini, :fecha_fin, 'N/A', :tarifa, :valor, 0, :usuario, 'NO', 1)
    ");

    $stmt->execute([
        ':placa' => $placa,
        ':fecha_ini' => $fecha_ini,
        ':fecha_fin' => $fecha_fin,
        ':tarifa' => $tarifa,
        ':valor' => $valor,
        ':usuario' => $usuario
    ]);

    // 🔹 2. IMPRIMIR
    $connector = new WindowsPrintConnector("POS-58"); // 👈 nombre impresora
    $printer = new Printer($connector);

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("PARQUEADERO\n");
    $printer->text("-----------------------\n");

    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Placa: $placa\n");
    $printer->text("Ingreso: $fecha_ini\n");
    $printer->text("Salida: $fecha_fin\n");
    $printer->text("Valor: $ $valor\n");

    $printer->text("\nGracias por su visita\n");

    $printer->cut();
    $printer->close();

    echo json_encode([
        "success" => true,
        "message" => "Ticket impreso"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}