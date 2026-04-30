# Pruebas de aceptación E2E

## Matriz mínima por rol

- Visitante: `auth/me`, navegación pública y acceso no autenticado.
- Usuario: `login/logout/me`, flujo de inscripciones y certificados de usuario.
- Asociado: `login/logout/me`, inscripciones del asociado y oferta de referidos.
- Admin: `login/logout/me`, CRUD base (páginas/blog/usuarios/inscripciones), certificados admin.

## Cobertura funcional

- Auth: login/logout/me.
- Navegación SPA por hash (`/dashboard.php#...`).
- CRUD Admin: páginas, blog, usuarios, inscripciones.
- Flujos de asociado.
- Flujos de usuario.
- Generación/visualización de certificados (admin y usuario).

## Ejecución única

```bash
bash scripts/run-acceptance.sh
```

## Salidas de auditoría

- `tests/e2e/reports/api.json`
- `tests/e2e/reports/ui.json`
- `tests/e2e/reports/final-report.json`
- `tests/e2e/reports/final-report.md`

Reglas de validación:
- Se aceptan solo `200/201` y `4xx` controlados.
- Cualquier `5xx` marca módulo en **FAIL** y activa `no_deploy=true`.
