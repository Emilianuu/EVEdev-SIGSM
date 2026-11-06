<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['es', 'en'])) {
  $_SESSION['idioma'] = $_GET['lang'];
}
$idiomaActual = $_SESSION['idioma'] ?? 'es';
$lang = require_once "../../servicios_comunes/idiomas/{$idiomaActual}.php";

if (!isset($_SESSION['id_usuario'])) {
  header("Location: ../../servicios_comunes/autenticacion/login_controlador.php");
  exit();
}

$nombreSesion = $_SESSION['nombre_completo'] ?? 'Usuario';
$emailSesion  = $_SESSION['email'] ?? '';
$palabras = explode(' ', trim($nombreSesion));
$iniciales = '';
if (isset($palabras[0])) $iniciales .= strtoupper(substr($palabras[0], 0, 1));
if (isset($palabras[1])) $iniciales .= strtoupper(substr($palabras[1], 0, 1));
?>
<!doctype html>
<html lang="<?= $idiomaActual ?>">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $lang['hospital_nombre'] ?> – <?= $lang['tarjeta_admin_titulo'] ?></title>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../vista/assets/css/panel-de-administracion.css" />

  <!-- EXPORTAMOS EL IDIOMA PARA JAVASCRIPT -->
  <script>
    const LANG = <?= json_encode($lang) ?>;
  </script>
</head>

