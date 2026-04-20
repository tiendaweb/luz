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

renderCalendar();
selectDate("2026-05-09");
