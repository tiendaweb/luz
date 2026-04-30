-- Soporte para 2 tipos de certificado: asistencia y conclusión
-- Reemplaza UNIQUE(user_id, forum_id) por UNIQUE(user_id, forum_id, type)
-- de modo que un mismo participante puede recibir ambos certificados del mismo foro

ALTER TABLE user_certificates RENAME TO user_certificates_old;

CREATE TABLE user_certificates (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  forum_id INTEGER NOT NULL,
  type TEXT NOT NULL DEFAULT 'completion',
  created_at TEXT NOT NULL,
  created_by_user_id INTEGER,
  UNIQUE(user_id, forum_id, type),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CHECK (type IN ('attendance', 'completion'))
);

INSERT INTO user_certificates (id, user_id, forum_id, type, created_at, created_by_user_id)
SELECT id, user_id, forum_id, 'completion', created_at, created_by_user_id
FROM user_certificates_old;

DROP TABLE user_certificates_old;

CREATE INDEX IF NOT EXISTS idx_user_certificates_user_id ON user_certificates(user_id);
CREATE INDEX IF NOT EXISTS idx_user_certificates_forum_id ON user_certificates(forum_id);
CREATE INDEX IF NOT EXISTS idx_user_certificates_type ON user_certificates(type);
