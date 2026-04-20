const scheduleByDate = {
  "2026-05-04": [
    { segmento: "Profesionales", zona: "Ecuador/Bolivia", horario: "21:00–22:00", cupo: "disponible" }
  ],
  "2026-05-05": [
    { segmento: "Profesionales", zona: "Ecuador", horario: "20:00–21:00", cupo: "ultimos" }
  ],
  "2026-05-06": [
    { segmento: "Profesionales", zona: "Guatemala", horario: "20:00–21:00", cupo: "disponible" },
    { segmento: "Profesionales", zona: "Perú", horario: "21:00–22:00", cupo: "agotado" },
    { segmento: "Estudiantes", zona: "Guatemala", horario: "11:00–12:00", cupo: "ultimos" }
  ],
  "2026-05-09": [
    { segmento: "Profesionales", zona: "Colombia", horario: "10:00–11:00", cupo: "disponible" },
    { segmento: "Profesionales", zona: "México", horario: "19:00–20:00", cupo: "ultimos" },
    { segmento: "Estudiantes", zona: "Colombia", horario: "11:00–12:00", cupo: "disponible" },
    { segmento: "Estudiantes", zona: "México", horario: "11:00–12:00", cupo: "agotado" }
  ],
  "2026-05-11": [
    { segmento: "Profesionales", zona: "Ecuador/Bolivia", horario: "21:00–22:00", cupo: "disponible" }
  ],
  "2026-05-12": [
    { segmento: "Profesionales", zona: "Ecuador", horario: "20:00–21:00", cupo: "ultimos" }
  ],
  "2026-05-13": [
    { segmento: "Profesionales", zona: "Guatemala", horario: "20:00–21:00", cupo: "disponible" },
    { segmento: "Profesionales", zona: "Perú", horario: "21:00–22:00", cupo: "ultimos" },
    { segmento: "Estudiantes", zona: "Guatemala", horario: "11:00–12:00", cupo: "disponible" }
  ],
  "2026-05-16": [
    { segmento: "Profesionales", zona: "Colombia", horario: "10:00–11:00", cupo: "ultimos" },
    { segmento: "Profesionales", zona: "México", horario: "19:00–20:00", cupo: "disponible" },
    { segmento: "Estudiantes", zona: "Colombia", horario: "11:00–12:00", cupo: "ultimos" },
    { segmento: "Estudiantes", zona: "México", horario: "11:00–12:00", cupo: "disponible" }
  ],
  "2026-05-18": [
    { segmento: "Profesionales", zona: "Ecuador/Bolivia", horario: "21:00–22:00", cupo: "agotado" }
  ],
  "2026-05-19": [
    { segmento: "Profesionales", zona: "Ecuador", horario: "20:00–21:00", cupo: "disponible" }
  ],
  "2026-05-20": [
    { segmento: "Profesionales", zona: "Guatemala", horario: "20:00–21:00", cupo: "ultimos" },
    { segmento: "Profesionales", zona: "Perú", horario: "21:00–22:00", cupo: "disponible" },
    { segmento: "Estudiantes", zona: "Guatemala", horario: "11:00–12:00", cupo: "ultimos" }
  ],
  "2026-05-23": [
    { segmento: "Profesionales", zona: "Colombia", horario: "10:00–11:00", cupo: "disponible" },
    { segmento: "Profesionales", zona: "México", horario: "19:00–20:00", cupo: "agotado" },
    { segmento: "Estudiantes", zona: "Colombia", horario: "11:00–12:00", cupo: "ultimos" },
    { segmento: "Estudiantes", zona: "México", horario: "11:00–12:00", cupo: "disponible" }
  ],
  "2026-05-25": [
    { segmento: "Profesionales", zona: "Ecuador/Bolivia", horario: "21:00–22:00", cupo: "disponible" }
  ],
  "2026-05-26": [
    { segmento: "Profesionales", zona: "Ecuador", horario: "20:00–21:00", cupo: "ultimos" }
  ],
  "2026-05-27": [
    { segmento: "Profesionales", zona: "Guatemala", horario: "20:00–21:00", cupo: "disponible" },
    { segmento: "Profesionales", zona: "Perú", horario: "21:00–22:00", cupo: "ultimos" },
    { segmento: "Estudiantes", zona: "Guatemala", horario: "11:00–12:00", cupo: "agotado" }
  ],
  "2026-05-30": [
    { segmento: "Profesionales", zona: "Colombia", horario: "10:00–11:00", cupo: "ultimos" },
    { segmento: "Profesionales", zona: "México", horario: "19:00–20:00", cupo: "disponible" },
    { segmento: "Estudiantes", zona: "Colombia", horario: "11:00–12:00", cupo: "disponible" },
    { segmento: "Estudiantes", zona: "México", horario: "11:00–12:00", cupo: "ultimos" }
  ]
};

