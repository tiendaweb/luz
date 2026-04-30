PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS forum_ebooks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  forum_id INTEGER NOT NULL,
  ebook_id INTEGER NOT NULL,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT,
  FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
  FOREIGN KEY (ebook_id) REFERENCES ebooks(id) ON DELETE CASCADE,
  UNIQUE (forum_id, ebook_id),
  CHECK (is_active IN (0, 1))
);

CREATE INDEX IF NOT EXISTS idx_forum_ebooks_forum_id ON forum_ebooks (forum_id);
CREATE INDEX IF NOT EXISTS idx_forum_ebooks_ebook_id ON forum_ebooks (ebook_id);
CREATE INDEX IF NOT EXISTS idx_forum_ebooks_active ON forum_ebooks (is_active);

INSERT OR IGNORE INTO forum_ebooks (forum_id, ebook_id, is_active)
SELECT forums.id, ebooks.id, 1
FROM forums
CROSS JOIN ebooks;
