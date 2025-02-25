<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
require_once 'head.php';
require_once 'navbar.php';

verificarAcceso();

// Obtener el mes seleccionado (por defecto el mes actual)
$mes_seleccionado = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio_seleccionado = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

// Obtener lista de usuarios
$stmt = $pdo->query("SELECT u.ID, u.nombre, u.cargo, u.imagen FROM usuarios u ORDER BY u.nombre");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Dashboard - Listado de Personal</h2>
        </div>
        <div class="col-md-6 d-flex justify-content-end">
            <form id="filtroMes" class="form-inline mr-2">
                <label class="mr-2">Filtro Mes:</label>
                <select class="form-control mr-2" name="mes" id="mes">
                    <?php
                    $meses = [
                        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
                        '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
                        '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
                        '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
                    ];
                    foreach ($meses as $num => $nombre) {
                        $selected = $mes_seleccionado == $num ? 'selected' : '';
                        echo "<option value='$num' $selected>$nombre</option>";
                    }
                    ?>
                </select>
                <select class="form-control mr-2" name="anio" id="anio">
                    <?php
                    $anio_actual = date('Y');
                    for ($i = $anio_actual - 1; $i <= $anio_actual + 1; $i++) {
                        $selected = $anio_seleccionado == $i ? 'selected' : '';
                        echo "<option value='$i' $selected>$i</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
            <button id="exportarExcel" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar a Excel
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td>
                                <img src="<?php 
                                    if (!empty($usuario['imagen'])) {
                                        // Si la imagen ya tiene el prefijo 'imgs/', no lo añadimos de nuevo
                                        if (strpos($usuario['imagen'], 'imgs/') === 0) {
                                            echo $usuario['imagen'];
                                        } else if (strpos($usuario['imagen'], 'public/imgs/') === 0) {
                                            // Si tiene el prefijo 'public/imgs/', lo reemplazamos por 'imgs/'
                                            echo str_replace('public/imgs/', 'imgs/', $usuario['imagen']);
                                        } else {
                                            // En cualquier otro caso, asumimos que necesita el prefijo 'imgs/'
                                            echo 'imgs/' . basename($usuario['imagen']);
                                        }
                                    } else {
                                        echo 'imgs/nofoto.png';
                                    }
                                ?>" 
                                     class="rounded-circle" 
                                     alt="Foto de perfil" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['cargo']); ?></td>
                            <td>
                                <button type="button" 
                                        class="btn btn-info btn-sm" 
                                        onclick="verDetallesUsuario(<?php echo $usuario['ID']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div class="modal fade" id="modalDetalles" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <img id="modalUserImage" src="" alt="Foto de perfil" 
                         class="rounded-circle mr-3" 
                         style="width: 60px; height: 60px; object-fit: cover;">
                    <div>
                        <h5 class="modal-title mb-0" id="modalUserName"></h5>
                        <p class="text-muted mb-0" id="modalUserCargo"></p>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-tabs" id="userDetailsTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="horario-tab" data-toggle="tab" href="#horario" role="tab">
                                    Horario
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="vacaciones-tab" data-toggle="tab" href="#vacaciones" role="tab">
                                    Vacaciones
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="horasExtra-tab" data-toggle="tab" href="#horasExtra" role="tab">
                                    Horas Extra
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content mt-3" id="userDetailsContent">
                            <div class="tab-pane fade show active" id="horario" role="tabpanel">
                                <div id="horarioContent"></div>
                            </div>
                            <div class="tab-pane fade" id="vacaciones" role="tabpanel">
                                <div id="vacacionesContent"></div>
                            </div>
                            <div class="tab-pane fade" id="horasExtra" role="tabpanel">
                                <div id="horasExtraContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Manejar cambio de filtro
    $('#filtroMes').on('submit', function(e) {
        e.preventDefault();
        const mes = $('#mes').val();
        const anio = $('#anio').val();
        window.location.href = `dashboard.php?mes=${mes}&anio=${anio}`;
    });

    // Asegurar que el modal se pueda cerrar correctamente
    // Compatibilidad con Bootstrap 4 y 5
    $('#modalDetalles').on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"], .close', function() {
        $('#modalDetalles').modal('hide');
    });
    
    // Exportar a Excel
    $('#exportarExcel').on('click', function() {
        const mes = $('#mes').val();
        const anio = $('#anio').val();
        window.location.href = `exportar_excel.php?mes=${mes}&anio=${anio}`;
    });
});

