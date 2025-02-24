<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

verificarAcceso();
$es_admin = esAdmin();
$usuario_id = obtenerIdUsuario();

// Obtener lista de empleados para el administrador
if ($es_admin) {
    $stmt = $pdo->query("SELECT ID, nombre FROM usuarios ORDER BY nombre");
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Función para obtener días aprobados por usuario y año
    function obtenerDiasAprobados($pdo, $usuario_id, $anio) {
        $stmt = $pdo->prepare("
            SELECT SUM(DATEDIFF(fecha_fin, fecha_inicio) + 1) as dias_aprobados
            FROM vacaciones 
            WHERE usuario_id = :usuario_id 
            AND YEAR(fecha_inicio) = :anio
            AND estado_solicitud = 'Aprobado'
        ");
        
        $stmt->execute([
            'usuario_id' => $usuario_id,
            'anio' => $anio
        ]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($resultado['dias_aprobados'] ?? 0);
    }
}

// Consulta base para las vacaciones
$sql = "SELECT v.*, 
        u.nombre as nombre_usuario, 
       
        ua.nombre as aprobado_por
        FROM vacaciones v
        INNER JOIN usuarios u ON v.usuario_id = u.ID
        LEFT JOIN usuarios ua ON ua.ID = :usuario_actual AND v.estado_solicitud = 'Aprobado'
        WHERE 1=1";

$params = ['usuario_actual' => $usuario_id];

// Si no es admin, solo ver sus propias vacaciones
if (!$es_admin) {
    $sql .= " AND v.usuario_id = :usuario_id";
    $params['usuario_id'] = $usuario_id;
}

$sql .= " ORDER BY v.fecha_solicitud DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vacaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-umbrella-beach"></i> Gestión de Vacaciones</h2>
        
        <?php if ($es_admin): ?>
        <div class="mb-4">
            <button type="button" class="btn btn-primary" onclick="mostrarModalAsignar()">
                <i class="fas fa-plus"></i> Asignar Vacaciones
            </button>
        </div>
        <?php else: ?>
        <div class="mb-4">
            <button type="button" class="btn btn-primary" onclick="mostrarModalSolicitar()">
                <i class="fas fa-paper-plane"></i> Solicitar Vacaciones
            </button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="tablaVacaciones">
                        <thead>
                            <tr>
                                <?php if ($es_admin): ?>
                                    <th>Empleado</th>
                                    
                                <?php endif; ?>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Días</th>
                                <th>Fecha Solicitud</th>
                                <th>Estado</th>
                                <th>Comentarios</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vacaciones as $vacacion): 
                                $fecha_inicio = new DateTime($vacacion['fecha_inicio']);
                                $fecha_fin = new DateTime($vacacion['fecha_fin']);
                                $dias = $fecha_inicio->diff($fecha_fin)->days + 1;
                            ?>
                                <tr>
                                    <?php if ($es_admin): ?>
                                        <td><?php echo htmlspecialchars($vacacion['nombre_usuario']); ?></td>
                                        
                                    <?php endif; ?>
                                    <td><?php echo date('d/m/Y', strtotime($vacacion['fecha_inicio'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($vacacion['fecha_fin'])); ?></td>
                                    <td><?php echo $dias; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($vacacion['fecha_solicitud'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($vacacion['estado_solicitud']) {
                                                'Aprobado' => 'success',
                                                'Rechazado' => 'danger',
                                                default => 'warning'
                                            };
                                        ?>">
                                            <?php echo $vacacion['estado_solicitud']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($vacacion['comentarios'] ?? ''); ?></td>
                                    <td>
                                        <?php if ($es_admin): ?>
                                            <?php if ($vacacion['estado_solicitud'] === 'Pendiente'): ?>
                                                <button class="btn btn-success btn-sm" onclick="procesarVacaciones(<?php 
                                                    echo htmlspecialchars(json_encode([
                                                        'id' => $vacacion['id'],
                                                        'usuario_id' => $vacacion['usuario_id'],
                                                        'fecha_inicio' => $vacacion['fecha_inicio'],
                                                        'fecha_fin' => $vacacion['fecha_fin'],
                                                        'dias' => $dias
                                                    ])); 
                                                ?>)">
                                                    <i class="fas fa-check"></i> Procesar
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-primary btn-sm" onclick="editarVacaciones(<?php 
                                                    echo htmlspecialchars(json_encode([
                                                        'id' => $vacacion['id'],
                                                        'usuario_id' => $vacacion['usuario_id'],
                                                        'fecha_inicio' => $vacacion['fecha_inicio'],
                                                        'fecha_fin' => $vacacion['fecha_fin']
                                                    ])); 
                                                ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="eliminarVacaciones(<?php echo $vacacion['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($vacacion['estado_solicitud'] === 'Pendiente'): ?>
                                                <button class="btn btn-danger btn-sm" onclick="cancelarSolicitud(<?php echo $vacacion['id']; ?>)">
                                                    <i class="fas fa-times"></i> Cancelar
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para asignar/solicitar vacaciones -->
    <div class="modal fade" id="modalVacaciones" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVacacionesTitle">Asignar Vacaciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <form id="formVacaciones">
                        <input type="hidden" id="vacacion_id">
                        
                        <?php if ($es_admin): ?>
                        <div class="mb-3">
                            <label for="usuario_id" class="form-label">Empleado</label>
                            <select class="form-select" id="usuario_id" required>
                                <option value="">Seleccione un empleado</option>
                                <?php foreach ($empleados as $empleado): ?>
                                    <option value="<?php echo $empleado['ID']; ?>">
                                        <?php echo htmlspecialchars($empleado['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" required>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" required>
                        </div>

                        <div class="mb-3">
                            <label for="dias_calculados" class="form-label">Días de Vacaciones</label>
                            <input type="text" class="form-control" id="dias_calculados" readonly>
                            <small class="form-text text-muted">
                                El período de vacaciones no puede exceder los 31 días
                            </small>
                        </div>

                        <?php if (!$es_admin): ?>
                        <div class="mb-3">
                            <label for="comentarios" class="form-label">Comentarios</label>
                            <textarea class="form-control" id="comentarios" rows="3" required></textarea>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarVacaciones()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para procesar solicitud -->
    <?php if ($es_admin): ?>
    <div class="modal fade" id="modalProcesar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Procesar Solicitud de Vacaciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <form id="formProcesar">
                        <input type="hidden" id="solicitud_id">
                        <input type="hidden" id="solicitud_usuario_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Período de Vacaciones</label>
                            <p id="periodo_vacaciones" class="form-control-plaintext"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total de Días</label>
                            <p id="total_dias" class="form-control-plaintext"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Disponibilidad de Días</label>
                            <div id="dias_disponibles" class="form-control-plaintext"></div>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Decisión</label>
                            <select class="form-control" id="estado" required>
                                <option value="Aprobado">Aprobar</option>
                                <option value="Rechazado">Rechazar</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="comentarios_proceso" class="form-label">Comentarios</label>
                            <textarea class="form-control" id="comentarios_proceso" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarProceso()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        $('#tablaVacaciones').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[<?php echo $es_admin ? 5 : 3; ?>, 'desc']]
        });

        // Calcular días solo cuando ambas fechas estén completas
        $('#fecha_inicio, #fecha_fin').on('change', function() {
            const inicio = $('#fecha_inicio').val();
            const fin = $('#fecha_fin').val();
            
            if (inicio && fin) {
                calcularDias();
            }
        });
    });

    function calcularDias() {
        const inicio = new Date($('#fecha_inicio').val());
        const fin = new Date($('#fecha_fin').val());
        
        if (inicio && fin) {
            const diferencia = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;
            $('#dias_calculados').val(diferencia);
        }
    }

    function validarFechas() {
        const inicio = new Date($('#fecha_inicio').val());
        const fin = new Date($('#fecha_fin').val());
        const dias = parseInt($('#dias_calculados').val() || 0);

        if (!inicio || !fin) {
            Swal.fire('Error', 'Por favor seleccione ambas fechas', 'error');
            return false;
        }

        if (inicio > fin) {
            Swal.fire('Error', 'La fecha de fin debe ser posterior a la fecha de inicio', 'error');
            return false;
        }

        if (dias > 31) {
            Swal.fire('Error', 'El período de vacaciones no puede exceder los 31 días', 'error');
            return false;
        }

        return true;
    }

    function mostrarModalAsignar() {
        $('#vacacion_id').val('');
        $('#formVacaciones').trigger('reset');
        $('#modalVacacionesTitle').text('Asignar Vacaciones');
        $('#modalVacaciones').modal('show');
    }

    function mostrarModalSolicitar() {
        $('#vacacion_id').val('');
        $('#formVacaciones').trigger('reset');
        $('#modalVacacionesTitle').text('Solicitar Vacaciones');
        $('#modalVacaciones').modal('show');
    }

    function guardarVacaciones() {
        if (!validarFechas()) {
            return;
        }

        <?php if ($es_admin): ?>
        const usuario_id = $('#usuario_id').val();
        if (!usuario_id) {
            Swal.fire('Error', 'Por favor seleccione un empleado', 'error');
            return;
        }
        <?php endif; ?>

        const formData = {
            id: $('#vacacion_id').val(),
            usuario_id: <?php echo $es_admin ? "$('#usuario_id').val()" : $usuario_id; ?>,
            fecha_inicio: $('#fecha_inicio').val(),
            fecha_fin: $('#fecha_fin').val(),
            comentarios: $('#comentarios').val()
        };

        $.ajax({
            url: 'procesar_vacaciones.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.mensaje,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error || 'Error al procesar la solicitud', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });

        $('#modalVacaciones').modal('hide');
    }

    function procesarVacaciones(data) {
        $('#solicitud_id').val(data.id);
        $('#solicitud_usuario_id').val(data.usuario_id);
        
        const fechaInicio = new Date(data.fecha_inicio);
        const fechaFin = new Date(data.fecha_fin);
        
        $('#periodo_vacaciones').text(
            fechaInicio.toLocaleDateString('es-ES') + ' al ' + 
            fechaFin.toLocaleDateString('es-ES')
        );
        $('#total_dias').text(data.dias + ' días');

        // Obtener días aprobados para este usuario
        $.ajax({
            url: 'obtener_dias_aprobados.php',
            method: 'GET',
            data: {
                usuario_id: data.usuario_id,
                anio: fechaInicio.getFullYear()
            },
            success: function(response) {
                if (response.success) {
                    const diasAprobados = response.dias_aprobados;
                    const totalDias = diasAprobados + data.dias;
                    
                    $('#dias_disponibles').html(
                        `<strong>Días aprobados este año:</strong> ${diasAprobados}<br>` +
                        `<strong>Días de esta solicitud:</strong> ${data.dias}<br>` +
                        `<strong>Total:</strong> ${totalDias}/31 días<br>` +
                        (totalDias > 31 ? 
                            '<div class="alert alert-danger mt-2">¡Advertencia! Esta aprobación excedería el límite de 31 días anuales.</div>' : 
                            '')
                    );
                }
            }
        });

        $('#modalProcesar').modal('show');
    }

    function guardarProceso() {
        const formData = {
            id: $('#solicitud_id').val(),
            usuario_id: $('#solicitud_usuario_id').val(),
            estado: $('#estado').val(),
            comentarios: $('#comentarios_proceso').val()
        };

        $.ajax({
            url: 'procesar_solicitud_vacaciones.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.mensaje,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.error || 'Error al procesar la solicitud', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });

        $('#modalProcesar').modal('hide');
    }

    function editarVacaciones(datos) {
        $('#vacacion_id').val(datos.id);
        $('#usuario_id').val(datos.usuario_id);
        $('#fecha_inicio').val(datos.fecha_inicio);
        $('#fecha_fin').val(datos.fecha_fin);
        calcularDias();
        
        $('#modalVacacionesTitle').text('Editar Vacaciones');
        $('#modalVacaciones').modal('show');
    }

    function eliminarVacaciones(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'eliminar_vacaciones.php',
                    method: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: response.mensaje,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.error || 'Error al eliminar', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error de conexión', 'error');
                    }
                });
            }
        });
    }

    function cancelarSolicitud(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea cancelar esta solicitud de vacaciones?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'cancelar_vacaciones.php',
                    method: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Cancelado!',
                                text: response.mensaje,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.error || 'Error al cancelar', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error de conexión', 'error');
                    }
                });
            }
        });
    }
    </script>
</body>
</html> 