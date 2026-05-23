<?php
session_start();

// Eliminamos las variables específicas del médico
unset($_SESSION['medico_id']);
unset($_SESSION['medico_nombre']);

// Destruimos la sesión por completo para mayor seguridad
session_destroy();

// Redirigimos al login del médico (mismo directorio)
header("Location: login-medico");
exit();