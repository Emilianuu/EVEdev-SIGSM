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
        tipo: dbDoc.categoria || "Sin categoría", // Tomamos el nombre de la categoría
        fecha: dbDoc.fecha_carga,
        estado: dbDoc.activo ? "aprobado" : "rechazado", // Convertimos el 1/0 a estado visual
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

const statusMeta = {
  aprobado: { label: "Aprobado", cls: "badge-green" },
  pendiente: { label: "Pendiente", cls: "badge-amber" },
  revision: { label: "En Revisión", cls: "badge-blue" },
  rechazado: { label: "Rechazado", cls: "badge-red" },
};

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
  const total = docs.length;
  const pendientes = docs.filter(
    (d) => d.estado === "pendiente" || d.estado === "revision",
  ).length;
  const aprobados = docs.filter((d) => d.estado === "aprobado").length;

  document.getElementById("stat-total").textContent = total;
  document.getElementById("stat-pendientes").textContent = pendientes;
  document.getElementById("stat-aprobados").textContent = aprobados;

  const tbody = document.getElementById("dash-doc-table");

  // Imprimir el título en la tabla pequeña del inicio
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
      <td>${badgeHtml(d.estado)}</td>
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

function badgeHtml(estado) {
  const m = statusMeta[estado] || statusMeta.pendiente;
  return `<span class="badge-hc ${m.cls}"><i class="bi bi-circle-fill badge-circle-icon"></i> ${m.label}</span>`;
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
      <td>${d.tipo}</td>
      <td class="table-cell-date">${d.fecha}</td>
      <td>${badgeHtml(d.estado)}</td>
      <td>
        <div class="actions flex-end-actions">
          <button class="btn-hc btn-hc-ghost btn-xs btn-view-doc" title="Ver" data-id="${d.id}"><i class="bi bi-eye"></i></button>
          <button class="btn-hc btn-hc-ghost btn-xs" title="Descargar"><i class="bi bi-download"></i></button>
        </div>
      </td>
    </tr>`,
    )
    .join("");

  document.getElementById("doc-count-label").textContent =
    `Mostrando 1-${docs.length} de ${docs.length} documentos`;
}

// ── Visualizador de Documento ────────────────────────────────────────
function openDocumento(id) {
  currentDocId = id;
  // Convertir ID a entero porque desde la BD llega numérico o string numérico
  const d = docs.find((x) => x.id == id);
  if (!d) return;

  document.getElementById("dd-title").textContent = `${d.tipo} (#${d.id})`;

  let html = `<h3>${d.tituloVisor}</h3>`;
  html += `<h4>${d.resumenHeading}</h4><p>${d.resumenText}</p>`;
  if (d.listHeading && d.listItems) {
    html += `<h4>${d.listHeading}</h4><ul>${d.listItems.map((i) => `<li>${i}</li>`).join("")}</ul>`;
  }
  document.getElementById("dd-content").innerHTML = html;

  setAdminView("view-documento-detalle");
}

function setEstado(id, estado) {
  const d = docs.find((x) => x.id == id);
  if (!d) return;
  d.estado = estado;

  // En una versión final, aquí harías un fetch() para actualizar el estado "activo" en MySQL
  renderDashboard();
  showToast(
    estado === "aprobado" ? "Documento aprobado" : "Documento rechazado",
    estado === "aprobado" ? "bi-check-circle" : "bi-x-circle",
  );
}

// ── Nuevo Documento ──────────────────────────────────────────────────
async function saveDoc() {
  // Nota: Para que el guardado funcione 100% con la BD, deberás
  // actualizar el HTML de esta vista para pedir: "titulo" y "id_categoria"
  const tipo = document.getElementById("nd-tipo").value; // Lo usaremos temporalmente como título
  const estado = document.getElementById("nd-estado").value;
  const contenido = document.getElementById("nd-contenido").value.trim();
  const fileInput = document.getElementById("hiddenFileInput");

  if (!tipo) {
    alert("Seleccione el tipo de documento.");
    return;
  }

  const formData = new FormData();
  formData.append("accion", "alta_documento");

  // Adaptamos temporalmente los campos del HTML a los que exige la BD
  formData.append("titulo", tipo + " - " + contenido.substring(0, 20));
  formData.append("id_categoria", 1); // Mock temporal de categoría
  formData.append("archivo_url", "/docs/nuevo_doc.pdf");

  if (fileInput.files.length > 0) {
    formData.append("archivo", fileInput.files[0]);
  }

  try {
    const response = await fetch("../controlador/dashboard_controlador.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.status === "success") {
      showToast("Documento guardado correctamente", "bi-check-circle");

      document.getElementById("nd-contenido").value = "";
      document.getElementById("nd-tipo").value = "";
      document.getElementById("nd-estado").value = "pendiente";
      if (fileInput) fileInput.value = "";

      cargarDocumentosServidor();
      setAdminView("view-documentos");
    } else {
      alert("Error: " + data.mensaje);
    }
  } catch (error) {
    console.error("Error en la petición:", error);
    alert("Ocurrió un error al guardar el documento.");
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
  document.addEventListener("click", (e) => {
    const docRow = e.target.closest(".clickable-doc-row");
    if (docRow) {
      openDocumento(docRow.dataset.id);
    }
    const viewBtn = e.target.closest(".btn-view-doc");
    if (viewBtn) {
      openDocumento(viewBtn.dataset.id);
    }
  });

  /* Events: Document Viewer Action Buttons */
  const btnApprove = document.getElementById("btnApproveDoc");
  if (btnApprove) {
    btnApprove.addEventListener("click", () =>
      setEstado(currentDocId, "aprobado"),
    );
  }

  const btnReject = document.getElementById("btnRejectDoc");
  if (btnReject) {
    btnReject.addEventListener("click", () =>
      setEstado(currentDocId, "rechazado"),
    );
  }

  /* Events: Upload & Save Docs */
  const btnSaveDocEl = document.getElementById("btnSaveDoc");
  if (btnSaveDocEl) btnSaveDocEl.addEventListener("click", saveDoc);

  const uploadZone = document.getElementById("uploadZoneArea");
  if (uploadZone) {
    uploadZone.addEventListener("click", function () {
      const input = document.getElementById("hiddenFileInput");
      if (input) input.click();
    });
  }

  /* Events: QR Generator */
  const btnGenQr = document.getElementById("btnGenerarQr");
  if (btnGenQr) {
    btnGenQr.addEventListener("click", generarQr);
  }

  /* Initialize starting views */
  cargarDocumentosServidor();
});
