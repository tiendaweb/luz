# QA de paridad visual (Template objetivo vs implementación actual)

## Alcance explícito
Esta matriz valida la paridad visual entre:

- **Origen de diseño:** `TEMPLATE OBJETIVO.html`.
- **Implementación objetivo de revisión:** `app/Views/home.php`.
- **Partiales incluidos por la vista principal:**
  - `app/Views/partials/header.php`
  - `app/Views/partials/modals/register.php`
  - `app/Views/partials/footer.php`

> Alcance funcional: sólo componentes visuales críticos y su comportamiento de interacción básico (sin cubrir lógica de API/back-end).

## Convenciones de resultado
- **OK:** mantiene estética y comportamiento visual objetivo.
- **Desvío:** diferencia perceptible o estructural respecto de la referencia objetivo.

## Matriz de paridad por componente crítico

| Componente crítico | Referencia de origen (template) | Referencia implementación | Criterio medible | Resultado | Evidencia breve |
|---|---|---|---|---|---|
| Navbar (desktop + branding) | Bloque `<!-- NAVBAR -->`: contenedor `glass fixed w-full`, altura `h-20`, marca `Foros PSME`, CTA `Inscribirse`. | `app/Views/partials/header.php` (`<!-- NAVBAR -->`). | Presencia de clases clave (`glass`, `fixed`, `z-50`, `h-20`), misma jerarquía visual de marca (título + subtítulo), CTA principal con `bg-teal-600` y navegación horizontal en `md+`. | **OK** | Se replica estructura, tipografía y CTA con clases equivalentes; mantiene peso visual y distribución. |
| Hero (above the fold) | Bloque `<!-- Hero -->` dentro de `#view-home`: badge, `h1` principal, párrafo introductorio, 2 CTA y visual lateral. | `app/Views/home.php` (`#view-home` bloque Hero). | `H1` único y dominante (`text-5xl lg:text-7xl`), CTA principal (`bg-teal-600`) + secundario (`border-2`), espaciados (`pt-16 pb-32`, `mb-*`) y layout responsivo (`flex-col lg:flex-row`). | **OK** | La jerarquía H1/CTA y composición visual (texto + imagen + elemento flotante) coinciden con el template. |
| Cards / secciones de contenido | Secciones “Nuestra Dinámica”, testimonios y FAQ con cards redondeadas (`rounded-3xl`) y estados hover. | `app/Views/home.php` (secciones metodología, testimonios, FAQ, blog/cards). | Cards con `rounded-3xl`, sombras/hover (`card-shadow` o `hover:bg-*`), títulos H2/H3/H4 coherentes y espaciados de bloque (`py-24`, `gap-*`). | **OK** | Se conserva gramática visual de tarjetas, ritmo vertical y tipografía por niveles. |
| Dashboard por rol | `#view-dashboard` + bloques con clases `admin-only`, `associate-only`, `user-only`. | `app/Views/home.php` (`#view-dashboard` y módulos por rol). | Visibilidad condicional por rol (`[data-active-role="..."]` + `*-only hidden`), sidebar + panel principal, cards por estado con color semántico. | **OK** | Implementación respeta segmentación visual por rol e identidad cromática de cada módulo. |
| Modales (registro) | Bloque `registerModal` con overlay, contenedor redondeado, cabecera + formulario + CTA final. | `app/Views/partials/modals/register.php` (`#registerModal`). | Overlay (`bg-slate-900/60` + blur), estructura modal (`max-w-2xl`, `rounded-[2.5rem]`), campos con `focus:ring-teal-500`, CTA de envío visible. | **OK** | El modal mantiene arquitectura visual objetivo, foco en formulario y contraste de acciones. |
| Estados hover / focus / active | Enlaces y botones con `hover:*`; inputs/select con `focus:ring-*`; secciones activas controladas por `view-section active`. | `app/Views/home.php` + `app/Views/partials/header.php` + `app/Views/partials/modals/register.php`. | Cobertura de interacción en elementos críticos: `hover:bg-*`/`hover:text-*`, foco visible en campos (`focus:ring-2`), activación de vista por clase `.active`. | **Desvío** | Hay buena cobertura de `hover` y `focus` en formularios, pero no se ve uso consistente de `focus-visible:*` en botones/enlaces de navegación (accesibilidad visual teclado mejorable). |
| Mobile menu | Bloque `#mobileMenu`: overlay full-screen, botón cierre, links principales y CTA de inscripción. | `app/Views/partials/header.php` (`#mobileMenu`). | Menú oculto/visible (`hidden`), ocupa viewport (`fixed inset-0 z-[60]`), navegación vertical y CTA principal destacado en mobile. | **OK** | Se respeta estructura full-screen y jerarquía de acciones; se añade botón móvil “Mi Área” condicional sin romper estética base. |

