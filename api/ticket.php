<?php

sleep(1);
session_start();

require '../conexion/conexion.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
}
$id = $_SESSION['id'];
$nombre = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario = $_SESSION['usuario'];
$usuarios = $_SESSION['usuario'];

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE id=$id";
}




require __DIR__ . '/autoload.php'; //Nota: si renombraste la carpeta a algo diferente de "ticket" cambia el nombre en esta línea
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

/*
Este ejemplo imprime un hola mundo en una impresora de tickets
en Windows.
La impresora debe estar instalada como genérica y debe estar
compartida
 */

/*
Conectamos con la impresora
 */

/*
Aquí, en lugar de "POS-58" (que es el nombre de mi impresora)
escribe el nombre de la tuya. Recuerda que debes compartirla
desde el panel de control
 */

$nombre_impresora = "Microsoft Print to PDF";

$connector = new WindowsPrintConnector($nombre_impresora);
$printer = new Printer($connector);

/* Print top logo */
//$printer -> setJustification(Printer::JUSTIFY_CENTER);

 $logo = EscposImage::load("logo1.bmp", true);
 $printer->bitImage($logo);


 // 5. Imprimir ticket con Mike42
    $connector = new WindowsPrintConnector("POS-80"); // Cambia a tu impresora
    $printer = new Printer($connector);

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("***** LIQUIDACIÓN DE CAJA *****\n");
    $printer->text("Fecha: " . date('d/m/Y h:i A') . "\n");
    $printer->text("Liquidación #: 12345 \n");
    $printer->text("------------------------------\n");

    $printer->setJustification(Printer::JUSTIFY_LEFT);
   

    $printer->text("------------------------------\n");
    $printer->setJustification(Printer::JUSTIFY_RIGHT);
    $printer->text("TOTAL: 100.000\n");

    $printer->feed(2);
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Entregado por: $id\n");
    $printer->text("Recibido por: \n");
    $printer->text("Observaciones: \n");

    $printer->feed(3);
    $printer->cut();
    $printer->close();

// $img = EscposImage::load("logo.bmp");
// $printer -> graphics($img);

/*
Imprimimos un mensaje. Podemos usar
el salto de línea o llamar muchas
veces a $printer->text()
 */

  usleep(500000);



echo "<script>window.close();</script>";


exit;

