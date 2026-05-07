<?php

sleep(1);
session_start();

// ✅ Conexión actual del proyecto
require_once '../../conexion/conexion.PHP';

// ✅ Verificación de sesión
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$id = $_SESSION['id'];
$nombre_usuario = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario = $_SESSION['usuario'];

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

$nombre_impresora = "xp-80c pos";

$connector = new WindowsPrintConnector($nombre_impresora);
$printer = new Printer($connector);

/* Print top logo */
//$printer -> setJustification(Printer::JUSTIFY_CENTER);

// $logo = EscposImage::load("logo.bmp", true);
// $printer->bitImage($logo);

// $img = EscposImage::load("logo.bmp");
// $printer -> graphics($img);

/*
Imprimimos un mensaje. Podemos usar
el salto de línea o llamar muchas
veces a $printer->text()
 */

   try {
    $connector = new WindowsPrintConnector($nombre_impresora);
    $printer = new Printer($connector);

     // ✅ Consulta del último ingreso
    $stmt = $pdo->query("
        SELECT r.recibo_id,
               r.recibo_man,
               r.fecha_recibo,
               DATE(r.fecha_ini) as fechaini,
               TIME(r.fecha_ini) as horaini,
               DATE(r.fecha_fin) as fechafin,
               TIME(r.fecha_fin) as horafin,
               r.ticket,
               r.placa,
               r.fecha_ini,
               r.fecha_fin,
               r.tiempo,
               r.valor_pagado,
               r.usuario,
               cat.cat_nombre,
               u.nombre,
               tar_valor,
               tar_bloque,
               tar_tiempo        
        FROM recibo r
        INNER JOIN cliente c ON c.placa = r.placa
        INNER JOIN categorias cat ON cat.cat_id = c.categoria
        INNER JOIN usuarios u ON u.id = r.usuario
        INNER JOIN  tarifas AS TA  ON TA.tar_id = r.tarifa_recibo
        INNER JOIN  tar_tiempo AS TT ON TT.tar_id_nombre = TA.tar_nombre
        
        
        ORDER BY r.recibo_id DESC
        LIMIT 1
    ");

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception("No hay registros recientes de parqueo.");
    }
    

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setTextSize(2, 2);
$printer->text("Parqueadero\n");
$printer->setTextSize(2, 2);
$printer->text("Parque de la familia");
$printer->setTextSize(2, 1);
$printer->feed();
$printer->text("Nit\n");
$printer->text("801.001.111-1\n");
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("------------------------\n");
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->setTextSize(1, 2);$printer->text("Recibo No:");$printer->setTextSize(2, 2);$printer->text("       ".$row['recibo_id']."\n");
$printer->setTextSize(1, 2);$printer->text("PLACA No:");$printer->setTextSize(3, 3);$printer->text("     ".$row['placa']."\n");
$printer->setTextSize(1, 2);$printer->text("Tipo Vehiculo:");$printer->setTextSize(2, 2);$printer->text("     ".$row['cat_nombre']."\n");
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setTextSize(2, 1);
$printer->text("------------------------\n");

 $printer->setJustification(Printer::JUSTIFY_LEFT);
 $printer->setTextSize(1, 1);$printer->text("Fecha Entrada:");$printer->setTextSize(2, 1); $printer->text("     ".$row['fechaini']."\n");
 $printer->setTextSize(1, 1);$printer->text("Hora Entrada :");$printer->setTextSize(2, 1); $printer->text("     ".$row['horaini']."\n");
 $printer->setTextSize(1, 1);$printer->text("Fecha Salida :");$printer->setTextSize(2, 1); $printer->text("     ".$row['fechafin']."\n");
 $printer->setTextSize(1, 1);$printer->text("Hora Salida  :");$printer->setTextSize(2, 1); $printer->text("     ".$row['horafin']."\n");
 $printer->setJustification(Printer::JUSTIFY_CENTER);
 $printer->text("------------------------\n");

$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->setTextSize(1, 1);$printer->text("Estadia      :");$printer->setTextSize(1, 2);$printer->text("      ".$row['tiempo']."\n");
$printer->setTextSize(1, 1);$printer->text("Tarifa       :");$printer->setTextSize(1, 2);$printer->text("      $ ".number_format($row['tar_valor'], 0, ",", ".")." * ".$row['tar_tiempo']."\n");
$printer->setTextSize(1, 1);$printer->text("Tarifa       :");$printer->setTextSize(1, 2);$printer->text("      $ ".number_format($row['tar_bloque'], 0, ",", ".")." * ".'12 horas'."\n");
$printer->setTextSize(1, 1);$printer->text("Valor a pagar:");$printer->setTextSize(2, 3);$printer->text("   $ ".number_format($row['valor_pagado'], 0, ",", ".")."\n");
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setTextSize(2, 1);
$printer->text("------------------------\n");

$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->setTextSize(1, 1);$printer->text("Cajero:");$printer->setTextSize(1, 2);$printer->text("          ".$row['nombre']."\n");
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setTextSize(2, 1);
$printer->text("------------------------\n");

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setTextSize(2, 1);
$printer->text("REGLAMENTO\n");
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->setTextSize(1, 1);
$printer->text("1-El vehiculo se entrega al portador del recibo.
2-No se aceptan ordenes telefonicas ni escritas.
3-Retirado el vehiculo no aceptamos ningun tipo
  de reclamo.
4-No se responde por objetos dejados en el
  vehiculo.
5-No se responde por la perdida, deterioro, o 
  danos ocurridos como consecuencia de incendio,
  terremoto,vendavales,asonada o revolucion 
  u otras causas similares.
6-El conductor debe asegurar bien su vehiculo
  (Ventanas y seguros).
7-No se permite la permanencia de personas 
  dentro del vehiculo una vez estacionado.\n");



// $printer->text("Wathsapp 1234567890\n\nParzibyte.me\n\nNo olvides suscribirte");
/*
Hacemos que el papel salga. Es como
dejar muchos saltos de línea sin escribir nada
 */
$printer->feed(1);

/*
Cortamos el papel. Si nuestra impresora
no tiene soporte para ello, no generará
ningún error
 */
$printer->cut();

/*
Por medio de la impresora mandamos un pulso.
Esto es útil cuando la tenemos conectada
por ejemplo a un cajón
 */
$printer->pulse();

/*
Para imprimir realmente, tenemos que "cerrar"
la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
 */
$printer->close();

//header('Location: ../../config/parqueoAjax.php');

echo "<script>window.close();</script>";

exit;

} catch (Exception $e) {
    echo "⚠️ Error: " . $e->getMessage();
}
