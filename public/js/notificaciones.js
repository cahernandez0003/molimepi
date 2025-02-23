// Obtener la base URL del proyecto
const baseUrl = window.location.origin + '/molimepi/public';

function cargarNotificaciones() {
    $.ajax({
        url: 'obtener_notificaciones.php',
        method: 'GET',
        success: function(response) {
            if (!response || typeof response !== 'object') {
                console.error('Respuesta inválida del servidor');
                return;
            }

            const notificaciones = response.notificaciones || [];
            const contadorNoLeidas = response.no_leidas || 0;
            
            // Actualizar contador
            const contadorElement = $('#contador-notificaciones');
            if (contadorNoLeidas > 0) {
                contadorElement.text(contadorNoLeidas).show();
            } else {
                contadorElement.hide();
            }
            
            // Limpiar y actualizar lista de notificaciones
            const listaNotificaciones = $('#notificacionesLista');
            if (!listaNotificaciones.length) {
                return; // El elemento no existe en esta página
            }
            
            listaNotificaciones.empty();
            
            if (notificaciones.length === 0) {
                listaNotificaciones.append(`
                    <div class="dropdown-item text-muted text-center">
                        No hay notificaciones nuevas
                    </div>
                `);
            } else {
                notificaciones.forEach(notif => {
                    if (!notif) return; // Skip if notification is undefined
                    
                    const fecha = new Date(notif.fecha_creacion).toLocaleString();
                    const leida = notif.leida == 1;
                    const itemClass = leida ? '' : 'bg-light fw-bold';
                    
                    listaNotificaciones.append(`
                        <div class="dropdown-item ${itemClass}">
                            <small class="text-muted float-end">${fecha}</small>
                            <p class="mb-0">${notif.mensaje || ''}</p>
                            ${notif.comentario ? `<small class="text-muted">Comentario: ${notif.comentario}</small>` : ''}
                        </div>
                    `);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar notificaciones:', error);
        }
    });
}

function marcarLeida(id) {
    if (!id) return Promise.resolve(); // Return empty promise if no id

    return $.ajax({
        url: 'marcar_notificacion_leida.php',
        method: 'POST',
        data: { id: id },
        success: function(response) {
            if (response && response.success) {
                cargarNotificaciones();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al marcar notificación como leída:', error);
        }
    });
}

// Cargar notificaciones al inicio
$(document).ready(function() {
    // Solo inicializar notificaciones si existe el dropdown
    if ($('#notificacionesDropdown').length) {
        cargarNotificaciones();
        
        // Actualizar cada 30 segundos
        setInterval(cargarNotificaciones, 30000);
        
        // Marcar como leídas al abrir el dropdown
        $('#notificacionesDropdown').on('show.bs.dropdown', function() {
            const notificaciones = $('#notificacionesLista .dropdown-item.bg-light');
            if (notificaciones.length > 0) {
                notificaciones.each(function() {
                    const id = $(this).data('id');
                    if (id) {
                        marcarLeida(id);
                    }
                });
            }
        });
    }
}); 