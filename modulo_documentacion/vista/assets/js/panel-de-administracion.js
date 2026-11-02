// ── Init Temprano del Tema Unificado ──────────────────────
(function initTheme() {
  if (localStorage.getItem("theme") === "dark") {
    document.documentElement.setAttribute("data-theme", "dark");
  }
})();

// ── Datos fijos de demostración (sin backend) ──────────────────────
let hcCounter = 4029;

let docs = [];

async function cargarDocumentosServidor() {
  try {
    // Hacemos la petición al controlador
    const response = await fetch(
      "../controlador/dashboard_controlador.php?accion=listar_documentos",
    );
    const result = await response.json();

    if (result.status === "success") {
      // 3. Mapeamos usando las columnas reales de la base de datos (JOIN con CATEGORIA)
      docs = result.data.map((dbDoc) => ({
        id: dbDoc.id_documento,
        titulo: dbDoc.titulo,
        tipo: dbDoc.categoria || "Sin categoría",
        idCategoria: dbDoc.id_categoria,
        fecha: dbDoc.fecha_carga,
        archivoUrl: dbDoc.archivo_url,
        tituloVisor: dbDoc.titulo ? dbDoc.titulo.toUpperCase() : "DOCUMENTO",
        resumenHeading: "Documento",
        resumenText: dbDoc.titulo,
        audit: [],
      }));

      // 4. Actualizamos las tablas de la interfaz
      renderDocumentosTable();
      renderDashboard();
    } else {
      console.error("Error al cargar documentos:", result.mensaje);
    }
  } catch (error) {
    console.error("Error de conexión:", error);
  }
}

let currentDocId = null;

function logout() {
  location.href = "../../servicios_comunes/vista_general/index.php";
}

// ── Sidebar (vistas internas del panel) ─────────────────────────────
function setAdminView(id) {
  document
    .querySelectorAll("#main-content .view")
    .forEach((v) => v.classList.remove("active"));
  const target = document.getElementById(id);
  if (target) target.classList.add("active");

  const titles = {
    "view-dashboard": "Panel Principal",
    "view-documentos": "Documentos Clínicos",
    "view-nuevo-doc": "Nuevo Documento",
    "view-documento-detalle": "Visualizador de Documento",
    "view-editar-doc": "Editar Documento",
    "view-generar-qr": "Generador de Accesos QR",
    "view-configuracion": "Configuración",
  };
  const titleEl = document.getElementById("topbar-title");
  if (titleEl) titleEl.textContent = titles[id] || "";

  const navMap = {
    "view-dashboard": "nav-inicio",
    "view-documentos": "nav-documentos",
    "view-generar-qr": "nav-generar-qr",
    "view-configuracion": "nav-configuracion",
  };
  document
    .querySelectorAll(".nav-link-custom")
    .forEach((n) => n.classList.remove("active"));

  if (navMap[id]) {
    const navEl = document.getElementById(navMap[id]);
    if (navEl) navEl.classList.add("active");
  }

  if (id === "view-documentos") renderDocumentosTable();
  if (id === "view-dashboard") renderDashboard();
  if (id === "view-generar-qr") initQr();

  if (window.innerWidth <= 768) closeMobileSidebar();
}

function toggleSidebar() {
  const sb = document.getElementById("sidebar");
  const mc = document.getElementById("main-content");
  const overlay = document.getElementById("sidebar-overlay");

  if (window.innerWidth <= 768) {
    const isOpen = sb.classList.contains("mobile-open");
    if (isOpen) {
      closeMobileSidebar();
    } else {
      sb.classList.add("mobile-open");
      overlay.classList.add("visible");
    }
  } else {
    sb.classList.toggle("collapsed");
    mc.classList.toggle("collapsed");
  }
}

function closeMobileSidebar() {
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebar-overlay");
  if (sidebar) sidebar.classList.remove("mobile-open");
  if (overlay) overlay.classList.remove("visible");
}

