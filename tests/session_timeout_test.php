<?php
// Script de prueba para verificar el cierre automático por inactividad.
// Instrucciones: start a local PHP server in the workspace root and abrir esta URL en navegador:
// php -S localhost:8000 -t .

session_start();

// Mostrar estado actual de la sesión
$now = time();
$last = $_SESSION['last_activity'] ?? null;

header('Content-Type: text/plain; charset=utf-8');

echo "Time now: " . date('H:i:s', $now) . "\n";
if ($last) {
    echo "Last activity: " . date('H:i:s', $last) . "\n";
    echo "Seconds since last activity: " . ($now - $last) . "\n";
} else {
    echo "No last_activity set in session.\n";
}

echo "Session ID: " . session_id() . "\n";

echo "Session contents:\n";
print_r($_SESSION);

// Nota: la lógica de expiración está en base_html.php y nav-bar.php; este script solo muestra estado.

?>