<!-- servicios_comunes/autenticacion/login_vista.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Institucional - Hospital de Clínicas</title>

    <!-- ENLACE AL CSS CENTRALIZADO -->
    <link rel="stylesheet" href="../vista_general/estilos.css" />

    <!-- FUENTES E ICONOS DE BOOTSTRAP -->
    <link href="https://googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <main>
        <div class="login-card">
            <div class="login-header">
                <img src="../vista_general/logo.png" alt="Logo Hospital de Clínicas" class="login-logo" />
                <h2>Acceso Institucional</h2>
                <p>Ingrese sus credenciales del Hospital</p>
            </div>

            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert-error" style="display: flex;">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div id="js-error" class="alert-error" style="display: none;"></div>

            <form id="loginForm" action="login_controlador.php" method="POST" novalidate>
                <div class="form-group">
                    <label for="email">Usuario Institucional (Email)</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="ejemplo@clinicas.edu.uy"
                        class="<?php echo isset($error) && !empty($error) ? 'input-error' : ''; ?>"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        class="<?php echo isset($error) && !empty($error) ? 'input-error' : ''; ?>"
                        placeholder="••••••••">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                </button>
            </form>

            <div class="login-footer">
                <hr>
                <p><strong>S.I.G.S.M.</strong><br>Hospital de Clínicas Dr. Manuel Quintela</p>
            </div>
        </div>
    </main>

    <script>
        // Sincronización del Modo Oscuro
        if (localStorage.getItem("theme") === "dark") {
            document.documentElement.setAttribute("data-theme", "dark");
        }

        document.getElementById('loginForm').addEventListener('submit', function(evento) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('contrasena');
            const errorDiv = document.getElementById('js-error');

            const emailValor = emailInput.value.trim();
            const passValor = passInput.value.trim();

            // Expresión regular para validar formato de email
            const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // 1. Limpiar estados previos (quitar bordes rojos y ocultar alertas)
            errorDiv.style.display = 'none';
            errorDiv.innerHTML = '';
            emailInput.classList.remove('input-error');
            passInput.classList.remove('input-error');

            let hayError = false;

            // 2. Validar campos vacíos primero
            if (emailValor === '') {
                emailInput.classList.add('input-error');
                hayError = true;
            }
            if (passValor === '') {
                passInput.classList.add('input-error');
                hayError = true;
            }

            if (hayError) {
                evento.preventDefault();
                mostrarError('Por favor, complete todos los campos obligatorios.');
                return; // Detenemos la ejecución aquí
            }

            // 3. Validar formato de email (solo si no estaban vacíos)
            if (!regexEmail.test(emailValor)) {
                evento.preventDefault();
                emailInput.classList.add('input-error');
                mostrarError('Por favor, ingrese un correo electrónico válido.');
                return;
            }
        });

        // Al escribir, quitar el borde rojo de ese campo específico
        document.getElementById('email').addEventListener('input', function() {
            this.classList.remove('input-error');
            document.getElementById('js-error').style.display = 'none';
        });

        document.getElementById('contrasena').addEventListener('input', function() {
            this.classList.remove('input-error');
            document.getElementById('js-error').style.display = 'none';
        });

        // Función auxiliar para imprimir el error en pantalla
        function mostrarError(mensaje) {
            const errorDiv = document.getElementById('js-error');
            errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> ' + mensaje;
            errorDiv.style.display = 'flex';
        }
    </script>
</body>

</html>