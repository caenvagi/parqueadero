<?php
require "../conexion/conexion.php";
    // REGISTRAR USUARIO NUEVO
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = trim($_POST['id']);
            $nombre = trim($_POST['nombre']);
            $cargo = trim($_POST['cargo']);
            $telefono = trim($_POST['telefono']);
            $usuario = trim($_POST['usuario']);
            $clave = trim($_POST['clave']);
            $tipo = trim($_POST['tipo_usuario']);
            $foto = trim($_POST['avatar']);
            $activo = trim($_POST['activo']);
            $contabilidad = trim($_POST['contabilidad']);

            // Hashear la clave con password_hash()
            $claveHash = password_hash($clave, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios 
                    (id, nombre, tipo_cargo, telefono, usuario, clave, tipo_usuario, avatar, activo, contabilidad)
                    VALUES 
                    (:id, :nombre, :cargo, :telefono, :usuario, :clave, :tipo, :foto, :activo, :contabilidad)";

            $stmt = $pdo->prepare($sql);

            try {
                $stmt->execute([
                    'id' => $id,
                    'nombre' => $nombre,
                    'cargo' => $cargo,
                    'telefono' => $telefono,
                    'usuario' => $usuario,
                    'clave' => $claveHash,
                    'tipo' => $tipo,
                    'foto' => $foto,
                    'activo' => $activo,
                    'contabilidad' => $contabilidad
                ]);
                echo "Usuario registrado correctamente.";
                // header("Location: usuarios.php"); // si deseas redirigir
            } catch (PDOException $e) {
                echo "Error al registrar usuario: " . $e->getMessage();
            }
        }
    // REGISTRAR USUARIO NUEVO
    // CONSULTA A TIPO DE CARGO PARA SELECT    
        try {
            // Verifica que $pdo esté definido correctamente

            // Preparar la consulta
            $stmt = $pdo->prepare("SELECT id_cargo, cargo_nombre FROM tipo_cargo");

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
    <?php
    require '../logs/head.php';
    ?>
</head>

<body>
    <!-- modal ingreso empleados -->
    <!-- Modal Body-->

    <div class="content">
        <div class="header">
            <h5 class="modal-title" id="modalTitleId"><i class="fa fa-user-circle" style='font-size:24px'></i>&nbsp;&nbsp;Ingresar empleado</h5>
        </div>
        <div class="body">
            <div class="container-fluid">
                <!-- formulario ingresar USUARIO -->
                <div class="col-md-12">
                    <div class="card border-4 rounded-3">
                        <form id="usuario" name="usuario" class="row g-0 p-2" action="usuarios_nuevos.php" method="POST">
                            <div class="input-group mb-2">
                                <input type="hidden" class="form-control" id="id" name="id" placeholder="id" aria-label="cedula" aria-describedby="basic-addon1" required autofocus>
                            </div>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-user-alt"></i>&nbsp;</span>
                                </div>
                                <input type="text" class="form-control" name="nombre" placeholder="Nombre" aria-label="nombre" aria-describedby="basic-addon1" required autofocus>
                            </div>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-phone"></i>&nbsp;</span>
                                </div>
                                <input value="" type="tel" class="form-control" name="telefono" placeholder="Telefono" aria-label="tel" aria-describedby="basic-addon1" minlength="10" maxlength="10" required autofocus>
                            </div>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-user-circle"></i>&nbsp;</span>
                                </div>
                                <input type="text" class="form-control" name="usuario" placeholder="Usuario" aria-label="usuario" aria-describedby="basic-addon1" required autofocus>
                            </div>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-key"></i>&nbsp;</span>
                                </div>
                                <input type="password" class="form-control" name="clave" placeholder="Clave" aria-label="clave" aria-describedby="basic-addon1" minlength="5" required autofocus>
                            </div>
                            <div class="mb-1">
                                <label class="form-label"></label>
                                <div class="input-group mb-1">
                                    <div class="input-group-prepend">
                                        <label class="input-group-text" for="inputGroupSelect01"><i class='fas fa-user-tie'></i>&nbsp;</label>
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

                            <input value="SI" type="hidden" class="form-control" id="activo" name="activo" placeholder="activo" aria-label="activo" aria-describedby="basic-addon1" required autofocus>

                            <div class="mb-1">
                                <label class="form-label"></label>
                                <div class="input-group mb-1">
                                    <div class="input-group-prepend">
                                        <label class="input-group-text" for="inputGroupSelect01"><i class='fas fa-chalkboard-teacher'></i>&nbsp;</label>
                                    </div>
                                    <select class="form-select custom-select" id="tipo_usuario" name="tipo_usuario" required autofocus>
                                        <option hidden selected>Rol</option>
                                        <?php foreach ($tipo_usuarios as $tipo_usuario): ?>
                                            <option value="<?= htmlspecialchars($tipo_usuario['id_tipo_usuario']) ?>"><?= htmlspecialchars($tipo_usuario['tipo_usuario']) ?></option>
                                            <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group mb-2">
                                <input type="hidden" class="form-control" value="SI" id="contabilidad" name="contabilidad" placeholder="contabilidad" aria-label="contabilidad" aria-describedby="basic-addon1" required autofocus>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-secondary btn btn-block" name="register" href="usuarios_nuevos.php"><i class="bi bi-plus-lg text-white">&nbsp;GUARDAR</i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- fin formulario ingresar guias -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
    </div>

    <!-- Modal Body-->
    <!-- modal ingreso empleados -->
</body>

</html>