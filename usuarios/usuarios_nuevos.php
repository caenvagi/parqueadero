<?php
session_start();
require_once "../conexion/conexion.php";

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

// REGISTRAR USUARIO NUEVO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $cedula = trim($_POST['cedula']);
    $cargo = trim($_POST['cargo']);
    $telefono = trim($_POST['telefono']);
    $usuario = trim($_POST['usuario']);
    $clave = trim($_POST['clave']);
    $tipo = trim($_POST['tipo_usuario']);
    $foto = trim($_POST['avatar']);
    $activo = trim($_POST['activo']);
    $contabilidad = trim($_POST['contabilidad']);
    $fecha_ingreso = trim($_POST['fecha_ingreso']);

    // Hashear la clave con password_hash()
    $claveHash = password_hash($clave, PASSWORD_DEFAULT);

    $sql = "        INSERT INTO usuarios 
                    (id, nombre, cedula, tipo_cargo, telefono, usuario, clave, tipo_usuario, avatar, activo, fecha_ingreso, contabilidad)
                    VALUES 
                    (:id, :nombre, :cedula, :cargo, :telefono, :usuario, :clave, :tipo, :foto, :activo, :fecha_ingreso, :contabilidad)";

    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'cedula' => $cedula,
            'cargo' => $cargo,
            'telefono' => $telefono,
            'usuario' => $usuario,
            'clave' => $claveHash,
            'tipo' => $tipo,
            'foto' => $foto,
            'activo' => $activo,
            'fecha_ingreso' => $fecha_ingreso,
            'contabilidad' => $contabilidad
        ]);
        // Obtener el ID recién insertado
            $ultimo_id = $pdo->lastInsertId();
        
        // Insertar en la tabla usuarios_historia
            $sql_historia = "INSERT INTO usuarios_historia (usuario, fecha_ingreso, cargo, user)
            VALUES (:usuario, :fecha_ingreso, :cargo, :user)";
            $stmt_historia = $pdo->prepare($sql_historia);
            $stmt_historia->execute([
                'usuario' => $ultimo_id,
                'fecha_ingreso' => $fecha_ingreso,
                'cargo' => $cargo,
                'user' => $_SESSION['id'],
            ]);
        
        // Confirmar la transacción
            //$pdo->commit();

        //echo "Usuario registrado correctamente.";
        header("location: usuarios_lista.php?mensaje=Usuario registrado correctamente.");
        // header("Location: usuarios.php"); // si deseas redirigir
    } catch (PDOException $e) {
        echo "<br><br><br><br>"."Error al registrar usuario: " . $e->getMessage();
        //header("location: usuarios_nuevos.php?mensaje=Error al registrar usuario");
    }
}
// REGISTRAR USUARIO NUEVO
// CONSULTA A TIPO DE CARGO PARA SELECT    
try {
    // Verifica que $pdo esté definido correctamente

    // Preparar la consulta
    $stmt = $pdo->prepare("  SELECT id_cargo, cargo_nombre 
                                        FROM tipo_cargo
                                        ");

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener resultados
    $cargos = $stmt->fetchAll();

    // Validar si hay datos
    if (!empty($cargos)) {
        // foreach ($cargos as $cargo) {
        //     echo "ID: " . htmlspecialchars($cargo['id_cargo']) . " - Nombre: " . htmlspecialchars($cargo['cargo_nombre']) . "<br>";
        // }
    } else {
        echo "No se encontraron cargos.";
    }
} catch (PDOException $e) {
    error_log("Error en la consulta: " . $e->getMessage());
    echo "Error al consultar los cargos.";
}
// CONSULTA A TIPO DE CARGO PARA SELECT
// CONSULTA A TIPO DE USUARIO PARA SELECT    
try {
    // Verifica que $pdo esté definido correctamente

    // Preparar la consulta
    $stmt = $pdo->prepare("SELECT id_tipo_usuario, tipo_usuario FROM tipo_usuarios");

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener resultados
    $tipo_usuarios = $stmt->fetchAll();

    // Validar si hay datos
    if (!empty($tipo_usuarios)) {
        // foreach ($tipo_usuarios as $tipo_usuario) {
        //     echo "ID: " . htmlspecialchars($tipo_usuario['id_tipo_usuario']) . " - tipo: " . htmlspecialchars($tipo_usuario['tipo_usuario']) . "<br>";
        // }
    } else {
        echo "No se encontraron cargos.";
    }
} catch (PDOException $e) {
    error_log("Error en la consulta: " . $e->getMessage());
    echo "Error al consultar los cargos.";
}
// CONSULTA A TIPO DE USUARIO PARA SELECT    
?>
<!DOCTYPE html>
<html lang="es">  

<head>
    <?php require '../logs/head.php'; ?>
</head>

<body>
    <?php require '../logs/nav-bar.php'; ?>

    <div id="layoutSidenav_content">
        <main>
            <!-- navegacion horizontal -->
            <ul class="nav nav-tabs mt-3">
                <li class="nav-item">
                    <a class="nav-link active" href="usuarios_nuevos.php">Crear Usuario</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " aria-current="page" href="usuarios_lista.php">Listado</a>
                </li>
                </li>
                
                <!-- <li class="nav-item">
                        <a class="nav-link" href="mensualidades_list.php">Listado de mensualidades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="mensualidades_ven.php">Vencimiento</a> 
                    </li>-->
                <!-- <li class="nav-item">
                            <a class="nav-link disabled">Disabled</a>
                        </li> -->
            </ul>
            <!-- navegacion horizontal -->
            <div class="card ">
                <div class="card-body">
                    <div class="container-fluid">
                        <!-- formulario ingresar USUARIO -->
                        <div class="col-md-6">
                            <div class="card border-4 rounded-3">
                                <div class="card-header">
                                    <h5 class="modal-title" id="modalTitleId"><i class="fa fa-user-circle" style='font-size:24px'></i>&nbsp;&nbsp;Ingresar empleado</h5>
                                </div>
                                <!-- Mensajes -->
                                <!-- Mensaje error password -->
                                <?php
                                if (isset($_GET['mensaje']) and $_GET['mensaje'] == 'Usuario registrado correctamente.') {
                                ?>
                                    <div class="alerta alert alert-success alert-dismissible fade show" role="alert">
                                        <strong>OK !</strong> Usuario registrado
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php
                                }
                                ?>
                                <?php
                                if (isset($_GET['mensaje']) and $_GET['mensaje'] == 'Error al registrar usuario') {
                                ?>
                                    <div class="alerta alert alert-danger alert-dismissible fade show" role="alert">
                                        <strong>Error !</strong> No se puede ingresar usuario!
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php
                                }
                                ?>
                                <!-- fin Mensaje error password -->
                                <!-- Mensaje error usuario -->

                                <!-- fin Mensaje error usuario -->
                                <!-- Mensajes -->
                                <form id="usuario" name="usuarios" class="row g-0 p-2" action="usuarios_nuevos.php" method="POST">
                                    <div class="input-group mb-2">
                                        <input type="hidden" class="form-control" id="id" name="id" placeholder="id" aria-label="cedula" aria-describedby="basic-addon1" required autofocus>
                                    </div>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-circle"></i>&nbsp;</span>
                                        </div>
                                        <input type="text" class="form-control" name="nombre" placeholder="Nombre" onkeyup="javascript:this.value=this.value.toUpperCase();" aria-label="nombre" aria-describedby="basic-addon1" required autofocus>
                                    </div>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone"></i>&nbsp;</span>
                                        </div>
                                        <input value="" type="number" class="form-control" name="cedula" placeholder="Cedula" aria-label="tel" aria-describedby="basic-addon1" minlength="5" maxlength="10" required autofocus>
                                    </div>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-telephone"></i>&nbsp;</span>
                                        </div>
                                        <input value="" type="tel" class="form-control" name="telefono" placeholder="Telefono" aria-label="tel" aria-describedby="basic-addon1" minlength="10" maxlength="10" required autofocus>
                                    </div>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-add"></i>&nbsp;</span>
                                        </div>
                                        <input type="text" class="form-control" name="usuario" placeholder="Usuario" aria-label="usuario" aria-describedby="basic-addon1" required autofocus>
                                    </div>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-key"></i>&nbsp;</span>
                                        </div>
                                        <input type="text" class="form-control" name="clave" placeholder="Clave" aria-label="clave" aria-describedby="basic-addon1" minlength="5" required autofocus>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label"></label>
                                        <div class="input-group mb-1">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="inputGroupSelect01"><i class='bi bi-person-vcard'></i>&nbsp;</label>
                                            </div>
                                            <select class="form-select custom-select" id="cargo" name="cargo" required autofocus>
                                                <option hidden selected>Cargo</option>
                                                <?php foreach ($cargos as $cargo): ?>
                                                    <option value="<?= htmlspecialchars($cargo['id_cargo']) ?>"><?= htmlspecialchars($cargo['cargo_nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <input value="../assets/img/logo.png" type="hidden" class="form-control" id="avatar" name="avatar" placeholder="avatar" aria-label="foto" aria-describedby="basic-addon1" required autofocus>

                                    <input value="1" type="hidden" class="form-control" id="activo" name="activo" placeholder="activo" aria-label="activo" aria-describedby="basic-addon1" required autofocus>

                                    <div class="mb-2">
                                        <label class="form-label"></label>
                                        <div class="input-group mb-1">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="inputGroupSelect01"><i class='bi bi-person-gear'></i>&nbsp;</label>
                                            </div>
                                            <select class="form-select custom-select" id="tipo_usuario" name="tipo_usuario" required autofocus>
                                                <option hidden selected>Rol</option>
                                                <?php foreach ($tipo_usuarios as $tipo_usuario): ?>
                                                    <option value="<?= htmlspecialchars($tipo_usuario['id_tipo_usuario']) ?>"><?= htmlspecialchars($tipo_usuario['tipo_usuario']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <input type="hidden" class="form-control" value="1" id="contabilidad" name="contabilidad" placeholder="contabilidad" aria-label="contabilidad" aria-describedby="basic-addon1" required autofocus>
                                    <input type="hidden" class="form-control" value="<?php echo date('Y-m-d') ?>" id="fecha_ingreso" name="fecha_ingreso" placeholder="fecha_ingreso" aria-label="fecha_ingreso" aria-describedby="basic-addon1" required autofocus>
                                    <input type="hidden" class="form-control" value="<?php echo $id ?>" id="user" name="user" placeholder="user" aria-label="user" aria-describedby="basic-addon1" required autofocus>                
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-secondary btn btn-block" name="register" href="usuarios_nuevos.php"><i class="bi bi-plus-lg text-white">&nbsp;GUARDAR</i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- fin formulario ingresar USUARIO -->
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        window.addEventListener('load', () => {
            document.getElementById('usuario').reset();
        });
    </script>
</body>



</html>