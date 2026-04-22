(() => {
  let signatureCtx = null;
  let signatureIsDrawing = false;

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
      const statNodes = document.querySelectorAll("#view-dashboard .grid > article h4");
      if (statNodes[0]) statNodes[0].textContent = String(summary.registrations_total ?? 0);
      if (statNodes[1]) statNodes[1].textContent = String(summary.cert_requests_total ?? 0);
      if (statNodes[2]) statNodes[2].textContent = String(summary.users_total ?? 0);
      if (statNodes[3]) statNodes[3].textContent = String(summary.messages_total ?? 0);
    } catch (_error) {
      // silencioso: mantenemos datos mock si no hay backend disponible
    }
  };

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
        window.refreshDashboardSummary();
      } catch (_error) {
        showRegisterFeedback("error", "No pudimos persistir la inscripción en este momento. Intenta nuevamente.");
      }
    });
  }

  window.addEventListener("DOMContentLoaded", () => {
    setupRegistrationForm();
    window.toggleCertFields(false);
    window.refreshDashboardSummary();
  });
})();
