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

-- Migration (idempotent): LAF Academy registration number + administrator approval (docs/ACADEMY.md).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'reg_no');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `reg_no` VARCHAR(24) NULL AFTER `id`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'approved_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `approved_at` DATETIME NULL AFTER `email_verified_at`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'approved_by');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `approved_by` INT UNSIGNED NULL AFTER `approved_at`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
UPDATE `academy_learners` SET `reg_no` = CONCAT('LAF-AC-', YEAR(`created_at`), '-', LPAD(`id`, 6, '0')) WHERE `reg_no` IS NULL;
SET @lumina_has_idx = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND index_name = 'uq_academy_learners_reg_no');
SET @lumina_ddl = IF(@lumina_has_idx = 0, 'ALTER TABLE `academy_learners` ADD UNIQUE KEY `uq_academy_learners_reg_no` (`reg_no`)', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- Bono AI, the support assistant of the public website (docs/BONO.md): what it learned from the site, answers an administrator taught it, and the questions people asked.
CREATE TABLE IF NOT EXISTS `bono_pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `url_hash` CHAR(40) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `content_hash` CHAR(40) NOT NULL DEFAULT '',
  `indexed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bono_pages_url` (`url_hash`),
  KEY `ix_bono_pages_indexed` (`indexed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bono_chunks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` INT UNSIGNED NOT NULL,
  `heading` VARCHAR(255) NOT NULL DEFAULT '',
  `anchor` VARCHAR(120) NOT NULL DEFAULT '',
  `body` TEXT NOT NULL,
  `stems` TEXT NOT NULL,
  `tf` TEXT NOT NULL,
  `len` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ix_bono_chunks_page` (`page_id`),
  CONSTRAINT `fk_bono_chunks_page` FOREIGN KEY (`page_id`) REFERENCES `bono_pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bono_taught` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` VARCHAR(300) NOT NULL,
  `answer` TEXT NOT NULL,
  `stems` TEXT NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `hits` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bono_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` VARCHAR(500) NOT NULL,
  `outcome` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `score` FLOAT NOT NULL DEFAULT 0,
  `page_url` VARCHAR(500) NULL,
  `helpful` TINYINT NULL,
  `handled` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_bono_log_created` (`created_at`),
  KEY `ix_bono_log_open` (`outcome`, `handled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration (idempotent): the learner's short bio shown in the portal and to the Academy team (docs/ACADEMY.md).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'bio');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_learners` ADD COLUMN `bio` VARCHAR(600) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- Messages between the Academy team and each learner (one conversation per learner, shown in the learner portal and under Admin > LAF Academy > Messages).
CREATE TABLE IF NOT EXISTS `academy_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `learner_id` INT UNSIGNED NOT NULL,
  `sender` ENUM('admin','learner') NOT NULL,
  `admin_id` INT UNSIGNED NULL,
  `body` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `ix_academy_messages_learner` (`learner_id`, `id`),
  KEY `ix_academy_messages_unread` (`sender`, `read_at`),
  CONSTRAINT `fk_academy_messages_learner` FOREIGN KEY (`learner_id`) REFERENCES `academy_learners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inquiries sent from the public "Message the Academy team" form by people who have no portal yet (docs/ACADEMY.md). Someone who already has an account writes into their own conversation instead.
CREATE TABLE IF NOT EXISTS `academy_inquiries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `message` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `handled_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `ix_academy_inquiries_open` (`handled_at`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
