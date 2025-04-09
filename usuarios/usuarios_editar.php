<?php
session_start();
// Incluir archivo de conexión
require_once "../conexion/conexion.php";

// Validar que se reciben todos los datos necesarios
if (
    isset($_POST['id']) &&
    isset($_POST['nombre']) &&
    isset($_POST['tipo_cargo']) &&
    isset($_POST['telefono']) &&
    isset($_POST['usuario']) &&
    isset($_POST['clave']) &&
    isset($_POST['tipo_usuario']) &&
    isset($_POST['avatar']) &&
    isset($_POST['activo']) &&
    isset($_POST['contabilidad'])
) {
    // Obtener datos del formulario
    $id             = $_POST['id'];
    $nombre         = $_POST['nombre'];
    $tipo_cargo     = $_POST['tipo_cargo'];
    $telefono       = $_POST['telefono'];
    $usuario        = $_POST['usuario'];
    $clave          = password_hash($_POST['clave'], PASSWORD_DEFAULT); // Encriptar la clave
    $tipo_usuario   = $_POST['tipo_usuario'];
    $avatar         = $_POST['avatar'];
    $activo         = $_POST['activo'];
    $contabilidad   = $_POST['contabilidad'];

    try {
        // Preparar la consulta SQL con parámetros
        $sql = "UPDATE usuarios SET 
                    nombre = :nombre,
                    tipo_cargo = :tipo_cargo,
                    telefono = :telefono,
                    usuario = :usuario,
                    clave = :clave,
                    tipo_usuario = :tipo_usuario,
                    avatar = :avatar,
                    activo = :activo,
                    contabilidad = :contabilidad
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        // Ejecutar con los parámetros vinculados
        $stmt->execute([
            ':nombre'         => $nombre,
            ':tipo_cargo'     => $tipo_cargo,
            ':telefono'       => $telefono,
            ':usuario'        => $usuario,
            ':clave'          => $clave,
            ':tipo_usuario'   => $tipo_usuario,
            ':avatar'         => $avatar,
            ':activo'         => $activo,
            ':contabilidad'   => $contabilidad,
            ':id'             => $id
        ]);

        echo "Usuario actualizado correctamente.";
    } catch (PDOException $e) {
        error_log("Error al actualizar usuario: " . $e->getMessage());
        echo "Hubo un error al actualizar el usuario.";
    }
} else {
    echo "Faltan datos para actualizar el usuario.";
}
$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID de usuario no especificado.");
}

