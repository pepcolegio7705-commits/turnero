
<?php 
    require_once '../../../core/config.php';
    require_once '../../../core/funciones.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración de Horarios | Panel Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <?php include("../../includes/header.php"); ?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h2 class="fw-bold text-dark mb-1">
                <i data-lucide="clock" class="text-primary me-2"></i>Agenda de Profesionales
            </h2>
            <p class="text-muted">Configure los bloques de tiempo para la reserva de turnos.</p>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-3">
                <label class="form-label small fw-bold text-primary">1. Seleccione un Médico:</label>
                <select id="select_medico_horario" class="form-select border-primary-subtle shadow-none">
                    <option value="">Elija un profesional...</option>
                    <?php
                    $stmt = $pdo->query("SELECT id, nombre, apellido FROM medicos WHERE estado = 1 ORDER BY apellido ASC");
                    while($m = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$m['id']}'>{$m['apellido']}, {$m['nombre']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <div id="contenedor_horarios" style="display: none;">
        <div class="card shadow-sm border-0 overflow-hidden" style="border-radius: 1rem;">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="m-0 fw-bold"><i data-lucide="calendar-range" class="me-2 text-primary"></i>Configuración para: <span id="nombre_medico_titulo" class="text-primary"></span></h5>
            </div>
            <div class="card-body p-0">
                <form id="formHorarios">
                    <input type="hidden" name="medico_id" id="horario_medico_id">
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Día de la Semana</th>
                                    <th>Estado</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                    <th>Duración del Turno</th>
                                    <th class="pe-4">Acciones Rápidas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
                                foreach ($dias as $idx => $dia):
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary"><?= $dia ?></td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input switch-dia" type="checkbox" name="activo[<?= $idx ?>]" value="1" id="check_<?= $idx ?>">
                                            <label class="form-check-label small" for="check_<?= $idx ?>">Atiende</label>
                                        </div>
                                    </td>
                                    <td><input type="time" name="inicio[<?= $idx ?>]" class="form-control form-control-sm input-horario shadow-none" disabled></td>
                                    <td><input type="time" name="fin[<?= $idx ?>]" class="form-control form-control-sm input-horario shadow-none" disabled></td>
                                    <td>
                                        <select name="duracion[<?= $idx ?>]" class="form-select form-select-sm input-horario shadow-none" disabled>
                                            <option value="15">15 minutos</option>
                                            <option value="20" selected>20 minutos</option>
                                            <option value="30">30 minutos</option>
                                            <option value="45">45 minutos</option>
                                            <option value="60">1 hora</option>
                                        </select>
                                    </td>
                                    <td class="pe-4">
                                        <button type="button" class="btn btn-sm btn-outline-secondary border-0 btn-copy" title="Copiar a todos los días" disabled>
                                            <i data-lucide="copy" style="width:16px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-white p-4 border-top-0 text-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm">
                            <i data-lucide="save" class="me-2" style="width:18px;"></i>Guardar Configuración Semanal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include("../../includes/footer.php"); ?>
<script>
$(document).ready(function() {
    lucide.createIcons();

    // 1. Manejo del Cambio de Médico
    $('#select_medico_horario').on('change', function() {
        const id = $(this).val();
        const nombre = $("#select_medico_horario option:selected").text();
        if(id) {
            $('#horario_medico_id').val(id);
            $('#nombre_medico_titulo').text(nombre);
            // La limpieza ocurre dentro de esta función ahora:
            cargarHorarios(id); 
            $('#contenedor_horarios').slideDown();
        } else {
            $('#contenedor_horarios').slideUp();
        }
    });

    // 2. Switches de Habilitación
    $(document).on('change', '.switch-dia', function() {
        const row = $(this).closest('tr');
        const activo = $(this).is(':checked');
        row.find('.input-horario, .btn-copy').prop('disabled', !activo);
    });

    // 3. Función para Cargar Datos de la DB
    // 3. Función para Cargar Datos de la DB (CORREGIDA)
    function cargarHorarios(id) {
        // --- PASO DE LIMPIEZA TOTAL ---
        // 1. Apagamos todos los switches
        $('.switch-dia').prop('checked', false);
        // 2. Deshabilitamos todos los inputs de hora y select de duración
        $('.input-horario, .btn-copy').prop('disabled', true);
        // 3. Limpiamos los valores de las horas (ponemos vacíos)
        $('.input-horario').val('');
        // 4. Resetear la duración a un valor por defecto (ej: 20 min)
        $('select.input-horario').val('20');
        
        // Ahora procedemos a buscar los datos reales del médico seleccionado
        $.get('acciones.php?accion=get_horarios', {id: id}, function(res) {
            if(res.status && res.data.length > 0) {
                res.data.forEach(h => {
                    // Buscamos la fila correspondiente al día de la semana (h.dia_semana)
                    let row = $(`input[name="activo[${h.dia_semana}]"]`).closest('tr');
                    
                    // Marcamos como activo y disparamos el evento 'change' 
                    // para que se habiliten los inputs de esa fila
                    row.find('.switch-dia').prop('checked', true).trigger('change');
                    
                    // Cargamos los valores específicos
                    row.find(`input[name="inicio[${h.dia_semana}]"]`).val(h.hora_inicio);
                    row.find(`input[name="fin[${h.dia_semana}]"]`).val(h.hora_fin);
                    row.find(`select[name="duracion[${h.dia_semana}]"]`).val(h.duracion_turno);
                });
            }
        }, 'json');
    }

    // 4. Guardar Configuración
    $('#formHorarios').on('submit', function(e) {
        e.preventDefault();
        $.post('acciones.php?accion=guardar_horarios', $(this).serialize(), function(res) {
            if(res.status) {
                Swal.fire('¡Éxito!', 'Los horarios se actualizaron correctamente.', 'success');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    });

    // Bonus: Copiar horario a otros días activos
    $('.btn-copy').on('click', function() {
        const row = $(this).closest('tr');
        const inicio = row.find('input[type="time"]').first().val();
        const fin = row.find('input[type="time"]').last().val();
        const dur = row.find('select').val();

        $('.switch-dia:checked').each(function() {
            const targetRow = $(this).closest('tr');
            targetRow.find('input[type="time"]').first().val(inicio);
            targetRow.find('input[type="time"]').last().val(fin);
            targetRow.find('select').val(dur);
        });
    });
});
</script>

</body>
</html>