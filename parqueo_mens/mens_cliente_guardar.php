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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // $fecha_creacion = $_POST['fecha_creacion'];
    $placa = strtoupper($_POST['placa']);
    $nombre = $_POST['nombre'];
    $cedula = $_POST['cedula'];
    $celular = $_POST['celular'];
    $vehiculo = $_POST['vehiculo'];
    $categoria = $_POST['categoria'];
    $plan = $_POST['plan'];
    $valor = $_POST['valor'];    
    $caseta = $_POST['caseta'];
    $user = $_POST['user'];
    $fechaInicio = date("Y-m-d");
    $usuario = $_SESSION['id'];// luego puedes usar sesión

    // ===== VALIDAR SI YA EXISTE LA PLACA =====

    $sql = "SELECT placa FROM cliente WHERE placa=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placa]);

    if ($stmt->rowCount() > 0) {
        echo "<div class='alert alert-danger'>
        La placa ya está registrada cliente por horas.
        dirígete a la sección de clientes por horas para editar o eliminar el cliente. 
        <a href='editar_cliente.php?placa=$placa' class='alert-link'>
            Editar cliente
        </a>
      </div>";
        exit;
    }

    // ===== CALCULAR FECHA FIN =====

    function calcularFechaFin($fechaInicio, $plan)
    {
        if ($plan == 7)
            return date('Y-m-d', strtotime($fechaInicio . ' +7 days'));

        if ($plan == 6)
            return date('Y-m-d', strtotime($fechaInicio . ' +15 days'));

        if ($plan == 3)
            return date('Y-m-d', strtotime($fechaInicio . ' +1 month'));
    }

    $fecha_fin = calcularFechaFin($fechaInicio, $plan);

    try {

        $pdo->beginTransaction();

        // ===== INSERTAR CLIENTE =====

        $sql = "INSERT INTO cliente
        (fecha_creacion,placa,nombre,cedula,celular,vehiculo,categoria,valor,cli_tar_tiempo,caseta,mensualidad,activo,user)
        VALUES (NOW(),?,?,?,?,?,?,?,?,?,'SI','SI',?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $placa,
            $nombre,
            $cedula,
            $celular,
            $vehiculo,
            $categoria,
            $valor,
            $plan,
            $caseta,
            $usuario
        ]);

        // ===== HISTORIAL =====

        $sql = "INSERT INTO mensualidad_historial
        (placa, fecha_ingreso, fecha_retiro, caseta, valor, plan, usuario, observacion)
        VALUES (?, NOW(), NULL, ?, ?, ?, ?, 'Ingreso mensualidad')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $placa,
            $caseta,
            $valor,
            $plan,
            $usuario
        ]);

        // ===== CREAR PAGO =====

        $sql = "INSERT INTO pagos
        (fecha,
        estado,
        placa,
        valor,
        plan,
        fecha_inicio,
        fecha_fin,
        usuario,
        caseta,
        observacion)
        VALUES (NOW(), 
        'PENDIENTE', 
        ?, 
        ?, 
        ?, 
        NOW(), 
        ?, 
        ?, 
        ?, 
        'Cobro mensualidad')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $placa,
            $valor,
            $plan,
            $fecha_fin,
            $usuario,
            $caseta
            
        ]);

        // ===== OCUPAR CASETA =====

        $sql = "UPDATE casetas
        SET casetas_estado='Ocupado'
        WHERE caseta_id=?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$caseta]);

        $pdo->commit();

        echo "<div class='alert alert-success'>
        Cliente registrado correctamente
        </div>";

    } catch (Exception $e) {

    $pdo->rollBack();

    echo "<div class='alert alert-danger'>
    Error: ".$e->getMessage()."
    </div>";
}
}
?>
