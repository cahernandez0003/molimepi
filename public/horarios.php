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
    $temp = $pdo->query("SELECT ID, nombre FROM usuarios");
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
        <div class="mb-3">
            <button class="btn btn-primary" onclick="$('#modalCopiarHorarios').modal('show')">
                <i class="fas fa-copy"></i> Copiar Horarios de Mes
            </button>
        </div>
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="fas fa-clock"></i> Gestión de Horarios
                </h3>
                <div>
                    <button type="button" class="btn btn-success" onclick="exportarHorarios('excel')">
                        <i class="fas fa-file-excel"></i> Exportar Excel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="exportarHorarios('pdf')">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
        <div id="calendario"></div>
            </div>
        </div>
    </div>

    <!-- Modal para ver eventos del día -->
    <div class="modal fade" id="modalEventosDia" tabindex="-1" aria-labelledby="modalEventosDiaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloEventos"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>
                <div class="modal-body" id="contenidoEventos">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar horario -->
    <div class="modal fade" id="modalAgregarHorario" tabindex="-1" aria-labelledby="modalAgregarHorarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarHorarioLabel">Agregar Horario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>
                <div class="modal-body">
                    <form id="formHorario" action="guardar_horario.php" method="POST">
                        <input type="hidden" id="horario_id" name="horario_id">
                        <input type="hidden" id="fechaSeleccionada" name="fecha">
                        
                        <div class="mb-3">
                            <label for="usuario_id" class="form-label">Empleado</label>
                            <select class="form-select" id="usuario_id" name="usuario_id" required>
                                <option value="">Seleccione un empleado</option>
                                <?php foreach ($empleados as $empleado): ?>
                                    <option value="<?php echo $empleado['ID']; ?>"><?php echo htmlspecialchars($empleado['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo" onchange="toggleHorarioFields()" required>
                                <option value="normal">Normal</option>
                                <option value="descanso">Descanso</option>
                                <option value="baja">Baja</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>

                        <div id="camposHorario">
                            <div class="mb-3">
                                <label for="hora_entrada" class="form-label">Hora de Entrada</label>
                                <input type="time" class="form-control" id="hora_entrada" name="hora_entrada">
                            </div>
                            <div class="mb-3">
                                <label for="hora_salida" class="form-label">Hora de Salida</label>
                                <input type="time" class="form-control" id="hora_salida" name="hora_salida">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para copiar horarios -->
    <div class="modal fade" id="modalCopiarHorarios" tabindex="-1" aria-labelledby="modalCopiarHorariosLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCopiarHorariosLabel">Copiar Horarios de Mes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>
                <div class="modal-body">
                    <form id="formCopiarHorarios" action="copiar_horarios.php" method="POST">
                        <div class="mb-3">
                            <label for="mes_origen" class="form-label">Mes Origen</label>
                            <input type="month" class="form-control" id="mes_origen" name="mes_origen" required>
                        </div>
                        <div class="mb-3">
                            <label for="mes_destino" class="form-label">Mes Destino</label>
                            <input type="month" class="form-control" id="mes_destino" name="mes_destino" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Copiar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cargar estilos de FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    
    <!-- Cargar FullCalendar y Moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>

    <script>
        // Función para exportar horarios
        function exportarHorarios(formato) {
            const mes = moment($('#calendario').fullCalendar('getDate')).format('YYYY-MM');
            window.location.href = `exportar_horarios.php?formato=${formato}&mes=${mes}`;
        }

        // Inicializar calendario cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', function() {
            let calendarEl = document.getElementById('calendario');
            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                selectable: true,
                events: 'obtener_horarios.php',
                dateClick: function(info) {
                    cargarHorarios(info.dateStr);
                }
            });
            calendar.render();
        });

        // Manejar envío del formulario de horarios
        $('#formHorario').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#modalAgregarHorario').modal('hide');
                        Swal.fire({
                            title: "¡Éxito!",
                            text: response.message,
                            icon: "success"
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire("Error", response.error || "No se pudo guardar el horario", "error");
                    }
                },
                error: function() {
                    Swal.fire("Error", "No se pudo procesar la solicitud", "error");
                }
            });
        });

        // Manejar envío del formulario de copiar horarios
        $('#formCopiarHorarios').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#modalCopiarHorarios').modal('hide');
                        Swal.fire({
                            title: "¡Éxito!",
                            text: response.message,
                            icon: "success"
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire("Error", response.error || "No se pudieron copiar los horarios", "error");
                    }
                },
                error: function() {
                    Swal.fire("Error", "No se pudo procesar la solicitud", "error");
                }
            });
        });

        // Resto de funciones existentes
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
                    console.log('Evento a procesar:', evento);
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
            $('#tipo').val('normal');
            toggleHorarioFields();
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
                                    window.location.reload();
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
            console.log('Editando horario:', { id, nombre, fecha, hora_entrada, hora_salida, usuario_id, tipo });
            
            $('#modalEventosDia').modal('hide');
            $('#modalAgregarHorarioLabel').text('Editar Horario');
            $('#horario_id').val(id);
            $('#fechaSeleccionada').val(fecha);
            $('#usuario_id').val(usuario_id);
            $('#tipo').val(tipo);
            $('#hora_entrada').val(hora_entrada);
            $('#hora_salida').val(hora_salida);
            
            toggleHorarioFields();
            $('#modalAgregarHorario').modal('show');
        }

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
    </script>
</body>
</html>
