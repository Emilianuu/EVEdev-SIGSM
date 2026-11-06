<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica de cambio de idioma
if (isset($_GET['lang']) && in_array($_GET['lang'], ['es', 'en'])) {
    $_SESSION['idioma'] = $_GET['lang'];
}
$idiomaActual = $_SESSION['idioma'] ?? 'es';
$lang = require_once "../idiomas/{$idiomaActual}.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../autenticacion/login_controlador.php");
    exit();
}
?>
<!doctype html>
<html lang="<?= $idiomaActual ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $lang['hospital_nombre'] ?> – <?= $lang['portal_heading'] ?></title>

    <link rel="stylesheet" href="estilos.css" />
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <header>
        <div class="header-brand">
            <img src="logo.png" alt="Logo" class="custom-logo" />
            <div>
                <div class="header-title"><?= $lang['hospital_nombre'] ?></div>
                <div class="header-subtitle"><?= $lang['hospital_subtitulo'] ?></div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 16px">
            <!-- Botones de Idioma -->
            <div class="lang-switch">
                <a href="?lang=es" style="text-decoration:none; margin-right:8px; <?= $idiomaActual == 'es' ? 'font-weight:bold; color:var(--hc-blue);' : 'color:var(--slate-500);' ?>">ES</a>|
                <a href="?lang=en" style="text-decoration:none; margin-left:8px; <?= $idiomaActual == 'en' ? 'font-weight:bold; color:var(--hc-blue);' : 'color:var(--slate-500);' ?>">EN</a>
            </div>
            <span style="font-size: 0.85rem; color: var(--slate-700);">
                <i class="bi bi-person-circle"></i> <strong><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></strong>
            </span>
            <a href="../autenticacion/logout.php" class="btn-logout"><i class="bi bi-box-arrow-right"></i> <?= $lang['btn_cerrar_sesion'] ?></a>
        </div>
    </header>

    <main>
        <div class="wrapper">
            <div class="page-eyebrow"><?= $lang['portal_eyebrow'] ?></div>
            <h1 class="page-heading"><?= $lang['portal_heading'] ?></h1>
            <p class="page-desc"><?= $lang['portal_desc'] ?></p>

            <div class="modules-grid">
                <a href="../../modulo_traslados/vista/panel-de-gestion.php" class="module-card" style="--card-accent: var(--accent-traslado)">
                    <div class="card-icon"><i class="bi bi-truck"></i></div>
                    <div class="card-body">
                        <div class="card-label"><?= $lang['tarjeta_traslados_label'] ?></div>
                        <h2 class="card-title"><?= $lang['tarjeta_traslados_titulo'] ?></h2>
                        <p class="card-desc"><?= $lang['tarjeta_traslados_desc'] ?></p>
                    </div>
                    <div class="card-footer-row">
                        <i class="bi bi-arrow-right card-arrow"></i>
                    </div>
                </a>

                <a href="../../modulo_documentacion/controlador/dashboard_controlador.php" class="module-card" style="--card-accent: var(--accent-admin)">
                    <div class="card-icon"><i class="bi bi-folder"></i></div>
                    <div class="card-body">
                        <div class="card-label"><?= $lang['tarjeta_admin_label'] ?></div>
                        <h2 class="card-title"><?= $lang['tarjeta_admin_titulo'] ?></h2>
                        <p class="card-desc"><?= $lang['tarjeta_admin_desc'] ?></p>
                    </div>
                    <div class="card-footer-row">
                        <i class="bi bi-arrow-right card-arrow"></i>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <footer><?= $lang['footer_texto'] ?></footer>

    <script>
        if (localStorage.getItem("theme") === "dark") document.documentElement.setAttribute("data-theme", "dark");
        window.addEventListener("storage", (e) => {
            if (e.key === "theme") {
                e.newValue === "dark" ? document.documentElement.setAttribute("data-theme", "dark") : document.documentElement.removeAttribute("data-theme");
            }
        });
    </script>
</body>

</html>