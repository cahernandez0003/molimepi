<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para registrar la entrada y salida
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-clipboard-check"></i> Registro de Asistencia</h2>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <h4>Marcar Asistencia</h4>
                                <div class="mt-4">
                                    <button class="btn btn-success btn-lg m-2">
                                        <i class="fas fa-sign-in-alt"></i> Entrada
                                    </button>
                                    <button class="btn btn-danger btn-lg m-2">
                                        <i class="fas fa-sign-out-alt"></i> Salida
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4>Registro de Hoy</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Aquí irán los registros de asistencia -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 