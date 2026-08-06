<?php

session_start();

session_destroy();

// Redirige a index.php con mensaje de timeout si viene del timeout automático
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    header("Location: /parqueadero/index.php?mensaje=timeout");
} else {
    // Logout manual
    header("Location: /parqueadero/index.php");
}

exit();

?>