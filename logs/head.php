<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="Carlos Valencia" content="" />

    <html lang="es">
    <link rel="icon" href="../assets/img/logo1.jpg" type="image/jpg">	
    <title>Parqueadero 1.2</title>

    <link rel='stylesheet' type='text/css' href='../css/styles.css'>
    <link rel='stylesheet' type='text/css' href='../css/estilos.css'>

    <script src="../modulos/jquery-3.7.1/jquery-3.7.1.js"></script>	

    <?php date_default_timezone_set('America/New_York'); ?>
    
    <!-- include the style -->
    <link rel="stylesheet" href="../modulos/alertifyjs/css/alertify.min.css" />
    <!-- include a theme -->
    <link rel="stylesheet" href="../modulos/alertifyjs/css/themes/default.min.css" />

    

    <!-- include the script -->
    <script src="../modulos/alertifyjs/alertify.min.js"></script>
    <script src="../js/mensajes.js"></script>
    <script src="../js/scripts.js"></script> 
    <script src="../modulos/popper/popper.min.js"></script>  	 
	
    
    <!-- Bootstrap CSS-->
    <script src="../modulos/bootstrap-5.3.5-dist/js/bootstrap.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../modulos/bootstrap-5.3.5-dist/css/bootstrap.css" crossorigin="anonymous">
    <link rel="stylesheet" href="../modulos/icons-1.11.3/font/bootstrap-icons.css">

    

    
    
    <!-- Inactivity auto-logout script (aplicado globalmente) -->
    <script>
        (function() {
            // Tiempo de inactividad en milisegundos (3 minutos - pruebas)
            // Para producción cambiar a 20 * 60 * 1000 (20 minutos)
            var maxInactive = 3 * 60 * 1000; // 3 minutos

            var timer;
            var isLoggingOut = false;
            var lastActivityTime = new Date().getTime();
            
            function logout() {
                if (isLoggingOut) return; // Evitar múltiples redirecciones
                isLoggingOut = true;
                console.log('Timeout de sesión: Redirigiendo a logout...');
                // Redirige al logout del proyecto (ruta absoluta)
                var logoutUrl = window.location.protocol + '//' + window.location.hostname;
                if (window.location.port) {
                    logoutUrl += ':' + window.location.port;
                }
                logoutUrl += '/parqueadero/logout.php?timeout=1';
                window.location.href = logoutUrl;
            }

            function resetTimer() {
                lastActivityTime = new Date().getTime();
                clearTimeout(timer);
                timer = setTimeout(logout, maxInactive);
            }

            // Verificación periódica del timeout (cada 10 segundos)
            function checkInactivity() {
                var currentTime = new Date().getTime();
                var timeSinceActivity = currentTime - lastActivityTime;
                console.log('Tiempo de inactividad: ' + Math.floor(timeSinceActivity / 1000) + ' segundos');
                if (timeSinceActivity > maxInactive) {
                    logout();
                }
            }
            
            setInterval(checkInactivity, 10000); // Verificar cada 10 segundos

            // Registrar eventos que indiquen actividad
            window.addEventListener('load', resetTimer);
            document.addEventListener('mousemove', resetTimer);
            document.addEventListener('mousedown', resetTimer);
            document.addEventListener('click', resetTimer);
            document.addEventListener('scroll', resetTimer);
            document.addEventListener('keypress', resetTimer);
            document.addEventListener('touchstart', resetTimer);

            // Opcional: cuando se cierra la ventana, limpiar timer
            window.addEventListener('beforeunload', function() { clearTimeout(timer); });
        })();
    </script>
    





