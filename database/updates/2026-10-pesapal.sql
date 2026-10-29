-- Pesapal payment gateway: two new tables + online donations. phpMyAdmin: select the Lumina database > Import > choose this file > Go. Safe to run more than once; changes no existing data.
SET NAMES utf8mb4;

-- Pesapal payment gateway (docs/PESAPAL.md): one ledger of every Pesapal payment (donations and shop orders) + the notification (IPN) ids registered with Pesapal.
CREATE TABLE IF NOT EXISTS `pesapal_payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `merchant_reference` VARCHAR(50) NOT NULL,
  `order_tracking_id` VARCHAR(80) NULL,
  `purpose` ENUM('donation','shop') NOT NULL,
  `ref_id` INT UNSIGNED NOT NULL,
  `amount_minor` BIGINT UNSIGNED NOT NULL,
  `currency` CHAR(3) NOT NULL,
  `status` ENUM('pending','paid','failed','reversed','review') NOT NULL DEFAULT 'pending',
  `mode` VARCHAR(10) NOT NULL DEFAULT 'sandbox',
  `payer_name` VARCHAR(150) NOT NULL DEFAULT '',
  `payer_email` VARCHAR(190) NOT NULL DEFAULT '',
  `payer_phone` VARCHAR(40) NOT NULL DEFAULT '',
  `pay_method` VARCHAR(60) NULL,
  `confirmation` VARCHAR(60) NULL,
  `last_error` VARCHAR(190) NULL,
  `paid_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pesapal_reference` (`merchant_reference`),
  UNIQUE KEY `uq_pesapal_tracking` (`order_tracking_id`),
  KEY `ix_pesapal_target` (`purpose`, `ref_id`),
  KEY `ix_pesapal_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pesapal_ipns` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `env` VARCHAR(10) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `ipn_id` VARCHAR(80) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pesapal_ipn` (`env`, `url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration (idempotent): online donations (method "pesapal", source "online").
SET @lumina_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'method' AND column_type LIKE '%pesapal%') = 0,
  'ALTER TABLE `donations` MODIFY COLUMN `method` ENUM(''gofundme'',''bank'',''mobile_money'',''paypal'',''cash'',''other'',''pesapal'') NOT NULL DEFAULT ''other''', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'donations' AND column_name = 'source' AND column_type LIKE '%online%') = 0,
  'ALTER TABLE `donations` MODIFY COLUMN `source` ENUM(''pledge_form'',''manual'',''online'') NOT NULL DEFAULT ''manual''', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
