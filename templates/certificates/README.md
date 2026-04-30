# Plantillas de certificados (editables)

Ubicación: `templates/certificates/<version>/<type>.html`

- Versiones soportadas por defecto: `v1`.
- Tipos soportados: `attendance`, `completion`.
- Si no existe la plantilla editable, el sistema usa fallback en `app/Templates/certificates/*.php`.

## Placeholders disponibles

- `{{participant_name}}`
- `{{forum_code}}`
- `{{forum_title}}`
- `{{date_issued}}`
- `{{signature_data_url}}`
- `{{director_name}}`
- `{{director_signature}}`

## Ejemplo de naming

- `templates/certificates/v1/attendance.html`
- `templates/certificates/v1/completion.html`
