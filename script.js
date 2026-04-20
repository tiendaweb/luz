(() => {
  // ========================================
  // Datos de calendario (simulados)
  // ========================================
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

  const monthYear = { month: 4, year: 2026 };

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

  // ========================================
  // Utilidades compartidas (validación + estados)
  // ========================================
  const Utils = {
    isEmail(value) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value).trim());
    },

    isRequiredFilled(value) {
      return String(value || "").trim().length > 0;
    },

    toDateKey(year, month, day) {
      return `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
    },

    renderState(container, { message, variant = "info", hidden = false }) {
      if (!container) return;
      container.textContent = message || "";
      container.className = `alert alert--${variant}${hidden ? " u-hidden" : ""}`;
    }
  };

  // ========================================
  // Bloque: navegación
  // ========================================
  const NavigationBlock = {
    init() {
      const currentPath = window.location.pathname.split("/").pop() || "index.html";
      const links = document.querySelectorAll(".nav-link");
      if (!links.length) return;

      links.forEach((link) => {
        const href = link.getAttribute("href");
        if (!href) return;
        const isCurrent = href === currentPath;
        link.classList.toggle("is-active", isCurrent);
        if (isCurrent) {
          link.setAttribute("aria-current", "page");
        } else {
          link.removeAttribute("aria-current");
        }
      });
    }
  };

  // ========================================
  // Bloque: calendario y selección de turnos
  // ========================================
  const CalendarBlock = {
    init() {
      this.calendar = document.getElementById("calendar");
      this.selectedDate = document.getElementById("selected-date");
      this.eventsList = document.getElementById("events-list");
      this.turnoInput = document.getElementById("turnoSeleccionado");

      if (!this.calendar) return;
      this.renderCalendar();
      this.selectDate("2026-05-09");
    },

    renderCalendar() {
      const firstDay = new Date(monthYear.year, monthYear.month, 1);
      const lastDay = new Date(monthYear.year, monthYear.month + 1, 0);
      const startOffset = firstDay.getDay();
      const totalDays = lastDay.getDate();
      const weekDays = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];

      this.calendar.innerHTML = "";

      weekDays.forEach((day) => {
        const head = document.createElement("div");
        head.className = "calendar__weekday";
        head.textContent = day;
        this.calendar.appendChild(head);
      });

      for (let i = 0; i < startOffset; i += 1) {
        const blank = document.createElement("div");
        blank.className = "calendar__blank";
        this.calendar.appendChild(blank);
      }

      for (let day = 1; day <= totalDays; day += 1) {
        const key = Utils.toDateKey(monthYear.year, monthYear.month, day);
        const hasEvents = Boolean(scheduleByDate[key]);

        const button = document.createElement("button");
        button.type = "button";
        button.className = `calendar__day${hasEvents ? " has-events" : ""}`;
        button.dataset.dateKey = key;
        button.textContent = String(day);
        button.disabled = !hasEvents;

        if (hasEvents) {
          button.addEventListener("click", () => this.selectDate(key));
        }

        this.calendar.appendChild(button);
      }
    },

    selectDate(dateKey) {
      if (!this.selectedDate || !this.eventsList) return;

      document.querySelectorAll(".calendar__day.is-selected").forEach((el) => {
        el.classList.remove("is-selected");
      });

      const activeBtn = document.querySelector(`.calendar__day[data-date-key="${dateKey}"]`);
      if (activeBtn) activeBtn.classList.add("is-selected");

      const date = new Date(`${dateKey}T12:00:00`);
      this.selectedDate.textContent = dateFormat.format(date);

      const events = scheduleByDate[dateKey] || [];
      this.eventsList.innerHTML = "";

      events.forEach((eventData) => {
        const row = document.createElement("article");
        row.className = "event-row";

        const info = document.createElement("div");
        info.className = "event-row__info";
        info.innerHTML = `
          <h4 class="u-m-0">${eventData.segmento} · ${eventData.zona}</h4>
          <p class="u-m-0 u-text-muted">Horario AR: ${eventData.horario}</p>
        `;

        const cupoData = cupoMap[eventData.cupo] || cupoMap.disponible;
        const status = document.createElement("span");
        status.className = `badge ${cupoData.className}`;
        status.textContent = cupoData.text;

        const cta = document.createElement("a");
        cta.className = "btn btn--secondary";
        cta.href = "#formulario-inscripcion";
        cta.textContent = "Inscribirse a este foro";
        cta.addEventListener("click", () => {
          if (this.turnoInput) {
            this.turnoInput.value = `${dateFormat.format(date)} · ${eventData.segmento} ${eventData.zona} (${eventData.horario})`;
          }
        });

        const actions = document.createElement("div");
        actions.className = "event-row__actions";
        actions.append(status, cta);

        row.append(info, actions);
        this.eventsList.appendChild(row);
      });
    }
  };

  // ========================================
  // Bloque: formularios (inscripción)
  // ========================================
  const FormsBlock = {
    init() {
      const form = document.getElementById("inscripcion-form");
      if (!form) return;

      const feedback = document.getElementById("inscripcion-feedback");
      const turno = document.getElementById("turnoSeleccionado");
      const nombre = document.getElementById("nombreCompleto");
      const documento = document.getElementById("documento");

      form.addEventListener("submit", (event) => {
        event.preventDefault();

        const errors = [];
        if (!Utils.isRequiredFilled(turno?.value)) {
          errors.push("Selecciona una fecha y turno desde el calendario.");
        }
        if (!Utils.isRequiredFilled(nombre?.value)) {
          errors.push("Completa tu nombre y apellidos.");
        }
        if (!Utils.isRequiredFilled(documento?.value)) {
          errors.push("Completa tu documento (DNI/Cédula).");
        }

        if (errors.length > 0) {
          Utils.renderState(feedback, {
            message: errors.join(" "),
            variant: "warning",
            hidden: false
          });
          return;
        }

        Utils.renderState(feedback, {
          message: "Inscripción simulada enviada correctamente. Punto de extensión: enviar payload al backend en este submit.",
          variant: "success",
          hidden: false
        });

        form.reset();
      });
    }
  };

  // ========================================
  // Bloque: login simulado
  // ========================================
  const LoginBlock = {
    init() {
      const form = document.getElementById("login-form");
      if (!form) return;

      const roleField = document.getElementById("role");
      const emailField = document.getElementById("email");
      const feedback = document.getElementById("login-feedback");

      const routesByRole = {
        admin: "dashboard-admin.html",
        asociado: "dashboard-admin.html?modo=asociado#panel-asociados",
        usuario: "dashboard-usuario.html"
      };

      const roleByEmail = {
        "admin@psme.test": "admin",
        "asociado@psme.test": "asociado",
        "usuario@psme.test": "usuario"
      };

      form.addEventListener("submit", (event) => {
        event.preventDefault();

        const email = emailField.value.trim().toLowerCase();
        const selectedRole = roleField.value || roleByEmail[email] || "";

        if (!Utils.isRequiredFilled(email) || !Utils.isEmail(email)) {
          Utils.renderState(feedback, {
            message: "Ingresa un email válido para continuar con el acceso simulado.",
            variant: "warning",
            hidden: false
          });
          return;
        }

        if (!selectedRole || !routesByRole[selectedRole]) {
          Utils.renderState(feedback, {
            message: "Selecciona un rol válido o utiliza un email dummy conocido para continuar.",
            variant: "warning",
            hidden: false
          });
          return;
        }

        Utils.renderState(feedback, { message: "", hidden: true });
        // Punto de extensión backend: intercambiar este redirect directo por autenticación real y manejo de token/sesión.
        window.location.href = routesByRole[selectedRole];
      });
    }
  };


  // ========================================
  // Bloque: contacto simulado (sin persistencia)
  // ========================================
  const ContactBlock = {
    init() {
      this.form = document.getElementById("contacto-form");
      this.feedback = document.getElementById("contacto-feedback");
      this.submitBtn = document.getElementById("contacto-submit");
      this.locationCard = document.querySelector("[data-location-card]");
      this.locationText = document.querySelector("[data-location-text]");
      this.locationMap = document.querySelector("[data-location-map]");

      this.renderLocation();
      if (!this.form) return;

      const nombre = document.getElementById("contacto-nombre");
      const email = document.getElementById("contacto-email");
      const mensaje = document.getElementById("contacto-mensaje");

      this.form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const errors = [];
        if (!Utils.isRequiredFilled(nombre?.value)) {
          errors.push("Completa tu nombre.");
        }
        if (!Utils.isRequiredFilled(email?.value) || !Utils.isEmail(email?.value)) {
          errors.push("Ingresa un email válido.");
        }
        if (!Utils.isRequiredFilled(mensaje?.value)) {
          errors.push("Escribe tu mensaje antes de enviar.");
        }

        if (errors.length > 0) {
          Utils.renderState(this.feedback, {
            message: errors.join(" "),
            variant: "warning",
            hidden: false
          });
          return;
        }

        if (this.submitBtn) {
          this.submitBtn.disabled = true;
          this.submitBtn.textContent = "Enviando...";
        }

        Utils.renderState(this.feedback, {
          message: "Envío simulado en proceso...",
          variant: "info",
          hidden: false
        });

        await new Promise((resolve) => window.setTimeout(resolve, 900));

        const shouldFail = String(mensaje.value).toLowerCase().includes("error");

        if (shouldFail) {
          Utils.renderState(this.feedback, {
            message: "No se pudo enviar el mensaje (simulación). Verifica los datos e intenta nuevamente.",
            variant: "danger",
            hidden: false
          });
        } else {
          Utils.renderState(this.feedback, {
            message: "Mensaje enviado correctamente (simulación). Te responderé a la brevedad por el medio indicado.",
            variant: "success",
            hidden: false
          });
          this.form.reset();
        }

        if (this.submitBtn) {
          this.submitBtn.disabled = false;
          this.submitBtn.textContent = "Enviar mensaje";
        }
      });
    },

    renderLocation() {
      const physicalReference = {
        name: "",
        mapQuery: ""
      };

      const hasRealReference = Utils.isRequiredFilled(physicalReference.name) && Utils.isRequiredFilled(physicalReference.mapQuery);
      if (!hasRealReference || !this.locationCard || !this.locationText || !this.locationMap) return;

      this.locationCard.classList.remove("u-hidden");
      this.locationText.textContent = physicalReference.name;
      this.locationMap.innerHTML = `
        <iframe
          title="Mapa de referencia"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          src="https://www.google.com/maps?q=${encodeURIComponent(physicalReference.mapQuery)}&output=embed">
        </iframe>
      `;
    }
  };

  // ========================================
  // Bloque: widgets de dashboard (simulados)
  // ========================================
  const DashboardWidgetsBlock = {
    init() {
      const widgets = document.querySelectorAll("[data-widget]");
      if (!widgets.length) return;

      const simulatedData = {
        usuariosActivos: "342",
        forosSemana: "12",
        ticketsPendientes: "7",
        progresoUsuario: "68%"
      };

      widgets.forEach((widget) => {
        const widgetKey = widget.dataset.widget;
        const value = simulatedData[widgetKey] || "--";
        widget.textContent = value;
      });

      // Punto de extensión backend: reemplazar simulatedData por respuesta de API por rol.
    }
  };

  function boot() {
    NavigationBlock.init();
    CalendarBlock.init();
    FormsBlock.init();
    LoginBlock.init();
    ContactBlock.init();
    DashboardWidgetsBlock.init();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
