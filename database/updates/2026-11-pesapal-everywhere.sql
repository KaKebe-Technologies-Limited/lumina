-- Pesapal at every payment point (as the second option): the payment ledger accepts gifts to a project and membership fees; the switches for the new places. phpMyAdmin: select the Lumina database > Import > choose this file > Go.
-- Safe to run more than once; adds only: no data is deleted, nothing is switched on (a new place stays OFF until the administrator presses the button on Admin > Pesapal).
SET NAMES utf8mb4;

-- Migration (idempotent): pesapal_payments.purpose also takes 'project' and 'membership'.
SET @lumina_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pesapal_payments' AND column_name = 'purpose' AND column_type LIKE '%membership%') = 0 AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'pesapal_payments') = 1,
  'ALTER TABLE `pesapal_payments` MODIFY COLUMN `purpose` ENUM(''donation'',''shop'',''academy_signup'',''academy_cert'',''project'',''membership'') NOT NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- Pesapal at every payment point, as the SECOND option (docs/PESAPAL.md). A new place is OFF until an administrator ticks it (Admin > Pesapal > "Offer Pesapal everywhere");
-- the two Academy places were always offered (they had no switch), so they stay on. INSERT IGNORE: nothing already saved is changed.
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
  ('pesapal_place_project', '0'),
  ('pesapal_place_membership', '0'),
  ('pesapal_place_academy_signup', '1'),
  ('pesapal_place_academy_cert', '1');
-- the shop's Pesapal label: new wording, only where it still has the old default wording
UPDATE settings SET setting_value = 'Card, mobile money or bank (Pesapal)' WHERE setting_key = 'shop_pay_pesapal_label' AND setting_value = 'Card or mobile money (Pesapal)';
