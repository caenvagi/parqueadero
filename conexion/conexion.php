<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'parqueadero1';
$username = 'root';
$password = '';

try {
    // Configuración de opciones para mayor seguridad y rendimiento
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,     // Lanza excepciones ante errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devuelve los resultados como arrays asociativos
        PDO::ATTR_EMULATE_PREPARES => false,              // Desactiva la emulación de sentencias preparadas
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4" // Asegura codificación UTF-8 completa
    ];

    // Crear la conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);

    // Puedes habilitar este mensaje para depuración:
    // echo "✅ Conexión exitosa a la base de datos";

} catch (PDOException $e) {

    error_log("Error de conexión: " . $e->getMessage(), 0);

    echo json_encode([
        "success" => false,
        "mensaje" => "Error de conexión a la base de datos"
    ]);

    exit;
}
?>
