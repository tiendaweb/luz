CREATE TABLE IF NOT EXISTS registration_asset_reviews (
  registration_id INTEGER PRIMARY KEY,
  payment_proof_status TEXT NOT NULL DEFAULT 'pending',
  signature_status TEXT NOT NULL DEFAULT 'pending',
  payment_proof_note TEXT,
  signature_note TEXT,
  updated_by_user_id INTEGER,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
  FOREIGN KEY (updated_by_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS registration_review_audit (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  registration_id INTEGER NOT NULL,
  asset TEXT NOT NULL,
  previous_status TEXT,
  next_status TEXT NOT NULL,
  reason TEXT NOT NULL,
  actor_user_id INTEGER,
  actor_role TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_user_id) REFERENCES users(id)
);
