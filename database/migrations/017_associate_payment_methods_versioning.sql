-- Versionado simple de métodos de pago de asociados.
-- Reemplaza la restricción UNIQUE(user_id) por historial activo/inactivo.

ALTER TABLE associate_payment_methods RENAME TO associate_payment_methods_legacy;

CREATE TABLE IF NOT EXISTS associate_payment_methods (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  country_code TEXT NOT NULL DEFAULT 'AR',
  method_type TEXT NOT NULL DEFAULT 'bank_transfer',
  bank_name TEXT,
  account_holder TEXT,
  account_number TEXT,
  account_type TEXT,
  currency TEXT DEFAULT 'ARS',
  alias_or_reference TEXT,
  payment_email TEXT,
  is_active INTEGER NOT NULL DEFAULT 1,
  activated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deactivated_at TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO associate_payment_methods (
  id, user_id, country_code, method_type, bank_name, account_holder, account_number,
  account_type, currency, alias_or_reference, payment_email, is_active,
  activated_at, deactivated_at, created_at, updated_at
)
SELECT
  id,
  user_id,
  'AR',
  CASE
    WHEN COALESCE(payment_email, '') <> '' THEN 'email_payment'
    ELSE 'bank_transfer'
  END,
  bank_name,
  account_holder,
  account_number,
  account_type,
  COALESCE(currency, 'ARS'),
  alias_or_reference,
  payment_email,
  COALESCE(is_active, 1),
  COALESCE(created_at, CURRENT_TIMESTAMP),
  CASE WHEN COALESCE(is_active, 1) = 1 THEN NULL ELSE COALESCE(updated_at, CURRENT_TIMESTAMP) END,
  COALESCE(created_at, CURRENT_TIMESTAMP),
  updated_at
FROM associate_payment_methods_legacy;

DROP TABLE associate_payment_methods_legacy;

CREATE INDEX IF NOT EXISTS idx_associate_payment_methods_user_id ON associate_payment_methods(user_id);
CREATE INDEX IF NOT EXISTS idx_associate_payment_methods_user_active ON associate_payment_methods(user_id, is_active, id DESC);
