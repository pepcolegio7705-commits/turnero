<?php
// Mantenemos el inicio de sesión y la configuración
session_start();
require_once '../../../core/config.php';

// Protección de Rol: Solo Admin entra aquí
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../../login.php"); // O un mensaje de error
    exit;
}

// Consulta de usuarios: Solo traemos los que no están eliminados definitivamente (opcional)
// O traemos todos para que el admin pueda reactivarlos si lo desea.
$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY created_at DESC");
$usuarios = $stmt->fetchAll();

// El header debe ir DESPUÉS de cualquier lógica de redirección (header location)
include("../../includes/header.php"); 
?>

<div class="main-card animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Gestión de Usuarios</h3>
            <p class="text-muted small">Control de acceso al sistema Sintek-Salud</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario">
            <i data-lucide="user-plus" class="me-2" style="width:18px;"></i> Nuevo Usuario
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="tablaUsuarios">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre Real</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $u): ?>
                <tr>
                    <td><span class="text-muted small">#<?php echo $u['id']; ?></span></td>
                    <td class="fw-bold"><?php echo $u['usuario']; ?></td>
                    <td><?php echo $u['nombre']; ?></td>
                    <td>
                        <span class="badge <?php echo $u['rol'] == 'admin' ? 'bg-dark' : 'bg-light text-dark border'; ?>">
                            <?php echo strtoupper($u['rol']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if($u['estado'] == 1): ?>
                            <span class="badge bg-success-subtle text-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <!-- Botón Editar -->
                        <button class="btn btn-sm btn-outline-primary border-0" 
                                onclick='prepararEdicion(<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES, "UTF-8"); ?>)'>
                            <i data-lucide="edit-3" style="width:16px;"></i>
                        </button>

                        <!-- Botón Eliminar (Desactivar) -->
                        <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                            <button class="btn btn-sm btn-outline-danger border-0" 
                                    onclick="eliminarUsuario(<?php echo $u['id']; ?>, '<?php echo addslashes($u['nombre']); ?>')">
                                <i data-lucide="user-x" style="width:16px;"></i>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary border-0" disabled title="No puedes desactivarte a ti mismo">
                                <i data-lucide="lock" style="width:16px;"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Nuevo Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="formUsuario">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Registrar Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre de Usuario (Login)</label>
                        <input type="text" name="usuario" class="form-control" placeholder="jperez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Rol</label>
                            <select name="rol" class="form-select">
                                <option value="recepcion">Recepción</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="formEditarUsuario">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre de Usuario</label>
                        <input type="text" name="usuario" id="edit_usuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                        <div class="form-text text-primary" style="font-size: 10px;">Si no deseas cambiarla, deja este campo vacío.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Rol</label>
                            <select name="rol" id="edit_rol" class="form-select">
                                <option value="recepcion">Recepción</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Estado</label>
                            <select name="estado" id="edit_estado" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Inicializar Iconos
    lucide.createIcons();
    const BASE_URL = '<?php echo BASE_URL; ?>';

    // 1. Procesar Formulario de Registro
    // 1. Procesar Formulario de Registro
    document.getElementById('formUsuario').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Resultado: http://localhost/turnero/admin/usuarios/guardar
        fetch(`${BASE_URL}admin/usuarios/guardar`, {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                Swal.fire('¡Éxito!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    });
    
    function prepararEdicion(datos) {
        console.log("Datos para editar:", datos); // Si esto no sale en consola, el botón no llama a la función

        // Llenar campos
        document.getElementById('edit_id').value = datos.id;
        document.getElementById('edit_nombre').value = datos.nombre;
        document.getElementById('edit_usuario').value = datos.usuario;
        document.getElementById('edit_rol').value = datos.rol;
        document.getElementById('edit_estado').value = datos.estado;
        
        // Intentar levantar el modal
        const elModal = document.getElementById('modalEditarUsuario');
        if (elModal) {
            const myModal = new bootstrap.Modal(elModal);
            myModal.show();
        } else {
            console.error("No se encontró el elemento HTML del modal");
        }
    }
    // 3. Procesar la Actualización
    document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
        e.preventDefault(); // CRITICO: Esto evita que la página se recargue sola
        
        console.log("Enviando actualización..."); // Debug para ver si entra aquí

        const formData = new FormData(this);

        fetch(`${BASE_URL}admin/usuarios/actualizar`, {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('Error en la red o 404');
            return res.json();
        })
        .then(data => {
            console.log("Respuesta recibida:", data);
            if(data.status === 'success') {
                Swal.fire('¡Actualizado!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error en el fetch:', error);
            Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
        });
    });

    // 4. Confirmación de Desactivación
    function eliminarUsuario(id, nombre) {
        Swal.fire({
            title: '¿Desactivar?',
            text: `El usuario ${nombre} será desactivado.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desactivar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Resultado: http://localhost/turnero/admin/usuarios/eliminar?id=...
                fetch(`${BASE_URL}admin/usuarios/eliminar?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        Swal.fire('Hecho', data.message, 'success').then(() => location.reload());
                    }
                });
            }
        });
    }
</script>