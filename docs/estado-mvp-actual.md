# Estado MVP actual (puente funcional-técnico)

Fecha de actualización: 2026-04-30.

Este documento mapea cada feature clave del MVP con su endpoint backend en `public/api/`, la vista frontend que lo consume y su estado actual.

| Feature | Endpoint backend (`public/api/`) | Vista/frontend conectado | Estado | Nota breve |
|---|---|---|---|---|
| Login / sesión | `auth/login.php`, `auth/logout.php`, `auth/me.php` | `login.html` + `assets/js/login.js` y guardas de sesión en `assets/js/auth.js` | **Listo** | Autenticación real con hash de contraseña, sesión PHP y CSRF token. |
| Referidos | `associate/referral-link.php`, `referrals/offer.php` | `dashboard-asociado.html` + `assets/js/registrations.js` (flujo de link y oferta por código) | **Listo** | Genera código persistente y resuelve oferta por país/default desde DB. |
| Pagos | `registrations/create.php`, `admin/settings.php`, `associate/payment-methods.php`, `referrals/offer.php` | `foros.html` + `assets/js/forums.js` / `assets/js/registrations.js` | **Parcial** | Hay captura/validación de comprobante y configuración de métodos, pero no cobro automático con pasarela ni conciliación transaccional. |
| Certificados | `admin/certificates.php`, `admin/certificate-view.php`, `user/certificates.php`, `admin/certificates/bulk-attendance.php` | `dashboard-admin.html` y `dashboard-usuario.html` + `assets/js/dashboard.js` | **Listo** | Flujo operativo para elegibilidad, emisión y consulta por usuario. |
| Ebooks | `user/ebooks.php`, `user/ebooks_download.php`, `admin/ebooks.php`, `admin/ebooks/grant-access.php`, `admin/ebooks/forum-link.php` | `dashboard-usuario.html` / área de recursos en `assets/js/dashboard.js` y `assets/js/registrations.js` | **Listo** | Catálogo por foro, reglas de acceso y descarga con token firmado/expirable. |

## Convención de estados

- **Listo**: funcionalidad implementada con persistencia backend y uso real desde frontend.
- **Parcial**: hay backend+UI funcional para parte del flujo, pero faltan integraciones críticas de negocio/operación.
- **Pendiente**: sin endpoint operativo o sin consumo real desde frontend.
