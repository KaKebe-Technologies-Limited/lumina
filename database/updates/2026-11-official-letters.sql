-- Official letters (Admin > Official letters). Safe to run again. The tables are also created on first use.

CREATE TABLE IF NOT EXISTS `letters` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ref_no` VARCHAR(60) NULL,
  `letter_date` DATE NOT NULL,
  `use_letterhead` TINYINT(1) NOT NULL DEFAULT 1,
  `to_name` VARCHAR(190) NULL,
  `to_title` VARCHAR(190) NULL,
  `to_org` VARCHAR(190) NULL,
  `to_address` TEXT NULL,
  `salutation` VARCHAR(190) NULL,
  `subject` VARCHAR(300) NOT NULL,
  `body` MEDIUMTEXT NOT NULL,
  `closing` VARCHAR(120) NULL,
  `sig_mode` VARCHAR(10) NOT NULL DEFAULT 'blank',
  `signatory_id` INT UNSIGNED NULL,
  `from_name` VARCHAR(190) NULL,
  `from_title` VARCHAR(190) NULL,
  `from_office` VARCHAR(190) NULL,
  `cc` TEXT NULL,
  `enclosures` TEXT NULL,
  `status` VARCHAR(10) NOT NULL DEFAULT 'draft',
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_letters_status` (`status`, `updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `letter_contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(190) NOT NULL DEFAULT '',
  `email` VARCHAR(190) NOT NULL,
  `organisation` VARCHAR(190) NULL,
  `phone` VARCHAR(60) NULL,
  `grp` VARCHAR(80) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_letter_contacts_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `letter_sends` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `letter_id` INT UNSIGNED NOT NULL,
  `mode` VARCHAR(10) NOT NULL DEFAULT 'each',
  `subject` VARCHAR(300) NOT NULL,
  `message` TEXT NULL,
  `total` INT UNSIGNED NOT NULL DEFAULT 0,
  `sent_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `failed_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_letter_sends_letter` (`letter_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `letter_recipients` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `send_id` INT UNSIGNED NOT NULL,
  `letter_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(190) NOT NULL DEFAULT '',
  `email` VARCHAR(190) NOT NULL,
  `status` VARCHAR(10) NOT NULL DEFAULT 'sent',
  `error` VARCHAR(255) NULL,
  `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_letter_recipients_send` (`send_id`),
  KEY `ix_letter_recipients_letter` (`letter_id`, `email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

