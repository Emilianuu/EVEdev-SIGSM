// ── Init Temprano del Tema Unificado ──────────────────────
(function initTheme() {
  if (localStorage.getItem("theme") === "dark") {
    document.documentElement.setAttribute("data-theme", "dark");
  }
})();

// ── Datos fijos de demostración (sin backend) ──────────────────────
let hcCounter = 4029;
let qrCount = 0;

let docs = [];

async function cargarDocumentosServidor() {
  try {
    const response = await fetch(
      "../controlador/dashboard_controlador.php?accion=listar_documentos",
    );
    const result = await response.json();

    if (result.status === "success") {
      docs = result.data.map((dbDoc) => ({
        id: dbDoc.id_documento,
        titulo: dbDoc.titulo,
        tipo: dbDoc.categoria || LANG.doc_tipo_sin_categoria || "Sin categoría",
        idCategoria: dbDoc.id_categoria,
        fecha: dbDoc.fecha_carga,
        archivoUrl: dbDoc.archivo_url,
        tituloVisor: dbDoc.titulo
          ? dbDoc.titulo.toUpperCase()
          : LANG.doc_visor_default || "DOCUMENTO",
        resumenHeading: LANG.doc_resumen_heading || "Documento",
        resumenText: dbDoc.titulo,
        audit: [],
      }));

      renderDocumentosTable();
      renderDashboard();
    } else {
      console.error(
        LANG.doc_error_cargar || "Error al cargar documentos:",
        result.mensaje,
      );
    }
  } catch (error) {
    console.error(LANG.doc_error_conexion || "Error de conexión:", error);
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
    "view-dashboard": LANG.doc_menu_panel || "Panel Principal",
    "view-documentos": LANG.doc_menu_documentos || "Documentos Clínicos",
    "view-nuevo-doc": LANG.doc_titulo_nuevo || "Nuevo Documento",
    "view-documento-detalle":
      LANG.doc_visor_detalle || "Visualizador de Documento",
    "view-editar-doc": LANG.doc_titulo_editar || "Editar Documento",
    "view-generar-qr": LANG.qr_titulo || "Generador de Accesos QR",
    "view-configuracion": LANG.config_titulo || "Configuración",
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
  document.getElementById("stat-qr").textContent = qrCount;

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

  const hoy = new Date().toLocaleDateString(LANG.lang_code || "es-UY", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
  document.getElementById("dash-date").textContent =
    hoy.charAt(0).toUpperCase() + hoy.slice(1);
}

// ── Documentos Clínicos ─────────────────────────────────────────────
function renderDocumentosTable(data = docs) {
  const tbody = document.getElementById("admin-doc-list-table");
  if (!tbody) return;

  tbody.innerHTML = data
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
            <li><a class="dropdown-item btn-view-doc" href="#" data-id="${d.id}"><i class="bi bi-eye text-primary me-2"></i> ${LANG.doc_btn_visualizar || "Visualizar"}</a></li>
            <li><a class="dropdown-item btn-generate-qr" href="#" data-id="${d.id}"><i class="bi bi-qr-code text-success me-2"></i> ${LANG.qr_btn_generar || "Generar QR"}</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item btn-delete-doc text-danger" href="#" data-id="${d.id}"><i class="bi bi-trash me-2"></i> ${LANG.modal_btn_eliminar || "Eliminar"}</a></li>
          </ul>
        </div>
      </td>
    </tr>`,
    )
    .join("");

  document.getElementById("doc-count-label").textContent = (
    LANG.doc_contador || "Mostrando 1-{total} de {total} documentos"
  ).replace(/\{total\}/g, docs.length);

  poblarSelectQR(data);
}

// ── Visualizador de Documento ────────────────────────────────────────
async function openDocumento(id) {
  currentDocId = id;
  const d = docs.find((x) => x.id == id);
  if (!d) return;

  document.getElementById("dd-title").textContent = `${d.tipo} (#${d.id})`;

  const origen = window.location.origin;
  const rutaProyecto = window.location.pathname.split(
    "/modulo_documentacion/",
  )[0];
  const linkPublico = `${origen}${rutaProyecto}/modulo_documentacion/vista/visor_publico.php?id=${d.id}`;
  const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=${encodeURIComponent(linkPublico)}`;

  let infoHtml = `
    <div style="margin-bottom: 1rem;">
      <span style="font-size: 0.85em; color: var(--hc-gray-500); text-transform: uppercase; font-weight: 600;">${LANG.doc_label_titulo || "Título"}</span>
      <p style="margin-top: 4px; font-weight: 500;">${d.titulo}</p>
    </div>
    <div style="margin-bottom: 1rem;">
      <span style="font-size: 0.85em; color: var(--hc-gray-500); text-transform: uppercase; font-weight: 600;">${LANG.doc_label_categoria || "Categoría"}</span>
      <p style="margin-top: 4px;">${d.tipo}</p>
    </div>
    <div style="margin-bottom: 1rem;">
      <span style="font-size: 0.85em; color: var(--hc-gray-500); text-transform: uppercase; font-weight: 600;">${LANG.doc_th_fecha_carga || "Fecha de Carga"}</span>
      <p style="margin-top: 4px;">${d.fecha}</p>
    </div>
    <hr style="border: 0; border-top: 1px solid var(--hc-gray-200); margin: 1.5rem 0;">
    <div style="text-align: center;">
      <span style="display: block; font-size: 0.85em; color: var(--hc-gray-500); text-transform: uppercase; font-weight: 600; margin-bottom: 10px;">${LANG.qr_acceso_rapido || "Acceso Rápido QR"}</span>
      <img src="${qrApiUrl}" alt="QR" style="width: 140px; height: 140px; border-radius: 8px; border: 1px solid var(--hc-gray-200); padding: 8px; background: #fff;">
      <p style="font-size: 0.75rem; color: var(--hc-gray-400); margin-top: 8px; margin-bottom: 0;">${LANG.qr_escanee || "Escanee para acceso público"}</p>
    </div>
  `;
  document.getElementById("dd-info").innerHTML = infoHtml;

  document.getElementById("dd-content").innerHTML = `
    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--hc-gray-400);">
      <i class="bi bi-hourglass-split" style="margin-right: 8px;"></i> ${LANG.doc_comprobando || "Comprobando archivo..."}
    </div>
  `;

  setAdminView("view-documento-detalle");

  const btnDownload = document.getElementById("btnDownloadDoc");

  try {
    const response = await fetch(d.archivoUrl, { method: "HEAD" });

    if (response.ok) {
      let pdfHtml = `
        <iframe src="${d.archivoUrl}" width="100%" height="100%" style="border: none; display: block; border-radius: 12px;">
          ${LANG.doc_no_soporte_pdf || "Su navegador no soporta la visualización de PDFs."}
        </iframe>
      `;
      document.getElementById("dd-content").innerHTML = pdfHtml;

      if (btnDownload) {
        btnDownload.href = d.archivoUrl;
        btnDownload.style.pointerEvents = "auto";
        btnDownload.style.opacity = "1";
      }
    } else {
      mostrarErrorArchivo();
    }
  } catch (error) {
    mostrarErrorArchivo();
  }

  function mostrarErrorArchivo() {
    document.getElementById("dd-content").innerHTML = `
      <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--hc-gray-500); text-align: center; padding: 2rem;">
        <i class="bi bi-file-earmark-x" style="font-size: 3.5rem; margin-bottom: 1rem; color: var(--hc-gray-400);"></i>
        <h4 style="margin: 0 0 8px; color: var(--hc-gray-700); font-weight: 600; font-size: 1.1rem;">${LANG.doc_error_no_encontrado || "Archivo no encontrado"}</h4>
        <p style="font-size: 0.85rem; max-width: 320px; margin: 0; line-height: 1.5;">${LANG.doc_error_descripcion || "El documento físico no está disponible en el servidor. Puede que no se haya subido correctamente o haya sido eliminado."}</p>
      </div>
    `;

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
    alert(
      LANG.doc_alerta_campos ||
        "Por favor, ingrese el título y seleccione una categoría.",
    );
    return;
  }

  if (fileInput.files.length === 0) {
    alert(LANG.doc_alerta_archivo || "Debe adjuntar un archivo PDF.");
    return;
  }

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
      showToast(
        LANG.doc_toast_guardado || "Documento subido y guardado correctamente",
        "bi-check-circle",
      );

      document.getElementById("nd-titulo").value = "";
      document.getElementById("nd-categoria").value = "";
      fileInput.value = "";

      document.getElementById("uploadZoneDefault").style.display = "block";
      document.getElementById("uploadZoneSelected").style.display = "none";

      cargarDocumentosServidor();
      setAdminView("view-documentos");
    } else {
      alert((LANG.msg_error || "Error: ") + data.mensaje);
    }
  } catch (error) {
    alert(LANG.doc_error_conexion || "Ocurrió un error de conexión.");
  }
}
// ── Generador de Accesos QR ──────────────────────────────────────────
function initQr() {
  const select = document.getElementById("qr-documento");
  select.innerHTML =
    `<option value="">${LANG.qr_select_doc || "Seleccione un documento..."}</option>` +
    docs
      .map((d) => `<option value="${d.id}">${d.tipo} (#${d.id})</option>`)
      .join("");

  document.getElementById("qr-result").style.display = "none";
  document.getElementById("qr-empty").style.display = "block";
}

function generarQr() {
  const docId = document.getElementById("qr-documento").value;
  if (!docId) {
    showToast(
      LANG.qr_alerta_seleccione ||
        "Seleccione un documento para generar el código QR",
      "bi-exclamation-triangle",
    );
    return;
  }

  const doc = docs.find((d) => d.id == docId);

  document.getElementById("qr-empty").style.display = "none";
  document.getElementById("qr-result").style.display = "block";
  document.getElementById("qr-result-doc-title").textContent =
    `${doc.tipo} (#${doc.id})`;
  document.getElementById("qr-result-desc").textContent =
    LANG.qr_acceso_exclusivo ||
    `Acceso QR exclusivo para el documento seleccionado`;

  showToast(
    LANG.qr_toast_generado || "Código QR generado correctamente",
    "bi-qr-code",
  );

  const qrResult = document.getElementById("qr-result");
  qrResult.classList.remove("hidden-by-default");
  qrResult.style.display = "block";

  qrCount++;
  const statQr = document.getElementById("stat-qr");
  if (statQr) statQr.textContent = qrCount;
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

  document.querySelectorAll(".btn-toggle-sidebar").forEach((btn) => {
    btn.addEventListener("click", toggleSidebar);
  });

  document.querySelectorAll(".btn-nav-view").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      setAdminView(this.getAttribute("data-view"));
    });
  });

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

  let docToDelete = null;

  document.addEventListener("click", (e) => {
    const docRow = e.target.closest(".clickable-doc-row");
    if (docRow) {
      openDocumento(docRow.dataset.id);
    }

    const viewBtn = e.target.closest(".btn-view-doc");
    if (viewBtn) {
      e.preventDefault();
      openDocumento(viewBtn.dataset.id);
    }

    const qrBtn = e.target.closest(".btn-generate-qr");
    if (qrBtn) {
      e.preventDefault();
      setAdminView("view-generar-qr");
      const selectQR = document.getElementById("qr-documento");
      if (selectQR) {
        selectQR.value = qrBtn.dataset.id;
        document.getElementById("btnGenerarQr").click();
      }
    }

    const deleteBtn = e.target.closest(".btn-delete-doc");
    if (deleteBtn) {
      e.preventDefault();
      docToDelete = deleteBtn.dataset.id;
      const d = docs.find((x) => x.id == docToDelete);
      document.getElementById("deleteDocName").textContent = d
        ? d.titulo
        : LANG.modal_doc_default || "este documento";
      document.getElementById("modalEliminarOverlay").classList.add("open");
    }
  });

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
          showToast(
            LANG.modal_toast_eliminado || "Documento eliminado con éxito",
            "bi-trash",
          );
          document
            .getElementById("modalEliminarOverlay")
            .classList.remove("open");
          docToDelete = null;
          cargarDocumentosServidor();
        } else {
          alert((LANG.msg_error || "Error: ") + data.mensaje);
        }
      } catch (error) {
        alert(
          LANG.modal_error_conexion ||
            "Error de conexión al intentar eliminar.",
        );
      }
    });
  }

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
        fileNameDisplay.textContent = this.files[0].name;
        zoneDefault.style.display = "none";
        zoneSelected.style.display = "block";
      } else {
        zoneDefault.style.display = "block";
        zoneSelected.style.display = "none";
      }
    });
  }

  const btnGenQr = document.getElementById("btnGenerarQr");
  if (btnGenQr) {
    btnGenQr.addEventListener("click", generarQr);
  }

  cargarDocumentosServidor();

  const btnEditDoc = document.getElementById("btnEditDoc");
  if (btnEditDoc) {
    btnEditDoc.addEventListener("click", () => {
      const d = docs.find((x) => x.id == currentDocId);
      if (!d) return;

      document.getElementById("ed-id").value = d.id;
      document.getElementById("ed-titulo").value = d.titulo;

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

      document.getElementById("hiddenFileInputEd").value = "";
      document.getElementById("uploadZoneDefaultEd").style.display = "block";
      document.getElementById("uploadZoneSelectedEd").style.display = "none";

      setAdminView("view-editar-doc");
    });
  }

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

  const btnUpdateDoc = document.getElementById("btnUpdateDoc");
  if (btnUpdateDoc) {
    btnUpdateDoc.addEventListener("click", async () => {
      const id = document.getElementById("ed-id").value;
      const titulo = document.getElementById("ed-titulo").value.trim();
      const idCat = document.getElementById("ed-categoria").value;

      if (!titulo || !idCat)
        return alert(
          LANG.doc_alerta_campos || "Ingrese un título y categoría.",
        );

      const formData = new FormData();
      formData.append("accion", "editar_documento");
      formData.append("id_documento", id);
      formData.append("titulo", titulo);
      formData.append("id_categoria", idCat);

      if (fileInputEd.files.length > 0)
        formData.append("archivo", fileInputEd.files[0]);

      try {
        const response = await fetch(
          "../controlador/dashboard_controlador.php",
          { method: "POST", body: formData },
        );
        const data = await response.json();

        if (data.status === "success") {
          showToast(
            LANG.doc_toast_modificado || "Documento modificado con éxito",
            "bi-check-circle",
          );
          await cargarDocumentosServidor();
          openDocumento(id);
        } else {
          alert((LANG.msg_error || "Error: ") + data.mensaje);
        }
      } catch (err) {
        alert(
          LANG.doc_error_editar || "Ocurrió un error de conexión al editar.",
        );
      }
    });
  }

  const searchInput = document.getElementById("searchDoc");
  const filterSelect = document.getElementById("filterCat");
  const btnClear = document.getElementById("btnClearFilters");

  function aplicarFiltros() {
    if (!searchInput || !filterSelect) return;

    const textoBusqueda = searchInput.value.toLowerCase().trim();
    const categoriaFiltro = filterSelect.value;

    const filtrados = docs.filter((d) => {
      const coincideTexto =
        d.titulo.toLowerCase().includes(textoBusqueda) ||
        d.id.toString() === textoBusqueda;

      const coincideCat = categoriaFiltro === "" || d.tipo === categoriaFiltro;

      return coincideTexto && coincideCat;
    });

    renderDocumentosTable(filtrados);
  }

  if (searchInput) searchInput.addEventListener("input", aplicarFiltros);
  if (filterSelect) filterSelect.addEventListener("change", aplicarFiltros);

  if (btnClear) {
    btnClear.addEventListener("click", () => {
      searchInput.value = "";
      filterSelect.value = "";
      renderDocumentosTable(docs);
    });
  }

  window.poblarSelectQR = function (data) {
    const select = document.getElementById("qr-documento");
    if (!select) return;

    select.innerHTML = `<option value="">${LANG.qr_select_doc || "Seleccione un documento..."}</option>`;
    data.forEach((d) => {
      const opt = document.createElement("option");
      opt.value = d.id;
      opt.textContent = `[HC-${d.id}] ${d.titulo}`;
      select.appendChild(opt);
    });
  };

  if (btnGenerarQr) {
    btnGenerarQr.addEventListener("click", () => {
      const selectDoc = document.getElementById("qr-documento");
      const docId = selectDoc.value;
      const selectExp = document.getElementById("qr-expiracion");
      const expText = selectExp.options[selectExp.selectedIndex].text;

      if (!docId)
        return showToast(
          LANG.qr_alerta_seleccione || "Por favor, seleccione un documento",
          "bi-exclamation-triangle",
        );

      const d = docs.find((x) => x.id == docId);
      if (!d) return;

      const origen = window.location.origin;
      const rutaProyecto = window.location.pathname.split(
        "/modulo_documentacion/",
      )[0];
      const linkPublico = `${origen}${rutaProyecto}/modulo_documentacion/vista/visor_publico.php?id=${d.id}`;

      const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=10&data=${encodeURIComponent(linkPublico)}`;

      const qrContainer = document.querySelector(".qr-result-img-wrapper");
      qrContainer.innerHTML = `<img src="${qrApiUrl}" alt="QR" style="width: 100%; height: 100%; object-fit: contain; border-radius: 6px;" />`;

      document.getElementById("qr-result-doc-title").textContent = d.titulo;
      document.getElementById("qr-result-desc").textContent = expText;

      document.getElementById("qr-empty").style.display = "none";
      const qrResult = document.getElementById("qr-result");
      qrResult.classList.remove("hidden-by-default");
      qrResult.style.display = "block";
    });
  }
});
