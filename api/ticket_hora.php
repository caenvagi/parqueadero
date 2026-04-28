<?php

sleep(1);
// ✅ Conexión actual del proyecto
require_once '../conexion/conexion.PHP';

date_default_timezone_set('America/Bogota');


// ✅ Librería de impresión Mike42
require __DIR__ . '/autoload.php';
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

// ✅ Configura el nombre de la impresora (debe coincidir con el nombre compartido en Windows)
$nombre_impresora = "POS-80C";

$ticket = $_POST['ticket'] ?? $_GET['ticket'] ?? 0;

if (!$ticket) {
    throw new Exception("Ticket no recibido");
}

try {
    $connector = new WindowsPrintConnector($nombre_impresora);
    $printer = new Printer($connector);

    // ✅ Consulta del último ingreso
   $stmt = $pdo->prepare("
    SELECT 
        p.parqueo_id,
        p.placa_cli,
        DATE(p.fecha_ini) AS fecha,
        TIME(p.fecha_ini) AS hora,
        u.nombre AS cajero,
        t.tar_valor,
        tt.tar_tiempo,
        cat.cat_nombre,
        t.tar_bloque
    FROM parqueo p
    INNER JOIN usuarios u ON u.id = p.usuario
    INNER JOIN cliente c ON c.placa = p.placa_cli
    INNER JOIN categorias cat ON cat.cat_id = c.categoria
    INNER JOIN tarifas t ON t.tar_categoria = c.categoria
    INNER JOIN tar_tiempo tt ON tt.tar_id_nombre = t.tar_nombre
    WHERE p.parqueo_id = :ticket
");

$stmt->execute([
    ':ticket' => $ticket   // 👈 con :
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception("No hay registros recientes de parqueo.");
    }

    // ✅ Encabezado del ticket
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(2, 2);
    $printer->text("Parqueadero\n");
    $printer->setTextSize(2, 2);
    $printer->text("Parque de la Familia\n");
    $printer->setTextSize(2, 1);
    $printer->text("Montenegro - Quindío\n");
    $printer->setTextSize(2, 1);
    $printer->text("WhatsApp: 300-1087869\n");
    $printer->text("------------------------\n");

    // ✅ Detalles del ingreso
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->setTextSize(1, 2);
    $printer->setTextSize(1, 2);$printer->text("Ticket No:");$printer->setTextSize(2, 2);$printer->text("       ".$row['parqueo_id']."\n");

    $printer->setTextSize(1, 2);$printer->text("PLACA No:");$printer->setTextSize(3, 3);$printer->text("     ".$row['placa_cli']."\n");

    $printer->setTextSize(1, 1);$printer->text("Fecha Entrada:");$printer->setTextSize(2, 1); $printer->text("     ".$row['fecha']."\n");
$printer->setTextSize(1, 1);$printer->text("Hora Entrada :");$printer->setTextSize(2, 1); $printer->text("     ".$row['hora']."\n");
$printer->setTextSize(1, 1);$printer->text("Categoria    :");$printer->setTextSize(1, 2);$printer->text("          ".$row['cat_nombre']."\n");
$printer->setTextSize(1, 1);$printer->text("Tarifa       :");$printer->setTextSize(1, 2);$printer->text("  $".number_format($row['tar_valor'], 0, ",", ".")." * ".$row['tar_tiempo']." Y/O FRACCION\n");
$printer->setTextSize(1, 1);$printer->text("Tarifa       :");$printer->setTextSize(1, 2);$printer->text("  $".number_format($row['tar_bloque'], 0, ",", ".")." * ".'12 horas'."\n");
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setTextSize(2, 1);
$printer->text("------------------------\n");
$printer->setTextSize(1, 1);$printer->text("Cajero:");$printer->setTextSize(1, 2);$printer->text("          ".$row['cajero']."\n");
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setTextSize(2, 1);
$printer->text("------------------------\n");

    // ✅ Código de barras
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
    $printer->barcode("{B" . $row['parqueo_id'], Printer::BARCODE_CODE128);
    $printer->text("------------------------\n");

    // ✅ Reglamento
    $printer->setTextSize(2, 1);
    $printer->text("REGLAMENTO\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->setTextSize(1, 1);
    $printer->text("1. El vehículo se entrega al portador del recibo\n");
    $printer->text("2. No se aceptan órdenes telefónicas ni escritas\n");
    $printer->text("3. Retirado el vehículo no aceptamos reclamos\n");
    $printer->text("4. No se responde por objetos dejados en el vehículo\n");
    $printer->text("5. No se responde por pérdidas o daños por causas de fuerza mayor\n");
    $printer->text("6. Asegure bien su vehículo (ventanas y seguros)\n");
    $printer->text("7. No se permite la permanencia de personas dentro del vehículo\n");

    // ✅ Final del ticket
    $printer->feed(1);
    $printer->cut();
    $printer->pulse();
    $printer->close();

    echo json_encode([
    "success" => true,
    "message" => "Ticket impreso"
]);
    exit();

} catch (Exception $e) {
    echo "⚠️ Error: " . $e->getMessage();
}
?>
