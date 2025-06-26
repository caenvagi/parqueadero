<?php
require_once "../conexion/conexion.php";

$usuario_id = $_GET['usuario_id'] ?? null;
$fecha_inicio = $_GET['start'];
$fecha_fin = $_GET['end'];

$sql = "SELECT t.*, u.nombre 
        FROM usuarios_turnos t 
        JOIN usuarios u ON t.usuario_id = u.id 
        WHERE t.fecha_inicio >= ? 
          AND t.fecha_fin <= ?";
$params = [$fecha_inicio, $fecha_fin];

if (!empty($usuario_id)) {
    $sql .= " AND t.usuario_id = ?";
    $params[] = $usuario_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);



$eventos = [];

foreach ($stmt as $row) {
    $estado = $row['pagado'] == 1 ? 'Pagado' : 'Por pagar';
    $fechaInicio = new DateTime($row['fecha_inicio']);
    $fechaFin = new DateTime($row['fecha_fin']);
    // Si el evento pasa de un día a otro, acórtalo visualmente
if ($fechaFin->format('Y-m-d') !== $fechaInicio->format('Y-m-d')) {
    // Solo mostrar una hora (u otra duración visible)
    $fechaFinVisual = clone $fechaInicio;
    $fechaFinVisual->modify('+1 hour');
} else {
    $fechaFinVisual = $fechaFin;
} 
    $eventos[] = [
  'id' => $row['id_turno'],
  'title' => '', // lo dejaremos vacío si vamos a personalizar visualmente el HTML
  'start' => $row['fecha_inicio'],
  'end' => $fechaFinVisual->format('Y-m-d\TH:i:s'),
  
  'extendedProps' => [
    'nombre' => $row['nombre'],
    'valor' => $row['valor'],
    'pagado' => $row['pagado'], // 1 o 0
    'hora_real_fin' => $fechaFin->format('Y-m-d\TH:i:s') // si necesitas en el popover
  ]
];
}

echo json_encode($eventos);