// ── Dashboard ────────────────────────────────────────────────────────
function renderDashboard() {
  document.getElementById("stat-total").textContent = docs.length;

  const tbody = document.getElementById("dash-doc-table");
  tbody.innerHTML = docs
    .slice(0, 4)
    .map(
      (d) => `
    <tr class="clickable-doc-row" data-id="${d.id}">
      <td><strong>#${d.id}</strong></td>
      <td>
        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">
          ${d.titulo}
        </div>
      </td>
    </tr>`,
    )
    .join("");

  const hoy = new Date().toLocaleDateString("es-UY", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
  document.getElementById("dash-date").textContent =
    hoy.charAt(0).toUpperCase() + hoy.slice(1);
}

// ── Documentos Clínicos ─────────────────────────────────────────────
function renderDocumentosTable() {
  const tbody = document.getElementById("admin-doc-list-table");
  if (!tbody) return;

  tbody.innerHTML = docs
    .map(
      (d) => `
    <tr>
      <td><strong>#${d.id}</strong></td>
      <td>
        <div style="font-weight: 500;">${d.titulo}</div>
        <div style="font-size: 0.85em; opacity: 0.7;">${d.tipo}</div>
      </td>
      <td class="table-cell-date">${d.fecha}</td>
      <td class="text-right">
        <div class="dropdown">
          <button class="btn-hc btn-hc-ghost btn-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 4px 10px; font-size: 1.1rem;">
            <i class="bi bi-three-dots-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.85rem; border: 1px solid var(--hc-gray-200); border-radius: 8px;">
            <li><a class="dropdown-item btn-view-doc" href="#" data-id="${d.id}"><i class="bi bi-eye text-primary me-2"></i> Visualizar</a></li>
            <li><a class="dropdown-item btn-generate-qr" href="#" data-id="${d.id}"><i class="bi bi-qr-code text-success me-2"></i> Generar QR</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item btn-delete-doc text-danger" href="#" data-id="${d.id}"><i class="bi bi-trash me-2"></i> Eliminar</a></li>
          </ul>
        </div>
      </td>
    </tr>`,
    )
    .join("");

  document.getElementById("doc-count-label").textContent =
    `Mostrando 1-${docs.length} de ${docs.length} documentos`;
}

// ── Visualizador de Documento ────────────────────────────────────────
async function openDocumento(id) {
  currentDocId = id;
  const d = docs.find((x) => x.id == id);
  if (!d) return;

  document.getElementById("dd-title").textContent = `${d.tipo} (#${d.id})`;

  // 1. Inyectar Información lateral (Se carga instantáneamente)
  let infoHtml = `
    <div style="margin-bottom: 1rem;">
      <span style="font-size: 0.85em; color: var(--hc-gray-500); text-transform: uppercase; font-weight: 600;">Título</span>
      <p style="margin-top: 4px; font-weight: 500;">${d.titulo}</p>
    </div>
    <div style="margin-bottom: 1rem;">
      <span style="font-size: 0.85em; color: var(--hc-gray-500); text-transform: uppercase; font-weight: 600;">Categoría</span>
      <p style="margin-top: 4px;">${d.tipo}</p>
    </div>
    <div style="margin-bottom: 1rem;">
      <span style="font-size: 0.85em; color: var(--hc-gray-500); text-transform: uppercase; font-weight: 600;">Fecha de Carga</span>
      <p style="margin-top: 4px;">${d.fecha}</p>
    </div>
  `;
  document.getElementById("dd-info").innerHTML = infoHtml;

  // 2. Mostrar estado de carga temporal en el visor
  document.getElementById("dd-content").innerHTML = `
    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--hc-gray-400);">
      <i class="bi bi-hourglass-split" style="margin-right: 8px;"></i> Comprobando archivo...
    </div>
  `;

  // Cambiar la vista inmediatamente
  setAdminView("view-documento-detalle");

  // Referencia al botón de descarga
  const btnDownload = document.getElementById("btnDownloadDoc");

  // 3. Comprobar si el archivo existe en el servidor
  try {
    // Hace una petición "HEAD" (solo pide los encabezados, no descarga el archivo completo)
    const response = await fetch(d.archivoUrl, { method: "HEAD" });

    if (response.ok) {
      // El archivo EXISTE -> Cargar el iframe
      let pdfHtml = `
        <iframe src="${d.archivoUrl}" width="100%" height="100%" style="border: none; display: block; border-radius: 12px;">
          Su navegador no soporta la visualización de PDFs.
        </iframe>
      `;
      document.getElementById("dd-content").innerHTML = pdfHtml;

      // Habilitar botón de descarga
      if (btnDownload) {
        btnDownload.href = d.archivoUrl;
        btnDownload.style.pointerEvents = "auto";
        btnDownload.style.opacity = "1";
      }
    } else {
      // El archivo NO EXISTE (Error 404, etc) -> Mostrar interfaz limpia
      mostrarErrorArchivo();
    }
  } catch (error) {
    // Falla de red -> Mostrar interfaz limpia
    mostrarErrorArchivo();
  }

  // Función interna para dibujar el error visual
  function mostrarErrorArchivo() {
    document.getElementById("dd-content").innerHTML = `
      <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--hc-gray-500); text-align: center; padding: 2rem;">
        <i class="bi bi-file-earmark-x" style="font-size: 3.5rem; margin-bottom: 1rem; color: var(--hc-gray-400);"></i>
        <h4 style="margin: 0 0 8px; color: var(--hc-gray-700); font-weight: 600; font-size: 1.1rem;">Archivo no encontrado</h4>
        <p style="font-size: 0.85rem; max-width: 320px; margin: 0; line-height: 1.5;">El documento físico no está disponible en el servidor. Puede que no se haya subido correctamente o haya sido eliminado.</p>
      </div>
    `;

    // Deshabilitar botón de descarga para evitar errores
    if (btnDownload) {
      btnDownload.removeAttribute("href");
      btnDownload.style.pointerEvents = "none";
      btnDownload.style.opacity = "0.5";
    }
  }
}

// ── Nuevo Documento ──────────────────────────────────────────────────
async function saveDoc() {
  const titulo = document.getElementById("nd-titulo").value.trim();
  const idCategoria = document.getElementById("nd-categoria").value;
  const fileInput = document.getElementById("hiddenFileInput");

  if (!titulo || !idCategoria) {
    alert("Por favor, ingrese el título y seleccione una categoría.");
    return;
  }

  if (fileInput.files.length === 0) {
    alert("Debe adjuntar un archivo PDF.");
    return;
  }

  // Empaquetar datos y archivo para envío
  const formData = new FormData();
  formData.append("accion", "alta_documento");
  formData.append("titulo", titulo);
  formData.append("id_categoria", idCategoria);
  formData.append("archivo", fileInput.files[0]);

  try {
    const response = await fetch("../controlador/dashboard_controlador.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.status === "success") {
      showToast("Documento subido y guardado correctamente", "bi-check-circle");

      // Limpiar formulario
      document.getElementById("nd-titulo").value = "";
      document.getElementById("nd-categoria").value = "";
      fileInput.value = "";

      document.getElementById("uploadZoneDefault").style.display = "block";
      document.getElementById("uploadZoneSelected").style.display = "none";

      // Recargar lista y volver
      cargarDocumentosServidor();
      setAdminView("view-documentos");
    } else {
      alert("Error: " + data.mensaje);
    }
  } catch (error) {
    console.error("Error en la petición:", error);
    alert("Ocurrió un error de conexión.");
  }
}
// ── Generador de Accesos QR ──────────────────────────────────────────
function initQr() {
  const select = document.getElementById("qr-documento");
  select.innerHTML =
    `<option value="">Seleccione un documento...</option>` +
    docs
      .map((d) => `<option value="${d.id}">${d.tipo} (#${d.id})</option>`)
      .join("");

  document.getElementById("qr-result").style.display = "none";
  document.getElementById("qr-empty").style.display = "block";
}

function generarQr() {
  const docId = document.getElementById("qr-documento").value;
  if (!docId) {
    alert("Seleccione un documento para generar el código QR.");
    return;
  }

  const doc = docs.find((d) => d.id == docId);

  document.getElementById("qr-empty").style.display = "none";
  document.getElementById("qr-result").style.display = "block";
  document.getElementById("qr-result-doc-title").textContent =
    `${doc.tipo} (#${doc.id})`;
  document.getElementById("qr-result-desc").textContent =
    `Acceso QR exclusivo para el documento seleccionado`;

  showToast("Código QR generado correctamente", "bi-qr-code");
}

// ── Toast ────────────────────────────────────────────────────────────
let toastTimer = null;
function showToast(msg, icon) {
  const el = document.getElementById("toast");
  el.innerHTML = `<i class="bi ${icon || "bi-info-circle"}"></i> ${msg}`;
  el.classList.add("show");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove("show"), 2600);
}

