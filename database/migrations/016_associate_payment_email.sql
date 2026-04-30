-- Agrega email de pago (MercadoPago / PayPal / etc.) a los datos bancarios del asociado
-- Permite que el asociado configure múltiples canales de cobro al referido

ALTER TABLE associate_payment_methods ADD COLUMN payment_email TEXT;
