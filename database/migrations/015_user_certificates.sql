-- User Certificates Table
CREATE TABLE IF NOT EXISTS user_certificates (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  forum_id INTEGER NOT NULL,
  created_at TEXT NOT NULL,
  created_by_user_id INTEGER,
  UNIQUE(user_id, forum_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_user_certificates_user_id ON user_certificates(user_id);
CREATE INDEX IF NOT EXISTS idx_user_certificates_forum_id ON user_certificates(forum_id);
