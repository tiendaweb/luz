# IA · Sitemap y flujos de navegación

## 1) Sitemap (páginas obligatorias)

```text
index.html (Landing)
├── about.html (Sobre María Luz)
├── foros.html (Foros / Eventos)
│   └── [flujo de inscripción simulado]
├── contact.html (Contacto)
└── login.html (Ingreso simulado)
    ├── dashboard-admin.html (si rol = admin)
    └── dashboard-usuario.html (si rol = usuario)
```

### Enlaces mínimos por página

- `index.html`
  - Navega a: `about.html`, `foros.html`, `contact.html`, `login.html`
  - CTA principal: **Inscribirse al Foro** → `foros.html`
- `about.html`
  - Volver a landing: `index.html`
  - CTA secundario: **Ver foros** → `foros.html`
  - Enlace de contacto: `contact.html`
- `foros.html`
  - Volver a landing: `index.html`
  - Enlace a información profesional: `about.html`
  - Enlace de soporte: `contact.html`
  - Acceso a sesión: `login.html`
  - CTA de conversión: **Inscribirse** → sección/formulario de inscripción en la misma página (ancla `#inscripcion`) o paso simulado interno
- `contact.html`
  - Volver a landing: `index.html`
  - Enlace a foros: `foros.html`
  - Enlace a sobre María Luz: `about.html`
- `login.html`
  - Volver a landing: `index.html`
  - Enlace a contacto de ayuda: `contact.html`
  - Redirección por rol:
    - admin → `dashboard-admin.html`
    - usuario → `dashboard-usuario.html`
- `dashboard-admin.html`
  - Logout simulado → `login.html`
  - Ir a sitio público → `index.html` o `foros.html`
- `dashboard-usuario.html`
  - Logout simulado → `login.html`
  - Ir a foros disponibles → `foros.html`

---

## 2) Flujo principal de conversión

Objetivo: llevar tráfico desde la landing hacia la inscripción al foro y cerrar con mensaje final.

```text
Landing (index.html)
→ Foros (foros.html)
→ Inscripción (formulario/ancla #inscripcion en foros.html)
→ Mensaje final (estado de éxito en foros.html o pantalla de confirmación simulada)
```

### Definición por etapas

1. **Landing**
   - Promesa de valor clara.
   - CTA dominante: “Inscribirse al Foro”.
2. **Foros**
   - Lista de opciones + fechas/segmentos.
   - CTA por foro y CTA fijo de inscripción.
3. **Inscripción**
   - Formulario simulado (sin persistencia).
   - Validación visual de campos obligatorios.
4. **Mensaje final**
   - Confirmación y texto de compromiso.
   - Enlaces de continuidad: volver a foros o contacto.

---

## 3) Flujos secundarios

### A) Descubrimiento de autoridad profesional

```text
Landing (index.html)
→ Sobre María Luz (about.html)
```

Uso: usuarios que necesitan confianza antes de inscribirse.

### B) Consulta directa

```text
Landing (index.html)
→ Contacto (contact.html)
```

Uso: usuarios con dudas sobre horarios, modalidad o certificados.

### C) Acceso autenticado simulado por rol

```text
Landing (index.html) o cualquier página pública
→ Login (login.html)
→ Dashboard según rol:
   - admin → dashboard-admin.html
   - usuario → dashboard-usuario.html
```

Uso: continuidad post-registro y navegación a zona privada simulada.

---

## 4) Estados de navegación y enlaces cruzados

## Estados de navegación obligatorios

Definir en el sistema de estilos (header, menú, botones y links):

- **Activo (`:active` + estado de página actual)**
  - Ítem del menú correspondiente a la página actual con estilo persistente (ej. subrayado o fondo destacado).
- **Hover (`:hover`)**
  - Feedback visual claro para mouse/pointer (cambio de color, contraste o elevación).
- **Foco (`:focus-visible`)**
  - Anillo de foco visible para accesibilidad por teclado.
  - No remover outline sin reemplazo accesible.

### Matriz mínima de enlaces cruzados

- Header global en páginas públicas (`index`, `about`, `foros`, `contact`, `login`):
  - Inicio | Sobre María Luz | Foros | Contacto | Login
- Footer global recomendado:
  - Repetir acceso a Foros y Contacto como rutas de rescate.
- Dashboards:
  - Deben incluir al menos: volver a sitio público + logout.

### Reglas UX de consistencia

- El enlace de la página actual aparece marcado como **activo**.
- Todo CTA principal de una página debe tener destino claro y único.
- Si una acción no completa flujo (ej. login fallido simulado), mostrar mensaje de error inline y mantener foco en el formulario.

---

## 5) Nota de implementación para trabajo paralelo (Frontend + Contenido)

Este documento funciona como **fuente de verdad funcional** para:

- Frontend: maquetado de rutas, menús, estados y redirecciones simuladas.
- Contenido/UX Writing: redacción de CTAs, mensajes de validación y mensaje final.

Checklist operativo:

- [ ] Todas las páginas obligatorias creadas con enlaces entre sí.
- [ ] Flujo principal completo hasta mensaje final.
- [ ] Flujos secundarios navegables desde landing.
- [ ] Estados `activo`, `hover`, `focus-visible` definidos y visibles.
- [ ] Header/footer consistentes en páginas públicas.
- [ ] Login simulado con redirección por rol.

