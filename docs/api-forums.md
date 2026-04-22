# API de Foros (agenda dinámica)

Esta guía define el contrato de los endpoints usados por la portada para evitar ambigüedades regionales de fecha y hora.

## Convención de fecha y zona horaria

- `startsAt` se envía en **UTC** con formato **ISO 8601**: `YYYY-MM-DDTHH:mm:ssZ`.
  - Ejemplo: `2026-05-09T13:00:00Z`.
- `timezone` usa identificadores **IANA** (por ejemplo, `America/Bogota`, `America/Argentina/Buenos_Aires`).
- El cliente debe:
  1. Parsear `startsAt` como UTC.
  2. Mostrar la fecha/hora en la zona especificada por `timezone`.

## GET `/api/forums/next.php`

Devuelve el próximo foro publicado con `starts_at > now`.

### Respuesta 200

```json
{
  "ok": true,
  "forum": {
    "id": 1,
    "code": "morning",
    "title": "Foro de la mañana",
    "description": "...",
    "platformType": "zoom",
    "platformUrl": "https://zoom.us/j/psme-manana",
    "timezone": "America/Argentina/Buenos_Aires",
    "status": "published",
    "speakerJson": [
      { "name": "Maria Luz Genovese", "role": "Directora" }
    ],
    "startsAt": "2026-05-09T13:00:00Z"
  }
}
```

Si no existen foros futuros publicados:

```json
{ "ok": true, "forum": null }
```

## GET `/api/forums/list.php`

Lista foros publicados con paginación básica.

### Query params

- `page` (opcional, entero, mínimo `1`, default `1`).
- `per_page` (opcional, entero `1..20`, default `6`).

### Respuesta 200

```json
{
  "ok": true,
  "items": [
    {
      "id": 1,
      "code": "morning",
      "title": "Foro de la mañana",
      "description": "...",
      "platformType": "zoom",
      "platformUrl": "https://zoom.us/j/psme-manana",
      "timezone": "America/Argentina/Buenos_Aires",
      "status": "published",
      "speakerJson": [
        { "name": "Maria Luz Genovese", "role": "Directora" }
      ],
      "startsAt": "2026-05-09T13:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "perPage": 6,
    "total": 2,
    "totalPages": 1
  }
}
```

## Errores

- Método HTTP no soportado: `405`.
- Formato general de error:

```json
{ "ok": false, "error": "Método no permitido" }
```
