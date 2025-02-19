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

            if (!eventos || eventos.length === 0) {
                modalBody.innerHTML = `<p class="text-center text-muted">No hay horarios programados para este día.</p>`;
            } else {
                eventos.forEach(evento => {
                    console.log('Evento a procesar:', evento); // Debug
                    let tipo = evento.tipo || 'normal';
                    let iconoTipo = {
                        'normal': '',
                        'descanso': '🏠',
                        'baja': '🏥',
                        'otros': '⚠️'
                    }[tipo] || '';
                    
                    let infoAdicional = '';
                    if (tipo === 'normal') {
                        infoAdicional = `
                            <p class="mb-1"><strong>Horario:</strong> ${evento.hora_entrada || ''} - ${evento.hora_salida || ''}</p>
                            <p class="mb-1"><strong>Horas:</strong> ${evento.horas_dia || 0}</p>`;
                    }
                    
                    let estiloTarjeta = tipo === 'baja' ? 'background-color: #ffffff; color: #dc3545;' : '';
                    
                    modalBody.innerHTML += `
                        <div class="evento-tarjeta mb-3 p-3 border rounded" style="${estiloTarjeta}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-2">${iconoTipo} ${evento.title}</h5>
                                    <p class="mb-1"><strong>Tipo:</strong> ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</p>
                                    ${infoAdicional}
                                </div>
                                <div>
                                    <button class="btn btn-danger btn-sm mr-1" onclick="eliminarHorario(${evento.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="editarHorario(${evento.id}, '${evento.title}', '${evento.start}', '${evento.hora_entrada || ''}', '${evento.hora_salida || ''}', ${evento.usuario_id}, '${tipo}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
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
            $('#modalAgregarHorarioLabel').text('Agregar Horario');
            $('#horario_id').val('');
            $('#fechaSeleccionada').val(fecha);
            $('#usuario_id').val('');
            $('#hora_entrada').val('');
            $('#hora_salida').val('');
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

        function editarHorario(id, nombre, fecha, hora_entrada, hora_salida, usuario_id, tipo = 'normal') {
            console.log('Editando horario:', { id, nombre, fecha, hora_entrada, hora_salida, usuario_id, tipo }); // Debug
            
            $('#modalEventosDia').modal('hide');
            $('#modalAgregarHorarioLabel').text('Editar Horario');
            $('#horario_id').val(id);
            $('#fechaSeleccionada').val(fecha);
            $('#usuario_id').val(usuario_id).trigger('change');
            $('#tipo').val(tipo);
            $('#hora_entrada').val(hora_entrada);
            $('#hora_salida').val(hora_salida);
            
            toggleHorarioFields(); // Actualizar visibilidad de campos según tipo
            
            $('#modalAgregarHorario').modal('show');

            // Verificación adicional después de un breve retraso
            setTimeout(() => {
                if ($('#usuario_id').val() != usuario_id) {
                    console.log('Reintentando seleccionar usuario:', usuario_id);
                    $('#usuario_id').val(usuario_id);
                }
            }, 100);
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
                            <label for="tipo">Tipo de Registro:</label>
                            <select id="tipo" name="tipo" class="form-control" required onchange="toggleHorarioFields()">
                                <option value="normal">Horario Normal</option>
                                <option value="descanso">Descanso</option>
                                <option value="baja">Baja</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>

                        <div id="camposHorario">
                            <div class="form-group">
                                <label for="hora_entrada">Hora de Entrada:</label>
                                <input type="time" id="hora_entrada" name="hora_entrada" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="hora_salida">Hora de Salida:</label>
                                <input type="time" id="hora_salida" name="hora_salida" class="form-control" required>
                            </div>
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

    <!-- Modal para copiar horarios -->
    <div class="modal fade" id="modalCopiarHorarios" tabindex="-1" role="dialog" aria-labelledby="modalCopiarHorariosLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCopiarHorariosLabel">Copiar Horarios de Mes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formCopiarHorarios">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="mesOrigen">Mes Origen:</label>
                            <input type="month" id="mesOrigen" name="mesOrigen" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="mesDestino">Mes Destino:</label>
                            <input type="month" id="mesDestino" name="mesDestino" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Copiar Horarios</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
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
        function toggleHorarioFields() {
            const tipo = $('#tipo').val();
            const camposHorario = $('#camposHorario');
            const horaEntrada = $('#hora_entrada');
            const horaSalida = $('#hora_salida');
            
            if (tipo === 'normal') {
                camposHorario.show();
                horaEntrada.prop('required', true);
                horaSalida.prop('required', true);
            } else {
                camposHorario.hide();
                horaEntrada.prop('required', false);
                horaSalida.prop('required', false);
                horaEntrada.val('');
                horaSalida.val('');
            }
        }

        // Agregar botón para copiar horarios
        $('.container').prepend(`
            <div class="mb-3">
                <button class="btn btn-primary" onclick="$('#modalCopiarHorarios').modal('show')">
                    <i class="fas fa-copy"></i> Copiar Horarios de Mes
                </button>
            </div>
        `);

        // Manejar el formulario de copiar horarios
        $('#formCopiarHorarios').on('submit', function(e) {
            e.preventDefault();
            
            const mesOrigen = $('#mesOrigen').val();
            const mesDestino = $('#mesDestino').val();
            
            if (mesOrigen === mesDestino) {
                Swal.fire("Error", "El mes origen y destino no pueden ser iguales", "error");
                return;
            }

            $.ajax({
                url: 'copiar_horarios.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: "¡Éxito!",
                            text: response.message,
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            $('#modalCopiarHorarios').modal('hide');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire("Error", response.error || "No se pudieron copiar los horarios", "error");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr, status, error);
                    Swal.fire("Error", "No se pudieron copiar los horarios", "error");
                }
            });
        });

        // Validar el formulario antes de enviar
        $('#formHorario').on('submit', function(e) {
            e.preventDefault();
            
            const tipo = $('#tipo').val();
            if (!$('#usuario_id').val()) {
                Swal.fire("Error", "Por favor seleccione un empleado", "error");
                return;
            }

            if (tipo === 'normal') {
                if (!$('#hora_entrada').val() || !$('#hora_salida').val()) {
                    Swal.fire("Error", "Para horarios normales, debe especificar hora de entrada y salida", "error");
                    return;
                }

                if ($('#hora_entrada').val() >= $('#hora_salida').val()) {
                    Swal.fire("Error", "La hora de salida debe ser posterior a la hora de entrada", "error");
                    return;
                }
            }

            const formData = new FormData(this);
            // Si el tipo no es normal, establecer hora_entrada y hora_salida como null
            if (tipo !== 'normal') {
                formData.set('hora_entrada', '');
                formData.set('hora_salida', '');
            }

            $.ajax({
                url: 'procesar_horario.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: "¡Éxito!",
                            text: response.message,
                            icon: "success",
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            $('#modalAgregarHorario').modal('hide');
                            window.location.reload();
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
