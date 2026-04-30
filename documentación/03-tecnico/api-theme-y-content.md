# API — Theme y Content

## Objetivo

Definir contratos base para gestión de tema y contenido.

## Endpoints (borrador)

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/theme` | Obtener configuración de tema activa |
| PUT | `/api/theme` | Actualizar configuración de tema |
| GET | `/api/content/:id` | Obtener contenido por identificador |
| PUT | `/api/content/:id` | Actualizar borrador de contenido |
| POST | `/api/content/:id/publish` | Publicar versión activa |

## Contratos de datos (referencia)

- **Theme**: `colors`, `typography`, `spacing`, `components`.
- **Content**: `id`, `status`, `blocks[]`, `version`, `updatedAt`.

## Trazabilidad (requisito → endpoint/vista)

| Requisito | Endpoint/API | Vista/UI |
|---|---|---|
| REQ-STYLE-001 | `/api/theme` | `/admin/theme` |
| REQ-CONTENT-001 | `/api/content/:id` | `/editor` |
| REQ-CONTENT-002 | `/api/content/:id/publish` | `/editor` |

## Enlaces relacionados

- [[00-index]]
- [[03-tecnico/arquitectura-contenido-inline]]
- [[01-qa/matriz-pruebas]]