<body>
  <div id="sidebar-overlay"></div>

  <div id="app-shell">
    <nav id="sidebar">
      <div class="sidebar-brand">
        <img src="../../servicios_comunes/vista_general/banner.jpg" class="brand-img-normal" />
        <img src="../../servicios_comunes/vista_general/banner-reducido.jpg" class="brand-img-collapsed" />
      </div>

      <div class="sidebar-section-label"><?= $lang['doc_sidebar_seccion'] ?></div>
      <div class="sidebar-label-bold"><?= $lang['doc_sidebar_titulo'] ?></div>

      <ul class="sidebar-menu-list mb-0">
        <li class="nav-item-custom">
          <a class="nav-link-custom active btn-nav-view" data-view="view-dashboard" id="nav-inicio" href="#">
            <i class="bi bi-grid-fill"></i> <?= $lang['doc_menu_panel'] ?>
          </a>
        </li>
        <li class="nav-item-custom">
          <a class="nav-link-custom btn-nav-view" data-view="view-documentos" id="nav-documentos" href="#">
            <i class="bi bi-file-earmark-text"></i> <?= $lang['doc_menu_documentos'] ?>
          </a>
        </li>
        <li class="nav-item-custom">
          <a class="nav-link-custom btn-nav-view" data-view="view-generar-qr" id="nav-generar-qr" href="#">
            <i class="bi bi-qr-code"></i> <?= $lang['doc_menu_qr'] ?>
          </a>
        </li>
        <li class="nav-item-custom">
          <a class="nav-link-custom btn-nav-view" data-view="view-configuracion" id="nav-configuracion" href="#">
            <i class="bi bi-gear"></i> <?= $lang['doc_menu_config'] ?>
          </a>
        </li>
      </ul>

      <div class="sidebar-footer">
        <div class="avatar-circle"><?php echo $iniciales; ?></div>
        <div class="footer-user-info">
          <div class="avatar-name" id="user-name"><?php echo htmlspecialchars($nombreSesion); ?></div>
          <div class="avatar-role"><?= $lang['rol_funcionario'] ?></div>
        </div>
      </div>
    </nav>

    <div id="main-content">
      <div class="topbar">
        <button class="toggle-btn btn-toggle-sidebar" title="Menú">
          <i class="bi bi-list"></i>
        </button>
        <div class="topbar-breadcrumb">
          <span class="bc-current" id="topbar-title"><?= $lang['doc_menu_panel'] ?></span>
        </div>
        <div class="topbar-actions ms-auto" style="display:flex; align-items:center; gap: 15px;">
          <button class="btn-hc btn-hc-ghost btn-text-sm" id="btnBackPortal" style="padding: 4px 12px">
            <i class="bi bi-box-arrow-left"></i> <?= $lang['btn_volver_portal'] ?>
          </button>
        </div>
      </div>

      <div class="page-body">
        <!-- ── PANEL PRINCIPAL ─────────── -->
        <div id="view-dashboard" class="view active">
          <div class="page-header">
            <div>
              <h1><?= $lang['doc_menu_panel'] ?></h1>
            </div>
            <span class="badge-hc badge-gray" id="dash-date"></span>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-xl-6 col-md-6">
              <div class="stat-card btn-nav-view" data-view="view-documentos" style="cursor: pointer;">
                <p class="stat-label"><i class="bi bi-file-earmark-text"></i> <?= $lang['doc_dash_total'] ?></p>
                <p class="stat-value" id="stat-total">0</p>
                <p class="stat-delta"><?= $lang['doc_dash_total_sub'] ?></p>
              </div>
            </div>
            <div class="col-xl-6 col-md-6">
              <div class="stat-card btn-nav-view" data-view="view-generar-qr" style="cursor: pointer;">
                <p class="stat-label"><i class="bi bi-qr-code"></i> <?= $lang['doc_dash_qr'] ?></p>
                <p class="stat-value" id="stat-qr">0</p>
                <p class="stat-delta"><?= $lang['doc_dash_qr_sub'] ?></p>
              </div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-12">
              <div class="panel h-100">
                <div class="panel-header">
                  <p class="panel-title"><i class="bi bi-clock-history"></i> <?= $lang['doc_dash_cargas_recientes'] ?></p>
                  <button class="btn-hc btn-hc-ghost btn-xs btn-nav-view" data-view="view-documentos">
                    <?= $lang['doc_dash_btn_ver_todos'] ?> <i class="bi bi-arrow-right"></i>
                  </button>
                </div>
                <div class="table-responsive-wrapper">
                  <table class="hc-table">
                    <thead>
                      <tr>
                        <th><?= $lang['doc_th_id'] ?></th>
                        <th><?= $lang['doc_th_tipo_archivo'] ?></th>
                      </tr>
                    </thead>
                    <tbody id="dash-doc-table"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── DOCUMENTOS CLÍNICOS ─────────── -->
        <div id="view-documentos" class="view">
          <div class="page-header">
            <div>
              <h1><?= $lang['doc_titulo_clinicos'] ?></h1>
              <p class="subtitle"><?= $lang['doc_subtitulo_clinicos'] ?></p>
            </div>
            <button class="btn-hc btn-hc-primary btn-nav-view" data-view="view-nuevo-doc">
              <i class="bi bi-plus-lg"></i> <?= $lang['doc_btn_nuevo'] ?>
            </button>
          </div>

          <div class="panel">
            <div class="panel-header panel-header-wrap">
              <div class="search-wrap search-wrap-flexible">
                <i class="bi bi-search"></i>
                <input type="text" id="searchDoc" class="hc-input" placeholder="<?= $lang['doc_placeholder_buscar'] ?>" />
              </div>
              <select class="hc-input select-sm" id="filterCat">
                <option value=""><?= $lang['doc_filtro_todos'] ?></option>
                <option value="Protocolo"><?= $lang['doc_filtro_protocolo'] ?></option>
                <option value="Consentimiento"><?= $lang['doc_filtro_consentimiento'] ?></option>
                <option value="Instructivo"><?= $lang['doc_filtro_instructivo'] ?></option>
              </select>
            </div>
            <div class="table-responsive-wrapper">
              <table class="hc-table">
                <thead>
                  <tr>
                    <th><?= $lang['doc_th_codigo'] ?></th>
                    <th><?= $lang['doc_th_nombre'] ?></th>
                    <th><?= $lang['doc_th_fecha_carga'] ?></th>
                    <th class="text-right"><?= $lang['traslados_th_acciones'] ?></th>
                  </tr>
                </thead>
                <tbody id="admin-doc-list-table"></tbody>
              </table>
            </div>
            <div class="panel-footer">
              <span class="doc-count-text" id="doc-count-label"></span>
              <div class="pagination-controls"></div>
            </div>
          </div>
        </div>

        <!-- ── NUEVO DOCUMENTO ──────────────── -->
        <div id="view-nuevo-doc" class="view">
          <div class="page-header">
            <div>
              <h1><?= $lang['doc_titulo_nuevo'] ?></h1>
              <p class="subtitle"><?= $lang['doc_subtitulo_nuevo'] ?></p>
            </div>
            <button class="btn-hc btn-hc-secondary btn-nav-view" data-view="view-documentos">
              <i class="bi bi-arrow-left"></i> <?= $lang['doc_btn_volver'] ?>
            </button>
          </div>

          <div class="row g-4">
            <div class="col-lg-8">
              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title"><i class="bi bi-pencil-square"></i> <?= $lang['doc_panel_info'] ?></p>
                </div>
                <div class="panel-body">
                  <div class="col-md-12 mb-3">
                    <label class="hc-label"><?= $lang['doc_label_titulo'] ?> <span class="text-red-asterisk">*</span></label>
                    <input type="text" class="hc-input" id="nd-titulo" placeholder="<?= $lang['doc_placeholder_titulo'] ?>" />
                  </div>
                  <div class="col-md-12">
                    <label class="hc-label"><?= $lang['doc_label_categoria'] ?> <span class="text-red-asterisk">*</span></label>
                    <select class="hc-input" id="nd-categoria">
                      <option value=""><?= $lang['doc_select_categoria'] ?></option>
                      <option value="1"><?= $lang['doc_filtro_protocolo'] ?></option>
                      <option value="2"><?= $lang['doc_filtro_consentimiento'] ?></option>
                      <option value="3"><?= $lang['doc_filtro_instructivo'] ?></option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="panel mb-3">
                <div class="panel-header">
                  <p class="panel-title"><i class="bi bi-paperclip"></i> <?= $lang['doc_panel_adjuntar'] ?></p>
                </div>
                <div class="panel-body">
                  <div class="upload-zone" id="uploadZoneArea">
                    <div id="uploadZoneDefault">
                      <i class="bi bi-cloud-upload upload-icon-lg"></i>
                      <div class="upload-text-bold"><?= $lang['doc_upload_clic'] ?></div>
                      <div class="upload-text-sm"><?= $lang['doc_upload_limite'] ?></div>
                    </div>
                    <div id="uploadZoneSelected" style="display: none;">
                      <i class="bi bi-file-earmark-pdf-fill" style="font-size: 2.2rem; color: var(--hc-red); display: block; margin-bottom: 10px;"></i>
                      <div class="upload-text-bold" id="selectedFileName" style="word-break: break-all;">archivo.pdf</div>
                      <div class="upload-text-sm" style="color: var(--hc-blue); margin-top: 6px;"><?= $lang['doc_upload_cambiar'] ?></div>
                    </div>
                    <input type="file" accept=".pdf" class="hidden-file-input" id="hiddenFileInput" />
                  </div>
                </div>
              </div>
              <div class="info-banner">
                <i class="bi bi-qr-code"></i> <?= $lang['doc_info_qr'] ?>
              </div>
            </div>
            <div class="col-12">
              <div class="flex-end-gap">
                <button class="btn-hc btn-hc-secondary btn-nav-view" data-view="view-documentos"><?= $lang['doc_btn_cancelar'] ?></button>
                <button class="btn-hc btn-hc-primary" id="btnSaveDoc"><i class="bi bi-floppy"></i> <?= $lang['doc_btn_guardar'] ?></button>
              </div>
            </div>
          </div>
        </div>

        <!-- ── EDITAR DOCUMENTO ──────────────── -->
        <div id="view-editar-doc" class="view">
          <div class="page-header">
            <div>
              <h1><?= $lang['doc_titulo_editar'] ?></h1>
              <p class="subtitle"><?= $lang['doc_subtitulo_editar'] ?></p>
            </div>
            <button class="btn-hc btn-hc-secondary" onclick="setAdminView('view-documento-detalle')">
              <i class="bi bi-arrow-left"></i> <?= $lang['doc_btn_volver_doc'] ?>
            </button>
          </div>

          <div class="row g-4">
            <div class="col-lg-8">
              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title"><i class="bi bi-pencil-square"></i> <?= $lang['doc_panel_info'] ?></p>
                </div>
                <div class="panel-body">
                  <input type="hidden" id="ed-id" />
                  <div class="col-md-12 mb-3">
                    <label class="hc-label"><?= $lang['doc_label_titulo'] ?> <span class="text-red-asterisk">*</span></label>
                    <input type="text" class="hc-input" id="ed-titulo" />
                  </div>
                  <div class="col-md-12">
                    <label class="hc-label"><?= $lang['doc_label_categoria'] ?> <span class="text-red-asterisk">*</span></label>
                    <select class="hc-input" id="ed-categoria">
                      <option value=""><?= $lang['doc_select_categoria'] ?></option>
                      <option value="1"><?= $lang['doc_filtro_protocolo'] ?></option>
                      <option value="2"><?= $lang['doc_filtro_consentimiento'] ?></option>
                      <option value="3"><?= $lang['doc_filtro_instructivo'] ?></option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="panel mb-3">
                <div class="panel-header">
                  <p class="panel-title"><i class="bi bi-paperclip"></i> <?= $lang['doc_panel_reemplazar'] ?></p>
                </div>
                <div class="panel-body">
                  <div class="upload-zone" id="uploadZoneAreaEd">
                    <div id="uploadZoneDefaultEd">
                      <i class="bi bi-cloud-upload upload-icon-lg"></i>
                      <div class="upload-text-bold"><?= $lang['doc_upload_clic_nuevo'] ?></div>
                      <div class="upload-text-sm"><?= $lang['doc_upload_opcional'] ?></div>
                    </div>
                    <div id="uploadZoneSelectedEd" style="display: none;">
                      <i class="bi bi-file-earmark-pdf-fill" style="font-size: 2.2rem; color: var(--hc-red); display: block; margin-bottom: 10px;"></i>
                      <div class="upload-text-bold" id="selectedFileNameEd" style="word-break: break-all;">archivo.pdf</div>
                      <div class="upload-text-sm" style="color: var(--hc-blue); margin-top: 6px;"><?= $lang['doc_upload_cambiar'] ?></div>
                    </div>
                    <input type="file" accept=".pdf" class="hidden-file-input" id="hiddenFileInputEd" />
                  </div>
                </div>
              </div>
              <div class="info-banner">
                <i class="bi bi-info-circle"></i> <?= $lang['doc_info_mantener'] ?>
              </div>
            </div>

            <div class="col-12">
              <div class="flex-end-gap">
                <button class="btn-hc btn-hc-secondary" onclick="setAdminView('view-documento-detalle')"><?= $lang['doc_btn_cancelar'] ?></button>
                <button class="btn-hc btn-hc-primary" id="btnUpdateDoc">
                  <i class="bi bi-floppy"></i> <?= $lang['doc_btn_actualizar'] ?>
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
                <a href="#" class="breadcrumb-link btn-nav-view" data-view="view-documentos"><?= $lang['doc_visor_breadcrumb'] ?></a>
                &nbsp;›&nbsp; <?= $lang['doc_visor_detalle'] ?>
              </p>
              <h1 id="dd-title">Documento</h1>
            </div>
            <div class="header-actions-flex">
              <button class="btn-hc btn-hc-secondary btn-nav-view" data-view="view-documentos">
                <i class="bi bi-arrow-left"></i> <?= $lang['doc_btn_volver'] ?>
              </button>
              <button id="btnEditDoc" class="btn-hc btn-hc-secondary">
                <i class="bi bi-pencil"></i> <?= $lang['doc_visor_btn_editar'] ?>
              </button>
              <a id="btnDownloadDoc" href="#" target="_blank" class="btn-hc btn-hc-primary" download>
                <i class="bi bi-download"></i> <?= $lang['doc_visor_btn_descargar'] ?>
              </a>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-lg-8">
              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title"><?= $lang['doc_visor_panel_pdf'] ?></p>
                </div>
                <div class="panel-body">
                  <div class="viewer-box" id="dd-content" style="height: 650px;"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title"><?= $lang['doc_visor_panel_info'] ?></p>
                </div>
                <div class="panel-body">
                  <div id="dd-info"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── GENERADOR DE ACCESOS QR ─────────── -->
        <div id="view-generar-qr" class="view">
          <div class="page-header">
            <div>
              <h1><?= $lang['qr_titulo'] ?></h1>
              <p class="subtitle"><?= $lang['qr_subtitulo'] ?></p>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-lg-7">
              <div class="panel">
                <div class="panel-header">
                  <p class="panel-title"><?= $lang['qr_panel_parametros'] ?></p>
                </div>
                <div class="panel-body">
                  <div class="mb-3">
                    <label class="hc-label"><?= $lang['qr_label_seleccionar'] ?></label>
                    <select class="hc-input" id="qr-documento">
                      <option value=""><?= $lang['qr_select_doc'] ?></option>
                    </select>
                  </div>
                  <div class="mb-4">
                    <label class="hc-label"><?= $lang['qr_label_plazo'] ?></label>
                    <select class="hc-input" id="qr-expiracion">
                      <option><?= $lang['qr_plazo_24h'] ?></option>
                      <option><?= $lang['qr_plazo_48h'] ?></option>
                      <option><?= $lang['qr_plazo_7d'] ?></option>
                      <option><?= $lang['qr_plazo_nunca'] ?></option>
                    </select>
                  </div>
                  <button class="btn-hc btn-hc-primary w-100 flex-center-content" id="btnGenerarQr">
                    <?= $lang['qr_btn_generar'] ?>
                  </button>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="panel h-100">
                <div class="panel-header">
                  <p class="panel-title"><?= $lang['qr_panel_listo'] ?></p>
                </div>
                <div class="panel-body text-center" id="qr-empty">
                  <div class="empty-qr-box">
                    <i class="bi bi-qr-code qr-icon-lg"></i>
                    <?= $lang['qr_empty_state'] ?>
                  </div>
                </div>
                <div class="panel-body text-center hidden-by-default" id="qr-result">
                  <div class="qr-code-img qr-result-img-wrapper">
                    <!-- Contenedor del QR -->
                  </div>
                  <p class="qr-result-patient" id="qr-result-doc-title"></p>
                  <p class="qr-result-desc" id="qr-result-desc"></p>
                  <div class="qr-actions">
                    <button class="btn-hc btn-hc-secondary btn-sm"><i class="bi bi-printer"></i> <?= $lang['qr_btn_imprimir'] ?></button>
                    <button class="btn-hc btn-hc-primary btn-sm"><i class="bi bi-download"></i> <?= $lang['qr_btn_descargar'] ?></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- CONFIGURACIÓN -->
        <!-- CONFIGURACIÓN -->
        <section class="view" id="view-configuracion">
          <div class="page-header">
            <div>
              <h1><?= $lang['config_titulo'] ?></h1>
            </div>
          </div>
          <div class="row g-4 align-items-stretch">
            <!-- Tarjeta Modo Oscuro -->
            <div class="col-lg-6">
              <div class="panel h-100">
                <div class="panel-header">
                  <p class="panel-title"><i class="bi bi-palette"></i> <?= $lang['config_panel_apariencia'] ?></p>
                </div>
                <div class="panel-body">
                  <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input check-cursor-pointer" type="checkbox" id="cfg-darkmode" />
                    </div>
                    <label class="hc-label check-cursor-pointer form-check-label-title mb-0" for="cfg-darkmode"><?= $lang['config_label_darkmode'] ?></label>
                  </div>
                  <p class="subtitle form-check-description" style="margin-left: 50px;"><?= $lang['config_desc_darkmode'] ?></p>
                </div>
              </div>
            </div>

            <!-- Tarjeta de Idioma -->
            <div class="col-lg-6">
              <div class="panel h-100">
                <div class="panel-header">
                  <p class="panel-title"><i class="bi bi-globe"></i> <?= $lang['config_idioma'] ?? 'Idioma / Language' ?></p>
                </div>
                <div class="panel-body">
                  <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <a href="?lang=es" class="btn-hc <?= $idiomaActual == 'es' ? 'btn-hc-primary' : 'btn-hc-secondary' ?>" style="text-decoration: none;">Español</a>
                    <a href="?lang=en" class="btn-hc <?= $idiomaActual == 'en' ? 'btn-hc-primary' : 'btn-hc-secondary' ?>" style="text-decoration: none;">English</a>
                  </div>
                  <p class="subtitle form-check-description"><?= $lang['config_desc_idioma'] ?? 'Seleccione el idioma de la interfaz del sistema.' ?></p>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <!-- ── MODAL ELIMINAR ─────────── -->
  <div class="qr-modal-overlay" id="modalEliminarOverlay">
    <div class="qr-modal" style="max-width: 400px; padding: 30px;">
      <i class="bi bi-exclamation-octagon" style="font-size: 3rem; color: var(--hc-red); display: block; margin-bottom: 15px;"></i>
      <h3 style="margin-bottom: 10px;"><?= $lang['modal_eliminar_titulo'] ?></h3>
      <p style="margin-bottom: 25px; color: var(--hc-gray-600);"><?= $lang['modal_eliminar_desc'] ?> <strong id="deleteDocName" style="color: var(--hc-gray-900);"></strong>. <?= $lang['modal_eliminar_advertencia'] ?></p>
      <div style="display: flex; gap: 10px; justify-content: center;">
        <button class="btn-hc btn-hc-secondary" id="btnCancelDelete"><?= $lang['modal_btn_cancelar'] ?></button>
        <button class="btn-hc btn-hc-danger" id="btnConfirmDelete"><?= $lang['modal_btn_eliminar'] ?></button>
      </div>
    </div>
  </div>

  <div class="hc-toast" id="toast"></div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
  <script src="../vista/assets/js/panel-de-administracion.js?v=5"></script>
</body>

</html>