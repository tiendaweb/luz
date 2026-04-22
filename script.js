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
        initial.parentElement.className = "w-12 h-12 bg-teal-600 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      } else if (role === "associate") {
        initial.innerText = "A";
        nameDisp.innerText = "Coordinador Red";
        initial.parentElement.className = "w-12 h-12 bg-purple-600 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      } else {
        initial.innerText = "U";
        nameDisp.innerText = "Inscripto Foro";
        initial.parentElement.className = "w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      }
    }

    if (redirectToDashboard) {
      window.showView("dashboard");
    }

    return role;
  }

  window.showView = (viewId) => {
    const view = normalizeView(viewId);
    document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
    document.getElementById(`view-${view}`)?.classList.add("active");
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
    firstBtn?.classList.add("bg-teal-50", "text-teal-700");
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
    window.toggleCertFields(false);
    refreshDashboardSummary();
  });
})();
