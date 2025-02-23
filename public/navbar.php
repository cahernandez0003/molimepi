<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

// Verificar si el usuario está autenticado antes de acceder a la BD
verificarAcceso();
$usuario_id = obtenerIdUsuario();
$es_admin = esAdmin();

// Obtener los datos del usuario en sesión
$stmt = $pdo->prepare("SELECT nombre, imagen FROM usuarios WHERE ID = :id");
$stmt->execute(['id' => $usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el usuario no tiene imagen, usar una por defecto
$imagenPerfil = (!empty($usuario['imagen']) && file_exists("../" . $usuario['imagen'])) ? "../" . $usuario['imagen'] : '../public/imgs/nofoto.png';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">MOLIMEPI</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
                </li>
                <?php if ($es_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="empleados.php"><i class="fas fa-users"></i> Empleados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="horarios.php"><i class="fas fa-clock"></i> Horarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="exportar.php"><i class="fas fa-file-export"></i> Exportar</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="asistencia.php"><i class="fas fa-clipboard-check"></i> Asistencia</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="hrs_extras.php"><i class="fas fa-clock"></i> Horas Extra</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="solicitudes.php"><i class="fas fa-envelope"></i> Solicitudes</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <!-- Notificaciones -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificacionesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span id="contador-notificaciones" class="badge bg-danger rounded-pill position-absolute" style="top: 0; right: 0; display: none;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificacionesDropdown">
                        <div class="dropdown-header">Notificaciones</div>
                        <div id="notificacionesLista">
                            <!-- Las notificaciones se cargarán aquí -->
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="ver_notificaciones.php">
                            Ver todas las notificaciones
                        </a>
                    </div>
                </li>
                <!-- Información del Usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $imagenPerfil; ?>" class="rounded-circle" style="width: 40px; height: 40px; margin-right: 10px;">
                        <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
                        <?php if (!$es_admin): ?>
                            <a class="dropdown-item" href="mi_horario.php"><i class="fas fa-calendar"></i> Mi Horario</a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="#" onclick="confirmarLogout()"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div style="margin-top: 80px;"><!-- Espacio para compensar el navbar fijo --></div>

<!-- Scripts necesarios -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
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
</script>
