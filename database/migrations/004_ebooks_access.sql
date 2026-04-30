CREATE TABLE IF NOT EXISTS registration_attendance (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  registration_id INTEGER NOT NULL UNIQUE,
  attendance_percent REAL NOT NULL DEFAULT 0,
  recorded_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ebooks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  description TEXT,
  status TEXT NOT NULL DEFAULT 'published',
  provider TEXT NOT NULL DEFAULT 'local',
  local_path TEXT,
  external_url TEXT,
  min_attendance REAL NOT NULL DEFAULT 75,
  requires_approved INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT,
  CHECK (provider IN ('local', 'external'))
);

CREATE TABLE IF NOT EXISTS ebook_access_rules (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ebook_id INTEGER NOT NULL,
  rule_type TEXT NOT NULL,
  operator TEXT NOT NULL DEFAULT 'gte',
  threshold_value REAL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ebook_id) REFERENCES ebooks(id) ON DELETE CASCADE,
  CHECK (rule_type IN ('registration_status', 'attendance_percent')),
  CHECK (operator IN ('gte', 'eq'))
);

CREATE TABLE IF NOT EXISTS user_ebook_access (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  ebook_id INTEGER NOT NULL,
  access_granted INTEGER NOT NULL DEFAULT 1,
  reason TEXT,
  granted_by_user_id INTEGER,
  expires_at TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, ebook_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (ebook_id) REFERENCES ebooks(id) ON DELETE CASCADE,
  FOREIGN KEY (granted_by_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS ebook_download_audit (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER,
  ebook_id INTEGER,
  event TEXT NOT NULL,
  access_granted INTEGER NOT NULL DEFAULT 0,
  reason TEXT,
  ip_address TEXT,
  user_agent TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (ebook_id) REFERENCES ebooks(id)
);

INSERT INTO ebooks (title, description, status, provider, local_path, external_url, min_attendance, requires_approved)
SELECT 'Guía de intervención psicosocial',
       'Material base para las cohortes aprobadas o con asistencia alta.',
       'published',
       'local',
       'guia-intervencion-psicosocial.pdf',
       NULL,
       75,
       1
WHERE NOT EXISTS (SELECT 1 FROM ebooks WHERE title = 'Guía de intervención psicosocial');

INSERT INTO ebooks (title, description, status, provider, local_path, external_url, min_attendance, requires_approved)
SELECT 'Lecturas recomendadas LATAM',
       'Compendio de papers y referencias para profundizar los ejes del foro.',
       'published',
       'external',
       NULL,
       'https://example.com/ebooks/lecturas-recomendadas-latam.pdf',
       80,
       0
WHERE NOT EXISTS (SELECT 1 FROM ebooks WHERE title = 'Lecturas recomendadas LATAM');

INSERT OR IGNORE INTO ebook_access_rules (ebook_id, rule_type, operator, threshold_value)
SELECT id, 'registration_status', 'eq', NULL FROM ebooks;

INSERT OR IGNORE INTO ebook_access_rules (ebook_id, rule_type, operator, threshold_value)
SELECT id, 'attendance_percent', 'gte', min_attendance FROM ebooks;
