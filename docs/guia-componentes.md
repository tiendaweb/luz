# Guía de componentes y paleta (MVP)

## Variables CSS base (`styles.css`)

Paleta centralizada en `:root`:

- `--color-primary`, `--color-primary-700`, `--color-primary-contrast`
- `--color-accent`, `--color-accent-700`, `--color-accent-contrast`
- `--color-surface`, `--color-surface-muted`, `--color-border`
- `--color-text`, `--color-text-muted`
- Estados AA: `--color-status-approved-*`, `--color-status-pending-*`, `--color-status-rejected-*`

> Regla: evitar hex hardcodeado en componentes nuevos.

## Usos por componente

- **Botón principal**: `.btn-primary` (CTA, acciones de envío/confirmación)
- **Botón secundario**: `.btn-secondary` (paginación, acciones neutras)
- **Navegación**: `.nav-link-accent` para hover/foco consistente
- **Cards**: `.card-surface` para fondo/borde uniforme
- **Badges de estado**: `.status-badge` + modificador:
  - `.status-badge--approved`
  - `.status-badge--pending`
  - `.status-badge--rejected`
- **Alertas estado** (feedback operativo): `.alert-pending`, `.alert-rejected`

## Contraste y accesibilidad (WCAG AA)

- Se usan combinaciones oscuras sobre fondos claros en estados críticos.
- `:focus-visible` en `.btn-primary` usa `outline` de alto contraste.
- Estados no dependen sólo del color: se mantiene etiqueta textual (Aprobada/Pendiente/Rechazada).

## Cobertura de consistencia visual

Se alineó el uso de tokens en:

- Vistas PHP: login, foros, layout principal, dashboard (badges/alerts dinámicos).
- Pantallas HTML: login (referencia estática).

Para nuevas vistas, reutilizar clases del sistema antes de crear utilidades ad-hoc.
