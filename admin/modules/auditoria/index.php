<?php 
    include '../../includes/header.php'; 
    // La auditoría se basa en el momento del cobro (created_at)
    $hoy = date('Y-m-d');
    $total_monto = 0;
    $total_comision = 0;
?>

<div class="main-card animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Auditoría y Control de Caja</h4>
            <p class="text-muted small">Reporte basado en transacciones reales (Fecha de Cobro).</p>
        </div>
        <div class="d-flex gap-2 mb-3">
            <button type="button" onclick="descargarExcel()" class="btn btn-success">
                <i data-lucide="file-spreadsheet" class="me-2"></i>Descargar Excel
            </button>
            <button type="button" onclick="descargarPDF()" class="btn btn-danger">
                <i data-lucide="file-text" class="me-2"></i>Descargar PDF
            </button>
        </div>

<script>
function descargarExcel() {
    const desde = $('#fecha_desde').val();
    const hasta = $('#fecha_hasta').val();
    window.location.href = `exportar_excel.php?desde=${desde}&hasta=${hasta}`;
}

function descargarPDF() {
    const desde = $('#fecha_desde').val();
    const hasta = $('#fecha_hasta').val();
    window.open(`exportar_pdf.php?desde=${desde}&hasta=${hasta}`, '_blank');
}
</script>
    </div>

    <!-- Filtros de Auditoría -->
    <div class="card border-0 bg-light mb-4">
        <div class="card-body">
            <form id="formAuditoria" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Desde (Cobro)</label>
                    <input type="date" class="form-control" id="fecha_desde" name="desde" value="<?php echo $hoy; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Hasta (Cobro)</label>
                    <input type="date" class="form-control" id="fecha_hasta" name="hasta" value="<?php echo $hoy; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Comisión Clínica %</label>
                    <input type="number" class="form-control" id="comision_pct" value="10" min="0" max="100">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i data-lucide="search" class="me-2"></i>Auditar Transacciones
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen de Auditoría -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white border-start border-primary border-5">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold text-uppercase">Caja Efectivo (Líquido)</h6>
                    <h3 class="fw-bold mb-0 text-dark" id="resumen_efectivo">$0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white border-start border-info border-5">
                <div class="card-body">
                    <h6 class="text-muted small fw-bold text-uppercase">Digital / Obras Sociales</h6>
                    <h3 class="fw-bold mb-0 text-dark" id="resumen_digital">$0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white">
                <div class="card-body">
                    <h6 class="text-white-50 small fw-bold text-uppercase">Neto Clínica (Comisión)</h6>
                    <h3 class="fw-bold mb-0 text-success" id="resumen_comision">$0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaAuditoria">
                <thead>
                    <tr>
                        <th>Fecha Cobro</th>
                        <th>Médico</th>
                        <th>Paciente / Cobertura</th>
                        <th>Método de Pago</th>
                        <th>Monto Cobrado</th>
                        <th>Nro. Operación</th>
                        <th>Diferencia</th>
                    </tr>
                </thead>
                <tbody id="tabla_auditoria_body">
                    <!-- Se llena vía AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 30px; border-top: 2px solid #000; padding-top: 10px;">
        <table style="width: 300px; margin-left: auto; border: none;">
            <tr>
                <td style="border:none;"><strong>Total Bruto:</strong></td>
                <td style="border:none; text-align:right;" id="pdf_total_bruto">$0,00</td>
            </tr>
            <tr>
                <td style="border:none;">
                    <strong>Comisión Clínica (<span id="leyenda_pct">10</span>%):</strong>
                </td>
                <td style="border:none; text-align:right; color: #d9534f;" id="pdf_total_comision">
                    -$0,00
                </td>
            </tr>
            <tr style="font-size: 18px; color: #5cb85c;">
                <td style="border:none;"><strong>Neto a Liquidar:</strong></td>
                <td style="border:none; text-align:right;" id="pdf_total_neto">$0,00</td>
            </tr>
        </table>
    </div>

    <hr class="my-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-secondary">Conciliación por Convenio (Médico - O.S.)</h5>
    </div>

    <div class="row g-3 mb-4 bg-white p-3 rounded-4 shadow-sm border">
        <div class="col-md-5">
            <label class="small fw-bold">Seleccionar Médico</label>
            <select id="sel_medico" class="form-select border-0 bg-light"></select>
        </div>
        <div class="col-md-5">
            <label class="small fw-bold">Seleccionar Obra Social</label>
            <select id="sel_os" class="form-select border-0 bg-light"></select>
        </div>
        <div class="col-md-2">
            <label class="d-block">&nbsp;</label>
            <button onclick="cargarReporteEspecifico()" class="btn btn-dark w-100">Filtrar</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Médico</th>
                        <th>Obra Social</th>
                        <th>Nro. Operación</th>
                        <th>Monto</th>
                        <th>Fecha Cobro</th>
                    </tr>
                </thead>
                <tbody id="tabla_especifica_body">
                    <tr><td colspan="5" class="text-center text-muted">Seleccione filtros para ver datos</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<script>
    $(document).ready(function() {
        lucide.createIcons();
        
        $('#formAuditoria').on('submit', function(e) {
            e.preventDefault();
            ejecutarAuditoria();
        });

        // Carga inicial al entrar
        ejecutarAuditoria();
    });

    function ejecutarAuditoria() {
        const desde = $('#fecha_desde').val();
        const hasta = $('#fecha_hasta').val();
        
        // 1. Obtenemos el porcentaje de comisión directamente del input dinámico
        const comisionPct = parseFloat($('#comision_pct').val()) || 0;

        $.get('acciones.php?accion=reporte_auditoria_completa', { desde: desde, hasta: hasta }, function(res) {
            if(res.status) {
                let html = '';
                let totalEfectivo = 0;
                let totalDigital = 0;
                let totalGeneral = 0;

                if(res.data.length === 0) {
                    html = '<tr><td colspan="7" class="text-center py-4 text-muted">No hay transacciones en este rango de fechas.</td></tr>';
                } else {
                    res.data.forEach(item => {
                        const monto = parseFloat(item.monto_cobrado) || 0;
                        const valorMedico = parseFloat(item.precio_lista) || 0;
                        totalGeneral += monto;

                        // Clasificación de ingresos (Caja física vs Digital/O.S.)
                        const esEfectivo = item.tipo_pago.toLowerCase().includes('efectivo');
                        if(esEfectivo) totalEfectivo += monto;
                        else totalDigital += monto;

                        // Lógica de alerta visual para auditoría
                        const diferencia = monto - valorMedico;
                        let difBadge = '';
                        if(diferencia < 0 && (!item.obra_social_nombre)) {
                            difBadge = `<span class="badge bg-danger-subtle text-danger" title="Menos que el valor de lista">-$${Math.abs(diferencia).toLocaleString('es-AR')}</span>`;
                        } else if (item.obra_social_nombre) {
                            difBadge = `<span class="badge bg-info-subtle text-info">Cubre O.S.</span>`;
                        }

                        html += `
                            <tr>
                                <td class="small text-muted">${item.fecha_cobro}</td>
                                <td>
                                    <div class="fw-bold text-primary">${item.medico_apellido} ${item.medico_nombre}</div>
                                    <div style="font-size: 10px;" class="text-muted">Valor Lista: $${valorMedico.toLocaleString('es-AR')}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">${item.paciente_apellido}, ${item.paciente_nombre}</div>
                                    <div class="small text-muted">${item.obra_social_nombre || 'Particular (Sin Cobertura)'}</div>
                                </td>
                                <td>
                                    <span class="badge border text-dark bg-light">${item.tipo_pago}</span>
                                </td>
                                <td class="fw-bold text-end">$${monto.toLocaleString('es-AR')}</td>
                                <td class="text-center">
                                    <code class="text-dark small">${item.nro_operacion || 'N/A'}</code>
                                </td>
                                <td>${difBadge}</td>
                            </tr>`;
                    });
                }

                // --- ACTUALIZACIÓN DINÁMICA DE LA INTERFAZ ---
                
                // 1. Inyectar filas en la tabla principal
                $('#tabla_auditoria_body').html(html);

                // 2. Actualizar la leyenda del porcentaje en el texto (Ej: Comisión Clínica (15%))
                // Requiere que en tu HTML tengas: <span id="leyenda_pct"></span>
                $('#leyenda_pct').text(comisionPct);

                // 3. Cálculos finales de la Clínica
                const comisionMonto = totalGeneral * (comisionPct / 100);
                const netoLiquidar = totalGeneral - comisionMonto;

                // 4. Actualizar Resúmenes de Dashboard
                $('#resumen_efectivo').text('$' + totalEfectivo.toLocaleString('es-AR', {minimumFractionDigits: 2}));
                $('#resumen_digital').text('$' + totalDigital.toLocaleString('es-AR', {minimumFractionDigits: 2}));
                $('#resumen_comision').text('$' + comisionMonto.toLocaleString('es-AR', {minimumFractionDigits: 2}));

                // 5. Inyectar en los campos de la liquidación (resuelve errores de PHP "Undefined variable")
                $('#pdf_total_bruto').text('$' + totalGeneral.toLocaleString('es-AR', {minimumFractionDigits: 2}));
                $('#pdf_total_comision').text('-$' + comisionMonto.toLocaleString('es-AR', {minimumFractionDigits: 2}));
                $('#pdf_total_neto').text('$' + netoLiquidar.toLocaleString('es-AR', {minimumFractionDigits: 2}));

                // 6. Reiniciar iconos de Lucide para las nuevas filas
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        }, 'json');
    }

    // Escuchar cambios en el input de comisión para recalcular al instante
    $(document).on('change keyup', '#comision_pct', function() {
        ejecutarAuditoria();
    });

    // Al cargar el documento, llenar selectores
    $.get('acciones.php?accion=obtener_filtros', function(res) {
        let optM = '<option value="">Todos los Médicos</option>';
        res.medicos.forEach(m => optM += `<option value="${m.id}">${m.apellido}, ${m.nombre}</option>`);
        $('#sel_medico').html(optM);

        let optOS = '<option value="">Todas las Obras Sociales</option>';
        res.os.forEach(os => optOS += `<option value="${os.id}">${os.nombre}</option>`);
        $('#sel_os').html(optOS);
    }, 'json');

    function cargarReporteEspecifico() {
        const m_id = $('#sel_medico').val();
        const os_id = $('#sel_os').val();

        $.get('acciones.php?accion=reporte_especifico_os', { medico_id: m_id, os_id: os_id }, function(res) {
            if(res.status) {
                let html = '';
                if(res.data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-4">No se encontraron cobros para esta combinación.</td></tr>';
                } else {
                    res.data.forEach(item => {
                        html += `
                            <tr>
                                <td><strong>${item.medico}</strong></td>
                                <td><span class="badge bg-info-subtle text-info">${item.obra_social}</span></td>
                                <td><code>${item.nro_operacion || 'SIN NRO'}</code></td>
                                <td class="fw-bold">$${parseFloat(item.monto).toLocaleString('es-AR')}</td>
                                <td class="small text-muted">${item.fecha}</td>
                            </tr>`;
                    });
                }
                $('#tabla_especifica_body').html(html);
            }
        }, 'json');
    }

    function descargarExcel() {
        const desde = $('#fecha_desde').val();
        const hasta = $('#fecha_hasta').val();
        window.location.href = `exportar_excel.php?desde=${desde}&hasta=${hasta}`;
    }

    function descargarPDF() {
        const desde = $('#fecha_desde').val();
        const hasta = $('#fecha_hasta').val();
        // Obtenemos la comisión que el usuario definió en el input
        const comision = $('#comision_pct').val() || 10;
        
        // Abrimos el generador FPDF en una pestaña nueva
        window.open(`exportar_pdf.php?desde=${desde}&hasta=${hasta}&comision=${comision}`, '_blank');
    }
</script>

<style>
    .table thead th {
        background-color: #f8fafc;
        border-bottom: 2px solid #edf2f7;
        font-size: 0.7rem;
    }
    .main-card {
        background: white;
        border-radius: 1.5rem;
        padding: 2.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }
    @media print {
        .navbar, #formAuditoria, .btn { display: none !important; }
        .main-card { box-shadow: none; padding: 0; }
        body { background: white; }
    }
</style>