<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener los datos del usuario
$stmt = $pdo->prepare("SELECT nombre, correo, telefono, rol, imagen FROM usuarios WHERE ID = :id");
$stmt->execute(['id' => $usuario_id]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

// Manejar la actualización del perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];

    // Directorio de almacenamiento de imágenes
    $rutaDirectorio = "../public/imgs/";

    // Crear la carpeta si no existe
    if (!is_dir($rutaDirectorio)) {
        mkdir($rutaDirectorio, 0777, true);
    }

    // Procesar la imagen si se sube una nueva
    if (!empty($_FILES['imagen']['name'])) {
        $imagenNombre = basename($_FILES['imagen']['name']);
        $rutaImagen = $rutaDirectorio . $imagenNombre;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaImagen);
        $imagenGuardar = "public/imgs/" . $imagenNombre; // Ruta relativa para la BD
    } else {
        $imagenGuardar = $empleado['imagen']; // Mantener la imagen actual si no se cambia
    }

    // Si el usuario quiere cambiar la contraseña
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET nombre = :nombre, correo = :correo, telefono = :telefono, imagen = :imagen, password = :password WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
            'imagen' => $imagenGuardar,
            'password' => $password,
            'id' => $usuario_id
        ]);
    } else {
        $sql = "UPDATE usuarios SET nombre = :nombre, correo = :correo, telefono = :telefono, imagen = :imagen WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
            'imagen' => $imagenGuardar,
            'id' => $usuario_id
        ]);
    }

    // Actualizar los datos en sesión para reflejar cambios en el navbar
    $_SESSION['nombre'] = $nombre;
    $_SESSION['imagen'] = $imagenGuardar;

    $mensaje = "Perfil actualizado correctamente.";
}
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-user"></i> Mi Perfil</h2>
        
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="card mt-3">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label><strong>Nombre:</strong></label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($empleado['nombre']); ?>" required disabled>
                    </div>
                    <div class="form-group">
                        <label><strong>Correo:</strong></label>
                        <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($empleado['correo']); ?>" required disabled>
                    </div>
                    <div class="form-group">
                        <label><strong>Teléfono:</strong></label>
                        <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($empleado['telefono']); ?>" required disabled>
                    </div>
                    <div class="form-group">
                        <label><strong>Imagen de Perfil:</strong></label><br>
                        <img src="<?php echo (!empty($empleado['imagen']) && file_exists("../" . $empleado['imagen'])) ? "../" . $empleado['imagen'] : '../public/imgs/nofoto.png'; ?>" class="rounded-circle" style="width: 100px; height: 100px;">
                        <input type="file" name="imagen" class="form-control mt-2" disabled>
                    </div>
                    <div class="form-group">
                        <label><strong>Nueva Contraseña (Opcional):</strong></label>
                        <input type="password" name="password" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <button type="button" id="editarPerfil" class="btn btn-warning"><i class="fas fa-edit"></i> Editar Perfil</button>
                        <button type="submit" id="guardarCambios" class="btn btn-primary" style="display: none;" onclick="confirmarGuardado(event)"><i class="fas fa-save"></i> Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.getElementById('editarPerfil').addEventListener('click', function () {
        document.querySelectorAll('input').forEach(input => input.removeAttribute('disabled'));
        document.getElementById('editarPerfil').style.display = 'none';
        document.getElementById('guardarCambios').style.display = 'inline-block';
    });

    function confirmarGuardado(event) {
        event.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Se guardarán los cambios en tu perfil.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar cambios',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector('form').submit(); // Enviar el formulario si el usuario confirma
            }
        });
    }
    </script>
</body>
</html>
