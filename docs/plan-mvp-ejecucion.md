# Plan de ejecución MVP

## Tabla base de planificación

| Tarea | Dependencia | Responsable | Esfuerzo | Modo (Síncrono/Asíncrono) | Criterio de aceptación |
|---|---|---|---|---|---|
| Definir modelo de datos (foros/pagos/estados/asistencia) | Ninguna | Backend + Arquitectura | Alto | Síncrono | ERD versionado, migraciones listas y revisadas; cubre foros, pagos, estados de asociado y asistencia. |
| Implementar autorización por rol | Modelo de datos definido | Backend + Seguridad | Alto | Síncrono | Matriz rol-permiso implementada en API; endpoints críticos con pruebas de acceso permitido/denegado. |
| Definir contratos API (registro, estados, asistencia, beneficios) | Modelo de datos definido | Backend + Frontend | Medio | Síncrono | OpenAPI/contratos publicados y validados; consumidores FE integrados sin ambigüedades de campos. |
| Producir contenido editorial de foros | Contrato API de foros | Contenido + Producto | Medio | Asíncrono | Contenido final aprobado para foros (copys, descripciones, metadatos) y cargado en formato acordado. |
| Cargar invitados y textos de landing | Contrato API de foros | Marketing + Contenido + Frontend | Medio | Asíncrono | Invitados, bios e imágenes cargados; textos de landing validados por negocio y visibles en staging. |
| Ejecutar QA visual y copy de mensajes UX | Vistas FE implementadas + textos cargados | Diseño + QA + Frontend | Medio | Asíncrono | Checklist visual/copy sin bloqueantes P0/P1; ajustes aplicados y confirmados en regresión. |

## Hitos y entregables mínimos

| Hito | Entregable mínimo | Dependencias críticas | Evidencia de cierre |
|---|---|---|---|
| H1 pagos+referidos | Flujo de pago funcional con trazabilidad de referido en backend y frontend. | Modelo de datos + contratos API + autorización por rol | Demo E2E en staging, logs de transacción y casos de prueba de referido. |
| H2 aprobación asociado | Flujo de aprobación/rechazo de asociado con cambio de estado auditado. | H1 + contratos de estados + permisos por rol | Historial de estados visible, auditoría registrada y pruebas de transición válidas/ inválidas. |
| H3 eBooks/certificados protegidos | Descarga/visualización de beneficios restringida por estado y rol. | H2 + contratos de beneficios + autorización | Pruebas de acceso autorizado/no autorizado y evidencia de protección de recursos. |
| H4 countdown+detalle foros | Landing con countdown activo y detalle de foros publicado con invitados. | Contenido editorial + carga de invitados/textos + FE integrado | Capturas en staging, QA visual aprobado y enlaces navegables funcionando. |
| H5 hardening + QA final | Estabilización final con regresión integral y documentación de salida. | H1-H4 completos | Reporte final QA sin bloqueantes, checklist de hardening completado y acta de release. |

## Definition of Done por módulo

### Backend API
- Endpoints de registro, estados, asistencia y beneficios implementados según contrato versionado.
- Validaciones de negocio y códigos de error documentados y cubiertos por pruebas automatizadas.
- Migraciones y seeds ejecutables en entorno limpio sin intervención manual.
- **Criterios verificables:**
  - Suite de tests de API en verde.
  - OpenAPI actualizado y publicado.
  - Logs de observabilidad para operaciones críticas (pagos, cambios de estado).
- **Evidencia esperada para cierre:**
  - Reporte de tests y cobertura.
  - Enlace/archivo del contrato API firmado por backend/frontend.
  - Registro de despliegue exitoso en staging.

### Frontend vistas
- Vistas MVP implementadas: registro, estados de asociado, asistencia, beneficios, landing con countdown y detalle de foros.
- Manejo de estados de carga, vacíos y error con mensajes UX definidos.
- Integración con contratos API sin uso de campos no documentados.
- **Criterios verificables:**
  - Pruebas de integración/UI en verde para flujos críticos.
  - Cumplimiento responsive en breakpoints acordados.
  - Accesibilidad base (labels, focus, contraste) validada.
- **Evidencia esperada para cierre:**
  - Capturas o video corto por flujo crítico en staging.
  - Checklist UX/UI firmado por Diseño y Producto.
  - Reporte de pruebas E2E de frontend.

### Seguridad/autorización
- Matriz de roles y permisos definida, implementada y aplicada en backend y frontend.
- Recursos protegidos (eBooks/certificados) inaccesibles sin permisos o estado habilitante.
- Eventos sensibles auditados (inicio de sesión, pagos, cambio de estado, accesos denegados).
- **Criterios verificables:**
  - Casos de prueba positivos/negativos por rol ejecutados.
  - No existen rutas críticas sin control de autorización.
  - Hallazgos críticos de seguridad en cero.
- **Evidencia esperada para cierre:**
  - Matriz rol-permiso versionada.
  - Evidencia de tests de autorización.
  - Resultado de revisión de seguridad/hardening.

### QA
- Plan de pruebas funcionales, regresión y visual ejecutado para alcance MVP.
- Defectos P0/P1 cerrados; P2/P3 con plan explícito de tratamiento.
- Validación cross-browser/dispositivo para rutas de negocio prioritarias.
- **Criterios verificables:**
  - Ejecución completa de casos críticos con resultado aprobado.
  - Regresión completa sin bloqueantes de release.
  - Trazabilidad caso ↔ incidente ↔ corrección actualizada.
- **Evidencia esperada para cierre:**
  - Reporte QA final con severidades.
  - Bitácora de incidencias y estado.
  - Acta de go/no-go firmada.

### Documentación
- Documentación funcional y técnica del MVP actualizada (flujos, supuestos, límites y riesgos).
- Checklist de operación y soporte post-release disponible.
- Notas de versión con cambios, impactos y rollback.
- **Criterios verificables:**
  - Documentos accesibles en repositorio/wiki con fecha y responsable.
  - Versionado coherente con el release candidato.
  - Aprobación explícita de líderes de Backend, Frontend, QA y Producto.
- **Evidencia esperada para cierre:**
  - Enlaces a documentos finales.
  - Registro de aprobaciones.
  - Release notes publicadas.
