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
            let calendarEl = document.getElementById('calendario');
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                selectable: true,
                events: 'obtener_horarios.php',
                dateClick: function(info) {
                    cargarHorarios(info.dateStr); // Solo cargar horarios, no abrir modal de agregar
                }
            });
            calendar.render();
        });

        function cargarHorarios(fecha) {
            console.log('Cargando horarios para fecha:', fecha);
            $.ajax({
                url: 'obtener_horarios.php',
                method: 'GET',
                data: { fecha: fecha },
                dataType: 'json',
                success: function(response) {
                    console.log('Eventos recibidos:', response);
                    mostrarTarjetaEventos(response, fecha);
                },
                error: function(error) {
                    console.error("Error al obtener eventos:", error);
                    Swal.fire('Error', 'No se pudieron cargar los horarios', 'error');
                }
            });
        }

        function mostrarTarjetaEventos(eventos, fecha) {
            let modalBody = document.getElementById('contenidoEventos');
            modalBody.innerHTML = '';

            if (eventos.length === 0) {
                modalBody.innerHTML = `<p class="text-center text-muted">No hay horarios programados para este día.</p>`;
            } else {
                eventos.forEach(evento => {
                    console.log('Procesando evento:', evento);
                    modalBody.innerHTML += `
                        <div class="evento-tarjeta d-flex justify-content-between align-items-center">
                            <div>
                                <h5>${evento.title}</h5>
                                <p><strong>Horario:</strong> ${evento.hora_entrada} - ${evento.hora_salida}</p>
                            </div>
                            <div>
                                <button class="btn btn-danger btn-sm" onclick="eliminarHorario(${evento.id})">
                                    <i class="fas fa-trash"></i> 
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="editarHorario(${evento.id}, '${evento.hora_entrada}', '${evento.hora_salida}')">
                                    <i class="fas fa-edit"></i> 
                                </button>
                            </div>
                        </div>`;
                });
            }

            modalBody.innerHTML += `
                <hr>
                <button class="btn btn-primary" onclick="mostrarFormularioAgregar('${fecha}')">
                    <i class="fas fa-plus"></i> Agregar Horario
                </button>`;

            document.getElementById('tituloEventos').innerText = "Horarios del " + fecha;
            $('#modalEventosDia').modal('show');
        }

        function mostrarFormularioAgregar(fecha) {
            $('#modalEventosDia').modal('hide');
            $('#fechaSeleccionada').val(fecha);
            $('#usuario_id').val(''); // Limpiar selección previa
            $('#hora_entrada').val(''); // Limpiar hora entrada
            $('#hora_salida').val(''); // Limpiar hora salida
            $('#modalAgregarHorario').modal('show');
        }

        function eliminarHorario(idHorario) {
            if (!idHorario) {
                console.error('ID de horario no proporcionado');
                Swal.fire("Error", "ID de horario no válido", "error");
                return;
            }

            Swal.fire({
                title: "¿Eliminar horario?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'eliminar_horario.php',
                        method: 'POST',
                        data: { id: idHorario },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: "Eliminado",
                                    text: response.message || "El horario ha sido eliminado correctamente.",
                                    icon: "success"
                                }).then(() => {
                                    window.location.href = 'horarios.php'; // Redireccionar después de eliminar
                                });
                            } else {
                                Swal.fire("Error", response.error || "No se pudo eliminar el horario.", "error");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error en la solicitud:', {xhr, status, error});
                            Swal.fire("Error", "No se pudo eliminar el horario.", "error");
                        }
                    });
                }
            });
        }

        function editarHorario(id, horaEntrada, horaSalida) {
            $('#modalEventosDia').modal('hide');
            $('#horario_id').val(id);
            $('#hora_entrada').val(horaEntrada);
            $('#hora_salida').val(horaSalida);
            $('#modalAgregarHorario').modal('show');
        }
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
                <form id="formHorario">
                    <div class="modal-body">
                        <input type="hidden" id="horario_id" name="horario_id">
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

    <!-- Modal para mostrar los eventos del día seleccionado -->
    <div class="modal fade" id="modalEventosDia" tabindex="-1" aria-labelledby="tituloEventos" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloEventos">Horarios del día</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="contenidoEventos">
                    <!-- Aquí se mostrarán los eventos -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Agregar el manejo del formulario
        $('#formHorario').on('submit', function(e) {
            e.preventDefault();
            
            // Validar que todos los campos requeridos estén llenos
            if (!$('#usuario_id').val() || !$('#hora_entrada').val() || !$('#hora_salida').val() || !$('#fechaSeleccionada').val()) {
                Swal.fire("Error", "Por favor complete todos los campos", "error");
                return;
            }

            $.ajax({
                url: 'procesar_horario.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: "¡Éxito!",
                            text: response.message,
                            icon: "success"
                        }).then(() => {
                            $('#modalAgregarHorario').modal('hide');
                            window.location.reload(); // Recargar la página para actualizar el calendario
                        });
                    } else {
                        Swal.fire("Error", response.error || "No se pudo procesar el horario", "error");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr, status, error);
                    Swal.fire("Error", "No se pudo procesar el horario", "error");
                }
            });
        });

        // Limpiar el formulario cuando se cierra el modal
        $('#modalAgregarHorario').on('hidden.bs.modal', function () {
            $('#formHorario')[0].reset();
            $('#horario_id').val('');
        });
    </script>
</body>
</html>
