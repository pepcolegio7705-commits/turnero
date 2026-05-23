<?php 
    require_once '../../../core/Database.php';
    require_once '../../../core/funciones.php';
    require_once '../../../core/config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Turnos - Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        :root { --bs-primary: #0d6efd; }
        .card-turno { transition: all 0.2s; }
        .rounded-4 { border-radius: 1rem !important; }
        
        /* Badges de Estado */
        .badge-estado { 
            padding: 0.6em 1.2em; 
            border-radius: 50px; 
            font-weight: 600; 
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .bg-pendiente { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .bg-espera { background-color: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; }
        .bg-atendido { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .bg-ausente { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .bg-cancelado { background-color: #e2e3e5; color: #41464b; border: 1px solid #d3d3d4; }
        
        .avatar-info {
            width: 40px; height: 40px;
            background: #f0f2f5;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">

<?php include("../../includes/header.php"); ?>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="fw-bold mb-0"><i class="bi bi-calendar-check text-primary me-2"></i>Gestión de Turnos</h2>
            <p class="text-muted mb-0 small text-uppercase fw-semibold">Control de Recepción y Sala de Espera</p>
        </div>
        <div class="col-auto">
            <a href="<?php echo $url_base; ?>turnos/" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                <i class="bi bi-plus-lg me-2"></i> Nuevo Turno
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form id="formFiltros" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted ps-2"><i class="bi bi-calendar3 me-1"></i> Fecha</label>
                    <input type="date" id="filtro_fecha" class="form-control border-0 bg-light rounded-3" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted ps-2"><i class="bi bi-person-badge me-1"></i> Profesional</label>
                    <select id="filtro_medico" class="form-select border-0 bg-light rounded-3">
                        <option value="">Todos los médicos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted ps-2"><i class="bi bi-funnel me-1"></i> Estado</label>
                    <select id="filtro_estado" class="form-select border-0 bg-light rounded-3">
                        <option value="" selected>Todos los estados</option>
                        <option value="Pendiente" selected>Solo Pendientes</option>
                        <option value="Espera">En Espera</option>
                        <option value="Atendido">Finalizados</option>
                        <option value="Ausente">Ausentes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" onclick="cargarTurnos()" class="btn btn-dark w-100 rounded-3 py-2 fw-bold">
                        <i class="bi bi-arrow-clockwise me-2"></i> Actualizar Listado
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!--<<div class="row g-3 mb-4" id="contenedor_resumen_caja">
    </div>-->

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-white border-bottom">
                    <tr>
                        <th class="ps-4 py-3 text-muted small">HORA</th>
                        <th class="text-muted small">PACIENTE</th>
                        <th class="text-muted small">MÉDICO / ESPECIALIDAD</th>
                        <th class="text-muted small">COBERTURA</th>
                        <th class="text-muted small">ESTADO</th>
                        <th class="pe-4 text-end text-muted small">ACCIONES</th>
                    </tr>
                </thead>
                <tbody id="tabla_turnos_body" class="bg-white">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCobro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Recepción de Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="info_cobro_paciente" class="p-3 rounded-4 bg-light mb-4 border border-dashed border-secondary"></div>

                <form id="formProcesarAtencion">
                    <input type="hidden" id="cobro_turno_id" name="turno_id">
                    <input type="hidden" id="valor_particular_medico">
                    <input type="hidden" id="valor_coseguro_os"> <div id="contenedor_opciones_pago">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">¿Cómo desea abonar la atención?</label>
                            <div class="row g-2" id="opciones_cobertura_dinamica"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Monto a Cobrar ($)</label>
                            <input type="number" step="0.01" class="form-control form-control-lg fw-bold text-success" id="monto_cobrado" name="monto_cobrado" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Medio de Pago</label>
                            <select class="form-select border-2" id="medio_pago" name="medio_pago">
                                <option value="Efectivo">Efectivo (Cash)</option>
                                <option value="Debito_OS" id="opt_debito_os">Débito Obra Social / Posnet</option>
                                <option value="Transferencia">Transferencia Bancaria</option>
                                <option value="Billetera_Virtual">Billetera Virtual (Mercado Pago, etc.)</option>
                                <option value="Debito_Automatico">Débito Automático</option>
                            </select>
                        </div>

                        <div class="mb-3" id="contenedor_nro_operacion" style="display: none;">
                            <label class="form-label small fw-bold text-primary">Número de Operación / Referencia</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white"><i class="bi bi-receipt"></i></span>
                                <input type="text" class="form-control border-primary" id="nro_operacion">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 mt-4">
                        <label class="form-label small fw-bold">Nota de Recepción (Opcional)</label>
                        <textarea id="obs_pago" name="observaciones" class="form-control rounded-3" rows="2" placeholder="Ej: Trae estudios previos..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow">
                        <i class="bi bi-person-check me-2"></i> Confirmar y Pasar a Espera
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Inicialización
    cargarTurnos();
    cargarMedicosFiltro();
    actualizarReporteCaja();
    lucide.createIcons();

    // --- 1. LÓGICA DE REPORTES ---
    function actualizarReporteCaja() {
        const fecha = $('#filtro_fecha').val();
        $.get('acciones.php?accion=reporte_diario_caja', { fecha: fecha }, function(res) {
            if(res.status) {
                let html = '';
                let totalGeneral = 0;
                res.data.forEach(item => {
                    const monto = parseFloat(item.total_recaudado) || 0;
                    totalGeneral += monto;
                    html += `
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 bg-white">
                                <div class="card-body">
                                    <h6 class="text-muted small fw-bold text-uppercase">${item.categoria}</h6>
                                    <div class="d-flex justify-content-between align-items-end">
                                        <h3 class="fw-bold mb-0">$${monto.toLocaleString('es-AR')}</h3>
                                        <span class="badge bg-light text-dark border">${item.cantidad_turnos} turnos</span>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });

                html += `
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-4 bg-dark text-white">
                            <div class="card-body">
                                <h6 class="text-white-50 small fw-bold text-uppercase">Total Recaudado</h6>
                                <h3 class="fw-bold mb-0 text-success">$${totalGeneral.toLocaleString('es-AR')}</h3>
                            </div>
                        </div>
                    </div>`;
                $('#contenedor_resumen_caja').html(html);
            }
        }, 'json');
    }

    // --- 2. FILTROS Y EVENTOS DINÁMICOS ---
    $('#filtro_medico, #filtro_fecha, #filtro_estado').on('change', cargarTurnos);

    // Cambio entre Obra Social y Particular en el Modal
    $(document).on('change', 'input[name="metodo_atencion"]', function() {
        const valorMedico = $('#valor_particular_medico').val();
        const valorCoseguro = $('#valor_coseguro_os').val();
        const esParticular = ($(this).val() === 'particular');

        if (esParticular) {
            $('#monto_cobrado').val(valorMedico);
            $('#opt_debito_os').hide(); // Ocultamos Débito OS si es particular
            if ($('#medio_pago').val() === 'Debito_OS') {
                $('#medio_pago').val('Efectivo').trigger('change');
            }
        } else {
            $('#monto_cobrado').val(valorCoseguro);
            $('#opt_debito_os').show(); // Mostramos Débito OS si es obra social
        }
    });

    // Control del Número de Operación según medio de pago
    $(document).on('change', '#medio_pago', function() {
        const medio = $(this).val();
        if (medio !== 'Efectivo' && medio !== '') {
            $('#contenedor_nro_operacion').slideDown();
            $('#nro_operacion').prop('required', true);
        } else {
            $('#contenedor_nro_operacion').slideUp();
            $('#nro_operacion').val('').prop('required', false);
        }
    });

    // --- 3. ENVÍO DEL FORMULARIO DE COBRO ---
    $('#formProcesarAtencion').on('submit', function(e) {
        e.preventDefault();
        
        const medio = $('#medio_pago').val();
        const nroOp = $('#nro_operacion').val();

        // Validación de Nro de Operación para pagos no-efectivo
        if (medio !== 'Efectivo' && nroOp.trim() === '') {
            Swal.fire('Atención', 'Debe ingresar el Nro. de Operación para este medio de pago', 'warning');
            return;
        }

        let metodoElegido = $('input[name="metodo_atencion"]:checked').val() || $('#metodo_particular_fijo').val();

        const datos = {
            turno_id: $('#cobro_turno_id').val(),
            metodo_atencion: metodoElegido,
            medio_pago: medio,
            monto_cobrado: $('#monto_cobrado').val(),
            nro_operacion: nroOp,
            observaciones: $('#obs_pago').val()
        };

        $.post('acciones.php?accion=confirmar_recepcion', datos, function(res) {
            if(res.status) {
                Swal.fire({ icon: 'success', title: '¡Recibido!', timer: 2000, showConfirmButton: false });
                $('#modalCobro').modal('hide');
                cargarTurnos();
            } else {
                Swal.fire('Error', res.message || 'Error desconocido', 'error');
            }
        }, 'json');
    });
});

// --- 4. FUNCIONES GLOBALES ---

function cargarTurnos() {
    const data = {
        fecha: $('#filtro_fecha').val(),
        medico_id: $('#filtro_medico').val(),
        estado: $('#filtro_estado').val()
    };

    $.post('acciones.php?accion=listar_turnos_gestion', data, function(res) {
        let html = '';
        if(res.status && res.data.length > 0) {
            res.data.forEach(t => {
                const badgeClass = `bg-${t.estado.toLowerCase()}`;
                html += `
                    <tr>
                        <td class="ps-4 fw-bold text-primary">${t.hora.substring(0,5)} hs</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-info me-2"><i class="bi bi-person"></i></div>
                                <div>
                                    <div class="fw-bold">${t.paciente_nombre}</div>
                                    <div class="small text-muted">DNI: ${t.paciente_dni}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-bold">${t.medico_nombre}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">${t.especialidad}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">${t.obra_social || 'PARTICULAR'}</span>
                        </td>
                        <td><span class="badge-estado ${badgeClass}"><i class="bi bi-dot"></i>${t.estado.toUpperCase()}</span></td>
                        <td class="pe-4 text-end">
                            ${t.estado === 'Pendiente' ? `
                                <button class="btn btn-sm btn-success rounded-pill px-3 fw-bold me-1" onclick="abrirCobro(${t.id})">
                                    <i class="bi bi-check2-circle me-1"></i> Recibir
                                </button>
                                <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                                    <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="cancelarTurno(${t.id})">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                <?php endif; ?>
                            ` : `<i class="bi bi-info-circle text-muted" title="Ya procesado"></i>`}
                        </td>
                    </tr>`;
            });
        } else {
            html = '<tr><td colspan="6" class="text-center py-5 text-muted">No se encontraron turnos.</td></tr>';
        }
        $('#tabla_turnos_body').html(html);
        
        // El reporte de caja se actualiza siempre que cargamos la tabla
        if (typeof actualizarReporteCaja === "function") {
            actualizarReporteCaja();
        }
    }, 'json');
}

function abrirCobro(turno_id) {
    $.post('acciones.php?accion=obtener_detalle_cobro', { id: turno_id }, function(res) {
        if(res.status) {
            const t = res.data;
            $('#cobro_turno_id').val(t.id);
            $('#valor_particular_medico').val(t.valor_consulta);
            $('#valor_coseguro_os').val(t.coseguro_estandar || 0);

            // Info visual del paciente
            let infoHtml = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold">${t.paciente_nombre}</h6>
                        <small class="text-muted">Médico: ${t.medico_nombre}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-dark">Cons. Part: $${t.valor_consulta}</span><br>
                        ${t.obra_social_id ? `<span class="badge bg-primary">Coseguro: $${t.coseguro_estandar}</span>` : ''}
                    </div>
                </div>`;
            $('#info_cobro_paciente').html(infoHtml);

            // Opciones de Cobertura
            let opcionesHtml = '';
            if(t.obra_social_id) {
                opcionesHtml = `
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="metodo_atencion" id="opt_os" value="obra_social" checked>
                        <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="opt_os">
                            <i class="bi bi-card-checklist d-block mb-1 fs-4"></i>
                            <small class="fw-bold">Obra Social</small>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="metodo_atencion" id="opt_part" value="particular">
                        <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="opt_part">
                            <i class="bi bi-cash d-block mb-1 fs-4"></i>
                            <small class="fw-bold">Particular</small>
                        </label>
                    </div>`;
                $('#monto_cobrado').val(t.coseguro_estandar);
                $('#opt_debito_os').show();
            } else {
                opcionesHtml = `
                    <div class="col-12 text-center border p-3 rounded-4 bg-white">
                        <p class="mb-0 small fw-bold text-primary">SIN COBERTURA: Atención Particular</p>
                        <input type="hidden" name="metodo_atencion" id="metodo_particular_fijo" value="particular">
                    </div>`;
                $('#monto_cobrado').val(t.valor_consulta);
                $('#opt_debito_os').hide();
            }
            
            $('#opciones_cobertura_dinamica').html(opcionesHtml);

            // Resetear campos de pago
            $('#medio_pago').val('Efectivo');
            $('#contenedor_nro_operacion').hide();
            $('#nro_operacion').val('');
            $('#metodos_pago_efectivo').show();

            $('#modalCobro').modal('show');
        }
    }, 'json');
}

function cargarMedicosFiltro() {
    $.get('acciones.php?accion=get_medicos_filtro', function(res) {
        if(res.status) {
            let options = '<option value="">Todos los médicos</option>';
            res.data.forEach(m => options += `<option value="${m.id}">${m.apellido}, ${m.nombre}</option>`);
            $('#filtro_medico').html(options);
        }
    }, 'json');
}

function cancelarTurno(id) {
    Swal.fire({
        title: '¿Anular Turno?',
        text: "Esta acción marcará el turno como Cancelado.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'No, mantener'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('acciones.php?accion=cancelar_turno', { id: id }, function(res) {
                if (res.status) {
                    Swal.fire('¡Anulado!', 'El turno ha sido cancelado.', 'success');
                    cargarTurnos();
                }
            }, 'json');
        }
    });
}
</script>

</body>
</html>