# Estado MVP por módulo

## Resumen ejecutivo

Documento para registrar el estado real del MVP por módulo, con foco en brechas entre alcance planificado y estado implementado.

## Estado por módulo

| Módulo | Alcance MVP | Estado actual | Evidencia | Bloqueadores | Próximo paso |
|---|---|---|---|---|---|
| Admin / Editor visual | CRUD de bloques + preview | Por validar | N/A | Definir contrato API final | Ejecutar pruebas de flujo E2E |
| Estilos / Theme | Tokens base + aplicación global | Por validar | N/A | Definir fallback de tema | Alinear especificación de estilos |
| Contenido inline | Edición y guardado contextual | Por validar | N/A | Definir estrategia de versionado | Validar experiencia en editor |
| Publicación | Borrador/Publicado | Por validar | N/A | Reglas de permisos | Definir flujo de aprobación |

## Riesgos de alcance

- Dependencia de contratos API no cerrados.
- Posibles inconsistencias entre editor visual y render final.
- Riesgo de deuda técnica si no se formaliza versionado de contenido.

## Enlaces relacionados

- [[00-index]]
- [[01-qa/matriz-pruebas]]
- [[01-qa/hallazgos]]
- [[02-producto/requerimientos-admin-editor-visual]]
- [[03-tecnico/arquitectura-contenido-inline]]
