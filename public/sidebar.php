<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">MOLIMEPI</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link text-white" href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
            </li>
            <?php if ($_SESSION['rol'] === 'Administrador'): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="empleados.php"><i class="fas fa-users"></i> Empleados</a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="horarios.php"><i class="fas fa-clock"></i> Horarios</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="asistencia.php"><i class="fas fa-clipboard-check"></i> Asistencia</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="solicitudes.php"><i class="fas fa-envelope"></i> Solicitudes</a>
            </li>
            <?php if ($_SESSION['rol'] === 'Administrador'): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="exportar.php"><i class="fas fa-file-export"></i> Exportar</a>
            </li>
            <?php endif; ?>
            <li class="nav-item mt-4">
                <a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </li>
        </ul>
    </div>
</nav> 