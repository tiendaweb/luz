CREATE TABLE IF NOT EXISTS site_settings (
    setting_key TEXT PRIMARY KEY,
    value_type TEXT NOT NULL CHECK(value_type IN ('string', 'text', 'email', 'phone', 'color')),
    value_text TEXT NOT NULL,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT OR IGNORE INTO site_settings (setting_key, value_type, value_text) VALUES
    ('public_phone_primary', 'phone', '+54 9 11 4000-0000'),
    ('public_phone_secondary', 'phone', '+54 9 11 4000-0001'),
    ('public_email_primary', 'email', 'contacto@forospsme.com'),
    ('public_email_support', 'email', 'soporte@forospsme.com'),
    ('director_name', 'string', 'María Luz Genovese'),
    ('director_title', 'string', 'Psicóloga Social especializada en Salud Mental y Emocional (SmE)'),
    ('director_location', 'string', 'Buenos Aires, Argentina'),
    ('contact_short_text', 'text', 'Comunidad de debate y fortalecimiento psicosocial en Latinoamérica.'),
    ('contact_cta_text', 'text', 'Escribinos para coordinar entrevistas, consultas o información de próximos foros.'),
    ('brand_color_primary', 'color', '#0d9488'),
    ('brand_color_accent', 'color', '#0f766e');

CREATE INDEX IF NOT EXISTS idx_site_settings_type ON site_settings(value_type);
