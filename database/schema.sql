-- =====================================================================================
-- Lumina Africa Foundation - database schema (MySQL 5.7+ / MariaDB 10.4+, InnoDB, utf8mb4).
-- IDEMPOTENT: safe to run repeatedly (CREATE ... IF NOT EXISTS). bin/setup.php imports it for you.
-- Conventions: every table has id, created_at, updated_at. Booleans are TINYINT(1). File columns hold a RELATIVE path:
--   public images  -> relative to /uploads  ("2026/10/abc.jpg") or a bundled "assets/img/..." path
--   private files  -> relative to storage/private ("cv/2026/10/abc.pdf")
-- =====================================================================================

-- No CREATE DATABASE / USE here: import this file INTO the database you already created (phpMyAdmin / cPanel "Import" with that
-- database selected, or `mysql -u USER -p DBNAME < database/schema.sql`). bin/setup.php creates the local database itself.

SET NAMES utf8mb4;

-- ------------------------------------------------------------------ people & system

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(40) NOT NULL DEFAULT 'editor',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `must_change_password` TINYINT(1) NOT NULL DEFAULT 0,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(40) NOT NULL,
  `name` VARCHAR(80) NOT NULL,
  `description` VARCHAR(255) NOT NULL DEFAULT '',
  `abilities` TEXT NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration (idempotent): users.role was ENUM('admin','editor'); custom roles need any role slug.
