<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'UsuarioModelo.php';

// Si ya tiene sesión activa, va directo al portal principal
if (isset($_SESSION['id_usuario'])) {
    header("Location: ../vista_general/index.php");
    exit();
}

// Verificar envio de datos por el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitización básica: quitar espacios y evitar inyección de scripts en el email
    $emailInput = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $passInput  = trim($_POST['contrasena']);

    if (!empty($emailInput) && !empty($passInput)) {

        // VALIDACIÓN: Comprobar que el email tiene un formato válido
        if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
            $error = "El formato del correo electrónico no es válido. Debe incluir un '@' y un dominio.";
        } else {
            // Pedir datos del usuario al Modelo
            $funcionario = UsuarioModelo::buscarPorEmail($emailInput);

            // Comparar contraseña escrita contra el hash seguro guardado
            if ($funcionario && password_verify($passInput, $funcionario['contrasena_hash'])) {

                // Medida preventiva contra fijación de sesiones
                session_regenerate_id(true);

                // Setear las credenciales globales del usuario
                $_SESSION['id_usuario']     = $funcionario['id_usuario'];
                $_SESSION['nombre_usuario'] = $funcionario['nombre_usuario'];
                $_SESSION['nombre_completo'] = $funcionario['nombre'] . ' ' . $funcionario['apellido'];
                $_SESSION['email']          = $funcionario['email'];

                // Guarda el registro DML de su fecha de ingreso
                UsuarioModelo::registrarUltimoLogin($funcionario['id_usuario']);

                // Redirigir a la plantilla transversales del portal
                header("Location: ../vista_general/index.php");
                exit();
            } else {
                $error = "El correo electrónico o la contraseña son incorrectos, o la cuenta está inactiva.";
            }
        }
    } else {
        $error = "Por favor, complete todos los campos obligatorios.";
    }
}

// Cargar e inyecta la vista HTML
require_once 'login_vista.php';
