PRAGMA foreign_keys = ON;

ALTER TABLE forums ADD COLUMN objective TEXT NOT NULL DEFAULT '';
ALTER TABLE forums ADD COLUMN topics_json TEXT;
ALTER TABLE forums ADD COLUMN modality TEXT NOT NULL DEFAULT 'Virtual sincrónico';
ALTER TABLE forums ADD COLUMN requirements TEXT;
ALTER TABLE forums ADD COLUMN seats_total INTEGER NOT NULL DEFAULT 0;
ALTER TABLE forums ADD COLUMN seats_available INTEGER NOT NULL DEFAULT 0;
ALTER TABLE forums ADD COLUMN cta_label TEXT NOT NULL DEFAULT 'Inscribirme';
ALTER TABLE forums ADD COLUMN cta_url TEXT;

CREATE TABLE IF NOT EXISTS forum_guests (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  forum_id INTEGER NOT NULL,
  full_name TEXT NOT NULL,
  role TEXT NOT NULL,
  bio TEXT NOT NULL,
  sort_order INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_forum_guests_forum_id ON forum_guests (forum_id);
CREATE UNIQUE INDEX IF NOT EXISTS uq_forum_guests_unique_person ON forum_guests (forum_id, full_name);

UPDATE forums
SET objective = 'Profundizar estrategias de intervención psicosocial en contextos clínicos y comunitarios.',
    topics_json = '["Intervención grupal en crisis","Lectura de emergentes vinculares","Diseño de dispositivos de cuidado"]',
    modality = 'Virtual en vivo por Zoom',
    requirements = 'Dirigido a profesionales y estudiantes avanzados del área psicosocial. Se recomienda experiencia previa en coordinación de grupos.',
    seats_total = 80,
    seats_available = 26,
    cta_label = 'Reservar cupo mañana',
    cta_url = '/#view-forums'
WHERE code = 'morning';

UPDATE forums
SET objective = 'Integrar herramientas teórico-prácticas para la atención en salud mental desde una perspectiva latinoamericana.',
    topics_json = '["Salud mental y territorio","Construcción de redes comunitarias","Autocuidado profesional"]',
    modality = 'Virtual en vivo por Google Meet',
    requirements = 'Abierto a estudiantes y equipos de trabajo de instituciones educativas o de salud.',
    seats_total = 100,
    seats_available = 41,
    cta_label = 'Inscribirme al foro tarde',
    cta_url = '/#view-forums'
WHERE code = 'afternoon';

INSERT OR IGNORE INTO forum_guests (forum_id, full_name, role, bio, sort_order)
SELECT id, 'Maria Luz Genovese', 'Directora y moderadora', 'Psicóloga Social especializada en coordinación de grupos operativos y salud mental comunitaria.', 1
FROM forums WHERE code = 'morning';

INSERT OR IGNORE INTO forum_guests (forum_id, full_name, role, bio, sort_order)
SELECT id, 'Dra. Claudia Vaca', 'Invitada internacional', 'Psicóloga Clínica (Colombia) enfocada en trauma complejo y abordajes interdisciplinarios.', 2
FROM forums WHERE code = 'morning';

INSERT OR IGNORE INTO forum_guests (forum_id, full_name, role, bio, sort_order)
SELECT id, 'Maria Luz Genovese', 'Directora y moderadora', 'Psicóloga Social especializada en coordinación de grupos operativos y salud mental comunitaria.', 1
FROM forums WHERE code = 'afternoon';

INSERT OR IGNORE INTO forum_guests (forum_id, full_name, role, bio, sort_order)
SELECT id, 'Lic. Tomás Riera', 'Invitado especial', 'Especialista en salud pública y diseño de programas de intervención territorial con jóvenes.', 2
FROM forums WHERE code = 'afternoon';
