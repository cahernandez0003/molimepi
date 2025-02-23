<?php
session_start();

function estaAutenticado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador';
}

function esEmpleado() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'Empleado';
}

function verificarAcceso() {
    if (!estaAutenticado()) {
        header('Location: login.php');
        exit();
    }
}

function verificarAccesoAdmin() {
    verificarAcceso();
    if (!esAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

function verificarAccesoEmpleado($usuario_id = null) {
    verificarAcceso();
    if (esEmpleado() && $usuario_id && $usuario_id != $_SESSION['usuario_id']) {
        header('Location: dashboard.php');
        exit();
    }
}

function obtenerIdUsuario() {
    return $_SESSION['usuario_id'] ?? null;
}
?> 