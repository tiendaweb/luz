# Especificación de estilos

## Objetivo

Definir lineamientos de estilo para el sistema visual del MVP.

## Sistema base

- Paleta primaria/secundaria con tokens.
- Tipografía base para títulos, cuerpo y metadatos.
- Escala de espaciado consistente.
- Estados de interacción (hover, focus, disabled).

## Tokens (ejemplo inicial)

| Token | Valor ejemplo | Uso |
|---|---|---|
| `color.primary` | `#3B82F6` | Botones primarios, enlaces |
| `color.neutral.900` | `#111827` | Texto principal |
| `font.family.base` | `Inter, sans-serif` | Cuerpo general |
| `space.4` | `16px` | Separaciones estándar |

## Reglas de aplicación

- Los cambios de tema deben propagarse sin recarga completa.
- Mantener contraste AA como mínimo en componentes críticos.
- Evitar estilos inline no tokenizados en componentes nuevos.

## Enlaces relacionados

- [[00-index]]
- [[02-producto/requerimientos-admin-editor-visual]]
- [[03-tecnico/api-theme-y-content]]
