<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

// Aquí puedes agregar lógica para exportar datos a PDF y Excel
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-file-export"></i> Exportar Datos</h2>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Exportar Registros</h4>
                        <form>
                            <div class="form-group">
                                <label>Tipo de Reporte</label>
                                <select class="form-control">
                                    <option>Asistencia</option>
                                    <option>Horarios</option>
                                    <option>Solicitudes</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Formato</label>
                                <select class="form-control">
                                    <option>PDF</option>
                                    <option>Excel</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>