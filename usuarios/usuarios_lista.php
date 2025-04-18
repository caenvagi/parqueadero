<?php
session_start();
require_once "../conexion/conexion.php";

// CONSULTA USUARIOS EN TABLA USUARIOS    
    try {
        // Verifica que $pdo esté definido correctamente

        // Preparar la consulta
        $stmt = $pdo->prepare("  SELECT id,nombre,avatar,activo,tipo_cargo,usuario,cargo_nombre
                                        FROM usuarios as US
                                        INNER JOIN tipo_cargo as TC ON TC.id_cargo = US.tipo_cargo
                                        ORDER BY activo DESC");

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
                                            echo "<td data-activo='1'> <h3 style=color:green><i class='bi bi-person-check-fill'></i></h3> </td>";
                                        } else {
                                            echo "<td data-activo='0'> <h3 style=color:grey><i class='bi bi-person-x-fill'></i></h3> </td>";
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
</body>
<script>
            const table = document.getElementById("tabla_empleados");
const rows = table.getElementsByTagName("tr");

for (let z = 1; z < rows.length; z++) {
    const activoCell = rows[z].cells[4];
    const estado = activoCell.getAttribute("data-activo");

    if (estado === "1") {
        rows[z].style.backgroundColor = "";
    } else {
        rows[z].style.backgroundColor = "lightgray";
    }
}
        </script>
</html>