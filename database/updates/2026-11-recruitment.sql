-- Recruitment (ambassadors, jobs, volunteers, automatic criteria, ambassador profile). SAFE TO RUN AGAIN: every change checks first whether it is already there.
-- Importing this file is optional: the website creates all of this by itself the first time an admin page or form is used.

CREATE TABLE IF NOT EXISTS `recruit_kpis` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `label` VARCHAR(190) NOT NULL, `weight` TINYINT UNSIGNED NOT NULL DEFAULT 1, `sort_order` INT NOT NULL DEFAULT 0, `is_active` TINYINT(1) NOT NULL DEFAULT 1, PRIMARY KEY (`id`), KEY `ix_recruit_kpis_scope` (`scope`, `is_active`, `sort_order`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recruit_scores` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `application_id` INT UNSIGNED NOT NULL, `kpi_id` INT UNSIGNED NOT NULL, `points` TINYINT UNSIGNED NOT NULL, `scored_by` INT UNSIGNED NULL, `scored_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uq_recruit_scores` (`scope`, `application_id`, `kpi_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recruit_log` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `application_id` INT UNSIGNED NOT NULL, `status` VARCHAR(20) NOT NULL, `note` VARCHAR(600) NULL, `mailed` TINYINT(1) NOT NULL DEFAULT 0, `by_user` INT UNSIGNED NULL, `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `ix_recruit_log` (`scope`, `application_id`, `id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `recruit_feedback` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `application_id` INT UNSIGNED NOT NULL, `feedback` TEXT NULL, `updated_by` INT UNSIGNED NULL, `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uq_recruit_feedback` (`scope`, `application_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Columns of the three application tables
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND column_name = 'ref_no') = 0, 'ALTER TABLE `ambassador_applications` ADD COLUMN `ref_no` VARCHAR(30) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND column_name = 'score_pct') = 0, 'ALTER TABLE `ambassador_applications` ADD COLUMN `score_pct` DECIMAL(5,2) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND column_name = 'scored_at') = 0, 'ALTER TABLE `ambassador_applications` ADD COLUMN `scored_at` DATETIME NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND column_name = 'photo_path') = 0, 'ALTER TABLE `ambassador_applications` ADD COLUMN `photo_path` VARCHAR(255) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND column_name = 'cv_path') = 0, 'ALTER TABLE `ambassador_applications` ADD COLUMN `cv_path` VARCHAR(255) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND column_name = 'cv_original_name') = 0, 'ALTER TABLE `ambassador_applications` ADD COLUMN `cv_original_name` VARCHAR(190) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND column_name = 'cv_mime') = 0, 'ALTER TABLE `ambassador_applications` ADD COLUMN `cv_mime` VARCHAR(100) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND column_name = 'cv_size') = 0, 'ALTER TABLE `ambassador_applications` ADD COLUMN `cv_size` INT UNSIGNED NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND column_name = 'profile_token') = 0, 'ALTER TABLE `ambassador_applications` ADD COLUMN `profile_token` VARCHAR(64) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND index_name = 'ix_ambassador_applications_ref') = 0, 'ALTER TABLE `ambassador_applications` ADD KEY `ix_ambassador_applications_ref` (`ref_no`)', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'applications' AND column_name = 'ref_no') = 0, 'ALTER TABLE `applications` ADD COLUMN `ref_no` VARCHAR(30) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'applications' AND column_name = 'score_pct') = 0, 'ALTER TABLE `applications` ADD COLUMN `score_pct` DECIMAL(5,2) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'applications' AND column_name = 'scored_at') = 0, 'ALTER TABLE `applications` ADD COLUMN `scored_at` DATETIME NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'applications' AND column_name = 'photo_path') = 0, 'ALTER TABLE `applications` ADD COLUMN `photo_path` VARCHAR(255) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'applications' AND index_name = 'ix_applications_ref') = 0, 'ALTER TABLE `applications` ADD KEY `ix_applications_ref` (`ref_no`)', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'involvement_requests' AND column_name = 'ref_no') = 0, 'ALTER TABLE `involvement_requests` ADD COLUMN `ref_no` VARCHAR(30) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'involvement_requests' AND column_name = 'score_pct') = 0, 'ALTER TABLE `involvement_requests` ADD COLUMN `score_pct` DECIMAL(5,2) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'involvement_requests' AND column_name = 'scored_at') = 0, 'ALTER TABLE `involvement_requests` ADD COLUMN `scored_at` DATETIME NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'involvement_requests' AND column_name = 'photo_path') = 0, 'ALTER TABLE `involvement_requests` ADD COLUMN `photo_path` VARCHAR(255) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'involvement_requests' AND column_name = 'cv_path') = 0, 'ALTER TABLE `involvement_requests` ADD COLUMN `cv_path` VARCHAR(255) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'involvement_requests' AND column_name = 'cv_original_name') = 0, 'ALTER TABLE `involvement_requests` ADD COLUMN `cv_original_name` VARCHAR(190) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'involvement_requests' AND column_name = 'cv_mime') = 0, 'ALTER TABLE `involvement_requests` ADD COLUMN `cv_mime` VARCHAR(100) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'involvement_requests' AND column_name = 'cv_size') = 0, 'ALTER TABLE `involvement_requests` ADD COLUMN `cv_size` INT UNSIGNED NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'involvement_requests' AND index_name = 'ix_involvement_requests_ref') = 0, 'ALTER TABLE `involvement_requests` ADD KEY `ix_involvement_requests_ref` (`ref_no`)', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;

