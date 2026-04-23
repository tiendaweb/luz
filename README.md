# Foro LATAM 2026 PSME — Estado actual del proyecto

Este README describe **el estado real de la base actual** (frontend + backend PHP + SQLite + APIs) y separa qué está implementado, qué está parcial/simulado y qué falta construir.

---

## Estado actual (implementado hoy)

### Frontend
- Vistas y páginas HTML existentes para flujo público y paneles (`index.html`, `foros.html`, `contacto.html`, `login.html`, dashboards).
- JS por dominio para autenticación, foros e inscripciones (`assets/js/auth.js`, `assets/js/forums.js`, `assets/js/registrations.js`).
- Estilos globales en `styles.css`.

### Backend PHP + SQLite
- Router principal en `public/index.php`.
- Conexión SQLite vía PDO en `app/Database/connection.php`.
- Migraciones SQL y seeds PHP para crear/llenar datos demo.
- Servicios backend para autenticación e inscripciones en `app/Services/*`.

### APIs disponibles (núcleo)
- Auth: login/logout/me.
- Foros: listado, próximo foro, detalle.
- Inscripciones: creación y consulta.
- Dashboard/resúmenes y endpoints por rol (admin/asociado/usuario).

---

## Demo / simulado (actualmente)

- El proyecto corre con **datos demo** y credenciales de prueba.
- El contenido funcional depende de seeds locales (no hay entorno productivo configurado aquí).
- No hay integración de pagos reales ni pasarela transaccional end-to-end.
- No hay canal de emails/notificaciones productivas documentado en esta base.

---

## Pendiente por construir

- Endurecimiento para producción (secretos, hardening, observabilidad, backups y despliegue).
- Integraciones externas reales (pagos, mensajería transaccional, etc.).
- Blog funcional completo (no hay módulo de blog operativo en esta base actual).
- Evolución de páginas personalizadas con editor/gestión dinámica (si se requiere CMS).

---

## Arquitectura actual

Rutas/áreas clave del código:

- **Router HTTP principal:** `public/index.php`
- **Endpoints API:** `public/api/*`
- **Migraciones SQL:** `database/migrations/*`
- **Seeds de datos demo:** `database/seeds/*`
- **Conexión DB (DSN SQLite):** `app/Database/connection.php`

Notas:
- El DSN está configurado a un archivo SQLite local (`data/app.sqlite`).
- El router permite resolver rutas `api/*` y servir vistas/activos.

---

## Flujo mínimo de arranque local

### 1) Ejecutar migraciones + seeds

```bash
php scripts/seed.php
```

Este comando aplica migraciones y carga datos demo (usuarios, foros y datos relacionados).

### 2) Credenciales demo vigentes

> Uso exclusivo en entorno local/demo.

- **Admin**: `admin@psme.local` / `Admin123*`
- **Asociado**: `asociado@psme.local` / `Asociado123*`
- **Usuario**: `usuario@psme.local` / `Usuario123*`

### 3) Rutas principales de UI/API

#### UI
- `/` (home)
- `/foros.html`
- `/login.html`
- `/dashboard-admin.html`
- `/dashboard-asociado.html`
- `/dashboard-usuario.html`

#### API
- `/api/auth/login`
- `/api/auth/logout`
- `/api/auth/me`
- `/api/forums/list`
- `/api/forums/next`
- `/api/forums/detail`
- `/api/registrations/create`
- `/api/registrations/me`
- `/api/admin/registrations`
- `/api/associate/registrations`

---


## Convención de slugs y rutas para páginas personalizadas

### Slug (campo `custom_pages.slug`)
- Formato obligatorio: `^[a-z0-9]+(?:-[a-z0-9]+)*$`
- Reglas:
  - Sólo minúsculas, números y guiones medios (`-`).
  - No se permiten espacios, tildes ni guiones al inicio/fin.
  - Debe ser único por página.
- Ejemplos válidos: `quienes-somos`, `agenda-2026`, `preguntas-frecuentes`.

### Rutas de acceso
- **API admin CRUD** (requiere sesión `admin`):
  - `GET /public/api/admin/pages/list.php`
  - `GET /public/api/admin/pages/show.php?id={id}`
  - `POST /public/api/admin/pages/create.php`
  - `PATCH /public/api/admin/pages/update.php`
  - `DELETE /public/api/admin/pages/delete.php?id={id}`
- **API pública** (solo publicadas):
  - `GET /public/api/pages/show.php?slug={slug}`
- **Render web público** (solo publicadas):
  - `GET /p/{slug}`

### Seguridad de `content_html`
- El HTML se sanitiza del lado servidor antes de persistir.
- Se aplica lista blanca de etiquetas/atributos permitidos.
- Se eliminan etiquetas y atributos inseguros (`script`, `iframe`, `on*`, `style`, URLs no seguras).

## Matriz de estado por módulo

| Módulo | Implementado | Parcial | Pendiente | Comentario breve |
|---|---:|---:|---:|---|
| Auth | ✅ |  |  | Login/logout/me con persistencia SQLite y sesión. |
| Foros | ✅ |  |  | Listado, próximo y detalle vía API + UI de foros. |
| Inscripciones | ✅ |  |  | Alta de inscripción y estados base con datos persistidos. |
| Admin |  | ✅ |  | Gestión principal disponible; faltan capacidades avanzadas productivas. |
| Blog |  |  | ✅ | No existe módulo blog funcional en esta base actual. |
| Páginas personalizadas |  | ✅ |  | Hay páginas estáticas; falta capa dinámica tipo CMS/editor. |

---

## Contexto del proyecto (histórico)

El objetivo inicial del proyecto fue una maqueta funcional para presentar la propuesta del **Foro LATAM 2026 PSME** (con foco en experiencia de navegación, propuesta de valor, calendario y conversión de inscripciones).

Ese objetivo de maqueta fue el punto de partida histórico. El estado actual ya incorpora backend PHP, SQLite, migraciones/seeds y endpoints API en funcionamiento para entorno local/demo.