## Checklist de regresión manual por viewport

> Ejecutar este checklist tras cambios visuales en componentes críticos.

### 1) Mobile — 360x800
- [ ] Navbar: logo/texto no colisiona y botón hamburguesa visible.
- [ ] Menú móvil abre/cierra correctamente; no hay scroll de fondo no deseado.
- [ ] Hero: H1 y CTAs no se cortan; botones apilan en columna con separación adecuada.
- [ ] Cards/secciones: no hay overflow horizontal; paddings legibles.
- [ ] Modal registro: abre centrado, permite scroll interno, CTA final visible.
- [ ] Interacciones: verificar `hover` equivalente táctil, foco por teclado externo y estados activos.

### 2) Tablet — 768x1024
- [ ] Navbar cambia a layout desktop (`md:flex`) y mantiene alineaciones.
- [ ] Hero conserva balance texto/imagen y spacing vertical.
- [ ] Grillas (`md:grid-cols-*`) muestran cards sin saltos visuales.
- [ ] Dashboard por rol mantiene sidebar + contenido sin solapamientos.
- [ ] Modal mantiene ancho cómodo y jerarquía tipográfica.
- [ ] Interacciones: hover/focus en CTAs y enlaces principales.

### 3) Desktop — 1366x768
- [ ] Navbar fija sin jitter al navegar entre vistas.
- [ ] Hero conserva impacto visual (H1 dominante y CTA primario claramente principal).
- [ ] Secciones y cards respetan ritmo vertical (`py-*`) y profundidad (sombras/hover).
- [ ] Dashboard por rol: módulos condicionales visibles según rol simulado.
- [ ] Menú móvil no interfiere en `md+`.
- [ ] Interacciones: verificar hover, focus-visible (si aplica), estados active de vista.

## Registro de ejecución por lotes (tema crema)

### Lote 1 — Dashboard + CTAs principales (2026-04-24)
- Alcance validado: `dashboard`, CTA primarios de header, foros e inscripción.
- Resultado: checklist ejecutado en los tres viewports sin desvíos críticos observados en jerarquía visual.
- Observación: se conserva semántica de estados operativos (success/warn/error) sin cambios cromáticos.

### Lote 2 — Landing + Blog + Contacto/Directora (2026-04-24)
- Alcance validado: home/landing, blog, bloque de contacto y formulario de consulta en `directora`.
- Resultado: checklist ejecutado en mobile/tablet/desktop con paridad visual general respecto a estructura objetivo.
- Observación: se unificó la identidad de marca a clases semánticas (`btn-primary`, `text-brand`, `bg-brand`) y variables `:root`.

## Política de PR (visual)

### Regla obligatoria
**No romper estética objetivo.**

### Requerimiento de mantenimiento
Toda PR que modifique componentes visuales críticos (navbar, hero, cards/secciones, dashboard por rol, modales, estados de interacción, menú móvil) **debe actualizar esta matriz** con:

1. Componente afectado.
2. Nuevo criterio medible (si cambió).
3. Resultado esperado/observado.
4. Evidencia breve del ajuste.

### Criterio de aceptación mínimo
- Sin desvíos visuales no justificados respecto al template objetivo.
- Si hay desvío intencional, debe quedar documentado en esta matriz con motivo y alcance.
