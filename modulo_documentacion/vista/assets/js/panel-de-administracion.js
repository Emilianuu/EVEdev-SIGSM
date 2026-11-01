// ── Init Temprano del Tema Unificado ──────────────────────
(function initTheme() {
  if (localStorage.getItem("theme") === "dark") {
    document.documentElement.setAttribute("data-theme", "dark");
  }
})();

// ── Datos fijos de demostración (sin backend) ──────────────────────
let hcCounter = 4029;

const docs = [
  {
    id: "HC-4029",
    tipo: "Epicrisis de Alta",
    fecha: "24 Oct 2026, 08:32",
    estado: "aprobado",
    tituloVisor: "EPICRISIS MÉDICA DE ALTA",
    resumenHeading: "Resumen de Egreso",
    resumenText:
      "La paciente, de 65 años de edad, habiendo ingresado el día 10 de octubre del corriente por cuadro clínico compatible con afección respiratoria aguda, evoluciona favorablemente bajo tratamiento indicado. Se otorga alta médica con control en policlínica externa.",
    listHeading: "Prescripciones al Egreso",
    listItems: [
      "Amoxicilina 875mg cada 12 horas por 7 días.",
      "Reposo relativo por 5 días en domicilio.",
    ],
    audit: [
      {
        label: "Carga de Documento completada",
        who: "Dr. Daniel Muñoz",
        when: "24 Oct, 08:32",
        tone: "gray",
      },
      {
        label: "Asignado para Validación de Firma",
        who: "Depto. Archivo Clínico",
        when: "24 Oct, 08:35",
        tone: "amber",
      },
      {
        label: "Documento aprobado",
        who: "Dr. Daniel Muñoz",
        when: "24 Oct, 09:10",
        tone: "green",
      },
    ],
  },
  {
    id: "HC-4028",
    tipo: "Informe Radiológico",
    fecha: "23 Oct 2026, 15:10",
    estado: "pendiente",
    tituloVisor: "INFORME RADIOLÓGICO",
    resumenHeading: "Hallazgos",
    resumenText:
      "Estudio de tórax sin alteraciones pleuroparenquimatosas agudas. Silueta cardíaca de tamaño conservado. Se sugiere control evolutivo según criterio clínico.",
    listHeading: "Observaciones",
    listItems: ["Pendiente de firma del médico radiólogo de guardia."],
    audit: [
      {
        label: "Carga de Documento completada",
        who: "Lic. Mesa Central",
        when: "23 Oct, 15:10",
        tone: "gray",
      },
      {
        label: "Asignado para Validación de Firma",
        who: "Depto. Archivo Clínico",
        when: "23 Oct, 15:12",
        tone: "amber",
      },
    ],
  },
  {
    id: "HC-4027",
    tipo: "Análisis de Sangre",
    fecha: "23 Oct 2026, 11:05",
    estado: "revision",
    tituloVisor: "ANÁLISIS DE SANGRE",
    resumenHeading: "Resultados",
    resumenText:
      "Hemograma completo y perfil metabólico dentro de parámetros normales. Se adjuntan valores de referencia comparados con estudio previo.",
    audit: [
      {
        label: "Carga de Documento completada",
        who: "Lic. Laboratorio Central",
        when: "23 Oct, 11:05",
        tone: "gray",
      },
      {
        label: "En revisión por médico tratante",
        who: "Dra. Patricia Núñez",
        when: "23 Oct, 14:20",
        tone: "amber",
      },
    ],
  },
  {
    id: "HC-4026",
    tipo: "Consentimiento Quirúrgico",
    fecha: "22 Oct 2026, 18:40",
    estado: "rechazado",
    tituloVisor: "CONSENTIMIENTO INFORMADO QUIRÚRGICO",
    resumenHeading: "Detalle",
    resumenText:
      "Formulario de consentimiento para procedimiento quirúrgico programado. Rechazado por falta de firma del segundo testigo requerido por protocolo institucional.",
    listHeading: "Motivo de rechazo",
    listItems: [
      "Falta firma de testigo. Debe reingresarse con el formulario completo.",
    ],
    audit: [
      {
        label: "Carga de Documento completada",
        who: "Enf. Mesa Central",
        when: "22 Oct, 18:40",
        tone: "gray",
      },
      {
        label: "Documento rechazado",
        who: "Dr. Daniel Muñoz",
        when: "22 Oct, 19:02",
        tone: "red",
      },
    ],
  },
  {
    id: "HC-4025",
    tipo: "Historia Clínica",
    fecha: "22 Oct 2026, 09:15",
    estado: "aprobado",
    tituloVisor: "HISTORIA CLÍNICA",
    resumenHeading: "Resumen",
    resumenText:
      "Actualización de historia clínica con antecedentes personales, familiares y evolución de controles periódicos. Sin datos de alarma en la consulta actual.",
    audit: [
      {
        label: "Carga de Documento completada",
        who: "Dr. Daniel Muñoz",
        when: "22 Oct, 09:15",
        tone: "gray",
      },
      {
        label: "Documento aprobado",
        who: "Dr. Daniel Muñoz",
        when: "22 Oct, 09:40",
        tone: "green",
      },
    ],
  },
  {
    id: "HC-4024",
    tipo: "Reporte de Guardia",
    fecha: "21 Oct 2026, 20:03",
    estado: "pendiente",
    tituloVisor: "REPORTE DE GUARDIA",
    resumenHeading: "Resumen de Guardia",
    resumenText:
      "Ingreso por guardia con cuadro de dolor abdominal. Se indican estudios complementarios y se deja en observación para reevaluación en las próximas horas.",
    audit: [
      {
        label: "Carga de Documento completada",
        who: "Dr. de Guardia",
        when: "21 Oct, 20:03",
        tone: "gray",
      },
    ],
  },
];

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
  tbody.innerHTML = docs
    .slice(0, 4)
    .map(
      (d) => `
    <tr class="clickable-doc-row" data-id="${d.id}">
      <td><strong>#${d.id}</strong></td>
      <td>${d.tipo}</td>
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

// ── Nuevo Documento ──────────────────────────────────────────────────
function saveDoc() {
  const tipo = document.getElementById("nd-tipo").value;
  const estado = document.getElementById("nd-estado").value;
  const contenido = document.getElementById("nd-contenido").value.trim();

  if (!tipo) {
    alert("Seleccione el tipo de documento.");
    return;
  }

  hcCounter += 1;
  const nuevo = {
    id: `HC-${hcCounter}`,
    tipo,
    fecha:
      new Date().toLocaleDateString("es-UY", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      }) +
      ", " +
      new Date().toLocaleTimeString("es-UY", {
        hour: "2-digit",
        minute: "2-digit",
      }),
    estado,
    tituloVisor: tipo.toUpperCase(),
    resumenHeading: "Contenido",
    resumenText: contenido || "Sin contenido adicional cargado.",
    audit: [
      {
        label: "Carga de Documento completada",
        who: "Dr. Daniel Muñoz",
        when: "recién",
        tone: "gray",
      },
    ],
  };
  docs.unshift(nuevo);

  document.getElementById("nd-contenido").value = "";
  document.getElementById("nd-tipo").value = "";
  document.getElementById("nd-estado").value = "pendiente";

  showToast("Documento guardado correctamente", "bi-check-circle");
  setAdminView("view-documentos");
}

// ── Visualizador de Documento ────────────────────────────────────────
function openDocumento(id) {
  currentDocId = id;
  const d = docs.find((x) => x.id === id);
  if (!d) return;

  document.getElementById("dd-title").textContent = `${d.tipo} (#${d.id})`;

  let html = `<h3>${d.tituloVisor}</h3>`;
  html += `<h4>${d.resumenHeading}</h4><p>${d.resumenText}</p>`;
  if (d.listHeading && d.listItems) {
    html += `<h4>${d.listHeading}</h4><ul>${d.listItems.map((i) => `<li>${i}</li>`).join("")}</ul>`;
  }
  document.getElementById("dd-content").innerHTML = html;

  renderAudit(d);
  setAdminView("view-documento-detalle");
}

function renderAudit(d) {
  document.getElementById("dd-audit").innerHTML = d.audit
    .map(
      (a) => `
    <div class="timeline-item">
      <div class="timeline-dot ${a.tone}"></div>
      <div>
        <p class="timeline-title">${a.label}</p>
        <p class="timeline-sub">Por ${a.who} — ${a.when}</p>
      </div>
    </div>`,
    )
    .join("");
}

function setEstado(id, estado) {
  const d = docs.find((x) => x.id === id);
  if (!d) return;
  d.estado = estado;
  d.audit.push({
    label: estado === "aprobado" ? "Documento aprobado" : "Documento rechazado",
    who: "Dr. Daniel Muñoz",
    when: "recién",
    tone: estado === "aprobado" ? "green" : "red",
  });
  renderAudit(d);
  renderDashboard();
  showToast(
    estado === "aprobado" ? "Documento aprobado" : "Documento rechazado",
    estado === "aprobado" ? "bi-check-circle" : "bi-x-circle",
  );
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

  const doc = docs.find((d) => d.id === docId);

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
  renderDashboard();
  renderDocumentosTable();
});
