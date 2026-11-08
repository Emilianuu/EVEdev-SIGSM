<?php
// servicios_comunes/autenticacion/logout.php

// 1. Unirse a la sesión existente en el servidor
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Limpiar todas las variables almacenadas en $_SESSION
$_SESSION = array();

// 3. Destruir la cookie de sesión en el navegador del usuario (Recomendado por seguridad)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Destruir físicamente la sesión en el servidor
session_destroy();

// 5. Redirigir al controlador del login de forma limpia
header("Location: login_controlador.php");
exit();
