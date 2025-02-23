<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - MoliMepi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <style>
        .notification-card {
            margin-bottom: 1rem;
            border-left: 4px solid #007bff;
        }
        .notification-card.read {
            border-left-color: #6c757d;
            background-color: #f8f9fa;
        }
        .notification-meta {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .notification-comment {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
        }
        .badge-notification {
            font-size: 0.8rem;
            padding: 0.25em 0.6em;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once '../config/database.php';

    // Verificar que el usuario está autenticado
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];
    $es_admin = $_SESSION['rol'] === 'Administrador';

    // Consulta SQL según el rol
    if ($es_admin) {
        $sql = "SELECT n.ID, n.mensaje, n.tipo, n.estado, n.comentario, n.fecha_creacion, 
                       u.nombre as nombre_usuario, u.ID as usuario_id,
                       CASE 
                           WHEN n.tipo = 'solicitud_password' THEN 'Solicitud de Contraseña'
                           WHEN n.tipo = 'respuesta_password' THEN 'Respuesta de Solicitud'
                           ELSE n.tipo 
                       END as tipo_formato
                FROM notificaciones n 
                LEFT JOIN usuarios u ON n.usuario_id = u.ID 
                ORDER BY n.fecha_creacion DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    } else {
        $sql = "SELECT n.ID, n.mensaje, n.tipo, n.estado, n.comentario, n.fecha_creacion,
                       CASE 
                           WHEN n.tipo = 'solicitud_password' THEN 'Solicitud de Contraseña'
                           WHEN n.tipo = 'respuesta_password' THEN 'Respuesta de Solicitud'
                           ELSE n.tipo 
                       END as tipo_formato
                FROM notificaciones n 
                WHERE n.usuario_id = :usuario_id 
                ORDER BY n.fecha_creacion DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id]);
    }

    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>
            <?php echo $es_admin ? 'Gestión de Notificaciones' : 'Mis Notificaciones'; ?>
        </h2>
        <div class="table-responsive">
            <table id="tabla-notificaciones" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <?php if ($es_admin): ?>
                            <th>Usuario</th>
                        <?php endif; ?>
                        <th>Tipo</th>
                        <th>Mensaje</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notificaciones as $row): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?></td>
                            <?php if ($es_admin): ?>
                                <td><?php echo htmlspecialchars($row['nombre_usuario']); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($row['tipo_formato']); ?></td>
                            <td>
                                <?php 
                                $mensaje_corto = strlen($row['mensaje']) > 50 
                                    ? substr($row['mensaje'], 0, 47) . '...' 
                                    : $row['mensaje'];
                                echo htmlspecialchars($mensaje_corto);
                                ?>
                            </td>
                            <td>
                                <?php if ($row['estado']): ?>
                                    <span class="badge bg-<?php 
                                        echo $row['estado'] === 'Aprobada' ? 'success' : 
                                            ($row['estado'] === 'Rechazada' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo htmlspecialchars($row['estado']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($es_admin && $row['tipo'] === 'solicitud_password' && empty($row['estado'])): ?>
                                    <div class="btn-group">
                                        <button type="button" 
                                                class="btn btn-success btn-sm" 
                                                data-id="<?php echo $row['ID']; ?>"
                                                data-mensaje="<?php echo htmlspecialchars($row['mensaje']); ?>"
                                                onclick="mostrarModalConfirmacion(this, true)">
                                            <i class="fas fa-check"></i> Aprobar
                                        </button>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm" 
                                                data-id="<?php echo $row['ID']; ?>"
                                                data-mensaje="<?php echo htmlspecialchars($row['mensaje']); ?>"
                                                onclick="mostrarModalConfirmacion(this, false)">
                                            <i class="fas fa-times"></i> Rechazar
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <button type="button" 
                                            class="btn btn-info btn-sm" 
                                            data-id="<?php echo $row['ID']; ?>"
                                            data-tipo="<?php echo htmlspecialchars($row['tipo']); ?>"
                                            data-mensaje="<?php echo htmlspecialchars($row['mensaje']); ?>"
                                            data-comentario="<?php echo htmlspecialchars($row['comentario'] ?? ''); ?>"
                                            data-estado="<?php echo htmlspecialchars($row['estado'] ?? ''); ?>"
                                            onclick="verDetalleNotificacion(this)">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para detalles de notificación -->
    <div class="modal fade" id="detalleNotificacionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles de la Notificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- El contenido se carga dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#tabla-notificaciones').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
            });
        });

        function mostrarModalConfirmacion(btn, aprobar) {
            const id = btn.getAttribute('data-id');
            const mensaje = btn.getAttribute('data-mensaje');
            
            if (!id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo obtener el ID de la notificación'
                });
                return;
            }

            Swal.fire({
                title: aprobar ? '¿Aprobar solicitud?' : '¿Rechazar solicitud?',
                html: `
                    <div class="mb-3">
                        <strong>Mensaje:</strong>
                        <p>${mensaje}</p>
                    </div>
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Comentario:</label>
                        <textarea class="form-control" id="comentario" rows="3"></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: aprobar ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: aprobar ? 'Sí, aprobar' : 'Sí, rechazar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const comentario = document.getElementById('comentario').value.trim();
                    if (!comentario) {
                        Swal.showValidationMessage('Por favor ingrese un comentario');
                        return false;
                    }
                    return comentario;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    procesarSolicitudPassword(id, aprobar, result.value);
                }
            });
        }

        function procesarSolicitudPassword(id, aprobar, comentario) {
            $.ajax({
                url: 'procesar_solicitud_password.php',
                method: 'POST',
                data: {
                    id: id,
                    aprobar: aprobar,
                    comentario: comentario
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.mensaje
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Error al procesar la solicitud'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al comunicarse con el servidor'
                    });
                }
            });
        }

        function verDetalleNotificacion(btn) {
            const id = btn.getAttribute('data-id');
            const tipo = btn.getAttribute('data-tipo');
            const mensaje = btn.getAttribute('data-mensaje');
            const comentario = btn.getAttribute('data-comentario');
            const estado = btn.getAttribute('data-estado');

            let contenidoModal = `
                <div class="mb-3">
                    <strong>Mensaje:</strong>
                    <p>${mensaje}</p>
                </div>`;

            if (comentario) {
                contenidoModal += `
                    <div class="mb-3">
                        <strong>Comentario:</strong>
                        <p>${comentario}</p>
                    </div>`;
            }

            if (estado) {
                contenidoModal += `
                    <div class="mb-3">
                        <strong>Estado:</strong>
                        <p>${estado}</p>
                    </div>`;
            }

            $('#detalleNotificacionModal .modal-body').html(contenidoModal);
            $('#detalleNotificacionModal').modal('show');

            if (!estado || estado === 'Pendiente') {
                marcarComoLeida(id);
            }
        }

        function marcarComoLeida(id) {
            $.ajax({
                url: 'marcar_notificacion_leida.php',
                method: 'POST',
                data: { id: id },
                success: function(response) {
                    if (response.success) {
                        $('#tabla-notificaciones').DataTable().ajax.reload(null, false);
                    }
                }
            });
        }
    </script>
</body>
</html> 