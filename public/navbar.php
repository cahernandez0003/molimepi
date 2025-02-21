<?php
require_once '../config/database.php';

// Verificar si el usuario está autenticado antes de acceder a la BD
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener los datos del usuario en sesión
$stmt = $pdo->prepare("SELECT nombre, imagen FROM usuarios WHERE ID = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el usuario no tiene imagen, usar una por defecto
$imagenPerfil = (!empty($usuario['imagen']) && file_exists("../" . $usuario['imagen'])) ? "../" . $usuario['imagen'] : '../public/imgs/nofoto.png';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="dashboard.php">MOLIMEPI</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
            </li>
            <?php if ($_SESSION['rol'] === 'Administrador'): ?>
            <li class="nav-item">
                <a class="nav-link" href="empleados.php"><i class="fas fa-users"></i> Empleados</a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="horarios.php"><i class="fas fa-clock"></i> Horarios</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="asistencia.php"><i class="fas fa-clipboard-check"></i> Asistencia</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="solicitudes.php"><i class="fas fa-envelope"></i> Solicitudes</a>
            </li>
            <?php if ($_SESSION['rol'] === 'Administrador'): ?>
            <li class="nav-item">
                <a class="nav-link" href="exportar.php"><i class="fas fa-file-export"></i> Exportar</a>
            </li>
            <?php endif; ?>
        </ul>
        <ul class="navbar-nav ml-auto">
            <!-- Notificaciones -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle position-relative" href="#" id="notificacionesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <span id="contador-notificaciones" class="badge badge-danger badge-pill position-absolute" style="top: 0; right: 0; display: none;">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificacionesDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                    <h6 class="dropdown-header">Notificaciones</h6>
                    <div id="lista-notificaciones">
                        <!-- Las notificaciones se cargarán aquí dinámicamente -->
                    </div>
                </div>
            </li>
            <!-- Información del Usuario -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="<?php echo $imagenPerfil; ?>" class="rounded-circle" style="width: 40px; height: 40px; margin-right: 10px;">
                    <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="#" onclick="confirmarLogout()"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<div style="margin-top: 80px;"><!-- Espacio para compensar el navbar fijo --></div>

<!-- Scripts necesarios -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script de notificaciones con ruta absoluta -->
<script>
    // Configuración global
    window.baseUrl = '<?php echo "http://" . $_SERVER["HTTP_HOST"] . "/molimepi/public"; ?>';
</script>
<script src="<?php echo "http://" . $_SERVER["HTTP_HOST"] . "/molimepi/public/js/notificaciones.js"; ?>"></script>

<script>
function confirmarLogout() {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Se cerrará tu sesión y tendrás que volver a iniciar sesión.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "logout.php";
        }
    });
}

// Inicializar dropdowns
$(document).ready(function() {
    $('.dropdown-toggle').dropdown();
});
</script>