// ── Bind Events & Init ────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
  /* Events: Global App */
  const sidebarOverlay = document.getElementById("sidebar-overlay");
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", closeMobileSidebar);
  }

  const btnLogin = document.getElementById("btnLogin");
  if (btnLogin) btnLogin.addEventListener("click", doLogin);

  const btnBackPortal = document.getElementById("btnBackPortal");
  if (btnBackPortal) {
    btnBackPortal.addEventListener("click", () => {
      location.href = "../../servicios_comunes/vista_general/index.php";
    });
  }

  const btnLogoutEl = document.getElementById("btnLogout");
  if (btnLogoutEl) btnLogoutEl.addEventListener("click", logout);

  /* Events: Sidebar & Navigation Toggle */
  document.querySelectorAll(".btn-toggle-sidebar").forEach((btn) => {
    btn.addEventListener("click", toggleSidebar);
  });

  document.querySelectorAll(".btn-nav-view").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      setAdminView(this.getAttribute("data-view"));
    });
  });

  /* Events: Config Theme Sincronizado */
  const darkModeToggle = document.getElementById("cfg-darkmode");
  if (darkModeToggle) {
    darkModeToggle.checked = localStorage.getItem("theme") === "dark";

    darkModeToggle.addEventListener("change", function () {
      if (this.checked) {
        document.documentElement.setAttribute("data-theme", "dark");
        localStorage.setItem("theme", "dark");
      } else {
        document.documentElement.removeAttribute("data-theme");
        localStorage.setItem("theme", "light");
      }
    });

    window.addEventListener("storage", (e) => {
      if (e.key === "theme") {
        const isDark = e.newValue === "dark";
        darkModeToggle.checked = isDark;

        if (isDark) {
          document.documentElement.setAttribute("data-theme", "dark");
        } else {
          document.documentElement.removeAttribute("data-theme");
        }
      }
    });
  }

  /* Events: Documents Interaction via Delegation */
  let docToDelete = null;

  document.addEventListener("click", (e) => {
    // Clic en la fila del dashboard (minitabla)
    const docRow = e.target.closest(".clickable-doc-row");
    if (docRow) {
      openDocumento(docRow.dataset.id);
    }

    // 1. Acción: Visualizar
    const viewBtn = e.target.closest(".btn-view-doc");
    if (viewBtn) {
      e.preventDefault();
      openDocumento(viewBtn.dataset.id);
    }

    // 2. Acción: Generar QR (Preparamos el terreno)
    const qrBtn = e.target.closest(".btn-generate-qr");
    if (qrBtn) {
      e.preventDefault();
      // Por ahora solo mostramos un toast, lo implementaremos en el futuro
      showToast("Generar QR estará disponible pronto", "bi-tools");
    }

    // 3. Acción: Eliminar (Abre el Modal)
    const deleteBtn = e.target.closest(".btn-delete-doc");
    if (deleteBtn) {
      e.preventDefault();
      docToDelete = deleteBtn.dataset.id;
      const d = docs.find((x) => x.id == docToDelete);
      // Inyectar el nombre del documento en el texto del modal
      document.getElementById("deleteDocName").textContent = d
        ? d.titulo
        : "este documento";
      document.getElementById("modalEliminarOverlay").classList.add("open");
    }
  });

  /* ── Eventos del Modal de Eliminar ── */
  const btnCancelDelete = document.getElementById("btnCancelDelete");
  if (btnCancelDelete) {
    btnCancelDelete.addEventListener("click", () => {
      document.getElementById("modalEliminarOverlay").classList.remove("open");
      docToDelete = null;
    });
  }

  const btnConfirmDelete = document.getElementById("btnConfirmDelete");
  if (btnConfirmDelete) {
    btnConfirmDelete.addEventListener("click", async () => {
      if (!docToDelete) return;

      const formData = new FormData();
      formData.append("accion", "eliminar_documento");
      formData.append("id_documento", docToDelete);

      try {
        const response = await fetch(
          "../controlador/dashboard_controlador.php",
          {
            method: "POST",
            body: formData,
          },
        );
        const data = await response.json();

        if (data.status === "success") {
          showToast("Documento eliminado con éxito", "bi-trash");
          document
            .getElementById("modalEliminarOverlay")
            .classList.remove("open");
          docToDelete = null;
          cargarDocumentosServidor(); // Recargar tabla automáticamente
        } else {
          alert("Error: " + data.mensaje);
        }
      } catch (error) {
        alert("Error de conexión al intentar eliminar.");
      }
    });
  }

  /* Events: Upload & Save Docs */
  const btnSaveDocEl = document.getElementById("btnSaveDoc");
  if (btnSaveDocEl) btnSaveDocEl.addEventListener("click", saveDoc);

  const uploadZone = document.getElementById("uploadZoneArea");
  const fileInput = document.getElementById("hiddenFileInput");
  const zoneDefault = document.getElementById("uploadZoneDefault");
  const zoneSelected = document.getElementById("uploadZoneSelected");
  const fileNameDisplay = document.getElementById("selectedFileName");

  if (uploadZone) {
    uploadZone.addEventListener("click", function () {
      if (fileInput) fileInput.click();
    });
  }

  if (fileInput) {
    fileInput.addEventListener("change", function () {
      if (this.files && this.files.length > 0) {
        // Mostrar estado de archivo seleccionado
        fileNameDisplay.textContent = this.files[0].name;
        zoneDefault.style.display = "none";
        zoneSelected.style.display = "block";
      } else {
        // Volver al estado por defecto si se cancela
        zoneDefault.style.display = "block";
        zoneSelected.style.display = "none";
      }
    });
  }

  /* Events: QR Generator */
  const btnGenQr = document.getElementById("btnGenerarQr");
  if (btnGenQr) {
    btnGenQr.addEventListener("click", generarQr);
  }

  /* Initialize starting views */
  cargarDocumentosServidor();

  /* ── EVENTOS: Editar Documento ────────────────────────────────────────── */
  const btnEditDoc = document.getElementById("btnEditDoc");
  if (btnEditDoc) {
    btnEditDoc.addEventListener("click", () => {
      const d = docs.find((x) => x.id == currentDocId);
      if (!d) return;

      // Cargar datos en el formulario
      document.getElementById("ed-id").value = d.id;
      document.getElementById("ed-titulo").value = d.titulo;

      // Seleccionar la categoría correcta
      const selectCat = document.getElementById("ed-categoria");
      for (let i = 0; i < selectCat.options.length; i++) {
        if (
          selectCat.options[i].value == d.idCategoria ||
          selectCat.options[i].text == d.tipo
        ) {
          selectCat.selectedIndex = i;
          break;
        }
      }

      // Reiniciar zona de archivo
      document.getElementById("hiddenFileInputEd").value = "";
      document.getElementById("uploadZoneDefaultEd").style.display = "block";
      document.getElementById("uploadZoneSelectedEd").style.display = "none";

      setAdminView("view-editar-doc");
    });
  }

  // Interfaz de subida para Edición
  const uploadZoneEd = document.getElementById("uploadZoneAreaEd");
  const fileInputEd = document.getElementById("hiddenFileInputEd");
  if (uploadZoneEd && fileInputEd) {
    uploadZoneEd.addEventListener("click", () => fileInputEd.click());
    fileInputEd.addEventListener("change", function () {
      if (this.files && this.files.length > 0) {
        document.getElementById("selectedFileNameEd").textContent =
          this.files[0].name;
        document.getElementById("uploadZoneDefaultEd").style.display = "none";
        document.getElementById("uploadZoneSelectedEd").style.display = "block";
      } else {
        document.getElementById("uploadZoneDefaultEd").style.display = "block";
        document.getElementById("uploadZoneSelectedEd").style.display = "none";
      }
    });
  }

  // Enviar Petición de Edición al Controlador
  const btnUpdateDoc = document.getElementById("btnUpdateDoc");
  if (btnUpdateDoc) {
    btnUpdateDoc.addEventListener("click", async () => {
      const id = document.getElementById("ed-id").value;
      const titulo = document.getElementById("ed-titulo").value.trim();
      const idCat = document.getElementById("ed-categoria").value;

      if (!titulo || !idCat) return alert("Ingrese un título y categoría.");

      const formData = new FormData();
      formData.append("accion", "editar_documento");
      formData.append("id_documento", id);
      formData.append("titulo", titulo);
      formData.append("id_categoria", idCat);

      // Si subió un archivo nuevo, lo enviamos; si no, PHP dejará el actual
      if (fileInputEd.files.length > 0)
        formData.append("archivo", fileInputEd.files[0]);

      try {
        const response = await fetch(
          "../controlador/dashboard_controlador.php",
          { method: "POST", body: formData },
        );
        const data = await response.json();

        if (data.status === "success") {
          showToast("Documento modificado con éxito", "bi-check-circle");
          await cargarDocumentosServidor(); // Recargamos para traer los datos nuevos
          openDocumento(id); // Volvemos al visor
        } else {
          alert("Error: " + data.mensaje);
        }
      } catch (err) {
        alert("Ocurrió un error de conexión al editar.");
      }
    });
  }
});
