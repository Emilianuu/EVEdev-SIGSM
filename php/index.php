<?php
// C:\xampp\htdocs\sigsm\index.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirección inteligente
if (isset($_SESSION['id_usuario'])) {
    // Si ya está logueado, lo mandamos a la pantalla estilizada que acabamos de armar
    header("Location: servicios_comunes/vista_general/index.php");
} else {
    // Si no, al formulario de login
    header("Location: servicios_comunes/autenticacion/login_controlador.php");
}
exit();
