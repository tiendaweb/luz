ALTER TABLE registration_meta ADD COLUMN network_id INTEGER;
ALTER TABLE registration_meta ADD COLUMN country_code TEXT;
CREATE INDEX IF NOT EXISTS idx_registration_meta_network_id ON registration_meta(network_id);
CREATE INDEX IF NOT EXISTS idx_registration_meta_country_code ON registration_meta(country_code);
