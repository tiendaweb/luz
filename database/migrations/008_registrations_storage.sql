-- Actualizar tabla registrations para guardar firmas y comprobantes de pago
-- Agregar columnas para almacenar datos de firma y ruta de comprobante
-- SQLite requires separate ALTER TABLE statements for each column

ALTER TABLE registrations ADD COLUMN signature_data TEXT;
ALTER TABLE registrations ADD COLUMN payment_proof_path TEXT;
ALTER TABLE registrations ADD COLUMN referral_code TEXT;

-- Crear índice en referral_code para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_registrations_referral_code ON registrations(referral_code);