const monthYear = { month: 4, year: 2026 }; // Mayo = 4 (indexado)

const cupoMap = {
  disponible: { text: "Disponible", className: "badge--disponible" },
  ultimos: { text: "Últimos cupos", className: "badge--ultimos" },
  agotado: { text: "Agotado", className: "badge--agotado" }
};

const dateFormat = new Intl.DateTimeFormat("es-AR", {
  weekday: "long",
  day: "numeric",
  month: "long",
  year: "numeric"
});

function toDateKey(year, month, day) {
  return `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
}

function renderCalendar() {
  const calendar = document.getElementById("calendar");
  if (!calendar) return;

  const firstDay = new Date(monthYear.year, monthYear.month, 1);
  const lastDay = new Date(monthYear.year, monthYear.month + 1, 0);
  const startOffset = firstDay.getDay();
  const totalDays = lastDay.getDate();

  const weekDays = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];

  calendar.innerHTML = "";

  weekDays.forEach((day) => {
    const head = document.createElement("div");
    head.className = "calendar__weekday";
    head.textContent = day;
    calendar.appendChild(head);
  });

  for (let i = 0; i < startOffset; i += 1) {
    const blank = document.createElement("div");
    blank.className = "calendar__blank";
    calendar.appendChild(blank);
  }

  for (let day = 1; day <= totalDays; day += 1) {
    const key = toDateKey(monthYear.year, monthYear.month, day);
    const hasEvents = Boolean(scheduleByDate[key]);

    const button = document.createElement("button");
    button.type = "button";
    button.className = `calendar__day${hasEvents ? " has-events" : ""}`;
    button.dataset.dateKey = key;
    button.textContent = String(day);
    button.disabled = !hasEvents;

    if (hasEvents) {
      button.addEventListener("click", () => selectDate(key));
    }

    calendar.appendChild(button);
  }
}

function selectDate(dateKey) {
  const selectedDate = document.getElementById("selected-date");
  const eventsList = document.getElementById("events-list");
  const turnoInput = document.getElementById("turnoSeleccionado");

  if (!selectedDate || !eventsList) return;

  document.querySelectorAll(".calendar__day.is-selected").forEach((el) => {
    el.classList.remove("is-selected");
  });

  const activeBtn = document.querySelector(`.calendar__day[data-date-key="${dateKey}"]`);
  if (activeBtn) activeBtn.classList.add("is-selected");

  const date = new Date(`${dateKey}T12:00:00`);
  selectedDate.textContent = dateFormat.format(date);

  const events = scheduleByDate[dateKey] || [];
  eventsList.innerHTML = "";

  events.forEach((eventData) => {
    const row = document.createElement("article");
    row.className = "event-row";

    const info = document.createElement("div");
    info.className = "event-row__info";
    info.innerHTML = `
      <h4 class="u-m-0">${eventData.segmento} · ${eventData.zona}</h4>
      <p class="u-m-0 u-text-muted">Horario AR: ${eventData.horario}</p>
    `;

    const status = document.createElement("span");
    status.className = `badge ${cupoMap[eventData.cupo].className}`;
    status.textContent = cupoMap[eventData.cupo].text;

    const cta = document.createElement("a");
    cta.className = "btn btn--secondary";
    cta.href = "#formulario-inscripcion";
    cta.textContent = "Inscribirse a este foro";
    cta.addEventListener("click", () => {
      if (turnoInput) {
        turnoInput.value = `${dateFormat.format(date)} · ${eventData.segmento} ${eventData.zona} (${eventData.horario})`;
      }
    });

    const actions = document.createElement("div");
    actions.className = "event-row__actions";
    actions.append(status, cta);

    row.append(info, actions);
    eventsList.appendChild(row);
  });
}

function setupFormValidation() {
  const form = document.getElementById("inscripcionForm");
  if (!form) return;

  const certificado = document.getElementById("certificado");
  const linkPagoField = document.getElementById("linkPagoField");
  const linkPagoInput = document.getElementById("linkPago");
  const comprobanteInput = document.getElementById("comprobante");
  const comprobanteEstado = document.getElementById("comprobanteEstado");
  const feedback = document.getElementById("formFeedback");
  const success = document.getElementById("formSuccess");

  const hasValue = (value) => String(value || "").trim().length > 0;
  const showError = (message) => {
    if (!feedback) return;
    feedback.textContent = message;
    feedback.classList.remove("is-hidden");
  };

  const clearError = () => {
    if (!feedback) return;
    feedback.textContent = "";
    feedback.classList.add("is-hidden");
  };

  const showSuccess = (message) => {
    if (!success) return;
    success.innerHTML = message;
    success.classList.remove("is-hidden");
  };

  const clearSuccess = () => {
    if (!success) return;
    success.textContent = "";
    success.classList.add("is-hidden");
  };

  const toggleLinkPago = () => {
    const needsPaymentLink = certificado && certificado.value === "si";

    if (!linkPagoField || !linkPagoInput) return;

    linkPagoField.classList.toggle("is-hidden", !needsPaymentLink);
    linkPagoField.setAttribute("aria-hidden", String(!needsPaymentLink));
    linkPagoInput.required = needsPaymentLink;

    if (!needsPaymentLink) {
      linkPagoInput.value = "";
      linkPagoInput.classList.remove("is-invalid");
    }
  };

  const validate = () => {
    clearError();

    const turno = document.getElementById("turnoSeleccionado");
    const rol = document.getElementById("rol");
    const nombre = document.getElementById("nombreCompleto");
    const documento = document.getElementById("documento");
    const profesion = document.getElementById("profesion");
    const aceptacion = document.getElementById("aceptacion");

    const nombreRegex = /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ'’\-\s]{5,}$/;
    const documentoRegex = /^[0-9]{6,12}$/;
    const profesionRegex = /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9.,()\-\s]{3,}$/;

    const fieldsToReset = [turno, rol, nombre, documento, profesion, linkPagoInput];
    fieldsToReset.forEach((field) => field?.classList.remove("is-invalid"));

    if (!hasValue(turno?.value)) {
      turno?.classList.add("is-invalid");
      return "Debes seleccionar fecha y turno desde el calendario antes de enviar la inscripción.";
    }

    if (!hasValue(rol?.value)) {
      rol?.classList.add("is-invalid");
      return "Selecciona tu rol (Profesional o Estudiante).";
    }

    if (!hasValue(nombre?.value) || !nombreRegex.test(nombre.value.trim())) {
      nombre?.classList.add("is-invalid");
      return "Ingresa tu nombre y apellidos completos (mínimo 5 caracteres, solo letras y espacios).";
    }

    if (!hasValue(documento?.value) || !documentoRegex.test(documento.value.trim())) {
      documento?.classList.add("is-invalid");
      return "El documento debe contener entre 6 y 12 dígitos numéricos (sin puntos ni guiones).";
    }

    if (!hasValue(profesion?.value) || !profesionRegex.test(profesion.value.trim())) {
      profesion?.classList.add("is-invalid");
      return "Indica tu profesión o ejercicio actual con al menos 3 caracteres válidos.";
    }

    if (!hasValue(certificado?.value)) {
      certificado?.classList.add("is-invalid");
      return "Indica si deseas solicitar certificado de asistencia.";
    }

    if (certificado?.value === "si") {
      const urlValue = (linkPagoInput?.value || "").trim();
      const isValidUrl = /^https?:\/\/.+\..+/.test(urlValue);

      if (!isValidUrl) {
        linkPagoInput?.classList.add("is-invalid");
        return "Si solicitas certificado, debes ingresar un link de pago válido que inicie con http:// o https://.";
      }
    }

    if (!comprobanteInput?.files || comprobanteInput.files.length === 0) {
      return "Adjunta el comprobante de pago (PDF o imagen) para completar la inscripción.";
    }

    if (!signatureState.hasSignature) {
      return "La firma digital es obligatoria. Firma en el área de canvas antes de enviar.";
    }

    if (!aceptacion?.checked) {
      return "Debes aceptar el compromiso y la responsabilidad de asistencia para continuar.";
    }

    return "";
  };

  certificado?.addEventListener("change", () => {
    toggleLinkPago();
    certificado.classList.remove("is-invalid");
  });

  comprobanteInput?.addEventListener("change", () => {
    if (!comprobanteEstado) return;

    if (comprobanteInput.files && comprobanteInput.files.length > 0) {
      comprobanteEstado.textContent = `Archivo seleccionado: ${comprobanteInput.files[0].name} (simulado)`;
    } else {
      comprobanteEstado.textContent = "Aún no seleccionaste archivo.";
    }
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    clearSuccess();

    const message = validate();
    if (message) {
      showError(message);
      return;
    }

    clearError();
    showSuccess(
      "Gracias por seleccionar esta experiencia en comunidad. Nos pedimos compromiso y responsabilidad a la hora de asistir. Cualquier inquietud, estaré a disposición.<br><strong>María Luz Genovese</strong> | Psicóloga Social | WhatsApp: (+54) 9 115593 6719"
    );
    form.reset();
    toggleLinkPago();
    clearSignature();
    if (comprobanteEstado) comprobanteEstado.textContent = "Aún no seleccionaste archivo.";
  });

  toggleLinkPago();
}

const signatureState = {
  isDrawing: false,
  hasSignature: false,
  lastX: 0,
  lastY: 0
};

let signatureCanvas;
let signatureCtx;

function getPoint(event) {
  const rect = signatureCanvas.getBoundingClientRect();
  if (event.touches && event.touches[0]) {
    return {
      x: event.touches[0].clientX - rect.left,
      y: event.touches[0].clientY - rect.top
    };
  }

  return {
    x: event.clientX - rect.left,
    y: event.clientY - rect.top
  };
}

function beginSignature(event) {
  if (!signatureCanvas || !signatureCtx) return;
  signatureState.isDrawing = true;
  signatureState.hasSignature = true;

  const point = getPoint(event);
  signatureState.lastX = point.x;
  signatureState.lastY = point.y;
}

function drawSignature(event) {
  if (!signatureState.isDrawing || !signatureCtx) return;
  event.preventDefault();

  const point = getPoint(event);

  signatureCtx.beginPath();
  signatureCtx.moveTo(signatureState.lastX, signatureState.lastY);
  signatureCtx.lineTo(point.x, point.y);
  signatureCtx.stroke();

  signatureState.lastX = point.x;
  signatureState.lastY = point.y;
}

function stopSignature() {
  signatureState.isDrawing = false;
}

function clearSignature() {
  if (!signatureCanvas || !signatureCtx) return;

  signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
  signatureCtx.fillStyle = "#f8fbff";
  signatureCtx.fillRect(0, 0, signatureCanvas.width, signatureCanvas.height);
  signatureState.hasSignature = false;
}

function setupSignatureCanvas() {
  signatureCanvas = document.getElementById("firmaCanvas");
  if (!signatureCanvas) return;

  signatureCtx = signatureCanvas.getContext("2d");
  signatureCtx.lineWidth = 2;
  signatureCtx.lineJoin = "round";
  signatureCtx.lineCap = "round";
  signatureCtx.strokeStyle = "#0a4a7a";

  clearSignature();

  signatureCanvas.addEventListener("mousedown", beginSignature);
  signatureCanvas.addEventListener("mousemove", drawSignature);
  signatureCanvas.addEventListener("mouseup", stopSignature);
  signatureCanvas.addEventListener("mouseleave", stopSignature);

  signatureCanvas.addEventListener("touchstart", beginSignature, { passive: false });
  signatureCanvas.addEventListener("touchmove", drawSignature, { passive: false });
  signatureCanvas.addEventListener("touchend", stopSignature);

  document.getElementById("limpiarFirma")?.addEventListener("click", clearSignature);
}

renderCalendar();
selectDate("2026-05-09");
setupSignatureCanvas();
setupFormValidation();
