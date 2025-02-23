<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

verificarAcceso();
$usuario_id = obtenerIdUsuario();

// Obtener el historial de asistencia
$sql = "SELECT ra.*, 
        DATE_FORMAT(ra.fecha, '%d/%m/%Y') as fecha_formateada,
        she.estado as estado_solicitud 
        FROM registro_asistencia ra 
        LEFT JOIN solicitudes_horas_extra she ON ra.usuario_id = she.usuario_id 
            AND DATE(ra.fecha) = DATE(she.fecha)
        WHERE ra.usuario_id = :usuario_id 
        ORDER BY ra.fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['usuario_id' => $usuario_id]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>Registro de Asistencia</h2>
        
        <div class="card mb-4">
            <div class="card-body">
                <form id="formAsistencia" class="row g-3">
                    <div class="col-md-4">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="hora_entrada" class="form-label">Hora de Entrada</label>
                        <input type="time" class="form-control" id="hora_entrada" name="hora_entrada" required>
                    </div>
                    <div class="col-md-4">
                        <label for="hora_salida" class="form-label">Hora de Salida</label>
                        <input type="time" class="form-control" id="hora_salida" name="hora_salida">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4>Historial de Asistencia</h4>
                <table id="tablaAsistencia" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Total Horas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registros as $registro): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($registro['fecha_formateada']); ?></td>
                                <td><?php echo htmlspecialchars($registro['hora_entrada']); ?></td>
                                <td><?php echo $registro['hora_salida'] ? htmlspecialchars($registro['hora_salida']) : '-'; ?></td>
                                <td><?php echo $registro['total_horas'] ? htmlspecialchars($registro['total_horas']) . ' hrs' : '-'; ?></td>
                                <td>
                                    <?php if (!in_array($registro['estado_solicitud'], ['Aprobado', 'Rechazado'])): ?>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="editarRegistro(<?php 
                                            echo htmlspecialchars(json_encode([
                                                'id' => $registro['id'],
                                                'fecha' => date('Y-m-d', strtotime($registro['fecha'])),
                                                'hora_entrada' => $registro['hora_entrada'],
                                                'hora_salida' => $registro['hora_salida']
                                            ])); 
                                        ?>)">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-<?php echo $registro['estado_solicitud'] === 'Aprobado' ? 'success' : 'danger'; ?>">
                                            <?php echo $registro['estado_solicitud']; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div class="modal fade" id="editarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Registro de Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEdicion" class="row g-3">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="col-12">
                            <label for="edit_fecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="edit_fecha" name="fecha" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_hora_entrada" class="form-label">Hora de Entrada</label>
                            <input type="time" class="form-control" id="edit_hora_entrada" name="hora_entrada" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_hora_salida" class="form-label">Hora de Salida</label>
                            <input type="time" class="form-control" id="edit_hora_salida" name="hora_salida">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarEdicion()">Guardar Cambios</button>
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
        // Inicializar DataTable
        $('#tablaAsistencia').DataTable({
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            }
        });

        // Manejar envío del formulario
        $('#formAsistencia').on('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            
            $.ajax({
                url: 'procesar_asistencia.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
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
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la solicitud'
                    });
                }
            });
        });
    });

    // Función para abrir el modal de edición
    function editarRegistro(registro) {
        $('#edit_id').val(registro.id);
        $('#edit_fecha').val(registro.fecha);
        $('#edit_hora_entrada').val(registro.hora_entrada);
        $('#edit_hora_salida').val(registro.hora_salida);
        
        new bootstrap.Modal(document.getElementById('editarModal')).show();
    }

    // Función para guardar la edición
    function guardarEdicion() {
        let formData = new FormData(document.getElementById('formEdicion'));
        
        $.ajax({
            url: 'procesar_asistencia.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al procesar la solicitud'
                });
            }
        });
    }
    </script>
</body>
</html> 