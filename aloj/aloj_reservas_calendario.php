<?php
require_once "../conexion/conexion.php";

$sql = "SELECT r.id, r.fecha_ingreso, r.fecha_salida, r.estado, h.nombre AS habitacion
        FROM aloj_reservas r
        JOIN aloj_habitaciones h ON r.habitacion_id = h.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$eventos = [];

foreach ($reservas as $reserva) {
    $color = match ($reserva['estado']) {
        'confirmada' => '#dc3545',     // rojo
        'pendiente'  => '#fd7e14',     // naranja
        'cancelada'  => '#6c757d',     // gris
        'finalizada' => '#198754',     // verde
        default      => '#0d6efd'      // azul para otros
    };

    $eventos[] = [
    'title' => "Hab: " . $reserva['habitacion'],
    'start' => $reserva['fecha_ingreso'],
    'end'   => date('Y-m-d', strtotime($reserva['fecha_salida'] . ' +1 day')),
    'color' => $color,
    'extendedProps' => [
        'estado' => $reserva['estado'],
        'habitacion' => $reserva['habitacion'],
        'id' => $reserva['id']
    ]
];
}

header('Content-Type: application/json');
echo json_encode($eventos);
