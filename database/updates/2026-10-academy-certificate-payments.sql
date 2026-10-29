-- LAF Academy update: certificate payments through Pesapal (table academy_payments + the per-course fee column academy_courses.certificate_fee).
-- phpMyAdmin: select the Lumina database > Import > choose this file > Go. Safe to run more than once. Changes no existing data.
-- Afterwards add PESAPAL_CONSUMER_KEY / PESAPAL_CONSUMER_SECRET to the server .env (docs/PESAPAL.md).
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------------------------------------------------------------------
-- CERTIFICATE PAYMENTS (docs/PESAPAL.md): a learner pays the certificate fee through Pesapal before the certificate is requested / issued. One row per attempt
-- (merchant_ref is OUR reference, tracking_id is Pesapal's order id). Only a row that Pesapal itself confirmed (or an administrator waived) lets the certificate through.
-- The learner and course names are kept as a snapshot so the accounting trail survives an erased account or a deleted course.
CREATE TABLE IF NOT EXISTS `academy_payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `learner_id` INT UNSIGNED NULL,
  `course_id` INT UNSIGNED NULL,
  `learner_name` VARCHAR(120) NOT NULL,
  `learner_email` VARCHAR(190) NOT NULL,
  `course_title` VARCHAR(190) NOT NULL,
  `merchant_ref` VARCHAR(50) NOT NULL,
  `tracking_id` VARCHAR(64) NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'UGX',
  `status` ENUM('pending','paid','failed','review','waived') NOT NULL DEFAULT 'pending',   -- review = money arrived but does not match what we asked for: staff decide
  `source` ENUM('pesapal','manual') NOT NULL DEFAULT 'pesapal',
  `method` VARCHAR(60) NULL,
  `confirmation_code` VARCHAR(80) NULL,
  `note` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `paid_at` DATETIME NULL,
  `checked_at` DATETIME NULL,
  `receipt_emailed_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_academy_payments_ref` (`merchant_ref`),
  KEY `ix_academy_payments_tracking` (`tracking_id`),
  KEY `ix_academy_payments_learner` (`learner_id`, `course_id`, `status`),
  KEY `ix_academy_payments_status` (`status`, `created_at`),
  CONSTRAINT `fk_academy_payments_learner` FOREIGN KEY (`learner_id`) REFERENCES `academy_learners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_academy_payments_course` FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The certificate fee of one course (whole currency units, UGX). NULL = use the site-wide fee (Settings > LAF Academy), 0 = this course's certificate is free.
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_courses' AND column_name = 'certificate_fee');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_courses` ADD COLUMN `certificate_fee` INT UNSIGNED NULL AFTER `certificate_enabled`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