function verDetallesUsuario(userId) {
    console.log('Obteniendo detalles para usuario:', userId);
    
    // Limpiar contenido previo para evitar datos antiguos
    $('#horarioContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
    $('#vacacionesContent').html('');
    $('#horasExtraContent').html('');
    
    // Mostrar el modal inmediatamente para mejor experiencia de usuario
    $('#modalDetalles').modal('show');
    
    // Obtener datos del usuario
    $.ajax({
        url: 'obtener_detalles_usuario.php',
        method: 'GET',
        data: {
            usuario_id: userId,
            mes: $('#mes').val(),
            anio: $('#anio').val()
        },
        success: function(response) {
            console.log('Respuesta recibida:', response);
            if (response.success) {
                const data = response.data;
                
                // Actualizar información básica
                $('#modalUserImage').attr('src', data.imagen.startsWith('imgs/') ? data.imagen : 'imgs/' + data.imagen);
                $('#modalUserName').text(data.nombre);
                $('#modalUserCargo').text(data.cargo);

                // Actualizar horario
                let horarioHTML = `
                    <table class="table table-bordered text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Día</th>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Total Horas</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                // Crear un mapa de horarios para búsqueda rápida
                const horarioMap = {};
                data.horario.forEach(h => {
                    horarioMap[h.dia_numero] = h;
                });

                // Obtener el número de días en el mes seleccionado
                const mes = $('#mes').val();
                const anio = $('#anio').val();
                const diasEnMes = new Date(anio, mes, 0).getDate();

                // Días de la semana
                const diasSemana = [
                    'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'
                ];

                // Variables para calcular total de horas
                let totalHorasMes = 0;

                // Generar filas para cada día del mes
                for (let dia = 1; dia <= diasEnMes; dia++) {
                    const fecha = new Date(anio, mes - 1, dia);
                    const diaSemana = diasSemana[fecha.getDay()];
                    const horarioDia = horarioMap[dia] || null;

                    // Calcular horas del día
                    let horasDia = 0;
                    let horarioTexto = '-';
                    if (horarioDia && horarioDia.tipo !== 'descanso') {
                        const [horaEntrada, horaSalida] = horarioDia.horario.split(' - ');
                        if (horaEntrada && horaSalida) {
                            const entrada = new Date(`2000-01-01T${horaEntrada}`);
                            const salida = new Date(`2000-01-01T${horaSalida}`);
                            horasDia = (salida - entrada) / (1000 * 60 * 60);
                            horarioTexto = horarioDia.horario;
                            totalHorasMes += horasDia;
                        }
                    }

                    horarioHTML += `
                        <tr>
                            <td>${diaSemana}</td>
                            <td>${dia.toString().padStart(2, '0')}/${mes.padStart(2, '0')}/${anio}</td>
                            <td>
                                ${horarioDia ? 
                                    (horarioDia.tipo === 'descanso' ? 
                                        'DESCANSO' : 
                                        horarioTexto) : 
                                    '-'
                                }
                            </td>
                            <td>${horarioDia && horarioDia.tipo !== 'descanso' ? horasDia.toFixed(2) : '-'} hrs</td>
                        </tr>`;
                }

                // Añadir fila de total
                horarioHTML += `
                    <tr class="table-active">
                        <td colspan="3"><strong>Total Horas Trabajadas</strong></td>
                        <td><strong>${totalHorasMes.toFixed(2)} hrs</strong></td>
                    </tr>`;

                horarioHTML += `</tbody></table>`;
                $('#horarioContent').html(horarioHTML);

                // Actualizar vacaciones
                let vacacionesHTML = `
                    <table class="table table-bordered text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Período</th>
                                <th>Días</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                if (data.vacaciones.length > 0) {
                    let totalDiasVacaciones = 0;
                    data.vacaciones.forEach(v => {
                        const fechaInicio = new Date(v.fecha_inicio.split('/').reverse().join('-'));
                        const fechaFin = new Date(v.fecha_fin.split('/').reverse().join('-'));
                        const diasVacaciones = Math.ceil((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1;
                        totalDiasVacaciones += diasVacaciones;

                        vacacionesHTML += `
                            <tr>
                                <td>${v.fecha_inicio} - ${v.fecha_fin}</td>
                                <td>${diasVacaciones}</td>
                            </tr>`;
                    });

                    // Añadir fila de total
                    vacacionesHTML += `
                        <tr class="table-active">
                            <td><strong>Total</strong></td>
                            <td><strong>${totalDiasVacaciones}</strong></td>
                        </tr>`;
                } else {
                    vacacionesHTML += `
                        <tr>
                            <td colspan="2">Sin vacaciones aprobadas</td>
                        </tr>`;
                }
                vacacionesHTML += `</tbody></table>`;
                $('#vacacionesContent').html(vacacionesHTML);

                // Actualizar horas extra
                let horasExtraHTML = `
                    <table class="table table-bordered text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Día</th>
                                <th>Fecha</th>
                                <th>Horas Extra</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                // Procesar horas extra
                const horasExtraDesglose = data.horas_extra.detalle.split('; ').filter(h => h.trim() !== '');
                
                if (horasExtraDesglose.length > 0) {
                    horasExtraDesglose.forEach(horaExtra => {
                        const [fechaStr, horasStr] = horaExtra.split(': ');
                        const horas = parseFloat(horasStr.replace(' hrs', ''));
                        const fecha = new Date(fechaStr.split('/').reverse().join('-'));
                        const diaSemana = diasSemana[fecha.getDay()];

                        horasExtraHTML += `
                            <tr>
                                <td>${diaSemana}</td>
                                <td>${fechaStr}</td>
                                <td>${horas} hrs</td>
                            </tr>`;
                    });

                    // Añadir fila de total
                    horasExtraHTML += `
                        <tr class="table-active">
                            <td colspan="2"><strong>Total</strong></td>
                            <td><strong>${data.horas_extra.total} hrs</strong></td>
                        </tr>`;
                } else {
                    horasExtraHTML += `
                        <tr>
                            <td colspan="3">Sin horas extra</td>
                        </tr>`;
                }
                horasExtraHTML += `</tbody></table>`;
                $('#horasExtraContent').html(horasExtraHTML);
            } else {
                Swal.fire('Error', 'No se pudieron cargar los detalles del usuario', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la petición:', error);
            console.error('Estado:', status);
            console.error('Respuesta:', xhr.responseText);
            Swal.fire('Error', 'Error al conectar con el servidor', 'error');
        }
    });
}
</script>