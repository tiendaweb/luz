# Foros PSME — Documentación para Claude

Una plataforma SPA (Single Page Application) en PHP/SQLite para gestionar foros internacionales de salud mental con múltiples roles de usuario.

## Inicio rápido

### Credenciales demo

Las 3 cuentas demo están pre-cargadas en la BD. Úsalas visitando `/login`:

| Rol | Email | Contraseña |
|---|---|---|
| Admin (Luz) | `admin@psme.local` | `Admin123*` |
| Asociado | `asociado@psme.local` | `Asociado123*` |
| Usuario | `usuario@psme.local` | `Usuario123*` |

### URLs principales

- **`/`** — Home y SPA principal (todas las vistas)
- **`/login`** — Pantalla de login con 3 role cards visuales
- **`/foros`** → redirige a `/#view-forums`
- **`/contacto`** → redirige a `/#view-about`
- **`/api/*`** — Endpoints RESTful (sin extensión `.php`)

## Arquitectura

### Estructura de directorios

```
/public/index.php              Router HTTP principal
/app/
  /Database/connection.php      PDO SQLite
  /Services/*                   Lógica reutilizable
  /Views/
    /home.php                   SPA principal (todas las secciones)
    /login.php                  Página de login
    /partials/*                 Componentes (navbar, footer, modals)
    /layouts/*                  Wrappers HTML (auth, redirect)
/assets/js/                    JS modular por dominio
/database/
  /migrations/                  SQL schemas (idempotentes)
  /seeds/                       Datos demo
/docs/                          Documentación
```

### Router HTTP (`public/index.php`)

Despacha en orden:
1. **API routes** — exacta coincidencia en array `$routes` (→ `/api/*` sin `.php`)
2. **Custom pages** — regex `p/{slug}` → renderiza desde `custom_pages` DB
3. **SPA redirects** — `/foros`, `/contacto`, `/dashboard-*` → redirige al hash correspondiente
4. **Page routes** — `/`, `/login` → requieren archivos PHP en raíz
5. **Assets estáticos** — archivos CSS, JS, SVG, etc.
6. **404 JSON** — para todo lo demás

### SPA y vistas

La app es un SPA con un único HTML (`app/Views/home.php`) que contiene todas las secciones:
- `#view-home` — Hero + metodología + testimonios + FAQ
- `#view-forums` — Calendario y listado de foros
- `#view-about` — Bio de Luz Genovese + formulario de contacto
- `#view-blog` — Blog público (artículos)
- `#view-dashboard` — Panel de usuario con navegación por rol

**Navegación**: hash-based (`#view-NAME`) manejada en JS. No recarga de página.

### Roles y permisos

Base de datos (`roles` table) + sesión PHP. Cada rol rige qué componentes `[data-active-role="X"]` se muestran:

