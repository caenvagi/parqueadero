<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'parqueadero1';
$username = 'root';
$password = '';

try {
    // Opciones para mejorar la seguridad
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Manejo de errores con excepciones
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Resultados como arrays asociativos
        PDO::ATTR_EMULATE_PREPARES => false, // Desactiva la emulación de declaraciones preparadas
    ];

    // Conectar a la base de datos con PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);

    //echo "Conexión exitosa";

} catch (PDOException $e) {
    // Manejo seguro de errores
    error_log("Error de conexión: " . $e->getMessage()); // Guardar en logs
    die("Error de conexión. Intente más tarde.");
}
?>
