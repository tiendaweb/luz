# Checklist de despliegue: `data/` y `data/app.sqlite`

Ejecutar antes de iniciar la API en cualquier entorno (dev/staging/prod):

1. Verificar ruta absoluta esperada de la base SQLite:
   - `realpath /ruta/al/proyecto/data`
   - Confirmar que el archivo final sea `/ruta/al/proyecto/data/app.sqlite`.
2. Verificar existencia de carpeta `data/`:
   - `test -d data`
   - Si no existe: `mkdir -p data`.
3. Verificar permisos de escritura sobre carpeta `data/` para el usuario del proceso web (php-fpm/apache/nginx):
   - `test -w data`
   - Si falla, ajustar permisos y ownership.
4. Verificar ownership correcto:
   - `ls -ld data`
   - Asegurar owner/grupo compatibles con el usuario del servicio.
5. Verificar archivo `data/app.sqlite`:
   - Si no existe, crearlo: `touch data/app.sqlite`.
   - Verificar escritura: `test -w data/app.sqlite`.
6. **Migrar esquema antes de levantar la API**:
   - `php scripts/migrate.php`
   - Confirmar salida final: `[migrate] Schema ready.`
7. Validar que no queden migraciones pendientes:
   - `php scripts/migrate.php` (idempotente; no debe fallar)
8. Recién después de migrar, iniciar/reiniciar servicio web/API.
9. Probar endpoint API de salud funcional:
   - Hacer una request a un endpoint API.
   - Confirmar que no aparece `db_unavailable` ni `schema_outdated`.
10. Si el esquema está desactualizado, la API devuelve error controlado:
   - `error.code = schema_outdated`
   - `error.message` operativo (sin stack trace)
   - `error.details.pending_migrations` con archivos faltantes.

> Nota: el startup de la API verifica versión de esquema en cada arranque/carga de bootstrap para alertar antes de procesar tráfico real.
