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
</head>
<body class="bg-light">
	<?php include("../../includes/header.php"); ?>

<div class="main-card">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-dark">Gestión de Pacientes</h2>
            <p class="text-muted small">Listado de pacientes registrados en el sistema.</p>
        </div>
        <button class="btn btn-primary d-flex align-items-center" onclick="nuevoPaciente()">
            <i data-lucide="user-plus" class="me-2" style="width: 18px;"></i>
            Nuevo Paciente
        </button>
    </div>

    <table id="tablaPacientes" class="table w-100">
        <thead>
            <tr>
                <th>DNI</th>
                <th>Nombre Completo</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<div class="modal fade" id="modalPaciente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold px-3 pt-3" id="modalTitulo">Datos del Paciente</h5>
                <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPaciente">
                <div class="modal-body p-4">
                    <input type="hidden" id="id_paciente" name="id">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">DNI</label>
                            <input type="text" id="dni" name="dni" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Apellido</label>
                            <input type="text" id="apellido" name="apellido" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Fecha de Nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Cobertura Médica</label>
                            <select id="paciente_os_id" name="obra_social_id" class="form-select" required>
                                </select>
                            <div id="info_pago" class="form-text text-muted small mt-1 italic">
                                Si la obra social no figura, seleccione "Particular".
                            </div>
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
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Paciente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem;">
            <div class="modal-header bg-primary text-white border-0 py-4" style="border-radius: 1.5rem 1.5rem 0 0;">
                <div class="d-flex align-items-center px-3">
                    <div class="bg-white p-2 rounded-circle me-3 text-primary">
                        <i data-lucide="user" style="width: 30px; height: 30px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="ficha_nombre_full">Nombre del Paciente</h5>
                        <span class="badge bg-white text-primary rounded-pill mt-1" id="ficha_dni">DNI: 00.000.000</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-6">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Fecha Nacimiento</label>
                        <div class="d-flex align-items-center">
                            <i data-lucide="calendar" class="text-primary me-2" style="width:16px;"></i>
                            <span id="ficha_nacimiento" class="fw-medium">--/--/----</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Obra Social</label>
                        <div class="d-flex align-items-center">
                            <i data-lucide="heart" class="text-danger me-2" style="width:16px;"></i>
                            <span id="ficha_obrasocial" class="fw-medium">Particular</span>
                        </div>
                    </div>
                    <div class="col-12 border-top pt-3">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Información de Contacto</label>
                        <div class="mb-2">
                            <i data-lucide="phone" class="text-success me-2" style="width:16px;"></i>
                            <span id="ficha_telefono">---</span>
                        </div>
                        <div>
                            <i data-lucide="mail" class="text-warning me-2" style="width:16px;"></i>
                            <span id="ficha_email">---</span>
                        </div>
                    </div>
                    <div class="col-12 border-top pt-3">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Registrado el</label>
                        <div class="text-muted small" id="ficha_registro">----</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>

<script>
$(document).ready(function() {
    const tabla = $('#tablaPacientes').DataTable({
        "ajax": { "url": "acciones.php?accion=listar", "type": "POST" },
        "columns": [
            { "data": "dni" },
            { "data": "nombre_completo" },
            { "data": "telefono" },
            { "data": "email" },
            { "data": "acciones", "className": "text-end" }
        ],
        "drawCallback": function() { lucide.createIcons(); },
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" }
    });

    // --- FUNCIÓN PARA CARGAR EL SELECT DE OBRAS SOCIALES ---
    window.cargarObrasSocialesPacientes = function(id_seleccionada = 0) {
        $.get('acciones.php?accion=get_os_activas', function(res) {
            if (res.status) {
                let html = '<option value="0">Particular / Sin Cobertura</option>';
                res.data.forEach(os => {
                    let selected = (id_seleccionada == os.id) ? 'selected' : '';
                    html += `<option value="${os.id}" ${selected}>${os.nombre}</option>`;
                });
                $('#paciente_os_id').html(html);
            } else {
                $('#paciente_os_id').html('<option value="0">Particular / Sin Cobertura</option>');
            }
        }, 'json');
    }

    window.nuevoPaciente = function() {
        $('#formPaciente')[0].reset();
        $('#id_paciente').val('');
        $('#modalTitulo').text('Nuevo Paciente');
        
        // LLAMADA IMPORTANTE: Cargar las OS al abrir para nuevo
        cargarObrasSocialesPacientes(0);
        
        new bootstrap.Modal($('#modalPaciente')).show();
    }

    window.editar = function(id) {
        $.post('acciones.php?accion=leer_uno', {id: id}, function(res) {
            if(res.status) {
                $('#id_paciente').val(id);
                $('#dni').val(res.data.dni);
                $('#nombre').val(res.data.nombre);
                $('#apellido').val(res.data.apellido);
                $('#fecha_nacimiento').val(res.data.fecha_nacimiento);
                $('#telefono').val(res.data.telefono);
                $('#email').val(res.data.email);
                
                // LLAMADA IMPORTANTE: Cargar las OS y seleccionar la del paciente
                cargarObrasSocialesPacientes(res.data.obra_social_id);
                
                $('#modalTitulo').text('Editar Paciente');
                new bootstrap.Modal($('#modalPaciente')).show();
            }
        }, 'json');
    }

    $('#formPaciente').on('submit', function(e) {
        e.preventDefault();
        $.post('acciones.php?accion=guardar', $(this).serialize(), function(res) {
            if(res.status) {
                bootstrap.Modal.getInstance($('#modalPaciente')).hide();
                tabla.ajax.reload(null, false);
                Swal.fire('Éxito', res.message, 'success');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    });

    window.eliminar = function(id) {
        Swal.fire({
            title: '¿Eliminar paciente?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, borrar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('acciones.php?accion=eliminar', {id: id}, function(res) {
                    tabla.ajax.reload(null, false);
                    Swal.fire('Eliminado', res.message, 'success');
                }, 'json');
            }
        });
    }

    window.verFicha = function(id) {
        $.post('acciones.php?accion=leer_uno', {id: id}, function(res) {
            if(res.status) {
                const p = res.data;
                $('#ficha_nombre_full').text(p.apellido.toUpperCase() + ', ' + p.nombre);
                $('#ficha_dni').text('DNI: ' + p.dni);
                $('#ficha_nacimiento').text(p.fecha_nacimiento ? p.fecha_nacimiento.split('-').reverse().join('/') : 'No cargada');
                
                // Aquí usamos el nombre de la OS que debería venir del JOIN en leer_uno
                $('#ficha_obrasocial').text(p.obra_social_nombre || 'Particular');
                
                $('#ficha_telefono').text(p.telefono || 'Sin teléfono');
                $('#ficha_email').text(p.email || 'Sin email');
                $('#ficha_registro').text(new Date(p.created_at).toLocaleString());

                const modalFicha = new bootstrap.Modal(document.getElementById('modalFicha'));
                modalFicha.show();
                setTimeout(() => { lucide.createIcons(); }, 150);
            }
        }, 'json');
    }
});
</script>