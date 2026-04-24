(() => {
  let signatureCtx = null;
  let signatureIsDrawing = false;
  let activeReferralOffer = null;
  let activeRegistrationCountry = "AR";

  function normalizeCountryCode(value) {
    const raw = String(value || "").trim().toUpperCase();
    return /^[A-Z]{2}$/.test(raw) ? raw : "";
  }

  function inferCountryFromLocale() {
    const locales = Array.isArray(navigator.languages) && navigator.languages.length
      ? navigator.languages
      : [navigator.language || ""];

    for (const locale of locales) {
      const match = String(locale).match(/[-_]([A-Z]{2})$/i);
      if (match?.[1]) {
        const normalized = normalizeCountryCode(match[1]);
        if (normalized) return normalized;
      }
    }

    return "AR";
  }

  function resolveRegistrationCountry() {
    const params = new URLSearchParams(window.location.search);
    const countryFromQuery = normalizeCountryCode(params.get("country"));
    if (countryFromQuery) return countryFromQuery;

    const selector = document.getElementById("registrationCountrySelect");
    if (selector instanceof HTMLSelectElement) {
      const selected = normalizeCountryCode(selector.value);
      if (selected) return selected;
    }

    return inferCountryFromLocale();
  }

  function setupRegistrationCountrySelector() {
    const selector = document.getElementById("registrationCountrySelect");
    if (!(selector instanceof HTMLSelectElement)) return;

    const preferredCountry = resolveRegistrationCountry();
    const hasOption = Array.from(selector.options).some((option) => option.value === preferredCountry);
    selector.value = hasOption ? preferredCountry : "AR";
    activeRegistrationCountry = normalizeCountryCode(selector.value) || "AR";

    selector.addEventListener("change", () => {
      activeRegistrationCountry = normalizeCountryCode(selector.value) || "AR";
      loadOfferFromReferralCode(activeRegistrationCountry);
    });
  }

  function showRegisterFeedback(type, message) {
    const box = document.getElementById("registerFormAlert");
    if (!box) return;
    box.classList.remove("hidden", "bg-emerald-50", "text-emerald-800", "border", "border-emerald-200", "bg-rose-50", "text-rose-700", "border-rose-200");
    if (type === "success") {
      box.classList.add("bg-emerald-50", "text-emerald-800", "border", "border-emerald-200");
    } else {
      box.classList.add("bg-rose-50", "text-rose-700", "border", "border-rose-200");
    }
    box.textContent = message;
  }

  function setSignatureCanvasSize(canvas) {
    const ratio = window.devicePixelRatio || 1;
    const { width, height } = canvas.getBoundingClientRect();
    canvas.width = Math.floor(width * ratio);
    canvas.height = Math.floor(height * ratio);
    signatureCtx = canvas.getContext("2d");
    signatureCtx.scale(ratio, ratio);
    signatureCtx.lineWidth = 2;
    signatureCtx.lineCap = "round";
    signatureCtx.strokeStyle = "#0f172a";
    signatureCtx.fillStyle = "#ffffff";
    signatureCtx.fillRect(0, 0, width, height);
  }

  function getCanvasPoint(canvas, event) {
    const rect = canvas.getBoundingClientRect();
    if (event.touches?.[0]) {
      return { x: event.touches[0].clientX - rect.left, y: event.touches[0].clientY - rect.top };
    }
    return { x: event.clientX - rect.left, y: event.clientY - rect.top };
  }

  function isCanvasBlank(canvas) {
    const context = canvas.getContext("2d");
    if (!context) return true;
    const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;
    for (let i = 0; i < pixels.length; i += 4) {
      if (pixels[i] !== 255 || pixels[i + 1] !== 255 || pixels[i + 2] !== 255 || pixels[i + 3] !== 255) {
        return false;
      }
    }
    return true;
  }

  function applyReferralOfferUI(offer) {
    const notice = document.getElementById("referralOfferNotice");
    const input = document.getElementById("referralCodeInput");
    const paymentInstructions = document.getElementById("paymentInstructions");
    const paymentLink = document.getElementById("paymentGatewayLink");

    activeReferralOffer = offer;
    if (input) input.value = offer?.referralCode || "";

    if (!notice || !paymentInstructions || !paymentLink) return;

    if (!offer) {
      notice.classList.add("hidden");
      paymentInstructions.innerHTML = '<i class="fa-solid fa-link mr-2"></i> Realiza el abono de inscripción al Foro aquí:';
      paymentLink.href = "#";
      paymentLink.textContent = "Ir a la pasarela de Pago";
      return;
    }

    notice.classList.remove("hidden");
    const appliedCountry = offer.countryCode || activeRegistrationCountry;
    notice.textContent = `Inscripción con referido de ${offer.associateName}. Precio aplicado: ${offer.priceAmount} ${offer.currencyCode}. Método: ${offer.paymentMethod}. País: ${appliedCountry}.`;
    paymentInstructions.innerHTML = `<i class="fa-solid fa-link mr-2"></i> Pago con referido (${offer.referralCode}) para ${appliedCountry}: ${offer.paymentMethod}.`;
    paymentLink.href = offer.paymentLink;
    paymentLink.textContent = `Pagar ${offer.priceAmount} ${offer.currencyCode}`;
  }

  async function loadOfferFromReferralCode(countryOverride = "") {
    const params = new URLSearchParams(window.location.search);
    const code = (params.get("ref") || "").trim();
    if (!code) {
      applyReferralOfferUI(null);
      return;
    }

    try {
      const country = normalizeCountryCode(countryOverride) || resolveRegistrationCountry();
      activeRegistrationCountry = country || "AR";
      const result = await window.appApiFetch(`/api/referrals/offer?code=${encodeURIComponent(code)}&country=${encodeURIComponent(activeRegistrationCountry)}`);
      applyReferralOfferUI(result.offer || null);
    } catch (_error) {
      applyReferralOfferUI(null);
    }
  }

  function resetRegisterForm() {
    const form = document.getElementById("registerForm");
    const canvas = document.getElementById("signatureCanvas");
    form?.reset();
    window.toggleCertFields(false);
    if (canvas) setSignatureCanvasSize(canvas);
  }

  window.toggleCertFields = (show) => {
    const certFields = document.getElementById("certFields");
    const proofInput = document.getElementById("paymentProof");
    certFields?.classList.toggle("hidden", !show);
    if (proofInput) {
      proofInput.required = Boolean(show);
      proofInput.disabled = !show;
      if (!show) proofInput.value = "";
    }
  };

  window.refreshDashboardSummary = async () => {
    try {
      const { summary } = await window.appApiFetch("/api/dashboard/summary");
      const adminCards = document.getElementById("adminKpiCards");
      if (adminCards) {
        adminCards.innerHTML = `
          <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Inscripciones Totales</p>
            <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${summary.registrations_total ?? 0}</h3>
            <p class="text-sm text-slate-600">Base de inscripciones registradas.</p>
          </div>
          <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Pagos Pendientes</p>
            <h3 class="text-3xl font-extrabold text-amber-600 mb-4">${summary.pending_payments_total ?? 0}</h3>
            <p class="text-sm text-slate-600">Comprobantes por revisión administrativa.</p>
          </div>
          <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Usuarios Registrados</p>
            <h3 class="text-3xl font-extrabold text-teal-600 mb-4">${summary.users_total ?? 0}</h3>
            <p class="text-sm text-slate-600">Total de cuentas activas en plataforma.</p>
          </div>
        `;
      }
    } catch (_error) {
      // silencioso: mantenemos datos mock si no hay backend disponible
    }
  };

  async function loadAssociateOffer() {
    const form = document.getElementById("associateOfferForm");
    if (!form || document.body.getAttribute("data-active-role") !== "associate") return;

    try {
      const result = await window.appApiFetch("/api/associate/offer");
      const offer = result.offer;
      if (!offer) return;
      form.referralCode.value = offer.referralCode;
      form.currencyCode.value = offer.currencyCode;
      form.priceAmount.value = offer.priceAmount;
      form.paymentMethod.value = offer.paymentMethod;
      form.paymentLink.value = offer.paymentLink;
      const preview = document.getElementById("associateReferralPreview");
      if (preview) preview.textContent = `${window.location.origin}/inscripcion?ref=${offer.referralCode}&country=AR`;
    } catch (_error) {
      // noop
    }
  }

  function setupAssociateOfferForm() {
    const form = document.getElementById("associateOfferForm");
    const status = document.getElementById("associateOfferStatus");
    const preview = document.getElementById("associateReferralPreview");
    if (!form || !status || !preview) return;

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      const payload = {
        referralCode: String(form.referralCode.value || "").trim().toUpperCase(),
        currencyCode: String(form.currencyCode.value || "").trim().toUpperCase(),
        priceAmount: Number(form.priceAmount.value || 0),
        paymentMethod: String(form.paymentMethod.value || "").trim(),
        paymentLink: String(form.paymentLink.value || "").trim()
      };

      try {
        await window.appApiFetch("/api/associate/offer", { method: "POST", body: JSON.stringify(payload) });
        status.textContent = "Configuración guardada.";
        preview.textContent = `${window.location.origin}/inscripcion?ref=${payload.referralCode}&country=AR`;
      } catch (error) {
        status.textContent = error instanceof Error ? error.message : "No se pudo guardar.";
      }
    });
  }

  function renderAdminRegistrations(items) {
    const target = document.getElementById("adminRegistrationsList");
    if (!target) return;
    if (!Array.isArray(items) || items.length === 0) {
      target.innerHTML = '<p class="text-xs text-slate-500">Sin inscripciones cargadas.</p>';
      return;
    }

    target.innerHTML = items.slice(0, 12).map((item) => `
      <article class="rounded-xl border border-slate-200 bg-slate-50 p-3">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="font-bold">#${item.id} · ${item.full_name}</p>
            <p class="text-xs text-slate-500">${item.document_id} · ${item.forum_slot}</p>
            <p class="text-xs text-slate-500">Ref: ${item.referral_code || '—'} · ${item.price_amount || '—'} ${item.currency_code || ''}</p>
            ${renderStatusTimeline(item.status_history)}
          </div>
          <div class="flex items-center gap-2">
            <select data-action="status" data-id="${item.id}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
              <option value="pending" ${item.status === 'pending' ? 'selected' : ''}>Pendiente</option>
              <option value="payment_submitted" ${item.status === 'payment_submitted' ? 'selected' : ''}>Comprobante enviado</option>
              <option value="approved" ${item.status === 'approved' ? 'selected' : ''}>Aprobada</option>
              <option value="rejected" ${item.status === 'rejected' ? 'selected' : ''}>Rechazada</option>
            </select>
            <button data-action="delete" data-id="${item.id}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-bold text-white">Eliminar</button>
          </div>
        </div>
      </article>
    `).join("");
  }

  function renderStatusTimeline(history) {
    if (!Array.isArray(history) || history.length === 0) {
      return '<p class="mt-1 text-xs text-slate-400">Sin historial de cambios.</p>';
    }

    const lines = history.slice(0, 4).map((entry) => {
      const from = entry.from_status || "inicio";
      const to = entry.to_status || "—";
      const role = entry.reviewed_by_role || "sistema";
      const note = entry.note ? ` · Nota: ${entry.note}` : "";
      const date = entry.created_at ? new Date(entry.created_at).toLocaleString() : "sin fecha";
      return `<li class="text-[11px] text-slate-600">${from} → ${to} · ${role} · ${date}${note}</li>`;
    });

    return `<ul class="mt-1 space-y-1 rounded-lg bg-white/70 p-2">${lines.join("")}</ul>`;
  }


  function renderAssociateRegistrations(items) {
    const target = document.getElementById("associateRegistrationsList");
    if (!target) return;
    if (!Array.isArray(items) || items.length === 0) {
      target.innerHTML = '<p class="text-xs text-slate-500">Sin registros referidos para este filtro.</p>';
      return;
    }

    target.innerHTML = items.map((item) => `
      <article class="rounded-xl border border-violet-100 bg-white p-3">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <p class="font-bold text-slate-800">#${item.id} · ${item.full_name}</p>
            <p class="text-xs text-slate-500">${item.document_id} · ${item.forum_slot}</p>
            <p class="text-xs text-slate-500">Estado actual: <span class="font-bold text-slate-700">${item.status}</span> · Ref: ${item.referral_code || "—"}</p>
            ${renderStatusTimeline(item.status_history)}
          </div>
          <div class="flex items-center gap-2">
            <button data-action="associate-approve" data-id="${item.id}" class="rounded-lg bg-emerald-600 px-2 py-1 text-xs font-bold text-white">Aprobar</button>
            <button data-action="associate-reject" data-id="${item.id}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-bold text-white">Rechazar</button>
          </div>
        </div>
      </article>
    `).join("");
  }

  function statusLabel(status) {
    if (status === "approved") return "Aprobada";
    if (status === "rejected") return "Rechazada";
    if (status === "payment_submitted") return "Comprobante enviado";
    return "Pendiente";
  }

  async function loadAssociateRegistrations() {
    if (document.body.getAttribute("data-active-role") !== "associate") return;
    const filter = document.getElementById("associateRegistrationsFilter");
    const status = filter ? String(filter.value || "all") : "all";
    const query = status === "all" ? "" : `?status=${encodeURIComponent(status)}`;

    try {
      const result = await window.appApiFetch(`/api/associate/registrations${query}`);
      renderAssociateRegistrations(result.items || []);
      const referralInput = document.getElementById("myReferralCode");
      const referralLink = referralInput instanceof HTMLInputElement ? String(referralInput.value || `${window.location.origin}/`) : `${window.location.origin}/`;
      renderAssociateNetwork(result.items || [], referralLink);
    } catch (_error) {
      renderAssociateRegistrations([]);
      renderAssociateNetwork([], `${window.location.origin}/`);
    }
  }

  function renderAdminAssociates(items) {
    const target = document.getElementById("adminAssociatesList");
    if (!target) return;
    if (!Array.isArray(items) || items.length === 0) {
      target.innerHTML = '<p class="text-xs text-slate-500">Sin asociados.</p>';
      return;
    }

    target.innerHTML = items.map((item) => `
      <article class="rounded-xl border border-slate-200 bg-slate-50 p-3">
        <p class="font-bold">${item.full_name} <span class="text-xs text-slate-500">(${item.email || 'sin email'})</span></p>
        <p class="text-xs text-slate-600">Código: ${item.referral_code || 'sin configurar'} · ${item.price_amount || '-'} ${item.currency_code || ''}</p>
      </article>
    `).join("");
  }

  function renderAdminPayments(items) {
    const target = document.getElementById("adminPaymentsContainer");
    if (!target) return;
    if (!Array.isArray(items) || items.length === 0) {
      target.innerHTML = '<p class="text-slate-500">No hay pagos para validar en este momento.</p>';
      return;
    }

    target.innerHTML = items.slice(0, 20).map((item) => {
      const statusClass = item.status === "approved"
        ? "text-emerald-700"
        : item.status === "rejected"
          ? "text-rose-700"
          : item.status === "payment_submitted"
            ? "text-blue-700"
            : "text-amber-700";
      return `
        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
          <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
              <p class="font-bold text-slate-900">#${item.id} · ${item.full_name || "Registro sin nombre"}</p>
              <p class="text-xs text-slate-500">${item.email || "sin email"} · ${item.document_id || "sin documento"}</p>
              <p class="text-xs text-slate-500">Comprobante: ${item.payment_proof_name || "No adjunto"} · ${item.price_amount || "—"} ${item.currency_code || ""}</p>
            </div>
            <div class="text-left md:text-right">
              <p class="text-xs font-bold uppercase ${statusClass}">${statusLabel(item.status || "pending")}</p>
              <p class="text-xs text-slate-500">${item.updated_at ? new Date(item.updated_at).toLocaleString() : "Sin fecha de actualización"}</p>
            </div>
          </div>
        </article>
      `;
    }).join("");
  }

  function renderAssociateNetwork(items, referralLink) {
    const overview = document.getElementById("associateNetworkOverview");
    const byCountry = document.getElementById("associateReferralCountryList");
    const pendingBox = document.getElementById("associatePendingApprovals");
    const historyBox = document.getElementById("associateHistoryList");
    const validatePaymentsBox = document.getElementById("associatePaymentsContainer");
    if (!overview || !byCountry || !pendingBox || !historyBox) return;

    const list = Array.isArray(items) ? items : [];
    const total = list.length;
    const pending = list.filter((item) => item.status === "pending").length;
    const approved = list.filter((item) => item.status === "approved").length;

    overview.innerHTML = `
      <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Total Referidos</p>
        <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${total}</h3>
        <p class="text-sm text-slate-600">Contactos acumulados en tu red.</p>
      </div>
      <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Pendientes</p>
        <h3 class="text-3xl font-extrabold text-amber-600 mb-4">${pending}</h3>
        <p class="text-sm text-slate-600">Pagos por revisar y aprobar.</p>
      </div>
      <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Aprobadas</p>
        <h3 class="text-3xl font-extrabold text-emerald-600 mb-4">${approved}</h3>
        <p class="text-sm text-slate-600">Inscripciones validadas por tu equipo.</p>
      </div>
    `;

    const countryMap = list.reduce((acc, item) => {
      const key = String(item.country_code || "LATAM").toUpperCase();
      acc[key] = (acc[key] || 0) + 1;
      return acc;
    }, {});
    const countries = Object.entries(countryMap);
    byCountry.innerHTML = countries.length
      ? countries.map(([country, count]) => `
        <article class="rounded-xl bg-slate-50 border border-slate-100 p-4 flex items-center justify-between">
          <div>
            <p class="font-bold text-slate-900">${country}</p>
            <p class="text-xs text-slate-500">${count} referidos registrados</p>
          </div>
          <input readonly value="${referralLink}${referralLink.includes("?") ? "&" : "?"}country=${country}" class="w-56 px-3 py-2 rounded-lg border border-slate-300 bg-white text-xs font-mono">
        </article>
      `).join("")
      : '<p class="text-slate-500">Aún no hay distribución por países.</p>';

    const pendingItems = list.filter((item) => item.status === "pending").slice(0, 6);
    pendingBox.innerHTML = pendingItems.length
      ? pendingItems.map((item) => `
        <article class="rounded-xl bg-slate-50 border border-slate-100 p-4">
          <p class="font-bold text-slate-900">${item.full_name || "Referido sin nombre"}</p>
          <p class="text-xs text-slate-500">${item.email || "sin email"} · ${item.forum_slot || "foro sin asignar"}</p>
        </article>
      `).join("")
      : '<p class="text-slate-500">No hay pagos pendientes por aprobar.</p>';

    if (validatePaymentsBox) {
      validatePaymentsBox.innerHTML = pendingItems.length
        ? pendingItems.map((item) => `
          <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
              <div>
                <p class="font-bold text-slate-900">#${item.id} · ${item.full_name || "Referido sin nombre"}</p>
                <p class="text-xs text-slate-500">${item.document_id || "sin documento"} · ${item.forum_slot || "foro sin asignar"}</p>
                <p class="text-xs text-slate-500">Estado: ${statusLabel(item.status || "pending")} · Ref: ${item.referral_code || "—"}</p>
                <p class="text-xs text-slate-500 mt-1">Esperado: ${item.price_amount || "—"} ${item.currency_code || ""} · ${item.payment_method || "método no informado"}</p>
                ${item.payment_link ? `<a class="text-xs text-teal-700 underline" href="${item.payment_link}" target="_blank" rel="noopener noreferrer">Link de pago esperado</a>` : ""}
              </div>
              <div class="space-y-2 md:text-right">
                ${item.payment_proof_preview ? (
                  String(item.payment_proof_mime || "").includes("image/")
                    ? `<img src="${item.payment_proof_preview}" alt="Comprobante ${item.id}" class="h-28 w-28 rounded-lg border border-slate-200 object-cover md:ml-auto">`
                    : `<a href="${item.payment_proof_preview}" target="_blank" rel="noopener noreferrer" class="inline-block rounded-lg bg-slate-200 px-3 py-1 text-xs font-bold text-slate-700">Ver comprobante</a>`
                ) : '<p class="text-xs text-rose-600 font-semibold">Sin comprobante adjunto</p>'}
                <div class="flex gap-2 md:justify-end">
                  <button data-action="associate-approve" data-id="${item.id}" class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-bold text-white">Aprobar</button>
                  <button data-action="associate-reject" data-id="${item.id}" class="rounded-lg bg-rose-600 px-3 py-1 text-xs font-bold text-white">Rechazar</button>
                </div>
              </div>
            </div>
          </article>
        `).join("")
        : '<p class="text-slate-500">No hay comprobantes pendientes por validar.</p>';
    }

    historyBox.innerHTML = list.length
      ? list.slice(0, 8).map((item) => `
        <article class="rounded-xl bg-slate-50 border border-slate-100 p-4">
          <p class="font-bold text-slate-900">${item.full_name || "Referido sin nombre"}</p>
          <p class="text-xs text-slate-500">Estado: ${statusLabel(item.status || "pending")} · Ref: ${item.referral_code || "—"}</p>
        </article>
      `).join("")
      : '<p class="text-slate-500">Aún no tienes historial de referidos.</p>';
  }

  function renderAssociateNetworkTrace(items) {
    const target = document.getElementById("associateNetworkTraceList");
    if (!target) return;
    if (!Array.isArray(items) || items.length === 0) {
      target.innerHTML = '<p class="text-slate-500">Aún no hay relaciones de red registradas.</p>';
      return;
    }
    target.innerHTML = items.slice(0, 20).map((item) => `
      <article class="rounded-xl border border-slate-200 bg-slate-50 p-3">
        <p class="text-sm font-bold text-slate-800">${item.inviter_name || "Asociado"} → ${item.referred_name || "Referido"}</p>
        <p class="text-xs text-slate-500">Registro #${item.registration_id} · Ref: ${item.referral_code || "—"} · Estado: ${statusLabel(item.status || "pending")}</p>
      </article>
    `).join("");
  }

  function renderAdminNetworkTrace(items) {
    const target = document.getElementById("adminNetworkTraceList");
    if (!target) return;
    if (!Array.isArray(items) || items.length === 0) {
      target.innerHTML = '<p class="text-slate-500">Sin trazabilidad disponible.</p>';
      return;
    }
    target.innerHTML = items.slice(0, 30).map((item) => `
      <article class="rounded-xl border border-slate-200 bg-slate-50 p-3">
        <p class="text-sm font-bold text-slate-800">${item.inviter_name || "Usuario"} (${item.inviter_role || "sin rol"}) → ${item.referred_name || "Referido"}</p>
        <p class="text-xs text-slate-500">Registro #${item.registration_id} · Ref: ${item.referral_code || "—"} · Estado: ${statusLabel(item.status || "pending")}</p>
      </article>
    `).join("");
  }

  async function loadNetworkTrace() {
    const role = document.body.getAttribute("data-active-role");
    try {
      if (role === "associate") {
        const result = await window.appApiFetch("/api/associate/network-trace");
        renderAssociateNetworkTrace(result.items || []);
      }
      if (role === "admin") {
        const result = await window.appApiFetch("/api/admin/network-trace");
        renderAdminNetworkTrace(result.items || []);
      }
    } catch (_error) {
      renderAssociateNetworkTrace([]);
      renderAdminNetworkTrace([]);
    }
  }

  function renderUserPaymentStatus(items) {
    const target = document.getElementById("userPaymentStatus");
    if (!target) return;

    const list = Array.isArray(items) ? items : [];
    const active = list[0] || null;
    if (!active) {
      target.innerHTML = `
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
          <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Estado de Pago</p>
          <h3 class="text-3xl font-extrabold text-slate-900 mb-4">Sin registro</h3>
          <p class="text-sm text-slate-600">Aún no encontramos inscripciones activas.</p>
        </div>
      `;
      return;
    }

    const paymentStatus = active.admin_status || active.status || "pending";
    const progress = Number(active.attendance_percent || 0);
    const originReferral = active.referral_code || "Registro directo";
    const benefits = active.benefits || {};

    target.innerHTML = `
      <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Estado de Pago</p>
        <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${paymentStatus}</h3>
        <p class="text-sm text-slate-600">Inscripción: ${active.forum_slot || `Foro #${active.forum_id || "—"}`}</p>
      </div>
      <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Referido de Origen</p>
        <h3 class="text-2xl font-extrabold text-teal-600 mb-4">${originReferral}</h3>
        <p class="text-sm text-slate-600">Código aplicado en tu registro.</p>
      </div>
      <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Progreso y Beneficios</p>
        <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${progress}%</h3>
        <div class="mt-4 w-full bg-slate-200 rounded-full h-2 mb-3">
          <div class="bg-teal-600 h-2 rounded-full" style="width: ${Math.max(0, Math.min(progress, 100))}%"></div>
        </div>
        <p class="text-xs text-slate-600">${benefits.ebooks_enabled ? "✅ eBooks habilitados" : "🔒 eBooks bloqueados"} · ${benefits.certificate_enabled ? "✅ Certificado habilitado" : "🔒 Certificado pendiente"}</p>
      </div>
    `;
  }

  async function loadAdminData() {
    if (document.body.getAttribute("data-active-role") !== "admin") return;
    try {
      const [registrations, associates] = await Promise.all([
        window.appApiFetch("/api/admin/registrations"),
        window.appApiFetch("/api/admin/associates")
      ]);
      renderAdminRegistrations(registrations.items || []);
      renderAdminAssociates(associates.items || []);
      renderAdminPayments(registrations.items || []);
      await loadNetworkTrace();
    } catch (_error) {
      // noop
    }
  }

  function renderUserEbooks(items) {
    const target = document.getElementById("userEbooksList");
    if (!target) return;
    if (!Array.isArray(items) || items.length === 0) {
      target.innerHTML = '<p class="text-xs text-slate-500">No hay ebooks publicados actualmente.</p>';
      return;
    }

    target.innerHTML = items.map((item) => {
      const canDownload = Boolean(item.has_access && item.download_url);
      const badge = canDownload
        ? '<span class="rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-bold text-emerald-700">Acceso habilitado</span>'
        : '<span class="rounded-full bg-rose-100 px-2 py-1 text-[11px] font-bold text-rose-700">Sin acceso</span>';
      const action = canDownload
        ? `<a href="${item.download_url}" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-bold text-white hover:bg-sky-700">Descargar</a>`
        : '<span class="text-[11px] font-semibold text-slate-500">Cumple aprobación o asistencia mínima para habilitar.</span>';

      return `
      <article class="rounded-xl border border-sky-100 bg-sky-50/40 p-3">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <p class="font-bold text-slate-800">${item.title}</p>
            <p class="text-xs text-slate-600">${item.description || "Sin descripción."}</p>
            <p class="text-[11px] text-slate-500 mt-1">${item.access_reason || ""}</p>
          </div>
          <div class="flex flex-col items-start gap-2 md:items-end">
            ${badge}
            ${action}
          </div>
        </div>
      </article>`;
    }).join("");
  }

  function showUserEbooksAlert(message) {
    const alert = document.getElementById("userEbooksAlert");
    if (!alert) return;
    if (!message) {
      alert.classList.add("hidden");
      alert.textContent = "";
      return;
    }
    alert.classList.remove("hidden");
    alert.textContent = message;
  }

  async function loadUserEbooks() {
    const role = document.body.getAttribute("data-active-role");
    if (role !== "user" && role !== "admin" && role !== "associate") return;

    try {
      showUserEbooksAlert("");
      const result = await window.appApiFetch("/api/user/ebooks");
      renderUserEbooks(result.items || []);
    } catch (error) {
      renderUserEbooks([]);
      showUserEbooksAlert(error instanceof Error ? error.message : "No se pudo cargar el catálogo de ebooks.");
    }
  }

  function setupAssociateRegistrationActions() {
    const list = document.getElementById("associateRegistrationsList");
    const paymentsList = document.getElementById("associatePaymentsContainer");
    const refresh = document.getElementById("refreshAssociateRegistrations");
    const refreshPayments = document.getElementById("refreshAssociatePayments");
    const filter = document.getElementById("associateRegistrationsFilter");

    refresh?.addEventListener("click", () => loadAssociateRegistrations());
    refreshPayments?.addEventListener("click", () => loadAssociateRegistrations());
    filter?.addEventListener("change", () => loadAssociateRegistrations());

    const handleAssociateReviewClick = async (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;

      const isApprove = target.dataset.action === "associate-approve";
      const isReject = target.dataset.action === "associate-reject";
      if (!isApprove && !isReject) return;

      const registrationId = Number(target.dataset.id || 0);
      if (!registrationId) return;

      const nextStatus = isApprove ? "approved" : "rejected";
      const note = window.prompt(
        isReject
          ? "Ingresa una nota obligatoria para rechazar la inscripción:"
          : "Ingresa una nota opcional para aprobar la inscripción:"
      ) || "";
      if (isReject && !note.trim()) {
        window.alert("La nota es obligatoria para rechazar.");
        return;
      }
      await window.appApiFetch("/api/associate/registrations", {
        method: "PATCH",
        body: JSON.stringify({ registrationId, status: nextStatus, note })
      });
      loadAssociateRegistrations();
      loadAdminData();
      loadNetworkTrace();
    };

    list?.addEventListener("click", handleAssociateReviewClick);
    paymentsList?.addEventListener("click", handleAssociateReviewClick);
  }

  function setupAdminActions() {
    const regList = document.getElementById("adminRegistrationsList");
    const refreshRegs = document.getElementById("refreshAdminRegistrations");
    const refreshAssoc = document.getElementById("refreshAdminAssociates");

    refreshRegs?.addEventListener("click", () => loadAdminData());
    refreshAssoc?.addEventListener("click", () => loadAdminData());

    regList?.addEventListener("change", async (event) => {
      const target = event.target;
      if (!(target instanceof HTMLSelectElement) || target.dataset.action !== "status") return;
      const nextStatus = String(target.value || "");
      const note = window.prompt(
        nextStatus === "rejected"
          ? "Ingresa una nota obligatoria para rechazar:"
          : "Ingresa una nota opcional para este cambio:"
      ) || "";
      if (nextStatus === "rejected" && !note.trim()) {
        window.alert("La nota es obligatoria para rechazar.");
        loadAdminData();
        return;
      }
      await window.appApiFetch("/api/admin/registrations", {
        method: "PATCH",
        body: JSON.stringify({ registrationId: Number(target.dataset.id || 0), status: nextStatus, note })
      });
      loadAdminData();
    });

    regList?.addEventListener("click", async (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement) || target.dataset.action !== "delete") return;
      await window.appApiFetch(`/api/admin/registrations?id=${encodeURIComponent(target.dataset.id || '')}`, { method: "DELETE" });
      loadAdminData();
      window.refreshDashboardSummary();
    });
  }


  function renderUserBenefits(items) {
    const card = document.getElementById("userBenefitsList");
    const summary = document.getElementById("userBenefitsSummary");
    if (!card || !summary) return;

    if (!Array.isArray(items) || items.length === 0) {
      summary.textContent = "No encontramos inscripciones asociadas a tu usuario.";
      card.innerHTML = '<p class="text-xs text-slate-500">Cuando tengas una inscripción validada verás beneficios habilitados aquí.</p>';
      return;
    }

    summary.textContent = "Beneficios habilitados según estado administrativo aprobado y asistencia registrada por sesión.";
    card.innerHTML = items.map((item) => {
      const ebooks = item.benefits?.ebooks_enabled;
      const cert = item.benefits?.certificate_enabled;
      return `
        <article class="rounded-2xl border border-sky-200 bg-white p-4">
          <p class="font-bold text-slate-800">${item.forum_slot || `Foro #${item.forum_id}`}</p>
          <p class="text-xs text-slate-500">Estado admin: <span class="font-bold">${item.admin_status}</span> · Asistencia: ${item.attendance_percent}% (${item.sessions_with_attendance}/${item.sessions_total} sesiones)</p>
          <ul class="mt-2 text-xs space-y-1">
            <li class="${ebooks ? "text-emerald-700" : "text-slate-500"}">${ebooks ? "✅" : "🔒"} eBooks ${ebooks ? "habilitados" : "bloqueados (requiere aprobación)"}</li>
            <li class="${cert ? "text-emerald-700" : "text-slate-500"}">${cert ? "✅" : "🔒"} Certificado ${cert ? "habilitado" : "bloqueado (requiere aprobación + 75% asistencia)"}</li>
          </ul>
        </article>
      `;
    }).join("");
  }

  async function loadUserBenefits() {
    if (document.body.getAttribute("data-active-role") !== "user") return;
    try {
      const result = await window.appApiFetch("/api/registrations/me");
      renderUserBenefits(result.items || []);
      renderUserPaymentStatus(result.items || []);
    } catch (_error) {
      renderUserBenefits([]);
      renderUserPaymentStatus([]);
    }
  }

  function setupRegistrationForm() {
    const form = document.getElementById("registerForm");
    const signatureCanvas = document.getElementById("signatureCanvas");
    const clearBtn = document.getElementById("clearSignatureBtn");
    if (!form || !signatureCanvas || !clearBtn) return;

    setSignatureCanvasSize(signatureCanvas);
    window.addEventListener("resize", () => setSignatureCanvasSize(signatureCanvas));

    const startSignature = (event) => {
      signatureIsDrawing = true;
      const point = getCanvasPoint(signatureCanvas, event);
      signatureCtx.beginPath();
      signatureCtx.moveTo(point.x, point.y);
    };
    const drawSignature = (event) => {
      if (!signatureIsDrawing) return;
      event.preventDefault();
      const point = getCanvasPoint(signatureCanvas, event);
      signatureCtx.lineTo(point.x, point.y);
      signatureCtx.stroke();
    };
    const stopSignature = () => {
      signatureIsDrawing = false;
    };

    signatureCanvas.addEventListener("mousedown", startSignature);
    signatureCanvas.addEventListener("mousemove", drawSignature);
    window.addEventListener("mouseup", stopSignature);
    signatureCanvas.addEventListener("touchstart", startSignature, { passive: true });
    signatureCanvas.addEventListener("touchmove", drawSignature, { passive: false });
    window.addEventListener("touchend", stopSignature);
    clearBtn.addEventListener("click", () => setSignatureCanvasSize(signatureCanvas));

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      showRegisterFeedback("error", "");
      document.getElementById("registerFormAlert")?.classList.add("hidden");

      const formData = new FormData(form);
      const needsCert = formData.get("certif") === "yes";
      const proofFile = formData.get("paymentProof");
      const accepted = Boolean(formData.get("acceptanceCheck"));

      if (!accepted) {
        showRegisterFeedback("error", "Debes aceptar el compromiso para continuar con la inscripción.");
        return;
      }
      if (isCanvasBlank(signatureCanvas)) {
        showRegisterFeedback("error", "La firma digital es obligatoria para confirmar la inscripción.");
        return;
      }
      if (needsCert && (!(proofFile instanceof File) || proofFile.size === 0)) {
        showRegisterFeedback("error", "Si solicitas certificación, debes adjuntar el comprobante de pago.");
        return;
      }

      try {
        let paymentProofBase64 = null;
        let paymentProofName = null;
        let paymentProofMime = null;
        let paymentProofSize = null;
        if (proofFile instanceof File && proofFile.size > 0) {
          const fileAsBase64 = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
              const value = String(reader.result || "");
              resolve(value.includes(",") ? value.split(",")[1] : value);
            };
            reader.onerror = reject;
            reader.readAsDataURL(proofFile);
          });
          paymentProofBase64 = fileAsBase64;
          paymentProofName = proofFile.name;
          paymentProofMime = proofFile.type || "application/octet-stream";
          paymentProofSize = proofFile.size;
        }

        await window.appApiFetch("/api/registrations/create", {
          method: "POST",
          body: JSON.stringify({
            email: String(formData.get("email") || ""),
            password: String(formData.get("password") || ""),
            forumId: Number(formData.get("forumId") || 0),
            forumSlot: String((document.getElementById("forumIdSelect")?.selectedOptions?.[0]?.textContent || "").trim()),
            fullName: String(formData.get("fullName") || ""),
            documentId: String(formData.get("documentId") || ""),
            referralCode: String(formData.get("referralCode") || ""),
            needsCert,
            acceptanceChecked: true,
            signatureDataUrl: signatureCanvas.toDataURL("image/png"),
            paymentProof: paymentProofBase64 ? {
              name: paymentProofName,
              mime: paymentProofMime,
              size: paymentProofSize,
              base64: paymentProofBase64
            } : null
          })
        });
        showRegisterFeedback("success", "Inscripción registrada con éxito. Tu cupo quedó guardado correctamente.");
        resetRegisterForm();
        applyReferralOfferUI(activeReferralOffer);
        window.refreshDashboardSummary();
        loadAdminData();
      } catch (_error) {
        showRegisterFeedback("error", "No pudimos persistir la inscripción en este momento. Intenta nuevamente.");
      }
    });
  }

  function setupPaymentMethodsForm() {
    const form = document.getElementById("paymentMethodsForm");
    const status = document.getElementById("paymentMethodsStatus");
    if (!form || !status) return;

    // Load existing payment methods if available
    window.appApiFetch("/api/associate/payment-methods")
      .then(result => {
        if (result.data) {
          form.bankName.value = result.data.bankName || "";
          form.accountHolder.value = result.data.accountHolder || "";
          form.accountNumber.value = result.data.accountNumber || "";
          form.accountType.value = result.data.accountType || "";
          form.currency.value = result.data.currency || "ARS";
          form.aliasOrReference.value = result.data.aliasOrReference || "";
        }
      })
      .catch(() => {
        // Silently fail if not an associate
      });

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      try {
        status.textContent = "Guardando...";
        await window.appApiFetch("/api/associate/payment-methods", {
          method: "POST",
          body: JSON.stringify({
            bankName: String(form.bankName.value || ""),
            accountHolder: String(form.accountHolder.value || ""),
            accountNumber: String(form.accountNumber.value || ""),
            accountType: String(form.accountType.value || ""),
            currency: String(form.currency.value || "ARS"),
            aliasOrReference: String(form.aliasOrReference.value || ""),
          })
        });
        status.textContent = "✓ Datos guardados exitosamente";
        status.classList.remove("text-slate-600");
        status.classList.add("text-emerald-600", "font-bold");
      } catch (error) {
        status.textContent = "✗ Error al guardar: " + (error instanceof Error ? error.message : "Error desconocido");
        status.classList.remove("text-slate-600");
        status.classList.add("text-rose-600", "font-bold");
      }
    });
  }

  async function loadReferralLink() {
    const referralInput = document.getElementById("myReferralCode");
    if (!referralInput) return;

    try {
      const country = encodeURIComponent(activeRegistrationCountry || "AR");
      const result = await window.appApiFetch(`/api/associate/referral-link?country=${country}`);
      if (result.referralLink) {
        referralInput.value = result.referralLink;
      }
    } catch (_error) {
      // Silently fail if not an associate
    }
  }

  function setupUserEbooksActions() {
    // Setup user ebooks actions (placeholder for future ebook functionality)
    const ebooksContainer = document.getElementById("userEbooksList");
    if (!ebooksContainer) return;
    // Actions will be set up when ebooks are loaded
  }

  window.addEventListener("DOMContentLoaded", async () => {
    setupRegistrationCountrySelector();
    setupRegistrationForm();
    setupAssociateOfferForm();
    setupPaymentMethodsForm();
    setupAssociateRegistrationActions();
    setupAdminActions();
    setupUserEbooksActions();
    window.toggleCertFields(false);
    await loadOfferFromReferralCode(activeRegistrationCountry);
    await loadAssociateOffer();
    await loadReferralLink();
    await loadAssociateRegistrations();
    await loadNetworkTrace();
    await window.refreshDashboardSummary();
    await loadAdminData();
    await loadUserEbooks();
    await loadUserBenefits();
  });

  window.addEventListener("app:role-changed", async () => {
    await loadAssociateOffer();
    await loadAssociateRegistrations();
    await loadAdminData();
    await loadNetworkTrace();
    await loadUserBenefits();
  });
})();
