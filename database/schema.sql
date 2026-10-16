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
  `role` ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `must_change_password` TINYINT(1) NOT NULL DEFAULT 0,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
