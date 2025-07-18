<?php

sleep(1);
session_start();

require '../../conexion/conexion.php';

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

$nombre_impresora = "xp-80c pos";

$connector = new WindowsPrintConnector($nombre_impresora);
$printer = new Printer($connector);

/* Print top logo */
//$printer -> setJustification(Printer::JUSTIFY_CENTER);

 $logo = EscposImage::load("logo1.bmp", true);
 $printer->bitImage($logo);

// $img = EscposImage::load("logo.bmp");
// $printer -> graphics($img);

/*
Imprimimos un mensaje. Podemos usar
el salto de línea o llamar muchas
veces a $printer->text()
 */

  usleep(500000);

$reserva_id = isset($_GET['reserva_id']) ? intval($_GET['reserva_id']) : 0;

$stmt = $pdo->prepare("
    SELECT r.*, c.nombre AS cliente_nombre, h.nombre AS habitacion_nombre, u.usuario AS atendido_por
    FROM aloj_reservas r
    JOIN aloj_clientes c ON r.cliente_id = c.id
    JOIN aloj_habitaciones h ON r.habitacion_id = h.id
    LEFT JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$reserva_id]);
$reserva = $stmt->fetch();

if (!$reserva) {
    die("Reserva no encontrada.");
}

// Calcular días/noches
$fecha_ingreso = new DateTime($reserva['fecha_ingreso']);
$fecha_salida = new DateTime($reserva['fecha_salida']);
$intervalo = $fecha_ingreso->diff($fecha_salida);
$dias_estadia = $intervalo->days;
$noches_estadia = $dias_estadia;


$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setTextSize(2, 2);
$printer->text("ALOJAMIENTO EL DESCANSO\n");
$printer->setTextSize(1, 1);
$printer->text("Montenegro - Quindío\n");
$printer->text("WhatsApp: 314 813 9800\n");
$printer->text("-------------------------------\n");

$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->setTextSize(1, 1);
$printer->text("Reserva No: " . $reserva['id'] . "\n");
$printer->text("Cliente    : " . $reserva['cliente_nombre'] . "\n");
$printer->text("Fecha      : " . date('Y-m-d H:i') . "\n");
$printer->text("Ingreso    : " . date('Y-m-d', strtotime($reserva['fecha_ingreso'])) . "\n");
$printer->text("Salida     : " . date('Y-m-d', strtotime($reserva['fecha_salida'])) . "\n");
$printer->text("Habitación : " . $reserva['habitacion_nombre'] . "\n");
$printer->text("Estadía    : {$dias_estadia} día(s) / {$noches_estadia} noche(s)\n");
$printer->text("Personas   : " . $reserva['cantidad_personas'] . "\n");
$printer->text("Valor Total: $" . number_format($reserva['valor_total'], 0, ',', '.') . "\n");
$printer->text("Atendido por: " . ($reserva['atendido_por'] ?? 'N/D') . "\n");

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("-------------------------------\n");
$printer->text("¡Gracias por su reserva!\n");

$content = 123456;
$printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
$printer -> barcode("{B".$content, Printer::BARCODE_CODE128);
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

