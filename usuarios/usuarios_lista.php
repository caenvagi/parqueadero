<?php
session_start();
require_once "../conexion/conexion.php";

// CONSULTA USUARIOS EN TABLA USUARIOS    
    try {
        // Verifica que $pdo esté definido correctamente

        // Preparar la consulta
        $stmt = $pdo->prepare("  SELECT id,nombre,avatar,activo,tipo_cargo,usuario,cargo_nombre
                                        FROM usuarios as US
                                        INNER JOIN tipo_cargo as TC ON TC.id_cargo = US.tipo_cargo");

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener resultados
        $usuarios_activos = $stmt->fetchAll();

        // Validar si hay datos
        if (!empty($usuarios_activos)) {
            // foreach ($usuarios_activos as $usuario) {
            //     echo "ID: " . htmlspecialchars($usuario['id']) . " - Nombre: " . htmlspecialchars($usuario['nombre']) . "<br>";
            // }
        } else {
            echo "No se encontraron usuarios.";
        }
    } catch (PDOException $e) {
        error_log("Error en la consulta: " . $e->getMessage());
        echo "Error al consultar los usuarios.";
    }


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
                        <a class="nav-link" href="usuarios_nuevos.php">Crear Usuario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="usuarios_lista.php">Listado</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mensualidades_list.php">Edicion</a>
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
            <!-- tabla empleados -->
                <div class="justify-content-between m-0 col col-10 col-sm-10 col-md-12">
                    <div class="card m-2">
                    <div class="card-header">
                        Empleados registrados:
                    </div>
                    <div class="card-body">
                    <table id="tabla_empleados" class="table table table-sm table-borderless table-hover mt-3 table text-center table align-middle">
                        <thead>
                            <tr>
                                <th align="center">NOMBRE</th>
                                <th align="center">AVATAR</th>
                                <th align="center">CARGO</th>
                                <th align="center">USER</th>
                                <th align="center">ACTIVO</th>
                                <th align="center">EDITAR</th>

                            </tr>
                        </thead>
                        <tbody>
                            
                                <style>
                                    .avatar4 {
                                        width: 3em;
                                        border-radius: 20px;
                                        filter: grayscale(0);
                                    }

                                    .avatar5 {
                                        width: 3em;
                                        border-radius: 20px;
                                        filter: grayscale(1);
                                    }
                                </style>

                                <?php foreach ($usuarios_activos as $usuario): ?>
                                
                                <tr>
                                    <td align="center"><a href=""><?= htmlspecialchars($usuario['nombre']) ?></a></td>
                                                                            
                                    <td><?php
                                        if (isset($usuario['activo']) && $usuario['activo'] == "1") {
                                            echo "<img class='avatar4' src='{$usuario['avatar']}'>";
                                        } else {
                                            echo "<img class='avatar5' src='{$usuario['avatar']}'>";
                                        }
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['cargo_nombre']) ?></td>
                                    <td><?= htmlspecialchars($usuario['usuario']) ?></td>
                                    
                                    <?php
                                        if (isset($usuario['activo']) && $usuario['activo'] == "1") {
                                            echo "<td> SI </td>";
                                        } else {
                                            echo "<td> NO </td>";
                                        }
                                        ?>
                                    <td align="center"><a href="usuarios_editar.php?id=<?= htmlspecialchars($usuario['id']) ?>" title="Editar" class="btn btn-outline-success btn-xs">
                                    <i class="bi bi-pencil-square"></i></a></td>                               
                                </tr>
                                <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                    </div>
                    </div>
                </div>
            <!-- fin tabla empleados -->
        </main>
    </div>                                    
    <!-- <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
    </style>
    <table>
        <tr>
            <th>ID</th>
            <th>NOMBRE</th>
            <th>CARGO</th>
            <th>TELEFONO</th>
            <th>USUARIO</th>
            <th>CLAVE</th>
            <th>TIPO</th>
            <th>AVATAR</th>
            <th>ACTIVO</th>
            <th>CONTABILIDAD</th>
            <th></th>
        </tr>

        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= htmlspecialchars($usuario['id']) ?></td>
                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                <td><?= htmlspecialchars($usuario['tipo_cargo']) ?></td>
                <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                <td><?= htmlspecialchars($usuario['usuario']) ?></td>
                <td><?= htmlspecialchars($usuario['clave']) ?></td>
                <td><?= htmlspecialchars($usuario['tipo_usuario']) ?></td>
                <td><?= htmlspecialchars($usuario['avatar']) ?></td>
                <td><?= htmlspecialchars($usuario['activo']) ?></td>
                <td><?= htmlspecialchars($usuario['contabilidad']) ?></td>
            </tr>
        <?php endforeach; ?>

    </table> -->



</body>

</html>