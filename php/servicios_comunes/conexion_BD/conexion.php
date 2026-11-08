<?php

date_default_timezone_set('America/Montevideo');

// getenv() lee la variable de entorno. Si no existe, usa el valor después del '?:'
$host = getenv('DB_HOST') ?: 'localhost';
$usuario = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'sigsm';

try {

    $conexion = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $usuario, $password);

    // Configurar PDO para que lance excepciones cuando ocurra un error
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Descomentar la siguiente línea solo para probar si conecta correctamente
    //echo "Conexión exitosa";

} catch (PDOException $e) {

    die("Error de conexión: " . $e->getMessage());
}
