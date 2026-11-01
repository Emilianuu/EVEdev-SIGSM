<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Proteger la ruta: si no está logueado, lo mandamos al login
if (!isset($_SESSION['id_usuario'])) {
  header("Location: ../../servicios_comunes/autenticacion/login_controlador.php");
  exit();
}

// Rescatar los datos de la sesión
$nombreSesion = $_SESSION['nombre_completo'] ?? 'Usuario';
$emailSesion  = $_SESSION['email'] ?? '';

// Calcular las iniciales para el círculo del avatar
$palabras = explode(' ', trim($nombreSesion));
$iniciales = '';
if (isset($palabras[0])) $iniciales .= strtoupper(substr($palabras[0], 0, 1));
if (isset($palabras[1])) $iniciales .= strtoupper(substr($palabras[1], 0, 1));
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hospital de Clínicas – Gestión de Documentación</title>

  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"
    rel="stylesheet" />
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
    rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="../vista/assets/css/panel-de-administracion.css" />
</head>

<body>
  <!-- ════════════════════════════════════════
         ADMIN APP SHELL
    ════════════════════════════════════════ -->
  <div id="sidebar-overlay"></div>

  <div id="app-shell">
    <!-- SIDEBAR -->
    <nav id="sidebar">
      <div class="sidebar-brand">
        <img
          src="../../servicios_comunes/vista_general/banner.jpg"
          alt="Banner Hospital de Clínicas"
          class="brand-img-normal" />
        <img
          src="../../servicios_comunes/vista_general/banner-reducido.jpg"
          alt="Ícono Hospital de Clínicas"
          class="brand-img-collapsed" />
      </div>

      <div class="sidebar-section-label">Sistema de Administración</div>
      <div class="sidebar-label-bold">Gestión de Documentos</div>

      <ul class="sidebar-menu-list mb-0">
        <li class="nav-item-custom">
          <a
            class="nav-link-custom active btn-nav-view"
            data-view="view-dashboard"
            id="nav-inicio"
            href="#">
            <i class="bi bi-grid-fill"></i> Panel Principal
          </a>
        </li>
        <li class="nav-item-custom">
          <a
            class="nav-link-custom btn-nav-view"
            data-view="view-documentos"
            id="nav-documentos"
            href="#">
            <i class="bi bi-file-earmark-text"></i> Documentos
          </a>
        </li>
        <li class="nav-item-custom">
          <a
            class="nav-link-custom btn-nav-view"
            data-view="view-generar-qr"
            id="nav-generar-qr"
            href="#">
            <i class="bi bi-qr-code"></i> Generar QR
          </a>
        </li>
        <li class="nav-item-custom">
          <a
            class="nav-link-custom btn-nav-view"
            data-view="view-configuracion"
            id="nav-configuracion"
            href="#">
            <i class="bi bi-gear"></i> Configuración
          </a>
        </li>
      </ul>

      <div class="sidebar-footer">
        <div class="avatar-circle"><?php echo $iniciales; ?></div>
        <div class="footer-user-info">
          <div class="avatar-name" id="user-name"><?php echo htmlspecialchars($nombreSesion); ?></div>
          <div class="avatar-role">Funcionario</div>
        </div>
      </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div id="main-content">
      <!-- Topbar (solo mobile toggle) -->
      <div class="topbar">
        <button class="toggle-btn btn-toggle-sidebar" title="Menú">
          <i class="bi bi-list"></i>
        </button>
        <div class="topbar-breadcrumb">
          <span class="bc-current" id="topbar-title">Panel Principal</span>
        </div>
        <div class="topbar-actions ms-auto">
          <button
            class="btn-hc btn-hc-ghost btn-text-sm"
            id="btnBackPortal"
            style="padding: 4px 12px">
            <i class="bi bi-box-arrow-left"></i> Volver al portal principal
          </button>
        </div>
      </div>

      <!-- PAGE BODY -->
      <div class="page-body">
        <!-- ── PANEL PRINCIPAL (INICIO) ─────────── -->
        <div id="view-dashboard" class="view active">
          <div class="page-header">
            <div>
              <h1>
                Bienvenido, <span id="dash-username"><?php echo htmlspecialchars($nombreSesion); ?></span>
              </h1>
              <p class="subtitle">
                Módulo de Gestión de Historias Clínicas y Documentos de
                Guardia
              </p>
            </div>
            <span class="badge-hc badge-gray" id="dash-date"></span>
          </div>

          <!-- Stat cards -->
          <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
              <div class="stat-card">
                <p class="stat-label">
                  <i class="bi bi-file-earmark-text"></i> Total Documentos
                </p>
                <p class="stat-value" id="stat-total">0</p>
                <p class="stat-delta">Últimos 30 días</p>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="stat-card">
                <p class="stat-label">
                  <i class="bi bi-exclamation-circle"></i> Revisión Pendiente
                </p>
                <p class="stat-value" id="stat-pendientes">0</p>
                <p class="stat-delta stat-delta-accent">Requieren firma</p>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="stat-card">
                <p class="stat-label">
                  <i class="bi bi-check-circle"></i> Aprobados
                </p>
                <p class="stat-value" id="stat-aprobados">0</p>
                <p class="stat-delta">Validados correctamente</p>
              </div>
            </div>
            <div class="col-xl-3 col-md-6">
              <div class="stat-card">
                <p class="stat-label">
                  <i class="bi bi-qr-code"></i> QR Generados
                </p>
                <p class="stat-value" id="stat-qr">1,829</p>
                <p class="stat-delta">Accesos externos</p>
              </div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-lg-7">
              <div class="panel h-100">
                <div class="panel-header">
                  <p class="panel-title">
                    <i class="bi bi-clock-history"></i> Cargas de Documentos
                    Recientes
                  </p>
                  <button
                    class="btn-hc btn-hc-ghost btn-xs btn-nav-view"
                    data-view="view-documentos">
                    Ver todos <i class="bi bi-arrow-right"></i>
                  </button>
                </div>
                <div class="table-responsive-wrapper">
                  <table class="hc-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Tipo de Archivo</th>
                        <th>Estado</th>
                      </tr>
                    </thead>
                    <tbody id="dash-doc-table"></tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="panel h-100">
                <div class="panel-header">
                  <p class="panel-title">
                    <i class="bi bi-graph-up"></i> Actividad Semanal
                  </p>
                </div>
                <div class="panel-body">
                  <div class="sparkline-box">
                    <svg viewBox="0 0 220 60" preserveAspectRatio="none">
                      <polyline
                        points="0,42 30,30 60,38 90,18 120,26 150,10 180,22 220,14"
                        fill="none"
                        stroke="var(--hc-blue)"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                    </svg>
                  </div>
                  <p class="chart-subtitle-text">
                    142 validaciones promedio diarias
                  </p>
                  <p class="section-heading mb-custom-10">
                    Estado de Servidores
                  </p>
                  <div class="server-status-row">
                    <span class="status-dot"></span> Servicio de Firma
                    Electrónica OK
                  </div>
                  <div class="server-status-row">
                    <span class="status-dot"></span> Base de datos Hist.
                    Clínicas OK
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── DOCUMENTOS CLÍNICOS ─────────── -->
        <div id="view-documentos" class="view">
          <div class="page-header">
            <div>
              <h1>Documentos Clínicos</h1>
              <p class="subtitle">
                Listado total de registros digitales homologados en el
                hospital
              </p>
            </div>
            <button
              class="btn-hc btn-hc-primary btn-nav-view"
              data-view="view-nuevo-doc">
              <i class="bi bi-plus-lg"></i> Nuevo Documento
            </button>
          </div>

          <div class="panel">
            <div class="panel-header panel-header-wrap">
              <div class="search-wrap search-wrap-flexible">
                <i class="bi bi-search"></i>
                <input
                  type="text"
                  class="hc-input"
                  placeholder="Buscar por ID, Tipo o Funcionario..." />
              </div>
              <select class="hc-input select-sm">
                <option>Tipo: Todos</option>
                <option>Epicrisis de Alta</option>
                <option>Informe Radiológico</option>
                <option>Análisis de Sangre</option>
                <option>Consentimiento Quirúrgico</option>
                <option>Historia Clínica</option>
                <option>Reporte de Guardia</option>
              </select>
              <select class="hc-input select-sm">
                <option>Estado: Todos</option>
                <option>Pendiente</option>
                <option>En Revisión</option>
                <option>Aprobado</option>
                <option>Rechazado</option>
              </select>
              <button class="btn-hc btn-hc-secondary btn-sm">
                <i class="bi bi-funnel"></i> Limpiar
              </button>
            </div>
            <div class="table-responsive-wrapper">
              <table class="hc-table">
                <thead>
                  <tr>
                    <th>Código HC</th>
                    <th>Nombre del Documento</th>
                    <th>Tipo de Documento</th>
                    <th>Fecha de Carga</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody id="admin-doc-list-table"></tbody>
              </table>
            </div>
            <div class="panel-footer">
              <span class="doc-count-text" id="doc-count-label"></span>
              <div class="pagination-controls">
                <button class="btn-hc btn-hc-secondary btn-xs">
                  Anterior
                </button>
                <button class="btn-hc btn-hc-primary btn-xs">
                  Siguiente
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ── NUEVO DOCUMENTO ──────────────── -->
        <div id="view-nuevo-doc" class="view">
          <div class="page-header">
            <div>
              <h1>Nuevo Documento</h1>
              <p class="subtitle">
                Complete los datos para cargar un nuevo documento clínico
              </p>
            </div>
            <button
              class="btn-hc btn-hc-secondary btn-nav-view"
              data-view="view-documentos">
              <i class="bi bi-arrow-left"></i> Volver
            </button>
          </div>

          <div class="row g-4">
            <div class="col-lg-8">
              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title">
                    <i class="bi bi-pencil-square"></i> Información del
                    Documento
                  </p>
                </div>
                <div class="panel-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="hc-label">Tipo de Documento
                        <span class="text-red-asterisk">*</span></label>
                      <select class="hc-input" id="nd-tipo">
                        <option value="">Seleccione un tipo</option>
                        <option>Epicrisis de Alta</option>
                        <option>Informe Radiológico</option>
                        <option>Análisis de Sangre</option>
                        <option>Consentimiento Quirúrgico</option>
                        <option>Historia Clínica</option>
                        <option>Reporte de Guardia</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="hc-label">Estado inicial</label>
                      <select class="hc-input" id="nd-estado">
                        <option value="pendiente">Pendiente</option>
                        <option value="revision">En Revisión</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label class="hc-label">Contenido del Documento</label>
                      <textarea
                        class="hc-input hc-textarea hc-textarea-large"
                        id="nd-contenido"
                        placeholder="Ingrese el resumen clínico o contenido del documento..."></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="panel mb-3">
                <div class="panel-header">
                  <p class="panel-title">
                    <i class="bi bi-paperclip"></i> Adjuntar Archivo
                  </p>
                </div>
                <div class="panel-body">
                  <div class="upload-zone" id="uploadZoneArea">
                    <i class="bi bi-cloud-upload upload-icon-lg"></i>
                    <div class="upload-text-bold">
                      Haga clic para seleccionar
                    </div>
                    <div class="upload-text-sm">PDF hasta 10 MB</div>
                    <input
                      type="file"
                      accept=".pdf"
                      class="hidden-file-input"
                      id="hiddenFileInput" />
                  </div>
                </div>
              </div>
              <div class="info-banner">
                <i class="bi bi-qr-code"></i> El acceso QR para este documento
                podrá generarse luego desde “Generar QR”.
              </div>
            </div>
            <div class="col-12">
              <div class="flex-end-gap">
                <button
                  class="btn-hc btn-hc-secondary btn-nav-view"
                  data-view="view-documentos">
                  Cancelar
                </button>
                <button class="btn-hc btn-hc-primary" id="btnSaveDoc">
                  <i class="bi bi-floppy"></i> Guardar Documento
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ── VISUALIZADOR DE DOCUMENTO ─────────── -->
        <div id="view-documento-detalle" class="view">
          <div class="page-header">
            <div>
              <p class="breadcrumb-link-wrapper">
                <a
                  href="#"
                  class="breadcrumb-link btn-nav-view"
                  data-view="view-documentos">Documentos</a>
                &nbsp;›&nbsp; Detalle
              </p>
              <h1 id="dd-title">Documento</h1>
            </div>
            <div class="header-actions-flex">
              <button class="btn-hc btn-approve-custom" id="btnApproveDoc">
                <i class="bi bi-check-lg"></i> Aprobar
              </button>
              <button class="btn-hc btn-hc-danger" id="btnRejectDoc">
                <i class="bi bi-x-lg"></i> Rechazar
              </button>
              <button class="btn-hc btn-hc-secondary">
                <i class="bi bi-download"></i> Descargar
              </button>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-lg-8">
              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title">Visor de Documento Homologado</p>
                  <span class="page-info-text">Pág 1 de 1</span>
                </div>
                <div class="panel-body">
                  <div class="viewer-box" id="dd-content"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title">Historial de Auditoría</p>
                </div>
                <div class="panel-body">
                  <div class="timeline" id="dd-audit"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── GENERADOR DE ACCESOS QR ─────────── -->
        <div id="view-generar-qr" class="view">
          <div class="page-header">
            <div>
              <h1>Generador de Accesos QR</h1>
              <p class="subtitle">
                Cree un código QR temporal para acceder externamente a un
                documento validado
              </p>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-lg-7">
              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title">Parámetros de Generación</p>
                </div>
                <div class="panel-body">
                  <div class="mb-3">
                    <label class="hc-label">Seleccionar Documento</label>
                    <select class="hc-input" id="qr-documento">
                      <option value="">Seleccione un documento...</option>
                    </select>
                  </div>
                  <div class="mb-4">
                    <label class="hc-label">Plazo de Expiración</label>
                    <select class="hc-input" id="qr-expiracion">
                      <option>
                        Vence en 24 horas (Expira automáticamente)
                      </option>
                      <option>Vence en 48 horas</option>
                      <option>Vence en 7 días</option>
                      <option>Sin expiración</option>
                    </select>
                  </div>
                  <button
                    class="btn-hc btn-hc-primary w-100 flex-center-content"
                    id="btnGenerarQr">
                    Generar Código de Acceso QR
                  </button>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="panel h-100">
                <div class="panel-header">
                  <p class="panel-title">Código de Acceso Listo</p>
                </div>
                <div class="panel-body text-center" id="qr-empty">
                  <div class="empty-qr-box">
                    <i class="bi bi-qr-code qr-icon-lg"></i>
                    Seleccione un documento y genere su código de acceso.
                  </div>
                </div>
                <div
                  class="panel-body text-center hidden-by-default"
                  id="qr-result">
                  <div class="qr-code-img qr-result-img-wrapper">
                    <!-- SVG inline kept for rendering, classes applied to wrapper -->
                    <svg viewBox="0 0 100 100" width="164" height="164">
                      <rect width="100" height="100" fill="white" />
                      <rect
                        x="5"
                        y="5"
                        width="35"
                        height="35"
                        rx="3"
                        fill="none"
                        stroke="#1a1c20"
                        stroke-width="3" />
                      <rect
                        x="12"
                        y="12"
                        width="21"
                        height="21"
                        fill="#1a1c20"
                        rx="1" />
                      <rect
                        x="60"
                        y="5"
                        width="35"
                        height="35"
                        rx="3"
                        fill="none"
                        stroke="#1a1c20"
                        stroke-width="3" />
                      <rect
                        x="67"
                        y="12"
                        width="21"
                        height="21"
                        fill="#1a1c20"
                        rx="1" />
                      <rect
                        x="5"
                        y="60"
                        width="35"
                        height="35"
                        rx="3"
                        fill="none"
                        stroke="#1a1c20"
                        stroke-width="3" />
                      <rect
                        x="12"
                        y="67"
                        width="21"
                        height="21"
                        fill="#1a1c20"
                        rx="1" />
                      <rect
                        x="45"
                        y="5"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="45"
                        y="15"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="55"
                        y="10"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="45"
                        y="45"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="55"
                        y="55"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="65"
                        y="45"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="75"
                        y="55"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="45"
                        y="65"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="55"
                        y="75"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="65"
                        y="65"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="75"
                        y="75"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                      <rect
                        x="85"
                        y="85"
                        width="7"
                        height="7"
                        fill="#1a1c20" />
                    </svg>
                  </div>
                  <p class="qr-result-patient" id="qr-result-doc-title"></p>
                  <p class="qr-result-desc" id="qr-result-desc"></p>
                  <div class="qr-actions">
                    <button class="btn-hc btn-hc-secondary btn-sm">
                      <i class="bi bi-printer"></i> Imprimir
                    </button>
                    <button class="btn-hc btn-hc-primary btn-sm">
                      <i class="bi bi-download"></i> Descargar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── CONFIGURACIÓN ─────────── -->
        <div id="view-configuracion" class="view">
          <div class="page-header">
            <div>
              <h1>Configuración</h1>
              <p class="subtitle">Preferencias de la cuenta y del sistema</p>
            </div>
          </div>
          <div class="row g-4">
            <div class="col-lg-6">
              <div class="panel mb-4">
                <div class="panel-header">
                  <p class="panel-title">Datos de la Cuenta</p>
                </div>
                <div class="panel-body">
                  <div class="mb-3">
                    <label class="hc-label">Nombre</label>
                    <input
                      class="hc-input"
                      value="Dr. Daniel Muñoz"
                      disabled />
                  </div>
                  <div class="mb-3">
                    <label class="hc-label">Usuario Institucional</label>
                    <input
                      class="hc-input"
                      value="daniel.munoz@clinicas.edu.uy"
                      disabled />
                  </div>
                  <div class="mb-0">
                    <label class="hc-label">Rol</label>
                    <input class="hc-input" value="Administrador" disabled />
                  </div>
                </div>
              </div>

              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title">
                    <i class="bi bi-palette"></i> Apariencia
                  </p>
                </div>
                <div class="panel-body">
                  <div class="form-check form-switch mb-0">
                    <input
                      class="form-check-input check-cursor-pointer"
                      type="checkbox"
                      id="cfg-darkmode" />
                    <label
                      class="form-check-label dark-mode-label"
                      for="cfg-darkmode">
                      Modo Oscuro (Estilo EVE)
                    </label>
                    <p class="dark-mode-desc">
                      Cambia la interfaz a una paleta de colores oscuros ideal
                      para ambientes de baja luminosidad.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title">Notificaciones</p>
                </div>
                <div class="panel-body">
                  <div class="form-check form-switch mb-3">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      checked
                      id="cfg-1" />
                    <label class="form-check-label check-label-sm" for="cfg-1">Avisar cuando un documento queda pendiente de
                      firma</label>
                  </div>
                  <div class="form-check form-switch mb-3">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      checked
                      id="cfg-2" />
                    <label class="form-check-label check-label-sm" for="cfg-2">Avisar cuando se genera un código QR</label>
                  </div>
                  <div class="form-check form-switch mb-0">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      id="cfg-3" />
                    <label class="form-check-label check-label-sm" for="cfg-3">Resumen diario por correo</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /page-body -->
    </div>
    <!-- /main-content -->
  </div>
  <!-- /app-shell -->

  <div class="hc-toast" id="toast"></div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
  <script src="../vista/assets/js/panel-de-administracion.js?v=2"></script>
</body>

</html>