# Requerimientos — Admin Editor Visual

## Objetivo

Definir capacidades mínimas del módulo administrativo para creación y mantenimiento de contenido mediante editor visual.

## Requerimientos funcionales

- **REQ-ADMIN-001**: Crear, editar, eliminar y reordenar bloques de contenido.
- **REQ-ADMIN-002**: Vista previa en tiempo real del contenido.
- **REQ-ADMIN-003**: Guardado en borrador y publicación.
- **REQ-ADMIN-004**: Historial básico de cambios.

## Requerimientos no funcionales

- Tiempo de respuesta de acciones críticas < 500ms (objetivo).
- Persistencia confiable y recuperación ante error de red.
- Interfaz usable en escritorio (MVP).

## Trazabilidad (requisito → endpoint/vista)

| Requisito | Endpoint/API | Vista/UI | Caso QA |
|---|---|---|---|
| REQ-ADMIN-001 | `/api/admin/blocks` | `/admin/editor-visual` | QA-001, QA-002 |
| REQ-ADMIN-002 | `/api/admin/preview` | `/admin/editor-visual` | QA-002 |
| REQ-ADMIN-003 | `/api/content/publish` | `/admin/editor-visual` | QA-004 |
| REQ-ADMIN-004 | `/api/admin/history` | `/admin/editor-visual` | Pendiente |

## Enlaces relacionados

- [[00-index]]
- [[01-qa/matriz-pruebas]]
- [[03-tecnico/api-theme-y-content]]
