# Guía de componentes y paleta (MVP)

## Variables CSS base (`styles.css`)

Paleta centralizada en `:root`:

### Inventario por categoría

- **Fondo / superficie**: `--color-primary`, `--color-primary-700`, `--color-surface`, `--color-surface-muted`.
- **Texto**: `--color-text`, `--color-text-muted`, `--color-primary-contrast`, `--color-accent-contrast`.
- **Bordes**: `--color-border`.
- **Contraste / énfasis**: `--color-accent`, `--color-accent-700`.
- **Estados**: `--color-status-approved-bg`, `--color-status-approved-text`, `--color-status-pending-bg`, `--color-status-pending-text`, `--color-status-rejected-bg`, `--color-status-rejected-text`.

> Regla: evitar hex hardcodeado en componentes nuevos.

## Tokens del widget de tema admin

### Editables (valor explícito)

- `colors.primary`, `colors.secondary`, `colors.accent`, `colors.surface`, `colors.text`
- `colors.border`, `colors.text_muted`
- `colors.primary_contrast`, `colors.accent_700`, `colors.accent_contrast`
- `colors.status_approved_bg`, `colors.status_approved_text`
- `colors.status_pending_bg`, `colors.status_pending_text`
- `colors.status_rejected_bg`, `colors.status_rejected_text`
- `typography.font_family`, `typography.font_size_base`

### Derivados automáticos (fallback)

- `--color-primary-700`: mezcla de `primary` hacia negro (~22%) cuando falta `secondary`.
- `--color-primary-contrast`: contraste automático (texto claro/oscuro) sobre `primary`.
- `--color-accent-700`: mezcla de `accent` hacia negro (~20%) cuando falta explícito.
- `--color-accent-contrast`: contraste automático sobre `accent`.
- `--color-surface-muted`: mezcla sutil de `surface` con tinta oscura (~3%).
- `--color-border`: mezcla de `surface` con tinta oscura (~12%) cuando no se define.
- `--color-text-muted`: mezcla de `text` + `surface` (~35%) cuando no se define.
- Estados (`*_text`): contraste automático sobre su respectivo `*_bg` cuando no se define.

### Precedencia

1. **Valor explícito del admin** (guardado en `/api/admin/theme`).
2. **Valor derivado automático** (si el explícito falta o está vacío).
3. **Default del sistema** (si no hay valor persistido).

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
