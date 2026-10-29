-- Academy sign-up fee and certificate fee through Pesapal. phpMyAdmin: select the Lumina database > Import > choose this file > Go. Safe to run more than once; changes no existing data.
-- Run it AFTER 2026-10-pesapal.sql (or the full update import).
SET NAMES utf8mb4;

-- Academy fees paid through Pesapal (sign-up fee on academy_learners, certificate fee on academy_certificates; docs/PESAPAL.md). Existing rows stay 'none' (no fee).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'fee_status');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `fee_status` ENUM(''none'',''due'',''paid'',''waived'') NOT NULL DEFAULT ''none''', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'fee_paid_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `fee_paid_at` DATETIME NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_certificates' AND column_name = 'fee_status');
SET @lumina_ddl = IF(@lumina_has_col = 0 AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'academy_certificates') = 1, 'ALTER TABLE `academy_certificates` ADD COLUMN `fee_status` ENUM(''none'',''due'',''paid'',''waived'') NOT NULL DEFAULT ''none''', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_certificates' AND column_name = 'fee_paid_at');
SET @lumina_ddl = IF(@lumina_has_col = 0 AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'academy_certificates') = 1, 'ALTER TABLE `academy_certificates` ADD COLUMN `fee_paid_at` DATETIME NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pesapal_payments' AND column_name = 'purpose' AND column_type LIKE '%academy_signup%') = 0 AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'pesapal_payments') = 1,
  'ALTER TABLE `pesapal_payments` MODIFY COLUMN `purpose` ENUM(''donation'',''shop'',''academy_signup'',''academy_cert'') NOT NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
