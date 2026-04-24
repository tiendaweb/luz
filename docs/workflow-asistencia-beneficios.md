# Workflow de Asistencia y Habilitación de Beneficios

## Objetivo
Definir reglas medibles para habilitar beneficios (eBooks y certificados) en función del estado administrativo de la inscripción y la asistencia registrada por sesión.

## Reglas de negocio

1. **Inscripción válida para beneficios**
   - Debe existir una inscripción con `registration_admin_state.status = approved`.
   - Si el estado no está aprobado (`pending` o `rejected`), no se habilita ningún beneficio.

2. **Habilitación de eBooks**
   - Se habilitan cuando la inscripción está aprobada.
   - No dependen de `needs_cert`.

3. **Habilitación de certificados**
   - Requiere inscripción aprobada.
   - Requiere **asistencia mínima del 75%** sobre sesiones registradas en `forum_attendance`.
   - Se considera asistencia válida por sesión cuando `status IN ('present', 'partial')`.

## Modelo de asistencia

La tabla `forum_attendance` registra por sesión:
- `registration_id`
- `forum_id`
- `session_key` o `session_date` (al menos uno obligatorio)
- `status`: `present`, `absent`, `partial`
- `minutes_attended` (opcional)
- `recorded_by_user_id`, `recorded_at`, `notes`

## API de asistencia

- `GET /api/admin/attendance.php` y `GET /api/associate/attendance.php`
  - Lista asistencia por `forum_id` y/o `registration_id`.
- `POST|PATCH /api/admin/attendance.php` y `POST|PATCH /api/associate/attendance.php`
  - Registra o actualiza estado por sesión.

## Permisos

- **Admin**: acceso total.
- **Associate**: solo puede listar/registrar asistencia sobre inscripciones referidas propias (`registration_meta.referrer_user_id = associate_id`).

## Integridad de datos

- Índice único parcial en `registrations(user_id, forum_id)` cuando `user_id` y `forum_id` no son `NULL`.
- Validación previa al alta para evitar duplicados por usuario+foro.
- Resolución de `forum_id` real desde payload explícito (`forumId`) con fallback desde `forumSlot`.


## Gestión admin de eBooks por foro
Ver detalle operativo en `docs/workflow-ebooks-por-foro.md`.
