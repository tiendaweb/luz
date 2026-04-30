CREATE TABLE IF NOT EXISTS content_prompt_templates (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  objective TEXT NOT NULL,
  audience TEXT NOT NULL,
  tone TEXT NOT NULL,
  channel TEXT NOT NULL,
  length TEXT NOT NULL,
  cta TEXT NOT NULL,
  keywords TEXT NOT NULL,
  legal TEXT NOT NULL,
  created_by_user_id INTEGER,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_content_prompt_templates_name
  ON content_prompt_templates(name);
