# Workflow: eBooks por foro (admin)

## Objetivo
Permitir que el equipo admin gestione un catálogo de eBooks y su asignación a foros específicos.

## Endpoints admin

### eBooks
- `GET /api/admin/ebooks/list`
- `POST /api/admin/ebooks/create`
- `PATCH /api/admin/ebooks/update`
- `DELETE /api/admin/ebooks/delete?id={id}`

Campos relevantes:
- `title`, `description`, `status`
- `provider`: `local` o `external`
- `min_attendance`: umbral de asistencia (0-100)
- `requires_approved`: si requiere estado aprobado (o umbral de asistencia)
- `local_path` (obligatorio en `provider=local`)
- `external_url` HTTPS válida (obligatorio en `provider=external`)

### Asignación foro↔ebook
- `GET /api/admin/forum-ebooks/list`
- `POST /api/admin/forum-ebooks/create`
- `PATCH /api/admin/forum-ebooks/update`
- `DELETE /api/admin/forum-ebooks/delete?id={id}`

## Reglas de proveedor

### provider = local
- El archivo debe existir en `storage/ebooks/`.
- Se valida que el path resuelto esté dentro del directorio permitido.

### provider = external
- Se valida URL HTTPS válida (`https://...`).

## Flujo en Dashboard Admin
1. Abrir tab **eBooks por Foro**.
2. Completar formulario (título, descripción, proveedor, umbral, estado).
3. Seleccionar uno o más foros para asignar el eBook.
4. Guardar.
5. Editar/eliminar desde el listado inferior.

## Seed/migración incluida
Se agregó la migración `015_seed_forum_ebooks_examples.sql` con dos ejemplos:
- **Guía práctica del Foro de la mañana** → foro `morning`
- **Workbook del Foro de la tarde** → foro `afternoon`

Además se agregaron archivos de ejemplo en:
- `storage/ebooks/guia-practica-foro-manana.pdf`
- `storage/ebooks/workbook-foro-tarde.pdf`
