<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

verificarAccesoAdmin();
$es_admin = true;
$usuario_id = obtenerIdUsuario();

// Consulta para obtener las horas extra
$sql = "SELECT 
            ra.id as registro_id,
            ra.fecha,
            ra.total_horas as horas_registradas,
            ht.horas_dia as horas_programadas,
            ROUND(ra.total_horas - ht.horas_dia, 2) as horas_extra,
            u.nombre,
            u.identificacion,
            u.ID as empleado_id,
            COALESCE(she.estado, 'Pendiente') as estado,
            COALESCE(she.horas_solicitadas, ROUND(ra.total_horas - ht.horas_dia, 2)) as horas_solicitadas,
            she.horas_aprobadas,
            she.comentarios,
            CASE 
                WHEN she.estado = 'Aprobado' THEN ua.nombre
                ELSE NULL 
            END as aprobado_por,
            she.aprobado_en
        FROM registro_asistencia ra
        INNER JOIN usuarios u ON ra.usuario_id = u.ID
        INNER JOIN horarios_trabajo ht ON ra.usuario_id = ht.usuario_id 
            AND DATE(ra.fecha) = DATE(ht.fecha)
            AND ht.tipo = 'normal'
        LEFT JOIN solicitudes_horas_extra she ON ra.usuario_id = she.usuario_id 
            AND DATE(ra.fecha) = DATE(she.fecha)
        LEFT JOIN usuarios ua ON she.aprobado_por = ua.ID
        WHERE ra.total_horas > ht.horas_dia
        AND ra.hora_salida IS NOT NULL
        AND NOT EXISTS (
            SELECT 1 FROM hrex_empleado he 
            WHERE he.usuario_id = ra.usuario_id 
            AND DATE(he.fecha) = DATE(ra.fecha)
        )
        ORDER BY ra.fecha DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<?php include 'head.php'; ?>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2><i class="fas fa-clock"></i> Gestión de Horas Extra</h2>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="tablaHorasExtra">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Empleado</th>
                                <th>Identificación</th>
                                <th>Horas Programadas</th>
                                <th>Horas Registradas</th>
                                <th>Horas Extra</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $registro): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></td>
                                    <td><?php echo htmlspecialchars($registro['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['identificacion']); ?></td>
                                    <td><?php echo number_format($registro['horas_programadas'], 2); ?></td>
                                    <td><?php echo number_format($registro['horas_registradas'], 2); ?></td>
                                    <td>
                                        <?php if ($registro['estado'] === 'Aprobado'): ?>
                                            <?php echo number_format($registro['horas_aprobadas'], 2); ?>
                                            <small class="text-muted">
                                                (Solicitadas: <?php echo number_format($registro['horas_solicitadas'], 2); ?>)
                                            </small>
                                        <?php else: ?>
                                            <?php echo number_format($registro['horas_solicitadas'], 2); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($registro['estado']) {
                                                'Aprobado' => 'success',
                                                'Rechazado' => 'danger',
                                                default => 'warning'
                                            };
                                        ?>">
                                            <?php echo $registro['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                    <?php if ($registro['estado'] === 'Pendiente'): ?>
                                        <button class="btn btn-success btn-sm" onclick="procesarHorasExtra(<?php 
                                            echo htmlspecialchars(json_encode([
                                                'registro_id' => $registro['registro_id'],
                                                'empleado_id' => $registro['empleado_id'],
                                                'fecha' => $registro['fecha'],
                                                'horas_solicitadas' => $registro['horas_solicitadas']
                                            ])); 
                                        ?>)">
                                            <i class="fas fa-check"></i> Procesar
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
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

    <!-- Modal para procesar horas extra -->
    <div class="modal fade" id="procesarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Procesar Horas Extra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <form id="formProcesar">
                        <input type="hidden" id="registro_id">
                        <input type="hidden" id="empleado_id">
                        <input type="hidden" id="fecha">
                        
                        <div class="mb-3">
                            <label for="horas_solicitadas" class="form-label">Horas Extra Solicitadas</label>
                            <input type="number" class="form-control" id="horas_solicitadas" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="horas_aprobadas" class="form-label">Horas Extra a Aprobar</label>
                            <input type="number" class="form-control" id="horas_aprobadas" step="0.01" min="0" required>
                            <small class="form-text text-muted">
                                Puede aprobar total o parcialmente las horas extra solicitadas
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Decisión</label>
                            <select class="form-control" id="estado" required>
                                <option value="Aprobado">Aprobar</option>
                                <option value="Rechazado">Rechazar</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="comentarios" class="form-label">Comentarios</label>
                            <textarea class="form-control" id="comentarios" rows="3" required></textarea>
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

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        $('#tablaHorasExtra').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[0, 'desc']]
        });

        // Validar que las horas aprobadas no excedan las solicitadas
        $('#horas_aprobadas').on('input', function() {
            const solicitadas = parseFloat($('#horas_solicitadas').val());
            const aprobadas = parseFloat(this.value);
            
            if (aprobadas > solicitadas) {
                this.value = solicitadas;
            }
        });

        // Cambiar comportamiento según estado
        $('#estado').on('change', function() {
            const esRechazo = this.value === 'Rechazado';
            $('#horas_aprobadas').prop('required', !esRechazo)
                               .prop('readonly', esRechazo)
                               .val(esRechazo ? 0 : '');
        });
    });

    function procesarHorasExtra(datos) {
        $('#registro_id').val(datos.registro_id);
        $('#empleado_id').val(datos.empleado_id);
        $('#fecha').val(datos.fecha);
        $('#horas_solicitadas').val(datos.horas_solicitadas);
        $('#horas_aprobadas').val(datos.horas_solicitadas);
        $('#comentarios').val('');
        $('#estado').val('Aprobado').trigger('change');
        
        new bootstrap.Modal(document.getElementById('procesarModal')).show();
    }

    function guardarProceso() {
        const formData = {
            registro_id: $('#registro_id').val(),
            empleado_id: $('#empleado_id').val(),
            fecha: $('#fecha').val(),
            horas_solicitadas: $('#horas_solicitadas').val(),
            horas_aprobadas: $('#horas_aprobadas').val(),
            estado: $('#estado').val(),
            comentarios: $('#comentarios').val()
        };

        if (!formData.comentarios) {
            Swal.fire('Error', 'Por favor ingrese un comentario', 'error');
            return;
        }

        if (formData.estado === 'Aprobado' && (!formData.horas_aprobadas || formData.horas_aprobadas <= 0)) {
            Swal.fire('Error', 'Debe especificar las horas a aprobar', 'error');
            return;
        }

        $.ajax({
            url: 'procesar_horas_extra.php',
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

        bootstrap.Modal.getInstance(document.getElementById('procesarModal')).hide();
    }
    </script>
</body>
</html> 