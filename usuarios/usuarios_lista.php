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
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
    <link href="../modulos/sweetalert/sweetalert2.min.css" rel="stylesheet">
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
                <!-- Mensajes -->
                                <!-- Mensaje error password -->
                                <?php
                                if (isset($_GET['mensaje']) and $_GET['mensaje'] == 'Usuario registrado correctamente.') {
                                ?>
                                    <div class="alerta alert alert-success alert-dismissible fade show" role="alert">
                                        <strong>OK !</strong> Empleado registrado
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
                <div class="card m-2"id="span">
                    <div class="card-header">
                        <a href="usuarios_nuevos.php" title="Crear Usuario" id="agregar_users" class="btn btn-outline-success btn-xs">
                        <i class="bi bi-plus-square-fill"></i>&nbsp;&nbsp;Crear Usuario</a>
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
                                        <td align="center"><?= htmlspecialchars($usuario['nombre']) ?></td>

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
    <!-- sweet alert -->
        <script src="../modulos/sweetalert/sweetalert2.all.min.js"></script>
    <!-- aviso de usuario actulizado con exito -->
        <?php if (isset($_SESSION['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?= $_SESSION['success'] ?>',
                confirmButtonColor: '#3085d6'
            });
        </script>
        <?php unset($_SESSION['success']);
        endif; ?>
    <!-- aviso de usuario actulizado con exito -->
</body>
    <!-- datatable lista usuarios -->
        <script>
            $(document).ready(function() {
                $('#tabla_empleados').DataTable({
                    responsive: true,
                    dom: 'Bfrtilp',
                    language: {
                        "decimal": "",
                        "emptyTable": "No hay información",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
                        "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                        "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                        "infoPostFix": "",
                        "thousands": ",",
                        "lengthMenu": "Mostrar _MENU_ Entradas",
                        "loadingRecords": "Cargando...",
                        "processing": "Procesando...",
                        "search": "Buscar:",
                        "zeroRecords": "Sin resultados encontrados",
                        "paginate": {
                            "first": "Primero",
                            "last": "Ultimo",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        },
                    },
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="bi bi-file-earmark-x"></i> ',
                            titleAttr: 'Exportar a Excel',
                            className: 'btn btn-success'
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="bi bi-file-earmark-pdf"></i> ',
                            titleAttr: 'Exportar a PDF',
                            className: 'btn btn-danger'
                        },
                        {
                            extend: 'print',
                            text: '<i class="bi bi-printer"></i> ',
                            titleAttr: 'Imprimir',
                            className: 'btn btn-info'
                        },
                    ],
                    "order": [
                        [4, "asc"]
                    ],
                    'pageLength': 25,

                    createdRow: function(row, data, dataIndex) {
                        const estado = $('td:eq(4)', row).attr('data-activo');
                        if (estado === "0") {
                            $(row).addClass('fila-inactiva');
                        }
                    }

                });

            });
        </script>
    <!-- datatable lista usuarios -->





</html>