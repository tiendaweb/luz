-- Tabla para almacenar métodos de pago de asociados
-- Cada asociado puede tener múltiples métodos de pago configurados

CREATE TABLE IF NOT EXISTS associate_payment_methods (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL UNIQUE,
  bank_name TEXT NOT NULL,
  account_holder TEXT NOT NULL,
  account_number TEXT NOT NULL,
  account_type TEXT,
  currency TEXT DEFAULT 'ARS',
  alias_or_reference TEXT,
  is_active INTEGER DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE INDEX IF NOT EXISTS idx_associate_payment_methods_user_id ON associate_payment_methods(user_id);
