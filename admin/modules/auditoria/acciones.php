<?php
require_once '../../../core/config.php';
require_once '../../../core/funciones.php';

$accion = $_GET['accion'] ?? '';

switch($accion) {
    case 'reporte_auditoria_completa':
        try {
            // Recibimos las fechas y ajustamos el rango horario para created_at
            $desde = ($_GET['desde'] ?? date('Y-m-d')) . ' 00:00:00';
            $hasta = ($_GET['hasta'] ?? date('Y-m-d')) . ' 23:59:59';
            
            $sql = "SELECT 
                        t.id,
                        t.created_at as fecha_cobro,
                        m.apellido as medico_apellido,
                        m.nombre as medico_nombre,
                        m.valor_consulta as precio_lista,
                        t.monto_cobrado,
                        t.tipo_pago,
                        t.nro_operacion,
                        os.nombre as obra_social_nombre,
                        p.apellido as paciente_apellido,
                        p.nombre as paciente_nombre
                    FROM turnos t
                    INNER JOIN medicos m ON t.medico_id = m.id
                    INNER JOIN pacientes p ON t.paciente_id = p.id
                    LEFT JOIN obras_sociales os ON t.obra_social_id = os.id
                    WHERE t.created_at BETWEEN ? AND ? 
                      AND t.estado = 'Atendido'
                    ORDER BY t.created_at DESC";
                        
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$desde, $hasta]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['status' => true, 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => false, 'message' => "Error de base de datos: " . $e->getMessage()]);
        }
        exit;
    break;

    case 'obtener_filtros':
        // Traer todos los médicos y obras sociales para los selectores
        $medicos = $pdo->query("SELECT id, apellido, nombre FROM medicos ORDER BY apellido ASC")->fetchAll(PDO::FETCH_ASSOC);
        $os = $pdo->query("SELECT id, nombre FROM obras_sociales ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['medicos' => $medicos, 'os' => $os]);
        exit;
    break;

    case 'reporte_especifico_os':
        try {
            $medico_id = $_GET['medico_id'] ?? '';
            $os_id = $_GET['os_id'] ?? '';

            $sql = "SELECT 
                        m.apellido as medico,
                        os.nombre as obra_social,
                        t.nro_operacion,
                        t.monto_cobrado as monto,
                        t.created_at as fecha
                    FROM turnos t
                    INNER JOIN medicos m ON t.medico_id = m.id
                    INNER JOIN obras_sociales os ON t.obra_social_id = os.id
                    /* Aseguramos que exista la relación en la tabla intermedia */
                    INNER JOIN medico_obras_sociales mos ON (mos.medico_id = m.id AND mos.obra_social_id = os.id)
                    WHERE t.estado = 'Atendido' ";
            
            $params = [];
            if($medico_id != '') { $sql .= " AND t.medico_id = ?"; $params[] = $medico_id; }
            if($os_id != '') { $sql .= " AND t.obra_social_id = ?"; $params[] = $os_id; }
            
            $sql .= " ORDER BY t.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['status' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
        exit;
    break;
    
    // Aquí pueden ir otros casos como el reporte_diario_caja corregido si lo necesitas
}