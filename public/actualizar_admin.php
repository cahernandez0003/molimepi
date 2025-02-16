<?php
require_once '../config/database.php';

// Nueva contraseña segura para el administrador
$nueva_password = password_hash("123456", PASSWORD_BCRYPT);

$stmt = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = 1");
$stmt->execute(['password' => $nueva_password]);

echo "Contraseña del administrador usuario fue actualizada correctamente.";
?>
