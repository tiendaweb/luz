# Arquitectura — Contenido Inline

## Objetivo

Describir la arquitectura propuesta para edición y renderizado de contenido inline.

## Componentes

- **Capa UI**: Editor en contexto y renderizador.
- **Capa de aplicación**: Orquestación de guardado/publicación.
- **Capa API**: Endpoints de contenido y versionado.
- **Persistencia**: Modelo de bloques/versiones.

## Flujo de alto nivel

1. Usuario edita contenido en contexto.
2. UI genera payload estructurado por bloques.
3. API valida y persiste borrador/version.
4. Publicación promueve versión activa.
5. Renderizador consume versión publicada.

## Decisiones técnicas abiertas

- Esquema de versionado (snapshot vs diff).
- Política de bloqueo concurrente (optimista vs pesimista).
- Estrategia de cache invalidadas por publicación.

## Enlaces relacionados

- [[00-index]]
- [[03-tecnico/api-theme-y-content]]
- [[01-qa/hallazgos]]