| Rol | Slug | Vistas permitidas | API acceso |
|---|---|---|---|
| Guest (visitante) | `guest` | home, forums, about, blog, login | ninguno |
| Usuario (inscripto) | `user` | todas + dashboard (mi área) | registrations, ebooks |
| Asociado (coordinador) | `associate` | todas + dashboard (referidos) | associate/* |
| Admin (Luz) | `admin` | todas + dashboard (gestión) | admin/* |

**Control en frontend**: CSS `[data-active-role="admin"] .admin-only { display: block !important; }`

**Control en backend**: Session check + CSRF token en cada mutación.

## APIs

### Autenticación

| Endpoint | Método | Descripción |
|---|---|---|
| `/api/auth/login` | POST | `{username, email, password}` → `{ok, user, csrfToken, sessionExpiresAt}` |
| `/api/auth/logout` | POST | Destruye sesión |
| `/api/auth/me` | GET | Retorna usuario actual + CSRF token |

### Foros

| Endpoint | Método | Parámetros |
|---|---|---|
| `/api/forums/list` | GET | `?page=1&limit=4` → array de foros |
| `/api/forums/next` | GET | Próximo foro a iniciar |
| `/api/forums/detail` | GET | `?forum=CODE` → detalle completo |

### Inscripciones

| Endpoint | Método | Descripción |
|---|---|---|
| `/api/registrations/create` | POST | Nueva inscripción con firma canvas + proof |
| `/api/registrations/me` | GET | Mis inscripciones |

### Admin

| Endpoint | Método | Nota |
|---|---|---|
| `/api/admin/registrations` | GET/POST/DELETE | CRUD de inscripciones |
| `/api/admin/associates` | GET | Listado de asociados |
| `/api/admin/pages/*` | GET/POST/PATCH/DELETE | CRUD de páginas estáticas |
| `/api/admin/blog/*` | GET/POST/PATCH/DELETE | CRUD de artículos blog |

### Asociado

| Endpoint | Método | Descripción |
|---|---|---|
| `/api/associate/offer` | GET/POST | Código referido + config de precio |
| `/api/associate/registrations` | GET/PATCH | Ver/aprobar inscripciones referidas |

### Usuario

| Endpoint | Método | Descripción |
|---|---|---|
| `/api/user/ebooks` | GET | Lista de ebooks desbloqueados |

## Base de datos

SQLite en `/workspace/luz/data/app.sqlite` (HARDCODED en `app/Database/connection.php`).

### Tablas principales

- `users` — email, password_hash, role_id
- `roles` — admin, user, associate, guest
- `forums` — código, título, plataforma, fechas, seats
- `registrations` — usuario → foro, con estado workflow (pending/approved/rejected)
- `custom_pages` — slug, title, content_html, seo_*, status
- `blog_posts` — slug, title, excerpt, content_html, status

Migraciones idempotentes en `database/migrations/*.sql`. Auto-ejecutadas en primer request.

## Convenciones

### URLs limpias

- **SPA links**: `/#view-forums`, `/#view-dashboard` (no recargan)
- **HTTP links**: `/foros` (redirige a hash), `/` (home), `/login`
- **APIs**: `/api/auth/login` (sin `.php`)

### Seguridad

- **Sesiones**: regeneración post-login, TTL configurable, session_id() basado
- **CSRF**: token en header `X-CSRF-Token` para mutaciones
- **HTML**: sanitización de `content_html` en páginas/blog antes de persistir
- **Roles**: validación en backend antes de cada operación sensible

### JS estructura

```
assets/js/
  auth.js          — login/logout/roles, applyRoleUI()
  login.js         — form handler, credenciales demo
  navigation.js    — hash routing, showView(), toggleMobileMenu()
  forums.js        — listado, detalle, countdown
  registrations.js — modal inscripción, firma canvas, upload proof
  pages-admin.js   — CRUD de páginas
```

Todos cargan en la SPA. Globales: `window.appApiFetch`, `window.setRole`, `window.logout`, `window.showView`.

## Flujos clave

### Login

1. Visitante llena email/contraseña en `/login`
2. JS envía POST `/api/auth/login`
3. Backend verifica credenciales, regenera sesión, retorna user + csrfToken
4. JS redirige a `/#view-dashboard`
5. En home.php, `auth.js` en DOMContentLoaded llama `GET /api/auth/me` y aplica rol

### Inscripción a foro

1. Usuario hace clic "Inscribirse" → abre modal `#registerModal`
2. Selecciona foro, ingresa DNI, dibuja firma en canvas
3. Si requiere certificado: sube comprobante de pago (PDF/JPG/PNG/WEBP)
4. Modal valida: firma no vacía, certificado si requiere
5. POST `/api/registrations/create` con firma como data URL + archivo base64
6. Backend persiste en DB, crea audit trail
7. Mensaje de éxito, modal cierra

### Dashboard Admin — gestión de inscripciones

1. Admin inicia sesión como `admin@psme.local`
2. Dashboard muestra panel "Gestión Inscripciones"
3. Tabla lista todas las inscripciones con estados (pending/approved/rejected)
4. Admin puede cambiar estado + dejar nota
5. Cada cambio: POST/PATCH `/api/admin/registrations` + audit en `registration_status_history`

### Páginas personalizadas (`/p/{slug}`)

1. Admin crea página en dashboard: slug, título, HTML contenido, SEO
2. Contenido se sanitiza (whitelist de tags seguro)
3. Guarda en `custom_pages` con status=published
4. Visitante accede `/p/ejemplo-slug`
5. Router detecta regex, busca en DB, renderiza `app/Views/pages/show.php`

## Testing local

### Seeds

```bash
php scripts/seed.php
```

Auto-ejecuta migraciones + carga datos demo (usuarios, foros, etc.).

### Smoke test API

```bash
bash scripts/smoke-api.sh
```

Tests básicos de endpoints.

## Extensibilidad

### Agregar nueva sección a la SPA

1. Agregar `<section id="view-NAME" class="view-section">...</section>` a home.php
2. Agregar entrada a `$routes` en navigation.js
3. JS llama a `showView('NAME')` para mostrar

### Agregar nuevo endpoint API

1. Crear archivo en `public/api/*/endpoint.php`
2. Registrar en `public/index.php` array `$routes`
3. Usar `require_once __DIR__ . '/api/_bootstrap.php'` para sesión + CSRF

### Agregar nuevo rol

1. Insertar en `roles` table (migration)
2. Ajustar lógica en `auth.js` (normalizeRole)
3. Crear secciones con `[data-active-role="nuevo-rol"]`

## Conocidos / Limitaciones

- **DB path hardcoded**: `/workspace/luz/data/app.sqlite` (revisar conexión.php para dev local)
- **SQL Server**: SQLite local; sin HA ni replicación
- **Pagos reales**: Aún no integrados; solo simulados en registrations
- **Emails**: No hay transaccionales configuradas
- **Blog frontend**: Admin CRUD existe; frontend aún es placeholder

## Gotchas que ya rompieron el sitio (NO repetir)

Esta sección documenta bugs concretos que volvieron loops de login o formularios rotos. Cualquiera que toque estos puntos debe leer aquí primero.

### 1. `window.appApiFetch` retorna **JSON parseado**, no un `Response`

Definido en `assets/js/auth.js`. Internamente hace `fetch().then(r => r.json())` y, si falla, lanza. Por lo tanto:

```js
// ❌ MAL — esto causó un redirect loop a /login en dashboard.js
const res = await window.appApiFetch('/api/auth/me');
if (!res.ok) { window.location.href = '/login'; return; }
const data = await res.json();   // TypeError: res.json is not a function

