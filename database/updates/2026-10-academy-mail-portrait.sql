-- LAF Academy update: e-mail delivery record, learner portraits, 6-digit confirmation code + WhatsApp number (academy_learners) and certificate approval (academy_certificates).
-- phpMyAdmin: select the Lumina database > Import > choose this file > Go. Safe to run more than once. Changes no existing data;
-- certificates issued before are approved once, so they keep working.
SET NAMES utf8mb4;

-- Migration (idempotent): e-mail delivery record + learner portrait (docs/ACADEMY.md). Databases created earlier get the columns here.
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'mail_sent_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `mail_sent_at` DATETIME NULL AFTER `token_expires_at`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'mail_error');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `mail_error` VARCHAR(190) NULL AFTER `mail_sent_at`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'photo_path');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `photo_path` VARCHAR(255) NULL AFTER `mail_error`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'photo_consent_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `photo_consent_at` DATETIME NULL AFTER `photo_path`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- Migration (idempotent): 6-digit confirmation code (e-mail or WhatsApp) + the learner's WhatsApp number (docs/ACADEMY.md).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'code_hash');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `code_hash` CHAR(64) NULL AFTER `photo_consent_at`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'code_expires_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `code_expires_at` DATETIME NULL AFTER `code_hash`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'code_attempts');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `code_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `code_expires_at`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'whatsapp');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `whatsapp` VARCHAR(20) NULL AFTER `code_attempts`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- Certificate approval (docs/ACADEMY.md): a certificate earned by passing an exam waits until an administrator approves it. Certificates issued before this
-- column existed are approved once, at the moment it is added (the UPDATE runs only when the column was missing, never again).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_certificates' AND column_name = 'approved_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_certificates` ADD COLUMN `approved_at` DATETIME NULL AFTER `issued_at`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_ddl = IF(@lumina_has_col = 0, 'UPDATE `academy_certificates` SET `approved_at` = `issued_at` WHERE `approved_at` IS NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_certificates' AND column_name = 'approved_by');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_certificates` ADD COLUMN `approved_by` INT UNSIGNED NULL AFTER `approved_at`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_certificates' AND column_name = 'emailed_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_certificates` ADD COLUMN `emailed_at` DATETIME NULL AFTER `approved_by`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'google_sub');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `google_sub` VARCHAR(40) NULL AFTER `whatsapp`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
