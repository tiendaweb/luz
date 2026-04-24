# Workflow de pagos y aprobación de inscripciones

## Estados oficiales

- `pending`: inscripción creada y en revisión.
- `approved`: inscripción validada por admin/asociado.
- `rejected`: inscripción rechazada con motivo obligatorio.

## Reglas de negocio

1. **Alta inicial**
   - Toda inscripción nueva crea estado `pending`.
   - Se registra un evento en `registration_status_history` con transición `null -> pending` y actor `system`.

2. **Transiciones permitidas (PATCH admin/asociado)**
   - Estados válidos de destino: `pending`, `approved`, `rejected`.
   - `rejected` requiere **nota obligatoria** (no vacía).
   - `approved` permite nota opcional.

3. **Validación de comprobante para aprobar**
   - Para pasar a `approved`, se exige comprobante (`payment_proof_base64`) si:
     - `needs_cert = 1`, o
     - la inscripción tiene flujo de pago configurado por referido (`payment_method`, `payment_link` o `price_amount > 0`).

4. **Auditoría de estado**
   - Cada cambio real de estado (`from_status != to_status`) se agrega a `registration_status_history`.
   - Se guarda: estado origen, estado destino, nota, rol/usuario revisor y fecha (`created_at`).

5. **Visualización en dashboard**
   - Admin y asociado ven línea de tiempo con los eventos más recientes del historial por inscripción.

## Casos límite

### Reapertura de inscripción

- `rejected -> pending` o `approved -> pending` está permitido para reabrir revisión.
- La nota es opcional, pero recomendada para trazabilidad.

### Corrección de comprobante

- Si una inscripción fue rechazada por comprobante inválido/faltante, puede subir/completar comprobante y volver a `pending`.
- Solo después de contar con comprobante válido (si aplica la regla) podrá pasar a `approved`.

### Rechazo sin nota

- Debe fallar con error de validación (`422`) y no modificar estado.

### Aprobación sin comprobante cuando aplica

- Debe fallar con error de validación (`422`) y no modificar estado.

### Cambio al mismo estado

- Se actualiza estado actual, pero no se agrega evento nuevo de historial porque no hay transición efectiva.
