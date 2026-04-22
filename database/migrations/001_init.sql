PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS roles (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  slug TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  full_name TEXT NOT NULL,
  email TEXT UNIQUE,
  document_id TEXT UNIQUE,
  role_id INTEGER NOT NULL,
  password_hash TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE IF NOT EXISTS forums (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT NOT NULL UNIQUE,
  title TEXT NOT NULL,
  starts_at TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS registrations (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER,
  forum_id INTEGER,
  forum_slot TEXT NOT NULL,
  full_name TEXT NOT NULL,
  document_id TEXT NOT NULL,
  needs_cert INTEGER NOT NULL DEFAULT 0,
  payment_proof_name TEXT,
  payment_proof_mime TEXT,
  payment_proof_size INTEGER,
  payment_proof_base64 TEXT,
  acceptance_checked INTEGER NOT NULL DEFAULT 1,
  signature_data_url TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (forum_id) REFERENCES forums(id)
);

CREATE TABLE IF NOT EXISTS associate_offers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE,
  referral_code TEXT NOT NULL UNIQUE,
  payment_method TEXT NOT NULL,
  payment_link TEXT NOT NULL,
  price_amount REAL NOT NULL,
  currency_code TEXT NOT NULL,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS registration_meta (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  registration_id INTEGER NOT NULL UNIQUE,
  referral_code TEXT,
  referrer_user_id INTEGER,
  payment_method TEXT,
  payment_link TEXT,
  price_amount REAL,
  currency_code TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
  FOREIGN KEY (referrer_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS registration_admin_state (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  registration_id INTEGER NOT NULL UNIQUE,
  status TEXT NOT NULL DEFAULT 'pending',
  note TEXT,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS referrals (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  referrer_user_id INTEGER NOT NULL,
  referred_user_id INTEGER NOT NULL,
  note TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (referrer_user_id) REFERENCES users(id),
  FOREIGN KEY (referred_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS messages (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  sender_user_id INTEGER,
  subject TEXT,
  body TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS sessions_audit (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER,
  event TEXT NOT NULL,
  ip_address TEXT,
  user_agent TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT OR IGNORE INTO roles (slug, name) VALUES
  ('guest', 'Invitado'),
  ('user', 'Inscripto'),
  ('associate', 'Asociado'),
  ('admin', 'Administrador');

INSERT OR IGNORE INTO forums (code, title, starts_at) VALUES
  ('morning', 'Foro de la mañana', NULL),
  ('afternoon', 'Foro de la tarde', NULL);

INSERT OR IGNORE INTO associate_offers (user_id, referral_code, payment_method, payment_link, price_amount, currency_code)
SELECT users.id, 'ASOCIADO2026', 'Transferencia bancaria', 'https://pagos.psme.local/asociado2026', 35.00, 'USD'
FROM users
INNER JOIN roles ON roles.id = users.role_id
WHERE roles.slug = 'associate'
LIMIT 1;
