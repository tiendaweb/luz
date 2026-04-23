# Operación confiable (API + SQLite)

## 1) Manejo estándar de errores API

Todos los endpoints bajo `public/api/*` deben responder errores con una forma consistente:

```json
{
  "ok": false,
  "error": {
    "code": "validation_error",
    "message": "Descripción entendible para operación",
    "details": {"field": "slug"}
  }
}
```

### Reglas operativas
- `4xx`: errores de cliente (validación, autenticación, autorización, CSRF).
- `5xx`: errores inesperados del servidor.
- Nunca exponer stack traces ni SQL en respuestas públicas.
- Registrar en auditoría eventos de sesión (`login`, `logout`, `session_expired`).

## 2) Política de backups para `data/app.sqlite`

### Frecuencia recomendada
- **Cada 6 horas** en horario operativo.
- **Backup diario** retenido 30 días.
- **Backup semanal** retenido 12 semanas.

### Método recomendado
1. Poner la app en modo mantenimiento corto (o parar escritura).
2. Ejecutar copia consistente:
   ```bash
   sqlite3 data/app.sqlite ".backup 'data/backups/app-$(date -u +%Y%m%dT%H%M%SZ).sqlite'"
   ```
3. Verificar integridad de la copia:
   ```bash
   sqlite3 data/backups/<archivo>.sqlite "PRAGMA integrity_check;"
   ```
4. Subir backup cifrado a almacenamiento externo (S3/Blob/NAS).

## 3) Recuperación ante corrupción de DB

### Señales típicas
- Respuestas 500 al consultar/insertar.
- `database disk image is malformed` en logs de PHP/SQLite.
- `PRAGMA integrity_check` distinto de `ok`.

### Runbook de recuperación
1. **Detener escrituras** (modo mantenimiento).
2. Tomar snapshot del archivo actual para forense.
3. Validar último backup íntegro (`PRAGMA integrity_check`).
4. Restaurar backup seleccionado sobre `data/app.sqlite`.
5. Ejecutar `php scripts/seed.php` **solo si aplica** para datos demo faltantes.
6. Validar salud con pruebas de humo API (`scripts/smoke-api.sh`).
7. Rehabilitar tráfico y monitorear 30 minutos.

## 4) Checklist de despliegue

Antes de cada release:

- [ ] `php -l` sin errores en archivos modificados.
- [ ] Migraciones aplicadas en entorno objetivo.
- [ ] Variable `SESSION_TTL_SECONDS` definida según política de seguridad.
- [ ] Validaciones comunes activas en endpoints críticos.
- [ ] Protección CSRF validada en mutaciones (`POST/PUT/PATCH/DELETE`).
- [ ] Backups recientes verificados y restauración ensayada.
- [ ] Pruebas de humo API ejecutadas y registradas.
- [ ] README actualizado (modo demo vs listo para producción).
