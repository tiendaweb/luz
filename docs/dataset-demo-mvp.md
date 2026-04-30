# Dataset demo MVP

## Objetivo
Este dataset demo está orientado a QA funcional y demos comerciales del flujo completo: inscripción, revisión administrativa, referidos, beneficios y acceso a eBooks.

## Cobertura incluida

### 1) Usuarios y red de referidos
- Se incluyen usuarios demo adicionales:
  - `asociada.red@psme.local` (asociada)
  - `referido.aprobado@psme.local`
  - `referido.pendiente@psme.local`
  - `referido.rechazado@psme.local`
  - `usuario.directo@psme.local`
- La asociada queda vinculada por tabla `referrals` con 3 referidos en distinto estado.

### 2) Foros (3 configuraciones)
- `morning`: foro general profesional.
- `afternoon`: foro abierto formativo.
- `evening_premium`: foro premium nocturno con beneficios exclusivos y requisitos más estrictos.

### 3) Inscripciones y estados administrativos
Se cargan inscripciones con combinaciones relevantes para validar reglas:
- **approved** con comprobante y alta asistencia.
- **pending** sin comprobante.
- **rejected** con comprobante.
- **approved** sin comprobante (caso directo sin referido).

Cada inscripción incluye:
- `registration_admin_state`
- entrada en `registration_status_history`
- `registration_meta` con país y red
- `registration_attendance` para simular beneficios dependientes de asistencia.

### 4) Compras con y sin comprobante
- Casos con `payment_proof_*` completo (simulación de comprobante subido).
- Casos con comprobante nulo para validar pendientes/carga incompleta.

### 5) Certificados de presencia
- A usuarios aprobados con asistencia >= 75% se les crea certificado de tipo `attendance` en `user_certificates`.
- Esto habilita pruebas de listados y generación/descarga de certificados de presencia.

### 6) eBooks con acceso diferenciado por foro
Se precargan eBooks demo:
1. **Manual PSME Base**: disponible para todos los foros.
2. **Toolkit Intervención Avanzada**: disponible para `morning` y `evening_premium`, requiere aprobación + asistencia alta.
3. **Guía Premium de Coordinación Clínica**: exclusiva de `evening_premium`, con umbral mayor de asistencia.

Además, se vinculan por `forum_ebooks` para validar matrix foro-beneficio.

## Uso en QA
Checklist recomendado:
1. Iniciar sesión con usuarias/os de cada estado y validar tarjetas de estado.
2. Verificar diferencias en acceso de eBooks según:
   - estado admin (`pending/approved/rejected`)
   - porcentaje de asistencia
   - foro al que pertenece la inscripción
3. Validar trazabilidad de referidos en vistas de asociado/admin.
4. Confirmar existencia de certificados de presencia solo donde corresponda.

## Uso en demo comercial
Narrativa sugerida:
- Mostrar embudo de inscripción → revisión admin → habilitación de beneficios.
- Comparar un usuario aprobado vs pendiente/rechazado.
- Destacar que los beneficios (eBooks/certificados) pueden condicionarse por reglas de negocio (foro, estado, asistencia).

## Regeneración de datos
Ejecutar:

```bash
php scripts/seed.php
```

> El seed es idempotente para correos/códigos/títulos usados en demo MVP.