SET @lumina_role_type = (SELECT DATA_TYPE FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'role');
SET @lumina_ddl = IF(@lumina_role_type = 'enum', 'ALTER TABLE `users` MODIFY COLUMN `role` VARCHAR(40) NOT NULL DEFAULT ''editor''', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` MEDIUMTEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stats` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_key` VARCHAR(50) NOT NULL DEFAULT 'home',
  `label` VARCHAR(120) NOT NULL,
  `value_number` INT UNSIGNED NOT NULL DEFAULT 0,
  `prefix` VARCHAR(10) NOT NULL DEFAULT '',
  `suffix` VARCHAR(10) NOT NULL DEFAULT '',
  `description` VARCHAR(255) NULL,
  `icon` VARCHAR(40) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_stats_group` (`group_key`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `user_name` VARCHAR(120) NOT NULL DEFAULT 'system',
  `action` VARCHAR(60) NOT NULL,
  `entity_type` VARCHAR(60) NULL,
  `entity_id` VARCHAR(64) NULL,
  `summary` VARCHAR(255) NOT NULL DEFAULT '',
  `details` LONGTEXT NULL,
  `ip` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_audit_created` (`created_at`),
  KEY `ix_audit_entity` (`entity_type`, `entity_id`),
  KEY `ix_audit_user` (`user_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL DEFAULT '',
  `ip` VARCHAR(45) NOT NULL,
  `success` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_login_ip_time` (`ip`, `success`, `created_at`),
  KEY `ix_login_email_time` (`email`, `ip`, `success`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rl_key` CHAR(40) NOT NULL,
  `hits` INT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rate_key` (`rl_key`),
  KEY `ix_rate_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------ CMS: pages + editable content blocks

CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `type` ENUM('registry','custom') NOT NULL DEFAULT 'registry',
  `subtitle` VARCHAR(255) NULL,
  `body` MEDIUMTEXT NULL,
  `template` VARCHAR(40) NOT NULL DEFAULT 'default',
  `hero_image` VARCHAR(255) NULL,
  `hero_image_alt` VARCHAR(255) NULL,
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `og_image` VARCHAR(255) NULL,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `show_in_nav` TINYINT(1) NOT NULL DEFAULT 0,
  `show_in_footer` TINYINT(1) NOT NULL DEFAULT 0,
  `nav_label` VARCHAR(80) NULL,
  `nav_order` INT NOT NULL DEFAULT 500,
  `parent_slug` VARCHAR(120) NULL,
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_slug` (`slug`),
  KEY `ix_pages_nav` (`is_published`, `show_in_nav`, `nav_order`),
  KEY `ix_pages_parent` (`parent_slug`),
  CONSTRAINT `fk_pages_parent` FOREIGN KEY (`parent_slug`) REFERENCES `pages` (`slug`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pages_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `content_blocks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_slug` VARCHAR(120) NOT NULL,
  `block_key` VARCHAR(160) NOT NULL,
  `value` MEDIUMTEXT NULL,
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blocks_page_key` (`page_slug`, `block_key`),
  KEY `ix_blocks_user` (`updated_by`),
  CONSTRAINT `fk_blocks_page` FOREIGN KEY (`page_slug`) REFERENCES `pages` (`slug`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_blocks_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------ programmes & projects

CREATE TABLE IF NOT EXISTS `programs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(160) NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `tagline` VARCHAR(190) NULL,
  `summary` TEXT NULL,
  `body` MEDIUMTEXT NULL,
  `image` VARCHAR(255) NULL,
  `image_alt` VARCHAR(255) NULL,
  `icon` VARCHAR(40) NULL,
  `status` ENUM('planned','ongoing','completed') NOT NULL DEFAULT 'planned',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_programs_slug` (`slug`),
  KEY `ix_programs_list` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration (idempotent, works on MySQL and MariaDB): databases created before programs.status existed get the column here.
-- New programs default to 'planned'; the owner sets 'ongoing' / 'completed' in Admin > Programs only when that is true.
SET @lumina_has_programs_status = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'programs' AND column_name = 'status');
SET @lumina_ddl = IF(@lumina_has_programs_status = 0, 'ALTER TABLE `programs` ADD COLUMN `status` ENUM(''planned'',''ongoing'',''completed'') NOT NULL DEFAULT ''planned'' AFTER `icon`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id` INT UNSIGNED NULL,
  `slug` VARCHAR(160) NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `tagline` VARCHAR(190) NULL,
  `summary` TEXT NULL,
  `body` MEDIUMTEXT NULL,
  `image` VARCHAR(255) NULL,
  `image_alt` VARCHAR(255) NULL,
  `status` ENUM('planned','ongoing','completed') NOT NULL DEFAULT 'ongoing',
  `location` VARCHAR(190) NULL,
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_projects_slug` (`slug`),
  KEY `ix_projects_program` (`program_id`),
  KEY `ix_projects_list` (`is_active`, `status`, `sort_order`),
  CONSTRAINT `fk_projects_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------ editorial content

CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_type` ENUM('blog','news','press') NOT NULL DEFAULT 'blog',
  `slug` VARCHAR(190) NOT NULL,
  `title` VARCHAR(220) NOT NULL,
  `excerpt` VARCHAR(500) NULL,
  `body` MEDIUMTEXT NULL,
  `cover_image` VARCHAR(255) NULL,
  `cover_alt` VARCHAR(255) NULL,
  `category` VARCHAR(80) NULL,
  `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` DATETIME NULL,
  `author_id` INT UNSIGNED NULL,
  `author_name` VARCHAR(120) NULL,
  `source_name` VARCHAR(190) NULL,
  `external_url` VARCHAR(500) NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_posts_slug` (`slug`),
  KEY `ix_posts_list` (`post_type`, `status`, `published_at`),
  KEY `ix_posts_author` (`author_id`),
  CONSTRAINT `fk_posts_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activities` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(190) NOT NULL,
  `title` VARCHAR(220) NOT NULL,
  `summary` TEXT NULL,
  `body` MEDIUMTEXT NULL,
  `image` VARCHAR(255) NULL,
  `image_alt` VARCHAR(255) NULL,
  `activity_date` DATE NULL,
  `location` VARCHAR(190) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_activities_slug` (`slug`),
  KEY `ix_activities_list` (`is_active`, `activity_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `image` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(255) NOT NULL DEFAULT '',
  `caption` VARCHAR(255) NULL,
  `album` VARCHAR(100) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_gallery_list` (`is_active`, `album`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_key` VARCHAR(50) NOT NULL DEFAULT 'general',
  `question` VARCHAR(300) NOT NULL,
  `answer` MEDIUMTEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_faqs_group` (`group_key`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------ careers

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(190) NOT NULL,
  `title` VARCHAR(220) NOT NULL,
  `department` VARCHAR(120) NULL,
  `location` VARCHAR(190) NULL,
  `employment_type` ENUM('full_time','part_time','contract','internship','volunteer') NOT NULL DEFAULT 'full_time',
  `summary` TEXT NULL,
  `description` MEDIUMTEXT NULL,
  `requirements` MEDIUMTEXT NULL,
  `how_to_apply` TEXT NULL,
  `apply_email` VARCHAR(190) NULL,
  `closing_date` DATE NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_jobs_slug` (`slug`),
  KEY `ix_jobs_list` (`is_active`, `closing_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` INT UNSIGNED NULL,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `cover_letter` TEXT NULL,
  `cv_path` VARCHAR(255) NULL,
  `cv_original_name` VARCHAR(190) NULL,
  `cv_mime` VARCHAR(100) NULL,
  `cv_size` INT UNSIGNED NULL,
  `linkedin_url` VARCHAR(255) NULL,
  `status` ENUM('new','reviewing','shortlisted','rejected','hired') NOT NULL DEFAULT 'new',
  `admin_notes` TEXT NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_applications_job` (`job_id`),
  KEY `ix_applications_status` (`status`, `created_at`),
  CONSTRAINT `fk_applications_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------ inbox

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `subject` VARCHAR(220) NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `admin_notes` TEXT NULL,
  `replied_at` DATETIME NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_contact_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `name` VARCHAR(150) NULL,
  `status` ENUM('subscribed','unsubscribed') NOT NULL DEFAULT 'subscribed',
  `token` CHAR(40) NOT NULL,
  `source` VARCHAR(60) NULL,
  `ip` VARCHAR(45) NULL,
  `subscribed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_subscribers_email` (`email`),
  UNIQUE KEY `uq_subscribers_token` (`token`),
  KEY `ix_subscribers_status` (`status`, `subscribed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `involvement_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `involvement_type` VARCHAR(60) NOT NULL DEFAULT 'volunteer',
  `organisation` VARCHAR(190) NULL,
  `message` TEXT NULL,
  `status` ENUM('new','contacted','in_progress','closed') NOT NULL DEFAULT 'new',
  `admin_notes` TEXT NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_involvement_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `donations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `donor_name` VARCHAR(150) NULL,
  `donor_email` VARCHAR(190) NULL,
  `donor_phone` VARCHAR(40) NULL,
  `amount` DECIMAL(14,2) NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'UGX',
  `method` ENUM('gofundme','bank','mobile_money','paypal','cash','other') NOT NULL DEFAULT 'other',
  `status` ENUM('pledged','received','cancelled') NOT NULL DEFAULT 'pledged',
  `source` ENUM('pledge_form','manual') NOT NULL DEFAULT 'manual',
  `reference` VARCHAR(120) NULL,
  `message` TEXT NULL,
  `is_anonymous` TINYINT(1) NOT NULL DEFAULT 0,
  `receipt_no` VARCHAR(40) NULL,
  `received_at` DATETIME NULL,
  `admin_notes` TEXT NULL,
  `ip` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_donations_receipt` (`receipt_no`),
  KEY `ix_donations_status` (`status`, `created_at`),
  KEY `ix_donations_received` (`received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------ organisation entities

CREATE TABLE IF NOT EXISTS `team_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `role_title` VARCHAR(150) NOT NULL DEFAULT '',
  `team_group` ENUM('board','staff','advisor','volunteer') NOT NULL DEFAULT 'staff',
  `bio` TEXT NULL,
  `photo` VARCHAR(255) NULL,
  `email` VARCHAR(190) NULL,
  `linkedin` VARCHAR(255) NULL,
  `x` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_team_list` (`is_active`, `team_group`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `partners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(190) NOT NULL,
  `partner_type` ENUM('partner','funder','donor','collaborator') NOT NULL DEFAULT 'partner',
  `logo` VARCHAR(255) NULL,
  `website` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_partners_list` (`is_active`, `partner_type`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logo optimisation (docs/PARTNERS_NEWSLETTER.md): additive + idempotent. `logo` stays the fallback picture (PNG/JPEG 1x, or the validated original for
-- rows saved before this feature / without GD); the columns below are the optimised variants and their size (no layout shift).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'logo_2x');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `partners` ADD COLUMN `logo_2x` VARCHAR(255) NULL AFTER `logo`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'logo_webp');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `partners` ADD COLUMN `logo_webp` VARCHAR(255) NULL AFTER `logo_2x`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'logo_webp_2x');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `partners` ADD COLUMN `logo_webp_2x` VARCHAR(255) NULL AFTER `logo_webp`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'logo_width');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `partners` ADD COLUMN `logo_width` SMALLINT UNSIGNED NULL AFTER `logo_webp_2x`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'logo_height');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `partners` ADD COLUMN `logo_height` SMALLINT UNSIGNED NULL AFTER `logo_width`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partners' AND column_name = 'logo_optimised');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `partners` ADD COLUMN `logo_optimised` TINYINT(1) NOT NULL DEFAULT 0 AFTER `logo_height`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(220) NOT NULL,
  `category` ENUM('annual_report','financial','policy','brief','newsletter','other') NOT NULL DEFAULT 'other',
  `year` SMALLINT UNSIGNED NULL,
  `description` TEXT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_name` VARCHAR(190) NULL,
  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `mime` VARCHAR(100) NULL,
  `is_public` TINYINT(1) NOT NULL DEFAULT 1,
  `download_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `published_at` DATE NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_documents_list` (`is_active`, `is_public`, `category`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(190) NOT NULL,
  `title` VARCHAR(220) NOT NULL,
  `summary` VARCHAR(500) NULL,
  `body` MEDIUMTEXT NULL,
  `image` VARCHAR(255) NULL,
  `image_alt` VARCHAR(255) NULL,
  `start_at` DATETIME NOT NULL,
  `end_at` DATETIME NULL,
  `location` VARCHAR(255) NULL,
  `registration_url` VARCHAR(500) NULL,
  `registration_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `capacity` INT UNSIGNED NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_events_slug` (`slug`),
  KEY `ix_events_list` (`is_active`, `start_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_registrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `organisation` VARCHAR(190) NULL,
  `status` ENUM('registered','attended','cancelled') NOT NULL DEFAULT 'registered',
  `notes` TEXT NULL,
  `ip` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_registration_event_email` (`event_id`, `email`),
  KEY `ix_registration_status` (`event_id`, `status`),
  CONSTRAINT `fk_registration_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(190) NOT NULL,
  `title` VARCHAR(220) NOT NULL,
  `summary` VARCHAR(500) NULL,
  `body` MEDIUMTEXT NULL,
  `image` VARCHAR(255) NULL,
  `image_alt` VARCHAR(255) NULL,
  `person_name` VARCHAR(150) NULL,
  `person_role` VARCHAR(150) NULL,
  `location` VARCHAR(190) NULL,
  `consent_confirmed` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `published_at` DATETIME NULL,
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stories_slug` (`slug`),
  KEY `ix_stories_list` (`is_active`, `consent_confirmed`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------ membership (see docs/MEMBERSHIP.md)
-- ADDITIVE and IDEMPOTENT: three new tables only, nothing existing is altered, so re-importing upgrades a live database without data loss.
-- Personal data lives here (date of birth, address, optional ID number, minors): never expose it publicly, never write it to logs.

CREATE TABLE IF NOT EXISTS `membership_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `fee_amount` DECIMAL(12,2) NULL,                 -- NULL = fee to be announced, 0 = free
  `joining_fee` DECIMAL(12,2) NULL,                -- optional one-off registration fee, separate from the recurring fee
  `currency` CHAR(3) NOT NULL DEFAULT 'UGX',
  `period` ENUM('one_time','monthly','quarterly','annual','lifetime') NOT NULL DEFAULT 'annual',
  `eligibility` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `max_members` INT UNSIGNED NULL,                 -- NULL = unlimited
  `code` VARCHAR(8) NOT NULL DEFAULT '',           -- short code in the member number (IND, YTH, ORG ...); '' = derived from the name's initials
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_membership_types_slug` (`slug`),
  KEY `ix_membership_types_list` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MEMBERSHIP CARD: the number code of a type (idempotent).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'membership_types' AND column_name = 'code');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `membership_types` ADD COLUMN `code` VARCHAR(8) NOT NULL DEFAULT ''''', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

CREATE TABLE IF NOT EXISTS `members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_ref` VARCHAR(40) NOT NULL,          -- LAF-APP-YYYY-NNNNN
  `membership_number` VARCHAR(40) NULL,            -- LAF-YYYY-NNNN, assigned on first approval
  `type_id` INT UNSIGNED NOT NULL,
  -- fee snapshot taken when the application was submitted: later fee changes never rewrite history
  `type_name` VARCHAR(150) NOT NULL DEFAULT '',
  `fee_amount` DECIMAL(12,2) NULL,
  `joining_fee` DECIMAL(12,2) NULL,
  `fee_currency` CHAR(3) NOT NULL DEFAULT 'UGX',
  `fee_period` ENUM('one_time','monthly','quarterly','annual','lifetime') NOT NULL DEFAULT 'annual',
  `status` ENUM('pending','approved','rejected','suspended','expired','cancelled') NOT NULL DEFAULT 'pending',
  -- personal
  `full_name` VARCHAR(150) NOT NULL,
  `gender` ENUM('female','male','prefer_not_to_say') NOT NULL DEFAULT 'prefer_not_to_say',
  `date_of_birth` DATE NOT NULL,
  `nationality` VARCHAR(80) NOT NULL DEFAULT '',
  `id_number` VARCHAR(60) NULL,                    -- only collected when setting membership_collect_id = 1
  -- contact
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `whatsapp` VARCHAR(40) NULL,
  `country` VARCHAR(80) NOT NULL DEFAULT '',
  `region` VARCHAR(120) NOT NULL DEFAULT '',       -- district / region
  `city` VARCHAR(120) NOT NULL DEFAULT '',         -- sub-county / city
  `address` VARCHAR(255) NULL,                     -- village / address
  -- livelihood
  `occupation_status` VARCHAR(40) NOT NULL DEFAULT '',
  `occupation_other` VARCHAR(190) NULL,
  `education_level` VARCHAR(40) NOT NULL DEFAULT '',
  `organisation` VARCHAR(190) NULL,
  -- participation
  `interests` TEXT NULL,                           -- JSON list of option keys
  `skills` TEXT NULL,
  `availability` VARCHAR(40) NOT NULL DEFAULT '',
  `heard_about` VARCHAR(40) NOT NULL DEFAULT '',
  `heard_about_other` VARCHAR(190) NULL,
  `motivation` TEXT NULL,
  -- parent / guardian (required when under 18)
  `guardian_name` VARCHAR(150) NULL,
  `guardian_phone` VARCHAR(40) NULL,
  `guardian_relationship` VARCHAR(80) NULL,
  `consent_guardian_at` DATETIME NULL,
  -- emergency contact
  `emergency_name` VARCHAR(150) NOT NULL DEFAULT '',
  `emergency_phone` VARCHAR(40) NOT NULL DEFAULT '',
  `emergency_relationship` VARCHAR(80) NOT NULL DEFAULT '',
  -- payment (text only, no uploads)
  `payment_method` VARCHAR(60) NOT NULL DEFAULT '',
  `payment_reference` VARCHAR(120) NULL,
  -- consent
  `consent_terms_at` DATETIME NULL,
  `consent_privacy_at` DATETIME NULL,
  `declaration_at` DATETIME NULL,
  `consent_updates` TINYINT(1) NOT NULL DEFAULT 0,
  `consent_ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `consent_terms_ver` VARCHAR(16) NULL,            -- short fingerprint of the terms text the applicant accepted (the text itself is editable)
  `consent_privacy_ver` VARCHAR(16) NULL,          -- same for the privacy notice
  -- administration
  `internal_notes` TEXT NULL,
  `status_reason` VARCHAR(500) NULL,
  `status_changed_at` DATETIME NULL,
  `status_changed_by` INT UNSIGNED NULL,
  `approved_by` INT UNSIGNED NULL,
  `approved_at` DATETIME NULL,
  `start_date` DATE NULL,
  `expiry_date` DATE NULL,                         -- NULL = no expiry (one_time / lifetime)
  `anonymised_at` DATETIME NULL,                   -- set when the personal data was erased on request (the number, fee snapshot and receipts stay)
  -- headshot: private file below storage/private/members/photos (random name), never public
  `photo_path` VARCHAR(190) NULL,
  `photo_updated_at` DATETIME NULL,
  `photo_consent_at` DATETIME NULL,
  `photo_original` TINYINT(1) NOT NULL DEFAULT 0,
  -- proof of payment: private file below storage/private/members/proofs
  `proof_path` VARCHAR(190) NULL,
  `proof_name` VARCHAR(190) NULL,
  `proof_mime` VARCHAR(60) NULL,
  `proof_uploaded_at` DATETIME NULL,
  `proof_status` ENUM('none','received','verified','rejected') NOT NULL DEFAULT 'none',
  `proof_verified_by` INT UNSIGNED NULL,
  `proof_verified_at` DATETIME NULL,
  -- unguessable token of the private "My membership" page (64 hex characters); NULL until first needed, blanked on erasure
  `dashboard_token` CHAR(64) NULL,
  -- membership card: issued / revoked; the PDF is a snapshot, the verification page shows the live truth
  `card_status` ENUM('none','issued','revoked') NOT NULL DEFAULT 'none',
  `card_version` INT UNSIGNED NOT NULL DEFAULT 0,
  `card_issued_at` DATETIME NULL,
  `card_emailed_at` DATETIME NULL,
  `card_emailed_version` INT UNSIGNED NOT NULL DEFAULT 0,
  `card_valid_until` DATE NULL,
  `card_verify_token` VARCHAR(64) NULL,
  `card_needs_reissue` TINYINT(1) NOT NULL DEFAULT 0,
  `card_reissue_reason` VARCHAR(40) NULL,
  `card_revoked_at` DATETIME NULL,
  `card_snapshot` MEDIUMTEXT NULL,                 -- JSON of what the issued card says (name, type, dates, fee ...): the card is a snapshot, later edits need a re-issue
  `card_photo_path` VARCHAR(190) NULL,             -- private copy of the headshot printed on the issued card (deleted on erasure)
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_members_ref` (`application_ref`),
  UNIQUE KEY `uq_members_number` (`membership_number`),
  KEY `ix_members_status` (`status`, `created_at`),
  KEY `ix_members_email` (`email`),
  KEY `ix_members_type` (`type_id`, `status`),
  KEY `ix_members_expiry` (`expiry_date`),
  UNIQUE KEY `uq_members_dashboard_token` (`dashboard_token`),
  UNIQUE KEY `uq_members_card_token` (`card_verify_token`),
  KEY `ix_members_card` (`card_status`, `status`),
  KEY `ix_members_proof` (`proof_status`),
  CONSTRAINT `fk_members_type` FOREIGN KEY (`type_id`) REFERENCES `membership_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_members_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration (idempotent): databases that created `members` before these columns existed get them here.
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'consent_terms_ver');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `consent_terms_ver` VARCHAR(16) NULL AFTER `user_agent`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'consent_privacy_ver');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `consent_privacy_ver` VARCHAR(16) NULL AFTER `consent_terms_ver`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'anonymised_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `anonymised_at` DATETIME NULL AFTER `expiry_date`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- MEMBERSHIP CARD (docs/MEMBERSHIP_CARD.md): headshot, proof of payment, dashboard token, card state. Idempotent: every column and index is added only when missing.
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'photo_path');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `photo_path` VARCHAR(190) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'photo_updated_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `photo_updated_at` DATETIME NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'photo_consent_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `photo_consent_at` DATETIME NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'photo_original');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `photo_original` TINYINT(1) NOT NULL DEFAULT 0', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'proof_path');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `proof_path` VARCHAR(190) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'proof_name');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `proof_name` VARCHAR(190) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'proof_mime');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `proof_mime` VARCHAR(60) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'proof_uploaded_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `proof_uploaded_at` DATETIME NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'proof_status');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `proof_status` ENUM(''none'',''received'',''verified'',''rejected'') NOT NULL DEFAULT ''none''', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'proof_verified_by');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `proof_verified_by` INT UNSIGNED NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'proof_verified_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `proof_verified_at` DATETIME NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'dashboard_token');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `dashboard_token` CHAR(64) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_status');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_status` ENUM(''none'',''issued'',''revoked'') NOT NULL DEFAULT ''none''', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_version');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_version` INT UNSIGNED NOT NULL DEFAULT 0', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_issued_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_issued_at` DATETIME NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_emailed_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_emailed_at` DATETIME NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_emailed_version');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_emailed_version` INT UNSIGNED NOT NULL DEFAULT 0', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_valid_until');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_valid_until` DATE NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_verify_token');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_verify_token` VARCHAR(64) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_needs_reissue');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_needs_reissue` TINYINT(1) NOT NULL DEFAULT 0', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_reissue_reason');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_reissue_reason` VARCHAR(40) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_revoked_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_revoked_at` DATETIME NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_snapshot');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_snapshot` MEDIUMTEXT NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'members' AND column_name = 'card_photo_path');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `members` ADD COLUMN `card_photo_path` VARCHAR(190) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_idx = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'members' AND index_name = 'uq_members_dashboard_token');
SET @lumina_ddl = IF(@lumina_has_idx = 0, 'ALTER TABLE `members` ADD UNIQUE KEY `uq_members_dashboard_token` (`dashboard_token`)', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_idx = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'members' AND index_name = 'uq_members_card_token');
SET @lumina_ddl = IF(@lumina_has_idx = 0, 'ALTER TABLE `members` ADD UNIQUE KEY `uq_members_card_token` (`card_verify_token`)', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_idx = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'members' AND index_name = 'ix_members_card');
SET @lumina_ddl = IF(@lumina_has_idx = 0, 'ALTER TABLE `members` ADD KEY `ix_members_card` (`card_status`, `status`)', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_idx = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'members' AND index_name = 'ix_members_proof');
SET @lumina_ddl = IF(@lumina_has_idx = 0, 'ALTER TABLE `members` ADD KEY `ix_members_proof` (`proof_status`)', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

CREATE TABLE IF NOT EXISTS `member_payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` INT UNSIGNED NOT NULL,
  `receipt_number` VARCHAR(40) NULL,               -- LAF-RCT-YYYY-NNNN, issued when a payment is marked received
  `purpose` ENUM('joining','membership','renewal','other') NOT NULL DEFAULT 'membership',
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` CHAR(3) NOT NULL DEFAULT 'UGX',
  `method` VARCHAR(60) NOT NULL DEFAULT '',
  `reference` VARCHAR(120) NULL,
  `status` ENUM('received','pending','waived') NOT NULL DEFAULT 'pending',
  `paid_at` DATETIME NULL,
  `term_start` DATE NULL,
  `term_end` DATE NULL,
  `recorded_by` INT UNSIGNED NULL,
  `notes` VARCHAR(500) NULL,
  `proof_path` VARCHAR(190) NULL,                  -- proof of payment attached by staff (private copy below storage/private/members/proofs)
  `proof_name` VARCHAR(190) NULL,
  `proof_mime` VARCHAR(60) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_member_payments_receipt` (`receipt_number`),
  KEY `ix_member_payments_member` (`member_id`, `status`),
  KEY `ix_member_payments_paid` (`paid_at`),
  CONSTRAINT `fk_member_payments_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_member_payments_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MEMBERSHIP CARD: proof of payment attached to a payment row (idempotent).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'member_payments' AND column_name = 'proof_path');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `member_payments` ADD COLUMN `proof_path` VARCHAR(190) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'member_payments' AND column_name = 'proof_name');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `member_payments` ADD COLUMN `proof_name` VARCHAR(190) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'member_payments' AND column_name = 'proof_mime');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `member_payments` ADD COLUMN `proof_mime` VARCHAR(60) NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- ======================================================================================================================
-- NEWSLETTER (see docs/NEWSLETTER.md). ADDITIVE and IDEMPOTENT: three new tables, no change to existing ones.
-- `content` is LONGTEXT holding the structured JSON of the issue (MariaDB 10.4 stores JSON as LONGTEXT too; JSON_VALID keeps it honest on
-- MariaDB and MySQL 8). Dates are stored in the application time zone (Africa/Kampala); the "is it live" test compares against a
-- timestamp bound by PHP, never against the database server's NOW().
-- ======================================================================================================================
CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(160) NOT NULL,
  `issue_number` INT UNSIGNED NULL,                 -- shown as "Issue No. N"; the editor suggests max+1; unique (ux_newsletters_number), the service gives a friendly error
  `title` VARCHAR(190) NOT NULL,
  `summary` VARCHAR(600) NULL,
  `issue_date` DATE NOT NULL,
  `cover_image` VARCHAR(255) NULL,                  -- uploads/ path (or bundled assets/ path); NULL = none
  `cover_alt` VARCHAR(190) NULL,
  `content` LONGTEXT NOT NULL,                      -- JSON, version 1 (docs/NEWSLETTER.md section 2)
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `status` ENUM('draft','scheduled','published','archived') NOT NULL DEFAULT 'draft',
  `publish_at` DATETIME NULL,                       -- scheduled issues go live when publish_at <= now (resolved lazily, no cron needed)
  `published_at` DATETIME NULL,                     -- when it actually went live
  `version` INT UNSIGNED NOT NULL DEFAULT 1,        -- optimistic locking: +1 on every content save
  `email_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `email_subject` VARCHAR(190) NULL,
  `email_preheader` VARCHAR(190) NULL,
  `email_status` ENUM('none','queued','sending','paused','sent','cancelled') NOT NULL DEFAULT 'none',
  `email_total` INT UNSIGNED NOT NULL DEFAULT 0,
  `email_sent` INT UNSIGNED NOT NULL DEFAULT 0,
  `email_failed` INT UNSIGNED NOT NULL DEFAULT 0,
  `email_started_at` DATETIME NULL,
  `email_completed_at` DATETIME NULL,
  `created_by` INT UNSIGNED NULL,
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletters_slug` (`slug`),
  KEY `ix_newsletters_status` (`status`, `publish_at`),
  KEY `ix_newsletters_issue_date` (`issue_date`),
  UNIQUE KEY `ux_newsletters_number` (`issue_number`),            -- NULLs allowed (several issues without a number); two issues can never share one
  KEY `ix_newsletters_email_status` (`email_status`),
  CONSTRAINT `chk_newsletters_content` CHECK (JSON_VALID(`content`)),
  CONSTRAINT `fk_newsletters_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_newsletters_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Upgrade of a database that ran the first build of this feature (plain index on issue_number): earlier duplicates lose their number
-- (it is optional), the plain index is replaced by a UNIQUE one. A no-op on a fresh install and when run again.
SET @lumina_has_ux = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND index_name = 'ux_newsletters_number');
SET @lumina_ddl = IF(@lumina_has_ux = 0, 'UPDATE `newsletters` n JOIN (SELECT issue_number, MIN(id) AS keep_id FROM `newsletters` WHERE issue_number IS NOT NULL GROUP BY issue_number HAVING COUNT(*) > 1) d ON d.issue_number = n.issue_number AND n.id <> d.keep_id SET n.issue_number = NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_ddl = IF(@lumina_has_ux = 0, 'ALTER TABLE `newsletters` ADD UNIQUE KEY `ux_newsletters_number` (`issue_number`)', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_ix = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND index_name = 'ix_newsletters_number');
SET @lumina_ddl = IF(@lumina_has_ix > 0, 'ALTER TABLE `newsletters` DROP INDEX `ix_newsletters_number`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- PDF issues (docs/PARTNERS_NEWSLETTER.md): additive + idempotent. kind = how the issue is read: 'online' (the block editor) or 'pdf' (an uploaded PDF).
-- An online issue may ALSO carry a PDF. The file itself lives OUTSIDE the web root (storage/private/newsletters/YYYY/MM) and is served only by the gated download route.
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND column_name = 'kind');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `newsletters` ADD COLUMN `kind` ENUM(''online'',''pdf'') NOT NULL DEFAULT ''online'' AFTER `slug`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND column_name = 'pdf_path');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `newsletters` ADD COLUMN `pdf_path` VARCHAR(255) NULL AFTER `cover_alt`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND column_name = 'pdf_name');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `newsletters` ADD COLUMN `pdf_name` VARCHAR(190) NULL AFTER `pdf_path`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND column_name = 'pdf_size');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `newsletters` ADD COLUMN `pdf_size` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `pdf_name`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND column_name = 'pdf_downloads');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `newsletters` ADD COLUMN `pdf_downloads` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `pdf_size`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND column_name = 'pdf_updated_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `newsletters` ADD COLUMN `pdf_updated_at` DATETIME NULL AFTER `pdf_downloads`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_ix = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND index_name = 'ix_newsletters_kind');
SET @lumina_ddl = IF(@lumina_has_ix = 0, 'ALTER TABLE `newsletters` ADD KEY `ix_newsletters_kind` (`kind`, `status`)', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

CREATE TABLE IF NOT EXISTS `newsletter_revisions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `newsletter_id` INT UNSIGNED NOT NULL,
  `version` INT UNSIGNED NOT NULL,                  -- the newsletters.version this snapshot was taken at
  `title` VARCHAR(190) NOT NULL,
  `content` LONGTEXT NOT NULL,                      -- JSON snapshot (content only; the issue details are not restored)
  `source` VARCHAR(20) NOT NULL DEFAULT 'save',     -- save | autosave | publish | restore
  `saved_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_newsletter_revisions_issue` (`newsletter_id`, `id`),
  CONSTRAINT `chk_newsletter_revisions_content` CHECK (JSON_VALID(`content`)),
  CONSTRAINT `fk_newsletter_revisions_issue` FOREIGN KEY (`newsletter_id`) REFERENCES `newsletters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_newsletter_revisions_user` FOREIGN KEY (`saved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `newsletter_deliveries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `newsletter_id` INT UNSIGNED NOT NULL,
  `subscriber_id` INT UNSIGNED NULL,
  `email` VARCHAR(190) NOT NULL,                    -- address of the campaign record (admin only; never logged)
  `status` ENUM('queued','sent','failed','skipped') NOT NULL DEFAULT 'queued',
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `last_error` VARCHAR(255) NULL,                   -- short reason, never contains credentials or addresses
  `sent_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletter_deliveries_issue_email` (`newsletter_id`, `email`),   -- one row per address and issue: an issue is never sent twice to the same address
  KEY `ix_newsletter_deliveries_status` (`newsletter_id`, `status`, `id`),
  CONSTRAINT `fk_newsletter_deliveries_issue` FOREIGN KEY (`newsletter_id`) REFERENCES `newsletters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_newsletter_deliveries_subscriber` FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================================================================================
-- CALENDAR & PROGRAMME SCHEDULER (see docs/CALENDAR.md). ADDITIVE and IDEMPOTENT: three new tables, no change to existing ones.
-- Dates are plain DATEs / TIMEs in the application time zone (Africa/Kampala, no daylight saving); nothing here depends on the database
-- server's time zone. Recurring entries are stored ONCE (rule in `recurrence` + `recurrence_data`); the date engine
-- (App\Services\Calendar\*) expands them, so there are no generated rows to keep in step. `calendar_overrides` changes ONE occurrence
-- (move, cancel, confirm) without touching the series. Visitor events are NOT copied: the engine reads the existing `events` table.
-- ======================================================================================================================
CREATE TABLE IF NOT EXISTS `calendar_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(60) NOT NULL,
  `name` VARCHAR(80) NOT NULL,
  `color` CHAR(7) NOT NULL DEFAULT '#5a6470',          -- #rrggbb; the accessible text / tint colours are derived (ColorContrast::pair)
  `icon` VARCHAR(40) NOT NULL DEFAULT 'calendar',      -- name from App\Core\Icons (colour is never the only cue)
  `sort_order` INT NOT NULL DEFAULT 100,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `show_on_public` TINYINT(1) NOT NULL DEFAULT 1,      -- 0 = a type for the team only (never in the public calendar, feeds or filters)
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,           -- seeded types the engine knows by slug (event, uganda-holiday): rename / recolour yes, delete no
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_calendar_types_slug` (`slug`),
  KEY `ix_calendar_types_order` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calendar_entries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `slug` VARCHAR(190) NOT NULL,
  `description` TEXT NULL,                              -- small sanitised HTML (Sanitizer::html on save, rich() on output)
  `location` VARCHAR(255) NULL,
  `all_day` TINYINT(1) NOT NULL DEFAULT 1,
  `start_date` DATE NOT NULL,                           -- first day; only a lower bound for the nth_weekday / easter / islamic rules
  `end_date` DATE NULL,                                 -- last day of a multi-day entry (NULL = one day)
  `start_time` TIME NULL,                               -- Africa/Kampala; NULL when all_day
  `end_time` TIME NULL,
  `recurrence` ENUM('none','daily','weekly','monthly','yearly','nth_weekday','easter','islamic') NOT NULL DEFAULT 'none',
  `recurrence_data` TEXT NULL,                          -- JSON object, validated by RecurrenceRules::normalise (docs/CALENDAR.md section 3)
  `repeat_until` DATE NULL,                             -- last day an occurrence may start
  `program_id` INT UNSIGNED NULL,
  `project_id` INT UNSIGNED NULL,
  `event_id` INT UNSIGNED NULL,                         -- optional link to an existing event (the event page is linked from the entry)
  `link_url` VARCHAR(500) NULL,                         -- http(s) only
  `link_label` VARCHAR(120) NULL,
  `color` CHAR(7) NULL,                                 -- optional per-entry colour override (NULL = the type colour)
  `source_label` VARCHAR(190) NULL,                     -- e.g. "Public Holidays Act (Uganda)", "United Nations"
  `source_url` VARCHAR(500) NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,            -- seeded holidays / international days: editable and hideable, never hard-deleted
  `is_public` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `needs_confirmation` TINYINT(1) NOT NULL DEFAULT 0,   -- "date to be confirmed" until an override with confirmed = 1 exists for the occurrence
  `created_by` INT UNSIGNED NULL,
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_calendar_entries_slug` (`slug`),
  KEY `ix_calendar_entries_public` (`is_active`, `is_public`, `start_date`),
  KEY `ix_calendar_entries_type` (`type_id`),
  KEY `ix_calendar_entries_program` (`program_id`),
  KEY `ix_calendar_entries_project` (`project_id`),
  KEY `ix_calendar_entries_event` (`event_id`),
  CONSTRAINT `chk_calendar_entries_data` CHECK (`recurrence_data` IS NULL OR JSON_VALID(`recurrence_data`)),
  CONSTRAINT `fk_calendar_entries_type` FOREIGN KEY (`type_id`) REFERENCES `calendar_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_calendar_entries_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_calendar_entries_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_calendar_entries_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_calendar_entries_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_calendar_entries_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calendar_overrides` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entry_id` INT UNSIGNED NOT NULL,
  `occurrence_date` DATE NOT NULL,                      -- the day the RULE produces (not the moved day): identifies the occurrence
  `new_date` DATE NULL,                                 -- moved to this day (same length); NULL = same day
  `new_start_time` TIME NULL,                           -- moved to this time (with new_end_time, may be NULL = no end time)
  `new_end_time` TIME NULL,
  `cancelled` TINYINT(1) NOT NULL DEFAULT 0,
  `confirmed` TINYINT(1) NOT NULL DEFAULT 0,            -- an admin confirmed the date (clears "date to be confirmed" for this occurrence)
  `note` VARCHAR(300) NULL,                             -- shown to visitors (e.g. "Announced by the Uganda Muslim Supreme Council")
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_calendar_overrides_occurrence` (`entry_id`, `occurrence_date`),
  KEY `ix_calendar_overrides_new_date` (`new_date`),
  CONSTRAINT `fk_calendar_overrides_entry` FOREIGN KEY (`entry_id`) REFERENCES `calendar_entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_calendar_overrides_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================================================================================
-- MEETING SCHEDULER (see docs/MEETINGS.md). ADDITIVE and IDEMPOTENT: three new tables, no change to existing ones.
-- A meeting OWNS one calendar entry (calendar_entries, type "Meeting / outreach"): the entry is created, kept in step and made public / hidden
-- by App\Services\Meetings\MeetingService; nobody else writes it. Times are plain DATETIMEs in the application time zone (Africa/Kampala = EAT,
-- UTC+3, no daylight saving); nothing depends on the database server's time zone. Attendee e-mail addresses and the join links are PERSONAL /
-- SECRET data: admin + editor only, never in public endpoints, logs or audit details.
-- ======================================================================================================================
CREATE TABLE IF NOT EXISTS `meetings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `calendar_entry_id` INT UNSIGNED NOT NULL,
  `uid` VARCHAR(120) NOT NULL,                                   -- iCalendar UID, stable for the life of the meeting: lumina-meet-<24 hex>@<host>
  `title` VARCHAR(190) NOT NULL,                                 -- cache of the calendar entry title (the meeting is the source of truth)
  `platform` ENUM('zoom','google_meet','teams','jitsi','whatsapp','phone','in_person','other') NOT NULL DEFAULT 'other',
  `join_url` VARCHAR(500) NULL,                                  -- SECRET: https only (a phone / WhatsApp meeting may use tel: or a wa.me link)
  `passcode` VARCHAR(100) NULL,
  `dial_in` VARCHAR(190) NULL,
  `location` VARCHAR(255) NULL,                                  -- address (in person)
  `description` TEXT NULL,                                       -- sanitised HTML (agenda / description as typed)
  `agenda` TEXT NULL,                                            -- plain-text version of `description` (ICS and text e-mail), derived on every save
  `start_at` DATETIME NOT NULL,                                  -- EAT; the schedule's source of truth (the calendar entry is derived)
  `end_at` DATETIME NOT NULL,                                    -- EAT, same calendar day as start_at, after it
  `organiser_name` VARCHAR(150) NOT NULL,
  `organiser_email` VARCHAR(190) NOT NULL,                       -- Reply-To and ICS ORGANIZER; never the From address
  `status` ENUM('draft','scheduled','cancelled','completed') NOT NULL DEFAULT 'draft',
  `sequence` INT UNSIGNED NOT NULL DEFAULT 0,                    -- iCalendar SEQUENCE: +1 on every change of an invitation-visible field AFTER the first invitation was sent
  `visibility` ENUM('private','public') NOT NULL DEFAULT 'private',
  `show_join_link_public` TINYINT(1) NOT NULL DEFAULT 0,
  `reminder_24h` TINYINT(1) NOT NULL DEFAULT 0,
  `reminder_1h` TINYINT(1) NOT NULL DEFAULT 0,
  `allow_rsvp` TINYINT(1) NOT NULL DEFAULT 1,
  `cancelled_reason` VARCHAR(500) NULL,
  `cancelled_at` DATETIME NULL,
  `invitations_sent_at` DATETIME NULL,                           -- first time ANY invitation was handed to the mail server
  `send_paused` TINYINT(1) NOT NULL DEFAULT 0,                   -- the queue is paused (an admin pressed Pause, or a mail-server configuration error paused it)
  `send_halt_reason` VARCHAR(255) NULL,                          -- short, password-free reason when the queue paused itself
  `created_by` INT UNSIGNED NULL,
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meetings_entry` (`calendar_entry_id`),
  UNIQUE KEY `uq_meetings_uid` (`uid`),
  KEY `ix_meetings_status_start` (`status`, `start_at`),
  KEY `ix_meetings_start` (`start_at`),
  CONSTRAINT `fk_meetings_entry` FOREIGN KEY (`calendar_entry_id`) REFERENCES `calendar_entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_meetings_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_meetings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `meeting_attendees` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meeting_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL DEFAULT '',
  `email` VARCHAR(190) NOT NULL,                                 -- lower-case; PERSONAL DATA (admin + editor only, never logged)
  `source` ENUM('manual','member','user','previous','request') NOT NULL DEFAULT 'manual',
  `member_id` INT UNSIGNED NULL,
  `user_id` INT UNSIGNED NULL,
  `token` VARCHAR(64) NOT NULL,                                  -- the invitee's credential for /meeting/{token}: 48 hex = 192 bits from random_bytes
  `rsvp` ENUM('pending','accepted','declined','tentative') NOT NULL DEFAULT 'pending',
  `rsvp_at` DATETIME NULL,
  `invite_status` ENUM('not_sent','queued','sent','failed') NOT NULL DEFAULT 'not_sent',
  `queue_kind` ENUM('invite','update','cancel') NULL,            -- what a queued row will send (NULL unless invite_status = queued)
  `invite_sequence` INT UNSIGNED NULL,                           -- SEQUENCE of the last message that was handed to the mail server (NULL = never)
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,                -- attempts for the CURRENT queued message (reset when it is queued; max 3)
  `last_error` VARCHAR(255) NULL,                                -- short reason, never credentials or addresses
  `invite_sent_at` DATETIME NULL,                                -- first time an invitation reached the mail server (NULL = never invited)
  `reminder_24h_sent_at` DATETIME NULL,
  `reminder_1h_sent_at` DATETIME NULL,
  `reminder_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,       -- failed tries of the reminder that is due now (max 3)
  `removed_at` DATETIME NULL,                                    -- removed from the meeting: the token stops working, no more mail except an optional cancellation
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meeting_attendees_email` (`meeting_id`, `email`),   -- one row per address and meeting: nobody is invited twice
  UNIQUE KEY `uq_meeting_attendees_token` (`token`),
  KEY `ix_meeting_attendees_queue` (`meeting_id`, `invite_status`, `id`),
  KEY `ix_meeting_attendees_member` (`member_id`),
  KEY `ix_meeting_attendees_user` (`user_id`),
  CONSTRAINT `fk_meeting_attendees_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_meeting_attendees_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_meeting_attendees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `meeting_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,                                 -- PERSONAL DATA (admin + editor only)
  `phone` VARCHAR(40) NULL,
  `organisation` VARCHAR(190) NULL,
  `topic` VARCHAR(120) NOT NULL,                                 -- one of the editable topics (setting meeting_request_topics), stored as the label
  `message` TEXT NOT NULL,
  `preferred_date` DATE NULL,
  `preferred_time` VARCHAR(20) NULL,                             -- time window key: morning | afternoon | evening | any (EAT)
  `duration_minutes` SMALLINT UNSIGNED NULL,
  `platform_pref` ENUM('video','phone','in_person') NULL,
  `status` ENUM('new','approved','declined','closed') NOT NULL DEFAULT 'new',
  `meeting_id` INT UNSIGNED NULL,                                -- the meeting scheduled from this request
  `handled_by` INT UNSIGNED NULL,
  `admin_notes` TEXT NULL,
  `consent_at` DATETIME NULL,
  `ip_hash` CHAR(40) NULL,                                       -- keyed hash of the visitor's IP (abuse tracking only; cannot be reversed without the application key)
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_meeting_requests_status` (`status`, `created_at`),
  KEY `ix_meeting_requests_email` (`email`),
  KEY `ix_meeting_requests_meeting` (`meeting_id`),
  CONSTRAINT `fk_meeting_requests_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_meeting_requests_user` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================================================================================
-- FORMS (see docs/FORMS.md). ADDITIVE and IDEMPOTENT: three new tables + one guarded column on `jobs`.
-- `forms.definition` and `forms.settings` are JSON stored as text and validated / normalised in PHP (App\Services\Forms\FormDefinition,
-- FormSettings), so the schema works on MariaDB 10.4 and MySQL 8 alike. Date-times are plain DATETIMEs in the application time zone
-- (Africa/Kampala = EAT, UTC+3, no daylight saving). A submission stores a SNAPSHOT of every answer (label + type + value) so history stays
-- readable after the form is edited. Submissions, answers, IP hashes and uploaded files are PERSONAL DATA: admin only (forms.submissions),
-- never in logs / audit details / sitemap / JSON endpoints; files live in storage/private/forms and are streamed by an authenticated controller.
-- ======================================================================================================================
CREATE TABLE IF NOT EXISTS `forms` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,                                   -- public address /forms/{slug}; unique, reserved words refused
  `title` VARCHAR(190) NOT NULL,
  `description` TEXT NULL,                                        -- plain text: short description (directory card, SEO fallback)
  `status` ENUM('draft','published','closed','archived') NOT NULL DEFAULT 'draft',
  `definition` LONGTEXT NOT NULL,                                 -- JSON, version 1: {"pages":[{"id","title","fields":[...]}]} (FormDefinition)
  `settings` LONGTEXT NULL,                                       -- JSON: intro, messages, notifications, theme, retention ... (FormSettings)
  `version` INT UNSIGNED NOT NULL DEFAULT 1,                      -- optimistic locking: +1 on every save
  `opens_at` DATETIME NULL,                                       -- EAT; NULL = open as soon as published
  `closes_at` DATETIME NULL,                                      -- EAT; NULL = no closing time
  `max_submissions` INT UNSIGNED NULL,                            -- NULL = unlimited
  `listed` TINYINT(1) NOT NULL DEFAULT 0,                         -- shown in the public /forms directory (when that page is switched on)
  `submissions_count` INT UNSIGNED NOT NULL DEFAULT 0,            -- kept in step by SubmissionService (store / delete / retention)
  `template_key` VARCHAR(40) NULL,                                -- template this form started from (informational)
  `published_at` DATETIME NULL,                                   -- first time it went live
  `created_by` INT UNSIGNED NULL,
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_forms_slug` (`slug`),
  KEY `ix_forms_status` (`status`, `updated_at`),
  KEY `ix_forms_listed` (`listed`, `status`),
  CONSTRAINT `fk_forms_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_forms_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `form_submissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `form_id` INT UNSIGNED NOT NULL,
  `form_version` INT UNSIGNED NOT NULL DEFAULT 1,                 -- version of the form the visitor saw
  `answers` LONGTEXT NOT NULL,                                    -- JSON list of SNAPSHOT entries: {"id","key","type","label","value","text",...} (SubmissionService::buildSnapshot)
  `search_text` MEDIUMTEXT NULL,                                  -- plain text of all answers, for the admin search (LIKE)
  `submitter_email` VARCHAR(190) NULL,                            -- lower-case; PERSONAL DATA; used for "one submission per e-mail" and the confirmation e-mail
  `context_type` VARCHAR(30) NULL,                                -- server-verified context, e.g. "job"
  `context_ref` VARCHAR(190) NULL,                                -- e.g. the job slug
  `context_label` VARCHAR(220) NULL,                              -- e.g. the job title at the time ("Applying for: ...")
  `status` VARCHAR(30) NOT NULL DEFAULT 'new',                    -- new | read | starred | shortlisted | rejected | accepted | archived | spam (labels editable per form)
  `notes` TEXT NULL,                                              -- internal notes (admin only)
  `consent_at` DATETIME NULL,                                     -- when the visitor ticked a consent box (NULL = the form has none)
  `ip_hash` CHAR(64) NULL,                                        -- keyed hash of the visitor's IP (abuse tracking only)
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_form_submissions_inbox` (`form_id`, `status`, `created_at`),
  KEY `ix_form_submissions_email` (`form_id`, `submitter_email`),
  KEY `ix_form_submissions_status` (`status`, `created_at`),
  KEY `ix_form_submissions_created` (`created_at`),
  CONSTRAINT `fk_form_submissions_form` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `form_files` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `submission_id` INT UNSIGNED NOT NULL,
  `field_id` VARCHAR(40) NOT NULL,                                -- the stable field id inside forms.definition
  `original_name` VARCHAR(255) NOT NULL,                          -- cleaned client file name (display + download name)
  `stored_path` VARCHAR(255) NOT NULL,                            -- relative to storage/private: forms/YYYY/MM/<random>.<ext>  (never web accessible)
  `mime` VARCHAR(100) NOT NULL DEFAULT 'application/octet-stream',
  `size` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_form_files_submission` (`submission_id`),
  CONSTRAINT `fk_form_files_submission` FOREIGN KEY (`submission_id`) REFERENCES `form_submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Careers integration (guarded, idempotent): a job may name the form that replaces the built-in application form. NULL = built-in form.
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'jobs' AND column_name = 'form_id');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `jobs` ADD COLUMN `form_id` INT UNSIGNED NULL AFTER `apply_email`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_fk = (SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND table_name = 'jobs' AND constraint_name = 'fk_jobs_form');
SET @lumina_ddl = IF(@lumina_has_fk = 0, 'ALTER TABLE `jobs` ADD KEY `ix_jobs_form` (`form_id`), ADD CONSTRAINT `fk_jobs_form` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- ======================================================================================================================
-- SHOP (see docs/SHOP.md). ADDITIVE and IDEMPOTENT: eight new tables, no change to an existing table.
-- Money is DECIMAL(12,2) in the shop currency (setting shop_currency, default UGX); the application computes in integer minor units
-- (App\Services\Shop\Money, 100 per unit) and PricingService is the ONLY place where an order total is calculated. Orders are PERSONAL DATA
-- (name, e-mail, phone, address): admin-gated, never in logs / audit details / sitemap / JSON endpoints. NO card data is ever stored: card and
-- mobile-money payments go through the Flutterwave hosted checkout; only the gateway reference, the status and the amount are kept.
-- Orders keep a SNAPSHOT of every line (name, option text, unit price) so editing or deleting a product never changes history.
-- ======================================================================================================================
CREATE TABLE IF NOT EXISTS `shop_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shop_categories_slug` (`slug`),
  KEY `ix_shop_categories_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(160) NOT NULL,
  `category_id` INT UNSIGNED NULL,
  `name` VARCHAR(190) NOT NULL,
  `summary` VARCHAR(400) NULL,                                    -- short description (cards, search results, social sharing)
  `description` MEDIUMTEXT NULL,                                  -- long description: sanitised rich text (Sanitizer::html), output with rich()
  `details` TEXT NULL,                                            -- "details list" on the product page: one detail per line, plain text
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,                    -- regular price in the shop currency
  `sale_price` DECIMAL(12,2) NULL,                                -- NULL = no sale; must be lower than price to count
  `sale_starts` DATETIME NULL,                                    -- EAT; NULL = from now
  `sale_ends` DATETIME NULL,                                      -- EAT; NULL = until further notice
  `sku` VARCHAR(60) NULL,
  `track_stock` TINYINT(1) NOT NULL DEFAULT 0,                    -- 0 = unlimited (made to order)
  `stock_qty` INT NULL,                                           -- units on hand when track_stock = 1 (reserved units are already subtracted)
  `low_stock` INT UNSIGNED NOT NULL DEFAULT 5,                    -- "Few left" badge and admin alert at or below this
  `options` MEDIUMTEXT NULL,                                      -- JSON option groups: [{"key","label","required","values":[{"value","label","stock"|null,"swatch"|null}]}] (ProductService::parseOptions)
  `allow_booking` TINYINT(1) NOT NULL DEFAULT 0,                  -- "Book / reserve this item" next to "Add to cart"
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_sample` TINYINT(1) NOT NULL DEFAULT 0,                      -- placeholder product shipped with the site: the admin shows "Sample product - edit or delete"
  `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  `sort_order` INT NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shop_products_slug` (`slug`),
  KEY `ix_shop_products_list` (`status`, `is_featured`, `sort_order`),
  KEY `ix_shop_products_category` (`category_id`, `status`),
  KEY `ix_shop_products_created` (`created_at`),
  CONSTRAINT `fk_shop_products_category` FOREIGN KEY (`category_id`) REFERENCES `shop_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_product_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `path` VARCHAR(255) NOT NULL,                                   -- fallback picture (JPEG/PNG): "assets/img/shop/x.jpg" (bundled) or "shop/2026/10/x.jpg" (under uploads/)
  `path_webp` VARCHAR(255) NULL,
  `path_2x` VARCHAR(255) NULL,                                    -- exactly twice the size of `path`, or NULL
  `path_webp_2x` VARCHAR(255) NULL,
  `width` SMALLINT UNSIGNED NULL,                                 -- pixel size of `path` (width / height attributes: no layout shift)
  `height` SMALLINT UNSIGNED NULL,
  `alt` VARCHAR(190) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_cover` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_shop_product_images_product` (`product_id`, `sort_order`),
  CONSTRAINT `fk_shop_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `shop_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(40) NOT NULL,                            -- shown to people and quoted as the payment reference: PREFIX-YYMMDD-XXXX
  `token` VARCHAR(64) NOT NULL,                                   -- the customer's credential for /shop/order/{token}: 40 hex = 160 bits from random_bytes
  `status` ENUM('pending','confirmed','processing','ready','shipped','completed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `payment_status` ENUM('unpaid','pending','paid','failed','refunded','review') NOT NULL DEFAULT 'unpaid',
  `payment_method` VARCHAR(30) NOT NULL DEFAULT 'book',          -- book | cod | momo | bank | flutterwave
  `customer_name` VARCHAR(150) NOT NULL,                          -- PERSONAL DATA from here to delivery_address
  `customer_email` VARCHAR(190) NOT NULL,
  `customer_phone` VARCHAR(40) NOT NULL,
  `delivery_method` ENUM('pickup','delivery') NOT NULL DEFAULT 'pickup',
  `delivery_address` TEXT NULL,
  `delivery_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` CHAR(3) NOT NULL DEFAULT 'UGX',
  `customer_note` TEXT NULL,
  `staff_note` TEXT NULL,                                         -- internal notes (admin only)
  `payment_reference` VARCHAR(120) NULL,                          -- what the customer typed (mobile-money transaction ID / bank transfer reference): to be VERIFIED by staff
  `gateway_tx_ref` VARCHAR(120) NULL,                             -- latest Flutterwave tx_ref: <order_number>-<attempt>
  `gateway_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `hold_until` DATETIME NULL,                                     -- stock reserved until then while the order is unpaid (NULL = held until staff acts / already paid)
  `consent_at` DATETIME NULL,
  `ip_hash` CHAR(64) NULL,                                        -- keyed hash of the buyer's IP (abuse tracking only)
  `paid_at` DATETIME NULL,
  `stock_released` TINYINT(1) NOT NULL DEFAULT 0,                 -- 1 once the reserved units went back on the shelf (never twice)
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shop_orders_number` (`order_number`),
  UNIQUE KEY `uq_shop_orders_token` (`token`),
  KEY `ix_shop_orders_status` (`status`, `created_at`),
  KEY `ix_shop_orders_payment` (`payment_status`, `created_at`),
  KEY `ix_shop_orders_created` (`created_at`),
  KEY `ix_shop_orders_email` (`customer_email`),
  KEY `ix_shop_orders_hold` (`hold_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NULL,                                 -- NULL once the product was deleted (the snapshot below stays)
  `name` VARCHAR(190) NOT NULL,
  `options_text` VARCHAR(255) NULL,                               -- "Size: M, Colour: Navy"
  `selection` VARCHAR(500) NULL,                                  -- JSON {"size":"m","colour":"navy"}: needed to give option stock back
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `qty` INT UNSIGNED NOT NULL DEFAULT 1,
  `line_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `sku` VARCHAR(60) NULL,
  `image` VARCHAR(255) NULL,                                      -- cover picture path at order time (receipt, e-mail)
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_shop_order_items_order` (`order_id`),
  KEY `ix_shop_order_items_product` (`product_id`),
  CONSTRAINT `fk_shop_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_shop_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `shop_products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `method` VARCHAR(30) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` CHAR(3) NOT NULL DEFAULT 'UGX',
  `reference` VARCHAR(120) NULL,                                  -- tx_ref, mobile-money transaction ID, bank reference, receipt number
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',               -- pending | success | failed | review | refunded | duplicate | superseded
  `source` ENUM('customer','staff','gateway','webhook') NOT NULL DEFAULT 'staff',
  `gateway_id` VARCHAR(40) NULL,                                  -- Flutterwave transaction id (digits)
  `user_id` INT UNSIGNED NULL,                                    -- staff member who recorded it
  `meta` VARCHAR(255) NULL,                                       -- short note (never card data, never personal data)
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_shop_payments_order` (`order_id`, `id`),
  KEY `ix_shop_payments_reference` (`reference`),
  CONSTRAINT `fk_shop_payments_order` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_order_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `kind` VARCHAR(30) NOT NULL,                                    -- created | status | payment | note | email | stock | gateway
  `message` VARCHAR(255) NOT NULL DEFAULT '',                     -- the timeline line: no customer data
  `user_id` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_shop_order_log_order` (`order_id`, `id`),
  CONSTRAINT `fk_shop_order_log_order` FOREIGN KEY (`order_id`) REFERENCES `shop_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kind` VARCHAR(40) NOT NULL,                                    -- payment_started | return_verified | webhook_ok | webhook_rejected | mismatch | duplicate | connection_test ...
  `ref` VARCHAR(120) NULL,                                        -- tx_ref / order number: never a payload, never personal data
  `order_id` INT UNSIGNED NULL,
  `status` VARCHAR(30) NULL,                                      -- successful | failed | rejected | error ...
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_shop_events_created` (`created_at`),
  KEY `ix_shop_events_ref` (`ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================================================================================
-- SOCIAL MEDIA: auto-share of published newsletters (docs/SOCIAL.md). ADDITIVE and IDEMPOTENT: two guarded ALTERs on `newsletters`, two new tables.
-- =====================================================================================================================================

-- The admin's share choices are saved WITH the issue and used when it goes public: JSON {"facebook":{"on":true,"caption":"..."}, ...}. NULL = never opened the panel
-- (the defaults of Admin > Social media apply). share_version rises only when an admin explicitly chooses "Post again".
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND column_name = 'share_choices');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `newsletters` ADD COLUMN `share_choices` TEXT NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'newsletters' AND column_name = 'share_version');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `newsletters` ADD COLUMN `share_version` INT UNSIGNED NOT NULL DEFAULT 1', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- One row per (issue, channel, share_version): the UNIQUE key is what guarantees an issue is never posted twice to the same channel (unpublish and
-- republish create nothing new; "Post again" raises share_version). The log keeps channel, status, the caption that was sent, the remote post id / URL
-- the platform returned and a SHORT error text. Never a token, never a full API payload or response.
CREATE TABLE IF NOT EXISTS `social_posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `newsletter_id` INT UNSIGNED NOT NULL,
  `channel` VARCHAR(20) NOT NULL,                                 -- facebook | instagram | x | linkedin | telegram | webhook
  `share_version` INT UNSIGNED NOT NULL DEFAULT 1,
  `status` ENUM('queued','posting','posted','failed','skipped','review') NOT NULL DEFAULT 'queued',   -- review = the attempt may or may not have reached the platform: a person decides
  `caption` TEXT NULL,                                            -- the final plain text that is (or was) sent
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `next_attempt_at` DATETIME NULL,                                -- application time zone; NULL = due now
  `last_attempt_at` DATETIME NULL,                                -- recorded BEFORE the platform is called (a crash leaves a `posting` row that becomes `review`)
  `remote_id` VARCHAR(190) NULL,
  `remote_url` VARCHAR(255) NULL,
  `last_error` VARCHAR(255) NULL,                                 -- short text without credentials
  `fail_code` VARCHAR(20) NULL,                                   -- why it failed: 'auth' = the access token / key was rejected (only these are re-queued after a new token is pasted); NULL = anything else
  `posted_at` DATETIME NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_social_posts_issue_channel` (`newsletter_id`, `channel`, `share_version`),
  KEY `ix_social_posts_due` (`status`, `next_attempt_at`),
  KEY `ix_social_posts_channel` (`channel`, `status`),
  CONSTRAINT `fk_social_posts_issue` FOREIGN KEY (`newsletter_id`) REFERENCES `newsletters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_social_posts_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Databases that already have social_posts (created before fail_code existed): guarded, idempotent ALTER.
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'social_posts' AND column_name = 'fail_code');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `social_posts` ADD COLUMN `fail_code` VARCHAR(20) NULL AFTER `last_error`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- Branded share images (GD-rendered, stored OUTSIDE the web root) and the unguessable token that makes them reachable at /social/image/{token}.jpg
-- (Instagram and Facebook must fetch a PUBLIC url). One token per (issue, variant): re-rendering keeps the token, so a url a platform already has stays valid.
CREATE TABLE IF NOT EXISTS `social_images` (
  `token` CHAR(64) NOT NULL,                                      -- 256 random bits as hex
  `newsletter_id` INT UNSIGNED NOT NULL,
  `variant` ENUM('square','portrait','landscape') NOT NULL,
  `path` VARCHAR(255) NOT NULL,                                   -- relative to storage/private/social
  `src_hash` VARCHAR(64) NULL,                                    -- hash of what the picture was drawn from (title, summary, cover, designer settings): a change re-renders it
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`token`),
  UNIQUE KEY `uq_social_images_issue_variant` (`newsletter_id`, `variant`),
  CONSTRAINT `fk_social_images_issue` FOREIGN KEY (`newsletter_id`) REFERENCES `newsletters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================================================================
