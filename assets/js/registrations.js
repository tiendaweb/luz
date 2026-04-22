(() => {
  let signatureCtx = null;
  let signatureIsDrawing = false;
  let activeReferralOffer = null;

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
    notice.textContent = `Inscripción con referido de ${offer.associateName}. Precio aplicado: ${offer.priceAmount} ${offer.currencyCode}. Método: ${offer.paymentMethod}.`;
    paymentInstructions.innerHTML = `<i class="fa-solid fa-link mr-2"></i> Pago con referido (${offer.referralCode}): ${offer.paymentMethod}.`;
    paymentLink.href = offer.paymentLink;
    paymentLink.textContent = `Pagar ${offer.priceAmount} ${offer.currencyCode}`;
  }

  async function loadOfferFromReferralCode() {
    const params = new URLSearchParams(window.location.search);
    const code = (params.get("ref") || "").trim();
    if (!code) {
      applyReferralOfferUI(null);
      return;
    }

    try {
      const result = await window.appApiFetch(`/api/referrals/offer.php?code=${encodeURIComponent(code)}`);
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
      const { summary } = await window.appApiFetch("/api/dashboard/summary.php");
      const statNodes = document.querySelectorAll("#view-dashboard .grid > div h4");
      if (statNodes[0]) statNodes[0].textContent = String(summary.registrations_total ?? 0);
      if (statNodes[1]) statNodes[1].textContent = String(summary.cert_requests_total ?? 0);
      if (statNodes[2]) statNodes[2].textContent = String(summary.users_total ?? 0);
    } catch (_error) {
      // silencioso: mantenemos datos mock si no hay backend disponible
    }
  };

  async function loadAssociateOffer() {
    const form = document.getElementById("associateOfferForm");
    if (!form || document.body.getAttribute("data-active-role") !== "associate") return;

    try {
      const result = await window.appApiFetch("/api/associate/offer.php");
      const offer = result.offer;
      if (!offer) return;
      form.referralCode.value = offer.referralCode;
      form.currencyCode.value = offer.currencyCode;
      form.priceAmount.value = offer.priceAmount;
      form.paymentMethod.value = offer.paymentMethod;
      form.paymentLink.value = offer.paymentLink;
      const preview = document.getElementById("associateReferralPreview");
      if (preview) preview.textContent = `${window.location.origin}/index.php?ref=${offer.referralCode}`;
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
        await window.appApiFetch("/api/associate/offer.php", { method: "POST", body: JSON.stringify(payload) });
        status.textContent = "Configuración guardada.";
        preview.textContent = `${window.location.origin}/index.php?ref=${payload.referralCode}`;
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
          </div>
          <div class="flex items-center gap-2">
            <select data-action="status" data-id="${item.id}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
              <option value="pending" ${item.status === 'pending' ? 'selected' : ''}>Pendiente</option>
              <option value="approved" ${item.status === 'approved' ? 'selected' : ''}>Aprobada</option>
              <option value="rejected" ${item.status === 'rejected' ? 'selected' : ''}>Rechazada</option>
            </select>
            <button data-action="delete" data-id="${item.id}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-bold text-white">Eliminar</button>
          </div>
        </div>
      </article>
    `).join("");
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
          </div>
          <div class="flex items-center gap-2">
            <button data-action="associate-approve" data-id="${item.id}" class="rounded-lg bg-emerald-600 px-2 py-1 text-xs font-bold text-white">Aprobar</button>
            <button data-action="associate-reject" data-id="${item.id}" class="rounded-lg bg-rose-600 px-2 py-1 text-xs font-bold text-white">Rechazar</button>
          </div>
        </div>
      </article>
    `).join("");
  }

  async function loadAssociateRegistrations() {
    if (document.body.getAttribute("data-active-role") !== "associate") return;
    const filter = document.getElementById("associateRegistrationsFilter");
    const status = filter ? String(filter.value || "all") : "all";
    const query = status === "all" ? "" : `?status=${encodeURIComponent(status)}`;

    try {
      const result = await window.appApiFetch(`/api/associate/registrations.php${query}`);
      renderAssociateRegistrations(result.items || []);
    } catch (_error) {
      renderAssociateRegistrations([]);
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

  async function loadAdminData() {
    if (document.body.getAttribute("data-active-role") !== "admin") return;
    try {
      const [registrations, associates] = await Promise.all([
        window.appApiFetch("/api/admin/registrations.php"),
        window.appApiFetch("/api/admin/associates.php")
      ]);
      renderAdminRegistrations(registrations.items || []);
      renderAdminAssociates(associates.items || []);
    } catch (_error) {
      // noop
    }
  }

  function setupAssociateRegistrationActions() {
    const list = document.getElementById("associateRegistrationsList");
    const refresh = document.getElementById("refreshAssociateRegistrations");
    const filter = document.getElementById("associateRegistrationsFilter");

    refresh?.addEventListener("click", () => loadAssociateRegistrations());
    filter?.addEventListener("change", () => loadAssociateRegistrations());

    list?.addEventListener("click", async (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;

      const isApprove = target.dataset.action === "associate-approve";
      const isReject = target.dataset.action === "associate-reject";
      if (!isApprove && !isReject) return;

      const registrationId = Number(target.dataset.id || 0);
      if (!registrationId) return;

      const nextStatus = isApprove ? "approved" : "rejected";
      await window.appApiFetch("/api/associate/registrations.php", {
        method: "PATCH",
        body: JSON.stringify({ registrationId, status: nextStatus })
      });
      loadAssociateRegistrations();
      loadAdminData();
    });
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
      await window.appApiFetch("/api/admin/registrations.php", {
        method: "PATCH",
        body: JSON.stringify({ registrationId: Number(target.dataset.id || 0), status: target.value })
      });
      loadAdminData();
    });

    regList?.addEventListener("click", async (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement) || target.dataset.action !== "delete") return;
      await window.appApiFetch(`/api/admin/registrations.php?id=${encodeURIComponent(target.dataset.id || '')}`, { method: "DELETE" });
      loadAdminData();
      window.refreshDashboardSummary();
    });
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

        await window.appApiFetch("/api/registrations/create.php", {
          method: "POST",
          body: JSON.stringify({
            forumSlot: String(formData.get("forumSlot") || ""),
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

  window.addEventListener("DOMContentLoaded", async () => {
    setupRegistrationForm();
    setupAssociateOfferForm();
    setupAssociateRegistrationActions();
    setupAdminActions();
    window.toggleCertFields(false);
    await loadOfferFromReferralCode();
    await loadAssociateOffer();
    await loadAssociateRegistrations();
    await window.refreshDashboardSummary();
    await loadAdminData();
  });

  window.addEventListener("app:role-changed", async () => {
    await loadAssociateOffer();
    await loadAssociateRegistrations();
    await loadAdminData();
  });
})();
