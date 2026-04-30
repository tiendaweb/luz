# Índice general de documentación

Este espacio concentra la documentación funcional, de QA y técnica del proyecto, con navegación compatible con Obsidian.

## Mapa de secciones

- [[00-index]]
- [[01-qa/estado-mvp]]
- [[01-qa/matriz-pruebas]]
- [[01-qa/hallazgos]]
- [[02-producto/requerimientos-admin-editor-visual]]
- [[02-producto/especificacion-estilos]]
- [[03-tecnico/arquitectura-contenido-inline]]
- [[03-tecnico/api-theme-y-content]]

## Trazabilidad (requisito → endpoint/vista)

| Requisito | Endpoint/API | Vista/UI | Estado |
|---|---|---|---|
| REQ-ADMIN-001 Gestión de bloques visuales | `/api/admin/blocks` | `/admin/editor-visual` | Pendiente |
| REQ-STYLE-001 Aplicación de tema | `/api/theme` | `/admin/theme` | Pendiente |
| REQ-CONTENT-001 Publicación de contenido inline | `/api/content/inline` | `/editor` | Pendiente |

## Glosario

- **MVP**: Versión mínima viable para validación temprana.
- **Editor visual**: Interfaz administrativa para construir contenido sin código.
- **Contenido inline**: Contenido embebido/editable en contexto de vista.
- **Tema (theme)**: Configuración visual global (colores, tipografías, espaciados).
- **Trazabilidad**: Relación verificable entre requerimientos, implementación y pruebas.

## Checklist de entrega final

- [ ] Requerimientos de producto documentados y validados.
- [ ] Matriz de pruebas ejecutada y con resultados actualizados.
- [ ] Hallazgos priorizados (severidad e impacto).
- [ ] Endpoints y vistas mapeados en tabla de trazabilidad.
- [ ] Decisiones de arquitectura aprobadas.
- [ ] Contratos API versionados.
- [ ] Índice y enlaces wiki sin enlaces rotos.

## Navegación rápida

Ir a QA: [[01-qa/estado-mvp]] · [[01-qa/matriz-pruebas]] · [[01-qa/hallazgos]]

Ir a Producto: [[02-producto/requerimientos-admin-editor-visual]] · [[02-producto/especificacion-estilos]]

Ir a Técnico: [[03-tecnico/arquitectura-contenido-inline]] · [[03-tecnico/api-theme-y-content]]
