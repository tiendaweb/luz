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
6. Validar espacio en disco suficiente para SQLite y archivos temporales.
7. Probar inicialización de conexión en runtime:
   - Hacer una request a un endpoint API.
   - Confirmar que no aparece `db_unavailable`.
8. Si falla migración, revisar respuesta API para identificar:
   - `error.code = migration_failed`
   - `error.details.filename` con la migración exacta.

> Nota: La API ahora responde errores controlados para problemas de permisos/ruta en DB, evitando fatal errors.
