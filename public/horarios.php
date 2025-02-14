<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para crear y gestionar horarios
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-clock"></i> Gestión de Horarios</h2>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Horario Semanal</h4>
                        <!-- Aquí irá el calendario o tabla de horarios -->
                    </div>
                    <div class="col-md-6">
                        <h4>Acciones</h4>
                        <!-- Botones y controles para gestionar horarios -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 