-- Columns of the criteria tables
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'recruit_kpis' AND column_name = 'auto_key') = 0, 'ALTER TABLE `recruit_kpis` ADD COLUMN `auto_key` VARCHAR(30) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'recruit_scores' AND column_name = 'auto') = 0, 'ALTER TABLE `recruit_scores` ADD COLUMN `auto` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'recruit_scores' AND column_name = 'skipped') = 0, 'ALTER TABLE `recruit_scores` ADD COLUMN `skipped` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'recruit_scores' AND column_name = 'comment') = 0, 'ALTER TABLE `recruit_scores` ADD COLUMN `comment` VARCHAR(300) NULL', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;

-- Statuses (repeating this is harmless)
ALTER TABLE `ambassador_applications` MODIFY `status` ENUM('new','reviewing','interview','approved','on_hold','declined') NOT NULL DEFAULT 'new';
ALTER TABLE `applications` MODIFY `status` ENUM('new','reviewing','shortlisted','interview','rejected','hired') NOT NULL DEFAULT 'new';
ALTER TABLE `involvement_requests` MODIFY `status` ENUM('new','contacted','in_progress','closed','reviewing','interview','accepted','on_hold','declined') NOT NULL DEFAULT 'new';

-- Numbers and private profile links for applications that have none yet
UPDATE `ambassador_applications` SET ref_no = CONCAT('AMB', '-', YEAR(created_at), '-', LPAD(id, 4, '0')) WHERE ref_no IS NULL;
UPDATE `applications` SET ref_no = CONCAT('JOB', '-', YEAR(created_at), '-', LPAD(id, 4, '0')) WHERE ref_no IS NULL;
UPDATE `involvement_requests` SET ref_no = CONCAT('VOL', '-', YEAR(created_at), '-', LPAD(id, 4, '0')) WHERE ref_no IS NULL;
UPDATE `ambassador_applications` SET profile_token = SHA2(CONCAT(id, UUID(), RAND()), 256) WHERE profile_token IS NULL;
SET @q = (SELECT IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND index_name = 'uq_ambassador_profile_token') = 0, 'ALTER TABLE `ambassador_applications` ADD UNIQUE KEY `uq_ambassador_profile_token` (`profile_token`)', 'SELECT 1'));
PREPARE st FROM @q; EXECUTE st; DEALLOCATE PREPARE st;

-- The criteria themselves (and their automatic scoring) are added by the website on first use.
