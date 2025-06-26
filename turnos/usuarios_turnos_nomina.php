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
    // $stmt = $pdo->prepare("  SELECT id,nombre,avatar,activo,tipo_cargo,usuario,cargo_nombre,UT.pagado
    //                                     FROM usuarios as US
    //                                     INNER JOIN tipo_cargo as TC ON TC.id_cargo = US.tipo_cargo
    //                                     INNER JOIN usuarios_turnos as UT ON UT.usuario_id = US.id
    //                                     WHERE activo = 1
    //                                     ORDER BY activo DESC");

    $stmt = $pdo->prepare("SELECT    US.id, 
                                            US.nombre, 
                                            US.avatar, 
                                            US.activo, 
                                            US.tipo_cargo, 
                                            US.usuario, 
                                            TC.cargo_nombre,
                            (SELECT COUNT(*) FROM usuarios_turnos T WHERE T.usuario_id = US.id AND T.pagado = 0) AS pagado
                            FROM usuarios AS US
                            INNER JOIN tipo_cargo AS TC ON TC.id_cargo = US.tipo_cargo
                            WHERE US.activo = 1
                            ORDER BY US.activo DESC");                                    

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
</head>

<body>
    <?php require '../logs/nav-bar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
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

                <div class="card m-2" id="span">
                    <div class="card-header">
                        Lista de empleados por pagar:
                    </div>
                    <div class="card-body">

                        <table id="tabla_empleados" class="table table table-sm table-borderless table-hover mt-3 table text-center table align-middle">

                            <thead>
                                <tr>
                                    <th class="text-center">NOMBRE</th>
                                    <th class="text-center">AVATAR</th>
                                    <th class="text-center">ESTADO DE PAGO</th>
                                    <th class="text-center">ACTIVO</th>
                                    <th class="text-center">PAGAR</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios_activos as $usuario): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($usuario['nombre']) ?></td>

                                        <td><?php
                                            if (isset($usuario['activo']) && $usuario['activo'] == "1") {
                                                echo "<img class='avatar4' src='{$usuario['avatar']}'>";
                                            } else {
                                                echo "<img class='avatar5' src='{$usuario['avatar']}'>";
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($usuario['pagado'] > 0): ?>
                                                <span class="badge bg-danger">Por pagar (<?= $usuario['pagado'] ?>)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">No hay turnos por pagar</span>
                                            <?php endif; ?>
                                        </td>

                                        <?php
                                        if (isset($usuario['activo']) && $usuario['activo'] == "1") {
                                            echo "<td data-activo='1'> <h3 style=color:green><i class='bi bi-person-check-fill'></i></h3> </td>";
                                        } else {
                                            echo "<td data-activo='0'> <h3 style=color:grey><i class='bi bi-person-x-fill'></i></h3> </td>";
                                        }
                                        ?>
                                        <td align="center"><a href="usuarios_turnos_recibos.php?id=<?= htmlspecialchars($usuario['id']) ?>" title="Editar" class="btn btn-outline-success btn-xs">
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