// ✅ BIEN
const data = await window.appApiFetch('/api/auth/me');
if (!data?.authenticated) { window.location.href = '/login'; return; }
```

El payload ya trae `{ ok, authenticated, user, csrfToken }`. Si necesitás el status HTTP usá `fetch` directo, no `appApiFetch`.

### 2. Las páginas con dashboard DEBEN cargar `navigation.js`

`auth.js` toma el helper `normalizeRole` desde `window.__navigation` (definido en `navigation.js`). Si una página carga `auth.js` sin haber cargado antes `navigation.js`, todo rol se cae a `"guest"` y `applyRoleUI("guest")` redirige `/dashboard → /login`.

`auth.js` ahora tiene un fallback defensivo, pero la regla sigue: **toda página con UI de rol incluye `navigation.js` antes de `auth.js`**.

### 3. La API de foros responde `items`, no `forums`

`/api/forums/list` (`public/api/forums/list.php`) devuelve `{ ok, items, pagination }`. Si tu JS hace `result.forums` queda en `[]` y el `<select id="forumIdSelect">` muestra "No hay foros disponibles" — el usuario no puede inscribirse. Ya pasó en `assets/js/registrations.js#loadForumOptions`.

Convención general: revisar el archivo PHP del endpoint antes de asumir el shape — la mayoría usa `items`, no nombres específicos de dominio.

### 4. Variables de `_session.php` no son visibles dentro de `render_main_layout()`

`app/Views/_session.php` define `$_viewIsLoggedIn`, `$_viewCurrentUser`, etc. en el **scope del archivo que lo incluye**. Si la página lo incluye al tope (`pages/dashboard.php`), las variables existen en scope global de esa página. Pero `render_main_layout()` es una función, y al hacer `require __DIR__ . '/../partials/header.php'` desde adentro:

- `require_once _session.php` ya está marcado como cargado y no se re-incluye.
- Las variables del scope de la página NO entran al scope de la función.
- → `$_viewIsLoggedIn` queda undefined dentro de header.php / footer.php.

`partials/header.php` y `partials/footer.php` ahora leen `$_SESSION['auth_user']` directamente. Si vas a agregar otro partial que necesite estado de sesión, leé `$_SESSION` o pasalo explícitamente vía `$config` en el layout.

### 5. CSS `.role-only` con `!important` cruzado

`layouts/main.php` declara visibilidad por rol con `!important`. Al agregar reglas adicionales (ej. `display: flex` para sub-menús), **siempre prefijar con `body[data-active-role="X"]`**. Sin el prefijo, el `!important` del `display: flex` gana sobre el `display: none !important` base y muestra el menú a todos los roles.

### 6. Sub-menús del dashboard usan `*-only` para visibilidad

Los `<div id="user-menu">`, `#associate-menu`, `#admin-menu` deben llevar las clases `user-only`, `associate-only`, `admin-only` (combinadas con `space-y-2`). Eso deja la visibilidad bajo control de las reglas CSS por rol del layout.

Antes el código tenía `style="display:none"` inline + ninguna clase de rol; entonces dependía de un `dashboard.js` JS que ponía `style.display='block'` cuando llegaba `/api/auth/me`. Si esa request fallaba (caso del bug 1), el menú quedaba escondido para siempre. Ahora la visibilidad es declarativa vía atributo `data-active-role` en `<body>`.

## Links útiles

- Docs: `/docs/` (markdown)
- Plan MVP: `docs/plan-mvp-ejecucion.md`
- Operación: `docs/operacion-confiable.md`
- API tests: `docs/api-smoke-tests.md`

## Preguntas frecuentes para Claude

**P: ¿Cómo agrego un nuevo campo a las inscripciones?**
R: 1) Migration SQL 2) Actualizar seed 3) Formulario modal + JS POST 4) API endpoint que lo persiste

**P: ¿Dónde está el archivo HTML de diseño referencia?**
R: Archivado en `HTML/` (copia estática). El PHP operativo vive en `app/Views/`.

**P: ¿Cómo cambio el rol después de iniciar sesión?**
R: No es flujo normal. Para testing: click en role card en login nuevamente, o editar `window.__csrfToken` + `POST /api/auth/login` con nuevo rol.

**P: ¿Puedo agregar estilos CSS nuevos?**
R: Tailwind CDN se carga en layouts/auth.php. CSS custom inline en `<style>` dentro home.php. No hay compilación.

**P: ¿Por qué algunas URLs tienen .php y otras no?**
R: Rutas limpias `/api/..` se normalizan en el router. URLs antiguas con `.php` siguen funcionando por compatibilidad.
