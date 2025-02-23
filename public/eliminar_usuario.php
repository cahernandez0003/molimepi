<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: empleados.php');
    exit();
}

$id = $_GET['id'];

try {
    // Obtener información del usuario antes de eliminar
    $stmt = $pdo->prepare("SELECT imagen FROM usuarios WHERE ID = :id");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Eliminar el usuario
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE ID = :id");
    $stmt->execute(['id' => $id]);

    // Si el usuario tenía una imagen personalizada, eliminarla
    if ($usuario && $usuario['imagen'] !== 'public/imgs/nofoto.png') {
        $rutaImagen = str_replace('public/', '', $usuario['imagen']);
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen);
        }
    }

    $_SESSION['mensaje'] = "Usuario eliminado correctamente";
    $_SESSION['tipo_mensaje'] = "success";
} catch (PDOException $e) {
    error_log("Error al eliminar usuario: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error al eliminar el usuario";
    $_SESSION['tipo_mensaje'] = "error";
}

header('Location: empleados.php');
exit();
?>

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

<script>
    $('#confirmDeleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Botón que activó el modal
        var userId = button.data('id'); // Extraer el ID del usuario
        var deleteUrl = 'eliminar_usuario.php?id=' + userId; // Crear la URL de eliminación

        // Actualizar el botón de confirmación
        var modal = $(this);
        modal.find('#confirmDeleteButton').attr('href', deleteUrl);
    });
</script> 