# MVP roles, pagos y ajustes dinámicos

## Objetivo
Definir el alcance funcional mínimo del MVP para operar registros con flujo de referido/pago por país, controles por rol y ajustes dinámicos sin redeploy.

## 1) Alcance funcional por rol

### Admin
- Gestionar configuración global y por país (moneda, precio, método de pago, links, validaciones de comprobante).
- Aprobar/rechazar inscripciones de cualquier red/asociado.
- Forzar reapertura de casos (`approved/rejected -> pending`) con auditoría.
- Gestionar catálogo de foros, reglas de elegibilidad y ventanas de inscripción.
- Consultar reportes consolidados de pagos/estado por país y por red.

### Asociado
- Revisar y decidir inscripciones de su red/ámbito asignado.
- Cargar observaciones de validación de comprobante.
- Visualizar métricas operativas de su red (pendientes, aprobados, rechazados, tiempo de resolución).
- No puede editar settings globales ni de otros países/redes fuera de su scope.

### Usuario final
- Registrarse y editar datos permitidos mientras el estado sea `pending`.
- Adjuntar comprobante cuando la regla de país/red lo exija.
- Consultar estado de su inscripción e historial resumido de decisiones.
- Acceder a beneficios (eBooks/certificados) solo cuando cumpla estado habilitante.

### Operación/Soporte (si aplica en MVP)
- Ejecutar live edit de textos operativos y datos de contacto.
- Activar/desactivar flags no críticos (mensajes, ayudas contextuales, banner informativo).
- Escalar incidencias de reglas de pago/permiso a Admin.

## 2) Flujo de referido/pago por país

## Paso a paso estándar
1. Usuario entra con parámetro de referido (`ref_code`) o selección manual de red.
2. Sistema resuelve país + red + campaña vigente y calcula regla de pago efectiva.
3. Se presenta instrucción de pago local:
   - método (transferencia/link/pasarela externa),
   - moneda e importe,
   - datos de referencia obligatorios.
4. Usuario adjunta comprobante cuando aplique.
5. Registro queda en `pending` con trazabilidad de referido y regla aplicada (snapshot).
6. Asociado/Admin valida y decide (`approved`/`rejected`).

## Reglas mínimas por país (MVP)
- **País con pago obligatorio**: requiere comprobante válido para aprobar.
- **País con pago opcional/promocional**: puede aprobar sin comprobante si la regla efectiva marca monto `0`.
- **País sin cobro MVP**: registro sin instrucción de pago; no debe bloquear aprobación.
- Toda decisión debe guardar snapshot de reglas para auditoría (evita recalcular con settings posteriores).

## Datos a persistir por transacción
- `country_code`, `network_id`, `ref_code` (si existe).
- `pricing_rule_id` y versión de settings aplicada.
- `price_amount`, `currency`, `payment_method`, `payment_link`.
- `payment_proof_status` (missing/received/validated/rejected).

## 3) Matriz de permisos (resumen)

| Acción | Admin | Asociado | Usuario | Soporte |
|---|---|---|---|---|
| Ver inscripción propia | ✅ | ✅ (scope red) | ✅ (solo propia) | ✅ (solo lectura) |
| Cambiar estado inscripción | ✅ | ✅ (scope red) | ❌ | ❌ |
| Rechazar sin nota | ❌ | ❌ | ❌ | ❌ |
| Configurar reglas de pago por país | ✅ | ❌ | ❌ | ❌ |
| Configurar reglas por red | ✅ | ❌ | ❌ | ❌ |
| Live edit de textos operativos | ✅ | ❌ | ❌ | ✅ |
| Ver reportes globales | ✅ | ❌ | ❌ | ❌ |
| Ver reportes de red | ✅ | ✅ | ❌ | ✅ (lectura) |
| Descargar beneficios protegidos | ✅ | ✅ (según política) | ✅ (si estado habilita) | ❌ |

## 4) Riesgos y supuestos

## Riesgos
- Desalineación entre reglas por país y copy visible al usuario (genera rechazos evitables).
- Cambios de settings durante una revisión activa sin versionado (rompe trazabilidad).
- Sobrecarga operativa en asociados si picos de `pending` no tienen SLA ni cola priorizada.
- Reglas regulatorias locales no contempladas (comprobantes/facturación/retenciones).

## Supuestos
- Existe catálogo inicial de países/redes con owner de negocio.
- La matriz de permisos se implementa tanto en backend como en frontend.
- Live edit se limita a campos permitidos (sin ejecutar scripts ni modificar lógica).
- Se dispone de telemetría mínima para medir rechazos por motivo y tiempos de aprobación.

## 5) Plan de ejecución token-eficiente

## Estrategia de lotes pequeños
- **Lote 1 (roles + permisos)**: cerrar matriz y endpoints de autorización.
- **Lote 2 (pagos por país/red)**: activar reglas efectivas + snapshot de configuración.
- **Lote 3 (settings dinámicos + live edit)**: publicar panel y validaciones de seguridad.
- **Lote 4 (hardening)**: trazabilidad, métricas operativas y cierre de huecos QA.

## Validación por módulo
- Cada lote se valida con checklist acotado de:
  1) reglas de negocio,
  2) autorización,
  3) auditoría de eventos,
  4) UX de error.
- Se promueve “merge pequeño” con rollback simple por feature flag.

## Pruebas de humo mínimas por API
- Registro: crea `pending` con snapshot correcto de país/red.
- Cambio de estado: valida transición y nota obligatoria en `rejected`.
- Pago: impide `approved` sin comprobante cuando la regla lo exige.
- Settings: cambios dinámicos impactan solo nuevos registros, no históricos.
- Beneficios: acceso bloqueado/permitido según rol + estado.
