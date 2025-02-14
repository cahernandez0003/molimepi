<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para enviar y gestionar solicitudes
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-envelope"></i> Gestión de Solicitudes</h2>
        <div class="card mt-3">
            <div class="card-body">
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#nuevaSolicitudModal">
                    <i class="fas fa-plus"></i> Nueva Solicitud
                </button>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aquí irán las solicitudes -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 