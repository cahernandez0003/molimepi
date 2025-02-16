<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Administrador') {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

// Habilitar la visualización de errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obtener empleados para la selección
try {
    $temp = $pdo->query("SELECT ID, nombre FROM usuarios WHERE rol = 'Empleado'");
    $empleados = $temp->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la consulta de empleados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-4">
        <h2><i class="fas fa-calendar-alt"></i> Gestión de Horarios</h2>
        <div id="calendario"></div>
    </div>

    <!-- Cargar jQuery antes de cualquier otro script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>

    <!-- Cargar estilos de FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    
    <!-- Cargar FullCalendar y Moment.js después de jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

    <script>
        $(document).ready(function() {
            if (typeof FullCalendar === 'undefined') {
                console.error("FullCalendar no se ha cargado correctamente.");
                return;
            }
            
            let calendarEl = document.getElementById('calendario');
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                selectable: true,
                dateClick: function(info) {
                    $('#fechaSeleccionada').val(info.dateStr);
                    $('#modalAgregarHorario').modal('show');
                }
            });
            calendar.render();
        });
    </script>

    <!-- Modal para agregar horarios -->
    <div class="modal fade" id="modalAgregarHorario" tabindex="-1" role="dialog" aria-labelledby="modalAgregarHorarioLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarHorarioLabel">Agregar Horario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="procesar_horario.php">
                    <div class="modal-body">
                        <input type="hidden" id="fechaSeleccionada" name="fecha" required>
                        <div class="form-group">
                            <label for="usuario_id">Empleado:</label>
                            <select id="usuario_id" name="usuario_id" class="form-control" required>
                                <option value="">Seleccione un empleado</option>
                                <?php foreach ($empleados as $empleado): ?>
                                    <option value="<?php echo htmlspecialchars($empleado['ID']); ?>">
                                        <?php echo htmlspecialchars($empleado['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="hora_entrada">Hora de Entrada:</label>
                            <input type="time" id="hora_entrada" name="hora_entrada" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="hora_salida">Hora de Salida:</label>
                            <input type="time" id="hora_salida" name="hora_salida" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
