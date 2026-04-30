(() => {
  const validViews = new Set(["home", "forums", "about", "blog", "dashboard"]);
  const validRoles = new Set(["guest", "user", "associate", "admin"]);

  const normalizeView = (viewId) => (validViews.has(viewId) ? viewId : "home");
  const normalizeRole = (roleName) => (validRoles.has(roleName) ? roleName : "guest");
  let signatureCtx = null;
  let signatureIsDrawing = false;

  async function apiFetch(url, options = {}) {
    const normalizedUrl = url.startsWith("/api/") ? url : `/api/${url.replace(/^\/+/, "")}`;
    const fallbackUrl = normalizedUrl.replace(/^\/api\//, "/public/api/");

    const requestConfig = {
      credentials: "same-origin",
      headers: { "Content-Type": "application/json", ...(options.headers || {}) },
      ...options
    };

    const doRequest = async (requestUrl) => {
      const response = await fetch(requestUrl, requestConfig);
      const data = await response.json().catch(() => ({}));
      return { response, data };
    };

    let result = await doRequest(normalizedUrl);
    if (result.response.status === 404 && fallbackUrl !== normalizedUrl) {
      result = await doRequest(fallbackUrl);
    }
    if (!result.response.ok || result.data.ok === false) {
      throw new Error(result.data.error || "Error de comunicación con el servidor.");
    }
    return result.data;
  }

  async function refreshDashboardSummary() {
    try {
      const { summary } = await apiFetch("/api/dashboard/summary.php");
      const statNodes = document.querySelectorAll("#view-dashboard .grid > article h4");
      if (statNodes[0]) statNodes[0].textContent = String(summary.registrations_total ?? 0);
      if (statNodes[1]) statNodes[1].textContent = String(summary.cert_requests_total ?? 0);
      if (statNodes[2]) statNodes[2].textContent = String(summary.users_total ?? 0);
      if (statNodes[3]) statNodes[3].textContent = String(summary.messages_total ?? 0);
    } catch (_error) {
      // silencioso: mantenemos datos mock si no hay backend disponible
    }
  }

  function showRegisterFeedback(type, message) {
    const box = document.getElementById("registerFormAlert");
    if (!box) return;
    box.classList.remove("hidden", "bg-amber-50", "text-amber-800", "border", "border-amber-200", "bg-rose-50", "text-rose-700", "border-rose-200");
    if (type === "success") {
      box.classList.add("bg-amber-50", "text-amber-800", "border", "border-amber-200");
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

  function resetRegisterForm() {
    const form = document.getElementById("registerForm");
    const canvas = document.getElementById("signatureCanvas");
    form?.reset();
    window.toggleCertFields(false);
    if (canvas) setSignatureCanvasSize(canvas);
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

        await apiFetch("/api/registrations/create.php", {
          method: "POST",
          body: JSON.stringify({
            forumSlot: String(formData.get("forumSlot") || ""),
            fullName: String(formData.get("fullName") || ""),
            documentId: String(formData.get("documentId") || ""),
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
        refreshDashboardSummary();
      } catch (_error) {
        showRegisterFeedback("error", "No pudimos persistir la inscripción en este momento. Intenta nuevamente.");
      }
    });
  }

  function closeMobileMenu() {
    document.getElementById("mobileMenu")?.classList.add("hidden");
  }

  function parseHashState() {
    const rawHash = window.location.hash.replace("#", "");
    const [rawView, query = ""] = rawHash.split("?");
    const view = rawView.startsWith("view-") ? rawView.replace("view-", "") : "home";
    const role = normalizeRole(new URLSearchParams(query).get("role") || "guest");
    return { view: normalizeView(view), role };
  }

  function updateHash(viewId) {
    const role = normalizeRole(document.body.getAttribute("data-active-role") || "guest");
    const query = role === "guest" ? "" : `?role=${role}`;
    const nextHash = `#view-${normalizeView(viewId)}${query}`;
    if (window.location.hash !== nextHash) {
      window.location.hash = nextHash;
    }
  }

  function formatBlogDate(dateValue) {
    if (!dateValue) return "Sin fecha";
    const date = new Date(dateValue);
    if (Number.isNaN(date.getTime())) return "Sin fecha";
    return date.toLocaleDateString("es-AR", { year: "numeric", month: "long", day: "numeric" });
  }

  function renderPublicBlogPosts(items) {
    const target = document.getElementById("blogPublicList");
    if (!target) return;

    if (!Array.isArray(items) || items.length === 0) {
      target.innerHTML = `
        <article class="bg-white rounded-3xl border border-slate-100 shadow-lg p-8 card-shadow md:col-span-2 lg:col-span-3">
          <span class="inline-block text-xs font-extrabold uppercase tracking-widest text-slate-500 mb-4">Blog</span>
          <h4 class="text-2xl font-bold mb-4">Aún no hay artículos publicados</h4>
          <p class="text-slate-600">Próximamente publicaremos nuevos materiales sobre la causa PSME.</p>
        </article>`;
      return;
    }

    target.innerHTML = items.map((item) => `
      <article class="bg-white rounded-3xl border border-slate-100 shadow-lg p-8 card-shadow">
        <span class="inline-block text-xs font-extrabold uppercase tracking-widest text-amber-700 mb-4">${formatBlogDate(item.published_at)}</span>
        <h4 class="text-2xl font-bold mb-4">${item.title || "Sin título"}</h4>
        <p class="text-slate-600">${item.excerpt || ""}</p>
      </article>`).join("");
  }

  async function loadPublicBlogPosts() {
    try {
      const result = await apiFetch("/api/blog/list.php");
      renderPublicBlogPosts(result.items || []);
    } catch (_error) {
      renderPublicBlogPosts([]);
    }
  }

  function showAdminBlogFeedback(type, message) {
    const box = document.getElementById("adminBlogFeedback");
    if (!box) return;
    if (!message) {
      box.classList.add("hidden");
      box.textContent = "";
      return;
    }

    box.classList.remove("hidden", "bg-amber-50", "text-amber-800", "border-amber-200", "bg-rose-50", "text-rose-700", "border-rose-200", "border");
    box.classList.add("border");
    if (type === "success") {
      box.classList.add("bg-amber-50", "text-amber-800", "border-amber-200");
    } else {
      box.classList.add("bg-rose-50", "text-rose-700", "border-rose-200");
    }
    box.textContent = message;
  }

  function resetAdminBlogForm() {
    const id = document.getElementById("adminBlogId");
    const slug = document.getElementById("adminBlogSlug");
    const title = document.getElementById("adminBlogTitle");
    const excerpt = document.getElementById("adminBlogExcerpt");
    const content = document.getElementById("adminBlogContent");
    const status = document.getElementById("adminBlogStatus");
    if (id) id.value = "";
    if (slug) slug.value = "";
    if (title) title.value = "";
    if (excerpt) excerpt.value = "";
    if (content) content.value = "";
    if (status) status.value = "draft";
  }

  function fillAdminBlogForm(item) {
    const id = document.getElementById("adminBlogId");
    const slug = document.getElementById("adminBlogSlug");
    const title = document.getElementById("adminBlogTitle");
    const excerpt = document.getElementById("adminBlogExcerpt");
    const content = document.getElementById("adminBlogContent");
    const status = document.getElementById("adminBlogStatus");
    if (id) id.value = String(item.id || "");
    if (slug) slug.value = item.slug || "";
    if (title) title.value = item.title || "";
    if (excerpt) excerpt.value = item.excerpt || "";
    if (content) content.value = item.content_html || "";
    if (status) status.value = item.status || "draft";
  }

  function renderAdminBlogPosts(items) {
    const target = document.getElementById("adminBlogList");
    if (!target) return;
    if (!Array.isArray(items) || items.length === 0) {
      target.innerHTML = '<p class="text-xs text-slate-500">No hay artículos cargados.</p>';
      return;
    }

    target.innerHTML = items.map((item) => `
      <article class="rounded-xl border border-slate-200 bg-slate-50 p-3">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="font-bold text-slate-800">${item.title}</p>
            <p class="text-xs text-slate-500">/${item.slug} · ${item.status} · ${formatBlogDate(item.published_at)}</p>
          </div>
          <div class="flex gap-2">
            <button data-action="edit-blog" data-id="${item.id}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-bold">Editar</button>
            <button data-action="delete-blog" data-id="${item.id}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-bold text-white">Eliminar</button>
          </div>
        </div>
      </article>`).join("");
  }

  async function loadAdminBlogPosts() {
    if ((document.body.getAttribute("data-active-role") || "") !== "admin") return;
    try {
      const result = await apiFetch("/api/admin/blog/list.php");
      const items = result.items || [];
      renderAdminBlogPosts(items);
      window.__adminBlogItems = items;
    } catch (_error) {
      renderAdminBlogPosts([]);
    }
  }

  function setupAdminBlogActions() {
    const form = document.getElementById("adminBlogForm");
    const refresh = document.getElementById("adminBlogRefreshBtn");
    const clear = document.getElementById("adminBlogClearBtn");
    const list = document.getElementById("adminBlogList");

    refresh?.addEventListener("click", async () => {
      showAdminBlogFeedback("success", "");
      await loadAdminBlogPosts();
    });

    clear?.addEventListener("click", () => {
      showAdminBlogFeedback("success", "");
      resetAdminBlogForm();
    });

    form?.addEventListener("submit", async (event) => {
      event.preventDefault();
      const payload = {
        id: Number(document.getElementById("adminBlogId")?.value || 0),
        slug: String(document.getElementById("adminBlogSlug")?.value || "").trim(),
        title: String(document.getElementById("adminBlogTitle")?.value || "").trim(),
        excerpt: String(document.getElementById("adminBlogExcerpt")?.value || "").trim(),
        content_html: String(document.getElementById("adminBlogContent")?.value || "").trim(),
        status: String(document.getElementById("adminBlogStatus")?.value || "draft").trim()
      };

      try {
        if (payload.id > 0) {
          await apiFetch("/api/admin/blog/update.php", { method: "PATCH", body: JSON.stringify(payload) });
          showAdminBlogFeedback("success", "Artículo actualizado correctamente.");
        } else {
          await apiFetch("/api/admin/blog/create.php", { method: "POST", body: JSON.stringify(payload) });
          showAdminBlogFeedback("success", "Artículo creado correctamente.");
        }
        resetAdminBlogForm();
        await loadAdminBlogPosts();
        await loadPublicBlogPosts();
      } catch (error) {
        showAdminBlogFeedback("error", error instanceof Error ? error.message : "No se pudo guardar el artículo.");
      }
    });

    list?.addEventListener("click", async (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;
      const action = target.dataset.action || "";
      const id = Number(target.dataset.id || 0);
      if (!id) return;

      if (action === "edit-blog") {
        const items = Array.isArray(window.__adminBlogItems) ? window.__adminBlogItems : [];
        const item = items.find((entry) => Number(entry.id) === id);
        if (item) fillAdminBlogForm(item);
        return;
      }

      if (action === "delete-blog") {
        if (!window.confirm("¿Eliminar este artículo?")) return;
        try {
          await apiFetch(`/api/admin/blog/delete.php?id=${encodeURIComponent(String(id))}`, { method: "DELETE" });
          showAdminBlogFeedback("success", "Artículo eliminado.");
          await loadAdminBlogPosts();
          await loadPublicBlogPosts();
        } catch (error) {
          showAdminBlogFeedback("error", error instanceof Error ? error.message : "No se pudo eliminar.");
        }
      }
    });
  }

  function setBriefFeedback(type, message) {
    const box = document.getElementById("briefFeedback");
    if (!box) return;
    if (!message) {
      box.classList.add("hidden");
      box.textContent = "";
      return;
    }

    box.classList.remove("hidden", "bg-amber-50", "text-amber-800", "border-amber-200", "bg-rose-50", "text-rose-700", "border-rose-200", "border");
    box.classList.add("border");
    if (type === "success") {
      box.classList.add("bg-amber-50", "text-amber-800", "border-amber-200");
    } else {
      box.classList.add("bg-rose-50", "text-rose-700", "border-rose-200");
    }
    box.textContent = message;
  }

  function buildContentBriefPayload() {
    const form = document.getElementById("contentBriefForm");
    if (!(form instanceof HTMLFormElement)) return null;
    const fd = new FormData(form);
    return {
      objective: String(fd.get("objective") || "").trim(),
      audience: String(fd.get("audience") || "").trim(),
      tone: String(fd.get("tone") || "").trim(),
      channel: String(fd.get("channel") || "").trim(),
      length: String(fd.get("length") || "").trim(),
      cta: String(fd.get("cta") || "").trim(),
      keywords: String(fd.get("keywords") || "").trim(),
      legal: String(fd.get("legal") || "").trim()
    };
  }

  function buildPromptFromBrief(payload) {
    return [
      "Actúa como estratega de contenidos para PSME y redacta una primera versión lista para edición.",
      `Objetivo: ${payload.objective}`,
      `Audiencia: ${payload.audience}`,
      `Tono: ${payload.tone}`,
      `Canal: ${payload.channel}`,
      `Longitud objetivo: ${payload.length}`,
      `Llamado a la acción (CTA): ${payload.cta}`,
      `Keywords obligatorias: ${payload.keywords}`,
      `Restricciones legales/éticas: ${payload.legal}`,
      "Estructura esperada:",
      "1) Título sugerido.",
      "2) Bajada o resumen breve.",
      "3) Desarrollo en secciones con subtítulos.",
      "4) Cierre con CTA.",
      "5) Meta descripción SEO (máx. 155 caracteres)."
    ].join("\n");
  }

  function buildDraftFromBrief(payload) {
    return [
      `<h2>${payload.objective}</h2>`,
      `<p><strong>Audiencia:</strong> ${payload.audience}</p>`,
      `<p><strong>Tono sugerido:</strong> ${payload.tone}. <strong>Canal:</strong> ${payload.channel}. <strong>Longitud:</strong> ${payload.length}.</p>`,
      `<p>En este borrador base se abordarán los temas clave: ${payload.keywords}.</p>`,
      `<h3>Desarrollo sugerido</h3>`,
      `<p>Desarrollar aquí los argumentos principales respetando estas restricciones: ${payload.legal}.</p>`,
      `<p><strong>CTA:</strong> ${payload.cta}</p>`
    ].join("\n");
  }

  function fillBriefForm(payload) {
    const form = document.getElementById("contentBriefForm");
    if (!(form instanceof HTMLFormElement)) return;
    form.objective.value = payload.objective || "";
    form.audience.value = payload.audience || "";
    form.tone.value = payload.tone || "";
    form.channel.value = payload.channel || "";
    form.length.value = payload.length || "";
    form.cta.value = payload.cta || "";
    form.keywords.value = payload.keywords || "";
    form.legal.value = payload.legal || "";
  }

  function renderBriefPresetOptions(items) {
    const select = document.getElementById("briefPresetSelect");
    if (!(select instanceof HTMLSelectElement)) return;
    const options = ['<option value="">Seleccionar preset…</option>'];
    (Array.isArray(items) ? items : []).forEach((item) => {
      options.push(`<option value="${item.id}">${item.name}</option>`);
    });
    select.innerHTML = options.join("");
  }

  async function loadContentPromptTemplates() {
    if ((document.body.getAttribute("data-active-role") || "") !== "admin") return;
    try {
      const result = await apiFetch("/api/admin/content-prompts/list.php");
      window.__contentPromptTemplates = Array.isArray(result.items) ? result.items : [];
      renderBriefPresetOptions(window.__contentPromptTemplates);
    } catch (_error) {
      window.__contentPromptTemplates = [];
      renderBriefPresetOptions([]);
    }
  }

  async function setupContentBriefActions() {
    const copyBtn = document.getElementById("briefCopyPromptBtn");
    const output = document.getElementById("briefPromptOutput");
    const savePresetBtn = document.getElementById("briefSavePresetBtn");
    const loadPresetBtn = document.getElementById("briefLoadPresetBtn");
    const deletePresetBtn = document.getElementById("briefDeletePresetBtn");
    const generateDraftBtn = document.getElementById("briefGenerateDraftBtn");
    const presetNameInput = document.getElementById("briefPresetName");
    const presetSelect = document.getElementById("briefPresetSelect");

    if (!copyBtn || !output) return;

    copyBtn.addEventListener("click", async () => {
      const payload = buildContentBriefPayload();
      if (!payload) return;
      const prompt = buildPromptFromBrief(payload);
      output.value = prompt;
      try {
        await navigator.clipboard.writeText(prompt);
        setBriefFeedback("success", "Prompt copiado al portapapeles.");
      } catch (_error) {
        setBriefFeedback("error", "No se pudo copiar automáticamente. Copia manual desde la caja.");
      }
    });

    generateDraftBtn?.addEventListener("click", async () => {
      const payload = buildContentBriefPayload();
      if (!payload) return;
      const draft = buildDraftFromBrief(payload);
      const prompt = buildPromptFromBrief(payload);
      output.value = `${prompt}

--- BORRADOR BASE ---
${draft}`;

      const blogContent = document.getElementById("adminBlogContent");
      if (blogContent instanceof HTMLTextAreaElement && blogContent.value.trim() === "") {
        blogContent.value = draft;
      }

      const pageForm = document.getElementById("adminPageForm");
      if (pageForm instanceof HTMLFormElement && pageForm.content_html && !String(pageForm.content_html.value || "").trim()) {
        pageForm.content_html.value = draft;
      }

      try {
        await navigator.clipboard.writeText(draft);
        setBriefFeedback("success", "Borrador base generado. Se copió al portapapeles y se precargó en Blog/Páginas cuando aplica.");
      } catch (_error) {
        setBriefFeedback("success", "Borrador base generado. Copia manual desde la caja si lo necesitas.");
      }
    });

    savePresetBtn?.addEventListener("click", async () => {
      const payload = buildContentBriefPayload();
      const presetName = String((presetNameInput instanceof HTMLInputElement ? presetNameInput.value : "") || "").trim();
      if (!payload || !presetName) {
        setBriefFeedback("error", "Debes indicar un nombre para guardar el preset.");
        return;
      }

      try {
        await apiFetch("/api/admin/content-prompts/create.php", {
          method: "POST",
          body: JSON.stringify({ name: presetName, ...payload })
        });
        if (presetNameInput instanceof HTMLInputElement) presetNameInput.value = "";
        await loadContentPromptTemplates();
        setBriefFeedback("success", "Preset guardado correctamente.");
      } catch (error) {
        setBriefFeedback("error", error instanceof Error ? error.message : "No se pudo guardar el preset.");
      }
    });

    loadPresetBtn?.addEventListener("click", () => {
      const selectedId = Number((presetSelect instanceof HTMLSelectElement ? presetSelect.value : "") || 0);
      const items = Array.isArray(window.__contentPromptTemplates) ? window.__contentPromptTemplates : [];
      const selected = items.find((item) => Number(item.id) === selectedId);
      if (!selected) {
        setBriefFeedback("error", "Selecciona un preset para cargar.");
        return;
      }
      fillBriefForm(selected);
      setBriefFeedback("success", "Preset cargado en el brief.");
    });

    deletePresetBtn?.addEventListener("click", async () => {
      const selectedId = Number((presetSelect instanceof HTMLSelectElement ? presetSelect.value : "") || 0);
      if (!selectedId) {
        setBriefFeedback("error", "Selecciona un preset para eliminar.");
        return;
      }
      if (!window.confirm("¿Eliminar este preset?")) return;

      try {
        await apiFetch(`/api/admin/content-prompts/delete.php?id=${encodeURIComponent(String(selectedId))}`, { method: "DELETE" });
        await loadContentPromptTemplates();
        setBriefFeedback("success", "Preset eliminado.");
      } catch (error) {
        setBriefFeedback("error", error instanceof Error ? error.message : "No se pudo eliminar el preset.");
      }
    });

    await loadContentPromptTemplates();
  }

  function applyRoleUI(roleName, options = {}) {
    const role = normalizeRole(roleName);
    const { redirectToDashboard = false } = options;

    document.body.setAttribute("data-active-role", role);

    const userBtn = document.querySelector(".user-access-btn");
    const mobileUserBtn = document.querySelector(".mobile-user-access-btn");
    const badge = document.getElementById("userRoleBadge");
    const initial = document.getElementById("userInitial");
    const nameDisp = document.getElementById("userName");

    if (role === "guest") {
      userBtn?.classList.add("hidden");
      mobileUserBtn?.classList.add("hidden");

      if (window.location.hash.includes("view-dashboard")) {
        window.showView("home");
      }
      return role;
    }

    userBtn?.classList.remove("hidden");
    mobileUserBtn?.classList.remove("hidden");
    if (badge) badge.innerText = role.toUpperCase();

    if (initial && nameDisp && initial.parentElement) {
      if (role === "admin") {
        initial.innerText = "ML";
        nameDisp.innerText = "Luz Genovese";
        initial.parentElement.className = "w-12 h-12 bg-amber-700 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      } else if (role === "associate") {
        initial.innerText = "A";
        nameDisp.innerText = "Coordinador Red";
        initial.parentElement.className = "w-12 h-12 bg-amber-800 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      } else {
        initial.innerText = "U";
        nameDisp.innerText = "Inscripto Foro";
        initial.parentElement.className = "w-12 h-12 bg-amber-600 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      }
    }

    if (redirectToDashboard) {
      window.showView("dashboard");
    }

    window.dispatchEvent(new CustomEvent("app:role-changed", { detail: { role } }));
    return role;
  }

  window.showView = (viewId) => {
    const view = normalizeView(viewId);
    document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
    document.getElementById(`view-${view}`)?.classList.add("active");
    if (view === "blog") {
      loadPublicBlogPosts();
    }
    updateHash(view);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  window.setRole = async (roleName) => {
    const role = normalizeRole(roleName);
    try {
      const result = await apiFetch("/api/auth/login.php", {
        method: "POST",
        body: JSON.stringify({ role })
      });
      applyRoleUI(result.user?.role || role, { redirectToDashboard: true });
      refreshDashboardSummary();
    } catch (_error) {
      applyRoleUI(role, { redirectToDashboard: true });
    }
  };

  window.setDashTab = () => {
    const firstBtn = document.querySelector("#view-dashboard nav button");
    firstBtn?.classList.add("bg-amber-50", "text-amber-800");
  };

  window.toggleMobileMenu = () => document.getElementById("mobileMenu")?.classList.toggle("hidden");

  window.openModal = (id) => {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove("hidden");
    document.body.style.overflow = "hidden";
  };

  window.closeModal = (id) => {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add("hidden");
    document.body.style.overflow = "auto";
  };

  window.toggleFaq = (buttonElement) => {
    buttonElement?.nextElementSibling?.classList.toggle("hidden");
    buttonElement?.querySelector("i")?.classList.toggle("rotate-180");
  };

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

  window.logout = async () => {
    try {
      await apiFetch("/api/auth/logout.php", { method: "POST", body: JSON.stringify({}) });
    } catch (_error) {
      // seguimos con cierre local aun si backend falla
    }
    alert("Sesión cerrada");
    applyRoleUI("guest", { redirectToDashboard: false });
    window.showView("home");
  };

  window.addEventListener("hashchange", () => {
    const { view, role } = parseHashState();
    document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
    document.getElementById(`view-${view}`)?.classList.add("active");
    applyRoleUI(role, { redirectToDashboard: false });
    closeMobileMenu();
  });


  window.addEventListener("app:role-changed", () => {
    loadAdminBlogPosts();
    loadContentPromptTemplates();
  });

  window.addEventListener("DOMContentLoaded", async () => {
    const { view, role } = parseHashState();
    document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
    document.getElementById(`view-${view}`)?.classList.add("active");
    window.setDashTab("overview");
    try {
      const me = await apiFetch("/api/auth/me.php");
      const sessionRole = me.user?.role || role;
      applyRoleUI(sessionRole, { redirectToDashboard: false });
    } catch (_error) {
      applyRoleUI(role, { redirectToDashboard: false });
    }
    updateHash(view);
    setupRegistrationForm();
    setupAdminBlogActions();
    await setupContentBriefActions();
    window.toggleCertFields(false);
    refreshDashboardSummary();
    loadPublicBlogPosts();
    loadAdminBlogPosts();
  });
})();
