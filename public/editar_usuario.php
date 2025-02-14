<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $identificacion = $_POST['identificacion'];
    $cargo = $_POST['cargo'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    // Manejar la subida de imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imagen = 'imgs/' . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen);
    } else {
        $imagen = $_POST['imagen_actual'] ?: 'imgs/nofoto.png'; // Mantener la imagen actual o usar la predeterminada
    }

    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, identificacion = :identificacion, cargo = :cargo, telefono = :telefono, correo = :correo, rol = :rol, imagen = :imagen WHERE ID = :id");
    $stmt->execute([
        'nombre' => $nombre,
        'identificacion' => $identificacion,
        'cargo' => $cargo,
        'telefono' => $telefono,
        'correo' => $correo,
        'rol' => $rol,
        'imagen' => $imagen,
        'id' => $id
    ]);

    header('Location: usuarios.php'); // Redirigir a la lista de usuarios
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE ID = :id");
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-user-edit"></i> Editar Usuario</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $usuario['ID']; ?>">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo $usuario['nombre']; ?>" required>
            </div>
            <div class="form-group">
                <label for="identificacion">Identificación:</label>
                <input type="text" id="identificacion" name="identificacion" class="form-control" value="<?php echo $usuario['identificacion']; ?>" required>
            </div>
            <div class="form-group">
                <label for="cargo">Cargo:</label>
                <input type="text" id="cargo" name="cargo" class="form-control" value="<?php echo $usuario['cargo']; ?>" required>
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" class="form-control" value="<?php echo $usuario['telefono']; ?>" required>
            </div>
            <div class="form-group">
                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" class="form-control" value="<?php echo $usuario['correo']; ?>" required>
            </div>
            <div class="form-group">
                <label for="rol">Rol:</label>
                <select id="rol" name="rol" class="form-control" required>
                    <option value="Empleado" <?php echo $usuario['rol'] == 'Empleado' ? 'selected' : ''; ?>>Empleado</option>
                    <option value="Administrador" <?php echo $usuario['rol'] == 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label for="imagen">Imagen:</label>
                <input type="file" id="imagen" name="imagen" class="form-control">
                <input type="hidden" name="imagen_actual" value="<?php echo $usuario['imagen']; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</body>
</html> 