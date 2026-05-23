<?php 
    require_once '../../../core/config.php';
    require_once '../../../core/funciones.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Otorgar Turno | Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <?php include("../../includes/header.php"); ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-lg" style="border-radius: 1.5rem;">
                    
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold mb-0">Solicitud de Turno</h4>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3" id="badge-paso">Paso 1 de 3</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div id="barra-progreso" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 33%;"></div>
                        </div>
                        
                        <div id="alerta_cobertura" style="display:none;" class="mt-3">
                            <div class="alert alert-warning d-flex align-items-center shadow-sm mb-0">
                                <i data-lucide="alert-triangle" class="me-2"></i>
                                <small id="texto_alerta_os"></small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form id="formTurnero">
                            
                            <div id="paso1" class="seccion-paso">
                                <h5 class="fw-bold mb-3"><i data-lucide="user-search" class="me-2 text-primary"></i>Identificación del Paciente</h5>
                                <div class="bg-light p-4 rounded-4 mb-4">
                                    <label class="form-label fw-bold">Número de DNI</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" class="form-control border-0 shadow-none" id="dni_paciente" placeholder="Ej: 35123456" required>
                                        <button class="btn btn-primary px-4" type="button" id="btnVerificarDNI">
                                            Verificar <i data-lucide="chevron-right" class="ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                                <div id="campos_paciente_nuevo" class="row g-3" style="display:none;">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nombre</label>
                                        <input type="text" id="nuevo_p_nombre" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Apellido</label>
                                        <input type="text" id="nuevo_p_apellido" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Teléfono</label>
                                        <input type="text" id="nuevo_p_tel" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Obra Social</label>
                                        <select id="nuevo_p_os" class="form-select">
                                            <option value="">PARTICULAR / NINGUNA</option>
                                            <?php
                                            $os_query = $pdo->query("SELECT id, nombre FROM obras_sociales ORDER BY nombre ASC");
                                            while($os = $os_query->fetch()) {
                                                echo "<option value='{$os['id']}'>{$os['nombre']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div id="paciente_encontrado" style="display:none;" class="alert alert-success border-0 rounded-4">
                                    <div class="d-flex align-items-center">
                                        <i data-lucide="check-circle" class="me-3" style="width:32px; height:32px;"></i>
                                        <div>
                                            <h6 class="mb-0 fw-bold" id="txt_paciente_nombre"></h6>
                                            <small>Paciente registrado en el sistema.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="paso2" class="seccion-paso" style="display:none;">
                                <h5 class="fw-bold mb-3"><i data-lucide="stethoscope" class="me-2 text-primary"></i>¿Con quién desea atenderse?</h5>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Seleccione Especialidad</label>
                                    <select id="select_especialidad" class="form-select form-select-lg shadow-none rounded-3">
                                        <option value="">Elegir...</option>
                                        <?php
                                        $esp = $pdo->query("SELECT * FROM especialidades ORDER BY nombre ASC");
                                        while($e = $esp->fetch()) echo "<option value='{$e['id']}'>{$e['nombre']}</option>";
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Seleccione Profesional</label>
                                    <select id="select_medico" class="form-select form-select-lg shadow-none rounded-3" disabled>
                                        <option value="">Primero elija especialidad</option>
                                    </select>
                                </div>
                            </div>

                            <div id="paso3" class="seccion-paso" style="display:none;">
                                <h5 class="fw-bold mb-3"><i data-lucide="calendar-check" class="me-2 text-primary"></i>Selección de Fecha y Hora</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div id="calendario_interactivo"></div>
                                        <input type="hidden" id="fecha_final" name="fecha">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Horarios Disponibles</label>
                                        <div id="contenedor_slots" class="d-flex flex-wrap gap-2 pt-2">
                                            <p class="text-muted small">Seleccione una fecha para ver horarios.</p>
                                        </div>
                                        <input type="hidden" id="hora_final" name="hora">
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>

                    <div class="card-footer bg-light border-0 p-4 d-flex justify-content-between">
                        <button type="button" id="btnVolver" class="btn btn-outline-secondary px-4 rounded-pill fw-bold" style="visibility:hidden;">
                            <i data-lucide="arrow-left" class="me-1" style="width:18px;"></i> Volver
                        </button>
                        <button type="button" id="btnSiguiente" class="btn btn-primary px-5 rounded-pill fw-bold" disabled>
                            Siguiente <i data-lucide="arrow-right" class="ms-1" style="width:18px;"></i>
                        </button>
                        <button type="button" id="btnFinalizar" class="btn btn-success px-5 rounded-pill fw-bold" style="display:none;">
                            Confirmar Turno <i data-lucide="check" class="ms-1" style="width:18px;"></i>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

<?php include("../../includes/footer.php"); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        lucide.createIcons();
        let pasoActual = 1;
        let pacienteId = null; 
        let obraSocialPacienteExistente = null; // Variable clave para pacientes registrados

        // 1. VERIFICAR DNI
        $('#btnVerificarDNI').on('click', function() {
            const dni = $('#dni_paciente').val();
            if (dni.length < 7) {
                Swal.fire('Atención', 'Ingresa un DNI válido', 'warning');
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.post('acciones.php?accion=validar_paciente', { dni: dni }, function(res) {
                btn.prop('disabled', false).html('Verificar <i data-lucide="chevron-right"></i>');
                lucide.createIcons();

                if (res.existe) {
                    pacienteId = res.data.id;
                    // IMPORTANTE: Asegúrate de que tu PHP envíe 'obra_social_id' en el JSON
                    obraSocialPacienteExistente = res.data.obra_social_id; 
                    
                    $('#txt_paciente_nombre').text(res.data.nombre + ' ' + res.data.apellido);
                    $('#paciente_encontrado').fadeIn();
                    $('#campos_paciente_nuevo').hide(); 
                    habilitarSiguiente(true);
                } else {
                    pacienteId = null;
                    obraSocialPacienteExistente = null;
                    $('#paciente_encontrado').hide();
                    $('#campos_paciente_nuevo').slideDown();
                    $('#nuevo_p_nombre, #nuevo_p_apellido, #nuevo_p_tel').val('');
                    habilitarSiguiente(true);
                }
                validarCoberturaMedico(); // Validar por si ya había un médico seleccionado
            }, 'json');
        });

        // 2. FILTRAR MÉDICOS Y VALIDAR COBERTURA
        $('#select_especialidad').on('change', function() {
            const esp_id = $(this).val();
            const selectMed = $('#select_medico');

            if (esp_id) {
                selectMed.prop('disabled', false).html('<option value="">Cargando...</option>');
                $.get('acciones.php?accion=get_medicos_especialidad', { especialidad_id: esp_id }, function(res) {
                    let options = '<option value="">Seleccione Profesional...</option>';
                    if (res.status && res.data.length > 0) {
                        res.data.forEach(m => {
                            options += `<option value="${m.id}">${m.apellido}, ${m.nombre}</option>`;
                        });
                    }
                    selectMed.html(options);
                }, 'json');
            }
        });

        $('#select_medico').on('change', function() {
            habilitarSiguiente($(this).val() !== "");
            validarCoberturaMedico(); // <--- CRUCIAL: Se ejecuta al elegir médico
        });

        $('#nuevo_p_os').on('change', function() {
            validarCoberturaMedico(); // <--- CRUCIAL: Se ejecuta al elegir OS paciente nuevo
        });

        // 3. FUNCIÓN MAESTRA DE VALIDACIÓN DE COBERTURA
        function validarCoberturaMedico() {
            const medico_id = $('#select_medico').val();
            // Si el paciente existe, usamos su OS de la DB. Si no, la del Select.
            let os_id = (pacienteId !== null) ? obraSocialPacienteExistente : $('#nuevo_p_os').val();

            if (!os_id || !medico_id) {
                $('#alerta_cobertura').fadeOut();
                return;
            }

            $.post('acciones.php?accion=validar_cobertura_medico', { medico_id, os_id }, function(res) {
                if (!res.atiende) {
                    $('#texto_alerta_os').html('El profesional <b>no atiende</b> por esta cobertura. El turno será <b>Particular</b>.');
                    $('#alerta_cobertura').fadeIn();
                } else {
                    $('#alerta_cobertura').fadeOut();
                }
            }, 'json');
        }

        // 4. NAVEGACIÓN
        $('#btnSiguiente').on('click', function() {
            if (pasoActual < 3) cambiarPaso(pasoActual + 1);
        });

        $('#btnVolver').on('click', function() {
            if (pasoActual > 1) cambiarPaso(pasoActual - 1);
        });

        function cambiarPaso(n) {
            $(`#paso${pasoActual}`).hide();
            $(`#paso${n}`).fadeIn();
            pasoActual = n;

            $('#badge-paso').text(`Paso ${n} de 3`);
            $('#barra-progreso').css('width', (n === 1 ? 33 : (n === 2 ? 66 : 100)) + '%');
            $('#btnVolver').css('visibility', n === 1 ? 'hidden' : 'visible');
            
            if (n === 3) {
                $('#btnSiguiente').hide();
                $('#btnFinalizar').show();
                prepararCalendario();
            } else {
                $('#btnSiguiente').show();
                $('#btnFinalizar').hide();
            }
        }

        function habilitarSiguiente(estado) {
            $('#btnSiguiente').prop('disabled', !estado);
        }

        // 5. CALENDARIO Y SLOTS
        let fp;
        function prepararCalendario() {
            const medico_id = $('#select_medico').val();
            fp = flatpickr("#calendario_interactivo", {
                inline: true, locale: "es", minDate: "today", dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr) {
                    $('#fecha_final').val(dateStr);
                    cargarChipsHoras(medico_id, dateStr);
                }
            });
        }

        function cargarChipsHoras(medico_id, fecha) {
            const contenedor = $('#contenedor_slots');
            contenedor.html('<div class="spinner-border spinner-border-sm text-primary"></div>');
            $.post('acciones.php?accion=get_disponibilidad', { medico_id, fecha }, function(res) {
                try {
                    let limpia = res.substring(res.lastIndexOf('{"'));
                    let data = JSON.parse(limpia);
                    if (data.status && data.slots.length > 0) {
                        let html = '';
                        data.slots.forEach(h => {
                            html += `<button type="button" class="btn btn-outline-primary btn-hora-chip m-1 fw-bold" data-hora="${h}">${h}</button>`;
                        });
                        contenedor.html(html);
                    } else {
                        contenedor.html('<small class="text-muted">No hay horarios.</small>');
                    }
                } catch(e) { contenedor.html('Error.'); }
            }, 'text');
        }

        $(document).on('click', '.btn-hora-chip', function() {
            $('.btn-hora-chip').removeClass('btn-primary text-white').addClass('btn-outline-primary');
            $(this).removeClass('btn-outline-primary').addClass('btn-primary text-white');
            $('#hora_final').val($(this).data('hora'));
            $('#btnFinalizar').prop('disabled', false);
        });

        // 6. FINALIZAR
        $('#btnFinalizar').on('click', function() {
            // Definimos si el paciente es nuevo antes de armar el objeto
            const esNuevo = (pacienteId === null);

            let osParaEnviar = esNuevo ? $('#nuevo_p_os').val() : obraSocialPacienteExistente;

            const datos = {
                paciente_id: pacienteId,
                es_nuevo: esNuevo,
                dni: $('#dni_paciente').val(),
                nombre: $('#nuevo_p_nombre').val(),
                apellido: $('#nuevo_p_apellido').val(),
                tel: $('#nuevo_p_tel').val(),
                obra_social_id: osParaEnviar, // <--- Este dato ahora está asegurado
                medico_id: $('#select_medico').val(),
                fecha: $('#fecha_final').val(),
                hora: $('#hora_final').val()
            };

            // Validación extra por seguridad
            if (esNuevo && (!datos.nombre || !datos.apellido)) {
                Swal.fire('Atención', 'Debes completar nombre y apellido para el nuevo paciente', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Confirmar Turno?',
                text: `Se agendará el turno para el paciente ${esNuevo ? datos.nombre : ''}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, agendar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostramos un cargando para evitar clics dobles
                    Swal.showLoading();
                    
                    $.post('acciones.php?accion=confirmar_turno', datos, function(res) {
                        if(res.status) {
                            Swal.fire('¡Éxito!', res.message, 'success').then(() => {
                                window.location.href = "index.php";
                            });
                        } else {
                            Swal.fire('Error de Sistema', res.message, 'error');
                        }
                    }, 'json').fail(function() {
                        Swal.fire('Error', 'No se pudo comunicar con el servidor', 'error');
                    });
                }
            });
        });
    });
</script>
</body>
</html>