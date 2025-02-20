<?php
require_once '../config/database.php';

// Nueva contraseña
$nueva_password = password_hash("123456", PASSWORD_BCRYPT);

$stmt = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = 19");
$stmt->execute(['password' => $nueva_password]);

echo "Contraseña actualizada correctamente.";
?>
