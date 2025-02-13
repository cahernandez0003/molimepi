<?php
$host = 'localhost';
$dbname = 'molimepi';
$username = 'root'; // Cambia esto si tienes un usuario diferente
$password = ''; // Cambia esto si tienes una contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}
?> 