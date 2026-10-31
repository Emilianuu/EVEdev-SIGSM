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
$palabras = explode(' ', trim($nombreSesion));
$iniciales = '';
if (isset($palabras[0])) $iniciales .= strtoupper(substr($palabras[0], 0, 1));
if (isset($palabras[1])) $iniciales .= strtoupper(substr($palabras[1], 0, 1));

// =======================================================================
// CARGA DINÁMICA DE RECURSOS DESDE LA BD
// =======================================================================
require_once $_SERVER['DOCUMENT_ROOT'] . '/sigsm/servicios_comunes/conexion_BD/conexion.php';

// Arrays para los selects del formulario
$vehiculosDisponibles = [];
$conductoresDisponibles = [];
$enfermerosDisponibles = [];
$destinosDisponibles = [];

// Arrays y contadores para la pestaña de Recursos (Valores por defecto)
$todosVehiculos = [];
$vehiculosTotales = 0;
$vehiculosDispCount = 0;

$todosConductores = [];
$conductoresTotales = 0;
$conductoresDispCount = 0;

$todosEnfermeros = [];
$enfermerosTotales = 0;
$enfermerosDispCount = 0;

try {
  // 1. Vehículos (Solo los Disponibles - Para el formulario)
  $sqlVehiculos = "SELECT v.id_vehiculo, v.matricula, v.marca, v.modelo, 
                            tv.nombre as tipo, tv.apto_silla_ruedas, tv.apto_camilla, 
                            tv.apto_uti, tv.apto_biologico, tv.apto_insumos 
                     FROM VEHICULO v 
                     JOIN TIPO_VEHICULO tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo 
                     WHERE v.estado_disponibilidad = 'DISPONIBLE' AND v.activo = TRUE";
  $vehiculosDisponibles = $conexion->query($sqlVehiculos)->fetchAll(PDO::FETCH_ASSOC);

  // 2. Conductores (Activos - Para el formulario)
  $sqlConductores = "SELECT id_personal, nombre, apellido FROM PERSONAL WHERE rol_personal = 'CONDUCTOR' AND activo = TRUE";
  $conductoresDisponibles = $conexion->query($sqlConductores)->fetchAll(PDO::FETCH_ASSOC);

  // 3. Enfermeros (Activos - Para el formulario)
  $sqlEnfermeros = "SELECT id_personal, nombre, apellido FROM PERSONAL WHERE rol_personal = 'ENFERMERO' AND activo = TRUE";
  $enfermerosDisponibles = $conexion->query($sqlEnfermeros)->fetchAll(PDO::FETCH_ASSOC);

  // 4. Destinos (Cualquier ubicación que no sea el Hospital ID 1)
  $sqlDestinos = "SELECT id_ubicacion, nombre, tipo_ubicacion FROM UBICACION WHERE id_ubicacion != 1";
  $destinosDisponibles = $conexion->query($sqlDestinos)->fetchAll(PDO::FETCH_ASSOC);

  // =========================================================
  // DATOS PARA LA PESTAÑA "RECURSOS" (ESTADÍSTICAS EN VIVO)
  // =========================================================

  // VEHÍCULOS (Todos)
  $sqlTodosVehiculos = "SELECT v.id_vehiculo, v.matricula, tv.nombre as tipo, v.estado_disponibilidad,
                          (SELECT CONCAT(p.nombre, ' ', p.apellido) 
                           FROM PERSONAL p JOIN TRASLADO t ON t.id_vehiculo = v.id_vehiculo 
                           JOIN PERSONAL_TRASLADO pt ON pt.id_traslado = t.id_traslado 
                           WHERE pt.id_personal = p.id_personal AND pt.rol_en_traslado = 'CONDUCTOR' 
                           AND v.estado_disponibilidad = 'EN_USO' ORDER BY t.id_traslado DESC LIMIT 1) as conductor_asignado
                        FROM VEHICULO v 
                        JOIN TIPO_VEHICULO tv ON v.id_tipo_vehiculo = tv.id_tipo_vehiculo";
  $todosVehiculos = $conexion->query($sqlTodosVehiculos)->fetchAll(PDO::FETCH_ASSOC);
  $vehiculosTotales = count($todosVehiculos);
  $vehiculosDispCount = count(array_filter($todosVehiculos, function ($v) {
    return $v['estado_disponibilidad'] === 'DISPONIBLE';
  }));

  // CONDUCTORES (Todos)
  $sqlTodosConductores = "SELECT p.id_personal, p.nombre, p.apellido, p.ci, p.activo,
                            (SELECT v.matricula 
                             FROM VEHICULO v JOIN TRASLADO t ON t.id_vehiculo = v.id_vehiculo 
                             JOIN PERSONAL_TRASLADO pt ON pt.id_traslado = t.id_traslado 
                             WHERE pt.id_personal = p.id_personal AND pt.rol_en_traslado = 'CONDUCTOR' 
                             AND v.estado_disponibilidad = 'EN_USO' ORDER BY t.id_traslado DESC LIMIT 1) as vehiculo_asignado
                          FROM PERSONAL p WHERE p.rol_personal = 'CONDUCTOR'";
  $todosConductores = $conexion->query($sqlTodosConductores)->fetchAll(PDO::FETCH_ASSOC);
  $conductoresTotales = count($todosConductores);
  $conductoresDispCount = count(array_filter($todosConductores, function ($c) {
    return $c['activo'] == 1 && empty($c['vehiculo_asignado']);
  }));

  // ENFERMEROS (Todos)
  $sqlTodosEnfermeros = "SELECT p.id_personal, p.nombre, p.apellido, p.ci, p.activo,
                           (SELECT t.id_traslado 
                            FROM TRASLADO t JOIN PERSONAL_TRASLADO pt ON pt.id_traslado = t.id_traslado 
                            JOIN VEHICULO v ON t.id_vehiculo = v.id_vehiculo
                            WHERE pt.id_personal = p.id_personal AND pt.rol_en_traslado = 'ENFERMERO' 
                            AND v.estado_disponibilidad = 'EN_USO' ORDER BY t.id_traslado DESC LIMIT 1) as traslado_asignado
                         FROM PERSONAL p WHERE p.rol_personal = 'ENFERMERO'";
  $todosEnfermeros = $conexion->query($sqlTodosEnfermeros)->fetchAll(PDO::FETCH_ASSOC);
  $enfermerosTotales = count($todosEnfermeros);
  $enfermerosDispCount = count(array_filter($todosEnfermeros, function ($e) {
    return $e['activo'] == 1 && empty($e['traslado_asignado']);
  }));
} catch (Exception $e) {
  error_log("Error cargando listas dinámicas: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hospital de Clínicas – Ambulancias y Traslados</title>

  <!-- Bootstrap 5.3 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Estilos propios -->
  <link rel="stylesheet" href="../vista/assets/css/panel-de-gestion.css" />
</head>

<body>
  <!-- SIDEBAR -->
  <aside id="sidebar">
    <div class="sidebar-brand">
      <img src="../../servicios_comunes/vista_general/banner.jpg" alt="Banner Hospital de Clínicas" class="brand-img-normal" />
      <img src="../../servicios_comunes/vista_general/banner-reducido.jpg" alt="Ícono Hospital de Clínicas" class="brand-img-collapsed" />
    </div>

    <div class="sidebar-section-label">Sistema de Gestión</div>
    <div class="sidebar-label-bold">Ambulancias y Traslados</div>

    <ul class="list-unstyled mb-0 sidebar-menu-list">
      <li class="nav-item-custom">
        <a class="nav-link-custom active" data-view="dashboard" href="#">
          <i class="bi bi-grid-fill"></i> Panel Principal
        </a>
      </li>
      <li class="nav-item-custom">
        <a class="nav-link-custom" data-view="traslados" href="#">
          <i class="bi bi-truck"></i> Traslados
        </a>
      </li>
      <li class="nav-item-custom">
        <a class="nav-link-custom" data-view="nuevo-traslado" href="#">
          <i class="bi bi-plus-circle"></i> Nuevo Traslado
        </a>
      </li>
      <li class="nav-item-custom">
        <a class="nav-link-custom" data-view="seguimiento" href="#">
          <i class="bi bi-geo-alt-fill"></i> Seguimiento
        </a>
      </li>
      <li class="nav-item-custom">
        <a class="nav-link-custom" data-view="recursos" href="#">
          <i class="bi bi-gear-fill"></i> Recursos
        </a>
      </li>
      <li class="nav-item-custom">
        <a class="nav-link-custom" data-view="configuracion" href="#">
          <i class="bi bi-sliders"></i> Configuración
        </a>
      </li>
    </ul>

    <div class="sidebar-footer">
      <div class="avatar-circle"><?php echo $iniciales; ?></div>
      <div class="footer-user-info">
        <div class="avatar-name"><?php echo htmlspecialchars($nombreSesion); ?></div>
        <div class="avatar-role">Funcionario</div>
      </div>
    </div>
  </aside>

  <!-- ─── MAIN CONTENT ─────────────────────────────── -->
  <main id="main">
    <div class="topbar">
      <button class="toggle-btn" id="mobileToggle" title="Menú">
        <i class="bi bi-list"></i>
      </button>
      <div class="topbar-breadcrumb">
        <span class="bc-current" id="topbar-title">Panel Principal</span>
      </div>
      <div class="topbar-actions ms-auto">
        <button class="btn btn-outline-secondary border-0" id="btnLogout" style="padding: 4px 12px; font-size: 0.82rem; font-weight: 600">
          <i class="bi bi-box-arrow-left"></i> Volver al portal principal
        </button>
      </div>
    </div>

    <!-- DASHBOARD -->
    <section class="view active" id="view-dashboard">
      <div class="page-title">Panel Principal</div>

      <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="dash-card dc-blue" style="cursor: pointer;" onclick="document.querySelector('[data-view=\'traslados\']').click();">
            <div>
              <div class="dc-val" id="dash-traslados-activos"><span class="spinner-border spinner-border-sm"></span></div>
              <div class="dc-label">Traslados Activos</div>
            </div>
            <i class="bi bi-plus-circle"></i>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="dash-card dc-green" style="cursor: pointer;" onclick="document.querySelector('[data-view=\'recursos\']').click();">
            <div>
              <div class="dc-val"><?= $vehiculosDispCount ?>/<?= $vehiculosTotales ?></div>
              <div class="dc-label">Vehículos Disponibles</div>
            </div>
            <i class="bi bi-truck"></i>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="dash-card dc-orange" style="cursor: pointer;" onclick="document.querySelector('[data-view=\'recursos\']').click();">
            <div>
              <div class="dc-val"><?= ($conductoresTotales - $conductoresDispCount) + ($enfermerosTotales - $enfermerosDispCount) ?></div>
              <div class="dc-label">Personal en Servicio</div>
            </div>
            <i class="bi bi-person"></i>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="dash-card dc-purple">
            <div>
              <div class="dc-val" id="dash-traslados-dia"><span class="spinner-border spinner-border-sm"></span></div>
              <div class="dc-label">Traslados del Día</div>
            </div>
            <i class="bi bi-calendar3"></i>
          </div>
        </div>
      </div>

      <div class="section-card">
        <h5>Traslados Activos en Curso</h5>
        <div id="dash-lista-activos">
          <div class="text-center text-muted py-3">
            <div class="spinner-border spinner-border-sm me-2"></div> Cargando traslados activos...
          </div>
        </div>
      </div>
    </section>

    <!-- TRASLADOS -->
    <section class="view" id="view-traslados">
      <div class="page-title mb-4">Gestión de Traslados y Solicitudes</div>

      <div class="section-card mb-5 py-3 px-4">
        <div class="row align-items-center g-3">
          <div class="col-md-3 text-muted fw-bold">
            Filtros de búsqueda:
          </div>
          <div class="col-md-4">
            <select id="filtro-estado" class="form-select border-primary">
              <option value="">Todos los estados</option>
              <option value="pendiente">Solo Solicitudes Pendientes</option>
              <option value="asignado">Traslados Asignados (En base)</option>
              <option value="camino">Traslados En Camino / Tránsito</option>
              <option value="completado">Traslados Completados</option>
              <option value="cancelado">Traslados Cancelados</option>
            </select>
          </div>
          <div class="col-md-5">
            <div class="input-group">
              <span class="input-group-text border-primary bg-transparent text-primary"><i class="bi bi-search"></i></span>
              <input type="text" id="filtro-texto" class="form-control border-primary" placeholder="Buscar paciente, ID, destino, vehículo...">
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 1: TRASLADOS CONFIRMADOS -->
      <h5 class="mb-3 fw-bold">Traslados Confirmados</h5>

      <div id="alert-traslados-container"></div>

      <div class="section-card p-0 overflow-hidden mb-5">
        <div class="table-responsive">
          <table class="table table-custom mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Fecha/Hora</th>
                <th>Vehículo</th>
                <th>Conductor</th>
                <th>Enfermero</th>
                <th>Paciente/Carga</th>
                <th>Destino</th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla-traslados-body">
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  <div class="spinner-border spinner-border-sm me-2"></div> Cargando traslados...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SECCIÓN 2: SOLICITUDES PENDIENTES -->
      <h5 class="mb-3 fw-bold">Solicitudes Pendientes de Aprobación</h5>

      <div id="alert-solicitudes-container"></div>

      <div class="section-card p-0 overflow-hidden">
        <div class="table-responsive">
          <table class="table table-custom mb-0">
            <thead class="table-light">
              <tr>
                <th>ID Sol.</th>
                <th>Fecha Creada</th>
                <th>Categoría</th>
                <th>Paciente / Carga</th>
                <th>Destino</th>
                <th>Motivo</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla-solicitudes-body">
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <div class="spinner-border spinner-border-sm me-2"></div> Cargando solicitudes...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- NUEVO TRASLADO -->
    <section class="view" id="view-nuevo-traslado">
      <div class="page-title">Nueva Solicitud de Traslado</div>

      <div class="section-card">
        <!-- Wizard Header -->
        <div class="wizard-steps mb-4">
          <div class="wizard-step active">
            <div class="step-num">1</div>
            <span>Tipo</span>
          </div>
          <div class="step-line"></div>
          <div class="wizard-step">
            <div class="step-num">2</div>
            <span>Datos</span>
          </div>
          <div class="step-line"></div>
          <div class="wizard-step">
            <div class="step-num">3</div>
            <span>Ruta</span>
          </div>
        </div>

        <form id="formNuevoTraslado" novalidate>
          <!-- PASO 1: TIPO DE TRASLADO -->
          <div class="wizard-panel" data-step="1">
            <p class="wizard-subtitle">Seleccione el tipo de traslado</p>
            <div class="col-md-12 mb-3">
              <label class="form-label form-label-custom">Categoría <span class="text-danger">*</span></label>
              <select class="form-select" name="categoria_traslado" id="categoria_traslado" required>
                <option value="" selected disabled>Seleccione...</option>
                <option value="PACIENTE">Paciente (Humano)</option>
                <option value="MATERIAL_BIOLOGICO">Material Biológico / Órganos</option>
                <option value="EQUIPAMIENTO">Equipamiento Médico</option>
                <option value="INSUMOS">Insumos / Farmacia</option>
              </select>
            </div>
            <div class="info-box mt-3">
              <i class="bi bi-info-circle-fill"></i>
              La solicitud se guardará como PENDIENTE para su posterior despacho.
            </div>
          </div>

          <!-- PASO 2: DATOS DEL TRASLADO -->
          <div class="wizard-panel d-none" data-step="2">
            <p class="wizard-subtitle">Información Específica</p>
            <div id="seccion_paciente" class="d-none">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label form-label-custom">Cédula de Identidad <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="ci_paciente" id="ci_paciente" placeholder="Ej: 12345678">
                  <div class="invalid-feedback" id="feedback-ci">Por favor, ingrese una cédula válida (sin puntos ni guiones).</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label form-label-custom">Modo de Movilidad <span class="text-danger">*</span></label>
                  <select class="form-select" name="tipo_movilidad" id="tipo_movilidad">
                    <option value="AMBULATORIO">Ambulatorio (Camina)</option>
                    <option value="SILLA_RUEDAS">Silla de Ruedas</option>
                    <option value="CAMILLA">Camilla</option>
                  </select>
                </div>
                <div class="col-12 mt-2">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="requiere_soporte_uti" id="requiere_soporte_uti" value="1">
                    <label class="form-check-label text-danger fw-bold" for="requiere_soporte_uti">Requiere Soporte Vital / UTI</label>
                  </div>
                </div>
              </div>
            </div>
            <div id="seccion_logistica" class="d-none">
              <div class="col-12">
                <label class="form-label form-label-custom">Detalle exacto de la carga <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="detalle_carga" id="detalle_carga" placeholder="Ej: 10 Tubos de sangre">
              </div>
            </div>
            <div class="col-12 mt-3">
              <label class="form-label form-label-custom">Motivo del Traslado <span class="text-danger">*</span></label>
              <textarea class="form-control" name="motivo" rows="2" placeholder="Detalles adicionales..." required></textarea>
            </div>
          </div>

          <!-- PASO 3: RUTA Y HORARIOS -->
          <div class="wizard-panel d-none" data-step="3">
            <p class="wizard-subtitle">Establecer Origen, Destino y Hora</p>
            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label form-label-custom">Origen</label>
                <input type="text" class="form-control text-muted bg-light" value="Hospital de Clínicas" readonly />
                <input type="hidden" name="origen" value="1" />
              </div>

              <div class="col-md-6">
                <label class="form-label form-label-custom">Destino <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="destino" placeholder="Ej: Sanatorio Americano, Domicilio..." required />
              </div>

              <div class="col-md-6">
                <label class="form-label form-label-custom">Fecha <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="fecha" required />
              </div>

              <div class="col-md-6">
                <label class="form-label form-label-custom">Hora estimada <span class="text-danger">*</span></label>
                <input type="time" class="form-control" name="hora" required />
              </div>

            </div>
          </div>

          <div id="form-alert-container"></div>
          <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
            <button type="button" class="btn btn-outline-secondary btn-wizard-prev" id="btnPrevStep" disabled><i class="bi bi-chevron-left"></i> ANTERIOR</button>
            <button type="button" class="btn btn-primary btn-wizard-next" id="btnNextStep">SIGUIENTE <i class="bi bi-chevron-right"></i></button>
            <button type="submit" class="btn btn-success d-none" id="btnSubmitForm"><i class="bi bi-floppy"></i> REGISTRAR SOLICITUD</button>
          </div>
        </form>
      </div>
    </section>

    <!-- MODAL ASIGNAR RECURSOS (Agregar antes de cerrar el body) -->
    <div class="modal fade" id="modalAsignarRecursos" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold text-primary"><i class="bi bi-truck me-2"></i>Despacho: Asignar Recursos</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formAsignarRecursos">
            <div class="modal-body">
              <input type="hidden" name="id_solicitud" id="modal_id_solicitud" />
              <div id="modal-alert-container"></div>
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label fw-bold">Vehículo <span class="text-danger">*</span></label>
                  <select class="form-select" name="vehiculo" id="modal_vehiculo_select" required>
                    <option value="" selected disabled>Seleccionar vehículo idóneo...</option>
                    <?php foreach ($vehiculosDisponibles as $v): ?>
                      <option value="<?= $v['id_vehiculo'] ?>" data-sr="<?= $v['apto_silla_ruedas'] ?>" data-cam="<?= $v['apto_camilla'] ?>" data-uti="<?= $v['apto_uti'] ?>" data-bio="<?= $v['apto_biologico'] ?>" data-ins="<?= $v['apto_insumos'] ?>">
                        <?= htmlspecialchars($v['tipo'] . ' - ' . $v['marca'] . ' ' . $v['modelo'] . ' (' . $v['matricula'] . ')') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div id="modal_vehiculo_warning" class="text-danger mt-1 d-none" style="font-size: 0.85rem;">No hay vehículos que cumplan los requisitos.</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Conductor <span class="text-danger">*</span></label>
                  <select class="form-select" name="conductor" required>
                    <option value="" selected disabled>Seleccionar...</option>
                    <?php foreach ($conductoresDisponibles as $c): ?>
                      <option value="<?= $c['id_personal'] ?>"><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Enfermero</label>
                  <select class="form-select" name="enfermero">
                    <option value="" selected>Ninguno / No requiere</option>
                    <?php foreach ($enfermerosDisponibles as $e): ?>
                      <option value="<?= $e['id_personal'] ?>"><?= htmlspecialchars($e['nombre'] . ' ' . $e['apellido']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-success" id="btnConfirmarAsignacion"><i class="bi bi-check2-circle"></i> Confirmar Despacho</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- SEGUIMIENTO -->
    <section class="view" id="view-seguimiento">
      <div class="page-title mb-4">Seguimiento de Traslado</div>

      <!-- NUEVO: CONTENEDOR DE ALERTAS DE SEGUIMIENTO -->
      <div id="alert-seguimiento-container"></div>

      <div class="section-card mb-4 py-3 px-4">
        <div class="section-card mb-4 py-3 px-4">
          <div class="row align-items-center g-3">
            <div class="col-md-6 col-lg-5">
              <label class="form-label fw-bold text-primary mb-1"><i class="bi bi-search me-1"></i> Buscar Traslado Manualmente</label>
              <div class="input-group">
                <span class="input-group-text border-primary bg-transparent text-primary fw-bold">AMB-</span>
                <input type="number" id="input-buscar-seguimiento" class="form-control border-primary" placeholder="Ej: 3" min="1" oninput="this.value = Math.abs(this.value)">
                <button class="btn btn-primary" type="button" id="btn-buscar-seguimiento">Rastrear</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Estado vacío por defecto -->
        <div id="seg-empty-state" class="text-center py-5 mt-5">
          <i class="bi bi-geo-alt display-1 text-muted mb-3 opacity-50"></i>
          <h4 class="text-muted">Seleccione un traslado</h4>
          <p class="text-muted">Haga clic en el botón "Ver Seguimiento" en la tabla de traslados activos.</p>
        </div>

        <!-- Contenedor dinámico (oculto inicialmente) -->
        <div id="seg-content-container" class="row g-4 d-none">
          <div class="col-12 col-xl-8">
            <div class="section-card">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 seguimiento-title" id="seg-titulo">AMB-###</h5>
                <span id="seg-badge" class="badge-estado b-transito">Estado</span>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-6">
                  <div class="info-label">Paciente / Carga</div>
                  <div class="info-value-bold" id="seg-paciente">-</div>
                </div>
                <div class="col-6">
                  <div class="info-label">Vehículo</div>
                  <div class="info-value-bold" id="seg-vehiculo">-</div>
                </div>
                <div class="col-6">
                  <div class="info-label">Conductor</div>
                  <div class="info-value" id="seg-conductor">-</div>
                </div>
                <div class="col-6">
                  <div class="info-label">Enfermero Responsable</div>
                  <div class="info-value" id="seg-enfermero">-</div>
                </div>
              </div>

              <div class="d-flex justify-content-between mb-1 ruta-label">
                <span id="seg-origen">Origen</span>
                <strong id="seg-destino">Destino</strong>
              </div>
              <div class="progress mb-1 progress-md">
                <div id="seg-progress-bar" class="progress-bar progress-bar-primary" style="width: 0%"></div>
              </div>
            </div>
          </div>

          <!-- Línea de Tiempo Dinámica -->
          <div class="col-12 col-xl-4">
            <div class="section-card">
              <h5>Línea de Tiempo</h5>
              <div id="seg-timeline-container">
              </div>
            </div>
          </div>
        </div>
    </section>

    <!-- RECURSOS -->
    <section class="view" id="view-recursos">
      <div class="page-title">Gestión de Recursos</div>

      <!-- Stat cards (Dinámicas) -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
          <div class="stat-card">
            <div>
              <div class="stat-value"><?= $vehiculosDispCount ?>/<?= $vehiculosTotales ?></div>
              <div class="stat-label">Vehículos Totales</div>
              <div class="stat-sub">Disponibles</div>
            </div>
            <div class="stat-icon-circle"><i class="bi bi-truck-front-fill"></i></div>
          </div>
        </div>
        <div class="col-12 col-sm-4">
          <div class="stat-card">
            <div>
              <div class="stat-value"><?= $conductoresDispCount ?>/<?= $conductoresTotales ?></div>
              <div class="stat-label">Conductores</div>
              <div class="stat-sub">Disponibles</div>
            </div>
            <div class="stat-icon-circle"><i class="bi bi-person-fill"></i></div>
          </div>
        </div>
        <div class="col-12 col-sm-4">
          <div class="stat-card">
            <div>
              <div class="stat-value"><?= $enfermerosDispCount ?>/<?= $enfermerosTotales ?></div>
              <div class="stat-label">Enfermeros</div>
              <div class="stat-sub">Disponibles</div>
            </div>
            <div class="stat-icon-circle"><i class="bi bi-plus-circle-fill"></i></div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="section-card p-0 overflow-hidden">
        <div class="px-3 pt-3">
          <div class="tab-pill-bar">
            <div class="tab-pill active" data-tab="tab-vehiculos">VEHÍCULOS</div>
            <div class="tab-pill" data-tab="tab-conductores">CONDUCTORES</div>
            <div class="tab-pill" data-tab="tab-enfermeros">ENFERMEROS</div>
          </div>
        </div>

        <!-- Vehículos tab -->
        <div id="tab-vehiculos" class="tab-content-panel active">
          <div class="table-responsive">
            <table class="table table-custom mb-0">
              <thead>
                <tr>
                  <th>ID Vehículo</th>
                  <th>Tipo</th>
                  <th>Patente</th>
                  <th>Estado</th>
                  <th>Conductor Asignado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($todosVehiculos as $v): ?>
                  <?php
                  $estado = $v['estado_disponibilidad'];
                  $badgeClass = $estado === 'DISPONIBLE' ? 'b-disponible' : ($estado === 'EN_USO' ? 'b-servicio' : 'b-mant');
                  $estadoTxt = $estado === 'DISPONIBLE' ? '<i class="bi bi-check-circle-fill"></i> Disponible' : str_replace('_', ' ', $estado);
                  $tipoClass = stripos($v['tipo'], 'Ambulancia') !== false ? 'bt-ambulancia' : 'bt-auto';
                  ?>
                  <tr>
                    <td>VEH-<?= $v['id_vehiculo'] ?></td>
                    <td><span class="badge-tipo <?= $tipoClass ?>"><?= htmlspecialchars($v['tipo']) ?></span></td>
                    <td><?= htmlspecialchars($v['matricula']) ?></td>
                    <td><span class="badge-estado <?= $badgeClass ?>"><?= ucfirst(strtolower($estadoTxt)) ?></span></td>
                    <td><?= $v['conductor_asignado'] ? htmlspecialchars($v['conductor_asignado']) : '–' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Conductores tab -->
        <div id="tab-conductores" class="tab-content-panel">
          <div class="table-responsive">
            <table class="table table-custom mb-0">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>CI</th>
                  <th>Estado</th>
                  <th>Vehículo Asignado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($todosConductores as $c): ?>
                  <?php
                  $enUso = !empty($c['vehiculo_asignado']);
                  $badgeClass = $enUso ? 'b-servicio' : ($c['activo'] ? 'b-disponible' : 'b-mant');
                  $estadoTxt = $enUso ? 'En servicio' : ($c['activo'] ? '<i class="bi bi-check-circle-fill"></i> Disponible' : 'Inactivo');
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></td>
                    <td><?= htmlspecialchars($c['ci']) ?></td>
                    <td><span class="badge-estado <?= $badgeClass ?>"><?= $estadoTxt ?></span></td>
                    <td><?= $enUso ? htmlspecialchars($c['vehiculo_asignado']) : '–' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Enfermeros tab -->
        <div id="tab-enfermeros" class="tab-content-panel">
          <div class="table-responsive">
            <table class="table table-custom mb-0">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>CI</th>
                  <th>Estado</th>
                  <th>Traslado Asignado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($todosEnfermeros as $e): ?>
                  <?php
                  $enUso = !empty($e['traslado_asignado']);
                  $badgeClass = $enUso ? 'b-servicio' : ($e['activo'] ? 'b-disponible' : 'b-mant');
                  $estadoTxt = $enUso ? 'En servicio' : ($e['activo'] ? '<i class="bi bi-check-circle-fill"></i> Disponible' : 'Inactivo');
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($e['nombre'] . ' ' . $e['apellido']) ?></td>
                    <td><?= htmlspecialchars($e['ci']) ?></td>
                    <td><span class="badge-estado <?= $badgeClass ?>"><?= $estadoTxt ?></span></td>
                    <td><?= $enUso ? 'ID Traslado: ' . htmlspecialchars($e['traslado_asignado']) : '–' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- CONFIGURACIÓN -->
    <section class="view" id="view-configuracion">
      <div class="page-title">Configuración</div>
      <div class="row g-4">
        <div class="col-lg-6">
          <div class="section-card">
            <h5><i class="bi bi-palette"></i> Apariencia</h5>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input check-cursor-pointer" type="checkbox" id="cfg-darkmode" />
              <label class="form-check-label fw-bold form-check-label-title" for="cfg-darkmode">Modo Oscuro (Estilo EVE)</label>
              <p class="text-muted mt-1 form-check-description">Cambia la interfaz a la paleta de colores oscuros con acentos neón ideal para guardias nocturnas.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- MODAL CONFIRMAR RECHAZO -->
    <div class="modal fade" id="modalConfirmarRechazo" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmar Rechazo</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            ¿Está seguro que desea rechazar la solicitud <strong id="rechazo-id-text"></strong>? Esta acción cancelará el viaje y no se puede deshacer.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" id="btnConfirmarRechazo">Sí, rechazar solicitud</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL MODIFICAR TRASLADO -->
    <div class="modal fade" id="modalModificarTraslado" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Modificar Traslado AMB-<span id="mod_title_id"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formModificarTraslado">
            <div class="modal-body">
              <input type="hidden" name="id_traslado" id="mod_id_traslado" />
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label fw-bold">Vehículo <span class="text-danger">*</span></label>
                  <select class="form-select" name="vehiculo" id="mod_vehiculo" required>
                    <?php foreach ($todosVehiculos as $v): ?>
                      <option value="<?= $v['id_vehiculo'] ?>"><?= htmlspecialchars($v['tipo'] . ' - ' . $v['matricula']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Conductor <span class="text-danger">*</span></label>
                  <select class="form-select" name="conductor" id="mod_conductor" required>
                    <?php foreach ($todosConductores as $c): ?>
                      <option value="<?= $c['id_personal'] ?>"><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Enfermero</label>
                  <select class="form-select" name="enfermero" id="mod_enfermero">
                    <option value="">Ninguno / No requiere</option>
                    <?php foreach ($todosEnfermeros as $e): ?>
                      <option value="<?= $e['id_personal'] ?>"><?= htmlspecialchars($e['nombre'] . ' ' . $e['apellido']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary" id="btnGuardarModificacion"><i class="bi bi-floppy"></i> Guardar Cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL CONFIRMAR CANCELACIÓN DE TRASLADO -->
    <div class="modal fade" id="modalConfirmarCancelacion" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="bi bi-x-octagon-fill me-2"></i> Confirmar Cancelación</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            ¿Está seguro que desea cancelar el traslado <strong>AMB-<span id="cancel-id-text"></span></strong>?<br><br>
            <small class="text-muted">El viaje pasará a estado "Cancelado" y los recursos asignados quedarán liberados.</small>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-danger" id="btnConfirmarCancelacion">Sí, cancelar traslado</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL ACTUALIZAR ESTADO Y OBSERVACIONES -->
    <div class="modal fade" id="modalActualizarEstado" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold text-success"><i class="bi bi-clock-history me-2"></i>Actualizar Estado AMB-<span id="lbl_estado_id"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <form id="formActualizarEstado">
            <div class="modal-body">
              <input type="hidden" name="id_traslado" id="estado_id_traslado" />
              <div class="mb-3">
                <label class="form-label fw-bold">Nuevo Estado <span class="text-danger">*</span></label>
                <select class="form-select border-success" name="id_estado" required>
                  <option value="" selected disabled>Seleccione fase del viaje...</option>
                  <option value="2">En Camino / En Tránsito</option>
                  <option value="3">Completado / Llegada a destino</option>
                </select>
              </div>
              <div class="mb-0">
                <label class="form-label fw-bold">Observaciones <span class="text-danger">*</span></label>
                <textarea class="form-control" name="observaciones" rows="3" placeholder="Ej: Chofer retiró la nevera térmica con muestras..." required></textarea>
                <small class="text-muted">Este texto aparecerá debajo del estado en la Línea de Tiempo.</small>
              </div>
            </div>
            <div class="modal-footer border-top-0">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-success" id="btnGuardarEstado"><i class="bi bi-send-check"></i> Registrar Avance</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript propio -->
    <script src="../vista/assets/js/panel-de-gestion.js?v=2"></script>
</body>

</html>