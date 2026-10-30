-- Admin > Visitors: the pages people open (no IP address stored), and a small cache of country look-ups. ADDITIVE and IDEMPOTENT.
-- The website also creates these two tables by itself the first time it needs them, so importing this file is optional.
CREATE TABLE IF NOT EXISTS `site_visits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `visited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `path` VARCHAR(255) NOT NULL,
  `country` CHAR(2) NULL,
  `visitor_code` CHAR(20) NOT NULL,
  `device` VARCHAR(10) NOT NULL DEFAULT 'desktop',
  `referrer_host` VARCHAR(120) NULL,
  PRIMARY KEY (`id`),
  KEY `ix_site_visits_time` (`visited_at`),
  KEY `ix_site_visits_country` (`country`, `visited_at`),
  KEY `ix_site_visits_path` (`path`(120), `visited_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `geo_cache` (
  `ip_hash` CHAR(40) NOT NULL,
  `country` CHAR(2) NULL,
  `checked_at` DATETIME NOT NULL,
  PRIMARY KEY (`ip_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
