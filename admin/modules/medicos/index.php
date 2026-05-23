<?php 
require_once '../../../core/config.php';
require_once '../../../core/funciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Médicos - CMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        #contenedor_os::-webkit-scrollbar { width: 6px; }
        #contenedor_os::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        #contenedor_os .form-check:hover { background-color: #f8fafc; border-radius: 4px; }
    </style>
</head>
<body class="bg-light">
<?php include("../../includes/header.php"); ?>
<div class="main-card">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-dark">Gestión de Profesionales</h2>
            <p class="text-muted small">Administra la nómina de médicos y sus datos de contacto.</p>
        </div>
        <button class="btn btn-primary d-flex align-items-center" onclick="nuevoMedico()">
            <i data-lucide="plus" class="me-2" style="width: 18px;"></i>
            Agregar Médico
        </button>
    </div>

    <div class="table-responsive">
        <table id="tablaMedicos" class="table w-100">
            <thead>
                <tr>
                    <th>Matrícula</th>
                    <th>Nombre Completo</th>
                    <th>Especialidad</th>
                    <th>Consultorio</th> <!-- Mantenemos la columna -->
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal Formulario -->
<div class="modal fade" id="modalMedico" tabindex="-1" aria-hidden="true">
    <form id="formMedico">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalTitulo">Nuevo Profesional</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="id_medico" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Apellido</label>
                            <input type="text" id="apellido" name="apellido" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">DNI (Único)</label>
                            <input type="text" id="dni" name="dni" class="form-control" required placeholder="Sin puntos">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Matrícula</label>
                            <input type="text" id="matricula" name="matricula" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" id="email" name="email" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Especialidad</label>
                        <select id="especialidad_id" name="especialidad_id" class="form-select" required></select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Valor Consulta ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="valor_consulta" id="valor_consulta" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">N° Consultorio</label>
                            <div class="input-group">
                                <span class="input-group-text"><i data-lucide="door-open" style="width:14px;"></i></span>
                                <input type="text" name="consultorio" id="consultorio" class="form-control" placeholder="Ej: A-101" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Contraseña de Acceso</label>
                        <input type="password" name="password" id="medico_password" class="form-control" placeholder="Asignar o cambiar contraseña">
                    </div>

                    <div class="col-12 mt-3">
                        <label class="form-label small fw-bold text-primary d-flex align-items-center">
                            <i data-lucide="shield-check" class="me-1" style="width:16px;"></i> 
                            Obras Sociales Aceptadas
                        </label>
                        <div class="border rounded p-3 bg-light" style="max-height: 150px; overflow-y: auto;" id="contenedor_os">
                            <!-- Obras sociales dinámicas -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Profesional</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Ficha Técnica (Vista Ojo) -->