// Obtener los datos del usuario para prellenar el formulario
try {
    $stmt = $pdo->prepare("  SELECT * 
                                    FROM usuarios as US
                                    INNER JOIN tipo_cargo as TC ON TC.id_cargo = US.tipo_cargo 
                                    INNER JOIN tipo_usuarios as TU ON TU.id_tipo_usuario = US.tipo_usuario
                                    WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $usuario_lista = $stmt->fetch();

    if (!$usuario_lista) {
        die("Usuario no encontrado.");
    }
} catch (PDOException $e) {
    error_log("Error al obtener usuario: " . $e->getMessage());
    die("Error al cargar datos del usuario.");
}

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
            <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios_nuevos.php">Crear Usuario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " aria-current="page" href="usuarios_lista.php">Listado</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="mensualidades_list.php">Edicion</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="mensualidades_ven.php">Vencimiento</a> 
                    </li>-->
                    <!-- <li class="nav-item">
                            <a class="nav-link disabled">Disabled</a>
                        </li> -->
                </ul>
            <!-- navegacion horizontal -->

            <form id="usuario" name="usuario" class="row g-0 p-2" action="usuarios_editar_proceso.php" method="POST">
                <div class="card m-0" id="">
                    <div class="">
                        <div class="">
                            <div class="card-header">
                                <h5 class="" id=""><i class="fa fa-user-circle" style='font-size:24px'></i>&nbsp;Modificar Empleado <br></h5>
                            </div>
                            <div class="card-body">
                                <div class="">
                                    <h7 class=""><br>
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($usuario_lista['id']) ?>">
                                        <input type="hidden" name="avatar" value="<?= htmlspecialchars($usuario_lista['avatar']) ?>">
                                        <input type="hidden" name="contabilidad" value="<?= htmlspecialchars($usuario_lista['contabilidad']) ?>">
                                        <div class="input-group mb-1 mt-2">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text" for="inputGroupSelect01"><i class='fas fa-user-tie'></i>&nbsp;Nombre</label>
                                            </div>
                                            <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($usuario_lista['nombre']) ?>" placeholder="Nombre" onkeyup="javascript:this.value=this.value.toUpperCase();" aria-label="nombre" aria-describedby="basic-addon1" required autofocus>
                                        </div>
                                </div>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-phone"></i>&nbsp;Celular&nbsp;&nbsp;</span>
                                    </div>
                                    <input type="tel" class="form-control" name="telefono" id="telefono" placeholder="Telefono" value="<?= htmlspecialchars($usuario_lista['telefono']) ?>" aria-label="tel" aria-describedby="basic-addon1" minlength="10" maxlength="10" required autofocus>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label"></label>
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text" for="inputGroupSelect01"><i class='fas fa-user-tie'></i>&nbsp;Cargo</label>
                                        </div>
                                        <select class="form-select custom-select" id="cargo" name="cargo" required autofocus>
                                            <option value="<?= htmlspecialchars($usuario_lista['id_cargo']) ?>"><?= htmlspecialchars($usuario_lista['cargo_nombre']) ?></option>
                                            <?php foreach ($cargos as $cargo): ?>
                                                <option value="<?= htmlspecialchars($cargo['id_cargo']) ?>"><?= htmlspecialchars($cargo['cargo_nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-phone"></i>&nbsp;Usuario&nbsp;&nbsp;</span>
                                        </div>
                                        <input type="text" class="form-control" name="user" id="user" placeholder="Usuario" value="<?= htmlspecialchars($usuario_lista['usuario']) ?>" aria-label="tel" aria-describedby="basic-addon1" minlength="10" maxlength="10" required autofocus>
                                    </div>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fa fa-phone"></i>&nbsp;Clave&nbsp;&nbsp;</span>
                                        </div>
                                        <input type="password" class="form-control" name="clave" id="clave" placeholder="clave" value="<?= htmlspecialchars($usuario_lista['clave']) ?>" aria-label="tel" aria-describedby="basic-addon1" minlength="5" maxlength="10" required autofocus>
                                        <button class="btn btn-secondary" onclick="mostrarPassword1()" type="button" id="button-addon1"><span class="fa fa-eye-slash icon"></span></button>        
                                    </div>
                                    <div class="mb-2">
                                    <label class="form-label"></label>
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text" for="inputGroupSelect01"><i class='fas fa-user-tie'></i>&nbsp;usuario</label>
                                        </div>
                                        <select class="form-select custom-select" id="tipo_usuario" name="tipo_usuario" required autofocus>
                                            <option value="<?= htmlspecialchars($usuario_lista['id_tipo_usuario']) ?>"><?= htmlspecialchars($usuario_lista['tipo_usuario']) ?></option>
                                            <?php foreach ($tipo_usuarios as $tipo_usuario): ?>
                                                <option value="<?= htmlspecialchars($tipo_usuario['id_tipo_usuario']) ?>"><?= htmlspecialchars($tipo_usuario['tipo_usuario']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                    <center><label class="form-label">Empleado Activo </label></center>
                                    <div class="justify-content-center input-group mb-0 text-center">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="inlineCheckbox1" name="activo" value="1" <?php if($usuario_lista['activo']== "1")  print "checked" ?>>
                                            <label class="form-check-label" for="inlineCheckbox1">SI</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="inlineCheckbox2" name="activo" value="0" <?php if($usuario_lista['activo']== "0")  print "checked" ?>>
                                            <label class="form-check-label" for="inlineCheckbox2">NO</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="footer">
                                <button type="submit" value="editar" class="btn btn-primary btn btn-block">Guardar cambios</button>
                            </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</body>
<!-- mostrar contaseña javascript -->        
<script type="text/javascript">
                function mostrarPassword1(){
                    var cambio = document.getElementById("clave");
                        if(cambio.type == "password"){
                            cambio.type = "text";
                            $('.icon').removeClass('fa fa-eye-slash').addClass('fa fa-eye');
                        }else{
                            cambio.type = "password";
                            $('.icon').removeClass('fa fa-eye').addClass('fa fa-eye-slash');
                        }
                    }
                </script>
</html>