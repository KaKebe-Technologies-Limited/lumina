-- Recruitment (ambassadors, jobs, volunteers): tracking numbers, KPI scoring, stage history. Safe to run again. The page also creates all of this on first use.

CREATE TABLE IF NOT EXISTS `recruit_kpis` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `label` VARCHAR(190) NOT NULL, `weight` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0, `is_active` TINYINT(1) NOT NULL DEFAULT 1, PRIMARY KEY (`id`), KEY `ix_recruit_kpis_scope` (`scope`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recruit_scores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `application_id` INT UNSIGNED NOT NULL, `kpi_id` INT UNSIGNED NOT NULL, `points` TINYINT UNSIGNED NOT NULL,
  `scored_by` INT UNSIGNED NULL, `scored_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uq_recruit_scores` (`scope`, `application_id`, `kpi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recruit_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `application_id` INT UNSIGNED NOT NULL, `status` VARCHAR(20) NOT NULL, `note` VARCHAR(600) NULL,
  `mailed` TINYINT(1) NOT NULL DEFAULT 0, `by_user` INT UNSIGNED NULL, `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `ix_recruit_log` (`scope`, `application_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Columns added to the three application tables (run each ALTER once; ignore "Duplicate column" errors).
ALTER TABLE `ambassador_applications` ADD COLUMN `ref_no` VARCHAR(30) NULL, ADD COLUMN `score_pct` DECIMAL(5,2) NULL, ADD COLUMN `scored_at` DATETIME NULL, ADD COLUMN `photo_path` VARCHAR(255) NULL, ADD COLUMN `cv_path` VARCHAR(255) NULL, ADD COLUMN `cv_original_name` VARCHAR(190) NULL, ADD COLUMN `cv_mime` VARCHAR(100) NULL, ADD COLUMN `cv_size` INT UNSIGNED NULL, ADD KEY `ix_ambassador_applications_ref` (`ref_no`);
ALTER TABLE `applications` ADD COLUMN `ref_no` VARCHAR(30) NULL, ADD COLUMN `score_pct` DECIMAL(5,2) NULL, ADD COLUMN `scored_at` DATETIME NULL, ADD COLUMN `photo_path` VARCHAR(255) NULL, ADD KEY `ix_applications_ref` (`ref_no`);
ALTER TABLE `involvement_requests` ADD COLUMN `ref_no` VARCHAR(30) NULL, ADD COLUMN `score_pct` DECIMAL(5,2) NULL, ADD COLUMN `scored_at` DATETIME NULL, ADD COLUMN `photo_path` VARCHAR(255) NULL, ADD COLUMN `cv_path` VARCHAR(255) NULL, ADD COLUMN `cv_original_name` VARCHAR(190) NULL, ADD COLUMN `cv_mime` VARCHAR(100) NULL, ADD COLUMN `cv_size` INT UNSIGNED NULL, ADD KEY `ix_involvement_requests_ref` (`ref_no`);
ALTER TABLE `ambassador_applications` MODIFY `status` ENUM('new','reviewing','interview','approved','on_hold','declined') NOT NULL DEFAULT 'new';
ALTER TABLE `applications` MODIFY `status` ENUM('new','reviewing','shortlisted','interview','rejected','hired') NOT NULL DEFAULT 'new';
ALTER TABLE `involvement_requests` MODIFY `status` ENUM('new','contacted','in_progress','closed','reviewing','interview','accepted','on_hold','declined') NOT NULL DEFAULT 'new';
UPDATE `ambassador_applications` SET ref_no = CONCAT('AMB', '-', YEAR(created_at), '-', LPAD(id, 4, '0')) WHERE ref_no IS NULL;
UPDATE `applications` SET ref_no = CONCAT('JOB', '-', YEAR(created_at), '-', LPAD(id, 4, '0')) WHERE ref_no IS NULL;
UPDATE `involvement_requests` SET ref_no = CONCAT('VOL', '-', YEAR(created_at), '-', LPAD(id, 4, '0')) WHERE ref_no IS NULL;
