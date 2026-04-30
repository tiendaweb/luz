# Matriz de pruebas

## Objetivo

Registrar casos de prueba, resultado esperado y resultado obtenido para seguimiento de calidad.

## Matriz

| ID Caso | Requisito | Escenario | Pasos (resumen) | Resultado esperado | Resultado obtenido | Estado |
|---|---|---|---|---|---|---|
| QA-001 | REQ-ADMIN-001 | Crear bloque en editor visual | Crear bloque, guardar, recargar vista | Bloque persiste y renderiza correctamente | Pendiente | Pendiente |
| QA-002 | REQ-ADMIN-001 | Editar bloque existente | Modificar contenido y guardar | Cambios visibles en preview y vista final | Pendiente | Pendiente |
| QA-003 | REQ-STYLE-001 | Aplicar tema global | Cambiar tokens de color/tipografía | UI actualiza estilos sin romper layout | Pendiente | Pendiente |
| QA-004 | REQ-CONTENT-001 | Edición inline contextual | Editar contenido en vista y publicar | Contenido se guarda y publica correctamente | Pendiente | Pendiente |

## Criterios de aceptación QA

- Cobertura mínima de casos críticos del flujo admin/editor/theme.
- Sin defectos bloqueantes abiertos para salida de MVP.
- Evidencia por caso: fecha, responsable y referencia de ejecución.

## Enlaces relacionados

- [[00-index]]
- [[01-qa/estado-mvp]]
- [[01-qa/hallazgos]]
- [[03-tecnico/api-theme-y-content]]
