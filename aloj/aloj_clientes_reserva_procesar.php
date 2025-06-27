<?php
session_start();
require_once "../conexion/conexion.php";


// Obtener el próximo ID de reserva (AUTO_INCREMENT)
$stmtAI = $pdo->query("SHOW TABLE STATUS LIKE 'aloj_reservas'");
$tabla = $stmtAI->fetch();
$proximo_reserva_id = $tabla['Auto_increment'];

if (!isset($_SESSION['id'])) {
    die("No autorizado.");
}

$usuario_id = $_SESSION['id'];
$created_at = date("Y-m-d H:i:s");

try {
    // =============================
    // 1. DATOS DEL CLIENTE
    // =============================
    $nombre         = strtoupper(trim($_POST['nombre']));
    $documento      = trim($_POST['documento']);
    $telefono       = trim($_POST['telefono']);
    $procedencia    = strtoupper(trim($_POST['procedencia']));
    $placa_vehiculo = strtoupper(str_replace('-', '', $_POST['placa_vehiculo']));

    if ($nombre == "") {
        throw new Exception("El nombre es obligatorio.");
    }

    if (strlen($documento) < 5 || strlen($documento) > 10 || !is_numeric($documento)) {
        throw new Exception("Documento inválido.");
    }

    // Verificar si cliente existe
    $stmt = $pdo->prepare("SELECT id FROM aloj_clientes WHERE documento = ?");
    $stmt->execute([$documento]);
    $cliente = $stmt->fetch();

    if ($cliente) {
        $cliente_id = $cliente['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO aloj_clientes 
            (nombre, documento, telefono, procedencia, placa_vehiculo, usuario_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $documento, $telefono, $procedencia, $placa_vehiculo, $usuario_id, $created_at]);
        $cliente_id = $pdo->lastInsertId();
    }

    

    // =============================
    // 2. DATOS DE LA RESERVA
    // =============================
    if (empty($_POST['habitacion_id']) || empty($_POST['valor_total']) || empty($_POST['rango_fechas'])) {
        throw new Exception("Debe completar habitación, valor y fechas.");
    }

    $habitacion_id      = $_POST['habitacion_id'];
    $cantidad_personas  = $_POST['cantidad_personas'];
    $valor_total        = floatval($_POST['valor_total']);
    $estado             = $_POST['estado'] ?? 'pendiente';

    list($fecha_ingreso, $fecha_salida) = explode(' / ', $_POST['rango_fechas']);

    if (!$fecha_ingreso || !$fecha_salida) {
        throw new Exception("Debe seleccionar un rango de fechas válido.");
    }

    if ($fecha_ingreso > $fecha_salida) {
        throw new Exception("La fecha de ingreso no puede ser posterior a la salida.");
    }

    // Calcular diferencia de días
    $datetime1 = new DateTime($fecha_ingreso);
    $datetime2 = new DateTime($fecha_salida);
    $dias = $datetime1->diff($datetime2)->days;

    if ($dias <= 0) {
        throw new Exception("La reserva debe ser al menos de una noche.");
    }

    // Opcional: Puedes guardar $dias en otra columna si deseas

    // =============================
    // 3. GUARDAR RESERVA
    // =============================
    $stmt = $pdo->prepare("
    INSERT INTO aloj_reservas (id, cliente_id, habitacion_id, fecha_ingreso, fecha_salida, cantidad_personas, valor_total, estado, usuario_id, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente', ?, NOW())
");
$stmt->execute([
    $proximo_reserva_id,
    $cliente_id,
    $habitacion_id,
    $fecha_ingreso,
    $fecha_salida,
    $cantidad_personas,
    $valor_total,
    $_SESSION['id']
]);

   $reserva_id = $pdo->lastInsertId();

echo "<script>
    alert('✅ Cliente y reserva guardados correctamente. Total: $dias noche(s).');
    window.location.href = 'aloj_pagos.php?reserva_id=$reserva_id';
</script>";


} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
} catch (PDOException $e) {
    echo "❌ Error en base de datos: " . $e->getMessage();
}

if (isset($_POST['acompanantes']['nombre'])) {
    $nombres     = $_POST['acompanantes']['nombre'];
    $documentos  = $_POST['acompanantes']['documento'];
    $edades      = $_POST['acompanantes']['edad'];
    $usuario_id  = $_SESSION['id']; // quien registró

    for ($i = 0; $i < count($nombres); $i++) {
        $nombre = strtoupper(trim($nombres[$i]));
        $documento = trim($documentos[$i]);
        $edad = intval($edades[$i]);

        if ($nombre && $documento && $edad > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO aloj_acompanantes (reserva_id, nombre, documento, edad, usuario_id, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$proximo_reserva_id, $nombre, $documento, $edad, $usuario_id]);
        }
    }
}
?>
