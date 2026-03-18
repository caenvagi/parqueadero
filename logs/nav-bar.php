<?php
//session_start();

require_once "../conexion/conexion.php";

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
}
$id = $_SESSION['id'];
$nombre = $_SESSION['nombre'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario = $_SESSION['usuario'];
$foto = $_SESSION['avatar'];

if ($tipo_usuario == 1) {
    $where = "";
} else if ($tipo_usuario == 2) {
    $where = "WHERE id=$id";
}

// CONSULTA USUARIOS EN TABLA USUARIOS    
    try {
        // Verifica que $pdo esté definido correctamente

        // Preparar la consulta
        $stmt = $pdo->prepare("SELECT        US.nombre,                                                
                                                    US.usuario,
                                                    US.tipo_usuario,
                                                    Tu.tipo_usuario,                                                
                                                    US.telefono,
                                                    US.avatar,
                                                    US.id                                           
                                        FROM        usuarios US
                                        INNER JOIN  tipo_usuarios Tu ON US.tipo_usuario = Tu.id_tipo_usuario
                                        WHERE       id=$id");

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener resultados
        $usuarios = $stmt->fetchAll();

        // Validar si hay datos
        if (!empty($usuarios)) {
            // foreach ($usuarios as $usuario) {
            //     echo "ID: " . htmlspecialchars($usuario['id']) . " - Nombre: " . htmlspecialchars($usuario['nombre']) . "<br>";
            // }
        } else {
            echo "No se encontraron cargos.";
        }
    } catch (PDOException $e) {
        error_log("Error en la consulta: " . $e->getMessage());
        echo "Error al consultar los cargos.";
    }
// CONSULTA USUARIOS EN TABLA USUARIOS 

// CONSULTA USUARIOS EN TABLA USUARIOS 
// try {
//     // Verifica que $pdo esté definido correctamente

//     // Preparar la consulta
//     $stmt = $pdo->prepare("SELECT           US.nombre,
// //                                                 US.usuario,
// //                                                 US.tipo_usuario,
// //                                                 Tu.tipo_usuario,
// //                                                 US.telefono,
// //                                                 US.avatar                                      
// //                                     FROM        usuarios US
// //                                     INNER JOIN  tipo_usuarios Tu ON US.tipo_usuario = Tu.id_tipo_usuario
// //                                     WHERE       id=$id");

//     // Ejecutar la consulta
//     $stmt->execute();

//     // Obtener resultados
//     $usuarios = $stmt->fetchAll();

//     // Validar si hay datos
//     if (!empty($usuarios)) {
//         // foreach ($usuarios as $usuario) {
//         //     echo "ID: " . htmlspecialchars($usuario['id']) . " - Nombre: " . htmlspecialchars($usuario['nombre']) . "<br>";
//         // }
//     } else {
//         echo "No se encontraron cargos.";
//     }
// } catch (PDOException $e) {
//     error_log("Error en la consulta: " . $e->getMessage());
//     echo "Error al consultar los cargos.";
// }
// CONSULTA USUARIOS EN TABLA USUARIOS 


?>
<!DOCTYPE html>
<html lang="es">

<head>

</head>


<body onload="mueveReloj()" class="BG sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">

        <!-- Navbar Brand -->
        <a class="logo2" id="logo-parqueadero" href="../principal/dashboard.php"><img class="logo2" src="../assets/img/logo1.jpg"></a>
        <a class="navbar-brand ps-3" id="titulo-parqueadero" href="../principal/dashboard.php">Parque de la familia</a>
        <!-- Sidebar Toggle -->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" aria-label="menu" href="#!"><i class="bi bi-list-ol"></i></button>

        <b style="font-size:16px" class="text-white" class="text-left" class="ng-binding"></b>

        <!-- FECHA Y HORA-->
            <div class="col-sm-4" name="reloj" id="reloj">        
                <div class="fechas-relojes m-3" name="fechas-relojes" id="fechas-relojes">
                    <div class="tiempo-reloj" name="tiempo-reloj" id="tiempo-reloj">
                        <span class="hora" name="hora" id="hora"></span>
                        <span class="doble-punto" name="doble-punto" id="doble-punto">:</span>
                        <span class="minuto" name="minuto" id="minuto"></span>
                        <span class="doble-punto" name="doble-punto" id="doble-punto">:</span>
                        <span class="segundo" name="segundo" id="segundo"></span>
                        <span class="strampm" name="strampm" id="strampm">am pm</span>
                    </div>
                        
                </div>
            </div>
            <div class="cont-reloj" name="cont-reloj" id="cont-reloj">
                <span class="fecha-reloj" name="fecha-reloj" id="fecha-reloj"></span>
            </div>
        <!--FIN  FECHA Y HORA-->

        <!-- Navbar Search -->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group"></div>
        </form>

        
        
        <!-- Navbar -->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4" id="navbar-nav">
            <li class="nav-item dropdown">
                
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" 
                    data-bs-toggle="dropdown" aria-expanded="false"><?php foreach ($usuarios as $usuario): ?>
                        <?= htmlspecialchars($usuario['nombre']) ?>&nbsp;&nbsp;&nbsp;<img class="avatar2" 
                    src="<?= htmlspecialchars($usuario['avatar']) ?>" />
                    <!-- &nbsp;&nbsp;<i class="fas fa-user fa-fw"></i>--></a>
                
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="../usuarios/usuario_perfil.php?id=<?= htmlspecialchars($usuario['id']) ?>"><i class="fa fa-user-circle"></i>&nbsp;Perfil</a></li>
                    <!--<li><a class="dropdown-item" href="#!">Otros</a></li>-->
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="../logout.php"><i class='fas fa-power-off'></i>&nbspCerrar Sesion</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- BARRA SUPERIOR FIN-->
    <!-- INICIO MENU PRINCIPAL -->
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <!-- titulo del menu <div class="sb-sidenav-menu-heading">Menu</div> -->
                        <a class="nav-link" href="#">
                            
                            <img class="avatar" src="<?= htmlspecialchars($usuario['avatar']) ?>" />
                            <?= htmlspecialchars($usuario['nombre']) ?>
                            <?= htmlspecialchars($usuario['usuario']) ?>
                            
                            <div class="sb-nav-link-icon"><!--<i class="fas fa-tachometer-alt"></i>--></div>
                        </a>
                        <?php endforeach; ?>
                        <div class="sb-sidenav-menu-heading">Menu</div>

                        <!-- MENU PARQUEO INICIO-->
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapse-parqueo" aria-expanded="false" aria-controls="collapseLayouts">
                                <div><i style="font-size:24px" class="bi bi-p-circle-fill"></i></div>
                                &nbsp;&nbsp;&nbsp;Parqueo/Horas
                                <div class="sb-sidenav-collapse-arrow"><i class="bi bi-chevron-left"></i></div>
                            </a>
                            <div class="collapse" id="collapse-parqueo" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="../parqueo_hora/parqueo_form.php">
                                        <div><i class="bi bi-car-front-fill" style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Ingreso
                                    </a>
                                    <a class="nav-link" href="../parqueo_casetas/parqueo_casetas_hora.php">
                                        <div><i class="bi bi-house-door-fill" style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Casetas
                                    </a>
                                    <a class="nav-link" href="../parqueo_hora/parqueo_tarifas.php">
                                        <div><i class="bi bi-ticket-perforated-fill" style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Tarifas
                                    </a>
                                    <!-- <a class="nav-link" href="../config/mensualidades.php">
                                        <div><i class='fas fa-calendar-alt' style='font-size:20px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Mensualidades
                                    </a>
                                    <a class="nav-link" href="../config/parqueomanual.php">
                                        <div><i class='fas fa-edit' style='font-size:20px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Ingreso Manual
                                    </a>
                                    <a class="nav-link" href="../config/ingresos.php">
                                        <div><i class='fas fa-list-ol' style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;en parqueo
                                    </a>
                                    <a class="nav-link" href="../config/listado.php">
                                        <div><i class='fas fa-clipboard-list' style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Listado -->
                                    </a>
                                </nav>
                            </div>
                        <!-- MENU PARQUEO FIN-->

                        <!-- MENU PARQUEO INICIO-->
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapse-mensualidad" aria-expanded="false" aria-controls="collapseLayouts">
                                <div><i style="font-size:24px" class="bi bi-car-front-fill"></i></div>
                                &nbsp;&nbsp;&nbsp;Mensualidades
                                <div class="sb-sidenav-collapse-arrow"><i class="bi bi-chevron-left"></i></div>
                            </a>
                            <div class="collapse" id="collapse-mensualidad" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="../parqueo_mens/mens_cliente_nuevo.php">
                                        <div><i class="bi bi-car-front-fill" style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Crear cliente
                                    </a>
                                    <a class="nav-link" href="../parqueo_mens/mens_pagar.php">
                                        <div><i class="bi bi-car-front-fill" style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Pagar Mes
                                    </a>
                                    <a class="nav-link" href="../parqueo_casetas/parqueo_casetas_mens.php">
                                        <div><i class="bi bi-house-door-fill" style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Casetas
                                    </a>
                                    <!-- <a class="nav-link" href="../parqueo_hora/parqueo_tarifas.php">
                                        <div><i class="bi bi-ticket-perforated-fill" style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Tarifas
                                    </a> -->
                                    <!-- <a class="nav-link" href="../config/mensualidades.php">
                                        <div><i class='fas fa-calendar-alt' style='font-size:20px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Mensualidades
                                    </a>
                                    <a class="nav-link" href="../config/parqueomanual.php">
                                        <div><i class='fas fa-edit' style='font-size:20px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Ingreso Manual
                                    </a>
                                    <a class="nav-link" href="../config/ingresos.php">
                                        <div><i class='fas fa-list-ol' style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;en parqueo
                                    </a>
                                    <a class="nav-link" href="../config/listado.php">
                                        <div><i class='fas fa-clipboard-list' style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Listado -->
                                    </a>
                                </nav>
                            </div>
                        <!-- MENU PARQUEO FIN-->

                        <!-- MENU ALOJAMIENTO INICIO-->
                            <!-- <a class="nav-link collapsed" href="../aloj/aloj_habitaciones.php" data-bs-toggle="collapse" data-bs-target="#collapse-alojamiento" aria-expanded="false" aria-controls="collapseLayouts">
                                <div><i class="fas fa-hotel" style='font-size:24px'></i></div>
                                &nbsp;&nbsp;&nbsp;Alojamiento
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapse-alojamiento" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="#">
                                        <div><i class="fas fa-bed" style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Ingreso
                                    </a>
                                    <a class="nav-link" href="#">
                                        <div><i class='fas fa-edit' style='font-size:20px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Ingreso Manual
                                    </a>
                                    <a class="nav-link" href="#">
                                        <div><i class='fas fa-calendar-alt' style='font-size:20px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Inventario
                                    </a>
                                    <a class="nav-link" href="#">
                                        <div><i class='fas fa-list-ol' style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Reservas
                                    </a>
                                    <a class="nav-link" href="#">
                                        <div><i class='fas fa-calendar-check' style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Habitaciones
                                    </a>
                                </nav>
                            </div> -->
                        <!-- MENU ALOJAMIENTO FIN-->

                        <!-- MENU CAJA Caja-->
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapse-caja" aria-expanded="false" aria-controls="collapseLayouts">
                                <div><i style="font-size:24px" class="bi bi-pc-display-horizontal"></i></div>
                                &nbsp;&nbsp;&nbsp;Caja
                                <div class="sb-sidenav-collapse-arrow"><i class="bi bi-chevron-left"></i></div>
                            </a>
                            <div class="collapse" id="collapse-caja" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="../caja/caja_listado.php">
                                        <div><i class="bi bi-calculator" style="font-size:24px"></i></div>
                                        &nbsp;&nbsp;&nbsp;Listado
                                    </a>
                                    <a class="nav-link" href="../caja/caja_registro.php">
                                        <div><i class="bi bi-printer-fill" style="font-size:24px"></i></div>
                                        &nbsp;&nbsp;&nbsp;Registros
                                    </a>
                                    <!-- <a class="nav-link" href="../config/caja_conceptos.php">
                                        <div><i class="bi bi-card-checklist" style="font-size:24px"></i></div>
                                        &nbsp;&nbsp;&nbsp;Conceptos
                                    </a> -->
                                </nav>
                            </div> 
                        <!-- MENU CONFIGURACION Caja-->

                        <!-- MENU CONFIGURACION -->
                            <!-- <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div><i class="bi bi-gear-fill" style='font-size:24px'></i></div>
                                &nbsp;&nbsp;&nbsp;Configuracion:
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>

                            <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">

                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="../config/categorias.php">
                                        <div><i class="bi bi-bookmarks-fill" style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Categorias
                                    </a>
                                </nav>
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="../config/tarifas.php">
                                        <div><i class="bi bi-cash-coin" style='font-size:24px'></i></div>
                                        &nbsp;&nbsp;&nbsp;Tarifas
                                    </a>
                                </nav>
                            </div> -->
                        <!-- MENU FIN-->

                        <!-- MENU ALOJAMIENTO-->
                            <?php if ($tipo_usuario == 1) { ?>
                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapse-alojamiento" aria-expanded="false" aria-controls="collapseLayouts">
                                    <div><i style="font-size:24px" class="bi bi-house-door-fill"></i></div>
                                    &nbsp;&nbsp;&nbsp;Alojamiento
                                    <div class="sb-sidenav-collapse-arrow"><i class="bi bi-chevron-left"></i></div>
                                </a>
                                <div class="collapse" id="collapse-alojamiento" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                    <nav class="sb-sidenav-menu-nested nav">

                                        <a class="nav-link" href="../aloj/aloj_clientes.php">
                                            <div><i class="fas fa-user-cog" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Huespedes
                                        </a>

                                        
                                        
                                        <a class="nav-link" href="../aloj/aloj_reservas_listado.php">
                                            <div><i class="fas fa-user-cog" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Reservas
                                        </a>
                                        <a class="nav-link" href="../aloj/aloj_calendario.php">
                                            <div><i class="fas fa-user-cog" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Calendario
                                        </a>

                                        <a class="nav-link" href="../aloj/aloj_habitaciones.php">
                                            <div><i class="fa fa-user-circle" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Habitaciones
                                        </a>
                                        <!-- <a class="nav-link" href="../turnos/usuarios_turnos_nomina.php">
                                            <div><i class="fas fa-user-cog" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Nomina
                                        </a>
                                        <a class="nav-link" href="../usuarios/tipo_cargo.php">
                                            <div><i class="fas fa-user-tie" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Cargos
                                        </a>
                                        <a class="nav-link" href="../usuarios/tipo_usuarios.php">
                                            <div><i class="fas fa-user-cog" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Roles user
                                        </a> -->
                                    </nav>
                                </div>
                            <?php  } ?>
                        <!-- MENU USUARIOS FIN-->
                        
                        <!-- MENU USUARIOS-->
                            <?php if ($tipo_usuario == 1) { ?>
                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapse-configuracion" aria-expanded="false" aria-controls="collapseLayouts">
                                    <div><i style="font-size:24px" class="bi bi-people-fill"></i></div>
                                    &nbsp;&nbsp;&nbsp;Empleados
                                    <div class="sb-sidenav-collapse-arrow"><i class="bi bi-chevron-left"></i></div>
                                </a>
                                <div class="collapse" id="collapse-configuracion" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="../usuarios/usuarios_lista.php">
                                            <div><i class="fa fa-user-circle" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Empleados
                                        </a>
                                        <a class="nav-link" href="../turnos/usuarios_turnos_calendario.php">
                                            <div><i class="fas fa-user-cog" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Turnos
                                        </a>
                                        <a class="nav-link" href="../turnos/usuarios_turnos_nomina.php">
                                            <div><i class="fas fa-user-cog" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Nomina
                                        </a>
                                        <a class="nav-link" href="../usuarios/tipo_cargo.php">
                                            <div><i class="fas fa-user-tie" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Cargos
                                        </a>
                                        <a class="nav-link" href="../usuarios/tipo_usuarios.php">
                                            <div><i class="fas fa-user-cog" style='font-size:24px'></i></div>
                                            &nbsp;&nbsp;&nbsp;Roles user
                                        </a>
                                    </nav>
                                </div>
                            <?php  } ?>
                        <!-- MENU USUARIOS FIN-->

                        <!-- OTRO MENU INICIO-->
                            <!--<a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                                            <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                            Pages
                                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                        </a>
                                        <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                                            <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseAuth" aria-expanded="false" aria-controls="pagesCollapseAuth">
                                                    Authentication
                                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                                </a>
                                                <div class="collapse" id="pagesCollapseAuth" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                                    <nav class="sb-sidenav-menu-nested nav">
                                                        <a class="nav-link" href="login.html">Login</a>
                                                        <a class="nav-link" href="register.html">Register</a>
                                                        <a class="nav-link" href="password.html">Forgot Password</a>
                                                    </nav>
                                                </div>
                                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                                                    Error
                                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                                </a>
                                                <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                                    <nav class="sb-sidenav-menu-nested nav">
                                                        <a class="nav-link" href="401.html">401 Page</a>
                                                        <a class="nav-link" href="404.html">404 Page</a>
                                                        <a class="nav-link" href="500.html">500 Page</a>
                                                    </nav>
                                                </div>
                                            </nav>
                                        </div>
                                        
                                        <div class="sb-sidenav-menu-heading">Addons</div>
                                        <a class="nav-link" href="charts.html">
                                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                            Charts
                                        </a>
                                        <a class="nav-link" href="tables.html">
                                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                                            Tables
                                        </a>-->
                            <!-- titulo del menu <div class="sb-sidenav-menu-heading">Menu</div> -->
                        <!-- OTRO MENU FIN  -->
                    </div>
                </div>
                <div class="sb-sidenav-footer text-center">
                    <img class="logosiste" src="../assets/img/LOGO-09.png" />
                    <div class="small"></div>
                </div>
            </nav>
        </div>
        <!-- FIN MENU PRINCIPAL -->

</body>

</html>