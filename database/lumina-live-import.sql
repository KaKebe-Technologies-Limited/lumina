-- Lumina Africa Foundation - database import (schema + starter content)
-- Import into the database your host created (phpMyAdmin > select the database > Import). Safe to import more than once.
-- Contains NO users: create the first admin at /install (README, section Deploy).
SET NAMES utf8mb4;

-- ======================================================================
-- PART: schema.sql
-- ======================================================================

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


-- ======================================================================
-- PART: seed.sql
-- ======================================================================

-- =====================================================================================
-- Lumina Africa Foundation - seed data. Programs and projects: the owner's PLANNED-programs copy (docs/content-source/planned-programs.md).
-- Everything else: content taken from https://www.luminaafricafoundation.com/ (see docs/CONTENT_NOTES.md).
-- Owner of this file: B6 "content-seed". Source URL and caveats for every block: docs/CONTENT_NOTES.md.
--
-- IMPORT:   bin/setup.php (imports schema.sql, then this file, then adds any missing pages rows)
--           or   mysql -u root lumina < database/seed.sql
-- UTF-8:    the file is UTF-8 without BOM and starts with SET NAMES utf8mb4 (curly quotes and dashes are real characters).
--
-- IDEMPOTENT AND NON-DESTRUCTIVE:
--   * Re-running never duplicates anything and never TRUNCATEs. The only DELETEs are the two slug-scoped statements that remove the four
--     programs and four projects of the previous site (see the programs section); they are no-ops once those rows are gone.
--   * Tables with a unique natural key (settings, pages, programs, projects, activities, posts, content_blocks) use
--     INSERT ... ON DUPLICATE KEY UPDATE. An existing row is refreshed from this file ONLY while it is still pristine
--     (updated_at = created_at, i.e. nobody has edited it in the admin). Rows the owner edited are left alone.
--   * Tables without a natural unique key (stats, faqs, gallery_images, team_members) get their rows once (INSERT ... SELECT ...
--     WHERE NOT EXISTS), followed by the same pristine-only refresh UPDATE.
--   * To force every seeded row back to this file's values (development only; it overwrites admin edits):
--         mysql -u root lumina -e "SET @reseed = 1; SOURCE database/seed.sql"
-- Page COPY (headings, paragraphs, buttons) is NOT seeded: the registry defaults in app/config/pages/*.php carry the live wording.
-- A content_blocks row is written below only where the live wording differs from the registry default.
-- =====================================================================================

SET NAMES utf8mb4;
SET @reseed = IFNULL(@reseed, 0);

-- ------------------------------------------------------------------ settings (every key of app/config/settings_schema.php)
INSERT INTO settings (setting_key, setting_value) VALUES
  ('site_name', 'Lumina Africa Foundation'),
  ('tagline', 'Igniting Light, Inspiring Change.'),
  ('reg_number', 'GSD/R 1545'),
  ('founder_name', 'Moses Komakech'),
  ('footer_credit', 'Website managed by Lumina Africa Foundation.'),
  ('email_primary', 'luminaafricafoundation@gmail.com'),
  ('email_info', 'info@luminaafricafoundation.com'),
  ('phone_1', '+256-779-712-990'),
  ('phone_2', '+256-790-635-544'),
  ('whatsapp', 'https://wa.me/256779712990'),
  ('address', 'Laroo-Pece Division'),
  ('city_country', 'Gulu City, Northern Uganda'),
  ('map_embed_url', ''),
  ('notify_email', 'luminaafricafoundation@gmail.com'),
  ('social_facebook', 'https://www.facebook.com/profile.php?id=61578215262565'),
  ('social_instagram', 'https://www.instagram.com/luminaafricafdn/'),
  ('social_linkedin', 'https://www.linkedin.com/company/lumina-africa-foundation'),
  ('social_x', 'https://x.com/luminaafricafdn'),
  ('social_tiktok', 'https://www.tiktok.com/@lumina.africa.fou'),
  ('social_youtube', ''),
  ('donate_url', 'https://gofund.me/676b7a086'),
  ('donate_intro', ''),
  ('donate_bank_details', ''),
  ('donate_mobile_money', ''),
  ('donate_paypal_email', ''),
  ('logo_path', 'assets/img/brand/logo-512.png'),
  ('favicon_path', 'assets/img/brand/favicon-32.png'),
  ('og_image', 'assets/img/brand/logo-512.png'),
  ('meta_description', 'Lumina Africa Foundation is a community-based non-profit in Gulu City, Northern Uganda, developing community-centred programs for underserved communities.'),
  ('analytics_id', '')
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ pages (one row per registry page; nav order mirrors the live site)
INSERT INTO pages (slug, title, type, template, is_published, show_in_nav, show_in_footer, nav_label, nav_order, parent_slug) VALUES
  ('home', 'Home', 'registry', 'default', 1, 1, 0, 'Home', 10, NULL),
  ('about', 'About Us', 'registry', 'default', 1, 1, 1, 'About', 20, NULL),
  ('programs', 'Programs', 'registry', 'default', 1, 1, 1, 'Programs', 30, NULL),
  ('projects', 'Projects', 'registry', 'default', 1, 1, 1, 'Projects', 40, NULL),
  ('involvement', 'Involvement', 'registry', 'default', 1, 1, 1, 'Involvement', 50, NULL),
  ('donate', 'Donate', 'registry', 'default', 1, 1, 1, 'Donate', 60, NULL),
  ('blog', 'Blog', 'registry', 'default', 1, 1, 1, 'Blog', 70, NULL),
  ('contact', 'Contact', 'registry', 'default', 1, 1, 1, 'Contact', 80, NULL),
  ('activities', 'Activities', 'registry', 'default', 1, 1, 1, 'Activities', 90, NULL),
  ('privacy', 'Privacy Policy', 'registry', 'default', 1, 0, 1, 'Privacy Policy', 200, NULL),
  ('global', 'Site-wide content (header, footer, shared buttons)', 'registry', 'default', 1, 0, 0, 'Site-wide content', 9000, NULL),
  ('site_errors', 'Error pages & maintenance message', 'registry', 'default', 1, 0, 0, 'Error pages', 9010, NULL),
  ('site_newsletter', 'Newsletter messages & unsubscribe page', 'registry', 'default', 1, 0, 0, 'Newsletter messages', 9020, NULL),
  ('site_custom', 'Labels for pages you create', 'registry', 'default', 1, 0, 0, 'Created-page labels', 9030, NULL),
  ('team', 'Our Team', 'registry', 'default', 1, 1, 1, 'Our Team', 21, 'about'),
  ('partners', 'Partners', 'registry', 'default', 1, 1, 1, 'Partners', 22, 'about'),
  ('reports', 'Reports & Downloads', 'registry', 'default', 1, 1, 1, 'Reports & Downloads', 23, 'about'),
  ('careers', 'Careers', 'registry', 'default', 1, 1, 1, 'Careers', 55, 'involvement'),
  ('events', 'Events', 'registry', 'default', 1, 1, 1, 'Events', 91, 'activities'),
  ('gallery', 'Gallery', 'registry', 'default', 1, 1, 1, 'Gallery', 92, 'activities'),
  ('news', 'News & Press', 'registry', 'default', 1, 1, 1, 'News', 71, 'blog'),
  ('stories', 'Impact Stories', 'registry', 'default', 1, 1, 1, 'Impact Stories', 72, 'blog')
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  type = IF((@reseed = 1 OR updated_at = created_at), VALUES(type), type),
  template = IF((@reseed = 1 OR updated_at = created_at), VALUES(template), template),
  is_published = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_published), is_published),
  show_in_nav = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_nav), show_in_nav),
  show_in_footer = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_footer), show_in_footer),
  nav_label = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_label), nav_label),
  nav_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_order), nav_order),
  parent_slug = IF((@reseed = 1 OR updated_at = created_at), VALUES(parent_slug), parent_slug),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ stats (home page counters; the live site's own labels are swapped, see CONTENT_NOTES.md)
INSERT INTO stats (group_key, label, value_number, prefix, suffix, description, icon, sort_order, is_active)
SELECT seed.group_key, seed.label, seed.value_number, seed.prefix, seed.suffix, seed.description, seed.icon, seed.sort_order, seed.is_active FROM (
  SELECT 'home' AS group_key, 'Youth Trained' AS label, 150 AS value_number, '' AS prefix, '+' AS suffix, NULL AS description, 'users' AS icon, 10 AS sort_order, 1 AS is_active
  UNION ALL SELECT 'home', 'Active Programs', 5, '', '', NULL, 'layers', 20, 1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM stats WHERE group_key = 'home');

UPDATE stats SET value_number = 150, prefix = '', suffix = '+', description = NULL, icon = 'users', sort_order = 10, is_active = 1, updated_at = created_at
  WHERE group_key = 'home' AND label = 'Youth Trained' AND (@reseed = 1 OR updated_at = created_at);
UPDATE stats SET value_number = 5, prefix = '', suffix = '', description = NULL, icon = 'layers', sort_order = 20, is_active = 1, updated_at = created_at
  WHERE group_key = 'home' AND label = 'Active Programs' AND (@reseed = 1 OR updated_at = created_at);

-- ------------------------------------------------------------------ programs = the three PLANNED program pillars
-- Source: docs/content-source/planned-programs.md (owner-supplied, verbatim). Status is 'planned': nothing here claims funded or running work.
-- The four programs and four projects imported from the previous live site are REMOVED (owner decision). The two DELETEs below are the
-- only deletes in this file: they clean any database that was seeded before. Projects go first (their program_id FK is ON DELETE SET NULL).
DELETE FROM projects WHERE slug IN ('digital-pathways-for-youth', 'digital-employability-internship-linkages', 'inclusive-digital-access-for-out-of-school-youth', 'youth-digital-entrepreneurship-micro-income-development');
DELETE FROM programs WHERE slug IN ('digital-pathways-for-youth', 'digital-employability-internship-linkages', 'inclusive-digital-access-for-out-of-school-youth', 'youth-digital-entrepreneurship-micro-income-development');

INSERT INTO programs (slug, title, tagline, summary, body, image, image_alt, icon, status, is_featured, is_active, sort_order, seo_description) VALUES
  ('climate-resilience-environmental-sustainability', 'Climate Resilience and Environmental Sustainability', NULL, NULL, NULL, NULL, NULL, 'sprout', 'planned', 1, 1, -2, NULL),
  ('sdgs-community-development', 'Sustainable Development Goals (SDGs) and Community Development', NULL, NULL, NULL, NULL, NULL, 'target', 'planned', 1, 1, -1, NULL),
  ('health-community-wellbeing', 'Health & Community Wellbeing', 'Promoting health awareness and supporting healthier communities.', 'We envision health initiatives that improve access to reliable health information, encourage informed decision-making and promote community wellbeing.', '<p>We envision health initiatives that improve access to reliable health information, encourage informed decision-making and promote community wellbeing. Our proposed approach will emphasise awareness, education, inclusion and collaboration with qualified health professionals and relevant institutions.</p><h3>Our intended contribution</h3><p>We aim to help communities become better informed about health, strengthen awareness of preventive practices and improve knowledge of appropriate health information and services.</p><p>Implementation will depend on available resources, community priorities and appropriate professional partnerships. Clinical services will only be undertaken by qualified and authorised providers.</p>', 'assets/img/site/photo-04.jpg', 'Young people seated in the audience at a community event, some smiling and looking off to one side', 'heart', 'planned', 1, 1, 1, NULL),
  ('education-skills-youth-development', 'Education, Skills & Youth Development', 'Expanding learning opportunities and preparing young people for the future.', 'Lumina Africa Foundation intends to develop initiatives that support learning, mentorship, personal development and access to skills relevant to employment, entrepreneurship and community participation.', '<p>We believe education should equip individuals with both knowledge and practical abilities. Lumina Africa Foundation intends to develop initiatives that support learning, mentorship, personal development and access to skills relevant to employment, entrepreneurship and community participation.</p><p>Our proposed interventions will prioritise young people and underserved learners who face barriers to educational opportunities, professional guidance and skills development.</p><h3>Our intended contribution</h3><p>We aim to strengthen access to learning, improve practical competencies, build confidence and help young people prepare for further education, employment, entrepreneurship and leadership.</p>', 'assets/img/site/photo-06.jpg', 'Young people seated at desks in a lecture room, listening', 'graduation-cap', 'planned', 1, 1, 2, 'Expanding learning opportunities and preparing young people for the future.'),
  ('technology-digital-inclusion-innovation', 'Technology, Digital Inclusion & Innovation', 'Expanding access to technology and preparing young people for the digital economy.', 'Lumina Africa Foundation intends to develop digital inclusion and innovation initiatives that help underserved communities access relevant technology knowledge, build practical competencies and explore solutions to local challenges.', '<p>Technology can open pathways to education, employment, entrepreneurship and innovation. However, barriers involving access to devices, connectivity, digital skills and technical mentorship can prevent many young people from benefiting from these opportunities.</p><p>Lumina Africa Foundation intends to develop digital inclusion and innovation initiatives that help underserved communities access relevant technology knowledge, build practical competencies and explore solutions to local challenges.</p><h3>Our intended contribution</h3><p>We aspire to improve digital readiness, expand access to technology learning, encourage innovation and help young people explore opportunities in the digital economy.</p>', 'assets/img/site/photo-03.jpg', 'A man in a blue polo shirt points at a computer screen while young people wearing headphones work at computers around him', 'laptop', 'planned', 1, 1, 3, 'Expanding access to technology and preparing young people for the digital economy.')
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  tagline = IF((@reseed = 1 OR updated_at = created_at), VALUES(tagline), tagline),
  summary = IF((@reseed = 1 OR updated_at = created_at), VALUES(summary), summary),
  body = IF((@reseed = 1 OR updated_at = created_at), VALUES(body), body),
  image = IF((@reseed = 1 OR updated_at = created_at), VALUES(image), image),
  image_alt = IF((@reseed = 1 OR updated_at = created_at), VALUES(image_alt), image_alt),
  icon = IF((@reseed = 1 OR updated_at = created_at), VALUES(icon), icon),
  status = IF((@reseed = 1 OR updated_at = created_at), VALUES(status), status),
  is_featured = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_featured), is_featured),
  is_active = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_active), is_active),
  sort_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(sort_order), sort_order),
  seo_description = IF((@reseed = 1 OR updated_at = created_at), VALUES(seo_description), seo_description),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ projects = the 27 PLANNED "proposed areas of intervention" (7 Health, 9 Education, 11 Technology)
-- title = text before the colon, summary/body = the sentence after it, both verbatim. program_id is resolved by pillar slug.
INSERT INTO projects (program_id, slug, title, tagline, summary, body, image, image_alt, status, location, is_featured, is_active, sort_order) VALUES
  ((SELECT id FROM programs WHERE slug = 'climate-resilience-environmental-sustainability'), 'climate-change-awareness-and-community-adaptation', 'Climate change awareness and community adaptation', NULL, NULL, NULL, NULL, NULL, 'planned', NULL, 1, 1, 1),
  ((SELECT id FROM programs WHERE slug = 'climate-resilience-environmental-sustainability'), 'tree-planting-ecosystem-restoration-and-biodiversity-conservation', 'Tree planting, ecosystem restoration and biodiversity conservation', NULL, NULL, NULL, NULL, NULL, 'planned', NULL, 1, 1, 2),
  ((SELECT id FROM programs WHERE slug = 'climate-resilience-environmental-sustainability'), 'climate-smart-agriculture-and-sustainable-livelihoods', 'Climate-smart agriculture and sustainable livelihoods', NULL, NULL, NULL, NULL, NULL, 'planned', NULL, 1, 1, 3),
  ((SELECT id FROM programs WHERE slug = 'climate-resilience-environmental-sustainability'), 'youth-led-green-innovation-and-environmental-protection', 'Youth-led green innovation and environmental protection', NULL, NULL, NULL, NULL, NULL, 'planned', NULL, 1, 1, 4),
  ((SELECT id FROM programs WHERE slug = 'climate-resilience-environmental-sustainability'), 'community-preparedness-and-resilience-to-climate-related-disasters', 'Community preparedness and resilience to climate-related disasters', NULL, NULL, NULL, NULL, NULL, 'planned', NULL, 1, 1, 5),
  ((SELECT id FROM programs WHERE slug = 'sdgs-community-development'), 'localizing-the-un-sustainable-development-goals-in-community-initiatives', 'Localizing the UN Sustainable Development Goals in community initiatives', NULL, NULL, NULL, NULL, NULL, 'planned', NULL, 1, 1, 1),
  ((SELECT id FROM programs WHERE slug = 'sdgs-community-development'), 'youth-engagement-in-sustainable-development-and-policy-advocacy', 'Youth engagement in sustainable development and policy advocacy', NULL, NULL, NULL, NULL, NULL, 'planned', NULL, 1, 1, 2),
  ((SELECT id FROM programs WHERE slug = 'sdgs-community-development'), 'partnerships-that-advance-education-health-gender-equality-and-decent-work', 'Partnerships that advance education, health, gender equality and decent work', NULL, NULL, NULL, NULL, NULL, 'planned', NULL, 1, 1, 3),
  ((SELECT id FROM programs WHERE slug = 'sdgs-community-development'), 'community-led-projects-addressing-poverty-inequality-and-environmental-challenges', 'Community-led projects addressing poverty, inequality and environmental challenges', NULL, NULL, NULL, NULL, NULL, 'planned', NULL, 1, 1, 4),
  ((SELECT id FROM programs WHERE slug = 'sdgs-community-development'), 'monitoring-evaluation-and-reporting-on-measurable-contributions-to-the-sdgs', 'Monitoring, evaluation and reporting on measurable contributions to the SDGs', NULL, NULL, NULL, NULL, NULL, 'planned', NULL, 1, 1, 5),
  ((SELECT id FROM programs WHERE slug = 'health-community-wellbeing'), 'community-health-education', 'Community Health Education', NULL, 'Develop awareness activities that help community members access reliable health information and make informed decisions.', '<p>Develop awareness activities that help community members access reliable health information and make informed decisions.</p>', NULL, NULL, 'planned', NULL, 0, 1, 1),
  ((SELECT id FROM programs WHERE slug = 'health-community-wellbeing'), 'youth-health-wellbeing', 'Youth Health & Wellbeing', NULL, 'Explore youth-focused health education initiatives that respond to relevant community needs.', '<p>Explore youth-focused health education initiatives that respond to relevant community needs.</p>', NULL, NULL, 'planned', NULL, 0, 1, 2),
  ((SELECT id FROM programs WHERE slug = 'health-community-wellbeing'), 'mental-health-awareness', 'Mental Health Awareness', NULL, 'Promote informed conversations about mental wellbeing, stigma reduction and available support resources.', '<p>Promote informed conversations about mental wellbeing, stigma reduction and available support resources.</p>', NULL, NULL, 'planned', NULL, 0, 1, 3),
  ((SELECT id FROM programs WHERE slug = 'health-community-wellbeing'), 'maternal-family-health-awareness', 'Maternal & Family Health Awareness', NULL, 'Explore community education initiatives addressing maternal health, family wellbeing and access to appropriate information.', '<p>Explore community education initiatives addressing maternal health, family wellbeing and access to appropriate information.</p>', NULL, NULL, 'planned', NULL, 0, 1, 4),
  ((SELECT id FROM programs WHERE slug = 'health-community-wellbeing'), 'preventive-health-healthy-living', 'Preventive Health & Healthy Living', NULL, 'Promote awareness of preventive practices and behaviours that support healthier communities.', '<p>Promote awareness of preventive practices and behaviours that support healthier communities.</p>', NULL, NULL, 'planned', NULL, 0, 1, 5),
  ((SELECT id FROM programs WHERE slug = 'health-community-wellbeing'), 'digital-health-information', 'Digital Health Information', NULL, 'Explore responsible ways to use digital platforms to improve access to trustworthy health information.', '<p>Explore responsible ways to use digital platforms to improve access to trustworthy health information.</p>', NULL, NULL, 'planned', NULL, 0, 1, 6),
  ((SELECT id FROM programs WHERE slug = 'health-community-wellbeing'), 'community-health-outreach', 'Community Health Outreach', NULL, 'Seek partnerships with health facilities, qualified professionals and relevant organisations to support community outreach and awareness initiatives.', '<p>Seek partnerships with health facilities, qualified professionals and relevant organisations to support community outreach and awareness initiatives.</p>', NULL, NULL, 'planned', NULL, 0, 1, 7),
  ((SELECT id FROM programs WHERE slug = 'education-skills-youth-development'), 'education-support', 'Education Support', NULL, 'Explore initiatives that help address barriers to learning for vulnerable children and underserved learners.', '<p>Explore initiatives that help address barriers to learning for vulnerable children and underserved learners.</p>', NULL, NULL, 'planned', NULL, 0, 1, 8),
  ((SELECT id FROM programs WHERE slug = 'education-skills-youth-development'), 'digital-literacy', 'Digital Literacy', NULL, 'Plan foundational computer training covering computer use, office productivity tools, internet literacy and digital communication.', '<p>Plan foundational computer training covering computer use, office productivity tools, internet literacy and digital communication.</p>', NULL, NULL, 'planned', NULL, 0, 1, 9),
  ((SELECT id FROM programs WHERE slug = 'education-skills-youth-development'), 'career-guidance', 'Career Guidance', NULL, 'Develop activities that introduce young people to career pathways, educational opportunities and professional development.', '<p>Develop activities that introduce young people to career pathways, educational opportunities and professional development.</p>', NULL, NULL, 'planned', NULL, 0, 1, 10),
  ((SELECT id FROM programs WHERE slug = 'education-skills-youth-development'), 'employability-job-readiness', 'Employability & Job Readiness', NULL, 'Plan training in CV writing, interview preparation, workplace communication, professional conduct and job-search skills.', '<p>Plan training in CV writing, interview preparation, workplace communication, professional conduct and job-search skills.</p>', NULL, NULL, 'planned', NULL, 0, 1, 11),
  ((SELECT id FROM programs WHERE slug = 'education-skills-youth-development'), 'youth-mentorship', 'Youth Mentorship', NULL, 'Build a network of professionals, educators and experienced individuals who can guide young people in their personal and professional development.', '<p>Build a network of professionals, educators and experienced individuals who can guide young people in their personal and professional development.</p>', NULL, NULL, 'planned', NULL, 0, 1, 12),
  ((SELECT id FROM programs WHERE slug = 'education-skills-youth-development'), 'leadership-personal-development', 'Leadership & Personal Development', NULL, 'Develop opportunities for young people to strengthen communication, teamwork, critical thinking, responsibility and leadership.', '<p>Develop opportunities for young people to strengthen communication, teamwork, critical thinking, responsibility and leadership.</p>', NULL, NULL, 'planned', NULL, 0, 1, 13),
  ((SELECT id FROM programs WHERE slug = 'education-skills-youth-development'), 'internship-experiential-learning', 'Internship & Experiential Learning', NULL, 'Seek partnerships with businesses, institutions and organisations to facilitate practical learning and workplace exposure.', '<p>Seek partnerships with businesses, institutions and organisations to facilitate practical learning and workplace exposure.</p>', NULL, NULL, 'planned', NULL, 0, 1, 14),
  ((SELECT id FROM programs WHERE slug = 'education-skills-youth-development'), 'girls-education-young-womens-empowerment', 'Girls'' Education & Young Women''s Empowerment', NULL, 'Promote equitable access to learning, mentorship, skills development and leadership opportunities.', '<p>Promote equitable access to learning, mentorship, skills development and leadership opportunities.</p>', NULL, NULL, 'planned', NULL, 0, 1, 15),
  ((SELECT id FROM programs WHERE slug = 'education-skills-youth-development'), 'inclusive-learning', 'Inclusive Learning', NULL, 'Explore approaches that respond to the needs of underserved learners, including persons with disabilities.', '<p>Explore approaches that respond to the needs of underserved learners, including persons with disabilities.</p>', NULL, NULL, 'planned', NULL, 0, 1, 16),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'ict-skills-computer-literacy', 'ICT Skills & Computer Literacy', NULL, 'Plan foundational training in computer operations, file management, office applications, internet use and digital communication.', '<p>Plan foundational training in computer operations, file management, office applications, internet use and digital communication.</p>', NULL, NULL, 'planned', NULL, 0, 1, 17),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'digital-inclusion-access', 'Digital Inclusion & Access', NULL, 'Explore partnerships and resource mobilisation initiatives to improve access to digital learning, equipment and relevant learning resources.', '<p>Explore partnerships and resource mobilisation initiatives to improve access to digital learning, equipment and relevant learning resources.</p>', NULL, NULL, 'planned', NULL, 0, 1, 18),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'software-development', 'Software Development', NULL, 'Develop opportunities for introductory coding, web development, application development and practical problem-solving.', '<p>Develop opportunities for introductory coding, web development, application development and practical problem-solving.</p>', NULL, NULL, 'planned', NULL, 0, 1, 19),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'artificial-intelligence-emerging-technologies', 'Artificial Intelligence & Emerging Technologies', NULL, 'Plan accessible learning activities on AI, emerging technologies, responsible technology use and real-world applications.', '<p>Plan accessible learning activities on AI, emerging technologies, responsible technology use and real-world applications.</p>', NULL, NULL, 'planned', NULL, 0, 1, 20),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'digital-employability', 'Digital Employability', NULL, 'Explore digital skills pathways relevant to modern workplaces, remote work, freelancing and online opportunities.', '<p>Explore digital skills pathways relevant to modern workplaces, remote work, freelancing and online opportunities.</p>', NULL, NULL, 'planned', NULL, 0, 1, 21),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'innovation-problem-solving', 'Innovation & Problem-Solving', NULL, 'Encourage young people to identify community challenges and develop practical, locally relevant solutions.', '<p>Encourage young people to identify community challenges and develop practical, locally relevant solutions.</p>', NULL, NULL, 'planned', NULL, 0, 1, 22),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'digital-entrepreneurship', 'Digital Entrepreneurship', NULL, 'Plan learning activities covering online business, digital marketing, digital products and technology-enabled enterprise development.', '<p>Plan learning activities covering online business, digital marketing, digital products and technology-enabled enterprise development.</p>', NULL, NULL, 'planned', NULL, 0, 1, 23),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'cybersecurity-digital-safety', 'Cybersecurity & Digital Safety', NULL, 'Promote awareness of online safety, privacy, cyber threats, responsible digital behaviour and data protection.', '<p>Promote awareness of online safety, privacy, cyber threats, responsible digital behaviour and data protection.</p>', NULL, NULL, 'planned', NULL, 0, 1, 24),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'robotics-automation', 'Robotics & Automation', NULL, 'Explore practical exposure to robotics, automation and related technologies as resources and partnerships become available.', '<p>Explore practical exposure to robotics, automation and related technologies as resources and partnerships become available.</p>', NULL, NULL, 'planned', NULL, 0, 1, 25),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'technology-for-community-development', 'Technology for Community Development', NULL, 'Investigate how digital tools can support education, health awareness, access to information and local development.', '<p>Investigate how digital tools can support education, health awareness, access to information and local development.</p>', NULL, NULL, 'planned', NULL, 0, 1, 26),
  ((SELECT id FROM programs WHERE slug = 'technology-digital-inclusion-innovation'), 'digital-innovation-activities', 'Digital Innovation Activities', NULL, 'Explore workshops, bootcamps, innovation challenges and peer-learning opportunities for young people.', '<p>Explore workshops, bootcamps, innovation challenges and peer-learning opportunities for young people.</p>', NULL, NULL, 'planned', NULL, 0, 1, 27)
ON DUPLICATE KEY UPDATE
  program_id = IF((@reseed = 1 OR updated_at = created_at), VALUES(program_id), program_id),
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  tagline = IF((@reseed = 1 OR updated_at = created_at), VALUES(tagline), tagline),
  summary = IF((@reseed = 1 OR updated_at = created_at), VALUES(summary), summary),
  body = IF((@reseed = 1 OR updated_at = created_at), VALUES(body), body),
  image = IF((@reseed = 1 OR updated_at = created_at), VALUES(image), image),
  image_alt = IF((@reseed = 1 OR updated_at = created_at), VALUES(image_alt), image_alt),
  status = IF((@reseed = 1 OR updated_at = created_at), VALUES(status), status),
  location = IF((@reseed = 1 OR updated_at = created_at), VALUES(location), location),
  is_featured = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_featured), is_featured),
  is_active = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_active), is_active),
  sort_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(sort_order), sort_order),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ activities (live /activities: the one real activity)
INSERT INTO activities (slug, title, summary, body, image, image_alt, activity_date, location, is_active, sort_order) VALUES
  ('visit-to-wii-quarry-site', 'Visit to Wii Got Quarry Site', 'Our Director joined the Delegation of the Northern Region Youth Parliament for a dynamic outreach event at the Wii Got Quarry Site in Gulu City, together with youth leaders from the EU Youth Sounding Board and representatives of the 8th Northern Regional Youth Parliament.', '<p>We are thrilled to share that our Director has joined the incredible team of the Delegation of the Northern Region Youth Parliament! He had the amazing opportunity to participate in a dynamic outreach event at the Wii Got Quarry Site in Gulu City, where he collaborated passionately with enthusiastic youth leaders from the EU Youth Sounding Board and the representatives of the 8th Northern Regional Youth Parliament. This exciting initiative aimed to empower and inspire our vibrant young participants!</p><p>We engaged the quarry youth in stimulating discussions about environmental sustainability and community development while providing them with essential safety gear to keep them safe and sound during all activities. Plus, in line with our commitment to fostering sustainability, the event was highlighted by the donation and planting of 20 tree seedlings! These seedlings symbolize hope for a brighter, greener future and deepen our understanding of the importance of environmental stewardship.</p><p>This incredible collective effort showcases our strong belief that young people can lead the charge for positive change in their communities, embracing active roles in environmental protection and social responsibility!</p><p><a href="/blog/empowering-voices-planting-hope-lumina-africa-foundations-weekend-at-wii-got-quarry">Read the full blog post</a></p>', NULL, NULL, NULL, 'Wii Got Quarry Site, Laroo, Gulu City', 1, 10)
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  summary = IF((@reseed = 1 OR updated_at = created_at), VALUES(summary), summary),
  body = IF((@reseed = 1 OR updated_at = created_at), VALUES(body), body),
  image = IF((@reseed = 1 OR updated_at = created_at), VALUES(image), image),
  image_alt = IF((@reseed = 1 OR updated_at = created_at), VALUES(image_alt), image_alt),
  activity_date = IF((@reseed = 1 OR updated_at = created_at), VALUES(activity_date), activity_date),
  location = IF((@reseed = 1 OR updated_at = created_at), VALUES(location), location),
  is_active = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_active), is_active),
  sort_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(sort_order), sort_order),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ posts (live /blog: two posts; the second one is imported as a DRAFT, see CONTENT_NOTES.md)
INSERT INTO posts (post_type, slug, title, excerpt, body, cover_image, cover_alt, category, status, published_at, author_name, is_featured, seo_title, seo_description) VALUES
  ('blog', 'empowering-voices-planting-hope-lumina-africa-foundations-weekend-at-wii-got-quarry', 'Empowering Voices, Planting Hope – Lumina Africa Foundation’s Weekend at Wii Got Quarry', 'This past weekend, Lumina Africa Foundation joined hands with youth leaders from the EU Youth Sounding Board and the 8th Northern Regional Youth Parliament for an inspiring outreach at the Wii Got Quarry Site in Laroo, Gulu City.', '<h2>About the Outreach at Wii Got Quarry Site, Gulu City</h2><p>At Lumina Africa Foundation, we believe that real change begins at the grassroots, where young people live, work, and dream. Over the weekend, our Director <strong>Moses Komakech</strong>, together with youth leaders from the <strong>EU Youth Sounding Board</strong> and the <strong>8th Northern Regional Youth Parliament</strong>, led an outreach at <strong>Wii Got Quarry Site in Laroo, Gulu City</strong>.</p><p>The visit was designed to listen to quarry youth, understand their realities, and co-create solutions that enhance both their livelihoods and well-being.</p><h2>Why We Visited the Quarry Youth</h2><p>The youth at Wii Got Quarry are hardworking and resilient, yet they face daily challenges such as unsafe working conditions, lack of protective gear, and limited opportunities for growth. As a Foundation that champions <strong>youth empowerment, community development, and sustainability</strong>, we saw it as an important step to directly engage them, hear their voices, and provide immediate support while exploring long-term solutions.</p><h2>What We Did</h2><h3>1. Dialogue and Engagement</h3><ul><li>Conducted <strong>interactive discussions, interviews, and questionnaires</strong> with the quarry youth to capture their challenges and aspirations.</li><li>Created a safe space where they could freely share their experiences and suggest solutions.</li></ul><h3>2. Safety and Protection</h3><ul><li>Distributed <strong>protective gloves and reflector jackets</strong> to enhance their safety while working at the quarry.</li><li>Used the reflector jackets as a symbol to spread awareness about youth rights, safety, and leadership engagement.</li></ul><h3>3. Environmental Sustainability</h3><ul><li>Donated and planted <strong>20 tree seedlings</strong> within the quarry site and surrounding areas.</li><li>Encouraged quarry youth to embrace environmental stewardship as part of building a sustainable future.</li></ul><h2>Our Impact</h2><ul><li><strong>Direct beneficiaries:</strong> Quarry youth who received protective gear and participated in the tree planting activity.</li><li><strong>Community awareness:</strong> Broadened understanding of the role of the Regional Youth Parliament and Lumina Africa Foundation in amplifying youth voices.</li><li><strong>Environmental contribution:</strong> Set the foundation for a greener quarry site and a cleaner environment through tree planting.</li></ul><h2>Looking Ahead</h2><p>This initiative is just the beginning. Lumina Africa Foundation is committed to:</p><ul><li>Continuing similar outreach programs in other vulnerable communities.</li><li>Strengthening partnerships with youth networks, local leaders, and organizations to scale impact.</li><li>Designing programs that address both <strong>immediate needs</strong> (such as safety and protection) and <strong>long-term empowerment</strong> (such as skills development, advocacy, and environmental action).</li></ul><h2>Conclusion</h2><p>The visit to Wii Got Quarry was a <strong>powerful reminder that young people must be at the center of change</strong>. By listening to their voices, acting on their challenges, and involving them in solutions, we can build resilient, empowered, and sustainable communities.</p><p>At Lumina Africa Foundation, we remain steadfast in our mission to <strong>uplift youth, protect their rights, and create opportunities that inspire hope and transformation</strong>.</p><p><strong>Together, we are empowering voices, protecting lives, and planting hope for the future.</strong></p>', NULL, NULL, 'Community Engagement', 'published', '2025-08-22 09:34:06', 'Moses Komakech', 0, NULL, 'This past weekend, Lumina Africa Foundation joined hands with youth leaders from the EU Youth Sounding Board and the 8th Northern Regional Youth Parliament for an inspiring outreach at the Wii Got Quarry Site in Laroo, Gulu City.'),
  ('blog', 'breaking-the-stigma-discussing-mental-health-in-northern-uganda', 'Breaking the Stigma: Discussing Mental Health in Northern Uganda', 'Mental health remains one of the most overlooked issues in Northern Uganda, where stigma, cultural misconceptions, and limited access to care prevent many from seeking help. This article explores the realities faced by communities struggling with trauma, stress, and mental illness, particularly in post-conflict areas.', '<h2>Understanding the Mental Health Landscape</h2><p>Mental health remains a pressing issue worldwide, and Northern Uganda is no exception. The aftermath of prolonged conflicts and socio-economic struggles has significantly impacted the mental well-being of its populace. While the stigma surrounding mental health issues continues to persist, conversations surrounding it are becoming increasingly vital. Addressing this stigma is a crucial step towards fostering better mental health awareness and support systems.</p><h2>Confronting Cultural Taboos</h2><p>In many communities within Northern Uganda, discussing mental health is often viewed through a lens of cultural taboo. Many individuals fear judgment or isolation if they openly express their mental health struggles. This societal fear contributes to a lack of understanding and acceptance of mental health conditions. By challenging these cultural norms and encouraging open discussions, individuals can begin to share their experiences, thus helping to normalize the conversation surrounding mental health. Increased awareness can ultimately lead to a more supportive environment, reducing the stigma attached to these discussions.</p><h2>Community Initiatives and Support Systems</h2><p>One of the most effective ways to break the stigma surrounding mental health is through community initiatives. Organizations and local leaders are increasingly stepping up to provide educational resources and promote mental health awareness. Workshops, seminars, and support groups allow individuals to interact and discuss their feelings in a safe space. Moreover, it is vital for healthcare professionals to be trained in culturally sensitive techniques that address mental health without judgment, thereby improving the overall discourse around this essential subject.</p><p>In addition, involving local influencers in these initiatives can help shift perceptions. Using popular culture and trusted community members to promote mental health discussions can significantly affect how issues are perceived. By harnessing these relationships, community initiatives can successfully break down barriers and encourage individuals to seek help when needed.</p><p>In conclusion, breaking the stigma associated with mental health issues in Northern Uganda requires a collective effort. By fostering open discussions, challenging cultural biases, and implementing community initiatives, we can pave the way for healthier mental health awareness. Society as a whole benefits when individuals feel accepted and supported in addressing their mental health. The journey may be long, but with persistence, the stigma surrounding mental health can eventually dissipate.</p>', NULL, NULL, NULL, 'draft', '2025-08-25 00:00:00', 'Moses Komakech', 0, NULL, NULL)
ON DUPLICATE KEY UPDATE
  post_type = IF((@reseed = 1 OR updated_at = created_at), VALUES(post_type), post_type),
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  excerpt = IF((@reseed = 1 OR updated_at = created_at), VALUES(excerpt), excerpt),
  body = IF((@reseed = 1 OR updated_at = created_at), VALUES(body), body),
  cover_image = IF((@reseed = 1 OR updated_at = created_at), VALUES(cover_image), cover_image),
  cover_alt = IF((@reseed = 1 OR updated_at = created_at), VALUES(cover_alt), cover_alt),
  category = IF((@reseed = 1 OR updated_at = created_at), VALUES(category), category),
  status = IF((@reseed = 1 OR updated_at = created_at), VALUES(status), status),
  published_at = IF((@reseed = 1 OR updated_at = created_at), VALUES(published_at), published_at),
  author_name = IF((@reseed = 1 OR updated_at = created_at), VALUES(author_name), author_name),
  is_featured = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_featured), is_featured),
  seo_title = IF((@reseed = 1 OR updated_at = created_at), VALUES(seo_title), seo_title),
  seo_description = IF((@reseed = 1 OR updated_at = created_at), VALUES(seo_description), seo_description),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ jobs: none seeded on purpose (live /careers: "Currently, there are no openings."; the volunteer pathway is page copy in the careers registry)

-- ------------------------------------------------------------------ faqs (live /donate; three answers are inactive until the foundation confirms them)
INSERT INTO faqs (group_key, question, answer, sort_order, is_active)
SELECT seed.group_key, seed.question, seed.answer, seed.sort_order, seed.is_active FROM (
  SELECT 'donate' AS group_key, 'How can I donate?' AS question, 'You can donate via mobile money, bank transfer, or international donations through PayPal/Stripe.' AS answer, 10 AS sort_order, 0 AS is_active
  UNION ALL SELECT 'donate', 'Is my donation tax-deductible?', 'Please check with your local tax authority regarding the deductibility of donations made to our organization.', 20, 1
  UNION ALL SELECT 'donate', 'What programs do donations support?', 'Our planned interventions focus on three interconnected pillars: Health, Education, and Technology & Innovation. Lumina Africa Foundation welcomes conversations with organisations and individuals interested in helping develop our proposed interventions through program funding and grants, and computers, digital equipment and learning resources.', 30, 0
  UNION ALL SELECT 'donate', 'Can I donate monthly?', 'Yes, you can set up a recurring donation to support our planned programs.', 40, 0
  UNION ALL SELECT 'donate', 'Who can I contact?', 'You can contact us through our website for any inquiries or assistance regarding donations.', 50, 1
  UNION ALL SELECT 'donate', 'What is the impact of my donation?', 'Together, we can help turn these plans into practical opportunities for the communities we serve. The initiatives described on this site represent our planned program areas and proposed interventions, and they should not be interpreted as confirmation that every activity is currently funded, operational or already delivering results.', 60, 1
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM faqs WHERE group_key = 'donate');

UPDATE faqs SET answer = 'You can donate via mobile money, bank transfer, or international donations through PayPal/Stripe.', sort_order = 10, is_active = 0, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'How can I donate?' AND (@reseed = 1 OR updated_at = created_at);
UPDATE faqs SET answer = 'Please check with your local tax authority regarding the deductibility of donations made to our organization.', sort_order = 20, is_active = 1, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'Is my donation tax-deductible?' AND (@reseed = 1 OR updated_at = created_at);
UPDATE faqs SET answer = 'Our planned interventions focus on three interconnected pillars: Health, Education, and Technology & Innovation. Lumina Africa Foundation welcomes conversations with organisations and individuals interested in helping develop our proposed interventions through program funding and grants, and computers, digital equipment and learning resources.', sort_order = 30, is_active = 0, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'What programs do donations support?' AND (@reseed = 1 OR updated_at = created_at);
UPDATE faqs SET answer = 'Yes, you can set up a recurring donation to support our planned programs.', sort_order = 40, is_active = 0, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'Can I donate monthly?' AND (@reseed = 1 OR updated_at = created_at);
UPDATE faqs SET answer = 'You can contact us through our website for any inquiries or assistance regarding donations.', sort_order = 50, is_active = 1, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'Who can I contact?' AND (@reseed = 1 OR updated_at = created_at);
UPDATE faqs SET answer = 'Together, we can help turn these plans into practical opportunities for the communities we serve. The initiatives described on this site represent our planned program areas and proposed interventions, and they should not be interpreted as confirmation that every activity is currently funded, operational or already delivering results.', sort_order = 60, is_active = 1, updated_at = created_at
  WHERE group_key = 'donate' AND question = 'What is the impact of my donation?' AND (@reseed = 1 OR updated_at = created_at);

-- ------------------------------------------------------------------ gallery_images (photos bundled under assets/img/site; the two photos of children are inactive until consent is confirmed)
INSERT INTO gallery_images (image, alt_text, caption, album, is_active, sort_order)
SELECT seed.image, seed.alt_text, seed.caption, seed.album, seed.is_active, seed.sort_order FROM (
  SELECT 'assets/img/site/photo-03.jpg' AS image, 'A man in a blue polo shirt points at a computer screen while young people wearing headphones work at computers around him' AS alt_text, 'Young people working at computers' AS caption, 'Digital learning' AS album, 1 AS is_active, 10 AS sort_order
  UNION ALL SELECT 'assets/img/site/photo-05.jpg', 'Young people seated in a bright room with laptops; one in the foreground types on a laptop', 'Young people with laptops in a classroom', 'Digital learning', 1, 20
  UNION ALL SELECT 'assets/img/site/photo-04.jpg', 'Young people seated in red theatre-style seats at an event, some smiling and looking off to one side', 'Young people at an event', 'Events and gatherings', 1, 30
  UNION ALL SELECT 'assets/img/site/photo-06.jpg', 'Young people seated at desks in a lecture room, listening', 'Young people in a lecture room', 'Events and gatherings', 1, 40
  UNION ALL SELECT 'assets/img/site/photo-02.jpg', 'Black-and-white photograph of a crowd of children looking at the camera', 'Children looking at the camera', 'Community', 0, 50
  UNION ALL SELECT 'assets/img/site/photo-08.jpg', 'Black-and-white portrait of a child holding a cardboard sign that reads HOPE', 'A child holding a sign reading HOPE', 'Community', 0, 60
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM gallery_images g WHERE g.image = seed.image);

UPDATE gallery_images SET alt_text = 'A man in a blue polo shirt points at a computer screen while young people wearing headphones work at computers around him', caption = 'Young people working at computers', album = 'Digital learning', is_active = 1, sort_order = 10, updated_at = created_at
  WHERE image = 'assets/img/site/photo-03.jpg' AND (@reseed = 1 OR updated_at = created_at);
UPDATE gallery_images SET alt_text = 'Young people seated in a bright room with laptops; one in the foreground types on a laptop', caption = 'Young people with laptops in a classroom', album = 'Digital learning', is_active = 1, sort_order = 20, updated_at = created_at
  WHERE image = 'assets/img/site/photo-05.jpg' AND (@reseed = 1 OR updated_at = created_at);
UPDATE gallery_images SET alt_text = 'Young people seated in red theatre-style seats at an event, some smiling and looking off to one side', caption = 'Young people at an event', album = 'Events and gatherings', is_active = 1, sort_order = 30, updated_at = created_at
  WHERE image = 'assets/img/site/photo-04.jpg' AND (@reseed = 1 OR updated_at = created_at);
UPDATE gallery_images SET alt_text = 'Young people seated at desks in a lecture room, listening', caption = 'Young people in a lecture room', album = 'Events and gatherings', is_active = 1, sort_order = 40, updated_at = created_at
  WHERE image = 'assets/img/site/photo-06.jpg' AND (@reseed = 1 OR updated_at = created_at);
UPDATE gallery_images SET alt_text = 'Black-and-white photograph of a crowd of children looking at the camera', caption = 'Children looking at the camera', album = 'Community', is_active = 0, sort_order = 50, updated_at = created_at
  WHERE image = 'assets/img/site/photo-02.jpg' AND (@reseed = 1 OR updated_at = created_at);
UPDATE gallery_images SET alt_text = 'Black-and-white portrait of a child holding a cardboard sign that reads HOPE', caption = 'A child holding a sign reading HOPE', album = 'Community', is_active = 0, sort_order = 60, updated_at = created_at
  WHERE image = 'assets/img/site/photo-08.jpg' AND (@reseed = 1 OR updated_at = created_at);

-- ------------------------------------------------------------------ team_members (only the founder; the other board members named on the live About page are NOT seeded)
INSERT INTO team_members (name, role_title, team_group, bio, photo, email, linkedin, x, sort_order, is_active)
SELECT seed.name, seed.role_title, seed.team_group, seed.bio, seed.photo, seed.email, seed.linkedin, seed.x, seed.sort_order, seed.is_active FROM (
  SELECT 'Moses Komakech' AS name, 'Founder' AS role_title, 'board' AS team_group, 'Moses Komakech founded Lumina Africa Foundation to bridge the digital divide and create pathways for youth to learn, work, and lead in the digital age. He is listed as Chairperson of the Board of Directors.' AS bio, NULL AS photo, NULL AS email, 'https://www.linkedin.com/in/moses-komakech-27a61120b' AS linkedin, NULL AS x, 10 AS sort_order, 1 AS is_active
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM team_members t WHERE t.name = seed.name);

UPDATE team_members SET role_title = 'Founder', team_group = 'board', bio = 'Moses Komakech founded Lumina Africa Foundation to bridge the digital divide and create pathways for youth to learn, work, and lead in the digital age. He is listed as Chairperson of the Board of Directors.', photo = NULL, email = NULL, linkedin = 'https://www.linkedin.com/in/moses-komakech-27a61120b', x = NULL, sort_order = 10, is_active = 1, updated_at = created_at
  WHERE name = 'Moses Komakech' AND (@reseed = 1 OR updated_at = created_at);

-- ------------------------------------------------------------------ content_blocks (only where the live wording differs from the registry default)
-- (none: every registry default already carries the live wording)

-- Not seeded on purpose (real data the owner must supply): partners, documents, events, event_registrations, stories, subscribers,
-- contact_messages, applications, involvement_requests, donations, users. See docs/CONTENT_NOTES.md.

-- ------------------------------------------------------------------ membership (see docs/MEMBERSHIP.md). ADDITIVE and IDEMPOTENT.
-- Settings: defaults of app/config/settings_schema.php (group "Membership"); refreshed only while pristine, like every other setting.
INSERT INTO settings (setting_key, setting_value) VALUES
  ('membership_open', '0'),   -- closed until the owner has set the fees, the terms and the privacy text (Site settings > Membership)
  ('membership_currency', 'UGX'),
  ('membership_number_prefix', 'LAF'),
  ('membership_payment_instructions', ''),
  ('membership_collect_id', '0'),
  ('membership_contact_email', '')
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- Pages: the Membership tab (header + footer, right after Involvement) and its terms page (not in the menus).
INSERT INTO pages (slug, title, type, template, is_published, show_in_nav, show_in_footer, nav_label, nav_order, parent_slug) VALUES
  ('membership', 'Membership', 'registry', 'default', 1, 1, 1, 'Membership', 52, 'involvement'),
  ('membership-terms', 'Membership terms', 'registry', 'default', 1, 0, 0, 'Membership terms', 53, NULL)
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  type = IF((@reseed = 1 OR updated_at = created_at), VALUES(type), type),
  template = IF((@reseed = 1 OR updated_at = created_at), VALUES(template), template),
  is_published = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_published), is_published),
  show_in_nav = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_nav), show_in_nav),
  show_in_footer = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_footer), show_in_footer),
  nav_label = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_label), nav_label),
  nav_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_order), nav_order),
  parent_slug = IF((@reseed = 1 OR updated_at = created_at), VALUES(parent_slug), parent_slug),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- Membership types: three PLACEHOLDERS for the owner to edit or delete. The fee is NULL on purpose (the site then says "Fee to be announced");
-- no amount is invented. Inserted only while the table is EMPTY, so a re-import never brings back a type the owner deleted or renamed.
INSERT INTO membership_types (slug, name, description, fee_amount, joining_fee, currency, period, eligibility, sort_order, is_active, max_members)
SELECT seed.slug, seed.name, seed.description, seed.fee_amount, seed.joining_fee, seed.currency, seed.period, seed.eligibility, seed.sort_order, seed.is_active, seed.max_members FROM (
  SELECT 'individual-member' AS slug, 'Individual Member' AS name, NULL AS description, NULL AS fee_amount, NULL AS joining_fee, 'UGX' AS currency, 'annual' AS period, NULL AS eligibility, 10 AS sort_order, 1 AS is_active, NULL AS max_members
  UNION ALL SELECT 'youth-student-member', 'Youth & Student Member', NULL, NULL, NULL, 'UGX', 'annual', NULL, 20, 1, NULL
  UNION ALL SELECT 'organisation-partner-member', 'Organisation / Partner Member', NULL, NULL, NULL, 'UGX', 'annual', NULL, 30, 1, NULL
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM membership_types);

-- Membership card (docs/MEMBERSHIP_CARD.md). Settings of Admin > Membership > Membership card: the defaults REPRODUCE the owner's reference card.
-- Same keys and values as App\Services\Membership\Card\CardSettings::DEFS (a selftest compares them). Refreshed only while pristine.
INSERT INTO settings (setting_key, setting_value) VALUES
  ('membership_card_org_name', 'LUMINA AFRICA FOUNDATION'),
  ('membership_card_tagline', 'Igniting Light. Inspiring Change.'),
  ('membership_card_label', 'MEMBERSHIP CARD'),
  ('membership_card_logo', ''),
  ('membership_card_navy', '#0F1B33'),
  ('membership_card_accent', '#E0642C'),
  ('membership_card_gold', '#C9973F'),
  ('membership_card_green', '#22A35A'),
  ('membership_card_tile', '#F4F6FA'),
  ('membership_card_label_color', '#6A7389'),
  ('membership_card_muted', '#B9C2DA'),
  ('membership_card_show_photo', '1'),
  ('membership_card_show_qr', '1'),
  ('membership_card_tile_1', 'member_since'),
  ('membership_card_tile_2', 'payment'),
  ('membership_card_tile_3', 'fee'),
  ('membership_card_label_member', 'MEMBER'),
  ('membership_card_label_from', 'FROM'),
  ('membership_card_label_number', 'MEMBER NUMBER'),
  ('membership_card_label_valid_until', 'VALID UNTIL'),
  ('membership_card_label_member_since', 'MEMBER SINCE'),
  ('membership_card_label_payment', 'PAYMENT'),
  ('membership_card_label_fee', '{period} MEMBERSHIP'),
  ('membership_card_label_type', 'MEMBERSHIP TYPE'),
  ('membership_card_label_district', 'DISTRICT'),
  ('membership_card_scan_text', 'Scan to verify membership'),
  ('membership_card_renews_text', 'Renews {period}'),
  ('membership_card_no_expiry_text', 'No expiry'),
  ('membership_card_status_active', 'ACTIVE MEMBER'),
  ('membership_card_status_expired', 'EXPIRED'),
  ('membership_card_status_suspended', 'SUSPENDED'),
  ('membership_card_status_revoked', 'NOT VALID'),
  ('membership_card_pay_paid', 'Confirmed'),
  ('membership_card_pay_waived', 'Waived'),
  ('membership_card_pay_pending', 'Pending'),
  ('membership_card_pay_free', 'No fee'),
  ('membership_card_pay_partial', 'Part paid'),
  ('membership_card_pay_tbd', 'Fee not set'),
  ('membership_card_fee_free', 'Free'),
  ('membership_card_fee_tbd', 'To be announced'),
  ('membership_card_fee_per_year', '/ year'),
  ('membership_card_fee_per_month', '/ month'),
  ('membership_card_fee_per_quarter', '/ quarter'),
  ('membership_card_fee_for_life', '(for life)'),
  ('membership_card_fee_one_off', '(one-off)'),
  ('membership_card_period_annual', 'ANNUAL'),
  ('membership_card_period_monthly', 'MONTHLY'),
  ('membership_card_period_quarterly', 'QUARTERLY'),
  ('membership_card_period_lifetime', 'LIFETIME'),
  ('membership_card_period_one_time', 'ONE-TIME'),
  ('membership_card_adverb_annual', 'annually'),
  ('membership_card_adverb_monthly', 'monthly'),
  ('membership_card_adverb_quarterly', 'quarterly'),
  ('membership_card_joining_paid', 'Joining fee {amount} paid'),
  ('membership_card_joining_due', 'Joining fee {amount} due'),
  ('membership_card_joining_waived', 'Joining fee {amount} waived'),
  ('membership_card_location_format', '{city}, {country}'),
  ('membership_card_panel_text', CONCAT('Present this card for member check-in', CHAR(10), 'Lumina Africa Foundation · Gulu, Uganda')),
  ('membership_card_footer_1', '{joining} · Issued {issued}'),
  ('membership_card_footer_2', 'Support: {phone} · {email}'),
  ('membership_card_footer_3', ''),
  ('membership_card_support_phone', ''),
  ('membership_card_support_email', ''),
  ('membership_card_number_format', '{PREFIX}-{TYPE}-{YY}-{SEQ}'),
  ('membership_card_trigger', 'paid_approved'),
  ('membership_card_reissue_renewal', '1'),
  ('membership_card_require_photo', '0'),
  ('membership_photo_required', '1'),
  ('membership_proof_retention_days', '90'),
  ('membership_card_email_subject', 'Your {site} membership card ({number})'),
  ('membership_card_email_body', CONCAT('Hello {first_name},', CHAR(10), '', CHAR(10), 'Your membership is confirmed and your membership card is attached to this message as a PDF. Keep it on your phone or print it.', CHAR(10), '', CHAR(10), 'Name: {name}', CHAR(10), 'Member number: {number}', CHAR(10), 'Type: {type}', CHAR(10), 'Valid until: {valid_until}', CHAR(10), '', CHAR(10), 'How to use your card: show it (printed or on your phone) when you check in at our events and meetings. Anyone can scan the QR code on it to confirm that your membership is genuine and current.', CHAR(10), '', CHAR(10), '{photo_note}Your private membership page (download your card again, upload a photo): {dashboard_url}', CHAR(10), '', CHAR(10), 'Questions? Reply to this e-mail or write to {contact_email}.', CHAR(10), '', CHAR(10), 'Thank you for standing with {site}.'))
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- Codes of the three starter membership types in the member number (LAF-IND-26-0001). Only a type that has no code yet is touched, so an owner's edit survives.
UPDATE membership_types SET code = 'IND' WHERE slug = 'individual-member' AND code = '';
UPDATE membership_types SET code = 'YTH' WHERE slug = 'youth-student-member' AND code = '';
UPDATE membership_types SET code = 'ORG' WHERE slug = 'organisation-partner-member' AND code = '';

-- ------------------------------------------------------------------ newsletter (see docs/NEWSLETTER.md). ADDITIVE and IDEMPOTENT.
-- Settings: defaults of app/config/settings_schema.php (group "Newsletter"); refreshed only while pristine, like every other setting.
-- The SMTP login is NOT a setting: it lives in .env (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD ...).
INSERT INTO settings (setting_key, setting_value) VALUES
  ('newsletter_from_name', ''),
  ('newsletter_from_email', ''),
  ('newsletter_reply_to', ''),
  ('newsletter_footer_text', ''),
  ('newsletter_batch_size', '30'),
  ('newsletter_default_subject_prefix', ''),
  ('newsletter_pdf_max_mb', '25')
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- Page: /newsletter. Header menu: a child of Blog (dropdown "Blog > Newsletter"), plus the footer menu. All its copy comes from the page registry
-- (app/config/pages/newsletter.php). The owner can move or hide it in Admin > Menus. parent_slug is looked up so the statement also works on a database
-- whose `blog` row is not there yet (then it is NULL and the entry is a top-level item).
INSERT INTO pages (slug, title, type, template, is_published, show_in_nav, show_in_footer, nav_label, nav_order, parent_slug)
SELECT 'newsletter', 'Newsletter', 'registry', 'default', 1, 1, 1, 'Newsletter', 73, (SELECT p.slug FROM pages p WHERE p.slug = 'blog')
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(title), pages.title),
  type = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(type), pages.type),
  template = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(template), pages.template),
  is_published = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(is_published), pages.is_published),
  show_in_nav = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(show_in_nav), pages.show_in_nav),
  show_in_footer = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(show_in_footer), pages.show_in_footer),
  nav_label = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(nav_label), pages.nav_label),
  nav_order = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(nav_order), pages.nav_order),
  parent_slug = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(parent_slug), pages.parent_slug),
  updated_at = IF((@reseed = 1 OR pages.updated_at = pages.created_at), pages.created_at, pages.updated_at);

-- ONE-TIME menu upgrade (guarded by the marker setting below). The first build put Newsletter in the footer only (show_in_nav = 0, no parent, order 75).
-- A database that already has that row and where the owner has edited the page since (so the pristine refresh above skipped it) would otherwise never
-- get the new menu entry. The row is moved ONLY while its three menu fields still hold those first-build values, i.e. the owner never touched the
-- menu placement; updated_at is left alone. After the first run the marker stops this for good, so hiding the entry later (Admin > Menus) sticks.
UPDATE pages SET show_in_nav = 1, nav_order = 73,
  parent_slug = (SELECT b.slug FROM (SELECT slug FROM pages WHERE slug = 'blog') b),
  updated_at = updated_at
WHERE slug = 'newsletter' AND show_in_nav = 0 AND parent_slug IS NULL AND nav_order = 75
  AND NOT EXISTS (SELECT 1 FROM (SELECT setting_key FROM settings WHERE setting_key = 'migration_newsletter_nav_v1') m);
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('migration_newsletter_nav_v1', '1');

-- ONE-TIME menu upgrade: Membership becomes a child of Involvement. Moved ONLY while the owner never touched its menu placement (top level, order 52);
-- the marker stops this after the first run so a later change in Admin > Menus sticks.
UPDATE pages SET parent_slug = 'involvement', updated_at = updated_at
WHERE slug = 'membership' AND parent_slug IS NULL AND nav_order = 52
  AND NOT EXISTS (SELECT 1 FROM (SELECT setting_key FROM settings WHERE setting_key = 'migration_membership_nav_v1') m);
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('migration_membership_nav_v1', '1');

-- ONE sample issue, a DRAFT (never public until the owner publishes it), built ONLY from wording that already exists on this site / in the
-- owner's copy: the planned-programs introduction (docs/content-source/planned-programs.md), the "Visit to Wii Got Quarry Site" activity, the
-- founder quote of the About page and the gallery photo captions. The owner edits it or deletes it. Inserted only while the table is EMPTY,
-- so a re-import never brings back a sample the owner deleted and never adds a second issue next to the owner's own.
INSERT INTO newsletters (slug, issue_number, title, summary, issue_date, cover_image, cover_alt, content, status, version)
SELECT seed.slug, seed.issue_number, seed.title, seed.summary, seed.issue_date, seed.cover_image, seed.cover_alt, seed.content, seed.status, seed.version FROM (
  SELECT 'sample-issue-edit-or-delete-me' AS slug, NULL AS issue_number, 'Sample issue — edit or delete me' AS title, 'Lumina Africa Foundation is developing a portfolio of community-centred programs designed to expand opportunities for underserved communities, particularly young people in Northern Uganda.' AS summary, CURDATE() AS issue_date,
         'assets/img/site/photo-04.jpg' AS cover_image, 'Young people seated in red theatre-style seats at an event, some smiling and looking off to one side' AS cover_alt,
         '{"version":1,"featured":{"heading":"Building Pathways to Opportunity. Preparing Communities for Change.","body_html":"<p>Lumina Africa Foundation is developing a portfolio of community-centred programs designed to expand opportunities for underserved communities, particularly young people in Northern Uganda.</p><p>Our planned interventions focus on three interconnected pillars: Health, Education, and Technology &amp; Innovation. Through these areas, we aspire to promote access to reliable information, strengthen practical skills, encourage innovation and help communities build the knowledge and capacity needed for sustainable development.</p>","image":{"path":"assets/img/site/photo-05.jpg","alt":"Young people seated in a bright room with laptops; one in the foreground types on a laptop","caption":"Young people with laptops in a classroom"},"link":{"label":"Explore Our Planned Programs","url":"/programs"}},"blocks":[{"id":"bsample01","type":"article","heading":"Visit to Wii Got Quarry Site","body_html":"<p>Our Director joined the Delegation of the Northern Region Youth Parliament for a dynamic outreach event at the Wii Got Quarry Site in Gulu City, together with youth leaders from the EU Youth Sounding Board and representatives of the 8th Northern Regional Youth Parliament.</p><p>We engaged the quarry youth in stimulating discussions about environmental sustainability and community development while providing them with essential safety gear to keep them safe and sound during all activities.</p>","image":{"path":"","alt":"","caption":""},"link":{"label":"Read the full blog post","url":"/blog/empowering-voices-planting-hope-lumina-africa-foundations-weekend-at-wii-got-quarry"}},{"id":"bsample02","type":"image","image":{"path":"assets/img/site/photo-03.jpg","alt":"A man in a blue polo shirt points at a computer screen while young people wearing headphones work at computers around him","caption":"Young people working at computers"}},{"id":"bsample03","type":"quote","body_html":"<p>Northern Uganda lost years to conflict, but we will not lose our future to digital exclusion. Every young person, regardless of certificates or background, deserves the skills to participate in today’s economy.</p>","cite":"Moses Komakech, Founder"},{"id":"bsample04","type":"divider"},{"id":"bsample05","type":"button","button":{"label":"Partner With Us","url":"/involvement","style":"primary"}}]}' AS content, 'draft' AS status, 1 AS version
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM newsletters);

INSERT INTO newsletter_revisions (newsletter_id, version, title, content, source)
SELECT n.id, n.version, n.title, n.content, 'save' FROM newsletters n
WHERE n.slug = 'sample-issue-edit-or-delete-me' AND NOT EXISTS (SELECT 1 FROM newsletter_revisions r WHERE r.newsletter_id = n.id);

-- ------------------------------------------------------------------ partners logos (see docs/PARTNERS_NEWSLETTER.md). ADDITIVE and IDEMPOTENT.
-- Settings only (defaults of app/config/settings_schema.php, group "Partners"); refreshed only while pristine. NO partner is seeded: the owner adds them.
INSERT INTO settings (setting_key, setting_value) VALUES
  ('partners_logo_hover_grey', '0'),
  ('partners_logo_trim', '1')
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ------------------------------------------------------------------ calendar (see docs/CALENDAR.md). ADDITIVE and IDEMPOTENT.
-- Same rules as the rest of this file: a row is refreshed from here ONLY while it is pristine (updated_at = created_at); a row an admin
-- edited, hid or confirmed is never overwritten. Per-occurrence changes (moving / confirming an Eid date) live in calendar_overrides,
-- which this file never touches, and they do not mark the entry as edited.
-- NO Lumina programme session is seeded: the owner adds those in Admin > Calendar.
INSERT INTO settings (setting_key, setting_value) VALUES
  ('calendar_week_start', 'monday'),
  ('calendar_show_on_contact', '1'),
  ('calendar_default_types', '')
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- Page: /calendar, a child of Contact in the menus (header dropdown + footer). All copy comes from app/config/pages/calendar.php.
-- parent_slug is looked up so the statement also works on a database whose `contact` row is not there yet (then it is NULL).
INSERT INTO pages (slug, title, type, template, is_published, show_in_nav, show_in_footer, nav_label, nav_order, parent_slug)
SELECT 'calendar', 'Calendar', 'registry', 'default', 1, 1, 1, 'Calendar', 91, (SELECT p.slug FROM pages p WHERE p.slug = 'contact')
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(title), pages.title),
  type = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(type), pages.type),
  template = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(template), pages.template),
  is_published = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(is_published), pages.is_published),
  show_in_nav = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(show_in_nav), pages.show_in_nav),
  show_in_footer = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(show_in_footer), pages.show_in_footer),
  nav_label = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(nav_label), pages.nav_label),
  nav_order = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(nav_order), pages.nav_order),
  parent_slug = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(parent_slug), pages.parent_slug),
  updated_at = IF((@reseed = 1 OR pages.updated_at = pages.created_at), pages.created_at, pages.updated_at);

-- Entry types (names and colours are editable in Admin > Calendar > Types; the colour pairs are checked for AA contrast by bin/selftest-calendar.php).
INSERT INTO calendar_types (slug, name, color, icon, sort_order, is_active, show_on_public, is_system) VALUES
  ('programme',         'Programme session',          '#2f6f7a', 'graduation-cap', 10, 1, 1, 0),
  ('event',             'Event',                      '#b4540f', 'calendar',       20, 1, 1, 1),
  ('uganda-holiday',    'Uganda public holiday',      '#b42318', 'star',           30, 1, 1, 1),
  ('international-day', 'International / UN day',     '#1f5fa8', 'globe',          40, 1, 1, 0),
  ('meeting',           'Meeting / outreach',         '#6b4e9b', 'users',          50, 1, 1, 0),
  ('deadline',          'Deadline',                   '#8a5a06', 'clock',          60, 1, 1, 0),
  ('other',             'Other',                      '#5a6470', 'tag',            70, 1, 1, 0)
ON DUPLICATE KEY UPDATE
  name = IF((@reseed = 1 OR updated_at = created_at), VALUES(name), name),
  color = IF((@reseed = 1 OR updated_at = created_at), VALUES(color), color),
  icon = IF((@reseed = 1 OR updated_at = created_at), VALUES(icon), icon),
  sort_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(sort_order), sort_order),
  is_system = VALUES(is_system),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- System entries: 14 Uganda public holidays (Public Holidays Act) + 46 curated international / UN days. Yearly / nth-weekday / Easter /
-- Islamic RULES, anchored at 2020 (earlier years are not shown). Eid al-Fitr and Eid al-Adha are flagged "date to be confirmed" (tabular
-- prediction; the real day follows moon sighting and the official announcement) until an admin confirms each year (calendar_overrides).
-- Sources of every day: docs/CALENDAR.md. Generated from the same rule validator the application uses (RecurrenceRules::normalise).
INSERT INTO calendar_entries (type_id, title, slug, description, all_day, start_date, recurrence, recurrence_data, source_label, source_url, is_system, is_public, is_active, needs_confirmation)
SELECT x.type_id, x.title, x.slug, x.description, 1, x.start_date, x.recurrence, x.recurrence_data, x.source_label, x.source_url, 1, 1, 1, x.needs_confirmation FROM (
SELECT t.id AS type_id, s.title, s.slug, s.description, s.start_date, s.recurrence, s.recurrence_data, s.source_label, s.source_url, s.needs_confirmation FROM (
  SELECT 'uganda-holiday' AS tslug, 'New Year''s Day' AS title, 'new-years-day' AS slug, '<p>The first day of the calendar year.</p>' AS description, '2020-01-01' AS start_date, 'yearly' AS recurrence, '{"interval":1}' AS recurrence_data, 'Public Holidays Act (Uganda)' AS source_label, 'https://ulii.org/akn/ug/act/1965/23' AS source_url, 0 AS needs_confirmation
  UNION ALL SELECT 'uganda-holiday', 'NRM Liberation Day', 'nrm-liberation-day', '<p>Public holiday marking 26 January 1986, when the National Resistance Movement took power.</p>', '2020-01-26', 'yearly', '{"interval":1}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'uganda-holiday', 'Archbishop Janani Luwum Day', 'archbishop-janani-luwum-day', '<p>Public holiday in memory of Archbishop Janani Luwum, who died on 16 February 1977.</p>', '2020-02-16', 'yearly', '{"interval":1}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'uganda-holiday', 'International Women''s Day', 'international-womens-day', '<p>Public holiday on the day the United Nations observes International Women''s Day.</p>', '2020-03-08', 'yearly', '{"interval":1}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'uganda-holiday', 'Good Friday', 'good-friday', '<p>Christian observance on the Friday before Easter Sunday. The date changes every year.</p>', '2020-01-01', 'easter', '{"easter_offset":-2}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'uganda-holiday', 'Easter Monday', 'easter-monday', '<p>The Monday after Easter Sunday. The date changes every year.</p>', '2020-01-01', 'easter', '{"easter_offset":1}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'uganda-holiday', 'Labour Day', 'labour-day', '<p>Public holiday on International Workers'' Day.</p>', '2020-05-01', 'yearly', '{"interval":1}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'uganda-holiday', 'Uganda Martyrs'' Day', 'uganda-martyrs-day', '<p>Public holiday remembering the Uganda Martyrs, Christians put to death between 1885 and 1887.</p>', '2020-06-03', 'yearly', '{"interval":1}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'uganda-holiday', 'National Heroes'' Day', 'national-heroes-day', '<p>Public holiday to honour national heroes.</p>', '2020-06-09', 'yearly', '{"interval":1}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'uganda-holiday', 'Eid al-Fitr', 'eid-al-fitr', '<p>Marks the end of Ramadan (1 Shawwal). The exact day follows the sighting of the new moon and the official announcement, so the date shown is a prediction until an administrator confirms it.</p>', '2020-01-01', 'islamic', '{"islamic":"eid_al_fitr"}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 1
  UNION ALL SELECT 'uganda-holiday', 'Eid al-Adha', 'eid-al-adha', '<p>The Feast of Sacrifice (10 Dhu al-Hijjah). The exact day follows the sighting of the new moon and the official announcement, so the date shown is a prediction until an administrator confirms it.</p>', '2020-01-01', 'islamic', '{"islamic":"eid_al_adha"}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 1
  UNION ALL SELECT 'uganda-holiday', 'Independence Day', 'independence-day', '<p>Marks Uganda''s independence from the United Kingdom on 9 October 1962.</p>', '2020-10-09', 'yearly', '{"interval":1}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'uganda-holiday', 'Christmas Day', 'christmas-day', '<p>Christian celebration of the birth of Jesus Christ.</p>', '2020-12-25', 'yearly', '{"interval":1}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'uganda-holiday', 'Boxing Day', 'boxing-day', '<p>The day after Christmas Day.</p>', '2020-12-26', 'yearly', '{"interval":1}', 'Public Holidays Act (Uganda)', 'https://ulii.org/akn/ug/act/1965/23', 0
  UNION ALL SELECT 'international-day', 'International Day of Education', 'international-day-of-education', '<p>Highlights the role of education in peace and sustainable development.</p>', '2020-01-24', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/education-day', 0
  UNION ALL SELECT 'international-day', 'World Cancer Day', 'world-cancer-day', '<p>Global awareness day on cancer prevention, detection and treatment, led by the Union for International Cancer Control.</p>', '2020-02-04', 'yearly', '{"interval":1}', 'Union for International Cancer Control', NULL, 0
  UNION ALL SELECT 'international-day', 'International Day of Zero Tolerance for Female Genital Mutilation', 'international-day-of-zero-tolerance-for-fgm', '<p>Raises awareness of the harm of female genital mutilation and calls for it to end.</p>', '2020-02-06', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/female-genital-mutilation-day', 0
  UNION ALL SELECT 'international-day', 'Safer Internet Day', 'safer-internet-day', '<p>An awareness day for the safe and positive use of digital technology, held on a Tuesday in the second week of February. The organisers announce each year''s date, so check it before planning around it.</p>', '2020-01-01', 'nth_weekday', '{"interval":1,"month":2,"weekday":2,"nth":1,"from_day":5}', 'Insafe / Safer Internet Day', 'https://www.saferinternetday.org/', 1
  UNION ALL SELECT 'international-day', 'International Day of Women and Girls in Science', 'international-day-of-women-and-girls-in-science', '<p>Promotes full and equal access to science for women and girls.</p>', '2020-02-11', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/women-and-girls-in-science-day/', 0
  UNION ALL SELECT 'international-day', 'World Day of Social Justice', 'world-day-of-social-justice', '<p>Supports efforts to tackle poverty, exclusion and unemployment.</p>', '2020-02-20', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/social-justice-day', 0
  UNION ALL SELECT 'international-day', 'International Mother Language Day', 'international-mother-language-day', '<p>Promotes linguistic and cultural diversity and multilingual education.</p>', '2020-02-21', 'yearly', '{"interval":1}', 'UNESCO / United Nations', 'https://www.un.org/en/observances/mother-language-day', 0
  UNION ALL SELECT 'international-day', 'World Water Day', 'world-water-day', '<p>Focuses attention on the importance of fresh water and access to safe water for all.</p>', '2020-03-22', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/water-day', 0
  UNION ALL SELECT 'international-day', 'World Tuberculosis Day', 'world-tuberculosis-day', '<p>Raises public awareness of tuberculosis and efforts to end it.</p>', '2020-03-24', 'yearly', '{"interval":1}', 'World Health Organization', NULL, 0
  UNION ALL SELECT 'international-day', 'World Health Day', 'world-health-day', '<p>The World Health Organization anniversary, used each year for a global health awareness theme.</p>', '2020-04-07', 'yearly', '{"interval":1}', 'World Health Organization', 'https://www.who.int/campaigns/world-health-day', 0
  UNION ALL SELECT 'international-day', 'International Girls in ICT Day', 'international-girls-in-ict-day', '<p>Encourages girls and young women to consider careers in information and communication technology. Held on the fourth Thursday of April.</p>', '2020-01-01', 'nth_weekday', '{"interval":1,"month":4,"weekday":4,"nth":4}', 'International Telecommunication Union', 'https://www.itu.int/women-and-girls/girls-in-ict/', 0
  UNION ALL SELECT 'international-day', 'International Mother Earth Day', 'international-mother-earth-day', '<p>Recognises the Earth as home and the need to protect it for present and future generations.</p>', '2020-04-22', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/earth-day', 0
  UNION ALL SELECT 'international-day', 'World Malaria Day', 'world-malaria-day', '<p>Recognises global efforts to control malaria.</p>', '2020-04-25', 'yearly', '{"interval":1}', 'World Health Organization', 'https://www.who.int/campaigns/world-malaria-day', 0
  UNION ALL SELECT 'international-day', 'World Hand Hygiene Day', 'world-hand-hygiene-day', '<p>Promotes clean hands to prevent infections, especially in health care.</p>', '2020-05-05', 'yearly', '{"interval":1}', 'World Health Organization', NULL, 0
  UNION ALL SELECT 'international-day', 'World Red Cross and Red Crescent Day', 'world-red-cross-and-red-crescent-day', '<p>Celebrates the principles of the Red Cross and Red Crescent Movement and its volunteers, on the birthday of its founder Henry Dunant.</p>', '2020-05-08', 'yearly', '{"interval":1}', 'IFRC', 'https://www.ifrc.org/world-red-cross-and-red-crescent-day', 0
  UNION ALL SELECT 'international-day', 'International Day of Families', 'international-day-of-families', '<p>Raises awareness of issues that affect families.</p>', '2020-05-15', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/international-day-of-families', 0
  UNION ALL SELECT 'international-day', 'World Telecommunication and Information Society Day', 'world-telecommunication-and-information-society-day', '<p>Promotes the benefits of information and communication technologies and bridging the digital divide.</p>', '2020-05-17', 'yearly', '{"interval":1}', 'International Telecommunication Union / United Nations', 'https://www.un.org/en/observances/telecommunication-day', 0
  UNION ALL SELECT 'international-day', 'Africa Day', 'africa-day', '<p>Marks the founding of the Organisation of African Unity on 25 May 1963, the forerunner of the African Union.</p>', '2020-05-25', 'yearly', '{"interval":1}', 'African Union', 'https://au.int/en/newsevents/20180525/africa-day-25-may', 0
  UNION ALL SELECT 'international-day', 'World No Tobacco Day', 'world-no-tobacco-day', '<p>Raises awareness of the health risks of tobacco use.</p>', '2020-05-31', 'yearly', '{"interval":1}', 'World Health Organization', 'https://www.who.int/campaigns/world-no-tobacco-day', 0
  UNION ALL SELECT 'international-day', 'World Environment Day', 'world-environment-day', '<p>The main United Nations day for encouraging action to protect the environment.</p>', '2020-06-05', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/environment-day', 0
  UNION ALL SELECT 'international-day', 'World Day Against Child Labour', 'world-day-against-child-labour', '<p>Raises awareness of child labour and what is needed to end it.</p>', '2020-06-12', 'yearly', '{"interval":1}', 'International Labour Organization / United Nations', 'https://www.un.org/en/observances/world-day-against-child-labour', 0
  UNION ALL SELECT 'international-day', 'World Blood Donor Day', 'world-blood-donor-day', '<p>Thanks voluntary blood donors and encourages more people to give blood.</p>', '2020-06-14', 'yearly', '{"interval":1}', 'World Health Organization', 'https://www.who.int/campaigns/world-blood-donor-day', 0
  UNION ALL SELECT 'international-day', 'Day of the African Child', 'day-of-the-african-child', '<p>Draws attention to the rights and needs of children in Africa; it recalls the 16 June 1976 Soweto student protests.</p>', '2020-06-16', 'yearly', '{"interval":1}', 'African Union', NULL, 0
  UNION ALL SELECT 'international-day', 'World Refugee Day', 'world-refugee-day', '<p>Honours the strength and courage of people forced to flee their homes.</p>', '2020-06-20', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/refugee-day', 0
  UNION ALL SELECT 'international-day', 'World Youth Skills Day', 'world-youth-skills-day', '<p>Celebrates the importance of equipping young people with skills for work and entrepreneurship.</p>', '2020-07-15', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/world-youth-skills-day', 0
  UNION ALL SELECT 'international-day', 'Nelson Mandela International Day', 'nelson-mandela-international-day', '<p>A call to take action for the community and to serve others, in honour of Nelson Mandela.</p>', '2020-07-18', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/events/mandeladay/', 0
  UNION ALL SELECT 'international-day', 'International Youth Day', 'international-youth-day', '<p>Draws attention to issues affecting young people and celebrates their role.</p>', '2020-08-12', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/youth-day', 0
  UNION ALL SELECT 'international-day', 'World Humanitarian Day', 'world-humanitarian-day', '<p>Honours aid workers and those who lost their lives in humanitarian service.</p>', '2020-08-19', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/humanitarian-day', 0
  UNION ALL SELECT 'international-day', 'International Day of Charity', 'international-day-of-charity', '<p>Recognises the role of charity and volunteers in helping people in need.</p>', '2020-09-05', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/charity-day', 0
  UNION ALL SELECT 'international-day', 'International Literacy Day', 'international-literacy-day', '<p>Highlights the importance of literacy for individuals, communities and societies.</p>', '2020-09-08', 'yearly', '{"interval":1}', 'UNESCO', 'https://www.unesco.org/en/days/literacy', 0
  UNION ALL SELECT 'international-day', 'International Day of Peace', 'international-day-of-peace', '<p>A day devoted to strengthening the ideals of peace.</p>', '2020-09-21', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/international-day-peace', 0
  UNION ALL SELECT 'international-day', 'World Teachers'' Day', 'world-teachers-day', '<p>Celebrates teachers and the importance of the teaching profession.</p>', '2020-10-05', 'yearly', '{"interval":1}', 'UNESCO', 'https://www.unesco.org/en/days/teachers', 0
  UNION ALL SELECT 'international-day', 'World Mental Health Day', 'world-mental-health-day', '<p>Raises awareness of mental health issues and support for mental health care.</p>', '2020-10-10', 'yearly', '{"interval":1}', 'World Health Organization', NULL, 0
  UNION ALL SELECT 'international-day', 'International Day of the Girl Child', 'international-day-of-the-girl-child', '<p>Recognises girls'' rights and the challenges they face.</p>', '2020-10-11', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/girl-child-day', 0
  UNION ALL SELECT 'international-day', 'Global Handwashing Day', 'global-handwashing-day', '<p>Promotes handwashing with soap as an easy, effective way to protect health.</p>', '2020-10-15', 'yearly', '{"interval":1}', 'Global Handwashing Partnership', NULL, 0
  UNION ALL SELECT 'international-day', 'World Food Day', 'world-food-day', '<p>Marks the founding of the Food and Agriculture Organization and promotes action against hunger.</p>', '2020-10-16', 'yearly', '{"interval":1}', 'Food and Agriculture Organization', NULL, 0
  UNION ALL SELECT 'international-day', 'International Day for the Eradication of Poverty', 'international-day-for-the-eradication-of-poverty', '<p>Promotes awareness of the need to end poverty in all countries.</p>', '2020-10-17', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/day-for-eradicating-poverty', 0
  UNION ALL SELECT 'international-day', 'United Nations Day', 'united-nations-day', '<p>Marks the anniversary of the entry into force of the United Nations Charter in 1945.</p>', '2020-10-24', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/un-day', 0
  UNION ALL SELECT 'international-day', 'International Day for Tolerance', 'international-day-for-tolerance', '<p>Promotes mutual understanding between cultures and peoples.</p>', '2020-11-16', 'yearly', '{"interval":1}', 'UNESCO', 'https://www.unesco.org/en/days/tolerance', 0
  UNION ALL SELECT 'international-day', 'Universal Children''s Day', 'universal-childrens-day', '<p>Promotes children''s welfare and rights; the United Nations also calls it World Children''s Day.</p>', '2020-11-20', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/world-childrens-day', 0
  UNION ALL SELECT 'international-day', 'International Day for the Elimination of Violence against Women', 'international-day-for-the-elimination-of-violence-against-women', '<p>Raises awareness that violence against women and girls must end; it opens the 16 Days of Activism.</p>', '2020-11-25', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/ending-violence-against-women-day', 0
  UNION ALL SELECT 'international-day', 'World AIDS Day', 'world-aids-day', '<p>Supports people living with HIV and remembers those who have died of AIDS-related illness.</p>', '2020-12-01', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/world-aids-day', 0
  UNION ALL SELECT 'international-day', 'International Day of Persons with Disabilities', 'international-day-of-persons-with-disabilities', '<p>Promotes the rights and well-being of persons with disabilities and their inclusion.</p>', '2020-12-03', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/day-of-persons-with-disabilities', 0
  UNION ALL SELECT 'international-day', 'International Volunteer Day', 'international-volunteer-day', '<p>Celebrates volunteers and their contribution to economic and social development.</p>', '2020-12-05', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/volunteer-day', 0
  UNION ALL SELECT 'international-day', 'Human Rights Day', 'human-rights-day', '<p>Marks the adoption of the Universal Declaration of Human Rights in 1948.</p>', '2020-12-10', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/human-rights-day', 0
  UNION ALL SELECT 'international-day', 'International Migrants Day', 'international-migrants-day', '<p>Recognises the contributions of migrants and promotes their rights.</p>', '2020-12-18', 'yearly', '{"interval":1}', 'United Nations', 'https://www.un.org/en/observances/migrants-day', 0
) AS s JOIN calendar_types t ON t.slug = s.tslug
) AS x
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR calendar_entries.updated_at = calendar_entries.created_at), VALUES(title), calendar_entries.title),
  description = IF((@reseed = 1 OR calendar_entries.updated_at = calendar_entries.created_at), VALUES(description), calendar_entries.description),
  start_date = IF((@reseed = 1 OR calendar_entries.updated_at = calendar_entries.created_at), VALUES(start_date), calendar_entries.start_date),
  recurrence = IF((@reseed = 1 OR calendar_entries.updated_at = calendar_entries.created_at), VALUES(recurrence), calendar_entries.recurrence),
  recurrence_data = IF((@reseed = 1 OR calendar_entries.updated_at = calendar_entries.created_at), VALUES(recurrence_data), calendar_entries.recurrence_data),
  source_label = IF((@reseed = 1 OR calendar_entries.updated_at = calendar_entries.created_at), VALUES(source_label), calendar_entries.source_label),
  source_url = IF((@reseed = 1 OR calendar_entries.updated_at = calendar_entries.created_at), VALUES(source_url), calendar_entries.source_url),
  needs_confirmation = IF((@reseed = 1 OR calendar_entries.updated_at = calendar_entries.created_at), VALUES(needs_confirmation), calendar_entries.needs_confirmation),
  type_id = IF((@reseed = 1 OR calendar_entries.updated_at = calendar_entries.created_at), VALUES(type_id), calendar_entries.type_id),
  updated_at = IF((@reseed = 1 OR calendar_entries.updated_at = calendar_entries.created_at), calendar_entries.created_at, calendar_entries.updated_at);

-- Eid days that are already known and verified (the engine's tabular day differs by one for three of them): confirmed overrides, so the page
-- shows the real day with the source in the note instead of "Date to be confirmed". INSERT IGNORE: an override an admin saved for the same
-- occurrence (UNIQUE entry_id + occurrence_date) always wins. Later years stay predicted until an admin confirms them (Admin > Calendar > Holidays).
INSERT IGNORE INTO calendar_overrides (entry_id, occurrence_date, new_date, confirmed, note)
SELECT e.id, s.occurrence_date, s.new_date, 1, s.note FROM (
  SELECT 'eid-al-fitr' AS slug, '2024-04-10' AS occurrence_date, NULL AS new_date, 'Eid al-Fitr, Wednesday 10 April 2024, as observed in Uganda (officeholidays.com).' AS note
  UNION ALL SELECT 'eid-al-adha', '2024-06-17', '2024-06-16', 'Announced by the Uganda Muslim Supreme Council: Idd ul Adhuha on Sunday 16 June 2024 (Daily Monitor).'
  UNION ALL SELECT 'eid-al-fitr', '2025-03-31', '2025-03-30', 'Announced by the Uganda Muslim Supreme Council: Idd ul Fitr on Sunday 30 March 2025 (Daily Monitor).'
  UNION ALL SELECT 'eid-al-adha', '2025-06-07', '2025-06-06', 'Declared a public holiday by the Ministry of Public Service: Friday 6 June 2025 (Nile Post).'
  UNION ALL SELECT 'eid-al-fitr', '2026-03-20', NULL, 'Announced by the Uganda Muslim Supreme Council: Idd ul Fitr on Friday 20 March 2026 (Daily Monitor).'
  UNION ALL SELECT 'eid-al-adha', '2026-05-27', NULL, 'Eid al-Adha prayers at the National Mosque on Wednesday 27 May 2026 (Uganda Muslim Supreme Council).'
) AS s JOIN calendar_entries e ON e.slug = s.slug AND e.is_system = 1;

-- ------------------------------------------------------------------ meeting scheduler (see docs/MEETINGS.md). ADDITIVE and IDEMPOTENT.
-- Same rules as the rest of this file: a row is refreshed from here ONLY while it is pristine (updated_at = created_at); a setting or page row an
-- admin edited is never overwritten. NO meeting, attendee or request is seeded: the owner creates them. The calendar type "Meeting / outreach"
-- (slug `meeting`) is seeded with the calendar types above; MeetingService::calendarTypeId() recreates it at run time if the owner deleted it.
INSERT INTO settings (setting_key, setting_value) VALUES
  ('meeting_default_duration', '60'),
  ('meeting_default_platform', 'google_meet'),
  ('meeting_from_note', ''),
  ('meeting_request_topics', CONCAT('Partnership', CHAR(10), 'Membership', CHAR(10), 'Volunteering', CHAR(10), 'Programmes', CHAR(10), 'Media', CHAR(10), 'Other')),
  ('meeting_request_enabled', '1')
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- Virtual registry page (no URL, no menu): wording of the invitee page /meeting/{token} and of the meeting e-mails (Admin > Pages > Meetings).
-- Needed as a row because content_blocks.page_slug is a foreign key to pages.slug.
INSERT INTO pages (slug, title, type, template, is_published, show_in_nav, show_in_footer, nav_label, nav_order, parent_slug) VALUES
  ('site_meetings', 'Meetings: invitee page & e-mail wording', 'registry', 'default', 1, 0, 0, 'Meetings', 9040, NULL)
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  type = IF((@reseed = 1 OR updated_at = created_at), VALUES(type), type),
  template = IF((@reseed = 1 OR updated_at = created_at), VALUES(template), template),
  is_published = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_published), is_published),
  show_in_nav = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_nav), show_in_nav),
  show_in_footer = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_footer), show_in_footer),
  nav_label = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_label), nav_label),
  nav_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_order), nav_order),
  parent_slug = IF((@reseed = 1 OR updated_at = created_at), VALUES(parent_slug), parent_slug),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ---------------------------------------------------------------------------------------------------------------------------------------
-- ROLES (Admin > Roles). INSERT IGNORE: rows the owner edited or created are never overwritten. admin always holds every ability (the empty list is ignored).
-- ---------------------------------------------------------------------------------------------------------------------------------------
INSERT IGNORE INTO roles (slug, name, description, abilities, is_system) VALUES
  ('admin', 'Administrator', 'Can do everything, including users, roles, settings and menus.', '[]', 1),
  ('editor', 'Editor', 'Edits pages and content, handles the inbox, members, newsletters, calendar and meetings.', '["dashboard.view","pages.edit","content.manage","content.delete","inbox.manage","export.csv","members.manage","newsletter.manage","calendar.manage","meetings.manage","forms.manage","forms.submissions","shop.manage","shop.orders","social.share"]', 1),
  ('content-writer', 'Content Writer', 'Writes and edits website pages and content (blog, programmes, gallery, events). Cannot see messages, members or settings.', '["dashboard.view","pages.edit","content.manage"]', 0),
  ('communications', 'Communications Officer', 'Content, newsletters, calendar and meetings. Cannot email the whole list or see the inbox.', '["dashboard.view","content.manage","newsletter.manage","calendar.manage","meetings.manage"]', 0),
  ('membership-officer', 'Membership Officer', 'Handles membership applications, payments and receipts, with CSV export and meetings. No website editing.', '["dashboard.view","members.manage","export.csv","meetings.manage"]', 0),
  ('support-officer', 'Support Officer', 'Answers messages and handles the inbox (messages, subscribers, applications, donations). No website editing.', '["dashboard.view","inbox.manage","export.csv"]', 0);

-- ------------------------------------------------------------------ forms (see docs/FORMS.md). ADDITIVE and IDEMPOTENT.
-- Same rules as the rest of this file: a row is refreshed from here ONLY while it is pristine (updated_at = created_at); a setting or page row an
-- admin edited is never overwritten. NO form and NO submission is seeded: the form templates are code (App\Services\Forms\FormTemplates) and the
-- owner creates forms in Admin > Forms.
INSERT INTO settings (setting_key, setting_value) VALUES
  ('forms_directory_enabled', '0'),
  ('forms_default_notify', ''),
  ('forms_max_upload_mb', '5'),
  ('forms_submit_limit', '10'),
  ('forms_retention_default_days', '0')
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- Page: /forms (the optional public list of forms) and the wording of every public form (buttons, messages, e-mails): app/config/pages/forms.php.
-- Not in the menus by default (the list itself is switched off until Settings > Forms says otherwise); needed as a row because content_blocks.page_slug
-- is a foreign key to pages.slug.
INSERT INTO pages (slug, title, type, template, is_published, show_in_nav, show_in_footer, nav_label, nav_order, parent_slug) VALUES
  ('forms', 'Forms', 'registry', 'default', 1, 0, 0, 'Forms', 92, NULL)
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  type = IF((@reseed = 1 OR updated_at = created_at), VALUES(type), type),
  template = IF((@reseed = 1 OR updated_at = created_at), VALUES(template), template),
  is_published = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_published), is_published),
  show_in_nav = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_nav), show_in_nav),
  show_in_footer = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_footer), show_in_footer),
  nav_label = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_label), nav_label),
  nav_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_order), nav_order),
  parent_slug = IF((@reseed = 1 OR updated_at = created_at), VALUES(parent_slug), parent_slug),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ONE-TIME role upgrade (guarded by the marker setting below): the built-in Editor role gets "Create and edit forms" and "See and handle form answers".
-- It is changed ONLY while its stored ability list is still EXACTLY the original default (the list before forms existed), i.e. the owner never edited
-- the role; a list the owner changed in Admin > Roles is left alone. (On a fresh database the INSERT IGNORE above already carries the new list.)
UPDATE roles SET abilities = '["dashboard.view","pages.edit","content.manage","content.delete","inbox.manage","export.csv","members.manage","newsletter.manage","calendar.manage","meetings.manage","forms.manage","forms.submissions"]',
  updated_at = updated_at
WHERE slug = 'editor' AND is_system = 1
  AND abilities = '["dashboard.view","pages.edit","content.manage","content.delete","inbox.manage","export.csv","members.manage","newsletter.manage","calendar.manage","meetings.manage"]'
  AND NOT EXISTS (SELECT 1 FROM (SELECT setting_key FROM settings WHERE setting_key = 'migration_forms_editor_role_v1') m);
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('migration_forms_editor_role_v1', '1');

-- ------------------------------------------------------------------ shop (see docs/SHOP.md). ADDITIVE and IDEMPOTENT.
-- Settings: every key of App\Services\Shop\ShopSettings::DEFS with its default (a selftest compares the two). They are refreshed from here ONLY while
-- they are pristine (updated_at = created_at), like every other setting; a value the owner saved is never overwritten. The shop starts CLOSED
-- (shop_open = 0): the owner reviews the sample products, sets delivery and the payment methods, then opens it in Admin > Shop > Settings.
-- The payment-gateway secrets are NOT seeded and NOT in this file: they are typed in Admin > Shop > Payments (stored encrypted) or set in .env.
INSERT INTO settings (setting_key, setting_value) VALUES
  ('shop_open', '0'),
  ('shop_closed_message', ''),
  ('shop_currency', 'UGX'),
  ('shop_order_prefix', 'LAF'),
  ('shop_notify_emails', ''),
  ('shop_max_qty', '10'),
  ('shop_hold_days', '7'),
  ('shop_manual_hold_days', '3'),
  ('shop_gateway_hold_hours', '24'),
  ('shop_restock_on_cancel', '1'),
  ('shop_restock_on_refund', '0'),
  ('shop_auto_confirm_paid', '1'),
  ('shop_pickup_enabled', '1'),
  ('shop_pickup_address', ''),
  ('shop_pickup_hours', ''),
  ('shop_delivery_enabled', '0'),
  ('shop_delivery_fee', '0'),
  ('shop_free_delivery_over', '0'),
  ('shop_delivery_areas', ''),
  ('shop_terms_text', ''),
  ('shop_returns_text', ''),
  ('shop_delivery_text', ''),
  ('shop_pay_book_enabled', '1'),
  ('shop_pay_book_label', ''),
  ('shop_pay_book_instructions', ''),
  ('shop_pay_book_sort', '50'),
  ('shop_pay_cod_enabled', '1'),
  ('shop_pay_cod_label', ''),
  ('shop_pay_cod_instructions', ''),
  ('shop_pay_cod_sort', '40'),
  ('shop_pay_momo_enabled', '0'),
  ('shop_pay_momo_label', ''),
  ('shop_pay_momo_instructions', ''),
  ('shop_pay_momo_sort', '20'),
  ('shop_momo_mtn_name', ''),
  ('shop_momo_mtn_number', ''),
  ('shop_momo_airtel_name', ''),
  ('shop_momo_airtel_number', ''),
  ('shop_pay_bank_enabled', '0'),
  ('shop_pay_bank_label', ''),
  ('shop_pay_bank_instructions', ''),
  ('shop_pay_bank_sort', '30'),
  ('shop_bank_name', ''),
  ('shop_bank_account_name', ''),
  ('shop_bank_account_number', ''),
  ('shop_bank_branch', ''),
  ('shop_bank_swift', ''),
  ('shop_pay_flutterwave_enabled', '0'),
  ('shop_pay_flutterwave_label', ''),
  ('shop_pay_flutterwave_instructions', ''),
  ('shop_pay_flutterwave_sort', '10'),
  ('shop_flw_public_key', ''),
  ('shop_flw_mode', 'test'),
  ('shop_flw_options', 'card,mobilemoneyuganda'),
  ('shop_flw_title', '')
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);


-- Page: /shop. Header menu: a child of Donate (dropdown under the Donate button) plus the footer menu. All its copy comes from the page registry
-- (app/config/pages/shop.php). The owner can move or hide it in Admin > Menus. parent_slug is looked up so the statement also works on a database
-- whose `donate` row is not there yet (then it is NULL and the entry is a top-level item).
INSERT INTO pages (slug, title, type, template, is_published, show_in_nav, show_in_footer, nav_label, nav_order, parent_slug)
SELECT 'shop', 'Shop', 'registry', 'default', 1, 1, 1, 'Shop', 96, (SELECT p.slug FROM pages p WHERE p.slug = 'donate')
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(title), pages.title),
  type = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(type), pages.type),
  template = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(template), pages.template),
  is_published = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(is_published), pages.is_published),
  show_in_nav = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(show_in_nav), pages.show_in_nav),
  show_in_footer = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(show_in_footer), pages.show_in_footer),
  nav_label = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(nav_label), pages.nav_label),
  nav_order = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(nav_order), pages.nav_order),
  parent_slug = IF((@reseed = 1 OR pages.updated_at = pages.created_at), VALUES(parent_slug), pages.parent_slug),
  updated_at = IF((@reseed = 1 OR pages.updated_at = pages.created_at), pages.created_at, pages.updated_at);

-- ONE-TIME menu upgrade (guarded by the marker setting below): a `shop` row that exists without the Donate parent (for example one created by an
-- earlier registry sync before this seed ran) is put under Donate, but ONLY while its three menu fields still hold the defaults of a row nobody has
-- touched (top level, in the header, order 96). After the first run the marker stops this for good, so moving or hiding Shop later (Admin > Menus) sticks.
UPDATE pages SET parent_slug = (SELECT d.slug FROM (SELECT slug FROM pages WHERE slug = 'donate') d), updated_at = updated_at
WHERE slug = 'shop' AND parent_slug IS NULL AND nav_order = 96 AND show_in_nav = 1
  AND NOT EXISTS (SELECT 1 FROM (SELECT setting_key FROM settings WHERE setting_key = 'migration_shop_nav_v1') m);
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('migration_shop_nav_v1', '1');

-- ONE-TIME role upgrade (guarded by the marker setting below): the built-in Editor role gets "Manage shop products" and "See and handle shop orders".
-- It is changed ONLY while its stored ability list is still EXACTLY the list it had before the shop existed (the owner never edited the role); a list
-- changed in Admin > Roles is left alone. shop.settings (payment secrets), shop.export and shop.delete stay with the administrator.
UPDATE roles SET abilities = '["dashboard.view","pages.edit","content.manage","content.delete","inbox.manage","export.csv","members.manage","newsletter.manage","calendar.manage","meetings.manage","forms.manage","forms.submissions","shop.manage","shop.orders"]',
  updated_at = updated_at
WHERE slug = 'editor' AND is_system = 1
  AND abilities = '["dashboard.view","pages.edit","content.manage","content.delete","inbox.manage","export.csv","members.manage","newsletter.manage","calendar.manage","meetings.manage","forms.manage","forms.submissions"]'
  AND NOT EXISTS (SELECT 1 FROM (SELECT setting_key FROM settings WHERE setting_key = 'migration_shop_editor_role_v1') m);
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('migration_shop_editor_role_v1', '1');

-- Sample categories and SIX SAMPLE PRODUCTS (placeholders: names, prices in UGX, texts and pictures are for the owner to replace or delete). They are
-- inserted ONCE (marker below), so deleting a sample or a whole category never brings it back on a later import; the pictures are the generated
-- illustrations in assets/img/shop/ (bin/dev/make-shop-samples.py). No order, no customer and no payment is ever seeded.
INSERT INTO shop_categories (slug, name, sort_order, is_active)
SELECT seed.slug, seed.name, seed.sort_order, 1 FROM (
  SELECT 'jumpers-hoodies' AS slug, 'Jumpers & hoodies' AS name, 10 AS sort_order
  UNION ALL SELECT 'caps', 'Caps', 20
  UNION ALL SELECT 't-shirts', 'T-shirts', 30
  UNION ALL SELECT 'stickers', 'Stickers', 40
  UNION ALL SELECT 'other', 'Other', 90
) AS seed
WHERE (@reseed = 1 OR NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'migration_shop_samples_v1'))
ON DUPLICATE KEY UPDATE
  shop_categories.name = IF((@reseed = 1 OR shop_categories.updated_at = shop_categories.created_at), VALUES(name), shop_categories.name),
  shop_categories.sort_order = IF((@reseed = 1 OR shop_categories.updated_at = shop_categories.created_at), VALUES(sort_order), shop_categories.sort_order),
  shop_categories.updated_at = IF((@reseed = 1 OR shop_categories.updated_at = shop_categories.created_at), shop_categories.created_at, shop_categories.updated_at);

INSERT INTO shop_products (slug, category_id, name, summary, description, details, price, track_stock, stock_qty, low_stock, options, allow_booking, is_featured, is_sample, status, sort_order)
SELECT seed.slug, (SELECT c.id FROM shop_categories c WHERE c.slug = seed.cat), seed.name, seed.summary, seed.description, seed.details, seed.price, seed.track_stock, seed.stock_qty, seed.low_stock, seed.options, seed.allow_booking, seed.is_featured, 1, 'published', seed.sort_order FROM (
  SELECT 'mission-hoodie' AS slug, 'jumpers-hoodies' AS cat, 'Mission Hoodie' AS name, 'A warm navy hoodie with the Lumina Africa Foundation patch on the chest. Sample product.' AS summary, '<p>A navy hooded jumper with a front pocket and the Lumina Africa Foundation patch on the chest. The tagline &ldquo;Igniting Light, Inspiring Change&rdquo; is on the back.</p><p><strong>This is a sample product.</strong> Replace the name, description, price and pictures with the real item in Admin &gt; Shop &gt; Products.</p>' AS description, 'Lumina Africa Foundation patch on the chest
Tagline on the back
Sizes S to XXL' AS details, 85000.00 AS price, 1 AS track_stock, 40 AS stock_qty, 5 AS low_stock, '[{"key":"size","label":"Size","required":true,"values":[{"value":"s","label":"S","stock":null,"swatch":null},{"value":"m","label":"M","stock":null,"swatch":null},{"value":"l","label":"L","stock":null,"swatch":null},{"value":"xl","label":"XL","stock":null,"swatch":null},{"value":"xxl","label":"XXL","stock":null,"swatch":null}]}]' AS options, 1 AS allow_booking, 1 AS is_featured, 10 AS sort_order
  UNION ALL SELECT 'lumina-crew-jumper', 'jumpers-hoodies', 'Lumina Crew Jumper', 'A gold crew-neck jumper with the Lumina patch. Sample product.', '<p>A gold crew-neck jumper with the Lumina Africa Foundation patch on the chest and the organisation name on the back.</p><p><strong>This is a sample product.</strong> Replace the name, description, price and pictures with the real item in Admin &gt; Shop &gt; Products.</p>', 'Lumina Africa Foundation patch on the chest
Organisation name on the back
Sizes S to XXL', 70000.00, 1, 25, 5, '[{"key":"size","label":"Size","required":true,"values":[{"value":"s","label":"S","stock":null,"swatch":null},{"value":"m","label":"M","stock":null,"swatch":null},{"value":"l","label":"L","stock":null,"swatch":null},{"value":"xl","label":"XL","stock":null,"swatch":null},{"value":"xxl","label":"XXL","stock":null,"swatch":null}]}]', 1, 0, 20
  UNION ALL SELECT 'lumina-cap', 'caps', 'Lumina Cap', 'A cap with the Lumina patch on the front, in navy or gold. Sample product.', '<p>A cap with the Lumina Africa Foundation patch on the front. Choose navy or gold.</p><p><strong>This is a sample product.</strong> Replace the name, description, price and pictures with the real item in Admin &gt; Shop &gt; Products.</p>', 'Lumina Africa Foundation patch on the front
Colours: navy or gold', 25000.00, 1, 60, 8, '[{"key":"colour","label":"Colour","required":true,"values":[{"value":"navy","label":"Navy","stock":null,"swatch":"#2c3950"},{"value":"gold","label":"Gold","stock":null,"swatch":"#d6a151"}]}]', 0, 1, 30
  UNION ALL SELECT 'lumina-tshirt-navy', 't-shirts', 'Lumina T-shirt (Navy)', 'A navy T-shirt with the Lumina patch on the chest. Sample product.', '<p>A navy T-shirt with the Lumina Africa Foundation patch on the chest and the organisation name on the back.</p><p><strong>This is a sample product.</strong> Replace the name, description, price and pictures with the real item in Admin &gt; Shop &gt; Products.</p>', 'Lumina Africa Foundation patch on the chest
Organisation name on the back
Sizes S to XXL', 35000.00, 1, 80, 10, '[{"key":"size","label":"Size","required":true,"values":[{"value":"s","label":"S","stock":null,"swatch":null},{"value":"m","label":"M","stock":null,"swatch":null},{"value":"l","label":"L","stock":null,"swatch":null},{"value":"xl","label":"XL","stock":null,"swatch":null},{"value":"xxl","label":"XXL","stock":null,"swatch":null}]}]', 0, 1, 40
  UNION ALL SELECT 'lumina-tshirt-cream', 't-shirts', 'Lumina T-shirt (Cream)', 'A cream T-shirt with the Lumina patch on the chest. Sample product.', '<p>A cream T-shirt with the Lumina Africa Foundation patch on the chest and &ldquo;Wear the mission&rdquo; on the back.</p><p><strong>This is a sample product.</strong> Replace the name, description, price and pictures with the real item in Admin &gt; Shop &gt; Products. Only a few are in stock so you can see how the &ldquo;Few left&rdquo; label looks.</p>', 'Lumina Africa Foundation patch on the chest
Wear the mission on the back
Sizes S to XXL', 35000.00, 1, 4, 5, '[{"key":"size","label":"Size","required":true,"values":[{"value":"s","label":"S","stock":null,"swatch":null},{"value":"m","label":"M","stock":null,"swatch":null},{"value":"l","label":"L","stock":null,"swatch":null},{"value":"xl","label":"XL","stock":null,"swatch":null},{"value":"xxl","label":"XXL","stock":null,"swatch":null}]}]', 0, 0, 50
  UNION ALL SELECT 'lumina-sticker-pack', 'stickers', 'Lumina Sticker Pack', 'A sheet of Lumina stickers: the patch, the flame and the tagline. Sample product.', '<p>A sheet of Lumina Africa Foundation stickers: the round patch, the flame, a star and the words &ldquo;Wear the mission&rdquo;.</p><p><strong>This is a sample product.</strong> Replace the name, description, price and pictures with the real item in Admin &gt; Shop &gt; Products.</p>', 'One sheet of stickers
Includes the round Lumina patch sticker', 10000.00, 0, NULL, 5, NULL, 0, 0, 60
) AS seed
WHERE (@reseed = 1 OR NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'migration_shop_samples_v1'))
ON DUPLICATE KEY UPDATE
  shop_products.name = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(name), shop_products.name),
  shop_products.summary = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(summary), shop_products.summary),
  shop_products.description = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(description), shop_products.description),
  shop_products.details = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(details), shop_products.details),
  shop_products.price = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(price), shop_products.price),
  shop_products.track_stock = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(track_stock), shop_products.track_stock),
  shop_products.stock_qty = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(stock_qty), shop_products.stock_qty),
  shop_products.low_stock = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(low_stock), shop_products.low_stock),
  shop_products.options = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(options), shop_products.options),
  shop_products.allow_booking = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(allow_booking), shop_products.allow_booking),
  shop_products.is_featured = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(is_featured), shop_products.is_featured),
  shop_products.sort_order = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), VALUES(sort_order), shop_products.sort_order),
  shop_products.updated_at = IF((@reseed = 1 OR shop_products.updated_at = shop_products.created_at), shop_products.created_at, shop_products.updated_at);

INSERT INTO shop_product_images (product_id, path, path_webp, path_2x, path_webp_2x, width, height, alt, sort_order, is_cover)
SELECT p.id, i.path, i.path_webp, i.path_2x, i.path_webp_2x, i.width, i.height, i.alt, i.sort_order, i.is_cover FROM shop_products p JOIN (
  SELECT 'mission-hoodie' AS slug, 'assets/img/shop/hoodie-navy-front.jpg' AS path, 'assets/img/shop/hoodie-navy-front.webp' AS path_webp, 'assets/img/shop/hoodie-navy-front-2x.jpg' AS path_2x, 'assets/img/shop/hoodie-navy-front-2x.webp' AS path_webp_2x, 600 AS width, 600 AS height, 'Navy hoodie with the Lumina patch on the chest (illustration)' AS alt, 10 AS sort_order, 1 AS is_cover
  UNION ALL SELECT 'mission-hoodie', 'assets/img/shop/hoodie-navy-back.jpg', 'assets/img/shop/hoodie-navy-back.webp', 'assets/img/shop/hoodie-navy-back-2x.jpg', 'assets/img/shop/hoodie-navy-back-2x.webp', 600, 600, 'Back of the navy hoodie with the tagline Igniting Light, Inspiring Change (illustration)', 20, 0
  UNION ALL SELECT 'lumina-crew-jumper', 'assets/img/shop/jumper-gold-front.jpg', 'assets/img/shop/jumper-gold-front.webp', 'assets/img/shop/jumper-gold-front-2x.jpg', 'assets/img/shop/jumper-gold-front-2x.webp', 600, 600, 'Gold crew-neck jumper with the Lumina patch (illustration)', 10, 1
  UNION ALL SELECT 'lumina-crew-jumper', 'assets/img/shop/jumper-gold-back.jpg', 'assets/img/shop/jumper-gold-back.webp', 'assets/img/shop/jumper-gold-back-2x.jpg', 'assets/img/shop/jumper-gold-back-2x.webp', 600, 600, 'Back of the gold jumper with the words Lumina Africa Foundation (illustration)', 20, 0
  UNION ALL SELECT 'lumina-cap', 'assets/img/shop/cap-navy.jpg', 'assets/img/shop/cap-navy.webp', 'assets/img/shop/cap-navy-2x.jpg', 'assets/img/shop/cap-navy-2x.webp', 600, 600, 'Navy cap with the Lumina patch (illustration)', 10, 1
  UNION ALL SELECT 'lumina-cap', 'assets/img/shop/cap-gold.jpg', 'assets/img/shop/cap-gold.webp', 'assets/img/shop/cap-gold-2x.jpg', 'assets/img/shop/cap-gold-2x.webp', 600, 600, 'Gold cap with the Lumina patch (illustration)', 20, 0
  UNION ALL SELECT 'lumina-tshirt-navy', 'assets/img/shop/tshirt-navy-front.jpg', 'assets/img/shop/tshirt-navy-front.webp', 'assets/img/shop/tshirt-navy-front-2x.jpg', 'assets/img/shop/tshirt-navy-front-2x.webp', 600, 600, 'Navy T-shirt with the Lumina patch on the chest (illustration)', 10, 1
  UNION ALL SELECT 'lumina-tshirt-navy', 'assets/img/shop/tshirt-navy-back.jpg', 'assets/img/shop/tshirt-navy-back.webp', 'assets/img/shop/tshirt-navy-back-2x.jpg', 'assets/img/shop/tshirt-navy-back-2x.webp', 600, 600, 'Back of the navy T-shirt with the words Lumina Africa Foundation (illustration)', 20, 0
  UNION ALL SELECT 'lumina-tshirt-cream', 'assets/img/shop/tshirt-cream-front.jpg', 'assets/img/shop/tshirt-cream-front.webp', 'assets/img/shop/tshirt-cream-front-2x.jpg', 'assets/img/shop/tshirt-cream-front-2x.webp', 600, 600, 'Cream T-shirt with the Lumina patch on the chest (illustration)', 10, 1
  UNION ALL SELECT 'lumina-tshirt-cream', 'assets/img/shop/tshirt-cream-back.jpg', 'assets/img/shop/tshirt-cream-back.webp', 'assets/img/shop/tshirt-cream-back-2x.jpg', 'assets/img/shop/tshirt-cream-back-2x.webp', 600, 600, 'Back of the cream T-shirt with the words Wear the mission (illustration)', 20, 0
  UNION ALL SELECT 'lumina-sticker-pack', 'assets/img/shop/stickers-sheet.jpg', 'assets/img/shop/stickers-sheet.webp', 'assets/img/shop/stickers-sheet-2x.jpg', 'assets/img/shop/stickers-sheet-2x.webp', 600, 600, 'A sheet of Lumina stickers (illustration)', 10, 1
  UNION ALL SELECT 'lumina-sticker-pack', 'assets/img/shop/stickers-single.jpg', 'assets/img/shop/stickers-single.webp', 'assets/img/shop/stickers-single-2x.jpg', 'assets/img/shop/stickers-single-2x.webp', 600, 600, 'The round Lumina patch sticker (illustration)', 20, 0
) AS i ON i.slug = p.slug
WHERE p.is_sample = 1 AND (@reseed = 1 OR NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'migration_shop_samples_v1'))
  AND NOT EXISTS (SELECT 1 FROM shop_product_images x WHERE x.product_id = p.id AND x.path = i.path);

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('migration_shop_samples_v1', '1');

-- ------------------------------------------------------------------ social media (see docs/SOCIAL.md). ADDITIVE and IDEMPOTENT.
-- Settings: every key of App\Services\Social\SocialConfig::defs() plus the three new profile links of settings_schema.php (a selftest compares them). Refreshed from here
-- ONLY while pristine (updated_at = created_at); a value the owner saved is never overwritten. Every channel starts SWITCHED OFF and unconfigured: nothing is posted anywhere
-- until the owner has created the platform keys, pasted them in Admin > Social media > Channels, tested them and switched the channel on. NO token, key or secret is seeded
-- (they are typed in the admin and stored encrypted, or set in .env).
INSERT INTO settings (setting_key, setting_value) VALUES
  ('social_whatsapp_channel', ''),
  ('social_telegram', ''),
  ('social_threads', ''),
  ('social_enabled', '1'),
  ('social_hashtags', '#LuminaAfricaFoundation #Uganda #CommunityDevelopment'),
  ('social_utm', '1'),
  ('social_graph_version', 'v23.0'),
  ('social_li_version', '202510'),
  ('social_order', 'facebook,instagram,linkedin,x,tiktok,youtube,whatsapp,telegram,threads'),
  ('social_show_footer', '1'),
  ('social_show_contact', '1'),
  ('social_show_newsletter', '1'),
  ('social_ch_facebook_enabled', '0'),
  ('social_ch_facebook_default', '1'),
  ('social_ch_facebook_page_id', ''),
  ('social_ch_instagram_enabled', '0'),
  ('social_ch_instagram_default', '1'),
  ('social_ch_instagram_account_id', ''),
  ('social_ch_x_enabled', '0'),
  ('social_ch_x_default', '1'),
  ('social_ch_x_mode', 'oauth1'),
  ('social_ch_linkedin_enabled', '0'),
  ('social_ch_linkedin_default', '1'),
  ('social_ch_linkedin_org_urn', ''),
  ('social_ch_linkedin_expires', ''),
  ('social_ch_telegram_enabled', '0'),
  ('social_ch_telegram_default', '1'),
  ('social_ch_telegram_chat', ''),
  ('social_ch_telegram_test_chat', ''),
  ('social_ch_webhook_enabled', '0'),
  ('social_ch_webhook_default', '1'),
  ('social_ch_webhook_fields', ''),
  ('social_tpl_facebook', ''),
  ('social_tpl_instagram', ''),
  ('social_tpl_x', ''),
  ('social_tpl_linkedin', ''),
  ('social_tpl_telegram', ''),
  ('social_tpl_webhook', ''),
  ('social_tpl_tiktok', ''),
  ('social_card_bg', '#2C3950'),
  ('social_card_bg_dark', '#172036'),
  ('social_card_accent', '#D6A151'),
  ('social_card_accent2', '#CC651A'),
  ('social_card_text', '#FFFFFF'),
  ('social_card_tagline', ''),
  ('social_card_show_logo', '1'),
  ('social_card_show_url', '1')
ON DUPLICATE KEY UPDATE
  setting_value = IF((@reseed = 1 OR updated_at = created_at), VALUES(setting_value), setting_value),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- ONE-TIME role upgrade (guarded by the marker setting below): the built-in Editor role gets "Share newsletters on social media" (social.share). It is changed ONLY while its
-- stored ability list is still EXACTLY the list it had before the social module (the owner never edited the role); a list changed in Admin > Roles is left alone.
-- social.manage (API keys and tokens) stays with the administrator.
UPDATE roles SET abilities = '["dashboard.view","pages.edit","content.manage","content.delete","inbox.manage","export.csv","members.manage","newsletter.manage","calendar.manage","meetings.manage","forms.manage","forms.submissions","shop.manage","shop.orders","social.share"]',
  updated_at = updated_at
WHERE slug = 'editor' AND is_system = 1
  AND abilities = '["dashboard.view","pages.edit","content.manage","content.delete","inbox.manage","export.csv","members.manage","newsletter.manage","calendar.manage","meetings.manage","forms.manage","forms.submissions","shop.manage","shop.orders"]'
  AND NOT EXISTS (SELECT 1 FROM (SELECT setting_key FROM settings WHERE setting_key = 'migration_social_editor_role_v1') m);
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('migration_social_editor_role_v1', '1');

-- ------------------------------------------------------------------ LAF Academy (see docs/ACADEMY.md). ADDITIVE and IDEMPOTENT.
-- Role "Facilitator": teaches in the Academy (own courses, quizzes, virtual classes, the learners of those courses). INSERT IGNORE: a role the owner edited is never overwritten.
INSERT IGNORE INTO roles (slug, name, description, abilities, is_system) VALUES
  ('facilitator', 'Facilitator', 'Teaches in the LAF Academy: writes their own courses and quizzes, schedules virtual classes and follows their learners. Cannot see anything else.', '["dashboard.view","academy.teach"]', 0);

-- Page: /academy (the Academy home and every Academy screen use its wording: app/config/pages/academy.php). Needed as a row because content_blocks.page_slug is a foreign key to pages.slug.
INSERT INTO pages (slug, title, type, template, is_published, show_in_nav, show_in_footer, nav_label, nav_order, parent_slug) VALUES
  ('academy', 'LAF Academy', 'registry', 'default', 1, 1, 1, 'LAF Academy', 45, NULL)
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  type = IF((@reseed = 1 OR updated_at = created_at), VALUES(type), type),
  template = IF((@reseed = 1 OR updated_at = created_at), VALUES(template), template),
  is_published = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_published), is_published),
  show_in_nav = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_nav), show_in_nav),
  show_in_footer = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_footer), show_in_footer),
  nav_label = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_label), nav_label),
  nav_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_order), nav_order),
  parent_slug = IF((@reseed = 1 OR updated_at = created_at), VALUES(parent_slug), parent_slug),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

-- Page: /involvement/ambassadors (call for ambassadors; wording: app/config/pages/ambassadors.php). A child of Involvement. Needed as a row because content_blocks.page_slug is a foreign key to pages.slug.
INSERT INTO pages (slug, title, type, template, is_published, show_in_nav, show_in_footer, nav_label, nav_order, parent_slug) VALUES
  ('ambassadors', 'Ambassadors', 'registry', 'default', 1, 1, 1, 'Ambassadors', 56, 'involvement')
ON DUPLICATE KEY UPDATE
  title = IF((@reseed = 1 OR updated_at = created_at), VALUES(title), title),
  type = IF((@reseed = 1 OR updated_at = created_at), VALUES(type), type),
  template = IF((@reseed = 1 OR updated_at = created_at), VALUES(template), template),
  is_published = IF((@reseed = 1 OR updated_at = created_at), VALUES(is_published), is_published),
  show_in_nav = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_nav), show_in_nav),
  show_in_footer = IF((@reseed = 1 OR updated_at = created_at), VALUES(show_in_footer), show_in_footer),
  nav_label = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_label), nav_label),
  nav_order = IF((@reseed = 1 OR updated_at = created_at), VALUES(nav_order), nav_order),
  parent_slug = IF((@reseed = 1 OR updated_at = created_at), VALUES(parent_slug), parent_slug),
  updated_at = IF((@reseed = 1 OR updated_at = created_at), created_at, updated_at);

