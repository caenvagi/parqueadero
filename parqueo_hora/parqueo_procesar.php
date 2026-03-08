<?php
session_start();
    require_once "../conexion/conexion.php";

    date_default_timezone_set('America/Bogota');

    if (!isset($_SESSION['id'])) {
        header("Location: index.php");
    }
    $id = $_SESSION['id'];
    $tipo_usuario = $_SESSION['tipo_usuario'];
    
    if ($tipo_usuario == 1) {
        $where = "";
    } else if ($tipo_usuario == 2) {
        $where = "WHERE id=$id";
    }

try {
    $placa = strtoupper(trim($_POST['placa']));
    $nombre = trim($_POST['nombre']);
    $celular = trim($_POST['celular']);
    $vehiculo = trim($_POST['vehiculo']);
    $categoria = (int)$_POST['categoria'];
    $caseta = (int)$_POST['casetas'];
    $usuario = $_SESSION['id'];;

    if(!$placa || !$nombre || !$celular || !$vehiculo || !$categoria || !$caseta || !$usuario){
        throw new Exception('Datos incompletos');
    }

    // 1️⃣ Validar si la placa ya está registrada en parqueo con estado activo
        $sql_check = "SELECT * FROM parqueo WHERE placa_cli = :placa AND estado = 'SI'";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute(['placa' => $placa]);

       if ($stmt_check->rowCount() > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => "🚫 El vehículo con placa $placa ya se encuentra en el parqueadero."
        ]);
        exit;
    } 

    // Verificar si el cliente ya existe
    $stmt = $pdo->prepare("SELECT placa FROM cliente WHERE placa = ?");
    $stmt->execute([$placa]);
    if($stmt->rowCount() == 0) {
        $sql = "INSERT INTO cliente (fecha_creacion, placa, nombre, cedula, celular, vehiculo, categoria, valor, cli_tar_tiempo, caseta, mensualidad, activo, user)
                VALUES (NOW(), ?, ?, 0, ?, ?, ?, 0, 1, ?, 'NO', 'SI', ?)";
        $pdo->prepare($sql)->execute([$placa, $nombre, $celular, $vehiculo, $categoria, $caseta, $usuario]);
    }

    // Insertar registro en parqueo
    $sql = "INSERT INTO parqueo (placa_cli, fecha_ini, tarifa, caseta, usuario, estado)
            VALUES (?, NOW(), 1, ?, ?, 'SI')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa, $caseta, $usuario]);

    // Cambiar estado de la caseta a Ocupado
        $update = $pdo->prepare("UPDATE casetas SET casetas_estado = 'Ocupado' WHERE caseta_id = ?");
        $update->execute([$caseta]);
    

    echo json_encode(['status' => 'success',
        'message' => "✅ Vehículo $placa ingresado correctamente."]);
        //  "<script>            
        //     // 🔹 Abre el ticket en una nueva ventana para impresión
        //     window.open('../modulos/imprimir_ticket_php/ticket_hora.php', '_blank', 'width=400,height=600');
        // </script>";
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al registrar el parqueo: ' . $e->getMessage()
    ]);
}
