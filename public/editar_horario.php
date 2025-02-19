<?php
session_start();
require_once '../config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug - Imprimir todas las variables recibidas
error_log("DEBUG: Iniciando editar_horario.php");
error_log("DEBUG: GET params: " . print_r($_GET, true));
error_log("DEBUG: POST params: " . print_r($_POST, true));
error_log("DEBUG: SESSION: " . print_r($_SESSION, true));

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    error_log("Usuario no autenticado o no es administrador");
    header('Location: login.php');
    exit;
}

// Verificar si se proporcionó un ID de horario
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    error_log("No se proporcionó ID de horario o no es válido");
    header('Location: horarios.php');
    exit;
}

$horario_id = intval($_GET['id']);
error_log("ID de horario recibido y convertido a int: " . $horario_id);

try {
    // Obtener los datos del horario
    $stmt = $pdo->prepare("SELECT h.*, u.nombre as nombre_usuario 
                          FROM horarios_trabajo h 
                          INNER JOIN usuarios u ON h.usuario_id = u.id 
                          WHERE h.id = ?");
    $stmt->execute([$horario_id]);
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horario) {
        error_log("No se encontró el horario con ID: " . $horario_id);
        header('Location: horarios.php');
        exit;
    }
    error_log("Datos del horario encontrados: " . print_r($horario, true));

    // Obtener lista de usuarios para el select
    $stmt = $pdo->query("SELECT id, nombre FROM usuarios WHERE estado = 'Activo' ORDER BY nombre");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error en editar_horario.php: " . $e->getMessage());
    header('Location: horarios.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'head.php'; ?>
    <title>Editar Horario</title>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Editar Horario</h4>
                        </div>
                        <div class="card-body">
                            <form id="formEditarHorario">
                                <input type="hidden" name="horario_id" value="<?php echo htmlspecialchars($horario['id']); ?>">
                                
                                <div class="form-group">
                                    <label for="usuario_id">Empleado</label>
                                    <select class="form-control" id="usuario_id" name="usuario_id" required>
                                        <option value="">Seleccione un empleado</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?php echo htmlspecialchars($usuario['id']); ?>"
                                                <?php echo ($usuario['id'] == $horario['usuario_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($usuario['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" 
                                           value="<?php echo htmlspecialchars($horario['fecha']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="hora_entrada">Hora de Entrada</label>
                                    <input type="time" class="form-control" id="hora_entrada" name="hora_entrada" 
                                           value="<?php echo htmlspecialchars($horario['hora_entrada']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="hora_salida">Hora de Salida</label>
                                    <input type="time" class="form-control" id="hora_salida" name="hora_salida" 
                                           value="<?php echo htmlspecialchars($horario['hora_salida']); ?>" required>
                                </div>

                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                    <a href="horarios.php" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('formEditarHorario').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Formulario enviado');
        
        const formData = new FormData(this);
        console.log('Datos del formulario:', {
            horario_id: formData.get('horario_id'),
            usuario_id: formData.get('usuario_id'),
            fecha: formData.get('fecha'),
            hora_entrada: formData.get('hora_entrada'),
            hora_salida: formData.get('hora_salida')
        });
        
        // Verificar que todos los campos estén presentes
        if (!formData.get('usuario_id') || !formData.get('fecha') || 
            !formData.get('hora_entrada') || !formData.get('hora_salida')) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Todos los campos son obligatorios'
            });
            return;
        }

        // Verificar que la hora de salida sea posterior a la hora de entrada
        if (formData.get('hora_entrada') >= formData.get('hora_salida')) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La hora de salida debe ser posterior a la hora de entrada'
            });
            return;
        }
        
        fetch('procesar_horario.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'horarios.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.error || 'Ha ocurrido un error al procesar la solicitud'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ha ocurrido un error al procesar la solicitud'
            });
        });
    });
    </script>
</body>
</html>
