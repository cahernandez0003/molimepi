// Obtener la base URL del proyecto
const baseUrl = window.location.origin + '/molimepi/public';

function cargarNotificaciones() {
    $.ajax({
        url: `${window.baseUrl}/obtener_notificaciones.php`,
        method: 'GET',
        success: function(response) {
            if (!response.success) {
                console.error('Error en la respuesta:', response.error);
                return;
            }

            const notificaciones = response.notificaciones;
            const contador = response.no_leidas;
            
            // Actualizar contador
            $('#contador-notificaciones').text(contador);
            $('#contador-notificaciones').toggle(contador > 0);

            // Actualizar lista de notificaciones
            const $lista = $('#lista-notificaciones');
            $lista.empty();

            if (notificaciones.length === 0) {
                $lista.append('<div class="dropdown-item text-center">No hay notificaciones</div>');
            } else {
                notificaciones.forEach(notif => {
                    const leida = notif.leida ? '' : 'font-weight-bold bg-light';
                    const fecha = new Date(notif.fecha_creacion).toLocaleString();
                    let url = '#';
                    
                    // Determinar la URL basada en el tipo de notificación
                    if (notif.tipo === 'solicitud_password') {
                        url = `${window.baseUrl}/solicitudes_password.php`;
                    }

                    $lista.append(`
                        <a href="${url}" class="dropdown-item ${leida}" data-id="${notif.ID}">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">${fecha}</small>
                                ${!notif.leida ? '<span class="badge badge-primary">Nueva</span>' : ''}
                            </div>
                            <p class="mb-0">${notif.mensaje}</p>
                        </a>
                        <div class="dropdown-divider"></div>
                    `);
                });

                // Agregar eventos click a las notificaciones
                $lista.find('.dropdown-item').on('click', function(e) {
                    const id = $(this).data('id');
                    marcarLeida(id);
                    if ($(this).attr('href') === '#') {
                        e.preventDefault();
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar notificaciones:', error);
            $('#contador-notificaciones').hide();
            $('#lista-notificaciones').html('<div class="dropdown-item text-center text-danger">Error al cargar notificaciones</div>');
        }
    });
}

function marcarLeida(id) {
    $.ajax({
        url: `${window.baseUrl}/marcar_notificacion_leida.php`,
        method: 'POST',
        data: { id: id },
        success: function(response) {
            if (response.success) {
                cargarNotificaciones();
            } else {
                console.error('Error al marcar como leída:', response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al marcar notificación como leída:', error);
        }
    });
}

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    // Asegurarse de que los dropdowns funcionen
    if ($.fn.dropdown) {
        $('.dropdown-toggle').dropdown();
    }
    
    // Iniciar sistema de notificaciones
    cargarNotificaciones();
    setInterval(cargarNotificaciones, 30000);
}); 