(() => {
  let countdownInterval = null;
  let countdownTargetMs = null;
  let currentPage = 1;
  let totalPages = 1;

  function twoDigits(value) {
    return String(value).padStart(2, "0");
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
      <p class="text-xs uppercase tracking-widest text-teal-600 font-extrabold mb-2">${forum.code}</p>
      <h4 class="text-xl font-extrabold text-slate-900 mb-2">${forum.title}</h4>
      <p class="text-slate-600 mb-4">${forum.description}</p>
      <ul class="space-y-2 text-sm text-slate-700">
        <li><i class="fa-solid fa-calendar text-teal-500 mr-2"></i>${formatDate(forum.startsAt, forum.timezone)}</li>
        <li><i class="fa-solid fa-globe text-teal-500 mr-2"></i>Zona horaria: ${forum.timezone}</li>
        <li><i class="fa-solid fa-video text-teal-500 mr-2"></i>Plataforma: ${forum.platformType}</li>
        ${forum.platformUrl ? `<li><i class="fa-solid fa-link text-teal-500 mr-2"></i><a class="text-teal-700 underline" href="${forum.platformUrl}" target="_blank" rel="noopener noreferrer">Abrir enlace</a></li>` : ""}
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
        <p class="text-xs uppercase tracking-widest text-teal-600 font-bold mb-1">${forum.code}</p>
        <h4 class="text-lg font-extrabold text-slate-900 mb-2">${forum.title}</h4>
        <p class="text-sm text-slate-600 mb-3">${forum.description}</p>
        <p class="text-sm text-slate-700"><i class="fa-solid fa-clock mr-2 text-teal-500"></i>${formatDate(forum.startsAt, forum.timezone)}</p>
        <p class="text-sm text-slate-700"><i class="fa-solid fa-globe mr-2 text-teal-500"></i>${forum.timezone}</p>
        <p class="text-sm text-slate-700"><i class="fa-solid fa-video mr-2 text-teal-500"></i>${forum.platformType}</p>
      </article>
    `).join("");
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

  window.addEventListener("DOMContentLoaded", () => {
    setupPaginationEvents();
    loadNextForum();
    loadForumsList(1);
  });
})();
