<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];

    // Manejar la subida de imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imagen = 'uploads/' . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen);
    } else {
        $imagen = $_POST['imagen_actual'];
    }

    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, correo = :correo, telefono = :telefono, imagen = :imagen WHERE ID = :id");
    $stmt->execute(['nombre' => $nombre, 'correo' => $correo, 'telefono' => $telefono, 'imagen' => $imagen, 'id' => $_SESSION['usuario_id']]);
}

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE ID = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-user"></i> Mi Perfil</h2>
        <div class="card mt-3">
            <div class="card-body">
                <p><strong>Nombre:</strong> <?php echo $empleado['nombre']; ?></p>
                <p><strong>Correo:</strong> <?php echo $empleado['correo']; ?></p>
                <p><strong>Teléfono:</strong> <?php echo $empleado['telefono']; ?></p>
                <p><strong>Rol:</strong> <?php echo $empleado['rol']; ?></p>
            </div>
        </div>
    </div>
</body>
</html> 