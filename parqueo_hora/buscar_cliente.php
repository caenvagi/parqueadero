
<?php
include '../conexion/conexion.php';

if (isset($_POST['placa'])) {
    $placa = strtoupper(trim($_POST['placa']));

    $sql = "SELECT * FROM cliente WHERE placa = :placa LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['placa' => $placa]);
    $cliente = $stmt->fetch();

    if ($cliente) {
        echo json_encode([
            'status' => 'found',
            'data' => [
                'nombre' => $cliente['nombre'],
                'cedula' => $cliente['cedula'],
                'celular' => $cliente['celular'],
                'vehiculo' => $cliente['vehiculo'],
                'categoria' => $cliente['categoria'],
                'valor' => $cliente['valor'],
                'plan_tarifa' => $cliente['plan_tarifa'],
                'mensualidad' => $cliente['mensualidad'],
                'activo' => $cliente['activo']
            ]
        ]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
}
?>
