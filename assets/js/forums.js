(() => {
  let countdownInterval = null;
  let countdownTargetMs = null;
  let currentPage = 1;
  let totalPages = 1;

  function twoDigits(value) {
    return String(value).padStart(2, "0");
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function getRoleFromHash(query = "") {
    const fromHash = new URLSearchParams(query).get("role");
    const fromBody = document.body.getAttribute("data-active-role") || "guest";
    return fromHash || fromBody || "guest";
  }

  function openForumDetail(forumId) {
    const hashPayload = window.location.hash.replace("#", "");
    const [, query = ""] = hashPayload.split("?");
    const role = getRoleFromHash(query);
    const params = new URLSearchParams();
    params.set("forum", String(forumId));
    if (role !== "guest") {
      params.set("role", role);
    }
    window.location.hash = `#view-forum-detail?${params.toString()}`;
  }

  function setCountdownValues({ days = 0, hours = 0, minutes = 0, seconds = 0 }) {
    const target = document.getElementById("forumCountdown");
    if (!target) return;
    target.querySelector('[data-unit="days"]').textContent = twoDigits(days);
    target.querySelector('[data-unit="hours"]').textContent = twoDigits(hours);
    target.querySelector('[data-unit="minutes"]').textContent = twoDigits(minutes);
    target.querySelector('[data-unit="seconds"]').textContent = twoDigits(seconds);
  }

  function setCountdownStatus(message) {
    const status = document.getElementById("forumCountdownStatus");
    if (status) status.textContent = message;
  }

  function stopCountdown() {
    if (countdownInterval) {
      clearInterval(countdownInterval);
      countdownInterval = null;
    }
  }

  function renderCountdownTick() {
    if (!countdownTargetMs) {
      setCountdownValues({});
      setCountdownStatus("Esperando fecha...");
      return;
    }

    const diffMs = countdownTargetMs - Date.now();
    if (diffMs <= 0) {
      stopCountdown();
      setCountdownValues({});
      setCountdownStatus("El foro ya comenzó. Recargando próximo evento...");
      loadNextForum();
      return;
    }

    const totalSeconds = Math.floor(diffMs / 1000);
    const days = Math.floor(totalSeconds / 86400);
    const hours = Math.floor((totalSeconds % 86400) / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    setCountdownValues({ days, hours, minutes, seconds });
    setCountdownStatus("Cuenta regresiva activa");
  }

  function startCountdown(isoDate) {
    const parsedMs = Date.parse(isoDate);
    if (Number.isNaN(parsedMs)) {
      countdownTargetMs = null;
      stopCountdown();
      setCountdownStatus("Fecha inválida recibida desde API.");
      return;
    }

    countdownTargetMs = parsedMs;
    stopCountdown();
    renderCountdownTick();
    countdownInterval = setInterval(renderCountdownTick, 1000);
  }

  function showAlert(message) {
    const alertBox = document.getElementById("forumsApiAlert");
    if (!alertBox) return;
    if (!message) {
      alertBox.classList.add("hidden");
      alertBox.textContent = "";
      return;
    }
    alertBox.classList.remove("hidden");
    alertBox.textContent = message;
  }

  function showDetailAlert(message) {
    const alertBox = document.getElementById("forumDetailAlert");
    if (!alertBox) return;
    if (!message) {
      alertBox.classList.add("hidden");
      alertBox.textContent = "";
      return;
    }
    alertBox.classList.remove("hidden");
    alertBox.textContent = message;
  }

  function formatDate(dateString, timezone) {
    try {
      return new Intl.DateTimeFormat("es-AR", {
        dateStyle: "full",
        timeStyle: "short",
        timeZone: timezone || "UTC"
      }).format(new Date(dateString));
    } catch (_error) {
      return dateString;
    }
  }

  function renderNextForum(forum) {
    const card = document.getElementById("nextForumCard");
    if (!card) return;

    if (!forum) {
      card.innerHTML = '<p class="text-sm text-slate-500">No hay foros publicados con fecha futura.</p>';
      countdownTargetMs = null;
      stopCountdown();
      setCountdownValues({});
      setCountdownStatus("Sin próximos eventos.");
      return;
    }

    card.innerHTML = `
      <p class="text-xs uppercase tracking-widest text-teal-600 font-extrabold mb-2">${escapeHtml(forum.code)}</p>
      <h4 class="text-xl font-extrabold text-slate-900 mb-2">${escapeHtml(forum.title)}</h4>
      <p class="text-slate-600 mb-4">${escapeHtml(forum.description)}</p>
      <ul class="space-y-2 text-sm text-slate-700">
        <li><i class="fa-solid fa-calendar text-teal-500 mr-2"></i>${escapeHtml(formatDate(forum.startsAt, forum.timezone))}</li>
        <li><i class="fa-solid fa-globe text-teal-500 mr-2"></i>Zona horaria: ${escapeHtml(forum.timezone)}</li>
        <li><i class="fa-solid fa-video text-teal-500 mr-2"></i>Plataforma: ${escapeHtml(forum.platformType)}</li>
        ${forum.platformUrl ? `<li><i class="fa-solid fa-link text-teal-500 mr-2"></i><a class="text-teal-700 underline" href="${escapeHtml(forum.platformUrl)}" target="_blank" rel="noopener noreferrer">Abrir enlace</a></li>` : ""}
      </ul>
    `;

    startCountdown(forum.startsAt);
  }

  async function loadNextForum() {
    try {
      const data = await window.appApiFetch("/api/forums/next.php");
      renderNextForum(data.forum || null);
      showAlert("");
    } catch (error) {
      showAlert(error instanceof Error ? error.message : "No se pudo cargar el próximo foro.");
    }
  }

  function renderForumsList(items) {
    const container = document.getElementById("forumsList");
    if (!container) return;

    if (!Array.isArray(items) || items.length === 0) {
      container.innerHTML = '<p class="text-sm text-slate-500">No hay foros para esta página.</p>';
      return;
    }

    container.innerHTML = items.map((forum) => `
      <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <p class="text-xs uppercase tracking-widest text-teal-600 font-bold mb-1">${escapeHtml(forum.code)}</p>
        <h4 class="text-lg font-extrabold text-slate-900 mb-2">${escapeHtml(forum.title)}</h4>
        <p class="text-sm text-slate-600 mb-3">${escapeHtml(forum.description)}</p>
        <p class="text-sm text-slate-700"><i class="fa-solid fa-clock mr-2 text-teal-500"></i>${escapeHtml(formatDate(forum.startsAt, forum.timezone))}</p>
        <p class="text-sm text-slate-700"><i class="fa-solid fa-globe mr-2 text-teal-500"></i>${escapeHtml(forum.timezone)}</p>
        <p class="text-sm text-slate-700"><i class="fa-solid fa-video mr-2 text-teal-500"></i>${escapeHtml(forum.platformType)}</p>
        <button type="button" data-forum-detail-id="${escapeHtml(forum.id)}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700">
          Ver detalle <i class="fa-solid fa-arrow-right"></i>
        </button>
      </article>
    `).join("");

    container.querySelectorAll("[data-forum-detail-id]").forEach((button) => {
      button.addEventListener("click", () => openForumDetail(button.dataset.forumDetailId));
    });
  }

  function renderPaginationMeta(pagination) {
    const meta = document.getElementById("forumsPaginationMeta");
    const prev = document.getElementById("forumsPrevPage");
    const next = document.getElementById("forumsNextPage");

    currentPage = Number(pagination?.page || 1);
    totalPages = Number(pagination?.totalPages || 1);

    if (meta) {
      meta.textContent = `Página ${currentPage} de ${totalPages} · ${pagination?.total || 0} foros publicados.`;
    }
    if (prev) prev.disabled = currentPage <= 1;
    if (next) next.disabled = currentPage >= totalPages;
  }

  function renderForumDetail(forum) {
    const container = document.getElementById("forumDetailContainer");
    if (!container) return;

    const topics = Array.isArray(forum?.topics) ? forum.topics : [];
    const guests = Array.isArray(forum?.guests) ? forum.guests : [];

    container.innerHTML = `
      <article class="bg-white rounded-3xl border border-slate-200 p-8 lg:p-10 shadow-xl">
        <p class="text-xs uppercase tracking-widest text-teal-600 font-bold mb-3">${escapeHtml(forum.code)}</p>
        <h2 class="text-4xl font-extrabold text-slate-900 mb-4">${escapeHtml(forum.title)}</h2>
        <p class="text-lg text-slate-600 mb-6">${escapeHtml(forum.description)}</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700 mb-8">
          <p class="rounded-2xl bg-slate-50 border border-slate-200 p-4"><i class="fa-solid fa-calendar text-teal-500 mr-2"></i>${escapeHtml(formatDate(forum.startsAt, forum.timezone))}</p>
          <p class="rounded-2xl bg-slate-50 border border-slate-200 p-4"><i class="fa-solid fa-globe text-teal-500 mr-2"></i>${escapeHtml(forum.timezone)}</p>
          <p class="rounded-2xl bg-slate-50 border border-slate-200 p-4"><i class="fa-solid fa-video text-teal-500 mr-2"></i>${escapeHtml(forum.modality || forum.platformType)}</p>
          <p class="rounded-2xl bg-slate-50 border border-slate-200 p-4"><i class="fa-solid fa-users text-teal-500 mr-2"></i>${escapeHtml(forum.seatsAvailable)} / ${escapeHtml(forum.seatsTotal)} cupos disponibles</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="space-y-6">
            <section class="rounded-3xl bg-slate-50 border border-slate-200 p-6">
              <h3 class="text-xl font-bold text-slate-900 mb-3">Objetivo</h3>
              <p class="text-slate-700">${escapeHtml(forum.objective || "-")}</p>
            </section>
            <section class="rounded-3xl bg-slate-50 border border-slate-200 p-6">
              <h3 class="text-xl font-bold text-slate-900 mb-3">Temáticas</h3>
              <ul class="space-y-2 text-slate-700">
                ${topics.length > 0 ? topics.map((topic) => `<li class="flex"><i class="fa-solid fa-check text-teal-500 mt-1 mr-2"></i><span>${escapeHtml(topic)}</span></li>`).join("") : '<li>Sin temáticas publicadas.</li>'}
              </ul>
            </section>
            <section class="rounded-3xl bg-slate-50 border border-slate-200 p-6">
              <h3 class="text-xl font-bold text-slate-900 mb-3">Requisitos</h3>
              <p class="text-slate-700">${escapeHtml(forum.requirements || "Sin requisitos adicionales")}</p>
            </section>
          </div>

          <div class="space-y-6">
            <section class="rounded-3xl bg-slate-900 text-white border border-slate-800 p-6">
              <h3 class="text-xl font-bold mb-3">Invitados</h3>
              <div class="space-y-4">
                ${guests.length > 0 ? guests.map((guest) => `
                  <article class="rounded-2xl bg-slate-800/90 p-4 border border-slate-700">
                    <p class="font-bold text-teal-300">${escapeHtml(guest.name)}</p>
                    <p class="text-xs uppercase tracking-widest text-slate-300 mb-2">${escapeHtml(guest.role)}</p>
                    <p class="text-sm text-slate-200">${escapeHtml(guest.bio)}</p>
                  </article>
                `).join("") : '<p class="text-sm text-slate-300">Sin invitados publicados.</p>'}
              </div>
            </section>
            <section class="rounded-3xl bg-white border border-slate-200 p-6 shadow-sm">
              <h3 class="text-xl font-bold text-slate-900 mb-3">Inscripción</h3>
              <p class="text-sm text-slate-600 mb-4">Asegura tu lugar en este foro antes de completar los cupos disponibles.</p>
              <a href="${escapeHtml(forum.ctaUrl || '#view-forums')}" class="inline-flex items-center gap-2 rounded-2xl bg-teal-600 px-6 py-3 text-sm font-bold text-white hover:bg-teal-700">
                ${escapeHtml(forum.ctaLabel || 'Inscribirme')} <i class="fa-solid fa-arrow-right"></i>
              </a>
            </section>
          </div>
        </div>
      </article>
    `;
  }

  async function loadForumDetailFromHash() {
    const payload = window.location.hash.replace("#", "");
    const [view, query = ""] = payload.split("?");
    if (view !== "view-forum-detail") {
      return;
    }

    const params = new URLSearchParams(query);
    const forumId = Number(params.get("forum") || 0);
    const container = document.getElementById("forumDetailContainer");

    if (!forumId) {
      showDetailAlert("Debes indicar un foro válido para ver el detalle.");
      if (container) {
        container.innerHTML = '<div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xl"><p class="text-sm text-slate-500">Selecciona un foro desde la agenda para ver el detalle.</p></div>';
      }
      return;
    }

    if (container) {
      container.innerHTML = '<div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xl"><p class="text-sm text-slate-500">Cargando detalle del foro...</p></div>';
    }

    try {
      const data = await window.appApiFetch(`/api/forums/detail.php?id=${forumId}`);
      renderForumDetail(data.forum || null);
      showDetailAlert("");
    } catch (error) {
      showDetailAlert(error instanceof Error ? error.message : "No se pudo cargar el detalle del foro.");
    }
  }

  async function loadForumsList(page = 1) {
    try {
      const data = await window.appApiFetch(`/api/forums/list.php?page=${page}&per_page=4`);
      renderForumsList(data.items || []);
      renderPaginationMeta(data.pagination || {});
      showAlert("");
    } catch (error) {
      showAlert(error instanceof Error ? error.message : "No se pudo cargar la agenda.");
    }
  }

  function setupPaginationEvents() {
    document.getElementById("forumsPrevPage")?.addEventListener("click", () => {
      if (currentPage > 1) loadForumsList(currentPage - 1);
    });
    document.getElementById("forumsNextPage")?.addEventListener("click", () => {
      if (currentPage < totalPages) loadForumsList(currentPage + 1);
    });
  }

  window.addEventListener("hashchange", loadForumDetailFromHash);

  window.addEventListener("DOMContentLoaded", () => {
    setupPaginationEvents();
    loadNextForum();
    loadForumsList(1);
    loadForumDetailFromHash();
  });
})();
