<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/turnero/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/turnero/core/funciones.php';
// Aquí iría la validación de sesión más adelante
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Especialidades - CMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-light">

<?php include("../../includes/header.php"); ?>

<div class="container mt-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-primary">Especialidades Médicas</h5>
            <button class="btn btn-primary btn-sm rounded-3" onclick="nuevoModal()">
                <i data-lucide="plus" class="w-4 h-4"></i> Nueva Especialidad
            </button>
        </div>
        <div class="card-body">
            <table id="tablaEspecialidades" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEspecialidad" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEspecialidad">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Nueva Especialidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_especialidad" name="id">
                    <div class="mb-3">
                        <label class="form-label text-muted small uppercase">Nombre de la Especialidad</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include("../../includes/footer.php"); ?>
<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>-->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Configuración de DataTables Server-Side
    let tabla = $('#tablaEspecialidades').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "acciones.php?accion=listar",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "nombre" },
            { "data": "estado" },
            { "data": "acciones" }
        ],
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" }
    });

    function nuevoModal() {
        $('#formEspecialidad')[0].reset();
        $('#id_especialidad').val('');
        $('#modalTitulo').text('Nueva Especialidad');
        $('#modalEspecialidad').modal('show');
    }

    // Lógica para Guardar (AJAX)
    $('#formEspecialidad').on('submit', function(e){
        e.preventDefault();
        $.ajax({
            url: 'acciones.php?accion=guardar',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res){
                if(res.status){
                    $('#modalEspecialidad').modal('hide');
                    tabla.ajax.reload();
                    Swal.fire('Éxito', res.message, 'success');
                }
            }
        });
    });

    lucide.createIcons();

    // Función para cargar datos en el modal y editar
function editar(id_encriptado) {
    $.ajax({
        url: 'acciones.php?accion=leer_uno',
        type: 'POST',
        data: { id: id_encriptado },
        success: function(res) {
            if(res.status) {
                $('#id_especialidad').val(id_encriptado); // Guardamos el ID encriptado
                $('#nombre').val(res.data.nombre);
                $('#modalTitulo').text('Editar Especialidad');
                $('#modalEspecialidad').modal('show');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
}

// Función para eliminar con confirmación de SweetAlert2
function eliminar(id_encriptado) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'acciones.php?accion=eliminar',
                type: 'POST',
                data: { id: id_encriptado },
                success: function(res) {
                    if(res.status) {
                        Swal.fire('Eliminado', res.message, 'success');
                        tabla.ajax.reload();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        }
    });
}
</script>
</body>
</html>