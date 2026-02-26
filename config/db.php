<?php
// Buscamos el archivo .env en la raíz
$path = __DIR__ . '/../.env';

if (!file_exists($path)) {
    die("Error: Archivo de configuración .env no encontrado.");
}

// Parseamos el archivo para obtener las variables
$config = parse_ini_file($path);

$host = $config['DB_HOST'];
$user = $config['DB_USER'];
$password = $config['DB_PASS'];
$dbname = $config['DB_NAME'];

// Conexión segura
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Fallo de conexión");
}

// Configuración para tildes y ñ (Vital para el bot) [3, 4]
$conn->set_charset("utf8mb4");
?>