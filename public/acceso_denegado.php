<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
require_once 'head.php';
require_once 'navbar.php';

verificarAcceso();
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4><i class="fas fa-exclamation-triangle"></i> Acceso Denegado</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-lock fa-5x text-danger mb-3"></i>
                        <h5 class="card-title">No tienes permisos para acceder a esta página</h5>
                        <p class="card-text">Esta sección está restringida a usuarios con rol de Administrador.</p>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> Si crees que deberías tener acceso a esta página, por favor contacta al administrador del sistema.
                    </div>
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-home"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>Si necesitas ayuda, contacta al departamento de soporte técnico.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 