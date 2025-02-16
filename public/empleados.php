<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $identificacion = $_POST['identificacion'];
    $cargo = $_POST['cargo'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    // Generar el nickname basado en la identificación
    $nickname = $identificacion;

    // Hashear la contraseña por defecto
    $password = password_hash('123456', PASSWORD_BCRYPT);

    // Manejar la subida de imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $imagen = 'imgs/' . basename($_FILES['imagen']['name']);
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen)) {
            echo "Error al subir la imagen.";
        }
    } else {
        $imagen = 'imgs/nofoto.png'; // Ruta de la imagen predeterminada
    }

    // Insertar en la tabla usuarios
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, identificacion, cargo, telefono, correo, nickname, password, rol, imagen, cambio_password) 
                           VALUES (:nombre, :identificacion, :cargo, :telefono, :correo, :nickname, :password, :rol, :imagen, 0)");
    $stmt->execute([
        'nombre' => $nombre,
        'identificacion' => $identificacion,
        'cargo' => $cargo,
        'telefono' => $telefono,
        'correo' => $correo,
        'nickname' => $nickname,
        'password' => $password,
        'rol' => $rol,
        'imagen' => $imagen
    ]);
}

$stmt = $pdo->prepare("SELECT * FROM usuarios");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-users"></i> Gestión de Empleados</h2>
        <div class="card mt-3" style="border: none; box-shadow: none;">
            <div class="card-body">
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#nuevoUsuarioModal">
                    <i class="fas fa-plus"></i> Nuevo Empleado
                </button>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th class="d-none d-md-table-cell">Identificación</th>
                            <th class="d-none d-md-table-cell">Cargo</th>
                            <th class="d-none d-md-table-cell">Teléfono</th>
                            <th class="d-none d-md-table-cell">Correo</th>
                            <th>Imagen</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td style="text-transform: uppercase;"><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td class="d-none d-md-table-cell" style="text-transform: uppercase;"><?php echo htmlspecialchars($usuario['identificacion']); ?></td>
                            <td class="d-none d-md-table-cell" style="text-transform: uppercase;"><?php echo htmlspecialchars($usuario['cargo']); ?></td>
                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($usuario['correo']); ?></td>
                            <td>
                                <img src="<?php echo str_replace('public/', '', htmlspecialchars($usuario['imagen'])); ?>" alt="Imagen de usuario" style="width: 50px; height: 50px;" data-toggle="modal" data-target="#imageModal" data-img-src="<?php echo str_replace('public/', '', htmlspecialchars($usuario['imagen'])); ?>" class="clickable-image">
                            </td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <a href="editar_usuario.php?id=<?php echo $usuario['ID']; ?>" class="btn btn-secondary" style="margin-right: 10px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger" data-toggle="modal" data-target="#confirmDeleteModal" data-id="<?php echo $usuario['ID']; ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para nuevo usuario -->
    <div class="modal fade" id="nuevoUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="nuevoUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoUsuarioModalLabel">Nuevo Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="identificacion">Identificación:</label>
                            <input type="text" id="identificacion" name="identificacion" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="cargo">Cargo:</label>
                            <input type="text" id="cargo" name="cargo" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="text" id="telefono" name="telefono" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo:</label>
                            <input type="email" id="correo" name="correo" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="rol">Rol:</label>
                            <select id="rol" name="rol" class="form-control" required>
                                <option value="Empleado">Empleado</option>
                                <option value="Administrador">Administrador</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="imagen">Imagen:</label>
                            <input type="file" id="imagen" name="imagen" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar este usuario?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a id="confirmDeleteButton" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
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

<script>
    $('#confirmDeleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Botón que activó el modal
        var userId = button.data('id'); // Extraer el ID del usuario
        var deleteUrl = 'eliminar_usuario.php?id=' + userId; // Crear la URL de eliminación

        // Actualizar el botón de confirmación
        var modal = $(this);
        modal.find('#confirmDeleteButton').attr('href', deleteUrl);
    });

    $(document).ready(function() {
        $('.clickable-image').on('click', function() {
            var imgSrc = $(this).data('img-src');
            $('#modalImage').attr('src', imgSrc);
        });
    });
</script>
</body>
</html> 