-- OPPORTUNITIES (scholarships, fellowships, internships, grants ... for young people). ADDITIVE and IDEMPOTENT: two new tables, nothing existing is touched.
-- `opportunity_feeds` = the websites (RSS/Atom feeds) the site reads; `opportunities` = what visitors see: items the team typed/uploaded (source 'manual')
-- and items read from a feed (source 'feed': a short excerpt + a link back to the source, never a copy of the whole article).
-- needs_review = 1 -> read from a feed and waiting for a person to approve it (only when "approve before showing" is switched on).
-- =====================================================================================================================
CREATE TABLE IF NOT EXISTS `opportunity_feeds` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `url_hash` CHAR(40) NOT NULL,
  `default_category` VARCHAR(30) NOT NULL DEFAULT 'other',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_fetched_at` DATETIME NULL,
  `last_status` VARCHAR(20) NULL,                                 -- ok | error
  `last_message` VARCHAR(255) NULL,                               -- short text for the admin ("12 new, 3 updated" / the reason it failed)
  `last_new` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_imported` INT UNSIGNED NOT NULL DEFAULT 0,
  `fail_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_opportunity_feeds_url` (`url_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `opportunities` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(190) NOT NULL,
  `category` VARCHAR(30) NOT NULL DEFAULT 'other',                -- scholarship | fellowship | internship | grant | job | training | competition | conference | volunteer | other
  `organisation` VARCHAR(190) NULL,
  `location` VARCHAR(190) NULL,
  `summary` TEXT NULL,
  `description` MEDIUMTEXT NULL,
  `eligibility` TEXT NULL,
  `benefits` TEXT NULL,
  `how_to_apply` TEXT NULL,
  `deadline` DATE NULL,                                           -- NULL = rolling / not stated
  `apply_url` VARCHAR(500) NULL,
  `image` VARCHAR(255) NULL,
  `image_alt` VARCHAR(255) NULL,
  `file_path` VARCHAR(255) NULL,                                  -- an uploaded PDF (call text, flyer), stored privately in storage/private/opportunities
  `file_name` VARCHAR(190) NULL,
  `file_size` INT UNSIGNED NULL,
  `source` ENUM('manual','feed') NOT NULL DEFAULT 'manual',
  `feed_id` INT UNSIGNED NULL,
  `source_name` VARCHAR(190) NULL,
  `source_url` VARCHAR(500) NULL,
  `source_hash` CHAR(40) NULL,                                    -- sha1 of the source link: the same item is never imported twice
  `posted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,        -- when it was published at the source (feed) or here (manual)
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `needs_review` TINYINT(1) NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(190) NULL,
  `seo_description` VARCHAR(300) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_opportunities_slug` (`slug`),
  UNIQUE KEY `uq_opportunities_source` (`source_hash`),
  KEY `ix_opportunities_list` (`is_active`, `needs_review`, `deadline`),
  KEY `ix_opportunities_category` (`category`, `is_active`),
  KEY `ix_opportunities_feed` (`feed_id`),
  CONSTRAINT `fk_opportunities_feed` FOREIGN KEY (`feed_id`) REFERENCES `opportunity_feeds` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The starting list of websites (only when the list is empty, so a feed you deleted never comes back on a re-import).
INSERT INTO `opportunity_feeds` (`name`, `url`, `url_hash`, `default_category`)
SELECT x.`name`, x.`url`, SHA1(x.`url`), x.`cat` FROM (
  SELECT 'Opportunities For Africans' AS `name`, 'https://www.opportunitiesforafricans.com/feed/' AS `url`, 'other' AS `cat`
  UNION ALL SELECT 'Opportunity Desk', 'https://opportunitydesk.org/feed/', 'other'
  UNION ALL SELECT 'Opportunities For Youth', 'https://www.opportunitiesforyouth.org/feed/', 'other'
  UNION ALL SELECT 'Funds for NGOs', 'https://www.fundsforngos.org/feed/', 'grant'
) x WHERE NOT EXISTS (SELECT 1 FROM `opportunity_feeds`);

-- =====================================================================================================================
-- LAF ACADEMY (free ICT learning portal: courses, lessons, quizzes, learners, virtual classes, certificates). ADDITIVE and IDEMPOTENT: new tables only.
-- Learners have their OWN accounts (academy_learners: e-mail + password); facilitators and administrators are ordinary users (role "facilitator").
-- See docs/ACADEMY.md.
-- =====================================================================================================================
CREATE TABLE IF NOT EXISTS `academy_courses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `category` VARCHAR(20) NOT NULL DEFAULT 'basics',
  `level` VARCHAR(20) NOT NULL DEFAULT 'beginner',
  `icon` VARCHAR(40) NULL,
  `summary` VARCHAR(255) NULL,
  `description` MEDIUMTEXT NULL,
  `outcomes` TEXT NULL,                                           -- JSON list of strings ("You can ...")
  `requirements` TEXT NULL,                                       -- JSON list of strings
  `resources` TEXT NULL,                                          -- JSON list of {title,url}
  `image` VARCHAR(255) NULL,
  `image_alt` VARCHAR(255) NULL,
  `pass_mark` TINYINT UNSIGNED NOT NULL DEFAULT 70,               -- % needed in the final exam
  `certificate_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `facilitator_id` INT UNSIGNED NULL,                             -- the facilitator who owns / teaches the course
  `source` VARCHAR(20) NULL,                                      -- 'starter' = installed from app/config/academy/courses
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_academy_courses_slug` (`slug`),
  KEY `ix_academy_courses_list` (`status`, `sort_order`),
  KEY `ix_academy_courses_facilitator` (`facilitator_id`),
  CONSTRAINT `fk_academy_courses_facilitator` FOREIGN KEY (`facilitator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_modules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `summary` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_academy_modules_course` (`course_id`, `sort_order`),
  CONSTRAINT `fk_academy_modules_course` FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_lessons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `module_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(190) NOT NULL,
  `kind` ENUM('text','video','file','link') NOT NULL DEFAULT 'text',
  `body` MEDIUMTEXT NULL,                                         -- sanitised HTML
  `video_url` VARCHAR(255) NULL,                                  -- a YouTube link (shown as a player)
  `link_url` VARCHAR(500) NULL,                                   -- an outside page (kind = link)
  `file_path` VARCHAR(255) NULL,                                  -- an uploaded handout, stored privately (storage/private/academy)
  `file_name` VARCHAR(190) NULL,
  `file_size` INT UNSIGNED NULL,
  `minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 10,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_academy_lessons_module` (`module_id`, `sort_order`),
  KEY `ix_academy_lessons_course` (`course_id`),
  CONSTRAINT `fk_academy_lessons_course` FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_academy_lessons_module` FOREIGN KEY (`module_id`) REFERENCES `academy_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A quiz belongs to a module (practice quiz) or, with module_id NULL, is the course FINAL EXAM (at most one per course: enforced by the application).
CREATE TABLE IF NOT EXISTS `academy_quizzes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `module_id` INT UNSIGNED NULL,
  `title` VARCHAR(190) NOT NULL,
  `pass_mark` TINYINT UNSIGNED NOT NULL DEFAULT 70,
  `time_limit_min` SMALLINT UNSIGNED NULL,
  `max_attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 0,            -- 0 = unlimited
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_academy_quizzes_course` (`course_id`),
  KEY `ix_academy_quizzes_module` (`module_id`),
  CONSTRAINT `fk_academy_quizzes_course` FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_academy_quizzes_module` FOREIGN KEY (`module_id`) REFERENCES `academy_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quiz_id` INT UNSIGNED NOT NULL,
  `prompt` VARCHAR(600) NOT NULL,
  `type` ENUM('single','multiple') NOT NULL DEFAULT 'single',
  `explanation` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ix_academy_questions_quiz` (`quiz_id`, `sort_order`),
  CONSTRAINT `fk_academy_questions_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `academy_quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_options` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ix_academy_options_question` (`question_id`, `sort_order`),
  CONSTRAINT `fk_academy_options_question` FOREIGN KEY (`question_id`) REFERENCES `academy_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_learners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `country` VARCHAR(80) NULL,
  `email_verified_at` DATETIME NULL,
  `token_hash` CHAR(64) NULL,                                     -- sha256 of the one-time link sent by e-mail (verify the address / reset the password)
  `token_kind` VARCHAR(10) NULL,
  `token_expires_at` DATETIME NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_academy_learners_email` (`email`),
  KEY `ix_academy_learners_token` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_enrollments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `learner_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `enrolled_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME NULL,
  `last_lesson_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_academy_enrollments` (`learner_id`, `course_id`),
  KEY `ix_academy_enrollments_course` (`course_id`),
  CONSTRAINT `fk_academy_enrollments_learner` FOREIGN KEY (`learner_id`) REFERENCES `academy_learners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_academy_enrollments_course` FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_progress` (
  `learner_id` INT UNSIGNED NOT NULL,
  `lesson_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `completed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`learner_id`, `lesson_id`),
  KEY `ix_academy_progress_course` (`learner_id`, `course_id`),
  CONSTRAINT `fk_academy_progress_learner` FOREIGN KEY (`learner_id`) REFERENCES `academy_learners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_academy_progress_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `academy_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `learner_id` INT UNSIGNED NOT NULL,
  `quiz_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submitted_at` DATETIME NULL,
  `layout` MEDIUMTEXT NULL,                                       -- JSON: the question and answer order this learner was shown
  `answers` MEDIUMTEXT NULL,                                      -- JSON: question id => chosen option ids
  `correct` SMALLINT UNSIGNED NULL,
  `total` SMALLINT UNSIGNED NULL,
  `score_pct` DECIMAL(5,2) NULL,
  `passed` TINYINT(1) NULL,
  PRIMARY KEY (`id`),
  KEY `ix_academy_attempts_learner` (`learner_id`, `quiz_id`),
  KEY `ix_academy_attempts_quiz` (`quiz_id`),
  CONSTRAINT `fk_academy_attempts_learner` FOREIGN KEY (`learner_id`) REFERENCES `academy_learners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_academy_attempts_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `academy_quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A certificate keeps a snapshot of the learner name and the course title, so it stays valid and verifiable if the account or the course is changed.
CREATE TABLE IF NOT EXISTS `academy_certificates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(24) NOT NULL,
  `learner_id` INT UNSIGNED NULL,
  `course_id` INT UNSIGNED NULL,
  `learner_name` VARCHAR(120) NOT NULL,
  `course_title` VARCHAR(190) NOT NULL,
  `score_pct` DECIMAL(5,2) NULL,
  `hours` DECIMAL(5,1) NULL,
  `issued_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revoked_at` DATETIME NULL,
  `revoked_reason` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_academy_certificates_code` (`code`),
  KEY `ix_academy_certificates_learner` (`learner_id`),
  KEY `ix_academy_certificates_course` (`course_id`),
  CONSTRAINT `fk_academy_certificates_learner` FOREIGN KEY (`learner_id`) REFERENCES `academy_learners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_academy_certificates_course` FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_classes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(190) NOT NULL,
  `description` TEXT NULL,
  `course_id` INT UNSIGNED NULL,
  `facilitator_id` INT UNSIGNED NULL,
  `platform` VARCHAR(20) NOT NULL DEFAULT 'meet',                 -- meet | zoom | teams | jitsi | other
  `join_url` VARCHAR(500) NULL,
  `passcode` VARCHAR(60) NULL,
  `start_at` DATETIME NOT NULL,
  `end_at` DATETIME NOT NULL,
  `status` ENUM('scheduled','cancelled','completed') NOT NULL DEFAULT 'scheduled',
  `audience` VARCHAR(10) NOT NULL DEFAULT 'course',               -- course = learners enrolled in the course; all = every signed-in learner
  `recording_url` VARCHAR(500) NULL,
  `reminder_sent_at` DATETIME NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_academy_classes_when` (`status`, `start_at`),
  KEY `ix_academy_classes_course` (`course_id`),
  KEY `ix_academy_classes_facilitator` (`facilitator_id`),
  CONSTRAINT `fk_academy_classes_course` FOREIGN KEY (`course_id`) REFERENCES `academy_courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_academy_classes_facilitator` FOREIGN KEY (`facilitator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- LAF Academy PARTNER COURSES: a curated catalogue of FREE courses on other learning platforms (Coursera, edX, Google, Microsoft Learn, Harvard CS50, freeCodeCamp, Khan Academy,
-- Cisco Networking Academy ...). The Academy only links to them (title, a summary in our own words, level, cost note); the courses run on the provider site.
-- ADDITIVE and IDEMPOTENT. link_status = the last HTTP status the "Check links" button saw (404 / 410 hide the course from visitors).
CREATE TABLE IF NOT EXISTS `academy_partner_courses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(190) NOT NULL,
  `provider` VARCHAR(80) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `url_hash` CHAR(40) NOT NULL,
  `category` VARCHAR(20) NOT NULL DEFAULT 'basics',
  `level` VARCHAR(20) NOT NULL DEFAULT 'beginner',
  `duration` VARCHAR(60) NULL,                                    -- free text: "Self-paced", "6 weeks"
  `summary` VARCHAR(320) NULL,
  `language` VARCHAR(40) NOT NULL DEFAULT 'English',
  `cost` VARCHAR(16) NOT NULL DEFAULT 'free',                     -- free | free_audit (free to learn, a certificate may cost) | free_account (free with a free account)
  `certificate` VARCHAR(80) NULL,                                 -- free text: "Paid certificate", "Free badge" ...
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 100,
  `source` VARCHAR(10) NOT NULL DEFAULT 'manual',                 -- starter = from app/config/academy/partners.php
  `link_status` SMALLINT UNSIGNED NULL,
  `link_checked_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_academy_partner_url` (`url_hash`),
  KEY `ix_academy_partner_list` (`is_active`, `category`, `level`),
  KEY `ix_academy_partner_provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------------------------------------------------------------------
-- LAF Academy: VOLUNTEER FACILITATOR APPLICATIONS (public form, CV and papers kept privately, reviewed in Admin > LAF Academy > Facilitator applications).
-- Approving an application creates the facilitator account (role facilitator) and records it in user_id. The role is voluntary and unpaid.
CREATE TABLE IF NOT EXISTS `academy_facilitator_applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `country` VARCHAR(80) NULL,
  `topic` VARCHAR(20) NOT NULL,                                   -- a course topic key (CourseService::CATEGORIES)
  `topic_detail` VARCHAR(190) NULL,                               -- the particular subject within the topic
  `headline` VARCHAR(190) NULL,                                   -- current role / organisation
  `experience` TEXT NULL,
  `motivation` TEXT NULL,
  `availability` VARCHAR(190) NULL,
  `links` VARCHAR(500) NULL,                                      -- LinkedIn, portfolio, GitHub ...
  `cv_path` VARCHAR(255) NULL,
  `cv_name` VARCHAR(190) NULL,
  `docs` MEDIUMTEXT NULL,                                         -- JSON list of supporting papers: {path, name, size}
  `status` ENUM('pending','more_info','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` TEXT NULL,                                         -- internal notes, never shown to the applicant
  `applicant_note` VARCHAR(500) NULL,                             -- the message e-mailed to the applicant with the decision
  `reviewed_by` INT UNSIGNED NULL,
  `reviewed_at` DATETIME NULL,
  `user_id` INT UNSIGNED NULL,
  `consent_at` DATETIME NULL,
  `ip_hash` CHAR(40) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_academy_fapp_status` (`status`, `created_at`),
  KEY `ix_academy_fapp_email` (`email`),
  KEY `ix_academy_fapp_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------------------------------------------------------------------
-- LAF Academy: CHALLENGES (quiz challenges, innovation challenges, hackathons). The administrator opens one for a time window; learners apply, take part,
-- and the system ranks the entries and marks the winners when the challenge ends (docs/ACADEMY.md).
CREATE TABLE IF NOT EXISTS `academy_challenges` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,
  `kind` ENUM('quiz','innovation','hackathon') NOT NULL DEFAULT 'quiz',
  `title` VARCHAR(190) NOT NULL,
  `category` VARCHAR(20) NOT NULL DEFAULT 'basics',               -- the topic
  `summary` VARCHAR(320) NULL,
  `description` TEXT NULL,
  `rules` TEXT NULL,
  `prize` VARCHAR(400) NULL,
  `status` ENUM('draft','published','cancelled') NOT NULL DEFAULT 'draft',
  `apply_opens_at` DATETIME NULL,
  `apply_closes_at` DATETIME NULL,
  `starts_at` DATETIME NULL,
  `ends_at` DATETIME NULL,
  `judging_ends_at` DATETIME NULL,                                -- judged challenges: results are final at this moment (default: the end)
  `team_max` TINYINT UNSIGNED NOT NULL DEFAULT 1,                 -- 1 = individuals only
  `max_entries` SMALLINT UNSIGNED NULL,
  `approval` ENUM('auto','manual') NOT NULL DEFAULT 'auto',
  `scoring` ENUM('quiz','judged') NOT NULL DEFAULT 'quiz',
  `quiz_script` MEDIUMTEXT NULL,
  `quiz_minutes` SMALLINT UNSIGNED NULL,
  `criteria` TEXT NULL,                                           -- JSON list of {name, weight} (weights add up to 100)
  `winners_count` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `pass_score` TINYINT UNSIGNED NOT NULL DEFAULT 0,               -- minimum score (%) to qualify for a certificate of participation
  `certify_participants` TINYINT(1) NOT NULL DEFAULT 1,
  `finalized_at` DATETIME NULL,
  `source` VARCHAR(10) NOT NULL DEFAULT 'manual',
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_academy_challenges_slug` (`slug`),
  KEY `ix_academy_challenges_list` (`status`, `starts_at`),
  KEY `ix_academy_challenges_final` (`status`, `finalized_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_challenge_entries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `challenge_id` INT UNSIGNED NOT NULL,
  `learner_id` INT UNSIGNED NOT NULL,
  `team_name` VARCHAR(120) NULL,
  `members` VARCHAR(600) NULL,                                    -- names of the other team members (plain text)
  `idea_title` VARCHAR(190) NULL,
  `idea_summary` TEXT NULL,
  `status` ENUM('pending','approved','rejected','withdrawn') NOT NULL DEFAULT 'pending',
  `admin_note` VARCHAR(500) NULL,
  `photo_path` VARCHAR(255) NULL,
  `photo_consent_at` DATETIME NULL,                               -- the learner agreed to the photo being published if they win
  `publish_photo` TINYINT(1) NOT NULL DEFAULT 1,                  -- staff can hide a photo from the winners page
  `quiz_started_at` DATETIME NULL,
  `quiz_submitted_at` DATETIME NULL,
  `quiz_layout` MEDIUMTEXT NULL,                                  -- JSON: question and answer order shown to this entrant
  `quiz_answers` MEDIUMTEXT NULL,
  `quiz_correct` SMALLINT UNSIGNED NULL,
  `quiz_total` SMALLINT UNSIGNED NULL,
  `quiz_seconds` INT UNSIGNED NULL,
  `sub_title` VARCHAR(190) NULL,
  `sub_summary` TEXT NULL,
  `sub_url` VARCHAR(500) NULL,
  `sub_file_path` VARCHAR(255) NULL,
  `sub_file_name` VARCHAR(190) NULL,
  `submitted_at` DATETIME NULL,
  `score` DECIMAL(6,2) NULL,                                      -- final score out of 100 (quiz: percentage; judged: weighted average of the judges)
  `rank_no` SMALLINT UNSIGNED NULL,
  `is_winner` TINYINT(1) NOT NULL DEFAULT 0,
  `award` VARCHAR(60) NULL,                                       -- for example "1st place"
  `certificate_id` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_academy_centry` (`challenge_id`, `learner_id`),
  KEY `ix_academy_centry_learner` (`learner_id`),
  KEY `ix_academy_centry_winner` (`is_winner`, `challenge_id`),
  CONSTRAINT `fk_academy_centry_challenge` FOREIGN KEY (`challenge_id`) REFERENCES `academy_challenges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_academy_centry_learner` FOREIGN KEY (`learner_id`) REFERENCES `academy_learners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academy_challenge_judgments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entry_id` INT UNSIGNED NOT NULL,
  `judge_id` INT UNSIGNED NOT NULL,                               -- users.id of the staff member
  `scores` TEXT NOT NULL,                                         -- JSON list: points 0-10 for each criterion, in order
  `total` DECIMAL(6,2) NOT NULL,                                  -- weighted total out of 100
  `comment` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_academy_judgment` (`entry_id`, `judge_id`),
  CONSTRAINT `fk_academy_judgment_entry` FOREIGN KEY (`entry_id`) REFERENCES `academy_challenge_entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Certificates of challenges: the same table and public check page as course certificates (kind = course or challenge; award = for example "Winner, 1st place").
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_certificates' AND column_name = 'kind');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_certificates` ADD COLUMN `kind` VARCHAR(12) NOT NULL DEFAULT ''course'' AFTER `code`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_certificates' AND column_name = 'award');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_certificates` ADD COLUMN `award` VARCHAR(80) NULL AFTER `course_title`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_certificates' AND column_name = 'challenge_id');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `academy_certificates` ADD COLUMN `challenge_id` INT UNSIGNED NULL AFTER `course_id`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- ---------------------------------------------------------------------------------------------------------------------------------------
-- AMBASSADORS: people anywhere in the world who offer to raise funds and lobby for the foundation (page /involvement/ambassadors, Admin > Inbox > Ambassadors).
-- The yearly pledge is money (annual_amount in currency), in-kind items (computers, materials: inkind_items + inkind_notes) and/or something else (other_text).
CREATE TABLE IF NOT EXISTS `ambassador_applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `region` VARCHAR(40) NOT NULL,                                  -- a key of App\Services\Web\Countries::REGIONS
  `country` VARCHAR(80) NOT NULL,
  `city` VARCHAR(120) NULL,
  `occupation` VARCHAR(190) NULL,
  `organisation` VARCHAR(190) NULL,
  `profile_url` VARCHAR(300) NULL,                                -- LinkedIn, website ...
  `roles` TEXT NULL,                                              -- JSON list of the ways they can help (fundraise, lobby, ...)
  `pledge_types` VARCHAR(60) NOT NULL DEFAULT '',                 -- comma list of: money, inkind, other
  `annual_amount` DECIMAL(14,2) NULL,                             -- what they can raise / give in a year
  `currency` CHAR(3) NULL,
  `inkind_items` TEXT NULL,                                       -- JSON list of the kinds of items (computers, books ...)
  `inkind_notes` TEXT NULL,                                       -- quantity, condition, estimated value, how it would be shipped
  `other_text` TEXT NULL,                                         -- anything else they can lobby for or donate
  `network` TEXT NULL,                                            -- who they can reach
  `motivation` TEXT NULL,
  `availability` VARCHAR(80) NULL,
  `public_ok` TINYINT(1) NOT NULL DEFAULT 0,                      -- may the name and country be shown on the website once approved
  `consent_at` DATETIME NULL,
  `status` ENUM('new','reviewing','approved','on_hold','declined') NOT NULL DEFAULT 'new',
  `admin_notes` TEXT NULL,
  `reviewed_by` INT UNSIGNED NULL,
  `reviewed_at` DATETIME NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_ambassadors_status` (`status`, `created_at`),
  KEY `ix_ambassadors_region` (`region`, `country`),
  KEY `ix_ambassadors_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------------------------------------------------------------------
-- DOCUMENT SIGNATURES (Admin > Documents > Signatures): the people responsible for a policy upload a signature image once (document_signatories), an administrator attaches them
-- to a document with a role (document_signatures) and applies the signature; the system then builds a SIGNED COPY of the PDF with an "Approval and signatures" page added at
-- the end (documents.signed_path). The original file is never changed. Public visitors get the signed copy when there is one.
CREATE TABLE IF NOT EXISTS `document_signatories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `title` VARCHAR(190) NULL,                                      -- for example Chairperson of the Board
  `signature_path` VARCHAR(255) NULL,                             -- private image (PNG or JPEG) under storage/private
  `signature_mime` VARCHAR(40) NULL,
  `signature_updated_at` DATETIME NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_document_signatories_active` (`is_active`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_signatures` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_id` INT UNSIGNED NOT NULL,
  `signatory_id` INT UNSIGNED NULL,                               -- NULL = a signature block read from the PDF that waits for a person
  `role_label` VARCHAR(80) NOT NULL DEFAULT 'Approved by',
  `sort_order` INT NOT NULL DEFAULT 10,
  `signed_at` DATETIME NULL,                                      -- NULL = awaiting signature
  `signed_by` INT UNSIGNED NULL,                                  -- the administrator who applied it
  `signed_ip` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_document_signature` (`document_id`, `signatory_id`),
  KEY `ix_document_signatures_signatory` (`signatory_id`),
  CONSTRAINT `fk_document_signatures_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_document_signatures_signatory` FOREIGN KEY (`signatory_id`) REFERENCES `document_signatories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approval details and the signed copy, on the document itself (additive).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'documents' AND column_name = 'version');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `documents` ADD COLUMN `version` VARCHAR(20) NULL AFTER `description`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'documents' AND column_name = 'approval_authority');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `documents` ADD COLUMN `approval_authority` VARCHAR(120) NULL AFTER `version`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'documents' AND column_name = 'adoption_date');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `documents` ADD COLUMN `adoption_date` DATE NULL AFTER `approval_authority`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'documents' AND column_name = 'signed_path');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `documents` ADD COLUMN `signed_path` VARCHAR(255) NULL AFTER `file_size`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'documents' AND column_name = 'signed_size');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `documents` ADD COLUMN `signed_size` INT UNSIGNED NULL AFTER `signed_path`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'documents' AND column_name = 'signed_at');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `documents` ADD COLUMN `signed_at` DATETIME NULL AFTER `signed_size`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'documents' AND column_name = 'signed_mode');
SET @lumina_ddl = IF(@lumina_has_col = 0, 'ALTER TABLE `documents` ADD COLUMN `signed_mode` VARCHAR(10) NULL AFTER `signed_at`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- Podcast / live broadcast meetings: new platform values (additive, idempotent).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'meetings' AND column_name = 'platform' AND column_type LIKE '%tiktok_live%');
SET @lumina_ddl = IF(@lumina_has_col = 0 AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'meetings') = 1, 'ALTER TABLE `meetings` MODIFY COLUMN `platform` ENUM(''zoom'',''google_meet'',''teams'',''jitsi'',''whatsapp'',''phone'',''in_person'',''other'',''podcast'',''youtube_live'',''facebook_live'',''tiktok_live'',''instagram_live'') NOT NULL DEFAULT ''other''', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;

-- Signature blocks read from the PDF itself (slot_index = which block of the signature page; the role is printed in the document): a block can wait for a person (signatory_id NULL).
SET @lumina_has_col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'document_signatures' AND column_name = 'slot_index');
SET @lumina_ddl = IF(@lumina_has_col = 0 AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'document_signatures') = 1, 'ALTER TABLE `document_signatures` ADD COLUMN `slot_index` TINYINT UNSIGNED NULL AFTER `sort_order`', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
SET @lumina_nullable = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'document_signatures' AND column_name = 'signatory_id' AND is_nullable = 'NO');
SET @lumina_ddl = IF(@lumina_nullable = 1, 'ALTER TABLE `document_signatures` MODIFY COLUMN `signatory_id` INT UNSIGNED NULL', 'DO 0');
PREPARE lumina_stmt FROM @lumina_ddl;
EXECUTE lumina_stmt;
DEALLOCATE PREPARE lumina_stmt;