<div class="modal fade" id="modalFichaMedico" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem;">
            <div class="modal-header bg-dark text-white border-0 py-4" style="border-radius: 1.5rem 1.5rem 0 0;">
                <div class="d-flex align-items-center px-3">
                    <div class="bg-primary p-2 rounded-circle me-3 text-white">
                        <i data-lucide="stethoscope" style="width: 30px; height: 30px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="ficha_medico_nombre">Dr. Nombre Apellido</h5>
                        <span class="badge bg-primary rounded-pill mt-1" id="ficha_medico_especialidad">Especialidad</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-6">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Matrícula</label>
                        <span id="ficha_medico_matricula" class="fw-bold text-dark">-------</span>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Consultorio</label>
                        <span id="ficha_medico_consultorio" class="badge bg-info text-dark">---</span>
                    </div>
                    <div class="col-12 border-top pt-3">
                        <div class="mb-2"><i data-lucide="phone" class="text-success me-2" style="width:16px;"></i> <span id="ficha_medico_telefono">---</span></div>
                        <div class="mb-3"><i data-lucide="mail" class="text-warning me-2" style="width:16px;"></i> <span id="ficha_medico_email">---</span></div>
                        <div class="p-3 rounded-4 bg-light d-flex align-items-center justify-content-between">
                            <span class="small fw-bold text-muted text-uppercase">Consulta Particular</span>
                            <span id="ficha_medico_valor" class="h5 fw-bold text-dark mb-0">$ 0.00</span>
                        </div>
                    </div>
                    <div class="col-12 border-top pt-3">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-2">Obras Sociales Aceptadas</label>
                        <div id="ficha_medico_obras" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        let tabla = $('#tablaMedicos').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": { "url": "acciones.php?accion=listar", "type": "POST" },
            "columns": [
                { "data": "matricula" },
                { "data": "nombre_completo" },
                { "data": "especialidad" },
                { "data": "consultorio" }, // Asegúrate que acciones.php lo devuelva
                { "data": "estado" },
                { "data": "acciones" }
            ],
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" },
            "drawCallback": function() { if (typeof lucide !== 'undefined') lucide.createIcons(); }
        });

        // Cargar Especialidades
        window.cargarEspecialidades = function(id_seleccionado = null) {
            $.get('acciones.php?accion=get_especialidades', function(res) {
                if (res.status) {
                    let html = '<option value="">Seleccione...</option>';
                    res.data.forEach(esp => {
                        let selected = (id_seleccionado == esp.id) ? 'selected' : '';
                        html += `<option value="${esp.id}" ${selected}>${esp.nombre}</option>`;
                    });
                    $('#especialidad_id').html(html);
                }
            });
        }

        // Cargar Checks Obras Sociales
        window.cargarChecksObrasSociales = function(medico_id = null) {
            $('#contenedor_os').html('<p class="text-muted small">Cargando...</p>');
            $.post('acciones.php?accion=listar_os_checks', { medico_id: medico_id }, function(res) {
                if (res.status) {
                    let html = '<div class="row px-2">';
                    res.data.forEach(os => {
                        const checked = os.asignada ? 'checked' : '';
                        html += `<div class="col-md-6 mb-2"><div class="form-check">
                            <input class="form-check-input" type="checkbox" name="os_asignadas[]" value="${os.id}" id="os_${os.id}" ${checked}>
                            <label class="form-check-label small" for="os_${os.id}">${os.nombre}</label>
                        </div></div>`;
                    });
                    html += '</div>';
                    $('#contenedor_os').html(html);
                }
            }, 'json');
        }

        window.nuevoMedico = function() {
            $('#formMedico')[0].reset();
            $('#id_medico').val('');
            $('#modalTitulo').text('Registrar Nuevo Profesional');
            cargarEspecialidades();
            cargarChecksObrasSociales(null);
            new bootstrap.Modal('#modalMedico').show();
        }

        window.editar = function(id_encriptado) {
            $.post('acciones.php?accion=leer_uno', { id: id_encriptado }, function(res) {
                if (res.status) {
                    $('#id_medico').val(id_encriptado);
                    $('#nombre').val(res.data.nombre);
                    $('#apellido').val(res.data.apellido);
                    $('#dni').val(res.data.dni);
                    $('#matricula').val(res.data.matricula);
                    $('#telefono').val(res.data.telefono);
                    $('#email').val(res.data.email);
                    $('#valor_consulta').val(res.data.valor_consulta);
                    $('#consultorio').val(res.data.consultorio); // CARGA EL CONSULTORIO AQUÍ
                    
                    cargarEspecialidades(res.data.especialidad_id);
                    cargarChecksObrasSociales(id_encriptado);
                    
                    $('#modalTitulo').text('Editar Profesional');
                    new bootstrap.Modal('#modalMedico').show();
                }
            }, 'json');
        }

        $('#formMedico').on('submit', function(e) {
            e.preventDefault();
            $.post('acciones.php?accion=guardar', $(this).serialize(), function(res) {
                if (res.status) {
                    bootstrap.Modal.getInstance('#modalMedico').hide();
                    tabla.ajax.reload(null, false);
                    Swal.fire('¡Éxito!', res.message, 'success');
                } else {
                    Swal.fire('Atención', res.message, 'warning');
                }
            }, 'json');
        });
    });

    // Ficha Técnica
    window.verFichaMedico = function(id) {
        $.post('acciones.php?accion=leer_uno', { id: id }, function(res) {
            if (res.status) {
                const m = res.data;
                $('#ficha_medico_nombre').text((m.nombre + ' ' + m.apellido).toUpperCase());
                $('#ficha_medico_especialidad').text(m.nombre_especialidad);
                $('#ficha_medico_matricula').text(m.matricula);
                $('#ficha_medico_consultorio').text(m.consultorio || 'No asignado'); // MUESTRA EL CONSULTORIO
                $('#ficha_medico_telefono').text(m.telefono || 'No registrado');
                $('#ficha_medico_email').text(m.email || 'No registrado');
                
                let valor = parseFloat(m.valor_consulta || 0);
                $('#ficha_medico_valor').text('$ ' + valor.toLocaleString('es-AR', {minimumFractionDigits: 2}));

                let htmlObras = m.obras_nombres 
                    ? m.obras_nombres.split('|').map(n => `<span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-3 py-2">${n}</span>`).join('')
                    : '<span class="text-muted small">Particular solamente.</span>';
                $('#ficha_medico_obras').html(htmlObras);

                new bootstrap.Modal('#modalFichaMedico').show();
                setTimeout(() => lucide.createIcons(), 150);
            }
        }, 'json');
    };
</script>
</body>
</html>