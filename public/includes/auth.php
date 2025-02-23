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
?> 