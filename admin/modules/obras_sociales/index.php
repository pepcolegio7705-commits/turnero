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
                <h2 class="fw-bold mb-0 text-dark">Obras Sociales</h2>
                <p class="text-muted small">Administra los convenios y valores de coseguro.</p>
            </div>
            <button class="btn btn-primary d-flex align-items-center" onclick="nuevaOS()">
                <i data-lucide="plus" class="me-2" style="width: 18px;"></i>
                Nueva Obra Social
            </button>
        </div>

        <table id="tablaOS" class="table w-100">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Coseguro Estándar</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="modal fade" id="modalOS" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold px-3 pt-3" id="modalTitulo">Datos de la Obra Social</h5>
                    <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="modal"></button>
                </div>
                <form id="formOS">
                    <div class="modal-body p-4">
                        <input type="hidden" id="id_os" name="id">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold">Monto Coseguro ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="coseguro_estandar" name="coseguro_estandar" class="form-control" step="0.01" value="0.00">
                            </div>
                            <small class="text-muted">Valor predeterminado que se cobrará en el turno.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include("../../includes/footer.php"); ?>

<script>
    $(document).ready(function() {
        const tabla = $('#tablaOS').DataTable({
            "ajax": { "url": "acciones.php?accion=listar", "type": "POST" },
            "columns": [
                { "data": "nombre" },
                { "data": "coseguro" },
                { "data": "estado" },
                { "data": "acciones", "className": "text-end" }
            ],
            "drawCallback": function() { lucide.createIcons(); },
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" }
        });

        window.nuevaOS = function() {
            $('#formOS')[0].reset();
            $('#id_os').val('');
            $('#modalTitulo').text('Nueva Obra Social');
            new bootstrap.Modal($('#modalOS')).show();
        }

        window.editar = function(id) {
            $.post('acciones.php?accion=leer_uno', {id: id}, function(res) {
                if(res.status) {
                    $('#id_os').val(id);
                    $('#nombre').val(res.data.nombre);
                    $('#coseguro_estandar').val(res.data.coseguro_estandar);
                    $('#modalTitulo').text('Editar Obra Social');
                    new bootstrap.Modal($('#modalOS')).show();
                }
            });
        }

        $('#formOS').on('submit', function(e) {
            e.preventDefault();
            $.post('acciones.php?accion=guardar', $(this).serialize(), function(res) {
                if(res.status) {
                    bootstrap.Modal.getInstance($('#modalOS')).hide();
                    tabla.ajax.reload();
                    Swal.fire('Éxito', res.message, 'success');
                }
            });
        });

        window.cambiarEstado = function(id, estadoActual) {
            const titulo = estadoActual === 1 ? '¿Suspender Obra Social?' : '¿Reactivar Obra Social?';
            const texto = estadoActual === 1 ? 'Ya no aparecerá disponible para nuevos turnos ni médicos.' : 'Volverá a aparecer en las listas de selección.';
            const btnColor = estadoActual === 1 ? '#ef4444' : '#22c55e';

            Swal.fire({
                title: titulo,
                text: texto,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: btnColor,
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('acciones.php?accion=estado', { id: id }, function(res) {
                        if (res.status) {
                            $('#tablaOS').DataTable().ajax.reload(null, false);
                            Swal.fire('Actualizado', res.message, 'success');
                        }
                    }, 'json');
                }
            });
        }
    });
</script>