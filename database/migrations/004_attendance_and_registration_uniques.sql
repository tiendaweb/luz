PRAGMA foreign_keys = ON;

CREATE UNIQUE INDEX IF NOT EXISTS uq_registrations_user_forum
ON registrations(user_id, forum_id)
WHERE user_id IS NOT NULL AND forum_id IS NOT NULL;

CREATE TABLE IF NOT EXISTS forum_attendance (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  registration_id INTEGER NOT NULL,
  forum_id INTEGER NOT NULL,
  session_key TEXT,
  session_date TEXT,
  status TEXT NOT NULL CHECK(status IN ('present', 'absent', 'partial')),
  minutes_attended INTEGER,
  recorded_by_user_id INTEGER,
  recorded_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notes TEXT,
  FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
  FOREIGN KEY (forum_id) REFERENCES forums(id) ON DELETE CASCADE,
  FOREIGN KEY (recorded_by_user_id) REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_forum_attendance_forum ON forum_attendance(forum_id, session_date);
CREATE INDEX IF NOT EXISTS idx_forum_attendance_registration ON forum_attendance(registration_id, session_date);
CREATE UNIQUE INDEX IF NOT EXISTS uq_forum_attendance_session
ON forum_attendance(registration_id, forum_id, COALESCE(session_key, ''), COALESCE(session_date, ''));
