<?php
include "core/config.php";
// Carga manual de PHPMailer
require 'core/PHPMailer/Exception.php';
require 'core/PHPMailer/PHPMailer.php';
require 'core/PHPMailer/SMTP.php';
// Carga de FPDF
require 'admin/fpdf/fpdf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Función para compatibilidad de acentos en PHP 8+
function toISO($text) {
    return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dni = $_POST['dni'] ?? null;
    $medico_id = $_POST['medico_id'] ?? null;
    $os_id = $_POST['obra_social_id'] ?? null;
    $email_paciente = $_POST['email'] ?? null; // IMPORTANTE: Agregar este name en tu HTML

    if ($os_id === "NULL" || $os_id === "") { $os_id = null; }

    $fecha_seleccionada = $_POST['fecha'] ?? null;
    $hora_turno = $_POST['hora'] ?? null; 

    try {
        if (!$dni || !$hora_turno || !$fecha_seleccionada || !$medico_id) {
            throw new Exception("Faltan datos obligatorios.");
        }

        $pdo->beginTransaction();

        // 1. LÓGICA DE PACIENTE
        $stmt = $pdo->prepare("SELECT id, email, nombre, apellido FROM pacientes WHERE dni = ? LIMIT 1");
        $stmt->execute([$dni]);
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$paciente) {
            $nombre = htmlspecialchars($_POST['nombre'] ?? '');
            $apellido = htmlspecialchars($_POST['apellido'] ?? '');
            $telefono = htmlspecialchars($_POST['telefono'] ?? '');
            
            if (empty($nombre) || empty($apellido) || empty($email_paciente)) {
                throw new Exception("Nombre, Apellido y Email son obligatorios para nuevos pacientes.");
            }

            $sqlP = "INSERT INTO pacientes (dni, nombre, apellido, telefono, email, obra_social_id, estado, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
            $stmtP = $pdo->prepare($sqlP);
            $stmtP->execute([$dni, $nombre, $apellido, $telefono, $email_paciente, $os_id]);
            $paciente_id = $pdo->lastInsertId();
            $email_final = $email_paciente;
            $nombre_completo = $nombre . ' ' . $apellido;
        } else {
            $paciente_id = $paciente['id'];
            $email_final = $paciente['email'];
            $nombre_completo = $paciente['nombre'] . ' ' . $paciente['apellido'];
        }

        // 2. VERIFICACIÓN ANTI-DUPLICADO
        $stmtCheck = $pdo->prepare("SELECT id FROM turnos WHERE medico_id = ? AND fecha = ? AND hora = ? AND estado != 'Cancelado'");
        $stmtCheck->execute([$medico_id, $fecha_seleccionada, $hora_turno]);
        if ($stmtCheck->fetch()) {
            throw new Exception("Lo sentimos, este horario acaba de ser reservado.");
        }

        // 3. INSERTAR TURNO
        $sqlT = "INSERT INTO turnos (medico_id, paciente_id, fecha, hora, obra_social_id, estado, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, 'Pendiente', NOW(), NOW())";
        $stmtT = $pdo->prepare($sqlT);
        $stmtT->execute([$medico_id, $paciente_id, $fecha_seleccionada, $hora_turno, $os_id]);
        $ultimo_id = $pdo->lastInsertId();

        // Buscamos nombre del médico para el PDF/Email
        $stmtM = $pdo->prepare("SELECT nombre, apellido FROM medicos WHERE id = ?");
        $stmtM->execute([$medico_id]);
        $medico = $stmtM->fetch();
        $nom_medico = "Dr/a. " . $medico['apellido'];

        $pdo->commit();

        // --- 4. GENERACIÓN DE PDF EN MEMORIA ---
        $pdf = new FPDF('P', 'mm', array(80, 150));
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 10, toISO('COMPROBANTE DE TURNO'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Ln(5);
        $pdf->Cell(60, 7, toISO("Paciente: $nombre_completo"), 0, 1);
        $pdf->Cell(60, 7, toISO("Profesional: $nom_medico"), 0, 1);
        $pdf->Cell(60, 7, toISO("Fecha: " . date('d/m/Y', strtotime($fecha_seleccionada))), 0, 1);
        $pdf->Cell(60, 7, toISO("Hora: $hora_turno hs"), 0, 1);
        
        $pdf_string = $pdf->Output('S', 'comprobante.pdf'); // 'S' para string (memoria)

        // --- 5. ENVÍO DE EMAIL ---
        if (!empty($email_final)) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Cambiar por el tuyo
                $mail->SMTPAuth   = true;
                $mail->Username   = 'tu-correo@gmail.com'; // Tu correo
                $mail->Password   = 'tu-contraseña-aplicacion'; // Tu contraseña
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('no-reply@tuclínica.com', toISO('Centro Médico'));
                $mail->addAddress($email_final);
                $mail->addStringAttachment($pdf_string, 'Comprobante_Turno.pdf');

                $mail->isHTML(true);
                $mail->Subject = toISO('Confirmación de Turno');
                $mail->Body    = "Hola <b>$nombre_completo</b>, tu turno ha sido agendado para el " . 
                                 date('d/m/Y', strtotime($fecha_seleccionada)) . " a las $hora_turno hs. Adjunto tu comprobante.";

                $mail->send();
            } catch (Exception $e) {
                // No lanzamos error para no arruinar la experiencia si falla el mailer
            }
        }

        $fecha_formateada = date('d/m/Y', strtotime($fecha_seleccionada));
        ob_clean(); 
        echo json_encode([
            'status' => 'success', 
            'message' => "¡Turno agendado! Se ha enviado un comprobante a $email_final.",
            'turno_id' => $ultimo_id
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}