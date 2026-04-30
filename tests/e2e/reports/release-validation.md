# Reporte final de liberación - Validación funcional por flujo de negocio

**Resultado:** 15/17 casos PASS; FAIL=2

| Flujo | Caso | Precondición | Request | Respuesta esperada | Resultado |
|---|---|---|---|---|---|
| Certificados | Elegibilidad attendance | Admin autenticado | `GET /api/admin/certificates.php?type=attendance` | 200 y lista con type=attendance | **PASS** (200) |
| Certificados | Elegibilidad completion | Admin autenticado | `GET /api/admin/certificates.php?type=completion` | 200 y lista con type=completion | **PASS** (200) |
| Certificados | Generación única (1ra) | Usuario elegible | `POST /api/admin/certificates.php attendance` | 200 y certificateId | **PASS** (200 cert=2) |
| Certificados | No duplicado (2da) | Certificado ya emitido | `POST /api/admin/certificates.php attendance repetido` | 200 + mensaje ya existe | **PASS** (200 msg=Certificado ya existe) |
| Certificados | Visualización/descarga | Certificado emitido | `GET /api/admin/certificate-view.php?id=...` | 200 HTML renderizado | **PASS** (200) |
| Certificados | Integridad plantilla/datos | Template v1 y datos dinámicos | `Validación de HTML de certificado` | Contiene estructura de certificado y datos renderizados | **PASS** (len=1827) |
| CRUD críticos | Users leer | Admin autenticado | `GET /api/admin/users.php` | 200 | **PASS** (200) |
| CRUD críticos | Registrations leer | Admin autenticado | `GET /api/admin/registrations.php` | 200 | **PASS** (200) |
| CRUD críticos | Blog leer | Admin autenticado | `GET /api/admin/blog/list.php` | 200 | **PASS** (200) |
| CRUD críticos | Pages leer | Admin autenticado | `GET /api/admin/pages/list.php` | 200 | **PASS** (200) |
| CRUD críticos | Settings leer | Admin autenticado | `GET /api/admin/settings.php` | 200 | **PASS** (200) |
| CRUD críticos | Blog crear | Admin autenticado | `POST /api/admin/blog/create.php` | 201/200 | **FAIL** (422) |
| CRUD críticos | Pages crear | Admin autenticado | `POST /api/admin/pages/create.php` | 201/200 | **FAIL** (422) |
| CRUD críticos | Users actualizar inexistente | Admin autenticado | `PATCH /api/admin/users.php userId inexistente` | 404 controlado | **PASS** (404) |
| CRUD críticos | Settings validación 422 | Admin autenticado | `PATCH /api/admin/settings.php email inválido` | 422 | **PASS** (422) |
| CRUD críticos | Registrations error 422 | Admin autenticado | `DELETE /api/admin/registrations.php?id=0` | 422 | **PASS** (422) |
| CRUD críticos | Permisos por rol 403 | Usuario autenticado no admin | `GET /api/admin/users.php` | 403 | **PASS** (403) |
