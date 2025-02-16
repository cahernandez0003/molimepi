<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

function mostrarUsuario($pdo, $id) {
    try {
        $sql = "SELECT * FROM usuarios WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: ID de usuario no proporcionado.");
}

$id = $_GET['id'];
$musu = mostrarUsuario($pdo, $id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $identificacion = $_POST['identificacion'];
    $cargo = $_POST['cargo'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    if (!empty($_FILES['imagen']['name'])) {
        $imagenNombre = "public/imgs/" . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], "../" . $imagenNombre);
    } else {
        $imagenNombre = $_POST['imagen_actual'];
    }

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET nombre = :nombre, identificacion = :identificacion, cargo = :cargo, telefono = :telefono, correo = :correo, rol = :rol, imagen = :imagen, password = :password WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'identificacion' => $identificacion,
            'cargo' => $cargo,
            'telefono' => $telefono,
            'correo' => $correo,
            'rol' => $rol,
            'imagen' => $imagenNombre,
            'password' => $password,
            'id' => $id
        ]);
    } else {
        $sql = "UPDATE usuarios SET nombre = :nombre, identificacion = :identificacion, cargo = :cargo, telefono = :telefono, correo = :correo, rol = :rol, imagen = :imagen WHERE ID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'identificacion' => $identificacion,
            'cargo' => $cargo,
            'telefono' => $telefono,
            'correo' => $correo,
            'rol' => $rol,
            'imagen' => $imagenNombre,
            'id' => $id
        ]);
    }
    header('Location: empleados.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-user-edit"></i> Editar Usuario</h2>
        <?php foreach ($musu as $urow): ?>
        <form method="post" enctype="multipart/form-data" onsubmit="return confirmarGuardado();">
            <table class="table table-striped table-hover text-justify">
                <tr>
                    <th> Nombres y Apellidos: </th>
                    <td><input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($urow['nombre']); ?>" required></td>
                </tr>
                <tr>
                    <th> Identificación: </th>
                    <td><input type="text" name="identificacion" class="form-control" value="<?php echo htmlspecialchars($urow['identificacion']); ?>" required></td>
                </tr>
                <tr>
                    <th> Correo Electrónico: </th>
                    <td><input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($urow['correo']); ?>" required></td>
                </tr>
                <tr>
                    <th> Teléfono: </th>
                    <td><input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($urow['telefono']); ?>" required></td>
                </tr>
                <tr>
                    <th> Cargo: </th>
                    <td><input type="text" name="cargo" class="form-control" value="<?php echo htmlspecialchars($urow['cargo']); ?>" required></td>
                </tr>
                <tr>
                    <th> Rol: </th>
                    <td>
                        <select name="rol" class="form-control">
                            <option value="Empleado" <?php echo ($urow['rol'] === 'Empleado') ? 'selected' : ''; ?>>Empleado</option>
                            <option value="Administrador" <?php echo ($urow['rol'] === 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th> Imagen: </th>
                    <td>
                        <input type="file" class="form-control" name="imagen" accept="image/*">
                        <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($urow['imagen']); ?>">
                        <img src="../<?php echo htmlspecialchars($urow['imagen']); ?>" alt="Imagen de usuario" style="width: 100px; height: 100px;" data-toggle="modal" data-target="#imageModal" data-img-src="../<?php echo htmlspecialchars($urow['imagen']); ?>" class="clickable-image">
                    </td>
                </tr>
                <tr>
                    <th> Nueva Contraseña (Opcional): </th>
                    <td><input type="password" name="password" class="form-control"></td>
                </tr>
            </table>
            <div class="form-group">
                <button type="submit" class="btn btn-outline-success" onclick="confirmarGuardado(event);">
                    <i class="fa fa-save"></i> Modificar
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="confirmarCancelacion();">
                    <i class="fa fa-times"></i> Cancelar
                </button>
            </div>

        </form>
        <?php endforeach; ?>
    </div>

    <!-- Modal para mostrar imagen ampliada -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Imagen del Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" src="" alt="Imagen ampliada" style="width: 100%; height: auto;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmarGuardado(event) {
    event.preventDefault();
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Se guardarán los cambios en el usuario.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.querySelector("form").submit();
        }
    });
}

function confirmarCancelacion() {
    Swal.fire({
        title: '¿Cancelar edición?',
        text: "Los cambios no guardados se perderán.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Volver'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'empleados.php';
        }
    });
}

$(document).ready(function() {
    $('.clickable-image').on('click', function() {
        var imgSrc = $(this).data('img-src');
        $('#modalImage').attr('src', imgSrc);
    });
});
</script>

</body>
</html>
