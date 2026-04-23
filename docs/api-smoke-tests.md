# Pruebas mínimas de humo API

Script: `scripts/smoke-api.sh`

## Qué valida
- Arranque local (`php -S`) sobre `public/`.
- Obtención de `csrfToken` en `GET /api/auth/me.php`.
- Login demo (`POST /api/auth/login.php`).
- Estado autenticado (`GET /api/auth/me.php`).
- Logout con CSRF (`POST /api/auth/logout.php`).

## Ejecución

```bash
bash scripts/smoke-api.sh
```

## Resultado esperado
- Salida final: `Smoke API OK`.
- Código de salida `0`.
- Si falla, revisar `/tmp/luz-smoke-server.log`.
