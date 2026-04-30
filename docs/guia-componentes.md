# Guía mínima de componentes reutilizables

Esta guía define un **sistema base HTML/CSS** para mantener consistencia visual y reducir estilos duplicados entre páginas.

## 1) Variables globales

Las variables CSS viven en `:root` dentro de `styles.css` e incluyen:

- **Color:** fondo, superficies, texto, bordes, primario y estados.
- **Tipografía:** familia, tamaños y line-height.
- **Spacing:** escala incremental (`--space-1` a `--space-7`).
- **Border-radius:** esquinas pequeñas, medianas, grandes y píldora.
- **Sombras:** niveles `sm` y `md`.

> Recomendación: no usar valores hex o px “hardcodeados” en componentes nuevos si ya existe un token equivalente.

## 2) Componentes base

### Botones CTA

- Base: `.btn`
- Principal: `.btn.btn--cta`
- Secundario: `.btn.btn--secondary`

```html
<a class="btn btn--cta" href="#inscripcion">Inscribirse al Foro</a>
<button class="btn btn--secondary" type="button">Ver agenda</button>
```

### Cards

- Contenedor: `.card`
- Título: `.card__title`
- Meta: `.card__meta`

```html
<article class="card">
  <h3 class="card__title">Foro Profesionales - Colombia</h3>
  <p class="card__meta">Sábados 9, 16, 23, 30 · 10:00–11:00</p>
</article>
```

### Tablas

- Wrapper responsive: `.table-wrap`
- Tabla: `.table`

```html
<div class="table-wrap">
  <table class="table">
    <thead>
      <tr><th>Zona</th><th>Fecha</th><th>Horario</th></tr>
    </thead>
    <tbody>
      <tr><td>Colombia</td><td>Sábados</td><td>10:00–11:00</td></tr>
    </tbody>
  </table>
</div>
```

### Badges de cupos

- Base: `.badge`
- Estados: `.badge--disponible`, `.badge--ultimos`, `.badge--agotado`

```html
<span class="badge badge--disponible">Cupos disponibles</span>
<span class="badge badge--ultimos">Últimos cupos</span>
<span class="badge badge--agotado">Agotado</span>
```

### Inputs

- Campo: `.field`
- Label: `.field__label`
- Controles: `.input`, `.select`, `.textarea`
- Hint: `.field__hint`

```html
<label class="field">
  <span class="field__label">Nombre completo</span>
  <input class="input" type="text" placeholder="Ej: Ana Pérez" />
  <small class="field__hint">Como figura en el certificado.</small>
</label>
```

### Alerts

- Base: `.alert`
- Estados: `.alert--info`, `.alert--success`, `.alert--warning`, `.alert--danger`

```html
<div class="alert alert--info">Tu inscripción aún no fue enviada.</div>
```

## 3) Accesibilidad (WCAG 2.1 AA)

Se implementan criterios de legibilidad y contraste:

- Texto principal oscuro sobre fondos claros.
- Estados de foco visibles mediante `:focus-visible` con outline notorio.
- Tamaño base legible (`1rem`) y line-height cómodo.
- Colores de estado con fondo + texto contrastados para lectura.

Buenas prácticas al extender:

- Evitar texto gris claro sobre fondo blanco.
- No depender solo de color para estados críticos (acompañar con ícono o texto).
- Probar navegación por teclado en componentes interactivos.

## 4) Breakpoints mobile-first

Definidos como tokens:

- `--bp-tablet: 48rem` (768px)
- `--bp-desktop: 64rem` (1024px)

Estrategia:

1. Estilos base para móvil.
2. Ajustes progresivos en tablet (grillas 2 columnas, botón más grande).
3. Ajustes en desktop (grillas 3 columnas, mayor respiración en cards).

## 5) Clases utilitarias clave

Para evitar repetir CSS entre HTMLs:

- **Texto:** `.u-text-muted`, `.u-text-center`, `.u-fw-600`
- **Espaciado:** `.u-m-0`, `.u-mt-4`, `.u-mb-4`, `.u-p-4`
- **Visuales:** `.u-rounded-md`, `.u-shadow-sm`
- **Estado:** `.u-hidden`
- **Layout:** `.container`, `.stack`, `.grid`, `.grid--2-cols`, `.grid--3-cols`

> Regla sugerida: construir vistas con utilidades + componentes primero; crear CSS nuevo solo si hay una necesidad repetible no cubierta.
