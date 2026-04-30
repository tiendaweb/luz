-- Ofertas regionales por asociado.
-- Mantiene compatibilidad con associate_offers (código y defaults históricos)
-- mientras se migra gradualmente a configuración por país.

CREATE TABLE IF NOT EXISTS associate_offer_regions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  associate_user_id INTEGER,
  country_code TEXT NOT NULL DEFAULT '*',
  currency_code TEXT NOT NULL,
  payment_method TEXT NOT NULL,
  payment_link TEXT NOT NULL,
  price_amount REAL NOT NULL,
  is_active INTEGER NOT NULL DEFAULT 1,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (associate_user_id) REFERENCES users(id)
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_associate_offer_regions_assoc_country
  ON associate_offer_regions(associate_user_id, country_code);

CREATE INDEX IF NOT EXISTS idx_associate_offer_regions_country_active
  ON associate_offer_regions(country_code, is_active);

CREATE INDEX IF NOT EXISTS idx_associate_offer_regions_assoc_active
  ON associate_offer_regions(associate_user_id, is_active);
