<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Monitor de Sala de Espera - Centro Médico</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #1a2a3a;
            font-family: 'Montserrat', sans-serif;
            overflow: hidden;
            color: white;
        }

        /* BARRA SUPERIOR CORPORATIVA */
        .barra-corporativa {
            background-color: #ffffff;
            color: #1a2a3a;
            padding: 15px 40px;
            border-bottom: 5px solid #00aaff;
        }

        .logo-container img {
            max-height: 80px;
            width: auto;
        }

        .clinica-info h2 {
            font-weight: 700;
            margin: 0;
            color: #0056b3;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .clinica-contacto {
            text-align: right;
            font-size: 1.1rem;
        }

        /* HEADER DEL MONITOR (ZONA AZUL) */
        .header-llamados {
            background: linear-gradient(90deg, #0056b3, #00aaff);
            padding: 15px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        /* DISEÑO DE TABLA Y FILAS */
        .fila-reciente {
            background-color: #fff3cd !important;
            color: #856404 !important;
            font-size: 2rem;
            font-weight: bold;
            border: 5px solid #ffc107;
            animation: pulse-border 2s infinite;
        }

        .nro-consultorio {
            background-color: #0056b3;
            color: white;
            padding: 10px 25px;
            border-radius: 12px;
            font-size: 2.5rem;
            display: inline-block;
            font-weight: 800;
        }

        .fila-reciente .nro-consultorio {
            background-color: #ffc107;
            color: #000;
        }

        .tabla-llamados {
            background-color: rgba(255,255,255,0.98);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
            color: #333;
            margin-top: 20px;
        }

        .reloj {
            font-size: 2.8rem;
            font-weight: 700;
            color: #ffffff;
        }

        .badge-estado {
            font-size: 1.2rem;
            padding: 10px 20px;
            border-radius: 50px;
            text-transform: uppercase;
        }

        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.8); }
            70% { box-shadow: 0 0 0 25px rgba(255, 193, 7, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
        }
    </style>
</head>
<body>

    <div class="barra-corporativa d-flex justify-content-between align-items-center">
        <div class="logo-container">
            <img src="https://via.placeholder.com/200x80?text=TU+LOGO+AQUI" alt="Logotipo">
        </div>
        
        <div class="clinica-info text-center">
            <h2>Clínica "San Rafael" Especialidades</h2>
            <p class="m-0 text-muted"><i class="fas fa-map-marker-alt"></i> Av. Principal 123 - Planta Baja</p>
        </div>

        <div class="clinica-contacto">
            <div class="fw-bold text-primary"><i class="fas fa-phone-alt"></i> Urgencias: (011) 4567-8900</div>
            <div class="text-muted"><i class="fab fa-whatsapp"></i> Turnos: +54 9 11 1234 5678</div>
        </div>
    </div>

    <div class="header-llamados d-flex justify-content-between align-items-center">
        <div>
            <h2 class="m-0 text-white"><i class="fas fa-bullhorn animate__animated animate__tada animate__infinite"></i> PACIENTES LLAMADOS</h2>
        </div>
        <div class="reloj" id="reloj">00:00:00</div>
    </div>

    <div class="container-fluid px-5 mt-3">
        <div class="table-responsive">
            <table class="table tabla-llamados text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="py-4 fs-4">MÉDICO / ESPECIALISTA</th>
                        <th class="py-4 fs-4">PACIENTE</th>
                        <th class="py-4 fs-4">CONSULTORIO</th>
                        <th class="py-4 fs-4">ESTADO</th>
                    </tr>
                </thead>
                <tbody id="tabla-llamados-body">
                    <!-- Los datos se cargan vía AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- El audio debe estar en public/includes/llamado.mp3 -->
    <audio id="sonidoLlamado" src="includes/llamado.mp3" preload="auto"></audio>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        let ultimoUpdate = null; // Almacena el timestamp del último llamado para detectar repeticiones

        function actualizarReloj() {
            const ahora = new Date();
            $('#reloj').text(ahora.toLocaleTimeString());
        }
        setInterval(actualizarReloj, 1000);

        function cargarLlamadosActivos() {
            // Se asume que llamados_listar.php está en la misma carpeta public/
            $.get('llamados_listar.php', function(data) {
                const tbody = $('#tabla-llamados-body');
                
                if (data.length > 0) {
                    const primerRegistro = data[0];
                    
                    // Si el primer registro está en estado 'Llamando' y el timestamp cambió: suena
                    if (primerRegistro.estado === 'Llamando' && primerRegistro.updated_at !== ultimoUpdate) {
                        const audio = document.getElementById('sonidoLlamado');
                        audio.currentTime = 0; // Reinicia por si ya estaba sonando
                        audio.play().catch(e => console.log("Interacción requerida para reproducir audio"));
                        
                        ultimoUpdate = primerRegistro.updated_at; // Actualizamos para no repetir el sonido sin cambios
                    }
                }

                tbody.empty();

                data.forEach((llamado, index) => {
                    const esLlamando = (llamado.estado === 'Llamando');
                    // Aplicar estilos de realce solo si es el llamado activo (índice 0 y estado Llamando)
                    const claseEspecial = (esLlamando && index === 0) ? 'fila-reciente animate__animated animate__flash' : '';
                    
                    // Traducción visual del estado
                    const textoEstado = esLlamando ? '<span class="badge bg-danger badge-estado">Llamando</span>' : '<span class="badge bg-warning text-dark badge-estado">En Consultorio</span>';
                    
                    const fila = `
                        <tr class="${claseEspecial}">
                            <td>
                                <strong class="d-block fs-4">${llamado.medico}</strong>
                            </td>
                            <td>
                                <strong class="text-uppercase fs-3">${llamado.paciente}</strong>
                            </td>
                            <td>
                                <div class="nro-consultorio">${llamado.consultorio}</div>
                            </td>
                            <td>
                                ${textoEstado}
                            </td>
                        </tr>`;
                    tbody.append(fila);
                });

                if (data.length === 0) {
                    tbody.append('<tr><td colspan="4" class="py-5 fs-4 text-muted">No hay llamados activos en este momento</td></tr>');
                }
            }).fail(function() {
                console.error("Error al conectar con llamados_listar.php");
            });
        }

        // Refresco constante cada 3 segundos para respuesta rápida
        setInterval(cargarLlamadosActivos, 3000);
        cargarLlamadosActivos();
        actualizarReloj();
    </script>
</body>
</html>