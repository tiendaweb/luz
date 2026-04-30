# Workflow de pagos y aprobación de inscripciones

## Estados oficiales

- `pending`: inscripción creada y en revisión.
- `approved`: inscripción validada por admin/asociado.
- `rejected`: inscripción rechazada con motivo obligatorio.

## Reglas de negocio

1. **Alta inicial**
   - Toda inscripción nueva crea estado `pending`.
   - Se registra un evento en `registration_status_history` con transición `null -> pending` y actor `system`.
   - Se guarda snapshot de settings efectivos: país, red, moneda, monto y método de pago.

2. **Aprobaciones por rol y alcance (asociado/red)**
   - **Admin** puede aprobar/rechazar cualquier inscripción.
   - **Asociado** solo puede aprobar/rechazar inscripciones de su red asignada.
   - Si el asociado intenta operar fuera de su red, la API responde `403` y audita intento denegado.

3. **Transiciones permitidas (PATCH admin/asociado)**
   - Estados válidos de destino: `pending`, `approved`, `rejected`.
   - `rejected` requiere **nota obligatoria** (no vacía).
   - `approved` permite nota opcional.

4. **Validación de comprobante para aprobar (reglas por país/red)**
   - Para pasar a `approved`, se exige comprobante (`payment_proof_base64`) cuando la regla efectiva del país/red lo indique.
   - Se considera que exige comprobante si:
     - `needs_cert = 1`, o
     - `price_amount > 0`, o
     - existe `payment_method`/`payment_link` activo para esa combinación país+red.
   - Si la regla efectiva define monto `0` y sin comprobante requerido, puede aprobarse sin adjunto.

5. **Reglas por país (MVP mínimo)**
   - Países con cobro: requieren método/importe/moneda y comprobante para aprobar.
   - Países promocionales: permiten monto `0` por campaña vigente y aprobación sin comprobante.
   - Países sin cobro MVP: no muestran instrucción de pago y no deben bloquear aprobación.

6. **Auditoría de estado y configuración aplicada**
   - Cada cambio real de estado (`from_status != to_status`) se agrega a `registration_status_history`.
   - Se guarda: estado origen, estado destino, nota, rol/usuario revisor y fecha (`created_at`).
   - Se referencia versión de settings aplicada al momento de la decisión para trazabilidad.

7. **Visualización en dashboard**
   - Admin y asociado ven línea de tiempo con los eventos más recientes del historial por inscripción.
   - Asociado solo visualiza casos de su red.

## Casos límite

## Compatibilidad con flags de usuario (`user_admin_flags`)

Para la gestión administrativa de usuarios se agregan flags explícitos por usuario:

- `is_validated`
- `is_paid`
- `updated_at`
- `updated_by_user_id`

### Regla de precedencia

1. **Si existe registro en `user_admin_flags` para el usuario**, los flags explícitos (`is_validated`, `is_paid`) son la fuente de verdad en el dashboard admin.
2. **Si no existe registro explícito**, se usa compatibilidad hacia atrás con `registration_admin_state`:
   - `legacy_is_validated = true` cuando el usuario tiene al menos una inscripción en estado `approved`.
   - `legacy_is_paid = true` cuando tiene al menos una inscripción en `payment_submitted` o `approved`.
3. Al guardar desde el tab de usuarios, siempre se crea/actualiza `user_admin_flags`, y desde ese momento prevalece el valor explícito.

### Reapertura de inscripción

- `rejected -> pending` o `approved -> pending` está permitido para reabrir revisión.
- La nota es opcional, pero recomendada para trazabilidad.

### Corrección de comprobante

- Si una inscripción fue rechazada por comprobante inválido/faltante, puede subir/completar comprobante y volver a `pending`.
- Solo después de contar con comprobante válido (si aplica la regla del país/red) podrá pasar a `approved`.

### Rechazo sin nota

- Debe fallar con error de validación (`422`) y no modificar estado.

### Aprobación sin comprobante cuando aplica

- Debe fallar con error de validación (`422`) y no modificar estado.

### Cambio al mismo estado

- Se actualiza estado actual, pero no se agrega evento nuevo de historial porque no hay transición efectiva.

### Cambio de settings durante revisión

- Los cambios de settings no alteran retroactivamente el snapshot de inscripciones existentes.
- El cambio aplica para nuevas inscripciones o para reaperturas explícitas con recálculo.
